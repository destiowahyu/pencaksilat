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

// Get total registrations for this competition (for header)
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registrations WHERE competition_id = ?");
$stmt->execute([$competition_id]);
$total_registrations = $stmt->fetch()['total'];

if (!$competition) {
    sendNotification('Perlombaan tidak ditemukan atau tidak aktif.', 'error');
    header('Location: perlombaan.php');
    exit();
}

// Check registration status
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

if ($registration_status !== 'open_regist') {
    sendNotification('Pendaftaran untuk perlombaan ini sudah ditutup atau belum dibuka.', 'error');
    header('Location: perlombaan.php');
    exit();
}

// Get user's athletes
$stmt = $pdo->prepare("
    SELECT a.*, k.nama_kontingen 
    FROM athletes a 
    JOIN kontingen k ON a.kontingen_id = k.id 
    WHERE a.user_id = ? 
    ORDER BY a.nama
");
$stmt->execute([$_SESSION['user_id']]);
$athletes = $stmt->fetchAll();

if (empty($athletes)) {
    sendNotification('Anda belum memiliki data atlet. Silakan tambahkan data atlet terlebih dahulu.', 'error');
    header('Location: data-atlet.php');
    exit();
}

// Get age categories
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$age_categories = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$competition_id]);
$competition_types = $stmt->fetchAll();

// Handle form submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'register') {
    $athlete_id = $_POST['athlete_id'] ?? 0;
    $age_category_id = $_POST['age_category_id'] ?? 0;
    $category_id = $_POST['category_id'] ?? null;
    $competition_type_id = $_POST['competition_type_id'] ?? 0;
    
    try {
        // Validate athlete belongs to user
        $stmt = $pdo->prepare("SELECT a.*, k.id as kontingen_id FROM athletes a JOIN kontingen k ON a.kontingen_id = k.id WHERE a.id = ? AND a.user_id = ?");
        $stmt->execute([$athlete_id, $_SESSION['user_id']]);
        $athlete = $stmt->fetch();
        
        if (!$athlete) {
            throw new Exception('Atlet tidak ditemukan atau bukan milik Anda.');
        }
        
        // Check if athlete already registered for this competition with the same combination
        // of competition type, age category, and (if applicable) tanding category
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations 
            WHERE competition_id = ? AND athlete_id = ?
              AND COALESCE(competition_type_id, 0) = COALESCE(?, 0)
              AND COALESCE(age_category_id, 0) = COALESCE(?, 0)
              AND COALESCE(category_id, 0) = COALESCE(?, 0)");
        $stmt->execute([$competition_id, $athlete_id, $competition_type_id ?: null, $age_category_id ?: null, $category_id ?: null]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Atlet sudah terdaftar untuk kombinasi kategori yang sama dalam perlombaan ini.');
        }
        
        // Validate age category
        if ($age_category_id) {
            $stmt = $pdo->prepare("SELECT * FROM age_categories WHERE id = ? AND competition_id = ?");
            $stmt->execute([$age_category_id, $competition_id]);
            $age_category = $stmt->fetch();
            
            if (!$age_category) {
                throw new Exception('Kategori umur tidak valid.');
            }
            
            // Check athlete age
            $athlete_age = date_diff(date_create($athlete['tanggal_lahir']), date_create('today'))->y;
            if ($athlete_age < $age_category['usia_min'] || $athlete_age > $age_category['usia_max']) {
                throw new Exception('Umur atlet tidak sesuai dengan kategori yang dipilih.');
            }
        }
        
        // Validate competition category if selected
        if ($category_id) {
            $stmt = $pdo->prepare("SELECT * FROM competition_categories WHERE id = ? AND competition_id = ?");
            $stmt->execute([$category_id, $competition_id]);
            $category = $stmt->fetch();
            
            if (!$category) {
                throw new Exception('Kategori tanding tidak valid.');
            }
            
            // Check weight if category has weight limits
            if (($category['berat_min'] && $athlete['berat_badan'] < $category['berat_min']) ||
                ($category['berat_max'] && $athlete['berat_badan'] > $category['berat_max'])) {
                throw new Exception('Berat badan atlet tidak sesuai dengan kategori yang dipilih.');
            }
        }
        
        // Validate competition type
        $stmt = $pdo->prepare("SELECT * FROM competition_types WHERE id = ? AND competition_id = ?");
        $stmt->execute([$competition_type_id, $competition_id]);
        $competition_type = $stmt->fetch();
        
        if (!$competition_type) {
            throw new Exception('Jenis kompetisi tidak valid.');
        }
        
        // Insert registration
        $stmt = $pdo->prepare("
            INSERT INTO registrations (
                competition_id, athlete_id, age_category_id, competition_type_id, category_id, payment_status, created_at
            ) VALUES (?, ?, ?, ?, ?, 'unpaid', NOW())
        ");
        $stmt->execute([
            $competition_id, 
            $athlete_id, 
            $age_category_id ?: null,
            $competition_type_id ?: null,
            $category_id ?: null
        ]);
        
        $registration_id = $pdo->lastInsertId();
        
        // Get WhatsApp group link if available
        $whatsapp_group = $competition['whatsapp_group'] ?? '';
        
        sendNotification('Pendaftaran berhasil! Silakan lakukan pembayaran dan upload bukti pembayaran.', 'success');
        
        // Redirect with WhatsApp group info
        if ($whatsapp_group) {
            $_SESSION['whatsapp_group'] = $whatsapp_group;
        }
        
        // Redirect with success and WhatsApp group info
        if ($whatsapp_group) {
            header('Location: perlombaan.php?tab=registered-athletes&success=1&whatsapp=' . urlencode($whatsapp_group));
        } else {
        header('Location: perlombaan.php?tab=registered-athletes&success=1');
        }
        exit();
        
    } catch (Exception $e) {
        sendNotification('Gagal mendaftarkan atlet: ' . $e->getMessage(), 'error');
    }
}

