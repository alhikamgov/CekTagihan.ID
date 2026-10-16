<?php
use App\Helpers\Pagination;
use App\Helpers\Security;

$title = 'Kelola Tenant';
ob_start();
?>

<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
    <div>
        <h3 class="text-lg font-semibold">Daftar Tenant</h3>
        <p class="text-sm text-gray-500">Total: <?= (int) $total ?> tenant</p>
    </div>
    <a href="/superadmin/tenants/create"
       class="bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-sm transition">
        + Tambah Tenant
    </a>
</div>

<?php if (!empty($_SESSION['new_admin_credentials'])): ?>
    <?php $cred = $_SESSION['new_admin_credentials']; ?>
    <div class="mb-6 bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-300 rounded-2xl p-5 shadow-sm">
        <div class="flex items-start gap-3 mb-4">
            <span class="text-2xl">🔑</span>
            <div>
                <h3 class="font-bold text-amber-900">Simpan Kredensial Admin Tenant!</h3>
                <p class="text-xs text-amber-700 mt-0.5">Password hanya ditampilkan <strong>sekali ini</strong>. Copy dan kirimkan ke admin tenant sekarang.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl p-4 space-y-2 text-sm font-mono">
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Tenant:</span>
                <span class="font-semibold"><?= Security::e($cred['tenant_nama']) ?></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Nama:</span>
                <span><?= Security::e($cred['admin_nama']) ?></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Email:</span>
                <span><?= Security::e($cred['admin_email']) ?></span>
            </div>
            <div class="flex justify-between gap-2 items-center">
                <span class="text-gray-500">Password:</span>
                <span class="font-bold text-amber-900"><?= Security::e($cred['password']) ?></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Portal:</span>
                <span><?= Security::e($_ENV['APP_URL'] ?? '') ?>/portal/<?= Security::e($cred['tenant_slug']) ?></span>
            </div>
        </div>

        <div class="mt-3 flex gap-2">
            <button type="button" onclick="copyCredentials(this)"
                    data-text="Email: <?= Security::e($cred['admin_email']) ?> | Password: <?= Security::e($cred['password']) ?>"
                    class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                📋 Copy Kredensial
            </button>
            <a href="/superadmin/tenants?clear_cred=1"
               class="bg-white hover:bg-gray-50 border border-amber-300 text-amber-800 text-xs font-semibold px-3 py-2 rounded-lg transition">
                Saya Sudah Simpan
            </a>
        </div>
    </div>

    <script>
    function copyCredentials(btn) {
        navigator.clipboard.writeText(btn.dataset.text);
        btn.textContent = '✓ Tersalin!';
        setTimeout(() => btn.textContent = '📋 Copy Kredensial', 2000);
    }
    </script>

    <?php unset($_SESSION['new_admin_credentials']); ?>
<?php endif; ?>

<!-- Filter -->
<form method="GET" action="/superadmin/tenants" class="mb-4 flex gap-2 flex-wrap">
    <input type="text" name="q" value="<?= Security::e($search ?? '') ?>"
           placeholder="Cari nama atau slug tenant..."
           class="flex-1 min-w-[200px] px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-sm">

    <select name="status"
            class="px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm">
        <option value="">Semua Status</option>
        <option value="active"    <?= ($status ?? '') === 'active'    ? 'selected' : '' ?>>Aktif</option>
        <option value="suspended" <?= ($status ?? '') === 'suspended' ? 'selected' : '' ?>>Suspend</option>
    </select>

    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg">
        Cari
    </button>

    <?php if (($search ?? '') !== '' || ($status ?? '') !== ''): ?>
        <a href="/superadmin/tenants" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
            Reset
        </a>
    <?php endif; ?>
</form>

<!-- Tabel -->
<div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Kontak</th>
                    <th class="px-4 py-3 text-center">Pelanggan</th>
                    <th class="px-4 py-3 text-center">Tagihan</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($tenants)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <?php if ($search !== '' || $status !== ''): ?>
                                Tidak ada tenant yang cocok.
                            <?php else: ?>
                                Belum ada tenant. Klik "+ Tambah Tenant" untuk mulai.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tenants as $t): ?>
                        <tr class="hover:bg-brand-50/40 transition">
                            <td class="px-4 py-3 font-medium"><?= Security::e($t['nama']) ?></td>
                            <td class="px-4 py-3 text-gray-500 font-mono text-xs"><?= Security::e($t['slug']) ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <?= Security::e($t['telepon'] ?: '-') ?><br>
                                <span><?= Security::e($t['email'] ?: '') ?></span>
                            </td>
                            <td class="px-4 py-3 text-center"><?= (int) $t['total_payers'] ?></td>
                            <td class="px-4 py-3 text-center"><?= (int) $t['total_invoices'] ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($t['status'] === 'active'): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-brand-100 text-brand-700 font-medium">Aktif</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">Suspend</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
    <a href="/superadmin/tenants/edit/<?= (int) $t['id'] ?>"
       class="text-brand-600 hover:text-brand-800 font-medium">Edit</a>
    <a href="/superadmin/tenants/reset-password/<?= (int) $t['id'] ?>"
       onclick="return confirm('Reset password admin tenant ini? Password baru akan ditampilkan sekali.')"
       class="text-blue-600 hover:text-blue-800 font-medium">Reset Sandi</a>
    <a href="/superadmin/tenants/toggle/<?= (int) $t['id'] ?>"
       onclick="return confirm('Yakin ubah status tenant ini?')"
       class="text-orange-600 hover:text-orange-800 font-medium">
        <?= $t['status'] === 'active' ? 'Suspend' : 'Aktifkan' ?>
    </a>
</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo Pagination::render($page, $lastPage, '/superadmin/tenants', [
    'q'      => $search,
    'status' => $status,
]); ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';