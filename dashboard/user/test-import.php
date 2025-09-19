<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    die('Unauthorized');
}

// Get user's kontingen
$stmt = $pdo->prepare("SELECT id, nama_kontingen FROM kontingen WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$userKontingen = $stmt->fetch();

if (!$userKontingen) {
    die('Anda belum memiliki kontingen. Silakan buat kontingen terlebih dahulu.');
}

// Sample test data
$testData = [
    [
        'Nama Kontingen' => $userKontingen['nama_kontingen'],
        'Nama Lengkap' => 'Test Atlet 1',
        'NIK' => '1234567890123456',
        'Jenis Kelamin' => 'L',
        'Tanggal Lahir' => '2005-01-01',
        'Tempat Lahir' => 'Jakarta',
        'Nama Sekolah/Instansi' => 'SMA Test 1',
        'Berat Badan (kg)' => '60.5',
        'Tinggi Badan (cm)' => '170.0'
    ],
    [
        'Nama Kontingen' => $userKontingen['nama_kontingen'],
        'Nama Lengkap' => 'Test Atlet 2',
        'NIK' => '1234567890123457',
        'Jenis Kelamin' => 'P',
        'Tanggal Lahir' => '2006-02-02',
        'Tempat Lahir' => 'Bandung',
        'Nama Sekolah/Instansi' => 'SMA Test 2',
        'Berat Badan (kg)' => '55.0',
        'Tinggi Badan (cm)' => '165.0'
    ]
];

// Test validation logic
echo "<h2>Test Validasi Import Data</h2>";
echo "<p>Kontingen: " . htmlspecialchars($userKontingen['nama_kontingen']) . "</p>";

foreach ($testData as $index => $data) {
    echo "<h3>Test Data " . ($index + 1) . "</h3>";
    echo "<ul>";
    
    // Test NIK
    if (preg_match('/^\d{16}$/', $data['NIK'])) {
        echo "<li style='color: green;'>✓ NIK valid: " . $data['NIK'] . "</li>";
    } else {
        echo "<li style='color: red;'>✗ NIK tidak valid: " . $data['NIK'] . "</li>";
    }
    
    // Test Jenis Kelamin
    if (in_array(strtoupper($data['Jenis Kelamin']), ['L', 'P'])) {
        echo "<li style='color: green;'>✓ Jenis Kelamin valid: " . $data['Jenis Kelamin'] . "</li>";
    } else {
        echo "<li style='color: red;'>✗ Jenis Kelamin tidak valid: " . $data['Jenis Kelamin'] . "</li>";
    }
    
    // Test Tanggal
    $dateFormats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d'];
    $dateValid = false;
    foreach ($dateFormats as $format) {
        $date = DateTime::createFromFormat($format, $data['Tanggal Lahir']);
        if ($date !== false) {
            $dateValid = true;
            break;
        }
    }
    
    if ($dateValid) {
        echo "<li style='color: green;'>✓ Tanggal valid: " . $data['Tanggal Lahir'] . "</li>";
    } else {
        echo "<li style='color: red;'>✗ Tanggal tidak valid: " . $data['Tanggal Lahir'] . "</li>";
    }
    
    // Test Berat Badan
    if (is_numeric($data['Berat Badan (kg)']) && floatval($data['Berat Badan (kg)']) > 0) {
        echo "<li style='color: green;'>✓ Berat Badan valid: " . $data['Berat Badan (kg)'] . "</li>";
    } else {
        echo "<li style='color: red;'>✗ Berat Badan tidak valid: " . $data['Berat Badan (kg)'] . "</li>";
    }
    
    // Test Tinggi Badan
    if (is_numeric($data['Tinggi Badan (cm)']) && floatval($data['Tinggi Badan (cm)']) > 0) {
        echo "<li style='color: green;'>✓ Tinggi Badan valid: " . $data['Tinggi Badan (cm)'] . "</li>";
    } else {
        echo "<li style='color: red;'>✗ Tinggi Badan tidak valid: " . $data['Tinggi Badan (cm)'] . "</li>";
    }
    
    echo "</ul>";
}

echo "<h3>Instruksi Test Import:</h3>";
echo "<ol>";
echo "<li>Download template dari halaman Data Atlet</li>";
echo "<li>Isi dengan data yang valid sesuai format</li>";
echo "<li>Pastikan tidak ada baris kosong di tengah data</li>";
echo "<li>Upload file dan periksa hasil import</li>";
echo "<li>Jika ada error, gunakan halaman Debug Import untuk melihat detail</li>";
echo "</ol>";

echo "<p><a href='data-atlet.php'>← Kembali ke Data Atlet</a></p>";
echo "<p><a href='debug-import.php'>→ Debug Import</a></p>";
?>
