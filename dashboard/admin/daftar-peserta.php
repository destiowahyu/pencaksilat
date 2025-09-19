<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use App\ExcelHelper;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$competition_id = $_GET['competition_id'] ?? 0;

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

// Get age categories
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$age_categories = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$competition_id]);
$competition_types = $stmt->fetchAll();

// Get competition categories
$stmt = $pdo->prepare("SELECT * FROM competition_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$competition_categories = $stmt->fetchAll();

// Tambah ambil parameter search
$search_nama = $_GET['search_nama'] ?? '';
$search_kontingen = $_GET['search_kontingen'] ?? '';

// Ambil parameter filter dari GET
$filter_age_category = $_GET['age_category'] ?? '';
$filter_competition_type = $_GET['competition_type'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_gender = $_GET['gender'] ?? '';

// Ambil semua kategori tanding unik dari peserta_pertandingan
if ($filter_competition_type && stripos($filter_competition_type, 'tanding') !== false) {
    $stmt = $pdo->prepare("SELECT DISTINCT kategori_tanding FROM daftar_peserta WHERE jenis_kompetisi = ? ORDER BY kategori_tanding");
    $stmt->execute([$filter_competition_type]);
    $kategori_tanding_options = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $kategori_tanding_enabled = true;
} else {
    $kategori_tanding_options = [];
    $kategori_tanding_enabled = false;
    $filter_category = '';
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'import_excel':
            if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['excel_file'];
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                // Hanya izinkan file .xls, .xlsx, .csv
                if (!in_array($file_extension, ['xls', 'xlsx', 'csv'])) {
                    $error_message = "File harus berformat Excel (.xls, .xlsx) atau CSV (.csv)";
                } else {
                    try {
                        // Create upload directory if not exists
                        $uploadDir = '../../uploads/temp/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }
                        // Move uploaded file
                        $tempFile = $uploadDir . 'import_' . time() . '.' . $file_extension;
                        if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
                            throw new Exception('Failed to move uploaded file');
                        }
                        // Read file sesuai format
                        $excelData = [];
                        $headers = [];
                        if ($file_extension === 'csv') {
                            // Baca CSV
                            if (($handle = fopen($tempFile, 'r')) !== false) {
                                while (($row = fgetcsv($handle)) !== false) {
                                    if (empty(array_filter($row))) continue;
                                    if (empty($headers)) {
                                        $headers = $row;
                                    } else {
                                        $row = array_slice($row, 0, 11);
                                        $excelData[] = $row;
                                    }
                                }
                                fclose($handle);
                            } else {
                                throw new Exception('Gagal membaca file CSV.');
                            }
                        } else {
                            // Baca xls/xlsx pakai PhpSpreadsheet
                            try {
                                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempFile);
                                $sheet = $spreadsheet->getActiveSheet();
                                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                                    $rowData = [];
                                    foreach ($row->getCellIterator() as $cell) {
                                        $rowData[] = $cell->getValue();
                                    }
                                    if ($rowIndex == 1) {
                                        $headers = $rowData;
                                    } else {
                                        $row = array_slice($rowData, 0, 11);
                                        $excelData[] = $row;
                                    }
                                }
                            } catch (\Exception $e) {
                                throw new Exception('Gagal membaca file Excel. Pastikan file valid dan tidak corrupt. Pesan error: ' . $e->getMessage());
                            }
                        }
                        
                        // Validate headers - more flexible approach
                        $expectedHeaders = [
                            'Nama',
                            'Jenis Kelamin', 
                            'Tanggal Lahir',
                            'Tempat Lahir',
                            'Nama Sekolah',
                            'Berat Badan',
                            'Tinggi Badan',
                            'Kontingen',
                            'Kategori Umur',
                            'Jenis Kompetisi',
                            'Kategori Tanding'
                        ];
                        
                        // Clean headers (remove BOM, spaces, etc.)
                        $cleanedHeaders = array_map(function($header) {
                            if ($header === null) return '';
                            return trim(str_replace(["\xEF\xBB\xBF", "\xFE\xFF", "\xFF\xFE"], '', $header));
                        }, $headers);
                        
                        // More flexible validation - check if we have at least the minimum required columns
                        if (count($cleanedHeaders) < 8) {
                            throw new Exception('File tidak sesuai format template. Ditemukan ' . count($cleanedHeaders) . ' kolom, minimal 8 kolom diperlukan. Pastikan menggunakan template yang benar.');
                        }
                        
                        // Update headers to cleaned version
                        $headers = $cleanedHeaders;
                        
                        $successCount = 0;
                        $errorCount = 0;
                        $errors = [];
                        
                        // Begin transaction
                        $pdo->beginTransaction();
                        
                        try {
                            foreach ($excelData as $rowIndex => $row) {
                                // Skip empty rows
                                if (empty(array_filter($row))) {
                                    continue;
                                }
                                
                                if (count($row) < 11) {
                                    $errors[] = "Baris " . ($rowIndex + 2) . ": Jumlah kolom kurang dari 11, pastikan format file sesuai template (tanpa kolom No).";
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Validasi kolom wajib (sudah ada di kode Anda)
                                if (
                                    empty($row[0]) || // nama
                                    empty($row[1]) || // jenis kelamin
                                    empty($row[2]) || // tanggal lahir
                                    empty($row[3]) || // tempat lahir
                                    empty($row[4]) || // nama sekolah
                                    empty($row[5]) || // berat badan
                                    empty($row[6]) || // tinggi badan
                                    empty($row[7]) || // kontingen
                                    empty($row[8]) || // kategori umur
                                    empty($row[9]) || // jenis kompetisi
                                    empty($row[10])   // kategori tanding
                                ) {
                                    $errors[] = "Baris " . ($rowIndex + 2) . ": Semua kolom wajib diisi";
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Validasi jenis kelamin
                                $gender = strtoupper(trim($row[1]));
                                if (!in_array($gender, ['L', 'P'])) {
                                    $errors[] = "Baris " . ($rowIndex + 2) . ": Jenis kelamin harus L atau P";
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Validasi tanggal lahir
                                $tanggal_lahir = excelDateToYMD($row[2]);
                                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_lahir)) {
                                    $errors[] = "Baris " . ($rowIndex + 2) . ": Format tanggal harus YYYY-MM-DD";
                                    $errorCount++;
                                    continue;
                                }
                                
                                // Insert ke peserta_pertandingan
                                $stmt = $pdo->prepare("
                                    INSERT INTO daftar_peserta
                                    (nama, jenis_kelamin, tanggal_lahir, tempat_lahir, nama_sekolah, berat_badan, tinggi_badan, kontingen, kategori_umur, jenis_kompetisi, kategori_tanding)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    trim($row[0]),
                                    $gender,
                                    $tanggal_lahir,
                                    trim($row[3]),
                                    trim($row[4]),
                                    floatval($row[5]),
                                    floatval($row[6]),
                                    trim($row[7]),
                                    trim($row[8]),
                                    trim($row[9]),
                                    trim($row[10])
                                ]);
                                $successCount++;
                            }
                            
                            // Commit transaction
                            $pdo->commit();
                            
                            // Clean up temp file
                            unlink($tempFile);
                            
                            $_SESSION['success_message'] = "Berhasil mengimpor $successCount data peserta";
                            header("Location: daftar-peserta.php?competition_id=$competition_id");
                            exit();
                            
                        } catch (Exception $e) {
                            // Rollback transaction
                            $pdo->rollBack();
                            throw $e;
                        }
                        
                    } catch (Exception $e) {
                        // Clean up temp file if exists
                        if (isset($tempFile) && file_exists($tempFile)) {
                            unlink($tempFile);
                        }
                        $error_message = $e->getMessage() . "<br><small>Pastikan file berformat Excel (.xlsx/.xls), CSV (.csv), atau Spreadsheet (.ods/.tsv) yang valid</small>";
                    }
                }
            } else {
                $error_message = "Pilih file Excel terlebih dahulu";
            }
            break;
            
        case 'update_participant':
            $participant_id = $_POST['participant_id'];
            $nama = $_POST['nama'];
            $jenis_kelamin = $_POST['jenis_kelamin'];
            $tanggal_lahir = $_POST['tanggal_lahir'];
            $tempat_lahir = $_POST['tempat_lahir'];
            $nama_sekolah = $_POST['nama_sekolah'];
            $berat_badan = $_POST['berat_badan'];
            $tinggi_badan = $_POST['tinggi_badan'];
            $age_category_id = $_POST['age_category_id'];
            $competition_type_id = $_POST['competition_type_id'];
            $category_id = $_POST['category_id'];
            
            try {
                // Update athlete data
                $stmt = $pdo->prepare("
                    UPDATE athletes a 
                    JOIN registrations r ON a.id = r.athlete_id 
                    SET a.nama = ?, a.jenis_kelamin = ?, a.tanggal_lahir = ?, 
                        a.tempat_lahir = ?, a.nama_sekolah = ?, a.berat_badan = ?, a.tinggi_badan = ?
                    WHERE r.id = ? AND r.competition_id = ?
                ");
                $stmt->execute([
                    $nama, $jenis_kelamin, $tanggal_lahir, $tempat_lahir,
                    $nama_sekolah, $berat_badan, $tinggi_badan, $participant_id, $competition_id
                ]);
                
                // Update registration data
                $stmt = $pdo->prepare("
                    UPDATE registrations 
                    SET age_category_id = ?, competition_type_id = ?, category_id = ?, updated_at = NOW()
                    WHERE id = ? AND competition_id = ?
                ");
                $stmt->execute([
                    $age_category_id, $competition_type_id, $category_id, $participant_id, $competition_id
                ]);
                
                $success_message = "Data peserta berhasil diperbarui";
            } catch (Exception $e) {
                $error_message = "Gagal memperbarui data: " . $e->getMessage();
            }
            break;
            
        case 'delete_participant':
            $participant_id = $_POST['participant_id'];
            
            try {
                // Delete registration (this will remove the participant from this competition)
                $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ? AND competition_id = ?");
                $stmt->execute([$participant_id, $competition_id]);
                $success_message = "Data peserta berhasil dihapus dari perlombaan ini";
            } catch (Exception $e) {
                $error_message = "Gagal menghapus data: " . $e->getMessage();
            }
            break;
    }
}

