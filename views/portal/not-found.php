<?php
use App\Helpers\Security;

ob_start();
?>

<div class="bg-white rounded-3xl shadow-xl p-8 text-center border border-gray-100">

    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mb-5">
        <i data-lucide="map-pin-off" class="w-10 h-10 text-red-600"></i>
    </div>

    <h2 class="text-xl font-bold text-gray-800 mb-1">Lokasi Tidak Ditemukan</h2>
    <p class="text-sm text-gray-500 mb-6">
        Lokasi yang Anda cari tidak tersedia atau sudah tidak aktif.
    </p>

    <a href="/portal"
       class="block w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-brand-200 transition-all">
        Pilih Lokasi Lain
    </a>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';