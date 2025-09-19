<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../index.php');
    exit();
}

// Handle delete registration
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $registration_id = $_POST['registration_id'];
    
    try {
        // Verify ownership
        $stmt = $pdo->prepare("
            SELECT r.* FROM registrations r 
            JOIN athletes a ON r.athlete_id = a.id 
            WHERE r.id = ? AND a.user_id = ?
        ");
        $stmt->execute([$registration_id, $_SESSION['user_id']]);
        $registration = $stmt->fetch();
        
        if ($registration && $registration['payment_status'] !== 'verified') {
            $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
            $stmt->execute([$registration_id]);
            
            sendNotification("Pendaftaran berhasil dihapus!", 'success');
        } else {
            sendNotification("Pendaftaran tidak dapat dihapus atau sudah terverifikasi.", 'danger');
        }
    } catch (Exception $e) {
        sendNotification("Gagal menghapus pendaftaran: " . $e->getMessage(), 'danger');
    }
}

// Get user's registrations for competitions with open registration and active status
$stmt = $pdo->prepare("
    SELECT r.*, c.nama_perlombaan, c.tanggal_pelaksanaan, a.nama as athlete_name, 
           cc.nama_kategori as category_name, k.nama_kontingen, r.payment_proof,
           u.nama as user_name, u.email, u.whatsapp,
           ac.nama_kategori as age_category_name,
           ct.nama_kompetisi,
           ct.biaya_pendaftaran,
           r.payment_status,
           CASE 
               WHEN c.registration_status IS NOT NULL THEN c.registration_status
               WHEN CURDATE() < c.tanggal_open_regist THEN 'coming_soon'
               WHEN CURDATE() BETWEEN c.tanggal_open_regist AND c.tanggal_close_regist THEN 'open_regist'
               WHEN CURDATE() > c.tanggal_close_regist THEN 'close_regist'
               ELSE 'coming_soon'
           END as current_registration_status
    FROM registrations r 
    JOIN competitions c ON r.competition_id = c.id 
    JOIN athletes a ON r.athlete_id = a.id 
    JOIN kontingen k ON a.kontingen_id = k.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN competition_categories cc ON r.category_id = cc.id
    LEFT JOIN age_categories ac ON r.age_category_id = ac.id
    LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
    WHERE a.user_id = ? AND c.status = 'active' 
    AND (c.registration_status = 'open_regist' OR 
         (CURDATE() BETWEEN c.tanggal_open_regist AND c.tanggal_close_regist))
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$registrations = $stmt->fetchAll();

// Group registrations by competition
$user_competitions = [];
foreach ($registrations as $reg) {
    $comp_id = $reg['competition_id'];
    if (!isset($user_competitions[$comp_id])) {
        $user_competitions[$comp_id] = [
            'info' => $reg,
            'athletes' => []
        ];
    }
    $user_competitions[$comp_id]['athletes'][] = $reg;
}

// Get available competitions with open registration
$stmt = $pdo->prepare("
    SELECT c.*, 
           COUNT(r.id) as total_registrations,
           CASE 
               WHEN c.registration_status IS NOT NULL THEN c.registration_status
               WHEN CURDATE() < c.tanggal_open_regist THEN 'coming_soon'
               WHEN CURDATE() BETWEEN c.tanggal_open_regist AND c.tanggal_close_regist THEN 'open_regist'
               WHEN CURDATE() > c.tanggal_close_regist THEN 'close_regist'
               ELSE 'coming_soon'
           END as current_registration_status
    FROM competitions c
    LEFT JOIN registrations r ON c.id = r.competition_id
    WHERE c.status = 'active' 
    AND (c.registration_status = 'open_regist' OR 
         (CURDATE() BETWEEN c.tanggal_open_regist AND c.tanggal_close_regist))
    GROUP BY c.id
    ORDER BY c.tanggal_pelaksanaan ASC
");
$stmt->execute();
$available_competitions = $stmt->fetchAll();


// Get notification
$notification = getNotification();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Pendaftaran Atlet - User Panel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Additional styles for athlete registration */
        .registrations-container {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .competition-registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .competition-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .competition-info h3 {
            margin: 0 0 10px 0;
            font-size: 1.4rem;
        }

        .competition-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            opacity: 0.9;
        }

        .competition-date, .kontingen-name {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-open_regist {
            background-color: #dcfce7;
            color: #166534;
        }

        .competition-actions {
            display: flex;
            gap: 10px;
        }

        .btn-upload-kontingen, .btn-invoice-kontingen {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid rgba(255,255,255,0.3);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-upload-kontingen:hover, .btn-invoice-kontingen:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
        }

        .athletes-list {
            padding: 25px;
        }

        .athletes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .athletes-header h4 {
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }

        .athletes-summary .total-cost {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--success-color);
        }

        .athletes-table-container {
            overflow-x: auto;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .athletes-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 0.9rem;
        }

        .athletes-table th {
            background: var(--light-color);
            color: var(--dark-color);
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            white-space: nowrap;
        }

        .athletes-table td {
            padding: 15px 12px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }

        .athlete-row:hover {
            background-color: #f9fafb;
        }

        .athlete-name strong {
            display: block;
            color: var(--primary-color);
            margin-bottom: 2px;
        }

        .registration-date {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .category-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .category-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .age-category {
            color: var(--text-light);
            font-size: 0.8rem;
        }

        .competition-type {
            background: #dbeafe;
            color: var(--primary-color);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .price-amount {
            color: var(--success-color);
            font-weight: 600;
        }

        .payment-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-paid {
            background-color: #dbeafe;
            color: var(--primary-color);
        }

        .status-verified {
            background-color: #dcfce7;
            color: #166534;
        }

        .payment-proof {
            margin-top: 5px;
        }

        .view-proof {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .view-proof:hover {
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 6px 8px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            min-width: 32px;
            height: 32px;
        }

        .btn-upload {
            background: var(--success-color);
            color: white;
        }

        .btn-upload:hover {
            background: #059669;
        }

        .btn-edit {
            background: var(--warning-color);
            color: white;
        }

        .btn-edit:hover {
            background: #d97706;
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .btn-invoice {
            background: var(--dark-color);
            color: white;
        }

        .btn-invoice:hover {
            background: #374151;
        }

        .btn-download {
            background: var(--primary-color);
            color: white;
        }

        .btn-download:hover {
            background: var(--secondary-color);
        }

        .btn-print {
            background: #8b5cf6;
            color: white;
        }

        .btn-print:hover {
            background: #7c3aed;
        }

        .kontingen-actions-bar {
            background: var(--light-color);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            border: 1px solid var(--border-color);
        }

        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .stat-label {
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .stat-value {
            font-weight: 700;
            font-size: 1rem;
        }

        .total-cost-item {
            border-left: 4px solid var(--success-color);
        }

        .total-amount {
            color: var(--success-color);
            font-size: 1.1rem;
        }

        .text-success { color: var(--success-color); }
        .text-info { color: var(--primary-color); }
        .text-warning { color: var(--warning-color); }

        .kontingen-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-kontingen {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-view-invoice {
            background: var(--primary-color);
            color: white;
        }

        .btn-view-invoice:hover {
            background: var(--secondary-color);
        }

        .btn-download-invoice {
            background: var(--success-color);
            color: white;
        }

        .btn-download-invoice:hover {
            background: #059669;
        }

        .btn-print-invoice {
            background: #8b5cf6;
            color: white;
        }

        .btn-print-invoice:hover {
            background: #7c3aed;
        }

        /* Enhanced Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-light);
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-radius: 20px;
            border: 2px dashed #cbd5e1;
            margin: 20px 0;
            animation: fadeInScale 0.8s ease-out;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.4;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .empty-state p {
            font-size: 1.3rem;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--text-color);
        }

        .empty-state small {
            font-size: 1rem;
            line-height: 1.5;
            opacity: 0.8;
        }

        /* Modal styles */
        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-help {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* Enhanced Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Close modal button - success green variant */
        .btn-close-modal {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .btn-close-modal:hover {
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.45);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
        }

        .btn-secondary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.4);
        }

        .btn-secondary:hover::before {
            left: 100%;
        }

        .btn-secondary:active {
            transform: translateY(0);
        }

        /* Special Button for Registration */
        .btn-register-now {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 16px 32px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            }
            50% {
                box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
            }
        }

        .btn-register-now::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn-register-now:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5);
            animation: none;
        }

        .btn-register-now:hover::before {
            left: 100%;
        }

        .btn-register-now:active {
            transform: translateY(-1px) scale(1.02);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .delete-confirmation {
            text-align: center;
        }

        .delete-confirmation i {
            font-size: 3rem;
            color: var(--danger-color);
            margin-bottom: 15px;
        }

        .delete-confirmation h4 {
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .delete-confirmation p {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        /* WhatsApp Modal Styles */
        .whatsapp-success {
            text-align: center;
            padding: 20px 0;
        }

        .whatsapp-success h4 {
            color: var(--text-color);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        .whatsapp-success p {
            color: var(--text-light);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .whatsapp-link-container {
            margin: 25px 0;
        }

        .whatsapp-link-btn {
            background: #22c55e;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            transition: background 0.3s;
        }

        .whatsapp-link-btn:hover {
            background: #16a34a;
            color: white;
            text-decoration: none;
        }

        .whatsapp-actions {
            margin-top: 25px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .athletes-table-container {
                font-size: 0.8rem;
            }
            
            .athletes-table th,
            .athletes-table td {
                padding: 8px 4px;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 2px;
            }
            
            .btn-action {
                width: 100%;
                justify-content: flex-start;
                gap: 8px;
            }
            
            .athletes-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .summary-stats {
                grid-template-columns: 1fr;
            }
            
            .kontingen-actions {
                flex-direction: column;
            }
            
            .competition-actions {
                flex-direction: column;
                gap: 8px;
            }
        }

        /* Enhanced Navigation Tabs */
        .nav-tabs-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .nav-tabs {
            display: flex;
            position: relative;
        }

        .nav-tabs::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .nav-tabs:hover::before {
            transform: scaleX(1);
        }

        .nav-tab {
            flex: 1;
            padding: 22px 20px;
            text-align: center;
            text-decoration: none;
            color: var(--text-light);
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 3px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .nav-tab::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-tab:hover {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .nav-tab:hover::before {
            left: 100%;
        }

        .nav-tab.active {
            color: var(--primary-color);
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-bottom-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .nav-tab i {
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .nav-tab:hover i {
            transform: scale(1.2);
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Competition Cards - Enhanced */
        .competitions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
            gap: 30px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .competition-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.05);
            animation: slideInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .competition-card:nth-child(1) { animation-delay: 0.1s; }
        .competition-card:nth-child(2) { animation-delay: 0.2s; }
        .competition-card:nth-child(3) { animation-delay: 0.3s; }
        .competition-card:nth-child(4) { animation-delay: 0.4s; }
        .competition-card:nth-child(5) { animation-delay: 0.5s; }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .competition-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border-color: var(--primary-color);
        }

        .competition-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .competition-card:hover::before {
            transform: scaleX(1);
        }

        .competition-card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .competition-card-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: transform 0.6s ease;
        }

        .competition-card:hover .competition-card-header::after {
            transform: rotate(45deg) translateX(100%);
        }

        .competition-card-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .competition-card-body {
            padding: 25px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        }

        .competition-info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            color: var(--text-color);
            padding: 12px 15px;
            transition: all 0.3s ease;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.7);
            border-left: 3px solid transparent;
        }

        .competition-info-item:hover {
            transform: translateX(8px);
            color: var(--primary-color);
            background: rgba(255, 255, 255, 0.9);
            border-left-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .competition-info-item i {
            color: var(--primary-color);
            width: 18px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: rgba(37, 99, 235, 0.1);
            padding: 8px;
            border-radius: 8px;
        }

        .competition-info-item:hover i {
            transform: scale(1.2) rotate(5deg);
            background: rgba(37, 99, 235, 0.2);
        }

        .competition-info-item span {
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .competition-info-item:hover span {
            font-weight: 600;
        }

        .competition-description {
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            background: white;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .competition-description p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        .competition-card-footer {
            padding: 25px;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 15px;
            background: white;
            justify-content: space-between;
            align-items: center;
        }

        /* Enhanced Status Badges */
        .status-badge {
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .status-badge::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .status-badge:hover::before {
            left: 100%;
        }

        .status-open_regist {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .status-coming_soon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .status-close_regist {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }


        /* Responsive Design */
        @media (max-width: 768px) {
            .competitions-grid {
                grid-template-columns: 1fr;
            }
        }

.btn-kontingen.btn-view-invoice {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    font-weight: 700;
    border: none;
    box-shadow: 0 2px 8px #2563eb22;
    transition: background 0.3s, box-shadow 0.3s;
}
.btn-kontingen.btn-view-invoice:hover {
    background: #1746a2;
    color: #fff;
    box-shadow: 0 4px 16px #2563eb33;
}
.btn-kontingen.btn-download-invoice {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    font-weight: 700;
    border: none;
    box-shadow: 0 2px 8px #22c55e22;
    transition: background 0.3s, box-shadow 0.3s;
}
.btn-kontingen.btn-download-invoice:hover {
    background: #15803d;
    color: #fff;
    box-shadow: 0 4px 16px #22c55e33;
}

.btn-action.btn-edit {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    font-weight: 700;
    border: none;
    transition: background 0.3s, box-shadow 0.3s;
}
.btn-action.btn-edit:hover {
    background: #b45309;
    color: #fff;
    box-shadow: 0 2px 8px #f59e0b33;
}
.btn-action.btn-invoice {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    font-weight: 700;
    border: none;
    transition: background 0.3s, box-shadow 0.3s;
}
.btn-action.btn-invoice:hover {
    background: #1746a2;
    color: #fff;
    box-shadow: 0 2px 8px #2563eb33;
}
.btn-action.btn-upload {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    font-weight: 700;
    border: none;
    transition: background 0.3s, box-shadow 0.3s;
}
.btn-action.btn-upload:hover {
    background: #15803d;
    color: #fff;
    box-shadow: 0 2px 8px #22c55e33;
}
.btn-action.btn-print {
    background: linear-gradient(135deg, #a21caf, #7c3aed);
    color: #fff;
    font-weight: 700;
    border: none;
    transition: background 0.3s, box-shadow 0.3s;
}
.btn-action.btn-print:hover {
    background: #581c87;
    color: #fff;
    box-shadow: 0 2px 8px #a21caf33;
}
.btn-action.btn-delete {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: #fff;
    font-weight: 700;
    border: none;
    transition: background 0.3s, box-shadow 0.3s;
}
.btn-action.btn-delete:hover {
    background: #991b1b;
    color: #fff;
    box-shadow: 0 2px 8px #ef444433;
}
    </style>
</head>
<body>
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
            <li><a href="data-atlet.php"><i class="fas fa-user-ninja"></i> Data Atlet</a></li>
            <li><a href="perlombaan.php" class="active"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Menu Pendaftaran Atlet</h1>
            <p class="page-subtitle">Kelola pendaftaran atlet pada perlombaan yang sedang buka pendaftaran</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="nav-tabs-container">
            <div class="nav-tabs">
                <a href="#registered-athletes" class="nav-tab active" onclick="showTab('registered-athletes', this)">
                    <i class="fas fa-users"></i> Atlet Terdaftar
                </a>
                <a href="#available-competitions" class="nav-tab" onclick="showTab('available-competitions', this)">
                    <i class="fas fa-trophy"></i> Perlombaan Tersedia
                </a>
            </div>
        </div>

        <?php if ($notification): ?>
            <div class="alert alert-<?php echo $notification['type'] === 'success' ? 'success' : 'danger'; ?>">
                <i class="fas fa-<?php echo $notification['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> 
                <?php echo $notification['message']; ?>
            </div>
        <?php endif; ?>
        
        <?php 
        // Check for success parameter from registration
        $url_success = $_GET['success'] ?? null;
        $url_whatsapp = $_GET['whatsapp'] ?? null;
        if ($url_success === '1'): 
        ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                Pendaftaran berhasil! Silakan lakukan pembayaran dan upload bukti pembayaran.
                <?php if ($url_whatsapp): ?>
                    <br><small>Link grup WhatsApp akan muncul dalam beberapa detik.</small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Tab Content: Registered Athletes -->
        <div id="registered-athletes" class="tab-content active">
            <!-- Registered Athletes Section (Enhanced Table) -->
            <?php if (!empty($user_competitions)): ?>
                <?php foreach ($user_competitions as $comp_id => $comp_data): ?>
                    <div class="competition-registration-card">
                        <div class="competition-header" style="padding: 30px 25px;">
                            <div class="competition-info" style="text-align: left; margin-left: 20px;">
                                <h3 style="color: #2563eb; font-weight: 800; font-size: 2.2rem; margin: 0 0 20px 0; text-align: left;">
                                    <?php echo htmlspecialchars($comp_data['info']['nama_perlombaan']); ?>
                                </h3>
                                
                                <div class="competition-meta" style="display: flex; justify-content: flex-start; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; align-items: center;">
                                    <span class="competition-date" style="background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-calendar"></i> <?php echo formatDate($comp_data['info']['tanggal_pelaksanaan']); ?>
                                    </span>
                                    <span class="kontingen-name" style="background: #2563eb; color: #fff; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-flag"></i> <?php echo htmlspecialchars($comp_data['info']['nama_kontingen']); ?>
                                    </span>
                                    <span class="status-badge status-<?php echo $comp_data['info']['current_registration_status']; ?>" style="font-size: 0.9rem; padding: 8px 16px;">
                                        Buka Pendaftaran
                                    </span>
                                </div>
                                
                                <!-- Kontingen Invoice & Upload -->
                                <div class="kontingen-actions" style="margin-top: 20px; display: flex; gap: 15px; justify-content: flex-start; flex-wrap: wrap;">
                                    <a href="generate-invoice.php?type=kontingen&competition_id=<?= $comp_id ?>" class="btn-kontingen btn-view-invoice" target="_blank" style="min-width: 200px; justify-content: center;">
                                        <i class="fas fa-file-invoice"></i> Lihat Invoice Kontingen
                                    </a>
                                    <button type="button" class="btn-kontingen btn-download-invoice" onclick="uploadKontingenPayment('<?= $comp_id ?>', '<?= htmlspecialchars(addslashes($comp_data['info']['nama_perlombaan'])) ?>')" style="min-width: 250px; justify-content: center;">
                                        <i class="fas fa-upload"></i> Upload Bukti Pembayaran Kontingen
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Ringkasan Statistik -->
                        <?php
                            $total_atlet = count($comp_data['athletes']);
                            $total_terverifikasi = 0;
                            $total_sudah_bayar = 0;
                            $total_menunggu = 0;
                            $total_biaya_kontingen = 0;
                            foreach ($comp_data['athletes'] as $ra) {
                                if ($ra['payment_status'] === 'verified') $total_terverifikasi++;
                                if ($ra['payment_status'] === 'paid') $total_sudah_bayar++;
                                if ($ra['payment_status'] === 'pending' || $ra['payment_status'] === 'unpaid') $total_menunggu++;
                                $total_biaya_kontingen += $ra['biaya_pendaftaran'] ?? 0;
                            }
                        ?>
                        <div class="athlete-summary-stats" style="margin:20px 0;display:flex;gap:30px;flex-wrap;">
                            <span><b>Total Atlet:</b> <?php echo $total_atlet; ?></span>
                            <span><b>Terverifikasi:</b> <?php echo $total_terverifikasi; ?></span>
                            <span><b>Sudah Bayar:</b> <?php echo $total_sudah_bayar; ?></span>
                            <span><b>Menunggu:</b> <?php echo $total_menunggu; ?></span>
                            <span><b>Total Biaya Kontingen:</b> Rp <?php echo number_format($total_biaya_kontingen,0,',','.'); ?></span>
                        </div>
                        <div class="athletes-table-container">
                            <table class="athletes-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Atlet</th>
                                        <th>Kategori Umur</th>
                                        <th>Jenis Kompetisi</th>
                                        <th>Kategori Tanding</th>
                                        <th>Biaya</th>
                                        <th>Status Pembayaran</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comp_data['athletes'] as $i => $ra): ?>
                                    <tr>
                                        <td><?php echo $i+1; ?></td>
                                        <td><?php echo htmlspecialchars($ra['athlete_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ra['age_category_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($ra['nama_kompetisi'] ?? '-'); ?></td>
                                        <td>
                                            <?php
                                            if (isset($ra['nama_kompetisi']) && stripos($ra['nama_kompetisi'], 'tanding') !== false) {
                                                echo htmlspecialchars($ra['category_name'] ?? '-');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>Rp <?php echo number_format($ra['biaya_pendaftaran'] ?? 0,0,',','.'); ?></td>
                                        <td>
                                            <?php
                                            switch($ra['payment_status']) {
                                                case 'verified': echo '<span style="color:#16a34a;font-weight:700;">Terverifikasi</span>'; break;
                                                case 'paid': echo '<span style="color:#2563eb;font-weight:700;">Sudah Bayar</span>'; break;
                                                case 'pending': case 'unpaid': default: echo '<span style="color:#991b1b;font-weight:700;">Menunggu</span>'; break;
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <a href="edit-pendaftaran.php?id=<?= $ra['id'] ?>" class="btn-action btn-edit" title="Edit Data Atlet">
                                                <i class="fas fa-edit"></i> <span>Edit</span>
                                            </a>
                                            <a href="generate-invoice.php?type=athlete&registration_id=<?= $ra['id'] ?>" class="btn-action btn-invoice" title="Lihat Invoice" target="_blank">
                                                <i class="fas fa-file-invoice"></i> <span>Invoice</span>
                                            </a>
                                            <button type="button" class="btn-action btn-upload" title="Upload Bukti Pembayaran" onclick="uploadAthletePayment('<?= $ra['id'] ?>', '<?= htmlspecialchars(addslashes($ra['athlete_name'])) ?>')">
                                                <i class="fas fa-upload"></i> <span>Upload</span>
                                            </button>
                                            <a href="generate-invoice.php?type=athlete&registration_id=<?= $ra['id'] ?>&action=print" class="btn-action btn-print" title="Print Invoice" target="_blank">
                                                <i class="fas fa-print"></i> <span>Print</span>
                                            </a>
                                            <?php if ($ra['payment_status'] !== 'verified'): ?>
                                            <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus pendaftaran atlet ini?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="registration_id" value="<?= $ra['id'] ?>">
                                                <button type="submit" class="btn-action btn-delete" title="Hapus Data Atlet">
                                                    <i class="fas fa-trash"></i> <span>Hapus</span>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Tab Content: Available Competitions -->
        <div id="available-competitions" class="tab-content">
            <?php if (empty($available_competitions)): ?>
                <div class="empty-state">
                    <i class="fas fa-trophy"></i>
                    <p>Tidak Ada Perlombaan Tersedia</p>
                    <small>Saat ini tidak ada perlombaan yang membuka pendaftaran.</small>
                </div>
            <?php else: ?>
                <div class="competitions-grid">
                    <?php foreach ($available_competitions as $competition): ?>
                        <div class="competition-card">
                            <div class="competition-card-header">
                                <h3 style="color:#2563eb;font-weight:700;"><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></h3>
                                <span class="status-badge status-<?php echo $competition['current_registration_status']; ?>">
                                    <?php 
                                    switch($competition['current_registration_status']) {
                                        case 'open_regist': echo 'Buka Pendaftaran'; break;
                                        case 'close_regist': echo 'Tutup Pendaftaran'; break;
                                        case 'coming_soon': echo 'Segera Dibuka'; break;
                                        default: echo 'Tidak Aktif';
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="competition-card-body">
                                <div class="competition-info-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>Tanggal: <?php echo formatDate($competition['tanggal_pelaksanaan']); ?></span>
                                </div>
                                <div class="competition-info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Lokasi: <?php echo htmlspecialchars($competition['lokasi'] ?? 'TBA'); ?></span>
                                </div>
                                <div class="competition-info-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>Pendaftaran: <?php echo formatDate($competition['tanggal_open_regist']); ?> - <?php echo formatDate($competition['tanggal_close_regist']); ?></span>
                                </div>
                                <div class="competition-info-item">
                                    <i class="fas fa-users"></i>
                                    <span>Total Pendaftar: <?php echo $competition['total_registrations']; ?> atlet</span>
                                </div>
                                
                                <?php if ($competition['deskripsi']): ?>
                                    <div class="competition-description">
                                        <p><?php echo nl2br(htmlspecialchars($competition['deskripsi'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="competition-card-footer">
                                <a href="competition-detail.php?id=<?php echo $competition['id']; ?>" class="btn-secondary">
                                    <i class="fas fa-info-circle"></i> Lihat Detail
                                </a>
                                <?php if ($competition['current_registration_status'] === 'open_regist'): ?>
                                    <a href="daftar-perlombaan.php?id=<?php echo $competition['id']; ?>" class="btn-register-now">
                                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                                    </a>
                                <?php else: ?>
                                    <button class="btn-secondary" disabled>
                                        <i class="fas fa-lock"></i> 
                                        <?php 
                                        switch($competition['current_registration_status']) {
                                            case 'close_regist': echo 'Pendaftaran Ditutup'; break;
                                            case 'coming_soon': echo 'Segera Dibuka'; break;
                                            default: echo 'Tidak Aktif';
                                        }
                                        ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Payment Upload Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload Bukti Pembayaran</h3>
                <span class="close" onclick="closePaymentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Upload bukti pembayaran untuk atlet: <strong id="athleteNameDisplay"></strong></p>
                <form id="paymentForm" enctype="multipart/form-data">
                    <input type="hidden" id="registrationId" name="registration_id">
                    <div class="form-group">
                        <label for="paymentProof">Bukti Pembayaran *</label>
                        <input type="file" id="paymentProof" name="payment_proof" accept="image/*" required>
                        <small class="form-help">Format: JPG, PNG, GIF. Maksimal 5MB.</small>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                        <button type="button" class="btn-secondary" onclick="closePaymentModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kontingen Payment Upload Modal -->
    <div id="kontingenPaymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload Bukti Pembayaran Kontingen</h3>
                <span class="close" onclick="closeKontingenPaymentModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Upload bukti pembayaran untuk perlombaan: <strong id="kontingenCompetitionName"></strong></p>
                <form id="kontingenPaymentForm" enctype="multipart/form-data">
                    <input type="hidden" id="kontingenCompetitionId" name="competition_id">
                    <div class="form-group">
                        <label for="kontingenPaymentProof">Bukti Pembayaran *</label>
                        <input type="file" id="kontingenPaymentProof" name="payment_proof" accept="image/*" required>
                        <small class="form-help">Format: JPG, PNG, GIF. Maksimal 5MB.</small>
                    </div>
                    <div class="form-group">
                        <label for="kontingenPaymentNote">Catatan Pembayaran</label>
                        <textarea id="kontingenPaymentNote" name="payment_note" rows="3" placeholder="Catatan tambahan (opsional)"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-upload"></i> Upload
                        </button>
                        <button type="button" class="btn-secondary" onclick="closeKontingenPaymentModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Registration Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Pendaftaran</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Edit pendaftaran untuk atlet: <strong id="editAthleteNameDisplay"></strong></p>
                <form id="editForm">
                    <input type="hidden" id="editRegistrationId" name="registration_id">
                    <input type="hidden" id="editCompetitionId" name="competition_id">
                
                    <div class="form-group">
                        <label for="editAthlete">Atlet *</label>
                        <select id="editAthlete" name="athlete_id" required>
                            <option value="">Pilih Atlet</option>
                        </select>
                    </div>
                
                    <div class="form-group">
                        <label for="editAgeCategory">Kategori Umur *</label>
                        <select id="editAgeCategory" name="age_category_id" required onchange="loadCompetitionCategories()">
                            <option value="">Pilih Kategori Umur</option>
                        </select>
                    </div>
                
                    <div class="form-group" id="editCategoryGroup" style="display: none;">
                        <label for="editCategory">Kategori Kompetisi</label>
                        <select id="editCategory" name="category_id">
                            <option value="">Pilih Kategori</option>
                        </select>
                    </div>
                
                    <div class="form-group">
                        <label for="editCompetitionType">Jenis Kompetisi *</label>
                        <select id="editCompetitionType" name="competition_type_id" required>
                            <option value="">Pilih Jenis Kompetisi</option>
                        </select>
                    </div>
                
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                        <button type="button" class="btn-secondary" onclick="closeEditModal()">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Konfirmasi Hapus</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="delete-confirmation">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Hapus Pendaftaran?</h4>
                    <p>Apakah Anda yakin ingin menghapus pendaftaran atlet <strong id="deleteAthleteName"></strong>?</p>
                    <p>Tindakan ini tidak dapat dibatalkan.</p>
                
                    <form id="deleteForm">
                        <input type="hidden" id="deleteRegistrationId" name="registration_id">
                    
                        <div class="form-actions">
                            <button type="submit" class="btn-danger">
                                <i class="fas fa-trash"></i> Ya, Hapus
                            </button>
                            <button type="button" class="btn-secondary" onclick="closeDeleteModal()">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Group Modal -->
    <div id="whatsappModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fab fa-whatsapp"></i> Grup WhatsApp Perlombaan</h3>
                <span class="close" onclick="closeWhatsAppModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="whatsapp-success">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: #22c55e; margin-bottom: 15px;"></i>
                    <h4>Pendaftaran Berhasil!</h4>
                    <p>Selamat! Pendaftaran atlet Anda telah berhasil. Silakan bergabung dengan grup WhatsApp perlombaan untuk mendapatkan informasi lebih lanjut.</p>
                    
                    <div class="whatsapp-link-container">
                        <a href="" id="whatsappLink" target="_blank" class="whatsapp-link-btn">
                            <i class="fab fa-whatsapp"></i> Join Grup WhatsApp
                        </a>
                    </div>
                    
                    <div class="whatsapp-actions">
                        <button type="button" class="btn-primary btn-close-modal" onclick="closeWhatsAppModal()">
                            <i class="fas fa-check"></i> Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript functions -->
    <script>
// Tab switching functions
function showTab(tabId, element) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all nav tabs
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabId).classList.add('active');
    
    // Add active class to clicked nav tab
    element.classList.add('active');
}




// Invoice functions
function viewAthleteInvoice(registrationId) {
    window.open('generate-invoice.php?type=athlete&registration_id=' + registrationId, '_blank');
}

function downloadAthleteInvoice(registrationId) {
    window.open('generate-invoice.php?type=athlete&registration_id=' + registrationId + '&action=download', '_blank');
}

function printAthleteInvoice(registrationId) {
    window.open('generate-invoice.php?type=athlete&registration_id=' + registrationId + '&action=print', '_blank');
}

function viewKontingenInvoice(competitionId) {
    window.open('generate-invoice.php?type=kontingen&competition_id=' + competitionId, '_blank');
}

function downloadKontingenInvoice(competitionId) {
    window.open('generate-invoice.php?type=kontingen&competition_id=' + competitionId + '&action=download', '_blank');
}

function printKontingenInvoice(competitionId) {
    window.open('generate-invoice.php?type=kontingen&competition_id=' + competitionId + '&action=print', '_blank');
}

// Payment upload functions
function uploadAthletePayment(registrationId, athleteName) {
    document.getElementById('registrationId').value = registrationId;
    document.getElementById('athleteNameDisplay').textContent = athleteName;
    document.getElementById('paymentModal').style.display = 'block';
}

function uploadKontingenPayment(competitionId, competitionName) {
    document.getElementById('kontingenCompetitionId').value = competitionId;
    document.getElementById('kontingenCompetitionName').textContent = competitionName;
    document.getElementById('kontingenPaymentModal').style.display = 'block';
}

function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.getElementById('paymentForm').reset();
}

function closeKontingenPaymentModal() {
    document.getElementById('kontingenPaymentModal').style.display = 'none';
    document.getElementById('kontingenPaymentForm').reset();
}

// Edit registration functions
function editAthleteRegistration(registrationId, competitionId, athleteName) {
    document.getElementById('editRegistrationId').value = registrationId;
    document.getElementById('editCompetitionId').value = competitionId;
    document.getElementById('editAthleteNameDisplay').textContent = athleteName;
    
    // Load available athletes first
    fetch('get-available-athletes.php?competition_id=' + competitionId + '&registration_id=' + registrationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('editAthlete');
                select.innerHTML = '<option value="">Pilih Atlet</option>';
                data.athletes.forEach(athlete => {
                    const option = document.createElement('option');
                    option.value = athlete.id;
                    option.textContent = athlete.nama + ' (' + athlete.jenis_kelamin + ')';
                    if (athlete.id == data.current_athlete_id) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            } else {
                alert('Error loading athletes: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error loading athletes');
            console.error(error);
        });
    
    // Load registration data
    fetch('get-registration-data.php?registration_id=' + registrationId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Load age categories first
                loadAgeCategories(competitionId, data.age_category_id);
                // Then load other data
                setTimeout(() => {
                    if (data.age_category_id) {
                        loadCompetitionCategories(data.category_id);
                    }
                    const competitionTypeSelect = document.getElementById('editCompetitionType');
                    competitionTypeSelect.value = data.competition_type_id || '';
                    // Toggle category visibility based on competition type
                    toggleCategoryVisibility();
                }, 500);
            } else {
                alert('Error loading registration data: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error loading registration data');
            console.error(error);
        });
    
    document.getElementById('editModal').style.display = 'block';
    
    // Add event listener for competition type change after modal is shown
    const competitionTypeSelect = document.getElementById('editCompetitionType');
    if (competitionTypeSelect) {
        // Remove existing event listeners
        const newSelect = competitionTypeSelect.cloneNode(true);
        competitionTypeSelect.parentNode.replaceChild(newSelect, competitionTypeSelect);
        
        // Add new event listener
        newSelect.addEventListener('change', function() {
            toggleCategoryVisibility();
        });
    }
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.getElementById('editForm').reset();
    
    // Reset category visibility
    document.getElementById('editCategoryGroup').style.display = 'none';
    document.getElementById('editAgeCategory').parentElement.style.display = 'block';
}

// Delete registration functions
function deleteAthleteRegistration(registrationId, athleteName) {
    document.getElementById('deleteRegistrationId').value = registrationId;
    document.getElementById('deleteAthleteName').textContent = athleteName;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

function closeWhatsAppModal() {
    document.getElementById('whatsappModal').style.display = 'none';
}

function showWhatsAppModal(whatsappLink) {
    document.getElementById('whatsappLink').href = whatsappLink;
    document.getElementById('whatsappModal').style.display = 'block';
}

// Load competition data functions
function loadAgeCategories(competitionId, selectedId = '') {
    fetch('get-competition-data-updated.php?type=age_categories&competition_id=' + competitionId)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('editAgeCategory');
            select.innerHTML = '<option value="">Pilih Kategori Umur</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama_kategori;
                if (item.id == selectedId) option.selected = true;
                select.appendChild(option);
            });
            
            // Load competition types
            loadCompetitionTypes(competitionId);
        })
        .catch(error => {
            console.error('Error loading age categories:', error);
        });
}

function loadCompetitionCategories(selectedId = '') {
    const competitionId = document.getElementById('editCompetitionId').value;
    const ageCategoryId = document.getElementById('editAgeCategory').value;
    const categoryGroup = document.getElementById('editCategoryGroup');
    
    // Only load categories if category group is visible (for tanding competitions)
    if (!categoryGroup.style.display || categoryGroup.style.display === 'none') {
        return;
    }
    
    if (competitionId && ageCategoryId) {
        fetch('get-competition-data-updated.php?type=competition_categories&competition_id=' + competitionId + '&age_category_id=' + ageCategoryId)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('editCategory');
                select.innerHTML = '<option value="">Pilih Kategori</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.nama_kategori;
                    if (item.id == selectedId) option.selected = true;
                    select.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading competition categories:', error);
            });
    } else {
        document.getElementById('editCategory').innerHTML = '<option value="">Pilih Kategori</option>';
    }
}

function loadCompetitionTypes(competitionId, selectedId = '') {
    fetch('get-competition-data-updated.php?type=competition_types&competition_id=' + competitionId)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('editCompetitionType');
            select.innerHTML = '<option value="">Pilih Jenis Kompetisi</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.nama_kompetisi + ' (' + (item.biaya_pendaftaran ? 'Rp ' + new Intl.NumberFormat('id-ID').format(item.biaya_pendaftaran) : 'Gratis') + ')';
                // Add data attribute to check if it's tanding
                option.dataset.isTanding = item.nama_kompetisi.toLowerCase().includes('tanding') ? 'true' : 'false';
                if (item.id == selectedId) option.selected = true;
                select.appendChild(option);
            });
            
            // Initial check for selected value
            if (selectedId) {
                toggleCategoryVisibility();
            }
        })
        .catch(error => {
            console.error('Error loading competition types:', error);
        });
}

function toggleCategoryVisibility() {
    const competitionTypeSelect = document.getElementById('editCompetitionType');
    const categoryGroup = document.getElementById('editCategoryGroup');
    const categorySelect = document.getElementById('editCategory');
    const ageCategorySelect = document.getElementById('editAgeCategory');
    
    if (competitionTypeSelect.value) {
        const selectedOption = competitionTypeSelect.options[competitionTypeSelect.selectedIndex];
        const isTanding = selectedOption.dataset.isTanding === 'true';
        
        if (isTanding) {
            // Show category group and age category
            categoryGroup.style.display = 'block';
            ageCategorySelect.parentElement.style.display = 'block';
            
            // Load categories if age category is selected
            if (ageCategorySelect.value) {
                loadCompetitionCategories();
            }
        } else {
            // Hide category group and age category for non-tanding
            categoryGroup.style.display = 'none';
            ageCategorySelect.parentElement.style.display = 'none';
            
            // Clear category selection
            categorySelect.innerHTML = '<option value="">Pilih Kategori</option>';
            categorySelect.value = '';
        }
    } else {
        // Hide both if no competition type selected
        categoryGroup.style.display = 'none';
        ageCategorySelect.parentElement.style.display = 'none';
    }
}

// Form submissions
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    submitBtn.disabled = true;
    
    fetch('upload-payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Remove registration success params so popup won't reappear
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete('success');
                url.searchParams.delete('whatsapp');
                history.replaceState(null, '', url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : ''));
            } catch (e) {}
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat upload file');
        console.error(error);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

document.getElementById('kontingenPaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    submitBtn.disabled = true;
    
    fetch('upload-kontingen-payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // Remove registration success params so popup won't reappear
            try {
                const url = new URL(window.location.href);
                url.searchParams.delete('success');
                url.searchParams.delete('whatsapp');
                history.replaceState(null, '', url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : ''));
            } catch (e) {}
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat upload file');
        console.error(error);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    fetch('update-registration.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat update data');
        console.error(error);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    submitBtn.disabled = true;
    
    fetch('delete-registration.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Terjadi kesalahan saat hapus data');
        console.error(error);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Add event listener for competition type change
document.addEventListener('DOMContentLoaded', function() {
    const competitionTypeSelect = document.getElementById('editCompetitionType');
    if (competitionTypeSelect) {
        competitionTypeSelect.addEventListener('change', function() {
            toggleCategoryVisibility();
        });
    }
    
    // Check for WhatsApp modal trigger
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const whatsapp = urlParams.get('whatsapp');
    
    if (success === '1' && whatsapp) {
        // Show WhatsApp modal after a short delay
        setTimeout(() => {
            showWhatsAppModal(decodeURIComponent(whatsapp));
        }, 1000);
    }

    // If success came from a previous registration flow, keep it; otherwise do nothing.
});

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['paymentModal', 'kontingenPaymentModal', 'editModal', 'deleteModal', 'whatsappModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
}
</script>
</body>
</html>
