<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Get competitions managed by this admin
$stmt = $pdo->prepare("
    SELECT c.* FROM competitions c 
    JOIN competition_admins ca ON c.id = ca.competition_id 
    WHERE ca.admin_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$competitions = $stmt->fetchAll();

// Get selected competition
$selected_competition_id = $_GET['competition_id'] ?? ($competitions[0]['id'] ?? 0);

// Get registrations for selected competition
$registrations = [];
if ($selected_competition_id) {
    $stmt = $pdo->prepare("
        SELECT r.*, c.nama_perlombaan, k.nama_kontingen, a.nama as athlete_name, a.nik, a.jenis_kelamin,
               a.tanggal_lahir, a.berat_badan, a.tinggi_badan, a.nama_sekolah,
               cc.nama_kategori as category_name, ac.nama_kategori as age_category_name,
               ct.nama_kompetisi, ct.biaya_pendaftaran, u.nama as user_name, u.whatsapp
        FROM registrations r 
        JOIN competitions c ON r.competition_id = c.id 
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON a.kontingen_id = k.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN competition_categories cc ON r.category_id = cc.id
        LEFT JOIN age_categories ac ON r.age_category_id = ac.id
        LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
        WHERE r.competition_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$selected_competition_id]);
    $registrations = $stmt->fetchAll();
}

// Handle approval action
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'approve') {
    $registration_id = $_POST['registration_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE registrations SET payment_status = 'verified' WHERE id = ?");
        $stmt->execute([$registration_id]);
        
        $success_message = "Pendaftaran berhasil disetujui!";
        
        // Refresh page
        header("Location: status-pendaftaran.php?competition_id=" . $selected_competition_id . "&success=1");
        exit();
    } catch (Exception $e) {
        $error_message = "Gagal menyetujui pendaftaran: " . $e->getMessage();
    }
}

// Helper function to format currency
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Helper function to calculate age
function calculateAge($birthDate) {
    $today = new DateTime();
    $birth = new DateTime($birthDate);
    return $today->diff($birth)->y;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran - Admin Panel</title>
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
                    <li><a href="perlombaan.php">Daftar Perlombaan</a></li>
                    <li><a href="status-pendaftaran.php" class="active">Status Pendaftaran</a></li>
                    <li><a href="data-pendaftaran.php">Data Pendaftaran</a></li>
                </ul>
            </li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Status Pendaftaran</h1>
            <p class="page-subtitle">Kelola status pendaftaran kontingen dan atlet</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Pendaftaran berhasil disetujui!
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> Data Pendaftaran Kontingen</h3>
                <div class="table-actions">
                    <select id="competitionFilter" onchange="filterByCompetition()" class="form-select">
                        <option value="">Pilih Perlombaan</option>
                        <?php foreach ($competitions as $comp): ?>
                            <option value="<?php echo $comp['id']; ?>" <?php echo $comp['id'] == $selected_competition_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($comp['nama_perlombaan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php if (empty($registrations)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Belum Ada Pendaftaran</p>
                    <small>Belum ada kontingen yang mendaftar pada perlombaan ini.</small>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kontingen</th>
                                <th>Nama Atlet</th>
                                <th>Detail Atlet</th>
                                <th>Kategori</th>
                                <th>Jenis Kompetisi</th>
                                <th>Biaya</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $index => $reg): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <div class="kontingen-info">
                                        <strong><?php echo htmlspecialchars($reg['nama_kontingen']); ?></strong>
                                        <small>PIC: <?php echo htmlspecialchars($reg['user_name']); ?></small>
                                        <small>WA: <?php echo htmlspecialchars($reg['whatsapp']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="athlete-basic">
                                        <strong><?php echo htmlspecialchars($reg['athlete_name']); ?></strong>
                                        <small>NIK: <?php echo htmlspecialchars($reg['nik']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="athlete-details">
                                        <div><strong>JK:</strong> <?php echo $reg['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
                                        <div><strong>Umur:</strong> <?php echo calculateAge($reg['tanggal_lahir']); ?> tahun</div>
                                        <div><strong>BB:</strong> <?php echo $reg['berat_badan']; ?> kg</div>
                                        <div><strong>TB:</strong> <?php echo $reg['tinggi_badan']; ?> cm</div>
                                        <div><strong>Sekolah:</strong> <?php echo htmlspecialchars($reg['nama_sekolah'] ?? '-'); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="category-info">
                                        <?php if ($reg['category_name']): ?>
                                            <div><strong><?php echo htmlspecialchars($reg['category_name']); ?></strong></div>
                                        <?php endif; ?>
                                        <?php if ($reg['age_category_name']): ?>
                                            <small><?php echo htmlspecialchars($reg['age_category_name']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($reg['nama_kompetisi'] ?? '-'); ?></strong>
                                </td>
                                <td>
                                    <span class="price-amount">
                                        <?php echo $reg['biaya_pendaftaran'] ? formatRupiah($reg['biaya_pendaftaran']) : 'Gratis'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php 
                                        switch($reg['payment_status']) {
                                            case 'paid': echo 'paid'; break;
                                            case 'verified': echo 'verified'; break;
                                            case 'pending': echo 'pending'; break;
                                            default: echo 'pending';
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
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action btn-detail" onclick="viewAthleteDetail(<?php echo $reg['athlete_id']; ?>)">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                        <?php if ($reg['payment_status'] == 'paid'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menyetujui pendaftaran ini?')">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="registration_id" value="<?php echo $reg['id']; ?>">
                                                <button type="submit" class="btn-action btn-approve">
                                                    <i class="fas fa-check"></i> Setujui
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Athlete Detail Modal -->
    <div id="athleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Atlet</h3>
                <span class="close" onclick="closeAthleteModal()">&times;</span>
            </div>
            <div class="modal-body" id="athleteModalBody">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        function filterByCompetition() {
            const select = document.getElementById('competitionFilter');
            const competitionId = select.value;
            
            if (competitionId) {
                window.location.href = 'status-pendaftaran.php?competition_id=' + competitionId;
            } else {
                window.location.href = 'status-pendaftaran.php';
            }
        }

        function viewAthleteDetail(athleteId) {
            // Show modal
            document.getElementById('athleteModal').style.display = 'block';
            
            // Load athlete details via AJAX
            fetch('get-athlete-detail.php?id=' + athleteId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('athleteModalBody').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('athleteModalBody').innerHTML = '<p>Error loading athlete details.</p>';
                });
        }

        function closeAthleteModal() {
            document.getElementById('athleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('athleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <style>
        .kontingen-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .kontingen-info strong {
            color: var(--primary-color);
        }

        .kontingen-info small {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .athlete-basic {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .athlete-basic strong {
            color: var(--text-color);
        }

        .athlete-basic small {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .athlete-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 0.8rem;
        }

        .athlete-details div {
            color: var(--text-light);
        }

        .category-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .category-info div {
            font-size: 0.9rem;
        }

        .category-info small {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .price-amount {
            font-weight: 600;
            color: var(--success-color);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
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

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-detail {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-detail:hover {
            background: #bbdefb;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-approve:hover {
            background: #218838;
        }

        .form-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: white;
            font-size: 0.9rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light-color);
        }

        .modal-header h3 {
            margin: 0;
            color: var(--primary-color);
        }

        .close {
            color: var(--text-light);
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--text-color);
        }

        .modal-body {
            padding: 20px;
        }

        @media (max-width: 768px) {
            .data-table {
                font-size: 0.8rem;
            }

            .data-table th,
            .data-table td {
                padding: 8px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</body>
</html>
