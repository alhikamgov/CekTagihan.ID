<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Tenant;
use App\Models\SubscriptionPayment;

class TenantSubscriptionController
{
    public function index(): void
    {
        $tenantId = Auth::tenantId();
        if ($tenantId <= 0) {
            Response::redirect('/login');
        }

        $status = Tenant::getSubscriptionStatus($tenantId);

        $stmt = Tenant::db()->prepare(
            "SELECT * FROM tenant_subscriptions
            WHERE tenant_id = ?
            ORDER BY start_date DESC
            LIMIT 20"
        );
        $stmt->execute([$tenantId]);
        $subscriptions = $stmt->fetchAll();

        $stmt = Tenant::db()->prepare(
            "SELECT * FROM subscription_payments
            WHERE tenant_id = ?
            ORDER BY created_at DESC
            LIMIT 20"
        );
        $stmt->execute([$tenantId]);
        $payments = $stmt->fetchAll();

        Response::view('admin/langganan/index', [
            'title'         => 'Langganan Saya',
            'status'        => $status,
            'subscriptions' => $subscriptions,
            'payments'      => $payments,
            'csrf'          => Security::csrfToken(),
        ]);
    }
}