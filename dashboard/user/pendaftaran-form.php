<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
header('Location: ../../index.php');
exit();
}

$competition_id = $_GET['competition_id'] ?? 0;

// Get competition details
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND status = 'active'");
$stmt->execute([$competition_id]);
$competition = $stmt->fetch();

if (!$competition) {
header('Location: perlombaan.php');
exit();
}

// Get user's athletes
$stmt = $pdo->prepare("SELECT * FROM athletes WHERE user_id = ? ORDER BY nama ASC");
$stmt->execute([$_SESSION['user_id']]);
$athletes = $stmt->fetchAll();

// Get age categories
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$age_categories = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$competition_id]);
$competition_types = $stmt->fetchAll();

// Get user's existing registrations for this competition
$stmt = $pdo->prepare("
SELECT r.*, a.nama as athlete_name, cc.nama_kategori as category_name, 
       ac.nama_kategori as age_category_name, ct.nama_kompetisi, ct.biaya_pendaftaran,
       k.nama_kontingen
FROM registrations r 
JOIN athletes a ON r.athlete_id = a.id 
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON a.kontingen_id = k.id
LEFT JOIN competition_categories cc ON r.category_id = cc.id
LEFT JOIN age_categories ac ON r.age_category_id = ac.id
LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
WHERE r.competition_id = ? AND a.user_id = ?
ORDER BY r.created_at DESC
");
$stmt->execute([$competition_id, $_SESSION['user_id']]);
$user_registrations = $stmt->fetchAll();

// Process form submission
if ($_POST && isset($_POST['selected_athletes'])) {
try {
    $pdo->beginTransaction();
    
    $selected_athletes = $_POST['selected_athletes'];
    $whatsapp_group_link = $competition['whatsapp_group'] ?? '';
    
    foreach ($selected_athletes as $athlete_id) {
        // Get athlete's kontingen
        $stmt = $pdo->prepare("SELECT kontingen_id FROM athletes WHERE id = ? AND user_id = ?");
        $stmt->execute([$athlete_id, $_SESSION['user_id']]);
        $athlete = $stmt->fetch();
        
        if ($athlete) {
            // Determine category_id based on competition type
            $category_id = null;
            $competition_type_id = $_POST['competition_type_' . $athlete_id] ?? null;
            
            if ($competition_type_id) {
                // Get competition type details
                $stmt = $pdo->prepare("SELECT nama_kompetisi FROM competition_types WHERE id = ?");
                $stmt->execute([$competition_type_id]);
                $comp_type = $stmt->fetch();
                
                // Only set category_id for "tanding" competitions
                if ($comp_type && (stripos($comp_type['nama_kompetisi'], 'tanding') !== false)) {
                    $category_id = $_POST['category_' . $athlete_id] ?? null;
                }
            }
            
            // Insert registration
            $stmt = $pdo->prepare("
                INSERT INTO registrations (competition_id, kontingen_id, athlete_id, category_id, age_category_id, competition_type_id, payment_status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $competition_id,
                $athlete['kontingen_id'],
                $athlete_id,
                $category_id, // This will be NULL for non-tanding competitions
                $_POST['age_category_' . $athlete_id] ?? null,
                $competition_type_id
            ]);
        }
    }
    
    $pdo->commit();
    
    // Show success with WhatsApp group link
    $success_message = "Pendaftaran berhasil! Silakan bergabung dengan grup WhatsApp.";
    $show_whatsapp_popup = true;
    
    // Refresh registrations data
    $stmt = $pdo->prepare("
        SELECT r.*, a.nama as athlete_name, cc.nama_kategori as category_name, 
               ac.nama_kategori as age_category_name, ct.nama_kompetisi, ct.biaya_pendaftaran,
               k.nama_kontingen
        FROM registrations r 
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON a.kontingen_id = k.id
        LEFT JOIN competition_categories cc ON r.category_id = cc.id
        LEFT JOIN age_categories ac ON r.age_category_id = ac.id
        LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
        WHERE r.competition_id = ? AND a.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$competition_id, $_SESSION['user_id']]);
    $user_registrations = $stmt->fetchAll();
    
} catch (Exception $e) {
    $pdo->rollback();
    $error_message = "Terjadi kesalahan: " . $e->getMessage();
}
}

