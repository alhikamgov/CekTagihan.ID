<?php
use App\Helpers\Security;

ob_start();
?>

<div class="max-w-lg mx-auto">

    <div class="mb-4">
        <a href="/dashboard" class="text-sm text-brand-600 hover:underline">← Kembali ke dashboard</a>
    </div>

    <div class="bg-white rounded-2xl shadow-soft p-6 border border-gray-100">

        <div class="flex items-start gap-3 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-brand-100 flex items-center justify-center flex-shrink-0">
                <i data-lucide="shield-check" class="w-6 h-6 text-brand-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold">Ganti Password</h3>
                <p class="text-sm text-gray-500 mt-0.5">Amankan akun Anda dengan password baru.</p>
            </div>
        </div>

        <form method="POST" action="/account/change-password" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-medium mb-1">Password Lama</label>
                <div class="relative">
                    <input type="password" name="current_password" id="current_password" required
                           class="w-full px-3 py-2 pr-10 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="Masukkan password saat ini">
                    <button type="button" onclick="togglePwd('current_password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-600">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
                <?php if (!empty($errors['current_password'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['current_password']) ?></p>
                <?php endif; ?>
            </div>

            <div class="pt-3 border-t border-gray-100">
                <label class="block text-sm font-medium mb-1">Password Baru</label>
                <div class="relative">
                    <input type="password" name="new_password" id="new_password" required
                           class="w-full px-3 py-2 pr-10 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="Min 8 karakter, huruf & angka">
                    <button type="button" onclick="togglePwd('new_password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-600">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
                <?php if (!empty($errors['new_password'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['new_password']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Konfirmasi Password Baru</label>
                <div class="relative">
                    <input type="password" name="confirm_password" id="confirm_password" required
                           class="w-full px-3 py-2 pr-10 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="Ketik ulang password baru">
                    <button type="button" onclick="togglePwd('confirm_password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-600">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
                <?php if (!empty($errors['confirm_password'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['confirm_password']) ?></p>
                <?php endif; ?>
            </div>

            <div class="bg-brand-50 border border-brand-200 rounded-lg p-3 text-xs text-brand-800 flex items-start gap-2">
                <i data-lucide="info" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
                <div>
                    <p class="font-semibold">Syarat password baru:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        <li>Minimal 8 karakter</li>
                        <li>Harus ada huruf dan angka</li>
                    </ul>
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t border-gray-100">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-brand transition">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Simpan Password Baru
                </button>
                <a href="/dashboard"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

<script>
function togglePwd(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';