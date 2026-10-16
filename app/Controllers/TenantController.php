<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Helpers\PasswordGenerator;
use App\Helpers\RateLimiter;
use App\Models\Tenant;
use App\Models\User;

class TenantController
{
    public function index(): void
{
    if (isset($_GET['clear_cred'])) {
    unset($_SESSION['new_admin_credentials']);
    Response::redirect('/superadmin/tenants');
}
    $search = trim($_GET['q'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $page   = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 10;

    $total    = Tenant::countFiltered($search, $status);
    $tenants  = Tenant::allWithStats($search, $status, $page, $perPage);
    $lastPage = max(1, (int) ceil($total / $perPage));

    Response::view('superadmin/tenants/index', [
        'title'    => 'Kelola Tenant',
        'tenants'  => $tenants,
        'search'   => $search,
        'status'   => $status,
        'page'     => $page,
        'lastPage' => $lastPage,
        'total'    => $total,
    ]);
}

    public function create(): void
    {
        Response::view('superadmin/tenants/form', [
            'title'  => 'Tambah Tenant',
            'tenant' => null,
            'csrf'   => Security::csrfToken(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function store(): void
{
    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid.';
        Response::redirect('/superadmin/tenants/create');
    }

    $nama      = trim($_POST['nama'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');
    $slug      = $slugInput !== '' ? Tenant::generateSlug($slugInput) : Tenant::generateSlug($nama);

    $adminNama  = trim($_POST['admin_nama']  ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');

    $validator = new Validator($_POST);
    $validator->required('nama', 'Nama tenant')
              ->maxLength('nama', 150, 'Nama tenant')
              ->email('email', 'Email tenant')
              ->required('admin_nama', 'Nama admin')
              ->required('admin_email', 'Email admin')
              ->email('admin_email', 'Email admin');

    // Cek email admin unik
    if (!empty($adminEmail) && User::findByEmail($adminEmail)) {
        $errors = $validator->errors();
        $errors['admin_email'] = 'Email sudah dipakai user lain.';

        Response::view('superadmin/tenants/form', [
            'title'  => 'Tambah Tenant',
            'tenant' => null,
            'csrf'   => Security::csrfToken(),
            'errors' => $errors,
            'old'    => $_POST,
        ]);
        return;
    }

    if ($validator->fails()) {
        Response::view('superadmin/tenants/form', [
            'title'  => 'Tambah Tenant',
            'tenant' => null,
            'csrf'   => Security::csrfToken(),
            'errors' => $validator->errors(),
            'old'    => $_POST,
        ]);
        return;
    }

    if (Tenant::slugExists($slug)) {
        $slug = $slug . '-' . time();
    }

    // Generate password otomatis
    $plainPassword = PasswordGenerator::generate('Ruko');

    $db = Tenant::db();
    $db->beginTransaction();

    try {
        // 1. Buat tenant
        $tenantId = Tenant::create([
            'nama'    => $nama,
            'slug'    => $slug,
            'alamat'  => trim($_POST['alamat'] ?? ''),
            'telepon' => trim($_POST['telepon'] ?? ''),
            'email'   => trim($_POST['email'] ?? ''),
            'status'  => 'active',
        ]);

        // 2. Buat admin tenant
        $adminId = User::create($tenantId, [
            'nama'     => $adminNama,
            'email'    => $adminEmail,
            'password' => $plainPassword,
            'role'     => 'admin',
        ]);

        $db->commit();

        AuditLog::write('tenant.create_with_admin', 'tenants', $tenantId, [
            'nama'      => $nama,
            'slug'      => $slug,
            'admin_id'  => $adminId,
        ]);

        // Simpan password sekali saja di flash untuk ditampilkan
        $_SESSION['flash_success'] = "Tenant '{$nama}' berhasil dibuat.";
        $_SESSION['new_admin_credentials'] = [
            'tenant_nama' => $nama,
            'tenant_slug' => $slug,
            'admin_nama'  => $adminNama,
            'admin_email' => $adminEmail,
            'password'    => $plainPassword,
        ];

        Response::redirect('/superadmin/tenants');

    } catch (\Throwable $e) {
        $db->rollBack();
        $_SESSION['flash_error'] = 'Gagal membuat tenant: ' . $e->getMessage();
        Response::redirect('/superadmin/tenants/create');
    }
}

    public function edit(string $id): void
{
    $id = (int) $id;
    $tenant = Tenant::findById($id);
    if (!$tenant) {
        $_SESSION['flash_error'] = 'Tenant tidak ditemukan.';
        Response::redirect('/superadmin/tenants');
    }

    $admins = User::findByTenantAndRole($id, 'admin');
    $admin  = $admins[0] ?? null;

    Response::view('superadmin/tenants/form', [
        'title'  => 'Edit Tenant',
        'tenant' => $tenant,
        'admin'  => $admin,
        'csrf'   => Security::csrfToken(),
        'errors' => [],
        'old'    => [],
    ]);
}

public function update(string $id): void
{
    $id = (int) $id;
    $tenant = Tenant::findById($id);
    if (!$tenant) {
        $_SESSION['flash_error'] = 'Tenant tidak ditemukan.';
        Response::redirect('/superadmin/tenants');
    }

    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid.';
        Response::redirect('/superadmin/tenants/edit/' . $id);
    }

    $nama      = trim($_POST['nama'] ?? '');
    $slugInput = trim($_POST['slug'] ?? '');
    $slug      = $slugInput !== '' ? Tenant::generateSlug($slugInput) : Tenant::generateSlug($nama);

    $adminNama  = trim($_POST['admin_nama']  ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');

    $validator = new Validator($_POST);
    $validator->required('nama', 'Nama tenant')
              ->maxLength('nama', 150, 'Nama tenant')
              ->email('email', 'Email tenant');

    if ($adminNama !== '') {
        $validator->required('admin_nama', 'Nama admin');
    }
    if ($adminEmail !== '') {
        $validator->email('admin_email', 'Email admin');

        // Cek email unik
        $existing = User::findByEmail($adminEmail);
        $admins = User::findByTenantAndRole($id, 'admin');
        $currentAdminId = $admins[0]['id'] ?? 0;

        if ($existing && (int) $existing['id'] !== (int) $currentAdminId) {
            $errors = $validator->errors();
            $errors['admin_email'] = 'Email sudah dipakai user lain.';

            Response::view('superadmin/tenants/form', [
                'title'  => 'Edit Tenant',
                'tenant' => $tenant,
                'admin'  => $admins[0] ?? null,
                'csrf'   => Security::csrfToken(),
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }
    }

    if (Tenant::slugExists($slug, $id)) {
        $slug = $slug . '-' . time();
    }

    if ($validator->fails()) {
        Response::view('superadmin/tenants/form', [
            'title'  => 'Edit Tenant',
            'tenant' => $tenant,
            'admin'  => User::findByTenantAndRole($id, 'admin')[0] ?? null,
            'csrf'   => Security::csrfToken(),
            'errors' => $validator->errors(),
            'old'    => $_POST,
        ]);
        return;
    }

    // Update tenant
    Tenant::update($id, [
        'nama'    => $nama,
        'slug'    => $slug,
        'alamat'  => trim($_POST['alamat'] ?? ''),
        'telepon' => trim($_POST['telepon'] ?? ''),
        'email'   => trim($_POST['email'] ?? ''),
    ]);

    // Update admin
    $admins = User::findByTenantAndRole($id, 'admin');
    if (!empty($admins[0])) {
        $updateData = [];
        if ($adminNama !== '')  $updateData['nama']  = $adminNama;
        if ($adminEmail !== '') $updateData['email'] = $adminEmail;

        if (!empty($updateData)) {
            User::updateProfile((int) $admins[0]['id'], [
                'nama'  => $updateData['nama']  ?? $admins[0]['nama'],
                'email' => $updateData['email'] ?? $admins[0]['email'],
            ]);
        }
    }

    AuditLog::write('tenant.update', 'tenants', $id, ['nama' => $nama]);

    $_SESSION['flash_success'] = "Tenant '{$nama}' berhasil diupdate.";
    Response::redirect('/superadmin/tenants');
}

    public function toggleStatus(string $id): void
    {
        $id = (int) $id;
        $tenant = Tenant::findById($id);
        if (!$tenant) {
            $_SESSION['flash_error'] = 'Tenant tidak ditemukan.';
            Response::redirect('/superadmin/tenants');
        }

        Tenant::toggleStatus($id);
        $newStatus = $tenant['status'] === 'active' ? 'suspended' : 'active';

        AuditLog::write('tenant.toggle_status', 'tenants', $id, ['status' => $newStatus]);

        $_SESSION['flash_success'] = "Status tenant '{$tenant['nama']}' diubah menjadi {$newStatus}.";
        Response::redirect('/superadmin/tenants');
    }

    public function resetPassword(string $tenantId): void
{
    $tenantId = (int) $tenantId;
    $tenant = Tenant::findById($tenantId);
    if (!$tenant) {
        $_SESSION['flash_error'] = 'Tenant tidak ditemukan.';
        Response::redirect('/superadmin/tenants');
    }

    $admins = User::findByTenantAndRole($tenantId, 'admin');
    if (empty($admins)) {
        $_SESSION['flash_error'] = 'Tenant ini belum punya admin.';
        Response::redirect('/superadmin/tenants');
    }

    Response::view('superadmin/tenants/reset-password', [
        'title'  => 'Reset Password Admin',
        'tenant' => $tenant,
        'admin'  => $admins[0],
        'csrf'   => Security::csrfToken(),
        'errors' => [],
        'old'    => [],
    ]);
}

public function resetPasswordConfirm(string $tenantId): void
{
    $tenantId = (int) $tenantId;
    $tenant = Tenant::findById($tenantId);
    if (!RateLimiter::check('reset_password', 5, 900)) {
    $_SESSION['flash_error'] = 'Terlalu banyak percobaan. Coba lagi dalam 15 menit.';
    Response::redirect('/superadmin/tenants');
}
    if (!$tenant) {
        $_SESSION['flash_error'] = 'Tenant tidak ditemukan.';
        Response::redirect('/superadmin/tenants');
    }

    $admins = User::findByTenantAndRole($tenantId, 'admin');
    if (empty($admins)) {
        $_SESSION['flash_error'] = 'Tenant ini belum punya admin.';
        Response::redirect('/superadmin/tenants');
    }
    $admin = $admins[0];

    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid.';
        Response::redirect('/superadmin/tenants');
    }

    $newPassword = trim($_POST['new_password'] ?? '');
    $confirm     = trim($_POST['confirm_password'] ?? '');

    // Validasi
    $errors = [];
    if ($newPassword === '') {
        $errors['new_password'] = 'Password baru wajib diisi.';
    } elseif (strlen($newPassword) < 8) {
        $errors['new_password'] = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $errors['new_password'] = 'Password harus kombinasi huruf dan angka.';
    }

    if ($confirm === '') {
        $errors['confirm_password'] = 'Konfirmasi password wajib diisi.';
    } elseif ($newPassword !== $confirm) {
        $errors['confirm_password'] = 'Konfirmasi password tidak cocok.';
    }

    if (!empty($errors)) {
        Response::view('superadmin/tenants/reset-password', [
            'title'  => 'Reset Password Admin',
            'tenant' => $tenant,
            'admin'  => $admin,
            'csrf'   => Security::csrfToken(),
            'errors' => $errors,
            'old'    => $_POST,
        ]);
        return;
    }

    User::updatePassword((int) $admin['id'], $newPassword);

    AuditLog::write('user.reset_password', 'users', (int) $admin['id'], [
        'tenant_id' => $tenantId,
        'email'     => $admin['email'],
        'by'        => 'superadmin',
    ]);

    $_SESSION['flash_success'] = 'Password admin berhasil diubah.';

    Response::redirect('/superadmin/tenants');
}
}