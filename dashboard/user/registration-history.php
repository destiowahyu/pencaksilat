<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit();
}

// Get user's registration history
$stmt = $pdo->prepare("
    SELECT 
        r.*,
        c.nama_perlombaan,
        c.tanggal_pelaksanaan,
        a.nama as athlete_name,
        k.nama_kontingen,
        ct.nama_kompetisi,
        ct.biaya_pendaftaran,
        ac.nama_kategori as age_category_name,
        cc.nama_kategori as competition_category_name
    FROM registrations r
    JOIN competitions c ON r.competition_id = c.id
JOIN athletes a ON r.athlete_id = a.id
JOIN kontingen k ON a.kontingen_id = k.id
    LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
    LEFT JOIN age_categories ac ON r.age_category_id = ac.id
    LEFT JOIN competition_categories cc ON r.category_id = cc.id
    WHERE a.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$registrations = $stmt->fetchAll();

// Get notification
$notification = getNotification();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pendaftaran - User Panel</title>
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
            <li><a href="perlombaan.php" class="active"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Riwayat Pendaftaran</h1>
            <p class="page-subtitle">Lihat semua riwayat pendaftaran atlet Anda</p>
            <div class="page-actions">
                <a href="perlombaan.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <?php if ($notification): ?>
            <div class="alert alert-<?php echo $notification['type'] === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $notification['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> 
                <?php echo $notification['message']; ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-header">
                <h2><i class="fas fa-history"></i> Riwayat Pendaftaran</h2>
            </div>

            <?php if (empty($registrations)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Belum Ada Riwayat Pendaftaran</h3>
                    <p>Anda belum pernah mendaftarkan atlet ke perlombaan manapun.</p>
                    <a href="perlombaan.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Daftar Perlombaan
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Perlombaan</th>
                                <th>Atlet</th>
                                <th>Kontingen</th>
                                <th>Jenis Kompetisi</th>
                                <th>Kategori</th>
                                <th>Biaya</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $index => $reg): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="competition-info">
                                        <strong><?php echo htmlspecialchars($reg['nama_perlombaan']); ?></strong>
                                        <small><?php echo formatDate($reg['tanggal_pelaksanaan']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="athlete-info">
                                        <strong><?php echo htmlspecialchars($reg['athlete_name']); ?></strong>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($reg['nama_kontingen']); ?></td>
                                <td><?php echo htmlspecialchars($reg['nama_kompetisi']); ?></td>
                                <td>
                                    <div class="category-info">
                                        <?php if ($reg['age_category_name']): ?>
                                            <div><strong><?php echo htmlspecialchars($reg['age_category_name']); ?></strong></div>
                                        <?php endif; ?>
                                        <?php if ($reg['competition_category_name']): ?>
                                            <small><?php echo htmlspecialchars($reg['competition_category_name']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="price-amount">
                                        <?php echo $reg['biaya_pendaftaran'] ? formatRupiah($reg['biaya_pendaftaran']) : 'Gratis'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $reg['payment_status']; ?>">
                                        <?php 
                                        switch($reg['payment_status']) {
                                            case 'pending': echo 'Belum Bayar'; break;
                                            case 'paid': echo 'Sudah Bayar'; break;
                                            case 'verified': echo 'Terverifikasi'; break;
                                            default: echo 'Unknown';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($reg['created_at'])); ?></td>
                                <td>
                                    <?php if ($reg['payment_status'] === 'pending'): ?>
                                        <div class="action-buttons">
                                            <a href="edit-pendaftaran.php?id=<?php echo $reg['id']; ?>" 
                                               class="btn-action btn-edit" title="Edit Pendaftaran">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="cancelRegistration(<?php echo $reg['id']; ?>)" 
                                                    class="btn-action btn-delete" title="Batalkan Pendaftaran">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function cancelRegistration(registrationId) {
            if (confirm('Apakah Anda yakin ingin membatalkan pendaftaran ini?')) {
                // Create form to submit cancellation
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'cancel-registration.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'registration_id';
                input.value = registrationId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <style>
        .competition-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .competition-info strong {
            color: var(--text-color);
        }

        .competition-info small {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .athlete-info strong {
            color: var(--text-color);
        }

        .category-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .category-info div {
            color: var(--text-color);
        }

        .category-info small {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .price-amount {
            color: var(--success-color);
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .btn-action {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #17a2b8;
            color: white;
        }

        .btn-edit:hover {
            background: #138496;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
            font-size: 0.8rem;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-paid {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-verified {
            background-color: #d4edda;
            color: #155724;
        }

        .page-actions {
            margin-top: 10px;
        }
    </style>
</body>
</html>
