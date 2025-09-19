<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_admin'])) {
        $nama = sanitizeInput($_POST['nama']);
        $email = sanitizeInput($_POST['email']);
        $whatsapp = sanitizeInput($_POST['whatsapp']);
        $alamat = sanitizeInput($_POST['alamat']);
        $password = hashPassword($_POST['password']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (nama, email, whatsapp, alamat, password, role) VALUES (?, ?, ?, ?, ?, 'admin')");
            $stmt->execute([$nama, $email, $whatsapp, $alamat, $password]);
            sendNotification('Admin berhasil ditambahkan!', 'success');
        } catch (PDOException $e) {
            sendNotification('Gagal menambahkan admin!', 'error');
        }
        header('Location: data-admin.php');
        exit();
    }
    
    if (isset($_POST['edit_admin'])) {
        $id = $_POST['admin_id'];
        $nama = sanitizeInput($_POST['nama']);
        $email = sanitizeInput($_POST['email']);
        $whatsapp = sanitizeInput($_POST['whatsapp']);
        $alamat = sanitizeInput($_POST['alamat']);
        
        $sql = "UPDATE users SET nama = ?, email = ?, whatsapp = ?, alamat = ?";
        $params = [$nama, $email, $whatsapp, $alamat];
        
        if (!empty($_POST['password'])) {
            $sql .= ", password = ?";
            $params[] = hashPassword($_POST['password']);
        }
        
        $sql .= " WHERE id = ? AND role = 'admin'";
        $params[] = $id;
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            sendNotification('Data admin berhasil diperbarui!', 'success');
        } catch (PDOException $e) {
            sendNotification('Gagal memperbarui data admin!', 'error');
        }
        header('Location: data-admin.php');
        exit();
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        $stmt->execute([$_GET['id']]);
        sendNotification('Admin berhasil dihapus!', 'success');
    } catch (PDOException $e) {
        sendNotification('Gagal menghapus admin!', 'error');
    }
    header('Location: data-admin.php');
    exit();
}

// Get admin data
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC");
$admins = $stmt->fetchAll();

$notification = getNotification();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Admin - SuperAdmin Panel</title>
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
            <li><a href="data-admin.php" class="active"><i class="fas fa-users-cog"></i> Data Admin</a></li>
            <li><a href="data-user.php"><i class="fas fa-users"></i> Data User</a></li>
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
            <h1 class="page-title">Data Admin</h1>
            <p class="page-subtitle">Kelola data administrator sistem</p>
        </div>

        <!-- Add Admin Form -->
        <div class="table-container" style="margin-bottom: 30px;">
            <div class="table-header">
                <h2 class="table-title">Tambah Admin Baru</h2>
            </div>
            <form method="POST" style="padding: 30px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="whatsapp">No. WhatsApp</label>
                        <input type="tel" id="whatsapp" name="whatsapp" required>
                    </div>
                    <div class="form-group">
                        <label for="alamat">Alamat</label>
                        <textarea id="alamat" name="alamat" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <button type="submit" name="add_admin" class="btn-primary">
                    <i class="fas fa-plus"></i> Tambah Admin
                </button>
            </form>
        </div>

        <!-- Admin List -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Daftar Admin (<?php echo count($admins); ?>)</h2>
                <div class="search-box">
                    <input type="text" id="searchAdmin" placeholder="Cari admin...">
                </div>
            </div>
            <table class="data-table" id="adminTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. WhatsApp</th>
                        <th>Alamat</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $index => $admin): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($admin['nama']); ?></td>
                        <td><?php echo htmlspecialchars($admin['email']); ?></td>
                        <td><?php echo htmlspecialchars($admin['whatsapp']); ?></td>
                        <td><?php echo htmlspecialchars(substr($admin['alamat'], 0, 50)) . '...'; ?></td>
                        <td><?php echo date('d M Y', strtotime($admin['created_at'])); ?></td>
                        <td>
                            <button class="btn-action btn-edit" onclick="editAdmin(<?php echo $admin['id']; ?>)">
                                <i class="fas fa-edit"></i> Ubah
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteAdmin(<?php echo $admin['id']; ?>)">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Admin Modal -->
    <div id="editAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Data Admin</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editAdminForm" method="POST">
                <input type="hidden" id="edit_admin_id" name="admin_id">
                <div class="form-group">
                    <label for="edit_nama">Nama Lengkap</label>
                    <input type="text" id="edit_nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="edit_whatsapp">No. WhatsApp</label>
                    <input type="tel" id="edit_whatsapp" name="whatsapp" required>
                </div>
                <div class="form-group">
                    <label for="edit_alamat">Alamat</label>
                    <textarea id="edit_alamat" name="alamat" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_password">Password Baru (kosongkan jika tidak diubah)</label>
                    <input type="password" id="edit_password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit_confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="edit_confirm_password" name="confirm_password">
                </div>
                <button type="submit" name="edit_admin" class="btn-primary full-width">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        const admins = <?php echo json_encode($admins); ?>;
        
        function editAdmin(id) {
            const admin = admins.find(a => a.id == id);
            if (admin) {
                document.getElementById('editAdminModal').style.display = 'block';
                document.getElementById('edit_admin_id').value = admin.id;
                document.getElementById('edit_nama').value = admin.nama;
                document.getElementById('edit_email').value = admin.email;
                document.getElementById('edit_whatsapp').value = admin.whatsapp;
                document.getElementById('edit_alamat').value = admin.alamat;
            }
        }
        
        function deleteAdmin(id) {
            if (confirmDelete('Apakah Anda yakin ingin menghapus admin ini?')) {
                window.location.href = `?action=delete&id=${id}`;
            }
        }
        
        function closeEditModal() {
            document.getElementById('editAdminModal').style.display = 'none';
        }
        
        // Form validation
        document.getElementById('editAdminForm').addEventListener('submit', function(e) {
            const password = document.getElementById('edit_password').value;
            const confirmPassword = document.getElementById('edit_confirm_password').value;
            
            if (password && password !== confirmPassword) {
                e.preventDefault();
                showAlert('Password dan konfirmasi password tidak cocok!', 'error');
                return false;
            }
        });
        
        // Initialize search
        document.addEventListener('DOMContentLoaded', function() {
            searchTable('searchAdmin', 'adminTable');
        });
    </script>
</body>
</html>
