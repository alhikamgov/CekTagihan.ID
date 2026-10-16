<?php
use App\Helpers\Security;

$prefillTenantId = $prefillTenantId ?? null;
$prefillTenantNama = $prefillTenantNama ?? null;

ob_start();
?>

<div class="bg-white rounded-3xl shadow-xl shadow-brand-100/50 p-6 border border-brand-100">

    <!-- Info Tenant (kalau dari URL slug) -->
    <?php if ($prefillTenantId && $prefillTenantNama): ?>
        <div class="mb-5 p-4 bg-brand-50 border border-brand-200 rounded-2xl">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="building-2" class="w-5 h-5 text-brand-600"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs text-brand-700 font-medium">Anda akan cek tagihan di:</p>
                    <p class="text-base font-bold text-brand-900 mt-0.5 break-words"><?= Security::e($prefillTenantNama) ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-6">
        <h2 class="text-lg font-bold text-gray-800">Cek Tagihan</h2>
        <p class="text-sm text-gray-500 mt-0.5">
            Pilih lokasi dan masukan nomor pelanggan.
        </p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-start gap-2">
            <span class="text-red-500 flex-shrink-0">⚠</span>
            <span><?= Security::e($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="/portal/cek" class="space-y-5" id="form-cek">
        <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">
        <input type="hidden" name="tenant_id" id="tenant_id" value="<?= Security::e($old['tenant_id'] ?? $prefillTenantId ?? '') ?>">

        <!-- Pilih Lokasi (HANYA muncul kalau BUKAN dari URL slug) -->
        <?php if (!$prefillTenantId): ?>
            <div class="relative">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Pilih Lokasi
                </label>
                <div class="relative">
                    <input type="text" id="tenant-search" autocomplete="off"
                           placeholder="Ketik minimal 3 huruf..."
                           class="w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-100 outline-none text-base transition">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
                </div>
                <div id="tenant-results" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto"></div>
                <p id="tenant-hint" class="text-xs text-gray-500 mt-1.5">
                    Ketik minimal 3 huruf dari nama lokasi Anda.
                </p>
            </div>
        <?php endif; ?>

        <!-- Nomor Pelanggan -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Nomor Pelanggan
            </label>
            <input type="text" name="nomor_induk" value="<?= Security::e($old['nomor_induk'] ?? '') ?>"
                   required autocomplete="off"
                   placeholder=""
                   class="w-full px-3 py-3 border-2 border-gray-200 rounded-xl focus:border-brand-500 focus:ring-4 focus:ring-brand-100 outline-none text-base font-mono transition">
            <p class="text-xs text-gray-500 mt-1.5">
                Belum tahu nomornya? <span class="text-gray-700">Hubungi pengelola Anda.</span>
            </p>
        </div>

        <button type="submit"
                class="w-full bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-brand-200 hover:shadow-xl transition-all">
            Cek Tagihan
        </button>
    </form>

</div>

<?php if (!$prefillTenantId): ?>
<script>
(function() {
    const tenants = <?= json_encode(
        array_map(fn($t) => ['id' => (int)$t['id'], 'nama' => $t['nama']], $tenants),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
    ) ?>;
    const searchInput = document.getElementById('tenant-search');
    const resultsBox  = document.getElementById('tenant-results');
    const hiddenId    = document.getElementById('tenant_id');
    const hint        = document.getElementById('tenant-hint');

    const oldId = hiddenId.value;
    if (oldId) {
        const found = tenants.find(t => String(t.id) === String(oldId));
        if (found) searchInput.value = found.nama;
    }

    const esc = (s) => String(s).replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    function render(list) {
        if (list.length === 0) {
            resultsBox.innerHTML = '<div class="p-3 text-sm text-gray-500 text-center">Lokasi tidak ditemukan</div>';
        } else {
            resultsBox.innerHTML = list.map(t => `
                <button type="button" data-id="${t.id}" data-nama="${esc(t.nama)}"
                        class="w-full text-left px-4 py-3 hover:bg-brand-50 border-b border-gray-100 last:border-0 text-sm">
                    ${esc(t.nama)}
                </button>
            `).join('');
        }
        resultsBox.classList.remove('hidden');
    }

    searchInput.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        hiddenId.value = '';

        if (q.length < 3) {
            resultsBox.classList.add('hidden');
            hint.textContent = q.length === 0
                ? 'Ketik minimal 3 huruf dari nama lokasi Anda.'
                : `Ketik ${3 - q.length} huruf lagi...`;
            return;
        }

        hint.textContent = '';
        const filtered = tenants.filter(t => t.nama.toLowerCase().includes(q));
        render(filtered);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 3) {
            const q = this.value.trim().toLowerCase();
            const filtered = tenants.filter(t => t.nama.toLowerCase().includes(q));
            render(filtered);
        }
    });

    resultsBox.addEventListener('click', function(e) {
        const btn = e.target.closest('button[data-id]');
        if (!btn) return;
        hiddenId.value = btn.dataset.id;
        searchInput.value = btn.dataset.nama;
        resultsBox.classList.add('hidden');
        hint.textContent = '';
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            resultsBox.classList.add('hidden');
        }
    });

    document.getElementById('form-cek').addEventListener('submit', function(e) {
        if (!hiddenId.value) {
            e.preventDefault();
            hint.textContent = 'Pilih lokasi dari daftar terlebih dahulu.';
            hint.classList.add('text-red-500');
            searchInput.focus();
        }
    });
})();
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';