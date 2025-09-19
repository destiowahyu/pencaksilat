<?php
session_start();
require_once '../config/database.php';

if ($_POST) {
    $nama = sanitizeInput($_POST['nama']);
    $email = sanitizeInput($_POST['email']);
    $whatsapp = sanitizeInput($_POST['whatsapp']);
    $alamat = sanitizeInput($_POST['alamat']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Kontingen data
    $nama_kontingen = sanitizeInput($_POST['nama_kontingen']);
    $provinsi = sanitizeInput($_POST['provinsi']);
    $kota = sanitizeInput($_POST['kota']);
    
    // Validation
    if (empty($nama) || empty($email) || empty($whatsapp) || empty($alamat) || empty($password) || 
        empty($nama_kontingen) || empty($provinsi) || empty($kota)) {
        sendNotification('Semua field harus diisi!', 'error');
        header('Location: ../index.php');
        exit();
    }
    
    if (!validateEmail($email)) {
        sendNotification('Format email tidak valid!', 'error');
        header('Location: ../index.php');
        exit();
    }
    
    if ($password !== $confirm_password) {
        sendNotification('Password dan konfirmasi password tidak cocok!', 'error');
        header('Location: ../index.php');
        exit();
    }
    
    if (strlen($password) < 6) {
        sendNotification('Password minimal 6 karakter!', 'error');
        header('Location: ../index.php');
        exit();
    }
    
    // Validate WhatsApp format
    if (!preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $whatsapp)) {
        sendNotification('Format nomor WhatsApp tidak valid! Gunakan format: 08xxxxxxxxx', 'error');
        header('Location: ../index.php');
        exit();
    }
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendNotification('Email sudah terdaftar!', 'error');
            header('Location: ../index.php');
            exit();
        }
        
        // Check if WhatsApp already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE whatsapp = ?");
        $stmt->execute([$whatsapp]);
        if ($stmt->fetch()) {
            sendNotification('Nomor WhatsApp sudah terdaftar!', 'error');
            header('Location: ../index.php');
            exit();
        }
        
        // Insert new user
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO users (nama, email, whatsapp, alamat, password, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->execute([$nama, $email, $whatsapp, $alamat, $hashedPassword]);
        $user_id = $pdo->lastInsertId();
        
        // Insert kontingen
        $stmt = $pdo->prepare("INSERT INTO kontingen (user_id, nama_kontingen, provinsi, kota) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $nama_kontingen, $provinsi, $kota]);
        
        // Commit transaction
        $pdo->commit();
        
        // Send welcome email
        $email_sent = sendWelcomeEmail($email, $nama, $nama_kontingen);
        
        // Send WhatsApp notification
        $whatsapp_sent = sendWhatsAppNotification($whatsapp, $nama, $nama_kontingen);
        
        $message = 'Registrasi berhasil! Silakan login dengan akun Anda.';
        if ($email_sent) {
            $message .= ' Email konfirmasi telah dikirim.';
        }
        if ($whatsapp_sent) {
            $message .= ' Notifikasi WhatsApp telah dikirim.';
        }
        
        sendNotification($message, 'success');
        header('Location: ../index.php');
        exit();
        
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollback();
        sendNotification('Terjadi kesalahan sistem!', 'error');
        header('Location: ../index.php');
        exit();
    }
}

header('Location: ../index.php');
exit();
?>