// Get registered athletes for this competition by this user
$stmt = $pdo->prepare("
    SELECT r.*, a.nama as athlete_name, a.jenis_kelamin, k.nama_kontingen, ac.nama_kategori as age_category_name,
           cc.nama_kategori as tanding_kategori, r.payment_status
    FROM registrations r
    JOIN athletes a ON r.athlete_id = a.id
    JOIN kontingen k ON a.kontingen_id = k.id
    LEFT JOIN age_categories ac ON r.category_id = ac.id
    LEFT JOIN competition_categories cc ON r.category_id = cc.id
    WHERE r.competition_id = ? AND a.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$competition_id, $_SESSION['user_id']]);
$registered_athletes = $stmt->fetchAll();

// Get notification
$notification = getNotification();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Perlombaan - <?php echo htmlspecialchars($competition['nama_perlombaan']); ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .registration-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .registration-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .registration-header h1 {
            margin: 0 0 10px 0;
            font-size: 1.8rem;
        }

        .registration-header p {
            margin: 0;
            opacity: 0.9;
        }

        .registration-form {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }
        
        .form-section h3::before {
            content: '';
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .form-section:nth-child(2) h3::before { content: '1'; }
        .form-section:nth-child(3) h3::before { content: '2'; }
        .form-section:nth-child(4) h3::before { content: '3'; }
        .form-section:nth-child(5) h3::before { content: '4'; }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group label.required::after {
            content: ' *';
            color: var(--danger-color);
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-group select:disabled {
            background-color: #f3f4f6;
            color: #9ca3af;
            cursor: not-allowed;
        }

        .form-help {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 5px;
        }

        .athlete-info {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .athlete-info h4 {
            margin: 0 0 15px 0;
            color: var(--primary-color);
        }

        .athlete-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .athlete-detail {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .athlete-detail label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .athlete-detail span {
            color: var(--text-light);
        }

        .price-info {
            background: #f0fdf4;
            border: 2px solid var(--success-color);
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .price-info h4 {
            margin: 0 0 10px 0;
            color: var(--success-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .price-amount {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--success-color);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            position: relative;
            z-index: 2;
            margin-bottom: 30px;
        }

        .btn-submit {
            background: #2563eb;
            color: #fff;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.3s;
        }

        .btn-submit:hover {
            background: #1746a2;
        }

        .btn-submit:disabled {
            background: #e5e7eb;
            cursor: not-allowed;
        }

        .btn-cancel {
            background: #fff;
            color: #2563eb;
            border: 2px solid #2563eb;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.3s, color 0.3s, border 0.3s;
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .whatsapp-info {
            background: #dcfce7;
            border: 2px solid #22c55e;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }

        .whatsapp-info h4 {
            margin: 0 0 10px 0;
            color: #166534;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .whatsapp-link {
            background: #22c55e;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .debug-info {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 0.9rem;
        }

        .loading-categories {
            color: var(--text-light);
            font-style: italic;
            padding: 10px;
        }

        .registration-status {
            background: rgba(255,255,255,0.2);
            padding: 12px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            text-align: center;
            margin-top: 10px;
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

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin: 0 2px;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
        }
        .btn-edit {
            background: #fffbe7;
            color: #b58105;
            border: 1px solid #ffe066;
        }
        .btn-edit:hover {
            background: #ffe066;
            color: #7c5a00;
        }
        .btn-invoice {
            background: #e7f0ff;
            color: #2563eb;
            border: 1px solid #2563eb22;
        }
        .btn-invoice:hover {
            background: #2563eb;
            color: #fff;
        }
        .btn-upload {
            background: #e7ffe7;
            color: #1a7f37;
            border: 1px solid #22c55e33;
        }
        .btn-upload:hover {
            background: #22c55e;
            color: #fff;
        }
        .btn-print {
            background: #f3e8ff;
            color: #9333ea;
            border: 1px solid #9333ea22;
        }
        .btn-print:hover {
            background: #9333ea;
            color: #fff;
        }
        .btn-action svg {
            margin-right: 6px;
            font-size: 1.1em;
        }
        @media (max-width: 600px) {
            .btn-action { font-size: 0.95rem; padding: 7px 10px; }
            .btn-action span { display: none; }
        }

        @media (max-width: 768px) {
            .registration-form {
                padding: 20px;
            }
            
            .athlete-details {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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
            <h1 class="page-title">Daftar Perlombaan</h1>
            <p class="page-subtitle">Daftarkan atlet Anda ke perlombaan</p>
        </div>

        <?php if ($notification): ?>
            <div class="alert alert-<?php echo $notification['type'] === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $notification['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> 
                <?php echo $notification['message']; ?>
            </div>
        <?php endif; ?>

        <div class="registration-container">
            <div class="registration-header">
                <h1 style="color:#2563eb !important; font-weight:800; text-shadow:none; margin:0 0 15px 0; font-size:2.2rem;">
                    <?php echo htmlspecialchars($competition['nama_perlombaan']); ?>
                </h1>
                <div class="competition-meta" style="display:flex;justify-content:center;gap:30px;margin-bottom:20px;flex-wrap:wrap;">
                    <div class="meta-item" style="display:flex;align-items:center;gap:8px;font-size:1rem;color:#2563eb;font-weight:700;">
                        <i class="fas fa-calendar" style="color:#2563eb !important;"></i>
                        <span style="color:#2563eb !important;font-weight:700;">
                            <?php echo formatDate($competition['tanggal_pelaksanaan']); ?>
                        </span>
                    </div>
                    <div class="meta-item" style="display:flex;align-items:center;gap:8px;font-size:1rem;color:#2563eb;font-weight:700;">
                        <i class="fas fa-map-marker-alt" style="color:#2563eb !important;"></i>
                        <span style="color:#2563eb !important;font-weight:700;">
                            <?php echo htmlspecialchars($competition['lokasi'] ?? 'Lokasi belum ditentukan'); ?>
                        </span>
                    </div>
                    <div class="meta-item" style="display:flex;align-items:center;gap:8px;font-size:1rem;color:#2563eb;font-weight:700;">
                        <i class="fas fa-users" style="color:#2563eb !important;"></i>
                        <span style="color:#2563eb !important;font-weight:700;">
                            <?php echo $total_registrations; ?> Pendaftar
                        </span>
                    </div>
                </div>
                <!-- Registration Status Badge (match competition-detail.php) -->
                <div class="registration-status status-<?php echo $registration_status; ?>"> 
                    <?php 
                    switch($registration_status) {
                        case 'open_regist': 
                            echo 'Buka Pendaftaran';
                            if ($competition['tanggal_close_regist']) {
                                echo '<br><small>Berakhir: ' . formatDate($competition['tanggal_close_regist']) . '</small>';
                            }
                            break;
                        case 'close_regist': 
                            echo 'Tutup Pendaftaran';
                            if ($competition['tanggal_close_regist']) {
                                echo '<br><small>Ditutup: ' . formatDate($competition['tanggal_close_regist']) . '</small>';
                            }
                            break;
                        case 'coming_soon': 
                            echo 'Segera Dibuka';
                            if ($competition['tanggal_open_regist']) {
                                echo '<br><small>Dibuka: ' . formatDate($competition['tanggal_open_regist']) . '</small>';
                            }
                            break;
                        default: echo 'Tidak Aktif';
                    }
                    ?>
                </div>
            </div>

            <form class="registration-form" method="POST" id="registrationForm">
                <input type="hidden" name="action" value="register">
                
                <!-- Athlete Selection -->
                <div class="form-section">
                    <h3><i class="fas fa-user-ninja"></i> Pilih Atlet</h3>
                    <div class="form-group">
                        <label for="athlete_id" class="required">Atlet</label>
                        <select id="athlete_id" name="athlete_id" required onchange="showAthleteInfo()">
                            <option value="">Pilih Atlet</option>
                            <?php foreach ($athletes as $athlete): ?>
                                <option value="<?php echo $athlete['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($athlete['nama']); ?>"
                                        data-nik="<?php echo htmlspecialchars($athlete['nik']); ?>"
                                        data-gender="<?php echo $athlete['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?>"
                                        data-birth="<?php echo formatDate($athlete['tanggal_lahir']); ?>"
                                        data-age="<?php echo date_diff(date_create($athlete['tanggal_lahir']), date_create('today'))->y; ?>"
                                        data-weight="<?php echo $athlete['berat_badan']; ?>"
                                        data-height="<?php echo $athlete['tinggi_badan']; ?>"
                                        data-kontingen="<?php echo htmlspecialchars($athlete['nama_kontingen']); ?>">
                                    <?php echo htmlspecialchars($athlete['nama']); ?> - <?php echo htmlspecialchars($athlete['nama_kontingen']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">Pilih atlet yang akan didaftarkan</div>
                    </div>
                    
                    <div id="athleteInfo" class="athlete-info" style="display: none;">
                        <h4>Informasi Atlet</h4>
                        <div class="athlete-details">
                            <div class="athlete-detail">
                                <label>Nama:</label>
                                <span id="athleteName">-</span>
                            </div>
                            <div class="athlete-detail">
                                <label>NIK:</label>
                                <span id="athleteNik">-</span>
                            </div>
                            <div class="athlete-detail">
                                <label>Jenis Kelamin:</label>
                                <span id="athleteGender">-</span>
                            </div>
                            <div class="athlete-detail">
                                <label>Tanggal Lahir:</label>
                                <span id="athleteBirth">-</span>
                            </div>
                            <div class="athlete-detail">
                                <label>Umur:</label>
                                <span id="athleteAge">-</span>
                            </div>
                            <div class="athlete-detail">
                                <label>Berat Badan:</label>
                                <span id="athleteWeight">-</span>
                            </div>
                            <div class="athlete-detail">
                                <label>Tinggi Badan:</label>
                                <span id="athleteHeight">-</span>
                            </div>
                            <div class="athlete-detail">
                                <label>Kontingen:</label>
                                <span id="athleteKontingen">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Age Category Selection -->
                <div class="form-section">
                    <h3><i class="fas fa-birthday-cake"></i> Langkah 2: Pilih Kategori Umur</h3>
                    
                    <?php if (!empty($age_categories)): ?>
                    <div class="form-group">
                        <label for="age_category_id" class="required">Kategori Umur</label>
                        <select id="age_category_id" name="age_category_id" required onchange="validateAgeCategory()">
                            <option value="">Pilih Kategori Umur</option>
                            <?php foreach ($age_categories as $age_cat): ?>
                                <option value="<?php echo $age_cat['id']; ?>" 
                                        data-min="<?php echo $age_cat['usia_min']; ?>"
                                        data-max="<?php echo $age_cat['usia_max']; ?>">
                                    <?php echo htmlspecialchars($age_cat['nama_kategori']); ?> (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">Pilih kategori umur sesuai dengan umur atlet yang dipilih</div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Step 3: Competition Type Selection -->
                <div class="form-section">
                    <h3><i class="fas fa-star"></i> Langkah 3: Pilih Jenis Kompetisi</h3>
                    <div class="form-group">
                        <label for="competition_type_id" class="required">Jenis Kompetisi</label>
                        <select id="competition_type_id" name="competition_type_id" required onchange="showPriceInfo()">
                            <option value="">Pilih Jenis Kompetisi</option>
                            <?php foreach ($competition_types as $comp_type): ?>
                                <option value="<?php echo $comp_type['id']; ?>" 
                                        data-price="<?php echo $comp_type['biaya_pendaftaran']; ?>"
                                        data-description="<?php echo htmlspecialchars($comp_type['deskripsi']); ?>"
                                        data-is-tanding="<?php echo stripos($comp_type['nama_kompetisi'], 'tanding') !== false ? 'true' : 'false'; ?>">
                                    <?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?>
                                    <?php if ($comp_type['biaya_pendaftaran']): ?>
                                        - <?php echo formatRupiah($comp_type['biaya_pendaftaran']); ?>
                                    <?php else: ?>
                                        - Gratis
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">Pilih jenis kompetisi yang akan diikuti</div>
                    </div>
                    
                    <div id="priceInfo" class="price-info" style="display: none;">
                        <h4><i class="fas fa-money-bill-wave"></i> Biaya Pendaftaran</h4>
                        <div class="price-amount" id="priceAmount">Rp 0</div>
                        <div id="priceDescription"></div>
                    </div>
                </div>

                <!-- Step 4: Competition Category (Only for Tanding) -->
                <div class="form-section" id="categorySection" style="display: none;">
                    <h3><i class="fas fa-list"></i> Langkah 4: Pilih Kategori Tanding</h3>
                    <div class="form-group" id="categoryGroup">
                        <label for="category_id">Kategori Tanding</label>
                        <select id="category_id" name="category_id">
                            <option value="">Pilih Kategori Tanding</option>
                        </select>
                        <div class="form-help">Pilih kategori tanding sesuai dengan berat badan atlet</div>
                        <div id="categoryLoading" class="loading-categories" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat kategori tanding...
                        </div>
                        <div id="categoryDebug" class="debug-info" style="display: none;"></div>
                    </div>
                </div>

                <!-- WhatsApp Group Info (Hidden initially) -->
                <?php if (!empty($competition['whatsapp_group'])): ?>
                <div class="whatsapp-info" id="whatsappInfo" style="display: none;">
                    <h4><i class="fab fa-whatsapp"></i> Grup WhatsApp Perlombaan</h4>
                    <p>Setelah pendaftaran berhasil, Anda akan mendapatkan akses ke grup WhatsApp perlombaan untuk informasi lebih lanjut.</p>
                    <a href="<?php echo htmlspecialchars($competition['whatsapp_group']); ?>" target="_blank" class="whatsapp-link">
                        <i class="fab fa-whatsapp"></i> Join Grup WhatsApp
                    </a>
                </div>
                <?php endif; ?>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="perlombaan.php" class="btn-cancel">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <span class="loading-spinner" id="loadingSpinner"></span>
                        <i class="fas fa-user-plus" id="submitIcon"></i> 
                        <span id="submitText">Daftar Sekarang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php /*
if (!empty($registered_athletes)): ?>
<div class="registered-athletes-section" style="margin:40px 0 0 0;">
    <h2 style="color:#2563eb;font-weight:700;margin-bottom:20px;">Atlet Terdaftar pada Event Ini</h2>
    <?php
    $total_atlet = count($registered_athletes);
    $total_terverifikasi = 0;
    $total_sudah_bayar = 0;
    $total_menunggu = 0;
    $total_biaya_kontingen = 0;
    foreach ($registered_athletes as $ra) {
        if ($ra['payment_status'] === 'verified') $total_terverifikasi++;
        if ($ra['payment_status'] === 'paid') $total_sudah_bayar++;
        if ($ra['payment_status'] === 'unpaid') $total_menunggu++;
        $total_biaya_kontingen += $ra['biaya_pendaftaran'] ?? 0;
    }
    ?>
    <div class="athlete-summary-stats" style="margin-bottom:20px;display:flex;gap:30px;flex-wrap:wrap;">
        <span><b>Total Atlet:</b> <?php echo $total_atlet; ?></span>
        <span><b>Terverifikasi:</b> <?php echo $total_terverifikasi; ?></span>
        <span><b>Sudah Bayar:</b> <?php echo $total_sudah_bayar; ?></span>
        <span><b>Menunggu:</b> <?php echo $total_menunggu; ?></span>
        <span><b>Total Biaya Kontingen:</b> Rp <?php echo number_format($total_biaya_kontingen,0,',','.'); ?></span>
    </div>
    <div class="table-responsive">
    <table class="table table-bordered" style="width:100%;background:#fff;border-radius:10px;overflow:hidden;">
        <thead style="background:#2563eb;color:#fff;">
            <tr>
                <th>No</th>
                <th>Nama Atlet</th>
                <th>Kategori Umur</th>
                <th>Biaya</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registered_athletes as $i => $ra): ?>
            <tr>
                <td><?php echo $i+1; ?></td>
                <td><?php echo htmlspecialchars($ra['athlete_name']); ?></td>
                <td><?php echo htmlspecialchars($ra['age_category_name'] ?? '-'); ?></td>
                <td>Rp <?php echo number_format($ra['biaya_pendaftaran'] ?? 0,0,',','.'); ?></td>
                <td>
                    <?php
                    switch($ra['payment_status']) {
                        case 'verified': echo '<span style="color:#16a34a;font-weight:700;">Terverifikasi</span>'; break;
                        case 'paid': echo '<span style="color:#2563eb;font-weight:700;">Sudah Bayar</span>'; break;
                        case 'unpaid': default: echo '<span style="color:#991b1b;font-weight:700;">Menunggu</span>'; break;
                    }
                    ?>
                </td>
                <td style="text-align:center;">
                    <!-- tombol aksi -->
                    <a href="edit-pendaftaran.php?id=<?= $ra['id'] ?>" class="btn-action btn-edit" title="Edit Data Atlet">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-1.414.828l-4.243 1.414 1.414-4.243a4 4 0 01.828-1.414z"></path></svg>
                        <span>Edit</span>
                    </a>
                    <a href="generate-invoice.php?id=<?= $ra['id'] ?>" class="btn-action btn-invoice" title="Lihat Invoice" target="_blank">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2M7 7h10M7 11h10M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span>Invoice</span>
                    </a>
                    <a href="upload-payment.php?id=<?= $ra['id'] ?>" class="btn-action btn-upload" title="Upload Bukti Pembayaran" target="_blank">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 16V4m0 0l-4 4m4-4l4 4M20 20H4"></path></svg>
                        <span>Upload</span>
                    </a>
                    <a href="generate-invoice.php?id=<?= $ra['id'] ?>&print=1" class="btn-action btn-print" title="Print Invoice" target="_blank">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V4h12v5M6 18v2a2 2 0 002 2h8a2 2 0 002-2v-2M6 14h12a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"></path></svg>
                        <span>Print</span>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>
*/ ?>

    <script>
        const COMPETITION_ID = <?php echo $competition_id; ?>;
        
        function showAthleteInfo() {
            const select = document.getElementById('athlete_id');
            const option = select.options[select.selectedIndex];
            const info = document.getElementById('athleteInfo');
            
            if (option.value) {
                document.getElementById('athleteName').textContent = option.dataset.name;
                document.getElementById('athleteNik').textContent = option.dataset.nik;
                document.getElementById('athleteGender').textContent = option.dataset.gender;
                document.getElementById('athleteBirth').textContent = option.dataset.birth;
                document.getElementById('athleteAge').textContent = option.dataset.age + ' tahun';
                document.getElementById('athleteWeight').textContent = option.dataset.weight + ' kg';
                document.getElementById('athleteHeight').textContent = option.dataset.height + ' cm';
                document.getElementById('athleteKontingen').textContent = option.dataset.kontingen;
                
                info.style.display = 'block';
                
                // Validate age categories
                validateAgeCategories(parseInt(option.dataset.age));
            } else {
                info.style.display = 'none';
            }
        }
        
        function validateAgeCategory() {
            const athleteSelect = document.getElementById('athlete_id');
            const ageCategorySelect = document.getElementById('age_category_id');
            
            if (athleteSelect.value && ageCategorySelect.value) {
                const athleteAge = parseInt(athleteSelect.options[athleteSelect.selectedIndex].dataset.age);
                const selectedOption = ageCategorySelect.options[ageCategorySelect.selectedIndex];
                const minAge = parseInt(selectedOption.dataset.min);
                const maxAge = parseInt(selectedOption.dataset.max);
                
                if (athleteAge >= minAge && athleteAge <= maxAge) {
                    // Age is valid, enable next step
                    document.getElementById('competition_type_id').disabled = false;
                    showNotification('Kategori umur sesuai dengan atlet yang dipilih', 'success');
                } else {
                    // Age is not valid
                    ageCategorySelect.value = '';
                    document.getElementById('competition_type_id').disabled = true;
                    showNotification('Umur atlet tidak sesuai dengan kategori yang dipilih', 'error');
                }
            }
        }
        
        function validateAgeCategories(athleteAge) {
            const ageCategorySelect = document.getElementById('age_category_id');
            const options = ageCategorySelect.options;
            
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                const minAge = parseInt(option.dataset.min);
                const maxAge = parseInt(option.dataset.max);
                
                if (athleteAge >= minAge && athleteAge <= maxAge) {
                    option.disabled = false;
                    option.style.color = '';
                } else {
                    option.disabled = true;
                    option.style.color = '#ccc';
                }
            }
        }
        
        function loadCompetitionCategories() {
            const ageCategoryId = document.getElementById('age_category_id').value;
            const categorySelect = document.getElementById('category_id');
            const categorySection = document.getElementById('categorySection');
            const loadingDiv = document.getElementById('categoryLoading');
            const debugDiv = document.getElementById('categoryDebug');
            
            // Only load categories if category section is visible (for tanding competitions)
            if (!categorySection.style.display || categorySection.style.display === 'none') {
                return;
            }
            
            // Reset category select
            categorySelect.innerHTML = '<option value="">Pilih Kategori Tanding</option>';
            
            if (ageCategoryId) {
                // Show loading
                loadingDiv.style.display = 'block';
                debugDiv.style.display = 'block';
                debugDiv.innerHTML = `Loading categories for competition ${COMPETITION_ID}, age category ${ageCategoryId}...`;
                
                const url = `get-competition-categories.php?competition_id=${COMPETITION_ID}&age_category_id=${ageCategoryId}`;
                console.log('Fetching categories from:', url);
                
                fetch(url)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        loadingDiv.style.display = 'none';
                        
                        if (data.success && data.data) {
                            debugDiv.innerHTML = `Found ${data.data.length} categories`;
                            
                            if (data.data.length === 0) {
                                categorySelect.innerHTML = '<option value="">Tidak ada kategori tanding untuk kategori umur ini</option>';
                            } else {
                                data.data.forEach(category => {
                                    const option = document.createElement('option');
                                    option.value = category.id;
                                    option.textContent = category.nama_kategori;
                                    
                                    // Add weight info if available
                                    if (category.berat_min || category.berat_max) {
                                        const weightInfo = [];
                                        if (category.berat_min) weightInfo.push(`Min: ${category.berat_min}kg`);
                                        if (category.berat_max) weightInfo.push(`Max: ${category.berat_max}kg`);
                                        option.textContent += ` (${weightInfo.join(', ')})`;
                                    }
                                    
                                    categorySelect.appendChild(option);
                                });
                            }
                            
                            // Hide debug after 3 seconds if successful
                            setTimeout(() => {
                                debugDiv.style.display = 'none';
                            }, 3000);
                        } else {
                            debugDiv.innerHTML = `Error: ${data.message || 'Unknown error'}`;
                            categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading categories:', error);
                        loadingDiv.style.display = 'none';
                        debugDiv.innerHTML = `Fetch error: ${error.message}`;
                        categorySelect.innerHTML = '<option value="">Error loading categories</option>';
                    });
            } else {
                debugDiv.style.display = 'none';
            }
        }
        
        function showPriceInfo() {
            const select = document.getElementById('competition_type_id');
            const option = select.options[select.selectedIndex];
            const priceInfo = document.getElementById('priceInfo');
            const priceAmount = document.getElementById('priceAmount');
            const priceDescription = document.getElementById('priceDescription');
            
            if (option.value) {
                const price = parseFloat(option.dataset.price) || 0;
                const description = option.dataset.description;
                
                priceAmount.textContent = price > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(price) : 'Gratis';
                priceDescription.textContent = description || '';
                priceInfo.style.display = 'block';
                
                // Toggle category visibility based on competition type
                toggleCategoryVisibility();
            } else {
                priceInfo.style.display = 'none';
                toggleCategoryVisibility();
            }
        }
        
        function toggleCategoryVisibility() {
            const competitionTypeSelect = document.getElementById('competition_type_id');
            const categorySection = document.getElementById('categorySection');
            const categorySelect = document.getElementById('category_id');
            
            if (competitionTypeSelect.value) {
                const selectedOption = competitionTypeSelect.options[competitionTypeSelect.selectedIndex];
                const isTanding = selectedOption.dataset.isTanding === 'true';
                
                if (isTanding) {
                    // Show category section for tanding competitions
                    categorySection.style.display = 'block';
                    
                    // Load categories if age category is selected
                    const ageCategorySelect = document.getElementById('age_category_id');
                    if (ageCategorySelect.value) {
                        loadCompetitionCategories();
                    }
                } else {
                    // Hide category section for non-tanding competitions
                    categorySection.style.display = 'none';
                    
                    // Clear category selection
                    categorySelect.innerHTML = '<option value="">Pilih Kategori Tanding</option>';
                    categorySelect.value = '';
                }
            } else {
                // Hide category section if no competition type selected
                categorySection.style.display = 'none';
            }
        }
        
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'danger' : type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> 
                ${message}
            `;
            
            // Insert at the top of main content
            const mainContent = document.querySelector('.main-content');
            mainContent.insertBefore(notification, mainContent.firstChild);
            
            // Remove after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }
        
        // Form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Pastikan field tidak disabled sebelum submit agar value terkirim
            document.getElementById('age_category_id').disabled = false;
            document.getElementById('competition_type_id').disabled = false;
            
            // Validate form
            const athleteId = document.getElementById('athlete_id').value;
            const ageCategoryId = document.getElementById('age_category_id').value;
            const competitionTypeId = document.getElementById('competition_type_id').value;
            
            if (!athleteId || !ageCategoryId || !competitionTypeId) {
                showNotification('Mohon lengkapi semua data yang diperlukan', 'error');
                return;
            }
            
            // Check if tanding competition needs category
            const competitionTypeSelect = document.getElementById('competition_type_id');
            const selectedOption = competitionTypeSelect.options[competitionTypeSelect.selectedIndex];
            const isTanding = selectedOption.dataset.isTanding === 'true';
            
            if (isTanding) {
                const categoryId = document.getElementById('category_id').value;
                if (!categoryId) {
                    showNotification('Untuk kompetisi tanding, kategori tanding harus dipilih', 'error');
                    return;
                }
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            // Show loading state
            submitBtn.disabled = true;
            submitIcon.style.display = 'none';
            loadingSpinner.style.display = 'inline-block';
            submitText.textContent = 'Mendaftarkan...';
            
            // Submit form
            this.submit();
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Disable competition type initially
            document.getElementById('competition_type_id').disabled = true;
            
            // Auto-select if only one athlete
            const athleteSelect = document.getElementById('athlete_id');
            if (athleteSelect.options.length === 2) {
                athleteSelect.selectedIndex = 1;
                showAthleteInfo();
            }
            
            // Initialize category visibility
            toggleCategoryVisibility();
            
            // Show WhatsApp info if registration was successful
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success') === '1') {
                const whatsappInfo = document.getElementById('whatsappInfo');
                if (whatsappInfo) {
                    whatsappInfo.style.display = 'block';
                    whatsappInfo.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    </script>
</body>
</html>
