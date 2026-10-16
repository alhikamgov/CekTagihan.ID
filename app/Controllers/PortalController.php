<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\RateLimiter;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Lang;
use App\Models\Invoice;
use App\Models\Payer;
use App\Models\Tenant;
use App\Models\Payment;
use App\Services\DokuService;

class PortalController
{
    /**
     * Landing portal — pilih tenant + input Nomor Pelanggan.
     */
    public function index(): void
    {
        if (!empty($_SESSION['portal_payer_id']) && !empty($_SESSION['portal_tenant_id'])) {
            Response::redirect('/portal/tagihan');
        }

        $tenants = Tenant::allActive();

        if (empty($tenants)) {
            Response::view('portal/no-tenant', [
                'title' => 'Cek Tagihan',
            ]);
            return;
        }

        Response::view('portal/index', [
            'title'   => 'Cek Tagihan',
            'tenants' => $tenants,
            'csrf'    => Security::csrfToken(),
            'error'   => $_SESSION['portal_error'] ?? null,
            'old'     => $_SESSION['portal_old'] ?? [],
        ]);

        unset($_SESSION['portal_error'], $_SESSION['portal_old']);
    }

    /**
 * Portal per tenant (via slug di URL).
 * Contoh: /portal/universitas-panca-sakti-bekasi
 */
public function tenantPortal(string $slug): void
{
    // Cari tenant by slug
    $tenant = Tenant::findBySlug($slug);

    // Kalau tidak ditemukan atau tidak aktif → redirect ke /portal
    if (!$tenant || $tenant['status'] !== 'active') {
        $_SESSION['portal_error'] = 'Lokasi tidak ditemukan. Silakan pilih dari daftar.';
        Response::redirect('/portal');
    }

    // Ambil semua tenant aktif (untuk dropdown cadangan)
    $tenants = Tenant::allActive();

    // Prefill tenant ID untuk auto-pilih
    $prefillTenantId = (int) $tenant['id'];

    Response::view('portal/index', [
        'title'           => 'Cek Tagihan — ' . $tenant['nama'],
        'tenants'         => $tenants,
        'prefillTenantId' => $prefillTenantId,
        'prefillTenantNama' => $tenant['nama'],
        'csrf'            => Security::csrfToken(),
        'error'           => $_SESSION['portal_error'] ?? null,
        'old'             => $_SESSION['portal_old'] ?? [],
    ]);

    unset($_SESSION['portal_error'], $_SESSION['portal_old']);
}

    /**
     * Proses cek tagihan.
     */
    public function cek(): void
    {
        if (!RateLimiter::check('portal_cek', 10, 60)) {
            $_SESSION['portal_error'] = 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.';
            Response::redirect('/portal');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['portal_error'] = 'Sesi tidak valid. Coba lagi.';
            Response::redirect('/portal');
        }

        $tenantId   = (int) ($_POST['tenant_id'] ?? 0);
        $nomorInduk = trim($_POST['nomor_induk'] ?? '');

        if ($tenantId <= 0 || $nomorInduk === '') {
            $_SESSION['portal_error'] = 'Pilih lokasi dan isi Nomor Pelanggan.';
            $_SESSION['portal_old']   = ['tenant_id' => $tenantId, 'nomor_induk' => $nomorInduk];
            Response::redirect('/portal');
        }

        $tenant = Tenant::findById($tenantId);
        if (!$tenant || $tenant['status'] !== 'active') {
            $_SESSION['portal_error'] = 'Lokasi tidak ditemukan atau tidak aktif.';
            Response::redirect('/portal');
        }

        $db = Payer::db();
        $stmt = $db->prepare(
            "SELECT * FROM payers
             WHERE tenant_id = ? AND nomor_induk = ? AND status = 'active'
             LIMIT 1"
        );
        $stmt->execute([$tenantId, $nomorInduk]);
        $payer = $stmt->fetch();

        if (!$payer) {
            $_SESSION['portal_error'] = 'Nomor Pelanggan tidak ditemukan.';
            $_SESSION['portal_old']   = ['tenant_id' => $tenantId, 'nomor_induk' => $nomorInduk];
            Response::redirect('/portal');
        }

        $_SESSION['portal_payer_id']       = (int) $payer['id'];
        $_SESSION['portal_payer_nama']     = $payer['nama'];
        $_SESSION['portal_payer_induk']    = $payer['nomor_induk'];
        $_SESSION['portal_payer_whatsapp'] = $payer['whatsapp'] ?? null;
        $_SESSION['portal_tenant_id']      = (int) $tenant['id'];
        $_SESSION['portal_tenant_nama']    = $tenant['nama'];
        $_SESSION['portal_tenant_slug']    = $tenant['slug'];

        Response::redirect('/portal/tagihan');
    }

