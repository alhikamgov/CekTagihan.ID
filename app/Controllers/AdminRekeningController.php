<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\TenantBankAccount;
use App\Models\Tenant;

class AdminRekeningController
{
    /**
     * List semua rekening tenant.
     */
    public function index(): void
    {
        $db = TenantBankAccount::db();
        $stmt = $db->query(
            "SELECT tba.*, t.nama AS tenant_nama, t.slug
             FROM tenant_bank_accounts tba
             INNER JOIN tenants t ON t.id = tba.tenant_id
             ORDER BY 
                CASE tba.status 
                    WHEN 'pending' THEN 1
                    WHEN 'verified' THEN 2
                    WHEN 'rejected' THEN 3
                END,
                tba.created_at DESC"
        );
        $rekeningList = $stmt->fetchAll();

        Response::view('superadmin/rekening/index', [
            'title'        => 'Verifikasi Rekening',
            'rekeningList' => $rekeningList,
        ]);
    }

    /**
     * Verifikasi rekening.
     */
    public function verify(string $id): void
    {
        $id = (int) $id;
        $db = TenantBankAccount::db();

        $stmt = $db->prepare("SELECT * FROM tenant_bank_accounts WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $rekening = $stmt->fetch();

        if (!$rekening) {
            $_SESSION['flash_error'] = 'Rekening tidak ditemukan.';
            Response::redirect('/superadmin/rekening');
        }

        // Update status jadi verified
        TenantBankAccount::updateStatus((int) $rekening['tenant_id'], 'verified');

        AuditLog::write('bank_account.verify', 'tenant_bank_accounts', $id, [
            'tenant_id' => $rekening['tenant_id'],
        ]);

        $_SESSION['flash_success'] = 'Rekening berhasil diverifikasi.';
        Response::redirect('/superadmin/rekening');
    }

    /**
     * Tolak rekening.
     */
    public function reject(string $id): void
    {
        $id = (int) $id;
        $db = TenantBankAccount::db();

        $stmt = $db->prepare("SELECT * FROM tenant_bank_accounts WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $rekening = $stmt->fetch();

        if (!$rekening) {
            $_SESSION['flash_error'] = 'Rekening tidak ditemukan.';
            Response::redirect('/superadmin/rekening');
        }

        TenantBankAccount::updateStatus((int) $rekening['tenant_id'], 'rejected');

        AuditLog::write('bank_account.reject', 'tenant_bank_accounts', $id, [
            'tenant_id' => $rekening['tenant_id'],
        ]);

        $_SESSION['flash_success'] = 'Rekening ditolak.';
        Response::redirect('/superadmin/rekening');
    }
}