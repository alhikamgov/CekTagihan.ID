<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InvoiceController;
use App\Controllers\InvoiceGenerateController;
use App\Controllers\PayerController;
use App\Controllers\PayerImportController;
use App\Controllers\TenantController;
use App\Controllers\PortalController;
use App\Controllers\XenditWebhookController;
use App\Controllers\DokuWebhookController;
use App\Controllers\LaporanController;
use App\Controllers\LandingController;
use App\Controllers\ProfilController;
use App\Controllers\JenisTagihanController;
use App\Controllers\ManualPaymentController;
use App\Controllers\RekeningController;
use App\Controllers\AdminRekeningController;
use App\Controllers\PendapatanController;
use App\Controllers\PencairanController;
use App\Controllers\LegalController;
use App\Controllers\PlatformSettingController;
use App\Controllers\DaftarController;
use App\Controllers\PendaftarController;
use App\Controllers\SuperadminPencairanController;
use App\Controllers\TenantSubscriptionController;
use App\Controllers\SuperadminSubscriptionController;
use App\Helpers\Response;
use App\Middleware\AuthMiddleware;
use App\Middleware\SubscriptionMiddleware;

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        '/' => ['handler' => [LandingController::class, 'index']],
        '/portal' => ['handler' => [PortalController::class, 'index']],
        '/portal/tagihan' => ['handler' => [PortalController::class, 'tagihan']],
        '/portal/ulang' => ['handler' => [PortalController::class, 'reset']],
        '/portal/pay' => ['handler' => [PortalController::class, 'bayar']],
        '/portal/selesai' => ['handler' => [PortalController::class, 'selesai']],
        '/portal/gagal'   => ['handler' => [PortalController::class, 'gagal']],
        '/portal/riwayat' => ['handler' => [PortalController::class, 'riwayat']],
        '/portal/{slug}'  => ['handler' => [PortalController::class, 'tenantPortal']],
        '/health' => ['handler' => [AuthController::class, 'showLogin']],
        '/daftar'        => ['handler' => [DaftarController::class, 'index']],
        '/daftar/sukses' => ['handler' => [DaftarController::class, 'sukses']],
        '/login'  => ['handler' => [AuthController::class, 'showLogin']],
        '/logout' => ['handler' => [AuthController::class, 'logout']],
        '/dashboard' => [
            'handler'    => [DashboardController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'check'],
        ],
        '/superadmin/tenants' => [
            'handler'    => [TenantController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/tenants/store'           => [
            'handler'    => [TenantController::class, 'store'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/tenants/create' => [
            'handler'    => [TenantController::class, 'create'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/tenants/edit/{id}' => [
            'handler'    => [TenantController::class, 'edit'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/tenants/toggle/{id}' => [
            'handler'    => [TenantController::class, 'toggleStatus'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/tenants/reset-password/{id}' => [
            'handler'    => [TenantController::class, 'resetPassword'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/rekening' => [
            'handler'    => [AdminRekeningController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/rekening/verify/{id}' => [
            'handler'    => [AdminRekeningController::class, 'verify'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/rekening/reject/{id}' => [
            'handler'    => [AdminRekeningController::class, 'reject'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pengaturan' => [
            'handler'    => [PlatformSettingController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pendaftar' => [
            'handler'    => [PendaftarController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pendaftar/show/{id}' => [
            'handler'    => [PendaftarController::class, 'show'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pencairan' => [
            'handler'    => [SuperadminPencairanController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pencairan/show/{id}' => [
            'handler'    => [SuperadminPencairanController::class, 'show'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/admin/payers' => [
            'handler'    => [PayerController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/create' => [
            'handler'    => [PayerController::class, 'create'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/edit/{id}' => [
            'handler'    => [PayerController::class, 'edit'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/toggle/{id}' => [
            'handler'    => [PayerController::class, 'toggleStatus'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/import' => [
            'handler'    => [PayerImportController::class, 'showUpload'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/import/template' => [
            'handler'    => [PayerImportController::class, 'downloadTemplate'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices' => [
            'handler'    => [InvoiceController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/create' => [
            'handler'    => [InvoiceController::class, 'create'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/show/{id}' => [
            'handler'    => [InvoiceController::class, 'show'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/cancel/{id}' => [
            'handler'    => [InvoiceController::class, 'cancel'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/generate' => [
            'handler'    => [InvoiceGenerateController::class, 'form'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/tunggakan' => [
            'handler'    => [DashboardController::class, 'tunggakan'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan'        => [
            'handler'    => [LaporanController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
            ],
        '/admin/laporan/export' => [
            'handler'    => [LaporanController::class, 'export'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan/per-payer' => [
            'handler'    => [LaporanController::class, 'perPayer'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan/per-payer/detail/{id}' => [
            'handler'    => [LaporanController::class, 'perPayerDetail'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan/export-per-payer' => [
            'handler'    => [LaporanController::class, 'exportPerPayer'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/profil' => [
            'handler'    => [ProfilController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/jenis-tagihan' => [
            'handler'    => [JenisTagihanController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/jenis-tagihan/create' => [
            'handler'    => [JenisTagihanController::class, 'create'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/jenis-tagihan/edit/{id}' => [
            'handler'    => [JenisTagihanController::class, 'edit'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/jenis-tagihan/toggle/{id}' => [
            'handler'    => [JenisTagihanController::class, 'toggleStatus'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/manual-payments' => [
            'handler'    => [ManualPaymentController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/manual-payments/create' => [
            'handler'    => [ManualPaymentController::class, 'create'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/manual-payments/show/{id}' => [
            'handler'    => [ManualPaymentController::class, 'show'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/manual-payments/invoices/{id}' => [
            'handler'    => [ManualPaymentController::class, 'getInvoices'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/rekening' => [
            'handler'    => [RekeningController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/pendapatan' => [
            'handler'    => [PendapatanController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/pencairan' => [
            'handler'    => [PencairanController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan' => [
            'handler'    => [LaporanController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan/status-pelanggan' => [
            'handler'    => [LaporanController::class, 'statusPelanggan'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan/export' => [
            'handler'    => [LaporanController::class, 'export'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/laporan/status-pelanggan/detail/{id}' => [
            'handler'    => [LaporanController::class, 'detailPelanggan'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/pencairan/cancel/{id}' => [
            'handler'    => [PencairanController::class, 'cancel'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/account/change-password' => [
            'handler'    => [AuthController::class, 'showChangePassword'],
            'middleware' => [AuthMiddleware::class, 'check'],
        ],
        // Tenant
        '/admin/langganan' => [
            'handler'    => [TenantSubscriptionController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],

        // Superadmin
        '/superadmin/langganan' => [
            'handler'    => [SuperadminSubscriptionController::class, 'index'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/langganan/detail/{id}' => [
            'handler'    => [SuperadminSubscriptionController::class, 'detail'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/kebijakan-privasi' => [
            'handler' => [LegalController::class, 'privacy']],
        '/syarat-ketentuan'  => [
            'handler' => [LegalController::class, 'terms']],
        ],
    'POST' => [
        '/portal/cek'  => ['handler' => [PortalController::class, 'cek']],
        '/portal/bayar' => ['handler' => [PortalController::class, 'bayar']],
        '/daftar' => ['handler' => [DaftarController::class, 'submit']],
        '/login' => ['handler' => [AuthController::class, 'login']],
        '/superadmin/tenants/store' => [
            'handler'    => [TenantController::class, 'store'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/tenants/update/{id}' => [
            'handler'    => [TenantController::class, 'update'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/tenants/reset-password-confirm/{id}' => [
            'handler'    => [TenantController::class, 'resetPasswordConfirm'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pengaturan/save' => [
            'handler'    => [PlatformSettingController::class, 'save'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pendaftar/approve/{id}' => [
            'handler'    => [PendaftarController::class, 'approve'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pendaftar/reject/{id}' => [
            'handler'    => [PendaftarController::class, 'reject'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pencairan/approve/{id}' => [
            'handler'    => [SuperadminPencairanController::class, 'approve'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pencairan/reject/{id}' => [
            'handler'    => [SuperadminPencairanController::class, 'reject'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/pencairan/mark-paid/{id}' => [
            'handler'    => [SuperadminPencairanController::class, 'markPaid'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/superadmin/langganan/extend/{id}' => [
            'handler'    => [SuperadminSubscriptionController::class, 'extend'],
            'middleware' => [AuthMiddleware::class, 'superadmin'],
        ],
        '/admin/payers/store' => [
            'handler'    => [PayerController::class, 'store'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/update/{id}' => [
            'handler'    => [PayerController::class, 'update'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/import/upload' => [
            'handler'    => [PayerImportController::class, 'upload'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/payers/import/commit' => [
            'handler'    => [PayerImportController::class, 'commit'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/store' => [
            'handler'    => [InvoiceController::class, 'store'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/cancel-confirm/{id}' => [
            'handler'    => [InvoiceController::class, 'cancelConfirm'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/generate/preview' => [
            'handler'    => [InvoiceGenerateController::class, 'preview'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/generate/commit' => [
            'handler'    => [InvoiceGenerateController::class, 'commit'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/profil/update' => [
            'handler'    => [ProfilController::class, 'update'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/jenis-tagihan/store' => [
            'handler'    => [JenisTagihanController::class, 'store'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/jenis-tagihan/update/{id}' => [
            'handler'    => [JenisTagihanController::class, 'update'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/manual-payments/store' => [
            'handler'    => [ManualPaymentController::class, 'store'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/rekening/save' => [
            'handler'    => [RekeningController::class, 'save'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/invoices/bulk-action' => [
            'handler'    => [InvoiceController::class, 'bulkAction'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/pencairan/request' => [
            'handler'    => [PencairanController::class, 'request'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/admin/pencairan/cancel/{id}' => [
            'handler'    => [PencairanController::class, 'cancel'],
            'middleware' => [AuthMiddleware::class, 'admin'],
        ],
        '/account/change-password' => [
            'handler'    => [AuthController::class, 'changePassword'],
            'middleware' => [AuthMiddleware::class, 'check'],
        ],
        '/webhook/doku' => [
            'handler'    => [DokuWebhookController::class, 'handle'],
            'middleware' => [],
        ],
    ],
];

$matched = false;
foreach ($routes[$method] ?? [] as $pattern => $def) {
    $regex = '#^' . preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $pattern) . '$#';
    if (!preg_match($regex, $uri, $matches)) {
        continue;
    }

    array_shift($matches);

    $handler    = $def['handler'];
    $middleware = $def['middleware'] ?? null;

    if ($middleware) {
        [$mwClass, $mwMethod] = $middleware;
        $mwClass::$mwMethod();
    }

    [$class, $action] = $handler;
    (new $class())->$action(...$matches);

    $matched = true;
    break;
}

if (!$matched) {
    http_response_code(404);
    require __DIR__ . '/../views/errors/404.php';
    exit;
}