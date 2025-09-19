<?php
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';
use App\ExcelHelper;

$filter_id = $_GET['filter_id'] ?? '';
$kategori_umur = $_GET['kategori_umur'] ?? '';
$jenis_kelamin = $_GET['jenis_kelamin'] ?? '';
$jenis_kompetisi = $_GET['jenis_kompetisi'] ?? '';
$kategori_tanding = $_GET['kategori_tanding'] ?? '';
if (!$filter_id) die('Filter ID tidak ditemukan');

// Cek apakah ada hasil acak untuk filter_id ini
$stmt = $pdo->prepare("SELECT urutan FROM daftar_peserta_draws WHERE filter_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$filter_id]);
$draw_result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($draw_result && $draw_result['urutan']) {
    // Jika ada hasil acak, gunakan urutan acak
    $urutan_acak = json_decode($draw_result['urutan'], true);
    
    // Ambil data peserta sesuai urutan acak
    $sql = "SELECT p.* FROM daftar_peserta p WHERE p.id IN (" . str_repeat('?,', count($urutan_acak) - 1) . "?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($urutan_acak);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Urutkan data sesuai urutan acak
    $data_ordered = [];
    foreach ($urutan_acak as $peserta_id) {
        foreach ($data as $row) {
            if ($row['id'] == $peserta_id) {
                $data_ordered[] = $row;
                break;
            }
        }
    }
    $data = $data_ordered;
    
    $filename = 'hasil_acak_batch_' . $filter_id;
    $title = 'Hasil Acak Batch #' . $filter_id . ' (Urutan Setelah Diacak)';
} else {
    // Jika tidak ada hasil acak, gunakan urutan asli
    $sql = "SELECT p.* FROM daftar_peserta_filtered f JOIN daftar_peserta p ON f.peserta_id = p.id WHERE f.filter_id = ?";
    $params = [$filter_id];
    if ($kategori_umur) {
        $sql .= " AND p.kategori_umur = ?";
        $params[] = $kategori_umur;
    }
    if ($jenis_kelamin) {
        $sql .= " AND p.jenis_kelamin = ?";
        $params[] = $jenis_kelamin;
    }
    if ($jenis_kompetisi) {
        $sql .= " AND p.jenis_kompetisi = ?";
        $params[] = $jenis_kompetisi;
    }
    if ($jenis_kompetisi && stripos($jenis_kompetisi, 'tanding') !== false && $kategori_tanding) {
        $sql .= " AND p.kategori_tanding = ?";
        $params[] = $kategori_tanding;
    }
    $sql .= " ORDER BY f.id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'data_batch_' . $filter_id;
    $title = 'Data Batch #' . $filter_id . ' (Urutan Asli)';
}

$headers = ['No', 'Nama', 'Jenis Kelamin', 'Tanggal Lahir', 'Tempat Lahir', 'Nama Sekolah', 'Berat Badan', 'Tinggi Badan', 'Kontingen', 'Kategori Umur', 'Jenis Kompetisi', 'Kategori Tanding'];
$rows = [];
foreach ($data as $i => $row) {
    $rows[] = [
        $i+1,
        $row['nama'],
        $row['jenis_kelamin'],
        $row['tanggal_lahir'],
        $row['tempat_lahir'],
        $row['nama_sekolah'],
        $row['berat_badan'],
        $row['tinggi_badan'],
        $row['kontingen'],
        $row['kategori_umur'],
        $row['jenis_kompetisi'],
        $row['kategori_tanding']
    ];
}

ExcelHelper::createAndDownloadFile($rows, $headers, $filename, $title, 'xlsx'); 