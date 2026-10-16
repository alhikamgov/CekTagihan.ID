<?php
declare(strict_types=1);

namespace App\Services;

class WhatsAppService
{
    private string $token;
    private string $sender;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/whatsapp.php';
        $this->token  = $config['token'];
        $this->sender = $config['sender'];
    }

    /**
     * Format nomor HP ke format 628xxx.
     */
    public static function formatPhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Kirim pesan WA via Fonnte.
     */
    public function send(string $phone, string $message): array
    {
        $phone = self::formatPhone($phone);

        if ($phone === '') {
            return ['status' => false, 'reason' => 'Nomor kosong'];
        }

        if (empty($this->token)) {
            return ['status' => false, 'reason' => 'Token WA belum diisi di .env'];
        }

        $payload = [
            'target'      => $phone,
            'message'     => $message,
            'countryCode' => '62',
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $this->token,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['status' => false, 'reason' => 'cURL: ' . $curlError];
        }

        $result = json_decode($response, true);

        return [
            'status' => $httpCode === 200 && ($result['status'] ?? false) === true,
            'reason' => $result['reason'] ?? $result['detail'] ?? 'OK',
            'raw'    => $response,
        ];
    }

    /**
     * Kirim konfirmasi pembayaran sukses ke payer.
     */
    public function sendPaymentSuccess(
        string $phone,
        string $payerNama,
        string $tenantNama,
        float $total,
        array $invoices,
        string $orderId
    ): array {
        $invoiceLines = '';
        foreach ($invoices as $inv) {
            $invoiceLines .= "• " . ucfirst($inv['jenis']) . " " . $inv['periode']
                          . ": Rp " . number_format((float) $inv['jumlah'], 0, ',', '.') . "\n";
        }

        $message = "Halo *{$payerNama}*,\n\n"
                 . "Pembayaran Anda untuk *{$tenantNama}* berhasil. ✅\n\n"
                 . "Order ID: {$orderId}\n\n"
                 . "Rincian tagihan:\n{$invoiceLines}\n"
                 . "Total: *Rp " . number_format($total, 0, ',', '.') . "*\n\n"
                 . "Terima kasih.";

        return $this->send($phone, $message);
    }

    /**
     * Kirim notifikasi ke admin tenant.
     */
    public function sendAdminNotification(
        string $phone,
        string $adminNama,
        string $payerNama,
        string $nomorInduk,
        float $total,
        string $orderId
    ): array {
        $message = "Halo *{$adminNama}*,\n\n"
                 . "💰 *Pembayaran Baru Masuk*\n\n"
                 . "Nama: {$payerNama}\n"
                 . "No. Pelanggan: {$nomorInduk}\n"
                 . "Total: *Rp " . number_format($total, 0, ',', '.') . "*\n"
                 . "Order ID: {$orderId}\n\n"
                 . "Cek detail di dashboard.";

        return $this->send($phone, $message);
    }

    /**
     * Kirim reminder tagihan ke payer.
     */
    public function sendReminder(
        string $phone,
        string $payerNama,
        string $tenantNama,
        array $invoices,
        string $portalUrl
    ): array {
        $total = 0;
        $invoiceLines = '';
        foreach ($invoices as $inv) {
            $total += (float) $inv['jumlah'];
            $invoiceLines .= "• " . ucfirst($inv['jenis']) . " " . $inv['periode']
                          . ": Rp " . number_format((float) $inv['jumlah'], 0, ',', '.')
                          . " (jatuh tempo: " . date('d M Y', strtotime($inv['due_date'])) . ")\n";
        }

        $message = "Halo *{$payerNama}*,\n\n"
                 . "Anda memiliki tagihan di *{$tenantNama}* yang perlu dibayar:\n\n"
                 . "{$invoiceLines}\n"
                 . "Total: *Rp " . number_format($total, 0, ',', '.') . "*\n\n"
                 . "Bayar sekarang: {$portalUrl}\n\n"
                 . "Abaikan pesan ini jika sudah membayar.";

        return $this->send($phone, $message);
    }
}