<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$competition_id = $_GET['competition_id'] ?? 0;

if (!$competition_id) {
    echo json_encode(['success' => false, 'message' => 'Competition ID required']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
    $stmt->execute([$competition_id]);
    $competition_types = $stmt->fetchAll();
    
    // Add is_tanding flag to each competition type
    foreach ($competition_types as &$type) {
        $type['is_tanding'] = (stripos($type['nama_kompetisi'], 'tanding') !== false) ? 1 : 0;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $competition_types
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
