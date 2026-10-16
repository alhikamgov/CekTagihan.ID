<?php
use App\Helpers\Security;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

ob_start();
?>

<div class="max-w-lg">

    <div class="mb-4">
        <a href="/admin/invoices" class="text-sm text-blue-600 hover:underline">← Kembali ke daftar invoice</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">

        <div class="flex items-start gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <span class="text-red-600 text-xl">⚠️</span>
            </div>
            <div>
                <h3 class="text-lg font-semibold">Batalkan Tagihan?</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Tindakan ini tidak bisa dibatalkan. Tagihan akan berstatus <strong>Dibatalkan</strong>.
                </p>
            </div>
        </div>

        <!-- Info Tagihan -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Pelanggan</span>
                <span class="font-medium"><?= Security::e($invoice['payer_nama']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Nomor Pelanggan</span>
                <span class="font-mono"><?= Security::e($invoice['nomor_induk']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Jenis</span>
                <span><?= ucfirst(Security::e($invoice['jenis'])) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Periode</span>
                <span><?= Security::e($invoice['periode']) ?></span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-200">
                <span class="text-gray-500">Jumlah</span>
                <span class="font-bold"><?= $fmtRupiah($invoice['jumlah']) ?></span>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="/admin/invoices/cancel-confirm/<?= (int) $invoice['id'] ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">
                    Alasan Pembatalan <span class="text-gray-400 text-xs">(opsional)</span>
                </label>
                <textarea name="cancel_reason" rows="3" maxlength="255"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                          placeholder="Contoh: Salah input jumlah, payer sudah bayar tunai, dll."></textarea>
                <p class="text-xs text-gray-500 mt-1">Alasan akan dicatat di audit log.</p>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    Ya, Batalkan Tagihan
                </button>
                <a href="/admin/invoices"
                   class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';