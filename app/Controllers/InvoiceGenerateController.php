<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Invoice;
use App\Models\InvoiceType;
use App\Models\Payer;

class InvoiceGenerateController
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
     * Form generate massal.
     */
    public function form(): void
    {
        $tenantId = $this->tenantId();

        $payers = Payer::allByTenant($tenantId, '', 'active');
        if (empty($payers)) {
            $_SESSION['flash_error'] = 'Tambahkan pelanggan dulu sebelum buat banyak tagihan.';
            Response::redirect('/admin/payers');
        }

        // Ambil jenis tagihan aktif
        $jenisList = InvoiceType::activeByTenant($tenantId);
        if (empty($jenisList)) {
            $_SESSION['flash_error'] = 'Tambahkan jenis tagihan dulu.';
            Response::redirect('/admin/jenis-tagihan');
        }

        Response::view('admin/invoices/generate-form', [
            'title'     => 'Buat Banyak Tagihan',
            'payers'    => $payers,
            'jenisList' => $jenisList,
            'csrf'      => Security::csrfToken(),
            'errors'    => [],
            'old'       => [],
        ]);
    }

    /**
     * Preview — tampilkan daftar payer yang akan dapat invoice.
     */
    public function preview(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/invoices/generate');
        }

        // Ambil input
        $jenis   = trim($_POST['jenis'] ?? '');
        $periode = trim($_POST['periode'] ?? '');
        $jumlah  = (float) ($_POST['jumlah'] ?? 0);
        $dueDate = trim($_POST['due_date'] ?? '');
        $exclude = $_POST['exclude'] ?? [];

        // Ambil jenis tagihan aktif dari DB
        $jenisList    = InvoiceType::activeByTenant($tenantId);
        $allowedJenis = array_column($jenisList, 'nama');

        // Validasi
        $errors = [];

        if (!in_array($jenis, $allowedJenis, true)) {
            $errors['jenis'] = 'Jenis tidak valid.';
        }
        if ($periode === '') {
            $errors['periode'] = 'Periode wajib diisi.';
        }
        if ($jumlah <= 0) {
            $errors['jumlah'] = 'Jumlah harus lebih dari 0.';
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $errors['due_date'] = 'Tanggal jatuh tempo tidak valid.';
        }

        $payers = Payer::allByTenant($tenantId, '', 'active');

        if (!empty($errors)) {
            Response::view('admin/invoices/generate-form', [
                'title'     => 'Buat Banyak Tagihan',
                'payers'    => $payers,
                'jenisList' => $jenisList,
                'csrf'      => Security::csrfToken(),
                'errors'    => $errors,
                'old'       => $_POST,
            ]);
            return;
        }

        // Proses per payer
        $toGenerate = [];
        $skipped    = [];

        foreach ($payers as $p) {
            if (in_array((string) $p['id'], $exclude, true)) {
                continue;
            }

            // Cek duplikat
            $dup = Invoice::findDuplicate($tenantId, (int) $p['id'], $jenis, $periode);
            if ($dup) {
                $skipped[] = [
                    'payer'  => $p,
                    'reason' => "Sudah ada invoice {$jenis} untuk periode {$periode}",
                ];
                continue;
            }

            $toGenerate[] = $p;
        }

        // Simpan input di session untuk commit nanti
        $_SESSION['generate_input'] = [
            'jenis'     => $jenis,
            'periode'   => $periode,
            'jumlah'    => $jumlah,
            'due_date'  => $dueDate,
            'payer_ids' => array_map(fn($p) => (int) $p['id'], $toGenerate),
        ];

        Response::view('admin/invoices/generate-preview', [
            'title'      => 'Preview - Buat Banyak Tagihan',
            'jenis'      => $jenis,
            'periode'    => $periode,
            'jumlah'     => $jumlah,
            'dueDate'    => $dueDate,
            'toGenerate' => $toGenerate,
            'skipped'    => $skipped,
            'csrf'       => Security::csrfToken(),
        ]);
    }

    /**
     * Commit — batch insert ke DB.
     */
    public function commit(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/invoices/generate');
        }

        $input = $_SESSION['generate_input'] ?? null;
        if (!$input || empty($input['payer_ids'])) {
            $_SESSION['flash_error'] = 'Tidak ada data untuk di-generate. Ulangi dari awal.';
            Response::redirect('/admin/invoices/generate');
        }

        // Susun rows (tanpa deskripsi)
        $rows = [];
        foreach ($input['payer_ids'] as $payerId) {
            $rows[] = [
                'payer_id' => $payerId,
                'jenis'    => $input['jenis'],
                'periode'  => $input['periode'],
                'jumlah'   => $input['jumlah'],
                'due_date' => $input['due_date'],
            ];
        }

        try {
            $inserted = Invoice::bulkCreate($tenantId, $rows);

            AuditLog::write('invoice.generate_bulk', 'invoices', null, [
                'jenis'    => $input['jenis'],
                'periode'  => $input['periode'],
                'inserted' => $inserted,
                'jumlah'   => $input['jumlah'],
            ]);

            unset($_SESSION['generate_input']);

            $_SESSION['flash_success'] = "Berhasil generate {$inserted} invoice.";
            Response::redirect('/admin/invoices');

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Gagal generate: ' . $e->getMessage();
            Response::redirect('/admin/invoices/generate');
        }
    }
}