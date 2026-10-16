<?php
use App\Helpers\Security;

$appName = $_ENV['APP_NAME'] ?? 'CekTagihan';

ob_start();
?>

<!-- ============ HERO ============ -->
<section class="relative overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-teal-50">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-emerald-200/40 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-32 -left-24 w-96 h-96 bg-teal-200/40 rounded-full blur-3xl"></div>
    <div class="absolute top-1/2 left-1/2 w-72 h-72 bg-amber-100/40 rounded-full blur-3xl"></div>

    <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            <div class="lg:col-span-7">
                <span class="inline-flex items-center gap-2 bg-emerald-600 text-white text-xs font-semibold px-3 py-1.5 rounded-full mb-5 shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-300 animate-pulse"></span>
                    Sewa bulanan. Uang transaksi 100% milik Anda.
                </span>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-[1.1] mb-6">
                    Pengelola seharusnya<br>
                    <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent">
                        tidak perlu jadi penagih.
                    </span>
                </h1>

                <p class="text-lg text-gray-700 leading-relaxed mb-8 max-w-xl">
                    Kelola ruko, kontrakan, apartemen, sekolah, atau iuran RT dari satu dashboard.
                    Pelanggan cek dan bayar sendiri. Anda tinggal terima beres.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <a href="/portal"
                       class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold px-7 py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition">
                        <i data-lucide="search" class="w-5 h-5"></i>
                        Cek tagihan saya
                    </a>
                    <a href="/daftar"
                       class="inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-gray-800 font-semibold px-7 py-3.5 rounded-xl border-2 border-emerald-200 hover:border-emerald-300 transition">
                        <i data-lucide="building-2" class="w-5 h-5 text-emerald-600"></i>
                        Saya pengelola
                    </a>
                </div>

                <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                        Tanpa potongan transaksi
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                        Uang langsung ke rekening Anda
                    </div>
                    <div class="flex items-center gap-1.5">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-500"></i>
                        Sewa bulanan atau tahunan
                    </div>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-3xl blur-2xl opacity-30"></div>

                    <div class="relative bg-white rounded-3xl shadow-2xl border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs text-gray-500">Tagihan Anda</p>
                                <p class="font-bold text-gray-800">RW 007 Griya Asri</p>
                            </div>
                            <span class="text-xs bg-amber-100 text-amber-700 font-medium px-2.5 py-1 rounded-full">
                                2 belum bayar
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">
                                        <i data-lucide="check" class="w-4 h-4 text-white"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Iuran Keamanan</p>
                                        <p class="text-xs text-gray-500">Januari 2026</p>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-emerald-700">Rp100.000</p>
                            </div>

                            <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50 border border-amber-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center">
                                        <i data-lucide="clock" class="w-4 h-4 text-white"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">Iuran Kebersihan</p>
                                        <p class="text-xs text-gray-500">Januari 2026</p>
                                    </div>
                                </div>
                                <p class="text-sm font-semibold text-amber-700">Rp50.000</p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                            <p class="text-xs text-gray-500">Total dipilih</p>
                            <p class="font-bold text-gray-800">Rp150.000</p>
                        </div>

                        <button class="w-full mt-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold py-2.5 rounded-xl text-sm">
                            Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============ UNTUK SIAPA ============ -->
