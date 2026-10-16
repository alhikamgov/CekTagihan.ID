<?php
use App\Helpers\Security;

$fmtRupiah = fn($n) => $n > 0 ? 'Rp ' . number_format((float) $n, 0, ',', '.') : '—';

ob_start();
?>

<!-- Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 mb-6">
    <form method="GET" action="/admin/laporan/per-payer" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Dari Bulan</label>
            <input type="month" name="start" value="<?= Security::e($startBulan) ?>"
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Sampai Bulan</label>
            <input type="month" name="end" value="<?= Security::e($endBulan) ?>"
                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm">
        </div>

        <button type="submit"
                class="inline-flex items-center justify-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Tampilkan
        </button>

        <?php if (!empty($rows)): ?>
            <a href="/admin/laporan/export-per-payer?start=<?= urlencode($startBulan) ?>&end=<?= urlencode($endBulan) ?>"
               class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-brand transition">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export Excel
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Pelanggan</p>
        </div>
        <p class="text-2xl font-bold text-gray-800"><?= count($rows) ?></p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                <i data-lucide="calendar" class="w-5 h-5 text-teal-600"></i>
            </div>
            <p class="text-sm text-gray-500">Jumlah Periode</p>
        </div>
        <p class="text-2xl font-bold text-gray-800"><?= count($periodeList) ?></p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-amber-600"></i>
            </div>
            <p class="text-sm text-gray-500">Grand Total</p>
        </div>
        <p class="text-2xl font-bold text-amber-600">
            Rp <?= number_format((float) ($totals['total_nominal'] ?? 0), 0, ',', '.') ?>
        </p>
    </div>
</div>

<!-- Tabel Rekap -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center flex-wrap gap-2">
        <h3 class="font-semibold text-gray-700">Rekap Per Pelanggan</h3>
        <span class="text-xs text-gray-500">
            <?= date('M Y', strtotime($startBulan . '-01')) ?> — <?= date('M Y', strtotime($endBulan . '-01')) ?>
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-3 py-3 sticky left-0 bg-gray-50 z-20 min-w-[50px]">No</th>
                    <th class="px-3 py-3 sticky left-[50px] bg-gray-50 z-20 min-w-[130px]">No. Pelanggan</th>
                    <th class="px-3 py-3 sticky left-[180px] bg-gray-50 z-20 min-w-[180px]">Nama</th>
                    <?php foreach ($periodeList as $p): ?>
                        <th class="px-3 py-3 text-right whitespace-nowrap min-w-[110px]">
                            <?= date('M Y', strtotime($p . '-01')) ?>
                        </th>
                    <?php endforeach; ?>
                    <th class="px-3 py-3 text-right bg-brand-50 text-brand-800 font-bold whitespace-nowrap min-w-[130px]">
                        Total
                    </th>
                    <th class="px-3 py-3 text-center whitespace-nowrap min-w-[80px]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="<?= 5 + count($periodeList) ?>" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">Belum ada data</p>
                                    <p class="text-sm text-gray-500 mt-0.5">Tidak ada invoice di rentang ini.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-3 py-3 text-gray-500 sticky left-0 bg-white z-10"><?= $i + 1 ?></td>
                            <td class="px-3 py-3 font-mono text-gray-600 sticky left-[50px] bg-white z-10">
                                <?= Security::e($r['nomor_induk']) ?>
                            </td>
                            <td class="px-3 py-3 font-medium text-gray-800 sticky left-[180px] bg-white z-10">
                                <?= Security::e($r['payer_nama']) ?>
                            </td>
                            <?php foreach ($periodeList as $p): ?>
                                <?php $key = 'periode_' . str_replace('-', '_', $p); ?>
                                <td class="px-3 py-3 text-right text-gray-600 whitespace-nowrap">
                                    <?= $fmtRupiah($r[$key] ?? 0) ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="px-3 py-3 text-right font-bold text-brand-700 bg-brand-50/30 whitespace-nowrap">
                                Rp <?= number_format((float) $r['total_nominal'], 0, ',', '.') ?>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="/admin/laporan/per-payer/detail/<?= (int) $r['payer_id'] ?>?start=<?= urlencode($startBulan) ?>&end=<?= urlencode($endBulan) ?>"
                                   class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-800 font-medium text-xs">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Baris Total -->
                    <tr class="bg-brand-50 font-bold">
                        <td class="px-3 py-3 sticky left-0 bg-brand-50 z-10"></td>
                        <td class="px-3 py-3 sticky left-[50px] bg-brand-50 z-10"></td>
                        <td class="px-3 py-3 text-right sticky left-[180px] bg-brand-50 z-10 text-brand-800">
                            TOTAL
                        </td>
                        <?php foreach ($periodeList as $p): ?>
                            <?php $key = 'periode_' . str_replace('-', '_', $p); ?>
                            <td class="px-3 py-3 text-right text-brand-800 whitespace-nowrap">
                                Rp <?= number_format((float) ($totals[$key] ?? 0), 0, ',', '.') ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="px-3 py-3 text-right text-brand-800 text-base whitespace-nowrap">
                            Rp <?= number_format((float) ($totals['total_nominal'] ?? 0), 0, ',', '.') ?>
                        </td>
                        <td class="px-3 py-3"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Link -->
<div class="mt-4">
    <a href="/admin/laporan"
       class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl border border-gray-200 transition">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Laporan Bulanan
    </a>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';