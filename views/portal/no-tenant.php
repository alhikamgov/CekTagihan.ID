<?php
ob_start();
?>

<div class="bg-white rounded-2xl shadow-sm p-8 text-center">
    <div class="text-4xl mb-3">🏢</div>
    <h3 class="font-semibold mb-1">Belum Ada Pengelola</h3>
    <p class="text-sm text-gray-500">
        Saat ini belum ada pengelola yang terdaftar. Coba lagi nanti atau hubungi admin.
    </p>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';