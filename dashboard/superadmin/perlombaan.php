<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../auth/login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nama_perlombaan = sanitizeInput($_POST['nama_perlombaan']);
                $deskripsi = sanitizeInput($_POST['deskripsi']);
                $tanggal_open_regist = $_POST['tanggal_open_regist'] ?? null;
                $tanggal_close_regist = $_POST['tanggal_close_regist'] ?? null;
                $tanggal_pelaksanaan = $_POST['tanggal_pelaksanaan'];
                $lokasi = sanitizeInput($_POST['lokasi']);
                $status = $_POST['status'] ?? 'active';

                // Validation
                if (empty($nama_perlombaan) || empty($tanggal_pelaksanaan) || empty($lokasi)) {
                    $error = "Nama perlombaan, tanggal pelaksanaan, dan lokasi wajib diisi!";
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO competitions (nama_perlombaan, deskripsi, tanggal_open_regist, tanggal_close_regist, tanggal_pelaksanaan, lokasi, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$nama_perlombaan, $deskripsi, $tanggal_open_regist, $tanggal_close_regist, $tanggal_pelaksanaan, $lokasi, $status]);
                        $success = "Perlombaan berhasil ditambahkan!";
                    } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'edit':
                $id = intval($_POST['id']);
                $nama_perlombaan = sanitizeInput($_POST['nama_perlombaan']);
                $deskripsi = sanitizeInput($_POST['deskripsi']);
                $status = $_POST['status'];

                // Validation
                if (empty($nama_perlombaan)) {
                    $error = "Nama perlombaan wajib diisi!";
                } else {
                    try {
                        $stmt = $pdo->prepare("
                            UPDATE competitions 
                            SET nama_perlombaan = ?, deskripsi = ?, status = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([$nama_perlombaan, $deskripsi, $status, $id]);
                        $success = "Perlombaan berhasil diperbarui!";
                    } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'assign_admin':
                $competition_id = intval($_POST['competition_id']);
                $admin_id = intval($_POST['admin_id']);

                // Check if admin already assigned
                $stmt = $pdo->prepare("SELECT id FROM competition_admins WHERE competition_id = ? AND admin_id = ?");
                $stmt->execute([$competition_id, $admin_id]);
                
                if ($stmt->fetch()) {
                    $error = "Admin sudah ditugaskan untuk perlombaan ini!";
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO competition_admins (competition_id, admin_id) VALUES (?, ?)");
                        $stmt->execute([$competition_id, $admin_id]);
                        $success = "Admin berhasil ditugaskan!";
                    } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
                    }
                }
                break;

            case 'remove_admin':
                $competition_id = intval($_POST['competition_id']);
                $admin_id = intval($_POST['admin_id']);

                try {
                    $stmt = $pdo->prepare("DELETE FROM competition_admins WHERE competition_id = ? AND admin_id = ?");
                    $stmt->execute([$competition_id, $admin_id]);
                    $success = "Admin berhasil dihapus dari perlombaan!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
                break;

            case 'toggle_status':
                $id = intval($_POST['id']);
                $current_status = $_POST['current_status'];
                $new_status = ($current_status === 'active') ? 'inactive' : 'active';

                try {
                    $stmt = $pdo->prepare("UPDATE competitions SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$new_status, $id]);
                    $success = "Status perlombaan berhasil diubah!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
                break;

            case 'delete':
                $id = intval($_POST['id']);
                try {
                    // Delete related admin assignments first
                    $stmt = $pdo->prepare("DELETE FROM competition_admins WHERE competition_id = ?");
                    $stmt->execute([$id]);
                    
                    // Delete competition
                    $stmt = $pdo->prepare("DELETE FROM competitions WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Perlombaan berhasil dihapus!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get competitions with search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE nama_perlombaan LIKE ? OR lokasi LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare("
    SELECT *, 
           (SELECT COUNT(*) FROM registrations WHERE competition_id = competitions.id) as total_peserta
    FROM competitions 
    $where_clause 
    ORDER BY created_at DESC
");
$stmt->execute($params);
$competitions = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM competitions");
$total_competitions = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM competitions WHERE status = 'active'");
$active_competitions = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM competitions WHERE status = 'inactive'");
$inactive_competitions = $stmt->fetch()['total'];

// Get all admins for assignment dropdown
$stmt = $pdo->prepare("SELECT id, nama, email FROM users WHERE role = 'admin' ORDER BY nama");
$stmt->execute();
$all_admins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Perlombaan - Pencak Silat</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-fist-raised"></i>
                <span>Pencak Silat</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="data-admin.php"><i class="fas fa-user-tie"></i> Data Admin</a></li>
            <li><a href="data-user.php"><i class="fas fa-users"></i> Data User</a></li>
            <li><a href="data-kontingen.php"><i class="fas fa-flag"></i> Data Kontingen</a></li>
            <li><a href="perlombaan.php" class="active"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="akun-saya.php"><i class="fas fa-user-cog"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Kelola Perlombaan</h1>
            <p class="page-subtitle">Kelola data perlombaan pencak silat</p>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $total_competitions; ?></h3>
                    <p>Total Perlombaan</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $active_competitions; ?></h3>
                    <p>Perlombaan Aktif</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $inactive_competitions; ?></h3>
                    <p>Perlombaan Inactive</p>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <div class="table-header">
                <h3 class="table-title">Daftar Perlombaan</h3>
                <div class="table-actions">
                    <div class="search-box">
                        <form method="GET">
                            <input type="text" name="search" placeholder="Cari perlombaan..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    <button class="btn-add" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Tambah Perlombaan
                    </button>
                </div>
            </div>

            <?php if (empty($competitions)): ?>
                <div style="text-align: center; padding: 50px;">
                    <i class="fas fa-trophy" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                    <h3 style="color: #666;">Belum ada data perlombaan</h3>
                    <p style="color: #999;">Klik tombol "Tambah Perlombaan" untuk menambah data pertama</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Status</th>
                            <th>Nama Perlombaan</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($competitions as $index => $competition): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $competition['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $competition['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($competition['lokasi']); ?>
                                    </small>
                                </td>
                                <td>
                                    <?php echo date('d M Y', strtotime($competition['created_at'])); ?>
                                    <br>
                                    <small style="color: #666;"><?php echo date('H:i', strtotime($competition['created_at'])); ?></small>
                                </td>
                                <td>
                                    <button class="btn-action btn-detail" onclick="viewDetail(<?php echo $competition['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action btn-edit" onclick="editCompetition(<?php echo htmlspecialchars(json_encode($competition)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $competition['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $competition['status']; ?>">
                                        <button type="submit" class="btn-action <?php echo $competition['status'] === 'active' ? 'btn-delete' : 'btn-edit'; ?>" 
                                                onclick="return confirm('Yakin ingin mengubah status perlombaan ini?')" 
                                                title="<?php echo $competition['status'] === 'active' ? 'Inactive-kan' : 'Aktifkan'; ?>">
                                            <i class="fas fa-toggle-<?php echo $competition['status'] === 'active' ? 'off' : 'on'; ?>"></i>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $competition['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" 
                                                onclick="return confirm('Yakin ingin menghapus perlombaan ini? Tindakan ini tidak dapat dibatalkan!')" 
                                                title="Hapus Perlombaan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Competition Modal -->
    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Perlombaan</h2>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="nama_perlombaan">Nama Perlombaan *</label>
                    <input type="text" id="nama_perlombaan" name="nama_perlombaan" required>
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi *</label>
                    <input type="text" id="lokasi" name="lokasi" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3"></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="tanggal_open_regist">Tanggal Buka Pendaftaran</label>
                        <input type="date" id="tanggal_open_regist" name="tanggal_open_regist">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_close_regist">Tanggal Tutup Pendaftaran</label>
                        <input type="date" id="tanggal_close_regist" name="tanggal_close_regist">
                    </div>
                </div>
                <div class="form-group">
                    <label for="tanggal_pelaksanaan">Tanggal Pelaksanaan *</label>
                    <input type="date" id="tanggal_pelaksanaan" name="tanggal_pelaksanaan" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('addModal')">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Competition Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Perlombaan</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label for="edit_nama_perlombaan">Nama Perlombaan *</label>
                    <input type="text" id="edit_nama_perlombaan" name="nama_perlombaan" required>
                </div>
                <div class="form-group">
                    <label for="edit_deskripsi">Deskripsi</label>
                    <textarea id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn-secondary" onclick="closeModal('editModal')">Batal</button>
                    <button type="submit" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail Competition Modal -->
    <div class="modal" id="detailModal">
        <div class="modal-content" style="max-width: 800px; max-height: 80vh; overflow-y: auto;">
            <div class="modal-header">
                <h2>Detail Perlombaan</h2>
                <span class="close" onclick="closeModal('detailModal')">&times;</span>
            </div>
            <div id="detailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function editCompetition(competition) {
            document.getElementById('edit_id').value = competition.id;
            document.getElementById('edit_nama_perlombaan').value = competition.nama_perlombaan;
            document.getElementById('edit_deskripsi').value = competition.deskripsi || '';
            document.getElementById('edit_status').value = competition.status;
            
            document.getElementById('editModal').style.display = 'block';
        }

        function viewDetail(competitionId) {
            // Show loading
            document.getElementById('detailContent').innerHTML = '<div style="text-align: center; padding: 50px;"><i class="fas fa-spinner fa-spin fa-2x"></i><br><br>Memuat data...</div>';
            document.getElementById('detailModal').style.display = 'block';
            
            // Fetch competition details with assigned admins
            fetch(`get_competition_detail.php?id=${competitionId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    document.getElementById('detailContent').innerHTML = data;
                    
                    // Add event listeners to forms in the loaded content
                    const forms = document.getElementById('detailContent').querySelectorAll('form');
                    forms.forEach(form => {
                        form.addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const formData = new FormData(form);
                            
                            fetch(window.location.href, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.text())
                            .then(() => {
                                // Reload the detail content to show updated data
                                viewDetail(competitionId);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('Terjadi kesalahan saat memproses permintaan');
                            });
                        });
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Fallback to simple detail view
                    const competition = <?php echo json_encode($competitions); ?>.find(c => c.id == competitionId);
                    if (competition) {
                        showSimpleDetail(competition);
                    }
                });
        }

        function showSimpleDetail(competition) {
            document.getElementById('detailContent').innerHTML = `
                <div style="padding: 20px;">
                    <div style="margin-bottom: 30px;">
                        <h3>Informasi Perlombaan</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <div>
                                <strong>Nama Perlombaan:</strong><br>
                                ${competition.nama_perlombaan}
                            </div>
                            <div>
                                <strong>Status:</strong><br>
                                <span class="status-badge ${competition.status === 'active' ? 'status-active' : 'status-inactive'}">
                                    ${competition.status === 'active' ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                            <div>
                                <strong>Deskripsi:</strong><br>
                                ${competition.deskripsi || 'Tidak ada deskripsi'}
                            </div>
                            <div>
                                <strong>Tanggal Dibuat:</strong><br>
                                ${new Date(competition.created_at).toLocaleDateString('id-ID')}
                            </div>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 30px;">
                        <h3>Admin yang Bertanggung Jawab</h3>
                        <div style="margin-top: 15px;">
                            <p style="color: #666; font-style: italic;">Memuat daftar admin...</p>
                        </div>
                    </div>
                    
                    <div>
                        <h3>Tambah Admin</h3>
                        <form method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="assign_admin">
                            <input type="hidden" name="competition_id" value="${competition.id}">
                            <div style="display: flex; gap: 10px; align-items: end;">
                                <div class="form-group" style="flex: 1; margin-bottom: 0;">
                                    <label for="admin_id">Pilih Admin:</label>
                                    <select name="admin_id" required>
                                        <option value="">Pilih Admin</option>
                                        <?php foreach ($all_admins as $admin): ?>
                                        <option value="<?php echo $admin['id']; ?>">
                                            <?php echo htmlspecialchars($admin['nama']); ?> (<?php echo htmlspecialchars($admin['email']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn-primary">Tambah</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Set minimum date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tanggal_open_regist').setAttribute('min', today);
            document.getElementById('tanggal_close_regist').setAttribute('min', today);
            document.getElementById('tanggal_pelaksanaan').setAttribute('min', today);
        });

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.style.display = 'none');
        }, 5000);
    </script>
</body>
</html>
