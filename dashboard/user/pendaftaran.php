<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit();
}

$competition_id = $_GET['competition_id'] ?? 0;
if (!$competition_id) {
    header('Location: perlombaan.php');
    exit();
}

// Get competition details
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND registration_status = 'open_regist'");
$stmt->execute([$competition_id]);
$competition = $stmt->fetch();

if (!$competition) {
    sendNotification('Pendaftaran untuk perlombaan ini tidak dibuka atau perlombaan tidak ditemukan.', 'error');
    header('Location: perlombaan.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $athlete_id = $_POST['athlete_id'];
    $category_id = $_POST['category_id'];
    $age_category_id = $_POST['age_category_id'];
    $competition_type_id = $_POST['competition_type_id'];
    
    // Get kontingen_id from athlete
    $stmt = $pdo->prepare("SELECT kontingen_id FROM athletes WHERE id = ? AND user_id = ?");
    $stmt->execute([$athlete_id, $_SESSION['user_id']]);
    $athlete = $stmt->fetch();

    if (!$athlete) {
        sendNotification('Atlet tidak valid.', 'error');
    } else {
        try {
            // Check for duplicate registration
            $stmt = $pdo->prepare("SELECT id FROM registrations WHERE athlete_id = ? AND competition_id = ? AND category_id = ? AND age_category_id = ? AND competition_type_id = ?");
            $stmt->execute([$athlete_id, $competition_id, $category_id, $age_category_id, $competition_type_id]);
            if ($stmt->fetch()) {
                sendNotification('Atlet ini sudah terdaftar di kategori yang sama.', 'warning');
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO registrations (athlete_id, competition_id, kontingen_id, category_id, age_category_id, competition_type_id, payment_status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
                ");
                $stmt->execute([$athlete_id, $competition_id, $athlete['kontingen_id'], $category_id, $age_category_id, $competition_type_id]);
                sendNotification('Atlet berhasil didaftarkan! Silakan lakukan pembayaran.', 'success');
                header('Location: index.php'); // Redirect to dashboard to see registration status
                exit();
            }
        } catch (PDOException $e) {
            sendNotification('Gagal mendaftarkan atlet: ' . $e->getMessage(), 'error');
        }
    }
    header("Location: pendaftaran.php?competition_id=$competition_id");
    exit();
}

// Get user's athletes
$athletes = $pdo->prepare("SELECT * FROM athletes WHERE user_id = ?");
$athletes->execute([$_SESSION['user_id']]);
$athletes = $athletes->fetchAll();

// Get competition categories
$categories = $pdo->prepare("SELECT * FROM competition_categories WHERE competition_id = ?");
$categories->execute([$competition_id]);
$categories = $categories->fetchAll();

$age_categories = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ?");
$age_categories->execute([$competition_id]);
$age_categories = $age_categories->fetchAll();

$competition_types = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ?");
$competition_types->execute([$competition_id]);
$competition_types = $competition_types->fetchAll();

$notification = getNotification();
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
    <?php if ($notification): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert('<?php echo $notification['message']; ?>', '<?php echo $notification['type']; ?>');
        });
    </script>
    <?php endif; ?>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- ... (sama seperti file perlombaan.php) ... -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Formulir Pendaftaran</h1>
            <p class="page-subtitle">Daftarkan atlet Anda untuk: <?php echo htmlspecialchars($competition['nama_perlombaan']); ?></p>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Pilih Atlet dan Kategori</h2>
            </div>
            <form method="POST" style="padding: 30px;">
                <?php if (empty($athletes)): ?>
                    <div class="alert alert-warning">
                        Anda belum memiliki data atlet. Silakan <a href="data-atlet.php">tambahkan atlet</a> terlebih dahulu.
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="athlete_id">Pilih Atlet <span class="required">*</span></label>
                        <select id="athlete_id" name="athlete_id" required>
                            <option value="">-- Pilih Atlet --</option>
                            <?php foreach ($athletes as $athlete): ?>
                                <option value="<?php echo $athlete['id']; ?>">
                                    <?php echo htmlspecialchars($athlete['nama']); ?> (<?php echo htmlspecialchars($athlete['nik']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Kategori Tanding <span class="required">*</span></label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Pilih Kategori Tanding --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nama_kategori']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="age_category_id">Kategori Umur <span class="required">*</span></label>
                        <select id="age_category_id" name="age_category_id" required>
                            <option value="">-- Pilih Kategori Umur --</option>
                            <?php foreach ($age_categories as $age_cat): ?>
                                <option value="<?php echo $age_cat['id']; ?>">
                                    <?php echo htmlspecialchars($age_cat['nama_kategori']); ?> (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="competition_type_id">Jenis Kompetisi <span class="required">*</span></label>
                        <select id="competition_type_id" name="competition_type_id" required>
                            <option value="">-- Pilih Jenis Kompetisi --</option>
                            <?php foreach ($competition_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars($type['nama_kompetisi']); ?> 
                                    (<?php echo $type['biaya_pendaftaran'] > 0 ? 'Rp ' . number_format($type['biaya_pendaftaran']) : 'Gratis'; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-top: 30px; display: flex; gap: 10px;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Daftarkan Atlet
                        </button>
                        <a href="perlombaan.php" class="btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <style>
        .required { color: red; }
    </style>
</body>
</html>
