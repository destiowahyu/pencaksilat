<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$competition_id = $_POST['competition_id'] ?? 0;

try {
    // Get competition summary for user
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(r.id) as total_registrations,
            SUM(CASE WHEN r.payment_status = 'verified' THEN 1 ELSE 0 END) as verified_count,
            SUM(CASE WHEN r.payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN r.payment_status = 'unpaid' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN r.payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
            SUM(ct.biaya_pendaftaran) as total_cost
        FROM registrations r
        JOIN athletes a ON r.athlete_id = a.id
        JOIN competition_types ct ON r.competition_type_id = ct.id
        WHERE r.competition_id = ? AND a.user_id = ?
    ");
    $stmt->execute([$competition_id, $_SESSION['user_id']]);
    $summary = $stmt->fetch();
    
    echo json_encode($summary);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
