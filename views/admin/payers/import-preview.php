<?php
use App\Helpers\Security;

$title = 'Preview Import Pelanggan';
ob_start();
?>

<div class="mb-4">
    <a href="/admin/payers/import" class="text-sm text-blue-600 hover:underline">← Upload ulang</a>
</div>

<!-- Ringkasan -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-sm p-5">
        <p class="text-sm text-gray-500">Total Baris</p>
        <p class="text-2xl font-bold mt-1"><?= (int) $total ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5">
        <p class="text-sm text-gray-500">Valid</p>
        <p class="text-2xl font-bold mt-1 text-green-600"><?= count($valid) ?></p>
    </div>
    <div class="bg-white rounded-lg shadow-sm p-5">
        <p class="text-sm text-gray-500">Invalid</p>
        <p class="text-2xl font-bold mt-1 text-red-600"><?= count($invalid) ?></p>
    </div>
</div>

<!-- Invalid rows -->
<?php if (!empty($invalid)): ?>
    <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
        <div class="px-4 py-3 bg-red-50 border-b border-red-100">
            <h3 class="font-semibold text-red-800">Baris Invalid (<?= count($invalid) ?>)</h3>
            <p class="text-xs text-red-600 mt-1">Baris berikut akan di-skip.</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-2">Baris</th>
                    <th class="px-4 py-2">Nomor Pelanggan</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Error</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($invalid as $row): ?>
                    <tr>
                        <td class="px-4 py-2 text-gray-500"><?= (int) $row['row_number'] ?></td>
                        <td class="px-4 py-2 font-mono"><?= Security::e($row['nomor_induk']) ?></td>
                        <td class="px-4 py-2"><?= Security::e($row['nama']) ?></td>
                        <td class="px-4 py-2 text-red-600 text-xs">
                            <?= Security::e(implode(', ', $row['errors'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Valid rows -->
<?php if (!empty($valid)): ?>
    <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
        <div class="px-4 py-3 bg-green-50 border-b border-green-100">
            <h3 class="font-semibold text-green-800">Baris Valid (<?= count($valid) ?>)</h3>
            <p class="text-xs text-green-600 mt-1">Baris berikut akan di-import.</p>
        </div>
        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-left sticky top-0">
                    <tr>
                        <th class="px-4 py-2">Baris</th>
                        <th class="px-4 py-2">Nomor Pelanggan</th>
                        <th class="px-4 py-2">Nama</th>
                        <th class="px-4 py-2">WhatsApp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($valid as $row): ?>
                        <tr>
                            <td class="px-4 py-2 text-gray-500"><?= (int) $row['row_number'] ?></td>
                            <td class="px-4 py-2 font-mono"><?= Security::e($row['nomor_induk']) ?></td>
                            <td class="px-4 py-2"><?= Security::e($row['nama']) ?></td>
                            <td class="px-4 py-2 text-gray-600"><?= Security::e($row['whatsapp']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Commit -->
    <form method="POST" action="/admin/payers/import/commit" class="flex gap-2">
        <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">
        <button type="submit"
                onclick="return confirm('Import <?= count($valid) ?> Pelanggan sekarang?')"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg">
            Import <?= count($valid) ?> Pelanggan
        </button>
        <a href="/admin/payers/import"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
            Batal
        </a>
    </form>
<?php else: ?>
    <div class="bg-white rounded-lg shadow-sm p-8 text-center text-gray-400">
        Tidak ada baris valid untuk diimport.
    </div>
    <div class="mt-4">
        <a href="/admin/payers/import"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
            Upload Ulang
        </a>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';