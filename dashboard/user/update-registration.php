<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit();
}

if ($_POST && isset($_POST['registration_id'])) {
    $registration_id = $_POST['registration_id'];
    $athlete_id = $_POST['athlete_id'] ?? 0;
    $age_category_id = $_POST['age_category_id'] ?? null;
    $category_id = $_POST['category_id'] ?? null;
    $competition_type_id = $_POST['competition_type_id'] ?? 0;
    
    try {
        // Verify user owns this registration and it's still pending or unpaid
        $stmt = $pdo->prepare("
            SELECT r.*, a.user_id as athlete_user_id, c.id as competition_id
            FROM registrations r
            JOIN athletes a ON r.athlete_id = a.id
            JOIN competitions c ON r.competition_id = c.id
            WHERE r.id = ? AND (r.payment_status = 'pending' OR r.payment_status = 'unpaid')
        ");
        $stmt->execute([$registration_id]);
        $registration = $stmt->fetch();
        
        if (!$registration) {
            throw new Exception('Pendaftaran tidak ditemukan atau tidak dapat diedit.');
        }
        
        if ($registration['athlete_user_id'] != $_SESSION['user_id']) {
            throw new Exception('Anda tidak memiliki akses untuk mengedit pendaftaran ini.');
        }
        
        // Validate new athlete belongs to user
        $stmt = $pdo->prepare("SELECT a.*, k.id as kontingen_id FROM athletes a JOIN kontingen k ON a.kontingen_id = k.id WHERE a.id = ? AND a.user_id = ?");
        $stmt->execute([$athlete_id, $_SESSION['user_id']]);
        $athlete = $stmt->fetch();
        
        if (!$athlete) {
            throw new Exception('Atlet tidak ditemukan atau bukan milik Anda.');
        }
        
        // Check if athlete already registered for this competition (excluding current registration)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE competition_id = ? AND athlete_id = ? AND id != ?");
        $stmt->execute([$registration['competition_id'], $athlete_id, $registration_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Atlet sudah terdaftar dalam perlombaan ini.');
        }
        
        // Get competition type to check if it's tanding
        $stmt = $pdo->prepare("SELECT * FROM competition_types WHERE id = ? AND competition_id = ?");
        $stmt->execute([$competition_type_id, $registration['competition_id']]);
        $competition_type = $stmt->fetch();
        
        if (!$competition_type) {
            throw new Exception('Jenis kompetisi tidak valid.');
        }
        
        $is_tanding = (stripos($competition_type['nama_kompetisi'], 'tanding') !== false);
        
        // Validate age category only for tanding competitions
        if ($is_tanding && $age_category_id) {
            $stmt = $pdo->prepare("SELECT * FROM age_categories WHERE id = ? AND competition_id = ?");
            $stmt->execute([$age_category_id, $registration['competition_id']]);
            $age_category = $stmt->fetch();
            
            if (!$age_category) {
                throw new Exception('Kategori umur tidak valid.');
            }
            
            // Check athlete age
            $athlete_age = date_diff(date_create($athlete['tanggal_lahir']), date_create('today'))->y;
            if ($athlete_age < $age_category['usia_min'] || $athlete_age > $age_category['usia_max']) {
                throw new Exception('Umur atlet tidak sesuai dengan kategori yang dipilih.');
            }
        }
        
        // Validate competition category if selected (only for tanding)
        if ($is_tanding && $category_id) {
            $stmt = $pdo->prepare("SELECT * FROM competition_categories WHERE id = ? AND competition_id = ?");
            $stmt->execute([$category_id, $registration['competition_id']]);
            $category = $stmt->fetch();
            
            if (!$category) {
                throw new Exception('Kategori tanding tidak valid.');
            }
            
            // Check weight if category has weight limits
            if (($category['berat_min'] && $athlete['berat_badan'] < $category['berat_min']) ||
                ($category['berat_max'] && $athlete['berat_badan'] > $category['berat_max'])) {
                throw new Exception('Berat badan atlet tidak sesuai dengan kategori yang dipilih.');
            }
        }
        
        // For non-tanding competitions, set category IDs to null
        if (!$is_tanding) {
            $age_category_id = null;
            $category_id = null;
        }
        
        // Update registration
        $stmt = $pdo->prepare("
            UPDATE registrations SET 
                kontingen_id = ?, 
                athlete_id = ?, 
                category_id = ?, 
                age_category_id = ?, 
                competition_type_id = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $athlete['kontingen_id'], 
            $athlete_id, 
            $category_id, 
            $age_category_id, 
            $competition_type_id,
            $registration_id
        ]);
        
        sendNotification('Pendaftaran berhasil diperbarui!', 'success');
        
    } catch (Exception $e) {
        sendNotification('Gagal memperbarui pendaftaran: ' . $e->getMessage(), 'error');
    }
}

header('Location: perlombaan.php?tab=registered-athletes&updated=1');
exit();
?>
