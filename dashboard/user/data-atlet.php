<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit();
}

// Get user's kontingen
$stmt = $pdo->prepare("SELECT * FROM kontingen WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$_SESSION['user_id']]);
$user_kontingen = $stmt->fetchAll();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_athlete'])) {
        $nama = sanitizeInput($_POST['nama']);
        $nik = sanitizeInput($_POST['nik']);
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $tempat_lahir = sanitizeInput($_POST['tempat_lahir']);
        $nama_sekolah = sanitizeInput($_POST['nama_sekolah']);
        $berat_badan = $_POST['berat_badan'];
        $tinggi_badan = $_POST['tinggi_badan'];
        $kontingen_id = $_POST['kontingen_id']; // TAMBAHAN BARU
        
        // Validate kontingen ownership
        $stmt = $pdo->prepare("SELECT id FROM kontingen WHERE id = ? AND user_id = ?");
        $stmt->execute([$kontingen_id, $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            sendNotification('Kontingen tidak valid!', 'error');
            header('Location: data-atlet.php');
            exit();
        }
        
        // Handle photo upload
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $foto = uploadFile($_FILES['foto'], 'uploads/athletes/');
        }
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO athletes (user_id, kontingen_id, nama, nik, jenis_kelamin, tanggal_lahir, tempat_lahir, nama_sekolah, berat_badan, tinggi_badan, foto) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$_SESSION['user_id'], $kontingen_id, $nama, $nik, $jenis_kelamin, $tanggal_lahir, $tempat_lahir, $nama_sekolah, $berat_badan, $tinggi_badan, $foto]);
            sendNotification('Atlet berhasil ditambahkan!', 'success');
        } catch (PDOException $e) {
            sendNotification('Gagal menambahkan atlet: ' . $e->getMessage(), 'error');
        }
        header('Location: data-atlet.php');
        exit();
    }
}

