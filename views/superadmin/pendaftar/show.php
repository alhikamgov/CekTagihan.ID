<?php
use App\Helpers\Security;

$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

$jenisLabel = fn(string $jenis) => match ($jenis) {
    'ruko' => 'Ruko', 'apartemen' => 'Apartemen', 'kontrakan' => 'Kontrakan',
    'sekolah' => 'Sekolah', 'pesantren' => 'Pesantren', 'komunitas' => 'Komunitas',
    'tempat_ibadah' => 'Tempat Ibadah', 'lain' => 'Lainnya', default => $jenis,
};

$isPending = $item['status'] === 'pending';

ob_start();
?>

<div class="max-w-3xl mx-auto">

    <div class="mb-4">
        <a href="/superadmin/pendaftar" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-4">
        <div class="flex items-start justify-between gap-3 mb-6 flex-wrap">
            <div>
                <h2 class="text-xl font-bold text-gray-800"><?= Security::e($item['nama_bisnis']) ?></h2>
                <p class="text-sm text-gray-500 mt-0.5">Didaftarkan: <?= $fmtTanggal($item['created_at']) ?></p>
            </div>
            <span class="px-3 py-1 text-xs rounded-full font-medium
                <?= $item['status'] === 'pending' ? 'bg-amber-100 text-amber-800' : '' ?>
                <?= $item['status'] === 'approved' ? 'bg-brand-100 text-brand-700' : '' ?>
                <?= $item['status'] === 'rejected' ? 'bg-red-100 text-red-700' : '' ?>">
                <?= ucfirst($item['status']) ?>
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Nama PIC</p>
                <p class="font-medium"><?= Security::e($item['nama_pic']) ?></p>
            </div>
            <div>
                <p class="text-gray-500">Jenis Bisnis</p>
                <p class="font-medium"><?= Security::e($jenisLabel($item['jenis_bisnis'])) ?></p>
            </div>
            <div>
                <p class="text-gray-500">Email</p>
                <p class="font-medium"><?= Security::e($item['email']) ?></p>
            </div>
            <div>
                <p class="text-gray-500">WhatsApp</p>
                <p class="font-mono font-medium"><?= Security::e($item['telepon']) ?></p>
            </div>
            <div>
                <p class="text-gray-500">Kota</p>
                <p class="font-medium"><?= Security::e($item['kota'] ?: '-') ?></p>
            </div>
            <div>
                <p class="text-gray-500">Jumlah Pelanggan/Unit</p>
                <p class="font-medium"><?= (int) $item['jumlah_unit'] ?></p>
            </div>
            <div class="md:col-span-2">
                <p class="text-gray-500">Alamat</p>
                <p class="font-medium"><?= nl2br(Security::e($item['alamat'] ?: '-')) ?></p>
            </div>
            <?php if (!empty($item['catatan'])): ?>
                <div class="md:col-span-2">
                    <p class="text-gray-500">Catatan</p>
                    <p class="text-gray-700"><?= nl2br(Security::e($item['catatan'])) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($item['status'] === 'rejected' && !empty($item['reject_reason'])): ?>
                <div class="md:col-span-2 p-3 bg-red-50 border border-red-100 rounded-lg">
                    <p class="text-xs font-semibold text-red-800 mb-1">Alasan Ditolak:</p>
                    <p class="text-sm text-red-700"><?= nl2br(Security::e($item['reject_reason'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isPending): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Approve -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-brand-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Setujui</h3>
                        <p class="text-xs text-gray-500">Buat tenant + admin otomatis</p>
                    </div>
                </div>
                <form method="POST" action="/superadmin/pendaftar/approve/<?= (int) $item['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">
                    <button type="submit"
                            onclick="return confirm('Approve pendaftar ini? Tenant + admin akan dibuat otomatis.')"
                            class="w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold py-2.5 rounded-xl shadow-brand transition">
                        Approve & Buat Tenant
                    </button>
                </form>
            </div>

            <!-- Reject -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-red-100">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800">Tolak</h3>
                        <p class="text-xs text-gray-500">Dengan alasan</p>
                    </div>
                </div>
                <form method="POST" action="/superadmin/pendaftar/reject/<?= (int) $item['id'] ?>" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">
                    <textarea name="reject_reason" rows="2" required
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 outline-none text-sm"
                              placeholder="Alasan penolakan..."></textarea>
                    <button type="submit"
                            onclick="return confirm('Tolak pendaftar ini?')"
                            class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
                        Tolak Pendaftar
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';