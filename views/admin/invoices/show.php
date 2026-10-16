<?php
use App\Helpers\Security;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';
$fmtTanggalOnly = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

$statusBadge = function (array $inv) {
    $paidAmount = (float) ($inv['paid_amount'] ?? 0);
    $total = (float) $inv['jumlah'];
    $status = $inv['status'];

    if ($status === 'cancelled') {
        return '<span class="px-3 py-1 text-sm rounded-full bg-gray-200 text-gray-600">Dibatalkan</span>';
    }
    if ($status === 'expired') {
        return '<span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-700">Kedaluwarsa</span>';
    }
    if ($status === 'paid') {
        return '<span class="px-3 py-1 text-sm rounded-full bg-brand-100 text-brand-700">Lunas</span>';
    }
    if ($paidAmount > 0 && $paidAmount < $total) {
        return '<span class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-700">Sebagian</span>';
    }
    return '<span class="px-3 py-1 text-sm rounded-full bg-amber-100 text-amber-700">Belum Bayar</span>';
};

$paidAmount  = (float) ($invoice['paid_amount'] ?? 0);
$totalAmount = (float) $invoice['jumlah'];
$remaining   = $totalAmount - $paidAmount;
$isPartial   = $paidAmount > 0 && $paidAmount < $totalAmount;

ob_start();
?>

<div class="mb-4">
    <a href="/admin/invoices" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar tagihan</a>
</div>

<div class="max-w-3xl">

    <div class="bg-white rounded-lg shadow-sm p-6 mb-4">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-lg font-semibold">Tagihan #<?= str_pad((string) $invoice['id'], 5, '0', STR_PAD_LEFT) ?></h3>
                <p class="text-sm text-gray-500 mt-1">Dibuat: <?= $fmtTanggal($invoice['created_at']) ?></p>
            </div>
            <div><?= $statusBadge($invoice) ?></div>
        </div>

        <!-- Info Pelanggan -->
        <div class="mb-6 pb-6 border-b border-gray-100">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Pelanggan</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Nama</p>
                    <p class="font-medium"><?= Security::e($invoice['payer_nama']) ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Nomor Pelanggan</p>
                    <p class="font-mono font-medium"><?= Security::e($invoice['nomor_induk']) ?></p>
                </div>
                <div>
                    <p class="text-gray-500">WhatsApp</p>
                    <p><?= Security::e($invoice['payer_whatsapp'] ?: '-') ?></p>
                </div>
            </div>
        </div>

        <!-- Info Tagihan -->
        <div class="mb-6 pb-6 border-b border-gray-100">
            <h4 class="text-sm font-semibold text-gray-500 uppercase mb-3">Detail Tagihan</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Jenis</p>
                    <p class="font-medium"><?= ucfirst(Security::e($invoice['jenis'])) ?></p>
                </div>
                <div>
                    <p class="text-gray-500">Periode</p>
                    <p class="font-medium"><?= Security::e($invoice['periode']) ?></p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500">Deskripsi</p>
                    <p><?= Security::e($invoice['deskripsi'] ?: '-') ?></p>
                </div>
            </div>
        </div>

        <!-- Jumlah -->
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Jumlah Tagihan</span>
                <span class="font-medium"><?= $fmtRupiah($invoice['jumlah']) ?></span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Jatuh Tempo</span>
                <span class="font-medium"><?= $fmtTanggalOnly($invoice['due_date']) ?></span>
            </div>
            <?php if ($invoice['status'] === 'paid' && $invoice['paid_at']): ?>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Dibayar Pada</span>
                    <span class="font-medium text-green-600"><?= $fmtTanggal($invoice['paid_at']) ?></span>
                </div>
            <?php endif; ?>
            <div class="flex justify-between pt-2 border-t border-gray-200">
                <span class="font-semibold">Total</span>
                <span class="font-bold text-lg"><?= $fmtRupiah($invoice['jumlah']) ?></span>
            </div>
        </div>

        <!-- Progress Pembayaran (kalau sebagian) -->
        <?php if ($isPartial): ?>
            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-sm font-semibold text-blue-900">Sudah Dibayar Sebagian</p>
                    <p class="text-sm font-bold text-blue-700">
                        <?= $fmtRupiah($paidAmount) ?> / <?= $fmtRupiah($totalAmount) ?>
                    </p>
                </div>
                <div class="w-full bg-blue-100 rounded-full h-2 mb-3">
                    <div class="bg-blue-500 h-2 rounded-full"
                         style="width: <?= round(($paidAmount / $totalAmount) * 100) ?>%"></div>
                </div>
                <p class="text-xs text-blue-800">
                    Sisa yang harus dibayar: <strong><?= $fmtRupiah($remaining) ?></strong>
                </p>
            </div>
        <?php endif; ?>

        <!-- Riwayat Pembayaran -->
        <?php
        $history = \App\Models\Invoice::paymentHistory((int) $invoice['id']);
        if (!empty($history)):
        ?>
            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    Riwayat Pembayaran (<?= count($history) ?>)
                </h4>
                <div class="space-y-2">
                    <?php foreach ($history as $h): ?>
                        <div class="flex justify-between items-center bg-white rounded-lg px-3 py-2 border border-gray-100 text-sm">
                            <div>
                                <p class="font-medium text-gray-800"><?= $fmtRupiah($h['jumlah']) ?></p>
                                <p class="text-xs text-gray-500">
                                    <?= ucfirst(str_replace('_', ' ', $h['metode'])) ?> •
                                    <?= date('d M Y H:i', strtotime($h['paid_at'])) ?>
                                    <?php if (!empty($h['created_by_nama'])): ?>
                                        • oleh <?= Security::e($h['created_by_nama']) ?>
                                    <?php endif; ?>
                                </p>
                                <?php if (!empty($h['catatan'])): ?>
                                    <p class="text-xs text-gray-500 mt-0.5 italic">"<?= Security::e($h['catatan']) ?>"</p>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($h['bukti_file'])): ?>
                                <a href="/storage/uploads/<?= Security::e($h['bukti_file']) ?>" target="_blank"
                                   class="text-xs text-brand-600 hover:underline flex-shrink-0 ml-3">Lihat Bukti</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Alasan Cancel (kalau cancelled) -->
    <?php if ($invoice['status'] === 'cancelled' && !empty($invoice['cancel_reason'])): ?>
        <div class="mt-4 p-4 bg-red-50 border border-red-100 rounded-lg">
            <p class="text-sm font-semibold text-red-800 mb-1">Alasan Pembatalan:</p>
            <p class="text-sm text-red-700"><?= Security::e($invoice['cancel_reason']) ?></p>
        </div>
    <?php endif; ?>

    <!-- Aksi -->
    <div class="flex gap-2">
        <?php if ($invoice['status'] === 'unpaid'): ?>
            <a href="/admin/invoices/cancel/<?= (int) $invoice['id'] ?>"
               class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                Batalkan Tagihan
            </a>
            <a href="/admin/manual-payments/create?invoice_id=<?= (int) $invoice['id'] ?>"
               class="bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-brand">
                Catat Pembayaran
            </a>
        <?php endif; ?>
        <a href="/admin/invoices"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';