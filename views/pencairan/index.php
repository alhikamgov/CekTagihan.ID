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

    <h1 class="text-2xl font-bold text-gray-800">Pencairan Saldo</h1>

    <?php if (!empty($error)): ?>
        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700"><?= Security::e($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="p-3 rounded-xl bg-green-50 border border-green-200 text-sm text-green-700"><?= Security::e($success) ?></div>
    <?php endif; ?>

    <!-- 4 Card Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-gradient-to-br from-brand-500 to-teal-600 text-white rounded-2xl p-5 shadow-lg">
            <p class="text-xs font-medium opacity-90">Sisa Saldo</p>
            <p class="text-2xl font-bold mt-1"><?= $fmtRupiah($summary['available']) ?></p>
            <p class="text-xs mt-2 opacity-75">Bisa ditarik</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <p class="text-xs font-medium text-gray-500">Pendapatan Online</p>
            <p class="text-xl font-bold text-gray-800 mt-1"><?= $fmtRupiah($summary['total_income']) ?></p>
            <p class="text-xs text-gray-400 mt-2">Settlement + capture</p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <p class="text-xs font-medium text-gray-500">Sudah Ditarik</p>
            <p class="text-xl font-bold text-gray-800 mt-1"><?= $fmtRupiah($summary['total_withdrawn']) ?></p>
            <p class="text-xs text-gray-400 mt-2">
                Pending: <?= $fmtRupiah($summary['total_pending']) ?>
            </p>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
            <p class="text-xs font-medium text-gray-500">Pembayaran Manual</p>
            <p class="text-xl font-bold text-gray-800 mt-1"><?= $fmtRupiah($summary['total_manual']) ?></p>
            <p class="text-xs text-gray-400 mt-2">Tunai / transfer langsung</p>
        </div>

    </div>

    <!-- Form Pengajuan -->
    <div class="bg-white rounded-2xl p-6 shadow border border-gray-100">
        <h2 class="font-semibold text-gray-800 mb-4">Ajukan Pencairan</h2>

        <?php if (!$bank || $bank['status'] !== 'verified'): ?>
            <p class="text-sm text-red-600">Rekening bank belum diverifikasi. Hubungi superadmin.</p>
        <?php else: ?>
            <div class="mb-4 p-3 rounded-xl bg-gray-50 text-sm">
                <p class="text-gray-500">Rekening tujuan</p>
                <p class="font-medium"><?= Security::e($bank['bank_nama']) ?> — <?= Security::e($bank['rekening_nomor']) ?></p>
                <p class="text-gray-700">a/n <?= Security::e($bank['rekening_pemilik']) ?></p>
            </div>

            <form method="post" action="/admin/pencairan/request" class="space-y-3">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

                <div>
                    <label class="text-sm text-gray-600">Jumlah (min Rp50.000)</label>
                    <input type="number" name="amount" min="50000" step="1000"
                           max="<?= (int) $summary['available'] ?>"
                           class="w-full border rounded-xl px-3 py-2 mt-1" required>
                    <p class="text-xs text-gray-400 mt-1">
                        Maksimal: <?= $fmtRupiah($summary['available']) ?>
                    </p>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Catatan (opsional)</label>
                    <textarea name="note" rows="2" class="w-full border rounded-xl px-3 py-2 mt-1"></textarea>
                </div>

                <button class="bg-brand-600 hover:bg-brand-700 text-white px-5 py-2 rounded-xl font-medium"
                        <?= $summary['available'] < 50000 ? 'disabled' : '' ?>>
                    Ajukan Pencairan
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Riwayat -->
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="p-5 border-b">
            <h2 class="font-semibold text-gray-800">Riwayat Pencairan</h2>
        </div>

        <?php if (empty($items)): ?>
            <p class="p-6 text-sm text-gray-500 text-center">Belum ada pengajuan.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Tanggal</th>
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
                                <td class="p-3 text-right font-semibold"><?= $fmtRupiah($w['amount']) ?></td>
                                <td class="p-3 text-xs">
                                    <?= Security::e($w['bank_nama']) ?><br>
                                    <?= Security::e($w['rekening_nomor']) ?>
                                </td>
                                <td class="p-3">
                                    <?= $statusBadge($w['status']) ?>
                                    <?php if ($w['status'] === 'rejected' && $w['reject_reason']): ?>
                                        <p class="text-xs text-gray-500 mt-1"><?= Security::e($w['reject_reason']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <?php if ($w['status'] === 'pending'): ?>
                                        <form method="post" action="/admin/pencairan/cancel/<?= $w['id'] ?>"
                                              onsubmit="return confirm('Batalkan pengajuan ini?')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <button class="text-xs text-red-600 hover:underline">Batalkan</button>
                                        </form>
                                    <?php elseif ($w['status'] === 'paid' && $w['payment_proof']): ?>
                                        <a href="/uploads/pencairan/<?= Security::e($w['payment_proof']) ?>"
                                           target="_blank" class="text-xs text-brand-600 hover:underline">Bukti</a>
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
require __DIR__ . '/../layouts/admin.php';