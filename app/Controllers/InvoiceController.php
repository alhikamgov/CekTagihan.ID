<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\AuditLog;
use App\Helpers\Response;
use App\Helpers\Security;
use App\Helpers\Validator;
use App\Models\Invoice;
use App\Models\InvoiceType;
use App\Models\Payer;

class InvoiceController
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
     * List invoice dengan filter + pagination.
     */
    public function index(): void
    {
        $tenantId = $this->tenantId();

        $filters = [
            'status'  => trim($_GET['status']  ?? ''),
            'jenis'   => trim($_GET['jenis']   ?? ''),
            'periode' => trim($_GET['periode'] ?? ''),
            'q'       => trim($_GET['q']       ?? ''),
        ];

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;

        $total    = Invoice::countByTenantFiltered($tenantId, $filters);
        $invoices = Invoice::allByTenant($tenantId, $filters, $page, $perPage);
        $summary  = Invoice::summaryByTenant($tenantId);
        $periodes = Invoice::listPeriodeByTenant($tenantId);
        $lastPage = max(1, (int) ceil($total / $perPage));

        // Jenis tagihan aktif dari DB
        $jenisList = InvoiceType::activeByTenant($tenantId);

        Response::view('admin/invoices/index', [
            'title'     => 'Kelola Invoice',
            'invoices'  => $invoices,
            'summary'   => $summary,
            'periodes'  => $periodes,
            'jenisList' => $jenisList,
            'filters'   => $filters,
            'page'      => $page,
            'lastPage'  => $lastPage,
            'total'     => $total,
        ]);
    }

    /**
     * Form buat invoice baru.
     */
    public function create(): void
    {
        $tenantId = $this->tenantId();

        // Kalau tidak ada payer, redirect
        $payers = Payer::allByTenant($tenantId, '', 'active');
        if (empty($payers)) {
            $_SESSION['flash_error'] = 'Tambahkan payer dulu sebelum membuat invoice.';
            Response::redirect('/admin/payers');
        }

        // Ambil jenis tagihan aktif
        $jenisList = InvoiceType::activeByTenant($tenantId);
        if (empty($jenisList)) {
            $_SESSION['flash_error'] = 'Tambahkan jenis tagihan dulu.';
            Response::redirect('/admin/jenis-tagihan');
        }

        Response::view('admin/invoices/form', [
            'title'     => 'Buat Invoice',
            'payers'    => $payers,
            'jenisList' => $jenisList,
            'csrf'      => Security::csrfToken(),
            'errors'    => [],
            'old'       => [],
        ]);
    }

    /**
     * Simpan invoice baru.
     */
    public function store(): void
    {
        $tenantId = $this->tenantId();

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/invoices/create');
        }

        $validator = new Validator($_POST);
        $validator->required('payer_id', 'Payer')
                  ->required('jenis', 'Jenis')
                  ->required('periode', 'Periode')
                  ->required('jumlah', 'Jumlah')
                  ->required('due_date', 'Tanggal jatuh tempo');

        $payerId  = (int) ($_POST['payer_id'] ?? 0);
        $jumlah   = (float) str_replace(['.', ','], ['', '.'], $_POST['jumlah'] ?? '0');
        $dueDate  = trim($_POST['due_date'] ?? '');
        $periode  = trim($_POST['periode'] ?? '');
        $jenis    = trim($_POST['jenis'] ?? '');

        // Validasi payer
        $payer = Payer::findByIdAndTenant($payerId, $tenantId);
        $errors = [];
        if (!$payer) {
            $errors['payer_id'] = 'Payer tidak valid.';
        }
        if ($validator->fails()) {
            $errors = array_merge($errors, $validator->errors());
        }

        // Validasi jenis dari DB
        $jenisList    = InvoiceType::activeByTenant($tenantId);
        $allowedJenis = array_column($jenisList, 'nama');

        if (!in_array($jenis, $allowedJenis, true)) {
            $errors['jenis'] = 'Jenis tidak valid.';
        }

        // Validasi jumlah
        if ($jumlah <= 0) {
            $errors['jumlah'] = 'Jumlah harus lebih dari 0.';
        }

        // Validasi tanggal
        if ($dueDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $errors['due_date'] = 'Format tanggal tidak valid.';
        }

        if (!empty($errors)) {
            Response::view('admin/invoices/form', [
                'title'     => 'Buat Invoice',
                'payers'    => Payer::allByTenant($tenantId, '', 'active'),
                'jenisList' => $jenisList,
                'csrf'      => Security::csrfToken(),
                'errors'    => $errors,
                'old'       => $_POST,
            ]);
            return;
        }

        $id = Invoice::create($tenantId, [
            'payer_id'  => $payerId,
            'jenis'     => $jenis,
            'periode'   => $periode,
            'deskripsi' => '',
            'jumlah'    => $jumlah,
            'due_date'  => $dueDate,
        ]);

        AuditLog::write('invoice.create', 'invoices', $id, [
            'payer_id' => $payerId,
            'jenis'    => $jenis,
            'jumlah'   => $jumlah,
        ]);

        $_SESSION['flash_success'] = 'Invoice berhasil dibuat.';
        Response::redirect('/admin/invoices');
    }

    /**
     * Detail invoice.
     */
    public function show(string $id): void
    {
        $tenantId = $this->tenantId();
        $invoice = Invoice::findByIdAndTenant((int) $id, $tenantId);

        if (!$invoice) {
            $_SESSION['flash_error'] = 'Invoice tidak ditemukan.';
            Response::redirect('/admin/invoices');
        }

        Response::view('admin/invoices/show', [
            'title'   => 'Detail Invoice',
            'invoice' => $invoice,
        ]);
    }

    /**
     * Form konfirmasi cancel invoice.
     */
    public function cancel(string $id): void
    {
        $tenantId = $this->tenantId();
        $id = (int) $id;
        $invoice = Invoice::findByIdAndTenant($id, $tenantId);

        if (!$invoice) {
            $_SESSION['flash_error'] = 'Invoice tidak ditemukan.';
            Response::redirect('/admin/invoices');
        }

        if ($invoice['status'] !== 'unpaid') {
            $_SESSION['flash_error'] = 'Hanya invoice berstatus unpaid yang bisa dibatalkan.';
            Response::redirect('/admin/invoices');
        }

        Response::view('admin/invoices/cancel', [
            'title'   => 'Batalkan Invoice',
            'invoice' => $invoice,
            'csrf'    => Security::csrfToken(),
        ]);
    }

    /**
     * Proses cancel invoice.
     */
    public function cancelConfirm(string $id): void
    {
        $tenantId = $this->tenantId();
        $id = (int) $id;

        $csrf = $_POST['csrf_token'] ?? '';
        if (!Security::verifyCsrf($csrf)) {
            $_SESSION['flash_error'] = 'Sesi tidak valid.';
            Response::redirect('/admin/invoices');
        }

        $invoice = Invoice::findByIdAndTenant($id, $tenantId);
        if (!$invoice) {
            $_SESSION['flash_error'] = 'Invoice tidak ditemukan.';
            Response::redirect('/admin/invoices');
        }

        if ($invoice['status'] !== 'unpaid') {
            $_SESSION['flash_error'] = 'Hanya invoice berstatus unpaid yang bisa dibatalkan.';
            Response::redirect('/admin/invoices');
        }

        $reason = trim($_POST['cancel_reason'] ?? '');
        Invoice::cancel($id, $tenantId, $reason);

        AuditLog::write('invoice.cancel', 'invoices', $id, [
            'payer_nomor_induk' => $invoice['nomor_induk'],
            'reason'            => $reason,
        ]);

        $_SESSION['flash_success'] = 'Invoice berhasil dibatalkan.';
        Response::redirect('/admin/invoices');
    }

    /**
 * Bulk action: cancel multiple invoices.
 */
