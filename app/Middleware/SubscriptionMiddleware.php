<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Auth;
use App\Helpers\Response;
use App\Models\Tenant;

class SubscriptionMiddleware
{
    /**
     * Hard Lock:
     *   - active / none → lolos
     *   - grace → lolos (dengan warning banner)
     *   - expired → hanya boleh akses whitelist
     */
    public static function check(): void
    {
        $tenantId = Auth::tenantId();

        if ($tenantId <= 0) {
            return;
        }

        $status = Tenant::getSubscriptionStatus($tenantId);

        // Aktif atau belum pernah berlangganan — lolos
        if (in_array($status['status'], ['active', 'none'], true)) {
            return;
        }

        // Grace — masih boleh akses penuh, kasih warning
        if ($status['status'] === 'grace') {
            $_SESSION['subscription_warning'] = [
                'level'     => 'warning',
                'days_left' => $status['days_left'],
                'end_date'  => $status['subscription']['end_date'],
            ];
            return;
        }

        // ============================================================
        // EXPIRED — Hard Lock
        // ============================================================
        $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Whitelist path yang tetap boleh diakses
        $whitelist = [
            '/admin/langganan',      // lihat status & info perpanjang
            '/admin/pencairan',      // cairkan saldo (uang tenant)
            '/admin/rekening',       // update rekening (untuk pencairan)
            '/admin/profil',         // update profil
            '/logout',
        ];

        foreach ($whitelist as $path) {
            if (str_starts_with($currentUri, $path)) {
                return;
            }
        }

        // Kalau dashboard — biarkan, nanti controller yang redirect ke /admin/langganan
        if ($currentUri === '/dashboard' || $currentUri === '/dashboard/') {
            Response::redirect('/admin/langganan');
        }

        // Selain whitelist — redirect ke halaman lock
        $_SESSION['subscription_expired'] = true;
        Response::redirect('/admin/langganan');
    }
}