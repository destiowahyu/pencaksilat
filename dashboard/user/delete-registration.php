<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $registration_id = $_POST['registration_id'];
    
    // Verify ownership and that payment is still pending
    $stmt = $pdo->prepare("
        SELECT r.*, a.nama as athlete_name FROM registrations r 
        JOIN athletes a ON r.athlete_id = a.id 
        WHERE r.id = ? AND a.user_id = ?
    ");
    $stmt->execute([$registration_id, $_SESSION['user_id']]);
    $registration = $stmt->fetch();
    
    if (!$registration) {
        echo json_encode(['success' => false, 'message' => 'Registration not found']);
        exit();
    }
    
    if ($registration['payment_status'] === 'verified') {
        echo json_encode(['success' => false, 'message' => 'Cannot delete verified registration']);
        exit();
    }
    
    // Delete the registration
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$registration_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pendaftaran ' . $registration['athlete_name'] . ' berhasil dihapus'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
