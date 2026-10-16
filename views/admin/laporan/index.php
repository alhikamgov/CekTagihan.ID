<?php
use App\Helpers\Security;

$fmtRupiah  = fn($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
$fmtTanggal = fn($d) => $d ? date('d M Y H:i', strtotime($d)) : '-';

$bulanLabel = date('F Y', strtotime($bulan . '-01'));

ob_start();
?>

<!-- Filter -->
<div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100 mb-6">
    <form method="GET" action="/admin/laporan" class="flex gap-2 flex-wrap items-center">
        <label class="text-sm font-medium text-gray-700">Pilih Periode:</label>
        <select name="bulan"
                class="px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none text-sm">
            <?php if (!empty($bulanList)): ?>
                <?php foreach ($bulanList as $b): ?>
                    <option value="<?= Security::e($b) ?>" <?= $bulan === $b ? 'selected' : '' ?>>
                        <?= date('F Y', strtotime($b . '-01')) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="<?= Security::e($bulan) ?>">
                    <?= date('F Y', strtotime($bulan . '-01')) ?>
                </option>
            <?php endif; ?>
        </select>

        <button type="submit"
                class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            <i data-lucide="filter" class="w-4 h-4"></i>
            Tampilkan
        </button>

        <?php if (!empty($rows)): ?>
            <a href="/admin/laporan/export?bulan=<?= urlencode($bulan) ?>"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-500 to-teal-600 hover:from-brand-600 hover:to-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow-brand transition">
                <i data-lucide="download" class="w-4 h-4"></i>
                Export Excel
            </a>
        <?php endif; ?>

        <a href="/admin/laporan/status-pelanggan"
           class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium px-4 py-2 rounded-xl transition ml-auto">
            <i data-lucide="users" class="w-4 h-4"></i>
            Status Per Pelanggan
        </a>
    </form>
</div>

<!-- Chart Row 1 -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    <!-- Chart: Pendapatan 30 Hari -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                <i data-lucide="trending-up" class="w-4 h-4 text-brand-600"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-700">Pendapatan 30 Hari Terakhir</h3>
                <p class="text-xs text-gray-500">Total pembayaran masuk per hari</p>
            </div>
        </div>
        <div style="height: 240px;">
            <canvas id="chartPendapatan"></canvas>
        </div>
    </div>

    <!-- Chart: Status Tagihan -->
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                <i data-lucide="pie-chart" class="w-4 h-4 text-teal-600"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-700">Status Tagihan</h3>
                <p class="text-xs text-gray-500">Lunas vs belum</p>
            </div>
        </div>
        <div style="height: 240px;">
            <canvas id="chartStatus"></canvas>
        </div>
    </div>
</div>

<!-- Chart Row 2 -->
<div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
            <i data-lucide="bar-chart-3" class="w-4 h-4 text-amber-600"></i>
        </div>
        <div>
            <h3 class="text-sm font-bold text-gray-700">Total Tagihan Per Jenis</h3>
            <p class="text-xs text-gray-500">Distribusi tagihan berdasarkan jenis</p>
        </div>
    </div>
    <div style="height: 240px;">
        <canvas id="chartJenis"></canvas>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5 text-brand-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Transaksi</p>
        </div>
        <p class="text-2xl font-bold text-gray-800"><?= (int) ($summary['total']['total_transaksi'] ?? 0) ?></p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-teal-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Nominal</p>
        </div>
        <p class="text-2xl font-bold text-teal-600"><?= $fmtRupiah($summary['total']['total_nominal'] ?? 0) ?></p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5 text-gray-600"></i>
            </div>
            <p class="text-sm text-gray-500">Total Pelanggan</p>
        </div>
        <p class="text-2xl font-bold text-gray-800"><?= (int) ($summary['total']['total_payer'] ?? 0) ?></p>
    </div>
</div>

<!-- Per Jenis -->
<?php if (!empty($summary['per_jenis'])): ?>
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-gray-100 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Per Jenis Tagihan</h3>
        <div class="flex flex-wrap gap-3">
            <?php foreach ($summary['per_jenis'] as $pj): ?>
                <div class="bg-gray-50 rounded-xl px-4 py-3 min-w-[160px]">
                    <p class="text-xs text-gray-500"><?= ucfirst(Security::e($pj['jenis'])) ?></p>
                    <p class="font-bold text-gray-800 text-sm mt-1"><?= $fmtRupiah($pj['total']) ?></p>
                    <p class="text-xs text-gray-500"><?= (int) $pj['jumlah_transaksi'] ?> transaksi</p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const chartPendapatan = <?= json_encode($chartPendapatan) ?>;
    const chartStatus     = <?= json_encode($chartStatus) ?>;
    const chartJenis      = <?= json_encode($chartJenis) ?>;

    // ==== Chart 1: Pendapatan 30 Hari ====
    new Chart(document.getElementById('chartPendapatan'), {
        type: 'line',
        data: {
            labels: chartPendapatan.map(d => d.tanggal),
            datasets: [{
                label: 'Pendapatan',
                data: chartPendapatan.map(d => d.total),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 2,
                pointHoverRadius: 6,
                pointBackgroundColor: '#10b981',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt',
                        font: { size: 10 }
                    },
                    grid: { color: '#f3f4f6' }
                },
                x: {
                    ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 8 },
                    grid: { display: false }
                }
            }
        }
    });

    // ==== Chart 2: Status Tagihan ====
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: ['Lunas', 'Sebagian', 'Belum Bayar'],
            datasets: [{
                data: [
                    parseInt(chartStatus.lunas || 0),
                    parseInt(chartStatus.sebagian || 0),
                    parseInt(chartStatus.belum || 0)
                ],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 11 }, padding: 12, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.label + ': ' + ctx.parsed + ' tagihan'
                    }
                }
            }
        }
    });

    // ==== Chart 3: Per Jenis ====
    new Chart(document.getElementById('chartJenis'), {
        type: 'bar',
        data: {
            labels: chartJenis.map(j => j.jenis.charAt(0).toUpperCase() + j.jenis.slice(1)),
            datasets: [{
                label: 'Total',
                data: chartJenis.map(j => j.total),
                backgroundColor: ['#10b981', '#14b8a6', '#f59e0b', '#3b82f6', '#8b5cf6', '#ef4444'],
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'Rp ' + (v / 1000000).toFixed(1) + 'jt',
                        font: { size: 10 }
                    },
                    grid: { color: '#f3f4f6' }
                },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';