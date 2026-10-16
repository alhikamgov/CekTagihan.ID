<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Models\InvoiceType;

class JenisTagihanController
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
        $types = InvoiceType::allByTenant($tenantId);

        Response::view('admin/jenis-tagihan/index', [
            'title' => 'Jenis Tagihan',
            'types' => $types,
        ]);
    }

    public function create(): void
    {
        $this->tenantId();

        Response::view('admin/jenis-tagihan/form', [
            'title'  => 'Tambah Jenis Tagihan',
            'type'   => null,
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
            Response::redirect('/admin/jenis-tagihan/create');
        }

        // Pertahankan apa yang diketik user
        $nama = trim($_POST['nama'] ?? '');

        $validator = new Validator($_POST);
        $validator->required('nama', 'Nama jenis')
                  ->maxLength('nama', 50, 'Nama jenis');

        if (!$validator->fails() && InvoiceType::namaExists($tenantId, $nama)) {
            $errors = $validator->errors();
            $errors['nama'] = 'Nama jenis sudah ada.';

            Response::view('admin/jenis-tagihan/form', [
                'title'  => 'Tambah Jenis Tagihan',
                'type'   => null,
                'csrf'   => Security::csrfToken(),
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }

        if ($validator->fails()) {
            Response::view('admin/jenis-tagihan/form', [
                'title'  => 'Tambah Jenis Tagihan',
                'type'   => null,
                'csrf'   => Security::csrfToken(),
                'errors' => $validator->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        $id = InvoiceType::create($tenantId, [
            'nama'   => $nama,
            'urutan' => (int) ($_POST['urutan'] ?? 0),
        ]);

        AuditLog::write('invoice_type.create', 'invoice_types', $id, ['nama' => $nama]);

        $_SESSION['flash_success'] = "Jenis tagihan '{$nama}' berhasil ditambahkan.";
        Response::redirect('/admin/jenis-tagihan');
    }

    public function edit(string $id): void
    {
        $tenantId = $this->tenantId();
        $type = InvoiceType::findByIdAndTenant((int) $id, $tenantId);

        if (!$type) {
            $_SESSION['flash_error'] = 'Jenis tagihan tidak ditemukan.';
            Response::redirect('/admin/jenis-tagihan');
        }

        Response::view('admin/jenis-tagihan/form', [
            'title'  => 'Edit Jenis Tagihan',
            'type'   => $type,
            'csrf'   => Security::csrfToken(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function update(string $id): void
    {
        $tenantId = $this->tenantId();
        $id = (int) $id;
        $type = InvoiceType::findByIdAndTenant($id, $tenantId);

        if (!$type) {
            $_SESSION['flash_error'] = 'Jenis tagihan tidak ditemukan.';
            Response::redirect('/admin/jenis-tagihan');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/jenis-tagihan/edit/' . $id);
        }

        // Pertahankan apa yang diketik user
        $nama = trim($_POST['nama'] ?? '');

        $validator = new Validator($_POST);
        $validator->required('nama', 'Nama jenis')
                  ->maxLength('nama', 50, 'Nama jenis');

        if (!$validator->fails() && InvoiceType::namaExists($tenantId, $nama, $id)) {
            $errors = $validator->errors();
            $errors['nama'] = 'Nama jenis sudah ada.';

            Response::view('admin/jenis-tagihan/form', [
                'title'  => 'Edit Jenis Tagihan',
                'type'   => $type,
                'csrf'   => Security::csrfToken(),
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }

        if ($validator->fails()) {
            Response::view('admin/jenis-tagihan/form', [
                'title'  => 'Edit Jenis Tagihan',
                'type'   => $type,
                'csrf'   => Security::csrfToken(),
                'errors' => $validator->errors(),
                'old'    => $_POST,
            ]);
            return;
        }

        InvoiceType::update($id, $tenantId, [
            'nama'   => $nama,
            'urutan' => (int) ($_POST['urutan'] ?? 0),
        ]);

        AuditLog::write('invoice_type.update', 'invoice_types', $id, ['nama' => $nama]);

        $_SESSION['flash_success'] = "Jenis tagihan '{$nama}' berhasil diupdate.";
        Response::redirect('/admin/jenis-tagihan');
    }

    public function toggleStatus(string $id): void
    {
        $tenantId = $this->tenantId();
        $id = (int) $id;
        $type = InvoiceType::findByIdAndTenant($id, $tenantId);

        if (!$type) {
            $_SESSION['flash_error'] = 'Jenis tagihan tidak ditemukan.';
            Response::redirect('/admin/jenis-tagihan');
        }

        InvoiceType::toggleStatus($id, $tenantId);
        $newStatus = $type['status'] === 'active' ? 'inactive' : 'active';

        AuditLog::write('invoice_type.toggle', 'invoice_types', $id, ['status' => $newStatus]);

        $_SESSION['flash_success'] = "Status berhasil diubah.";
        Response::redirect('/admin/jenis-tagihan');
    }
}