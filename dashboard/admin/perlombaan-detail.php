<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$competition_id = $_GET['id'] ?? 0;

// Verify admin has access to this competition
$stmt = $pdo->prepare("
    SELECT c.* FROM competitions c 
    JOIN competition_admins ca ON c.id = ca.competition_id 
    WHERE c.id = ? AND ca.admin_id = ?
");
$stmt->execute([$competition_id, $_SESSION['user_id']]);
$competition = $stmt->fetch();

if (!$competition) {
    header('Location: perlombaan.php');
    exit();
}

// Handle payment status update
if ($_POST && isset($_POST['update_payment_status'])) {
    $registration_id = $_POST['registration_id'];
    $new_status = $_POST['payment_status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE registrations SET payment_status = ? WHERE id = ?");
        $stmt->execute([$new_status, $registration_id]);
        $success_message = "Status pembayaran berhasil diperbarui!";
    } catch (Exception $e) {
        $error_message = "Gagal memperbarui status pembayaran: " . $e->getMessage();
    }
}

// Get competition statistics with error handling
$stats = [
    'registrations' => 0,
    'paid_registrations' => 0,
    'categories' => 0,
    'age_categories' => 0
];

try {
    // Check if registrations table exists and get count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE competition_id = ?");
    $stmt->execute([$competition_id]);
    $stats['registrations'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE competition_id = ? AND payment_status IN ('paid', 'verified')");
    $stmt->execute([$competition_id]);
    $stats['paid_registrations'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Table doesn't exist, keep default value 0
    $stats['registrations'] = 0;
    $stats['paid_registrations'] = 0;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM competition_categories WHERE competition_id = ?");
    $stmt->execute([$competition_id]);
    $stats['categories'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    $stats['categories'] = 0;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM age_categories WHERE competition_id = ?");
    $stmt->execute([$competition_id]);
    $stats['age_categories'] = $stmt->fetchColumn();
} catch (PDOException $e) {
    $stats['age_categories'] = 0;
}

// Get registration data for Status Pendaftaran (all registrations)
$all_registrations = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.id as registration_id,
            r.payment_status,
            r.payment_proof,
            r.created_at as registration_date,
            a.nama as athlete_name,
            a.jenis_kelamin,
            k.nama_kontingen,
            u.nama as penanggung_jawab,
            u.whatsapp as kontak_penanggung_jawab,
            cc.nama_kategori as category_name,
            ac.nama_kategori as age_category_name,
            ct.nama_kompetisi,
            ct.biaya_pendaftaran
        FROM registrations r
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON a.kontingen_id = k.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN competition_categories cc ON r.category_id = cc.id
        LEFT JOIN age_categories ac ON r.age_category_id = ac.id
        LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
        WHERE r.competition_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$competition_id]);
    $all_registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_registrations = [];
}

// Get registration data for Data Pendaftaran (only paid/verified)
$paid_registrations = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.id as registration_id,
            r.payment_status,
            r.created_at as registration_date,
            a.nama as athlete_name,
            a.jenis_kelamin,
            k.nama_kontingen,
            u.nama as penanggung_jawab,
            u.whatsapp as kontak_penanggung_jawab,
            cc.nama_kategori as category_name,
            ac.nama_kategori as age_category_name,
            ct.nama_kompetisi,
            ct.biaya_pendaftaran
        FROM registrations r
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON a.kontingen_id = k.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN competition_categories cc ON r.category_id = cc.id
        LEFT JOIN age_categories ac ON r.age_category_id = ac.id
        LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
        WHERE r.competition_id = ? AND r.payment_status IN ('paid', 'verified')
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$competition_id]);
    $paid_registrations = $stmt->fetchAll();
} catch (PDOException $e) {
    $paid_registrations = [];
}

// Get payment methods for this competition only
$payment_methods = [];
try {
    $stmt = $pdo->prepare("
        SELECT pm.*
        FROM payment_methods pm
        JOIN competition_payment_methods cpm ON pm.id = cpm.payment_method_id
        WHERE cpm.competition_id = ? AND pm.status = 'active'
        ORDER BY pm.nama_bank ASC
    ");
    $stmt->execute([$competition_id]);
    $payment_methods = $stmt->fetchAll();
} catch (PDOException $e) {
    $payment_methods = [];
}

// Get documents with error handling
$documents = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM competition_documents WHERE competition_id = ?");
    $stmt->execute([$competition_id]);
    $documents = $stmt->fetchAll();
} catch (PDOException $e) {
    $documents = [];
}

// Get competition categories with age category info
$categories = [];
try {
    $stmt = $pdo->prepare("
        SELECT cc.*, ac.nama_kategori as age_category_name, ac.usia_min, ac.usia_max
        FROM competition_categories cc 
        LEFT JOIN age_categories ac ON cc.age_category_id = ac.id 
        WHERE cc.competition_id = ?
        ORDER BY cc.nama_kategori
    ");
    $stmt->execute([$competition_id]);
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Get age categories with error handling
$age_categories = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY usia_min");
    $stmt->execute([$competition_id]);
    $age_categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $age_categories = [];
}

// Get competition types with error handling
$competition_types = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
    $stmt->execute([$competition_id]);
    $competition_types = $stmt->fetchAll();
} catch (PDOException $e) {
    $competition_types = [];
}

