<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$filter_id = $data['filter_id'] ?? null;
$urutan = $data['urutan'] ?? [];

if (!$filter_id || empty($urutan)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Simpan urutan hasil acak ke daftar_peserta_draws
    $stmt = $pdo->prepare("INSERT INTO daftar_peserta_draws (filter_id, urutan, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$filter_id, json_encode($urutan)]);
    
    // Simpan jumlah acak ke session
    $acak_count = $data['acak_count'] ?? 1;
    if (!isset($_SESSION['acak_count'])) {
        $_SESSION['acak_count'] = [];
    }
    $_SESSION['acak_count'][$filter_id] = $acak_count;
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Hasil acak berhasil disimpan']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 