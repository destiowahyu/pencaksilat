<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        header('Location: ../../index.php');
        exit();
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// If GET: render a simple upload form for individual athlete payment
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $registration_id = $_GET['id'] ?? '';
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload Bukti Pembayaran</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../../assets/css/style.css">
    </head>
    <body>
        <div class="main-content" style="max-width:600px;margin:40px auto;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.08);padding:24px;">
            <h2 style="margin-top:0">Upload Bukti Pembayaran Atlet</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="type" value="athlete">
                <input type="hidden" name="registration_id" value="<?php echo htmlspecialchars($registration_id); ?>">
                <div class="form-group">
                    <label for="payment_proof">Pilih File Bukti Pembayaran (JPG/PNG/GIF)</label>
                    <input type="file" id="payment_proof" name="payment_proof" required>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <a href="perlombaan.php" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// From here on, assume POST and return JSON
header('Content-Type: application/json');

try {
    $type = $_POST['type'] ?? 'athlete';
    
    if ($type === 'kontingen') {
        // Handle kontingen payment upload
        $competition_id = $_POST['competition_id'];
        $payment_note = $_POST['payment_note'] ?? '';
        
        // Verify user owns athletes in this competition
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM registrations r 
            JOIN athletes a ON r.athlete_id = a.id 
            WHERE r.competition_id = ? AND a.user_id = ?
        ");
        $stmt->execute([$competition_id, $_SESSION['user_id']]);
        
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'message' => 'No registrations found']);
            exit();
        }
        
        // Upload payment proof
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            exit();
        }
        
        $fileName = uploadFile($_FILES['payment_proof'], '../../uploads/');
        if (!$fileName) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit();
        }
        
        // Update all registrations for this user in this competition
        $stmt = $pdo->prepare("
            UPDATE registrations r 
            JOIN athletes a ON r.athlete_id = a.id 
            SET r.payment_proof = ?, r.payment_status = 'paid', r.payment_date = NOW(), r.payment_note = ?
            WHERE r.competition_id = ? AND a.user_id = ? AND r.payment_status = 'unpaid'
        ");
        $stmt->execute([$fileName, $payment_note, $competition_id, $_SESSION['user_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Bukti pembayaran kontingen berhasil diupload']);
        
    } else {
        // Handle individual athlete payment upload
        $registration_id = $_POST['registration_id'];
        
        // Verify ownership
        $stmt = $pdo->prepare("
            SELECT r.* FROM registrations r 
            JOIN athletes a ON r.athlete_id = a.id 
            WHERE r.id = ? AND a.user_id = ?
        ");
        $stmt->execute([$registration_id, $_SESSION['user_id']]);
        $registration = $stmt->fetch();
        
        if (!$registration) {
            echo json_encode(['success' => false, 'message' => 'Registration not found']);
            exit();
        }
        
        // Upload payment proof
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            exit();
        }
        
        $fileName = uploadFile($_FILES['payment_proof'], '../../uploads/');
        if (!$fileName) {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
            exit();
        }
        
        // Update registration
        $stmt = $pdo->prepare("
            UPDATE registrations 
            SET payment_proof = ?, payment_status = 'paid' 
            WHERE id = ?
        ");
        $stmt->execute([$fileName, $registration_id]);
        
        echo json_encode(['success' => true, 'message' => 'Bukti pembayaran berhasil diupload']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
