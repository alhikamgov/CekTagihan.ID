<?php
declare(strict_types=1);

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExportService
{
    /**
     * Export laporan transaksi ke Excel.
     */
    public static function exportLaporan(
        array $rows,
        array $summary,
        string $periodeLabel,
        string $tenantNama
    ): string {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan');

        // ==== Header ====
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', $tenantNama);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Laporan Transaksi - ' . $periodeLabel);
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'Dicetak: ' . date('d M Y H:i'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ==== Ringkasan ====
        $sheet->setCellValue('A5', 'RINGKASAN');
        $sheet->getStyle('A5')->getFont()->setBold(true);

        $sheet->setCellValue('A6', 'Total Transaksi');
        $sheet->setCellValue('B6', $summary['total_transaksi'] ?? 0);

        $sheet->setCellValue('A7', 'Total Nominal');
        $sheet->setCellValue('B7', $summary['total_nominal'] ?? 0);
        $sheet->getStyle('B7')->getNumberFormat()->setFormatCode('#,##0');

        $sheet->setCellValue('A8', 'Total Payer');
        $sheet->setCellValue('B8', $summary['total_payer'] ?? 0);

        // ==== Header Tabel ====
        $startRow = 10;

        $headers = ['No', 'Nomor Pelanggan', 'Nama Payer', 'Jenis', 'Periode', 'Tanggal Bayar', 'Jumlah'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $startRow, $h);
            $col++;
        }

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10b981']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A' . $startRow . ':G' . $startRow)->applyFromArray($headerStyle);

        // ==== Isi Tabel ====
        $row = $startRow + 1;
        $no  = 1;
        foreach ($rows as $r) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $r['nomor_induk']);
            $sheet->setCellValue('C' . $row, $r['payer_nama']);
            $sheet->setCellValue('D' . $row, ucfirst($r['jenis']));
            $sheet->setCellValue('E' . $row, $r['periode']);
            $sheet->setCellValue('F' . $row, date('d M Y H:i', strtotime($r['paid_at'])));
            $sheet->setCellValue('G' . $row, (float) $r['jumlah']);
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // ==== Baris Total ====
        $lastDataRow = $row - 1;
        $sheet->setCellValue('F' . $row, 'TOTAL');
        $sheet->setCellValue('G' . $row, '=SUM(G' . ($startRow + 1) . ':G' . $lastDataRow . ')');
        $sheet->getStyle('F' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F' . $row . ':G' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ecfdf5');

        // ==== Border ====
        $sheet->getStyle('A' . $startRow . ':G' . $row)->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ==== Column Width ====
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(16);

        // ==== Save ke file temporary ====
        $filename = 'laporan_' . date('YmdHis') . '.xlsx';
        $filePath = sys_get_temp_dir() . '/' . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
 * Export rekap per payer ke Excel.
 */
public static function exportRekapPerPayer(
    array $rows,
    array $periodeList,
    array $totals,
    string $periodeLabel,
    string $tenantNama
): string {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap Per Payer');

    $totalCols = 3 + count($periodeList) + 1;
    $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

    // Header
    $sheet->mergeCells('A1:' . $lastColLetter . '1');
    $sheet->setCellValue('A1', $tenantNama);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:' . $lastColLetter . '2');
    $sheet->setCellValue('A2', 'Laporan Per Pelanggan - ' . $periodeLabel);
    $sheet->getStyle('A2')->getFont()->setSize(12);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:' . $lastColLetter . '3');
    $sheet->setCellValue('A3', 'Dicetak: ' . date('d M Y H:i'));
    $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Header tabel
    $startRow = 5;
    $col = 1;

    $headers = ['No', 'No. Pelanggan', 'Nama'];
    foreach ($periodeList as $p) {
        $headers[] = date('M Y', strtotime($p . '-01'));
    }
    $headers[] = 'Total';

    foreach ($headers as $h) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++);
        $sheet->setCellValue($colLetter . $startRow, $h);
    }

    $headerStyle = [
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10b981']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('A' . $startRow . ':' . $lastColLetter . $startRow)->applyFromArray($headerStyle);

    // Isi
    $row = $startRow + 1;
    $no  = 1;

    foreach ($rows as $r) {
        $col = 1;
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $no++);
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $r['nomor_induk']);
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $r['payer_nama']);

        foreach ($periodeList as $p) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
            $key = 'periode_' . str_replace('-', '_', $p);
            $val = (float) ($r[$key] ?? 0);
            $sheet->setCellValue($colLetter, $val);
            $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');
        }

        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
        $sheet->setCellValue($colLetter, (float) $r['total_nominal']);
        $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($colLetter)->getFont()->setBold(true);

        $row++;
    }

    // Baris total
    $col = 1;
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, '');
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, '');
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, 'TOTAL');

    foreach ($periodeList as $p) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
        $key = 'periode_' . str_replace('-', '_', $p);
        $sheet->setCellValue($colLetter, (float) ($totals[$key] ?? 0));
        $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');
    }

    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
    $sheet->setCellValue($colLetter, (float) $totals['total_nominal']);
    $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');

    $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->getFont()->setBold(true);
    $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->getFill()
        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ecfdf5');

    // Border
    $sheet->getStyle('A' . $startRow . ':' . $lastColLetter . $row)->getBorders()
        ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Column width
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(25);
    for ($i = 4; $i <= $totalCols; $i++) {
        $sheet->getColumnDimension(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i)
        )->setWidth(15);
    }

    // Save
    $filename = 'rekap_per_payer_' . date('YmdHis') . '.xlsx';
    $filePath = sys_get_temp_dir() . '/' . $filename;

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    return $filePath;
}