// Get contacts with error handling
$contacts = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM competition_contacts WHERE competition_id = ? ORDER BY created_at DESC");
    $stmt->execute([$competition_id]);
    $contacts = $stmt->fetchAll();
} catch (PDOException $e) {
    $contacts = [];
}

// Helper function to safely get date value
function getDateValue($competition, $field_names) {
    foreach ($field_names as $field) {
        if (isset($competition[$field]) && !empty($competition[$field]) && 
            $competition[$field] !== '0000-00-00' && $competition[$field] !== '0000-00-00 00:00:00') {
            return $competition[$field];
        }
    }
    return null;
}

// Helper function to format date
if (!function_exists('formatDate')) {
    function formatDate($date) {
        if (!$date) return '-';
        return date('d F Y', strtotime($date));
    }
}

// Helper function to format currency
if (!function_exists('formatRupiah')) {
    function formatRupiah($number) {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Perlombaan - Admin Panel</title>
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
                </ul>
            </li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></h1>
            <p class="page-subtitle">Kelola detail perlombaan</p>
        </div>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- Competition Stats -->
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['registrations']; ?></h3>
                    <p>Total Pendaftaran</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['paid_registrations']; ?></h3>
                    <p>Sudah Bayar</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon yellow">
                    <i class="fas fa-list"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['categories']; ?></h3>
                    <p>Kategori Tanding</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon red">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['age_categories']; ?></h3>
                    <p>Kategori Umur</p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="table-container">
            <div class="table-header">
                <div class="tab-navigation">
                    <button class="tab-btn active" onclick="showTab('info')">
                        <i class="fas fa-info-circle"></i>
                        <span>Info Perlombaan</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('pendaftaran')">
                        <i class="fas fa-user-plus"></i>
                        <span>Pendaftaran</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('tournament')">
                        <i class="fas fa-trophy"></i>
                        <span>Pertandingan</span>
                    </button>
                </div>
            </div>

            <!-- Info Perlombaan Tab -->
            <div id="info-tab" class="tab-content active">
                <div style="padding: 30px;">
                    <!-- Poster Section -->
                    <?php if (!empty($competition['poster'])): ?>
                    <div class="info-section">
                        <h3><i class="fas fa-image"></i> Poster Perlombaan</h3>
                        <div class="poster-display">
                            <img src="../../uploads/posters/<?php echo htmlspecialchars($competition['poster']); ?>" 
                                 alt="Poster <?php echo htmlspecialchars($competition['nama_perlombaan']); ?>" 
                                 class="competition-poster">
                            <div class="poster-actions">
                                <a href="../../uploads/posters/<?php echo htmlspecialchars($competition['poster']); ?>" 
                                   target="_blank" class="btn-action btn-detail">
                                    <i class="fas fa-expand"></i> Lihat Full Size
                                </a>
                                <a href="../../uploads/posters/<?php echo htmlspecialchars($competition['poster']); ?>" 
                                   download class="btn-action btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="info-section">
                        <h3><i class="fas fa-info-circle"></i> Informasi Umum</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Nama Perlombaan:</label>
                                <span><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Status:</label>
                                <span class="status-badge status-<?php echo $competition['status']; ?>">
                                    <?php 
                                    switch($competition['status']) {
                                        case 'active': echo 'Aktif'; break;
                                        case 'open_regist': echo 'Buka Pendaftaran'; break;
                                        case 'close_regist': echo 'Tutup Pendaftaran'; break;
                                        case 'coming_soon': echo 'Segera Hadir'; break;
                                        case 'finished': echo 'Selesai'; break;
                                        default: echo 'Tidak Aktif';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Lokasi:</label>
                                <span><?php echo htmlspecialchars($competition['lokasi'] ?? '-'); ?></span>
                            </div>
                            
                            <!-- Fixed date fields with multiple possible field names -->
                            <div class="info-item">
                                <label>Tanggal Buka Pendaftaran:</label>
                                <span>
                                    <?php 
                                    $open_date = getDateValue($competition, ['tanggal_open_regist', 'open_regist_date', 'date_open_regist']);
                                    echo $open_date ? formatDate($open_date) : '-';
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Tanggal Tutup Pendaftaran:</label>
                                <span>
                                    <?php 
                                    $close_date = getDateValue($competition, ['tanggal_close_regist', 'close_regist_date', 'date_close_regist']);
                                    echo $close_date ? formatDate($close_date) : '-';
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Tanggal Pelaksanaan:</label>
                                <span>
                                    <?php 
                                    $event_date = getDateValue($competition, ['tanggal_pelaksanaan', 'event_date', 'pelaksanaan']);
                                    echo $event_date ? formatDate($event_date) : '-';
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>WhatsApp Group:</label>
                                <span>
                                    <?php if (!empty($competition['whatsapp_group'])): ?>
                                        <a href="<?php echo htmlspecialchars($competition['whatsapp_group']); ?>" target="_blank" class="btn-action btn-detail">
                                            <i class="fab fa-whatsapp"></i> Join Group
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="info-item">
                                <label>Tanggal Dibuat:</label>
                                <span>
                                    <?php 
                                    $created_date = getDateValue($competition, ['created_at']);
                                    echo $created_date ? date('d M Y H:i', strtotime($created_date)) : '-';
                                    ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <label>Terakhir Diupdate:</label>
                                <span>
                                    <?php 
                                    $updated_date = getDateValue($competition, ['updated_at']);
                                    echo $updated_date ? date('d M Y H:i', strtotime($updated_date)) : '-';
                                    ?>
                                </span>
                            </div>
                            <div class="info-item full-width">
                                <label>Deskripsi:</label>
                                <span><?php echo nl2br(htmlspecialchars($competition['deskripsi'] ?? '-')); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kontak Panitia Section -->
                    <div class="info-section">
                        <h3><i class="fas fa-phone"></i> Kontak Panitia</h3>
                        <?php if (empty($contacts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-phone"></i>
                                <p>Belum ada kontak panitia yang ditambahkan.</p>
                                <small>Tambahkan kontak panitia untuk memudahkan peserta menghubungi.</small>
                            </div>
                        <?php else: ?>
                            <div class="contact-list">
                                <?php foreach ($contacts as $contact): ?>
                                <div class="contact-item">
                                    <div class="contact-info">
                                        <strong><?php echo htmlspecialchars($contact['nama_kontak']); ?></strong>
                                        <?php if (!empty($contact['jabatan'])): ?>
                                            <span class="contact-position"><?php echo htmlspecialchars($contact['jabatan']); ?></span>
                                        <?php endif; ?>
                                        <div class="contact-number">
                                            <!-- Corrected column name from 'nomor_kontak' to 'nomor_whatsapp' -->
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['nomor_whatsapp']); ?>
                                        </div>
                                    </div>
                                    <div class="contact-actions">
                                        <!-- Corrected column name from 'nomor_kontak' to 'nomor_whatsapp' -->
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact['nomor_whatsapp']); ?>" 
                                           target="_blank" class="btn-action btn-whatsapp">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="info-section">
                        <h3><i class="fas fa-file-alt"></i> Dokumen</h3>
                        <?php if (empty($documents)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>Belum ada dokumen yang diupload.</p>
                                <small>Upload dokumen seperti technical meeting, peraturan, atau panduan untuk peserta.</small>
                            </div>
                        <?php else: ?>
                            <div class="document-list">
                                <?php foreach ($documents as $doc): ?>
                                <div class="document-item">
                                    <i class="fas fa-file-pdf"></i>
                                    <span><?php echo htmlspecialchars($doc['nama_dokumen']); ?></span>
                                    <a href="../../uploads/documents/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn-action btn-detail">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="info-section">
                        <h3><i class="fas fa-list"></i> Kategori Tanding</h3>
                        <?php if (empty($categories)): ?>
                            <div class="empty-state">
                                <i class="fas fa-list"></i>
                                <p>Belum ada kategori tanding yang ditambahkan.</p>
                                <small>Tambahkan kategori tanding seperti kelas berat, tinggi, atau jenis kelamin.</small>
                            </div>
                        <?php else: ?>
                            <div class="category-list">
                                <?php foreach ($categories as $cat): ?>
                                <div class="category-item">
                                    <div class="category-header">
                                        <strong><?php echo htmlspecialchars($cat['nama_kategori']); ?></strong>
                                    </div>
                                    <div class="category-details">
                                        <?php if ($cat['age_category_name']): ?>
                                            <div class="detail-info">
                                                <span class="detail-label">Kategori Umur:</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($cat['age_category_name']); ?> (<?php echo $cat['usia_min']; ?>-<?php echo $cat['usia_max']; ?> tahun)</span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($cat['berat_min'] || $cat['berat_max']): ?>
                                            <div class="detail-info">
                                                <span class="detail-label">Berat Badan:</span>
                                                <span class="detail-value">
                                                    <?php 
                                                    if ($cat['berat_min'] && $cat['berat_max']) {
                                                        echo $cat['berat_min'] . ' - ' . $cat['berat_max'] . ' kg';
                                                    } elseif ($cat['berat_min']) {
                                                        echo 'Min ' . $cat['berat_min'] . ' kg';
                                                    } elseif ($cat['berat_max']) {
                                                        echo 'Max ' . $cat['berat_max'] . ' kg';
                                                    }
                                                    ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($cat['deskripsi'])): ?>
                                            <div class="detail-info">
                                                <span class="detail-label">Deskripsi:</span>
                                                <span class="detail-value"><?php echo nl2br(htmlspecialchars($cat['deskripsi'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="info-section">
                        <h3><i class="fas fa-calendar"></i> Kategori Umur</h3>
                        <?php if (empty($age_categories)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar"></i>
                                <p>Belum ada kategori umur yang ditambahkan.</p>
                                <small>Tambahkan kategori umur seperti junior, senior, atau dewasa.</small>
                            </div>
                        <?php else: ?>
                            <div class="age-category-list">
                                <?php foreach ($age_categories as $age_cat): ?>
                                <div class="age-category-item">
                                    <strong><?php echo htmlspecialchars($age_cat['nama_kategori']); ?></strong>
                                    <span class="age-range">(<?php echo $age_cat['usia_min']; ?> - <?php echo $age_cat['usia_max']; ?> tahun)</span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="info-section">
                        <h3><i class="fas fa-star"></i> Jenis Kompetisi</h3>
                        <?php if (empty($competition_types)): ?>
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                <p>Belum ada jenis kompetisi yang ditambahkan.</p>
                                <small>Tambahkan jenis kompetisi seperti tanding, tunggal, ganda, atau regu.</small>
                            </div>
                        <?php else: ?>
                            <div class="competition-type-list">
                                <?php foreach ($competition_types as $comp_type): ?>
                                <div class="competition-type-item">
                                    <div class="competition-type-header">
                                        <strong><?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?></strong>
                                    </div>
                                    <div class="competition-type-details">
                                        <?php if ($comp_type['biaya_pendaftaran']): ?>
                                            <div class="detail-info">
                                                <span class="detail-label">Biaya Pendaftaran:</span>
                                                <span class="detail-value price"><?php echo formatRupiah($comp_type['biaya_pendaftaran']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($comp_type['deskripsi'])): ?>
                                            <div class="detail-info">
                                                <span class="detail-label">Deskripsi:</span>
                                                <span class="detail-value"><?php echo nl2br(htmlspecialchars($comp_type['deskripsi'])); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Payment Methods in General Information -->
                    <div class="info-section">
                        <h3><i class="fas fa-credit-card"></i> Metode Pembayaran</h3>
                        <?php if (!empty($payment_methods)): ?>
                            <div class="payment-methods-list">
                                <?php foreach ($payment_methods as $payment): ?>
                                    <div class="payment-method-item">
                                        <div class="payment-method-header">
                                            <strong><?php echo htmlspecialchars($payment['nama_bank']); ?></strong>
                                        </div>
                                        <div class="payment-method-details">
                                            <div class="payment-number"><?php echo htmlspecialchars($payment['nomor_rekening']); ?></div>
                                            <div class="payment-owner">a.n. <?php echo htmlspecialchars($payment['pemilik_rekening']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-credit-card"></i>
                                <p>Belum ada metode pembayaran yang tersedia</p>
                                <small>Metode pembayaran dikelola oleh superadmin</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons Section -->
                    <div class="info-section action-buttons-section">
                        <h3><i class="fas fa-cogs"></i> Aksi</h3>
                        <div class="action-buttons-container">
                            <a href="perlombaan.php" class="btn-back-to-list">
                                <i class="fas fa-arrow-left"></i>
                                <span>Kembali ke Daftar</span>
                            </a>
                            <a href="perlombaan-edit.php?id=<?php echo $competition_id; ?>" class="btn-edit-competition">
                                <i class="fas fa-edit"></i>
                                <span>Edit Perlombaan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pendaftaran Tab -->
            <div id="pendaftaran-tab" class="tab-content">
                <div style="padding: 30px;">
                    <!-- Sub Navigation for Pendaftaran -->
                    <div class="sub-tab-navigation">
                        <button class="sub-tab-btn active" onclick="showSubTab('status-pendaftaran')">
                            <i class="fas fa-clock"></i>
                            <span>Status Pendaftaran</span>
                        </button>
                        <button class="sub-tab-btn" onclick="showSubTab('data-pendaftaran')">
                            <i class="fas fa-check-circle"></i>
                            <span>Data Pendaftaran</span>
                        </button>
                    </div>

                    <!-- Status Pendaftaran Sub Tab -->
                    <div id="status-pendaftaran-subtab" class="sub-tab-content active">
                        <div class="registration-section">
                            <h3><i class="fas fa-clock"></i> Status Pendaftaran</h3>
                            <p>Data atlet yang sudah diupload oleh user dan didaftarkan untuk mengikuti perlombaan</p>
                            
                            <!-- Search input for Status Pendaftaran -->
                            <div class="search-container" style="gap:10px;">
                                <a href="export-registrations.php?competition_id=<?php echo $competition_id; ?>&type=all" class="btn-download-xls" target="_blank">
                                    <i class="fas fa-file-excel"></i> Download Excel
                                </a>
                                <input type="text" id="search-status-pendaftaran" class="search-input" placeholder="Cari nama atlet, kontingen, atau kategori...">
                            </div>
                            
                            <?php if (empty($all_registrations)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <p>Belum ada pendaftaran untuk perlombaan ini</p>
                                    <small>Pendaftaran akan muncul di sini setelah user mendaftarkan atlet mereka</small>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="data-table" id="status-pendaftaran-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Atlet</th>
                                                <th>Nama Kontingen</th>
                                                <th>Penanggung Jawab</th>
                                                <th>Kontak PJ</th>
                                                <th>Kategori</th>
                                                <th>Total Pembayaran</th>
                                                <th>Bukti Pembayaran</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($all_registrations as $index => $reg): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div class="athlete-info">
                                                        <strong><?php echo htmlspecialchars($reg['athlete_name']); ?></strong>
                                                        <small><?php echo $reg['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($reg['nama_kontingen']); ?></td>
                                                <td><?php echo htmlspecialchars($reg['penanggung_jawab']); ?></td>
                                                <td>
                                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $reg['kontak_penanggung_jawab']); ?>" target="_blank" class="contact-link">
                                                        <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($reg['kontak_penanggung_jawab']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="category-info">
                                                        <?php if ($reg['category_name']): ?>
                                                            <div><strong><?php echo htmlspecialchars($reg['category_name']); ?></strong></div>
                                                        <?php endif; ?>
                                                        <?php if ($reg['age_category_name']): ?>
                                                            <small><?php echo htmlspecialchars($reg['age_category_name']); ?></small>
                                                        <?php endif; ?>
                                                        <?php if ($reg['nama_kompetisi']): ?>
                                                            <small><?php echo htmlspecialchars($reg['nama_kompetisi']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="price-amount">
                                                        <?php echo $reg['biaya_pendaftaran'] ? formatRupiah($reg['biaya_pendaftaran']) : 'Gratis'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($reg['payment_proof']): ?>
                                                        <a href="../../uploads/<?php echo htmlspecialchars($reg['payment_proof']); ?>" target="_blank" class="btn-action btn-detail">
                                                            <i class="fas fa-image"></i> Lihat Bukti
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belum upload</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $reg['payment_status']; ?>">
                                                        <?php 
                                                        switch($reg['payment_status']) {
                                                            case 'unpaid': echo 'Belum Bayar'; break;
                                                            case 'paid': echo 'Sudah Bayar'; break;
                                                            case 'verified': echo 'Terverifikasi'; break;
                                                            default: echo 'Unknown';
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="registration_id" value="<?php echo $reg['registration_id']; ?>">
                                                        <select name="payment_status" onchange="this.form.submit()" class="status-select">
                                                            <option value="unpaid" <?php echo $reg['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>Belum Bayar</option>
                                                            <option value="paid" <?php echo $reg['payment_status'] == 'paid' ? 'selected' : ''; ?>>Sudah Bayar</option>
                                                            <option value="verified" <?php echo $reg['payment_status'] == 'verified' ? 'selected' : ''; ?>>Terverifikasi</option>
                                                        </select>
                                                        <input type="hidden" name="update_payment_status" value="1">
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Data Pendaftaran Sub Tab -->
                    <div id="data-pendaftaran-subtab" class="sub-tab-content">
                        <div class="registration-section">
                            <h3><i class="fas fa-check-circle"></i> Data Pendaftaran</h3>
                            <p>Data atlet yang sudah lunas dalam pembayarannya</p>
                            
                            <!-- Search input for Data Pendaftaran -->
                            <div class="search-container" style="gap:10px;">
                                <a href="export-registrations.php?competition_id=<?php echo $competition_id; ?>&type=paid" class="btn-download-xls" target="_blank">
                                    <i class="fas fa-file-excel"></i> Download Excel
                                </a>
                                <input type="text" id="search-data-pendaftaran" class="search-input" placeholder="Cari nama atlet, kontingen, atau kategori...">
                            </div>
                            
                            <?php if (empty($paid_registrations)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-check-circle"></i>
                                    <p>Belum ada atlet yang menyelesaikan pembayaran</p>
                                    <small>Atlet yang sudah lunas akan muncul di sini</small>
                                </div>
                            <?php else: ?>
                                <div class="stats-summary">
                                    <div class="stat-card">
                                        <div class="stat-icon">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="stat-info">
                                            <h4><?php echo count($paid_registrations); ?></h4>
                                            <p>Total Atlet Terverifikasi</p>
                                        </div>
                                    </div>
                                    
                                    <div class="stat-card">
                                        <div class="stat-icon">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="stat-info">
                                            <?php 
                                            $unique_kontingen = array_unique(array_column($paid_registrations, 'nama_kontingen'));
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
                                            $male_count = count(array_filter($paid_registrations, function($reg) { return $reg['jenis_kelamin'] == 'L'; }));
                                            $female_count = count($paid_registrations) - $male_count;
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
                                            $total_revenue = array_sum(array_column($paid_registrations, 'biaya_pendaftaran'));
                                            ?>
                                            <h4><?php echo formatRupiah($total_revenue); ?></h4>
                                            <p>Total Pendapatan</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="data-table" id="data-pendaftaran-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Atlet</th>
                                                <th>Nama Kontingen</th>
                                                <th>Penanggung Jawab</th>
                                                <th>Kontak PJ</th>
                                                <th>Kategori</th>
                                                <th>Status</th>
                                                <th>Tanggal Daftar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($paid_registrations as $index => $reg): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <div class="athlete-info">
                                                        <strong><?php echo htmlspecialchars($reg['athlete_name']); ?></strong>
                                                        <small><?php echo $reg['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($reg['nama_kontingen']); ?></td>
                                                <td><?php echo htmlspecialchars($reg['penanggung_jawab']); ?></td>
                                                <td>
                                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $reg['kontak_penanggung_jawab']); ?>" target="_blank" class="contact-link">
                                                        <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($reg['kontak_penanggung_jawab']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="category-info">
                                                        <?php if ($reg['category_name']): ?>
                                                            <div><strong><?php echo htmlspecialchars($reg['category_name']); ?></strong></div>
                                                        <?php endif; ?>
                                                        <?php if ($reg['age_category_name']): ?>
                                                            <small><?php echo htmlspecialchars($reg['age_category_name']); ?></small>
                                                        <?php endif; ?>
                                                        <?php if ($reg['nama_kompetisi']): ?>
                                                            <small><?php echo htmlspecialchars($reg['nama_kompetisi']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $reg['payment_status']; ?>">
                                                        <?php 
                                                        switch($reg['payment_status']) {
                                                            case 'paid': echo 'Lunas'; break;
                                                            case 'verified': echo 'Terverifikasi'; break;
                                                            default: echo 'Lunas';
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d M Y', strtotime($reg['registration_date'])); ?></td>
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

            <!-- Pertandingan Tab -->
            <div id="tournament-tab" class="tab-content">
                <div style="padding: 30px;">
                    <div class="tournament-section">
                        <h3><i class="fas fa-users"></i> Daftar Peserta</h3>
                        <p>Kelola daftar peserta yang akan bertanding.</p>
                        <button class="btn-primary" onclick="window.location.href='daftar-peserta.php?competition_id=<?php echo $competition_id; ?>'">
                            <i class="fas fa-users"></i> Kelola Peserta
                        </button>
                    </div>
                    
                    <div class="tournament-section">
                        <h3><i class="fas fa-random"></i> Pengundian</h3>
                        <p>Lakukan pengundian untuk menentukan lawan tanding.</p>
                        <button class="btn-primary" onclick="window.location.href='pengundian.php?competition_id=<?php echo $competition_id; ?>'">
                            <i class="fas fa-random"></i> Kelola Pengundian
                        </button>
                    </div>
                    
                    <div class="tournament-section">
                        <h3><i class="fas fa-sitemap"></i> Bagan Tanding</h3>
                        <p>Lihat dan kelola bagan pertandingan.</p>
                        <button class="btn-primary" onclick="window.location.href='bagan-tanding.php?competition_id=<?php echo $competition_id; ?>'">
                            <i class="fas fa-sitemap"></i> Kelola Bagan Tanding
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function showSubTab(subTabName) {
            // Hide all sub tabs
            document.querySelectorAll('.sub-tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.sub-tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected sub tab
            document.getElementById(subTabName + '-subtab').classList.add('active');
            event.target.classList.add('active');
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

        // SEARCHING/FILTERING TABLES
        function filterTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const filter = input.value.toLowerCase();
            const table = document.getElementById(tableId);
            const trs = table.querySelectorAll('tbody tr');
            trs.forEach(tr => {
                let text = tr.textContent.toLowerCase();
                if (text.indexOf(filter) > -1) {
                    tr.style.display = '';
                } else {
                    tr.style.display = 'none';
                }
            });
        }
        document.getElementById('search-status-pendaftaran').addEventListener('input', function() {
            filterTable('search-status-pendaftaran', 'status-pendaftaran-table');
        });
        document.getElementById('search-data-pendaftaran').addEventListener('input', function() {
            filterTable('search-data-pendaftaran', 'data-pendaftaran-table');
        });

        // WhatsApp Send Feature
        function sendToWhatsapp(type) {
            const competitionId = <?php echo (int)$competition_id; ?>;
            fetch(`generate-registrations-xls.php?competition_id=${competitionId}&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const fileLink = window.location.origin + data.link;
                        const waGroup = <?php echo json_encode($competition['whatsapp_group'] ?? ''); ?>;
                        let waMsg = encodeURIComponent('Berikut data atlet dalam format Excel: ' + fileLink);
                        let waUrl = waGroup ? waGroup : 'https://web.whatsapp.com/';
                        let modalHtml = `
                            <div id='wa-modal-bg' style='position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:9999;display:flex;align-items:center;justify-content:center;'>
                                <div id='wa-modal' style='background:#fff;padding:32px 24px;border-radius:12px;max-width:400px;width:100%;box-shadow:0 8px 32px rgba(0,0,0,0.15);position:relative;'>
                                    <button onclick='document.getElementById("wa-modal-bg").remove()' style='position:absolute;top:10px;right:10px;background:none;border:none;font-size:1.5rem;cursor:pointer;'>&times;</button>
                                    <h2 style='margin-bottom:18px;font-size:1.2rem;'>Kirim ke WhatsApp Grup</h2>
                                    <div style='margin-bottom:12px;'>
                                        <b>Link File Excel:</b><br>
                                        <input type='text' value='${fileLink}' readonly style='width:100%;padding:6px 8px;border:1px solid #ccc;border-radius:5px;font-size:1rem;margin-top:4px;' onclick='this.select()'>
                                    </div>
                                    <div style='margin-bottom:12px;'>
                                        <b>Link WhatsApp Grup:</b><br>
                                        ${waGroup ? `<a href='${waGroup}' target='_blank' style='color:#25d366;font-weight:600;'>${waGroup}</a>` : '<span style="color:#888;">Belum diinputkan</span>'}
                                    </div>
                                    <a href='${waGroup ? waGroup : 'https://web.whatsapp.com/'}?text='+waMsg target='_blank' class='btn-download-xls' style='background:#25d366;color:#fff;display:block;text-align:center;margin-bottom:10px;'>
                                        <i class="fab fa-whatsapp"></i> Buka WhatsApp Web & Kirim Pesan
                                    </a>
                                    <div style='font-size:0.95rem;color:#888;'>Salin link file lalu upload ke grup jika ingin mengirim file langsung.</div>
                                </div>
                            </div>
                        `;
                        document.body.insertAdjacentHTML('beforeend', modalHtml);
                    } else {
                        alert('Gagal generate file: ' + (data.error || 'Unknown error'));
                    }
                });
        }
    </script>

    <style>
        /* Action Buttons Section Styles */
        .action-buttons-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
            border: 2px solid #cbd5e1 !important;
        }

        .action-buttons-section h3 {
            color: #475569 !important;
            font-size: 1.4rem !important;
        }

        .action-buttons-section h3 i {
            background: linear-gradient(135deg, #475569 0%, #334155 100%) !important;
        }

        .action-buttons-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .btn-back-to-list {
            background: linear-gradient(135deg, #6b7280, #4b5563, #374151);
            color: white;
            padding: 18px 32px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.3);
            position: relative;
            overflow: hidden;
            min-width: 200px;
            justify-content: center;
        }

        .btn-back-to-list::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s;
        }

        .btn-back-to-list:hover {
            background: linear-gradient(135deg, #4b5563, #374151, #1f2937);
            color: white;
            text-decoration: none;
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(107, 114, 128, 0.4);
        }

        .btn-back-to-list:hover::before {
            left: 100%;
        }

        .btn-back-to-list:active {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.35);
        }

        .btn-edit-competition {
            background: linear-gradient(135deg, #f59e0b, #d97706, #b45309);
            color: white;
            padding: 18px 32px;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
            position: relative;
            overflow: hidden;
            min-width: 200px;
            justify-content: center;
        }

        .btn-edit-competition::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transition: left 0.6s;
        }

        .btn-edit-competition:hover {
            background: linear-gradient(135deg, #d97706, #b45309, #92400e);
            color: white;
            text-decoration: none;
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(245, 158, 11, 0.4);
        }

        .btn-edit-competition:hover::before {
            left: 100%;
        }

        .btn-edit-competition:active {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(245, 158, 11, 0.35);
        }

        .page-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        /* Modern Table Container Styles */
        .table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin-top: 20px;
        }

        .table-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 24px;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
        }
        
        .table-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 50%, #0ea5e9 100%);
        }

        .tab-navigation {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 12px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .tab-navigation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 50%, #0ea5e9 100%);
            opacity: 0.3;
        }
        
        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #64748b;
            position: relative;
            overflow: hidden;
            min-width: 140px;
            justify-content: center;
            transform: translateY(0);
        }
        
        .tab-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
            transform: scale(0.8);
        }
        
        .tab-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }
        
        .tab-btn span {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        
        .tab-btn i {
            position: relative;
            z-index: 2;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .tab-btn:hover {
            color: #0ea5e9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.2);
        }
        
        .tab-btn:hover::before {
            opacity: 0.1;
            transform: scale(1);
        }
        
        .tab-btn:hover i {
            transform: scale(1.15) rotate(5deg);
            color: #0ea5e9;
        }
        
        .tab-btn:hover span {
            transform: translateX(2px);
        }
        
        .tab-btn:active {
            transform: translateY(0);
            transition: all 0.1s ease;
        }
        
        .tab-btn:active::after {
            width: 300px;
            height: 300px;
            opacity: 0;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
            animation: tabActivePulse 0.6s ease-out;
        }
        
        .tab-btn.active::before {
            opacity: 1;
            transform: scale(1);
        }
        
        .tab-btn.active i {
            transform: scale(1.2) rotate(0deg);
            animation: iconBounce 0.6s ease-out;
        }
        
        .tab-btn.active span {
            transform: translateX(0);
            animation: textSlide 0.4s ease-out;
        }
        
        @keyframes tabActivePulse {
            0% {
                box-shadow: 0 4px 16px rgba(14, 165, 233, 0.25);
                transform: translateY(-1px);
            }
            50% {
                box-shadow: 0 12px 35px rgba(14, 165, 233, 0.4);
                transform: translateY(-3px);
            }
            100% {
                box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
                transform: translateY(-2px);
            }
        }
        
        @keyframes iconBounce {
            0% {
                transform: scale(1) rotate(0deg);
            }
            50% {
                transform: scale(1.3) rotate(10deg);
            }
            100% {
                transform: scale(1.2) rotate(0deg);
            }
        }
        
        @keyframes textSlide {
            0% {
                transform: translateX(-5px);
                opacity: 0.8;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .tab-content {
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .tab-content.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
            animation: contentSlideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes contentSlideIn {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }
            50% {
                opacity: 0.7;
                transform: translateY(10px) scale(0.99);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .sub-tab-content {
            display: none;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sub-tab-content.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
            animation: contentSlideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sub-tab-navigation {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 12px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .sub-tab-navigation::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 50%, #0ea5e9 100%);
            opacity: 0.3;
        }

        .sub-tab-btn {
            padding: 12px 24px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            color: #64748b;
            position: relative;
            overflow: hidden;
            min-width: 140px;
            justify-content: center;
        }
        
        .sub-tab-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
            transform: scale(0.8);
        }
        
        .sub-tab-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1;
        }
        
        .sub-tab-btn span {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .sub-tab-btn i {
            position: relative;
            z-index: 2;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sub-tab-btn:hover {
            color: #0ea5e9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.2);
        }
        
        .sub-tab-btn:hover::before {
            opacity: 0.1;
            transform: scale(1);
        }
        
        .sub-tab-btn:hover i {
            transform: scale(1.15) rotate(5deg);
            color: #0ea5e9;
        }
        
        .sub-tab-btn:hover span {
            transform: translateX(2px);
        }
        
        .sub-tab-btn:active {
            transform: translateY(0);
            transition: all 0.1s ease;
        }
        
        .sub-tab-btn:active::after {
            width: 300px;
            height: 300px;
            opacity: 0;
        }
        
        .sub-tab-btn.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
            animation: tabActivePulse 0.6s ease-out;
        }
        
        .sub-tab-btn.active::before {
            opacity: 1;
            transform: scale(1);
        }
        
        .sub-tab-btn.active i {
            transform: scale(1.2) rotate(0deg);
            animation: iconBounce 0.6s ease-out;
        }
        
        .sub-tab-btn.active span {
            transform: translateX(0);
            animation: textSlide 0.4s ease-out;
        }
        

        
        /* Loading state for tab switching */
        .tab-content.loading {
            position: relative;
        }
        
        .tab-content.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #e2e8f0;
            border-top: 2px solid #0ea5e9;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .sub-tab-content {
            display: none;
        }

        .sub-tab-content.active {
            display: block;
        }
        
        .info-section {
            margin-bottom: 40px;
            padding: 24px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .info-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #0ea5e9 0%, #0284c7 100%);
        }
        
        .info-section:last-child {
            margin-bottom: 0;
        }
        
        .info-section h3 {
            color: #0ea5e9;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .info-section h3 i {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            padding: 8px;
            border-radius: 8px;
            font-size: 1rem;
        }

        /* Poster Display Styles */
        .poster-display {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 20px;
            background: var(--light-color);
            border-radius: 12px;
        }

        .competition-poster {
            max-width: 100%;
            max-height: 600px;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }

        .competition-poster:hover {
            transform: scale(1.02);
        }

        .poster-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-download {
            background: #4caf50;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.3s;
        }

        .btn-download:hover {
            background: #45a049;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-item.full-width {
            grid-column: 1 / -1;
        }
        
        .info-item label {
            font-weight: 600;
            color: var(--text-color);
        }
        
        .info-item span {
            color: var(--text-light);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-open_regist {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-close_regist {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-coming_soon {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-finished {
            background-color: #f8d7da;
            color: #721c24;
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

        /* Contact List Styles */
        .contact-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contact-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--light-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .contact-info strong {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .contact-position {
            background: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-left: 10px;
        }

        .contact-number {
            color: var(--text-light);
            margin-top: 5px;
        }

        .contact-actions {
            display: flex;
            gap: 10px;
        }

        .btn-whatsapp {
            background: #25d366;
            color: white;
        }

        .btn-whatsapp:hover {
            background: #128c7e;
        }

        /* Payment Methods List Styles */
        .payment-methods-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .payment-method-item {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
        
        .payment-method-header strong {
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .payment-method-details {
            margin-top: 10px;
        }

        .payment-number {
            font-weight: bold;
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .payment-owner {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Document List Styles */
        .document-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .document-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--light-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .document-item i {
            color: #dc3545;
            font-size: 1.5rem;
        }

        .document-item span {
            flex: 1;
            font-weight: 500;
        }

        /* Category List Styles */
        .category-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .category-item {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .category-header strong {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .category-details {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail-info {
            display: flex;
            gap: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-color);
            min-width: 120px;
        }

        .detail-value {
            color: var(--text-light);
            flex: 1;
        }

        .detail-value.price {
            color: var(--success-color);
            font-weight: 600;
        }

        /* Age Category List Styles */
        .age-category-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .age-category-item {
            background: var(--light-color);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .age-category-item strong {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .age-range {
            color: var(--text-light);
            margin-top: 5px;
        }

        /* Competition Type List Styles */
        .competition-type-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .competition-type-item {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .competition-type-header strong {
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .competition-type-details {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        /* Registration Section Styles */
        .registration-section {
            margin-bottom: 30px;
        }

        .registration-section h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .registration-section p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
            margin-top: 20px;
        }

        .athlete-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .athlete-info strong {
            color: var(--text-color);
        }

        .athlete-info small {
            color: var(--text-light);
            font-size: 0.8rem;
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

        .contact-link {
            color: #25d366;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .contact-link:hover {
            color: #128c7e;
        }

        .price-amount {
            color: var(--success-color);
            font-weight: 600;
        }

        .status-select {
            padding: 4px 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.8rem;
            background: white;
        }

        .text-muted {
            color: var(--text-light);
            font-style: italic;
        }

        /* Stats Summary Styles */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: white;
            font-size: 1.5rem;
        }

        .stat-info h4 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.5rem;
        }

        .stat-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Tournament Section Styles */
        .tournament-section {
            background: var(--light-color);
            padding: 25px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .tournament-section h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tournament-section p {
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: var(--light-color);
            border-radius: 8px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .empty-state small {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .tab-navigation {
                flex-direction: column;
            }

            .sub-tab-navigation {
                flex-direction: column;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .stats-summary {
                grid-template-columns: 1fr;
            }

            .age-category-list {
                grid-template-columns: 1fr;
            }

            .contact-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .contact-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .poster-display {
                padding: 15px;
            }

            .competition-poster {
                max-height: 400px;
            }

            .action-buttons-container {
                flex-direction: column;
                align-items: center;
            }

            .btn-back-to-list,
            .btn-edit-competition {
                min-width: 100%;
                max-width: 300px;
            }
        }

        .search-container {
            margin-bottom: 15px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }
        .search-input {
            padding: 8px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            width: 100%;
            max-width: 350px;
            background: #fff;
            transition: border 0.2s;
        }
        .search-input:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        .btn-download-xls {
            background: #1d6f42;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            transition: background 0.2s;
        }
        .btn-download-xls:hover {
            background: #218838;
            color: #fff;
        }
    </style>
</body>
</html>