<section class="py-16 md:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">

        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="inline-block text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full mb-3">
                Kategori pengguna
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                Biasanya dipakai oleh
            </h2>
            <p class="text-gray-600">
                Kalau Anda punya pelanggan tetap yang harus bayar rutin, kemungkinan besar cocok.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <div class="group bg-gradient-to-br from-emerald-50 to-white rounded-2xl p-6 border border-emerald-100 hover:border-emerald-300 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center mb-4 shadow-md shadow-emerald-200 group-hover:scale-110 transition">
                    <i data-lucide="store" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Ruko & Kontrakan</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Sewa, IPL, listrik, air. Daripada chat satu-satu, arahkan penyewa ke satu link.
                </p>
            </div>

            <div class="group bg-gradient-to-br from-blue-50 to-white rounded-2xl p-6 border border-blue-100 hover:border-blue-300 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center mb-4 shadow-md shadow-blue-200 group-hover:scale-110 transition">
                    <i data-lucide="building-2" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Apartemen & Rusun</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Service charge, parkir, kebersihan. Untuk ratusan unit sekaligus.
                </p>
            </div>

            <div class="group bg-gradient-to-br from-amber-50 to-white rounded-2xl p-6 border border-amber-100 hover:border-amber-300 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center mb-4 shadow-md shadow-amber-200 group-hover:scale-110 transition">
                    <i data-lucide="graduation-cap" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Sekolah & Pesantren</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    SPP, uang gedung, seragam. Orang tua bisa bayar dari HP sendiri.
                </p>
            </div>

            <div class="group bg-gradient-to-br from-rose-50 to-white rounded-2xl p-6 border border-rose-100 hover:border-rose-300 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-pink-500 flex items-center justify-center mb-4 shadow-md shadow-rose-200 group-hover:scale-110 transition">
                    <i data-lucide="home" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">RT/RW & Komunitas</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Iuran keamanan, kebersihan, kas. Warga tinggal cek dan bayar.
                </p>
            </div>

            <div class="group bg-gradient-to-br from-violet-50 to-white rounded-2xl p-6 border border-violet-100 hover:border-violet-300 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-purple-500 flex items-center justify-center mb-4 shadow-md shadow-violet-200 group-hover:scale-110 transition">
                    <i data-lucide="users" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Koperasi & BUMDes</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Simpanan wajib, iuran anggota, angsuran pinjaman. Tercatat rapi.
                </p>
            </div>

            <div class="group bg-gradient-to-br from-teal-50 to-white rounded-2xl p-6 border border-teal-100 hover:border-teal-300 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-cyan-500 flex items-center justify-center mb-4 shadow-md shadow-teal-200 group-hover:scale-110 transition">
                    <i data-lucide="heart-handshake" class="w-6 h-6 text-white"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Tempat Ibadah</h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Infaq rutin, iuran pembangunan, sewa aula. Tercatat transparan.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- ============ CARA KERJA ============ -->
<section class="py-16 md:py-20 bg-gradient-to-b from-gray-50 to-white border-y border-gray-100">
    <div class="max-w-5xl mx-auto px-4">

        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="inline-block text-xs font-semibold text-teal-700 bg-teal-50 px-3 py-1 rounded-full mb-3">
                Cara pakai
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                Tiga langkah, selesai
            </h2>
            <p class="text-gray-600">
                Untuk pembayar. Tidak perlu install, tidak perlu bikin akun.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">

            <div class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-gradient-to-r from-emerald-300 via-teal-300 to-cyan-300"></div>

            <div class="relative text-center">
                <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-2xl font-extrabold shadow-xl shadow-emerald-200 mb-5 relative z-10">
                    1
                </div>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Pilih pengelola</h3>
                <p class="text-sm text-gray-600 leading-relaxed px-4">
                    Ketik nama lokasi, pilih dari daftar. Atau klik link/QR dari pengelola Anda.
                </p>
            </div>

            <div class="relative text-center">
                <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center text-white text-2xl font-extrabold shadow-xl shadow-teal-200 mb-5 relative z-10">
                    2
                </div>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Masukkan nomor</h3>
                <p class="text-sm text-gray-600 leading-relaxed px-4">
                    NIM, nomor rumah, nomor kios — apa pun yang pengelola tetapkan.
                </p>
            </div>

            <div class="relative text-center">
                <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white text-2xl font-extrabold shadow-xl shadow-cyan-200 mb-5 relative z-10">
                    3
                </div>
                <h3 class="font-bold text-lg text-gray-900 mb-2">Pilih & bayar</h3>
                <p class="text-sm text-gray-600 leading-relaxed px-4">
                    Centang tagihan (bisa sekaligus), pilih metode, selesai. Bukti masuk ke WhatsApp.
                </p>
            </div>

        </div>

        <div class="text-center mt-12">
            <a href="/portal"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold px-7 py-3.5 rounded-xl shadow-lg shadow-emerald-200 transition">
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
                Coba sekarang
            </a>
        </div>

    </div>
</section>

