<?php
use App\Helpers\Security;
use App\Helpers\SiteInfo;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

ob_start();
?>

<div class="bg-white rounded-3xl shadow-xl p-6 md:p-8 border border-gray-100 text-center">

    <!-- Ikon gagal -->
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mb-5">
        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-1">Pembayaran Belum Berhasil</h2>
    <p class="text-sm text-gray-500 mb-6">
        Jangan khawatir, tidak ada dana yang terpotong. Anda bisa coba bayar lagi.
    </p>

    <!-- Info Transaksi -->
    <?php if (!empty($payment)): ?>
        <div class="bg-gray-50 rounded-2xl p-5 text-left space-y-3 mb-5">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Order ID</span>
                <span class="font-mono text-xs text-gray-700">
                    <?= Security::e($payment['order_id']) ?>
                </span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Tanggal</span>
                <span class="text-gray-700"><?= $fmtTanggal($payment['created_at']) ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Jumlah</span>
                <span class="font-semibold text-gray-700"><?= $fmtRupiah($payment['gross_amount']) ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Status</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    <?= ucfirst(Security::e($payment['status'])) ?>
                </span>
            </div>
        </div>
    <?php elseif (!empty($orderId)): ?>
        <div class="bg-gray-50 rounded-2xl p-4 text-left mb-5">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Order ID</span>
                <span class="font-mono text-xs text-gray-700"><?= Security::e($orderId) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Kemungkinan Penyebab -->
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-left mb-6">
        <div class="flex items-start gap-2">
            <span class="text-amber-600 flex-shrink-0 mt-0.5">⚠</span>
            <div>
                <p class="text-xs font-semibold text-amber-900 mb-2">Kemungkinan penyebab:</p>
                <ul class="text-xs text-amber-800 space-y-1 list-disc list-inside">
                    <li>Pembayaran dibatalkan atau kedaluwarsa</li>
                    <li>Saldo tidak mencukupi</li>
                    <li>Koneksi terputus saat pembayaran</li>
                    <li>Metode pembayaran tidak didukung</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="space-y-2">
        <a href="/portal/tagihan"
           class="block w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-brand-200 transition-all">
            Coba Bayar Lagi
        </a>
        <a href="/portal"
           class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 rounded-xl transition">
            Kembali ke Portal
        </a>
    </div>

    <!-- Info Kontak -->
    <p class="text-xs text-gray-400 mt-6">
    Masih bermasalah? Hubungi admin pengelola
    <?php if (SiteInfo::whatsappFormatted()): ?>
        atau <a href="https://wa.me/<?= Security::e(SiteInfo::whatsapp()) ?>" target="_blank" class="text-brand-600 hover:underline"><?= Security::e(SiteInfo::whatsappFormatted()) ?></a>
    <?php endif; ?>
    .
</p>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';