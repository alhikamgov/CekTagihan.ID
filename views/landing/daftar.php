<?php
use App\Helpers\Security;

$val = fn(string $key) => Security::e($old[$key] ?? '');

ob_start();
?>

<div class="bg-gray-50 py-16 md:py-20">
    <div class="max-w-2xl mx-auto px-4">

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 shadow-lg shadow-brand-200 mb-4">
                <i data-lucide="building-2" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Daftar Jadi Pengelola</h1>
            <p class="text-gray-600 max-w-md mx-auto">
                Isi formulir di bawah. Tim kami akan menghubungi Anda dalam 1×24 jam.
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-3xl shadow-xl p-6 md:p-8 border border-gray-100">

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-start gap-2">
                    <span class="text-red-500 flex-shrink-0">⚠</span>
                    <span><?= Security::e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="/daftar" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

                <!-- Honeypot -->
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <label>Website URL</label>
                    <input type="text" name="website_url" tabindex="-1" autocomplete="off">
                </div>

                <!-- Section 1: Info Anda -->
                <div>
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">1</span>
                        Informasi Anda
                    </h3>
                    <div class="space-y-4 pl-8">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_pic" value="<?= $val('nama_pic') ?>" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                   placeholder="Contoh: Budi Santoso">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="<?= $val('email') ?>" required
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                       placeholder="nama@email.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" name="telepon" value="<?= $val('telepon') ?>" required
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                       placeholder="0812xxxxxxx">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Info Bisnis -->
                <div class="pt-5 border-t border-gray-100">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold">2</span>
                        Informasi Bisnis
                    </h3>
                    <div class="space-y-4 pl-8">
                        <div>
                            <label class="block text-sm font-medium mb-1">Nama Bisnis / Lokasi <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_bisnis" value="<?= $val('nama_bisnis') ?>" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                   placeholder="Contoh: Ruko Mekar Jaya">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Jenis Bisnis <span class="text-red-500">*</span></label>
                            <select name="jenis_bisnis" required
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                                <option value="">-- Pilih Jenis --</option>
                                <option value="ruko" <?= $val('jenis_bisnis') === 'ruko' ? 'selected' : '' ?>>Ruko</option>
                                <option value="apartemen" <?= $val('jenis_bisnis') === 'apartemen' ? 'selected' : '' ?>>Apartemen / Rusun</option>
                                <option value="kontrakan" <?= $val('jenis_bisnis') === 'kontrakan' ? 'selected' : '' ?>>Kontrakan</option>
                                <option value="sekolah" <?= $val('jenis_bisnis') === 'sekolah' ? 'selected' : '' ?>>Sekolah</option>
                                <option value="pesantren" <?= $val('jenis_bisnis') === 'pesantren' ? 'selected' : '' ?>>Pesantren</option>
                                <option value="komunitas" <?= $val('jenis_bisnis') === 'komunitas' ? 'selected' : '' ?>>Komunitas / RT-RW</option>
                                <option value="tempat_ibadah" <?= $val('jenis_bisnis') === 'tempat_ibadah' ? 'selected' : '' ?>>Tempat Ibadah</option>
                                <option value="lain" <?= $val('jenis_bisnis') === 'lain' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Jumlah Pelanggan / Unit</label>
                            <input type="number" name="jumlah_unit" value="<?= $val('jumlah_unit') ?>" min="0"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                   placeholder="Contoh: 50">
                            <p class="text-xs text-gray-500 mt-1">Perkiraan saja.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Kota</label>
                            <input type="text" name="kota" value="<?= $val('kota') ?>"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                   placeholder="Contoh: Bekasi">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Alamat Lengkap</label>
                            <textarea name="alamat" rows="2"
                                      class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                      placeholder="Jl. Contoh No. 1, Kel. Contoh, Kec. Contoh"><?= $val('alamat') ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Catatan Tambahan</label>
                            <textarea name="catatan" rows="3"
                                      class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                                      placeholder="Contoh: Butuh fitur multi-tagihan, ada 3 lokasi"><?= $val('catatan') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex gap-2">
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-brand-200 hover:shadow-xl transition-all">
                        Kirim Pendaftaran
                    </button>
                    <a href="/"
                       class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition">
                        Batal
                    </a>
                </div>

                <p class="text-xs text-gray-500 text-center">
                    Dengan mendaftar, Anda menyetujui
                    <a href="/syarat-ketentuan" class="text-brand-600 hover:underline">Syarat & Ketentuan</a> dan
                    <a href="/kebijakan-privasi" class="text-brand-600 hover:underline">Kebijakan Privasi</a> kami.
                </p>
            </form>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';