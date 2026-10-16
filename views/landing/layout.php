<?php
use App\Helpers\Security;
use App\Helpers\SiteInfo;

$appName = $_ENV['APP_NAME']    ?? 'CekTagihan';
$tagline = $_ENV['APP_TAGLINE'] ?? 'Bayar tagihan dari mana saja!';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CekTagihan — platform pembayaran tagihan sewa, IPL, listrik, dan air untuk pengelola ruko, apartemen, dan kawasan komersial.">
    <title><?= Security::e($title ?? $appName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        brand: {
                            50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',
                            400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',
                            800:'#065f46',900:'#064e3b'
                        }
                    },
                    boxShadow: {
                        'soft': '0 2px 8px -2px rgba(16, 185, 129, 0.08), 0 4px 16px -4px rgba(0, 0, 0, 0.04)',
                        'brand': '0 8px 24px -8px rgba(16, 185, 129, 0.35)',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-gray-800 font-sans antialiased">

<!-- Navbar -->
<nav class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
        <a href="/" class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shadow-brand">
                <span class="text-white text-lg font-bold">C</span>
            </div>
            <span class="font-bold text-lg text-gray-800"><?= Security::e($appName) ?></span>
        </a>

        <div class="hidden md:flex items-center gap-6 text-sm">
            <a href="#untuk-siapa" class="text-gray-600 hover:text-brand-600 transition">Cocok Untuk</a>
            <a href="#cara-pakai" class="text-gray-600 hover:text-brand-600 transition">Cara Pakai</a>
            <a href="#fitur" class="text-gray-600 hover:text-brand-600 transition">Fitur</a>
            <a href="#faq" class="text-gray-600 hover:text-brand-600 transition">FAQ</a>
        </div>

        <a href="/portal"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-brand transition">
            <i data-lucide="search" class="w-4 h-4"></i>
            Cek Tagihan
        </a>
    </div>
</nav>

<!-- Content -->
<?= $content ?? '' ?>

<!-- Footer -->
<footer class="bg-gray-50 border-t border-gray-100 mt-20">
    <div class="max-w-6xl mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                        <span class="text-white text-lg font-bold">C</span>
                    </div>
                    <span class="font-bold text-lg"><?= Security::e($appName) ?></span>
                </div>
                <p class="text-sm text-gray-500 max-w-md">
                    <?= Security::e($tagline) ?> — buat pengelola ruko, apartemen, sekolah, pesantren, komunitas, dan tempat ibadah.
                </p>
            </div>
            <div>
                <p class="font-semibold text-sm mb-3">Navigasi</p>
                <ul class="space-y-2 text-sm text-gray-500">
                    <li><a href="#untuk-siapa" class="hover:text-brand-600">Cocok Untuk</a></li>
                    <li><a href="#cara-pakai" class="hover:text-brand-600">Cara Pakai</a></li>
                    <li><a href="#fitur" class="hover:text-brand-600">Fitur</a></li>
                    <li><a href="#faq" class="hover:text-brand-600">FAQ</a></li>
                    <li><a href="/portal" class="hover:text-brand-600">Cek Tagihan</a></li>
                    <li><a href="/kebijakan-privasi" class="hover:text-brand-600">Kebijakan Privasi</a></li>
                    <li><a href="/syarat-ketentuan" class="hover:text-brand-600">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold text-sm mb-3">Kontak</p>
                <ul class="space-y-2 text-sm text-gray-500">
    <?php if (SiteInfo::emailSupport()): ?>
        <li class="flex items-center gap-2">
            <i data-lucide="mail" class="w-4 h-4"></i>
            <?= Security::e(SiteInfo::emailSupport()) ?>
        </li>
    <?php endif; ?>

    <?php if (SiteInfo::whatsappFormatted()): ?>
        <li class="flex items-center gap-2">
            <i data-lucide="phone" class="w-4 h-4"></i>
            <?= Security::e(SiteInfo::whatsappFormatted()) ?>
        </li>
    <?php endif; ?>

    <?php if (SiteInfo::address()): ?>
        <li class="flex items-start gap-2">
            <i data-lucide="map-pin" class="w-4 h-4 mt-0.5"></i>
            <span><?= Security::e(SiteInfo::address()) ?></span>
        </li>
    <?php endif; ?>
</ul>

<!-- Sosial Media -->
<?php if (SiteInfo::instagram() || SiteInfo::facebook() || SiteInfo::twitter() || SiteInfo::linkedin()): ?>
    <div class="flex gap-3 mt-4">
        <?php if (SiteInfo::instagram()): ?>
            <a href="<?= Security::e(SiteInfo::instagram()) ?>" target="_blank" rel="noopener"
               class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-brand-100 flex items-center justify-center transition"
               title="Instagram">
                <i data-lucide="instagram" class="w-4 h-4 text-gray-600"></i>
            </a>
        <?php endif; ?>

        <?php if (SiteInfo::facebook()): ?>
            <a href="<?= Security::e(SiteInfo::facebook()) ?>" target="_blank" rel="noopener"
               class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-brand-100 flex items-center justify-center transition"
               title="Facebook">
                <i data-lucide="facebook" class="w-4 h-4 text-gray-600"></i>
            </a>
        <?php endif; ?>

        <?php if (SiteInfo::twitter()): ?>
            <a href="<?= Security::e(SiteInfo::twitter()) ?>" target="_blank" rel="noopener"
               class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-brand-100 flex items-center justify-center transition"
               title="Twitter/X">
                <i data-lucide="twitter" class="w-4 h-4 text-gray-600"></i>
            </a>
        <?php endif; ?>

        <?php if (SiteInfo::linkedin()): ?>
            <a href="<?= Security::e(SiteInfo::linkedin()) ?>" target="_blank" rel="noopener"
               class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-brand-100 flex items-center justify-center transition"
               title="LinkedIn">
                <i data-lucide="linkedin" class="w-4 h-4 text-gray-600"></i>
            </a>
        <?php endif; ?>
    </div>
<?php endif; ?>
            </div>
        </div>
        <div class="mt-10 pt-6 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-3 text-xs text-gray-400">
            <p>&copy; <?= date('Y') ?><?= Security::e(SiteInfo::companyName() ?: $appName . '.id') ?> — All rights reserved.</p>
            <div class="flex gap-4">
                <a href="/kebijakan-privasi" class="hover:text-brand-600">Kebijakan Privasi</a>
                <a href="/syarat-ketentuan" class="hover:text-brand-600">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</footer>

<script>
    lucide.createIcons();
</script>

</body>
</html>