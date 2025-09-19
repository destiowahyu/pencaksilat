<?php
/**
 * Download template CSV untuk import data peserta
 */

require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use App\ExcelHelper;

// Get competition ID from URL
$competition_id = $_GET['id'] ?? null;

if (!$competition_id) {
    die('Competition ID tidak ditemukan');
}

// Get competition details
$stmt = $pdo->prepare("SELECT nama_perlombaan FROM competitions WHERE id = ?");
$stmt->execute([$competition_id]);
$competition = $stmt->fetch();

if (!$competition) {
    die('Competition tidak ditemukan');
}

// Headers sesuai dengan yang diharapkan sistem
$headers = [
    'Nama',
    'Jenis Kelamin',
    'Tanggal Lahir',
    'Tempat Lahir',
    'Nama Sekolah',
    'Berat Badan',
    'Tinggi Badan',
    'Kontingen',
    'Kategori Umur',
    'Jenis Kompetisi',
    'Kategori Tanding'
];

// Sample data
$sampleData = [
    [
        'Ahmad Rizki',
        'L',
        '2005-03-15',
        'Jakarta',
        'SMA Negeri 1 Jakarta',
        '65.50',
        '170.00',
        'Kontingen Jakarta',
        'Remaja (15-17 tahun)',
        'Tanding',
        'Kelas A (45-50 kg)'
    ],
    [
        'Siti Aminah',
        'P',
        '2006-07-20',
        'Jakarta',
        'SMA Negeri 2 Jakarta',
        '55.00',
        '160.00',
        'Kontingen Jakarta',
        'Remaja (15-17 tahun)',
        'Tanding',
        'Kelas B (50-55 kg)'
    ]
];

// Create title and instructions
$title = "Template Import Data Peserta - " . $competition['nama_perlombaan'];
$instructions = "Isi data sesuai format di bawah. Kolom 1-5 wajib diisi. Jenis Kelamin: L (Laki-laki) atau P (Perempuan). Format tanggal: YYYY-MM-DD";

// Pilihan format template
$format = $_GET['format'] ?? 'csv';

\App\ExcelHelper::createAndDownloadFile($sampleData, $headers, 'template_import_peserta_' . $competition_id, $title . "\n" . $instructions, $format);

exit;
?> 