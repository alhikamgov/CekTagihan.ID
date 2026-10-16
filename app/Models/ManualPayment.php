<?php
declare(strict_types=1);

namespace App\Models;

class ManualPayment extends BaseModel
{
    protected static string $table = 'manual_payments';

    /**
     * Ambil semua pembayaran manual dengan filter.
     */
    public static function allByTenant(
        int $tenantId,
        array $filters = [],
        int $page = 1,
        int $perPage = 20
    ): array {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT mp.*,
                    p.nomor_induk, p.nama AS payer_nama,
                    i.jenis, i.periode
                FROM manual_payments mp
                INNER JOIN payers p ON p.id = mp.payer_id
                INNER JOIN invoices i ON i.id = mp.invoice_id
                WHERE mp.tenant_id = ?";
        $params = [$tenantId];

        // Filter: search nama atau nomor pelanggan
        if (!empty($filters['q'])) {
            $sql .= " AND (p.nama LIKE ? OR p.nomor_induk LIKE ?)";
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        // Filter: metode
        if (!empty($filters['metode'])) {
            $sql .= " AND mp.metode = ?";
            $params[] = $filters['metode'];
        }

        // Filter: tanggal
        if (!empty($filters['start']) && !empty($filters['end'])) {
            $sql .= " AND DATE(mp.paid_at) BETWEEN ? AND ?";
            $params[] = $filters['start'];
            $params[] = $filters['end'];
        }

        $sql .= " ORDER BY mp.paid_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Hitung total pembayaran manual dengan filter.
     */
    public static function countByTenant(int $tenantId, array $filters = []): int
    {
        $sql = "SELECT COUNT(*)
                FROM manual_payments mp
                INNER JOIN payers p ON p.id = mp.payer_id
                WHERE mp.tenant_id = ?";
        $params = [$tenantId];

        if (!empty($filters['q'])) {
            $sql .= " AND (p.nama LIKE ? OR p.nomor_induk LIKE ?)";
            $like = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
        }

        if (!empty($filters['metode'])) {
            $sql .= " AND mp.metode = ?";
            $params[] = $filters['metode'];
        }

        if (!empty($filters['start']) && !empty($filters['end'])) {
            $sql .= " AND DATE(mp.paid_at) BETWEEN ? AND ?";
            $params[] = $filters['start'];
            $params[] = $filters['end'];
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Summary total (tanpa filter).
     */
    public static function summaryByTenant(int $tenantId): array
    {
        $stmt = self::db()->prepare(
            "SELECT
                COUNT(*) AS total_transaksi,
                COALESCE(SUM(jumlah), 0) AS total_nominal
             FROM manual_payments
             WHERE tenant_id = ?"
        );
        $stmt->execute([$tenantId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Summary bulan ini.
     */
    public static function summaryBulanIni(int $tenantId): array
    {
        $bulanIni = date('Y-m');
        $stmt = self::db()->prepare(
            "SELECT
                COUNT(*) AS total_transaksi,
                COALESCE(SUM(jumlah), 0) AS total_nominal
             FROM manual_payments
             WHERE tenant_id = ?
               AND DATE_FORMAT(paid_at, '%Y-%m') = ?"
        );
        $stmt->execute([$tenantId, $bulanIni]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Buat pembayaran manual baru.
     */
    public static function create(int $tenantId, array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO manual_payments
                (tenant_id, payer_id, invoice_id, jumlah, metode, bukti_file, catatan, paid_at, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $tenantId,
            $data['payer_id'],
            $data['invoice_id'],
            $data['jumlah'],
            $data['metode']     ?? 'tunai',
            $data['bukti_file'] ?? null,
            $data['catatan']    ?? null,
            $data['paid_at'],
            $data['created_by'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    /**
     * Cari berdasarkan ID dan tenant.
     */
    public static function findByIdAndTenant(int $id, int $tenantId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT mp.*,
                    p.nomor_induk, p.nama AS payer_nama,
                    i.jenis, i.periode
             FROM manual_payments mp
             INNER JOIN payers p ON p.id = mp.payer_id
             INNER JOIN invoices i ON i.id = mp.invoice_id
             WHERE mp.id = ? AND mp.tenant_id = ?
             LIMIT 1"
        );
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch() ?: null;
    }
}