// Build query for participants

// Query sederhana langsung dari daftar_peserta
$where = [];
$params = [];
if ($filter_age_category) {
    $where[] = "kategori_umur = ?";
    $params[] = $filter_age_category;
}
if ($filter_competition_type) {
    $where[] = "jenis_kompetisi = ?";
    $params[] = $filter_competition_type;
}
if ($filter_category) {
    $where[] = "kategori_tanding = ?";
    $params[] = $filter_category;
}
if ($filter_gender) {
    $where[] = "jenis_kelamin = ?";
    $params[] = $filter_gender;
}
if ($search_nama) {
    $where[] = "nama LIKE ?";
    $params[] = "%$search_nama%";
}
if ($search_kontingen) {
    $where[] = "kontingen LIKE ?";
    $params[] = "%$search_kontingen%";
}

$sql = "SELECT id AS registration_id, nama, jenis_kelamin, tanggal_lahir, tempat_lahir, nama_sekolah, berat_badan, tinggi_badan, kontingen, kategori_umur, jenis_kompetisi, kategori_tanding FROM daftar_peserta";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$participants = $stmt->fetchAll();

// Helper function to calculate age
function calculateAge($birthDate) {
    if (!$birthDate) return '-';
    $birth = new DateTime($birthDate);
    $today = new DateTime();
    $age = $today->diff($birth);
    return $age->y;
}

