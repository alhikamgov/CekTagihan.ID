<?php
use App\Helpers\Security;
use App\Helpers\Pagination;

$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

$statusBadge = function (string $status) {
    return match ($status) {
        'pending'  => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-50 text-amber-700 font-medium ring-1 ring-amber-200/50"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Menunggu</span>',
        'approved' => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-700 font-medium ring-1 ring-brand-200/50"><span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>Disetujui</span>',
        'rejected' => '<span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-red-50 text-red-700 font-medium ring-1 ring-red-200/50"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Ditolak</span>',
        default    => '',
    };
};

$jenisLabel = function (string $jenis) {
    return match ($jenis) {
        'ruko' => 'Ruko', 'apartemen' => 'Apartemen', 'kontrakan' => 'Kontrakan',
        'sekolah' => 'Sekolah', 'pesantren' => 'Pesantren', 'komunitas' => 'Komunitas',
        'tempat_ibadah' => 'Tempat Ibadah', 'lain' => 'Lainnya', default => $jenis,
    };
};

ob_start();
?>

<!-- Kredensial Baru -->
<?php if (!empty($_SESSION['new_tenant_credentials'])): ?>
    <?php $cred = $_SESSION['new_tenant_credentials']; ?>
    <div class="mb-6 bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-300 rounded-2xl p-5 shadow-sm">
        <div class="flex items-start gap-3 mb-4">
            <span class="text-2xl">🔑</span>
            <div>
                <h3 class="font-bold text-amber-900">Kredensial Admin Tenant Baru!</h3>
                <p class="text-xs text-amber-700 mt-0.5">Kirim ke tenant sekarang. Password hanya ditampilkan sekali.</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-4 space-y-2 text-sm font-mono">
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Tenant:</span>
                <span class="font-semibold"><?= Security::e($cred['nama_bisnis']) ?></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Nama:</span>
                <span><?= Security::e($cred['nama_pic']) ?></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Email:</span>
                <span><?= Security::e($cred['email']) ?></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Password:</span>
                <span class="font-bold text-amber-900"><?= Security::e($cred['password']) ?></span>
            </div>
            <div class="flex justify-between gap-2">
                <span class="text-gray-500">Portal:</span>
                <span><?= Security::e($_ENV['APP_URL'] ?? '') ?>/portal/<?= Security::e($cred['slug']) ?></span>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="button" onclick="copyCred(this)"
                    data-text="Email: <?= Security::e($cred['email']) ?> | Password: <?= Security::e($cred['password']) ?> | Portal: <?= Security::e($_ENV['APP_URL'] ?? '') ?>/portal/<?= Security::e($cred['slug']) ?>"
                    class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                📋 Copy Kredensial
            </button>
            <a href="/superadmin/pendaftar?clear_cred=1"
               class="bg-white hover:bg-gray-50 border border-amber-300 text-amber-800 text-xs font-semibold px-3 py-2 rounded-lg transition">
                Saya Sudah Kirim
            </a>
        </div>
    </div>
    <script>
    function copyCred(btn) {
        navigator.clipboard.writeText(btn.dataset.text);
        btn.textContent = '✓ Tersalin!';
        setTimeout(() => btn.textContent = '📋 Copy Kredensial', 2000);
    }
    </script>
    <?php unset($_SESSION['new_tenant_credentials']); ?>
<?php endif; ?>

<!-- Header -->
<div class="flex justify-between items-center mb-6 flex-wrap gap-3">
    <div>
        <h3 class="text-lg font-semibold">Pendaftar Tenant</h3>
        <p class="text-sm text-gray-500">
            Total: <?= (int) $total ?> pendaftar
            <?php if ($pendingCount > 0): ?>
                <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 font-semibold">
                    <?= $pendingCount ?> menunggu
                </span>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Filter -->
<div class="flex gap-2 mb-4 flex-wrap">
    <a href="/superadmin/pendaftar?status=pending"
       class="px-4 py-2 rounded-xl text-sm font-medium transition <?= $status === 'pending' ? 'bg-amber-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
        Menunggu
    </a>
    <a href="/superadmin/pendaftar?status=approved"
       class="px-4 py-2 rounded-xl text-sm font-medium transition <?= $status === 'approved' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
        Disetujui
    </a>
    <a href="/superadmin/pendaftar?status=rejected"
       class="px-4 py-2 rounded-xl text-sm font-medium transition <?= $status === 'rejected' ? 'bg-red-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
        Ditolak
    </a>
    <a href="/superadmin/pendaftar?status="
       class="px-4 py-2 rounded-xl text-sm font-medium transition <?= $status === '' ? 'bg-gray-800 text-white' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' ?>">
        Semua
    </a>
</div>

<!-- Tabel -->
<div class="bg-white rounded-2xl shadow-soft overflow-hidden border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-left">
                <tr>
                    <th class="px-4 py-3">Nama Bisnis</th>
                    <th class="px-4 py-3">PIC</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Kontak</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i data-lucide="inbox" class="w-8 h-8 text-gray-400"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">Belum ada pendaftar</p>
                                    <p class="text-sm text-gray-500 mt-0.5">Pendaftar baru akan muncul di sini.</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($items as $r): ?>
                        <tr class="hover:bg-brand-50/40 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-800"><?= Security::e($r['nama_bisnis']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= Security::e($r['nama_pic']) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= Security::e($jenisLabel($r['jenis_bisnis'])) ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <?= Security::e($r['email']) ?><br>
                                <span class="font-mono"><?= Security::e($r['telepon']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-center"><?= $statusBadge($r['status']) ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap"><?= $fmtTanggal($r['created_at']) ?></td>
                            <td class="px-4 py-3 text-right">
                                <a href="/superadmin/pendaftar/show/<?= (int) $r['id'] ?>"
                                   class="text-brand-600 hover:text-brand-800 font-medium">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php echo Pagination::render($page, $lastPage, '/superadmin/pendaftar', ['status' => $status]); ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';