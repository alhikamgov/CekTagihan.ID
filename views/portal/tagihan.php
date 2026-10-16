<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

$today = new DateTime();
$isOverdue = function (string $dueDate) use ($today): bool {
    return new DateTime($dueDate) < $today;
};

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
       class="flex-1 text-center px-4 py-3 rounded-xl font-medium text-sm transition bg-white shadow-sm border-2 border-brand-500 text-brand-700">
        Belum Bayar
        <?php if (!empty($invoices)): ?>
            <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-brand-500 text-white text-xs">
                <?= count($invoices) ?>
            </span>
        <?php endif; ?>
    </a>
    <a href="/portal/riwayat"
       class="flex-1 text-center px-4 py-3 rounded-xl font-medium text-sm transition bg-white shadow-sm border-2 border-gray-200 text-gray-600 hover:border-brand-300">
        Riwayat
        <?php if (!empty($riwayatSummary['total_transaksi'])): ?>
            <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-200 text-gray-700 text-xs">
                <?= (int) $riwayatSummary['total_transaksi'] ?>
            </span>
        <?php endif; ?>
    </a>
</div>

<?php if (empty($invoices)): ?>

    <div class="bg-white rounded-3xl shadow-xl shadow-brand-100/50 p-8 text-center border border-brand-100">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-100 mb-4">
            <span class="text-3xl">✓</span>
        </div>
        <h3 class="font-bold text-gray-800 mb-1">Tidak Ada Tagihan</h3>
        <p class="text-sm text-gray-500">Semua tagihan Anda sudah lunas. Terima kasih!</p>
    </div>

    <a href="/portal/ulang"
       class="mt-4 block text-center bg-white hover:bg-gray-50 text-gray-700 font-medium py-3 rounded-xl border-2 border-gray-200 transition">
        Cek Nomor Lain
    </a>

<?php else: ?>

    <form method="POST" action="/portal/bayar" id="form-bayar">
        <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

        <p class="text-sm text-gray-500 mb-3 px-1">
            Centang tagihan yang mau dibayar. Bisa pilih lebih dari satu.
        </p>

        <div class="space-y-3 mb-4">
            <?php foreach ($invoices as $inv): ?>
                <?php
                    $overdue    = $isOverdue($inv['due_date']);
                    $remaining  = (float) ($inv['remaining'] ?? $inv['jumlah']);
                    $paidAmount = (float) ($inv['paid_amount'] ?? 0);
                    $total      = (float) $inv['jumlah'];
                    $isPartial  = $paidAmount > 0 && $paidAmount < $total;
                ?>
                <label class="block bg-white rounded-2xl shadow-sm p-4 cursor-pointer hover:shadow-md hover:ring-2 hover:ring-brand-300 transition border border-gray-100">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" name="invoice_ids[]" value="<?= (int) $inv['id'] ?>"
                               data-jumlah="<?= $remaining ?>"
                               class="mt-1 w-5 h-5 rounded text-brand-600 focus:ring-brand-500 invoice-checkbox"
                               checked>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <p class="font-semibold text-gray-800"><?= ucfirst(Security::e($inv['jenis'])) ?></p>
                                    <p class="text-xs text-gray-500"><?= Security::e($inv['periode']) ?></p>
                                </div>
                                <p class="font-bold text-gray-800 text-right whitespace-nowrap">
                                    <?= $fmtRupiah($remaining) ?>
                                </p>
                            </div>

                            <?php if ($isPartial): ?>
                                <div class="mt-2 p-2 bg-blue-50 border border-blue-100 rounded-lg text-xs">
                                    <div class="flex justify-between text-blue-800">
                                        <span>Total tagihan:</span>
                                        <span class="font-medium"><?= $fmtRupiah($total) ?></span>
                                    </div>
                                    <div class="flex justify-between text-blue-800 mt-0.5">
                                        <span>Sudah dibayar:</span>
                                        <span class="font-medium"><?= $fmtRupiah($paidAmount) ?></span>
                                    </div>
                                    <div class="flex justify-between text-blue-900 mt-1 pt-1 border-t border-blue-200 font-semibold">
                                        <span>Sisa:</span>
                                        <span><?= $fmtRupiah($remaining) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <p class="text-xs mt-2 <?= $overdue ? 'text-red-600 font-semibold' : 'text-gray-500' ?>">
                                Jatuh tempo: <?= $fmtTanggal($inv['due_date']) ?>
                                <?= $overdue ? ' • Terlambat' : '' ?>
                            </p>
                        </div>
                    </div>
                </label>
            <?php endforeach; ?>
        </div>

        <!-- Total -->
        <div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-2xl shadow-xl shadow-brand-200/50 p-5 sticky bottom-4">
            <div class="flex justify-between items-center mb-3">
                <span class="text-sm text-white/90">Total Terpilih</span>
                <span class="text-2xl font-bold text-white" id="total-display">Rp 0</span>
            </div>
            <button type="submit" id="btn-bayar"
                    class="w-full bg-white text-brand-700 font-bold py-3 rounded-xl hover:bg-brand-50 transition shadow-sm">
                Bayar Sekarang
            </button>
        </div>
    </form>

    <a href="/portal/ulang"
       class="mt-4 block text-center text-sm text-gray-500 hover:text-brand-600 py-2 transition">
        ← Cek Nomor Lain
    </a>

    <script>
    (function() {
        const checkboxes = document.querySelectorAll('.invoice-checkbox');
        const totalDisplay = document.getElementById('total-display');
        const btnBayar = document.getElementById('btn-bayar');

        function formatRupiah(angka) {
            return 'Rp ' + angka.toLocaleString('id-ID');
        }

        function updateTotal() {
            let total = 0, checked = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    total += parseFloat(cb.dataset.jumlah);
                    checked++;
                }
            });
            totalDisplay.textContent = formatRupiah(total);
            btnBayar.disabled = checked === 0;
            btnBayar.classList.toggle('opacity-50', checked === 0);
            btnBayar.classList.toggle('cursor-not-allowed', checked === 0);
        }

        checkboxes.forEach(cb => cb.addEventListener('change', updateTotal));
        updateTotal();
    })();
    </script>

<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';