// Get statistics
$stats = [
    'total_participants' => count($participants),
    'male_participants' => 0,
    'female_participants' => 0
];

foreach ($participants as $participant) {
    if ($participant['jenis_kelamin'] == 'L') {
        $stats['male_participants']++;
    } else if ($participant['jenis_kelamin'] == 'P') {
        $stats['female_participants']++;
    }
}

// Helper: Konversi serial Excel ke tanggal
function excelDateToYMD($excelDate) {
    if (is_numeric($excelDate)) {
        $unixDate = ($excelDate - 25569) * 86400;
        return gmdate('Y-m-d', $unixDate);
    }
    return $excelDate;
}

$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Daftar Peserta</h1>
                <p class="page-subtitle"><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></p>
            </div>
            <div class="page-actions">
                <a href="perlombaan-detail.php?id=<?php echo $competition_id; ?>" class="btn btn-secondary">
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
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['total_participants']; ?></h3>
                    <p>Total Peserta</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon blue">
                    <i class="fas fa-male"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['male_participants']; ?></h3>
                    <p>Peserta Laki-laki</p>
                </div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-icon pink">
                    <i class="fas fa-female"></i>
                </div>
                <div class="dashboard-card-content">
                    <h3><?php echo $stats['female_participants']; ?></h3>
                    <p>Peserta Perempuan</p>
                </div>
            </div>
        </div>
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Import Data Peserta</h2>
                <div class="table-actions">
                    <a href="download-template-csv.php?id=<?php echo $competition_id; ?>" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download Template CSV (Direkomendasikan)
                    </a>
                    <a href="download-template.php?competition_id=<?php echo $competition_id; ?>&format=xlsx" class="btn btn-primary" style="margin-left:8px;">
                        <i class="fas fa-download"></i> Download Template Excel (.xlsx)
                    </a>
                </div>
            </div>
            <div style="margin-top: 10px; margin-bottom: 15px;">
                <small style="color: #1976d2; font-weight: 500;">Gunakan template berikut untuk mengisi data peserta secara massal. CSV bisa dibuka di Excel, Google Sheets, atau Notepad. Pastikan format kolom tidak diubah.</small>
            </div>
            <div style="padding: 20px; background: white; border-radius: 8px; margin-bottom: 20px;">
                <div style="background: #e3f2fd; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #2196f3;">
                    <h4 style="margin: 0 0 10px 0; color: #1976d2;"><i class="fas fa-info-circle"></i> Cara Import Data (Direkomendasikan)</h4>
                    <ol style="margin: 0; padding-left: 20px; color: #1565c0;">
                        <li>Download <strong>Template CSV</strong> (tombol biru di atas)</li>
                        <li>Buka file CSV dengan <strong>Notepad</strong> atau <strong>Excel</strong></li>
                        <li>Isi data sesuai format yang ada di template</li>
                        <li><strong>Save</strong> file (format CSV)</li>
                        <li>Upload file CSV ke sistem</li>
                    </ol>
                </div>
                <form method="POST" enctype="multipart/form-data" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                    <input type="hidden" name="action" value="import_excel">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label for="excel_file">File Excel, CSV, atau Spreadsheet (.xlsx/.xls/.csv)</label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv,.ods,.tsv" required>
                        <small>Format: Nama, Jenis Kelamin (L/P), Tanggal Lahir (YYYY-MM-DD), Tempat Lahir, Sekolah, Berat Badan, Tinggi Badan, Kontingen, Kategori Umur, Jenis Kompetisi, Kategori Tanding</small>
                        <br><small style="color: #28a745;"><strong>✅ Universal Support:</strong> Mendukung file .xlsx, .xls, .csv, .ods, .tsv - Gunakan format apapun!</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import Data
                    </button>
                </form>
            </div>
        </div>
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Filter & Search Data Peserta</h2>
                <div class="table-actions">
                    <a href="export-participants.php?competition_id=<?php echo $competition_id; ?>&age_category=<?php echo $filter_age_category; ?>&competition_type=<?php echo $filter_competition_type; ?>&category=<?php echo $filter_category; ?>&gender=<?php echo $filter_gender; ?>&search_nama=<?php echo urlencode($search_nama); ?>&search_kontingen=<?php echo urlencode($search_kontingen); ?>" class="btn btn-primary">
                        <i class="fas fa-download"></i> Export Excel
                    </a>
                    <button type="button" class="btn btn-primary" onclick="saveFilteredParticipants()">
                        <i class="fas fa-save"></i> Simpan Hasil Filter
                    </button>
                </div>
            </div>
            <div style="padding: 20px; background: white; border-radius: 8px; margin-bottom: 20px;">
                <form method="GET" style="margin-bottom: 0; display: flex; flex-wrap: wrap; gap: 15px; align-items: end;">
                    <input type="hidden" name="competition_id" value="<?php echo $competition_id; ?>">
                    <div class="form-group">
                        <label for="search_nama">Cari Nama Peserta</label>
                        <input type="text" id="search_nama" name="search_nama" value="<?php echo htmlspecialchars($search_nama); ?>" placeholder="Nama peserta...">
                    </div>
                    <div class="form-group">
                        <label for="search_kontingen">Cari Kontingen</label>
                        <input type="text" id="search_kontingen" name="search_kontingen" value="<?php echo htmlspecialchars($search_kontingen); ?>" placeholder="Nama kontingen...">
                    </div>
                        <div class="form-group">
                            <label for="age_category">Kategori Umur</label>
                            <select id="age_category" name="age_category" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Kategori Umur</option>
                                <?php foreach ($age_categories as $age_cat): ?>
                                <option value="<?php echo htmlspecialchars($age_cat['nama_kategori']); ?>" <?php echo $filter_age_category == $age_cat['nama_kategori'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($age_cat['nama_kategori']); ?> (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="gender">Jenis Kelamin</label>
                            <select id="gender" name="gender" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Jenis Kelamin</option>
                                <option value="L" <?php echo $filter_gender == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="P" <?php echo $filter_gender == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="competition_type">Jenis Kompetisi</label>
                        <select id="competition_type" name="competition_type" class="form-control" onchange="this.form.submit()">
                                <option value="">Semua Jenis Kompetisi</option>
                                <?php foreach ($competition_types as $comp_type): ?>
                                <option value="<?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?>" <?php echo $filter_competition_type == $comp_type['nama_kompetisi'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <div class="form-group">
                            <label for="category">Kategori Tanding</label>
                            <select id="category" name="category" class="form-control" onchange="this.form.submit()" <?php echo !$kategori_tanding_enabled ? 'disabled style=\'background:#f3f3f3;cursor:not-allowed;\'' : ''; ?>>
                                <option value="">Semua Kategori Tanding</option>
                            <?php foreach ($kategori_tanding_options as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($filter_category == $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: end;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="?competition_id=<?php echo $competition_id; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Daftar Peserta (<?php echo count($participants); ?> data)</h2>
                <p style="margin: 0; color: var(--text-soft); font-size: 0.9rem;">Data peserta yang sudah lunas pembayarannya</p>
            </div>
            <?php if (empty($participants)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Belum Ada Data Peserta</h3>
                    <p>Peserta yang sudah lunas pembayarannya akan muncul di sini</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Umur</th>
                            <th>Sekolah</th>
                            <th>Berat/Tinggi</th>
                            <th>Kontingen</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $index => $participant): ?>
                        <tr data-id="<?php echo $participant['registration_id']; ?>">
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <strong><?php echo htmlspecialchars($participant['nama']); ?></strong>
                                    <small style="color: var(--text-soft);"><?php echo htmlspecialchars($participant['tempat_lahir']); ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $participant['jenis_kelamin'] == 'L' ? 'status-active' : 'status-pending'; ?>">
                                    <?php echo $participant['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                </span>
                            </td>
                            <td><?php echo calculateAge($participant['tanggal_lahir']); ?> tahun</td>
                            <td><?php echo htmlspecialchars($participant['nama_sekolah'] ?? '-'); ?></td>
                            <td>
                                <?php echo $participant['berat_badan'] ? $participant['berat_badan'] . ' kg' : '-'; ?> / 
                                <?php echo $participant['tinggi_badan'] ? $participant['tinggi_badan'] . ' cm' : '-'; ?>
                            </td>
                            <td><?php echo htmlspecialchars($participant['kontingen']); ?></td>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <div><strong><?php echo htmlspecialchars($participant['kategori_umur']); ?></strong></div>
                                    <small style="color: var(--text-soft); "><?php echo htmlspecialchars($participant['jenis_kompetisi']); ?></small>
                                    <small style="color: var(--text-soft); "><?php echo htmlspecialchars($participant['kategori_tanding']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <button type="button" class="btn btn-secondary btn-action btn-edit" onclick="editParticipant(<?php echo $participant['registration_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-action btn-delete" onclick="deleteParticipant(<?php echo $participant['registration_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Participant Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Data Peserta</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update_participant">
                <input type="hidden" name="participant_id" id="edit_participant_id">
                
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_nama">Nama Lengkap *</label>
                            <input type="text" id="edit_nama" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_jenis_kelamin">Jenis Kelamin *</label>
                            <select id="edit_jenis_kelamin" name="jenis_kelamin" required>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_tanggal_lahir">Tanggal Lahir *</label>
                            <input type="date" id="edit_tanggal_lahir" name="tanggal_lahir" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_tempat_lahir">Tempat Lahir *</label>
                            <input type="text" id="edit_tempat_lahir" name="tempat_lahir" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_tempat_lahir">Tempat Lahir *</label>
                            <input type="text" id="edit_tempat_lahir" name="tempat_lahir" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_nama_sekolah">Nama Sekolah</label>
                            <input type="text" id="edit_nama_sekolah" name="nama_sekolah">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_berat_badan">Berat Badan (kg)</label>
                            <input type="number" id="edit_berat_badan" name="berat_badan" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="edit_tinggi_badan">Tinggi Badan (cm)</label>
                            <input type="number" id="edit_tinggi_badan" name="tinggi_badan" step="0.01">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_kontingen">Kontingen *</label>
                            <input type="text" id="edit_kontingen" name="kontingen" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_age_category_id">Kategori Umur *</label>
                            <select id="edit_age_category_id" name="age_category_id" required>
                                <option value="">Pilih Kategori Umur</option>
                                <?php foreach ($age_categories as $age_cat): ?>
                                    <option value="<?php echo $age_cat['id']; ?>">
                                        <?php echo htmlspecialchars($age_cat['nama_kategori']); ?> (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_competition_type_id">Jenis Kompetisi *</label>
                            <select id="edit_competition_type_id" name="competition_type_id" required onchange="handleEditCompetitionTypeChange()">
                                <option value="">Pilih Jenis Kompetisi</option>
                                <?php foreach ($competition_types as $comp_type): ?>
                                    <option value="<?php echo $comp_type['id']; ?>" 
                                            data-is-tanding="<?php echo (stripos($comp_type['nama_kompetisi'], 'tanding') !== false) ? '1' : '0'; ?>">
                                        <?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" id="edit_category_group" style="display: none;">
                            <label for="edit_category_id">Kategori Tanding</label>
                            <select id="edit_category_id" name="category_id">
                                <option value="">Pilih Kategori Tanding</option>
                                <?php foreach ($competition_categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Konfirmasi Hapus</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data peserta ini?</p>
                <p><strong id="delete_participant_name"></strong></p>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="delete_participant">
                <input type="hidden" name="participant_id" id="delete_participant_id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        // Handle competition type change for filtering
        function handleCompetitionTypeChange() {
            const competitionType = document.getElementById('competition_type');
            const categoryGroup = document.getElementById('category_filter_group');
            const selectedOption = competitionType.options[competitionType.selectedIndex];
            
            if (selectedOption && selectedOption.getAttribute('data-is-tanding') === '1') {
                categoryGroup.style.display = 'block';
            } else {
                categoryGroup.style.display = 'none';
                document.getElementById('category').value = '';
            }
        }

        // Handle competition type change for edit modal
        function handleEditCompetitionTypeChange() {
            const competitionType = document.getElementById('edit_competition_type_id');
            const categoryGroup = document.getElementById('edit_category_group');
            const selectedOption = competitionType.options[competitionType.selectedIndex];
            
            if (selectedOption && selectedOption.getAttribute('data-is-tanding') === '1') {
                categoryGroup.style.display = 'block';
            } else {
                categoryGroup.style.display = 'none';
                document.getElementById('edit_category_id').value = '';
            }
        }

        // Edit participant
        function editParticipant(participantId) {
            // Fetch participant data via AJAX
            fetch(`get-participant-data.php?id=${participantId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const participant = data.participant;
                        
                        document.getElementById('edit_participant_id').value = participant.id;
                        document.getElementById('edit_nama').value = participant.nama;
                        document.getElementById('edit_jenis_kelamin').value = participant.jenis_kelamin;
                        document.getElementById('edit_tanggal_lahir').value = participant.tanggal_lahir;
                        document.getElementById('edit_tempat_lahir').value = participant.tempat_lahir;
                        document.getElementById('edit_nama_sekolah').value = participant.nama_sekolah || '';
                        document.getElementById('edit_berat_badan').value = participant.berat_badan || '';
                        document.getElementById('edit_tinggi_badan').value = participant.tinggi_badan || '';
                        document.getElementById('edit_kontingen').value = participant.kontingen;
                        document.getElementById('edit_age_category_id').value = participant.age_category_id;
                        document.getElementById('edit_competition_type_id').value = participant.competition_type_id;
                        document.getElementById('edit_category_id').value = participant.category_id || '';
                        
                        // Show/hide category field based on competition type
                        handleEditCompetitionTypeChange();
                        
                        document.getElementById('editModal').style.display = 'block';
                    } else {
                        alert('Gagal mengambil data peserta');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data peserta');
                });
        }

        // Delete participant
        function deleteParticipant(participantId) {
            // Fetch participant name via AJAX
            fetch(`get-participant-data.php?id=${participantId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('delete_participant_id').value = participantId;
                        document.getElementById('delete_participant_name').textContent = data.participant.nama;
                        document.getElementById('deleteModal').style.display = 'block';
                    } else {
                        alert('Gagal mengambil data peserta');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data peserta');
                });
        }

        // Close modals
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === editModal) {
                closeModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }

        // Initialize competition type filter
        document.addEventListener('DOMContentLoaded', function() {
            handleCompetitionTypeChange();
        });

        // Otomatis reset kategori tanding jika jenis kompetisi bukan tanding
        const compType = document.getElementById('competition_type');
        const catSelect = document.getElementById('category');
        compType && compType.addEventListener('change', function() {
            const selected = compType.value.toLowerCase();
            if (!selected.includes('tanding')) {
                catSelect.value = '';
            }
        });

        function saveFilteredParticipants() {
            // Ambil semua id peserta yang tampil di tabel
            const ids = Array.from(document.querySelectorAll('tbody tr[data-id]')).map(tr => tr.getAttribute('data-id'));
            if (ids.length === 0) {
                alert('Tidak ada data yang bisa disimpan!');
                return;
            }
            if (!confirm('Simpan ' + ids.length + ' peserta hasil filter ke database?')) return;

            // Ambil nilai filter
            const kategori_umur = document.getElementById('age_category')?.value || '';
            const jenis_kelamin = document.getElementById('gender')?.value || '';
            const jenis_kompetisi = document.getElementById('competition_type')?.value || '';
            const kategori_tanding = document.getElementById('category')?.value || '';

            fetch('simpan-filtered-peserta.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    peserta_ids: ids,
                    kategori_umur,
                    jenis_kelamin,
                    jenis_kompetisi,
                    kategori_tanding
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Berhasil menyimpan hasil filter!');
                } else {
                    alert('Gagal menyimpan: ' + data.message);
                }
            })
            .catch(err => alert('Gagal menyimpan: ' + err));
        }
    </script>

    <style>
        .dashboard-card-icon.pink {
            background: linear-gradient(135deg, #ec4899, #be185d);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .modal .form-row {
            grid-template-columns: 1fr 1fr;
        }

        /* Additional styles to match admin theme */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .page-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-title {
            color: var(--primary-color);
            margin: 0 0 5px 0;
            font-size: 1.3rem;
        }

        .table-actions {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            background-color: var(--light-color);
            font-weight: 600;
            color: var(--text-color);
        }

        .data-table tr:hover {
            background-color: var(--light-color);
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: var(--light-color);
            border-radius: 8px;
            color: var(--text-light);
            border: 1px solid var(--border-color);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
            color: var(--text-light);
        }

        .empty-state h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: var(--text-color);
            font-weight: 500;
        }

        .empty-state p {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .modal .form-row {
                grid-template-columns: 1fr;
            }

            .modal-content {
                width: 95%;
                margin: 0;
                max-height: 90vh;
            }

            .page-actions {
                flex-direction: column;
            }

            .table-header {
                flex-direction: column;
                gap: 15px;
            }

            .table-actions {
                flex-direction: column;
            }

            .data-table {
                font-size: 0.8rem;
            }

            .data-table th,
            .data-table td {
                padding: 8px 6px;
            }
        }

        /* Layout filter lebih rapi dan responsif */
        form[method=GET] {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }
        form[method=GET] .form-group {
            min-width: 180px;
            flex: 1 1 180px;
        }
        @media (max-width: 900px) {
            form[method=GET] {
                flex-direction: column;
                gap: 10px;
            }
            form[method=GET] .form-group {
                min-width: 100%;
                width: 100%;
            }
        }
    </style>
</body>
</html> 