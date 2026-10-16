<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Models\Invoice;
use App\Models\ManualPayment;
use App\Models\Payer;

class ManualPaymentController
{
    private function tenantId(): int
    {
        $tid = $_SESSION['tenant_id'] ?? null;
        if (!$tid) {
            $_SESSION['flash_error'] = 'Akun Anda tidak terhubung ke tenant.';
            Response::redirect('/dashboard');
        }
        return (int) $tid;
    }

    /**
     * List pembayaran manual.
     */
    public function index(): void
    {
        $tenantId = $this->tenantId();

        $filters = [
            'q'      => trim($_GET['q'] ?? ''),
            'metode' => trim($_GET['metode'] ?? ''),
            'start'  => trim($_GET['start'] ?? ''),
            'end'    => trim($_GET['end'] ?? ''),
        ];

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $total    = ManualPayment::countByTenant($tenantId, $filters);
        $items    = ManualPayment::allByTenant($tenantId, $filters, $page, $perPage);
        $summary  = ManualPayment::summaryByTenant($tenantId);
        $lastPage = max(1, (int) ceil($total / $perPage));

        Response::view('admin/manual-payments/index', [
            'title'    => 'Pembayaran Manual',
            'items'    => $items,
            'summary'  => $summary,
            'filters'  => $filters,
            'page'     => $page,
            'lastPage' => $lastPage,
            'total'    => $total,
        ]);
    }

