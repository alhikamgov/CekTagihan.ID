<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Models\User;
use App\Models\Tenant;

class ProfilController
{
    private function tenantId(): int
    {
        $tid = $_SESSION['tenant_id'] ?? null;
        if (!$tid) {
            $_SESSION['flash_error'] = 'Akun Anda tidak terhubung ke tenant.';
            Response::redirect('/dashboard');
        }
        return (int) $tid;
    }

    /**
     * Form edit profil tenant.
     */
    public function index(): void
{
    $tenantId = $this->tenantId();
    $userId   = (int) ($_SESSION['user_id'] ?? 0);

    $tenant = Tenant::findById($tenantId);
    if (!$tenant) {
        $_SESSION['flash_error'] = 'Tenant tidak ditemukan.';
        Response::redirect('/dashboard');
    }

    $admin = User::findById($userId);

    Response::view('admin/profil/index', [
        'title'  => 'Profil Tenant',
        'tenant' => $tenant,
        'admin'  => $admin,
        'csrf'   => Security::csrfToken(),
        'errors' => [],
        'old'    => [],
    ]);
}

    /**
     * Simpan perubahan profil.
     */
    public function update(): void
{
    $tenantId = $this->tenantId();
    $userId   = (int) ($_SESSION['user_id'] ?? 0);

    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid.';
        Response::redirect('/admin/profil');
    }

    $validator = new Validator($_POST);
    $validator->required('nama', 'Nama tenant')
              ->maxLength('nama', 150, 'Nama tenant')
              ->email('email', 'Email tenant')
              ->required('admin_nama', 'Nama admin')
              ->email('admin_email', 'Email admin');

    // Cek email admin unik (kecuali milik sendiri)
    $adminEmail = trim($_POST['admin_email'] ?? '');
    if ($adminEmail !== '') {
        $existing = User::findByEmail($adminEmail);
        if ($existing && (int) $existing['id'] !== $userId) {
            $errors = $validator->errors();
            $errors['admin_email'] = 'Email sudah dipakai user lain.';
            
            $tenant = Tenant::findById($tenantId);
            Response::view('admin/profil/index', [
                'title'  => 'Profil Tenant',
                'tenant' => $tenant,
                'admin'  => User::findById($userId),
                'csrf'   => Security::csrfToken(),
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }
    }

    if ($validator->fails()) {
        $tenant = Tenant::findById($tenantId);
        Response::view('admin/profil/index', [
            'title'  => 'Profil Tenant',
            'tenant' => $tenant,
            'admin'  => User::findById($userId),
            'csrf'   => Security::csrfToken(),
            'errors' => $validator->errors(),
            'old'    => $_POST,
        ]);
        return;
    }

    // Update tenant
    Tenant::updateProfile($tenantId, [
        'nama'     => trim($_POST['nama'] ?? ''),
        'alamat'   => trim($_POST['alamat'] ?? ''),
        'telepon'  => trim($_POST['telepon'] ?? ''),
        'email'    => trim($_POST['email'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
    ]);

    // Update admin user
    User::updateProfile($userId, [
        'nama'  => trim($_POST['admin_nama'] ?? ''),
        'email' => $adminEmail,
    ]);

    // Update session
    $_SESSION['tenant_nama'] = trim($_POST['nama'] ?? '');
    $_SESSION['user_name']   = trim($_POST['admin_nama'] ?? '');

    AuditLog::write('tenant.update_profile', 'tenants', $tenantId, [
        'nama' => trim($_POST['nama'] ?? ''),
    ]);

    $_SESSION['flash_success'] = 'Profil berhasil diupdate.';
    Response::redirect('/admin/profil');
}
}