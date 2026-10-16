<?php
declare(strict_types=1);

namespace App\Helpers;

class Lang
{
    // =========================================
    // Istilah Umum
    // =========================================
    public const APP_NAME         = 'CekTagihan';
    public const APP_TAGLINE      = 'Bayar tagihan dari mana saja!';

    // =========================================
    // Istilah Pelanggan (dulu: Payer)
    // =========================================
    public const NOMOR_PELANGGAN    = 'Nomor Pelanggan';
    public const NOMOR_PELANGGAN_LC = 'nomor pelanggan';
    public const PELANGGAN          = 'Pelanggan';
    public const PELANGGAN_LC       = 'pelanggan';
    public const PELANGGAN_PLURAL   = 'Pelanggan';

    // Alias lama (biar tidak error kalau masih dipakai)
    public const PAYER    = 'Pelanggan';
    public const PAYER_LC = 'pelanggan';

    // =========================================
    // Istilah Tagihan (dulu: Invoice)
    // =========================================
    public const TAGIHAN    = 'Tagihan';
    public const TAGIHAN_LC = 'tagihan';
    public const INVOICE    = 'Tagihan';
    public const INVOICE_LC = 'tagihan';

    // =========================================
    // Istilah Tenant / Lokasi
    // =========================================
    public const TENANT       = 'Tenant';
    public const TENANT_LC    = 'tenant';
    public const LOKASI       = 'Lokasi';
    public const LOKASI_LC    = 'lokasi';
    public const PENGELOLA    = 'Pengelola';
    public const PENGELOLA_LC = 'pengelola';

    // =========================================
    // Istilah Admin
    // =========================================
    public const ADMIN        = 'Admin';
    public const ADMIN_TENANT = 'Admin Tenant';
    public const SUPERADMIN   = 'Superadmin';

    // =========================================
    // Istilah Status
    // =========================================
    public const STATUS_AKTIF     = 'Aktif';
    public const STATUS_NONAKTIF  = 'Nonaktif';
    public const STATUS_SUSPEND   = 'Suspend';
    public const STATUS_UNPAID    = 'Belum Bayar';
    public const STATUS_PAID      = 'Lunas';
    public const STATUS_EXPIRED   = 'Kedaluwarsa';
    public const STATUS_CANCELLED = 'Dibatalkan';

    // =========================================
    // Istilah Aksi
    // =========================================
    public const AKSI_TAMBAH  = 'Tambah';
    public const AKSI_EDIT    = 'Edit';
    public const AKSI_HAPUS   = 'Hapus';
    public const AKSI_SIMPAN  = 'Simpan';
    public const AKSI_BATAL   = 'Batal';
    public const AKSI_CARI    = 'Cari';
    public const AKSI_FILTER  = 'Filter';
    public const AKSI_RESET   = 'Reset';
    public const AKSI_DETAIL  = 'Detail';
    public const AKSI_KEMBALI = 'Kembali';

    // =========================================
    // Istilah Halaman
    // =========================================
    public const HALAMAN_SEBELUM = '← Sebelumnya';
    public const HALAMAN_BERIKUT = 'Berikutnya →';
    public const HALAMAN_DARI    = 'dari';

    // =========================================
    // Istilah Auth
    // =========================================
    public const EMAIL               = 'Email';
    public const PASSWORD            = 'Password';
    public const PASSWORD_LAMA       = 'Password Lama';
    public const PASSWORD_BARU       = 'Password Baru';
    public const KONFIRMASI_PASSWORD = 'Konfirmasi Password';
    public const LOGIN               = 'Masuk';
    public const LOGOUT              = 'Logout';
    public const LUPA_PASSWORD       = 'Lupa Password?';

    // =========================================
    // Istilah Lain
    // =========================================
    public const TELEPON   = 'Telepon';
    public const WHATSAPP  = 'WhatsApp';
    public const ALAMAT    = 'Alamat';
    public const NAMA      = 'Nama';
    public const JUMLAH    = 'Jumlah';
    public const TOTAL     = 'Total';
    public const PERIODE   = 'Periode';
    public const JENIS     = 'Jenis';
    public const DESKRIPSI = 'Deskripsi';
    public const JATUH_TEMPO = 'Jatuh Tempo';
    public const STATUS    = 'Status';
    public const AKSI      = 'Aksi';
}