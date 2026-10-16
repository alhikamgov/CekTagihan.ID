<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\AuditLog;
use App\Helpers\RateLimiter;
use App\Models\User;
use App\Models\Tenant;

class AuthController
{
    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            Response::redirect('/dashboard');
        }

        Response::view('auth/login', [
            'csrf'  => Security::csrfToken(),
            'error' => $_SESSION['flash_error'] ?? null,
        ]);

        unset($_SESSION['flash_error']);
    }

    public function login(): void
{
    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid. Coba lagi.';
        Response::redirect('/login');
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $_SESSION['flash_error'] = 'Email dan password wajib diisi.';
        Response::redirect('/login');
    }

    // Cek rate limit SEBELUM verifikasi
    if (!RateLimiter::check('login', 5, 900)) {
        $_SESSION['flash_error'] = 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.';
        Response::redirect('/login');
    }

    $user = User::verifyPassword($email, $password);

    if (!$user) {
        // Kalau gagal, counter naik (sudah naik di check())
        $_SESSION['flash_error'] = 'Email atau password salah.';
        Response::redirect('/login');
    }

    // Kalau sukses, reset counter
    RateLimiter::reset('login');

    session_regenerate_id(true);
    $_SESSION['user_id']   = (int) $user['id'];
    $_SESSION['user_name'] = $user['nama'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['tenant_id'] = $user['tenant_id'] ? (int) $user['tenant_id'] : null;

    $_SESSION['tenant_nama'] = null;
    if ($user['tenant_id']) {
        $tenant = Tenant::findById((int) $user['tenant_id']);
        if ($tenant) {
            $_SESSION['tenant_nama'] = $tenant['nama'];
        }
    }

    User::updateLastLogin((int) $user['id']);

    Response::redirect('/dashboard');
}

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        Response::redirect('/login');
    }

    public function showChangePassword(): void
{
    if (empty($_SESSION['user_id'])) {
        Response::redirect('/login');
    }

    Response::view('auth/change-password', [
        'title'  => 'Ganti Password',
        'csrf'   => Security::csrfToken(),
        'errors' => [],
    ]);
}

public function changePassword(): void
{
    if (empty($_SESSION['user_id'])) {
        Response::redirect('/login');
    }

    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid.';
        Response::redirect('/account/change-password');
    }

    $current = trim($_POST['current_password'] ?? '');
    $new     = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    $errors = [];

    // Validasi
    if ($current === '') {
        $errors['current_password'] = 'Password lama wajib diisi.';
    }
    if ($new === '') {
        $errors['new_password'] = 'Password baru wajib diisi.';
    } elseif (strlen($new) < 8) {
        $errors['new_password'] = 'Password minimal 8 karakter.';
    } elseif (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
        $errors['new_password'] = 'Password harus kombinasi huruf dan angka.';
    }
    if ($confirm === '') {
        $errors['confirm_password'] = 'Konfirmasi password wajib diisi.';
    } elseif ($new !== $confirm) {
        $errors['confirm_password'] = 'Konfirmasi password tidak cocok.';
    }

    // Cek password lama
    if (empty($errors['current_password'])) {
        $user = User::findById((int) $_SESSION['user_id']);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            $errors['current_password'] = 'Password lama salah.';
        }
    }

    if (!empty($errors)) {
        Response::view('auth/change-password', [
            'title'  => 'Ganti Password',
            'csrf'   => Security::csrfToken(),
            'errors' => $errors,
        ]);
        return;
    }

    // Update password
    User::updatePassword((int) $_SESSION['user_id'], $new);

    AuditLog::write('user.change_password', 'users', (int) $_SESSION['user_id'], [
        'email' => $_SESSION['user_name'] ?? null,
    ]);

    $_SESSION['flash_success'] = 'Password berhasil diubah. Gunakan password baru untuk login berikutnya.';
    Response::redirect('/dashboard');
}
}