<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use App\ExcelHelper;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$competition_id = $_GET['competition_id'] ?? 0;

// Verify admin has access to this competition
$stmt = $pdo->prepare("
    SELECT c.* FROM competitions c 
    JOIN competition_admins ca ON c.id = ca.competition_id 
    WHERE c.id = ? AND ca.admin_id = ?
");
$stmt->execute([$competition_id, $_SESSION['user_id']]);
$competition = $stmt->fetch();

if (!$competition) {
    header('Location: perlombaan.php');
    exit();
}

// Get age categories
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$age_categories = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$competition_id]);
$competition_types = $stmt->fetchAll();

// Get competition categories
$stmt = $pdo->prepare("SELECT * FROM competition_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$competition_categories = $stmt->fetchAll();

// Prepare template data - updated to match export-registrations.php columns
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

// Create sample data with user-friendly category names
$sampleData = [
    [
        1,
        'Ahmad Rizki',
        'L',
        '2005-03-15',
        'Jakarta',
        'SMA Negeri 1 Jakarta',
        '65.50',
        '170.00',
        'Kontingen Jakarta',
        'Budi Santoso',
        '081234567890',
        isset($age_categories[0]) ? $age_categories[0]['nama_kategori'] : 'Remaja (15-17 tahun)',
        isset($competition_types[0]) ? $competition_types[0]['nama_kompetisi'] : 'Tanding',
        isset($competition_categories[0]) ? $competition_categories[0]['nama_kategori'] : 'TANDING KELAS A (45-50 kg)',
        'paid',
        date('d-m-Y')
    ],
    [
        2,
        'Siti Aminah',
        'P',
        '2006-07-20',
        'Jakarta',
        'SMA Negeri 2 Jakarta',
        '55.00',
        '160.00',
        'Kontingen Jakarta',
        'Sari Indah',
        '081234567891',
        isset($age_categories[1]) ? $age_categories[1]['nama_kategori'] : 'Remaja (15-17 tahun)',
        isset($competition_types[1]) ? $competition_types[1]['nama_kompetisi'] : 'Tanding',
        isset($competition_categories[1]) ? $competition_categories[1]['nama_kategori'] : 'TANDING KELAS B (50-55 kg)',
        'pending',
        date('d-m-Y')
    ]
];

$filename = 'template_import_peserta_' . $competition['nama_perlombaan'];
$title = 'TEMPLATE IMPORT DATA PESERTA - ' . strtoupper($competition['nama_perlombaan']);

// Updated instructions without ID requirements
$instructions = '';

// Pilihan format template
$format = $_GET['format'] ?? 'xls';

\App\ExcelHelper::createAndDownloadFile($sampleData, $headers, $filename, $title . "\n" . $instructions, $format); 