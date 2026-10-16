<?php
use App\Helpers\Security;

$appName = $_ENV['APP_NAME']    ?? 'CekTagihan';
$tagline = $_ENV['APP_TAGLINE'] ?? 'Bayar tagihan dari mana saja!';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($title ?? 'Cek Tagihan') ?> — <?= Security::e($appName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-brand-50 via-white to-brand-50 min-h-screen">

<div class="min-h-screen flex flex-col">

    <!-- Header -->
    <header class="px-4 py-6">
        <div class="max-w-md mx-auto text-center">
            <a href="/" class="inline-block group">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 shadow-lg shadow-brand-200 mb-3 group-hover:scale-105 transition">
                    <span class="text-white text-2xl font-bold">C</span>
                </div>
            </a>
            <a href="/" class="block">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-brand-600 to-teal-600 bg-clip-text text-transparent">
                    <?= Security::e($appName) ?>
                </h1>
            </a>
            <p class="text-sm text-gray-500 mt-1"><?= Security::e($tagline) ?></p>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1 px-4 pb-8">
        <div class="max-w-md mx-auto">
            <?= $content ?? '' ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="px-4 py-6 text-center">
        <p class="text-xs text-gray-400">
            &copy; <?= date('Y') ?> <?= Security::e($appName) ?>.id
        </p>
    </footer>

</div>

</body>
</html>