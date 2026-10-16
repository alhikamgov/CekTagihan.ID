<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Models\TenantBankAccount;

class RekeningController
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
     * Halaman form rekening bank.
     */
    public function index(): void
    {
        $tenantId = $this->tenantId();
        $rekening = TenantBankAccount::findByTenant($tenantId);

        Response::view('admin/rekening/index', [
            'title'    => 'Rekening Bank',
            'rekening' => $rekening,
            'csrf'     => Security::csrfToken(),
            'errors'   => [],
            'old'      => [],
        ]);
    }

    /**
     * Simpan data rekening.
     */
    public function save(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/rekening');
        }

        $validator = new Validator($_POST);
        $validator->required('bank_nama', 'Nama Bank')
                  ->required('bank_kode', 'Kode Bank')
                  ->required('rekening_nomor', 'Nomor Rekening')
                  ->required('rekening_pemilik', 'Nama Pemilik Rekening')
                  ->maxLength('rekening_nomor', 50, 'Nomor Rekening')
                  ->maxLength('rekening_pemilik', 120, 'Nama Pemilik Rekening');

        if ($validator->fails()) {
            Response::view('admin/rekening/index', [
                'title'    => 'Rekening Bank',
                'rekening' => TenantBankAccount::findByTenant($tenantId),
                'csrf'     => Security::csrfToken(),
                'errors'   => $validator->errors(),
                'old'      => $_POST,
            ]);
            return;
        }

        // Cek apakah rekening sudah verified — kalau iya, tidak bisa diubah
        $existing = TenantBankAccount::findByTenant($tenantId);
        if ($existing && $existing['status'] === 'verified') {
            $_SESSION['flash_error'] = 'Rekening yang sudah diverifikasi tidak bisa diubah. Hubungi admin.';
            Response::redirect('/admin/rekening');
        }

        TenantBankAccount::upsert($tenantId, [
            'bank_nama'        => trim($_POST['bank_nama']),
            'bank_kode'        => strtoupper(trim($_POST['bank_kode'])),
            'rekening_nomor'   => trim($_POST['rekening_nomor']),
            'rekening_pemilik' => trim($_POST['rekening_pemilik']),
        ]);

        AuditLog::write('bank_account.save', 'tenant_bank_accounts', null, [
            'bank' => trim($_POST['bank_nama']),
            'nomor' => substr(trim($_POST['rekening_nomor']), -4),
        ]);

        $_SESSION['flash_success'] = 'Rekening bank berhasil disimpan. Menunggu verifikasi admin.';
        Response::redirect('/admin/rekening');
    }
}