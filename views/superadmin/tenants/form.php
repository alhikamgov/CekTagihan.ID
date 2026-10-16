<?php
use App\Helpers\Security;

$isEdit = $tenant !== null;
$title  = $isEdit ? 'Edit Tenant' : 'Tambah Tenant';
$action = $isEdit ? '/superadmin/tenants/update/' . $tenant['id'] : '/superadmin/tenants/store';

$val      = fn(string $key) => Security::e($old[$key] ?? $tenant[$key] ?? '');
$valAdmin = fn(string $key) => Security::e($old['admin_' . $key] ?? $admin[$key] ?? '');

ob_start();
?>

<div class="max-w-2xl">

    <div class="mb-4">
        <a href="/superadmin/tenants" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-bold mb-6"><?= $isEdit ? 'Edit' : 'Tambah' ?> Tenant</h3>

        <form method="POST" action="<?= Security::e($action) ?>" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <!-- Section 1: Info Tenant -->
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">1</span>
                    Informasi Tenant
                </h4>

                <div class="space-y-4 pl-8">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Tenant <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="<?= $val('nama') ?>" required
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
                               placeholder="Contoh: Ruko Mekar Jaya">
                        <?php if (!empty($errors['nama'])): ?>
                            <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['nama']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Slug</label>
                        <input type="text" name="slug" value="<?= $val('slug') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none font-mono text-sm"
                               placeholder="otomatis dari nama">
                        <p class="text-xs text-gray-500 mt-1">Dipakai di URL portal.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Alamat</label>
                        <textarea name="alamat" rows="2"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                  placeholder="Alamat lengkap"><?= $val('alamat') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Telepon</label>
                            <input type="text" name="telepon" value="<?= $val('telepon') ?>"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                   placeholder="0812xxxxxxx">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email Tenant</label>
                            <input type="email" name="email" value="<?= $val('email') ?>"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                   placeholder="admin@ruko.com">
                            <?php if (!empty($errors['email'])): ?>
                                <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['email']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Admin Tenant -->
            <div class="pt-6 border-t border-gray-100">
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-xs font-bold">2</span>
                    Akun Admin Tenant
                </h4>
                <p class="text-xs text-gray-500 mb-4 pl-8">
                    <?= $isEdit ? 'Ubah nama atau email admin tenant.' : 'Akun ini akan dipakai admin tenant untuk login. Password akan di-generate otomatis.' ?>
                </p>

                <div class="space-y-4 pl-8">
                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Nama Admin <?= $isEdit ? '' : '<span class="text-red-500">*</span>' ?>
                        </label>
                        <input type="text" name="admin_nama" value="<?= $valAdmin('nama') ?>" <?= $isEdit ? '' : 'required' ?>
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="Contoh: Budi Santoso">
                        <?php if (!empty($errors['admin_nama'])): ?>
                            <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['admin_nama']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Email Admin <?= $isEdit ? '' : '<span class="text-red-500">*</span>' ?>
                        </label>
                        <input type="email" name="admin_email" value="<?= $valAdmin('email') ?>" <?= $isEdit ? '' : 'required' ?>
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="admin@rukomekar.com">
                        <p class="text-xs text-gray-500 mt-1">Email ini untuk login admin tenant.</p>
                        <?php if (!empty($errors['admin_email'])): ?>
                            <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['admin_email']) ?></p>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isEdit): ?>
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 flex items-start gap-2">
                            <span class="flex-shrink-0">🔑</span>
                            <span>Password akan dibuat otomatis dan ditampilkan <strong>hanya sekali</strong> setelah tenant dibuat.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t border-gray-100">
                <button type="submit"
                        class="bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-sm transition">
                    <?= $isEdit ? 'Update Tenant' : 'Buat Tenant & Admin' ?>
                </button>
                <a href="/superadmin/tenants"
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