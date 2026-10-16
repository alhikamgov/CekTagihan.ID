<?php
use App\Helpers\Security;

$val = fn(string $key) => Security::e($old[$key] ?? $rekening[$key] ?? '');

$banks = [
    'BCA'      => 'Bank Central Asia (BCA)',
    'MANDIRI'  => 'Bank Mandiri',
    'BNI'      => 'Bank Negara Indonesia (BNI)',
    'BRI'      => 'Bank Rakyat Indonesia (BRI)',
    'PERMATA'  => 'Bank Permata',
    'CIMB'     => 'CIMB Niaga',
    'BSI'      => 'Bank Syariah Indonesia (BSI)',
    'DANAMON'  => 'Bank Danamon',
    'MAYBANK'  => 'Maybank Indonesia',
    'PANIN'    => 'Bank Panin',
];

$isVerified = $rekening && $rekening['status'] === 'verified';
$isPending  = $rekening && $rekening['status'] === 'pending';
$isRejected = $rekening && $rekening['status'] === 'rejected';

ob_start();
?>

<div class="max-w-2xl mx-auto">

    <!-- Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="info" class="w-5 h-5 text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-blue-900 mb-1">Rekening Penerima Dana</h3>
                <p class="text-sm text-blue-800 leading-relaxed">
                    Dana pembayaran online dari pelanggan akan otomatis dikirim ke rekening ini, Pastikan data rekening benar.
                </p>
            </div>
        </div>
    </div>

    <!-- Status Badge -->
    <?php if ($isVerified): ?>
        <div class="bg-brand-50 border border-brand-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-brand-600"></i>
            </div>
            <div>
                <p class="font-semibold text-brand-900 text-sm">Rekening Terverifikasi</p>
                <p class="text-xs text-brand-700 mt-0.5">
                    Dana pembayaran online akan masuk ke rekening ini.
                </p>
            </div>
        </div>
    <?php elseif ($isPending): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
            </div>
            <div>
                <p class="font-semibold text-amber-900 text-sm">Menunggu Verifikasi</p>
                <p class="text-xs text-amber-700 mt-0.5">
                    Admin platform akan memverifikasi rekening Anda dalam 1×24 jam.
                </p>
            </div>
        </div>
    <?php elseif ($isRejected): ?>
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
            </div>
            <div>
                <p class="font-semibold text-red-900 text-sm">Rekening Ditolak</p>
                <p class="text-xs text-red-700 mt-0.5">
                    Silakan perbaiki data rekening dan simpan ulang.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h3 class="text-lg font-bold mb-6">Data Rekening Bank</h3>

        <form method="POST" action="/admin/rekening/save" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">
                    Bank <span class="text-red-500">*</span>
                </label>
                <select name="bank_kode" id="bank_kode" required <?= $isVerified ? 'disabled' : '' ?>
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none <?= $isVerified ? 'bg-gray-50 cursor-not-allowed' : '' ?>">
                    <option value="">-- Pilih Bank --</option>
                    <?php foreach ($banks as $kode => $nama): ?>
                        <option value="<?= $kode ?>" <?= $val('bank_kode') === $kode ? 'selected' : '' ?>>
                            <?= Security::e($nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($isVerified): ?>
                    <input type="hidden" name="bank_kode" value="<?= $val('bank_kode') ?>">
                <?php endif; ?>
                <?php if (!empty($errors['bank_kode'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['bank_kode']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Nama Bank <span class="text-red-500">*</span>
                </label>
                <input type="text" name="bank_nama" id="bank_nama" value="<?= $val('bank_nama') ?>" required
                       <?= $isVerified ? 'disabled' : '' ?>
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none <?= $isVerified ? 'bg-gray-50 cursor-not-allowed' : '' ?>"
                       placeholder="Contoh: BCA">
                <?php if ($isVerified): ?>
                    <input type="hidden" name="bank_nama" value="<?= $val('bank_nama') ?>">
                <?php endif; ?>
                <?php if (!empty($errors['bank_nama'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['bank_nama']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Nomor Rekening <span class="text-red-500">*</span>
                </label>
                <input type="text" name="rekening_nomor" value="<?= $val('rekening_nomor') ?>" required
                       <?= $isVerified ? 'disabled' : '' ?>
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none font-mono <?= $isVerified ? 'bg-gray-50 cursor-not-allowed' : '' ?>"
                       placeholder="1234567890">
                <?php if ($isVerified): ?>
                    <input type="hidden" name="rekening_nomor" value="<?= $val('rekening_nomor') ?>">
                <?php endif; ?>
                <?php if (!empty($errors['rekening_nomor'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['rekening_nomor']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Nama Pemilik Rekening <span class="text-red-500">*</span>
                </label>
                <input type="text" name="rekening_pemilik" value="<?= $val('rekening_pemilik') ?>" required
                       <?= $isVerified ? 'disabled' : '' ?>
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none <?= $isVerified ? 'bg-gray-50 cursor-not-allowed' : '' ?>"
                       placeholder="Sesuai buku tabungan">
                <?php if ($isVerified): ?>
                    <input type="hidden" name="rekening_pemilik" value="<?= $val('rekening_pemilik') ?>">
                <?php endif; ?>
                <p class="text-xs text-gray-500 mt-1">Pastikan sesuai dengan nama di buku tabungan.</p>
                <?php if (!empty($errors['rekening_pemilik'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['rekening_pemilik']) ?></p>
                <?php endif; ?>
            </div>

            <div class="flex gap-2 pt-4 border-t border-gray-100">
                <?php if (!$isVerified): ?>
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-brand transition">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <?= $rekening ? 'Update Rekening' : 'Simpan Rekening' ?>
                    </button>
                <?php else: ?>
                    <div class="text-sm text-gray-500">
                        <i data-lucide="lock" class="w-4 h-4 inline"></i>
                        Rekening sudah diverifikasi. Hubungi admin untuk mengubah.
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

</div>

<script>
(function() {
    const bankKode = document.getElementById('bank_kode');
    const bankNama = document.getElementById('bank_nama');

    if (bankKode && bankNama) {
        bankKode.addEventListener('change', function() {
            const map = {
                'BCA': 'BCA', 'MANDIRI': 'Mandiri', 'BNI': 'BNI', 'BRI': 'BRI',
                'PERMATA': 'Permata', 'CIMB': 'CIMB Niaga', 'BSI': 'BSI',
                'DANAMON': 'Danamon', 'MAYBANK': 'Maybank', 'PANIN': 'Panin'
            };
            bankNama.value = map[this.value] || '';
        });
    }
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';