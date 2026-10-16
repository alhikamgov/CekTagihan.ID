<?php
use App\Helpers\Security;

$statusBadge = function (string $status) {
    return match ($status) {
        'pending'  => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-50 text-amber-700 font-medium ring-1 ring-amber-200/50"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Menunggu</span>',
        'verified' => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium ring-1 ring-brand-200/50"><span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>Terverifikasi</span>',
        'rejected' => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-red-50 text-red-700 font-medium ring-1 ring-red-200/50"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Ditolak</span>',
        default    => '',
    };
};

ob_start();
?>

<div class="mb-6">
    <h3 class="text-lg font-semibold">Verifikasi Rekening Tenant</h3>
    <p class="text-sm text-gray-500">Total: <?= count($rekeningList) ?> rekening</p>
</div>

<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Tenant</th>
                    <th class="px-4 py-3">Bank</th>
                    <th class="px-4 py-3">Nomor Rekening</th>
                    <th class="px-4 py-3">Pemilik</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($rekeningList)): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="landmark" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">Belum ada rekening</p>
                                    <p class="text-sm text-gray-500 mt-0.5">Tenant belum mengisi data rekening bank.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekeningList as $r): ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800"><?= Security::e($r['tenant_nama']) ?></p>
                                <p class="text-xs text-gray-500 font-mono"><?= Security::e($r['slug']) ?></p>
                            </td>
                            <td class="px-4 py-3 text-gray-700"><?= Security::e($r['bank_nama']) ?></td>
                            <td class="px-4 py-3 font-mono text-gray-700"><?= Security::e($r['rekening_nomor']) ?></td>
                            <td class="px-4 py-3 text-gray-700"><?= Security::e($r['rekening_pemilik']) ?></td>
                            <td class="px-4 py-3 text-center"><?= $statusBadge($r['status']) ?></td>
                            <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                <?php if ($r['status'] === 'pending'): ?>
                                    <a href="/superadmin/rekening/verify/<?= (int) $r['id'] ?>"
                                       onclick="return confirm('Verifikasi rekening ini?')"
                                       class="text-brand-600 hover:text-brand-800 font-medium">
                                        Verifikasi
                                    </a>
                                    <a href="/superadmin/rekening/reject/<?= (int) $r['id'] ?>"
                                       onclick="return confirm('Tolak rekening ini?')"
                                       class="text-red-600 hover:text-red-800 font-medium">
                                        Tolak
                                    </a>
                                <?php elseif ($r['status'] === 'verified'): ?>
                                    <span class="text-gray-400 text-xs">Terverifikasi</span>
                                <?php else: ?>
                                    <a href="/superadmin/rekening/verify/<?= (int) $r['id'] ?>"
                                       onclick="return confirm('Verifikasi ulang rekening ini?')"
                                       class="text-brand-600 hover:text-brand-800 font-medium">
                                        Verifikasi Ulang
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';