<!-- ============ PRICING (BAGIAN BARU) ============ -->
<section id="harga" class="py-16 md:py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4">

        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="inline-block text-xs font-semibold text-amber-700 bg-amber-50 px-3 py-1 rounded-full mb-3">
                Harga
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                Sewa. Bukan komisi.
            </h2>
            <p class="text-gray-600">
                Kami tidak ambil potongan dari pembayaran pelanggan Anda.
                Uang yang masuk, 100% menjadi milik Anda. Kami hanya menagih biaya sewa bulanan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">

            <!-- Bulanan -->
            <div class="relative bg-white rounded-3xl border-2 border-gray-200 p-8 hover:border-emerald-300 hover:shadow-xl transition">
                <div class="mb-6">
                    <h3 class="font-bold text-xl text-gray-900 mb-1">Bulanan</h3>
                    <p class="text-sm text-gray-500">Fleksibel, berhenti kapan saja</p>
                </div>

                <div class="mb-6">
                    <span class="text-4xl font-extrabold text-gray-900">Rp200rb</span>
                    <span class="text-gray-500">/bulan</span>
                </div>

                <ul class="space-y-3 mb-8 text-sm">
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                        <span class="text-gray-700">Unlimited pelanggan & tagihan</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                        <span class="text-gray-700">Portal pembayaran untuk pelanggan</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                        <span class="text-gray-700">Reminder WhatsApp otomatis</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                        <span class="text-gray-700">Pencairan saldo ke rekening Anda</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-500 flex-shrink-0"></i>
                        <span class="text-gray-700">Support via WhatsApp & email</span>
                    </li>
                </ul>

                <a href="/daftar?plan=monthly"
                   class="block text-center bg-white hover:bg-gray-50 text-gray-800 font-semibold py-3 rounded-xl border-2 border-gray-200 hover:border-emerald-300 transition">
                    Mulai bulanan
                </a>
            </div>

            <!-- Tahunan -->
            <div class="relative bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-8 text-white shadow-2xl shadow-emerald-300 transform md:-translate-y-2">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                    <span class="bg-amber-400 text-amber-900 text-xs font-bold px-3 py-1 rounded-full shadow-md">
                        HEMAT 2 BULAN
                    </span>
                </div>

                <div class="mb-6 mt-2">
                    <h3 class="font-bold text-xl mb-1">Tahunan</h3>
                    <p class="text-sm text-emerald-100">Lebih hemat, tidak perlu perpanjang tiap bulan</p>
                </div>

                <div class="mb-2">
                    <span class="text-4xl font-extrabold">Rp2jt</span>
                    <span class="text-emerald-100">/tahun</span>
                </div>
                <p class="text-sm text-emerald-100 mb-6">
                    <span class="line-through opacity-75">Rp2.400.000</span>
                    · hemat Rp400.000
                </p>

                <ul class="space-y-3 mb-8 text-sm">
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-200 flex-shrink-0"></i>
                        <span class="text-white">Semua fitur paket Bulanan</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-200 flex-shrink-0"></i>
                        <span class="text-white">Prioritas support</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-200 flex-shrink-0"></i>
                        <span class="text-white">Onboarding & training tim Anda</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-200 flex-shrink-0"></i>
                        <span class="text-white">Free kustomisasi 1x</span>
                    </li>
                    <li class="flex gap-2.5">
                        <i data-lucide="check" class="w-5 h-5 text-emerald-200 flex-shrink-0"></i>
                        <span class="text-white">Laporan custom sesuai kebutuhan</span>
                    </li>
                </ul>

                <a href="/daftar?plan=yearly"
                   class="block text-center bg-white text-emerald-700 font-bold py-3 rounded-xl hover:bg-emerald-50 transition shadow-lg">
                    Mulai tahunan
                </a>
            </div>

        </div>

        <div class="mt-10 max-w-3xl mx-auto">
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-400 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="info" class="w-5 h-5 text-white"></i>
                </div>
                <div class="text-sm text-amber-900 leading-relaxed">
                    <p class="font-semibold mb-1">Tentang biaya transaksi</p>
                    <p>
                        Biaya transaksi dari payment gateway (QRIS, VA, e-wallet) ditanggung
                        oleh masing-masing pengelola — bukan oleh kami. Besarannya bervariasi
                        tergantung metode, biasanya 0,7% s.d 2% per transaksi.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-8 max-w-3xl mx-auto">
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="gift" class="w-5 h-5 text-white"></i>
                </div>
                <div class="text-sm text-emerald-900 leading-relaxed">
                    <p class="font-semibold mb-1">Uji coba gratis 30 hari</p>
                    <p>
                        Semua tenant baru dapat masa uji coba 30 hari. Tidak perlu bayar sewa
                        sampai Anda yakin sistem ini cocok. Berhenti kapan saja, tidak ada
                        penalti.
                    </p>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ============ FITUR UNGGULAN ============ -->
