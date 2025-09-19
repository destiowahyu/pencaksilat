<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    exit();
}

$competition_id = $_GET['competition_id'] ?? 0;
$type = $_GET['type'] ?? '';
$age_category_id = $_GET['age_category_id'] ?? 0;

try {
    $data = [];
    
    switch ($type) {
        case 'age_categories':
            $stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
            $stmt->execute([$competition_id]);
            $data = $stmt->fetchAll();
            break;
            
        case 'competition_categories':
            if ($age_category_id) {
                $stmt = $pdo->prepare("
                    SELECT * FROM competition_categories 
                    WHERE competition_id = ? AND age_category_id = ?
                    ORDER BY nama_kategori
                ");
                $stmt->execute([$competition_id, $age_category_id]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT * FROM competition_categories 
                    WHERE competition_id = ?
                    ORDER BY nama_kategori
                ");
                $stmt->execute([$competition_id]);
            }
            $data = $stmt->fetchAll();
            break;
            
        case 'competition_types':
            $stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
            $stmt->execute([$competition_id]);
            $data = $stmt->fetchAll();
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
