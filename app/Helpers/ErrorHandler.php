<?php
declare(strict_types=1);

namespace App\Helpers;

class ErrorHandler
{
    /**
     * Register error & exception handlers.
     */
    public static function register(): void
    {
        // Exception handler
        set_exception_handler([self::class, 'handleException']);

        // Error handler
        set_error_handler([self::class, 'handleError']);

        // Shutdown handler (untuk fatal error)
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function handleException(\Throwable $e): void
    {
        self::log($e);

        $isProduction = ($_ENV['APP_ENV'] ?? 'local') === 'production';

        if ($isProduction) {
            self::renderProductionError();
        } else {
            self::renderLocalError($e);
        }
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            self::log(new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            ));

            $isProduction = ($_ENV['APP_ENV'] ?? 'local') === 'production';
            if ($isProduction) {
                self::renderProductionError();
            }
        }
    }

    private static function log(\Throwable $e): void
    {
        $logDir = __DIR__ . '/../../storage/logs/';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'app-error.log';
        $timestamp = date('Y-m-d H:i:s');
        $message = sprintf(
            "[%s] %s: %s in %s:%d\nStack trace:\n%s\n\n",
            $timestamp,
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        @file_put_contents($logFile, $message, FILE_APPEND);
    }

    private static function renderProductionError(): void
    {
        http_response_code(500);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Terjadi Kesalahan — CekTagihan</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-800 mb-2">Terjadi Kesalahan</h1>
                <p class="text-sm text-gray-500 mb-6">
                    Maaf, terjadi kesalahan pada sistem. Tim kami sudah menerima notifikasi dan akan segera memperbaikinya.
                </p>
                <a href="/"
                   class="inline-block bg-gradient-to-r from-brand-500 to-teal-600 text-white font-semibold px-6 py-3 rounded-xl shadow-md hover:shadow-lg transition">
                    Kembali ke Beranda
                </a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    private static function renderLocalError(\Throwable $e): void
    {
        http_response_code(500);
        $isProduction = false;
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Error (Local) — CekTagihan</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-6 mb-4">
                    <h1 class="text-lg font-bold text-red-800 mb-2">
                        <?= htmlspecialchars(get_class($e)) ?>
                    </h1>
                    <p class="text-red-700 font-medium mb-3">
                        <?= htmlspecialchars($e->getMessage()) ?>
                    </p>
                    <p class="text-sm text-red-600 font-mono">
                        <?= htmlspecialchars($e->getFile()) ?>:<?= $e->getLine() ?>
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h2 class="font-semibold text-gray-700 mb-3">Stack Trace</h2>
                    <pre class="bg-gray-900 text-gray-100 text-xs p-4 rounded-xl overflow-x-auto"><?= htmlspecialchars($e->getTraceAsString()) ?></pre>
                </div>

                <p class="text-center text-sm text-gray-500 mt-6">
                    Mode debug aktif. Di production, halaman ini akan menampilkan pesan ramah.
                </p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}