<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Helpers\Lang;
use App\Models\Payer;

class PayerController
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

    public function index(): void
    {
        $tenantId = $this->tenantId();
        $search   = trim($_GET['q'] ?? '');
        $status   = trim($_GET['status'] ?? '');
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $perPage  = 10;

        $total    = Payer::countByTenantFiltered($tenantId, $search, $status);
        $payers   = Payer::allByTenant($tenantId, $search, $status, $page, $perPage);
        $lastPage = max(1, (int) ceil($total / $perPage));

        Response::view('admin/payers/index', [
            'title'    => 'Kelola Pelanggan',
            'payers'   => $payers,
            'search'   => $search,
            'status'   => $status,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ]);
    }

    public function create(): void
    {
        $this->tenantId();

        Response::view('admin/payers/form', [
            'title'  => 'Tambah Payer',
            'payer'  => null,
            'csrf'   => Security::csrfToken(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function store(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/payers/create');
        }

        $validator = new Validator($_POST);
        $validator->required('nomor_induk', Lang::NOMOR_PELANGGAN)
          ->required('nama', Lang::NAMA)
          ->maxLength('nomor_induk', 50, Lang::NOMOR_PELANGGAN)
          ->maxLength('nama', 150, Lang::NAMA);

        $nomorInduk = trim($_POST['nomor_induk'] ?? '');

        if (!$validator->fails() && Payer::nomorIndukExists($tenantId, $nomorInduk)) {
            $validator = new Validator($_POST);
            $errors = $validator->errors();
            $errors['nomor_induk'] = 'Nomor Pelanggan sudah dipakai.';

            Response::view('admin/payers/form', [
                'title'  => 'Tambah Payer',
                'payer'  => null,
                'csrf'   => Security::csrfToken(),
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }

        if ($validator->fails()) {
            Response::view('admin/payers/form', [
                'title'  => 'Tambah Payer',
                'payer'  => null,
                'csrf'   => Security::csrfToken(),
                'errors' => $validator->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        $id = Payer::create($tenantId, [
    'nomor_induk' => $nomorInduk,
    'nama'        => trim($_POST['nama'] ?? ''),
    'whatsapp'    => trim($_POST['whatsapp'] ?? ''),
    'status'      => 'active',
    ]);

        AuditLog::write('payer.create', 'payers', $id, ['nomor_induk' => $nomorInduk]);

        $_SESSION['flash_success'] = "Payer '{$nomorInduk}' berhasil ditambahkan.";
        Response::redirect('/admin/payers');
    }

    public function edit(string $id): void
    {
        $tenantId = $this->tenantId();
        $payer = Payer::findByIdAndTenant((int) $id, $tenantId);
        if (!$payer) {
            $_SESSION['flash_error'] = 'Payer tidak ditemukan.';
            Response::redirect('/admin/payers');
        }

        Response::view('admin/payers/form', [
            'title'  => 'Edit Payer',
            'payer'  => $payer,
            'csrf'   => Security::csrfToken(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function update(string $id): void
    {
        $tenantId = $this->tenantId();
        $id = (int) $id;
        $payer = Payer::findByIdAndTenant($id, $tenantId);
        if (!$payer) {
            $_SESSION['flash_error'] = 'Payer tidak ditemukan.';
            Response::redirect('/admin/payers');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/payers/edit/' . $id);
        }

        $validator = new Validator($_POST);
        $validator->required('nomor_induk', 'Nomor Pelanggan')
                  ->required('nama', 'Nama')
                  ->maxLength('nomor_induk', 50, 'Nomor Pelanggan')
                  ->maxLength('nama', 150, 'Nama')
                  ->email('email', 'Email');

        $nomorInduk = trim($_POST['nomor_induk'] ?? '');

        if (!$validator->fails() && Payer::nomorIndukExists($tenantId, $nomorInduk, $id)) {
            $errors = $validator->errors();
            $errors['nomor_induk'] = 'Nomor Pelanggan sudah dipakai payer lain.';

            Response::view('admin/payers/form', [
                'title'  => 'Edit Payer',
                'payer'  => $payer,
                'csrf'   => Security::csrfToken(),
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }

        if ($validator->fails()) {
            Response::view('admin/payers/form', [
                'title'  => 'Edit Payer',
                'payer'  => $payer,
                'csrf'   => Security::csrfToken(),
                'errors' => $validator->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        Payer::update($id, $tenantId, [
            'nomor_induk' => $nomorInduk,
            'nama'        => trim($_POST['nama'] ?? ''),
            'whatsapp'    => trim($_POST['whatsapp'] ?? ''),
        ]);

        AuditLog::write('payer.update', 'payers', $id, ['nomor_induk' => $nomorInduk]);

        $_SESSION['flash_success'] = "Payer '{$nomorInduk}' berhasil diupdate.";
        Response::redirect('/admin/payers');
    }

    public function toggleStatus(string $id): void
    {
        $tenantId = $this->tenantId();
        $id = (int) $id;
        $payer = Payer::findByIdAndTenant($id, $tenantId);
        if (!$payer) {
            $_SESSION['flash_error'] = 'Payer tidak ditemukan.';
            Response::redirect('/admin/payers');
        }

        Payer::toggleStatus($id, $tenantId);
        $newStatus = $payer['status'] === 'active' ? 'inactive' : 'active';

        AuditLog::write('payer.toggle_status', 'payers', $id, ['status' => $newStatus]);

        $_SESSION['flash_success'] = "Status payer '{$payer['nomor_induk']}' diubah menjadi {$newStatus}.";
        Response::redirect('/admin/payers');
    }

    public function getInvoices(string $payerId): void
{
    $tenantId = $this->tenantId();
    $payerId  = (int) $payerId;

    $payer = Payer::findByIdAndTenant($payerId, $tenantId);
    if (!$payer) {
        Response::json(['status' => false, 'message' => 'Pelanggan tidak ditemukan']);
        return;
    }

    $db = Invoice::db();
    $stmt = $db->prepare(
        "SELECT id, jenis, periode, jumlah, paid_amount, due_date,
                (jumlah - paid_amount) AS remaining
         FROM invoices
         WHERE tenant_id = ? AND payer_id = ? AND status = 'unpaid'
         ORDER BY due_date ASC"
    );
    $stmt->execute([$tenantId, $payerId]);
    $invoices = $stmt->fetchAll();

    Response::json([
        'status'   => true,
        'payer'    => [
            'id'          => $payer['id'],
            'nomor_induk' => $payer['nomor_induk'],
            'nama'        => $payer['nama'],
        ],
        'invoices' => $invoices,
    ]);
}
}