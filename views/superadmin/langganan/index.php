<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

ob_start();
?>

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Kelola Langganan</h1>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700"><?= Security::e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="p-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700"><?= Security::e($success) ?></div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="flex gap-2 flex-wrap">
        <?php
        $filters = [
            ''         => 'Semua',
            'active'   => 'Aktif',
            'expiring' => 'Akan Habis (7 hari)',
            'expired'  => 'Kedaluwarsa',
        ];
        foreach ($filters as $key => $label):
        ?>
            <a href="?status=<?= $key ?>"
               class="px-4 py-2 rounded-xl text-sm font-medium transition
                      <?= $filter === $key
                          ? 'bg-emerald-600 text-white'
                          : 'bg-white text-gray-700 border border-gray-200 hover:border-emerald-300' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">Tenant</th>
                        <th class="text-left p-3">Paket</th>
                        <th class="text-left p-3">Berlaku Sampai</th>
                        <th class="text-left p-3">Sisa</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tenants)): ?>
                        <tr><td colspan="6" class="p-6 text-center text-gray-500">Tidak ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tenants as $t): ?>
                            <?php
                            $daysLeft = $t['days_left'];
                            $isExpired = $daysLeft === null || (int)$daysLeft < 0;
                            ?>
                            <tr class="border-t hover:bg-gray-50">
                                <td class="p-3">
                                    <p class="font-medium"><?= Security::e($t['tenant_nama']) ?></p>
                                    <p class="text-xs text-gray-400"><?= Security::e($t['slug']) ?></p>
                                </td>
                                <td class="p-3"><?= $t['plan_name'] ? Security::e(ucfirst($t['plan_name'])) : '—' ?></td>
                                <td class="p-3"><?= $t['end_date'] ? $fmtTanggal($t['end_date']) : '—' ?></td>
                                <td class="p-3">
                                    <?php if ($daysLeft === null): ?>
                                        <span class="text-gray-400">—</span>
                                    <?php elseif ((int)$daysLeft < 0): ?>
                                        <span class="text-red-600 font-medium">
                                            Lewat <?= abs((int)$daysLeft) ?> hari
                                        </span>
                                    <?php elseif ((int)$daysLeft <= 7): ?>
                                        <span class="text-amber-600 font-medium">
                                            <?= (int)$daysLeft ?> hari lagi
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-700"><?= (int)$daysLeft ?> hari</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <?php if ($isExpired): ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">Kedaluwarsa</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <button type="button"
                                            onclick="document.getElementById('extend-<?= $t['tenant_id'] ?>').classList.toggle('hidden')"
                                            class="text-xs bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg">
                                        Perpanjang
                                    </button>
                                </td>
                            </tr>
                            <!-- Form perpanjang (hidden) -->
                            <tr id="extend-<?= $t['tenant_id'] ?>" class="hidden border-t bg-emerald-50/30">
                                <td colspan="6" class="p-4">
                                    <form method="post" action="/superadmin/langganan/extend/<?= $t['tenant_id'] ?>" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                                        <select name="plan_name" class="border rounded-xl px-3 py-2 text-sm">
                                            <option value="manual">Manual</option>
                                            <option value="monthly">Bulanan</option>
                                            <option value="yearly">Tahunan</option>
                                        </select>

                                        <select name="months" class="border rounded-xl px-3 py-2 text-sm">
                                            <option value="1">1 bulan</option>
                                            <option value="3">3 bulan</option>
                                            <option value="6">6 bulan</option>
                                            <option value="12">12 bulan</option>
                                        </select>

                                        <input type="number" name="price" placeholder="Harga (Rp)" value="200000"
                                               class="border rounded-xl px-3 py-2 text-sm" required>

                                        <input type="text" name="note" placeholder="Catatan (opsional)"
                                               class="border rounded-xl px-3 py-2 text-sm">

                                        <button class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-xl text-sm">
                                            Simpan Perpanjangan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';