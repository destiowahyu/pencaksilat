<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

// Get statistics
$stats = [
    'admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'kontingen' => $pdo->query("SELECT COUNT(*) FROM kontingen")->fetchColumn(),
    'athletes' => $pdo->query("SELECT COUNT(*) FROM athletes")->fetchColumn(),
    'competitions' => $pdo->query("SELECT COUNT(*) FROM competitions")->fetchColumn()
];
// Get competitions
$stmt = $pdo->query("SELECT * FROM competitions ORDER BY created_at DESC");
$competitions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SuperAdmin - Pencak Silat</title>
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
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="data-admin.php"><i class="fas fa-users-cog"></i> Data Admin</a></li>
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
            <h1 class="page-title">Dashboard SuperAdmin</h1>
            <p class="page-subtitle">Selamat datang di panel administrasi sistem pencak silat</p>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-users-cog"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['admins']; ?></h3>
                    <p>Total Admin</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Total User</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon yellow">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['kontingen']; ?></h3>
                    <p>Total Kontingen</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon red">
                    <i class="fas fa-user-ninja"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['athletes']; ?></h3>
                    <p>Total Atlet</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['competitions']; ?></h3>
                    <p>Total Perlombaan</p>
                </div>
            </div>
        </div>

        <!-- Competitions Table -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Daftar Perlombaan</h2>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="searchCompetition" placeholder="Cari perlombaan...">
                    </div>
                    <button class="btn-add" onclick="window.location.href='perlombaan.php?action=add'">
                        <i class="fas fa-plus"></i> Tambah Perlombaan
                    </button>
                </div>
            </div>
            <table class="data-table" id="competitionTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Perlombaan</th>
                        <th>Tanggal Dibuat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($competitions as $index => $competition): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></td>
                        <td><?php echo date('d M Y', strtotime($competition['created_at'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $competition['status'] == 'active' ? 'active' : 'inactive'; ?>">
                                <?php 
                                switch($competition['status']) {
                                    case 'active': echo 'Aktif'; break;
                                    case 'open_regist': echo 'Buka Pendaftaran'; break;
                                    case 'close_regist': echo 'Tutup Pendaftaran'; break;
                                    case 'coming_soon': echo 'Segera Hadir'; break;
                                    default: echo 'Tidak Aktif';
                                }
                                ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-action btn-edit" onclick="editCompetition(<?php echo $competition['id']; ?>)">
                                <i class="fas fa-edit"></i> Ubah
                            </button>
                            <button class="btn-action btn-detail" onclick="detailCompetition(<?php echo $competition['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteCompetition(<?php echo $competition['id']; ?>)">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        function editCompetition(id) {
            window.location.href = `perlombaan.php?action=edit&id=${id}`;
        }
        
        function detailCompetition(id) {
            window.location.href = `perlombaan.php?action=detail&id=${id}`;
        }
        
        function deleteCompetition(id) {
            if (confirmDelete('Apakah Anda yakin ingin menghapus perlombaan ini?')) {
                window.location.href = `perlombaan.php?action=delete&id=${id}`;
            }
        }
        
        // Initialize search functionality
        document.addEventListener('DOMContentLoaded', function() {
            searchTable('searchCompetition', 'competitionTable');
        });
    </script>
</body>
</html>
