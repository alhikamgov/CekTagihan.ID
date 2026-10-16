<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Response;

class AuthMiddleware
{
    public static function check(): void
    {
        if (empty($_SESSION['user_id'])) {
            Response::redirect('/login');
        }
    }

    public static function role(string ...$roles): void
    {
        self::check();

        $current = $_SESSION['user_role'] ?? null;
        if (!in_array($current, $roles, true)) {
            http_response_code(403);
            echo '403 Forbidden: Anda tidak punya akses ke halaman ini.';
            exit;
        }
    }

    public static function superadmin(): void
    {
        self::role('superadmin');
    }

    public static function admin(): void
    {
        self::role('admin');
        \App\Middleware\SubscriptionMiddleware::check();
    }
}