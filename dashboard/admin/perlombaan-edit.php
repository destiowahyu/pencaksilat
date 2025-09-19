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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_basic_info':
            $nama_perlombaan = sanitizeInput($_POST['nama_perlombaan']);
            $deskripsi = sanitizeInput($_POST['deskripsi']);
            $lokasi = sanitizeInput($_POST['lokasi']);
            $maps_link = sanitizeInput($_POST['maps_link']);
            $whatsapp_group = sanitizeInput($_POST['whatsapp_group']);
            // Admin can now change registration status
            $registration_status = $_POST['registration_status'];
            $tanggal_open_regist = $_POST['tanggal_open_regist'] ?: null;
            $tanggal_close_regist = $_POST['tanggal_close_regist'] ?: null;
            $tanggal_pelaksanaan = $_POST['tanggal_pelaksanaan'] ?: null;

            if (empty($nama_perlombaan)) {
                $error = "Nama perlombaan wajib diisi!";
            } else {
                try {
                    $poster_filename = $competition['poster'];
                    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
                        $poster_filename = uploadFile($_FILES['poster'], '../../uploads/posters/');
                        if (!$poster_filename) {
                            $error = "Gagal mengupload poster!";
                        }
                    }

                    if (!isset($error)) {
                        // Main status is NOT updated by admin. Only registration_status is.
                        $stmt = $pdo->prepare("
                            UPDATE competitions 
                            SET nama_perlombaan = ?, deskripsi = ?, lokasi = ?, maps_link = ?, 
                                whatsapp_group = ?, registration_status = ?, tanggal_open_regist = ?, 
                                tanggal_close_regist = ?, tanggal_pelaksanaan = ?, poster = ?, updated_at = NOW()
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $nama_perlombaan, $deskripsi, $lokasi, $maps_link, 
                            $whatsapp_group, $registration_status, $tanggal_open_regist, 
                            $tanggal_close_regist, $tanggal_pelaksanaan, $poster_filename, $competition_id
                        ]);
                        
                        $success = "Informasi perlombaan berhasil diperbarui!";
                        
                        // Refresh data
                        $stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ?");
                        $stmt->execute([$competition_id]);
                        $competition = $stmt->fetch();
                    }
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
            break;

        case 'add_contact':
            $nama_kontak = sanitizeInput($_POST['nama_kontak']);
            $nomor_kontak = sanitizeInput($_POST['nomor_kontak']);
            $jabatan = sanitizeInput($_POST['jabatan']);

            if (!empty($nama_kontak) && !empty($nomor_kontak)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO competition_contacts (competition_id, nama_kontak, nomor_whatsapp, jabatan, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$competition_id, $nama_kontak, $nomor_kontak, $jabatan]);
                    $success = "Kontak panitia berhasil ditambahkan!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            } else {
                $error = "Nama kontak dan nomor kontak wajib diisi!";
            }
            break;

        case 'upload_document':
            $nama_dokumen = sanitizeInput($_POST['nama_dokumen']);
            
            if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] === UPLOAD_ERR_OK && !empty($nama_dokumen)) {
                // Corrected upload path
                $file_path = uploadFile($_FILES['dokumen'], '../../uploads/documents/');
                if ($file_path) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO competition_documents (competition_id, nama_dokumen, file_path, created_at) VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$competition_id, $nama_dokumen, $file_path]);
                        $success = "Dokumen berhasil diupload!";
                    } catch (PDOException $e) {
                        $error = "Error: " . $e->getMessage();
                    }
                } else {
                    $error = "Gagal mengupload dokumen!";
                }
            } else {
                $error = "Nama dokumen dan file dokumen wajib diisi!";
            }
            break;

        case 'add_category':
            $nama_kategori = sanitizeInput($_POST['nama_kategori']);
            $jenis_kelamin = isset($_POST['jenis_kelamin']) ? sanitizeInput($_POST['jenis_kelamin']) : 'Campuran';
            $age_category_id = $_POST['age_category_id'] ?: null;
            $berat_min = $_POST['berat_min'] ?: null;
            $berat_max = $_POST['berat_max'] ?: null;
            $deskripsi_kategori = sanitizeInput($_POST['deskripsi_kategori']);

            if (!empty($nama_kategori)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO competition_categories (competition_id, nama_kategori, jenis_kelamin, age_category_id, berat_min, berat_max, deskripsi, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$competition_id, $nama_kategori, $jenis_kelamin, $age_category_id, $berat_min, $berat_max, $deskripsi_kategori]);
                    $success = "Kategori pertandingan berhasil ditambahkan!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            } else {
                $error = "Nama kategori wajib diisi!";
            }
            break;

        case 'add_age_category':
            $nama_kategori_umur = sanitizeInput($_POST['nama_kategori_umur']);
            $usia_min = $_POST['usia_min'];
            $usia_max = $_POST['usia_max'];

            if (!empty($nama_kategori_umur) && !empty($usia_min) && !empty($usia_max)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO age_categories (competition_id, nama_kategori, usia_min, usia_max, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$competition_id, $nama_kategori_umur, $usia_min, $usia_max]);
                    $success = "Kategori umur berhasil ditambahkan!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            } else {
                $error = "Semua field kategori umur wajib diisi!";
            }
            break;

        case 'add_competition_type':
            $nama_kompetisi = sanitizeInput($_POST['nama_kompetisi']);
            $jenis_kelamin = isset($_POST['jenis_kelamin']) ? sanitizeInput($_POST['jenis_kelamin']) : 'Campuran';
            $biaya_pendaftaran = $_POST['biaya_pendaftaran'] ?: 0;
            $deskripsi_kompetisi = sanitizeInput($_POST['deskripsi_kompetisi']);

            if (!empty($nama_kompetisi)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO competition_types (competition_id, nama_kompetisi, jenis_kelamin, biaya_pendaftaran, deskripsi, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$competition_id, $nama_kompetisi, $jenis_kelamin, $biaya_pendaftaran, $deskripsi_kompetisi]);
                    $success = "Jenis kompetisi berhasil ditambahkan!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            } else {
                $error = "Nama kompetisi wajib diisi!";
            }
            break;
    }
}

