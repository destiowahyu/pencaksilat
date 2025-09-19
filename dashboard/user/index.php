<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit();
}

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM athletes WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$totalAthletes = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT r.competition_id) 
    FROM registrations r 
    JOIN athletes a ON r.athlete_id = a.id 
    JOIN kontingen k ON a.kontingen_id = k.id 
    WHERE k.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$totalCompetitions = $stmt->fetchColumn();

// Get active competitions
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT * FROM competitions
    WHERE status = 'active'
    AND (
        (registration_status = 'open_regist')
        OR (
            registration_status = 'auto'
            AND tanggal_open_regist <= :today
            AND tanggal_close_regist >= :today
        )
    )
    ORDER BY created_at DESC
");
$stmt->execute(['today' => $today]);
$activeCompetitions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Pencak Silat</title>
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
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="data-atlet.php"><i class="fas fa-user-ninja"></i> Data Atlet</a></li>
            <li>
                <a href="#" onclick="toggleSubmenu(this)">
                    <i class="fas fa-trophy"></i> Perlombaan <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                </a>
                <ul class="sidebar-submenu">
                    <li><a href="perlombaan.php">Daftar Perlombaan</a></li>
                </ul>
            </li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Dashboard User</h1>
            <p class="page-subtitle">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?></p>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-user-ninja"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $totalAthletes; ?></h3>
                    <p>Total Atlet</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $totalCompetitions; ?></h3>
                    <p>Perlombaan Diikuti</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon yellow">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo count($activeCompetitions); ?></h3>
                    <p>Perlombaan Aktif</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Aksi Cepat</h2>
            </div>
            <div style="padding: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div class="dashboard-card" style="cursor: pointer;" onclick="window.location.href='data-atlet.php'">
                    <div class="dashboard-card-icon blue">
                        <i class="fas fa-user-ninja"></i>
                    </div>
                    <div class="dashboard-card-content">
                        <h3>Kelola Atlet</h3>
                        <p>Tambah dan kelola data atlet Anda</p>
                    </div>
                </div>
                <div class="dashboard-card" style="cursor: pointer;" onclick="window.location.href='perlombaan.php'">
                    <div class="dashboard-card-icon green">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="dashboard-card-content">
                        <h3>Daftar Perlombaan</h3>
                        <p>Daftarkan atlet ke perlombaan</p>
                    </div>
                </div>
                <div class="dashboard-card" style="cursor: pointer;" onclick="window.location.href='akun-saya.php'">
                    <div class="dashboard-card-icon yellow">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="dashboard-card-content">
                        <h3>Profil Saya</h3>
                        <p>Kelola informasi akun Anda</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Competitions -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Perlombaan Aktif</h2>
            </div>
            <?php if (empty($activeCompetitions)): ?>
            <div style="padding: 40px; text-align: center; color: #6b7280;">
                <i class="fas fa-trophy" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3>Belum Ada Perlombaan Aktif</h3>
                <p>Saat ini belum ada perlombaan yang membuka pendaftaran.</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Perlombaan</th>
                        <th>Tanggal Pelaksanaan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeCompetitions as $index => $competition): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></td>
                        <td>
                            <?php 
                            if ($competition['tanggal_pelaksanaan']) {
                                echo date('d M Y', strtotime($competition['tanggal_pelaksanaan']));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="status-badge status-active">Buka Pendaftaran</span>
                        </td>
                        <td>
                            <button class="btn-action btn-detail" onclick="viewCompetition(<?php echo $competition['id']; ?>)">
                                <i class="fas fa-eye"></i> Lihat
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        function viewCompetition(id) {
            window.location.href = `perlombaan-detail.php?id=${id}`;
        }
    </script>
</body>
</html>
