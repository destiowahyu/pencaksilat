<?php
session_start();
require_once 'config/database.php';

// Get competition ID from URL
$competition_id = $_GET['id'] ?? 0;

if (!$competition_id) {
    header('Location: index.php');
    exit();
}

// Get competition details
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND status IN ('active', 'open_regist')");
$stmt->execute([$competition_id]);
$competition = $stmt->fetch();

if (!$competition) {
    header('Location: index.php');
    exit();
}

// Get competition categories
$stmt = $pdo->prepare("SELECT * FROM competition_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$categories = $stmt->fetchAll();

// Get age categories
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$age_categories = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$competition_id]);
$competition_types = $stmt->fetchAll();

// Get registration count
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM registrations WHERE competition_id = ?");
$stmt->execute([$competition_id]);
$registration_count = $stmt->fetch()['total'];

// Get notification
$notification = getNotification();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($competition['nama_perlombaan']); ?> - Detail Perlombaan</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="assets/js/script.js" defer></script>
</head>
<body>
    <?php if ($notification): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showAlert('<?php echo $notification['message']; ?>', '<?php echo $notification['type']; ?>');
        });
    </script>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-fist-raised"></i>
                <span>Pencak Silat Championship</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Beranda</a>
                <a href="index.php#events" class="nav-link">Perlombaan</a>
                <a href="index.php#about" class="nav-link">Tentang</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard/user/index.php" class="btn-login">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                <?php else: ?>
                    <button class="btn-login" onclick="openLoginModal()">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    <button class="btn-register" onclick="openRegisterModal()">
                        <i class="fas fa-user-plus"></i> Daftar
                    </button>
                <?php endif; ?>
            </div>
            <div class="hamburger" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Competition Detail Section -->
    <section class="competition-detail">
        <div class="container">
            <!-- Back Button -->
            <div class="back-button">
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                </a>
            </div>

            <!-- Competition Header -->
            <div class="competition-header">
                <div class="competition-status <?php echo $competition['status']; ?>">
                    <?php
                    switch($competition['status']) {
                        case 'open_regist': echo 'Buka Pendaftaran'; break;
                        case 'close_regist': echo 'Tutup Pendaftaran'; break;
                        case 'coming_soon': echo 'Segera Hadir'; break;
                        default: echo 'Aktif';
                    }
                    ?>
                </div>
                
                <?php if ($competition['poster']): ?>
                <div class="competition-poster">
                    <img src="uploads/posters/<?php echo $competition['poster']; ?>" alt="<?php echo htmlspecialchars($competition['nama_perlombaan']); ?>">
                </div>
                <?php endif; ?>

                <h1><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></h1>
                
                <div class="competition-meta">
                    <?php if ($competition['tanggal_pelaksanaan']): ?>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?php echo date('d M Y', strtotime($competition['tanggal_pelaksanaan'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($competition['lokasi']): ?>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($competition['lokasi']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span><?php echo $registration_count; ?> Peserta Terdaftar</span>
                    </div>
                </div>
            </div>

            <!-- Competition Content -->
            <div class="competition-content">
                <div class="content-grid">
                    <!-- Description -->
                    <div class="content-section">
                        <h2><i class="fas fa-info-circle"></i> Deskripsi Perlombaan</h2>
                        <div class="description">
                            <?php echo nl2br(htmlspecialchars($competition['deskripsi'])); ?>
                        </div>
                    </div>

                    <!-- Competition Types -->
                    <?php if (!empty($competition_types)): ?>
                    <div class="content-section">
                        <h2><i class="fas fa-trophy"></i> Jenis Kompetisi</h2>
                        <div class="types-grid">
                            <?php foreach ($competition_types as $type): ?>
                            <div class="type-card">
                                <h3><?php echo htmlspecialchars($type['nama_kompetisi']); ?></h3>
                                <?php if ($type['deskripsi']): ?>
                                <p><?php echo htmlspecialchars($type['deskripsi']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Age Categories -->
                    <?php if (!empty($age_categories)): ?>
                    <div class="content-section">
                        <h2><i class="fas fa-calendar-alt"></i> Kategori Umur</h2>
                        <div class="categories-grid">
                            <?php foreach ($age_categories as $age_cat): ?>
                            <div class="category-card">
                                <h3><?php echo htmlspecialchars($age_cat['nama_kategori']); ?></h3>
                                <p>Usia: <?php echo $age_cat['usia_min']; ?> - <?php echo $age_cat['usia_max']; ?> tahun</p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Competition Categories -->
                    <?php if (!empty($categories)): ?>
                    <div class="content-section">
                        <h2><i class="fas fa-weight"></i> Kategori Tanding</h2>
                        <div class="categories-grid">
                            <?php foreach ($categories as $category): ?>
                            <div class="category-card">
                                <h3><?php echo htmlspecialchars($category['nama_kategori']); ?></h3>
                                <?php if ($category['berat_min'] && $category['berat_max']): ?>
                                <p>Berat: <?php echo $category['berat_min']; ?> - <?php echo $category['berat_max']; ?> kg</p>
                                <?php endif; ?>
                                <p>Jenis Kelamin: <?php echo $category['jenis_kelamin']; ?></p>
                                <?php if ($category['deskripsi']): ?>
                                <small><?php echo htmlspecialchars($category['deskripsi']); ?></small>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Registration Info -->
                    <div class="content-section">
                        <h2><i class="fas fa-clipboard-list"></i> Informasi Pendaftaran</h2>
                        <div class="registration-info">
                            <?php if (isset($competition['tanggal_open_regist']) && $competition['tanggal_open_regist']): ?>
                            <div class="info-item">
                                <i class="fas fa-calendar-plus"></i>
                                <div>
                                    <strong>Pendaftaran Dibuka:</strong>
                                    <span><?php echo date('d M Y', strtotime($competition['tanggal_open_regist'])); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (isset($competition['tanggal_close_regist']) && $competition['tanggal_close_regist']): ?>
                            <div class="info-item">
                                <i class="fas fa-calendar-times"></i>
                                <div>
                                    <strong>Pendaftaran Ditutup:</strong>
                                    <span><?php echo date('d M Y', strtotime($competition['tanggal_close_regist'])); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <strong>Total Peserta:</strong>
                                    <span><?php echo $registration_count; ?> orang</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <?php if ($competition['whatsapp_group'] || $competition['maps_link']): ?>
                    <div class="content-section">
                        <h2><i class="fas fa-phone"></i> Informasi Kontak</h2>
                        <div class="contact-info">
                            <?php if ($competition['whatsapp_group']): ?>
                            <div class="contact-item">
                                <i class="fab fa-whatsapp"></i>
                                <div>
                                    <strong>Grup WhatsApp:</strong>
                                    <a href="<?php echo htmlspecialchars($competition['whatsapp_group']); ?>" target="_blank" class="whatsapp-link">
                                        Bergabung dengan Grup
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($competition['maps_link']): ?>
                            <div class="contact-item">
                                <i class="fas fa-map"></i>
                                <div>
                                    <strong>Lokasi:</strong>
                                    <a href="<?php echo htmlspecialchars($competition['maps_link']); ?>" target="_blank" class="maps-link">
                                        Lihat di Google Maps
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <?php if ($competition['status'] === 'open_regist'): ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="dashboard/user/daftar-perlombaan.php?competition_id=<?php echo $competition['id']; ?>" class="btn-primary">
                                <i class="fas fa-user-plus"></i> Daftar Sekarang
                            </a>
                        <?php else: ?>
                            <button class="btn-primary" onclick="openRegisterModal()">
                                <i class="fas fa-user-plus"></i> Daftar Sekarang
                            </button>
                        <?php endif; ?>
                    <?php elseif ($competition['status'] === 'close_regist'): ?>
                        <button class="btn-secondary" disabled>
                            <i class="fas fa-lock"></i> Pendaftaran Ditutup
                        </button>
                    <?php else: ?>
                        <button class="btn-secondary" disabled>
                            <i class="fas fa-clock"></i> Segera Hadir
                        </button>
                    <?php endif; ?>
                    
                    <a href="index.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Login</h2>
                <span class="close" onclick="closeLoginModal()">&times;</span>
            </div>
            <form id="loginForm" method="POST" action="auth/login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary full-width">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>Daftar Akun Baru</h2>
                <span class="close" onclick="closeRegisterModal()">&times;</span>
            </div>
            <form id="registerForm" method="POST" action="auth/register.php">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3><i class="fas fa-user"></i> Informasi Pribadi</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="reg_nama">Nama Lengkap *</label>
                            <input type="text" id="reg_nama" name="nama" required placeholder="Masukkan nama lengkap">
                        </div>
                        <div class="form-group">
                            <label for="reg_email">Email *</label>
                            <input type="email" id="reg_email" name="email" required placeholder="contoh@email.com">
                            <small>Email akan digunakan untuk notifikasi penting</small>
                        </div>
                        <div class="form-group">
                            <label for="reg_whatsapp">No. WhatsApp *</label>
                            <input type="tel" id="reg_whatsapp" name="whatsapp" required placeholder="08xxxxxxxxx">
                            <small>Format: 08xxxxxxxxx (akan digunakan untuk notifikasi)</small>
                        </div>
                        <div class="form-group">
                            <label for="reg_alamat">Alamat Lengkap *</label>
                            <textarea id="reg_alamat" name="alamat" rows="3" required placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Kontingen Information -->
                <div class="form-section">
                    <h3><i class="fas fa-flag"></i> Informasi Kontingen</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="reg_nama_kontingen">Nama Kontingen *</label>
                            <input type="text" id="reg_nama_kontingen" name="nama_kontingen" required placeholder="Contoh: Kontingen Jakarta Pusat">
                            <small>Nama kontingen yang akan mewakili daerah Anda</small>
                        </div>
                        <div class="form-group">
                            <label for="reg_provinsi">Provinsi *</label>
                            <select id="reg_provinsi" name="provinsi" required>
                                <option value="">Pilih Provinsi</option>
                                <?php foreach (getProvinces() as $province): ?>
                                <option value="<?php echo $province; ?>"><?php echo $province; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reg_kota">Kota/Kabupaten *</label>
                            <input type="text" id="reg_kota" name="kota" required placeholder="Masukkan nama kota/kabupaten">
                        </div>
                    </div>
                </div>

                <!-- Account Security -->
                <div class="form-section">
                    <h3><i class="fas fa-lock"></i> Keamanan Akun</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="reg_password">Password *</label>
                            <input type="password" id="reg_password" name="password" required placeholder="Minimal 6 karakter">
                            <small>Gunakan kombinasi huruf, angka, dan simbol untuk keamanan</small>
                        </div>
                        <div class="form-group">
                            <label for="reg_confirm_password">Konfirmasi Password *</label>
                            <input type="password" id="reg_confirm_password" name="confirm_password" required placeholder="Ulangi password">
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="form-section">
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="reg_terms" required>
                            <span class="checkmark"></span>
                            Saya menyetujui <a href="#" onclick="showTerms()">syarat dan ketentuan</a> yang berlaku
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="reg_newsletter">
                            <span class="checkmark"></span>
                            Saya ingin menerima notifikasi email dan WhatsApp tentang perlombaan terbaru
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-primary full-width">
                    <i class="fas fa-user-plus"></i> Daftar Akun
                </button>
                
                <div class="form-footer">
                    <p>Sudah punya akun? <a href="#" onclick="closeRegisterModal(); openLoginModal();">Login di sini</a></p>
                </div>
            </form>
        </div>
    </div>

    <style>
        .competition-detail {
            padding: 8rem 2rem 4rem 2rem;
            background: var(--cool-gray);
            min-height: 100vh;
        }

        .competition-detail .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-button {
            margin-bottom: 2rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--charcoal);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(58,134,255,0.1);
        }

        .btn-back:hover {
            background: var(--primary);
            color: var(--white);
            transform: translateY(-2px);
        }

        .competition-header {
            background: var(--white);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(58,134,255,0.1);
            position: relative;
        }

        .competition-status {
            position: absolute;
            top: 2rem;
            right: 2rem;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 700;
            color: var(--white);
            white-space: nowrap;
            z-index: 10;
        }

        .competition-status.open_regist {
            background: #10b981;
        }

        .competition-status.close_regist {
            background: var(--accent-red);
        }

        .competition-status.coming_soon {
            background: var(--accent-yellow);
            color: var(--charcoal);
        }

        .competition-status.active {
            background: var(--primary);
        }

        .competition-poster {
            margin-bottom: 2rem;
            border-radius: 16px;
            overflow: hidden;
            max-width: 400px;
        }

        .competition-poster img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .competition-header h1 {
            font-size: 3rem;
            font-weight: 700;
            color: var(--charcoal);
            margin-bottom: 2rem;
            margin-right: 200px;
            line-height: 1.2;
        }

        .competition-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--charcoal);
            font-size: 1.1rem;
            font-weight: 500;
        }

        .meta-item i {
            color: var(--primary);
            font-size: 1.25rem;
        }

        .competition-content {
            background: var(--white);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 10px 30px rgba(58,134,255,0.1);
        }

        .content-grid {
            display: grid;
            gap: 3rem;
        }

        .content-section h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--charcoal);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .content-section h2 i {
            color: var(--primary);
        }

        .description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--charcoal);
        }

        .types-grid, .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .type-card, .category-card {
            background: var(--cool-gray);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .type-card:hover, .category-card:hover {
            background: var(--lavender);
            transform: translateY(-2px);
        }

        .type-card h3, .category-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--charcoal);
            margin-bottom: 0.75rem;
        }

        .type-card p, .category-card p {
            color: var(--charcoal);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .category-card small {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .registration-info, .contact-info {
            display: grid;
            gap: 1.5rem;
        }

        .info-item, .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--cool-gray);
            border-radius: 12px;
        }

        .info-item i, .contact-item i {
            color: var(--primary);
            font-size: 1.5rem;
            width: 30px;
            text-align: center;
        }

        .info-item div, .contact-item div {
            flex: 1;
        }

        .info-item strong, .contact-item strong {
            display: block;
            color: var(--charcoal);
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .info-item span {
            color: #6b7280;
        }

        .whatsapp-link, .maps-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .whatsapp-link:hover, .maps-link:hover {
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--cool-gray);
            flex-wrap: wrap;
        }

        .action-buttons .btn-primary, .action-buttons .btn-secondary {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .action-buttons .btn-primary:disabled, .action-buttons .btn-secondary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Specific styling for disabled buttons */
        .action-buttons .btn-secondary:disabled {
            background: #6b7280 !important;
            color: var(--white) !important;
            border: 2px solid #6b7280 !important;
        }

        .action-buttons .btn-secondary:not(:disabled) {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .action-buttons .btn-secondary:not(:disabled):hover {
            background: var(--primary);
            color: var(--white);
        }

        /* Styling for back button */
        .action-buttons .btn-secondary[href] {
            background: var(--cool-gray) !important;
            color: var(--charcoal) !important;
            border: 2px solid var(--cool-gray) !important;
            text-decoration: none;
        }

        .action-buttons .btn-secondary[href]:hover {
            background: var(--charcoal) !important;
            color: var(--white) !important;
            border: 2px solid var(--charcoal) !important;
        }

        @media (max-width: 768px) {
            .competition-header {
                padding: 2rem;
            }

            .competition-header h1 {
                font-size: 2rem;
                margin-right: 0;
            }

            .competition-status {
                position: static;
                margin-bottom: 1rem;
                align-self: flex-start;
            }

            .competition-meta {
                flex-direction: column;
                gap: 1rem;
            }

            .competition-content {
                padding: 2rem;
            }

            .types-grid, .categories-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn-primary, .action-buttons .btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <script>
        // Include the same modal functions from index.php
        function openLoginModal() {
            document.getElementById('loginModal').style.display = 'block';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').style.display = 'none';
        }

        function openRegisterModal() {
            document.getElementById('registerModal').style.display = 'block';
        }

        function closeRegisterModal() {
            document.getElementById('registerModal').style.display = 'none';
        }

        function showTerms() {
            alert('Syarat dan ketentuan akan ditampilkan di sini.');
        }

        function toggleMobileMenu() {
            const navMenu = document.querySelector('.nav-menu');
            const hamburger = document.querySelector('.hamburger');
            
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const loginModal = document.getElementById('loginModal');
            const registerModal = document.getElementById('registerModal');
            
            if (event.target === loginModal) {
                closeLoginModal();
            }
            if (event.target === registerModal) {
                closeRegisterModal();
            }
        }
    </script>
</body>
</html>
