<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';
require_once '../../lib/ExcelHelper.php';

use App\ExcelHelper;

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

// Get approved registrations for selected competition
$approved_registrations = [];
if ($selected_competition_id) {
    $stmt = $pdo->prepare("
        SELECT r.*, c.nama_perlombaan, k.nama_kontingen, a.nama as athlete_name, a.nik, a.jenis_kelamin,
               a.tanggal_lahir, a.berat_badan, a.tinggi_badan, a.nama_sekolah,
               cc.nama_kategori as category_name, u.nama as user_name, u.whatsapp
        FROM registrations r 
        JOIN competitions c ON r.competition_id = c.id 
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON a.kontingen_id = k.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN competition_categories cc ON r.category_id = cc.id
        WHERE r.competition_id = ? AND r.payment_status = 'verified'
        ORDER BY k.nama_kontingen, a.nama
    ");
    $stmt->execute([$selected_competition_id]);
    $approved_registrations = $stmt->fetchAll();
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

// Export to Excel functionality
if (isset($_GET['export']) && $_GET['export'] === 'excel' && $selected_competition_id) {
    
    // Prepare data for Excel
    $headers = [
        'No',
        'Kontingen',
        'Nama Atlet',
        'NIK',
        'Jenis Kelamin',
        'Umur',
        'Berat Badan',
        'Tinggi Badan',
        'Sekolah',
        'Kategori'
    ];
    
    $data = [];
    foreach ($approved_registrations as $index => $reg) {
        $data[] = [
            $index + 1,
            $reg['nama_kontingen'],
            $reg['athlete_name'],
            $reg['nik'],
            ($reg['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'),
            calculateAge($reg['tanggal_lahir']),
            $reg['berat_badan'],
            $reg['tinggi_badan'],
            ($reg['nama_sekolah'] ?? '-'),
            ($reg['category_name'] ?? '-')
        ];
    }
    
    $filename = 'data_pendaftaran_' . date('Y-m-d');
    $title = 'DATA PENDAFTARAN TERVERIFIKASI - ' . date('d/m/Y');
    
    ExcelHelper::createAndDownloadExcelXlsx($data, $headers, $filename, $title);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftaran - Admin Panel</title>
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
                    <li><a href="status-pendaftaran.php">Status Pendaftaran</a></li>
                    <li><a href="data-pendaftaran.php" class="active">Data Pendaftaran</a></li>
                </ul>
            </li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Data Pendaftaran</h1>
            <p class="page-subtitle">Data atlet yang sudah lunas dan disetujui</p>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-check-circle"></i> Atlet Terverifikasi</h3>
                <div class="table-actions">
                    <select id="competitionFilter" onchange="filterByCompetition()" class="form-select">
                        <option value="">Pilih Perlombaan</option>
                        <?php foreach ($competitions as $comp): ?>
                            <option value="<?php echo $comp['id']; ?>" <?php echo $comp['id'] == $selected_competition_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($comp['nama_perlombaan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php if ($selected_competition_id && !empty($approved_registrations)): ?>
                        <a href="?competition_id=<?php echo $selected_competition_id; ?>&export=excel" class="btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($approved_registrations)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>Belum Ada Data Pendaftaran</p>
                    <small>Belum ada atlet yang terverifikasi pada perlombaan ini.</small>
                </div>
            <?php else: ?>
                <div class="stats-summary">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h4><?php echo count($approved_registrations); ?></h4>
                            <p>Total Atlet Terverifikasi</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <?php 
                            $unique_kontingen = array_unique(array_column($approved_registrations, 'nama_kontingen'));
                            ?>
                            <h4><?php echo count($unique_kontingen); ?></h4>
                            <p>Kontingen Terdaftar</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="stat-info">
                            <?php 
                            $male_count = count(array_filter($approved_registrations, function($reg) { return $reg['jenis_kelamin'] == 'L'; }));
                            $female_count = count($approved_registrations) - $male_count;
                            ?>
                            <h4><?php echo $male_count; ?> / <?php echo $female_count; ?></h4>
                            <p>Laki-laki / Perempuan</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <?php 
                            $total_revenue = array_sum(array_column($approved_registrations, 'biaya_pendaftaran'));
                            ?>
                            <h4><?php echo formatRupiah($total_revenue); ?></h4>
                            <p>Total Pendapatan</p>
                        </div>
                    </div>
                </div>

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
                                <th>Tanggal Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approved_registrations as $index => $reg): ?>
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
                                    <?php echo date('d M Y', strtotime($reg['updated_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        function filterByCompetition() {
            const select = document.getElementById('competitionFilter');
            const competitionId = select.value;
            
            if (competitionId) {
                window.location.href = 'data-pendaftaran.php?competition_id=' + competitionId;
            } else {
                window.location.href = 'data-pendaftaran.php';
            }
        }
    </script>

    <style>
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h4 {
            margin: 0;
            font-size: 1.8rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .stat-info p {
            margin: 5px 0 0 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

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

        .form-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background: white;
            font-size: 0.9rem;
        }

        .btn-success {
            background: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.3s;
        }

        .btn-success:hover {
            background: #218838;
        }

        @media (max-width: 768px) {
            .stats-summary {
                grid-template-columns: 1fr;
            }

            .data-table {
                font-size: 0.8rem;
            }

            .data-table th,
            .data-table td {
                padding: 8px;
            }
        }
    </style>
</body>
</html>
