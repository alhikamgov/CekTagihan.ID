<?php
declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelImportService
{
    /**
     * Mapping header file → key internal.
     * File pakai "nomor_pelanggan", internal tetap "nomor_induk" (kompatibel dengan DB).
     * Tetap menerima "nomor_induk" untuk file lama.
     */
    private const HEADER_MAP = [
        'nomor_pelanggan' => 'nomor_induk',
        'nomor_induk'     => 'nomor_induk', // backward compat
        'nama'            => 'nama',
        'whatsapp'        => 'whatsapp',
    ];

    /**
     * Baca file Excel/CSV → return array of rows.
     * Header file: nomor_pelanggan | nama | whatsapp
     * Output key: nomor_induk | nama | whatsapp (internal)
     */
    public static function readFile(string $filePath): array
    {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        // Baris pertama = header
        $header = array_shift($rows);

        // Bangun mapping kolom → key internal berdasarkan header aktual
        // Contoh: [0 => 'nomor_induk', 1 => 'nama', 2 => 'whatsapp']
        $columnMap = [];
        foreach ($header as $i => $colName) {
            $normalized = strtolower(trim((string) $colName));
            // Ganti spasi dengan underscore biar toleran
            $normalized = preg_replace('/\s+/', '_', $normalized);

            if (isset(self::HEADER_MAP[$normalized])) {
                $columnMap[$i] = self::HEADER_MAP[$normalized];
            }
        }

        // Validasi minimal ada kolom nomor + nama
        if (!in_array('nomor_induk', $columnMap, true)) {
            throw new \RuntimeException(
                'Kolom "nomor_pelanggan" tidak ditemukan di file. ' .
                'Pastikan header sesuai template.'
            );
        }
        if (!in_array('nama', $columnMap, true)) {
            throw new \RuntimeException(
                'Kolom "nama" tidak ditemukan di file.'
            );
        }

        $result = [];
        foreach ($rows as $index => $row) {
            // Skip baris kosong
            if (empty(array_filter($row, fn($v) => trim((string) $v) !== ''))) {
                continue;
            }

            $item = [
                'row_number'  => $index + 2,
                'nomor_induk' => '',
                'nama'        => '',
                'whatsapp'    => '',
            ];

            // Isi berdasarkan mapping
            foreach ($columnMap as $colIndex => $internalKey) {
                $item[$internalKey] = trim((string) ($row[$colIndex] ?? ''));
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Validasi per baris.
     * Input key: nomor_induk | nama | whatsapp (internal)
     */
    public static function validateRows(array $rows, array $existingNomorInduk = []): array
    {
        $valid = [];
        $invalid = [];
        $seenInFile = [];

        foreach ($rows as $row) {
            $errors = [];

            // Validasi nomor pelanggan
            if ($row['nomor_induk'] === '') {
                $errors[] = 'Nomor pelanggan kosong';
            } elseif (mb_strlen($row['nomor_induk']) > 50) {
                $errors[] = 'Nomor pelanggan > 50 karakter';
            } elseif (in_array($row['nomor_induk'], $existingNomorInduk, true)) {
                $errors[] = 'Nomor pelanggan sudah ada di database';
            } elseif (in_array($row['nomor_induk'], $seenInFile, true)) {
                $errors[] = 'Nomor pelanggan duplikat di file';
            }

            // Validasi nama
            if ($row['nama'] === '') {
                $errors[] = 'Nama kosong';
            } elseif (mb_strlen($row['nama']) > 150) {
                $errors[] = 'Nama > 150 karakter';
            }

            // Validasi whatsapp
            if (!empty($row['whatsapp']) && mb_strlen($row['whatsapp']) > 30) {
                $errors[] = 'WhatsApp > 30 karakter';
            }

            if (empty($errors)) {
                $valid[] = $row;
                $seenInFile[] = $row['nomor_induk'];
            } else {
                $row['errors'] = $errors;
                $invalid[] = $row;
            }
        }

        return [
            'valid'   => $valid,
            'invalid' => $invalid,
            'total'   => count($rows),
        ];
    }

    /**
     * Generate template Excel untuk download.
     * Header pakai "nomor_pelanggan" (bukan nomor_induk).
     */
    public static function generateTemplate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header — pakai nomor_pelanggan
        $sheet->setCellValue('A1', 'nomor_pelanggan');
        $sheet->setCellValue('B1', 'nama');
        $sheet->setCellValue('C1', 'whatsapp');

        // Style header
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('DBEAFE');

        // Contoh baris
        $sheet->setCellValue('A2', 'A-01');
        $sheet->setCellValue('B2', 'Budi Santoso');
        $sheet->setCellValue('C2', '081234567890');

        $sheet->setCellValue('A3', 'A-02');
        $sheet->setCellValue('B3', 'Siti Aminah');
        $sheet->setCellValue('C3', '081298765432');

        // Column width
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(18);

        // Save ke file temporary
        $filePath = sys_get_temp_dir() . '/template_import_' . time() . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }
}