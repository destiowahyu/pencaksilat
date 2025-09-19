<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit();
}

$competition_id = $_GET['id'] ?? 0;

if (!$competition_id) {
    header('Location: perlombaan.php');
    exit();
}

// Get competition details
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND status = 'active'");
$stmt->execute([$competition_id]);
$competition = $stmt->fetch();

if (!$competition) {
    sendNotification('Perlombaan tidak ditemukan atau tidak aktif.', 'error');
    header('Location: perlombaan.php');
    exit();
}

// Registration status logic
$registration_status = $competition['registration_status'] ?? 'coming_soon';
if ($registration_status === 'auto' || empty($registration_status)) {
    $today = date('Y-m-d');
    $open_date = $competition['tanggal_open_regist'];
    $close_date = $competition['tanggal_close_regist'];
    if ($open_date && $close_date) {
        if ($today < $open_date) {
            $registration_status = 'coming_soon';
        } elseif ($today >= $open_date && $today <= $close_date) {
            $registration_status = 'open_regist';
        } else {
            $registration_status = 'close_regist';
        }
    }
}

// Get other details like contacts, documents, categories, etc.
$contacts = $pdo->prepare("SELECT * FROM competition_contacts WHERE competition_id = ?");
$contacts->execute([$competition_id]);
$contacts = $contacts->fetchAll();

$documents = $pdo->prepare("SELECT * FROM competition_documents WHERE competition_id = ?");
$documents->execute([$competition_id]);
$documents = $documents->fetchAll();

$categories = $pdo->prepare("SELECT cc.*, ac.nama_kategori as age_category_name FROM competition_categories cc LEFT JOIN age_categories ac ON cc.age_category_id = ac.id WHERE cc.competition_id = ?");
$categories->execute([$competition_id]);
$categories = $categories->fetchAll();

$payment_methods = $pdo->prepare("SELECT * FROM payment_methods WHERE status = 'active'");
$payment_methods->execute();
$payment_methods = $payment_methods->fetchAll();

// Get age categories
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$age_categories = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$competition_id]);
$competition_types = $stmt->fetchAll();

