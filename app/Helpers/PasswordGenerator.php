<?php
declare(strict_types=1);

namespace App\Helpers;

class PasswordGenerator
{
    /**
     * Generate password mudah dibaca tapi tetap kuat.
     * Contoh: Ruko-A7k9!, Mekar-X2p8!
     */
    public static function generate(string $prefix = 'Ruko'): string
    {
        $huruf = 'abcdefghjkmnpqrstuvwxyz';
        $angka = '23456789';

        $kata = $prefix . '-' . strtoupper($huruf[random_int(0, strlen($huruf) - 1)])
              . $huruf[random_int(0, strlen($huruf) - 1)]
              . $angka[random_int(0, strlen($angka) - 1)]
              . $angka[random_int(0, strlen($angka) - 1)]
              . '!';

        return $kata;
    }
}