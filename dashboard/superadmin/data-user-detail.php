<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

$user_id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? 'view';

// Handle form submission for edit user
if ($_POST && isset($_POST['edit_user'])) {
    $nama = sanitizeInput($_POST['nama']);
    $email = sanitizeInput($_POST['email']);
    $whatsapp = sanitizeInput($_POST['whatsapp']);
    $alamat = sanitizeInput($_POST['alamat']);
    
    $sql = "UPDATE users SET nama = ?, email = ?, whatsapp = ?, alamat = ?";
    $params = [$nama, $email, $whatsapp, $alamat];
    
    // Update password if provided
    if (!empty($_POST['password'])) {
        $sql .= ", password = ?";
        $params[] = hashPassword($_POST['password']);
    }
    
    $sql .= " WHERE id = ? AND role = 'user'";
    $params[] = $user_id;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        sendNotification('Data user berhasil diperbarui!', 'success');
        
        // Send notification to user
        sendUserUpdateNotification($email, $whatsapp, $nama);
        
        header("Location: data-user-detail.php?id=$user_id");
        exit();
    } catch (PDOException $e) {
        sendNotification('Gagal memperbarui data user!', 'error');
    }
}

// Handle form submission for edit kontingen
if ($_POST && isset($_POST['edit_kontingen'])) {
    $kontingen_id = $_POST['kontingen_id'];
    $nama_kontingen = sanitizeInput($_POST['nama_kontingen']);
    $provinsi = sanitizeInput($_POST['provinsi']);
    $kota = sanitizeInput($_POST['kota']);
    
    try {
        $stmt = $pdo->prepare("UPDATE kontingen SET nama_kontingen = ?, provinsi = ?, kota = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$nama_kontingen, $provinsi, $kota, $kontingen_id, $user_id]);
        sendNotification('Data kontingen berhasil diperbarui!', 'success');
        
        header("Location: data-user-detail.php?id=$user_id&action=edit");
        exit();
    } catch (PDOException $e) {
        sendNotification('Gagal memperbarui data kontingen!', 'error');
    }
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'user'");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: data-user.php');
    exit();
}

// Get user's kontingen data
$stmt = $pdo->prepare("
    SELECT k.*, COUNT(a.id) as total_athletes 
    FROM kontingen k 
    LEFT JOIN athletes a ON k.id = a.kontingen_id 
    WHERE k.user_id = ? 
    GROUP BY k.id 
    ORDER BY k.created_at DESC
");
$stmt->execute([$user_id]);
$kontingen = $stmt->fetchAll();

// Get user's kontingen data for edit mode
if ($action == 'edit') {
    $stmt = $pdo->prepare("SELECT * FROM kontingen WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $kontingen_edit = $stmt->fetchAll();
}

$notification = getNotification();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - SuperAdmin Panel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php if ($notification): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert('<?php echo $notification['message']; ?>', '<?php echo $notification['type']; ?>');
        });
    </script>
    <?php endif; ?>

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
            <li><a href="data-user.php" class="active"><i class="fas fa-users"></i> Data User</a></li>
            <li><a href="data-kontingen.php"><i class="fas fa-flag"></i> Data Kontingen</a></li>
            <li><a href="perlombaan.php"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <?php echo $action == 'edit' ? 'Edit' : 'Detail'; ?> User: <?php echo htmlspecialchars($user['nama']); ?>
            </h1>
            <p class="page-subtitle">
                <a href="data-user.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data User
                </a>
            </p>
        </div>

        <?php if ($action == 'edit'): ?>
        <!-- Edit User Form -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Edit Informasi User</h2>
                <div class="table-actions">
                    <a href="data-user-detail.php?id=<?php echo $user_id; ?>" class="btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </div>
            <form method="POST" style="padding: 30px;">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="whatsapp">No. WhatsApp</label>
                        <input type="tel" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($user['whatsapp']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="password">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" id="password" name="password" placeholder="Masukkan password baru">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="edit_user" class="btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="data-user-detail.php?id=<?php echo $user_id; ?>" class="btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
</div>

<!-- Edit Kontingen Forms -->
<?php if (!empty($kontingen_edit)): ?>
<div class="table-container" style="margin-top: 30px;">
    <div class="table-header">
        <h2 class="table-title">Edit Data Kontingen</h2>
        <p class="table-subtitle">Edit informasi kontingen yang dimiliki user ini</p>
    </div>
    
    <?php foreach ($kontingen_edit as $index => $k): ?>
    <div class="kontingen-edit-card">
        <div class="kontingen-edit-header">
            <h4><i class="fas fa-flag"></i> Kontingen <?php echo $index + 1; ?>: <?php echo htmlspecialchars($k['nama_kontingen']); ?></h4>
        </div>
        <form method="POST" style="padding: 20px;">
            <input type="hidden" name="kontingen_id" value="<?php echo $k['id']; ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_kontingen_<?php echo $k['id']; ?>">Nama Kontingen</label>
                    <input type="text" id="nama_kontingen_<?php echo $k['id']; ?>" name="nama_kontingen" 
                           value="<?php echo htmlspecialchars($k['nama_kontingen']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="provinsi_<?php echo $k['id']; ?>">Provinsi</label>
                    <select id="provinsi_<?php echo $k['id']; ?>" name="provinsi" required>
                        <option value="">Pilih Provinsi</option>
                        <?php 
                        $provinces = [
                            'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 
                            'Jambi', 'Sumatera Selatan', 'Kepulauan Bangka Belitung', 'Bengkulu', 'Lampung',
                            'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Banten',
                            'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
                            'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
                            'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Gorontalo', 'Sulawesi Barat',
                            'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat'
                        ];
                        foreach ($provinces as $province): ?>
                            <option value="<?php echo $province; ?>" <?php echo ($k['provinsi'] == $province) ? 'selected' : ''; ?>>
                                <?php echo $province; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="kota_<?php echo $k['id']; ?>">Kota/Kabupaten</label>
                    <input type="text" id="kota_<?php echo $k['id']; ?>" name="kota" 
                           value="<?php echo htmlspecialchars($k['kota']); ?>" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" name="edit_kontingen" class="btn-primary">
                    <i class="fas fa-save"></i> Simpan Kontingen
                </button>
            </div>
        </form>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

        <?php else: ?>
        <!-- View User Details -->
        <div class="detail-container">
            <!-- User Information -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3><i class="fas fa-user"></i> Informasi User</h3>
                    <div class="detail-actions">
                        <a href="data-user-detail.php?id=<?php echo $user_id; ?>&action=edit" class="btn-primary">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $user['whatsapp']); ?>" 
                           target="_blank" class="btn-success">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <a href="mailto:<?php echo $user['email']; ?>" class="btn-info">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                </div>
                <div class="detail-content">
                    <!-- User Information -->
                    <div class="info-section">
                        <h4><i class="fas fa-user-circle"></i> Data Personal</h4>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>ID User:</label>
                                <span class="user-id"><?php echo $user['id']; ?></span>
                            </div>
                            <div class="info-item">
                                <label>Nama Lengkap:</label>
                                <span><?php echo htmlspecialchars($user['nama']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Email:</label>
                                <span><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>No. WhatsApp:</label>
                                <span><?php echo htmlspecialchars($user['whatsapp']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Alamat:</label>
                                <span><?php echo htmlspecialchars($user['alamat']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Terdaftar:</label>
                                <span><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Terakhir Update:</label>
                                <span><?php echo date('d M Y H:i', strtotime($user['updated_at'])); ?></span>
                            </div>
                            <?php if (!empty($kontingen)): ?>
                                <div class="info-item kontingen-info-section">
                                    <label>Data Kontingen:</label>
                                    <div class="kontingen-list">
                                        <?php foreach ($kontingen as $k): ?>
                                            <div class="kontingen-item">
                                                <div class="kontingen-details">
                                                    <strong><?php echo htmlspecialchars($k['nama_kontingen']); ?></strong>
                                                    <span class="kontingen-meta">
                                                        <?php echo htmlspecialchars($k['provinsi']); ?> - <?php echo htmlspecialchars($k['kota']); ?>
                                                    </span>
                                                    <span class="kontingen-athletes">
                                                        <i class="fas fa-user-ninja"></i> <?php echo $k['total_athletes']; ?> Atlet
                                                    </span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="info-item">
                                    <label>Data Kontingen:</label>
                                    <span class="no-data">Belum memiliki kontingen</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        // Form validation for edit
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('confirm_password');
                    
                    if (password && confirmPassword) {
                        if (password.value && password.value !== confirmPassword.value) {
                            e.preventDefault();
                            showAlert('Password dan konfirmasi password tidak cocok!', 'error');
                            return false;
                        }
                        
                        if (password.value && password.value.length < 6) {
                            e.preventDefault();
                            showAlert('Password minimal 6 karakter!', 'error');
                            return false;
                        }
                    }
                });
            }
        });
    </script>

    <style>
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .detail-container {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .detail-card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .detail-header {
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .detail-header h3 {
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .detail-content {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .info-section h4 {
            color: var(--primary-color);
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
        
        .user-id {
            background: var(--light-color);
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            color: var(--primary-color) !important;
            display: inline-block;
            width: fit-content;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-info {
            background: #3b82f6;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-info:hover {
            background: #2563eb;
        }
        
        @media (max-width: 768px) {
            .detail-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .detail-actions {
                justify-content: center;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }

        .kontingen-info-section {
            grid-column: 1 / -1; /* Full width */
        }

        .kontingen-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }

        .kontingen-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
        }

        .kontingen-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .kontingen-details strong {
            color: var(--primary-color);
            font-size: 1rem;
        }

        .kontingen-meta {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .kontingen-athletes {
            color: var(--success-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .no-data {
            color: var(--text-light);
            font-style: italic;
        }

        @media (max-width: 768px) {
            .kontingen-info-section {
                grid-column: 1;
            }
        }

        .kontingen-edit-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
}

.kontingen-edit-header {
    background: #f8fafc;
    padding: 15px 20px;
    border-bottom: 1px solid var(--border-color);
}

.kontingen-edit-header h4 {
    margin: 0;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1rem;
}

.table-subtitle {
    color: var(--text-light);
    margin: 5px 0 0 0;
    font-size: 0.9rem;
}
    </style>
</body>
</html>