public function bulkAction(): void
{
    $tenantId = $this->tenantId();

    $csrf = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCsrf($csrf)) {
        $_SESSION['flash_error'] = 'Sesi tidak valid.';
        Response::redirect('/admin/invoices');
    }

    $action = trim($_POST['bulk_action'] ?? '');
    $invoiceIds = $_POST['invoice_ids'] ?? [];

    if (empty($invoiceIds)) {
        $_SESSION['flash_error'] = 'Pilih setidaknya satu tagihan.';
        Response::redirect('/admin/invoices');
    }

    $invoiceIds = array_map('intval', $invoiceIds);

    if ($action === 'cancel') {
        $reason = trim($_POST['cancel_reason'] ?? 'Dibatalkan massal');
        $count = Invoice::bulkCancel($invoiceIds, $tenantId, $reason);

        AuditLog::write('invoice.bulk_cancel', 'invoices', null, [
            'count' => $count,
            'ids'   => $invoiceIds,
        ]);

        $_SESSION['flash_success'] = "Berhasil membatalkan {$count} tagihan.";

    } elseif ($action === 'reminder') {
        // Ambil invoice untuk reminder
        $invoices = Invoice::getForReminder($invoiceIds, $tenantId);

        if (empty($invoices)) {
            $_SESSION['flash_error'] = 'Tidak ada tagihan valid untuk dikirim reminder.';
            Response::redirect('/admin/invoices');
        }

        $wa = new \App\Services\WhatsAppService();
        $sent = 0;
        $failed = 0;

        // Group by payer (1 pelanggan bisa punya beberapa tagihan)
        $grouped = [];
        foreach ($invoices as $inv) {
            $payerId = $inv['payer_id'];
            if (!isset($grouped[$payerId])) {
                $grouped[$payerId] = [
                    'payer_nama'     => $inv['payer_nama'],
                    'nomor_induk'    => $inv['nomor_induk'],
                    'whatsapp'       => $inv['payer_whatsapp'],
                    'invoices'       => [],
                ];
            }
            $grouped[$payerId]['invoices'][] = $inv;
        }

        $tenant = \App\Models\Tenant::findById($tenantId);
        $tenantNama = $tenant['nama'] ?? 'Pengelola';
        $portalUrl = ($_ENV['APP_URL'] ?? '') . '/portal';

        foreach ($grouped as $payerId => $data) {
            if (empty($data['whatsapp'])) {
                $failed++;
                continue;
            }

            // Catat log
            $logId = \App\Models\NotificationLog::create(
                $tenantId,
                $payerId,
                'reminder',
                $data['whatsapp'],
                'Reminder tagihan (bulk)'
            );

            $result = $wa->sendReminder(
                $data['whatsapp'],
                $data['payer_nama'],
                $tenantNama,
                $data['invoices'],
                $portalUrl
            );

            if ($result['status']) {
                \App\Models\NotificationLog::markSent($logId, $result['reason']);
                $sent++;
            } else {
                \App\Models\NotificationLog::markFailed($logId, $result['reason']);
                $failed++;
            }
        }

        AuditLog::write('invoice.bulk_reminder', 'invoices', null, [
            'sent'   => $sent,
            'failed' => $failed,
            'ids'    => $invoiceIds,
        ]);

        if ($sent > 0) {
            $_SESSION['flash_success'] = "Reminder terkirim ke {$sent} pelanggan." . ($failed > 0 ? " ({$failed} gagal)" : '');
        } else {
            $_SESSION['flash_error'] = "Gagal mengirim reminder. Cek nomor WA pelanggan.";
        }

    } else {
        $_SESSION['flash_error'] = 'Aksi tidak valid.';
    }

    Response::redirect('/admin/invoices');
}
}