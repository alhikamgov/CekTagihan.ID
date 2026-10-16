<?php
use App\Helpers\Security;

ob_start();
?>

<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
    <div>
        <h3 class="text-lg font-semibold">Daftar Jenis Tagihan</h3>
        <p class="text-sm text-gray-500">Total: <?= count($types) ?> jenis</p>
    </div>
    <a href="/admin/jenis-tagihan/create"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-brand transition">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Tambah Jenis
    </a>
</div>

<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 text-left">
            <tr>
                <th class="px-4 py-3 w-16">Urutan</th>
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($types)): ?>
                <tr>
                    <td colspan="4" class="px-4 py-16 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 rounded-2xl bg-brand-50 flex items-center justify-center">
                                <i data-lucide="tag" class="w-8 h-8 text-brand-400"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700">Belum ada jenis tagihan</p>
                                <p class="text-sm text-gray-500 mt-0.5">Tambahkan jenis tagihan seperti Sewa, IPL, Listrik.</p>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($types as $t): ?>
                    <tr class="hover:bg-brand-50/40 transition-colors">
                        <td class="px-4 py-3 text-gray-500 text-center"><?= (int) $t['urutan'] ?></td>
                        <td class="px-4 py-3 font-medium text-gray-800"><?= Security::e($t['nama']) ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($t['status'] === 'active'): ?>
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
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <a href="/admin/jenis-tagihan/edit/<?= (int) $t['id'] ?>"
                               class="text-brand-600 hover:text-brand-800 font-medium">Edit</a>
                            <a href="/admin/jenis-tagihan/toggle/<?= (int) $t['id'] ?>"
                               onclick="return confirm('Ubah status jenis ini?')"
                               class="text-orange-600 hover:text-orange-800 font-medium">
                                <?= $t['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';