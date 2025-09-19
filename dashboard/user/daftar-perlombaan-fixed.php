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
        
        // Check if athlete already registered for this competition
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE competition_id = ? AND athlete_id = ?");
        $stmt->execute([$competition_id, $athlete_id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Atlet sudah terdaftar dalam perlombaan ini.');
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
                competition_id, kontingen_id, athlete_id, category_id, 
                age_category_id, competition_type_id, payment_status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([
            $competition_id, 
            $athlete['kontingen_id'], 
            $athlete_id, 
            $category_id ?: null, 
            $age_category_id ?: null, 
            $competition_type_id
        ]);
        
        $registration_id = $pdo->lastInsertId();
        
        // Get WhatsApp group link if available
        $whatsapp_group = $competition['whatsapp_group'] ?? '';
        
        sendNotification('Pendaftaran berhasil! Silakan lakukan pembayaran dan upload bukti pembayaran.', 'success');
        
        // Redirect with WhatsApp group info
        if ($whatsapp_group) {
            $_SESSION['whatsapp_group'] = $whatsapp_group;
        }
        
        header('Location: perlombaan.php?tab=registered-athletes&success=1');
        exit();
        
    } catch (Exception $e) {
        sendNotification('Gagal mendaftarkan atlet: ' . $e->getMessage(), 'error');
    }
}

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
            transition: all 0.3s ease;
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
        }

        .form-group {
            margin-bottom: 20px;
            transition: all 0.3s ease;
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
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
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
            background: var(--primary-dark);
        }

        .btn-submit:disabled {
            background: var(--text-light);
            cursor: not-allowed;
        }

        .btn-cancel {
            background: var(--dark-color);
            color: white;
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
            text-decoration: none;
        }

        .btn-cancel:hover {
            background: #374151;
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

        .category-hidden {
            opacity: 0.5;
            pointer-events: none;
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
                <h1><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></h1>
                <p>Tanggal Pelaksanaan: <?php echo formatDate($competition['tanggal_pelaksanaan']); ?></p>
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

                <!-- Category Selection -->
                <div class="form-section">
                    <h3><i class="fas fa-list"></i> Kategori Perlombaan</h3>
                    
                    <?php if (!empty($age_categories)): ?>
                    <div class="form-group">
                        <label for="age_category_id" class="required">Kategori Umur</label>
                        <select id="age_category_id" name="age_category_id" required onchange="loadCompetitionCategories()">
                            <option value="">Pilih Kategori Umur</option>
                            <?php foreach ($age_categories as $age_cat): ?>
                                <option value="<?php echo $age_cat['id']; ?>" 
                                        data-min="<?php echo $age_cat['usia_min']; ?>"
                                        data-max="<?php echo $age_cat['usia_max']; ?>">
                                    <?php echo htmlspecialchars($age_cat['nama_kategori']); ?> (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">Pilih kategori umur sesuai dengan umur atlet</div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="category_id">Kategori Tanding</label>
                        <select id="category_id" name="category_id">
                            <option value="">Pilih Kategori Tanding</option>
                        </select>
                        <div class="form-help">Pilih kategori tanding jika tersedia (opsional)</div>
                        <div id="categoryLoading" class="loading-categories" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Memuat kategori tanding...
                        </div>
                        <div id="categoryDebug" class="debug-info" style="display: none;"></div>
                    </div>
                </div>

                <!-- Competition Type Selection -->
                <div class="form-section">
                    <h3><i class="fas fa-star"></i> Jenis Kompetisi</h3>
                    <div class="form-group">
                        <label for="competition_type_id" class="required">Jenis Kompetisi</label>
                        <select id="competition_type_id" name="competition_type_id" required onchange="showPriceInfo()">
                            <option value="">Pilih Jenis Kompetisi</option>
                            <?php foreach ($competition_types as $comp_type): ?>
                                <option value="<?php echo $comp_type['id']; ?>" 
                                        data-price="<?php echo $comp_type['biaya_pendaftaran']; ?>"
                                        data-description="<?php echo htmlspecialchars($comp_type['deskripsi']); ?>"
                                        data-is-tanding="<?php echo (stripos($comp_type['nama_kompetisi'], 'tanding') !== false) ? '1' : '0'; ?>">
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

                <!-- WhatsApp Group Info -->
                <?php if (!empty($competition['whatsapp_group'])): ?>
                <div class="whatsapp-info">
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
            const loadingDiv = document.getElementById('categoryLoading');
            const debugDiv = document.getElementById('categoryDebug');
            
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
            
            // Get category section elements
            const categorySection = document.querySelector('.form-section:has(#category_id)');
            const ageCategoryGroup = document.getElementById('age_category_id').closest('.form-group');
            const categoryGroup = document.getElementById('category_id').closest('.form-group');
            
            if (option.value) {
                const price = parseFloat(option.dataset.price) || 0;
                const description = option.dataset.description;
                const isTanding = option.dataset.isTanding === '1';
                
                // Show/hide price info
                priceAmount.textContent = price > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(price) : 'Gratis';
                priceDescription.textContent = description || '';
                priceInfo.style.display = 'block';
                
                // Show/hide category sections based on competition type
                if (isTanding) {
                    // Show category sections for tanding competitions
                    if (categorySection) categorySection.style.display = 'block';
                    if (ageCategoryGroup) ageCategoryGroup.style.display = 'block';
                    if (categoryGroup) categoryGroup.style.display = 'block';
                    
                    // Make age category required for tanding
                    document.getElementById('age_category_id').required = true;
                } else {
                    // Hide category sections for non-tanding competitions
                    if (categorySection) categorySection.style.display = 'none';
                    if (ageCategoryGroup) ageCategoryGroup.style.display = 'none';
                    if (categoryGroup) categoryGroup.style.display = 'none';
                    
                    // Remove required attribute and reset values
                    document.getElementById('age_category_id').required = false;
                    document.getElementById('age_category_id').value = '';
                    document.getElementById('category_id').value = '';
                }
            } else {
                priceInfo.style.display = 'none';
                // Show category sections by default when no competition type is selected
                if (categorySection) categorySection.style.display = 'block';
                if (ageCategoryGroup) ageCategoryGroup.style.display = 'block';
                if (categoryGroup) categoryGroup.style.display = 'block';
            }
        }
        
        // Form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitIcon = document.getElementById('submitIcon');
            const submitText = document.getElementById('submitText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            
            // Show loading state
            submitBtn.disabled = true;
            submitIcon.style.display = 'none';
            loadingSpinner.style.display = 'inline-block';
            submitText.textContent = 'Mendaftarkan...';
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-select if only one athlete
            const athleteSelect = document.getElementById('athlete_id');
            if (athleteSelect.options.length === 2) {
                athleteSelect.selectedIndex = 1;
                showAthleteInfo();
            }
        });
    </script>
</body>
</html>