<section class="py-16 md:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            <div class="lg:col-span-5">
                <span class="inline-block text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full mb-4">
                    Fitur
                </span>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-5 leading-tight">
                    Yang bikin beda dari cara lama
                </h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                    Bukan cuma cek tagihan. Ada beberapa hal yang biasanya menghemat waktu
                    pengelola paling banyak.
                </p>
                <a href="/daftar" class="inline-flex items-center gap-2 text-emerald-700 hover:text-emerald-800 font-semibold">
                    Lihat selengkapnya →
                </a>
            </div>

            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-200">
                    <i data-lucide="wallet" class="w-7 h-7 mb-3"></i>
                    <h3 class="font-bold mb-1">Tanpa potongan</h3>
                    <p class="text-sm text-white/90 leading-relaxed">
                        Pembayaran pelanggan masuk 100% ke saldo Anda.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white border-2 border-emerald-100 hover:border-emerald-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mb-3">
                        <i data-lucide="layers" class="w-5 h-5 text-emerald-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Bayar sekaligus</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Sewa + listrik + air + SPP dalam satu transaksi.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white border-2 border-amber-100 hover:border-amber-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center mb-3">
                        <i data-lucide="bell" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Reminder WhatsApp</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Otomatis dikirim sebelum jatuh tempo.
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-white border-2 border-blue-100 hover:border-blue-300 transition">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-3">
                        <i data-lucide="shield-check" class="w-5 h-5 text-blue-600"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-1">Aman & tercatat</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Gateway berlisensi BI. Status update otomatis.
                    </p>
                </div>

            </div>
        </div>

    </div>
</section>

<!-- ============ UNTUK PENGELOLA ============ -->
<section class="py-16 md:py-24 bg-gray-900 relative overflow-hidden">
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-teal-500/20 rounded-full blur-3xl"></div>

    <div class="relative max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            <div class="lg:col-span-7 text-white">
                <span class="inline-block text-xs font-semibold text-emerald-300 bg-emerald-500/20 px-3 py-1 rounded-full mb-4">
                    Untuk pengelola
                </span>

                <h2 class="text-3xl md:text-4xl font-bold mb-5 leading-tight">
                    Berhenti nagih manual.<br>
                    <span class="text-emerald-400">Mulai kelola dengan tenang.</span>
                </h2>

                <p class="text-gray-300 leading-relaxed mb-8 max-w-xl">
                    Yang paling sering kami dengar: "sudah transfer, tapi lupa dicatat"
                    atau "yang telat bayar malah saya yang ngejar". Sistem ini dibuat
                    untuk mengurangi dua hal itu.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                        </div>
                        <p class="text-sm text-gray-200 leading-relaxed">
                            Buat tagihan massal untuk semua pelanggan
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                        </div>
                        <p class="text-sm text-gray-200 leading-relaxed">
                            Lihat siapa yang belum bayar real-time
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                        </div>
                        <p class="text-sm text-gray-200 leading-relaxed">
                            Uang pembayaran masuk utuh ke rekening Anda
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="check" class="w-4 h-4 text-emerald-400"></i>
                        </div>
                        <p class="text-sm text-gray-200 leading-relaxed">
                            Laporan otomatis, tinggal export Excel
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="/daftar"
                       class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white font-semibold px-6 py-3 rounded-xl shadow-lg transition">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        Coba gratis 30 hari
                    </a>
                    <a href="mailto:hello@cektagihan.id?subject=Mau%20pakai%20CekTagihan"
                       class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-medium px-6 py-3 rounded-xl border border-white/20 transition">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                        Tanya dulu
                    </a>
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="bg-white/5 backdrop-blur border border-white/10 rounded-3xl p-6 space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Sudah dipakai di</p>
                            <p class="text-xl font-bold text-white"><?= count($tenants ?? []) ?> lokasi</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                            <i data-lucide="users" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Bisa untuk</p>
                            <p class="text-xl font-bold text-white">Banyak pengelola</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Metode pembayaran</p>
                            <p class="text-xl font-bold text-white">QRIS, VA, e-wallet</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============ FAQ ============ -->