/**
 * Export laporan per payer (matrix, kolom = periode) ke Excel.
 */
public static function exportLaporanPerPayerMatrix(
    array $rows,
    array $periodeList,
    array $totals,
    string $periodeLabel,
    string $tenantNama,
    string $status = 'all'
): string {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Laporan Per Pelanggan');

    $totalCols = 3 + count($periodeList) + 1;
    $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

    // Header
    $sheet->mergeCells('A1:' . $lastColLetter . '1');
    $sheet->setCellValue('A1', $tenantNama);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A2:' . $lastColLetter . '2');
    $sheet->setCellValue('A2', 'Laporan Per Pelanggan - ' . $periodeLabel);
    $sheet->getStyle('A2')->getFont()->setSize(12);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:' . $lastColLetter . '3');
    $sheet->setCellValue('A3', 'Dicetak: ' . date('d M Y H:i'));
    $sheet->getStyle('A3')->getFont()->setSize(10)->setItalic(true);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Header tabel
    $startRow = 5;
    $col = 1;

    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $startRow, 'No');
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $startRow, 'No. Pelanggan');
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $startRow, 'Nama');

    foreach ($periodeList as $p) {
        $sheet->setCellValue(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $startRow,
            date('M Y', strtotime($p . '-01'))
        );
    }
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $startRow, 'Total');

    $headerStyle = [
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '10b981']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('A' . $startRow . ':' . $lastColLetter . $startRow)->applyFromArray($headerStyle);

    // Isi
    $row = $startRow + 1;
    $no  = 1;

    foreach ($rows as $r) {
        $col = 1;
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $no++);
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $r['nomor_induk']);
        $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $r['payer_nama']);

        foreach ($periodeList as $p) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
            $key = 'periode_' . str_replace('-', '_', $p);
            $val = (float) ($r[$key] ?? 0);
            $sheet->setCellValue($colLetter, $val);
            $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');
        }

        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
        $sheet->setCellValue($colLetter, (float) $r['total_nominal']);
        $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle($colLetter)->getFont()->setBold(true);

        $row++;
    }

    // Baris total
    $col = 1;
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, '');
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, '');
    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, 'TOTAL');

    foreach ($periodeList as $p) {
        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row;
        $key = 'periode_' . str_replace('-', '_', $p);
        $sheet->setCellValue($colLetter, (float) ($totals[$key] ?? 0));
        $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');
    }

    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
    $sheet->setCellValue($colLetter, (float) $totals['total_nominal']);
    $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode('#,##0');

    $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->getFont()->setBold(true);
    $sheet->getStyle('A' . $row . ':' . $lastColLetter . $row)->getFill()
        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ecfdf5');

    // Border
    $sheet->getStyle('A' . $startRow . ':' . $lastColLetter . $row)->getBorders()
        ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Column width
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(25);
    for ($i = 4; $i <= $totalCols; $i++) {
        $sheet->getColumnDimension(
            \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i)
        )->setWidth(15);
    }

    // Save
    $filename = 'laporan_per_pelanggan_' . date('YmdHis') . '.xlsx';
    $filePath = sys_get_temp_dir() . '/' . $filename;

    $writer = new Xlsx($spreadsheet);
    $writer->save($filePath);

    return $filePath;
}
}