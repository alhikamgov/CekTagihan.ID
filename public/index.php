<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Helpers\ErrorHandler;

ErrorHandler::register();

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Error handling berdasarkan environment
$isProduction = ($_ENV['APP_ENV'] ?? 'local') === 'production';

if ($isProduction) {
    // Production: sembunyikan error, log ke file
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../storage/logs/php-error.log');
    error_reporting(E_ALL);
} else {
    // Local: tampilkan error untuk debug
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../storage/logs/php-error.log');
    error_reporting(E_ALL);
}

session_start([
    'cookie_httponly'  => true,
    'cookie_samesite'  => 'Strict',
    'cookie_secure'    => ($_ENV['APP_ENV'] ?? 'local') === 'production',
    'cookie_lifetime'  => 0,
    'use_strict_mode'  => true,
    'use_only_cookies' => true,
]);

// Session timeout: 8 jam
$timeout = 8 * 60 * 60; // 8 jam = 28.800 detik
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash_error'] = 'Sesi Anda berakhir karena tidak aktif. Silakan login lagi.';
}

$_SESSION['last_activity'] = time();

require __DIR__ . '/../routes/web.php';
