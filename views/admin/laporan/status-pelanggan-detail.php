<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

$statusBadge = function (array $inv) {
    $paidAmount = (float) ($inv['paid_amount'] ?? 0);
    $total      = (float) $inv['jumlah'];
    $status     = $inv['status'];

    if ($status === 'cancelled') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium ring-1 ring-gray-200"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Dibatalkan</span>';
    }
    if ($status === 'expired') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-red-50 text-red-700 font-medium ring-1 ring-red-200/50"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Kedaluwarsa</span>';
    }
    if ($status === 'paid') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium ring-1 ring-brand-200/50"><span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>Lunas</span>';
    }
    if ($paidAmount > 0 && $paidAmount < $total) {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-blue-50 text-blue-700 font-medium ring-1 ring-blue-200/50"><span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>Sebagian</span>';
    }
    return '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-50 text-amber-700 font-medium ring-1 ring-amber-200/50"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Belum Bayar</span>';
};

ob_start();
?>

<!-- Tombol Kembali -->
<div class="mb-4">
    <a href="/admin/laporan/status-pelanggan"
       class="inline-flex items-center gap-2 text-sm text-brand-600 hover:text-brand-800 font-medium">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        Kembali ke Status Pelanggan
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
            <?php if (!empty($payer['whatsapp'])): ?>
                <p class="text-sm text-white/80 mt-0.5">
                    <i data-lucide="phone" class="w-3.5 h-3.5 inline"></i>
                    <?= Security::e($payer['whatsapp']) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm p-4 border border-brand-100">
        <p class="text-xs text-gray-500">Total Lunas</p>
        <p class="text-lg font-bold text-brand-600 mt-1"><?= $fmtRupiah($summary['total_dibayar'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-0.5"><?= (int) ($summary['jml_lunas'] ?? 0) ?> tagihan</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 border border-blue-100">
        <p class="text-xs text-gray-500">Sebagian</p>
        <p class="text-lg font-bold text-blue-600 mt-1"><?= (int) ($summary['jml_sebagian'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-0.5">tagihan dicicil</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 border border-amber-100">
        <p class="text-xs text-gray-500">Belum Bayar</p>
        <p class="text-lg font-bold text-amber-600 mt-1"><?= (int) ($summary['jml_belum'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-0.5">tagihan</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-4 border border-red-100">
        <p class="text-xs text-gray-500">Total Sisa</p>
        <p class="text-lg font-bold text-red-600 mt-1"><?= $fmtRupiah($summary['total_sisa'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-0.5">yang harus dibayar</p>
    </div>
</div>

<!-- Filter Tab -->
<div class="flex gap-2 mb-4 flex-wrap">
    <button type="button" onclick="filterInvoice('all')" data-filter="all"
            class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition bg-gray-800 text-white">
        Semua (<?= count($invoices) ?>)
    </button>
    <button type="button" onclick="filterInvoice('unpaid')" data-filter="unpaid"
            class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition bg-white text-gray-700 hover:bg-gray-100 border border-gray-200">
        Belum Bayar (<?= (int) (($summary['jml_belum'] ?? 0) + ($summary['jml_sebagian'] ?? 0)) ?>)
    </button>
    <button type="button" onclick="filterInvoice('paid')" data-filter="paid"
            class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition bg-white text-gray-700 hover:bg-gray-100 border border-gray-200">
        Lunas (<?= (int) ($summary['jml_lunas'] ?? 0) ?>)
    </button>
    <button type="button" onclick="filterInvoice('cancelled')" data-filter="cancelled"
            class="filter-btn px-4 py-2 rounded-xl text-sm font-medium transition bg-white text-gray-700 hover:bg-gray-100 border border-gray-200">
        Dibatalkan (<?= (int) ($summary['jml_cancelled'] ?? 0) ?>)
    </button>
</div>

<!-- Daftar Tagihan -->
<div class="space-y-3" id="invoice-list">
    <?php if (empty($invoices)): ?>
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center border border-gray-100">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
            </div>
            <h3 class="font-bold text-gray-800 mb-1">Belum Ada Tagihan</h3>
            <p class="text-sm text-gray-500">Pelanggan ini belum punya tagihan.</p>
        </div>
    <?php else: ?>
        <?php foreach ($invoices as $inv): ?>
            <?php
                $paidAmount = (float) ($inv['paid_amount'] ?? 0);
                $total      = (float) $inv['jumlah'];
                $remaining  = (float) ($inv['remaining'] ?? $total - $paidAmount);
                $isPartial  = $paidAmount > 0 && $paidAmount < $total;
                $percent    = $total > 0 ? round(($paidAmount / $total) * 100) : 0;

                // Filter key
                if ($inv['status'] === 'paid') {
                    $filterKey = 'paid';
                } elseif ($inv['status'] === 'cancelled') {
                    $filterKey = 'cancelled';
                } else {
                    $filterKey = 'unpaid';
                }
            ?>
            <div class="invoice-item bg-white rounded-2xl shadow-sm p-4 border border-gray-100"
                 data-status="<?= $filterKey ?>">
                <div class="flex justify-between items-start gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800"><?= ucfirst(Security::e($inv['jenis'])) ?></p>
                        <p class="text-xs text-gray-500"><?= Security::e($inv['periode']) ?></p>
                        <?php if (!empty($inv['deskripsi'])): ?>
                            <p class="text-xs text-gray-500 mt-0.5"><?= Security::e($inv['deskripsi']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <?= $statusBadge($inv) ?>
                    </div>
                </div>

                <!-- Jumlah -->
                <div class="flex justify-between items-end pt-2 border-t border-gray-100 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">Jatuh Tempo</p>
                        <p class="font-medium text-gray-700"><?= $fmtTanggal($inv['due_date']) ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500">Jumlah Tagihan</p>
                        <p class="font-bold text-gray-800"><?= $fmtRupiah($total) ?></p>
                    </div>
                </div>

                <!-- Progress bar (kalau cicil atau lunas) -->
                <?php if ($paidAmount > 0): ?>
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="flex justify-between items-center mb-1.5 text-xs">
                            <span class="<?= $inv['status'] === 'paid' ? 'text-brand-700 font-medium' : 'text-blue-700 font-medium' ?>">
                                <?= $inv['status'] === 'paid' ? '✓ Lunas' : 'Dibayar Sebagian' ?>
                            </span>
                            <span class="font-semibold"><?= $fmtRupiah($paidAmount) ?> / <?= $fmtRupiah($total) ?></span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="<?= $inv['status'] === 'paid' ? 'bg-brand-500' : 'bg-blue-500' ?> h-2 rounded-full transition-all"
                                 style="width: <?= $percent ?>%"></div>
                        </div>
                        <?php if ($isPartial): ?>
                            <p class="text-xs text-red-600 mt-1.5">
                                Sisa: <strong><?= $fmtRupiah($remaining) ?></strong>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Aksi -->
                <div class="mt-3 pt-2 flex gap-2 justify-end">
                    <a href="/admin/invoices/show/<?= (int) $inv['id'] ?>"
                       class="text-xs text-brand-600 hover:text-brand-800 font-medium">
                        Detail →
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Empty state filter -->
<div id="empty-filter" class="hidden bg-white rounded-2xl shadow-sm p-8 text-center border border-gray-100">
    <p class="text-sm text-gray-500">Tidak ada tagihan dengan status ini.</p>
</div>

<script>
function filterInvoice(filter) {
    const items = document.querySelectorAll('.invoice-item');
    const emptyEl = document.getElementById('empty-filter');
    let visibleCount = 0;

    items.forEach(item => {
        if (filter === 'all' || item.dataset.status === filter) {
            item.style.display = '';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });

    if (visibleCount === 0 && items.length > 0) {
        emptyEl.classList.remove('hidden');
    } else {
        emptyEl.classList.add('hidden');
    }

    // Update button style
    document.querySelectorAll('.filter-btn').forEach(btn => {
        if (btn.dataset.filter === filter) {
            btn.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-200', 'hover:bg-gray-100');
            btn.classList.add('bg-gray-800', 'text-white');
        } else {
            btn.classList.remove('bg-gray-800', 'text-white');
            btn.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-200', 'hover:bg-gray-100');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';