<?php
use App\Helpers\Security;

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

ob_start();
?>

<div class="mb-4">
    <a href="/admin/invoices/generate" class="text-sm text-blue-600 hover:underline">← Ubah pengaturan</a>
</div>

<!-- Ringkasan -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5">
        <p class="text-sm text-gray-500">Jenis</p>
        <p class="text-lg font-bold mt-1"><?= ucfirst(Security::e($jenis)) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5">
        <p class="text-sm text-gray-500">Periode</p>
        <p class="text-lg font-bold mt-1"><?= Security::e($periode) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5">
        <p class="text-sm text-gray-500">Jumlah per Pelanggan</p>
        <p class="text-lg font-bold mt-1"><?= $fmtRupiah($jumlah) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5">
        <p class="text-sm text-gray-500">Jatuh Tempo</p>
        <p class="text-lg font-bold mt-1"><?= $fmtTanggal($dueDate) ?></p>
    </div>
</div>

<!-- Info total -->
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 text-sm">
    <div class="flex justify-between mb-1">
        <span class="text-blue-900">Akan dibuatkan </span>
        <span class="font-semibold text-blue-900"><?= count($toGenerate) ?> Tagihan</span>
    </div>
    <div class="flex justify-between mb-1">
        <span class="text-blue-900">Total nilai</span>
        <span class="font-semibold text-blue-900"><?= $fmtRupiah($jumlah * count($toGenerate)) ?></span>
    </div>
    <?php if (!empty($skipped)): ?>
        <div class="flex justify-between pt-2 border-t border-blue-200">
            <span class="text-blue-900">Di-skip (duplikat)</span>
            <span class="font-semibold text-orange-600"><?= count($skipped) ?> payer</span>
        </div>
    <?php endif; ?>
</div>

<!-- Skipped -->
<?php if (!empty($skipped)): ?>
    <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
        <div class="px-4 py-3 bg-orange-50 border-b border-orange-100">
            <h3 class="font-semibold text-orange-800">Di-skip (<?= count($skipped) ?>)</h3>
            <p class="text-xs text-orange-600 mt-1">Pelanggan berikut sudah punya tagihan jenis + periode sama.</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-2">Nomor Pelanggan</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Alasan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($skipped as $s): ?>
                    <tr>
                        <td class="px-4 py-2 font-mono"><?= Security::e($s['payer']['nomor_induk']) ?></td>
                        <td class="px-4 py-2"><?= Security::e($s['payer']['nama']) ?></td>
                        <td class="px-4 py-2 text-orange-600 text-xs"><?= Security::e($s['reason']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- To generate -->
<?php if (!empty($toGenerate)): ?>
    <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
        <div class="px-4 py-3 bg-green-50 border-b border-green-100">
            <h3 class="font-semibold text-green-800">Akan dibuatkan (<?= count($toGenerate) ?>) Tagihan</h3>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-left sticky top-0">
                    <tr>
                        <th class="px-4 py-2">Nomor Pelanggan</th>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">WhatsApp</th>
                        <th class="px-4 py-2 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($toGenerate as $p): ?>
                        <tr>
                            <td class="px-4 py-2 font-mono"><?= Security::e($p['nomor_induk']) ?></td>
                            <td class="px-4 py-2"><?= Security::e($p['nama']) ?></td>
                            <td class="px-4 py-2 text-gray-600"><?= Security::e($p['whatsapp'] ?: '-') ?></td>
                            <td class="px-4 py-2 text-right"><?= $fmtRupiah($jumlah) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Commit -->
    <form method="POST" action="/admin/invoices/generate/commit" class="flex gap-2">
        <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">
        <button type="submit"
                onclick="return confirm('Generate <?= count($toGenerate) ?> Tagihan sekarang?')"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
            Buat <?= count($toGenerate) ?> Tagihan
        </button>
        <a href="/admin/invoices/generate"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
            Batal
        </a>
    </form>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-400 mb-4">
        Tidak ada payer yang bisa di-generate (semua sudah punya invoice jenis + periode sama).
    </div>
    <a href="/admin/invoices/generate"
       class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
        Kembali
    </a>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';