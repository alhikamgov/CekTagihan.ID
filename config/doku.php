<?php
// Konfigurasi DOKU. Nilai diambil dari .env
$mode   = strtolower($_ENV['DOKU_MODE'] ?? 'sandbox'); // sandbox | production
$appUrl = rtrim($_ENV['APP_URL'] ?? 'https://cektagihan.id', '/');

return [
    'mode'       => $mode,
    'client_id'  => $_ENV['DOKU_CLIENT_ID']  ?? '',
    'secret_key' => $_ENV['DOKU_SECRET_KEY'] ?? '',
    'base_url'   => $mode === 'production'
        ? 'https://api.doku.com'
        : 'https://api-sandbox.doku.com',

    // Path (tanpa domain) endpoint notifikasi di aplikasi kita.
    // Dipakai sebagai Request-Target saat verifikasi signature.
    'notification_path' => $_ENV['DOKU_NOTIFICATION_PATH'] ?? '/webhook/doku',

    // Redirect payer setelah bayar (order_id ditambahkan otomatis)
    'callback_url' => $appUrl . '/portal/selesai',

    // Masa berlaku halaman bayar, dalam menit (default 24 jam)
    'payment_due_minutes' => (int) ($_ENV['DOKU_PAYMENT_DUE_MINUTES'] ?? 1440),

    // HANYA untuk sandbox: izinkan checkout tanpa sub-account
    // (berguna selama Sub Account belum disetujui DOKU).
    // Diabaikan otomatis jika mode = production.
    'allow_no_sub_account' => ($_ENV['DOKU_ALLOW_NO_SUBACCOUNT'] ?? '0') === '1',

    'timeout' => 30,
];