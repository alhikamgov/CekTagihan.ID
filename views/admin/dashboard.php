<?php
use App\Helpers\Security;

$userName = $_SESSION['user_name'] ?? 'Guest';
$userRole = $_SESSION['user_role'] ?? '-';

$fmtRupiah = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y', strtotime($d)) : '-';

// Data pendapatan sewa (dari controller)
$rev = $subscriptionRevenue ?? [
    'revenue_this_month' => 0,
    'revenue_last_month' => 0,
    'revenue_all_time'   => 0,
    'growth_percent'     => 0,
    'active_tenants'     => 0,
    'expiring_soon'      => 0,
    'expired_tenants'    => 0,
];
$growthUp = $rev['growth_percent'] >= 0;

ob_start();
?>

<!-- Greeting -->
<div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-3xl shadow-xl shadow-brand-200/50 p-6 md:p-8 text-white mb-6 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
    <div class="relative">
        <h3 class="text-2xl md:text-3xl font-bold mb-1">Assalamualaikum, <?= Security::e($userName) ?> 👋</h3>
        <p class="text-white/90 text-sm">
            Anda login sebagai <span class="font-semibold"><?= Security::e($userRole) ?></span>. Selamat bekerja hari ini.
        </p>
    </div>
</div>

<?php if ($userRole === 'admin'): ?>

    <!-- ============================================= -->
    <!-- DASHBOARD ADMIN TENANT                         -->
    <!-- ============================================= -->

    <!-- Quick Action -->
    <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="zap" class="w-4 h-4 text-brand-600"></i>
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Aksi Cepat</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <a href="/admin/invoices/create"
               class="flex items-center gap-2 px-3 py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-sm font-medium rounded-xl transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Buat Tagihan
            </a>
            <a href="/admin/invoices/generate"
               class="flex items-center gap-2 px-3 py-2.5 bg-teal-50 hover:bg-teal-100 text-teal-700 text-sm font-medium rounded-xl transition">
                <i data-lucide="zap" class="w-4 h-4"></i>
                Buat Banyak Tagihan
            </a>
            <a href="/admin/manual-payments/create"
               class="flex items-center gap-2 px-3 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-sm font-medium rounded-xl transition">
                <i data-lucide="hand-coins" class="w-4 h-4"></i>
                Pembayaran Manual / Cicil
            </a>
            <a href="/admin/pencairan"
               class="flex items-center gap-2 px-3 py-2.5 bg-green-50 hover:bg-green-100 text-green-700 text-sm font-medium rounded-xl transition">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                Pencairan Dana
            </a>
        </div>
    </div>

    <!-- 4 Metrik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <a href="/admin/payers" class="block bg-white rounded-3xl shadow-sm p-5 border border-brand-100 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-brand-600"></i>
                </div>
                <p class="text-sm text-gray-500">Pelanggan</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?= (int) ($summary['total_payer'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1">Aktif</p>
        </a>

        <a href="/admin/invoices" class="block bg-white rounded-3xl shadow-sm p-5 border border-teal-100 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-teal-600"></i>
                </div>
                <p class="text-sm text-gray-500">Tagihan Bulan Ini</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?= (int) ($summary['total_invoice_bulan_ini'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= date('F Y') ?></p>
        </a>

        <div class="bg-white rounded-3xl shadow-sm p-5 border border-green-100">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600"></i>
                </div>
                <p class="text-sm text-gray-500">Terbayar Bulan Ini</p>
            </div>
            <p class="text-2xl font-bold text-green-600">
                <?= $fmtRupiah($summary['total_paid_bulan_ini'] ?? 0) ?>
            </p>
            <p class="text-xs text-gray-500 mt-1">Total settled</p>
        </div>

        <a href="/admin/tunggakan" class="block bg-white rounded-3xl shadow-sm p-5 border border-red-100 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600"></i>
                </div>
                <p class="text-sm text-gray-500">Tunggakan</p>
            </div>
            <p class="text-2xl font-bold text-red-600">
                <?= $fmtRupiah($summary['total_overdue'] ?? 0) ?>
            </p>
            <p class="text-xs text-gray-500 mt-1">Lewat jatuh tempo</p>
        </a>
    </div>

    <!-- Detail Transaksi -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 mt-6">
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-brand-600"></i>
                <h3 class="font-semibold text-gray-700">Transaksi Tebaru</h3>
            </div>
            <span class="text-xs text-gray-500"><?= (int) $trxTotal ?> transaksi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 text-left">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Nomor Pelanggan</th>
                        <th class="px-4 py-3">Nama Pelanggan</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Periode</th>
                        <th class="px-4 py-3">Tanggal Bayar</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($transaksiList)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                        <i data-lucide="file-x" class="w-8 h-8 text-gray-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-700">Belum ada transaksi</p>
                                        <p class="text-sm text-gray-500 mt-0.5">Belum ada pembayaran yang tercatat.</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transaksiList as $i => $r): ?>
                            <tr class="hover:bg-brand-50/40 transition-colors">
                                <td class="px-4 py-3 text-gray-500"><?= (($trxPage - 1) * 10) + $i + 1 ?></td>
                                <td class="px-4 py-3 font-mono text-gray-600"><?= Security::e($r['nomor_induk']) ?></td>
                                <td class="px-4 py-3 font-medium text-gray-800"><?= Security::e($r['payer_nama']) ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= Security::e($r['jenis']) ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= Security::e($r['periode']) ?></td>
                                <td class="px-4 py-3 text-gray-600"><?= $fmtTanggal($r['paid_at']) ?></td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800"><?= $fmtRupiah($r['jumlah']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($trxLastPage > 1): ?>
            <div class="flex items-center justify-between px-4 py-3 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    Menampilkan <?= (($trxPage - 1) * 10) + 1 ?>–<?= min($trxPage * 10, $trxTotal) ?> dari <?= $trxTotal ?> transaksi
                </p>
                <div class="flex gap-1">
                    <?php if ($trxPage > 1): ?>
                        <a href="?page=<?= $trxPage - 1 ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">←</a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $trxPage - 2); $i <= min($trxLastPage, $trxPage + 2); $i++): ?>
                        <a href="?page=<?= $i ?>"
                           class="px-3 py-1.5 text-sm border rounded-lg <?= $i === $trxPage ? 'bg-brand-500 text-white border-brand-500' : 'border-gray-200 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($trxPage < $trxLastPage): ?>
                        <a href="?page=<?= $trxPage + 1 ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">→</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php elseif ($userRole === 'superadmin'): ?>

    <!-- ============================================= -->
    <!-- DASHBOARD SUPERADMIN                           -->
    <!-- ============================================= -->

    <!-- ============ PENGHASILAN PLATFORM (SEWA) ============ -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-teal-600 flex items-center justify-center">
                <i data-lucide="wallet" class="w-4 h-4 text-white"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-800">Penghasilan Platform</h2>
            <span class="text-xs text-gray-400">dari sewa langganan</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <!-- Card utama: bulan ini -->
            <div class="bg-gradient-to-br from-brand-500 via-brand-600 to-teal-600 text-white rounded-2xl p-6 shadow-lg shadow-brand-200 md:col-span-2 relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>

                <div class="relative">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-xs font-medium text-brand-100 uppercase tracking-wide">
                                Penghasilan Bulan Ini
                            </p>
                            <p class="text-4xl font-extrabold mt-2">
                                <?= $fmtRupiah($rev['revenue_this_month']) ?>
                            </p>
                        </div>
                        <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur">
                            <i data-lucide="trending-up" class="w-7 h-7 text-white"></i>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4 border-t border-white/20">
                        <?php if ($growthUp && $rev['growth_percent'] > 0): ?>
                            <span class="inline-flex items-center gap-1 bg-white/20 px-2.5 py-1 rounded-full text-xs font-medium">
                                <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                +<?= number_format(abs($rev['growth_percent']), 1) ?>%
                            </span>
                        <?php elseif ($rev['growth_percent'] < 0): ?>
                            <span class="inline-flex items-center gap-1 bg-red-400/30 px-2.5 py-1 rounded-full text-xs font-medium">
                                <i data-lucide="arrow-down-right" class="w-3 h-3"></i>
                                <?= number_format($rev['growth_percent'], 1) ?>%
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 bg-white/20 px-2.5 py-1 rounded-full text-xs font-medium">
                                <i data-lucide="minus" class="w-3 h-3"></i>
                                0%
                            </span>
                        <?php endif; ?>
                        <span class="text-xs text-brand-100">
                            vs bulan lalu (<?= $fmtRupiah($rev['revenue_last_month']) ?>)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card: total sepanjang waktu -->
            <div class="bg-white rounded-2xl p-6 shadow border border-gray-100 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center">
                            <i data-lucide="coins" class="w-5 h-5 text-amber-600"></i>
                        </div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">
                            Total Sepanjang Waktu
                        </p>
                    </div>
                    <p class="text-2xl font-bold text-gray-800">
                        <?= $fmtRupiah($rev['revenue_all_time']) ?>
                    </p>
                </div>
                <p class="text-xs text-gray-400 mt-3 pt-3 border-t border-gray-100">
                    Akumulasi sejak platform berjalan
                </p>
            </div>

        </div>

        <!-- Row tenant stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">

            <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="building-2" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= (int) $rev['active_tenants'] ?></p>
                        <p class="text-xs text-gray-500">Tenant berlangganan aktif</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= (int) $rev['expiring_soon'] ?></p>
                        <p class="text-xs text-gray-500">Akan habis ≤7 hari</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 shadow border border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-800"><?= (int) $rev['expired_tenants'] ?></p>
                        <p class="text-xs text-gray-500">Langganan lewat grace</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Alert: tenant expired -->
    <?php if (($rev['expired_tenants'] ?? 0) > 0): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-white"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-red-900 mb-0.5">
                    <?= (int) $rev['expired_tenants'] ?> tenant sudah lewat masa tenggang
                </p>
                <p class="text-sm text-red-700 mb-2">
                    Akses mereka dibatasi sampai perpanjang langganan.
                </p>
                <a href="/superadmin/langganan?status=expired"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-red-800 hover:text-red-900">
                    Lihat tenant
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Alert: tenant expiring soon -->
    <?php if (($rev['expiring_soon'] ?? 0) > 0): ?>
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5 text-white"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-amber-900 mb-0.5">
                    <?= (int) $rev['expiring_soon'] ?> tenant akan habis dalam 7 hari
                </p>
                <p class="text-sm text-amber-700 mb-2">
                    Segera hubungi mereka untuk perpanjang.
                </p>
                <a href="/superadmin/langganan?status=expiring"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-800 hover:text-amber-900">
                    Lihat tenant
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Action -->
    <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 mb-6">
        <div class="flex items-center gap-2 mb-3">
            <i data-lucide="zap" class="w-4 h-4 text-brand-600"></i>
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Aksi Cepat</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <a href="/superadmin/tenants/create"
               class="flex items-center gap-2 px-3 py-2.5 bg-brand-50 hover:bg-brand-100 text-brand-700 text-sm font-medium rounded-xl transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Tambah Tenant
            </a>
            <a href="/superadmin/tenants"
               class="flex items-center gap-2 px-3 py-2.5 bg-teal-50 hover:bg-teal-100 text-teal-700 text-sm font-medium rounded-xl transition">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                Kelola Tenant
            </a>
            <a href="/superadmin/langganan"
               class="flex items-center gap-2 px-3 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-xl transition">
                <i data-lucide="calendar-check" class="w-4 h-4"></i>
                Kelola Langganan
            </a>
            <a href="/superadmin/rekening"
               class="flex items-center gap-2 px-3 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-sm font-medium rounded-xl transition relative">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                Verifikasi Rekening
                <?php if (($summary['rekening_pending'] ?? 0) > 0): ?>
                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center font-bold">
                        <?= (int) $summary['rekening_pending'] ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- 4 Metrik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <a href="/superadmin/tenants" class="block bg-white rounded-3xl shadow-sm p-5 border border-brand-100 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                    <i data-lucide="building-2" class="w-5 h-5 text-brand-600"></i>
                </div>
                <p class="text-sm text-gray-500">Total Tenant</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?= (int) ($summary['total_tenant'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1">
                <?= (int) ($summary['total_tenant_aktif'] ?? 0) ?> aktif, <?= (int) ($summary['total_tenant_suspend'] ?? 0) ?> suspend
            </p>
        </a>

        <div class="bg-white rounded-3xl shadow-sm p-5 border border-teal-100">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-teal-600"></i>
                </div>
                <p class="text-sm text-gray-500">Total Pelanggan</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?= (int) ($summary['total_payer'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1">Semua tenant</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-5 border border-amber-100">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-5 h-5 text-amber-600"></i>
                </div>
                <p class="text-sm text-gray-500">Tagihan Bulan Ini</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?= (int) ($summary['total_invoice_bulan_ini'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1"><?= date('F Y') ?></p>
        </div>

        <a href="/superadmin/rekening" class="block bg-white rounded-3xl shadow-sm p-5 border border-green-100 hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600"></i>
                </div>
                <p class="text-sm text-gray-500">Rekening Verified</p>
            </div>
            <p class="text-3xl font-bold text-gray-800"><?= (int) ($summary['rekening_verified'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1">
                <?= (int) ($summary['rekening_pending'] ?? 0) ?> menunggu verifikasi
            </p>
        </a>
    </div>

    <!-- List Tenant Terbaru -->
    <?php if (!empty($tenantList)): ?>
        <div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
            <div class="px-4 py-3 bg-brand-50 border-b border-brand-100 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-brand-600"></i>
                    <h3 class="font-semibold text-brand-800">Tenant Terbaru</h3>
                </div>
                <a href="/superadmin/tenants" class="text-xs text-brand-700 hover:text-brand-900 font-medium">Lihat Semua →</a>
            </div>
            <div class="divide-y divide-gray-100">
                <?php foreach ($tenantList as $t): ?>
                    <a href="/superadmin/tenants/edit/<?= (int) $t['id'] ?>"
                       class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-brand-50/40 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-400 to-teal-500 flex items-center justify-center text-white font-semibold flex-shrink-0">
                                <?= strtoupper(substr($t['nama'], 0, 1)) ?>
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 truncate"><?= Security::e($t['nama']) ?></p>
                                <p class="text-xs text-gray-500 font-mono truncate"><?= Security::e($t['slug']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <div class="text-right hidden sm:block">
                                <p class="text-xs text-gray-500"><?= (int) $t['total_payers'] ?> pelanggan</p>
                                <p class="text-xs text-gray-500"><?= (int) $t['total_invoices'] ?> tagihan</p>
                            </div>
                            <?php if ($t['status'] === 'active'): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                    Aktif
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-red-50 text-red-700 font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Suspend
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

<?php else: ?>

    <!-- Fallback -->
    <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Selamat Datang</h3>
        <p class="text-sm text-gray-500">Anda login sebagai <?= Security::e($userRole) ?>.</p>
    </div>

<?php endif; ?>

<script>
function toggleTooltip(e, tooltipId) {
    e.stopPropagation();
    const tooltip = document.getElementById(tooltipId);
    if (!tooltip) return;

    const isHidden = tooltip.classList.contains('hidden');

    document.querySelectorAll('[id^="tt-"]').forEach(el => el.classList.add('hidden'));

    if (isHidden) {
        const rect = e.target.closest('button').getBoundingClientRect();
        tooltip.classList.remove('hidden');

        const tooltipRect = tooltip.getBoundingClientRect();
        let top = rect.bottom + 8;
        let left = rect.left - tooltipRect.width + rect.width;

        if (left < 8) left = 8;
        if (left + tooltipRect.width > window.innerWidth - 8) {
            left = window.innerWidth - tooltipRect.width - 8;
        }
        if (top + tooltipRect.height > window.innerHeight - 8) {
            top = rect.top - tooltipRect.height - 8;
        }

        tooltip.style.top = top + 'px';
        tooltip.style.left = left + 'px';

        if (window.lucide) lucide.createIcons();
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="tt-"]') && !e.target.closest('button[onclick^="toggleTooltip"]')) {
        document.querySelectorAll('[id^="tt-"]').forEach(el => el.classList.add('hidden'));
    }
});

if (window.lucide) lucide.createIcons();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';