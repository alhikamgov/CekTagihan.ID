<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

$statusMap = [
    'active'  => ['bg-emerald-100 text-emerald-700', 'Aktif', 'check-circle-2'],
    'grace'   => ['bg-amber-100 text-amber-700', 'Masa Tenggang', 'alert-circle'],
    'expired' => ['bg-red-100 text-red-700', 'Kedaluwarsa', 'x-circle'],
    'none'    => ['bg-gray-100 text-gray-700', 'Belum Berlangganan', 'info'],
];
$sm = $statusMap[$status['status']] ?? $statusMap['none'];

ob_start();
?>

<div class="space-y-6">

    <h1 class="text-2xl font-bold text-gray-800">Langganan Saya</h1>

    <!-- ============ CARD LOCK (muncul saat expired) ============ -->
    <?php if ($status['status'] === 'expired'): ?>
        <div class="bg-gradient-to-br from-red-500 to-rose-600 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-red-200 relative overflow-hidden">
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>

            <div class="relative">
                <div class="flex items-start gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0">
                        <i data-lucide="lock" class="w-7 h-7 text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold mb-1">Akses Dashboard Dibatasi</h2>
                        <p class="text-white/90 text-sm">
                            Langganan Anda sudah berakhir pada
                            <strong><?= $fmtTanggal($status['subscription']['end_date']) ?></strong>.
                            Untuk mengakses fitur penuh, segera perpanjang langganan.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Yang masih bisa -->
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-200"></i>
                            <p class="font-semibold text-sm">Masih Bisa Diakses</p>
                        </div>
                        <ul class="space-y-2 text-sm text-white/90">
                            <li class="flex gap-2">
                                <span class="text-emerald-200">•</span>
                                <span>Portal pembayaran pelanggan tetap berjalan</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-emerald-200">•</span>
                                <span>Lihat laporan & data historis</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-emerald-200">•</span>
                                <span>Ajukan pencairan saldo</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-emerald-200">•</span>
                                <span>Update rekening bank & profil</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Yang tidak bisa -->
                    <div class="bg-white/10 backdrop-blur rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center gap-2 mb-3">
                            <i data-lucide="x-circle" class="w-4 h-4 text-red-200"></i>
                            <p class="font-semibold text-sm">Sementara Tidak Bisa</p>
                        </div>
                        <ul class="space-y-2 text-sm text-white/90">
                            <li class="flex gap-2">
                                <span class="text-red-200">•</span>
                                <span>Buat tagihan baru</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-red-200">•</span>
                                <span>Tambah / edit pelanggan</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-red-200">•</span>
                                <span>Catat pembayaran manual</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-red-200">•</span>
                                <span>Reminder WhatsApp otomatis</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="mailto:alhikam@cektagihan.id?subject=Perpanjang%20Langganan%20-%20<?= urlencode($_SESSION['tenant_name'] ?? '') ?>"
                       class="inline-flex items-center justify-center gap-2 bg-white text-red-700 font-bold px-6 py-3 rounded-xl hover:bg-red-50 transition shadow-lg">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                        Hubungi Kami untuk Perpanjang
                    </a>
                    <a href="https://wa.me/6281234567890?text=Halo,%20saya%20ingin%20perpanjang%20langganan"
                       class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-medium px-6 py-3 rounded-xl border border-white/30 transition">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        WhatsApp
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ============ CARD STATUS ============ -->
    <div class="bg-white rounded-2xl shadow border border-gray-100 p-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <p class="text-xs text-gray-500 mb-1">Status Langganan</p>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium <?= $sm[0] ?>">
                        <?= $sm[1] ?>
                    </span>
                    <?php if ($status['subscription']): ?>
                        <span class="text-xs text-gray-400">
                            Paket: <?= Security::e(ucfirst($status['subscription']['plan_name'])) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($status['subscription']): ?>
                    <p class="text-sm text-gray-700">
                        Berlaku sampai:
                        <strong><?= $fmtTanggal($status['subscription']['end_date']) ?></strong>
                        <?php if ($status['days_left'] !== null): ?>
                            <?php if ($status['days_left'] >= 0): ?>
                                <span class="text-gray-500">(<?= $status['days_left'] ?> hari lagi)</span>
                            <?php else: ?>
                                <span class="text-red-600">(lewat <?= abs($status['days_left']) ?> hari)</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Anda belum memiliki langganan aktif.</p>
                <?php endif; ?>
            </div>

            <?php if ($status['status'] !== 'expired'): ?>
                <a href="mailto:hello@cektagihan.id?subject=Perpanjang%20Langganan"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                    Hubungi Kami untuk Perpanjang
                </a>
            <?php endif; ?>
        </div>

        <?php if ($status['status'] === 'grace'): ?>
            <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 flex items-start gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong>Perhatian:</strong> Langganan Anda sudah berakhir. Anda masih bisa menggunakan
                    sistem penuh dalam masa tenggang.
                    <?php if ($status['days_left'] !== null && $status['days_left'] < 0): ?>
                        Sisa <strong><?= abs($status['days_left']) ?> hari</strong> lagi sebelum akses dibatasi.
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ============ RIWAYAT LANGGANAN ============ -->
    <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
        <div class="p-5 border-b">
            <h2 class="font-semibold text-gray-800">Riwayat Langganan</h2>
        </div>

        <?php if (empty($subscriptions)): ?>
            <p class="p-6 text-sm text-gray-500 text-center">Belum ada riwayat.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-3">Paket</th>
                            <th class="text-left p-3">Periode</th>
                            <th class="text-right p-3">Harga</th>
                            <th class="text-left p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscriptions as $s): ?>
                            <tr class="border-t">
                                <td class="p-3 font-medium"><?= Security::e(ucfirst($s['plan_name'])) ?></td>
                                <td class="p-3 text-xs">
                                    <?= $fmtTanggal($s['start_date']) ?> — <?= $fmtTanggal($s['end_date']) ?>
                                </td>
                                <td class="p-3 text-right"><?= $fmtRupiah($s['price']) ?></td>
                                <td class="p-3">
                                    <?php
                                    $subStatusMap = [
                                        'active'    => 'bg-emerald-100 text-emerald-700',
                                        'expired'   => 'bg-gray-100 text-gray-600',
                                        'cancelled' => 'bg-red-100 text-red-600',
                                    ];
                                    $cls = $subStatusMap[$s['status']] ?? 'bg-gray-100 text-gray-600';
                                    ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs <?= $cls ?>">
                                        <?= ucfirst($s['status']) ?>
                                    </span>
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