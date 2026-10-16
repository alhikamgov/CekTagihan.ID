<?php
use App\Helpers\Security;
use App\Helpers\Lang;

$title = 'Kelola Pelanggan';
ob_start();
?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h3 class="text-lg font-semibold">Daftar Pelanggan</h3>
        <p class="text-sm text-gray-500">Total: <?= (int) $total ?> Pelanggan</p>
    </div>
    <div class="flex gap-2">
    <a href="/admin/payers/import"
       class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg">
        <i data-lucide="upload"></i> Import Excel
    </a>
    <a href="/admin/payers/create"
   class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-brand transition-all hover:shadow-lg">
    <i data-lucide="plus" class="w-4 h-4"></i>
    Tambah Pelanggan
</a>
</div>
</div>

<!-- Filter -->
<form method="GET" action="/admin/payers" class="mb-4 flex gap-2">
    <div class="relative flex-1 min-w-[200px]">
    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
    <input type="text" name="q" value="<?= Security::e($search) ?>"
           placeholder="Cari <?= Lang::NOMOR_PELANGGAN_LC ?>, nama, atau telepon..."
           class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none text-sm transition">
</div>

<button type="submit" class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl">
    <i data-lucide="filter" class="w-4 h-4"></i>
    Filter
</button>

    <select name="status"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-sm">
        <option value="">Semua Status</option>
        <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Aktif</option>
        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
    </select>

    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-lg">
        Cari
    </button>

    <?php if ($search !== '' || $status !== ''): ?>
        <a href="/admin/payers" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg">
            Reset
        </a>
    <?php endif; ?>
</form>

<!-- Tabel -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 text-left">
            <tr>
                <th class="px-4 py-3"><?= Lang::NOMOR_PELANGGAN ?></th>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Kontak</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($payers)): ?>
                <tr>
                    <td colspan="5" class="px-4 py-16 text-center">
    <div class="flex flex-col items-center gap-3">
        <div class="w-16 h-16 rounded-2xl bg-brand-50 flex items-center justify-center">
            <i data-lucide="users" class="w-8 h-8 text-brand-400"></i>
        </div>
        <div>
            <p class="font-semibold text-gray-700">Belum ada payer</p>
            <p class="text-sm text-gray-500 mt-0.5">Tambahkan payer atau import dari Excel.</p>
        </div>
        <a href="/admin/payers/create"
           class="mt-2 inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Pelanggan Pertama
        </a>
    </div>
</td>
                </tr>
            <?php else: ?>
                <?php foreach ($payers as $p): ?>
                    <tr class="hover:bg-brand-50/40 transition-colors">
                        <td class="px-4 py-3 font-mono font-medium"><?= Security::e($p['nomor_induk']) ?></td>
                        <td class="px-4 py-3 font-medium"><?= Security::e($p['nama']) ?></td>
                        <td class="px-4 py-3 text-gray-500">
    <?= Security::e($p['whatsapp'] ?: '-') ?>
</td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($p['status'] === 'active'): ?>
    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium ring-1 ring-brand-200/50">
        <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
        Aktif
    </span>
<?php else: ?>
    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-gray-100 text-gray-600 font-medium ring-1 ring-gray-200">
        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
        Nonaktif
    </span>
<?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="/admin/payers/edit/<?= (int) $p['id'] ?>"
                               class="text-blue-600 hover:text-blue-800">Edit</a>
                            <a href="/admin/payers/toggle/<?= (int) $p['id'] ?>"
                               onclick="return confirm('Yakin ubah status payer ini?')"
                               class="text-orange-600 hover:text-orange-800">
                                <?= $p['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
use App\Helpers\Pagination;
echo Pagination::render($page, $lastPage, '/admin/payers', [
    'q'      => $search,
    'status' => $status,
]);
?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';