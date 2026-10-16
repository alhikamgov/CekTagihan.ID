<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\RateLimiter;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Models\TenantRegistration;

class DaftarController
{
    /**
     * Halaman form daftar.
     */
    public function index(): void
    {
        Response::view('landing/daftar', [
            'title' => 'Daftar Jadi Pengelola',
            'csrf'  => Security::csrfToken(),
            'error' => $_SESSION['daftar_error'] ?? null,
            'old'   => $_SESSION['daftar_old'] ?? [],
        ]);

        unset($_SESSION['daftar_error'], $_SESSION['daftar_old']);
    }

    /**
     * Proses submit form.
     */
    public function submit(): void
    {
        // Rate limit: max 3 submit per jam per IP
        if (!RateLimiter::check('daftar_tenant', 3, 3600)) {
            $_SESSION['daftar_error'] = 'Terlalu banyak percobaan. Coba lagi dalam 1 jam.';
            Response::redirect('/daftar');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['daftar_error'] = 'Sesi tidak valid. Coba lagi.';
            Response::redirect('/daftar');
        }

        // Honeypot — kalau diisi, berarti bot
        if (!empty($_POST['website_url'])) {
            Response::redirect('/daftar/sukses');
        }

        $validator = new Validator($_POST);
        $validator->required('nama_pic', 'Nama')
                  ->required('email', 'Email')
                  ->email('email', 'Email')
                  ->required('telepon', 'Nomor WhatsApp')
                  ->required('nama_bisnis', 'Nama Bisnis')
                  ->required('jenis_bisnis', 'Jenis Bisnis')
                  ->maxLength('nama_pic', 120, 'Nama')
                  ->maxLength('nama_bisnis', 150, 'Nama Bisnis');

        $jenisValid = ['ruko','apartemen','kontrakan','sekolah','pesantren','komunitas','tempat_ibadah','lain'];
        $jenis = trim($_POST['jenis_bisnis'] ?? '');
        if (!in_array($jenis, $jenisValid, true)) {
            $errors = $validator->errors();
            $errors['jenis_bisnis'] = 'Jenis bisnis tidak valid.';
        }

        $email = trim($_POST['email'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');

        // Validasi telepon
        $teleponClean = preg_replace('/[^0-9]/', '', $telepon);
        if (strlen($teleponClean) < 10) {
            $errors = $validator->errors();
            $errors['telepon'] = 'Nomor WhatsApp minimal 10 digit.';
        } else {
            if (str_starts_with($teleponClean, '0')) {
                $teleponClean = '62' . substr($teleponClean, 1);
            } elseif (str_starts_with($teleponClean, '8')) {
                $teleponClean = '62' . $teleponClean;
            }
        }

        // Cek email sudah pernah daftar
        if (!empty($email) && TenantRegistration::findByEmail($email)) {
            $errors = $validator->errors();
            $errors['email'] = 'Email ini sudah pernah mendaftar. Tunggu konfirmasi kami.';
        }

        if (!empty($errors) || $validator->fails()) {
            $_SESSION['daftar_error'] = $validator->firstError() ?? 'Ada kesalahan pada form.';
            $_SESSION['daftar_old']   = $_POST;
            Response::redirect('/daftar');
        }

        $id = TenantRegistration::create([
            'nama_pic'    => trim($_POST['nama_pic']),
            'email'       => $email,
            'telepon'     => $teleponClean,
            'nama_bisnis' => trim($_POST['nama_bisnis']),
            'jenis_bisnis' => $jenis,
            'jumlah_unit' => (int) ($_POST['jumlah_unit'] ?? 0),
            'alamat'      => trim($_POST['alamat'] ?? ''),
            'kota'        => trim($_POST['kota'] ?? ''),
            'catatan'     => trim($_POST['catatan'] ?? ''),
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        AuditLog::write('tenant_registration.create', 'tenant_registrations', $id, [
            'nama_bisnis' => trim($_POST['nama_bisnis']),
            'email'       => $email,
        ]);

        Response::redirect('/daftar/sukses');
    }

    /**
     * Halaman sukses.
     */
    public function sukses(): void
    {
        Response::view('landing/daftar-sukses', [
            'title' => 'Pendaftaran Berhasil',
        ]);
    }
}