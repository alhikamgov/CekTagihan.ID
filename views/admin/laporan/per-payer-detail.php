<?php
use App\Helpers\Security;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

ob_start();
?>

<!-- Tombol Kembali -->
<div class="mb-4">
    <a href="/admin/laporan/per-payer?start=<?= urlencode($startBulan) ?>&end=<?= urlencode($endBulan) ?>"
       class="inline-flex items-center gap-2 text-sm text-brand-600 hover:text-brand-800 font-medium">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali ke Rekap
    </a>
</div>

<!-- Info Pelanggan -->
<div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-3xl shadow-xl shadow-brand-200/50 p-6 text-white mb-6">
    <div class="flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0">
            <span class="text-3xl font-bold"><?= strtoupper(substr($payer['nama'], 0, 1)) ?></span>
        </div>
        <div class="min-w-0">
            <h2 class="text-xl font-bold truncate"><?= Security::e($payer['nama']) ?></h2>
            <p class="text-sm text-white/80 font-mono"><?= Security::e($payer['nomor_induk']) ?></p>
        </div>
    </div>
</div>

<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-3xl shadow-sm p-5 border border-brand-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Lunas</p>
        </div>
        <p class="text-2xl font-bold text-brand-600">
            <?= $fmtRupiah($summary['total_lunas'] ?? 0) ?>
        </p>
        <p class="text-xs text-gray-500 mt-1">
            <?= (int) ($summary['jumlah_lunas'] ?? 0) ?> invoice
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-red-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Tunggakan</p>
        </div>
        <p class="text-2xl font-bold text-red-600">
            <?= $fmtRupiah($summary['total_tunggakan'] ?? 0) ?>
        </p>
        <p class="text-xs text-gray-500 mt-1">
            <?= (int) ($summary['jumlah_tunggakan'] ?? 0) ?> invoice
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-gray-600"></i>
            </div>
            <p class="text-sm text-gray-500">Grand Total</p>
        </div>
        <p class="text-2xl font-bold text-gray-800">
            <?= $fmtRupiah(($summary['total_lunas'] ?? 0) + ($summary['total_tunggakan'] ?? 0)) ?>
        </p>
    </div>
</div>

<!-- Detail Per Bulan -->
<?php if (empty($grouped)): ?>
    <div class="bg-white rounded-3xl shadow-sm p-12 text-center border border-gray-100">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
            <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
        </div>
        <h3 class="font-bold text-gray-800 mb-1">Belum Ada Tagihan</h3>
        <p class="text-sm text-gray-500">Tidak ada invoice di rentang periode ini.</p>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php foreach ($grouped as $g): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Bulan -->
                <div class="px-5 py-3 bg-gradient-to-r from-brand-50 to-teal-50 border-b border-gray-100 flex justify-between items-center flex-wrap gap-2">
                    <h3 class="font-bold text-gray-800"><?= Security::e($g['label']) ?></h3>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-brand-600 font-medium">
                            Lunas: <?= $fmtRupiah($g['lunas']) ?>
                        </span>
                        <?php if ($g['unpaid'] > 0): ?>
                            <span class="text-red-600 font-medium">
                                Tunggakan: <?= $fmtRupiah($g['unpaid']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Daftar Item -->
                <div class="divide-y divide-gray-100">
                    <?php foreach ($g['items'] as $item): ?>
                        <?php $isPaid = $item['status'] === 'paid'; ?>
                        <div class="px-5 py-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0
                                            <?= $isPaid ? 'bg-brand-100' : 'bg-red-100' ?>">
                                    <i data-lucide="<?= $isPaid ? 'check' : 'x' ?>"
                                       class="w-5 h-5 <?= $isPaid ? 'text-brand-600' : 'text-red-600' ?>"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-gray-800"><?= ucfirst(Security::e($item['jenis'])) ?></p>
                                    <p class="text-xs text-gray-500">
                                        Jatuh tempo: <?= date('d M Y', strtotime($item['due_date'])) ?>
                                        <?php if ($isPaid && $item['paid_at']): ?>
                                            • Dibayar: <?= date('d M Y', strtotime($item['paid_at'])) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold <?= $isPaid ? 'text-gray-800' : 'text-red-600' ?>">
                                    <?= $fmtRupiah($item['jumlah']) ?>
                                </p>
                                <span class="inline-block mt-0.5 px-2 py-0.5 text-xs rounded-full
                                             <?= $isPaid ? 'bg-brand-50 text-brand-700' : 'bg-red-50 text-red-700' ?>">
                                    <?= $isPaid ? 'Lunas' : 'Belum Bayar' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Footer Total -->
                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-600">Total Bulan Ini</span>
                    <span class="font-bold text-gray-800"><?= $fmtRupiah($g['total']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';