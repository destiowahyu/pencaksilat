<?php
session_start();
require_once '../config/database.php';

if ($_POST) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (!validateEmail($email)) {
        sendNotification('Format email tidak valid!', 'error');
        header('Location: ../index.php');
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch ($user['role']) {
                case 'superadmin':
                    header('Location: ../dashboard/superadmin/index.php');
                    break;
                case 'admin':
                    header('Location: ../dashboard/admin/index.php');
                    break;
                case 'user':
                    header('Location: ../dashboard/user/index.php');
                    break;
                default:
                    header('Location: ../index.php');
            }
            exit();
        } else {
            sendNotification('Email atau password salah!', 'error');
            header('Location: ../index.php');
            exit();
        }
    } catch (PDOException $e) {
        sendNotification('Terjadi kesalahan sistem!', 'error');
        header('Location: ../index.php');
        exit();
    }
}

header('Location: ../index.php');
exit();
?>
