<?php
use App\Helpers\Security;
use App\Helpers\Pagination;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

ob_start();
?>

<!-- Info Payer -->
<div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-3xl shadow-xl shadow-brand-200/50 p-6 text-white mb-4">
    <p class="text-xs opacity-80"><?= Security::e($tenantNama) ?></p>
    <h2 class="text-xl font-bold mt-1">Halo, <?= Security::e($payerNama) ?> 👋</h2>
    <p class="text-sm opacity-90 mt-0.5">
        No. Pelanggan: <span class="font-mono font-semibold"><?= Security::e($payerInduk) ?></span>
    </p>
</div>

<!-- Tab Navigasi -->
<div class="flex gap-2 mb-4">
    <a href="/portal/tagihan"
       class="flex-1 text-center px-4 py-3 rounded-xl font-medium text-sm transition bg-white shadow-sm border-2 border-gray-200 text-gray-600 hover:border-brand-300">
        Belum Bayar
    </a>
    <a href="/portal/riwayat"
       class="flex-1 text-center px-4 py-3 rounded-xl font-medium text-sm transition bg-white shadow-sm border-2 border-brand-500 text-brand-700">
        Riwayat
    </a>
</div>

<!-- Summary -->
<?php if (!empty($summary['total_transaksi'])): ?>
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 mb-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500">Total Transaksi</p>
                <p class="text-xl font-bold text-gray-800 mt-1"><?= (int) $summary['total_transaksi'] ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Total Nominal</p>
                <p class="text-xl font-bold text-brand-600 mt-1"><?= $fmtRupiah($summary['total_nominal']) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Daftar Riwayat -->
<?php if (empty($items)): ?>
    <div class="bg-white rounded-3xl shadow-xl shadow-brand-100/50 p-8 text-center border border-brand-100">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
            <i data-lucide="receipt" class="w-8 h-8 text-gray-400"></i>
        </div>
        <h3 class="font-bold text-gray-800 mb-1">Belum Ada Riwayat</h3>
        <p class="text-sm text-gray-500">
            Setelah Anda bayar tagihan, riwayatnya akan muncul di sini.
        </p>
    </div>
<?php else: ?>
    <div class="space-y-3 mb-4">
        <?php foreach ($items as $inv): ?>
            <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
                <div class="flex justify-between items-start gap-3 mb-2">
                    <div>
                        <p class="font-semibold text-gray-800"><?= ucfirst(Security::e($inv['jenis'])) ?></p>
                        <p class="text-xs text-gray-500"><?= Security::e($inv['periode']) ?></p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium ring-1 ring-brand-200/50">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                        Lunas
                    </span>
                </div>
                <div class="flex justify-between items-end pt-2 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500">Dibayar</p>
                        <p class="text-xs text-gray-700"><?= $fmtTanggal($inv['paid_at']) ?></p>
                    </div>
                    <p class="font-bold text-gray-800"><?= $fmtRupiah($inv['jumlah']) ?></p>
                </div>
                <?php if (!empty($inv['deskripsi'])): ?>
                    <p class="text-xs text-gray-500 mt-2"><?= Security::e($inv['deskripsi']) ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php echo Pagination::render($page, $lastPage, '/portal/riwayat'); ?>
<?php endif; ?>

<!-- Tombol Kembali -->
<div class="mt-4">
    <a href="/portal/ulang"
       class="block text-center text-sm text-gray-500 hover:text-brand-600 py-2 transition">
        ← Cek Nomor Lain
    </a>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';