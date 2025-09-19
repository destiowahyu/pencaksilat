<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$participant_id = $_GET['id'] ?? 0;

if (!$participant_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid participant ID']);
    exit();
}

try {
    // Get participant data from registrations table - removed NIK
    $stmt = $pdo->prepare("
        SELECT r.*, a.nama, a.jenis_kelamin, a.tanggal_lahir, a.tempat_lahir, 
               a.nama_sekolah, a.berat_badan, a.tinggi_badan, k.nama_kontingen,
               ac.nama_kategori as age_category_name, ct.nama_kompetisi, cc.nama_kategori as category_name
        FROM registrations r
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON r.kontingen_id = k.id
        LEFT JOIN age_categories ac ON r.age_category_id = ac.id
        LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
        LEFT JOIN competition_categories cc ON r.category_id = cc.id
        WHERE r.id = ?
    ");
    $stmt->execute([$participant_id]);
    $participant = $stmt->fetch();

    if ($participant) {
        echo json_encode(['success' => true, 'participant' => $participant]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Participant not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 