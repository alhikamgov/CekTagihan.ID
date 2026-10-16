<?php
declare(strict_types=1);

namespace App\Models;

class Withdrawal extends BaseModel
{
    protected static string $table = 'withdrawals';

    public const MIN_AMOUNT = 50000; // Rp50.000

    public static function createRequest(
        int $tenantId,
        float $amount,
        array $bank,
        int $userId,
        ?string $note = null
    ): int {
        $stmt = self::db()->prepare(
            "INSERT INTO withdrawals
             (tenant_id, amount, status, bank_nama, bank_kode, rekening_nomor, rekening_pemilik, requested_by, note)
             VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $tenantId,
            $amount,
            $bank['bank_nama'],
            $bank['bank_kode'],
            $bank['rekening_nomor'],
            $bank['rekening_pemilik'],
            $userId,
            $note,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function listByTenant(int $tenantId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = self::db()->prepare(
            "SELECT w.*, u.nama AS reviewer_nama
             FROM withdrawals w
             LEFT JOIN users u ON u.id = w.reviewed_by
             WHERE w.tenant_id = ?
             ORDER BY w.requested_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$tenantId, $perPage, $offset]);
        return $stmt->fetchAll();
    }

    public static function countByTenant(int $tenantId): int
    {
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM withdrawals WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return (int) $stmt->fetchColumn();
    }

    public static function listAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT w.*, t.nama AS tenant_nama, t.slug AS tenant_slug,
                       u.nama AS requester_nama, r.nama AS reviewer_nama
                FROM withdrawals w
                INNER JOIN tenants t ON t.id = w.tenant_id
                LEFT JOIN users u ON u.id = w.requested_by
                LEFT JOIN users r ON r.id = w.reviewed_by
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (t.nama LIKE ? OR w.rekening_pemilik LIKE ?)";
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " ORDER BY FIELD(w.status, 'pending','approved','paid','rejected'), w.requested_at DESC
                  LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM withdrawals w INNER JOIN tenants t ON t.id = w.tenant_id WHERE 1=1";
        $params = [];
        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (t.nama LIKE ? OR w.rekening_pemilik LIKE ?)";
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM withdrawals WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function updateStatus(int $id, string $status, int $reviewerId, ?string $reason = null): void
    {
        $fields = "status = ?, reviewed_by = ?, reviewed_at = NOW()";
        $params = [$status, $reviewerId];

        if ($status === 'rejected' && $reason !== null) {
            $fields .= ", reject_reason = ?";
            $params[] = $reason;
        }
        if ($status === 'paid') {
            $fields .= ", paid_at = NOW()";
        }

        $params[] = $id;
        $stmt = self::db()->prepare("UPDATE withdrawals SET $fields WHERE id = ?");
        $stmt->execute($params);
    }

    public static function setProof(int $id, string $filename): void
    {
        $stmt = self::db()->prepare("UPDATE withdrawals SET payment_proof = ? WHERE id = ?");
        $stmt->execute([$filename, $id]);
    }

    /**
     * Ringkasan saldo untuk satu tenant.
     *
     * Model SEWA:
     *   - Pendapatan Online = SUM(gross_amount) - SUM(gateway_fee)
     *   - Platform tidak ambil potongan (platform_fee = 0)
     *   - Fee gateway adalah biaya riil dari DOKU (QRIS 0.7%, VA flat, dll)
     */
    public static function getBalanceSummary(int $tenantId): array
    {
        $db = self::db();

        // Total pendapatan online
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(gross_amount - COALESCE(gateway_fee, 0)), 0)
            FROM payments
            WHERE tenant_id = ?
            AND status IN ('settlement', 'capture')"
        );
        $stmt->execute([$tenantId]);
        $totalIncome = (float) $stmt->fetchColumn();

        // Total yang sudah ditarik (approved + paid)
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM withdrawals
            WHERE tenant_id = ? AND status IN ('approved', 'paid')"
        );
        $stmt->execute([$tenantId]);
        $totalWithdrawn = (float) $stmt->fetchColumn();

        // Total yang masih pending (mengunci saldo)
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM withdrawals
            WHERE tenant_id = ? AND status = 'pending'"
        );
        $stmt->execute([$tenantId]);
        $totalPending = (float) $stmt->fetchColumn();

        // Total pembayaran manual (tunai/transfer langsung, tidak lewat platform)
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(jumlah), 0) FROM manual_payments WHERE tenant_id = ?"
        );
        $stmt->execute([$tenantId]);
        $totalManual = (float) $stmt->fetchColumn();

        // Saldo tersedia = pendapatan - sudah ditarik - pending
        $available = $totalIncome - $totalWithdrawn - $totalPending;

        return [
            'total_income'    => $totalIncome,
            'total_withdrawn' => $totalWithdrawn,
            'total_pending'   => $totalPending,
            'total_manual'    => $totalManual,
            'available'       => max(0, $available),
        ];
    }

}