<section class="py-16 md:py-20 bg-gradient-to-b from-white to-emerald-50/40">
    <div class="max-w-3xl mx-auto px-4">

        <div class="text-center mb-12">
            <span class="inline-block text-xs font-semibold text-emerald-700 bg-white border border-emerald-200 px-3 py-1 rounded-full mb-3">
                FAQ
            </span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900">
                Pertanyaan yang sering muncul
            </h2>
        </div>

        <div class="space-y-3">

            <details class="group bg-white rounded-2xl border-2 border-gray-100 hover:border-emerald-200 overflow-hidden transition">
                <summary class="flex justify-between items-center cursor-pointer p-5">
                    <span class="font-semibold text-gray-800">Betul tidak ada potongan dari pembayaran?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-emerald-500 group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                    Betul. Uang yang dibayar pelanggan masuk utuh ke saldo akun Anda.
                    Kami tidak mengambil persentase. Satu-satunya yang dipotong adalah biaya
                    payment gateway (QRIS, VA, e-wallet) yang dibebankan langsung oleh pihak
                    gateway, bukan oleh kami.
                </div>
            </details>

            <details class="group bg-white rounded-2xl border-2 border-gray-100 hover:border-emerald-200 overflow-hidden transition">
                <summary class="flex justify-between items-center cursor-pointer p-5">
                    <span class="font-semibold text-gray-800">Berapa lama uji coba gratis?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-emerald-500 group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                    30 hari sejak akun diaktifkan. Selama masa itu, semua fitur terbuka.
                    Kami tidak minta kartu kredit atau komitmen apa pun. Kalau tidak cocok,
                    tinggal berhenti.
                </div>
            </details>

            <details class="group bg-white rounded-2xl border-2 border-gray-100 hover:border-emerald-200 overflow-hidden transition">
                <summary class="flex justify-between items-center cursor-pointer p-5">
                    <span class="font-semibold text-gray-800">Bagaimana kalau saya lupa bayar sewa?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-emerald-500 group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                    Ada masa tenggang 7 hari setelah tanggal jatuh tempo. Selama masa itu,
                    sistem tetap berjalan. Kalau lewat masa tenggang, dashboard admin akan
                    dibatasi sampai Anda perpanjang. Data Anda tidak akan dihapus.
                </div>
            </details>

            <details class="group bg-white rounded-2xl border-2 border-gray-100 hover:border-emerald-200 overflow-hidden transition">
                <summary class="flex justify-between items-center cursor-pointer p-5">
                    <span class="font-semibold text-gray-800">Pelanggan saya tidak terbiasa online, bisa?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-emerald-500 group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                    Bisa. Selain bayar lewat portal, Anda tetap bisa mencatat pembayaran tunai
                    dari dashboard admin. Jadi tidak ada yang tertinggal.
                </div>
            </details>

            <details class="group bg-white rounded-2xl border-2 border-gray-100 hover:border-emerald-200 overflow-hidden transition">
                <summary class="flex justify-between items-center cursor-pointer p-5">
                    <span class="font-semibold text-gray-800">Berapa lama uang masuk ke rekening kami?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-emerald-500 group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                    Pembayaran online settle di akun platform dalam 1 hari kerja.
                    Pencairan ke rekening Anda diproses tim kami, biasanya 1×24 jam kerja
                    setelah disetujui.
                </div>
            </details>

            <details class="group bg-white rounded-2xl border-2 border-gray-100 hover:border-emerald-200 overflow-hidden transition">
                <summary class="flex justify-between items-center cursor-pointer p-5">
                    <span class="font-semibold text-gray-800">Data kami aman?</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-emerald-500 group-open:rotate-180 transition"></i>
                </summary>
                <div class="px-5 pb-5 text-sm text-gray-600 leading-relaxed">
                    Kami pakai payment gateway berlisensi Bank Indonesia (DOKU) untuk semua
                    transaksi. Data pribadi disimpan terenkripsi dan tidak dibagikan ke pihak ketiga.
                    Detail di <a href="/kebijakan-privasi" class="text-emerald-700 underline">kebijakan privasi</a>.
                </div>
            </details>

        </div>
    </div>
</section>

<!-- ============ CTA AKHIR ============ -->
<section class="py-16 md:py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4">
        <div class="relative bg-gradient-to-br from-emerald-600 via-teal-600 to-cyan-700 rounded-3xl p-8 md:p-12 text-center overflow-hidden">
            <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-16 -left-16 w-64 h-64 bg-amber-300/20 rounded-full blur-3xl"></div>

            <div class="relative">
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">
                    Coba gratis 30 hari. Tidak ada komitmen.
                </h2>
                <p class="text-white/90 mb-8 max-w-xl mx-auto">
                    Buat instansi Anda, unggah beberapa pelanggan, buat satu jenis tagihan.
                    Kalau ternyata tidak cocok, tinggal berhenti.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/daftar"
                       class="inline-flex items-center justify-center gap-2 bg-white text-emerald-700 font-semibold px-6 py-3 rounded-xl hover:bg-emerald-50 transition shadow-lg">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                        Daftar sebagai pengelola
                    </a>
                    <a href="/portal"
                       class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white font-semibold px-6 py-3 rounded-xl border border-white/30 transition">
                        <i data-lucide="search" class="w-5 h-5"></i>
                        Cek tagihan saya
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';