    /**
     * Halaman tagihan.
     */
    public function tagihan(): void
{
    if (empty($_SESSION['portal_payer_id'])) {
        Response::redirect('/portal');
    }

    $tenantId = (int) $_SESSION['portal_tenant_id'];
    $payerId  = (int) $_SESSION['portal_payer_id'];

    $db = Invoice::db();
    $stmt = $db->prepare(
        "SELECT *,
                (jumlah - COALESCE(paid_amount, 0)) AS remaining
         FROM invoices
         WHERE tenant_id = ?
           AND payer_id = ?
           AND status = 'unpaid'
           AND (jumlah - COALESCE(paid_amount, 0)) > 0
         ORDER BY due_date ASC"
    );
    $stmt->execute([$tenantId, $payerId]);
    $invoices = $stmt->fetchAll();

    $riwayatSummary = Invoice::summaryRiwayat($tenantId, $payerId);

    Response::view('portal/tagihan', [
        'title'          => 'Tagihan Saya',
        'payerNama'      => $_SESSION['portal_payer_nama'],
        'payerInduk'     => $_SESSION['portal_payer_induk'],
        'tenantNama'     => $_SESSION['portal_tenant_nama'],
        'invoices'       => $invoices,
        'riwayatSummary' => $riwayatSummary,
        'csrf'           => Security::csrfToken(),
    ]);
}

    /**
     * Halaman riwayat pembayaran.
     */
    public function riwayat(): void
    {
        // Recover session dari order_id kalau session kosong
        if (empty($_SESSION['portal_payer_id']) && !empty($_GET['order_id'])) {
            $this->recoverSessionFromOrder($_GET['order_id']);
        }

        if (empty($_SESSION['portal_payer_id'])) {
            Response::redirect('/portal');
        }

        $tenantId = (int) $_SESSION['portal_tenant_id'];
        $payerId  = (int) $_SESSION['portal_payer_id'];

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $total    = Invoice::countRiwayatByPayer($tenantId, $payerId);
        $items    = Invoice::riwayatByPayer($tenantId, $payerId, $page, $perPage);
        $summary  = Invoice::summaryRiwayat($tenantId, $payerId);
        $lastPage = max(1, (int) ceil($total / $perPage));

        Response::view('portal/riwayat', [
            'title'      => 'Riwayat Pembayaran',
            'payerNama'  => $_SESSION['portal_payer_nama'],
            'payerInduk' => $_SESSION['portal_payer_induk'],
            'tenantNama' => $_SESSION['portal_tenant_nama'],
            'items'      => $items,
            'summary'    => $summary,
            'page'       => $page,
            'lastPage'   => $lastPage,
            'total'      => $total,
        ]);
    }

