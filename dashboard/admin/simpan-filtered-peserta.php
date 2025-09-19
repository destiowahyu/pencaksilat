<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('debug_simpan.txt', print_r($data, true)); // DEBUG: cek data POST
$peserta_ids = $data['peserta_ids'] ?? [];

if (!is_array($peserta_ids) || empty($peserta_ids)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

$kategori_umur = $data['kategori_umur'] ?? '';
$jenis_kelamin = $data['jenis_kelamin'] ?? '';
$jenis_kompetisi = $data['jenis_kompetisi'] ?? '';
$kategori_tanding = $data['kategori_tanding'] ?? '';

// Format batch_name sesuai format keterangan filter
$batch_parts = [];

// Jenis Kompetisi
if ($jenis_kompetisi) {
    $batch_parts[] = strtoupper($jenis_kompetisi);
}

// Kategori Tanding (jika jenis kompetisi tanding)
if ($jenis_kompetisi && stripos($jenis_kompetisi, 'tanding') !== false && $kategori_tanding) {
    $batch_parts[] = strtoupper($kategori_tanding);
}

// Kategori Umur
if ($kategori_umur) {
    $batch_parts[] = strtoupper($kategori_umur);
}

// Jenis Kelamin
if ($jenis_kelamin) {
    $jenis_kelamin_text = ($jenis_kelamin == 'L') ? 'PUTRA' : (($jenis_kelamin == 'P') ? 'PUTRI' : strtoupper($jenis_kelamin));
    $batch_parts[] = $jenis_kelamin_text;
}

$batch_name = !empty($batch_parts) ? implode(' / ', $batch_parts) : 'Batch Peserta';

try {
    $pdo->beginTransaction();
    // Buat filter_id baru (auto increment)
    $stmt = $pdo->prepare("SELECT MAX(filter_id) FROM daftar_peserta_filtered");
    $stmt->execute();
    $max_id = $stmt->fetchColumn();
    $filter_id = $max_id ? $max_id + 1 : 1;

    // Simpan ke tabel batch
    $stmt = $pdo->prepare("INSERT INTO daftar_peserta_filter_batches (filter_id, batch_name, created_at, kategori_umur, jenis_kelamin, jenis_kompetisi, kategori_tanding) VALUES (?, ?, NOW(), ?, ?, ?, ?)");
    $stmt->execute([$filter_id, $batch_name, $kategori_umur, $jenis_kelamin, $jenis_kompetisi, $kategori_tanding]);

    foreach ($peserta_ids as $peserta_id) {
        $stmt = $pdo->prepare("INSERT INTO daftar_peserta_filtered (filter_id, peserta_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$filter_id, $peserta_id]);
    }
    // Simpan urutan hasil acak ke daftar_peserta_draws jika ada
    $urutan = $data['urutan'] ?? [];
    if (!empty($urutan)) {
        $stmt = $pdo->prepare("INSERT INTO daftar_peserta_draws (filter_id, urutan, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$filter_id, json_encode($urutan)]);
        file_put_contents('debug_draws.txt', print_r(['filter_id'=>$filter_id, 'urutan'=>$urutan], true), FILE_APPEND);
    }
    $pdo->commit();
    echo json_encode(['success' => true, 'filter_id' => $filter_id]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>