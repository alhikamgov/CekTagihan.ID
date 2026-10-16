<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';
$fmtTglOnly = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

$success = !empty($payment);

// Pastikan variabel ada walau view dipanggil tanpa data
$unpaidInvoices = $unpaidInvoices ?? [];

ob_start();
?>

<div class="bg-white rounded-3xl shadow-xl p-6 md:p-8 border border-gray-100 text-center">

    <?php if ($success): ?>

        <!-- Ikon sukses -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-100 mb-5">
            <svg class="w-10 h-10 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-800 mb-1">Pembayaran Berhasil!</h2>
        <p class="text-sm text-gray-500 mb-6">
            Terima kasih, pembayaran Anda sudah kami terima. Bukti sudah dikirim ke WhatsApp Anda.
        </p>

        <!-- Detail pembayaran -->
        <div class="bg-gray-50 rounded-2xl p-5 text-left space-y-3 mb-5">

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Order ID</span>
                <span class="font-mono text-xs text-gray-700">
                    <?= Security::e($payment['order_id']) ?>
                </span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Tanggal</span>
                <span class="text-gray-700">
                    <?= $fmtTanggal($payment['paid_at'] ?? $payment['created_at']) ?>
                </span>
            </div>

            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Status</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-brand-100 text-brand-700 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                    <?= ucfirst(Security::e($payment['status'])) ?>
                </span>
            </div>

            <div class="pt-3 border-t border-gray-200 flex justify-between">
                <span class="font-semibold text-gray-700">Total Dibayar</span>
                <span class="font-bold text-brand-600 text-lg">
                    <?= $fmtRupiah($payment['gross_amount']) ?>
                </span>
            </div>

        </div>

        <!-- Daftar tagihan yang dibayar -->
        <?php if (!empty($invoices)): ?>
            <div class="text-left mb-5">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">
                    Tagihan yang Dibayar
                </p>
                <div class="space-y-2">
                    <?php foreach ($invoices as $inv): ?>
                        <div class="flex justify-between items-start bg-brand-50 rounded-xl px-4 py-3 text-sm border border-brand-100">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800">
                                        <?= ucfirst(Security::e($inv['jenis'])) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?= Security::e($inv['periode']) ?>
                                    </p>
                                </div>
                            </div>
                            <p class="font-semibold text-gray-800 whitespace-nowrap">
                                <?= $fmtRupiah($inv['jumlah']) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ==================== TAGIHAN BELUM DIBAYAR ==================== -->
        <div class="text-left mb-5">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">
                Tagihan yang Belum Dibayar
            </p>

            <?php if (empty($unpaidInvoices)): ?>
                <div class="bg-gray-50 rounded-xl px-4 py-4 text-sm text-gray-500 text-center border border-gray-200">
                    🎉 Tidak ada tagihan lain yang belum dibayar.
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($unpaidInvoices as $inv): ?>
                        <div class="flex justify-between items-start bg-amber-50 rounded-xl px-4 py-3 text-sm border border-amber-100">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800">
                                        <?= ucfirst(Security::e($inv['jenis'])) ?>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?= Security::e($inv['periode']) ?>
                                        &middot; Jatuh tempo <?= $fmtTglOnly($inv['due_date']) ?>
                                    </p>
                                </div>
                            </div>
                            <p class="font-semibold text-gray-800 whitespace-nowrap">
                                <?= $fmtRupiah($inv['remaining'] ?? $inv['jumlah']) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tombol -->
        <div class="space-y-2">
            <?php if (!empty($unpaidInvoices)): ?>
                <a href="/portal/riwayat?order_id=<?= Security::e($payment['order_id']) ?>"
                class="block w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-brand-200 transition-all">
                    Lihat Riwayat Pembayaran
                </a>
            <?php else: ?>
                <a href="/portal/tagihan"
                   class="block w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-brand-200 transition-all">
                    Kembali ke Tagihan
                </a>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- Tidak ada data payment -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-100 mb-5">
            <svg class="w-10 h-10 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-xl font-bold text-gray-800 mb-1">Pembayaran Berhasil</h2>
        <p class="text-sm text-gray-500 mb-6">
            <?php if (!empty($orderId)): ?>
                Order ID: <span class="font-mono text-xs"><?= Security::e($orderId) ?></span><br>
            <?php endif; ?>
            Detail pembayaran tidak tersedia. Silakan cek tagihan Anda lagi.
        </p>

        <a href="/portal/tagihan"
           class="block w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-brand-200 transition-all">
            Kembali ke Tagihan
        </a>

    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';