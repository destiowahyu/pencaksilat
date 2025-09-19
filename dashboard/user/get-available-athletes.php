<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$competition_id = $_GET['competition_id'] ?? null;
$registration_id = $_GET['registration_id'] ?? null;

if (!$competition_id || !$registration_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

try {
    // Get current registration data
    $stmt = $pdo->prepare("
        SELECT r.athlete_id, a.nama as athlete_name 
        FROM registrations r 
        JOIN athletes a ON r.athlete_id = a.id 
        WHERE r.id = ? AND r.competition_id = ? AND a.user_id = ?
    ");
    $stmt->execute([$registration_id, $competition_id, $_SESSION['user_id']]);
    $current_registration = $stmt->fetch();
    
    if (!$current_registration) {
        http_response_code(404);
        echo json_encode(['error' => 'Registration not found']);
        exit();
    }
    
    // Get all athletes owned by the user
    $stmt = $pdo->prepare("
        SELECT id, nama, jenis_kelamin, tanggal_lahir 
        FROM athletes 
        WHERE user_id = ? 
        ORDER BY nama
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $all_athletes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get athletes already registered in this competition (excluding current registration)
    $stmt = $pdo->prepare("
        SELECT athlete_id 
        FROM registrations 
        WHERE competition_id = ? AND id != ? AND payment_status != 'verified'
    ");
    $stmt->execute([$competition_id, $registration_id]);
    $registered_athlete_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Filter available athletes
    $available_athletes = [];
    foreach ($all_athletes as $athlete) {
        // Include if not registered in this competition OR if it's the current athlete being edited
        if (!in_array($athlete['id'], $registered_athlete_ids) || $athlete['id'] == $current_registration['athlete_id']) {
            $available_athletes[] = [
                'id' => $athlete['id'],
                'nama' => $athlete['nama'],
                'jenis_kelamin' => $athlete['jenis_kelamin'],
                'tanggal_lahir' => $athlete['tanggal_lahir']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'current_athlete_id' => $current_registration['athlete_id'],
        'current_athlete_name' => $current_registration['athlete_name'],
        'athletes' => $available_athletes
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?> 