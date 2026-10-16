<?php
declare(strict_types=1);

namespace App\Models;

class PlatformSetting extends BaseModel
{
    protected static string $table = 'platform_settings';

    public static function get(string $key, $default = null)
    {
        $stmt = self::db()->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    public static function set(string $key, string $value): void
    {
        $stmt = self::db()->prepare(
            "INSERT INTO platform_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );
        $stmt->execute([$key, $value]);
    }

    public static function all(): array
    {
        $stmt = self::db()->query("SELECT * FROM platform_settings ORDER BY setting_key ASC");
        return $stmt->fetchAll();
    }
}