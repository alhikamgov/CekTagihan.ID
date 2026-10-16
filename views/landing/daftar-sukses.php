<?php
use App\Helpers\SiteInfo;

ob_start();
?>

<div class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-lg mx-auto px-4">

        <div class="bg-white rounded-3xl shadow-xl p-8 md:p-10 text-center border border-gray-100">

            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-100 mb-5">
                <svg class="w-10 h-10 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-800 mb-2">Pendaftaran Berhasil!</h1>
            <p class="text-sm text-gray-500 mb-6">
                Terima kasih sudah mendaftar. Tim kami akan meninjau pendaftaran Anda dan menghubungi
                via WhatsApp atau email dalam <strong>1×24 jam</strong>.
            </p>

            <div class="bg-brand-50 border border-brand-200 rounded-2xl p-5 text-left mb-6">
                <p class="text-xs font-semibold text-brand-900 mb-2">Langkah selanjutnya:</p>
                <ol class="text-xs text-brand-800 space-y-1.5 list-decimal list-inside">
                    <li>Tim kami review data Anda.</li>
                    <li>Kalau disetujui, akun admin akan dibuat.</li>
                    <li>Kredensial dikirim via WhatsApp/email.</li>
                    <li>Anda bisa mulai import pelanggan & tagihan.</li>
                </ol>
            </div>

            <a href="/"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg shadow-brand-200 transition">
                <i data-lucide="home" class="w-4 h-4"></i>
                Kembali ke Beranda
            </a>

            <?php if (SiteInfo::whatsappFormatted()): ?>
                <p class="text-xs text-gray-400 mt-6">
                    Ada pertanyaan?
                    <a href="https://wa.me/<?= Security::e(SiteInfo::whatsapp()) ?>" target="_blank" class="text-brand-600 hover:underline">
                        Hubungi kami
                    </a>
                </p>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';