<?php
declare(strict_types=1);

namespace App\Helpers;

class Auth
{
    /**
     * Cek beberapa pola session yang umum dipakai.
     * Sesuaikan urutan pengecekan kalau perlu.
     */
    public static function userId(): int
    {
        $keys = [
            'user_id',                      // paling umum di project simple
            'user.id',                      // nested array
            'auth.user_id',
            'auth.id',
        ];

        foreach ($keys as $key) {
            $val = self::getFromSession($key);
            if ($val !== null && $val !== '') {
                return (int) $val;
            }
        }

        // Fallback: cek $_SESSION['user'] sebagai array
        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            if (!empty($_SESSION['user']['id'])) return (int) $_SESSION['user']['id'];
            if (!empty($_SESSION['user']['user_id'])) return (int) $_SESSION['user']['user_id'];
        }

        return 0;
    }

    public static function tenantId(): int
    {
        $keys = [
            'tenant_id',
            'user.tenant_id',
            'auth.tenant_id',
        ];

        foreach ($keys as $key) {
            $val = self::getFromSession($key);
            if ($val !== null && $val !== '') {
                return (int) $val;
            }
        }

        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            if (!empty($_SESSION['user']['tenant_id'])) return (int) $_SESSION['user']['tenant_id'];
        }

        return 0;
    }

    public static function role(): string
    {
        $keys = ['role', 'user.role', 'auth.role'];
        foreach ($keys as $key) {
            $val = self::getFromSession($key);
            if ($val !== null && $val !== '') {
                return (string) $val;
            }
        }
        if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
            if (!empty($_SESSION['user']['role'])) return (string) $_SESSION['user']['role'];
        }
        return '';
    }

    /**
     * Dukung key bertitik: "user.id" -> $_SESSION['user']['id']
     */
    private static function getFromSession(string $key): mixed
    {
        if (str_contains($key, '.')) {
            $parts = explode('.', $key);
            $ref = $_SESSION;
            foreach ($parts as $p) {
                if (!is_array($ref) || !array_key_exists($p, $ref)) {
                    return null;
                }
                $ref = $ref[$p];
            }
            return $ref;
        }

        return $_SESSION[$key] ?? null;
    }
}