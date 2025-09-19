<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$competition_id = $_GET['competition_id'] ?? 0;
$age_category_id = $_GET['age_category_id'] ?? 0;

if (!$competition_id) {
    echo json_encode(['success' => false, 'message' => 'Competition ID required']);
    exit();
}

try {
    if ($age_category_id) {
        // Get categories for specific age category
        $stmt = $pdo->prepare("
            SELECT cc.*, ac.nama_kategori as age_category_name, ac.usia_min, ac.usia_max
            FROM competition_categories cc 
            LEFT JOIN age_categories ac ON cc.age_category_id = ac.id 
            WHERE cc.competition_id = ? AND cc.age_category_id = ?
            ORDER BY cc.nama_kategori
        ");
        $stmt->execute([$competition_id, $age_category_id]);
    } else {
        // Get all categories for competition
        $stmt = $pdo->prepare("
            SELECT cc.*, ac.nama_kategori as age_category_name, ac.usia_min, ac.usia_max
            FROM competition_categories cc 
            LEFT JOIN age_categories ac ON cc.age_category_id = ac.id 
            WHERE cc.competition_id = ?
            ORDER BY cc.nama_kategori
        ");
        $stmt->execute([$competition_id]);
    }
    
    $categories = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $categories
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
