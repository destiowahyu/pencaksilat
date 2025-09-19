<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// Get admin info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Get competitions managed by this admin
$competitions = [];
try {
    $stmt = $pdo->prepare("
        SELECT c.*, ca.assigned_at
        FROM competitions c 
        JOIN competition_admins ca ON c.id = ca.competition_id 
        WHERE ca.admin_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$admin_id]);
    $competitions = $stmt->fetchAll();
} catch (PDOException $e) {
    $competitions = [];
}

$competition_ids = array_column($competitions, 'id');

// Get statistics
$stats = [
    'total_competitions' => count($competitions),
    'total_registrations' => 0,
    'paid_registrations' => 0,
    'pending_payments' => 0
];

if (!empty($competition_ids)) {
    $placeholders = implode(',', array_fill(0, count($competition_ids), '?'));

    try {
        // Total registrations
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE competition_id IN ($placeholders)");
        $stmt->execute($competition_ids);
        $stats['total_registrations'] = $stmt->fetchColumn();
        
        // Paid registrations
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE competition_id IN ($placeholders) AND payment_status IN ('paid', 'verified')");
        $stmt->execute($competition_ids);
        $stats['paid_registrations'] = $stmt->fetchColumn();

        // Pending payments
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE competition_id IN ($placeholders) AND payment_status = 'unpaid'");
        $stmt->execute($competition_ids);
        $stats['pending_payments'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        // Handle potential errors if tables don't exist
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Pencak Silat</title>
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
            <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
            <h1 class="page-title">Dashboard Admin</h1>
            <p class="page-subtitle">Selamat datang, <?php echo htmlspecialchars($admin['nama']); ?>!</p>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['total_competitions']; ?></h3>
                    <p>Total Perlombaan Dikelola</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon yellow">
                    <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['total_registrations']; ?></h3>
                    <p>Total Atlet Mendaftar</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['paid_registrations']; ?></h3>
                    <p>Pembayaran Lunas</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon red">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['pending_payments']; ?></h3>
                    <p>Menunggu Pembayaran</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2>Perlombaan yang Anda Kelola</h2>
                <a href="perlombaan.php" class="btn-primary">
                    <i class="fas fa-eye"></i> Lihat Semua
                </a>
            </div>
            
            <?php if (empty($competitions)): ?>
                <div class="empty-state">
                    <i class="fas fa-trophy"></i>
                    <h3>Belum Ada Perlombaan</h3>
                    <p>Anda belum ditugaskan untuk mengelola perlombaan apapun.</p>
                    <small>Hubungi superadmin untuk mendapatkan akses ke perlombaan.</small>
                </div>
            <?php else: ?>
                <div class="competition-grid">
                    <?php foreach ($competitions as $comp): ?>
                    <div class="competition-card">
                        <div class="competition-header">
                            <h3><?php echo htmlspecialchars($comp['nama_perlombaan']); ?></h3>
                        </div>
                        <div class="competition-statuses">
                            <span class="status-badge status-<?php echo $comp['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $comp['status'])); ?>
                            </span>
                            <span class="status-badge status-regist-<?php echo $comp['registration_status']; ?>">
                                <i class="fas fa-edit"></i> <?php echo ucfirst(str_replace('_', ' ', $comp['registration_status'])); ?>
                            </span>
                        </div>
                        <div class="competition-info">
                            <?php if (!empty($comp['lokasi'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($comp['lokasi']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($comp['tanggal_pelaksanaan'])): ?>
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d M Y', strtotime($comp['tanggal_pelaksanaan'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="competition-actions">
                            <a href="perlombaan-detail.php?id=<?php echo $comp['id']; ?>" class="btn-action btn-detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="perlombaan-edit.php?id=<?php echo $comp['id']; ?>" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <style>
        .dashboard-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 30px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .section-header h2 {
            color: var(--primary-color);
            margin: 0;
        }
        .competition-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }
        .competition-card {
            background: var(--light-color);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .competition-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .competition-header h3 {
            color: var(--primary-color);
            margin: 0 0 10px 0;
            font-size: 1.2rem;
        }
        .competition-statuses {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-active, .status-open_regist { background-color: #dcfce7; color: #166534; }
        .status-coming_soon { background-color: #e0e7ff; color: #3730a3; }
        .status-close_regist { background-color: #fef9c3; color: #854d0e; }
        .status-finished { background-color: #fee2e2; color: #991b1b; }
        .status-regist-auto { background-color: #e5e7eb; color: #4b5563; }
        .status-regist-open_regist { background-color: #cffafe; color: #0891b2; }
        .status-regist-close_regist { background-color: #ffedd5; color: #9a3412; }
        .status-regist-coming_soon { background-color: #e0e7ff; color: #3730a3; }
        .competition-info {
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .info-item i {
            width: 16px;
            color: var(--primary-color);
        }
        .competition-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid var(--border-color);
            padding-top: 15px;
            margin-top: auto;
        }
        .btn-action {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-detail { background: #e0e7ff; color: #4338ca; }
        .btn-detail:hover { background: #c7d2fe; }
        .btn-edit { background: #d1fae5; color: #047857; }
        .btn-edit:hover { background: #a7f3d0; }
        .empty-state { text-align: center; padding: 40px 20px; color: var(--text-light); }
        .empty-state i { font-size: 3rem; margin-bottom: 15px; opacity: 0.5; }
        .empty-state h3 { color: var(--text-color); margin-bottom: 10px; }
    </style>
</body>
</html>
