<?php
declare(strict_types=1);

namespace App\Helpers;

class RateLimiter
{
    /**
     * Cek rate limit berdasarkan IP + aksi.
     * Return true kalau masih boleh, false kalau sudah lewat batas.
     */
    public static function check(string $action, int $maxAttempts = 10, int $windowSeconds = 60): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$action}_{$ip}";
        $file = sys_get_temp_dir() . '/' . md5($key) . '.json';

        $now = time();
        $data = ['count' => 0, 'reset_at' => $now + $windowSeconds];

        if (file_exists($file)) {
            $content = @file_get_contents($file);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['reset_at'])) {
                    $data = $decoded;
                }
            }
        }

        // Kalau window sudah lewat, reset
        if ($now >= $data['reset_at']) {
            $data = ['count' => 0, 'reset_at' => $now + $windowSeconds];
        }

        $data['count']++;

        @file_put_contents($file, json_encode($data));

        return $data['count'] <= $maxAttempts;
    }

    /**
     * Reset counter untuk IP + aksi tertentu.
     * Dipakai setelah login sukses supaya user tidak terkunci.
     */
    public static function reset(string $action): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit_{$action}_{$ip}";
        $file = sys_get_temp_dir() . '/' . md5($key) . '.json';

        if (file_exists($file)) {
            @unlink($file);
        }
    }
}