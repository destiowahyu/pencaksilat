<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    die('Unauthorized');
}

// Prepare template data
$headers = [
    'Nama Kontingen',
    'Nama Lengkap',
    'NIK',
    'Jenis Kelamin',
    'Tanggal Lahir',
    'Tempat Lahir',
    'Nama Sekolah/Instansi',
    'Berat Badan (kg)',
    'Tinggi Badan (cm)'
];

       $sampleData = [
           [
               'Kontingen Jakarta Pusat',
               'Ahmad Rizki',
               '3171234567890001',
               'L',
               '2005-03-15',
               'Jakarta',
               'SMA Negeri 1 Jakarta',
               '65.5',
               '170.0'
           ],
           [
               'Kontingen Jakarta Selatan',
               'Siti Aminah',
               '3171234567890002',
               'P',
               '2006-07-20',
               'Jakarta',
               'SMA Negeri 2 Jakarta',
               '55.0',
               '160.0'
           ]
       ];

$filename = 'template_data_atlet';
$title = 'TEMPLATE DATA ATLET';
       $instructions = 'PETUNJUK: 1) Nama Kontingen harus sama persis dengan yang sudah dibuat di Akun Saya 2) Jenis Kelamin: L (Laki-laki) atau P (Perempuan) 3) Format Tanggal: YYYY-MM-DD 4) Berat Badan dan Tinggi Badan dalam angka';

// Pilihan format template
$format = $_GET['format'] ?? 'xlsx';

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$currentRow = 1;

// Add title
$sheet->mergeCells('A1:' . getColumnLetter(count($headers)) . '1');
$sheet->setCellValue('A1', $title);
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E3F2FD');
$currentRow = 2;

// Add instructions
$sheet->mergeCells('A' . $currentRow . ':' . getColumnLetter(count($headers)) . $currentRow);
$sheet->setCellValue('A' . $currentRow, $instructions);
$sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(11);
$sheet->getStyle('A' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3CD');
$currentRow++;

// Add headers
$headerRow = $currentRow;
foreach ($headers as $colIndex => $header) {
    $columnLetter = getColumnLetter($colIndex + 1);
    $sheet->setCellValue($columnLetter . $headerRow, $header);
    $sheet->getStyle($columnLetter . $headerRow)->getFont()->setBold(true);
    $sheet->getStyle($columnLetter . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
    $sheet->getStyle($columnLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}
$currentRow++;

// Add sample data
foreach ($sampleData as $rowIndex => $row) {
    foreach ($row as $colIndex => $value) {
        $columnLetter = getColumnLetter($colIndex + 1);
        $sheet->setCellValue($columnLetter . $currentRow, $value);
    }
    $currentRow++;
}

// Auto-size columns
foreach (range('A', getColumnLetter(count($headers))) as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Add borders
$lastRow = $currentRow - 1;
$lastColumn = getColumnLetter(count($headers));
$sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Set headers for download
if ($format === 'xls') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    $writer = new Xls($spreadsheet);
} else {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    $writer = new Xlsx($spreadsheet);
}

header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;

// Helper function to convert column index to letter
function getColumnLetter($columnIndex) {
    $letter = '';
    while ($columnIndex > 0) {
        $columnIndex--;
        $letter = chr(65 + ($columnIndex % 26)) . $letter;
        $columnIndex = intval($columnIndex / 26);
    }
    return $letter;
} 