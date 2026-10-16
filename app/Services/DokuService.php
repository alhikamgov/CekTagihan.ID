<?php
declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Client DOKU (Non-SNAP): Checkout API, Check Status, verifikasi notifikasi.
 *
 * Signature: "HMACSHA256=" . base64(HMAC-SHA256(secret, komponen))
 * Komponen (dipisah "\n"):
 *   Client-Id:..., Request-Id:..., Request-Timestamp:..., Request-Target:...
 *   [Digest:base64(sha256(body))]  <- hanya jika request punya body
 */
class DokuService
{
    private string $clientId;
    private string $secretKey;
    private string $baseUrl;
    private string $mode;
    private string $notificationPath;
    private string $callbackUrl;
    private int $dueMinutes;
    private int $timeout;
    private bool $allowNoSubAccount;

    public function __construct(?array $config = null)
    {
        $config ??= require __DIR__ . '/../../config/doku.php';

        $this->clientId  = (string) ($config['client_id']  ?? '');
        $this->secretKey = (string) ($config['secret_key'] ?? '');

        if ($this->clientId === '' || $this->secretKey === '') {
            throw new \RuntimeException('DOKU_CLIENT_ID / DOKU_SECRET_KEY belum diisi di .env');
        }

        $this->mode             = (string) ($config['mode'] ?? 'sandbox');
        $this->baseUrl          = rtrim((string) ($config['base_url'] ?? 'https://api-sandbox.doku.com'), '/');
        $this->notificationPath = (string) ($config['notification_path'] ?? '/webhook/doku');
        $this->callbackUrl      = (string) ($config['callback_url'] ?? '');
        $this->dueMinutes       = max(1, (int) ($config['payment_due_minutes'] ?? 1440));
        $this->timeout          = (int) ($config['timeout'] ?? 30);

        // Guard: tanpa sub-account hanya boleh di luar production
        $this->allowNoSubAccount = !empty($config['allow_no_sub_account']) && $this->mode !== 'production';
    }

    // ------------------------------------------------------------------
    // SIGNATURE
    // ------------------------------------------------------------------

