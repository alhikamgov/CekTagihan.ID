<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

$metodeLabel = match($item['metode']) {
    'tunai'         => 'Tunai',
    'transfer_bank' => 'Transfer Bank',
    'lain'          => 'Lainnya',
    default         => $item['metode'],
};

ob_start();
?>

<div class="max-w-2xl mx-auto">

    <div class="mb-4">
        <a href="/admin/manual-payments" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-teal-100 flex items-center justify-center">
                <i data-lucide="receipt" class="w-6 h-6 text-teal-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold">Detail Pembayaran Manual</h3>
                <p class="text-sm text-gray-500">ID: #<?= str_pad((string) $item['id'], 5, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Pelanggan</span>
                <span class="font-medium"><?= Security::e($item['payer_nama']) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Nomor Pelanggan</span>
                <span class="font-mono"><?= Security::e($item['nomor_induk']) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Jenis Tagihan</span>
                <span><?= ucfirst(Security::e($item['jenis'])) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Periode</span>
                <span><?= Security::e($item['periode']) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Metode</span>
                <span><?= Security::e($metodeLabel) ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-gray-500">Tanggal Bayar</span>
                <span><?= $fmtTanggal($item['paid_at']) ?></span>
            </div>
            <?php if (!empty($item['catatan'])): ?>
                <div class="py-2 border-b border-gray-100">
                    <p class="text-gray-500 mb-1">Catatan</p>
                    <p class="text-gray-700"><?= nl2br(Security::e($item['catatan'])) ?></p>
                </div>
            <?php endif; ?>

            <div class="flex justify-between py-3 bg-teal-50 rounded-xl px-4 mt-3">
                <span class="font-semibold text-teal-900">Total</span>
                <span class="font-bold text-teal-700 text-lg"><?= $fmtRupiah($item['jumlah']) ?></span>
            </div>
        </div>

        <?php if (!empty($item['bukti_file'])): ?>
            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-700 mb-2">Bukti Pembayaran</p>
                <?php
                $ext = strtolower(pathinfo($item['bukti_file'], PATHINFO_EXTENSION));
                $fileUrl = '/storage/uploads/' . Security::e($item['bukti_file']);
                ?>
                <?php if (in_array($ext, ['jpg', 'jpeg', 'png'], true)): ?>
                    <img src="<?= $fileUrl ?>" alt="Bukti" class="rounded-xl max-w-full max-h-96 border border-gray-200">
                <?php else: ?>
                    <a href="<?= $fileUrl ?>" target="_blank"
                       class="inline-flex items-center gap-2 text-brand-600 hover:text-brand-800 font-medium">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Lihat Bukti (PDF)
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';