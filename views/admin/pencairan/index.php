<?php
use App\Helpers\Security;
use App\Helpers\Pagination;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

ob_start();
?>

<!-- Info Rekening -->
<?php if ($rekening && $rekening['status'] === 'verified'): ?>
    <div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-3xl shadow-xl shadow-brand-200/50 p-6 text-white mb-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0">
                    <i data-lucide="landmark" class="w-7 h-7"></i>
                </div>
                <div>
                    <p class="text-xs text-white/70">Rekening Tujuan Pencairan</p>
                    <h3 class="text-lg font-bold">
                        <?= Security::e($rekening['bank_nama']) ?> — <?= Security::e($rekening['rekening_nomor']) ?>
                    </h3>
                    <p class="text-sm text-white/80 mt-0.5">
                        Atas Nama: <?= Security::e($rekening['rekening_pemilik']) ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-white/20 backdrop-blur rounded-xl px-3 py-2">
                <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                <span class="text-xs font-semibold">Terverifikasi</span>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-white/20">
            <p class="text-xs text-white/80 leading-relaxed">
                ℹ️ Setiap pembayaran online dari pelanggan akan <strong>otomatis ditransfer ke rekening ini</strong>
                melalui Xendit, setelah dipotong komisi platform. Tidak perlu menarik dana manual.
            </p>
        </div>
    </div>
<?php elseif ($rekening): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-amber-900 mb-1">Rekening Belum Terverifikasi</h3>
                <p class="text-sm text-amber-800 leading-relaxed">
                    Rekening Anda masih menunggu verifikasi admin. Setelah terverifikasi,
                    dana dari pembayaran online akan otomatis masuk ke rekening Anda.
                </p>
                <a href="/admin/rekening" class="inline-flex items-center gap-1 text-sm text-amber-900 hover:text-amber-700 font-medium mt-2">
                    Lihat Rekening →
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 mb-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-red-900 mb-1">Rekening Belum Diisi</h3>
                <p class="text-sm text-red-800 leading-relaxed">
                    Isi data rekening bank Anda dulu supaya dana pembayaran online bisa masuk.
                </p>
                <a href="/admin/rekening" class="inline-flex items-center gap-1 text-sm text-red-900 hover:text-red-700 font-medium mt-2">
                    Isi Rekening Sekarang →
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Transaksi</p>
        </div>
        <p class="text-2xl font-bold text-gray-800">
            <?= (int) ($summary['total_transaksi'] ?? 0) ?>
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                <i data-lucide="minus-circle" class="w-5 h-5 text-red-600"></i>
            </div>
            <p class="text-sm text-gray-500">Komisi Platform</p>
        </div>
        <p class="text-2xl font-bold text-red-600">
            <?= $fmtRupiah($summary['total_fee'] ?? 0) ?>
        </p>
    </div>

    <div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-3xl shadow-xl shadow-brand-200/50 p-5 text-white">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5"></i>
            </div>
            <p class="text-sm text-white/90">Total Masuk Rekening</p>
        </div>
        <p class="text-2xl font-bold">
            <?= $fmtRupiah($summary['total_tenant'] ?? 0) ?>
        </p>
    </div>
</div>

<!-- Filter -->
<form method="GET" action="/admin/pencairan" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
    <div class="relative md:col-span-2">
        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="q" value="<?= Security::e($filters['q']) ?>"
               placeholder="Cari order ID / nama pelanggan"
               class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-sm transition">
    </div>

    <input type="date" name="start" value="<?= Security::e($filters['start']) ?>"
           class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">

    <input type="date" name="end" value="<?= Security::e($filters['end']) ?>"
           class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">

    <div class="md:col-span-4 flex gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Filter
        </button>
        <?php if (array_filter($filters)): ?>
            <a href="/admin/pencairan"
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                Reset
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Tabel Pencairan -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-semibold text-gray-700">Daftar Pencairan</h3>
        <span class="text-xs text-gray-500"><?= (int) $total ?> transaksi</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Order ID</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Komisi</th>
                    <th class="px-4 py-3 text-right">Masuk Rekening</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="wallet" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">
                                        <?= array_filter($filters) ? 'Tidak ada yang cocok' : 'Belum ada pencairan' ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        <?= array_filter($filters) ? 'Coba ubah filter atau reset.' : 'Dana dari pembayaran online akan muncul di sini.' ?>
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $p): ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                <?= $fmtTanggal($p['paid_at']) ?>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                <?= Security::e($p['order_id']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800"><?= Security::e($p['payer_nama']) ?></p>
                                <p class="text-xs text-gray-500 font-mono"><?= Security::e($p['nomor_induk']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-700">
                                <?= $fmtRupiah($p['gross_amount']) ?>
                            </td>
                            <td class="px-4 py-3 text-right text-red-600">
                                − <?= $fmtRupiah($p['platform_fee'] ?? 0) ?>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-brand-600">
                                <?= $fmtRupiah($p['tenant_amount'] ?? $p['gross_amount']) ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium ring-1 ring-brand-200/50">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                    Masuk
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total -->
                    <tr class="bg-brand-50 font-bold">
                        <td colspan="3" class="px-4 py-3 text-brand-800">TOTAL</td>
                        <td class="px-4 py-3 text-right text-brand-800"><?= $fmtRupiah($summary['total_gross'] ?? 0) ?></td>
                        <td class="px-4 py-3 text-right text-red-700">− <?= $fmtRupiah($summary['total_fee'] ?? 0) ?></td>
                        <td class="px-4 py-3 text-right text-brand-800"><?= $fmtRupiah($summary['total_tenant'] ?? 0) ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo Pagination::render($page, $lastPage, '/admin/pencairan', [
    'q'     => $filters['q'],
    'start' => $filters['start'],
    'end'   => $filters['end'],
]); ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';