<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';
require_once '../../lib/ExcelHelper.php';

use App\ExcelHelper;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$competition_id = $_GET['competition_id'] ?? 0;
$type = $_GET['type'] ?? 'all';

if (!$competition_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Competition ID is required']);
    exit();
}

$where = "WHERE r.competition_id = ?";
if ($type === 'paid') {
    $where .= " AND r.payment_status IN ('paid', 'verified')";
}

$stmt = $pdo->prepare("
    SELECT 
        a.nama as athlete_name,
        a.jenis_kelamin,
        k.nama_kontingen,
        u.nama as penanggung_jawab,
        u.whatsapp as kontak_penanggung_jawab,
        cc.nama_kategori as category_name,
        r.payment_status,
        r.created_at as registration_date
    FROM registrations r
    JOIN athletes a ON r.athlete_id = a.id
    JOIN kontingen k ON a.kontingen_id = k.id
    JOIN users u ON k.user_id = u.id
    LEFT JOIN competition_categories cc ON r.category_id = cc.id
    $where
    ORDER BY r.created_at DESC
");
$stmt->execute([$competition_id]);
$registrations = $stmt->fetchAll();

// Prepare data for Excel
$headers = [
    'No',
    'Nama Atlet',
    'Jenis Kelamin',
    'Nama Kontingen',
    'Penanggung Jawab',
    'Kontak PJ',
    'Kategori',
    'Status Pembayaran',
    'Tanggal Daftar'
];

$data = [];
foreach ($registrations as $i => $reg) {
    $data[] = [
        $i + 1,
        $reg['athlete_name'],
        ($reg['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'),
        $reg['nama_kontingen'],
        $reg['penanggung_jawab'],
        $reg['kontak_penanggung_jawab'],
        $reg['category_name'] ?: '-',
        $reg['payment_status'],
        date('d-m-Y', strtotime($reg['registration_date']))
    ];
}

$filename = 'registrations_' . $type . '_' . $competition_id . '_' . date('Ymd_His');
$folder = '../../uploads/reports/';
$title = 'DATA PENDAFTARAN - ' . strtoupper($type) . ' - ' . date('d/m/Y H:i:s');

$filepath = ExcelHelper::createExcelFileXlsx($data, $headers, $filename, $folder, $title);

// Return public link (relative to web root)
$link = '/uploads/reports/' . $filename . '.xlsx';
echo json_encode(['success' => true, 'link' => $link]); 