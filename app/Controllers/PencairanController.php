<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\TenantBankAccount;
use App\Models\Withdrawal;

class PencairanController
{
    public function index(): void
    {
        error_log('DEBUG tenantId=' . Auth::tenantId() . ' userId=' . Auth::userId() . ' role=' . Auth::role());
        $tenantId = Auth::tenantId();   // ← ganti

        if ($tenantId <= 0) {
            $_SESSION['pencairan_error'] = 'Sesi tidak valid. Silakan login ulang.';
            Response::redirect('/login');
        }

        $summary = Withdrawal::getBalanceSummary($tenantId);

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;
        $total   = Withdrawal::countByTenant($tenantId);
        $items   = Withdrawal::listByTenant($tenantId, $page, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));

        $bank = TenantBankAccount::findByTenant($tenantId);

        Response::view('pencairan/index', [
            'title'    => 'Pencairan',
            'summary'  => $summary,
            'items'    => $items,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
            'bank'     => $bank,
            'csrf'     => Security::csrfToken(),
            'error'    => $_SESSION['pencairan_error'] ?? null,
            'success'  => $_SESSION['pencairan_success'] ?? null,
        ]);
        unset($_SESSION['pencairan_error'], $_SESSION['pencairan_success']);
    }

    public function request(): void
    {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['pencairan_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/pencairan');
        }

        $tenantId = Auth::tenantId();   // ← ganti
        $userId   = Auth::userId();     // ← ganti

        if ($tenantId <= 0 || $userId <= 0) {
            $_SESSION['pencairan_error'] = 'Sesi tidak valid.';
            Response::redirect('/login');
        }

        $amount = (float) ($_POST['amount'] ?? 0);
        $note   = trim($_POST['note'] ?? '');

        $summary = Withdrawal::getBalanceSummary($tenantId);

        if ($amount < Withdrawal::MIN_AMOUNT) {
            $_SESSION['pencairan_error'] = 'Minimum pencairan Rp' . number_format(Withdrawal::MIN_AMOUNT, 0, ',', '.');
            Response::redirect('/admin/pencairan');
        }

        if ($amount > $summary['available']) {
            $_SESSION['pencairan_error'] = 'Jumlah melebihi saldo tersedia.';
            Response::redirect('/admin/pencairan');
        }

        $bank = TenantBankAccount::findByTenant($tenantId);
        if (!$bank || $bank['status'] !== 'verified') {
            $_SESSION['pencairan_error'] = 'Rekening bank belum diverifikasi.';
            Response::redirect('/admin/pencairan');
        }

        try {
            Withdrawal::createRequest($tenantId, $amount, $bank, $userId, $note ?: null);
            $_SESSION['pencairan_success'] = 'Pengajuan pencairan berhasil dikirim. Menunggu persetujuan superadmin.';
        } catch (\Throwable $e) {
            $_SESSION['pencairan_error'] = 'Gagal mengajukan: ' . $e->getMessage();
        }

        Response::redirect('/admin/pencairan');
    }

    public function cancel(string $id): void
    {
        $withdrawalId = (int) $id;
        $tenantId     = Auth::tenantId();   // ← ganti
        $userId       = Auth::userId();     // ← ganti

        $w = Withdrawal::findById($withdrawalId);
        if (!$w || (int) $w['tenant_id'] !== $tenantId) {
            $_SESSION['pencairan_error'] = 'Pengajuan tidak ditemukan.';
            Response::redirect('/admin/pencairan');
        }

        if ($w['status'] !== 'pending') {
            $_SESSION['pencairan_error'] = 'Hanya pengajuan pending yang bisa dibatalkan.';
            Response::redirect('/admin/pencairan');
        }

        Withdrawal::updateStatus($withdrawalId, 'rejected', $userId, 'Dibatalkan oleh tenant');
        $_SESSION['pencairan_success'] = 'Pengajuan dibatalkan.';
        Response::redirect('/admin/pencairan');
    }
}