    /**
     * Restore session portal dari order_id (dipakai setelah redirect DOKU).
     */
    private function recoverSessionFromOrder(string $orderId): void
    {
        $payment = Payment::findByOrderId($orderId);
        if (!$payment) {
            return;
        }

        $db = Payment::db();

        $stmt = $db->prepare("SELECT * FROM payers WHERE id = ? LIMIT 1");
        $stmt->execute([$payment['payer_id']]);
        $payer = $stmt->fetch();

        $stmt = $db->prepare("SELECT * FROM tenants WHERE id = ? LIMIT 1");
        $stmt->execute([$payment['tenant_id']]);
        $tenant = $stmt->fetch();

        if (!$payer || !$tenant) {
            return;
        }

        $_SESSION['portal_payer_id']       = (int) $payer['id'];
        $_SESSION['portal_payer_nama']     = $payer['nama'];
        $_SESSION['portal_payer_induk']    = $payer['nomor_induk'];
        $_SESSION['portal_payer_whatsapp'] = $payer['whatsapp'] ?? null;
        $_SESSION['portal_tenant_id']      = (int) $tenant['id'];
        $_SESSION['portal_tenant_nama']    = $tenant['nama'];
        $_SESSION['portal_tenant_slug']    = $tenant['slug'];
    }

    /**
     * Proses pembayaran — redirect ke DOKU.
     * Model bisnis: SEWA. Tidak ada potongan platform.
     */
    public function bayar(): void
    {
        if (empty($_SESSION['portal_payer_id'])) {
            Response::redirect('/portal');
        }

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['portal_error'] = 'Sesi tidak valid. Coba lagi.';
            Response::redirect('/portal/tagihan');
        }

        $invoiceIds = $_POST['invoice_ids'] ?? [];
        if (empty($invoiceIds)) {
            $_SESSION['portal_error'] = 'Pilih setidaknya satu tagihan.';
            Response::redirect('/portal/tagihan');
        }

        $tenantId = (int) $_SESSION['portal_tenant_id'];
        $payerId  = (int) $_SESSION['portal_payer_id'];

