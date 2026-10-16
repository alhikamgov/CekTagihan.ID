<?php
use App\Helpers\Security;
use App\Helpers\Pagination;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

ob_start();
?>

<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded-3xl shadow-sm p-5 border border-brand-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm text-gray-500">Transaksi Manual</p>
        </div>
        <p class="text-3xl font-bold text-gray-800">
            <?= (int) ($summary['total_transaksi'] ?? 0) ?>
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-teal-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-teal-600"></i>
            </div>
            <p class="text-sm text-gray-500">Pendapatan Manual</p>
        </div>
        <p class="text-3xl font-bold text-teal-600">
            <?= $fmtRupiah($summary['total_nominal'] ?? 0) ?>
        </p>
    </div>
</div>

<!-- Header -->
<div class="flex justify-between items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="text-lg font-semibold">Daftar Pembayaran Manual</h3>
        <p class="text-sm text-gray-500">Total: <?= (int) $total ?> transaksi</p>
    </div>
    <a href="/admin/manual-payments/create"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-brand transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Catat Pembayaran
    </a>
</div>

<!-- Filter -->
<form method="GET" action="/admin/manual-payments" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2">
    <div class="relative md:col-span-2">
        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="q" value="<?= Security::e($filters['q']) ?>"
               placeholder="Cari nama / nomor pelanggan"
               class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-sm transition">
    </div>

    <select name="metode" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
        <option value="">Semua Metode</option>
        <option value="tunai"         <?= $filters['metode'] === 'tunai'         ? 'selected' : '' ?>>Tunai</option>
        <option value="transfer_bank" <?= $filters['metode'] === 'transfer_bank' ? 'selected' : '' ?>>Transfer Bank</option>
        <option value="lain"          <?= $filters['metode'] === 'lain'          ? 'selected' : '' ?>>Lainnya</option>
    </select>

    <input type="date" name="start" value="<?= Security::e($filters['start']) ?>"
           class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm"
           placeholder="Dari tanggal">

    <input type="date" name="end" value="<?= Security::e($filters['end']) ?>"
           class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm"
           placeholder="Sampai tanggal">

    <div class="md:col-span-5 flex gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Filter
        </button>
        <?php if (array_filter($filters)): ?>
            <a href="/admin/manual-payments"
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                Reset
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Tabel -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Tagihan</th>
                    <th class="px-4 py-3">Metode</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="receipt" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">
                                        <?= array_filter($filters) ? 'Tidak ada yang cocok' : 'Belum ada pembayaran manual' ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        <?= array_filter($filters) ? 'Coba ubah filter atau reset.' : 'Catat pembayaran tunai/transfer di sini.' ?>
                                    </p>
                                </div>
                                <?php if (!array_filter($filters)): ?>
                                    <a href="/admin/manual-payments/create"
                                       class="mt-2 inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                        Catat Pembayaran Pertama
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $mp): ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                <?= $fmtTanggal($mp['paid_at']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800"><?= Security::e($mp['payer_nama']) ?></p>
                                <p class="text-xs text-gray-500 font-mono"><?= Security::e($mp['nomor_induk']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?= ucfirst(Security::e($mp['jenis'])) ?><br>
                                <span class="text-xs text-gray-500"><?= Security::e($mp['periode']) ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <?php
                                $metodeLabel = match($mp['metode']) {
                                    'tunai'         => 'Tunai',
                                    'transfer_bank' => 'Transfer Bank',
                                    'lain'          => 'Lainnya',
                                    default         => $mp['metode'],
                                };
                                ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                    <?= Security::e($metodeLabel) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-teal-600">
                                <?= $fmtRupiah($mp['jumlah']) ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="/admin/manual-payments/show/<?= (int) $mp['id'] ?>"
                                   class="text-brand-600 hover:text-brand-800 font-medium">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo Pagination::render($page, $lastPage, '/admin/manual-payments', [
    'q'      => $filters['q'],
    'metode' => $filters['metode'],
    'start'  => $filters['start'],
    'end'    => $filters['end'],
]); ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';