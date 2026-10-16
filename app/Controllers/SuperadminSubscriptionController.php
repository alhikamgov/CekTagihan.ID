<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Tenant;

class SuperadminSubscriptionController
{
    public function index(): void
    {
        $filter = $_GET['status'] ?? '';

        $sql = "SELECT t.id AS tenant_id, t.nama AS tenant_nama, t.slug,
                       ts.plan_name, ts.price, ts.start_date, ts.end_date, ts.status AS sub_status,
                       DATEDIFF(ts.end_date, CURDATE()) AS days_left
                FROM tenants t
                LEFT JOIN tenant_subscriptions ts
                  ON ts.tenant_id = t.id
                  AND ts.id = (
                      SELECT id FROM tenant_subscriptions
                      WHERE tenant_id = t.id
                      ORDER BY end_date DESC LIMIT 1
                  )
                WHERE 1=1";
        $params = [];

        if ($filter === 'active') {
            $sql .= " AND ts.status = 'active' AND ts.end_date >= CURDATE()";
        } elseif ($filter === 'expiring') {
            $sql .= " AND ts.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        } elseif ($filter === 'expired') {
            $sql .= " AND (ts.end_date < CURDATE() OR ts.id IS NULL)";
        }

        $sql .= " ORDER BY ts.end_date ASC, t.nama ASC";

        $stmt = Tenant::db()->prepare($sql);
        $stmt->execute($params);
        $tenants = $stmt->fetchAll();

        Response::view('superadmin/langganan/index', [
            'title'   => 'Kelola Langganan',
            'tenants' => $tenants,
            'filter'  => $filter,
            'csrf'    => Security::csrfToken(),
            'success' => $_SESSION['sub_success'] ?? null,
            'error'   => $_SESSION['sub_error'] ?? null,
        ]);
        unset($_SESSION['sub_success'], $_SESSION['sub_error']);
    }

    public function extend(string $tenantId): void
    {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['sub_error'] = 'Sesi tidak valid.';
            Response::redirect('/superadmin/langganan');
        }

        $tenantId = (int) $tenantId;
        $months   = max(1, (int) ($_POST['months'] ?? 1));
        $price    = (float) ($_POST['price'] ?? 0);
        $planName = trim($_POST['plan_name'] ?? 'manual');
        $note     = trim($_POST['note'] ?? '');

        try {
            $subId = Tenant::extendSubscription(
                $tenantId,
                $planName,
                $price,
                $months,
                Auth::userId()
            );

            // Catat sebagai subscription_payment (paid manual)
            $invoiceNumber = 'SUB-' . date('Ymd') . '-' . str_pad((string)$tenantId, 4, '0', STR_PAD_LEFT) . '-' . time();

            $stmt = Tenant::db()->prepare(
                "INSERT INTO subscription_payments
                 (tenant_id, subscription_id, invoice_number, amount, period_start, period_end, status, paid_at, payment_method, paid_via, note)
                 VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW(), 'manual', 'manual', ?)"
            );

            // Ambil data subscription yang baru dibuat
            $stmtSub = Tenant::db()->prepare("SELECT * FROM tenant_subscriptions WHERE id = ?");
            $stmtSub->execute([$subId]);
            $sub = $stmtSub->fetch();

            $stmt->execute([
                $tenantId,
                $subId,
                $invoiceNumber,
                $price,
                $sub['start_date'],
                $sub['end_date'],
                $note ?: 'Perpanjangan manual oleh superadmin',
            ]);

            $_SESSION['sub_success'] = 'Langganan berhasil diperpanjang ' . $months . ' bulan.';
        } catch (\Throwable $e) {
            $_SESSION['sub_error'] = 'Gagal: ' . $e->getMessage();
        }

        Response::redirect('/superadmin/langganan');
    }

    public function detail(string $tenantId): void
    {
        $tenantId = (int) $tenantId;
        $tenant = Tenant::findById($tenantId);
        if (!$tenant) {
            http_response_code(404);
            require __DIR__ . '/../../views/errors/404.php';
            return;
        }

        $status = Tenant::getSubscriptionStatus($tenantId);

        $stmt = Tenant::db()->prepare(
            "SELECT * FROM tenant_subscriptions
             WHERE tenant_id = ?
             ORDER BY start_date DESC"
        );
        $stmt->execute([$tenantId]);
        $subscriptions = $stmt->fetchAll();

        $stmt = Tenant::db()->prepare(
            "SELECT * FROM subscription_payments
             WHERE tenant_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$tenantId]);
        $payments = $stmt->fetchAll();

        Response::view('superadmin/langganan/detail', [
            'title'         => 'Detail Langganan — ' . $tenant['nama'],
            'tenant'        => $tenant,
            'status'        => $status,
            'subscriptions' => $subscriptions,
            'payments'      => $payments,
            'csrf'          => Security::csrfToken(),
        ]);
    }
        /**
     * Ringkasan pendapatan dari langganan.
     * Dipakai di dashboard superadmin.
     */
    public static function getRevenueSummary(): array
    {
        $db = \App\Models\Tenant::db();

        // Pendapatan langganan bulan ini
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM subscription_payments
             WHERE status = 'paid'
               AND YEAR(paid_at) = YEAR(CURDATE())
               AND MONTH(paid_at) = MONTH(CURDATE())"
        );
        $stmt->execute();
        $revenueThisMonth = (float) $stmt->fetchColumn();

        // Pendapatan bulan lalu
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM subscription_payments
             WHERE status = 'paid'
               AND YEAR(paid_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
               AND MONTH(paid_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))"
        );
        $stmt->execute();
        $revenueLastMonth = (float) $stmt->fetchColumn();

        $growth = 0;
        if ($revenueLastMonth > 0) {
            $growth = (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100;
        }

        // Tenant aktif berlangganan
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT tenant_id)
             FROM tenant_subscriptions
             WHERE status = 'active' AND end_date >= CURDATE()"
        );
        $stmt->execute();
        $activeTenants = (int) $stmt->fetchColumn();

        // Tenant akan habis dalam 7 hari
        $stmt = $db->prepare(
            "SELECT COUNT(DISTINCT tenant_id)
             FROM tenant_subscriptions
             WHERE status = 'active'
               AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        );
        $stmt->execute();
        $expiringSoon = (int) $stmt->fetchColumn();

        // Tenant expired (lewat grace)
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

        // Total sepanjang waktu
        $stmt = $db->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM subscription_payments
             WHERE status = 'paid'"
        );
        $stmt->execute();
        $revenueAllTime = (float) $stmt->fetchColumn();

        return [
            'revenue_this_month' => $revenueThisMonth,
            'revenue_last_month' => $revenueLastMonth,
            'revenue_all_time'   => $revenueAllTime,
            'growth_percent'     => round($growth, 1),
            'active_tenants'     => $activeTenants,
            'expiring_soon'      => $expiringSoon,
            'expired_tenants'    => $expiredTenants,
        ];
    }
}