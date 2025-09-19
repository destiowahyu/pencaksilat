<?php
// Database configuration
$host = 'localhost';
$dbname = 'pencak_silat_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Email configuration
$email_config = [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_username' => 'your-email@gmail.com', // Ganti dengan email Anda
    'smtp_password' => 'your-app-password',     // Ganti dengan app password Gmail
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'Sistem Pencak Silat'
];

// WhatsApp API configuration (menggunakan service seperti Fonnte, Wablas, dll)
$whatsapp_config = [
    'api_url' => 'https://api.fonnte.com/send', // Contoh menggunakan Fonnte
    'api_token' => 'your-fonnte-token',         // Ganti dengan token API Anda
];

// Helper functions
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateId($prefix = '') {
    return $prefix . strtoupper(uniqid());
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim((string)($input ?? ''))));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function uploadFile($file, $uploadDir = 'uploads/') {
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    $fileName = time() . '_' . uniqid() . '.' . $fileExtension;
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }
    
    return false;
}

function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

function formatDate($date, $format = 'd M Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

function generateExcel($data, $headers, $filename) {
    // Use ExcelHelper for proper Excel generation
    require_once __DIR__ . '/../vendor/autoload.php';
    
    \App\ExcelHelper::createAndDownloadExcel($data, $headers, $filename);
}

function sendNotification($message, $type = 'info') {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}

function getNotification() {
    if (isset($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        unset($_SESSION['notification']);
        return $notification;
    }
    return null;
}

// Email functions
function sendWelcomeEmail($to_email, $nama, $nama_kontingen) {
    global $email_config;
    
    try {
        // Menggunakan PHPMailer (install via composer: composer require phpmailer/phpmailer)
        // Atau bisa menggunakan mail() function sederhana
        
        $subject = "Selamat Datang di Sistem Pencak Silat";
        $message = "
        <html>
        <head>
            <title>Selamat Datang</title>
        </head>
        <body>
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: #2563eb; color: white; padding: 20px; text-align: center;'>
                    <h1>🥋 Sistem Pencak Silat</h1>
                </div>
                <div style='padding: 30px; background: #f8fafc;'>
                    <h2>Selamat Datang, {$nama}!</h2>
                    <p>Terima kasih telah mendaftar di Sistem Pencak Silat. Akun Anda telah berhasil dibuat dengan detail berikut:</p>
                    
                    <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3>Detail Akun:</h3>
                        <p><strong>Nama:</strong> {$nama}</p>
                        <p><strong>Email:</strong> {$to_email}</p>
                        <p><strong>Kontingen:</strong> {$nama_kontingen}</p>
                    </div>
                    
                    <p>Anda sekarang dapat:</p>
                    <ul>
                        <li>Mengelola data atlet</li>
                        <li>Mendaftarkan atlet ke perlombaan</li>
                        <li>Memantau status pembayaran</li>
                        <li>Melihat hasil pertandingan</li>
                    </ul>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/pencak-silat/index.php' style='background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; display: inline-block;'>
                            Login Sekarang
                        </a>
                    </div>
                    
                    <p style='color: #6b7280; font-size: 14px;'>
                        Jika Anda memiliki pertanyaan, silakan hubungi kami melalui email ini atau WhatsApp support kami.
                    </p>
                </div>
                <div style='background: #374151; color: white; padding: 20px; text-align: center; font-size: 14px;'>
                    <p>&copy; 2024 Sistem Pencak Silat. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$email_config['from_name']} <{$email_config['from_email']}>" . "\r\n";
        
        return mail($to_email, $subject, $message, $headers);
        
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

// WhatsApp functions
function sendWhatsAppNotification($phone, $nama, $nama_kontingen) {
    global $whatsapp_config;
    
    try {
        // Format nomor WhatsApp (hapus 0 di depan, tambah 62)
        $phone = preg_replace('/^0/', '62', $phone);
        
        $message = "🥋 *SELAMAT DATANG DI SISTEM PENCAK SILAT* 🥋\n\n";
        $message .= "Halo *{$nama}*!\n\n";
        $message .= "Akun Anda telah berhasil dibuat dengan detail:\n";
        $message .= "👤 *Nama:* {$nama}\n";
        $message .= "🏛️ *Kontingen:* {$nama_kontingen}\n\n";
        $message .= "Anda sekarang dapat:\n";
        $message .= "✅ Mengelola data atlet\n";
        $message .= "✅ Mendaftarkan atlet ke perlombaan\n";
        $message .= "✅ Memantau status pembayaran\n";
        $message .= "✅ Melihat hasil pertandingan\n\n";
        $message .= "🔗 Login: http://localhost/pencak-silat/index.php\n\n";
        $message .= "Terima kasih telah bergabung! 🙏";
        
        $data = [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62'
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $whatsapp_config['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $whatsapp_config['api_token'],
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return $httpCode == 200;
        
    } catch (Exception $e) {
        error_log("WhatsApp error: " . $e->getMessage());
        return false;
    }
}

// Get Indonesian provinces
function getProvinces() {
    return [
        'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi',
        'Sumatera Selatan', 'Bangka Belitung', 'Bengkulu', 'Lampung', 'DKI Jakarta',
        'Jawa Barat', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur', 'Banten',
        'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur', 'Kalimantan Barat',
        'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
        'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara',
        'Gorontalo', 'Sulawesi Barat', 'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat',
        'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'
    ];
}

// Send registration confirmation email
function sendRegistrationConfirmation($user_email, $competition_name, $athlete_name) {
    global $email_config;
    
    $subject = "Konfirmasi Pendaftaran - {$competition_name}";
    $message = "
    <html>
    <head>
        <title>Konfirmasi Pendaftaran</title>
    </head>
    <body>
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #10b981; color: white; padding: 20px; text-align: center;'>
                <h1>🏆 Konfirmasi Pendaftaran</h1>
            </div>
            <div style='padding: 30px; background: #f8fafc;'>
                <h2>Pendaftaran Berhasil!</h2>
                <p>Atlet <strong>{$athlete_name}</strong> telah berhasil didaftarkan ke perlombaan:</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981;'>
                    <h3>{$competition_name}</h3>
                </div>
                
                <p><strong>Langkah selanjutnya:</strong></p>
                <ol>
                    <li>Lakukan pembayaran sesuai instruksi</li>
                    <li>Upload bukti pembayaran</li>
                    <li>Tunggu verifikasi dari panitia</li>
                    <li>Pantau jadwal pertandingan</li>
                </ol>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/pencak-silat/dashboard/user/perlombaan.php' style='background: #10b981; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; display: inline-block;'>
                        Lihat Status Pendaftaran
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: {$email_config['from_name']} <{$email_config['from_email']}>" . "\r\n";
    
    return mail($user_email, $subject, $message, $headers);
}

// Send payment confirmation WhatsApp
function sendPaymentConfirmationWhatsApp($phone, $competition_name, $athlete_name, $amount) {
    global $whatsapp_config;
    
    try {
        $phone = preg_replace('/^0/', '62', $phone);
        
        $message = "💰 *KONFIRMASI PEMBAYARAN* 💰\n\n";
        $message .= "Pembayaran untuk pendaftaran telah diterima!\n\n";
        $message .= "📋 *Detail:*\n";
        $message .= "🏆 Perlombaan: {$competition_name}\n";
        $message .= "👤 Atlet: {$athlete_name}\n";
        $message .= "💵 Jumlah: Rp " . number_format($amount, 0, ',', '.') . "\n\n";
        $message .= "✅ Status: *TERVERIFIKASI*\n\n";
        $message .= "Selamat! Atlet Anda telah resmi terdaftar.\n";
        $message .= "Pantau terus jadwal pertandingan di sistem.\n\n";
        $message .= "🔗 http://localhost/pencak-silat/dashboard/user/perlombaan.php";
        
        $data = [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62'
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $whatsapp_config['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $whatsapp_config['api_token'],
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return $httpCode == 200;
        
    } catch (Exception $e) {
        error_log("WhatsApp error: " . $e->getMessage());
        return false;
    }
}

// Send user update notification
function sendUserUpdateNotification($email, $whatsapp, $nama) {
    global $email_config, $whatsapp_config;
    
    // Send email notification
    $subject = "Informasi Akun Anda Telah Diperbarui";
    $message = "
    <html>
    <head>
        <title>Update Akun</title>
    </head>
    <body>
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background: #f59e0b; color: white; padding: 20px; text-align: center;'>
                <h1>🔄 Update Informasi Akun</h1>
            </div>
            <div style='padding: 30px; background: #f8fafc;'>
                <h2>Halo {$nama},</h2>
                <p>Informasi akun Anda telah diperbarui oleh administrator sistem.</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b;'>
                    <p><strong>Jika Anda tidak melakukan perubahan ini, segera hubungi administrator.</strong></p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/pencak-silat/dashboard/user/akun-saya.php' style='background: #f59e0b; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; display: inline-block;'>
                        Cek Akun Saya
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: {$email_config['from_name']} <{$email_config['from_email']}>" . "\r\n";
    
    $email_sent = mail($email, $subject, $message, $headers);
    
    // Send WhatsApp notification
    try {
        $phone = preg_replace('/^0/', '62', $whatsapp);
        
        $whatsapp_message = "🔄 *UPDATE INFORMASI AKUN* 🔄\n\n";
        $whatsapp_message .= "Halo *{$nama}*!\n\n";
        $whatsapp_message .= "Informasi akun Anda telah diperbarui oleh administrator sistem.\n\n";
        $whatsapp_message .= "⚠️ *Penting:* Jika Anda tidak melakukan perubahan ini, segera hubungi administrator.\n\n";
        $whatsapp_message .= "🔗 Cek akun: http://localhost/pencak-silat/dashboard/user/akun-saya.php";
        
        $data = [
            'target' => $phone,
            'message' => $whatsapp_message,
            'countryCode' => '62'
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $whatsapp_config['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $whatsapp_config['api_token'],
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $whatsapp_sent = $httpCode == 200;
        
    } catch (Exception $e) {
        error_log("WhatsApp error: " . $e->getMessage());
        $whatsapp_sent = false;
    }
    
    return $email_sent || $whatsapp_sent;
}

// Additional helper functions for the new features

// Format currency for Indonesian Rupiah
function formatRupiahCurrency($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Calculate age from birth date
function getAgeFromBirthDate($birthDate) {
    $today = new DateTime();
    $birth = new DateTime($birthDate);
    return $today->diff($birth)->y;
}

// Generate unique filename for uploads
function generateUniqueFilename($originalName, $prefix = '') {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return $prefix . time() . '_' . uniqid() . '.' . $extension;
}

// Validate file upload
function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $maxSize = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        return false;
    }
    
    if ($file['size'] > $maxSize) {
        return false;
    }
    
    return true;
}

// Send registration success notification
function sendRegistrationSuccessNotification($user_email, $user_whatsapp, $user_name, $competition_name, $athlete_name) {
    // Send email
    sendRegistrationConfirmation($user_email, $competition_name, $athlete_name);
    
    // Send WhatsApp notification
    try {
        global $whatsapp_config;
        
        $phone = preg_replace('/^0/', '62', $user_whatsapp);
        
        $message = "🏆 *PENDAFTARAN BERHASIL* 🏆\n\n";
        $message .= "Halo *{$user_name}*!\n\n";
        $message .= "Atlet *{$athlete_name}* telah berhasil didaftarkan ke:\n";
        $message .= "📋 *{$competition_name}*\n\n";
        $message .= "Langkah selanjutnya:\n";
        $message .= "1️⃣ Lakukan pembayaran\n";
        $message .= "2️⃣ Upload bukti pembayaran\n";
        $message .= "3️⃣ Tunggu verifikasi panitia\n\n";
        $message .= "🔗 Cek status: http://localhost/pencak-silat/dashboard/user/pendaftaran-saya.php\n\n";
        $message .= "Terima kasih! 🙏";
        
        $data = [
            'target' => $phone,
            'message' => $message,
            'countryCode' => '62'
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $whatsapp_config['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $whatsapp_config['api_token'],
                'Content-Type: application/json'
            ],
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
    } catch (Exception $e) {
        error_log("WhatsApp notification error: " . $e->getMessage());
    }
}

// Log system activities
function logActivity($user_id, $activity, $details = '') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, activity, details, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $activity, $details]);
    } catch (Exception $e) {
        error_log("Activity log error: " . $e->getMessage());
    }
}

// Check if user has permission
function hasPermission($user_id, $permission) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM user_permissions up 
            JOIN permissions p ON up.permission_id = p.id 
            WHERE up.user_id = ? AND p.name = ?
        ");
        $stmt->execute([$user_id, $permission]);
        return $stmt->fetchColumn() > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Generate random password
function generateRandomPassword($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $password;
}

// Clean old files
function cleanOldFiles($directory, $days = 30) {
    if (!is_dir($directory)) {
        return false;
    }
    
    $files = glob($directory . '/*');
    $cutoff = time() - ($days * 24 * 60 * 60);
    
    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $cutoff) {
            unlink($file);
        }
    }
    
    return true;
}
?>
