<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    die('Unauthorized');
}

// Get recent error logs
$logFile = ini_get('error_log');
if (empty($logFile)) {
    $logFile = 'php_errors.log';
}

$recentLogs = [];
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -50); // Get last 50 lines
}

// Get recent athletes to check data
$stmt = $pdo->prepare("
    SELECT a.*, k.nama_kontingen 
    FROM athletes a 
    LEFT JOIN kontingen k ON a.kontingen_id = k.id 
    WHERE a.user_id = ? 
    ORDER BY a.created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$recentAthletes = $stmt->fetchAll();

// Get user's kontingen
$stmt = $pdo->prepare("SELECT * FROM kontingen WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userKontingen = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Import - User Panel</title>
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
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Debug Import Data</h1>
            <p class="page-subtitle">Informasi debug untuk masalah import data atlet</p>
        </div>

        <div class="debug-container">
            <!-- Error Log Section -->
            <div class="debug-section">
                <h3><i class="fas fa-exclamation-triangle"></i> Error Log Terbaru</h3>
                <div class="log-container">
                    <?php if (empty($recentLogs)): ?>
                        <p>Tidak ada error log yang ditemukan.</p>
                    <?php else: ?>
                        <div class="log-content">
                            <?php foreach (array_reverse($recentLogs) as $log): ?>
                                <div class="log-line"><?php echo htmlspecialchars(trim($log)); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Athletes Section -->
            <div class="debug-section">
                <h3><i class="fas fa-users"></i> Data Atlet Terbaru</h3>
                <div class="athletes-list">
                    <?php if (empty($recentAthletes)): ?>
                        <p>Belum ada data atlet.</p>
                    <?php else: ?>
                        <table class="debug-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>JK</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Kontingen</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAthletes as $athlete): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($athlete['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($athlete['nik']); ?></td>
                                        <td><?php echo $athlete['jenis_kelamin']; ?></td>
                                        <td><?php echo $athlete['tanggal_lahir']; ?></td>
                                        <td><?php echo htmlspecialchars($athlete['nama_kontingen'] ?? 'Tidak Ada'); ?></td>
                                        <td><?php echo $athlete['created_at']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User Kontingen Section -->
            <div class="debug-section">
                <h3><i class="fas fa-flag"></i> Kontingen Anda</h3>
                <div class="kontingen-list">
                    <?php if (empty($userKontingen)): ?>
                        <p>Belum ada kontingen yang dibuat.</p>
                    <?php else: ?>
                        <table class="debug-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Kontingen</th>
                                    <th>Provinsi</th>
                                    <th>Kota</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userKontingen as $kontingen): ?>
                                    <tr>
                                        <td><?php echo $kontingen['id']; ?></td>
                                        <td><?php echo htmlspecialchars($kontingen['nama_kontingen']); ?></td>
                                        <td><?php echo htmlspecialchars($kontingen['provinsi']); ?></td>
                                        <td><?php echo htmlspecialchars($kontingen['kota']); ?></td>
                                        <td><?php echo $kontingen['created_at']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Import Test Section -->
            <div class="debug-section">
                <h3><i class="fas fa-upload"></i> Test Import</h3>
                <div class="test-import">
                    <p>Untuk menguji import, gunakan template yang sudah disediakan dan pastikan:</p>
                    <ul>
                        <li>Format tanggal: YYYY-MM-DD, DD/MM/YYYY, atau DD-MM-YYYY</li>
                        <li>NIK harus 16 digit angka</li>
                        <li>Jenis kelamin: L atau P</li>
                        <li>Berat badan dan tinggi badan dalam angka</li>
                        <li>Tidak ada baris kosong di tengah data</li>
                    </ul>
                    <a href="template-atlet.php" class="btn-primary">
                        <i class="fas fa-download"></i> Download Template
                    </a>
                    <a href="test-import.php" class="btn-primary" style="background: #10b981;">
                        <i class="fas fa-vial"></i> Test Validasi
                    </a>
                    <a href="data-atlet.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali ke Data Atlet
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .debug-container {
            display: grid;
            gap: 30px;
        }
        
        .debug-section {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 25px;
        }
        
        .debug-section h3 {
            color: var(--primary-color);
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .log-container {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .log-content {
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        .log-line {
            margin-bottom: 5px;
            padding: 2px 0;
            border-bottom: 1px solid #eee;
        }
        
        .debug-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .debug-table th,
        .debug-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .debug-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .test-import ul {
            margin: 15px 0;
            padding-left: 20px;
        }
        
        .test-import li {
            margin-bottom: 8px;
        }
        
        .btn-primary,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-right: 10px;
            margin-top: 15px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-primary:hover,
        .btn-secondary:hover {
            opacity: 0.9;
        }
    </style>
</body>
</html>
