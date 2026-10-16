<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\SiteInfo;
use App\Models\PlatformSetting;

class PlatformSettingController
{
    /**
     * Halaman pengaturan platform.
     */
    public function index(): void
    {
        $settings = PlatformSetting::all();

        // Ubah jadi key-value
        $data = [];
        foreach ($settings as $s) {
            $data[$s['setting_key']] = $s['setting_value'];
        }

        Response::view('superadmin/pengaturan/index', [
            'title'    => 'Pengaturan Platform',
            'settings' => $data,
            'csrf'     => Security::csrfToken(),
            'errors'   => [],
            'old'      => [],
        ]);
    }

    /**
     * Simpan pengaturan.
     */
    public function save(): void
    {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/superadmin/pengaturan');
        }

        $allowedKeys = [
            'contact_email_support',
            'contact_email_privacy',
            'contact_whatsapp',
            'contact_phone',
            'contact_address',
            'contact_hours',
            'social_instagram',
            'social_facebook',
            'social_twitter',
            'social_linkedin',
            'legal_company_name',
            'legal_nib',
            'legal_npwp',
            'legal_pse',
        ];

        $errors = [];

        // Validasi email
        $emailSupport = trim($_POST['contact_email_support'] ?? '');
        if ($emailSupport !== '' && !filter_var($emailSupport, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email_support'] = 'Format email support tidak valid.';
        }

        $emailPrivacy = trim($_POST['contact_email_privacy'] ?? '');
        if ($emailPrivacy !== '' && !filter_var($emailPrivacy, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email_privacy'] = 'Format email privacy tidak valid.';
        }

        // Validasi WA — harus angka
        $wa = trim($_POST['contact_whatsapp'] ?? '');
        if ($wa !== '') {
            $waClean = preg_replace('/[^0-9]/', '', $wa);
            if (strlen($waClean) < 10) {
                $errors['contact_whatsapp'] = 'Nomor WhatsApp minimal 10 digit.';
            } else {
                // Normalisasi ke 62
                if (str_starts_with($waClean, '0')) {
                    $waClean = '62' . substr($waClean, 1);
                } elseif (str_starts_with($waClean, '8')) {
                    $waClean = '62' . $waClean;
                }
                $_POST['contact_whatsapp'] = $waClean;
            }
        }

        if (!empty($errors)) {
            Response::view('superadmin/pengaturan/index', [
                'title'    => 'Pengaturan Platform',
                'settings' => array_merge(array_combine($allowedKeys, array_fill(0, count($allowedKeys), '')), $_POST),
                'csrf'     => Security::csrfToken(),
                'errors'   => $errors,
                'old'      => $_POST,
            ]);
            return;
        }

        // Simpan semua key
        foreach ($allowedKeys as $key) {
            if (isset($_POST[$key])) {
                PlatformSetting::set($key, trim($_POST[$key]));
            }
        }

        SiteInfo::clearCache();

        AuditLog::write('platform_setting.save', 'platform_settings', null, [
            'keys' => $allowedKeys,
        ]);

        $_SESSION['flash_success'] = 'Pengaturan platform berhasil disimpan.';
        Response::redirect('/superadmin/pengaturan');
    }
}