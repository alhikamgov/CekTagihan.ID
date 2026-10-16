<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Auth;
use App\Models\Withdrawal;

class SuperadminPencairanController
{
    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'q'      => trim($_GET['q'] ?? ''),
        ];

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $total   = Withdrawal::countAll($filters);
        $items   = Withdrawal::listAll($filters, $page, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));

        Response::view('superadmin/pencairan/index', [
            'title'    => 'Pengajuan Pencairan',
            'items'    => $items,
            'filters'  => $filters,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
            'csrf'     => Security::csrfToken(),
            'success'  => $_SESSION['pencairan_success'] ?? null,
            'error'    => $_SESSION['pencairan_error'] ?? null,
        ]);
        unset($_SESSION['pencairan_success'], $_SESSION['pencairan_error']);
    }

    public function show(string $id): void
    {
        $w = Withdrawal::findById((int) $id);
        if (!$w) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        Response::view('superadmin/pencairan/show', [
            'title' => 'Detail Pencairan',
            'w'     => $w,
            'csrf'  => Security::csrfToken(),
        ]);
    }

    public function approve(string $id): void
    {
        $this->review((int) $id, 'approved');
    }

    public function reject(string $id): void
    {
        $reason = trim($_POST['reject_reason'] ?? '');
        if ($reason === '') {
            $_SESSION['pencairan_error'] = 'Alasan penolakan wajib diisi.';
            Response::redirect('/superadmin/pencairan');
        }
        $this->review((int) $id, 'rejected', $reason);
    }

    public function markPaid(string $id): void
    {
        $withdrawalId = (int) $id;
        $userId = Auth::userId();

        $w = Withdrawal::findById($withdrawalId);
        if (!$w || $w['status'] !== 'approved') {
            $_SESSION['pencairan_error'] = 'Status tidak valid untuk ditandai lunas.';
            Response::redirect('/superadmin/pencairan');
        }

        // Handle upload bukti transfer
        if (!empty($_FILES['payment_proof']['tmp_name'])) {
            $dir = __DIR__ . '/../../public/uploads/pencairan';
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            $ext = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
            $filename = 'pencairan_' . $withdrawalId . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['payment_proof']['tmp_name'], $dir . '/' . $filename);
            Withdrawal::setProof($withdrawalId, $filename);
        }

        Withdrawal::updateStatus($withdrawalId, 'paid', $userId);
        $_SESSION['pencairan_success'] = 'Pencairan ditandai sudah dibayar.';
        Response::redirect('/superadmin/pencairan');
    }

    private function review(int $id, string $status, ?string $reason = null): void
    {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['pencairan_error'] = 'Sesi tidak valid.';
            Response::redirect('/superadmin/pencairan');
        }

        $w = Withdrawal::findById($id);
        if (!$w || $w['status'] !== 'pending') {
            $_SESSION['pencairan_error'] = 'Pengajuan tidak valid atau sudah diproses.';
            Response::redirect('/superadmin/pencairan');
        }

        Withdrawal::updateStatus($id, $status, Auth::userId(), $reason);
        $_SESSION['pencairan_success'] = 'Pengajuan ' . ($status === 'approved' ? 'disetujui' : 'ditolak') . '.';
        Response::redirect('/superadmin/pencairan');
    }

    /**
     * Ringkasan pendapatan dari langganan.
     * Dipakai di dashboard superadmin.
     */
    public static function getRevenueSummary(): array
    {
        $db = \App\Models\Tenant::db();

        // Total pendapatan langganan bulan ini
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
            FROM subscription_payments
            WHERE status = 'paid'
            AND YEAR(paid_at) = YEAR(CURDATE())
            AND MONTH(paid_at) = MONTH(CURDATE())"
        );
        $stmt->execute();
        $revenueThisMonth = (float) $stmt->fetchColumn();

        // Total pendapatan langganan bulan lalu
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
            FROM subscription_payments
            WHERE status = 'paid'
            AND YEAR(paid_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND MONTH(paid_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"
        );
        $stmt->execute();
        $revenueLastMonth = (float) $stmt->fetchColumn();

        // Growth %
        $growth = 0;
        if ($revenueLastMonth > 0) {
            $growth = (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100;
        }

        // Total tenant aktif berlangganan
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT tenant_id)
            FROM tenant_subscriptions
            WHERE status = 'active' AND end_date >= CURDATE()"
        );
        $stmt->execute();
        $activeTenants = (int) $stmt->fetchColumn();

        // Total tenant yang langganannya akan habis dalam 7 hari
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT tenant_id)
            FROM tenant_subscriptions
            WHERE status = 'active'
            AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
        $stmt->execute();
        $expiringSoon = (int) $stmt->fetchColumn();

        // Total tenant expired (lewat grace)
        $graceDays = (int) (\App\Models\PlatformSetting::get('subscription_grace_days') ?? 7);
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT t.id)
            FROM tenants t
            WHERE NOT EXISTS (
                SELECT 1 FROM tenant_subscriptions ts
                WHERE ts.tenant_id = t.id
                AND ts.status = 'active'
                AND ts.end_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            )"
        );
        $stmt->execute([$graceDays]);
        $expiredTenants = (int) $stmt->fetchColumn();

        // Total pendapatan sepanjang waktu
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
            FROM subscription_payments
            WHERE status = 'paid'"
        );
        $stmt->execute();
        $revenueAllTime = (float) $stmt->fetchColumn();

        return [
            'revenue_this_month'  => $revenueThisMonth,
            'revenue_last_month'  => $revenueLastMonth,
            'revenue_all_time'    => $revenueAllTime,
            'growth_percent'      => round($growth, 1),
            'active_tenants'      => $activeTenants,
            'expiring_soon'       => $expiringSoon,
            'expired_tenants'     => $expiredTenants,
        ];
    }
}