// Get existing data
$contacts = [];
$documents = [];
$categories = [];
$age_categories = [];
$competition_types = [];

try {
    // Get contacts
    $stmt = $pdo->prepare("SELECT * FROM competition_contacts WHERE competition_id = ? ORDER BY created_at DESC");
    $stmt->execute([$competition_id]);
    $contacts = $stmt->fetchAll();

    // Get documents
    $stmt = $pdo->prepare("SELECT * FROM competition_documents WHERE competition_id = ? ORDER BY created_at DESC");
    $stmt->execute([$competition_id]);
    $documents = $stmt->fetchAll();

    // Get categories
    $stmt = $pdo->prepare("
        SELECT cc.*, ac.nama_kategori as age_category_name 
        FROM competition_categories cc 
        LEFT JOIN age_categories ac ON cc.age_category_id = ac.id 
        WHERE cc.competition_id = ? 
        ORDER BY cc.created_at DESC
    ");
    $stmt->execute([$competition_id]);
    $categories = $stmt->fetchAll();

    // Get age categories
    $stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY usia_min");
    $stmt->execute([$competition_id]);
    $age_categories = $stmt->fetchAll();

    // Get competition types
    $stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY created_at DESC");
    $stmt->execute([$competition_id]);
    $competition_types = $stmt->fetchAll();
} catch (PDOException $e) {
    // Tables might not exist yet
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Perlombaan - Admin Panel</title>
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
            <h1 class="page-title">Edit Perlombaan</h1>
            <p class="page-subtitle">Edit informasi perlombaan: <?php echo htmlspecialchars($competition['nama_perlombaan']); ?></p>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <div class="table-container">
            <div class="table-header">
                <div class="tab-navigation">
                    <button class="tab-btn active" onclick="showTab('basic', this)">
                        <i class="fas fa-info-circle"></i>
                        <span>Info Dasar</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('contacts', this)">
                        <i class="fas fa-phone"></i>
                        <span>Kontak Panitia</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('documents', this)">
                        <i class="fas fa-file-alt"></i>
                        <span>Dokumen</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('categories', this)">
                        <i class="fas fa-list"></i>
                        <span>Kategori Tanding</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('competition-types', this)">
                        <i class="fas fa-star"></i>
                        <span>Jenis Kompetisi</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('age-categories', this)">
                        <i class="fas fa-calendar"></i>
                        <span>Kategori Umur</span>
                    </button>
                </div>
            </div>

            <!-- Basic Info Tab -->
            <div id="basic-tab" class="tab-content active">
                <div style="padding: 30px;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_basic_info">
                        
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Informasi Dasar</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="nama_perlombaan">Nama Perlombaan *</label>
                                    <input type="text" id="nama_perlombaan" name="nama_perlombaan" 
                                           value="<?php echo htmlspecialchars($competition['nama_perlombaan']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status Perlombaan</label>
                                    <input type="text" id="status" name="status" 
                                           value="<?php echo ucfirst(str_replace('_', ' ', $competition['status'])); ?>" disabled>
                                    <small>Status utama hanya bisa diubah oleh Superadmin.</small>
                                </div>
                                <div class="form-group">
                                    <label for="registration_status">Status Pendaftaran *</label>
                                    <select id="registration_status" name="registration_status" required>
                                        <option value="coming_soon" <?php echo $competition['registration_status'] == 'coming_soon' ? 'selected' : ''; ?>>Coming Soon</option>
                                        <option value="open_regist" <?php echo $competition['registration_status'] == 'open_regist' ? 'selected' : ''; ?>>Buka Pendaftaran</option>
                                        <option value="close_regist" <?php echo $competition['registration_status'] == 'close_regist' ? 'selected' : ''; ?>>Tutup Pendaftaran</option>
                                    </select>
                                    <small>Ubah status pendaftaran untuk peserta.</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="lokasi">Lokasi</label>
                                <input type="text" id="lokasi" name="lokasi" 
                                       value="<?php echo htmlspecialchars($competition['lokasi'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="deskripsi">Deskripsi</label>
                                <textarea id="deskripsi" name="deskripsi" rows="4"><?php echo htmlspecialchars($competition['deskripsi'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-image"></i> Poster Perlombaan</h3>
                            <?php if (!empty($competition['poster'])): ?>
                                <div class="current-poster">
                                    <img src="../../uploads/posters/<?php echo htmlspecialchars($competition['poster']); ?>" 
                                         alt="Current Poster" style="max-width: 300px; max-height: 400px; border-radius: 8px;">
                                    <p><small>Poster saat ini</small></p>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label for="poster">Upload Poster Baru</label>
                                <input type="file" id="poster" name="poster" accept="image/*">
                                <small>Format: JPG, PNG, GIF. Maksimal 5MB</small>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-calendar"></i> Jadwal Perlombaan</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="tanggal_open_regist">Tanggal Buka Pendaftaran</label>
                                    <input type="date" id="tanggal_open_regist" name="tanggal_open_regist" 
                                           value="<?php echo $competition['tanggal_open_regist']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="tanggal_close_regist">Tanggal Tutup Pendaftaran</label>
                                    <input type="date" id="tanggal_close_regist" name="tanggal_close_regist" 
                                           value="<?php echo $competition['tanggal_close_regist']; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="tanggal_pelaksanaan">Tanggal Pelaksanaan</label>
                                    <input type="date" id="tanggal_pelaksanaan" name="tanggal_pelaksanaan" 
                                           value="<?php echo $competition['tanggal_pelaksanaan']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3><i class="fas fa-link"></i> Link & Kontak</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div class="form-group">
                                    <label for="whatsapp_group">Link WhatsApp Group</label>
                                    <input type="url" id="whatsapp_group" name="whatsapp_group" 
                                           value="<?php echo htmlspecialchars($competition['whatsapp_group'] ?? ''); ?>"
                                           placeholder="https://chat.whatsapp.com/...">
                                </div>
                                <div class="form-group">
                                    <label for="maps_link">Link Google Maps</label>
                                    <input type="url" id="maps_link" name="maps_link" 
                                           value="<?php echo htmlspecialchars($competition['maps_link'] ?? ''); ?>"
                                           placeholder="https://maps.google.com/...">
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 15px; justify-content: flex-end; margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contacts Tab -->
            <div id="contacts-tab" class="tab-content">
                <div style="padding: 30px;">
                    <div class="form-section">
                        <h3><i class="fas fa-plus"></i> Tambah Kontak Panitia</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_contact">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
                                <div class="form-group">
                                    <label for="nama_kontak">Nama Kontak *</label>
                                    <input type="text" id="nama_kontak" name="nama_kontak" required>
                                </div>
                                <div class="form-group">
                                    <label for="nomor_kontak">Nomor Kontak *</label>
                                    <input type="text" id="nomor_kontak" name="nomor_kontak" required placeholder="081234567890">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan">Jabatan</label>
                                    <input type="text" id="jabatan" name="jabatan" placeholder="Ketua Panitia">
                                </div>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-list"></i> Daftar Kontak Panitia</h3>
                        <?php if (empty($contacts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-phone"></i>
                                <p>Belum ada kontak panitia</p>
                                <small>Tambahkan kontak panitia untuk memudahkan peserta menghubungi</small>
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
                                        <button onclick="deleteContact(<?php echo $contact['id']; ?>)" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Documents Tab -->
            <div id="documents-tab" class="tab-content">
                <div style="padding: 30px;">
                    <div class="form-section">
                        <h3><i class="fas fa-upload"></i> Upload Dokumen</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload_document">
                            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
                                <div class="form-group">
                                    <label for="nama_dokumen">Nama Dokumen *</label>
                                    <input type="text" id="nama_dokumen" name="nama_dokumen" required 
                                           placeholder="Technical Meeting, Peraturan, dll">
                                </div>
                                <div class="form-group">
                                    <label for="dokumen">File Dokumen *</label>
                                    <input type="file" id="dokumen" name="dokumen" required 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <small>Format: PDF, DOC, DOCX, JPG, PNG. Maksimal 10MB</small>
                                </div>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-file-alt"></i> Daftar Dokumen</h3>
                        <?php if (empty($documents)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <p>Belum ada dokumen</p>
                                <small>Upload dokumen penting seperti technical meeting, peraturan, atau panduan</small>
                            </div>
                        <?php else: ?>
                            <div class="document-list">
                                <?php foreach ($documents as $doc): ?>
                                <div class="document-item">
                                    <div class="document-icon">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div class="document-info">
                                        <strong><?php echo htmlspecialchars($doc['nama_dokumen']); ?></strong>
                                        <small>Diupload: <?php echo date('d M Y H:i', strtotime($doc['created_at'])); ?></small>
                                    </div>
                                    <div class="document-actions">
                                        <a href="../../uploads/documents/<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                           target="_blank" class="btn-action btn-detail">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                        <a href="../../uploads/documents/<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                           download class="btn-action btn-download">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <button onclick="deleteDocument(<?php echo $doc['id']; ?>)" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Categories Tab -->
            <div id="categories-tab" class="tab-content">
                <div style="padding: 30px;">
                    <div class="form-section">
                        <h3><i class="fas fa-plus"></i> Tambah Kategori Tanding</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_category">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label for="nama_kategori">Nama Kategori *</label>
                                    <input type="text" id="nama_kategori" name="nama_kategori" required 
                                           placeholder="Kelas A, Kelas B, Kelas C, dll">
                                </div>
                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin *</label>
                                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L">Putra (Laki-laki)</option>
                                        <option value="P">Putri (Perempuan)</option>
                                        <option value="Campuran">Campuran</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="age_category_id">Kategori Umur</label>
                                    <select id="age_category_id" name="age_category_id">
                                        <option value="">Pilih Kategori Umur</option>
                                        <?php foreach ($age_categories as $age_cat): ?>
                                            <option value="<?php echo $age_cat['id']; ?>">
                                                <?php echo htmlspecialchars($age_cat['nama_kategori']); ?> 
                                                (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="berat_min">Berat Min (kg)</label>
                                    <input type="number" id="berat_min" name="berat_min" step="0.1">
                                </div>
                                <div class="form-group">
                                    <label for="berat_max">Berat Max (kg)</label>
                                    <input type="number" id="berat_max" name="berat_max" step="0.1">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="deskripsi_kategori">Deskripsi</label>
                                <textarea id="deskripsi_kategori" name="deskripsi_kategori" rows="3" 
                                          placeholder="Deskripsi kategori tanding"></textarea>
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-plus"></i> Tambah Kategori
                            </button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-list"></i> Daftar Kategori Tanding</h3>
                        <?php if (empty($categories)): ?>
                            <div class="empty-state">
                                <i class="fas fa-list"></i>
                                <p>Belum ada kategori tanding</p>
                                <small>Tambahkan kategori tanding seperti putra, putri, atau berdasarkan berat badan</small>
                            </div>
                        <?php else: ?>
                            <div class="category-grid">
                                <?php foreach ($categories as $cat): ?>
                                <div class="category-card">
                                    <div class="category-header">
                                        <h4><?php echo htmlspecialchars($cat['nama_kategori']); ?></h4>
                                        <button onclick="deleteCategory(<?php echo $cat['id']; ?>)" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="category-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Jenis Kelamin:</span>
                                            <span class="detail-value">
                                                <?php 
                                                switch($cat['jenis_kelamin']) {
                                                    case 'L': echo 'Putra'; break;
                                                    case 'P': echo 'Putri'; break;
                                                    case 'Campuran': echo 'Campuran'; break;
                                                    default: echo 'Tidak ditentukan';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <?php if ($cat['age_category_name']): ?>
                                            <div class="detail-item">
                                                <span class="detail-label">Kategori Umur:</span>
                                                <span class="detail-value"><?php echo htmlspecialchars($cat['age_category_name']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($cat['berat_min'] || $cat['berat_max']): ?>
                                            <div class="detail-item">
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
                                            <div class="detail-item">
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
                </div>
            </div>

            <!-- Age Categories Tab -->
            <div id="age-categories-tab" class="tab-content">
                <div style="padding: 30px;">
                    <div class="form-section">
                        <h3><i class="fas fa-plus"></i> Tambah Kategori Umur</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_age_category">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end;">
                                <div class="form-group">
                                    <label for="nama_kategori_umur">Nama Kategori *</label>
                                    <input type="text" id="nama_kategori_umur" name="nama_kategori_umur" required 
                                           placeholder="Junior, Senior, dll">
                                </div>
                                <div class="form-group">
                                    <label for="usia_min">Usia Minimum *</label>
                                    <input type="number" id="usia_min" name="usia_min" required min="1" max="100">
                                </div>
                                <div class="form-group">
                                    <label for="usia_max">Usia Maksimum *</label>
                                    <input type="number" id="usia_max" name="usia_max" required min="1" max="100">
                                </div>
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-plus"></i> Tambah
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-calendar"></i> Daftar Kategori Umur</h3>
                        <?php if (empty($age_categories)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar"></i>
                                <p>Belum ada kategori umur</p>
                                <small>Tambahkan kategori umur seperti junior, senior, atau dewasa</small>
                            </div>
                        <?php else: ?>
                            <div class="age-category-grid">
                                <?php foreach ($age_categories as $age_cat): ?>
                                <div class="age-category-card">
                                    <div class="age-category-header">
                                        <h4><?php echo htmlspecialchars($age_cat['nama_kategori']); ?></h4>
                                        <button onclick="deleteAgeCategory(<?php echo $age_cat['id']; ?>)" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="age-range">
                                        <span class="age-badge"><?php echo $age_cat['usia_min']; ?> - <?php echo $age_cat['usia_max']; ?> tahun</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Competition Types Tab -->
            <div id="competition-types-tab" class="tab-content">
                <div style="padding: 30px;">
                    <div class="form-section">
                        <h3><i class="fas fa-plus"></i> Tambah Jenis Kompetisi</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="add_competition_type">
                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                                <div class="form-group">
                                    <label for="nama_kompetisi">Nama Kompetisi *</label>
                                    <input type="text" id="nama_kompetisi" name="nama_kompetisi" required 
                                           placeholder="Tanding, Tunggal, Ganda, dll">
                                </div>
                                <div class="form-group">
                                    <label for="jenis_kelamin">Jenis Kelamin *</label>
                                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L">Putra (Laki-laki)</option>
                                        <option value="P">Putri (Perempuan)</option>
                                        <option value="Campuran">Campuran</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="biaya_pendaftaran">Biaya Pendaftaran (Rp)</label>
                                    <input type="number" id="biaya_pendaftaran" name="biaya_pendaftaran" 
                                           min="0" placeholder="0">
                                    <small>Kosongkan atau isi 0 jika gratis</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="deskripsi_kompetisi">Deskripsi</label>
                                <textarea id="deskripsi_kompetisi" name="deskripsi_kompetisi" rows="3" 
                                          placeholder="Deskripsi jenis kompetisi"></textarea>
                            </div>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-plus"></i> Tambah Jenis Kompetisi
                            </button>
                        </form>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-star"></i> Daftar Jenis Kompetisi</h3>
                        <?php if (empty($competition_types)): ?>
                            <div class="empty-state">
                                <i class="fas fa-star"></i>
                                <p>Belum ada jenis kompetisi</p>
                                <small>Tambahkan jenis kompetisi seperti tanding, tunggal, ganda, atau regu</small>
                            </div>
                        <?php else: ?>
                            <div class="competition-type-grid">
                                <?php foreach ($competition_types as $comp_type): ?>
                                <div class="competition-type-card">
                                    <div class="competition-type-header">
                                        <h4><?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?></h4>
                                        <button onclick="deleteCompetitionType(<?php echo $comp_type['id']; ?>)" class="btn-action btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="competition-type-details">
                                        <div class="gender-info">
                                            <span class="gender-label">Jenis Kelamin:</span>
                                            <span class="gender-value">
                                                <?php 
                                                switch($comp_type['jenis_kelamin']) {
                                                    case 'L': echo 'Putra'; break;
                                                    case 'P': echo 'Putri'; break;
                                                    case 'Campuran': echo 'Campuran'; break;
                                                    default: echo 'Tidak ditentukan';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <div class="price-info">
                                            <span class="price-label">Biaya:</span>
                                            <span class="price-value">
                                                <?php echo $comp_type['biaya_pendaftaran'] > 0 ? 'Rp ' . number_format($comp_type['biaya_pendaftaran'], 0, ',', '.') : 'Gratis'; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($comp_type['deskripsi'])): ?>
                                            <div class="description">
                                                <?php echo nl2br(htmlspecialchars($comp_type['deskripsi'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <script>
        function showTab(tabName, btn) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            if (btn) {
                btn.classList.add('active');
            }
        }

        function deleteContact(id) {
            if (confirm('Yakin ingin menghapus kontak ini?')) {
                window.location.href = 'delete-item.php?type=contact&id=' + id + '&competition_id=<?php echo $competition_id; ?>';
            }
        }

        function deleteDocument(id) {
            if (confirm('Yakin ingin menghapus dokumen ini?')) {
                window.location.href = 'delete-item.php?type=document&id=' + id + '&competition_id=<?php echo $competition_id; ?>';
            }
        }

        function deleteCategory(id) {
            if (confirm('Yakin ingin menghapus kategori ini?')) {
                window.location.href = 'delete-item.php?type=category&id=' + id + '&competition_id=<?php echo $competition_id; ?>';
            }
        }

        function deleteAgeCategory(id) {
            if (confirm('Yakin ingin menghapus kategori umur ini?')) {
                window.location.href = 'delete-item.php?type=age_category&id=' + id + '&competition_id=<?php echo $competition_id; ?>';
            }
        }

        function deleteCompetitionType(id) {
            if (confirm('Yakin ingin menghapus jenis kompetisi ini?')) {
                window.location.href = 'delete-item.php?type=competition_type&id=' + id + '&competition_id=<?php echo $competition_id; ?>';
            }
        }

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => alert.style.display = 'none');
        }, 5000);
    </script>

    <style>
        /* Match the modern tab styles from detail page */
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
            z-index: 0; /* keep behind content so text stays visible */
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
            z-index: 0; /* ripple below content */
        }

        .tab-btn i,
        .tab-btn span { position: relative; z-index: 2; }
        .tab-btn.active span { color: #ffffff !important; }
        .tab-btn.active i { color: #ffffff !important; }

        .tab-btn:hover {
            color: #0ea5e9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.2);
        }

        .tab-btn:hover::before { opacity: 0.1; transform: scale(1); }
        .tab-btn:hover i { transform: scale(1.15) rotate(5deg); color: #0ea5e9; }
        .tab-btn:hover span { transform: translateX(2px); }

        .tab-btn:active { transform: translateY(0); transition: all 0.1s ease; }
        .tab-btn:active::after { width: 300px; height: 300px; opacity: 0; }

        .tab-btn.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white; /* ensure text is white on active */
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
            animation: tabActivePulse 0.6s ease-out;
        }

        .tab-btn.active::before { opacity: 1; transform: scale(1); }
        .tab-btn.active i { transform: scale(1.2); animation: iconBounce 0.6s ease-out; color: #ffffff; }
        .tab-btn.active span { animation: textSlide 0.4s ease-out; }

        @keyframes tabActivePulse {
            0% { box-shadow: 0 4px 16px rgba(14, 165, 233, 0.25); transform: translateY(-1px); }
            50% { box-shadow: 0 12px 35px rgba(14, 165, 233, 0.4); transform: translateY(-3px); }
            100% { box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3); transform: translateY(-2px); }
        }

        @keyframes iconBounce {
            0% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.3) rotate(10deg); }
            100% { transform: scale(1.2) rotate(0deg); }
        }

        @keyframes textSlide {
            0% { transform: translateX(-5px); opacity: 0.8; }
            100% { transform: translateX(0); opacity: 1; }
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
        }
        
        .form-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
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
        }

        .current-poster {
            margin-bottom: 20px;
            text-align: center;
        }

        .current-poster img {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

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

        .document-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .document-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: var(--light-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .document-icon i {
            color: #dc3545;
            font-size: 2rem;
        }

        .document-info {
            flex: 1;
        }

        .document-info strong {
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .document-info small {
            color: var(--text-light);
            display: block;
            margin-top: 5px;
        }

        .document-actions {
            display: flex;
            gap: 10px;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }

        .category-card {
            background: var(--light-color);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border-color);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .category-header h4 {
            color: var(--primary-color);
            margin: 0;
        }

        .category-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail-item {
            display: flex;
            gap: 10px;
        }

        .detail-label {
            font-weight: 600;
            color: var(--text-color);
            min-width: 100px;
        }

        .detail-value {
            color: var(--text-light);
            flex: 1;
        }

        .age-category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .age-category-card {
            background: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .age-category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .age-category-header h4 {
            color: var(--primary-color);
            margin: 0;
        }

        .age-badge {
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .competition-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }

        .competition-type-card {
            background: var(--light-color);
            border-radius: 8px;
            padding: 20px;
            border: 1px solid var(--border-color);
        }

        .competition-type-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .competition-type-header h4 {
            color: var(--primary-color);
            margin: 0;
        }

        .price-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .price-label {
            font-weight: 600;
            color: var(--text-color);
        }

        .price-value {
            color: var(--success-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .description {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.4;
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

        .btn-download {
            background: #4caf50;
            color: white;
        }

        .btn-download:hover {
            background: #45a049;
        }

        .btn-delete {
            background: #f44336;
            color: white;
        }

        .btn-delete:hover {
            background: #d32f2f;
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

            .category-grid,
            .competition-type-grid {
                grid-template-columns: 1fr;
            }

            .age-category-grid {
                grid-template-columns: 1fr;
            }

            .contact-item,
            .document-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .contact-actions,
            .document-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</body>
</html>
