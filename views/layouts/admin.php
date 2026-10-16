<?php
use App\Helpers\Security;

$userName    = $_SESSION['user_name']   ?? 'Guest';
$userRole    = $_SESSION['user_role']   ?? '-';
$appName     = $_ENV['APP_NAME']        ?? 'CekTagihan';
$tagline     = $_ENV['APP_TAGLINE']     ?? 'Bayar tagihan dari mana saja!';
$tenantNama  = $_SESSION['tenant_nama'] ?? null;

$displayName    = $tenantNama ?: $appName;
$displayTagline = $tenantNama ? 'Admin Panel' : $tagline;

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// =========================================
// MENU PER ROLE — TANPA DUPLIKASI
// =========================================
$menuGroups = [];

if ($userRole === 'superadmin') {
    $menuGroups[] = [
        'id'    => 'main',
        'label' => null,
        'items' => [
            ['href' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
        ],
    ];
    $menuGroups[] = [
        'id'    => 'admin',
        'label' => 'Operasional',
        'items' => [
            ['href' => '/superadmin/pendaftar',  'label' => 'Pendaftar Tenant',    'icon' => 'user-plus'],
            ['href' => '/superadmin/tenants',    'label' => 'Kelola Tenant',       'icon' => 'building-2'],
            ['href' => '/superadmin/rekening',   'label' => 'Verifikasi Rekening', 'icon' => 'landmark'],
            ['href' => '/superadmin/pencairan',  'label' => 'Pengajuan Pencairan', 'icon' => 'wallet'],
            ['href' => '/superadmin/langganan',  'label' => 'Kelola Langganan',    'icon' => 'calendar-check'],
        ],
    ];
    $menuGroups[] = [
        'id'    => 'pengaturan',
        'label' => 'Pengaturan',
        'items' => [
            ['href' => '/superadmin/pengaturan', 'label' => 'Pengaturan Platform', 'icon' => 'settings'],
        ],
    ];
} elseif ($userRole === 'admin') {
    $menuGroups[] = [
        'id'    => 'main',
        'label' => null,
        'items' => [
            ['href' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            ['href' => '/admin/langganan', 'label' => 'Langganan Saya', 'icon' => 'calendar-check'],
        ],
    ];
    $menuGroups[] = [
        'id'    => 'operasional',
        'label' => 'Operasional',
        'items' => [
            ['href' => '/admin/payers',          'label' => 'Pelanggan',         'icon' => 'users'],
            ['href' => '/admin/invoices',        'label' => 'Tagihan',           'icon' => 'file-text'],
            ['href' => '/admin/tunggakan',       'label' => 'Tunggakan',         'icon' => 'alert-triangle'],
            ['href' => '/admin/manual-payments', 'label' => 'Pembayaran Manual', 'icon' => 'hand-coins'],
        ],
    ];
    $menuGroups[] = [
        'id'    => 'laporan',
        'label' => 'Laporan',
        'items' => [
            ['href' => '/admin/laporan',                 'label' => 'Laporan Bulanan',   'icon' => 'bar-chart-3'],
            ['href' => '/admin/laporan/status-pelanggan','label' => 'Status Pelanggan',  'icon' => 'users'],
            ['href' => '/admin/laporan/per-payer',       'label' => 'Laporan Pelanggan', 'icon' => 'table-2'],
            ['href' => '/admin/pendapatan',              'label' => 'Pendapatan',        'icon' => 'trending-up'],
            ['href' => '/admin/pencairan',               'label' => 'Pencairan',         'icon' => 'landmark'],
        ],
    ];
    $menuGroups[] = [
        'id'    => 'pengaturan',
        'label' => 'Pengaturan',
        'items' => [
            ['href' => '/admin/jenis-tagihan', 'label' => 'Jenis Tagihan', 'icon' => 'tag'],
            ['href' => '/admin/rekening',      'label' => 'Rekening Bank', 'icon' => 'landmark'],
            ['href' => '/admin/profil',        'label' => 'Profil Tenant', 'icon' => 'settings'],
        ],
    ];
}

// Kumpulkan semua href menu
$allHrefs = [];
foreach ($menuGroups as $group) {
    foreach ($group['items'] as $item) {
        $allHrefs[] = $item['href'];
    }
}

$isActive = function (string $href) use ($currentPath, $allHrefs): bool {
    if ($currentPath === $href) {
        return true;
    }

    if (!str_starts_with($currentPath, $href . '/')) {
        return false;
    }

    foreach ($allHrefs as $other) {
        if ($other === $href) continue;
        if (str_starts_with($currentPath, $other . '/') && strlen($other) > strlen($href)) {
            return false;
        }
        if ($currentPath === $other) {
            return false;
        }
    }

    return true;
};

$isGroupActive = function (array $items) use ($isActive): bool {
    foreach ($items as $item) {
        if ($isActive($item['href'])) {
            return true;
        }
    }
    return false;
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::e($title ?? 'Dashboard') ?> — <?= Security::e($displayName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui'],
                    },
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
    <style>
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.25); }
        .group-collapsed .group-items { display: none; }
        .group-chevron { transition: transform 0.2s ease; }
        .group-collapsed .group-chevron { transform: rotate(-90deg); }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        .mobile-drawer-open { animation: slideIn 0.2s ease-out; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

<div class="flex min-h-screen">

    <!-- ============================================= -->
    <!-- SIDEBAR DESKTOP                                -->
    <!-- ============================================= -->
    <aside class="hidden md:flex w-64 bg-gradient-to-b from-brand-700 to-brand-800 flex-col flex-shrink-0">

        <!-- Logo -->
        <div class="p-5 border-b border-brand-600/40">
            <a href="/dashboard" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center flex-shrink-0 group-hover:bg-white/20 transition">
                    <span class="text-white font-bold text-lg">C</span>
                </div>
                <div class="min-w-0">
                    <h1 class="text-white font-bold truncate leading-tight"><?= Security::e($appName) ?></h1>
                    <p class="text-[11px] text-brand-200/80 truncate leading-tight"><?= Security::e($displayName) ?></p>
                </div>
            </a>
        </div>

        <!-- Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto sidebar-scroll">
            <?php foreach ($menuGroups as $group): ?>
                <?php
                $groupActive = $isGroupActive($group['items']);
                $groupHasLabel = !empty($group['label']);
                ?>
                <div class="group <?= $groupHasLabel ? 'group-collapsed' : '' ?> <?= $groupActive ? 'group-active' : '' ?>"
                     data-group-id="<?= Security::e($group['id']) ?>">

                    <?php if ($groupHasLabel): ?>
                        <button type="button"
                                class="group-toggle w-full flex items-center justify-between px-3 py-2 rounded-lg text-brand-200/70 hover:text-white hover:bg-white/5 transition"
                                onclick="toggleGroup('<?= Security::e($group['id']) ?>')">
                            <span class="text-[10px] font-bold uppercase tracking-widest">
                                <?= Security::e($group['label']) ?>
                            </span>
                            <i data-lucide="chevron-down" class="group-chevron w-3.5 h-3.5"></i>
                        </button>
                    <?php endif; ?>

                    <div class="group-items space-y-0.5 <?= $groupHasLabel ? 'mt-1' : '' ?>">
                        <?php foreach ($group['items'] as $item): ?>
                            <?php $active = $isActive($item['href']); ?>
                            <a href="<?= Security::e($item['href']) ?>"
                               title="<?= Security::e($item['label']) ?>"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 relative
                                      <?= $active
                                          ? 'bg-white/15 text-white shadow-sm'
                                          : 'text-brand-100/90 hover:bg-white/8 hover:text-white' ?>">
                                <?php if ($active): ?>
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-brand-300 rounded-r"></span>
                                <?php endif; ?>
                                <i data-lucide="<?= Security::e($item['icon']) ?>" class="w-4 h-4 flex-shrink-0 <?= $active ? 'text-brand-200' : '' ?>"></i>
                                <span class="truncate"><?= Security::e($item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- User Profile -->
        <div class="p-3 border-t border-brand-600/40">
            <div class="relative">
                <button type="button" onclick="toggleUserMenu()"
                        class="w-full flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-white/10 transition text-left">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-teal-500 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate"><?= Security::e($userName) ?></p>
                        <p class="text-[11px] text-brand-200/70 truncate"><?= Security::e($userRole) ?></p>
                    </div>
                    <i data-lucide="chevron-up" class="w-4 h-4 text-brand-200/60 flex-shrink-0"></i>
                </button>

                <!-- Dropdown User -->
                <div id="user-menu" class="hidden absolute bottom-full left-0 right-0 mb-2 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-xs text-gray-500">Login sebagai</p>
                        <p class="text-sm font-semibold text-gray-800 truncate"><?= Security::e($userName) ?></p>
                        <p class="text-xs text-gray-500 truncate"><?= Security::e($userRole) ?></p>
                    </div>
                    <a href="/account/change-password" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <i data-lucide="key-round" class="w-4 h-4 text-gray-400"></i>
                        Ganti Password
                    </a>
                    <a href="/logout" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition border-t border-gray-100">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </aside>

    <!-- ============================================= -->
    <!-- MAIN CONTENT                                   -->
    <!-- ============================================= -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar mobile -->
        <header class="md:hidden bg-gradient-to-r from-brand-600 to-teal-600 text-white px-4 py-3 flex items-center justify-between sticky top-0 z-30 shadow-md">
            <div class="flex items-center gap-2 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-bold text-sm">C</span>
                </div>
                <span class="font-semibold truncate"><?= Security::e($displayName) ?></span>
            </div>
            <button onclick="openMobileMenu()"
                    class="p-2 rounded-lg hover:bg-white/10 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </header>

        <!-- Backdrop -->
        <div id="mobile-backdrop" class="hidden md:hidden fixed inset-0 bg-black/50 z-40" onclick="closeMobileMenu()"></div>

        <!-- Mobile menu drawer -->
        <div id="mobile-menu" class="hidden md:hidden fixed top-0 left-0 bottom-0 w-72 bg-gradient-to-b from-brand-700 to-brand-800 text-white z-50 mobile-drawer-open overflow-y-auto sidebar-scroll">

            <!-- Header -->
            <div class="p-5 border-b border-brand-600/40 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold text-lg">C</span>
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-white font-bold truncate leading-tight"><?= Security::e($appName) ?></h1>
                        <p class="text-[11px] text-brand-200/80 truncate leading-tight"><?= Security::e($displayName) ?></p>
                    </div>
                </div>
                <button onclick="closeMobileMenu()" class="p-1.5 rounded-lg hover:bg-white/10">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Menu -->
            <nav class="px-3 py-4 space-y-1">
                <?php foreach ($menuGroups as $group): ?>
                    <?php $groupActive = $isGroupActive($group['items']); ?>
                    <div class="group <?= !empty($group['label']) ? 'group-collapsed' : '' ?> <?= $groupActive ? 'group-active' : '' ?>"
                         data-group-id="m-<?= Security::e($group['id']) ?>">

                        <?php if (!empty($group['label'])): ?>
                            <button type="button"
                                    class="group-toggle w-full flex items-center justify-between px-3 py-2 rounded-lg text-brand-200/70 hover:text-white hover:bg-white/5 transition"
                                    onclick="toggleGroup('m-<?= Security::e($group['id']) ?>')">
                                <span class="text-[10px] font-bold uppercase tracking-widest">
                                    <?= Security::e($group['label']) ?>
                                </span>
                                <i data-lucide="chevron-down" class="group-chevron w-3.5 h-3.5"></i>
                            </button>
                        <?php endif; ?>

                        <div class="group-items space-y-0.5 <?= !empty($group['label']) ? 'mt-1' : '' ?>">
                            <?php foreach ($group['items'] as $item): ?>
                                <?php $active = $isActive($item['href']); ?>
                                <a href="<?= Security::e($item['href']) ?>"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all relative
                                          <?= $active ? 'bg-white/15 text-white' : 'text-brand-100/90 hover:bg-white/8 hover:text-white' ?>">
                                    <?php if ($active): ?>
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-brand-300 rounded-r"></span>
                                    <?php endif; ?>
                                    <i data-lucide="<?= Security::e($item['icon']) ?>" class="w-4 h-4 flex-shrink-0"></i>
                                    <span class="truncate"><?= Security::e($item['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </nav>

            <!-- User -->
            <div class="p-4 border-t border-brand-600/40">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-teal-500 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                        <?= strtoupper(substr($userName, 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate"><?= Security::e($userName) ?></p>
                        <p class="text-[11px] text-brand-200/70 truncate"><?= Security::e($userRole) ?></p>
                    </div>
                </div>
                <div class="space-y-1">
                    <a href="/account/change-password" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-brand-100 hover:bg-white/10">
                        <i data-lucide="key-round" class="w-4 h-4"></i>
                        Ganti Password
                    </a>
                    <a href="/logout" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-300 hover:bg-red-500/20">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Header desktop -->
        <header class="hidden md:flex bg-white border-b border-gray-200 px-8 py-5 justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-gray-800"><?= Security::e($title ?? 'Dashboard') ?></h2>
                <p class="text-sm text-gray-500 mt-0.5"><?= Security::e($displayName) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700"><?= Security::e($userName) ?></p>
                    <p class="text-xs text-gray-400"><?= Security::e($userRole) ?></p>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-4 md:p-8">
            <?php
            use App\Helpers\Toast;
            echo Toast::render();
            if (!empty($_SESSION['flash_success'])) {
                echo '<script>document.addEventListener("DOMContentLoaded", () => showToast(' . json_encode($_SESSION['flash_success']) . ', "success"));</script>';
                unset($_SESSION['flash_success']);
            }
            if (!empty($_SESSION['flash_error'])) {
                echo '<script>document.addEventListener("DOMContentLoaded", () => showToast(' . json_encode($_SESSION['flash_error']) . ', "error"));</script>';
                unset($_SESSION['flash_error']);
            }
            ?>

            <?= $content ?? '' ?>
        </main>

    </div>
</div>

<script>
    // =========================================
    // Collapse / Expand Group
    // =========================================
    function toggleGroup(groupId) {
        const group = document.querySelector(`[data-group-id="${groupId}"]`);
        if (!group) return;

        group.classList.toggle('group-collapsed');

        const states = JSON.parse(localStorage.getItem('sidebar_groups') || '{}');
        states[groupId] = group.classList.contains('group-collapsed');
        localStorage.setItem('sidebar_groups', JSON.stringify(states));
    }

    // Restore state
    (function restoreGroupStates() {
        const states = JSON.parse(localStorage.getItem('sidebar_groups') || '{}');

        Object.keys(states).forEach(groupId => {
            const group = document.querySelector(`[data-group-id="${groupId}"]`);
            if (!group) return;

            if (group.classList.contains('group-active')) {
                group.classList.remove('group-collapsed');
                return;
            }

            if (states[groupId]) {
                group.classList.add('group-collapsed');
            } else {
                group.classList.remove('group-collapsed');
            }
        });
    })();

    // =========================================
    // User Menu Dropdown
    // =========================================
    function toggleUserMenu() {
        const menu = document.getElementById('user-menu');
        menu.classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('user-menu');
        if (!menu) return;

        const button = e.target.closest('button[onclick="toggleUserMenu()"]');
        if (!button && !e.target.closest('#user-menu')) {
            menu.classList.add('hidden');
        }
    });

    // =========================================
    // Mobile Menu
    // =========================================
    function openMobileMenu() {
        document.getElementById('mobile-menu').classList.remove('hidden');
        document.getElementById('mobile-backdrop').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) lucide.createIcons();
    }

    function closeMobileMenu() {
        document.getElementById('mobile-menu').classList.add('hidden');
        document.getElementById('mobile-backdrop').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // =========================================
    // Init Lucide Icons
    // =========================================
    if (window.lucide) {
        lucide.createIcons();
    }
</script>

</body>
</html>