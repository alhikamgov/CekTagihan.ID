<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

$statusBadge = function($status) {
    $map = [
        'pending'  => ['bg-amber-100 text-amber-700', 'Menunggu'],
        'approved' => ['bg-blue-100 text-blue-700', 'Disetujui'],
        'paid'     => ['bg-green-100 text-green-700', 'Sudah Dibayar'],
        'rejected' => ['bg-red-100 text-red-700', 'Ditolak'],
    ];
    $m = $map[$status] ?? ['bg-gray-100 text-gray-700', ucfirst($status)];
    return '<span class="inline-flex items-center px-2 py-0.5 text-xs rounded-full ' . $m[0] . ' font-medium">' . $m[1] . '</span>';
};

ob_start();
?>

<div class="space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Pengajuan Pencairan</h1>
        <span class="text-sm text-gray-500">Total: <?= (int) $total ?></span>
    </div>

    <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700"><?= Security::e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="p-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700"><?= Security::e($success) ?></div>
    <?php endif; ?>

    <!-- Filter -->
    <form method="get" class="flex gap-3 flex-wrap bg-white p-4 rounded-2xl border border-gray-100">
        <select name="status" class="border rounded-xl px-3 py-2 text-sm">
            <option value="">Semua Status</option>
            <?php foreach (['pending'=>'Menunggu','approved'=>'Disetujui','paid'=>'Sudah Dibayar','rejected'=>'Ditolak'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="q" value="<?= Security::e($filters['q']) ?>"
               placeholder="Cari tenant / pemilik rekening"
               class="border rounded-xl px-3 py-2 text-sm flex-1 min-w-[200px]">
        <button class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm">Filter</button>
    </form>

    <!-- Tabel -->
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <?php if (empty($items)): ?>
            <p class="p-8 text-center text-sm text-gray-500">Belum ada pengajuan.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Tanggal</th>
                            <th class="text-left p-3">Tenant</th>
                            <th class="text-right p-3">Jumlah</th>
                            <th class="text-left p-3">Rekening</th>
                            <th class="text-left p-3">Status</th>
                            <th class="text-left p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $w): ?>
                            <tr class="border-t">
                                <td class="p-3"><?= $fmtTanggal($w['requested_at']) ?></td>
                                <td class="p-3">
                                    <p class="font-medium"><?= Security::e($w['tenant_nama']) ?></p>
                                    <p class="text-xs text-gray-400">oleh <?= Security::e($w['requester_nama'] ?? '-') ?></p>
                                </td>
                                <td class="p-3 text-right font-semibold"><?= $fmtRupiah($w['amount']) ?></td>
                                <td class="p-3 text-xs">
                                    <?= Security::e($w['bank_nama']) ?><br>
                                    <?= Security::e($w['rekening_nomor']) ?><br>
                                    a/n <?= Security::e($w['rekening_pemilik']) ?>
                                </td>
                                <td class="p-3"><?= $statusBadge($w['status']) ?></td>
                                <td class="p-3 space-y-2">
                                    <?php if ($w['status'] === 'pending'): ?>
                                        <form method="post" action="/superadmin/pencairan/approve/<?= $w['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <button class="text-xs bg-blue-600 text-white px-2 py-1 rounded-lg">Setujui</button>
                                        </form>
                                        <form method="post" action="/superadmin/pencairan/reject/<?= $w['id'] ?>"
                                              onsubmit="this.reject_reason.value = prompt('Alasan penolakan:'); return this.reject_reason.value;">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="hidden" name="reject_reason" value="">
                                            <button class="text-xs bg-red-600 text-white px-2 py-1 rounded-lg">Tolak</button>
                                        </form>
                                    <?php elseif ($w['status'] === 'approved'): ?>
                                        <form method="post" action="/superadmin/pencairan/mark-paid/<?= $w['id'] ?>"
                                              enctype="multipart/form-data">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="file" name="payment_proof" accept="image/*,application/pdf"
                                                   class="text-xs mb-1">
                                            <button class="text-xs bg-green-600 text-white px-2 py-1 rounded-lg">Tandai Lunas</button>
                                        </form>
                                    <?php elseif ($w['status'] === 'paid' && $w['payment_proof']): ?>
                                        <a href="/uploads/pencairan/<?= Security::e($w['payment_proof']) ?>"
                                           target="_blank" class="text-xs text-brand-600 hover:underline">Lihat Bukti</a>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';