<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\PasswordGenerator;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Tenant;
use App\Models\TenantRegistration;
use App\Models\User;

class PendaftarController
{
    /**
     * List pendaftar.
     */
    public function index(): void
    {
        $status   = trim($_GET['status'] ?? 'pending');
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $perPage  = 20;

        $total    = TenantRegistration::countByStatus($status);
        $items    = TenantRegistration::allByStatus($status, $page, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $pendingCount = TenantRegistration::countPending();

        Response::view('superadmin/pendaftar/index', [
            'title'        => 'Pendaftar Tenant',
            'items'        => $items,
            'status'       => $status,
            'page'         => $page,
            'lastPage'     => $lastPage,
            'total'        => $total,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Detail pendaftar.
     */
    public function show(string $id): void
    {
        $id = (int) $id;
        $item = TenantRegistration::findById($id);

        if (!$item) {
            $_SESSION['flash_error'] = 'Pendaftar tidak ditemukan.';
            Response::redirect('/superadmin/pendaftar');
        }

        Response::view('superadmin/pendaftar/show', [
            'title' => 'Detail Pendaftar',
            'item'  => $item,
            'csrf'  => Security::csrfToken(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * Approve pendaftar → buat tenant + admin.
     */
    public function approve(string $id): void
    {
        $id = (int) $id;
        $reg = TenantRegistration::findById($id);

        if (!$reg) {
            $_SESSION['flash_error'] = 'Pendaftar tidak ditemukan.';
            Response::redirect('/superadmin/pendaftar');
        }

        if ($reg['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Pendaftar ini sudah diproses.';
            Response::redirect('/superadmin/pendaftar');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/superadmin/pendaftar');
        }

        $slug = Tenant::generateSlug($reg['nama_bisnis']);
        if (Tenant::slugExists($slug)) {
            $slug .= '-' . time();
        }

        $plainPassword = PasswordGenerator::generate('Ruko');

        $db = Tenant::db();
        $db->beginTransaction();

        try {
            // 1. Buat tenant
            $tenantId = Tenant::create([
                'nama'    => $reg['nama_bisnis'],
                'slug'    => $slug,
                'alamat'  => $reg['alamat'] ?? '',
                'telepon' => $reg['telepon'],
                'email'   => $reg['email'],
                'status'  => 'active',
            ]);

            // 2. Buat admin tenant
            $adminId = User::create($tenantId, [
                'nama'     => $reg['nama_pic'],
                'email'    => $reg['email'],
                'password' => $plainPassword,
                'role'     => 'admin',
            ]);

            // 3. Buat jenis tagihan default
            $defaultJenis = ['sewa', 'ipl', 'listrik', 'air', 'sampah', 'lain'];
            $urutan = 1;
            foreach ($defaultJenis as $jenis) {
                \App\Models\InvoiceType::create($tenantId, [
                    'nama'   => $jenis,
                    'urutan' => $urutan++,
                ]);
            }

            // 4. Update status pendaftar
            TenantRegistration::approve($id, (int) $_SESSION['user_id'], $tenantId);

            $db->commit();

            AuditLog::write('tenant_registration.approve', 'tenant_registrations', $id, [
                'tenant_id' => $tenantId,
                'admin_id'  => $adminId,
            ]);

            // Simpan kredensial di session untuk ditampilkan sekali
            $_SESSION['new_tenant_credentials'] = [
                'nama_bisnis' => $reg['nama_bisnis'],
                'slug'        => $slug,
                'nama_pic'    => $reg['nama_pic'],
                'email'       => $reg['email'],
                'password'    => $plainPassword,
            ];

            $_SESSION['flash_success'] = 'Pendaftar berhasil di-approve. Kredensial sudah dibuat.';
            Response::redirect('/superadmin/pendaftar');

        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal approve: ' . $e->getMessage();
            Response::redirect('/superadmin/pendaftar/show/' . $id);
        }
    }

    /**
     * Reject pendaftar.
     */
    public function reject(string $id): void
    {
        $id = (int) $id;
        $reg = TenantRegistration::findById($id);

        if (!$reg) {
            $_SESSION['flash_error'] = 'Pendaftar tidak ditemukan.';
            Response::redirect('/superadmin/pendaftar');
        }

        if ($reg['status'] !== 'pending') {
            $_SESSION['flash_error'] = 'Pendaftar ini sudah diproses.';
            Response::redirect('/superadmin/pendaftar');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/superadmin/pendaftar');
        }

        $reason = trim($_POST['reject_reason'] ?? 'Tidak ada alasan.');
        TenantRegistration::reject($id, $reason, (int) $_SESSION['user_id']);

        AuditLog::write('tenant_registration.reject', 'tenant_registrations', $id, [
            'reason' => $reason,
        ]);

        $_SESSION['flash_success'] = 'Pendaftar ditolak.';
        Response::redirect('/superadmin/pendaftar');
    }
}