<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Models\Tenant;

class LandingController
{
    public function index(): void
    {
        // Ambil tenant aktif untuk ditampilkan di landing
        $tenants = Tenant::allActive();

        Response::view('landing/index', [
            'title'   => 'CekTagihan — Bayar tagihan dari mana saja',
            'tenants' => $tenants,
        ]);
    }
}