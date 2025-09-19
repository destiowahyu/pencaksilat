<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

$kontingen_id = $_GET['id'] ?? 0;

// Get kontingen data with user info
$stmt = $pdo->prepare("
    SELECT k.*, u.nama as user_name, u.email as user_email, u.whatsapp as user_whatsapp
    FROM kontingen k 
    LEFT JOIN users u ON k.user_id = u.id 
    WHERE k.id = ?
");
$stmt->execute([$kontingen_id]);
$kontingen = $stmt->fetch();

if (!$kontingen) {
    header('Location: data-kontingen.php');
    exit();
}

// Get kontingen's athletes
$stmt = $pdo->prepare("
    SELECT a.* 
    FROM athletes a 
    WHERE a.kontingen_id = ? 
    ORDER BY a.created_at DESC
");
$stmt->execute([$kontingen_id]);
$athletes = $stmt->fetchAll();

// Get kontingen's registrations
$stmt = $pdo->prepare("
    SELECT r.*, c.nama_perlombaan, a.nama as athlete_name
    FROM registrations r 
    LEFT JOIN competitions c ON r.competition_id = c.id 
    LEFT JOIN athletes a ON r.athlete_id = a.id 
    WHERE a.kontingen_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$kontingen_id]);
$registrations = $stmt->fetchAll();

// Handle export athletes
if (isset($_GET['export']) && $_GET['export'] == 'athletes') {
    if (empty($athletes)) {
        header('Location: ?id=' . $kontingen_id . '&error=no_athletes');
        exit();
    }
    
    $headers = ['No', 'Nama', 'NIK', 'Jenis Kelamin', 'Tanggal Lahir', 'Tempat Lahir', 'Nama Sekolah/Instansi', 'Tinggi Badan', 'Berat Badan'];
    $data = [];
    
    foreach ($athletes as $index => $athlete) {
        $data[] = [
            $index + 1,
            $athlete['nama'],
            $athlete['nik'],
            $athlete['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan',
            date('d M Y', strtotime($athlete['tanggal_lahir'])),
            $athlete['tempat_lahir'],
            $athlete['nama_sekolah'],
            $athlete['tinggi_badan'] . ' cm',
            $athlete['berat_badan'] . ' kg'
        ];
    }
    
    require_once '../../lib/SimpleExcelHelper.php';
    $filename = 'data_atlet_' . str_replace(' ', '_', strtolower($kontingen['nama_kontingen'])) . '_' . date('Y-m-d') . '.csv';
    SimpleExcelHelper::exportToCSV($data, $filename);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kontingen - SuperAdmin Panel</title>
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
            <li><a href="data-kontingen.php" class="active"><i class="fas fa-flag"></i> Data Kontingen</a></li>
            <li><a href="perlombaan.php"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="pembayaran.php"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($kontingen['nama_kontingen']); ?></h1>
            <p class="page-subtitle">
                <a href="data-kontingen.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Kembali ke Data Kontingen
                </a>
            </p>
        </div>

        <!-- Error Message -->
        <?php if (isset($_GET['error']) && $_GET['error'] == 'no_athletes'): ?>
        <div class="alert alert-warning" style="background: #fef3c7; color: #92400e; padding: 15px; margin-bottom: 20px; border-radius: 8px;">
            <i class="fas fa-exclamation-triangle"></i> Tidak ada data atlet untuk di-export.
        </div>
        <?php endif; ?>

        <div class="detail-container">
            <!-- Athletes List -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3><i class="fas fa-user-ninja"></i> Daftar Atlet (<?php echo count($athletes); ?>)</h3>
                    <div class="detail-actions">
                        <?php if (!empty($athletes)): ?>
                        <a href="?id=<?php echo $kontingen_id; ?>&export=athletes" class="btn-primary">
                            <i class="fas fa-download"></i> Export Excel
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="detail-content">
                    <?php if (empty($athletes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-ninja"></i>
                        <p>Kontingen belum memiliki atlet</p>
                        <small style="color: #6b7280;">Atlet akan muncul setelah user menambahkan data atlet dan mengaitkannya dengan kontingen ini.</small>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="detail-table" id="athleteTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Tempat Lahir</th>
                                    <th>Nama Sekolah/Instansi</th>
                                    <th>Tinggi Badan (cm)</th>
                                    <th>Berat Badan (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($athletes as $index => $athlete): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($athlete['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($athlete['nik']); ?></td>
                                    <td><?php echo $athlete['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                    <td><?php echo date('d M Y', strtotime($athlete['tanggal_lahir'])); ?></td>
                                    <td><?php echo htmlspecialchars($athlete['tempat_lahir']); ?></td>
                                    <td><?php echo htmlspecialchars($athlete['nama_sekolah']); ?></td>
                                    <td><?php echo $athlete['tinggi_badan']; ?></td>
                                    <td><?php echo $athlete['berat_badan']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Registrations List -->
            <div class="detail-card">
                <div class="detail-header">
                    <h3><i class="fas fa-trophy"></i> Riwayat Pendaftaran (<?php echo count($registrations); ?>)</h3>
                </div>
                <div class="detail-content">
                    <?php if (empty($registrations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <p>Kontingen belum pernah mendaftar perlombaan</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="detail-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Perlombaan</th>
                                    <th>Atlet</th>
                                    <th>Status Pembayaran</th>
                                    <th>Tanggal Daftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $index => $reg): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($reg['nama_perlombaan'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($reg['athlete_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php 
                                            switch($reg['payment_status']) {
                                                case 'paid': 
                                                case 'verified': echo 'active'; break;
                                                case 'pending': echo 'pending'; break;
                                                default: echo 'inactive';
                                            }
                                        ?>">
                                            <?php 
                                            switch($reg['payment_status']) {
                                                case 'paid': echo 'Sudah Bayar'; break;
                                                case 'verified': echo 'Terverifikasi'; break;
                                                case 'pending': echo 'Menunggu'; break;
                                                default: echo 'Belum Bayar';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($reg['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>

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
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .detail-table th,
        .detail-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .detail-table th {
            background: var(--light-color);
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }
        
        .detail-table tr:hover {
            background: #f9fafb;
        }
        
        .alert {
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        
        @media (max-width: 768px) {
            .detail-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .detail-actions {
                justify-content: center;
            }
        }
    </style>
</body>
</html>