// Get total registrations
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registrations WHERE competition_id = ?");
$stmt->execute([$competition_id]);
$total_registrations = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Perlombaan - <?php echo htmlspecialchars($competition['nama_perlombaan']); ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .competition-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .competition-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .competition-header h1 {
            margin: 0 0 15px 0;
            font-size: 2.2rem;
        }
        .competition-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }
        .registration-status {
            background: rgba(255,255,255,0.2);
            padding: 12px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
        }
        .registration-status small {
            display: block;
            margin-top: 5px;
            opacity: 0.8;
            font-size: 0.8rem;
        }
        .status-open_regist {
            background: #dcfce7;
            color: #166534;
        }
        .status-coming_soon {
            background: #fef3c7;
            color: #92400e;
        }
        .status-close_regist {
            background: #fee2e2;
            color: #991b1b;
        }
        .detail-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }
        .detail-section {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .section-header {
            background: var(--light-color);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        .section-header h3 {
            margin: 0;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-body {
            padding: 25px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }
        .info-value {
            color: var(--text-light);
            line-height: 1.5;
        }
        .description {
            line-height: 1.7;
            color: var(--text-color);
        }
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--light-color);
            border-radius: 8px;
        }
        .contact-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        .contact-details {
            flex: 1;
        }
        .contact-label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }
        .contact-value {
            color: var(--text-light);
        }
        .document-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .document-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--light-color);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s;
        }
        .document-item:hover {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
        }
        .document-icon {
            font-size: 1.2rem;
        }
        .document-info {
            flex: 1;
        }
        .document-name {
            font-weight: 600;
            margin-bottom: 2px;
        }
        .document-desc {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .category-card {
            background: var(--light-color);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
        .category-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        .category-range {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        .competition-type-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .type-item {
            background: var(--light-color);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--success-color);
        }
        .type-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 5px;
        }
        .type-price {
            font-size: 0.9rem;
            color: var(--success-color);
            font-weight: 600;
        }
        .type-description {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 5px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: var(--shadow);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        @media (max-width: 768px) {
            .detail-sections {
                grid-template-columns: 1fr;
            }
            .competition-meta {
                flex-direction: column;
                gap: 15px;
            }
            .action-buttons {
                flex-direction: column;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .category-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .competition-header {
                padding: 20px;
            }
            .competition-header h1 {
                font-size: 1.8rem;
            }
        }
    </style>
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
            <div style="margin-bottom: 10px;">
                <a href="perlombaan.php" style="color: var(--text-light); text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Kembali ke Perlombaan
                </a>
            </div>
            <h1 class="page-title">Detail Perlombaan</h1>
            <p class="page-subtitle">Informasi lengkap tentang perlombaan</p>
        </div>
        <div class="competition-detail-container">
            <!-- Competition Header -->
            <div class="competition-header">
                <?php if (!empty($competition['poster'])): ?>
                    <div style="margin-bottom: 25px;">
                        <img src="../../uploads/posters/<?php echo htmlspecialchars($competition['poster']); ?>" alt="Poster Perlombaan" style="max-width: 100%; max-height: 350px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); object-fit: contain; background: #fff;" loading="lazy">
                    </div>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></h1>
                <div class="competition-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('d M Y', strtotime($competition['tanggal_pelaksanaan'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($competition['lokasi'] ?? 'TBA'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span><?php echo $total_registrations; ?> Pendaftar</span>
                    </div>
                </div>
                <div class="registration-status status-<?php echo $registration_status; ?>">
                    <?php 
                    switch($registration_status) {
                        case 'open_regist': 
                            echo 'Buka Pendaftaran';
                            if ($competition['tanggal_close_regist']) {
                                echo '<br><small>Berakhir: ' . date('d M Y', strtotime($competition['tanggal_close_regist'])) . '</small>';
                            }
                            break;
                        case 'close_regist': 
                            echo 'Tutup Pendaftaran';
                            if ($competition['tanggal_close_regist']) {
                                echo '<br><small>Ditutup: ' . date('d M Y', strtotime($competition['tanggal_close_regist'])) . '</small>';
                            }
                            break;
                        case 'coming_soon': 
                            echo 'Segera Dibuka';
                            if ($competition['tanggal_open_regist']) {
                                echo '<br><small>Dibuka: ' . date('d M Y', strtotime($competition['tanggal_open_regist'])) . '</small>';
                            }
                            break;
                        default: echo 'Tidak Aktif';
                    }
                    ?>
                </div>
            </div>
            <!-- General Information -->
            <div class="detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-info-circle"></i> Informasi Umum</h3>
                </div>
                <div class="section-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Nama Perlombaan</div>
                            <div class="info-value"><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tanggal Pelaksanaan</div>
                            <div class="info-value"><?php echo date('d M Y', strtotime($competition['tanggal_pelaksanaan'])); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Lokasi</div>
                            <div class="info-value"><?php echo htmlspecialchars($competition['lokasi'] ?? 'TBA'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Periode Pendaftaran</div>
                            <div class="info-value">
                                <?php echo date('d M Y', strtotime($competition['tanggal_open_regist'])); ?> - 
                                <?php echo date('d M Y', strtotime($competition['tanggal_close_regist'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($competition['deskripsi']): ?>
                        <div style="margin-top: 20px;">
                            <div class="info-label">Deskripsi</div>
                            <div class="description"><?php echo nl2br(htmlspecialchars($competition['deskripsi'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Age Categories -->
            <?php if (!empty($age_categories)): ?>
            <div class="detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-birthday-cake"></i> Kategori Umur</h3>
                </div>
                <div class="section-body">
                    <div class="category-grid">
                        <?php foreach ($age_categories as $age_cat): ?>
                            <div class="category-card">
                                <div class="category-name"><?php echo htmlspecialchars($age_cat['nama_kategori']); ?></div>
                                <div class="category-range"><?php echo $age_cat['usia_min']; ?> - <?php echo $age_cat['usia_max']; ?> tahun</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- Competition Types -->
            <?php if (!empty($competition_types)): ?>
            <div class="detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-star"></i> Jenis Kompetisi</h3>
                </div>
                <div class="section-body">
                    <div class="competition-type-list">
                        <?php foreach ($competition_types as $comp_type): ?>
                            <div class="type-item">
                                <div class="type-name"><?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?></div>
                                <div class="type-price">
                                    <?php if ($comp_type['biaya_pendaftaran']): ?>
                                        <?php echo 'Rp ' . number_format($comp_type['biaya_pendaftaran'], 0, ',', '.'); ?>
                                    <?php else: ?>
                                        Gratis
                                    <?php endif; ?>
                                </div>
                                <?php if ($comp_type['deskripsi']): ?>
                                    <div class="type-description"><?php echo htmlspecialchars($comp_type['deskripsi']); ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- Documents -->
            <?php if (!empty($documents)): ?>
            <div class="detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-file-alt"></i> Dokumen Perlombaan</h3>
                </div>
                <div class="section-body">
                    <div class="document-list">
                        <?php foreach ($documents as $doc): ?>
                        <a href="../../uploads/documents/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="document-item">
                            <div class="document-icon">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="document-info">
                                <div class="document-name"><?php echo htmlspecialchars($doc['nama_dokumen']); ?></div>
                                <div class="document-desc">Klik untuk melihat dokumen lengkap</div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- Contact Information -->
            <?php if (!empty($contacts)): ?>
            <div class="detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-phone"></i> Kontak Panitia</h3>
                </div>
                <div class="section-body">
                    <div class="contact-info">
                        <?php foreach ($contacts as $contact): ?>
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-address-book"></i>
                                </div>
                                <div class="contact-details">
                                    <div class="contact-label">
                                        <strong><?php echo htmlspecialchars($contact['nama_kontak']); ?></strong>
                                        <?php if (!empty($contact['jabatan'])): ?>
                                            <span style="color: var(--text-light); font-size: 0.9em; margin-left: 8px;">(<?php echo htmlspecialchars($contact['jabatan']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($contact['nomor_whatsapp'])): ?>
                                        <div class="contact-value">
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact['nomor_whatsapp']); ?>" target="_blank" style="color: #25d366; text-decoration: none;">
                                                <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($contact['nomor_whatsapp']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($contact['email'])): ?>
                                        <div class="contact-value">
                                            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" style="color: var(--primary-color); text-decoration: none;">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="perlombaan.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <?php if ($registration_status === 'open_regist'): ?>
                    <a href="daftar-perlombaan.php?id=<?php echo $competition_id; ?>" class="btn-primary">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </a>
                <?php else: ?>
                    <button class="btn-primary btn-disabled" disabled>
                        <i class="fas fa-lock"></i> 
                        <?php 
                        switch($registration_status) {
                            case 'close_regist': echo 'Pendaftaran Ditutup'; break;
                            case 'coming_soon': echo 'Pendaftaran Segera Dibuka'; break;
                            default: echo 'Pendaftaran Tidak Aktif';
                        }
                        ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
