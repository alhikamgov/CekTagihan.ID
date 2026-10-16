<?php
use App\Helpers\Lang;
use App\Helpers\Pagination;
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

ob_start();
?>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Tunggakan</p>
        </div>
        <p class="text-2xl font-bold text-red-600"><?= $fmtRupiah($summary['total_overdue'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1"><?= (int) ($summary['jumlah_overdue'] ?? 0) ?> invoice lewat jatuh tempo</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
            </div>
            <p class="text-sm text-gray-500">Jatuh Tempo 7 Hari</p>
        </div>
        <p class="text-2xl font-bold text-amber-600"><?= $fmtRupiah($summary['total_due_soon'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Segera ditagih</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Pelanggan</p>
        </div>
        <p class="text-2xl font-bold text-gray-800"><?= (int) ($summary['total_payer'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Punya tunggakan</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5 text-gray-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Tagihan</p>
        </div>
        <p class="text-2xl font-bold text-gray-800"><?= (int) ($summary['total_invoice'] ?? 0) ?></p>
        <p class="text-xs text-gray-500 mt-1">Belum dibayar</p>
    </div>

</div>

<!-- Filter -->
<div class="flex justify-between items-center mb-4 flex-wrap gap-2">
    <div class="flex gap-2">
        <a href="/admin/tunggakan?filter=all"
           class="px-4 py-2 rounded-xl text-sm font-medium transition
                  <?= $filter === 'all' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
            Semua
        </a>
        <a href="/admin/tunggakan?filter=overdue"
           class="px-4 py-2 rounded-xl text-sm font-medium transition
                  <?= $filter === 'overdue' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
            Lewat Jatuh Tempo
        </a>
        <a href="/admin/tunggakan?filter=due_soon"
           class="px-4 py-2 rounded-xl text-sm font-medium transition
                  <?= $filter === 'due_soon' ? 'bg-amber-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
            Jatuh Tempo 7 Hari
        </a>
    </div>
    <p class="text-sm text-gray-500">Total: <?= (int) $total ?> tagihan</p>
</div>

<!-- Tabel -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
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
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-brand-50 flex items-center justify-center">
                                    <i data-lucide="check-circle" class="w-8 h-8 text-brand-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">
                                        <?php if ($filter === 'all'): ?>
                                            Tidak ada tunggakan
                                        <?php else: ?>
                                            Tidak ada data di filter ini
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-sm text-gray-500 mt-0.5">Semua tagihan sudah lunas.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $inv): ?>
                        <?php
                            $hariTelat = (int) $inv['hari_telat'];
                            $isOverdue = $hariTelat > 0;
                            $isDueSoon = $hariTelat >= -7 && $hariTelat <= 0;
                        ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800"><?= Security::e($inv['payer_nama']) ?></p>
                                <p class="text-xs text-gray-500 font-mono"><?= Security::e($inv['nomor_induk']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-gray-600"><?= ucfirst(Security::e($inv['jenis'])) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= Security::e($inv['periode']) ?></td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-800"><?= $fmtRupiah($inv['jumlah']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= $fmtTanggal($inv['due_date']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($isOverdue): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        H+<?= $hariTelat ?>
                                    </span>
                                <?php elseif ($isDueSoon): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-100 text-amber-700 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <?= $hariTelat === 0 ? 'Hari ini' : abs($hariTelat) . ' hari lagi' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        <?= abs($hariTelat) ?> hari lagi
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <?php if (!empty($inv['payer_whatsapp'])): ?>
                                    <a href="https://wa.me/<?= preg_replace('/^0/', '62', Security::e($inv['payer_whatsapp'])) ?>?text=<?= urlencode('Halo ' . $inv['payer_nama'] . ', tagihan ' . $inv['jenis'] . ' ' . $inv['periode'] . ' Anda sebesar Rp ' . number_format((float) $inv['jumlah'], 0, ',', '.') . ' sudah jatuh tempo. Mohon segera dibayar. Terima kasih.') ?>"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-brand-600 hover:text-brand-800 font-medium">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        WA
                                    </a>
                                <?php endif; ?>
                                <a href="/admin/invoices/show/<?= (int) $inv['id'] ?>"
                                   class="text-gray-600 hover:text-gray-800 font-medium">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo Pagination::render($page, $lastPage, '/admin/tunggakan', ['filter' => $filter]); ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';