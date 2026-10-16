<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Invoice;

class DashboardController
{
    private function tenantId(): ?int
    {
        return $_SESSION['tenant_id'] ?? null;
    }

    /**
     * Dashboard utama — tampil beda per role.
     */
    public function index(): void
    {
        $tenantId = $this->tenantId();
        $role     = $_SESSION['user_role'] ?? null;

        $summary             = [];
        $saldoAplikasi       = [];
        $saldoManual         = [];
        $tunggakanList       = [];
        $tenantList          = [];
        $subscriptionRevenue = null;
        $transaksiList       = [];
        $trxPage             = 1;
        $trxLastPage         = 1;
        $trxTotal            = 0;

        if ($role === 'admin' && $tenantId) {
            $summary       = Invoice::summaryDashboard($tenantId);
            $saldoAplikasi = Invoice::summarySaldoAplikasi($tenantId);
            $saldoManual   = \App\Models\ManualPayment::summaryByTenant($tenantId);
            $tunggakanList = Invoice::topTunggakan($tenantId, 5);

            // ===== Detail Transaksi (10 terbaru, pagination) =====
            $trxPage = max(1, (int) ($_GET['page'] ?? 1));
            $perPage = 10;
            $offset  = ($trxPage - 1) * $perPage;

            $db = \App\Models\BaseModel::db();

            $stmt = $db->prepare(
                "SELECT COUNT(*)
                FROM invoices
                WHERE tenant_id = ? AND status = 'paid'"
            );
            $stmt->execute([$tenantId]);
            $trxTotal = (int) $stmt->fetchColumn();
            $trxLastPage = max(1, (int) ceil($trxTotal / $perPage));

            $stmt = $db->prepare(
                "SELECT i.*, p.nomor_induk, p.nama AS payer_nama
                FROM invoices i
                INNER JOIN payers p ON p.id = i.payer_id
                WHERE i.tenant_id = ? AND i.status = 'paid'
                ORDER BY i.paid_at DESC, i.id DESC
                LIMIT ? OFFSET ?"
            );
            $stmt->bindValue(1, $tenantId, \PDO::PARAM_INT);
            $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
            $stmt->execute();
            $transaksiList = $stmt->fetchAll();

        } elseif ($role === 'superadmin') {
            $summary             = $this->superadminSummary();
            $tenantList          = $this->superadminTenantTerbaru(5);
            $subscriptionRevenue = \App\Controllers\SuperadminSubscriptionController::getRevenueSummary();
        }

        Response::view('admin/dashboard', [
            'title'               => 'Dashboard',
            'summary'             => $summary,
            'saldoAplikasi'       => $saldoAplikasi,
            'saldoManual'         => $saldoManual,
            'tunggakanList'       => $tunggakanList,
            'tenantList'          => $tenantList,
            'subscriptionRevenue' => $subscriptionRevenue,
            'transaksiList'       => $transaksiList,
            'trxPage'             => $trxPage,
            'trxLastPage'         => $trxLastPage,
            'trxTotal'            => $trxTotal,
            'user'                => [
                'id'   => $_SESSION['user_id']   ?? null,
                'nama' => $_SESSION['user_name'] ?? 'Guest',
                'role' => $role,
            ],
        ]);
    }

    /**
     * Dashboard tunggakan (halaman terpisah).
     */
    public function tunggakan(): void
    {
        $tenantId = $this->tenantId();
        if (!$tenantId) {
            $_SESSION['flash_error'] = 'Akun Anda tidak terhubung ke tenant.';
            Response::redirect('/dashboard');
        }

        $filter  = trim($_GET['filter'] ?? 'all');
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $total    = Invoice::countTunggakanByTenant($tenantId, $filter);
        $items    = Invoice::tunggakanByTenant($tenantId, $filter, $page, $perPage);
        $summary  = Invoice::summaryTunggakan($tenantId);
        $lastPage = max(1, (int) ceil($total / $perPage));

        Response::view('admin/tunggakan', [
            'title'    => 'Tunggakan',
            'items'    => $items,
            'summary'  => $summary,
            'filter'   => $filter,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ]);
    }

    /**
     * Summary global untuk superadmin.
     * Model bisnis: SEWA — pendapatan platform dari langganan, bukan komisi transaksi.
     */
    private function superadminSummary(): array
    {
        $db = \App\Models\BaseModel::db();

        // ============ TENANT ============
        $stmt = $db->query("SELECT COUNT(*) FROM tenants");
        $totalTenant = (int) $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM tenants WHERE status = 'active'");
        $totalTenantAktif = (int) $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM tenants WHERE status = 'suspended'");
        $totalTenantSuspend = (int) $stmt->fetchColumn();

        // ============ PELANGGAN ============
        $stmt = $db->query("SELECT COUNT(*) FROM payers WHERE status = 'active'");
        $totalPayer = (int) $stmt->fetchColumn();

        // ============ TAGIHAN BULAN INI ============
        $bulanIni = date('Y-m');
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM invoices
             WHERE DATE_FORMAT(created_at, '%Y-%m') = ? AND status != 'cancelled'"
        );
        $stmt->execute([$bulanIni]);
        $totalInvoiceBulanIni = (int) $stmt->fetchColumn();

