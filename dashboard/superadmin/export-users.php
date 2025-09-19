<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use App\ExcelHelper;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $_POST['action'] !== 'export_users') {
    die('Invalid request');
}

// Get user data with kontingen information
$stmt = $pdo->query("
    SELECT u.*, 
           GROUP_CONCAT(k.nama_kontingen SEPARATOR ', ') as kontingen_names,
           COUNT(DISTINCT k.id) as total_kontingen,
           COUNT(DISTINCT a.id) as total_athletes
    FROM users u 
    LEFT JOIN kontingen k ON u.id = k.user_id 
    LEFT JOIN athletes a ON u.id = a.user_id 
    WHERE u.role = 'user' 
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Prepare data for Excel
$headers = [
    'No',
    'Nama',
    'Email',
    'No. WhatsApp',
    'Kontingen',
    'Jumlah Kontingen',
    'Total Atlet',
    'Terdaftar'
];

$data = [];
foreach ($users as $i => $user) {
    $data[] = [
        $i + 1,
        $user['nama'],
        $user['email'],
        $user['whatsapp'],
        $user['kontingen_names'] ?: 'Belum ada kontingen',
        $user['total_kontingen'],
        $user['total_athletes'],
        date('d M Y', strtotime($user['created_at']))
    ];
}

$filename = 'data_user_' . date('Ymd');
$title = 'DATA USER - ' . date('d/m/Y');

// Pilihan format export
$format = $_GET['format'] ?? 'xls';

\App\ExcelHelper::createAndDownloadFile($data, $headers, $filename, $title, $format); 