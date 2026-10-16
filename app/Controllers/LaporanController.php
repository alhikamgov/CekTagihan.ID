<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\ExcelExportService;

class LaporanController
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
     * Halaman utama laporan (bulanan + chart).
     */
    public function index(): void
    {
        $tenantId = $this->tenantId();

        $bulan = trim($_GET['bulan'] ?? date('Y-m'));

        $rows    = Invoice::laporanBulanan($tenantId, $bulan);
        $summary = Invoice::ringkasanLaporan(
            $tenantId,
            $bulan . '-01',
            date('Y-m-t', strtotime($bulan . '-01'))
        );

        $bulanList = Invoice::listBulanTransaksi($tenantId);

        // Data chart
        $chartPendapatan = Invoice::chartPendapatan30Hari($tenantId);
        $chartStatus     = Invoice::chartStatusTagihan($tenantId);
        $chartJenis      = Invoice::chartPerJenis($tenantId);

        Response::view('admin/laporan/index', [
            'title'           => 'Laporan',
            'bulan'           => $bulan,
            'bulanList'       => $bulanList,
            'rows'            => $rows,
            'summary'         => $summary,
            'chartPendapatan' => $chartPendapatan,
            'chartStatus'     => $chartStatus,
            'chartJenis'      => $chartJenis,
        ]);
    }

    /**
     * Laporan status per pelanggan.
     */
    public function statusPelanggan(): void
{
    $tenantId = $this->tenantId();

    $periode = trim($_GET['periode'] ?? '');
    $status  = trim($_GET['status'] ?? '');
    $search  = trim($_GET['q'] ?? '');

    $rows = Invoice::statusPerPayer($tenantId, $periode, $search);

    // Filter by status
    if ($status === 'belum') {
        $rows = array_filter($rows, fn($r) => (float) $r['total_sisa'] > 0);
    } elseif ($status === 'lunas') {
        $rows = array_filter($rows, fn($r) => (float) $r['total_sisa'] <= 0 && (int) $r['total_invoice'] > 0);
    }

    $periodes = Invoice::listPeriodeByTenant($tenantId);

    Response::view('admin/laporan/status-pelanggan', [
        'title'    => 'Status Pembayaran Pelanggan',
        'rows'     => array_values($rows),
        'periodes' => $periodes,
        'periode'  => $periode,
        'status'   => $status,
        'search'   => $search,
    ]);
}

    /**
     * Laporan per pelanggan (horizontal, matrix).
     */
    public function perPayer(): void
    {
        $tenantId = $this->tenantId();

        // Default: 3 bulan terakhir
        $endBulan   = $_GET['end']   ?? date('Y-m');
        $startBulan = $_GET['start'] ?? date('Y-m', strtotime('-2 months', strtotime($endBulan . '-01')));
        $status     = $_GET['status'] ?? 'all';

        $startDate = $startBulan . '-01';
        $endDate   = date('Y-m-t', strtotime($endBulan . '-01'));

        $data = Invoice::laporanPerPayerMatrix($tenantId, $startDate, $endDate, $status);

        Response::view('admin/laporan/per-payer', [
            'title'       => 'Laporan Per Pelanggan',
            'startBulan'  => $startBulan,
            'endBulan'    => $endBulan,
            'status'      => $status,
            'periodeList' => $data['periode_list'],
            'rows'        => $data['rows'],
            'totals'      => $data['totals'],
        ]);
    }

    /**
     * Export laporan bulanan ke Excel.
     */
    public function export(): void
    {
        $tenantId = $this->tenantId();

        $bulan = trim($_GET['bulan'] ?? date('Y-m'));

        $rows = Invoice::laporanBulanan($tenantId, $bulan);
        if (empty($rows)) {
            $_SESSION['flash_error'] = 'Tidak ada transaksi pada periode ini.';
            Response::redirect('/admin/laporan?bulan=' . urlencode($bulan));
        }

        $summary = Invoice::ringkasanLaporan(
            $tenantId,
            $bulan . '-01',
            date('Y-m-t', strtotime($bulan . '-01'))
        );

        $tenant = Tenant::findById($tenantId);
        $tenantNama = $tenant['nama'] ?? 'Tenant';

        $periodeLabel = date('F Y', strtotime($bulan . '-01'));

        $filePath = ExcelExportService::exportLaporan($rows, $summary['total'], $periodeLabel, $tenantNama);

        $filename = 'Laporan_' . str_replace(' ', '_', $periodeLabel) . '_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');

        readfile($filePath);
        @unlink($filePath);
        exit;
    }

    /**
     * Export laporan per pelanggan ke Excel.
     */
    public function exportPerPayer(): void
    {
        $tenantId = $this->tenantId();

        $endBulan   = $_GET['end']   ?? date('Y-m');
        $startBulan = $_GET['start'] ?? date('Y-m', strtotime('-2 months', strtotime($endBulan . '-01')));
        $status     = $_GET['status'] ?? 'all';

        $startDate = $startBulan . '-01';
        $endDate   = date('Y-m-t', strtotime($endBulan . '-01'));

        $data = Invoice::laporanPerPayerMatrix($tenantId, $startDate, $endDate, $status);

        if (empty($data['rows'])) {
            $_SESSION['flash_error'] = 'Tidak ada data untuk periode ini.';
            Response::redirect('/admin/laporan/per-payer');
        }

        $tenant = Tenant::findById($tenantId);
        $tenantNama = $tenant['nama'] ?? 'Tenant';

        $periodeLabel = date('M Y', strtotime($startBulan . '-01'))
                      . ' - ' . date('M Y', strtotime($endBulan . '-01'));

        $filePath = ExcelExportService::exportLaporanPerPayerMatrix(
            $data['rows'],
            $data['periode_list'],
            $data['totals'],
            $periodeLabel,
            $tenantNama,
            $status
        );

        $filename = 'Laporan_Per_Pelanggan_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=0');

        readfile($filePath);
        @unlink($filePath);
        exit;
    }

    /**
 * Detail semua tagihan pelanggan.
 */
public function detailPelanggan(string $payerId): void
{
    $tenantId = $this->tenantId();
    $payerId  = (int) $payerId;

    $payer = \App\Models\Payer::findByIdAndTenant($payerId, $tenantId);
    if (!$payer) {
        $_SESSION['flash_error'] = 'Pelanggan tidak ditemukan.';
        Response::redirect('/admin/laporan/status-pelanggan');
    }

    $invoices = Invoice::allByPayer($tenantId, $payerId);
    $summary  = Invoice::summaryByPayer($tenantId, $payerId);

    Response::view('admin/laporan/status-pelanggan-detail', [
        'title'    => 'Detail Pelanggan — ' . $payer['nama'],
        'payer'    => $payer,
        'invoices' => $invoices,
        'summary'  => $summary,
    ]);
}
}