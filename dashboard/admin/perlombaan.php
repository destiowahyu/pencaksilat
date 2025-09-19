<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Get search parameter
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get admin's assigned competitions with search
$sql = "
    SELECT c.*, ca.assigned_at 
    FROM competitions c 
    JOIN competition_admins ca ON c.id = ca.competition_id 
    WHERE ca.admin_id = ?
";

$params = [$_SESSION['user_id']];

if (!empty($search)) {
    $sql .= " AND (c.nama_perlombaan LIKE ? OR c.lokasi LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY ca.assigned_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$competitions = $stmt->fetchAll();

// Get statistics for admin's competitions
$competitionIds = array_column($competitions, 'id');
$stats = [
    'total_competitions' => count($competitions),
    'active_competitions' => 0,
    'inactive_competitions' => 0
];

foreach ($competitions as $comp) {
    if ($comp['status'] === 'active') {
        $stats['active_competitions']++;
    } else {
        $stats['inactive_competitions']++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Perlombaan - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-fist-raised"></i>
                <span>Admin Panel</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li>
                <a href="#" onclick="toggleSubmenu(this)" class="active">
                    <i class="fas fa-trophy"></i> Perlombaan <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                </a>
                <ul class="sidebar-submenu active">
                    <li><a href="perlombaan.php" class="active">Daftar Perlombaan</a></li>
                </ul>
            </li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Daftar Perlombaan</h1>
            <p class="page-subtitle">Kelola perlombaan yang ditugaskan kepada Anda</p>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['total_competitions']; ?></h3>
                    <p>Total Perlombaan</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['active_competitions']; ?></h3>
                    <p>Perlombaan Aktif</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon red">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['inactive_competitions']; ?></h3>
                    <p>Perlombaan Non-Aktif</p>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Perlombaan yang Dikelola</h2>
                <div class="search-box">
                    <form method="GET">
                        <input type="text" name="search" placeholder="Cari perlombaan..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            <?php if (empty($competitions)): ?>
            <div style="padding: 40px; text-align: center; color: #6b7280;">
                <i class="fas fa-trophy" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3>Belum Ada Perlombaan</h3>
                <p>Anda belum ditugaskan untuk mengelola perlombaan apapun.</p>
            </div>
            <?php else: ?>
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
                        <td>
                            <strong><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></strong>
                            <?php if ($competition['lokasi']): ?>
                            <br>
                            <small style="color: #666;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($competition['lokasi']); ?>
                            </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('d M Y', strtotime($competition['created_at'])); ?>
                            <br>
                            <small style="color: #666;"><?php echo date('H:i', strtotime($competition['created_at'])); ?></small>
                        </td>
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
                            <a href="perlombaan-detail.php?id=<?php echo $competition['id']; ?>" class="btn-action btn-detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
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
        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            const form = searchInput.closest('form');
            
            // Auto submit on Enter
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>
