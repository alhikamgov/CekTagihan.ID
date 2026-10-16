<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Payer;
use App\Services\ExcelImportService;

class PayerImportController
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
     * Halaman upload.
     */
    public function showUpload(): void
    {
        $this->tenantId();

        Response::view('admin/payers/import-upload', [
            'title' => 'Import Pelanggan dari Excel',
            'csrf'  => Security::csrfToken(),
            'error' => $_SESSION['flash_error'] ?? null,
        ]);

        unset($_SESSION['flash_error']);
    }

    /**
     * Handle upload → baca → validasi → tampilkan preview.
     */
    public function upload(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/payers/import');
        }

        // 1. Cek file ada
        if (empty($_FILES['file']['tmp_name']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'File belum dipilih atau gagal upload.';
            Response::redirect('/admin/payers/import');
        }

        $file = $_FILES['file'];

        // 2. Validasi ekstensi
        $allowedExt = ['xlsx', 'xls', 'csv'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            $_SESSION['flash_error'] = 'Format file harus .xlsx, .xls, atau .csv';
            Response::redirect('/admin/payers/import');
        }

        // 3. Validasi ukuran (max 5 MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $_SESSION['flash_error'] = 'Ukuran file maksimal 5 MB.';
            Response::redirect('/admin/payers/import');
        }

        // 4. Validasi MIME type
        $allowedMime = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
            'text/plain',
            'application/csv',
        ];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMime, true)) {
            $_SESSION['flash_error'] = 'Tipe file tidak didukung.';
            Response::redirect('/admin/payers/import');
        }

        // 5. Randomize nama file
        $randomName = 'import_' . $tenantId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $uploadDir  = __DIR__ . '/../../storage/uploads/';
        $tmpPath    = $uploadDir . $randomName;

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
            $_SESSION['flash_error'] = 'Gagal menyimpan file.';
            Response::redirect('/admin/payers/import');
        }

        @chmod($tmpPath, 0600);

        // 6. Baca file (ExcelImportService sudah handle mapping header)
        try {
            $rows = ExcelImportService::readFile($tmpPath);
        } catch (\Throwable $e) {
            @unlink($tmpPath);
            $_SESSION['flash_error'] = 'Gagal membaca file: ' . $e->getMessage();
            Response::redirect('/admin/payers/import');
        }

        if (empty($rows)) {
            @unlink($tmpPath);
            $_SESSION['flash_error'] = 'File kosong atau tidak ada data.';
            Response::redirect('/admin/payers/import');
        }

        // Ambil nomor induk yang sudah ada di DB
        $existing = Payer::allByTenant($tenantId);
        $existingNomorInduk = array_column($existing, 'nomor_induk');

        // Validasi
        $result = ExcelImportService::validateRows($rows, $existingNomorInduk);

        $_SESSION['import_file'] = $tmpPath;

        Response::view('admin/payers/import-preview', [
            'title'   => 'Preview Import Pelanggan',
            'valid'   => $result['valid'],
            'invalid' => $result['invalid'],
            'total'   => $result['total'],
            'csrf'    => Security::csrfToken(),
        ]);
    }

    /**
     * Commit import ke DB.
     */
    public function commit(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/payers/import');
        }

        $tmpPath = $_SESSION['import_file'] ?? null;
        if (!$tmpPath || !file_exists($tmpPath)) {
            $_SESSION['flash_error'] = 'File import sudah kedaluwarsa. Upload ulang.';
            Response::redirect('/admin/payers/import');
        }

        $uploadDir = realpath(__DIR__ . '/../../storage/uploads/');
        $realPath  = realpath($tmpPath);
        if (!$realPath || strpos($realPath, $uploadDir) !== 0) {
            @unlink($tmpPath);
            unset($_SESSION['import_file']);
            $_SESSION['flash_error'] = 'File tidak valid.';
            Response::redirect('/admin/payers/import');
        }

        $rows = ExcelImportService::readFile($tmpPath);

        $existing = Payer::allByTenant($tenantId);
        $existingNomorInduk = array_column($existing, 'nomor_induk');

        $result = ExcelImportService::validateRows($rows, $existingNomorInduk);
        $valid = $result['valid'];

        if (empty($valid)) {
            @unlink($tmpPath);
            unset($_SESSION['import_file']);
            $_SESSION['flash_error'] = 'Tidak ada baris valid untuk diimport.';
            Response::redirect('/admin/payers/import');
        }

        $db = Payer::db();
        $db->beginTransaction();

        try {
            $inserted = 0;
            foreach ($valid as $row) {
                // Terima kedua key — defensive
                $nomorInduk = $row['nomor_pelanggan'] ?? $row['nomor_induk'] ?? null;
                if (!$nomorInduk) {
                    continue;
                }

                Payer::create($tenantId, [
                    'nomor_induk' => $nomorInduk,
                    'nama'        => $row['nama'],
                    'whatsapp'    => $row['whatsapp'] ?? null,
                    'status'      => 'active',
                ]);
                $inserted++;
            }
            $db->commit();

            AuditLog::write('payer.import', 'payers', null, [
                'total_file' => $result['total'],
                'inserted'   => $inserted,
                'invalid'    => count($result['invalid']),
            ]);

            @unlink($tmpPath);
            unset($_SESSION['import_file']);

            $_SESSION['flash_success'] = "Berhasil import {$inserted} pelanggan.";
            Response::redirect('/admin/payers');

        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal import: ' . $e->getMessage();
            Response::redirect('/admin/payers/import');
        }
    }

    /**
     * Download template CSV.
     */
    public function downloadTemplate(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="template_import_pelanggan.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['nomor_pelanggan', 'nama', 'whatsapp']);
        fputcsv($out, ['A101', 'Budi Santoso', '6281234567890']);
        fputcsv($out, ['A102', 'Siti Aminah', '6281298765432']);
        fclose($out);
        exit;
    }
}