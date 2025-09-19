<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $nama_kontingen = trim($_POST['nama_kontingen']);
            $provinsi = trim($_POST['provinsi']);
            $kota = trim($_POST['kota']);
            
            if (empty($nama_kontingen) || empty($provinsi) || empty($kota)) {
                $response['message'] = 'Semua field harus diisi!';
            } else {
                if ($action === 'add') {
                    // Check if kontingen name already exists for this user
                    $stmt = $pdo->prepare("SELECT id FROM kontingen WHERE nama_kontingen = ? AND user_id = ?");
                    $stmt->execute([$nama_kontingen, $user_id]);
                    
                    if ($stmt->fetch()) {
                        $response['message'] = 'Nama kontingen sudah ada!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO kontingen (nama_kontingen, provinsi, kota, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
                        if ($stmt->execute([$nama_kontingen, $provinsi, $kota, $user_id])) {
                            $response['success'] = true;
                            $response['message'] = 'Kontingen berhasil ditambahkan!';
                        } else {
                            $response['message'] = 'Gagal menambahkan kontingen!';
                        }
                    }
                } else { // edit
                    $kontingen_id = $_POST['kontingen_id'];
                    
                    // Verify ownership
                    $stmt = $pdo->prepare("SELECT id FROM kontingen WHERE id = ? AND user_id = ?");
                    $stmt->execute([$kontingen_id, $user_id]);
                    
                    if (!$stmt->fetch()) {
                        $response['message'] = 'Kontingen tidak ditemukan atau Anda tidak memiliki akses!';
                    } else {
                        // Check if new name conflicts with existing kontingen (excluding current one)
                        $stmt = $pdo->prepare("SELECT id FROM kontingen WHERE nama_kontingen = ? AND user_id = ? AND id != ?");
                        $stmt->execute([$nama_kontingen, $user_id, $kontingen_id]);
                        
                        if ($stmt->fetch()) {
                            $response['message'] = 'Nama kontingen sudah ada!';
                        } else {
                            $stmt = $pdo->prepare("UPDATE kontingen SET nama_kontingen = ?, provinsi = ?, kota = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                            if ($stmt->execute([$nama_kontingen, $provinsi, $kota, $kontingen_id, $user_id])) {
                                $response['success'] = true;
                                $response['message'] = 'Kontingen berhasil diperbarui!';
                            } else {
                                $response['message'] = 'Gagal memperbarui kontingen!';
                            }
                        }
                    }
                }
            }
        } elseif ($action === 'delete') {
            $kontingen_id = $_POST['kontingen_id'];
            
            // Verify ownership
            $stmt = $pdo->prepare("SELECT id FROM kontingen WHERE id = ? AND user_id = ?");
            $stmt->execute([$kontingen_id, $user_id]);
            
            if (!$stmt->fetch()) {
                $response['message'] = 'Kontingen tidak ditemukan atau Anda tidak memiliki akses!';
            } else {
                // Check if kontingen has registered athletes
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM athletes WHERE kontingen_id = ?");
                $stmt->execute([$kontingen_id]);
                $athlete_count = $stmt->fetchColumn();
                
                if ($athlete_count > 0) {
                    $response['message'] = 'Kontingen tidak dapat dihapus karena masih memiliki atlet terdaftar!';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM kontingen WHERE id = ? AND user_id = ?");
                    if ($stmt->execute([$kontingen_id, $user_id])) {
                        $response['success'] = true;
                        $response['message'] = 'Kontingen berhasil dihapus!';
                    } else {
                        $response['message'] = 'Gagal menghapus kontingen!';
                    }
                }
            }
        } else {
            $response['message'] = 'Aksi tidak valid!';
        }
    } catch (Exception $e) {
        $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Method tidak diizinkan!';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
