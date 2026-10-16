<?php
use App\Helpers\Security;
use App\Helpers\SiteInfo;

$appName = $_ENV['APP_NAME'] ?? 'CekTagihan';

ob_start();
?>

<div class="bg-gray-50 py-16 md:py-24">
    <div class="max-w-3xl mx-auto px-4">

        <!-- Header -->
        <div class="mb-10">
            <a href="/" class="text-sm text-brand-600 hover:underline">← Kembali ke Beranda</a>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-4 mb-2">Syarat & Ketentuan</h1>
            <p class="text-sm text-gray-500">Terakhir diperbarui: <?= date('d F Y') ?></p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-6 md:p-10 space-y-8 text-gray-700 leading-relaxed">

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">1. Penerimaan Syarat</h2>
                <p>
                    Dengan mengakses dan menggunakan layanan <strong><?= Security::e($appName) ?></strong>,
                    Anda menyetujui Syarat & Ketentuan ini. Jika Anda tidak setuju, mohon untuk tidak
                    menggunakan layanan kami.
                </p>
                <p class="mt-3">
                    Syarat & Ketentuan ini disusun sesuai dengan <strong>UU No. 8 Tahun 1999 tentang
                    Perlindungan Konsumen</strong> dan <strong>PBI No. 6 Tahun 2026 tentang Perlindungan Konsumen</strong>.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">2. Definisi</h2>
                <ul class="list-disc list-inside space-y-2">
                    <li><strong>Platform:</strong> Situs web dan sistem <?= Security::e($appName) ?> yang menyediakan layanan cek dan bayar tagihan.</li>
                    <li><strong>Pengelola:</strong> Pihak yang mengelola lokasi (ruko, apartemen, sekolah, dll) dan menggunakan Platform untuk mengelola tagihan.</li>
                    <li><strong>Pelanggan:</strong> Pihak yang memiliki tagihan di lokasi Pengelola dan menggunakan Platform untuk cek dan bayar tagihan.</li>
                    <li><strong>Payment Gateway:</strong> Pihak ketiga (Xendit) yang memproses transaksi pembayaran.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">3. Ruang Lingkup Layanan</h2>
                <p><?= Security::e($appName) ?> menyediakan layanan:</p>
                <ul class="list-disc list-inside mt-3 space-y-2">
                    <li>Menampilkan tagihan Pelanggan berdasarkan nomor pelanggan.</li>
                    <li>Memproses pembayaran tagihan melalui payment gateway (QRIS, Virtual Account, e-wallet, retail).</li>
                    <li>Menyediakan dashboard bagi Pengelola untuk mengelola tagihan dan pelanggan.</li>
                    <li>Mengirim notifikasi terkait tagihan dan pembayaran.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">4. Hak dan Kewajiban Pengguna</h2>
                <p><strong>Hak Pengguna:</strong></p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Mendapatkan layanan sesuai dengan yang dijanjikan.</li>
                    <li>Menyampaikan keluhan atau pengaduan terkait layanan.</li>
                    <li>Mendapatkan informasi yang jelas tentang produk dan layanan.</li>
                </ul>
                <p class="mt-4"><strong>Kewajiban Pengguna:</strong></p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Memberikan data yang benar, akurat, dan terkini.</li>
                    <li>Menjaga kerahasiaan akses (jika ada).</li>
                    <li>Menggunakan layanan sesuai dengan hukum yang berlaku.</li>
                    <li>Tidak melakukan tindakan yang merugikan pihak lain.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">5. Biaya Layanan</h2>
                <p>
                    Penggunaan layanan <?= Security::e($appName) ?> untuk Pelanggan <strong>tidak dikenakan biaya tambahan</strong>.
                    Pelanggan hanya membayar sesuai nominal tagihan.
                </p>
                <p class="mt-3">
                    Biaya layanan untuk Pengelola diatur dalam perjanjian terpisah.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">6. Pembayaran dan Pengembalian Dana</h2>
                <p>
                    Semua pembayaran diproses melalui <strong>Xendit</strong> sebagai payment gateway resmi.
                    Status tagihan akan otomatis diperbarui setelah pembayaran berhasil.
                </p>
                <p class="mt-3">
                    <strong>Pengembalian dana (refund)</strong> hanya dapat dilakukan dalam kondisi tertentu
                    dan harus diajukan melalui Pengelola. Keputusan pengembalian dana sepenuhnya berada
                    di tangan Pengelola.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">7. Keamanan Transaksi</h2>
                <p>
                    Kami berkomitmen untuk menjaga keamanan transaksi Anda. Semua komunikasi data
                    menggunakan enkripsi SSL/HTTPS. Kami tidak menyimpan data kartu kredit/debit Anda.
                </p>
                <p class="mt-3">
                    Anda bertanggung jawab untuk menjaga kerahasiaan informasi akses Anda (jika ada)
                    dan segera melaporkan jika terjadi akses tidak sah.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">8. Penanganan Keluhan</h2>
                <p>
                    Jika Anda memiliki keluhan terkait layanan, silakan hubungi:
                </p>
                <div class="mt-3 p-4 bg-gray-50 rounded-2xl space-y-1">
    <?php if (SiteInfo::emailSupport()): ?>
        <p><strong>Email:</strong> <?= Security::e(SiteInfo::emailSupport()) ?></p>
    <?php endif; ?>
    <?php if (SiteInfo::whatsappFormatted()): ?>
        <p><strong>WhatsApp:</strong> <?= Security::e(SiteInfo::whatsappFormatted()) ?></p>
    <?php endif; ?>
    <?php if (SiteInfo::hours()): ?>
        <p><strong>Jam Operasional:</strong> <?= Security::e(SiteInfo::hours()) ?></p>
    <?php endif; ?>
    <?php if (SiteInfo::address()): ?>
        <p><strong>Alamat:</strong> <?= Security::e(SiteInfo::address()) ?></p>
    <?php endif; ?>
