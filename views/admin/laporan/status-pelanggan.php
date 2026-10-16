<?php
use App\Helpers\Security;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

$statusBadge = function (array $r) {
    $total   = (float) $r['total_tagihan'];
    $dibayar = (float) $r['total_dibayar'];
    $sisa    = (float) $r['total_sisa'];

    if ($total == 0) {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Belum ada tagihan</span>';
    }
    if ($sisa <= 0) {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium ring-1 ring-brand-200/50"><span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>Lunas Semua</span>';
    }
    if ($dibayar > 0) {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-blue-50 text-blue-700 font-medium ring-1 ring-blue-200/50"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Sebagian</span>';
    }
    return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-50 text-amber-700 font-medium ring-1 ring-amber-200/50"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Belum Bayar</span>';
};

ob_start();
?>

<!-- Header -->
<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
    <div>
        <h3 class="text-lg font-semibold">Status Pembayaran Per Pelanggan</h3>
        <p class="text-sm text-gray-500">Lihat siapa yang sudah lunas, sebagian, atau belum bayar</p>
    </div>
    <a href="/admin/laporan"
       class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl border border-gray-200 transition">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali ke Laporan
    </a>
</div>

<!-- Filter -->
<form method="GET" action="/admin/laporan/status-pelanggan" class="mb-4 bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">

        <!-- Searchbar -->
        <div class="relative md:col-span-2">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="q" value="<?= Security::e($search) ?>"
                   placeholder="Cari nama atau nomor pelanggan..."
                   class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-sm transition">
        </div>

        <!-- Periode -->
        <select name="periode"
                class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
            <option value="">Semua Periode</option>
            <?php foreach ($periodes as $p): ?>
                <option value="<?= Security::e($p) ?>" <?= $periode === $p ? 'selected' : '' ?>>
                    <?= Security::e($p) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Status -->
        <select name="status"
                class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
            <option value="">Semua Status</option>
            <option value="belum" <?= $status === 'belum' ? 'selected' : '' ?>>Yang Belum Lunas</option>
            <option value="lunas" <?= $status === 'lunas' ? 'selected' : '' ?>>Yang Sudah Lunas</option>
        </select>

        <!-- Tombol -->
        <div class="md:col-span-4 flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
                <i data-lucide="filter" class="w-4 h-4"></i>
                Filter
            </button>
            <?php if ($search !== '' || $periode !== '' || $status !== ''): ?>
                <a href="/admin/laporan/status-pelanggan"
                   class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl transition">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    Reset
                </a>
            <?php endif; ?>
            <span class="text-sm text-gray-500 self-center ml-auto">
                Total: <strong><?= count($rows) ?></strong> pelanggan
            </span>
        </div>
    </div>
</form>

<!-- Tabel -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3 text-center">Total Tagihan</th>
                    <th class="px-4 py-3 text-right">Total Tagihan (Rp)</th>
                    <th class="px-4 py-3 text-right">Sudah Dibayar</th>
                    <th class="px-4 py-3 text-right">Sisa</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="users" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">Tidak ada data</p>
                                    <p class="text-sm text-gray-500 mt-0.5">Belum ada tagihan di periode ini.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-4 py-3">
    <a href="/admin/laporan/status-pelanggan/detail/<?= (int) $r['payer_id'] ?>"
       class="block group">
        <p class="font-medium text-gray-800 group-hover:text-brand-600 group-hover:underline transition">
            <?= Security::e($r['payer_nama']) ?>
        </p>
        <p class="text-xs text-gray-500 font-mono">
            <?= Security::e($r['nomor_induk']) ?>
        </p>
    </a>
</td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs text-gray-600"><?= (int) $r['total_invoice'] ?> tagihan</span>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    <?php if ($r['jml_lunas'] > 0): ?>✅ <?= (int) $r['jml_lunas'] ?><?php endif; ?>
                                    <?php if ($r['jml_sebagian'] > 0): ?> ⚠️ <?= (int) $r['jml_sebagian'] ?><?php endif; ?>
                                    <?php if ($r['jml_belum'] > 0): ?> ❌ <?= (int) $r['jml_belum'] ?><?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                <?= $fmtRupiah($r['total_tagihan']) ?>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-brand-600">
                                <?= $fmtRupiah($r['total_dibayar']) ?>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-red-600">
                                <?= $fmtRupiah($r['total_sisa']) ?>
                            </td>
                            <td class="px-4 py-3 text-center"><?= $statusBadge($r) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Legenda -->
<div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-500">
    <span>✅ Lunas</span>
    <span>⚠️ Sebagian</span>
    <span>❌ Belum bayar</span>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';