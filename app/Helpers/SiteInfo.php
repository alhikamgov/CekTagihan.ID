<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\PlatformSetting;

class SiteInfo
{
    private static ?array $cache = null;

    /**
     * Load semua setting sekaligus (cache).
     */
    private static function load(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                $all = PlatformSetting::all();
                foreach ($all as $row) {
                    self::$cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        $data = self::load();
        return $data[$key] ?? $default;
    }

    public static function emailSupport(): string
    {
        return self::get('contact_email_support', 'support@cektagihan.id');
    }

    public static function emailPrivacy(): string
    {
        return self::get('contact_email_privacy', 'privacy@cektagihan.id');
    }

    public static function whatsapp(): string
    {
        return self::get('contact_whatsapp', '');
    }

    public static function whatsappFormatted(): string
    {
        $wa = self::whatsapp();
        if ($wa === '') return '';
        // Format: 62812xxxx → 0812-xxxx-xxxx
        if (str_starts_with($wa, '62')) {
            $wa = '0' . substr($wa, 2);
        }
        return $wa;
    }

    public static function phone(): string
    {
        return self::get('contact_phone', '');
    }

    public static function address(): string
    {
        return self::get('contact_address', '');
    }

    public static function hours(): string
    {
        return self::get('contact_hours', '');
    }

    public static function instagram(): string
    {
        return self::get('social_instagram', '');
    }

    public static function facebook(): string
    {
        return self::get('social_facebook', '');
    }

    public static function twitter(): string
    {
        return self::get('social_twitter', '');
    }

    public static function linkedin(): string
    {
        return self::get('social_linkedin', '');
    }

    public static function companyName(): string
    {
        return self::get('legal_company_name', '');
    }

    public static function nib(): string
    {
        return self::get('legal_nib', '');
    }

    public static function npwp(): string
    {
        return self::get('legal_npwp', '');
    }

    public static function pse(): string
    {
        return self::get('legal_pse', '');
    }

    /**
     * Reset cache — dipakai setelah update setting.
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}