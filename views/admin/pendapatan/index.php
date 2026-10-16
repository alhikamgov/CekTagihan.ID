<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

$bulanLabel = date('F Y', strtotime($bulan . '-01'));

ob_start();
?>

<!-- Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 mb-6">
    <form method="GET" action="/admin/pendapatan" class="flex gap-2 flex-wrap items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
            <select name="bulan"
                    class="px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm">
                <?php foreach ($bulanList as $b): ?>
                    <option value="<?= Security::e($b) ?>" <?= $bulan === $b ? 'selected' : '' ?>>
                        <?= date('F Y', strtotime($b . '-01')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit"
                class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Tampilkan
        </button>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    <!-- Pendapatan Online -->
    <div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-3xl shadow-xl shadow-brand-200/50 p-5 text-white">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                <i data-lucide="smartphone" class="w-5 h-5"></i>
            </div>
            <p class="text-sm text-white/90">Pendapatan Online</p>
        </div>
        <p class="text-2xl font-bold mb-2"><?= $fmtRupiah($totalOnline) ?></p>
        <div class="space-y-1 text-xs text-white/80 pt-2 border-t border-white/20">
            <div class="flex justify-between">
                <span>Komisi platform</span>
                <span>− <?= $fmtRupiah($totalFee) ?></span>
            </div>
            <div class="flex justify-between font-semibold">
                <span>Masuk rekening</span>
                <span><?= $fmtRupiah($totalTenant) ?></span>
            </div>
        </div>
    </div>

    <!-- Pendapatan Manual -->
    <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl shadow-xl shadow-amber-200/50 p-5 text-white">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center">
                <i data-lucide="hand-coins" class="w-5 h-5"></i>
            </div>
            <p class="text-sm text-white/90">Pendapatan Manual</p>
        </div>
        <p class="text-2xl font-bold"><?= $fmtRupiah($totalManual) ?></p>
        <p class="text-xs text-white/80 mt-2">Diterima langsung oleh admin</p>
    </div>

    <!-- Total -->
    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-green-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Pendapatan</p>
        </div>
        <p class="text-2xl font-bold text-green-600"><?= $fmtRupiah($totalTenant + $totalManual) ?></p>
        <p class="text-xs text-gray-500 mt-2">Online + Manual (setelah komisi)</p>
    </div>
</div>

<!-- Info Bulan -->
<div class="mb-4 text-sm text-gray-500">
    Menampilkan data periode: <strong><?= Security::e($bulanLabel) ?></strong>
</div>

<!-- ============================================= -->
<!-- PENDAPATAN ONLINE                              -->
<!-- ============================================= -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100 mb-6">
    <div class="px-4 py-3 bg-brand-50 border-b border-brand-100 flex justify-between items-center">
        <div>
            <h3 class="font-semibold text-brand-800">Pendapatan Online</h3>
            <p class="text-xs text-brand-600 mt-0.5">Pembayaran via Xendit</p>
        </div>
        <span class="text-xs text-brand-700 font-medium">
            <?= count($online) ?> transaksi
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                    <th class="px-4 py-3 text-right">Komisi</th>
                    <th class="px-4 py-3 text-right">Masuk Rekening</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($online)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="smartphone" class="w-6 h-6 text-gray-400"></i>
                                </div>
                                <p class="text-sm text-gray-500">Belum ada pendapatan online di periode ini.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($online as $p): ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                <?= $fmtTanggal($p['paid_at']) ?>
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
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total -->
                    <tr class="bg-brand-50 font-bold">
                        <td colspan="2" class="px-4 py-3 text-brand-800">TOTAL</td>
                        <td class="px-4 py-3 text-right text-brand-800"><?= $fmtRupiah($totalOnline) ?></td>
                        <td class="px-4 py-3 text-right text-red-700">− <?= $fmtRupiah($totalFee) ?></td>
                        <td class="px-4 py-3 text-right text-brand-800"><?= $fmtRupiah($totalTenant) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================= -->
<!-- PENDAPATAN MANUAL                              -->
<!-- ============================================= -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="px-4 py-3 bg-amber-50 border-b border-amber-100 flex justify-between items-center">
        <div>
            <h3 class="font-semibold text-amber-800">Pendapatan Manual</h3>
            <p class="text-xs text-amber-600 mt-0.5">Tunai / transfer langsung</p>
        </div>
        <span class="text-xs text-amber-700 font-medium">
            <?= count($manual) ?> transaksi
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Pelanggan</th>
                    <th class="px-4 py-3">Tagihan</th>
                    <th class="px-4 py-3">Metode</th>
                    <th class="px-4 py-3 text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($manual)): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="hand-coins" class="w-6 h-6 text-gray-400"></i>
                                </div>
                                <p class="text-sm text-gray-500">Belum ada pendapatan manual di periode ini.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($manual as $m): ?>
                        <tr class="hover:bg-amber-50/40 transition-colors">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                <?= $fmtTanggal($m['paid_at']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800"><?= Security::e($m['payer_nama']) ?></p>
                                <p class="text-xs text-gray-500 font-mono"><?= Security::e($m['nomor_induk']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <?= ucfirst(Security::e($m['jenis'])) ?><br>
                                <span class="text-xs text-gray-500"><?= Security::e($m['periode']) ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <?php
                                $metodeLabel = match($m['metode']) {
                                    'tunai'         => 'Tunai',
                                    'transfer_bank' => 'Transfer Bank',
                                    'lain'          => 'Lainnya',
                                    default         => $m['metode'],
                                };
                                ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
                                    <?= Security::e($metodeLabel) ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-amber-600">
                                <?= $fmtRupiah($m['jumlah']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total -->
                    <tr class="bg-amber-50 font-bold">
                        <td colspan="4" class="px-4 py-3 text-amber-800">TOTAL</td>
                        <td class="px-4 py-3 text-right text-amber-800"><?= $fmtRupiah($totalManual) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';