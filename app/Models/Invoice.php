<?php
declare(strict_types=1);

namespace App\Models;

class Invoice extends BaseModel
{
    protected static string $table = 'invoices';

    public static function allByTenant(int $tenantId, array $filters = [], int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT i.*, p.nomor_induk, p.nama AS payer_nama
            FROM invoices i
            INNER JOIN payers p ON p.id = i.payer_id
            WHERE i.tenant_id = ?";
    $params = [$tenantId];

    if (!empty($filters['status'])) {
        $sql .= " AND i.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['jenis'])) {
        $sql .= " AND i.jenis = ?";
        $params[] = $filters['jenis'];
    }
    if (!empty($filters['periode'])) {
        $sql .= " AND i.periode = ?";
        $params[] = $filters['periode'];
    }
    if (!empty($filters['q'])) {
        $sql .= " AND (p.nomor_induk LIKE ? OR p.nama LIKE ?)";
        $like = '%' . $filters['q'] . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY i.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

public static function countByTenantFiltered(int $tenantId, array $filters = []): int
{
    $sql = "SELECT COUNT(*)
            FROM invoices i
            INNER JOIN payers p ON p.id = i.payer_id
            WHERE i.tenant_id = ?";
    $params = [$tenantId];

    if (!empty($filters['status'])) {
        $sql .= " AND i.status = ?";
        $params[] = $filters['status'];
    }
    if (!empty($filters['jenis'])) {
        $sql .= " AND i.jenis = ?";
        $params[] = $filters['jenis'];
    }
    if (!empty($filters['periode'])) {
        $sql .= " AND i.periode = ?";
        $params[] = $filters['periode'];
    }
    if (!empty($filters['q'])) {
        $sql .= " AND (p.nomor_induk LIKE ? OR p.nama LIKE ?)";
        $like = '%' . $filters['q'] . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

    public static function findByIdAndTenant(int $id, int $tenantId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT i.*, p.nomor_induk, p.nama AS payer_nama, p.whatsapp AS payer_whatsapp
             FROM invoices i
             INNER JOIN payers p ON p.id = i.payer_id
             WHERE i.id = ? AND i.tenant_id = ?
             LIMIT 1"
        );
        $stmt->execute([$id, $tenantId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(int $tenantId, array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO invoices (tenant_id, payer_id, jenis, periode, deskripsi, jumlah, status, due_date)
             VALUES (?, ?, ?, ?, ?, ?, 'unpaid', ?)"
        );
        $stmt->execute([
            $tenantId,
            $data['payer_id'],
            $data['jenis'],
            $data['periode'],
            $data['deskripsi'] ?? null,
            $data['jumlah'],
            $data['due_date'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function cancel(int $id, int $tenantId, string $reason = ''): void
    {
        $stmt = self::db()->prepare(
            "UPDATE invoices 
            SET status = 'cancelled', 
                cancel_reason = ?, 
                cancelled_at = NOW()
            WHERE id = ? AND tenant_id = ? AND status = 'unpaid'"
            );
            $stmt->execute([$reason ?: null, $id, $tenantId]);
    }

    public static function summaryByTenant(int $tenantId): array
    {
        $stmt = self::db()->prepare(
            "SELECT
                COUNT(*) AS total_invoice,
                COALESCE(SUM(CASE WHEN status = 'unpaid' THEN jumlah ELSE 0 END), 0) AS total_unpaid,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN jumlah ELSE 0 END), 0) AS total_paid,
                COALESCE(SUM(CASE WHEN status = 'unpaid' AND due_date < CURDATE() THEN jumlah ELSE 0 END), 0) AS total_overdue
             FROM invoices
             WHERE tenant_id = ?"
        );
        $stmt->execute([$tenantId]);
        return $stmt->fetch() ?: [];
    }

    public static function listPeriodeByTenant(int $tenantId): array
    {
        $stmt = self::db()->prepare(
            "SELECT DISTINCT periode FROM invoices WHERE tenant_id = ? ORDER BY periode DESC"
        );
        $stmt->execute([$tenantId]);
        return array_column($stmt->fetchAll(), 'periode');
    }

        /**
     * Cek apakah payer sudah punya invoice dengan jenis + periode sama.
     */
    public static function findDuplicate(int $tenantId, int $payerId, string $jenis, string $periode): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT id FROM invoices 
             WHERE tenant_id = ? AND payer_id = ? AND jenis = ? AND periode = ? 
             AND status != 'cancelled'
             LIMIT 1"
        );
        $stmt->execute([$tenantId, $payerId, $jenis, $periode]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function bulkCreate(int $tenantId, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $db = self::db();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                "INSERT INTO invoices (tenant_id, payer_id, jenis, periode, deskripsi, jumlah, status, due_date)
                 VALUES (?, ?, ?, ?, ?, ?, 'unpaid', ?)"
            );

            $inserted = 0;
            foreach ($rows as $row) {
                $stmt->execute([
                    $tenantId,
                    $row['payer_id'],
                    $row['jenis'],
                    $row['periode'],
                    $row['deskripsi'] ?? null,
                    $row['jumlah'],
                    $row['due_date'],
                ]);
                $inserted++;
            }

            $db->commit();
            return $inserted;

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
 * Ambil semua invoice unpaid untuk tenant, dengan info payer + status telat.
 * Urut: paling telat dulu.
 */
public static function tunggakanByTenant(
    int $tenantId,
    string $filter = 'all', // all | overdue | due_soon
    int $page = 1,
    int $perPage = 20
): array {
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT i.*,
       p.nomor_induk, p.nama AS payer_nama, p.whatsapp AS payer_whatsapp,
       DATEDIFF(CURDATE(), i.due_date) AS hari_telat
            FROM invoices i
            INNER JOIN payers p ON p.id = i.payer_id
            WHERE i.tenant_id = ?
              AND i.status = 'unpaid'
              AND p.status = 'active'";
    $params = [$tenantId];

    if ($filter === 'overdue') {
        $sql .= " AND i.due_date < CURDATE()";
    } elseif ($filter === 'due_soon') {
        // Jatuh tempo dalam 7 hari ke depan, belum lewat
        $sql .= " AND i.due_date >= CURDATE() AND i.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    }

    $sql .= " ORDER BY i.due_date ASC, p.nomor_induk ASC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Hitung jumlah baris tunggakan (untuk pagination).
 */
public static function countTunggakanByTenant(int $tenantId, string $filter = 'all'): int
{
    $sql = "SELECT COUNT(*)
            FROM invoices i
            INNER JOIN payers p ON p.id = i.payer_id
            WHERE i.tenant_id = ?
              AND i.status = 'unpaid'
              AND p.status = 'active'";
    $params = [$tenantId];

    if ($filter === 'overdue') {
        $sql .= " AND i.due_date < CURDATE()";
    } elseif ($filter === 'due_soon') {
        $sql .= " AND i.due_date >= CURDATE() AND i.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    }

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Summary dashboard admin tenant.
 */
public static function summaryDashboard(int $tenantId): array
{
    $bulanIni = date('Y-m');

    // Total tagihan bulan ini (semua status, kecuali cancelled)
    $stmt = self::db()->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN status = 'unpaid' THEN jumlah ELSE 0 END), 0) AS total_unpaid,
            COALESCE(SUM(CASE WHEN status = 'paid' AND DATE_FORMAT(paid_at, '%Y-%m') = ? THEN jumlah ELSE 0 END), 0) AS total_paid_bulan_ini,
            COALESCE(SUM(CASE WHEN status = 'unpaid' AND due_date < CURDATE() THEN jumlah ELSE 0 END), 0) AS total_overdue,
            COUNT(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN 1 END) AS total_invoice_bulan_ini
         FROM invoices
         WHERE tenant_id = ? AND status != 'cancelled'"
    );
    $stmt->execute([$bulanIni, $bulanIni, $tenantId]);
    $invoiceSummary = $stmt->fetch() ?: [];

    // Total payer aktif
    $stmt = self::db()->prepare(
        "SELECT COUNT(*) FROM payers WHERE tenant_id = ? AND status = 'active'"
    );
    $stmt->execute([$tenantId]);
    $totalPayer = (int) $stmt->fetchColumn();

    return [
        'total_payer'              => $totalPayer,
        'total_invoice_bulan_ini'  => (int) ($invoiceSummary['total_invoice_bulan_ini'] ?? 0),
        'total_paid_bulan_ini'     => (float) ($invoiceSummary['total_paid_bulan_ini'] ?? 0),
        'total_unpaid'             => (float) ($invoiceSummary['total_unpaid'] ?? 0),
        'total_overdue'            => (float) ($invoiceSummary['total_overdue'] ?? 0),
    ];
}

/**
 * Laporan transaksi pada tanggal tertentu.
 */
public static function laporanHarian(int $tenantId, string $tanggal): array
{
    $stmt = self::db()->prepare(
        "SELECT i.*, p.nomor_induk, p.nama AS payer_nama
         FROM invoices i
         INNER JOIN payers p ON p.id = i.payer_id
         WHERE i.tenant_id = ?
           AND DATE(i.paid_at) = ?
           AND i.status = 'paid'
         ORDER BY i.paid_at ASC"
    );
    $stmt->execute([$tenantId, $tanggal]);
    return $stmt->fetchAll();
}

/**
 * Laporan transaksi pada bulan tertentu (format: YYYY-MM).
 */
public static function laporanBulanan(int $tenantId, string $bulan): array
{
    $stmt = self::db()->prepare(
        "SELECT i.*, p.nomor_induk, p.nama AS payer_nama
         FROM invoices i
         INNER JOIN payers p ON p.id = i.payer_id
         WHERE i.tenant_id = ?
           AND DATE_FORMAT(i.paid_at, '%Y-%m') = ?
           AND i.status = 'paid'
         ORDER BY i.paid_at ASC"
    );
    $stmt->execute([$tenantId, $bulan]);
    return $stmt->fetchAll();
}

/**
 * Ringkasan laporan: total transaksi, total nominal, per jenis, per metode.
 */
public static function ringkasanLaporan(int $tenantId, string $startDate, string $endDate): array
{
    // Total keseluruhan
    $stmt = self::db()->prepare(
        "SELECT
            COUNT(*) AS total_transaksi,
            COALESCE(SUM(jumlah), 0) AS total_nominal,
            COUNT(DISTINCT payer_id) AS total_payer
         FROM invoices
         WHERE tenant_id = ?
           AND status = 'paid'
           AND DATE(paid_at) BETWEEN ? AND ?"
    );
    $stmt->execute([$tenantId, $startDate, $endDate]);
    $total = $stmt->fetch() ?: [];

    // Per jenis
    $stmt = self::db()->prepare(
        "SELECT jenis,
                COUNT(*) AS jumlah_transaksi,
                COALESCE(SUM(jumlah), 0) AS total
         FROM invoices
         WHERE tenant_id = ?
           AND status = 'paid'
           AND DATE(paid_at) BETWEEN ? AND ?
         GROUP BY jenis
         ORDER BY total DESC"
    );
    $stmt->execute([$tenantId, $startDate, $endDate]);
    $perJenis = $stmt->fetchAll();

    return [
        'total'     => $total,
        'per_jenis' => $perJenis,
    ];
}

/**
 * Daftar periode yang punya transaksi (untuk dropdown).
 */
public static function listBulanTransaksi(int $tenantId): array
{
    $stmt = self::db()->prepare(
        "SELECT DISTINCT DATE_FORMAT(paid_at, '%Y-%m') AS bulan
         FROM invoices
         WHERE tenant_id = ? AND status = 'paid' AND paid_at IS NOT NULL
         ORDER BY bulan DESC"
    );
    $stmt->execute([$tenantId]);
    return array_column($stmt->fetchAll(), 'bulan');
}

/**
 * Ambil riwayat pembayaran (paid) untuk payer tertentu.
 */
public static function riwayatByPayer(int $tenantId, int $payerId, int $page = 1, int $perPage = 20): array
{
    $offset = ($page - 1) * $perPage;

    $stmt = self::db()->prepare(
        "SELECT i.*, p.nomor_induk, p.nama AS payer_nama
         FROM invoices i
         INNER JOIN payers p ON p.id = i.payer_id
         WHERE i.tenant_id = ?
           AND i.payer_id = ?
           AND i.status = 'paid'
         ORDER BY i.paid_at DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bindValue(1, $tenantId, \PDO::PARAM_INT);
    $stmt->bindValue(2, $payerId, \PDO::PARAM_INT);
    $stmt->bindValue(3, $perPage, \PDO::PARAM_INT);
    $stmt->bindValue(4, $offset, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Hitung total riwayat + total nominal.
 */
public static function summaryRiwayat(int $tenantId, int $payerId): array
{
    $stmt = self::db()->prepare(
        "SELECT
            COUNT(*) AS total_transaksi,
            COALESCE(SUM(jumlah), 0) AS total_nominal
         FROM invoices
         WHERE tenant_id = ? AND payer_id = ? AND status = 'paid'"
    );
    $stmt->execute([$tenantId, $payerId]);
    return $stmt->fetch() ?: ['total_transaksi' => 0, 'total_nominal' => 0];
}

/**
 * Hitung jumlah riwayat (untuk pagination).
 */
public static function countRiwayatByPayer(int $tenantId, int $payerId): int
{
    $stmt = self::db()->prepare(
        "SELECT COUNT(*) FROM invoices
         WHERE tenant_id = ? AND payer_id = ? AND status = 'paid'"
    );
    $stmt->execute([$tenantId, $payerId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Rekap per payer: baris = payer, kolom = total per bulan.
 */
public static function rekapPerPayer(
    int $tenantId,
    string $startDate,
    string $endDate
): array {
    // 1. Ambil semua bulan yang ada invoice
    $stmt = self::db()->prepare(
        "SELECT DISTINCT DATE_FORMAT(due_date, '%Y-%m') AS periode
         FROM invoices
         WHERE tenant_id = ? AND due_date BETWEEN ? AND ?
         ORDER BY periode ASC"
    );
    $stmt->execute([$tenantId, $startDate, $endDate]);
    $periodeList = array_column($stmt->fetchAll(), 'periode');

    // 2. Kalau tidak ada, generate dari rentang
    if (empty($periodeList)) {
        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);
        $end->modify('first day of next month');
        $period = new \DatePeriod($start, new \DateInterval('P1M'), $end);
        foreach ($period as $dt) {
            $periodeList[] = $dt->format('Y-m');
        }
    }

    // 3. Bangun query pivot
    $pivotCols = '';
    foreach ($periodeList as $p) {
        $safeP = preg_replace('/[^0-9\-]/', '', $p);
        $key   = 'periode_' . str_replace('-', '_', $p);
        $pivotCols .= ", COALESCE(SUM(CASE WHEN DATE_FORMAT(i.due_date, '%Y-%m') = '{$safeP}' THEN i.jumlah ELSE 0 END), 0) AS {$key}";
    }

    $sql = "SELECT
                p.id AS payer_id,
                p.nomor_induk,
                p.nama AS payer_nama,
                COALESCE(SUM(i.jumlah), 0) AS total_nominal
                {$pivotCols}
            FROM payers p
            LEFT JOIN invoices i ON i.payer_id = p.id
                AND i.tenant_id = p.tenant_id
                AND i.due_date BETWEEN ? AND ?
                AND i.status != 'cancelled'
            WHERE p.tenant_id = ? AND p.status = 'active'
            GROUP BY p.id, p.nomor_induk, p.nama
            ORDER BY p.nomor_induk ASC";

    $stmt = self::db()->prepare($sql);
    $stmt->execute([$startDate, $endDate, $tenantId]);
    $rows = $stmt->fetchAll();

    // 4. Hitung total per kolom
    $totals = ['total_nominal' => 0];
    foreach ($periodeList as $p) {
        $totals['periode_' . str_replace('-', '_', $p)] = 0;
    }
    foreach ($rows as $row) {
        $totals['total_nominal'] += (float) $row['total_nominal'];
        foreach ($periodeList as $p) {
            $key = 'periode_' . str_replace('-', '_', $p);
            $totals[$key] += (float) ($row[$key] ?? 0);
        }
    }

    return [
        'periode_list' => $periodeList,
        'rows'         => $rows,
        'totals'       => $totals,
    ];
}

/**
 * Detail per payer: breakdown per bulan × jenis.
 */
public static function detailPerPayer(
    int $tenantId,
    int $payerId,
    string $startDate,
    string $endDate
): array {
    $stmt = self::db()->prepare(
        "SELECT
            i.id, i.jenis, i.periode, i.jumlah, i.status, i.due_date, i.paid_at,
            DATE_FORMAT(i.due_date, '%Y-%m') AS bulan
         FROM invoices i
         WHERE i.tenant_id = ?
           AND i.payer_id = ?
           AND i.due_date BETWEEN ? AND ?
           AND i.status != 'cancelled'
         ORDER BY i.due_date ASC, i.jenis ASC"
    );
    $stmt->execute([$tenantId, $payerId, $startDate, $endDate]);
    $invoices = $stmt->fetchAll();

    // Group by bulan
    $grouped = [];
    foreach ($invoices as $inv) {
        $bulan = $inv['bulan'];
        if (!isset($grouped[$bulan])) {
            $grouped[$bulan] = [
                'bulan'  => $bulan,
                'label'  => date('F Y', strtotime($bulan . '-01')),
                'items'  => [],
                'total'  => 0,
                'lunas'  => 0,
                'unpaid' => 0,
            ];
        }
        $grouped[$bulan]['items'][] = $inv;
        $grouped[$bulan]['total']  += (float) $inv['jumlah'];
        if ($inv['status'] === 'paid') {
            $grouped[$bulan]['lunas'] += (float) $inv['jumlah'];
        } else {
            $grouped[$bulan]['unpaid'] += (float) $inv['jumlah'];
        }
    }

    return ['grouped' => array_values($grouped)];
}

/**
 * Summary total lunas & tunggakan per payer.
 */
public static function summaryPerPayer(int $tenantId, int $payerId): array
{
    $stmt = self::db()->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN status = 'paid' THEN jumlah ELSE 0 END), 0) AS total_lunas,
            COALESCE(SUM(CASE WHEN status = 'unpaid' THEN jumlah ELSE 0 END), 0) AS total_tunggakan,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) AS jumlah_lunas,
            COUNT(CASE WHEN status = 'unpaid' THEN 1 END) AS jumlah_tunggakan
         FROM invoices
         WHERE tenant_id = ? AND payer_id = ? AND status != 'cancelled'"
    );
    $stmt->execute([$tenantId, $payerId]);
    return $stmt->fetch() ?: [];
}

/**
 * Summary tunggakan untuk dashboard.
 */
public static function summaryTunggakan(int $tenantId): array
{
    $stmt = self::db()->prepare(
        "SELECT
            COUNT(*) AS total_invoice,
            COUNT(DISTINCT payer_id) AS total_payer,
            COALESCE(SUM(jumlah), 0) AS total_nominal,
            COALESCE(SUM(CASE WHEN due_date < CURDATE() THEN jumlah ELSE 0 END), 0) AS total_overdue,
            COUNT(CASE WHEN due_date < CURDATE() THEN 1 END) AS jumlah_overdue,
            COALESCE(SUM(CASE WHEN due_date >= CURDATE() AND due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN jumlah ELSE 0 END), 0) AS total_due_soon
         FROM invoices
         WHERE tenant_id = ? AND status = 'unpaid'"
    );
    $stmt->execute([$tenantId]);
    return $stmt->fetch() ?: [];
}

/**
 * Summary saldo aplikasi (dari Xendit payment).
 */
public static function summarySaldoAplikasi(int $tenantId): array
{
    $stmt = self::db()->prepare(
        "SELECT
            COUNT(*) AS total_transaksi,
            COALESCE(SUM(gross_amount), 0) AS total_gross,
            COALESCE(SUM(platform_fee), 0) AS total_fee,
            COALESCE(SUM(tenant_amount), 0) AS total_tenant
         FROM payments
         WHERE tenant_id = ?
           AND status IN ('settlement', 'capture')"
    );
    $stmt->execute([$tenantId]);
    return $stmt->fetch() ?: [];
}

/**
 * Bulk cancel multiple invoices.
 */
public static function bulkCancel(array $invoiceIds, int $tenantId, string $reason = ''): int
{
    if (empty($invoiceIds)) {
        return 0;
    }

    $db = self::db();
    $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));

    $stmt = $db->prepare(
        "UPDATE invoices
         SET status = 'cancelled', cancel_reason = ?, cancelled_at = NOW()
         WHERE id IN ($placeholders) AND tenant_id = ? AND status = 'unpaid'"
    );

    $params = array_merge([$reason], $invoiceIds, [$tenantId]);
    $stmt->execute($params);

    return $stmt->rowCount();
}

/**
 * Ambil invoice untuk bulk reminder (yang unpaid).
 */
public static function getForReminder(array $invoiceIds, int $tenantId): array
{
    if (empty($invoiceIds)) {
        return [];
    }

    $db = self::db();
    $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));

    $stmt = $db->prepare(
        "SELECT i.*,
                p.nama AS payer_nama, p.nomor_induk, p.whatsapp AS payer_whatsapp
         FROM invoices i
         INNER JOIN payers p ON p.id = i.payer_id
         WHERE i.id IN ($placeholders)
           AND i.tenant_id = ?
           AND i.status = 'unpaid'"
    );

    $params = array_merge($invoiceIds, [$tenantId]);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Top tunggakan untuk dashboard.
 */
public static function topTunggakan(int $tenantId, int $limit = 5): array
{
    $stmt = self::db()->prepare(
        "SELECT i.*,
                p.nomor_induk, p.nama AS payer_nama,
                DATEDIFF(CURDATE(), i.due_date) AS hari_telat
         FROM invoices i
         INNER JOIN payers p ON p.id = i.payer_id
         WHERE i.tenant_id = ?
           AND i.status = 'unpaid'
           AND p.status = 'active'
         ORDER BY i.due_date ASC
         LIMIT ?"
    );
    $stmt->bindValue(1, $tenantId, \PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Update paid_amount invoice setelah pembayaran.
 * Return: ['fully_paid' => bool, 'remaining' => float]
 */
public static function updatePaidAmount(int $invoiceId, float $additionalAmount): array
{
    $invoice = self::findById($invoiceId);
    if (!$invoice) {
        return ['fully_paid' => false, 'remaining' => 0];
    }

    $total    = (float) $invoice['jumlah'];
    $current  = (float) $invoice['paid_amount'];
    $newPaid  = $current + $additionalAmount;

    if ($newPaid >= $total) {
        // Lunas
        $stmt = self::db()->prepare(
            "UPDATE invoices SET paid_amount = ?, status = 'paid', paid_at = NOW() WHERE id = ?"
        );
        $stmt->execute([$total, $invoiceId]);

        return ['fully_paid' => true, 'remaining' => 0];
    }

    // Sebagian — status tetap unpaid
    $stmt = self::db()->prepare(
        "UPDATE invoices SET paid_amount = ? WHERE id = ?"
    );
    $stmt->execute([$newPaid, $invoiceId]);

    return ['fully_paid' => false, 'remaining' => $total - $newPaid];
}

/**
 * Sisa tagihan yang belum dibayar.
 */
public static function getRemaining(int $invoiceId): float
{
    $invoice = self::findById($invoiceId);
    if (!$invoice) return 0;

    return max(0, (float) $invoice['jumlah'] - (float) $invoice['paid_amount']);
}

/**
 * Riwayat pembayaran manual untuk satu invoice.
 */
public static function paymentHistory(int $invoiceId): array
{
    $stmt = self::db()->prepare(
        "SELECT mp.*, u.nama AS created_by_nama
         FROM manual_payments mp
         LEFT JOIN users u ON u.id = mp.created_by
         WHERE mp.invoice_id = ?
         ORDER BY mp.paid_at ASC"
    );
    $stmt->execute([$invoiceId]);
    return $stmt->fetchAll();
}

/**
 * Chart: pendapatan 30 hari terakhir.
 */
public static function chartPendapatan30Hari(int $tenantId): array
{
    $stmt = self::db()->prepare(
        "SELECT DATE(paid_at) AS tanggal,
                COALESCE(SUM(jumlah), 0) AS total
         FROM invoices
         WHERE tenant_id = ?
           AND status = 'paid'
           AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY DATE(paid_at)
         ORDER BY tanggal ASC"
    );
    $stmt->execute([$tenantId]);
    $raw = $stmt->fetchAll();

    $dataByDate = [];
    foreach ($raw as $r) {
        $dataByDate[$r['tanggal']] = (float) $r['total'];
    }

    $result = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $result[] = [
            'tanggal' => date('d M', strtotime($date)),
            'total'   => $dataByDate[$date] ?? 0,
        ];
    }

    return $result;
}

/**
 * Chart: status tagihan.
 */
public static function chartStatusTagihan(int $tenantId): array
{
    $stmt = self::db()->prepare(
        "SELECT
            COUNT(CASE WHEN status = 'paid' THEN 1 END) AS lunas,
            COUNT(CASE WHEN status = 'unpaid' AND COALESCE(paid_amount, 0) > 0 THEN 1 END) AS sebagian,
            COUNT(CASE WHEN status = 'unpaid' AND COALESCE(paid_amount, 0) = 0 THEN 1 END) AS belum
         FROM invoices
         WHERE tenant_id = ? AND status != 'cancelled'"
    );
    $stmt->execute([$tenantId]);
    return $stmt->fetch() ?: ['lunas' => 0, 'sebagian' => 0, 'belum' => 0];
}

/**
 * Chart: per jenis tagihan.
 */
public static function chartPerJenis(int $tenantId): array
{
    $stmt = self::db()->prepare(
        "SELECT jenis,
                COUNT(*) AS jumlah_transaksi,
                COALESCE(SUM(jumlah), 0) AS total
         FROM invoices
         WHERE tenant_id = ? AND status != 'cancelled'
         GROUP BY jenis
         ORDER BY total DESC
         LIMIT 6"
    );
    $stmt->execute([$tenantId]);
    return $stmt->fetchAll();
}

/**
 * Status pembayaran per pelanggan.
 */
public static function statusPerPayer(int $tenantId, string $periode = '', string $search = ''): array
{
    $sql = "SELECT
                p.id AS payer_id,
                p.nomor_induk,
                p.nama AS payer_nama,
                COUNT(i.id) AS total_invoice,
                COALESCE(SUM(i.jumlah), 0) AS total_tagihan,
                COALESCE(SUM(i.paid_amount), 0) AS total_dibayar,
                COALESCE(SUM(i.jumlah - COALESCE(i.paid_amount, 0)), 0) AS total_sisa,
                COUNT(CASE WHEN i.status = 'paid' THEN 1 END) AS jml_lunas,
                COUNT(CASE WHEN i.status = 'unpaid' AND COALESCE(i.paid_amount, 0) > 0 THEN 1 END) AS jml_sebagian,
                COUNT(CASE WHEN i.status = 'unpaid' AND COALESCE(i.paid_amount, 0) = 0 THEN 1 END) AS jml_belum
            FROM payers p
            LEFT JOIN invoices i ON i.payer_id = p.id
                AND i.status != 'cancelled'";

    $params = [];

    if ($periode !== '') {
        $sql .= " AND i.periode = ?";
        $params[] = $periode;
    }

    $sql .= " WHERE p.tenant_id = ? AND p.status = 'active'";
    $params[] = $tenantId;

    if ($search !== '') {
        $sql .= " AND (p.nama LIKE ? OR p.nomor_induk LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " GROUP BY p.id, p.nomor_induk, p.nama
              ORDER BY total_sisa DESC, p.nomor_induk ASC";

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Laporan per pelanggan (matrix, kolom = periode).
 * Return: ['periode_list' => [...], 'rows' => [...], 'totals' => [...]]
 */
public static function laporanPerPayerMatrix(
    int $tenantId,
    string $startDate,
    string $endDate,
    string $status = 'all'
): array {
    // 1. Ambil semua periode (bulan) yang ada di tenant dalam rentang
    $stmt = self::db()->prepare(
        "SELECT DISTINCT DATE_FORMAT(due_date, '%Y-%m') AS periode
         FROM invoices
         WHERE tenant_id = ?
           AND due_date BETWEEN ? AND ?"
        . ($status !== 'all' ? " AND status = ?" : "")
        . " ORDER BY periode ASC"
    );
    $params = [$tenantId, $startDate, $endDate];
    if ($status !== 'all') {
        $params[] = $status;
    }
    $stmt->execute($params);
    $periodeList = array_column($stmt->fetchAll(), 'periode');

    // Kalau tidak ada periode, generate dari rentang
    if (empty($periodeList)) {
        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);
        $end->modify('first day of next month');
        $interval = new \DateInterval('P1M');
        $period = new \DatePeriod($start, $interval, $end);
        foreach ($period as $dt) {
            $periodeList[] = $dt->format('Y-m');
        }
    }

    // 2. Bangun query pivot dinamis
    $pivotCols = '';
    foreach ($periodeList as $p) {
        $safeP = preg_replace('/[^0-9\-]/', '', $p);
        $key   = 'periode_' . str_replace('-', '_', $p);
        $pivotCols .= ", COALESCE(SUM(CASE WHEN DATE_FORMAT(i.due_date, '%Y-%m') = '{$safeP}' THEN i.jumlah ELSE 0 END), 0) AS {$key}";
    }

    $sql = "SELECT
                p.id AS payer_id,
                p.nomor_induk,
                p.nama AS payer_nama,
                COALESCE(SUM(i.jumlah), 0) AS total_nominal
                {$pivotCols}
            FROM payers p
            LEFT JOIN invoices i ON i.payer_id = p.id
                AND i.tenant_id = p.tenant_id
                AND i.due_date BETWEEN ? AND ?
                AND i.status != 'cancelled'";

    $params = [$startDate, $endDate];

    if ($status !== 'all') {
        $sql .= " AND i.status = ?";
        $params[] = $status;
    }

    $sql .= " WHERE p.tenant_id = ? AND p.status = 'active'
              GROUP BY p.id, p.nomor_induk, p.nama
              ORDER BY p.nomor_induk ASC";

    $params[] = $tenantId;

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // 3. Hitung total per kolom
    $totals = ['total_nominal' => 0];
    foreach ($periodeList as $p) {
        $key = 'periode_' . str_replace('-', '_', $p);
        $totals[$key] = 0;
    }

    foreach ($rows as $row) {
        $totals['total_nominal'] += (float) $row['total_nominal'];
        foreach ($periodeList as $p) {
            $key = 'periode_' . str_replace('-', '_', $p);
            $totals[$key] += (float) ($row[$key] ?? 0);
        }
    }

    return [
        'periode_list' => $periodeList,
        'rows'         => $rows,
        'totals'       => $totals,
    ];
}

/**
 * Ambil semua tagihan milik pelanggan (semua status).
 */
public static function allByPayer(int $tenantId, int $payerId): array
{
    $stmt = self::db()->prepare(
        "SELECT *,
                (jumlah - COALESCE(paid_amount, 0)) AS remaining
         FROM invoices
         WHERE tenant_id = ? AND payer_id = ?
         ORDER BY 
            CASE status
                WHEN 'unpaid' THEN 1
                WHEN 'paid' THEN 2
                WHEN 'cancelled' THEN 3
                ELSE 4
            END,
            due_date DESC"
    );
    $stmt->execute([$tenantId, $payerId]);
    return $stmt->fetchAll();
}

/**
 * Summary tagihan pelanggan.
 */
public static function summaryByPayer(int $tenantId, int $payerId): array
{
    $stmt = self::db()->prepare(
        "SELECT
            COUNT(*) AS total_invoice,
            COALESCE(SUM(jumlah), 0) AS total_tagihan,
            COALESCE(SUM(paid_amount), 0) AS total_dibayar,
            COALESCE(SUM(jumlah - COALESCE(paid_amount, 0)), 0) AS total_sisa,
            COUNT(CASE WHEN status = 'paid' THEN 1 END) AS jml_lunas,
            COUNT(CASE WHEN status = 'unpaid' AND COALESCE(paid_amount, 0) > 0 THEN 1 END) AS jml_sebagian,
            COUNT(CASE WHEN status = 'unpaid' AND COALESCE(paid_amount, 0) = 0 THEN 1 END) AS jml_belum,
            COUNT(CASE WHEN status = 'cancelled' THEN 1 END) AS jml_cancelled
         FROM invoices
         WHERE tenant_id = ? AND payer_id = ?"
    );
    $stmt->execute([$tenantId, $payerId]);
    return $stmt->fetch() ?: [];
}
}