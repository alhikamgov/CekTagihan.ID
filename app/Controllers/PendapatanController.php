<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Payment;

class PendapatanController
{
    private function tenantId(): int
    {
        $tid = $_SESSION['tenant_id'] ?? null;
        if (!$tid) {
            $_SESSION['flash_error'] = 'Akun Anda tidak terhubung ke tenant.';
            Response::redirect('/dashboard');
        }
        return (int) $tid;
    }

    /**
     * Halaman riwayat pendapatan.
     */
    public function index(): void
    {
        $tenantId = $this->tenantId();

        $bulan = trim($_GET['bulan'] ?? date('Y-m'));

        $db = Payment::db();

        // ========================================
        // PENDAPATAN ONLINE (via Xendit)
        // ========================================
        $stmt = $db->prepare(
            "SELECT p.*, py.nama AS payer_nama, py.nomor_induk
             FROM payments p
             INNER JOIN payers py ON py.id = p.payer_id
             WHERE p.tenant_id = ?
               AND p.status IN ('settlement', 'capture')
               AND DATE_FORMAT(p.paid_at, '%Y-%m') = ?
             ORDER BY p.paid_at DESC"
        );
        $stmt->execute([$tenantId, $bulan]);
        $online = $stmt->fetchAll();

        // ========================================
        // PENDAPATAN MANUAL
        // ========================================
        $stmt = $db->prepare(
            "SELECT mp.*, py.nama AS payer_nama, py.nomor_induk, i.jenis, i.periode
             FROM manual_payments mp
             INNER JOIN payers py ON py.id = mp.payer_id
             INNER JOIN invoices i ON i.id = mp.invoice_id
             WHERE mp.tenant_id = ?
               AND DATE_FORMAT(mp.paid_at, '%Y-%m') = ?
             ORDER BY mp.paid_at DESC"
        );
        $stmt->execute([$tenantId, $bulan]);
        $manual = $stmt->fetchAll();

        // ========================================
        // SUMMARY
        // ========================================
        $totalOnline = 0;
        $totalFee    = 0;
        $totalTenant = 0;
        foreach ($online as $o) {
            $totalOnline += (float) $o['gross_amount'];
            $totalFee    += (float) ($o['platform_fee'] ?? 0);
            $totalTenant += (float) ($o['tenant_amount'] ?? $o['gross_amount']);
        }

        $totalManual = array_sum(array_column($manual, 'jumlah'));

        // List bulan yang ada transaksi (untuk dropdown)
        $stmt = $db->prepare(
            "SELECT DISTINCT DATE_FORMAT(paid_at, '%Y-%m') AS bulan
             FROM payments
             WHERE tenant_id = ? AND status IN ('settlement', 'capture')
             UNION
             SELECT DISTINCT DATE_FORMAT(paid_at, '%Y-%m') AS bulan
             FROM manual_payments
             WHERE tenant_id = ?
             ORDER BY bulan DESC"
        );
        $stmt->execute([$tenantId, $tenantId]);
        $bulanList = array_column($stmt->fetchAll(), 'bulan');

        if (empty($bulanList)) {
            $bulanList = [date('Y-m')];
        }

        Response::view('admin/pendapatan/index', [
            'title'       => 'Riwayat Pendapatan',
            'bulan'       => $bulan,
            'bulanList'   => $bulanList,
            'online'      => $online,
            'manual'      => $manual,
            'totalOnline' => $totalOnline,
            'totalFee'    => $totalFee,
            'totalTenant' => $totalTenant,
            'totalManual' => $totalManual,
        ]);
    }
}