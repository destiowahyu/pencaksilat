<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$filter_id = $data['filter_id'] ?? null;

if (!$filter_id) {
    echo json_encode(['success' => false, 'message' => 'Filter ID tidak ditemukan']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Hapus hasil acak dari database
    $stmt = $pdo->prepare("DELETE FROM daftar_peserta_draws WHERE filter_id = ?");
    $stmt->execute([$filter_id]);
    
    // Hapus jumlah acak dari session
    if (isset($_SESSION['acak_count'][$filter_id])) {
        unset($_SESSION['acak_count'][$filter_id]);
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Hasil acak berhasil direset']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 