</div>
                <p class="mt-3">
                    Kami akan menindaklanjuti keluhan Anda paling lambat <strong>20 hari kerja</strong>
                    sesuai ketentuan perlindungan konsumen.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">9. Batasan Tanggung Jawab</h2>
                <p>
                    <?= Security::e($appName) ?> tidak bertanggung jawab atas:
                </p>
                <ul class="list-disc list-inside mt-3 space-y-2">
                    <li>Kesalahan data yang diberikan oleh Pengelola atau Pelanggan.</li>
                    <li>Gangguan layanan akibat force majeure (bencana alam, perang, dll).</li>
                    <li>Kerugian akibat penggunaan layanan yang melanggar hukum.</li>
                    <li>Tindakan pihak ketiga (payment gateway, penyedia WA) di luar kendali kami.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">10. Penghentian Layanan</h2>
                <p>
                    Kami berhak menghentikan atau menangguhkan akses Anda ke layanan jika terjadi
                    pelanggaran terhadap Syarat & Ketentuan ini.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">11. Perubahan Syarat</h2>
                <p>
                    Kami dapat mengubah Syarat & Ketentuan ini dari waktu ke waktu. Perubahan akan
                    diumumkan di halaman ini. Penggunaan layanan setelah perubahan berarti Anda
                    menyetujui Syarat & Ketentuan yang baru.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">12. Hukum yang Berlaku</h2>
<p>
    Syarat & Ketentuan ini diatur oleh dan ditafsirkan sesuai dengan hukum
    Republik Indonesia. Setiap sengketa akan diselesaikan secara musyawarah,
    dan jika tidak tercapai, akan diselesaikan melalui pengadilan yang berwenang
    di Indonesia.
</p>
<?php if (SiteInfo::companyName()): ?>
    <div class="mt-4 p-4 bg-gray-50 rounded-2xl space-y-1 text-sm">
        <p><strong>Badan Hukum:</strong> <?= Security::e(SiteInfo::companyName()) ?></p>
        <?php if (SiteInfo::nib()): ?>
            <p><strong>NIB:</strong> <?= Security::e(SiteInfo::nib()) ?></p>
        <?php endif; ?>
        <?php if (SiteInfo::npwp()): ?>
            <p><strong>NPWP:</strong> <?= Security::e(SiteInfo::npwp()) ?></p>
        <?php endif; ?>
        <?php if (SiteInfo::pse()): ?>
            <p><strong>PSE Kominfo:</strong> <?= Security::e(SiteInfo::pse()) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
            </section>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';