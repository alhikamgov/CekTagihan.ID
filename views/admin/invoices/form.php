<?php
use App\Helpers\Security;

$val = fn(string $key) => Security::e($old[$key] ?? '');

ob_start();
?>

<div class="max-w-2xl">

    <div class="mb-4">
        <a href="/admin/invoices" class="text-sm text-blue-600 hover:underline">← Kembali ke daftar invoice</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Buat Tagihan Baru</h3>

        <form method="POST" action="/admin/invoices/store" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">
                    Pelanggan <span class="text-red-500">*</span>
                </label>
                <select name="payer_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php foreach ($payers as $p): ?>
                        <option value="<?= (int) $p['id'] ?>" <?= ($old['payer_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                            <?= Security::e($p['nomor_induk']) ?> — <?= Security::e($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['payer_id'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['payer_id']) ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
    <label class="block text-sm font-medium mb-1">
        Jenis <span class="text-red-500">*</span>
    </label>
    <select name="jenis" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
        <option value="">-- Pilih Jenis --</option>
        <?php foreach ($jenisList as $jt): ?>
            <option value="<?= Security::e($jt['nama']) ?>"
                    <?= ($old['jenis'] ?? '') === $jt['nama'] ? 'selected' : '' ?>>
                <?= ucfirst(Security::e($jt['nama'])) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php if (!empty($errors['jenis'])): ?>
        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['jenis']) ?></p>
    <?php endif; ?>
</div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Periode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="periode" value="<?= $val('periode') ?>" required
                           placeholder="Contoh: 2026-10 atau Okt 2026"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <?php if (!empty($errors['periode'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['periode']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah" value="<?= $val('jumlah') ?>" required min="0" step="any"
                           placeholder="2500000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <?php if (!empty($errors['jumlah'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['jumlah']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Jatuh Tempo <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="due_date" value="<?= $val('due_date') ?>" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <?php if (!empty($errors['due_date'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['due_date']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                    Simpan Tagihan
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