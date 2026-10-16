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
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-4 mb-2">Kebijakan Privasi</h1>
            <p class="text-sm text-gray-500">Terakhir diperbarui: <?= date('d F Y') ?></p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-6 md:p-10 space-y-8 text-gray-700 leading-relaxed">

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">1. Pendahuluan</h2>
                <p>
                    Selamat datang di <strong><?= Security::e($appName) ?></strong>. Kami berkomitmen untuk melindungi
                    data pribadi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan,
                    menggunakan, menyimpan, dan melindungi data pribadi Anda sesuai dengan
                    <strong>Undang-Undang No. 27 Tahun 2022 tentang Pelindungan Data Pribadi (UU PDP)</strong>,
                    <strong>Peraturan Pemerintah No. 71 Tahun 2019</strong>, dan peraturan terkait lainnya di Indonesia.
                </p>
                <p class="mt-3">
                    Dengan menggunakan layanan <?= Security::e($appName) ?>, Anda menyetujui praktik yang dijelaskan
                    dalam Kebijakan Privasi ini.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">2. Data Pribadi yang Kami Kumpulkan</h2>
                <p>Kami dapat mengumpulkan data pribadi berikut:</p>
                <ul class="list-disc list-inside mt-3 space-y-2">
                    <li><strong>Data Identitas:</strong> Nama lengkap, nomor pelanggan.</li>
                    <li><strong>Data Kontak:</strong> Nomor WhatsApp, alamat email.</li>
                    <li><strong>Data Transaksi:</strong> Riwayat tagihan, jumlah pembayaran, metode pembayaran, status pembayaran.</li>
                    <li><strong>Data Teknis:</strong> Alamat IP, jenis browser, waktu akses.</li>
                    <li><strong>Data Rekening Bank</strong> (untuk Pengelola): Nama bank, nomor rekening, nama pemilik rekening.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">3. Dasar Hukum Pemrosesan Data</h2>
                <p>Kami memproses data pribadi Anda berdasarkan:</p>
                <ul class="list-disc list-inside mt-3 space-y-2">
                    <li><strong>Persetujuan Anda:</strong> Ketika Anda secara sadar memberikan data untuk menggunakan layanan kami.</li>
                    <li><strong>Pelaksanaan Perjanjian:</strong> Untuk memenuhi kewajiban kami dalam menyediakan layanan.</li>
                    <li><strong>Kewajiban Hukum:</strong> Untuk mematuhi peraturan perundang-undangan yang berlaku.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">4. Tujuan Penggunaan Data</h2>
                <p>Data pribadi Anda digunakan untuk:</p>
                <ul class="list-disc list-inside mt-3 space-y-2">
                    <li>Menampilkan tagihan Anda di portal <?= Security::e($appName) ?>.</li>
                    <li>Memproses pembayaran tagihan melalui payment gateway (Xendit).</li>
                    <li>Mengirim notifikasi terkait tagihan dan pembayaran via WhatsApp atau email.</li>
                    <li>Menyediakan laporan bagi Pengelola (admin tenant).</li>
                    <li>Meningkatkan kualitas layanan kami.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">5. Pengungkapan Data kepada Pihak Ketiga</h2>
                <p>Kami <strong>tidak menjual</strong> data pribadi Anda. Kami dapat membagikan data kepada:</p>
                <ul class="list-disc list-inside mt-3 space-y-2">
                    <li><strong>Pengelola (Admin Tenant):</strong> Data pelanggan dan transaksi diakses oleh pengelola lokasi tempat Anda terdaftar.</li>
                    <li><strong>Payment Gateway (Xendit):</strong> Untuk memproses pembayaran. Xendit memiliki kebijakan privasi sendiri.</li>
                    <li><strong>Penyedia Layanan WhatsApp (Fonnte/Wablas):</strong> Untuk mengirim notifikasi.</li>
                    <li><strong>Penegak Hukum:</strong> Jika diwajibkan oleh hukum berdasarkan permintaan resmi.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">6. Penyimpanan dan Keamanan Data</h2>
                <p>
                    Kami menyimpan data pribadi Anda selama diperlukan untuk tujuan di atas, dan sesuai dengan
                    ketentuan retensi data yang berlaku. Kami menerapkan langkah-langkah keamanan teknis dan
                    organisasi yang wajar untuk melindungi data Anda dari akses tidak sah, pengungkapan,
                    perubahan, atau penghancuran.
                </p>
                <p class="mt-3">
                    Dalam hal terjadi kegagalan pelindungan data pribadi, kami akan memberikan pemberitahuan
                    tertulis paling lambat <strong>3x24 jam</strong> kepada Anda dan lembaga pengawas, sesuai
                    ketentuan <strong>UU PDP</strong>.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">7. Hak Anda sebagai Subjek Data</h2>
                <p>Sesuai dengan UU PDP, Anda memiliki hak untuk:</p>
                <ul class="list-disc list-inside mt-3 space-y-2">
                    <li>Mendapatkan informasi tentang identitas kami, dasar hukum, dan tujuan pemrosesan data.</li>
                    <li>Mengakses dan mendapatkan salinan data pribadi Anda.</li>
                    <li>Memperbaiki atau memperbarui data pribadi Anda.</li>
                    <li>Menghapus atau memusnahkan data pribadi Anda.</li>
                    <li>Membatasi atau menolak pemrosesan data pribadi Anda.</li>
                    <li>Menarik persetujuan pemrosesan data pribadi Anda.</li>
                    <li>Mengajukan keluhan terkait pemrosesan data pribadi Anda.</li>
                </ul>
                <p class="mt-3">
                    Untuk menggunakan hak-hak tersebut, hubungi kami di alamat kontak di bawah.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">8. Cookie dan Teknologi Pelacakan</h2>
                <p>
                    Kami menggunakan cookie dan teknologi serupa untuk menjaga sesi login, mengingat preferensi,
                    dan menganalisis penggunaan layanan. Anda dapat menonaktifkan cookie di pengaturan browser,
                    namun beberapa fitur mungkin tidak berfungsi dengan baik.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">9. Perubahan Kebijakan</h2>
                <p>
                    Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu. Perubahan akan
                    diumumkan di halaman ini dengan tanggal pembaruan yang baru. Kami menyarankan Anda
                    untuk meninjau halaman ini secara berkala.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-gray-900 mb-3">10. Hubungi Kami</h2>
                <p>Jika Anda memiliki pertanyaan atau keluhan terkait Kebijakan Privasi ini, hubungi:</p>
                <div class="mt-3 p-4 bg-gray-50 rounded-2xl space-y-1">
                    <?php if (SiteInfo::emailPrivacy()): ?>
                        <p><strong>Email:</strong> <?= Security::e(SiteInfo::emailPrivacy()) ?></p>
                    <?php endif; ?>
                    <?php if (SiteInfo::whatsappFormatted()): ?>
                        <p><strong>WhatsApp:</strong> <?= Security::e(SiteInfo::whatsappFormatted()) ?></p>
                    <?php endif; ?>
                        <?php if (SiteInfo::address()): ?>
                        <p><strong>Alamat:</strong> <?= Security::e(SiteInfo::address()) ?></p>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';