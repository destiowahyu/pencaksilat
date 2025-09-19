<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';
require_once '../../lib/ExcelHelper.php';

use App\ExcelHelper;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized');
}

$competition_id = $_GET['competition_id'] ?? 0;
$type = $_GET['type'] ?? 'all'; // 'all' untuk status pendaftaran, 'paid' untuk data pendaftaran

if (!$competition_id) {
    die('Competition ID is required');
}

// Query data
$where = "WHERE r.competition_id = ?";
if ($type === 'paid') {
    $where .= " AND r.payment_status IN ('paid', 'verified')";
}

$stmt = $pdo->prepare("
    SELECT 
        a.nama as athlete_name,
        a.jenis_kelamin,
        a.tanggal_lahir,
        a.tempat_lahir,
        a.nama_sekolah,
        a.berat_badan,
        a.tinggi_badan,
        k.nama_kontingen,
        u.nama as penanggung_jawab,
        u.whatsapp as kontak_penanggung_jawab,
        ac.nama_kategori as age_category,
        ct.nama_kompetisi as competition_type,
        cc.nama_kategori as category_name,
        r.payment_status,
        r.created_at as registration_date
    FROM registrations r
    JOIN athletes a ON r.athlete_id = a.id
    JOIN kontingen k ON a.kontingen_id = k.id
    JOIN users u ON k.user_id = u.id
    LEFT JOIN competition_categories cc ON r.category_id = cc.id
    LEFT JOIN age_categories ac ON r.age_category_id = ac.id
    LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
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
    'Tanggal Lahir',
    'Tempat Lahir',
    'Nama Sekolah',
    'Berat Badan',
    'Tinggi Badan',
    'Nama Kontingen',
    'Penanggung Jawab',
    'Kontak PJ',
    'Kategori Umur',
    'Jenis Kompetisi',
    'Kategori Tanding',
    'Status Pembayaran',
    'Tanggal Daftar'
];

$data = [];
foreach ($registrations as $i => $reg) {
    $data[] = [
        $i + 1,
        $reg['athlete_name'],
        $reg['jenis_kelamin'],
        $reg['tanggal_lahir'] ? date('d-m-Y', strtotime($reg['tanggal_lahir'])) : '-',
        $reg['tempat_lahir'] ?: '-',
        $reg['nama_sekolah'] ?: '-',
        $reg['berat_badan'] ?: '-',
        $reg['tinggi_badan'] ?: '-',
        $reg['nama_kontingen'],
        $reg['penanggung_jawab'],
        $reg['kontak_penanggung_jawab'],
        $reg['age_category'] ?: '-',
        $reg['competition_type'] ?: '-',
        $reg['category_name'] ?: ($reg['competition_type'] && stripos($reg['competition_type'], 'tanding') !== false ? 'Belum dipilih' : 'Tidak berlaku'),
        $reg['payment_status'],
        date('d-m-Y', strtotime($reg['registration_date']))
    ];
}

$filename = 'registrations_' . $type . '_' . $competition_id;
$title = 'DATA PENDAFTARAN - ' . strtoupper($type) . ' - ' . date('d/m/Y');

// Pilihan format export - default ke XLSX untuk admin
$format = $_GET['format'] ?? 'xlsx';

\App\ExcelHelper::createAndDownloadFile($data, $headers, $filename, $title, $format); 