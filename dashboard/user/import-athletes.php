<?php
session_start();
require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    die('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excel_file'])) {
    header('Location: data-atlet.php?error=invalid_request');
    exit();
}

$file = $_FILES['excel_file'];
$allowedTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];
$allowedExtensions = ['xlsx', 'xls', 'csv'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: data-atlet.php?error=upload_error');
    exit();
}

$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions)) {
    header('Location: data-atlet.php?error=invalid_format');
    exit();
}

try {
    // Create upload directory if not exists
    $uploadDir = '../../uploads/temp/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Move uploaded file
    $tempFile = $uploadDir . 'import_' . time() . '.' . $fileExtension;
    if (!move_uploaded_file($file['tmp_name'], $tempFile)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    // Read file sesuai format
    $excelData = [];
    $headers = [];
    
    if ($fileExtension === 'csv') {
        // Baca CSV
        if (($handle = fopen($tempFile, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if (empty(array_filter($row))) continue;
                if (empty($headers)) {
                    $headers = $row;
                } else {
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
            $spreadsheet = IOFactory::load($tempFile);
            $sheet = $spreadsheet->getActiveSheet();
            
            // Get highest row and column
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            
            // Read headers (first row)
            $headers = [];
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $cellValue = $sheet->getCellByColumnAndRow($col, 1)->getValue();
                $headers[] = $cellValue ? trim($cellValue) : '';
            }
            
            // Read data rows (starting from row 2)
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cellValue = $sheet->getCellByColumnAndRow($col, $row)->getValue();
                    $rowData[] = $cellValue ? trim($cellValue) : '';
                }
                
                // Skip empty rows
                if (!empty(array_filter($rowData))) {
                    $excelData[] = $rowData;
                }
            }
            
        } catch (\Exception $e) {
            throw new Exception('Gagal membaca file Excel. Pastikan file valid dan tidak corrupt. Pesan error: ' . $e->getMessage());
        }
    }
    
    // Debug: Log data yang dibaca
    error_log("Headers: " . print_r($headers, true));
    error_log("Total rows: " . count($excelData));
    error_log("Sample data: " . print_r(array_slice($excelData, 0, 3), true));
    
    // Validate headers
    $expectedHeaders = [
        'Nama Kontingen',
        'Nama Lengkap', 
        'NIK',
        'Jenis Kelamin',
        'Tanggal Lahir',
        'Tempat Lahir',
        'Nama Sekolah/Instansi',
        'Berat Badan (kg)',
        'Tinggi Badan (cm)'
    ];
    
    if (count($headers) < count($expectedHeaders)) {
        throw new Exception('File tidak sesuai format template. Pastikan menggunakan template yang benar. Jumlah kolom: ' . count($headers) . ', yang dibutuhkan: ' . count($expectedHeaders));
    }
    
    // Get user's kontingen
    $stmt = $pdo->prepare("SELECT id, nama_kontingen FROM kontingen WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $userKontingenList = $stmt->fetchAll();
    
    if (empty($userKontingenList)) {
        throw new Exception('Anda belum memiliki kontingen. Silakan buat kontingen terlebih dahulu.');
    }
    
    // Create kontingen lookup array for faster search
    $kontingenLookup = [];
    foreach ($userKontingenList as $kontingen) {
        $kontingenLookup[strtolower(trim($kontingen['nama_kontingen']))] = $kontingen['id'];
    }
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    $processedRows = [];
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        foreach ($excelData as $rowIndex => $row) {
            // Skip completely empty rows
            if (empty(array_filter($row))) {
                error_log("Row " . ($rowIndex + 2) . " is completely empty, skipping");
                continue;
            }
            
            // Ensure row has enough columns
            while (count($row) < count($expectedHeaders)) {
                $row[] = '';
            }
            
            // Debug: Log row data
            error_log("Processing row " . ($rowIndex + 2) . ": " . print_r($row, true));
            
            // Check if this row has meaningful data (at least name and NIK)
            $hasMeaningfulData = false;
            for ($i = 1; $i <= 5; $i++) {
                if (!empty(trim($row[$i]))) {
                    $hasMeaningfulData = true;
                    break;
                }
            }
            
            if (!$hasMeaningfulData) {
                error_log("Row " . ($rowIndex + 2) . " has no meaningful data, skipping");
                continue;
            }
            
            // Validate required fields
            if (empty(trim($row[1])) || empty(trim($row[2])) || empty(trim($row[3])) || empty(trim($row[4])) || empty(trim($row[5]))) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Data tidak lengkap";
                $errors[] = $errorMsg;
                error_log($errorMsg . " - Row data: " . print_r($row, true));
                $errorCount++;
                continue;
            }
            
            // Validate NIK format (16 digits)
            $nik = trim($row[2]);
            if (!preg_match('/^\d{16}$/', $nik)) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": NIK harus 16 digit angka (NIK: " . $nik . ")";
                $errors[] = $errorMsg;
                error_log($errorMsg);
                $errorCount++;
                continue;
            }
            
            // Validate gender
            $jenisKelamin = strtoupper(trim($row[3]));
            if (!in_array($jenisKelamin, ['L', 'P'])) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Jenis kelamin harus L atau P (Nilai: " . $row[3] . ")";
                $errors[] = $errorMsg;
                error_log($errorMsg);
                $errorCount++;
                continue;
            }
            
            // Validate date format - be more flexible
            $dateValue = trim($row[4]);
            $formattedDate = null;
            
            // Try different date formats
            $dateFormats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y/m/d'];
            foreach ($dateFormats as $format) {
                $date = DateTime::createFromFormat($format, $dateValue);
                if ($date !== false) {
                    $formattedDate = $date->format('Y-m-d');
                    break;
                }
            }
            
            if (!$formattedDate) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Format tanggal tidak valid. Gunakan format YYYY-MM-DD, DD/MM/YYYY, atau DD-MM-YYYY (Nilai: " . $dateValue . ")";
                $errors[] = $errorMsg;
                error_log($errorMsg);
                $errorCount++;
                continue;
            }
            
            // Validate kontingen name from import file
            $kontingenName = trim($row[0]);
            $kontingenId = null;
            
            if (empty($kontingenName)) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Nama kontingen tidak boleh kosong";
                $errors[] = $errorMsg;
                error_log($errorMsg);
                $errorCount++;
                continue;
            }
            
            // Find kontingen ID by name (case-insensitive)
            $kontingenKey = strtolower($kontingenName);
            if (isset($kontingenLookup[$kontingenKey])) {
                $kontingenId = $kontingenLookup[$kontingenKey];
                error_log("Found kontingen: " . $kontingenName . " -> ID: " . $kontingenId);
            } else {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Kontingen '" . $kontingenName . "' tidak ditemukan. Pastikan nama kontingen sesuai dengan yang sudah dibuat.";
                $errors[] = $errorMsg;
                error_log($errorMsg . " - Available kontingen: " . implode(", ", array_keys($kontingenLookup)));
                $errorCount++;
                continue;
            }
            
            // Check if NIK already exists
            $stmt = $pdo->prepare("SELECT id FROM athletes WHERE nik = ? AND user_id = ?");
            $stmt->execute([$row[2], $_SESSION['user_id']]);
            if ($stmt->fetch()) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": NIK " . $row[2] . " sudah terdaftar";
                $errors[] = $errorMsg;
                error_log($errorMsg);
                $errorCount++;
                continue;
            }
            
            // Validate numeric values
            $beratBadan = trim($row[7]);
            if (!is_numeric($beratBadan) || floatval($beratBadan) <= 0) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Berat badan harus berupa angka positif (Nilai: " . $beratBadan . ")";
                $errors[] = $errorMsg;
                error_log($errorMsg);
                $errorCount++;
                continue;
            }
            
            $tinggiBadan = trim($row[8]);
            if (!is_numeric($tinggiBadan) || floatval($tinggiBadan) <= 0) {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Tinggi badan harus berupa angka positif (Nilai: " . $tinggiBadan . ")";
                $errors[] = $errorMsg;
                error_log($errorMsg);
                $errorCount++;
                continue;
            }
            
            // Insert athlete
            $stmt = $pdo->prepare("
                INSERT INTO athletes (
                    user_id, kontingen_id, nama, nik, jenis_kelamin, 
                    tanggal_lahir, tempat_lahir, nama_sekolah, 
                    berat_badan, tinggi_badan, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $result = $stmt->execute([
                $_SESSION['user_id'],
                $kontingenId, // Use validated kontingen ID
                trim($row[1]), // Nama Lengkap
                $nik, // NIK (already validated)
                $jenisKelamin, // Jenis Kelamin (already validated)
                $formattedDate, // Tanggal Lahir (formatted)
                trim($row[5]), // Tempat Lahir
                trim($row[6]), // Nama Sekolah
                floatval($beratBadan), // Berat Badan (already validated)
                floatval($tinggiBadan)  // Tinggi Badan (already validated)
            ]);
            
            if ($result) {
                $successCount++;
                error_log("Successfully inserted athlete: " . $row[1] . " (NIK: " . $row[2] . ")");
            } else {
                $errorMsg = "Baris " . ($rowIndex + 2) . ": Gagal menyimpan data ke database";
                $errors[] = $errorMsg;
                error_log($errorMsg . " - Row data: " . print_r($row, true));
                $errorCount++;
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Clean up temp file
        unlink($tempFile);
        
        // Redirect with success message
        $message = "Berhasil mengimpor $successCount data atlet";
        if ($errorCount > 0) {
            $message .= ". $errorCount data gagal diimpor.";
            if (!empty($errors)) {
                $message .= " Detail error: " . implode("; ", array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " dan " . (count($errors) - 3) . " error lainnya.";
                }
            }
            
            // Add kontingen information for user reference
            $availableKontingen = array_keys($kontingenLookup);
            if (!empty($availableKontingen)) {
                $message .= " Kontingen yang tersedia: " . implode(", ", $availableKontingen);
            }
        }
        
        header('Location: data-atlet.php?success=' . urlencode($message));
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        error_log("Import error: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    // Clean up temp file if exists
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    header('Location: data-atlet.php?error=' . urlencode($e->getMessage()));
    exit();
} 