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
    $competition_id = $_POST['competition_id'];
    $payment_note = $_POST['payment_note'] ?? '';
    
    // Verify user owns athletes in this competition
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM registrations r 
        JOIN athletes a ON r.athlete_id = a.id 
        WHERE r.competition_id = ? AND a.user_id = ? AND r.payment_status = 'unpaid'
    ");
    $stmt->execute([$competition_id, $_SESSION['user_id']]);
    
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'No pending registrations found']);
        exit();
    }
    
    // Upload payment proof
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit();
    }
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($_FILES['payment_proof']['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed']);
        exit();
    }
    
    if ($_FILES['payment_proof']['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum 5MB allowed']);
        exit();
    }
    
    // Create upload directory if not exists
    $uploadDir = '../../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileExtension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
    $fileName = 'kontingen_payment_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;
    
    if (!move_uploaded_file($_FILES['payment_proof']['tmp_name'], $targetPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit();
    }
    
    // Update all unpaid registrations for this user in this competition
    $stmt = $pdo->prepare("
        UPDATE registrations r 
        JOIN athletes a ON r.athlete_id = a.id 
        SET r.payment_proof = ?, r.payment_status = 'paid'
        WHERE r.competition_id = ? AND a.user_id = ? 
          AND r.payment_status = 'unpaid'
    ");
    $stmt->execute([$fileName, $competition_id, $_SESSION['user_id']]);
    
    $affectedRows = $stmt->rowCount();
    
    if ($affectedRows > 0) {
        echo json_encode([
            'success' => true, 
            'message' => "Bukti pembayaran kontingen berhasil diupload untuk {$affectedRows} pendaftaran"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No registrations updated']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