// Handle update (edit) athlete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_athlete'])) {
    $athlete_id = $_POST['athlete_id'];
    $nama = sanitizeInput($_POST['nama']);
    $nik = sanitizeInput($_POST['nik']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $tempat_lahir = sanitizeInput($_POST['tempat_lahir']);
    $nama_sekolah = sanitizeInput($_POST['nama_sekolah']);
    $berat_badan = $_POST['berat_badan'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $kontingen_id = $_POST['kontingen_id'];
    $foto = null;
    $foto_sql = '';
    // Validate kontingen ownership
    $stmt = $pdo->prepare("SELECT id FROM kontingen WHERE id = ? AND user_id = ?");
    $stmt->execute([$kontingen_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        sendNotification('Kontingen tidak valid!', 'error');
        header('Location: data-atlet.php');
        exit();
    }
    // Handle photo upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $foto = uploadFile($_FILES['foto'], 'uploads/athletes/');
        $foto_sql = ', foto = ?';
    }
    try {
        $params = [$kontingen_id, $nama, $nik, $jenis_kelamin, $tanggal_lahir, $tempat_lahir, $nama_sekolah, $berat_badan, $tinggi_badan];
        $sql = "UPDATE athletes SET kontingen_id = ?, nama = ?, nik = ?, jenis_kelamin = ?, tanggal_lahir = ?, tempat_lahir = ?, nama_sekolah = ?, berat_badan = ?, tinggi_badan = ?";
        if ($foto) {
            $sql .= $foto_sql;
            $params[] = $foto;
        }
        $sql .= " WHERE id = ? AND user_id = ?";
        $params[] = $athlete_id;
        $params[] = $_SESSION['user_id'];
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        sendNotification('Data atlet berhasil diupdate!', 'success');
    } catch (PDOException $e) {
        sendNotification('Gagal update atlet: ' . $e->getMessage(), 'error');
    }
    header('Location: data-atlet.php');
    exit();
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        // Get athlete data first to delete photo
        $stmt = $pdo->prepare("SELECT foto FROM athletes WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        $athlete = $stmt->fetch();
        
        if ($athlete && $athlete['foto']) {
            deleteFile('uploads/athletes/' . $athlete['foto']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM athletes WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        sendNotification('Atlet berhasil dihapus!', 'success');
    } catch (PDOException $e) {
        sendNotification('Gagal menghapus atlet!', 'error');
    }
    header('Location: data-atlet.php');
    exit();
}

// Get user's athletes with kontingen info
$stmt = $pdo->prepare("
    SELECT a.*, k.nama_kontingen 
    FROM athletes a 
    LEFT JOIN kontingen k ON a.kontingen_id = k.id 
    WHERE a.user_id = ? 
    ORDER BY a.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$athletes = $stmt->fetchAll();

$notification = getNotification();

// Helper function to calculate age (fix for the error)
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
    <title>Data Atlet - User Panel</title>
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
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-fist-raised"></i>
                <span>User Panel</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="data-atlet.php" class="active"><i class="fas fa-user-ninja"></i> Data Atlet</a></li>
            <li>
                <a href="#" onclick="toggleSubmenu(this)">
                    <i class="fas fa-trophy"></i> Perlombaan <i class="fas fa-chevron-down" style="margin-left: auto;"></i>
                </a>
                <ul class="sidebar-submenu">
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
            <h1 class="page-title">Data Atlet</h1>
            <p class="page-subtitle">Kelola data atlet Anda</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 30px;">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 30px;">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
        <?php endif; ?>

        <?php if (empty($user_kontingen)): ?>
        <!-- No Kontingen Warning -->
        <div class="alert alert-warning" style="margin-bottom: 30px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #f59e0b;"></i>
                <div>
                    <h3 style="margin: 0; color: #f59e0b;">Belum Ada Kontingen</h3>
                    <p style="margin: 5px 0 0 0;">Anda harus membuat kontingen terlebih dahulu sebelum menambahkan atlet.</p>
                    <a href="kontingen.php" class="btn-primary" style="margin-top: 10px; display: inline-block;">
                        <i class="fas fa-plus"></i> Buat Kontingen
                    </a>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Add Athlete Form -->
        <div class="table-container" style="margin-bottom: 30px;">
            <div class="table-header">
                <h2 class="table-title">Tambah Atlet Baru</h2>
            </div>
            <form method="POST" enctype="multipart/form-data" style="padding: 30px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <!-- Pilihan Kontingen -->
                    <div class="form-group">
                        <label for="kontingen_id">Pilih Kontingen <span style="color: red;">*</span></label>
                        <select id="kontingen_id" name="kontingen_id" required>
                            <option value="">Pilih Kontingen</option>
                            <?php foreach ($user_kontingen as $kontingen): ?>
                            <option value="<?php echo $kontingen['id']; ?>">
                                <?php echo htmlspecialchars($kontingen['nama_kontingen']); ?> 
                                (<?php echo htmlspecialchars($kontingen['provinsi']); ?> - <?php echo htmlspecialchars($kontingen['kota']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color: #6b7280;">Pilih kontingen untuk atlet ini</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="foto">Foto Atlet</label>
                        <input type="file" id="foto" name="foto" accept="image/*" onchange="handleFileUpload(this, 'foto-preview')">
                        <div id="foto-preview" style="margin-top: 10px;"></div>
                    </div>
                    <div class="form-group">
                        <label for="nama">Nama Lengkap <span style="color: red;">*</span></label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    <div class="form-group">
                        <label for="nik">NIK <span style="color: red;">*</span></label>
                        <input type="text" id="nik" name="nik" required maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                    </div>
                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin <span style="color: red;">*</span></label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir <span style="color: red;">*</span></label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>
                    </div>
                    <div class="form-group">
                        <label for="tempat_lahir">Tempat Lahir <span style="color: red;">*</span></label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" required>
                    </div>
                    <div class="form-group">
                        <label for="nama_sekolah">Nama Sekolah/Instansi <span style="color: red;">*</span></label>
                        <input type="text" id="nama_sekolah" name="nama_sekolah" required>
                    </div>
                    <div class="form-group">
                        <label for="berat_badan">Berat Badan (kg) <span style="color: red;">*</span></label>
                        <input type="number" id="berat_badan" name="berat_badan" step="0.1" min="1" max="200" required>
                    </div>
                    <div class="form-group">
                        <label for="tinggi_badan">Tinggi Badan (cm) <span style="color: red;">*</span></label>
                        <input type="number" id="tinggi_badan" name="tinggi_badan" step="0.1" min="50" max="250" required>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" name="add_athlete" class="btn-primary">
                        <i class="fas fa-plus"></i> Tambah Atlet
                    </button>
                    <button type="reset" class="btn-secondary" style="margin-left: 10px;">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Athletes List -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Daftar Atlet (<?php echo count($athletes); ?>)</h2>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="searchAthlete" placeholder="Cari atlet...">
                    </div>
                    <a class="btn-action-xl" href="export-athletes.php" target="_blank">
                        <i class="fas fa-download"></i> Export Excel
                    </a>
                    <button class="btn-action-xl" onclick="showImportModal()">
                        <i class="fas fa-upload"></i> Import Excel
                    </button>
                </div>
            </div>
            <?php if (empty($athletes)): ?>
            <div style="padding: 40px; text-align: center; color: #6b7280;">
                <i class="fas fa-user-ninja" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3>Belum Ada Data Atlet</h3>
                <p>Silakan tambahkan data atlet terlebih dahulu.</p>
            </div>
            <?php else: ?>
            <table class="data-table" id="athleteTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Kontingen</th>
                        <th>NIK</th>
                        <th>JK</th>
                        <th>Tanggal Lahir</th>
                        <th>Tempat Lahir</th>
                        <th>Sekolah/Instansi</th>
                        <th>BB (kg)</th>
                        <th>TB (cm)</th>
                        <th>Umur</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($athletes as $index => $athlete): ?>
                    <tr data-athlete-id="<?php echo $athlete['id']; ?>" data-foto="<?php echo htmlspecialchars($athlete['foto']); ?>" data-kontingen-id="<?php echo $athlete['kontingen_id']; ?>" data-tanggal-lahir="<?php echo htmlspecialchars($athlete['tanggal_lahir']); ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <?php if ($athlete['foto']): ?>
                                <img src="../../uploads/athletes/<?php echo $athlete['foto']; ?>" alt="<?php echo htmlspecialchars($athlete['nama']); ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user" style="color: #9ca3af;"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($athlete['nama']); ?></td>
                        <td>
                            <?php if ($athlete['nama_kontingen']): ?>
                                <span class="badge badge-primary"><?php echo htmlspecialchars($athlete['nama_kontingen']); ?></span>
                            <?php else: ?>
                                <span class="badge badge-warning">Tidak Ada</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($athlete['nik']); ?></td>
                        <td><?php echo $athlete['jenis_kelamin']; ?></td>
                        <td><?php echo date('d M Y', strtotime($athlete['tanggal_lahir'])); ?></td>
                        <td><?php echo htmlspecialchars($athlete['tempat_lahir']); ?></td>
                        <td><?php echo htmlspecialchars($athlete['nama_sekolah']); ?></td>
                        <td><?php echo $athlete['berat_badan']; ?></td>
                        <td><?php echo $athlete['tinggi_badan']; ?></td>
                        <td><?php echo calculateAge($athlete['tanggal_lahir']); ?> tahun</td>
                        <td>
                            <button class="btn-action btn-edit" onclick="editAthlete(<?php echo $athlete['id']; ?>)"><i class="fas fa-edit"></i> Ubah</button>
                            <button class="btn-action btn-delete" onclick="deleteAthlete(<?php echo $athlete['id']; ?>)"><i class="fas fa-trash"></i> Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="modal">
        <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
            <div class="modal-header">
                <h2>Import Data Atlet</h2>
                <span class="close" onclick="closeImportModal()">&times;</span>
            </div>
            <div style="padding: 30px;">
                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Template Excel</h3>
                    <p>Download template Excel terlebih dahulu, isi data sesuai format, kemudian upload kembali.</p>
                    <a href="template-atlet.php" class="btn-action-xl full-width" style="margin-bottom:20px;">
                        <i class="fas fa-file-excel"></i> Download Template Excel (.xlsx)
                    </a>
                </div>
                
                <div class="info-section">
                    <h3><i class="fas fa-upload"></i> Upload File Excel</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="file-upload">
                            <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls" style="display: none;">
                            <label for="excel_file" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; margin-bottom: 10px;"></i>
                                <p>Klik untuk pilih file Excel</p>
                                <small>Format: .xlsx (Excel terbaru) atau .xls (kompatibel dengan mobile dan office)</small>
                            </label>
                        </div>
                        <button type="submit" formaction="import-athletes.php" name="import_athletes" class="btn-action-xl full-width" style="margin-top: 20px;">
                            <i class="fas fa-upload"></i> Import Data
                        </button>
                    </form>
                </div>
                
                <div class="info-section">
                    <h3><i class="fas fa-list"></i> Format Data</h3>
                    <p>Pastikan data Excel memiliki kolom berikut (sesuai urutan):</p>
                    <ol>
                        <li><strong>Nama Kontingen</strong> - Harus sama persis dengan kontingen yang sudah dibuat di Akun Saya</li>
                        <li>Nama Lengkap</li>
                        <li>NIK</li>
                        <li>Jenis Kelamin (L/P)</li>
                        <li>Tanggal Lahir (YYYY-MM-DD)</li>
                        <li>Tempat Lahir</li>
                        <li>Nama Sekolah/Instansi</li>
                        <li>Berat Badan (kg)</li>
                        <li>Tinggi Badan (cm)</li>
                    </ol>
                    
                    <div class="kontingen-info" style="margin-top: 15px; padding: 15px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #2563eb;">
                        <h4 style="margin: 0 0 10px 0; color: #2563eb; font-size: 1rem;">
                            <i class="fas fa-info-circle"></i> Kontingen yang Tersedia:
                        </h4>
                        <?php if (empty($user_kontingen)): ?>
                            <p style="margin: 0; color: #dc2626; font-weight: 600;">
                                <i class="fas fa-exclamation-triangle"></i> Anda belum memiliki kontingen. 
                                <a href="akun-saya.php" style="color: #2563eb;">Buat kontingen terlebih dahulu</a>
                            </p>
                        <?php else: ?>
                            <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                                <?php foreach ($user_kontingen as $kontingen): ?>
                                    <li><strong><?php echo htmlspecialchars($kontingen['nama_kontingen']); ?></strong> 
                                        (<?php echo htmlspecialchars($kontingen['provinsi']); ?> - <?php echo htmlspecialchars($kontingen['kota']); ?>)
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Athlete Modal -->
    <div id="editAthleteModal" class="modal" style="display:none;">
      <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
          <h2>Edit Data Atlet</h2>
          <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <form id="editAthleteForm" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="athlete_id" id="edit_athlete_id">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
            <div class="form-group">
              <label for="edit_nama">Nama Lengkap</label>
              <input type="text" id="edit_nama" name="nama" required>
            </div>
            <div class="form-group">
              <label for="edit_nik">NIK</label>
              <input type="text" id="edit_nik" name="nik" required maxlength="16" pattern="[0-9]{16}">
            </div>
            <div class="form-group">
              <label for="edit_jenis_kelamin">Jenis Kelamin</label>
              <select id="edit_jenis_kelamin" name="jenis_kelamin" required>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>
            <div class="form-group">
              <label for="edit_tanggal_lahir">Tanggal Lahir</label>
              <input type="date" id="edit_tanggal_lahir" name="tanggal_lahir" required>
            </div>
            <div class="form-group">
              <label for="edit_tempat_lahir">Tempat Lahir</label>
              <input type="text" id="edit_tempat_lahir" name="tempat_lahir" required>
            </div>
            <div class="form-group">
              <label for="edit_nama_sekolah">Nama Sekolah/Instansi</label>
              <input type="text" id="edit_nama_sekolah" name="nama_sekolah" required>
            </div>
            <div class="form-group">
              <label for="edit_berat_badan">Berat Badan (kg)</label>
              <input type="number" id="edit_berat_badan" name="berat_badan" step="0.1" min="1" max="200" required>
            </div>
            <div class="form-group">
              <label for="edit_tinggi_badan">Tinggi Badan (cm)</label>
              <input type="number" id="edit_tinggi_badan" name="tinggi_badan" step="0.1" min="50" max="250" required>
            </div>
            <div class="form-group">
              <label for="edit_kontingen_id">Kontingen</label>
              <select id="edit_kontingen_id" name="kontingen_id" required>
                <?php foreach ($user_kontingen as $kontingen): ?>
                <option value="<?php echo $kontingen['id']; ?>"><?php echo htmlspecialchars($kontingen['nama_kontingen']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label for="edit_foto">Foto Atlet</label>
              <input type="file" id="edit_foto" name="foto" accept="image/*" onchange="previewEditFoto(this)">
              <div id="edit-foto-preview" style="margin-top:10px;"></div>
            </div>
          </div>
          <div class="modal-footer" style="margin-top:18px;">
            <button type="submit" name="edit_athlete" class="btn-primary"><i class="fas fa-save"></i> Simpan</button>
            <button type="button" class="btn-secondary" onclick="closeEditModal()"><i class="fas fa-times"></i> Batal</button>
          </div>
        </form>
      </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        // 2. JS: handle buka modal, isi data, preview foto, dan tutup modal
        function openEditModal(athlete) {
          document.getElementById('edit_athlete_id').value = athlete.id;
          document.getElementById('edit_nama').value = athlete.nama;
          document.getElementById('edit_nik').value = athlete.nik;
          document.getElementById('edit_jenis_kelamin').value = athlete.jenis_kelamin;
          document.getElementById('edit_tanggal_lahir').value = athlete.tanggal_lahir;
          document.getElementById('edit_tempat_lahir').value = athlete.tempat_lahir;
          document.getElementById('edit_nama_sekolah').value = athlete.nama_sekolah;
          document.getElementById('edit_berat_badan').value = athlete.berat_badan;
          document.getElementById('edit_tinggi_badan').value = athlete.tinggi_badan;
          document.getElementById('edit_kontingen_id').value = athlete.kontingen_id;
          // Preview foto lama
          let preview = document.getElementById('edit-foto-preview');
          preview.innerHTML = '';
          if (athlete.foto) {
            preview.innerHTML = `<img src='../../uploads/athletes/${athlete.foto}' style='width:60px;height:60px;border-radius:8px;object-fit:cover;'>`;
          }
          document.getElementById('edit_foto').value = '';
          document.getElementById('editAthleteModal').style.display = 'block';
        }
        function closeEditModal() {
          document.getElementById('editAthleteModal').style.display = 'none';
        }
        function previewEditFoto(input) {
          let preview = document.getElementById('edit-foto-preview');
          preview.innerHTML = '';
          if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
              preview.innerHTML = `<img src='${e.target.result}' style='width:60px;height:60px;border-radius:8px;object-fit:cover;'>`;
            };
            reader.readAsDataURL(input.files[0]);
          }
        }
        // 3. Ambil data dari baris tabel saat klik Ubah
        function editAthlete(id) {
          // Ambil data dari baris tabel (bisa juga pakai AJAX jika mau lebih dinamis)
          var row = document.querySelector('tr[data-athlete-id="'+id+'"]');
          if (!row) return;
          var cells = row.children;
          var athlete = {
            id: id,
            foto: row.getAttribute('data-foto'),
            nama: cells[2].textContent.trim(),
            kontingen_id: row.getAttribute('data-kontingen-id'),
            nik: cells[4].textContent.trim(),
            jenis_kelamin: cells[5].textContent.trim(),
            tanggal_lahir: row.getAttribute('data-tanggal-lahir'),
            tempat_lahir: cells[7].textContent.trim(),
            nama_sekolah: cells[8].textContent.trim(),
            berat_badan: cells[9].textContent.trim(),
            tinggi_badan: cells[10].textContent.trim()
          };
          openEditModal(athlete);
        }
        
        function deleteAthlete(id) {
            if (confirmDelete('Apakah Anda yakin ingin menghapus atlet ini?')) {
                window.location.href = `?action=delete&id=${id}`;
            }
        }
        
        function showImportModal() {
            document.getElementById('importModal').style.display = 'block';
        }
        
        function closeImportModal() {
            document.getElementById('importModal').style.display = 'none';
        }
        
        // Initialize search
        document.addEventListener('DOMContentLoaded', function() {
            searchTable('searchAthlete', 'athleteTable');
        });
    </script>

    <style>
        .info-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-section:last-child {
            border-bottom: none;
        }
        
        .info-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-section ol {
            margin-left: 20px;
        }
        
        .info-section ol li {
            margin-bottom: 5px;
        }
        
        .alert {
            padding: 20px;
            border-radius: 8px;
            border: 1px solid;
        }
        
        .alert-warning {
            background-color: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background-color: #fee2e2;
            border-color: #ef4444;
            color: #991b1b;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-primary {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .btn-action-xl {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 12px 24px;
            font-size: 1.08rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            min-width: 180px;
            justify-content: center;
        }
        .btn-action-xl:hover {
            background: #1e40af;
            color: #fff;
            text-decoration: none;
        }
        .btn-action-xl.full-width {
            width: 100%;
        }
        .modal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100vw; height:100vh; overflow:auto; background:rgba(0,0,0,0.3); }
        .modal-content { background:#fff; margin:5% auto; border-radius:12px; padding:30px; position:relative; }
        .modal-header { display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; margin-bottom:18px; }
        .modal-footer { text-align:right; }
        .close { font-size:2rem; cursor:pointer; }
    </style>
</body>
</html>