// Helper function to calculate age
function calculateAthleteAge($birthDate) {
$today = new DateTime();
$birth = new DateTime($birthDate);
return $today->diff($birth)->y;
}

// Helper function to format currency
function formatRupiahForm($number) {
return 'Rp ' . number_format($number, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pendaftaran - <?php echo htmlspecialchars($competition['nama_perlombaan']); ?></title>
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
        <h1 class="page-title">Pendaftaran Atlet</h1>
        <p class="page-subtitle"><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></p>
        <div class="page-actions">
            <a href="perlombaan-detail.php?id=<?php echo $competition_id; ?>" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <div class="content-container">
        <?php if (empty($athletes)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-user-ninja"></i>
                </div>
                <h3>Belum Ada Data Atlet</h3>
                <p>Anda belum memiliki data atlet. Silakan tambahkan data atlet terlebih dahulu.</p>
                <div class="empty-actions">
                    <a href="data-atlet.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Tambah Atlet
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" class="registration-form">
                <div class="form-section">
                    <h3><i class="fas fa-users"></i> Pilih Atlet untuk Didaftarkan</h3>
                    <p class="section-description">Pilih atlet yang akan didaftarkan pada perlombaan ini dan tentukan kategori untuk setiap atlet.</p>
                    
                    <div class="athletes-grid">
                        <?php foreach ($athletes as $athlete): ?>
                            <?php
                            // Check if athlete is already registered
                            $already_registered = false;
                            foreach ($user_registrations as $reg) {
                                if ($reg['athlete_id'] == $athlete['id']) {
                                    $already_registered = true;
                                    break;
                                }
                            }
                            ?>
                            
                            <div class="athlete-card <?php echo $already_registered ? 'already-registered' : ''; ?>">
                                <div class="athlete-header">
                                    <div class="athlete-checkbox">
                                        <?php if (!$already_registered): ?>
                                            <input type="checkbox" id="athlete_<?php echo $athlete['id']; ?>" 
                                                   name="selected_athletes[]" value="<?php echo $athlete['id']; ?>"
                                                   onchange="toggleAthleteOptions(<?php echo $athlete['id']; ?>)">
                                            <label for="athlete_<?php echo $athlete['id']; ?>">
                                                <strong><?php echo htmlspecialchars($athlete['nama']); ?></strong>
                                            </label>
                                        <?php else: ?>
                                            <div class="registered-badge">
                                                <i class="fas fa-check-circle"></i>
                                                <strong><?php echo htmlspecialchars($athlete['nama']); ?></strong>
                                                <span class="registered-text">Sudah Terdaftar</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="athlete-info">
                                    <div class="info-item">
                                        <span class="info-label">Jenis Kelamin:</span>
                                        <span><?php echo $athlete['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Umur:</span>
                                        <span><?php echo calculateAthleteAge($athlete['tanggal_lahir']); ?> tahun</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Berat Badan:</span>
                                        <span><?php echo $athlete['berat_badan']; ?> kg</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Tinggi Badan:</span>
                                        <span><?php echo $athlete['tinggi_badan']; ?> cm</span>
                                    </div>
                                </div>
                                
                                <?php if (!$already_registered): ?>
                                    <div class="athlete-options" id="options_<?php echo $athlete['id']; ?>" style="display: none;">
                                        <div class="option-group">
                                            <label>Kategori Umur:</label>
                                            <select name="age_category_<?php echo $athlete['id']; ?>" class="form-select" 
                                                    onchange="loadCompetitionCategories(<?php echo $athlete['id']; ?>)">
                                                <option value="">Pilih Kategori Umur</option>
                                                <?php foreach ($age_categories as $age_cat): ?>
                                                    <option value="<?php echo $age_cat['id']; ?>">
                                                        <?php echo htmlspecialchars($age_cat['nama_kategori']); ?>
                                                        (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="option-group">
                                            <label>Jenis Kompetisi:</label>
                                            <select name="competition_type_<?php echo $athlete['id']; ?>" class="form-select" 
                                                    onchange="toggleCategorySelection(<?php echo $athlete['id']; ?>)">
                                                <option value="">Pilih Jenis Kompetisi</option>
                                                <?php foreach ($competition_types as $comp_type): ?>
                                                    <option value="<?php echo $comp_type['id']; ?>" data-type="<?php echo strtolower($comp_type['nama_kompetisi']); ?>">
                                                        <?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?>
                                                        <?php if ($comp_type['biaya_pendaftaran']): ?>
                                                            - <?php echo formatRupiahForm($comp_type['biaya_pendaftaran']); ?>
                                                        <?php else: ?>
                                                            - Gratis
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="option-group" id="category_group_<?php echo $athlete['id']; ?>" style="display: none;">
                                            <label>Kategori Tanding:</label>
                                            <select name="category_<?php echo $athlete['id']; ?>" class="form-select" id="category_<?php echo $athlete['id']; ?>">
                                                <option value="">Pilih Kategori Tanding</option>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-paper-plane"></i> Submit Data Atlet
                    </button>
                    <a href="perlombaan-detail.php?id=<?php echo $competition_id; ?>" class="btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- WhatsApp Group Modal -->
<?php if (isset($show_whatsapp_popup) && $show_whatsapp_popup && !empty($competition['whatsapp_group'])): ?>
<div id="whatsappModal" class="modal" style="display: block;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fab fa-whatsapp"></i> Bergabung dengan Grup WhatsApp</h3>
        </div>
        <div class="modal-body">
            <div class="whatsapp-info">
                <i class="fab fa-whatsapp whatsapp-icon"></i>
                <h4>Pendaftaran Berhasil!</h4>
                <p>Silakan bergabung dengan grup WhatsApp untuk mendapatkan informasi terbaru tentang perlombaan.</p>
                
                <div class="whatsapp-actions">
                    <a href="<?php echo htmlspecialchars($competition['whatsapp_group']); ?>" target="_blank" class="btn-whatsapp">
                        <i class="fab fa-whatsapp"></i> Join Grup WhatsApp
                    </a>
                    <button onclick="closeWhatsAppModal()" class="btn-secondary">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="../../assets/js/script.js"></script>
<script>
    function toggleAthleteOptions(athleteId) {
        const checkbox = document.getElementById('athlete_' + athleteId);
        const options = document.getElementById('options_' + athleteId);
        const submitBtn = document.getElementById('submitBtn');
        
        if (checkbox.checked) {
            options.style.display = 'block';
        } else {
            options.style.display = 'none';
        }
        
        // Check if any athlete is selected
        const selectedAthletes = document.querySelectorAll('input[name="selected_athletes[]"]:checked');
        submitBtn.disabled = selectedAthletes.length === 0;
    }

    function toggleCategorySelection(athleteId) {
        const competitionTypeSelect = document.querySelector(`select[name="competition_type_${athleteId}"]`);
        const categoryGroup = document.getElementById(`category_group_${athleteId}`);
        const selectedOption = competitionTypeSelect.options[competitionTypeSelect.selectedIndex];
        
        if (selectedOption && selectedOption.dataset.type) {
            const competitionType = selectedOption.dataset.type;
            
            // Show category selection only for "tanding" (fighting) competitions
            if (competitionType.includes('tanding') || competitionType.includes('fighting')) {
                categoryGroup.style.display = 'block';
            } else {
                categoryGroup.style.display = 'none';
                // Clear category selection
                document.getElementById(`category_${athleteId}`).value = '';
            }
        } else {
            categoryGroup.style.display = 'none';
        }
    }

    function loadCompetitionCategories(athleteId) {
        const ageCategorySelect = document.querySelector(`select[name="age_category_${athleteId}"]`);
        const categorySelect = document.getElementById(`category_${athleteId}`);
        const ageCategoryId = ageCategorySelect.value;
        
        // Clear category options
        categorySelect.innerHTML = '<option value="">Pilih Kategori Tanding</option>';
        
        if (ageCategoryId) {
            // Fetch competition categories based on age category
            fetch(`get-competition-categories.php?competition_id=<?php echo $competition_id; ?>&age_category_id=${ageCategoryId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        
                        let text = category.nama_kategori;
                        if (category.berat_min || category.berat_max) {
                            text += ` (${category.berat_min}-${category.berat_max} kg)`;
                        }
                        
                        option.textContent = text;
                        categorySelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                });
        }
    }

    function closeWhatsAppModal() {
        document.getElementById('whatsappModal').style.display = 'none';
        // Redirect to competition detail page
        window.location.href = 'perlombaan-detail.php?id=<?php echo $competition_id; ?>';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('whatsappModal');
        if (event.target == modal) {
            closeWhatsAppModal();
        }
    }
</script>

<style>
    /* Content Container */
    .content-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 30px;
        padding: 30px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--primary-color);
        margin-bottom: 20px;
        opacity: 0.7;
    }

    .empty-state h3 {
        color: var(--primary-color);
        margin: 0 0 10px 0;
        font-size: 1.5rem;
    }

    .empty-state p {
        color: var(--text-light);
        margin: 0 0 30px 0;
        font-size: 1rem;
    }

    .empty-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    /* Registration Form Styles */
    .registration-form {
        max-width: 1200px;
        margin: 0 auto;
    }

    .form-section {
        background: white;
        padding: 30px;
        margin-bottom: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .form-section h3 {
        color: var(--primary-color);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-description {
        color: var(--text-light);
        margin-bottom: 20px;
        line-height: 1.5;
    }

    .athletes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
    }

    .athlete-card {
        border: 2px solid var(--border-color);
        border-radius: 8px;
        padding: 20px;
        transition: all 0.3s;
    }

    .athlete-card:has(input:checked) {
        border-color: var(--primary-color);
        background: #f8fafc;
    }

    .athlete-card.already-registered {
        border-color: #28a745;
        background: #f8fff9;
        opacity: 0.8;
    }

    .athlete-header {
        margin-bottom: 15px;
    }

    .athlete-checkbox {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .athlete-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--primary-color);
    }

    .athlete-checkbox label {
        cursor: pointer;
        font-size: 1.1rem;
        color: var(--primary-color);
    }

    .registered-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #28a745;
    }

    .registered-badge strong {
        font-size: 1.1rem;
    }

    .registered-text {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .athlete-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 15px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .info-label {
        font-size: 0.8rem;
        color: var(--text-light);
        font-weight: 600;
    }

    .athlete-options {
        border-top: 1px solid var(--border-color);
        padding-top: 15px;
    }

    .option-group {
        margin-bottom: 15px;
    }

    .option-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: var(--text-color);
        font-size: 0.9rem;
    }

    .form-select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: white;
        font-size: 0.9rem;
    }

    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(var(--primary-color-rgb), 0.2);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        padding: 30px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        border-radius: 12px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    }

    .modal-header {
        padding: 20px;
        border-bottom: 1px solid var(--border-color);
        background: var(--primary-color);
        color: white;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
    }

    .modal-body {
        padding: 30px;
    }

    .whatsapp-info {
        text-align: center;
    }

    .whatsapp-icon {
        font-size: 4rem;
        color: #25d366;
        margin-bottom: 15px;
    }

    .whatsapp-info h4 {
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .whatsapp-info p {
        color: var(--text-light);
        margin-bottom: 25px;
        line-height: 1.5;
    }

    .whatsapp-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-whatsapp {
        background: #25d366;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.3s;
        text-decoration: none;
    }

    .btn-whatsapp:hover {
        background: #128c7e;
    }

    @media (max-width: 768px) {
        .athletes-grid {
            grid-template-columns: 1fr;
        }
        
        .athlete-info {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }

        .modal-content {
            width: 95%;
            margin: 20% auto;
        }

        .whatsapp-actions {
            flex-direction: column;
        }
    }
</style>
</body>
</html>