        $db = Invoice::db();
        $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));
        $stmt = $db->prepare(
            "SELECT *,
                    (jumlah - COALESCE(paid_amount, 0)) AS remaining
            FROM invoices
            WHERE id IN ($placeholders)
            AND tenant_id = ?
            AND payer_id = ?
            AND status = 'unpaid'
            AND (jumlah - COALESCE(paid_amount, 0)) > 0"
        );
        $stmt->execute([...$invoiceIds, $tenantId, $payerId]);
        $invoices = $stmt->fetchAll();

        if (empty($invoices)) {
            $_SESSION['portal_error'] = 'Tagihan tidak valid atau sudah dibayar.';
            Response::redirect('/portal/tagihan');
        }

        // Total dari SISA tagihan
        $total = 0;
        foreach ($invoices as $inv) {
            $total += (float) $inv['remaining'];
        }

        // Model SEWA: tidak ada potongan platform
        $platformFee  = 0;
        $tenantAmount = $total; // 100% milik tenant

        $externalId = 'CEKTAGIHAN-' . $tenantId . '-' . time() . '-' . rand(1000, 9999);

        $paymentId = Payment::createPending($tenantId, $payerId, $externalId, $total);

        // Update platform_fee & tenant_amount sesuai model sewa
        $upd = Payment::db()->prepare(
            "UPDATE payments SET platform_fee = ?, tenant_amount = ? WHERE id = ?"
        );
        $upd->execute([$platformFee, $tenantAmount, $paymentId]);

        Payment::attachInvoices($paymentId, $invoiceIds);

        try {
            $doku = new DokuService();

            $payerEmail  = 'payer' . $payerId . '@cektagihan.id';
            $description = 'Pembayaran ' . count($invoices) . ' tagihan';

            $items = array_map(fn($inv) => [
                'name'     => ucfirst($inv['jenis']) . ' ' . $inv['periode'],
                'quantity' => 1,
                'price'    => (int) $inv['remaining'],
            ], $invoices);

            $customer = [
                'name'  => $_SESSION['portal_payer_nama'] ?? 'Payer',
                'email' => $payerEmail,
                'phone' => $_SESSION['portal_payer_whatsapp'] ?? null,
            ];

            // Sub-account opsional (kalau tenant sudah punya)
            $tenantBank = \App\Models\TenantBankAccount::findByTenant($tenantId);
            $subAccountId = null;
            $splitRuleId  = null;

            if ($tenantBank && $tenantBank['status'] === 'verified' && !empty($tenantBank['doku_sub_account_id'])) {
                $subAccountId = $tenantBank['doku_sub_account_id'];
                $splitRuleId  = $tenantBank['doku_split_rule_id'] ?: null;
            }

            $result = $doku->createCheckout(
                $externalId,
                (int) $total,
                $subAccountId,
                $splitRuleId,
                $customer,
                $items
            );

            $upd = Payment::db()->prepare(
                "UPDATE payments
                SET gateway_ref = ?, payment_url = ?, gateway = 'doku', updated_at = NOW()
                WHERE id = ?"
            );
            $upd->execute([
                $result['token_id'] ?: $result['request_id'],
                $result['payment_url'],
                $paymentId,
            ]);

            $_SESSION['portal_last_order_id'] = $externalId;

            header('Location: ' . $result['payment_url']);
            exit;

        } catch (\Throwable $e) {
            $delStmt = Payment::db()->prepare("DELETE FROM payments WHERE id = ?");
            $delStmt->execute([$paymentId]);

            $_SESSION['portal_error'] = 'Gagal memproses pembayaran: ' . $e->getMessage();
            Response::redirect('/portal/tagihan');
        }
    }


    /**
     * Halaman sukses setelah bayar.
     */
    public function selesai(): void
    {
        $orderId = trim($_GET['order_id'] ?? '');

        if ($orderId === '') {
            $orderId = $_SESSION['portal_last_order_id'] ?? '';
        }

        if ($orderId === '') {
            Response::view('portal/selesai', [
                'title'    => 'Pembayaran Berhasil',
                'payment'  => null,
                'invoices' => [],
                'orderId'  => null,
            ]);
            return;
        }

        $payment = Payment::findByOrderId($orderId);

        if (!$payment) {
            Response::view('portal/selesai', [
                'title'    => 'Pembayaran Berhasil',
                'payment'  => null,
                'invoices' => [],
                'orderId'  => $orderId,
            ]);
            return;
        }

        $db = Payment::db();
        $stmt = $db->prepare(
            "SELECT i.*, p.nomor_induk, p.nama AS payer_nama
             FROM invoices i
             INNER JOIN payment_invoices pi ON pi.invoice_id = i.id
             INNER JOIN payers p ON p.id = i.payer_id
             WHERE pi.payment_id = ?
             ORDER BY i.jenis ASC"
        );
        $stmt->execute([$payment['id']]);
        $invoices = $stmt->fetchAll();

        $stmtUnpaid = $db->prepare(
            "SELECT *,
                    (jumlah - COALESCE(paid_amount, 0)) AS remaining
            FROM invoices
            WHERE tenant_id = ?
            AND payer_id = ?
            AND status = 'unpaid'
            AND (jumlah - COALESCE(paid_amount, 0)) > 0
            ORDER BY due_date ASC"
        );
        $stmtUnpaid->execute([$payment['tenant_id'], $payment['payer_id']]);
        $unpaidInvoices = $stmtUnpaid->fetchAll();

        Response::view('portal/selesai', [
            'title'         => 'Pembayaran Berhasil',
            'payment'       => $payment,
            'invoices'      => $invoices,        // tagihan yang baru dibayar
            'unpaidInvoices'=> $unpaidInvoices,  // tagihan yang masih tersisa
            'orderId'       => $orderId,
        ]);
    }

    /**
     * Halaman gagal bayar.
     */
public function gagal(): void
{
    $orderId = trim($_GET['order_id'] ?? '');

    // Cari payment untuk tampilkan info
    $payment = null;
    if ($orderId !== '') {
        $payment = Payment::findByOrderId($orderId);
    }

    Response::view('portal/gagal', [
        'title'   => 'Pembayaran Gagal',
        'orderId' => $orderId,
        'payment' => $payment,
    ]);
}
}