        // ============ GMV (Gross Merchandise Volume) ============
        // Total nilai transaksi yang diproses melalui platform (bukan pendapatan platform).
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(gross_amount), 0) FROM payments
             WHERE status IN ('settlement', 'capture')
               AND DATE_FORMAT(paid_at, '%Y-%m') = ?"
        );
        $stmt->execute([$bulanIni]);
        $totalGmvBulanIni = (float) $stmt->fetchColumn();

        $stmt = $db->query(
            "SELECT COALESCE(SUM(gross_amount), 0) FROM payments
             WHERE status IN ('settlement', 'capture')"
        );
        $totalGmvAllTime = (float) $stmt->fetchColumn();

        // ============ PENDAPATAN PLATFORM (SEWA) ============
        // Pendapatan bulan ini
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM subscription_payments
             WHERE status = 'paid'
               AND DATE_FORMAT(paid_at, '%Y-%m') = ?"
        );
        $stmt->execute([$bulanIni]);
        $totalSewaBulanIni = (float) $stmt->fetchColumn();

        // Pendapatan bulan lalu
        $bulanLalu = date('Y-m', strtotime('-1 month'));
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0) FROM subscription_payments
             WHERE status = 'paid'
               AND DATE_FORMAT(paid_at, '%Y-%m') = ?"
        );
        $stmt->execute([$bulanLalu]);
        $totalSewaBulanLalu = (float) $stmt->fetchColumn();

        // Total sepanjang waktu
        $stmt = $db->query(
            "SELECT COALESCE(SUM(amount), 0) FROM subscription_payments
             WHERE status = 'paid'"
        );
        $totalSewaAllTime = (float) $stmt->fetchColumn();

        // Growth %
        $growthPercent = 0;
        if ($totalSewaBulanLalu > 0) {
            $growthPercent = (($totalSewaBulanIni - $totalSewaBulanLalu) / $totalSewaBulanLalu) * 100;
        }

        // ============ STATUS LANGGANAN ============
        $stmt = $db->query(
            "SELECT COUNT(DISTINCT tenant_id) FROM tenant_subscriptions
             WHERE status = 'active' AND end_date >= CURDATE()"
        );
        $tenantBerlangganan = (int) $stmt->fetchColumn();

        $stmt = $db->query(
            "SELECT COUNT(DISTINCT tenant_id) FROM tenant_subscriptions
             WHERE status = 'active'
               AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
        $tenantExpiringSoon = (int) $stmt->fetchColumn();

        // ============ REKENING ============
        $stmt = $db->query("SELECT COUNT(*) FROM tenant_bank_accounts WHERE status = 'pending'");
        $rekeningPending = (int) $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM tenant_bank_accounts WHERE status = 'verified'");
        $rekeningVerified = (int) $stmt->fetchColumn();

        return [
            // Tenant
            'total_tenant'            => $totalTenant,
            'total_tenant_aktif'      => $totalTenantAktif,
            'total_tenant_suspend'    => $totalTenantSuspend,

            // Pelanggan
            'total_payer'             => $totalPayer,

            // Tagihan
            'total_invoice_bulan_ini' => $totalInvoiceBulanIni,

            // GMV (volume transaksi)
            'total_gmv_bulan_ini'     => $totalGmvBulanIni,
            'total_gmv_all_time'      => $totalGmvAllTime,

            // Pendapatan platform dari sewa
            'total_sewa_bulan_ini'    => $totalSewaBulanIni,
            'total_sewa_bulan_lalu'   => $totalSewaBulanLalu,
            'total_sewa_all_time'     => $totalSewaAllTime,
            'growth_percent'          => round($growthPercent, 1),

            // Status langganan
            'tenant_berlangganan'     => $tenantBerlangganan,
            'tenant_expiring_soon'    => $tenantExpiringSoon,

            // Rekening
            'rekening_pending'        => $rekeningPending,
            'rekening_verified'       => $rekeningVerified,
        ];
    }

    /**
     * List tenant terbaru untuk superadmin.
     */
    private function superadminTenantTerbaru(int $limit = 5): array
    {
        $db = \App\Models\BaseModel::db();
        $stmt = $db->prepare(
            "SELECT t.id, t.nama, t.slug, t.status, t.created_at,
                    (SELECT COUNT(*) FROM payers p WHERE p.tenant_id = t.id) AS total_payers,
                    (SELECT COUNT(*) FROM invoices i WHERE i.tenant_id = t.id AND i.status != 'cancelled') AS total_invoices
             FROM tenants t
             ORDER BY t.created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}