    public function buildSignature(
        string $clientId,
        string $requestId,
        string $timestamp,
        string $requestTarget,
        ?string $body = null
    ): string {
        $components = "Client-Id:{$clientId}\n"
                    . "Request-Id:{$requestId}\n"
                    . "Request-Timestamp:{$timestamp}\n"
                    . "Request-Target:{$requestTarget}";

        if ($body !== null && $body !== '') {
            $digest = base64_encode(hash('sha256', $body, true));
            $components .= "\nDigest:{$digest}";
        }

        return 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $components, $this->secretKey, true));
    }

    /**
     * Ambil header DOKU dari $_SERVER (atau array serupa).
     */
    public static function headersFromServer(array $server): array
    {
        return [
            'client_id'  => (string) ($server['HTTP_CLIENT_ID']         ?? ''),
            'request_id' => (string) ($server['HTTP_REQUEST_ID']        ?? ''),
            'timestamp'  => (string) ($server['HTTP_REQUEST_TIMESTAMP'] ?? ''),
            'signature'  => (string) ($server['HTTP_SIGNATURE']         ?? ''),
        ];
    }

    /**
     * Verifikasi notifikasi dari DOKU.
     * $headers = hasil headersFromServer(); $rawBody = isi php://input MENTAH
     * (jangan json_decode lalu encode ulang, digest akan berbeda).
     */
    public function verifyNotification(array $headers, string $rawBody, ?string $requestTarget = null): bool
    {
        $clientId  = (string) ($headers['client_id']  ?? '');
        $requestId = (string) ($headers['request_id'] ?? '');
        $timestamp = (string) ($headers['timestamp']  ?? '');
        $signature = (string) ($headers['signature']  ?? '');

        if ($clientId === '' || $requestId === '' || $timestamp === '' || $signature === '') {
            return false;
        }

        if (!hash_equals($this->clientId, $clientId)) {
            return false;
        }

        $expected = $this->buildSignature(
            $clientId,
            $requestId,
            $timestamp,
            $requestTarget ?? $this->notificationPath,
            $rawBody
        );

        return hash_equals($expected, $signature);
    }

    // ------------------------------------------------------------------
    // CHECKOUT
    // ------------------------------------------------------------------

    /**
     * Bangun body request Checkout (tanpa memanggil API). Public agar bisa dites offline.
     *
     * @param array $customer ['id','name','email','phone'] (opsional)
     * @param array $items    [['name'=>..,'quantity'=>..,'price'=>..], ...] (opsional)
     */
    public function buildCheckoutPayload(
        string $invoiceNumber,
        int $amount,
        ?string $subAccountId,
        ?string $splitRuleId,
        array $customer = [],
        array $items = []
    ): array {
        if (!preg_match('/^[A-Za-z0-9._\-]{1,30}$/', $invoiceNumber)) {
            throw new \InvalidArgumentException(
                'invoice_number harus 1-30 karakter (huruf/angka/. _ -). Batas 30 karena channel kartu kredit.'
            );
        }

        if ($amount < 1 || $amount > 999999999999) {
            throw new \InvalidArgumentException('amount harus antara 1 dan 999.999.999.999 (IDR, tanpa desimal).');
        }

        $payload = [
            'order' => [
                'amount'         => $amount,
                'invoice_number' => $invoiceNumber,
            ],
            'payment' => [
                'payment_due_date' => $this->dueMinutes,
            ],
        ];

        if ($this->callbackUrl !== '') {
            $payload['order']['callback_url'] = $this->callbackUrl . '?order_id=' . urlencode($invoiceNumber);
        }

        // line_items hanya dikirim jika total harga == amount (hindari ditolak karena tidak cocok)
        if (!empty($items)) {
            $lineItems = [];
            $sum = 0;
            foreach ($items as $item) {
                $price = (int) ($item['price'] ?? 0);
                $qty   = max(1, (int) ($item['quantity'] ?? 1));
                $name  = mb_substr(trim((string) ($item['name'] ?? 'Tagihan')), 0, 100);
                $lineItems[] = ['name' => $name, 'quantity' => $qty, 'price' => $price];
                $sum += $price * $qty;
            }
            if ($sum === $amount) {
                $payload['order']['line_items'] = $lineItems;
            }
        }

        $cust = [];
        foreach (['id', 'name', 'email', 'phone'] as $key) {
            if (!empty($customer[$key])) {
                $cust[$key] = (string) $customer[$key];
            }
        }
        if ($cust) {
            $payload['customer'] = $cust;
        }

        // Routing ke sub-account (Collect & Route)
        if ($subAccountId !== null && $subAccountId !== '') {
            if (!self::isValidSubAccountId($subAccountId)) {
                throw new \InvalidArgumentException(
                    'Format sub-account ID tidak valid (harus seperti SAC-1234-1234567890123). '
                    . 'DOKU tidak memberi error untuk ID salah, jadi divalidasi di sini.'
                );
            }
            $account = ['id' => $subAccountId];

            if ($splitRuleId !== null && $splitRuleId !== '') {
                if (!preg_match('/^[A-Za-z0-9_\-]{8,60}$/', $splitRuleId)) {
                    throw new \InvalidArgumentException('Format split_rule_id tidak valid.');
                }
                $account['split_rule_id'] = $splitRuleId;
            }

            $payload['additional_info'] = ['account' => $account];
        } elseif (!$this->allowNoSubAccount) {
            throw new \InvalidArgumentException(
                'Sub-account tenant wajib diisi. Tanpa sub-account, dana masuk ke rekening utama platform.'
            );
        }

        return $payload;
    }

    /**
     * Buat halaman pembayaran DOKU Checkout.
     *
     * @return array ['payment_url','token_id','session_id','expires_at','request_id','raw']
     */
    public function createCheckout(
        string $invoiceNumber,
        int $amount,
        ?string $subAccountId,
        ?string $splitRuleId,
        array $customer = [],
        array $items = []
    ): array {
        $payload = $this->buildCheckoutPayload(
            $invoiceNumber, $amount, $subAccountId, $splitRuleId, $customer, $items
        );

        $res = $this->request('POST', '/checkout/v1/payment', $payload);
        $response = $res['body']['response'] ?? [];
        $url = $response['payment']['url'] ?? null;

        if (!$url) {
            $msg = $this->extractMessage($res['body']);
            throw new \RuntimeException('DOKU tidak mengembalikan payment.url. ' . $msg);
        }

        return [
            'payment_url' => (string) $url,
            'token_id'    => (string) ($response['payment']['token_id'] ?? ''),
            'session_id'  => (string) ($response['order']['session_id'] ?? ''),
            'expires_at'  => self::parseExpiry($response['payment']['expired_date'] ?? null),
            'request_id'  => $res['request_id'],
            'raw'         => $res['body'],
        ];
    }

    /**
     * Cek status transaksi ke DOKU (untuk rekonsiliasi / fallback webhook).
     */
    public function getStatus(string $invoiceNumber): array
    {
        $res = $this->request('GET', '/orders/v1/status/' . rawurlencode($invoiceNumber));
        return $res['body'];
    }

    // ------------------------------------------------------------------
    // HELPER
    // ------------------------------------------------------------------

    public static function isValidSubAccountId(string $id): bool
    {
        return (bool) preg_match('/^SAC-[A-Za-z0-9]+-[A-Za-z0-9]+$/', $id);
    }

    /**
     * expired_date DOKU = "yyyyMMddHHmmss" dalam UTC+7 -> "Y-m-d H:i:s" (WIB).
     */
    public static function parseExpiry(?string $expired): ?string
    {
        if (!$expired || !preg_match('/^\d{14}$/', $expired)) {
            return null;
        }
        $dt = DateTimeImmutable::createFromFormat('YmdHis', $expired, new DateTimeZone('Asia/Jakarta'));
        return $dt ? $dt->format('Y-m-d H:i:s') : null;
    }

    private function extractMessage(array $body): string
    {
        $m = $body['message'] ?? null;
        if (is_array($m)) {
            return implode('; ', array_map('strval', $m));
        }
        return is_string($m) ? $m : '';
    }

    private function uuidV4(): string
    {
        $d = random_bytes(16);
        $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
        $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
    }

    /**
     * @return array ['http_status'=>int,'body'=>array,'request_id'=>string]
     */
    private function request(string $method, string $path, ?array $payload = null): array
    {
        $requestId = $this->uuidV4();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $body = $payload !== null
            ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            : null;

        $signature = $this->buildSignature($this->clientId, $requestId, $timestamp, $path, $body);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->baseUrl . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Client-Id: ' . $this->clientId,
                'Request-Id: ' . $requestId,
                'Request-Timestamp: ' . $timestamp,
                'Signature: ' . $signature,
            ],
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw       = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError !== '') {
            throw new \RuntimeException('cURL Error: ' . $curlError);
        }

        $decoded = json_decode((string) $raw, true);
        $decoded = is_array($decoded) ? $decoded : [];

        if ($httpCode >= 400) {
            $msg = $this->extractMessage($decoded) ?: 'Unknown DOKU error';
            throw new \RuntimeException("DOKU Error ({$httpCode}): {$msg}");
        }

        return ['http_status' => $httpCode, 'body' => $decoded, 'request_id' => $requestId];
    }
}