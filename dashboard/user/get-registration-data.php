<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['registration_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Registration ID required']);
    exit();
}

$registration_id = $_GET['registration_id'];

try {
    // Get registration data
    $stmt = $pdo->prepare("
        SELECT r.*, a.nama as athlete_name
        FROM registrations r 
        JOIN athletes a ON r.athlete_id = a.id 
        WHERE r.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$registration_id, $_SESSION['user_id']]);
    $registration = $stmt->fetch();
    
    if (!$registration) {
        echo json_encode(['success' => false, 'message' => 'Registration not found']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'age_category_id' => $registration['age_category_id'],
        'category_id' => $registration['category_id'],
        'competition_type_id' => $registration['competition_type_id'],
        'athlete_name' => $registration['athlete_name'],
        'competition_id' => $registration['competition_id']
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
