<?php
use App\Helpers\Security;

$appName = $_ENV['APP_NAME']    ?? 'CekTagihan';
$tagline = $_ENV['APP_TAGLINE'] ?? 'Bayar tagihan dari mana saja!';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= Security::e($appName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',
                            400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',
                            800:'#065f46',900:'#064e3b'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-brand-50 via-white to-teal-50 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 shadow-lg shadow-brand-200 mb-4">
            <span class="text-white text-3xl font-bold">C</span>
        </div>
        <h1 class="text-2xl font-bold bg-gradient-to-r from-brand-600 to-teal-600 bg-clip-text text-transparent">
            <?= Security::e($appName) ?>
        </h1>
        <p class="text-sm text-gray-500 mt-1"><?= Security::e($tagline) ?></p>
    </div>

    <div class="bg-white rounded-3xl shadow-xl shadow-brand-100/50 p-8 border border-brand-100">

        <h2 class="text-lg font-bold text-gray-800 mb-6">Masuk ke Dashboard</h2>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-start gap-2">
                <span class="text-red-500 flex-shrink-0">⚠</span>
                <span><?= Security::e($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required autofocus
                       placeholder="nama@email.com"
                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-100 outline-none transition">
            </div>

            <div>
    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
    <div class="relative">
        <input type="password" name="password" id="password-input" required
               placeholder="••••••••"
               class="w-full px-4 py-3 pr-12 border-2 border-gray-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-100 outline-none transition">
        <button type="button" id="toggle-password"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-brand-600 transition p-1"
                aria-label="Tampilkan password">
            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            </svg>
        </button>
    </div>
</div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-brand-200 hover:shadow-xl transition-all">
                Masuk
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-400 mt-6">
        &copy; <?= date('Y') ?> <?= Security::e($appName) ?>.id
    </p>

</div>

<script>
(function() {
    const input = document.getElementById('password-input');
    const btn = document.getElementById('toggle-password');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');

    btn.addEventListener('click', function() {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        eyeOpen.classList.toggle('hidden', isPassword);
        eyeClosed.classList.toggle('hidden', !isPassword);
    });
})();
</script>

</body>
</html>