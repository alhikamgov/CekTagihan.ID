<?php
use App\Helpers\Security;

$appName = $_ENV['APP_NAME'] ?? 'CekTagihan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan — <?= Security::e($appName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',
                            400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',
                            800:'#065f46',900:'#064e3b'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-brand-50 via-white to-teal-50 min-h-screen flex items-center justify-center p-4">

<div class="max-w-md w-full text-center">

    <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-brand-100 mb-6">
        <span class="text-5xl font-bold text-brand-600">404</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-800 mb-2">Halaman Tidak Ditemukan</h1>
    <p class="text-sm text-gray-500 mb-8">
        Halaman yang Anda cari tidak ada atau sudah dipindahkan.
    </p>

    <div class="flex flex-col sm:flex-row gap-2 justify-center">
        <a href="/"
           class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold px-6 py-3 rounded-xl shadow-brand transition">
            Kembali ke Beranda
        </a>
        <a href="/portal"
           class="inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold px-6 py-3 rounded-xl border border-gray-200 transition">
            Cek Tagihan
        </a>
    </div>

</div>

</body>
</html>