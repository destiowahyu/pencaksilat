    <?php
session_start();
require_once '../../config/database.php';
require_once '../../lib/SimpleExcelHelper.php';

// Temporarily disable platform check for this file
if (!function_exists('platform_check_disabled')) {
    function platform_check_disabled() {
        return true;
    }
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

// Handle export
if (isset($_GET['export']) && $_GET['export'] == 'kontingen') {
    
    $stmt = $pdo->query("
        SELECT k.*, u.nama as user_name, u.email as user_email, u.whatsapp as user_whatsapp,
               COUNT(a.id) as total_athletes
        FROM kontingen k 
        LEFT JOIN users u ON k.user_id = u.id 
        LEFT JOIN athletes a ON k.id = a.kontingen_id
        GROUP BY k.id
        ORDER BY k.created_at DESC
    ");
    $kontingen_data = $stmt->fetchAll();
    
    $data = [];
    
    foreach ($kontingen_data as $index => $kontingen) {
        $data[] = [
            'No' => $index + 1,
            'Nama Kontingen' => $kontingen['nama_kontingen'],
            'Provinsi' => $kontingen['provinsi'],
            'Kota/Kabupaten' => $kontingen['kota'],
            'Penanggung Jawab' => $kontingen['user_name'],
            'Email' => $kontingen['user_email'],
            'WhatsApp' => $kontingen['user_whatsapp'],
            'Total Atlet' => $kontingen['total_athletes'],
            'Terdaftar' => date('d M Y', strtotime($kontingen['created_at']))
        ];
    }
    
    $filename = 'data_kontingen_' . date('Y-m-d') . '.csv';
    SimpleExcelHelper::exportToCSV($data, $filename);
}

// Get kontingen with user info and athlete count
$stmt = $pdo->query("
    SELECT k.*, u.nama as user_name, u.email as user_email, u.whatsapp as user_whatsapp,
           COUNT(a.id) as total_athletes
    FROM kontingen k 
    LEFT JOIN users u ON k.user_id = u.id 
    LEFT JOIN athletes a ON k.id = a.kontingen_id
    GROUP BY k.id
    ORDER BY k.created_at DESC
");
$kontingen = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kontingen - SuperAdmin Panel</title>
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
            <h1 class="page-title">Data Kontingen</h1>
            <p class="page-subtitle">Kelola data kontingen yang terdaftar dalam sistem</p>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo count($kontingen); ?></h3>
                    <p>Total Kontingen</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-user-ninja"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo array_sum(array_column($kontingen, 'total_athletes')); ?></h3>
                    <p>Total Atlet</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon yellow">
                    <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo count(array_unique(array_column($kontingen, 'user_id'))); ?></h3>
                    <p>Total Penanggung Jawab</p>
                </div>
            </div>
        </div>

        <!-- Kontingen Table -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Daftar Kontingen</h2>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="searchKontingen" placeholder="Cari kontingen...">
                    </div>
                    <a href="?export=kontingen" class="btn-success">
                        <i class="fas fa-download"></i> Export Excel
                    </a>
                </div>
            </div>
            <table class="data-table" id="kontingenTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kontingen</th>
                        <th>Provinsi</th>
                        <th>Kota/Kabupaten</th>
                        <th>Penanggung Jawab</th>
                        <th>Kontak</th>
                        <th>Total Atlet</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kontingen as $index => $k): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($k['nama_kontingen']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($k['provinsi']); ?></td>
                        <td><?php echo htmlspecialchars($k['kota']); ?></td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <strong><?php echo htmlspecialchars($k['user_name']); ?></strong>
                                <small style="color: #6b7280;"><?php echo htmlspecialchars($k['user_email']); ?></small>
                            </div>
                        </td>
                        <td>
                            <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $k['user_whatsapp']); ?>" 
                               target="_blank" class="whatsapp-link">
                                <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($k['user_whatsapp']); ?>
                            </a>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $k['total_athletes'] > 0 ? 'active' : 'inactive'; ?>">
                                <?php echo $k['total_athletes']; ?> Atlet
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($k['created_at'])); ?></td>
                        <td>
                            <button class="btn-action btn-detail" onclick="detailKontingen(<?php echo $k['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
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
        function detailKontingen(id) {
            window.location.href = `data-kontingen-detail.php?id=${id}`;
        }
        
        // Initialize search functionality
        document.addEventListener('DOMContentLoaded', function() {
            searchTable('searchKontingen', 'kontingenTable');
        });
    </script>

    <style>
        .whatsapp-link {
            color: #25d366;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        
        .whatsapp-link:hover {
            text-decoration: underline;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
            padding: 10px 20px;
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
    </style>
</body>
</html>
