<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

// Get user data with kontingen information
$stmt = $pdo->query("
    SELECT u.*, 
           GROUP_CONCAT(DISTINCT k.nama_kontingen SEPARATOR ', ') as kontingen_names,
           COUNT(DISTINCT k.id) as total_kontingen,
           COUNT(DISTINCT a.id) as total_athletes
    FROM users u 
    LEFT JOIN kontingen k ON u.id = k.user_id 
    LEFT JOIN athletes a ON u.id = a.user_id 
    WHERE u.role = 'user' 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data User - SuperAdmin Panel</title>
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
            <li><a href="data-user.php" class="active"><i class="fas fa-users"></i> Data User</a></li>
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
            <h1 class="page-title">Data User</h1>
            <p class="page-subtitle">Kelola data pengguna sistem</p>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo count($users); ?></h3>
                    <p>Total User</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-flag"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo array_sum(array_column($users, 'total_kontingen')); ?></h3>
                    <p>Total Kontingen</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon yellow">
                    <i class="fas fa-user-ninja"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo array_sum(array_column($users, 'total_athletes')); ?></h3>
                    <p>Total Atlet</p>
                </div>
            </div>
        </div>

        <!-- User List -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Daftar User (<?php echo count($users); ?>)</h2>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="searchUser" placeholder="Cari user...">
                    </div>
                    <button class="btn-add" onclick="exportUsers()">
                        <i class="fas fa-download"></i> Export Excel
                    </button>
                </div>
            </div>
            <table class="data-table" id="userTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. WhatsApp</th>
                        <th>Kontingen</th>
                        <th>Jumlah Kontingen</th>
                        <th>Total Atlet</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($user['nama']); ?></strong>
                                <small style="display: block; color: #6b7280;">ID: <?php echo $user['id']; ?></small>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $user['whatsapp']); ?>" 
                               target="_blank" class="whatsapp-link">
                                <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($user['whatsapp']); ?>
                            </a>
                        </td>
                        <td>
                            <div class="kontingen-list">
                                <?php if ($user['kontingen_names']): ?>
                                    <?php 
                                    $kontingen_array = explode(', ', $user['kontingen_names']);
                                    $kontingen_array = array_unique(array_filter($kontingen_array)); // Remove duplicates and empty values
                                    foreach ($kontingen_array as $kontingen): 
                                    ?>
                                    <span class="kontingen-badge"><?php echo htmlspecialchars(trim($kontingen)); ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="no-data">Belum ada kontingen</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $user['total_kontingen'] > 0 ? 'active' : 'inactive'; ?>">
                                <?php echo $user['total_kontingen']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $user['total_athletes'] > 0 ? 'pending' : 'inactive'; ?>">
                                <?php echo $user['total_athletes']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <button class="btn-action btn-detail" onclick="viewUser(<?php echo $user['id']; ?>)">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            <button class="btn-action btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                <i class="fas fa-edit"></i> Edit
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
        function viewUser(id) {
            window.location.href = `data-user-detail.php?id=${id}`;
        }
        
        function editUser(id) {
            window.location.href = `data-user-detail.php?id=${id}&action=edit`;
        }
        
        function exportUsers() {
            // Create export data
            const table = document.getElementById('userTable');
            const rows = table.querySelectorAll('tbody tr');
            const data = [];
            
            rows.forEach((row, index) => {
                const cells = row.querySelectorAll('td');
                const rowData = [
                    index + 1,
                    cells[1].querySelector('strong').textContent,
                    cells[2].textContent,
                    cells[3].textContent.replace(/[^\d]/g, ''), // Extract only numbers from WhatsApp
                    cells[4].textContent.replace(/Belum ada kontingen/g, '-'),
                    cells[5].textContent,
                    cells[6].textContent,
                    cells[7].textContent
                ];
                data.push(rowData);
            });
            
            // Send data to server for Excel generation
            const formData = new FormData();
            formData.append('action', 'export_users');
            formData.append('data', JSON.stringify(data));
            
            fetch('export-users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'data_user_' + new Date().toISOString().slice(0,10) + '.xlsx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengexport data');
            });
        }
        
        // Initialize search
        document.addEventListener('DOMContentLoaded', function() {
            searchTable('searchUser', 'userTable');
        });
    </script>

    <style>
        .user-info strong {
            color: var(--dark-color);
        }
        
        .whatsapp-link {
            color: #25d366;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .whatsapp-link:hover {
            text-decoration: underline;
        }
        
        .kontingen-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .kontingen-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .no-data {
            color: #9ca3af;
            font-style: italic;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .kontingen-list {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