    /**
     * Form catat pembayaran manual.
     */
    public function create(): void
    {
        $tenantId = $this->tenantId();

        // Ambil SEMUA pelanggan aktif (tidak filter by tagihan)
        $payers = Payer::allByTenant($tenantId, '', 'active', 1, 1000);

        if (empty($payers)) {
            $_SESSION['flash_error'] = 'Belum ada pelanggan aktif. Tambahkan pelanggan dulu.';
            Response::redirect('/admin/payers');
        }

        Response::view('admin/manual-payments/form', [
            'title'  => 'Catat Pembayaran Manual',
            'payers' => $payers,
            'csrf'   => Security::csrfToken(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * Ambil tagihan unpaid berdasarkan pelanggan (AJAX).
     */
    public function getInvoices(string $payerId): void
    {
        $tenantId = $this->tenantId();
        $payerId  = (int) $payerId;

        $payer = Payer::findByIdAndTenant($payerId, $tenantId);
        if (!$payer) {
            Response::json(['status' => false, 'message' => 'Pelanggan tidak ditemukan']);
            return;
        }

        $db = Invoice::db();
        $stmt = $db->prepare(
            "SELECT id, jenis, periode, jumlah, paid_amount, due_date,
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

        Response::json([
            'status'   => true,
            'payer'    => [
                'id'          => $payer['id'],
                'nomor_induk' => $payer['nomor_induk'],
                'nama'        => $payer['nama'],
            ],
            'invoices' => $invoices,
        ]);
    }

    /**
     * Simpan pembayaran manual.
     */
    public function store(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/manual-payments/create');
        }

        $payerId   = (int) ($_POST['payer_id'] ?? 0);
        $invoiceId = (int) ($_POST['invoice_id'] ?? 0);
        $jumlah    = (float) str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? '0');
        $metode    = trim($_POST['metode'] ?? 'tunai');
        $catatan   = trim($_POST['catatan'] ?? '');
        $paidAt    = trim($_POST['paid_at'] ?? date('Y-m-d\TH:i'));

        $errors = [];

        // Validasi pelanggan
        $payer = Payer::findByIdAndTenant($payerId, $tenantId);
        if (!$payer) {
            $errors['payer_id'] = 'Pelanggan tidak valid.';
        }

        // Validasi tagihan
        $db = Invoice::db();
        $stmt = $db->prepare(
            "SELECT * FROM invoices
             WHERE id = ? AND tenant_id = ? AND payer_id = ? AND status = 'unpaid'
             LIMIT 1"
        );
        $stmt->execute([$invoiceId, $tenantId, $payerId]);
        $invoice = $stmt->fetch();

        $remaining = 0;
        if (!$invoice) {
            $errors['invoice_id'] = 'Tagihan tidak valid atau sudah dibayar.';
        } else {
            $remaining = (float) $invoice['jumlah'] - (float) ($invoice['paid_amount'] ?? 0);

            if ($remaining <= 0) {
                $errors['invoice_id'] = 'Tagihan ini sudah lunas.';
            }
        }

        // Validasi jumlah
        if ($jumlah <= 0) {
            $errors['jumlah'] = 'Jumlah harus lebih dari 0.';
        } elseif ($invoice && $jumlah > $remaining) {
            $errors['jumlah'] = 'Jumlah melebihi sisa tagihan (Rp ' . number_format($remaining, 0, ',', '.') . ').';
        }

        if (!in_array($metode, ['tunai', 'transfer_bank', 'lain'], true)) {
            $errors['metode'] = 'Metode tidak valid.';
        }

        // Upload bukti
        $buktiFile = null;
        if (!empty($_FILES['bukti']['tmp_name']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
            $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $errors['bukti'] = 'Format bukti harus JPG, PNG, atau PDF.';
            } elseif ($_FILES['bukti']['size'] > 5 * 1024 * 1024) {
                $errors['bukti'] = 'Ukuran bukti maksimal 5 MB.';
            } else {
                $uploadDir = __DIR__ . '/../../storage/uploads/bukti/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $randomName = 'bukti_' . $tenantId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $uploadPath = $uploadDir . $randomName;

                if (move_uploaded_file($_FILES['bukti']['tmp_name'], $uploadPath)) {
                    $buktiFile = 'bukti/' . $randomName;
                    @chmod($uploadPath, 0600);
                }
            }
        }

        if (!empty($errors)) {
            // Ambil SEMUA pelanggan aktif untuk form
            $payers = Payer::allByTenant($tenantId, '', 'active', 1, 1000);

            Response::view('admin/manual-payments/form', [
                'title'  => 'Catat Pembayaran Manual',
                'payers' => $payers,
                'csrf'   => Security::csrfToken(),
                'errors' => $errors,
                'old'    => $_POST,
            ]);
            return;
        }

        $db = ManualPayment::db();
        $db->beginTransaction();

        try {
            // 1. Simpan pembayaran manual
            $manualId = ManualPayment::create($tenantId, [
                'payer_id'   => $payerId,
                'invoice_id' => $invoiceId,
                'jumlah'     => $jumlah,
                'metode'     => $metode,
                'bukti_file' => $buktiFile,
                'catatan'    => $catatan,
                'paid_at'    => date('Y-m-d H:i:s', strtotime($paidAt)),
                'created_by' => $_SESSION['user_id'] ?? null,
            ]);

            // 2. Update paid_amount invoice
            $result = Invoice::updatePaidAmount($invoiceId, $jumlah);

            $db->commit();

            AuditLog::write('manual_payment.create', 'manual_payments', $manualId, [
                'payer_id'   => $payerId,
                'invoice_id' => $invoiceId,
                'jumlah'     => $jumlah,
                'metode'     => $metode,
                'fully_paid' => $result['fully_paid'],
            ]);

            if ($result['fully_paid']) {
                $_SESSION['flash_success'] = 'Pembayaran berhasil. Tagihan sudah LUNAS.';
            } else {
                $sisa = number_format($result['remaining'], 0, ',', '.');
                $_SESSION['flash_success'] = "Pembayaran dicatat. Sisa tagihan: Rp {$sisa}.";
            }
            Response::redirect('/admin/manual-payments');

        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = 'Gagal menyimpan: ' . $e->getMessage();
            Response::redirect('/admin/manual-payments/create');
        }
    }

    /**
     * Detail pembayaran manual.
     */
    public function show(string $id): void
    {
        $tenantId = $this->tenantId();
        $item = ManualPayment::findByIdAndTenant((int) $id, $tenantId);

        if (!$item) {
            $_SESSION['flash_error'] = 'Pembayaran tidak ditemukan.';
            Response::redirect('/admin/manual-payments');
        }

        Response::view('admin/manual-payments/show', [
            'title' => 'Detail Pembayaran Manual',
            'item'  => $item,
        ]);
    }
}