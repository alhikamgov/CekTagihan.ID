<?php
use App\Helpers\Security;

$val = fn(string $key) => Security::e($old[$key] ?? $tenant[$key] ?? '');
$valAdmin = fn(string $key) => Security::e($old['admin_' . $key] ?? $admin[$key] ?? '');

ob_start();
?>

<div class="max-w-2xl mx-auto">

    <!-- Header Info -->
    <div class="bg-gradient-to-br from-brand-500 to-teal-600 rounded-3xl shadow-xl shadow-brand-200/50 p-6 text-white mb-6">
    <div class="flex items-center gap-4 mb-4">
        <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center flex-shrink-0">
            <span class="text-3xl font-bold"><?= strtoupper(substr($tenant['nama'], 0, 1)) ?></span>
        </div>
        <div class="min-w-0">
            <h2 class="text-xl font-bold truncate"><?= Security::e($tenant['nama']) ?></h2>
            <p class="text-sm text-white/80 truncate">
                <span class="font-mono"><?= Security::e($tenant['slug']) ?></span>
            </p>
        </div>
    </div>

    <!-- URL Portal Tenant -->
    <div class="bg-white/10 backdrop-blur rounded-xl p-3">
        <p class="text-xs text-white/70 mb-1.5">URL Portal Anda (bagikan ke pelanggan):</p>
        <div class="flex items-center gap-2">
            <code id="portal-url" class="flex-1 text-xs font-mono text-white bg-black/20 rounded-lg px-3 py-2 break-all">
                <?= Security::e(($_ENV['APP_URL'] ?? 'http://cektagihan.test')) ?>/portal/<?= Security::e($tenant['slug']) ?>
            </code>
            <button type="button" onclick="copyPortalUrl(this)"
                    class="flex-shrink-0 bg-white/20 hover:bg-white/30 text-white rounded-lg p-2 transition"
                    title="Copy URL">
                <i data-lucide="copy" class="w-4 h-4"></i>
            </button>
        </div>
        <p class="text-xs text-white/60 mt-1.5">
            Pelanggan bisa langsung buka link ini untuk cek tagihan tanpa pilih lokasi.
        </p>
    </div>
</div>

<script>
function copyPortalUrl(btn) {
    const el = document.getElementById('portal-url');
    if (!el) return;

    const url = el.textContent.trim();

    // Coba pakai Clipboard API dulu (butuh HTTPS/localhost)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url)
            .then(() => showCopied(btn))
            .catch(() => fallbackCopy(url, btn));
    } else {
        // Fallback untuk HTTP atau browser lama
        fallbackCopy(url, btn);
    }
}

function fallbackCopy(text, btn) {
    // Buat textarea sementara
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.top = '0';
    textarea.style.left = '0';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';
    document.body.appendChild(textarea);

    // Select & copy
    textarea.focus();
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);

    let success = false;
    try {
        success = document.execCommand('copy');
    } catch (e) {
        success = false;
    }

    document.body.removeChild(textarea);

    if (success) {
        showCopied(btn);
    } else {
        // Kalau masih gagal, tampilkan prompt manual
        showManualCopy(text);
    }
}

function showCopied(btn) {
    const original = btn.innerHTML;
    btn.innerHTML = '<span class="text-xs font-medium">✓ Tersalin</span>';
    btn.classList.add('bg-brand-400');

    setTimeout(() => {
        btn.innerHTML = original;
        btn.classList.remove('bg-brand-400');
        if (window.lucide) lucide.createIcons();
    }, 1500);
}

function showManualCopy(text) {
    // Munculkan prompt agar user bisa copy manual
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <h3 class="text-lg font-bold mb-2">Copy Manual</h3>
            <p class="text-sm text-gray-500 mb-4">Browser Anda tidak mengizinkan copy otomatis. Silakan copy manual di bawah ini:</p>
            <textarea readonly class="w-full px-3 py-2 border border-gray-200 rounded-lg font-mono text-xs bg-gray-50" rows="3" onclick="this.select()">${text}</textarea>
            <div class="flex gap-2 mt-4">
                <button type="button" onclick="this.closest('.fixed').remove()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 rounded-xl">
                    Tutup
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    // Auto-select textarea
    const textarea = modal.querySelector('textarea');
    textarea.focus();
    textarea.select();
}
</script>

    <!-- Form -->
    <form method="POST" action="/admin/profil/update" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

        <!-- Section 1: Info Tenant -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="mb-5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-brand-700">1</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Informasi Tenant</h3>
                    <p class="text-xs text-gray-500">Data lokasi/pengelola</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Nama Tenant <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama" value="<?= $val('nama') ?>" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none"
                           placeholder="Contoh: Ruko Mekar Jaya">
                    <?php if (!empty($errors['nama'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['nama']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Slug (URL Portal)</label>
                    <input type="text" value="<?= Security::e($tenant['slug']) ?>" disabled
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 font-mono text-sm cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Slug tidak bisa diubah.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Alamat</label>
                    <textarea name="alamat" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                              placeholder="Alamat lengkap"><?= $val('alamat') ?></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Telepon</label>
                        <input type="text" name="telepon" value="<?= $val('telepon') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="021xxxxxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email Tenant</label>
                        <input type="email" name="email" value="<?= $val('email') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="admin@ruko.com">
                        <?php if (!empty($errors['email'])): ?>
                            <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Nomor WhatsApp Tenant</label>
                    <input type="text" name="whatsapp" value="<?= $val('whatsapp') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="0812xxxxxxx">
                    <p class="text-xs text-gray-500 mt-1">Untuk terima notifikasi pembayaran.</p>
                </div>
            </div>
        </div>

        <!-- Section 2: Akun Admin -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="mb-5 flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                    <span class="text-sm font-bold text-teal-700">2</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Akun Admin Anda</h3>
                    <p class="text-xs text-gray-500">Ubah nama & email login Anda</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Nama Admin <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="admin_nama" value="<?= $valAdmin('nama') ?>" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="Contoh: Budi Santoso">
                    <?php if (!empty($errors['admin_nama'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['admin_nama']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Email Admin <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="admin_email" value="<?= $valAdmin('email') ?>" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="admin@rukomekar.com">
                    <p class="text-xs text-gray-500 mt-1">Email ini untuk login Anda.</p>
                    <?php if (!empty($errors['admin_email'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['admin_email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tombol -->
        <div class="flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-brand transition">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Perubahan
            </button>
            <a href="/dashboard"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-xl transition">
                Batal
            </a>
        </div>
    </form>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';