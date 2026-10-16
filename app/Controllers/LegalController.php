<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;

class LegalController
{
    /**
     * Halaman Kebijakan Privasi.
     */
    public function privacy(): void
    {
        Response::view('landing/privacy', [
            'title' => 'Kebijakan Privasi',
        ]);
    }

    /**
     * Halaman Syarat & Ketentuan.
     */
    public function terms(): void
    {
        Response::view('landing/terms', [
            'title' => 'Syarat & Ketentuan',
        ]);
    }
}