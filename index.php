<?php
session_start();
require_once 'config/database.php';

// Get active competitions
$stmt = $pdo->query("SELECT * FROM competitions WHERE status IN ('active', 'open_regist') ORDER BY created_at DESC");
$competitions = $stmt->fetchAll();

// Get notification
$notification = getNotification();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pendaftaran Pencak Silat</title>
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
                <a href="#home" class="nav-link">Beranda</a>
                <a href="#events" class="nav-link">Perlombaan</a>
                <a href="#about" class="nav-link">Tentang</a>
                <button class="btn-login" onclick="openLoginModal()">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button class="btn-register" onclick="openRegisterModal()">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
            </div>
            <div class="hamburger" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Sistem Pendaftaran Pencak Silat</h1>
            <p>Platform terpadu untuk pendaftaran dan perangkingan perlombaan pencak silat tingkat nasional</p>
            <div class="hero-buttons">
                <button class="btn-primary" onclick="openRegisterModal()">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
                <button class="btn-secondary" onclick="openLoginModal()">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </div>
        </div>
        <div class="hero-image">
            <img src="public/placeholder-logo.svg" alt="Pencak Silat Championship" />
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="events">
        <div class="container">
            <h2>Perlombaan Aktif</h2>
            <div class="events-grid">
                <?php foreach ($competitions as $competition): ?>
                <div class="event-card" onclick="window.location.href='competition-detail.php?id=<?php echo $competition['id']; ?>'">
                    <div class="event-status <?php echo $competition['status']; ?>">
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
                    <div class="event-poster">
                        <img src="uploads/posters/<?php echo $competition['poster']; ?>" alt="<?php echo htmlspecialchars($competition['nama_perlombaan']); ?>">
                    </div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></h3>
                    <?php if ($competition['tanggal_pelaksanaan']): ?>
                    <p class="event-date">
                        <i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($competition['tanggal_pelaksanaan'])); ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($competition['lokasi']): ?>
                    <p class="event-location">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($competition['lokasi']); ?>
                    </p>
                    <?php endif; ?>
                    <p class="event-description">
                        <?php echo htmlspecialchars(substr($competition['deskripsi'], 0, 120)) . (strlen($competition['deskripsi']) > 120 ? '...' : ''); ?>
                    </p>
                    <div class="event-actions" style="margin-top: 18px; display: flex; gap: 10px;" onclick="event.stopPropagation()">
                        <a href="competition-detail.php?id=<?php echo $competition['id']; ?>" class="btn-secondary">
                            <i class="fas fa-info-circle"></i> Detail
                        </a>
                        <?php if ($competition['status'] === 'open_regist'): ?>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="dashboard/user/daftar-perlombaan.php?competition_id=<?php echo $competition['id']; ?>" class="btn-primary">
                                    <i class="fas fa-user-plus"></i> Daftar
                                </a>
                            <?php else: ?>
                                <button class="btn-primary" onclick="event.stopPropagation(); openRegisterModal()">
                                    <i class="fas fa-user-plus"></i> Daftar
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
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

    <!-- Terms Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Syarat dan Ketentuan</h2>
                <span class="close" onclick="closeTermsModal()">&times;</span>
            </div>
            <div class="terms-content">
                <h3>1. Ketentuan Umum</h3>
                <p>Dengan mendaftar di sistem ini, Anda menyetujui untuk mematuhi semua aturan dan regulasi yang berlaku dalam perlombaan pencak silat.</p>
                
                <h3>2. Data Pribadi</h3>
                <p>Data pribadi yang Anda berikan akan digunakan untuk keperluan administrasi perlombaan dan tidak akan disebarluaskan kepada pihak ketiga tanpa persetujuan.</p>
                
                <h3>3. Notifikasi</h3>
                <p>Sistem akan mengirimkan notifikasi melalui email dan WhatsApp terkait informasi perlombaan, pembayaran, dan jadwal pertandingan.</p>
                
                <h3>4. Tanggung Jawab</h3>
                <p>Peserta bertanggung jawab atas keakuratan data yang dimasukkan dan kelengkapan dokumen yang diperlukan.</p>
                
                <h3>5. Pembayaran</h3>
                <p>Pembayaran pendaftaran harus dilakukan sesuai dengan instruksi yang diberikan dan dalam batas waktu yang ditentukan.</p>
                
                <div style="text-align: center; margin-top: 30px;">
                    <button class="btn-primary" onclick="acceptTerms()">
                        <i class="fas fa-check"></i> Saya Setuju
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTerms() {
            document.getElementById('termsModal').style.display = 'block';
        }
        
        function closeTermsModal() {
            document.getElementById('termsModal').style.display = 'none';
        }
        
        function acceptTerms() {
            document.getElementById('reg_terms').checked = true;
            closeTermsModal();
        }
        
        // Enhanced form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('reg_password').value;
            const confirmPassword = document.getElementById('reg_confirm_password').value;
            const whatsapp = document.getElementById('reg_whatsapp').value;
            const terms = document.getElementById('reg_terms').checked;
            
            // Password validation
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Password dan konfirmasi password tidak cocok!', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('Password minimal 6 karakter!', 'error');
                return false;
            }
            
            // WhatsApp validation
            const whatsappRegex = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
            if (!whatsappRegex.test(whatsapp)) {
                e.preventDefault();
                showAlert('Format nomor WhatsApp tidak valid! Gunakan format: 08xxxxxxxxx', 'error');
                return false;
            }
            
            // Terms validation
            if (!terms) {
                e.preventDefault();
                showAlert('Anda harus menyetujui syarat dan ketentuan!', 'error');
                return false;
            }
            
            // Show loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';
            submitBtn.disabled = true;
        });
        
        // Real-time WhatsApp format validation
        document.getElementById('reg_whatsapp').addEventListener('input', function(e) {
            let value = e.target.value;
            const whatsappRegex = /^(\+62|62|0)8[1-9][0-9]{6,9}$/;
            
            if (value && !whatsappRegex.test(value)) {
                e.target.style.borderColor = '#ef4444';
                if (!document.getElementById('whatsapp-error')) {
                    const error = document.createElement('small');
                    error.id = 'whatsapp-error';
                    error.style.color = '#ef4444';
                    error.textContent = 'Format tidak valid. Contoh: 08123456789';
                    e.target.parentNode.appendChild(error);
                }
            } else {
                e.target.style.borderColor = '#10b981';
                const error = document.getElementById('whatsapp-error');
                if (error) error.remove();
            }
        });
        
        // Real-time password strength indicator
        document.getElementById('reg_password').addEventListener('input', function(e) {
            const password = e.target.value;
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            let strengthText = '';
            let strengthColor = '';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthText = 'Lemah';
                    strengthColor = '#ef4444';
                    break;
                case 2:
                    strengthText = 'Sedang';
                    strengthColor = '#f59e0b';
                    break;
                case 3:
                    strengthText = 'Kuat';
                    strengthColor = '#10b981';
                    break;
                case 4:
                    strengthText = 'Sangat Kuat';
                    strengthColor = '#059669';
                    break;
            }
            
            let indicator = document.getElementById('password-strength');
            if (!indicator && password) {
                indicator = document.createElement('small');
                indicator.id = 'password-strength';
                e.target.parentNode.appendChild(indicator);
            }
            
            if (indicator && password) {
                indicator.textContent = `Kekuatan password: ${strengthText}`;
                indicator.style.color = strengthColor;
            } else if (indicator) {
                indicator.remove();
            }
        });
    </script>

    <style>
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-section:last-of-type {
            border-bottom: none;
        }
        
        .form-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group small {
            color: var(--text-light);
            font-size: 0.85rem;
            margin-top: 5px;
            display: block;
        }
        
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            line-height: 1.4;
        }
        
        .checkbox-label input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .form-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .terms-content {
            padding: 30px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .terms-content h3 {
            color: var(--primary-color);
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .terms-content p {
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                max-width: 95% !important;
                margin: 5% auto !important;
            }
        }
    </style>
</body>
</html>
