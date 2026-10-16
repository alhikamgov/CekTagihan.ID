<?php
use App\Helpers\Security;

$val = fn(string $key) => Security::e($old[$key] ?? $settings[$key] ?? '');

ob_start();
?>

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h3 class="text-lg font-semibold">Pengaturan Platform</h3>
        <p class="text-sm text-gray-500 mt-0.5">
            Informasi ini akan tampil di landing page, halaman legal, dan halaman lainnya.
        </p>
    </div>

    <form method="POST" action="/superadmin/pengaturan/save" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

        <!-- Kontak -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="mb-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                    <i data-lucide="phone" class="w-5 h-5 text-brand-600"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-gray-800">Informasi Kontak</h4>
                    <p class="text-xs text-gray-500">Tampil di footer landing page & halaman legal</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email Support</label>
                    <input type="email" name="contact_email_support" value="<?= $val('contact_email_support') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="support@cektagihan.id">
                    <p class="text-xs text-gray-500 mt-1">Ditampilkan untuk bantuan umum & keluhan.</p>
                    <?php if (!empty($errors['contact_email_support'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['contact_email_support']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email Privacy</label>
                    <input type="email" name="contact_email_privacy" value="<?= $val('contact_email_privacy') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="privacy@cektagihan.id">
                    <p class="text-xs text-gray-500 mt-1">Ditampilkan di Kebijakan Privasi.</p>
                    <?php if (!empty($errors['contact_email_privacy'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['contact_email_privacy']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor WhatsApp</label>
                        <input type="text" name="contact_whatsapp" value="<?= $val('contact_whatsapp') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="6281234567890">
                        <p class="text-xs text-gray-500 mt-1">Format: 628xxx (otomatis dinormalisasi).</p>
                        <?php if (!empty($errors['contact_whatsapp'])): ?>
                            <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['contact_whatsapp']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Telepon Kantor</label>
                        <input type="text" name="contact_phone" value="<?= $val('contact_phone') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="021-1234567">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Alamat Kantor</label>
                    <textarea name="contact_address" rows="2"
                              class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                              placeholder="Jl. Contoh No. 1, Jakarta"><?= $val('contact_address') ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Jam Operasional</label>
                    <input type="text" name="contact_hours" value="<?= $val('contact_hours') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="Senin–Jumat, 09.00–17.00 WIB">
                </div>
            </div>
        </div>

        <!-- Sosial Media -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="mb-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                    <i data-lucide="share-2" class="w-5 h-5 text-teal-600"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-gray-800">Sosial Media</h4>
                    <p class="text-xs text-gray-500">Tampil di footer landing page. Kosongkan kalau belum ada.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Instagram</label>
                    <input type="text" name="social_instagram" value="<?= $val('social_instagram') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="https://instagram.com/cektagihan">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Facebook</label>
                    <input type="text" name="social_facebook" value="<?= $val('social_facebook') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="https://facebook.com/cektagihan">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Twitter / X</label>
                    <input type="text" name="social_twitter" value="<?= $val('social_twitter') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="https://twitter.com/cektagihan">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">LinkedIn</label>
                    <input type="text" name="social_linkedin" value="<?= $val('social_linkedin') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="https://linkedin.com/company/cektagihan">
                </div>
            </div>
        </div>

        <!-- Informasi Legal -->
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
            <div class="mb-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                    <i data-lucide="file-badge" class="w-5 h-5 text-amber-600"></i>
                </div>
                <div>
                    <h4 class="text-base font-bold text-gray-800">Informasi Legal</h4>
                    <p class="text-xs text-gray-500">Tampil di Privacy Policy, ToS, dan footer</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Badan Hukum</label>
                    <input type="text" name="legal_company_name" value="<?= $val('legal_company_name') ?>"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="PT CekTagihan Indonesia">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">NIB</label>
                        <input type="text" name="legal_nib" value="<?= $val('legal_nib') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="1234567890123">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan kalau belum ada.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">NPWP</label>
                        <input type="text" name="legal_npwp" value="<?= $val('legal_npwp') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="01.234.567.8-901.000">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Nomor PSE Kominfo</label>
                        <input type="text" name="legal_pse" value="<?= $val('legal_pse') ?>"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                               placeholder="0000000">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol -->
        <div class="flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-brand transition">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';