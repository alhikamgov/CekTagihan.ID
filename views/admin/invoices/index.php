<?php
use App\Helpers\Security;
use App\Helpers\Pagination;

$title = 'Kelola Tagihan';
$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

$statusBadge = function (array $inv) {
    $paidAmount = (float) ($inv['paid_amount'] ?? 0);
    $total = (float) $inv['jumlah'];
    $status = $inv['status'];

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

<!-- Summary -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-3xl shadow-sm p-5 border border-amber-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Tagihan</p>
        </div>
        <p class="text-2xl font-bold text-amber-600"><?= $fmtRupiah($summary['total_unpaid'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Belum dibayar</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-brand-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Terbayar</p>
        </div>
        <p class="text-2xl font-bold text-brand-600"><?= $fmtRupiah($summary['total_paid'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Sudah lunas</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-red-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
            </div>
            <p class="text-sm text-gray-500">Tunggakan</p>
        </div>
        <p class="text-2xl font-bold text-red-600"><?= $fmtRupiah($summary['total_overdue'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Lewat jatuh tempo</p>
    </div>
</div>

<!-- Header -->
<div class="flex justify-between items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="text-lg font-semibold">Daftar Tagihan</h3>
        <p class="text-sm text-gray-500">Total: <?= (int) $total ?> tagihan</p>
    </div>
    <div class="flex gap-2">
        <a href="/admin/invoices/generate"
           class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i data-lucide="zap" class="w-4 h-4"></i>
            Buat Tagihan (Massal)
        </a>
        <a href="/admin/invoices/create"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-brand transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Buat Tagihan
        </a>
    </div>
</div>

<!-- Filter -->
<form method="GET" action="/admin/invoices" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2">
    <div class="relative md:col-span-2">
        <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
        <input type="text" name="q" value="<?= Security::e($filters['q']) ?>"
               placeholder="Cari nama / Nomor Pelanggan"
               class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-sm transition">
    </div>

    <select name="status" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
        <option value="">Semua Status</option>
        <option value="unpaid"    <?= $filters['status'] === 'unpaid'    ? 'selected' : '' ?>>Belum Bayar</option>
        <option value="paid"      <?= $filters['status'] === 'paid'      ? 'selected' : '' ?>>Lunas</option>
        <option value="expired"   <?= $filters['status'] === 'expired'   ? 'selected' : '' ?>>Kedaluwarsa</option>
        <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
    </select>

    <select name="jenis" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
        <option value="">Semua Jenis</option>
        <?php foreach ($jenisList as $jt): ?>
            <option value="<?= Security::e($jt['nama']) ?>" <?= $filters['jenis'] === $jt['nama'] ? 'selected' : '' ?>>
                <?= ucfirst(Security::e($jt['nama'])) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="periode" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none text-sm">
        <option value="">Semua Periode</option>
        <?php foreach ($periodes as $p): ?>
            <option value="<?= Security::e($p) ?>" <?= $filters['periode'] === $p ? 'selected' : '' ?>>
                <?= Security::e($p) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <div class="md:col-span-5 flex gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Filter
        </button>
        <?php if (array_filter($filters)): ?>
            <a href="/admin/invoices"
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl transition">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                Reset
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Form Bulk Action -->
<form method="POST" action="/admin/invoices/bulk-action" id="form-bulk">
    <input type="hidden" name="csrf_token" value="<?= Security::e(\App\Helpers\Security::csrfToken()) ?>">
    <input type="hidden" name="bulk_action" id="bulk_action_field" value="">

    <!-- Bulk Action Bar -->
    <div id="bulk-bar" class="hidden mb-4 bg-gradient-to-r from-brand-500 to-teal-600 rounded-2xl shadow-lg shadow-brand-200/50 p-3 flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-3 text-white">
            <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                <i data-lucide="check-square" class="w-4 h-4"></i>
            </div>
            <span class="text-sm font-medium">
                <span id="bulk-count">0</span> tagihan dipilih
            </span>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="bulkReminder()"
                    class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                Kirim Reminder WA
            </button>
            <button type="button" onclick="bulkCancel()"
                    class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                Batalkan
            </button>
            <button type="button" onclick="clearSelection()"
                    class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium px-3 py-2 rounded-lg transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-left">
                    <tr>
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" id="check-all"
                                   class="rounded text-brand-600 focus:ring-brand-500 cursor-pointer"
                                   onchange="toggleAll(this)">
                        </th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">
                                            <?= array_filter($filters) ? 'Tidak ada tagihan yang cocok' : 'Belum ada tagihan' ?>
                                        </p>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            <?= array_filter($filters) ? 'Coba ubah filter atau reset.' : 'Klik "Buat Tagihan" untuk mulai.' ?>
                                        </p>
                                    </div>
                                    <?php if (!array_filter($filters)): ?>
                                        <a href="/admin/invoices/create"
                                           class="mt-2 inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
                                            <i data-lucide="plus" class="w-4 h-4"></i>
                                            Buat Tagihan Pertama
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <?php
                                $paidAmount = (float) ($inv['paid_amount'] ?? 0);
                                $totalAmount = (float) $inv['jumlah'];
                                $remaining = $totalAmount - $paidAmount;
                                $isPartial = $paidAmount > 0 && $paidAmount < $totalAmount;
                            ?>
                            <tr class="hover:bg-brand-50/40 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="invoice_ids[]" value="<?= (int) $inv['id'] ?>"
                                           class="invoice-checkbox rounded text-brand-600 focus:ring-brand-500 cursor-pointer"
                                           onchange="updateBulkBar()">
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-800"><?= Security::e($inv['payer_nama']) ?></p>
                                    <p class="text-xs text-gray-500 font-mono"><?= Security::e($inv['nomor_induk']) ?></p>
                                </td>
                                <td class="px-4 py-3 text-gray-600"><?= ucfirst(Security::e($inv['jenis'])) ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= Security::e($inv['periode']) ?></td>
                                <td class="px-4 py-3 text-right font-medium text-gray-800">
                                    <?= $fmtRupiah($inv['jumlah']) ?>
                                    <?php if ($isPartial): ?>
                                        <div class="text-xs font-normal text-blue-600 mt-0.5">
                                            Dibayar: <?= $fmtRupiah($paidAmount) ?><br>
                                            Sisa: <?= $fmtRupiah($remaining) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600"><?= $fmtTanggal($inv['due_date']) ?></td>
                                <td class="px-4 py-3 text-center"><?= $statusBadge($inv) ?></td>
                                <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                    <a href="/admin/invoices/show/<?= (int) $inv['id'] ?>"
                                       class="text-brand-600 hover:text-brand-800 font-medium">
                                        Detail
                                    </a>
                                    <?php if ($inv['status'] === 'unpaid'): ?>
                                        <a href="/admin/invoices/cancel/<?= (int) $inv['id'] ?>"
                                           class="text-red-600 hover:text-red-800 font-medium">
                                            Batal
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<?php
echo Pagination::render($page, $lastPage, '/admin/invoices', [
    'q'       => $filters['q'],
    'status'  => $filters['status'],
    'jenis'   => $filters['jenis'],
    'periode' => $filters['periode'],
]);
?>

<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.invoice-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkBar();
}

function updateBulkBar() {
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    const checked = document.querySelectorAll('.invoice-checkbox:checked');
    const bar = document.getElementById('bulk-bar');
    const countEl = document.getElementById('bulk-count');
    const checkAll = document.getElementById('check-all');

    countEl.textContent = checked.length;

    if (checked.length > 0) {
        bar.classList.remove('hidden');
    } else {
        bar.classList.add('hidden');
    }

    if (checkboxes.length > 0) {
        checkAll.checked = checked.length === checkboxes.length;
        checkAll.indeterminate = checked.length > 0 && checked.length < checkboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.invoice-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('check-all').checked = false;
    updateBulkBar();
}

function bulkReminder() {
    const checked = document.querySelectorAll('.invoice-checkbox:checked');
    if (checked.length === 0) return;

    if (!confirm(`Kirim reminder WA ke ${checked.length} tagihan?`)) return;

    document.getElementById('bulk_action_field').value = 'reminder';
    document.getElementById('form-bulk').submit();
}

function bulkCancel() {
    const checked = document.querySelectorAll('.invoice-checkbox:checked');
    if (checked.length === 0) return;

    if (!confirm(`Batalkan ${checked.length} tagihan yang dipilih?`)) return;

    document.getElementById('bulk_action_field').value = 'cancel';
    document.getElementById('form-bulk').submit();
}

document.addEventListener('DOMContentLoaded', () => {
    updateBulkBar();
    if (window.lucide) lucide.createIcons();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';