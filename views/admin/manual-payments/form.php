<?php
use App\Helpers\Security;

$val = fn(string $key) => Security::e($old[$key] ?? '');

ob_start();
?>

<div class="max-w-2xl mx-auto">

    <div class="mb-4">
        <a href="/admin/manual-payments" class="text-sm text-brand-600 hover:underline">← Kembali ke daftar</a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-800">Catat Pembayaran Manual</h3>
            <p class="text-sm text-gray-500 mt-1">
                Catat pembayaran yang diterima tunai atau transfer bank.
                Bisa untuk pembayaran penuh atau cicilan.
            </p>
        </div>

        <form method="POST" action="/admin/manual-payments/store" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= Security::e($csrf) ?>">

            <!-- Pilih Pelanggan -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Pelanggan <span class="text-red-500">*</span>
                </label>
                <select name="payer_id" id="payer_id" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="">-- Pilih Pelanggan --</option>
                    <?php if (empty($payers)): ?>
                        <option value="" disabled>Tidak ada pelanggan aktif</option>
                    <?php else: ?>
                        <?php foreach ($payers as $p): ?>
                            <option value="<?= (int) $p['id'] ?>" <?= ($old['payer_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                                <?= Security::e($p['nomor_induk']) ?> — <?= Security::e($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if (!empty($errors['payer_id'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['payer_id']) ?></p>
                <?php endif; ?>
                <p class="text-xs text-gray-500 mt-1">
                    Total pelanggan aktif: <strong><?= count($payers) ?></strong>
                </p>
            </div>

            <!-- Pilih Tagihan -->
            <div>
                <label class="block text-sm font-medium mb-1">
                    Tagihan <span class="text-red-500">*</span>
                </label>
                <select name="invoice_id" id="invoice_id" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="">-- Pilih Pelanggan Dulu --</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Hanya tagihan belum bayar yang muncul.</p>
                <?php if (!empty($errors['invoice_id'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['invoice_id']) ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Jumlah (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="jumlah" id="jumlah" value="<?= $val('jumlah') ?>" required min="0" step="any"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                           placeholder="0">
                    <p class="text-xs text-gray-500 mt-1">Otomatis terisi sisa tagihan. Bisa diubah untuk cicilan.</p>
                    <?php if (!empty($errors['jumlah'])): ?>
                        <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['jumlah']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Metode <span class="text-red-500">*</span>
                    </label>
                    <select name="metode" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="tunai"         <?= ($old['metode'] ?? '') === 'tunai'         ? 'selected' : '' ?>>Tunai</option>
                        <option value="transfer_bank" <?= ($old['metode'] ?? '') === 'transfer_bank' ? 'selected' : '' ?>>Transfer Bank</option>
                        <option value="lain"          <?= ($old['metode'] ?? '') === 'lain'          ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Tanggal Bayar</label>
                <input type="datetime-local" name="paid_at" value="<?= $val('paid_at') ?: date('Y-m-d\TH:i') ?>"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Bukti Pembayaran (opsional)</label>
                <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, atau PDF. Maks 5 MB.</p>
                <?php if (!empty($errors['bukti'])): ?>
                    <p class="text-xs text-red-600 mt-1"><?= Security::e($errors['bukti']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Catatan</label>
                <textarea name="catatan" rows="2"
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none"
                          placeholder="Opsional, contoh: cicilan pertama"><?= $val('catatan') ?></textarea>
            </div>

            <div class="flex gap-2 pt-4 border-t border-gray-100">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-brand transition">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Pembayaran
                </button>
                <a href="/admin/manual-payments"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-5 py-2.5 rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>

<script>
(function() {
    const payerSelect   = document.getElementById('payer_id');
    const invoiceSelect = document.getElementById('invoice_id');
    const jumlahInput   = document.getElementById('jumlah');

    payerSelect.addEventListener('change', function() {
        const payerId = this.value;

        invoiceSelect.innerHTML = '<option value="">-- Memuat... --</option>';
        jumlahInput.value = '';

        if (!payerId) {
            invoiceSelect.innerHTML = '<option value="">-- Pilih Pelanggan Dulu --</option>';
            return;
        }

        fetch('/admin/manual-payments/invoices/' + payerId)
            .then(res => res.json())
            .then(data => {
                if (!data.status) {
                    invoiceSelect.innerHTML = '<option value="">-- Pelanggan tidak ditemukan --</option>';
                    return;
                }

                if (!data.invoices || data.invoices.length === 0) {
                    invoiceSelect.innerHTML = '<option value="">-- Tidak ada tagihan belum bayar --</option>';
                    return;
                }

                invoiceSelect.innerHTML = '<option value="">-- Pilih Tagihan --</option>';
                data.invoices.forEach(inv => {
                    const opt = document.createElement('option');
                    opt.value = inv.id;
                    opt.dataset.remaining = inv.remaining;
                    opt.dataset.total = inv.jumlah;
                    opt.dataset.paid = inv.paid_amount;

                    const jenisLabel = inv.jenis.charAt(0).toUpperCase() + inv.jenis.slice(1);
                    let label = jenisLabel + ' ' + inv.periode
                              + ' — Sisa: Rp ' + parseInt(inv.remaining).toLocaleString('id-ID');

                    if (parseFloat(inv.paid_amount) > 0) {
                        label += ' (dari Rp ' + parseInt(inv.jumlah).toLocaleString('id-ID') + ')';
                    }

                    opt.textContent = label;
                    invoiceSelect.appendChild(opt);
                });
            })
            .catch(err => {
                console.error(err);
                invoiceSelect.innerHTML = '<option value="">-- Gagal memuat --</option>';
            });
    });

    invoiceSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.dataset.remaining) {
            jumlahInput.value = Math.floor(parseFloat(opt.dataset.remaining));
            jumlahInput.max = parseFloat(opt.dataset.remaining);
        } else {
            jumlahInput.value = '';
            jumlahInput.removeAttribute('max');
        }
    });
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';