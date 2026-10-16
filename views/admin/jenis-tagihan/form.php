<?php
use App\Helpers\Security;

$isEdit = $type !== null;
$title  = $isEdit ? 'Edit Jenis Tagihan' : 'Tambah Jenis Tagihan';
$action = $isEdit ? '/admin/jenis-tagihan/update/' . $type['id'] : '/admin/jenis-tagihan/store';

$val = fn(string $key) => Security::e($old[$key] ?? $type[$key] ?? '');

ob_start();
?>

<div class="max-w-lg mx-auto">

    <div class="mb-4">
        <a href="/admin/jenis-tagihan" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-bold mb-4"><?= $isEdit ? 'Edit' : 'Tambah' ?> Jenis Tagihan</h3>

        <form method="POST" action="<?= Security::e($action) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">
                    Nama Jenis <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" value="<?= $val('nama') ?>" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                       placeholder="Contoh: Iuran Keamanan">
                <p class="text-xs text-gray-500 mt-1">Tulis sesuai keinginan Anda (contoh: Iuran Keamanan, SPP Bulanan).</p>
                <?php if (!empty($errors['nama'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['nama']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Urutan Tampil</label>
                <input type="number" name="urutan" value="<?= $val('urutan') ?>" min="0"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                       placeholder="0">
                <p class="text-xs text-gray-500 mt-1">Angka kecil tampil lebih dulu.</p>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-brand transition">
                    <?= $isEdit ? 'Update' : 'Simpan' ?>
                </button>
                <a href="/admin/jenis-tagihan"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';