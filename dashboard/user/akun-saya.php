<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
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
        $whatsapp = trim($_POST['whatsapp']);
        $alamat = trim($_POST['alamat']);
        
        if (empty($nama) || empty($email) || empty($whatsapp)) {
            $error = 'Nama, email, dan WhatsApp tidak boleh kosong!';
        } else {
            // Check if email already exists for other users
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $error = 'Email sudah digunakan oleh user lain!';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, whatsapp = ?, alamat = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$nama, $email, $whatsapp, $alamat, $user_id])) {
                    $_SESSION['nama'] = $nama;
                    $_SESSION['email'] = $email;
                    $message = 'Data profil berhasil diperbarui!';
                } else {
                    $error = 'Gagal memperbarui data profil!';
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
    <title>Akun Saya</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-fist-raised"></i>
                <span>User Panel</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="data-atlet.php"><i class="fas fa-user-ninja"></i> Data Atlet</a></li>
            <li><a href="perlombaan.php"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="akun-saya.php" class="active"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Akun Saya</h1>
            <p class="page-subtitle">Kelola informasi profil dan akun Anda</p>
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
                            <label for="nama">Nama Lengkap *</label>
                            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="whatsapp">WhatsApp *</label>
                            <input type="text" id="whatsapp" name="whatsapp" value="<?php echo htmlspecialchars($user['whatsapp']); ?>" required>
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

            <!-- Kontingen Management -->
            <div class="account-card">
                <div class="account-header">
                    <h3><i class="fas fa-flag"></i> Kelola Kontingen</h3>
                </div>
                <div class="kontingen-container">
                    <?php
                    // Get user's kontingen
                    $stmt = $pdo->prepare("SELECT * FROM kontingen WHERE user_id = ? ORDER BY created_at DESC");
                    $stmt->execute([$user_id]);
                    $kontingen_list = $stmt->fetchAll();
                    ?>
                    
                    <div class="kontingen-list">
                        <h4>Daftar Kontingen Anda:</h4>
                        <?php if (empty($kontingen_list)): ?>
                            <div class="empty-kontingen">
                                <i class="fas fa-flag"></i>
                                <p>Belum ada kontingen yang ditambahkan</p>
                                <small>Tambahkan kontingen untuk mengelola atlet dalam berbagai tim</small>
                            </div>
                        <?php else: ?>
                            <div class="kontingen-grid">
                                <?php foreach ($kontingen_list as $kontingen): ?>
                                    <div class="kontingen-item">
                                        <div class="kontingen-info">
                                            <h5><?php echo htmlspecialchars($kontingen['nama_kontingen']); ?></h5>
                                            <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($kontingen['provinsi']); ?>, <?php echo htmlspecialchars($kontingen['kota']); ?></p>
                                            <small>Dibuat: <?php echo date('d M Y', strtotime($kontingen['created_at'])); ?></small>
                                        </div>
                                        <div class="kontingen-actions">
                                            <button type="button" class="btn-edit-kontingen" onclick="editKontingen(<?php echo $kontingen['id']; ?>, '<?php echo htmlspecialchars(addslashes($kontingen['nama_kontingen'])); ?>', '<?php echo htmlspecialchars(addslashes($kontingen['provinsi'])); ?>', '<?php echo htmlspecialchars(addslashes($kontingen['kota'])); ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn-delete-kontingen" onclick="deleteKontingen(<?php echo $kontingen['id']; ?>, '<?php echo htmlspecialchars(addslashes($kontingen['nama_kontingen'])); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="add-kontingen-section">
                        <button type="button" class="btn-add-kontingen" onclick="showAddKontingenModal()">
                            <i class="fas fa-plus"></i> Tambah Kontingen Baru
                        </button>
                    </div>
                </div>
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
                        <span class="role-badge user">User</span>
                    </div>
                    <div class="info-item">
                        <label>Status Akun:</label>
                        <span class="status-badge active">Aktif</span>
                    </div>
                    <div class="info-item">
                        <label>Terdaftar:</label>
                        <span><?php echo date('d M Y H:i', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Terakhir Update:</label>
                        <span><?php echo $user['updated_at'] ? date('d M Y H:i', strtotime($user['updated_at'])) : 'Belum pernah'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Terakhir Login:</label>
                        <span><?php echo isset($_SESSION['last_login']) ? date('d M Y H:i', $_SESSION['last_login']) : '-'; ?></span>
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
                            <label for="current_password">Password Saat Ini *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru *</label>
                            <input type="password" id="new_password" name="new_password" minlength="6" required>
                            <small class="form-help">Minimal 6 karakter</small>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Konfirmasi Password Baru *</label>
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

    <!-- Add/Edit Kontingen Modal -->
    <div id="kontingenModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="kontingenModalTitle"><i class="fas fa-flag"></i> Tambah Kontingen Baru</h3>
                <span class="close" onclick="closeKontingenModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="kontingenForm" method="POST">
                    <input type="hidden" id="kontingenId" name="kontingen_id">
                    <div class="form-group">
                        <label for="kontingenNama">Nama Kontingen *</label>
                        <input type="text" id="kontingenNama" name="nama_kontingen" required placeholder="Contoh: Kontingen Jakarta Pusat">
                    </div>
                    <div class="form-group">
                        <label for="kontingenProvinsi">Provinsi *</label>
                        <select id="kontingenProvinsi" name="provinsi" required>
                            <option value="">Pilih Provinsi</option>
                            <?php foreach (getProvinces() as $province): ?>
                            <option value="<?php echo $province; ?>"><?php echo $province; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="kontingenKota">Kota/Kabupaten *</label>
                        <input type="text" id="kontingenKota" name="kota" required placeholder="Masukkan nama kota/kabupaten">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Simpan Kontingen
                        </button>
                        <button type="button" class="btn-secondary" onclick="closeKontingenModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteKontingenModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
                <span class="close" onclick="closeDeleteKontingenModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="delete-confirmation">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Hapus Kontingen?</h4>
                    <p>Apakah Anda yakin ingin menghapus kontingen <strong id="deleteKontingenName"></strong>?</p>
                    <p><strong>Peringatan:</strong> Semua atlet yang terdaftar dalam kontingen ini akan terpengaruh.</p>
                    <form id="deleteKontingenForm">
                        <input type="hidden" id="deleteKontingenId" name="kontingen_id">
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">
                                <i class="fas fa-trash"></i> Ya, Hapus
                            </button>
                            <button type="button" class="btn-secondary" onclick="closeDeleteKontingenModal()">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

        // Kontingen Management Functions
        function showAddKontingenModal() {
            document.getElementById('kontingenModalTitle').innerHTML = '<i class="fas fa-flag"></i> Tambah Kontingen Baru';
            document.getElementById('kontingenForm').reset();
            document.getElementById('kontingenId').value = '';
            document.getElementById('kontingenModal').style.display = 'block';
        }

        function editKontingen(id, nama, provinsi, kota) {
            document.getElementById('kontingenModalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Kontingen';
            document.getElementById('kontingenId').value = id;
            document.getElementById('kontingenNama').value = nama;
            document.getElementById('kontingenProvinsi').value = provinsi;
            document.getElementById('kontingenKota').value = kota;
            document.getElementById('kontingenModal').style.display = 'block';
        }

        function closeKontingenModal() {
            document.getElementById('kontingenModal').style.display = 'none';
        }

        function deleteKontingen(id, nama) {
            document.getElementById('deleteKontingenId').value = id;
            document.getElementById('deleteKontingenName').textContent = nama;
            document.getElementById('deleteKontingenModal').style.display = 'block';
        }

        function closeDeleteKontingenModal() {
            document.getElementById('deleteKontingenModal').style.display = 'none';
        }

        // Form submissions
        document.getElementById('kontingenForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const kontingenId = document.getElementById('kontingenId').value;
            
            // Add action parameter
            if (kontingenId) {
                formData.append('action', 'edit');
            } else {
                formData.append('action', 'add');
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
            
            fetch('manage-kontingen.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat menyimpan kontingen');
                console.error(error);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        document.getElementById('deleteKontingenForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'delete');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
            submitBtn.disabled = true;
            
            fetch('manage-kontingen.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan saat menghapus kontingen');
                console.error(error);
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = ['kontingenModal', 'deleteKontingenModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }
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
        /* Ensure all cards follow the two-column grid without forcing the last one full-width */
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
        .role-badge.user {
            background: #dbeafe;
            color: #2563eb;
        }
        .status-badge.active {
            background: #dcfce7;
            color: #166534;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            width: fit-content;
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
        .btn-primary {
            background: var(--primary-color);
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
        .btn-primary:hover {
            background: var(--primary-dark);
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
        /* Kontingen Management Styles */
        .kontingen-container {
            padding: 20px;
        }
        
        .kontingen-list h4 {
            color: var(--primary-color);
            margin: 0 0 20px 0;
            font-size: 1.1rem;
        }
        
        .empty-kontingen {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
            background: #f8fafc;
            border-radius: 10px;
            border: 2px dashed #cbd5e1;
        }
        
        .empty-kontingen i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.4;
        }
        
        .empty-kontingen p {
            margin: 0 0 10px 0;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .empty-kontingen small {
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .kontingen-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .kontingen-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .kontingen-item:hover {
            background: #f1f5f9;
            border-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.1);
        }
        
        .kontingen-info h5 {
            margin: 0 0 5px 0;
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .kontingen-info p {
            margin: 0 0 5px 0;
            color: var(--text-color);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .kontingen-info small {
            color: var(--text-light);
            font-size: 0.8rem;
        }
        
        .kontingen-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-edit-kontingen,
        .btn-delete-kontingen {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
        }
        
        .btn-edit-kontingen {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-edit-kontingen:hover {
            background: #d97706;
            transform: translateY(-1px);
        }
        
        .btn-delete-kontingen {
            background: var(--danger-color);
            color: white;
        }
        
        .btn-delete-kontingen:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }
        
        .add-kontingen-section {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .btn-add-kontingen {
            background: linear-gradient(135deg, #10b981, #059669, #047857);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        .btn-add-kontingen::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-add-kontingen:hover {
            background: linear-gradient(135deg, #059669, #047857, #065f46);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
        }
        
        .btn-add-kontingen:hover::before {
            left: 100%;
        }
        
        .btn-add-kontingen:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .close:hover {
            opacity: 0.7;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-body .form-group {
            margin-bottom: 20px;
        }
        
        .modal-body .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .modal-body .form-group input,
        .modal-body .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .modal-body .form-group input:focus,
        .modal-body .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .modal-body .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
        }
        
        .btn-secondary {
            background: #6b7280;
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
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn-danger {
            background: var(--danger-color);
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
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .delete-confirmation {
            text-align: center;
            padding: 20px 0;
        }
        
        .delete-confirmation i {
            font-size: 3rem;
            color: var(--danger-color);
            margin-bottom: 15px;
        }
        
        .delete-confirmation h4 {
            color: var(--text-color);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .delete-confirmation p {
            color: var(--text-light);
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .delete-confirmation .form-actions {
            justify-content: center;
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
            
            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
            
            .kontingen-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .kontingen-actions {
                align-self: flex-end;
            }
        }
    </style>
</body>
</html> 