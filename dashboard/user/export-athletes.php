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

$stmt = $pdo->prepare("
    SELECT a.*, k.nama_kontingen 
    FROM athletes a 
    LEFT JOIN kontingen k ON a.kontingen_id = k.id 
    WHERE a.user_id = ? 
    ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$athletes = $stmt->fetchAll();

// Prepare data for Excel
$headers = [
    'No',
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

$data = [];
foreach ($athletes as $i => $athlete) {
    $data[] = [
        $i + 1,
        $athlete['nama_kontingen'] ?: 'Tidak Ada',
        $athlete['nama'],
        $athlete['nik'],
        $athlete['jenis_kelamin'],
        $athlete['tanggal_lahir'],
        $athlete['tempat_lahir'],
        $athlete['nama_sekolah'],
        $athlete['berat_badan'],
        $athlete['tinggi_badan']
    ];
}

$filename = 'data_atlet_' . date('Ymd');
$title = 'DATA ATLET - ' . date('d/m/Y');

// Pilihan format export
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

// Add data
foreach ($data as $rowIndex => $row) {
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