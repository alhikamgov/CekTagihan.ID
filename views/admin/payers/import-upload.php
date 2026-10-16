<?php
use App\Helpers\Security;

$title = 'Import Pelanggan dari Excel';
ob_start();
?>

<div class="max-w-3xl">

    <div class="mb-4">
        <a href="/admin/payers" class="text-sm text-blue-600 hover:underline">← Kembali ke daftar</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">

        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold">Import Pelanggan dari Excel</h3>
                <p class="text-sm text-gray-500 mt-1">Upload file Excel/CSV untuk menambahkan payer secara massal.</p>
            </div>
            <a href="/admin/payers/import/template"
               class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-2 rounded-lg whitespace-nowrap">
                ⬇ Download Template
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm">
                <?= Security::e($error) ?>
            </div>
        <?php endif; ?>

        <!-- Panduan -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm">
            <p class="font-semibold text-blue-900 mb-2">Panduan:</p>
            <ol class="list-decimal list-inside space-y-1 text-blue-800">
                <li>Download template di kanan atas.</li>
                <li>Isi kolom: <code class="bg-white px-1 rounded">nomor_pelanggan</code>, <code class="bg-white px-1 rounded">nama</code>, <code class="bg-white px-1 rounded">whatsapp</code>.</li>
                <li>Kolom <strong>nomor_pelanggan</strong> dan <strong>nama</strong> wajib diisi.</li>
                <li>Jangan ubah nama header di baris pertama.</li>
                <li>Simpan file, lalu upload di form bawah.</li>
            </ol>
        </div>

        <form method="POST" action="/admin/payers/import/upload" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">
                    File Excel/CSV <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
                <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv. Maks 5 MB.</p>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    Upload & Preview
                </button>
                <a href="/admin/payers"
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