<?php
declare(strict_types=1);

namespace App\Models;

class Payment extends BaseModel
{
    protected static string $table = 'payments';

    public static function createPending(int $tenantId, int $payerId, string $orderId, float $total): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO payments (tenant_id, payer_id, order_id, gross_amount, status)
             VALUES (?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([$tenantId, $payerId, $orderId, $total]);
        return (int) self::db()->lastInsertId();
    }

    public static function attachInvoices(int $paymentId, array $invoiceIds): void
    {
        $stmt = self::db()->prepare("INSERT INTO payment_invoices (payment_id, invoice_id) VALUES (?, ?)");
        foreach ($invoiceIds as $invoiceId) {
            $stmt->execute([$paymentId, (int) $invoiceId]);
        }
    }

    public static function updateSnapToken(int $paymentId, string $token): void
    {
        $stmt = self::db()->prepare("UPDATE payments SET snap_token = ? WHERE id = ?");
        $stmt->execute([$token, $paymentId]);
    }

    public static function findByOrderId(string $orderId): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM payments WHERE order_id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        return $stmt->fetch() ?: null;
    }

    public static function updateStatus(int $paymentId, string $status): void
    {
        $stmt = self::db()->prepare("UPDATE payments SET status = ?, paid_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $paymentId]);
    }

    /**
 * Update split info setelah webhook settlement.
 */
public static function updateSplitInfo(int $paymentId, float $platformFee, float $tenantAmount): void
{
    $stmt = self::db()->prepare(
        "UPDATE payments
         SET platform_fee = ?, tenant_amount = ?, split_status = 'success'
         WHERE id = ?"
    );
    $stmt->execute([$platformFee, $tenantAmount, $paymentId]);
}

/**
 * Summary pendapatan online per tenant.
 */
public static function summaryOnlineByTenant(int $tenantId): array
{
    $stmt = self::db()->prepare(
        "SELECT
            COUNT(*) AS total_transaksi,
            COALESCE(SUM(gross_amount), 0) AS total_gross,
            COALESCE(SUM(platform_fee), 0) AS total_fee,
            COALESCE(SUM(tenant_amount), 0) AS total_tenant
         FROM payments
         WHERE tenant_id = ? AND status IN ('settlement', 'capture')"
    );
    $stmt->execute([$tenantId]);
    return $stmt->fetch() ?: [];
}

/**
 * Riwayat pencairan untuk tenant.
 * Menampilkan payment yang status settlement/capture.
 */
public static function pencairanByTenant(
    int $tenantId,
    array $filters = [],
    int $page = 1,
    int $perPage = 20
): array {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT p.*,
                py.nama AS payer_nama, py.nomor_induk
            FROM payments p
            INNER JOIN payers py ON py.id = p.payer_id
            WHERE p.tenant_id = ?
              AND p.status IN ('settlement', 'capture')";
    $params = [$tenantId];

    // Filter: tanggal
    if (!empty($filters['start']) && !empty($filters['end'])) {
        $sql .= " AND DATE(p.paid_at) BETWEEN ? AND ?";
        $params[] = $filters['start'];
        $params[] = $filters['end'];
    }

    // Filter: search order_id / nama pelanggan
    if (!empty($filters['q'])) {
        $sql .= " AND (p.order_id LIKE ? OR py.nama LIKE ? OR py.nomor_induk LIKE ?)";
        $like = '%' . $filters['q'] . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY p.paid_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Hitung total pencairan.
 */
public static function countPencairanByTenant(int $tenantId, array $filters = []): int
{
    $sql = "SELECT COUNT(*)
            FROM payments p
            INNER JOIN payers py ON py.id = p.payer_id
            WHERE p.tenant_id = ?
              AND p.status IN ('settlement', 'capture')";
    $params = [$tenantId];

    if (!empty($filters['start']) && !empty($filters['end'])) {
        $sql .= " AND DATE(p.paid_at) BETWEEN ? AND ?";
        $params[] = $filters['start'];
        $params[] = $filters['end'];
    }

    if (!empty($filters['q'])) {
        $sql .= " AND (p.order_id LIKE ? OR py.nama LIKE ? OR py.nomor_induk LIKE ?)";
        $like = '%' . $filters['q'] . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Summary pencairan: total gross, fee, tenant amount.
 */
public static function summaryPencairanByTenant(int $tenantId, array $filters = []): array
{
    $sql = "SELECT
                COUNT(*) AS total_transaksi,
                COALESCE(SUM(gross_amount), 0) AS total_gross,
                COALESCE(SUM(platform_fee), 0) AS total_fee,
                COALESCE(SUM(tenant_amount), 0) AS total_tenant
            FROM payments
            WHERE tenant_id = ?
              AND status IN ('settlement', 'capture')";
    $params = [$tenantId];

    if (!empty($filters['start']) && !empty($filters['end'])) {
        $sql .= " AND DATE(paid_at) BETWEEN ? AND ?";
        $params[] = $filters['start'];
        $params[] = $filters['end'];
    }

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: [];
}
}