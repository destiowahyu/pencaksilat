<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $alamat = trim($_POST['alamat']);
        
        if (empty($nama) || empty($email)) {
            $error = 'Nama dan email tidak boleh kosong!';
        } else {
            // Check if email already exists for other users
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $error = 'Email sudah digunakan oleh user lain!';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, alamat = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$nama, $email, $alamat, $user_id])) {
                    $_SESSION['nama'] = $nama;
                    $_SESSION['email'] = $email;
                    $message = 'Data akun berhasil diperbarui!';
                } else {
                    $error = 'Gagal memperbarui data akun!';
                }
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = 'Semua field password harus diisi!';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Konfirmasi password tidak cocok!';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!password_verify($current_password, $user['password'])) {
                $error = 'Password saat ini salah!';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$hashed_password, $user_id])) {
                    $message = 'Password berhasil diubah!';
                } else {
                    $error = 'Gagal mengubah password!';
                }
            }
        }
    }
}

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - SuperAdmin Panel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-fist-raised"></i>
                <span>SuperAdmin Panel</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="data-admin.php"><i class="fas fa-users-cog"></i> Data Admin</a></li>
            <li><a href="data-user.php"><i class="fas fa-users"></i> Data User</a></li>
            <li><a href="data-kontingen.php"><i class="fas fa-flag"></i> Data Kontingen</a></li>
            <li><a href="perlombaan.php"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="akun-saya.php" class="active"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Akun Saya</h1>
            <p class="page-subtitle">Kelola informasi akun SuperAdmin Anda</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <div class="account-container">
            <!-- Profile Information -->
            <div class="account-card">
                <div class="account-header">
                    <h3><i class="fas fa-user"></i> Informasi Profil</h3>
                </div>
                <form method="POST" class="account-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="alamat">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Account Information -->
            <div class="account-card">
                <div class="account-header">
                    <h3><i class="fas fa-info-circle"></i> Informasi Akun</h3>
                </div>
                <div class="account-info">
                    <div class="info-item">
                        <label>User ID:</label>
                        <span><?php echo $user['id']; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Role:</label>
                        <span class="role-badge superadmin">SuperAdmin</span>
                    </div>
                    <div class="info-item">
                        <label>Terdaftar:</label>
                        <span><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Terakhir Update:</label>
                        <span><?php echo $user['updated_at'] ? date('d M Y H:i', strtotime($user['updated_at'])) : 'Belum pernah'; ?></span>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="account-card">
                <div class="account-header">
                    <h3><i class="fas fa-lock"></i> Ubah Password</h3>
                </div>
                <form method="POST" class="account-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" id="new_password" name="new_password" minlength="6" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="change_password" class="btn-warning">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>

    <style>
        .account-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .account-card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .account-card:last-child {
            grid-column: 1 / -1;
        }
        
        .account-header {
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
            background: var(--light-color);
        }
        
        .account-header h3 {
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .account-form {
            padding: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }
        
        .form-group input,
        .form-group textarea {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .account-info {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-item label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }
        
        .info-item span {
            color: var(--text-light);
            font-size: 1rem;
        }
        
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            width: fit-content;
        }
        
        .role-badge.superadmin {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        @media (max-width: 768px) {
            .account-container {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .account-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>
