<?php
use App\Helpers\Lang;
use App\Helpers\Security;

$isEdit = $payer !== null;
$title  = $isEdit ? 'Edit ' . Lang::PAYER : Lang::AKSI_TAMBAH . ' ' . Lang::PAYER;
$action = $isEdit ? '/admin/payers/update/' . $payer['id'] : '/admin/payers/store';

$val = fn(string $key) => Security::e($old[$key] ?? $payer[$key] ?? '');

ob_start();
?>

<div class="max-w-2xl">

    <div class="mb-4">
        <a href="/admin/payers" class="text-sm text-brand-600 hover:underline">← <?= Lang::AKSI_KEMBALI ?> ke daftar</a>
    </div>

    <div class="bg-white rounded-2xl shadow-soft p-6 border border-gray-100">
        <h3 class="text-lg font-bold mb-4"><?= $isEdit ? Lang::AKSI_EDIT : Lang::AKSI_TAMBAH ?> <?= Lang::PAYER ?></h3>

        <form method="POST" action="<?= Security::e($action) ?>" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">
                    <?= Lang::NOMOR_PELANGGAN ?> <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nomor_induk" value="<?= $val('nomor_induk') ?>" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none font-mono"
                       placeholder="Contoh: A101">
                <p class="text-xs text-gray-500 mt-1">Dipakai <?= Lang::PELANGGAN_LC ?> untuk cek tagihan di portal.</p>
                <?php if (!empty($errors['nomor_induk'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['nomor_induk']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    <?= Lang::NAMA ?> <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" value="<?= $val('nama') ?>" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                       placeholder="Contoh: Budi Santoso">
                <?php if (!empty($errors['nama'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['nama']) ?></p>
                <?php endif; ?>
            </div>

            <div>
    <label class="block text-sm font-medium mb-1">Nomor WhatsApp</label>
    <input type="text" name="whatsapp" value="<?= $val('whatsapp') ?>"
           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
           placeholder="0812xxxxxxx">
    <p class="text-xs text-gray-500 mt-1">Nomor WA untuk kirim notifikasi tagihan.</p>
    <?php if (!empty($errors['whatsapp'])): ?>
        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['whatsapp']) ?></p>
    <?php endif; ?>
</div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-brand transition">
                    <?= $isEdit ? 'Update' : Lang::AKSI_SIMPAN ?>
                </button>
                <a href="/admin/payers"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-xl transition">
                    <?= Lang::AKSI_BATAL ?>
                </a>
            </div>
        </form>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';