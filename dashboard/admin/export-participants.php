<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Ambil filter dari GET
//$competition_id = $_GET['competition_id'] ?? 0; // HAPUS
$filter_age_category = $_GET['age_category'] ?? '';
$filter_competition_type = $_GET['competition_type'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_gender = $_GET['gender'] ?? '';
$search_nama = $_GET['search_nama'] ?? '';
$search_kontingen = $_GET['search_kontingen'] ?? '';
$peserta_ids = isset($_GET['peserta_ids']) ? explode(',', $_GET['peserta_ids']) : [];

$where = [];
$params = [];
if (!empty($peserta_ids)) {
    $in = implode(',', array_fill(0, count($peserta_ids), '?'));
    $where[] = 'id IN (' . $in . ')';
    $params = array_merge($params, $peserta_ids);
} else {
    if ($filter_age_category) {
        $where[] = 'kategori_umur = ?';
        $params[] = $filter_age_category;
    }
    if ($filter_competition_type) {
        $where[] = 'jenis_kompetisi = ?';
        $params[] = $filter_competition_type;
    }
    if ($filter_category) {
        $where[] = 'kategori_tanding = ?';
        $params[] = $filter_category;
    }
    if ($filter_gender) {
        $where[] = 'jenis_kelamin = ?';
        $params[] = $filter_gender;
    }
    if ($search_nama) {
        $where[] = 'nama LIKE ?';
        $params[] = "%$search_nama%";
    }
    if ($search_kontingen) {
        $where[] = 'kontingen LIKE ?';
        $params[] = "%$search_kontingen%";
    }
}
$sql = 'SELECT * FROM peserta_pertandingan';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY nama';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$headers = ['No', 'Nama', 'Jenis Kelamin', 'Tanggal Lahir', 'Tempat Lahir', 'Nama Sekolah', 'Berat Badan', 'Tinggi Badan', 'Kontingen', 'Kategori Umur', 'Jenis Kompetisi', 'Kategori Tanding'];
$sheet->fromArray($headers, null, 'A1');

// Data
$rowNum = 2;
foreach ($rows as $i => $row) {
    $sheet->fromArray([
        $i+1,
        $row['nama'],
        $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan',
        $row['tanggal_lahir'],
        $row['tempat_lahir'],
        $row['nama_sekolah'],
        $row['berat_badan'],
        $row['tinggi_badan'],
        $row['kontingen'],
        $row['kategori_umur'],
        $row['jenis_kompetisi'],
        $row['kategori_tanding'],
    ], null, 'A'.$rowNum);
    $rowNum++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="peserta_pertandingan.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 