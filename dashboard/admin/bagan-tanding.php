<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Ambil semua batch yang sudah ada hasil drawing
$stmt = $pdo->query("
    SELECT b.filter_id, b.batch_name, d.id as draw_id, d.created_at
    FROM daftar_peserta_filter_batches b
    JOIN daftar_peserta_draws d ON b.filter_id = d.filter_id
    ORDER BY d.created_at DESC
");
$batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_draw_id = isset($_GET['draw_id']) ? trim($_GET['draw_id']) : '';
$pesertaUrut = [];
$filter_kategori_umur = '';
$filter_jenis_kelamin = '';
$filter_jenis_kompetisi = '';
$filter_kategori_tanding = '';
$batch_peserta_count = 0; // Tambahkan variabel untuk menyimpan jumlah peserta dalam batch

if ($selected_draw_id) {
    $stmt = $pdo->prepare("SELECT urutan FROM daftar_peserta_draws WHERE id = ?");
    $stmt->execute([$selected_draw_id]);
    $urutan = json_decode($stmt->fetchColumn(), true);

    $stmt_filter_info = $pdo->prepare("
        SELECT b.kategori_umur, b.jenis_kelamin, b.jenis_kompetisi, b.kategori_tanding
        FROM daftar_peserta_draws d
        JOIN daftar_peserta_filter_batches b ON d.filter_id = b.filter_id
        WHERE d.id = ?
    ");
    $stmt_filter_info->execute([$selected_draw_id]);
    $filter_info = $stmt_filter_info->fetch(PDO::FETCH_ASSOC);

    if ($filter_info) {
        $filter_kategori_umur = $filter_info['kategori_umur'];
        $filter_jenis_kelamin = $filter_info['jenis_kelamin'];
        $filter_jenis_kompetisi = $filter_info['jenis_kompetisi'];
        $filter_kategori_tanding = $filter_info['kategori_tanding'];
    }

    if ($urutan) {
        $batch_peserta_count = count($urutan); // Simpan jumlah peserta dalam batch
        $in = str_repeat('?,', count($urutan) - 1) . '?';
        $stmt = $pdo->prepare("SELECT * FROM daftar_peserta WHERE id IN ($in)");
        $stmt->execute($urutan);
        $peserta = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pesertaMap = [];
        foreach ($peserta as $p) $pesertaMap[$p['id']] = $p;
        foreach ($urutan as $id) if (isset($pesertaMap[$id])) $pesertaUrut[] = $pesertaMap[$id];
    }
}

$competition_id = $_GET['competition_id'] ?? null;
$competition_name = '';
if ($competition_id) {
    $stmt = $pdo->prepare("SELECT nama_perlombaan FROM competitions WHERE id = ?");
    $stmt->execute([$competition_id]);
    $competition_name = $stmt->fetchColumn();
}
if (isset($_GET['versi_bagan'])) {
    $_SESSION['versi_bagan'] = $_GET['versi_bagan'];
}
$versi_bagan = $_GET['versi_bagan'] ?? ($_SESSION['versi_bagan'] ?? 'versi1');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bagan Tanding - Hasil Drawing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        .container {
            width: 100%;
            max-width: 98vw;
            margin: 32px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.10);
            padding: 40px 16px 120px 16px;
            position: relative;
            overflow-x: auto;
        }
        
        /* Responsive container */
        @media (max-width: 1200px) {
            .container {
                margin: 28px auto;
                padding: 32px 12px 100px 12px;
                border-radius: 14px;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
                padding: 24px 8px 80px 8px;
                border-radius: 12px;
                max-width: 100vw;
            }
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 12px auto;
                padding: 16px 4px 60px 4px;
                border-radius: 10px;
            }
        }
        .bracket-bg {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 32px;
            border-radius: 16px;
            margin-top: 24px;
            overflow: visible;
            position: relative;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(10px);
        }
        .bracket-container {
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            margin-top: 24px;
            overflow-x: auto;
            position: relative;
            width: 100%;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            box-sizing: border-box;
            white-space: nowrap;
            cursor: grab;
        }
        
        .bracket-container:active {
            cursor: grabbing;
        }
        
        /* Custom scrollbar untuk Webkit browsers */
        .bracket-container::-webkit-scrollbar {
            height: 12px;
        }
        
        .bracket-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 6px;
            margin: 0 4px;
        }
        
        .bracket-container::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #0ea5e9 0%, #0284c7 100%);
            border-radius: 6px;
            transition: all 0.3s ease;
            border: 2px solid #f1f5f9;
        }
        
        .bracket-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, #0284c7 0%, #0369a1 100%);
            transform: scale(1.05);
        }
        
        .bracket-container::-webkit-scrollbar-corner {
            background: #f1f5f9;
        }
        
        /* Responsive scrollbar */
        @media (max-width: 767px) {
            .bracket-container::-webkit-scrollbar {
                height: 8px;
            }
            
            .bracket-container::-webkit-scrollbar-track {
                border-radius: 4px;
                margin: 0 2px;
            }
            
            .bracket-container::-webkit-scrollbar-thumb {
                border-radius: 4px;
                border: 1px solid #f1f5f9;
            }
        }
        
        @media (max-width: 479px) {
            .bracket-container::-webkit-scrollbar {
                height: 6px;
            }
            
            .bracket-container::-webkit-scrollbar-track {
                border-radius: 3px;
                margin: 0 1px;
            }
            
            .bracket-container::-webkit-scrollbar-thumb {
                border-radius: 3px;
                border: 1px solid #f1f5f9;
            }
        }
        
        .bracket {
            display: flex;
            gap: 30px;
            min-width: fit-content;
            padding: 20px 0;
            align-items: stretch;
            justify-content: flex-start;
            position: relative;
            overflow: visible;
            box-sizing: border-box;
            flex-wrap: nowrap;
            user-select: none;
        }
        .round {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            min-height: 200px;
            position: relative;
            padding-right: 30px;
            min-width: 180px;
            max-width: 260px;
            flex-shrink: 0;
            flex-grow: 0;
        }
        .match-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }



        .match {
            background: white;
            border: 1px solid #888;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: none;
            transition: all 0.2s ease;
            position: relative;
            min-width: 160px;
            max-width: 220px;
            margin: 10px 0;
            user-select: none;
        }
        .match:hover {
            transform: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .match.has-winner {
            border-color: #22c55e;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.15);
        }
        .round:not(:last-child) .match::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            width: 30px;
            height: 0;
            border-top: 2px dashed #ccc;
            z-index: 1;
            transform: translateY(-50%);
        }
        /* Garis connector kurva yang presisi */
        .bracket-match.connector-to-blue::after {
            top: 20% !important; /* Mengarah ke slot biru (atas) */
            border-top: 3px solid #3b82f6;
            border-radius: 0 0 0 20px;
        }
        .bracket-match.connector-to-red::after {
            top: 80% !important; /* Mengarah ke slot merah (bawah) */
            border-top: 3px solid #3b82f6;
            border-radius: 0 0 0 20px;
        }
        /* Garis connector kurva yang lebih halus */
        .bracket-match.connector-curve::after {
            content: '';
            position: absolute;
            left: 100%;
            width: 64px;
            height: 64px;
            background: none;
            border: none;
            z-index: 1;
        }
        .bracket-match.connector-curve.connector-to-blue::after {
            top: 15%;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 64 64'%3E%3Cpath d='M0 32 Q24 32 40 20 T64 20' stroke='%233b82f6' stroke-width='3' fill='none'/%3E%3C/svg%3E") no-repeat;
        }
        .bracket-match.connector-curve.connector-to-red::after {
            top: 15%;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 64 64'%3E%3Cpath d='M0 32 Q24 32 40 44 T64 44' stroke='%233b82f6' stroke-width='3' fill='none'/%3E%3C/svg%3E") no-repeat;
        }
        /* Garis kurva untuk babak final ke winner */
        .bracket-col:nth-last-child(2) .bracket-match.connector-curve.connector-to-blue::after {
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 64 64'%3E%3Cpath d='M0 32 Q24 32 40 20 T64 20' stroke='%2322c55e' stroke-width='3' fill='none'/%3E%3C/svg%3E") no-repeat;
        }
        .bracket-col:nth-last-child(2) .bracket-match.connector-curve.connector-to-red::after {
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='64' height='64' viewBox='0 0 64 64'%3E%3Cpath d='M0 32 Q24 32 40 44 T64 44' stroke='%2322c55e' stroke-width='3' fill='none'/%3E%3C/svg%3E") no-repeat;
        }
        .bracket-connector {
            position: absolute;
            left: 100%;
            top: 50%;
            width: 64px;
            height: 0;
            border-top: 3px solid #3b82f6;
            z-index: 1;
            transform: translateY(-50%);
        }
        /* Garis connector yang menghubungkan babak */
        .bracket-col {
            position: relative;
        }
        /* Garis connector khusus untuk kolom winner */
        .bracket-col:last-child .bracket-match::after {
            display: none;
        }
        /* Garis dari babak final ke winner */
        .bracket-col:nth-last-child(2) .bracket-match::after {
            border-top: 3px solid #22c55e;
        }
        
        /* Styling khusus untuk kolom winner */
        .round:last-child {
            margin-right: 40px;
            min-width: 240px;
            max-width: 300px;
        }
        
        .round:last-child .match {
            min-width: 240px;
            max-width: 300px;
            margin-top: 40px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #22c55e;
            box-shadow: 0 4px 16px rgba(34, 197, 94, 0.15);
        }
        
        .round:last-child .match:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.2);
        }
        
        .round:last-child .participant {
            background: transparent;
            border-bottom: 1px solid rgba(34, 197, 94, 0.2);
            padding: 16px 20px;
            min-width: 200px;
            max-width: 260px;
        }
        
        .round:last-child .participant:last-child {
            border-bottom: none;
        }
        
        /* Tambahan padding untuk memastikan kolom winner terlihat penuh */
        .bracket::after {
            content: '';
            min-width: 60px;
            flex-shrink: 0;
        }
        
        .round:last-child .participant.winner {
            background: rgba(34, 197, 94, 0.1);
            border: 2px solid #22c55e;
        }

        /* Memastikan warna slot tetap konsisten di semua babak */
        .round:last-child .participant:first-child {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.1) 0%, rgba(0, 123, 255, 0.05) 100%);
            border-left: 3px solid #007bff;
        }

        .round:last-child .participant:first-child .participant-name {
            color: #007bff;
        }

        .round:last-child .participant:last-child {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
            border-left: 3px solid #dc3545;
        }

        .round:last-child .participant:last-child .participant-name {
            color: #dc3545;
        }

        .round:last-child .participant.winner:first-child .participant-name {
            color: #007bff;
        }

        .round:last-child .participant.winner:last-child .participant-name {
            color: #dc3545;
        }

        .round:last-child .participant.winner:first-child {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.2) 0%, rgba(0, 123, 255, 0.1) 100%);
            border-left: 3px solid #007bff;
        }

        .round:last-child .participant.winner:last-child {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2) 0%, rgba(220, 53, 69, 0.1) 100%);
            border-left: 3px solid #dc3545;
        }

        /* Memastikan warna nama peserta konsisten di semua keadaan */
        .participant:first-child .participant-name,
        .participant:first-child:hover .participant-name,
        .participant.winner:first-child .participant-name {
            color: #007bff !important;
        }

        .participant:last-child .participant-name,
        .participant:last-child:hover .participant-name,
        .participant.winner:last-child .participant-name {
            color: #dc3545 !important;
        }
        

        /* Responsive Design untuk Bracket */
        
        /* Large Desktop (1400px+) */
        @media (min-width: 1400px) {
            .bracket { 
                gap: 60px; 
                min-width: 1200px; 
            }
            .round { 
                min-width: 280px; 
                max-width: 340px;
            }
            .match {
                min-width: 280px;
                max-width: 340px;
                margin: 14px 0;
            }
            .participant {
                padding: 20px 24px;
                font-size: 1em;
                min-width: 240px;
                max-width: 300px;
            }
            .participant-number {
                min-width: 28px;
                height: 28px;
                font-size: 0.85em;
                margin-right: 12px;
            }

            .round h3 {
                font-size: 1.2em;
                padding: 10px 20px;
                margin-bottom: 24px;
            }
            .round:last-child {
                margin-right: 60px;
                min-width: 300px;
                max-width: 360px;
            }
        }
        
        /* Desktop (1200px - 1399px) */
        @media (max-width: 1399px) and (min-width: 1200px) {
            .bracket { 
                gap: 50px; 
                min-width: 900px; 
            }
            .round { 
                min-width: 240px; 
                max-width: 300px;
            }
            .match {
                min-width: 240px;
                max-width: 300px;
                margin: 12px 0;
            }
            .participant {
                padding: 18px 20px;
                font-size: 0.95em;
                min-width: 200px;
                max-width: 260px;
            }
            .participant-number {
                min-width: 26px;
                height: 26px;
                font-size: 0.8em;
                margin-right: 10px;
            }

            .round h3 {
                font-size: 1.1em;
                padding: 8px 16px;
                margin-bottom: 20px;
            }
            .round:last-child {
                margin-right: 50px;
                min-width: 260px;
                max-width: 320px;
            }
        }
        
        /* Small Desktop (1000px - 1199px) */
        @media (max-width: 1199px) and (min-width: 1000px) {
            .container { 
                padding: 28px 2vw 60px 2vw; 
            }
            .bracket-bg {
                padding: 28px;
                border-radius: 16px;
            }
            .bracket { 
                gap: 40px; 
                min-width: 800px; 
                padding: 14px 14px;
            }
            .round { 
                min-width: 220px; 
                max-width: 280px;
            }
            .match {
                min-width: 220px;
                max-width: 280px;
                margin: 10px 0;
            }
            .participant {
                padding: 16px 18px;
                font-size: 0.9em;
                min-width: 180px;
                max-width: 240px;
            }
            .participant-number {
                min-width: 24px;
                height: 24px;
                font-size: 0.75em;
                margin-right: 8px;
            }
            .round h3 {
                font-size: 1em;
                padding: 6px 12px;
                margin-bottom: 18px;
            }
            .round:last-child {
                margin-right: 40px;
                min-width: 240px;
                max-width: 300px;
            }
        }
        
        /* Tablet Landscape (768px - 999px) */
        @media (max-width: 999px) and (min-width: 768px) {
            .container { 
                padding: 20px 1vw 50px 1vw; 
            }
            .bracket-bg {
                padding: 20px;
                border-radius: 14px;
            }
            .bracket { 
                gap: 30px; 
                min-width: 700px; 
                padding: 12px 12px;
            }
            .round { 
                min-width: 200px; 
                max-width: 260px;
            }
            .match {
                min-width: 200px;
                max-width: 260px;
                margin: 8px 0;
            }
            .participant {
                padding: 14px 16px;
                font-size: 0.85em;
                min-width: 160px;
                max-width: 220px;
            }
            .participant-number {
                min-width: 22px;
                height: 22px;
                font-size: 0.7em;
                margin-right: 6px;
            }
            .round h3 {
                font-size: 0.9em;
                padding: 4px 10px;
                margin-bottom: 16px;
            }
            .participant-name {
                font-size: 0.9em;
            }
            .participant-continent {
                font-size: 0.8em;
            }
            .round:last-child {
                margin-right: 30px;
                min-width: 220px;
                max-width: 280px;
            }
        }
        
        /* Tablet Portrait (600px - 767px) */
        @media (max-width: 767px) and (min-width: 600px) {
            .container { 
                padding: 16px 0 40px 0; 
            }
            .bracket-bg {
                padding: 16px;
                border-radius: 12px;
            }
            .bracket { 
                gap: 25px; 
                min-width: 600px; 
                padding: 10px 10px;
            }
            .round { 
                min-width: 180px; 
                max-width: 240px;
            }
            .match {
                min-width: 180px;
                max-width: 240px;
                margin: 6px 0;
            }
            .participant {
                padding: 12px 14px;
                font-size: 0.8em;
                min-width: 140px;
                max-width: 200px;
            }
            .participant-number {
                min-width: 20px;
                height: 20px;
                font-size: 0.65em;
                margin-right: 5px;
            }
            .round h3 {
                font-size: 0.85em;
                padding: 3px 8px;
                margin-bottom: 12px;
            }
            .participant-name {
                font-size: 0.85em;
            }
            .participant-continent {
                font-size: 0.75em;
            }
            .round:last-child {
                margin-right: 25px;
                min-width: 200px;
                max-width: 260px;
            }
        }
        
        /* Mobile Large (480px - 599px) */
        @media (max-width: 599px) and (min-width: 480px) {
            .container { 
                padding: 12px 0 32px 0; 
            }
            .bracket-bg {
                padding: 12px;
                border-radius: 10px;
                margin-top: 16px;
            }
            .bracket-container {
                padding: 12px 20px 12px 0;
                border-radius: 10px;
            }
            .bracket { 
                gap: 20px; 
                min-width: 500px; 
                padding: 8px 8px;
            }
            .round { 
                min-width: 160px; 
                max-width: 200px;
            }
            .match {
                min-width: 160px;
                max-width: 200px;
                margin: 5px 0;
            }
            .participant {
                padding: 10px 12px;
                font-size: 0.75em;
                min-width: 120px;
                max-width: 180px;
            }
            .participant-number {
                min-width: 18px;
                height: 18px;
                font-size: 0.6em;
                margin-right: 4px;
            }
            .round h3 {
                font-size: 0.8em;
                padding: 2px 6px;
                margin-bottom: 10px;
            }
            .participant-name {
                font-size: 0.8em;
            }
            .participant-continent {
                font-size: 0.7em;
            }
            .round:last-child {
                margin-right: 20px;
                min-width: 180px;
                max-width: 220px;
            }
        }
        
        /* Mobile Medium (400px - 479px) */
        @media (max-width: 479px) and (min-width: 400px) {
            .container { 
                padding: 8px 0 28px 0; 
            }
            .bracket-bg {
                padding: 8px;
                border-radius: 8px;
                margin-top: 12px;
            }
            .bracket-container {
                padding: 8px 15px 8px 0;
                border-radius: 8px;
            }
            .bracket { 
                gap: 15px; 
                min-width: 400px; 
                padding: 6px 6px;
            }
            .round { 
                min-width: 140px; 
                max-width: 180px;
            }
            .match {
                min-width: 140px;
                max-width: 180px;
                margin: 4px 0;
            }
            .participant {
                padding: 8px 10px;
                font-size: 0.7em;
                min-width: 100px;
                max-width: 160px;
            }
            .participant-number {
                min-width: 16px;
                height: 16px;
                font-size: 0.55em;
                margin-right: 3px;
            }
            .round h3 {
                font-size: 0.75em;
                padding: 1px 4px;
                margin-bottom: 8px;
            }
            .participant-name {
                font-size: 0.75em;
            }
            .participant-continent {
                font-size: 0.65em;
            }
            .round:last-child {
                margin-right: 15px;
                min-width: 160px;
                max-width: 200px;
            }
        }
        
        /* Mobile Small (320px - 399px) */
        @media (max-width: 399px) {
            .container { 
                padding: 4px 0 24px 0; 
            }
            .bracket-bg {
                padding: 6px;
                border-radius: 6px;
                margin-top: 8px;
            }
            .bracket-container {
                padding: 6px 10px 6px 0;
                border-radius: 6px;
            }
            .bracket { 
                gap: 12px; 
                min-width: 350px; 
                padding: 4px 4px;
            }
            .round { 
                min-width: 120px; 
                max-width: 160px;
            }
            .match {
                min-width: 120px;
                max-width: 160px;
                margin: 3px 0;
            }
            .participant {
                padding: 6px 8px;
                font-size: 0.65em;
                min-width: 80px;
                max-width: 140px;
            }
            .participant-number {
                min-width: 14px;
                height: 14px;
                font-size: 0.5em;
                margin-right: 2px;
            }
            .round h3 {
                font-size: 0.7em;
                padding: 1px 3px;
                margin-bottom: 6px;
            }
            .participant-name {
                font-size: 0.7em;
            }
            .participant-continent {
                font-size: 0.6em;
            }
            .round:last-child {
                margin-right: 10px;
                min-width: 140px;
                max-width: 180px;
            }
        }
        
        /* Extra Small Mobile (below 320px) */
        @media (max-width: 319px) {
            .container { 
                padding: 2px 0 20px 0; 
            }
            .bracket-bg {
                padding: 4px;
                border-radius: 4px;
                margin-top: 6px;
            }
            .bracket-container {
                padding: 4px 8px 4px 0;
                border-radius: 4px;
            }
            .bracket { 
                gap: 8px; 
                min-width: 300px; 
                padding: 2px 2px;
            }
            .round { 
                min-width: 100px; 
                max-width: 140px;
            }
            .match {
                min-width: 100px;
                max-width: 140px;
                margin: 2px 0;
            }
            .participant {
                padding: 4px 6px;
                font-size: 0.6em;
                min-width: 60px;
                max-width: 120px;
            }
            .participant-number {
                min-width: 12px;
                height: 12px;
                font-size: 0.45em;
                margin-right: 1px;
            }
            .round h3 {
                font-size: 0.65em;
                padding: 0 2px;
                margin-bottom: 4px;
            }
            .participant-name {
                font-size: 0.65em;
            }
            .participant-continent {
                font-size: 0.55em;
            }
            .round:last-child {
                margin-right: 8px;
                min-width: 120px;
                max-width: 160px;
            }
        }
        h1 { text-align: center; font-size: 2rem; margin-bottom: 18px; font-weight: 800; color: #0ea5e9; }
        .form-group { margin-bottom: 18px; }
        .table-wrap { overflow-x: auto; margin-top: 20px; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; background: #f1f5f9; border-radius: 12px; overflow: hidden; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 1.05rem; }
        th { background: #0ea5e9; color: #fff; font-size: 1.1rem; font-weight: 700; }
        
        /* Responsive typography and form */
        @media (max-width: 768px) {
            h1 {
                font-size: 1.6rem;
                margin-bottom: 16px;
            }
            
            .form-group {
                margin-bottom: 16px;
            }
            
            table {
                border-radius: 8px;
            }
            
            th, td {
                padding: 10px 8px;
                font-size: 0.95rem;
            }
            
            th {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 480px) {
            h1 {
                font-size: 1.4rem;
                margin-bottom: 14px;
            }
            
            .form-group {
                margin-bottom: 14px;
            }
            
            table {
                border-radius: 6px;
            }
            
            th, td {
                padding: 8px 6px;
                font-size: 0.85rem;
            }
            
            th {
                font-size: 0.9rem;
            }
        }
        tr:last-child td { border-bottom: none; }
        .empty-state { text-align: center; padding: 40px 20px; background: #f1f5f9; border-radius: 12px; color: #64748b; margin: 30px auto; max-width: 600px; }
        
        /* Responsive empty state */
        @media (max-width: 768px) {
            .empty-state {
                padding: 32px 16px;
                margin: 24px auto;
                border-radius: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .empty-state {
                padding: 24px 12px;
                margin: 20px auto;
                border-radius: 8px;
            }
        }
        .participant {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.2s ease;
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85em;
            background: transparent; 
            border-radius: 0; 
            box-shadow: none; 
            margin: 0; 
            min-width: 140px;
            max-width: 200px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .participant:last-child {
            border-bottom: none;
        }
        .participant:hover {
            background: #f5f5f5;
        }

        .participant:first-child:hover {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.15) 0%, rgba(0, 123, 255, 0.08) 100%);
        }

        .participant:first-child:hover .participant-name {
            color: #007bff;
        }

        .participant:last-child:hover {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.08) 100%);
        }

        .participant:last-child:hover .participant-name {
            color: #dc3545;
        }
        .participant.winner {
            background: #e0ffe0;
            color: #333;
            font-weight: bold;
        }

        .participant.winner:first-child {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.2) 0%, rgba(0, 123, 255, 0.1) 100%);
            border-left: 3px solid #007bff;
        }

        .participant.winner:first-child .participant-name {
            color: #007bff;
        }

        .participant.winner:last-child {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.2) 0%, rgba(220, 53, 69, 0.1) 100%);
            border-left: 3px solid #dc3545;
        }

        .participant.winner:last-child .participant-name {
            color: #dc3545;
        }
        .bracket-slot.active::after {
            content: '';
        }
        .participant.bye { color: #bdbdbd; font-style: italic; font-weight: 500; background: #f1f5f9; }
        .participant-number {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            font-weight: 700;
            font-size: 0.8em;
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 2px 6px rgba(14, 165, 233, 0.3);
            flex-shrink: 0;
        }

        .participant-info { 
            display: flex; 
            flex-direction: column; 
            gap: 2px; 
            width: 100%; 
            min-width: 0;
            flex: 1;
        }
        .participant-name {
            font-weight: bold;
            font-size: 0.85em;
            color: #333;
            margin-bottom: 2px;
            white-space: normal;
            line-height: 1.2;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .participant-continent {
            font-size: 0.75em;
            color: #888;
            font-weight: normal;
            margin-top: 2px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            line-height: 1.1;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
        }
        .bracket-athlete-info { display: none; }
        .participant:first-child {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.1) 0%, rgba(0, 123, 255, 0.05) 100%);
            border-left: 3px solid #007bff;
        }

        .participant:first-child .participant-name {
            color: #007bff;
        }

        .participant:last-child {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
            border-left: 3px solid #dc3545;
        }

        .participant:last-child .participant-name {
            color: #dc3545;
        }

        /* Warna nama peserta sesuai slot untuk winner */
        .participant.winner:first-child .participant-name {
            color: #007bff;
        }

        .participant.winner:last-child .participant-name {
            color: #dc3545;
        }

        .participant.winner:first-child {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.15) 0%, rgba(0, 123, 255, 0.08) 100%);
            border-left: 3px solid #007bff;
        }

        .participant.winner:last-child {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.08) 100%);
            border-left: 3px solid #dc3545;
        }
        /* Winner column dynamic color */
        .participant.winner-blue {
            background: linear-gradient(135deg, rgba(0, 123, 255, 0.15) 0%, rgba(0, 123, 255, 0.08) 100%);
            border-left: 3px solid #007bff;
        }
        .participant.winner-blue .participant-name { color: #007bff; }
        .participant.winner-red {
            background: linear-gradient(135deg, rgba(220, 53, 69, 0.15) 0%, rgba(220, 53, 69, 0.08) 100%);
            border-left: 3px solid #dc3545;
        }
        .participant.winner-red .participant-name { color: #dc3545; }
        .radio-button {
            width: 14px;
            height: 14px;
            border: 1px solid #999;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }
        .participant.winner .radio-button {
            background: #28a745;
            border-color: #28a745;
        }
        .bracket-check-disabled { pointer-events: none; opacity: 0.5; }
        .bracket-eliminated { color: #bdbdbd !important; text-decoration: line-through; }
        /* Bracket Actions Container */
        .bracket-actions-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-top: 1px solid #e2e8f0;
            padding: 16px 20px;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .bracket-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .bracket-action-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 160px;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .bracket-action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.2);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .bracket-action-btn:hover::before {
            opacity: 1;
        }
        
        .bracket-action-btn i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }
        
        .bracket-action-btn:hover i {
            transform: scale(1.1);
        }
        
        .bracket-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .bracket-action-btn:active {
            transform: translateY(0);
        }
        
        .print-btn {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            color: white;
        }
        
        .print-btn:hover {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        }
        
        .save-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .save-btn:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        
        .reset-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .reset-btn:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }
        
        /* Responsive design untuk button actions */
        @media (max-width: 768px) {
            .bracket-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .bracket-action-btn {
                min-width: 100%;
                padding: 14px 20px;
            }
            
            .bracket-actions-container {
                padding: 12px 16px;
            }
        }
        
        @media (max-width: 480px) {
            .bracket-actions {
                gap: 8px;
            }
            
            .bracket-action-btn {
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            
            .bracket-actions-container {
                padding: 10px 12px;
            }
        }
        
        @media (max-width: 320px) {
            .bracket-actions {
                gap: 6px;
            }
            
            .bracket-action-btn {
                padding: 10px 14px;
                font-size: 0.85rem;
            }
            
            .bracket-actions-container {
                padding: 8px 10px;
            }
        }
        
        /* Tambahkan padding bottom pada container untuk menghindari overlap dengan fixed buttons */
        .container {
            padding-bottom: 150px;
        }
        
        /* Pastikan bracket container tidak tertutup */
        .bracket-bg {
            margin-bottom: 30px;
        }
        
        /* Tambahkan margin bottom pada bracket container */
        .bracket-container {
            margin-bottom: 20px;
        }
        
        /* Tambahkan margin bottom pada tab content */
        .tab-content {
            padding-bottom: 20px;
        }
        .bracket-notif { text-align: center; margin-top: 18px; font-weight: 600; font-size: 1.08em; }
        .bracket-notif.success { color: #22c55e; }
        .bracket-notif.error { color: #ef4444; }
        .bracket-slot.blue-slot {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(2, 132, 199, 0.08) 100%);
            border-left: 4px solid #0ea5e9;
        }
        .bracket-slot.red-slot {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(220, 38, 38, 0.08) 100%);
            border-left: 4px solid #ef4444;
        }
        .bracket-slot.blue-slot.active {
            border-left: 4px solid #0ea5e9;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.15) 0%, rgba(2, 132, 199, 0.15) 100%);
        }
        .bracket-slot.red-slot.active {
            border-left: 4px solid #ef4444;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
        }
        .round h3 {
            text-align: center;
            margin-bottom: 12px;
            color: #555;
            font-size: 1em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        .bracket-label::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.1) 0%, rgba(2, 132, 199, 0.1) 100%);
            border-radius: 12px;
            z-index: -1;
        }
        /* Efek untuk teks "Lolos" */
        .bracket-slot .bracket-slot-inner span[style*="color:#22c55e"] {
            text-shadow: 0 1px 2px rgba(34,197,94,0.2);
            font-weight: 800;
        }
        .bracket-slot.active .bracket-slot-inner span[style*="color:#22c55e"] {
            color: white !important;
            text-shadow: 0 1px 2px rgba(255,255,255,0.3);
        }
        .bracket-bg, .bracket-container, .bracket {
            background: #fff !important;
            overflow: visible !important;
        }
        
        /* Tab Navigation Styles - Consistent with perlombaan-detail.php */
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
            z-index: 1;
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
            z-index: 1;
        }
        
        .tab-btn span {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-btn i {
            position: relative;
            z-index: 2;
            font-size: 1.1rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .tab-btn:hover {
            color: #0ea5e9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.2);
        }
        
        .tab-btn:hover::before {
            opacity: 0.1;
            transform: scale(1);
        }
        
        .tab-btn:hover i {
            transform: scale(1.15) rotate(5deg);
            color: #0ea5e9;
        }
        
        .tab-btn:hover span {
            transform: translateX(2px);
        }
        
        .tab-btn:active {
            transform: translateY(0);
            transition: all 0.1s ease;
        }
        
        .tab-btn:active::after {
            width: 300px;
            height: 300px;
            opacity: 0;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
            transform: translateY(-2px);
            animation: tabActivePulse 0.6s ease-out;
        }
        
        .tab-btn.active::before {
            opacity: 1;
            transform: scale(1);
        }
        
        .tab-btn.active i {
            transform: scale(1.2) rotate(0deg);
            animation: iconBounce 0.6s ease-out;
        }
        
        .tab-btn.active span {
            transform: translateX(0);
            animation: textSlide 0.4s ease-out;
        }
        
        @keyframes tabActivePulse {
            0% {
                box-shadow: 0 4px 16px rgba(14, 165, 233, 0.25);
                transform: translateY(-1px);
            }
            50% {
                box-shadow: 0 12px 35px rgba(14, 165, 233, 0.4);
                transform: translateY(-3px);
            }
            100% {
                box-shadow: 0 8px 25px rgba(14, 165, 233, 0.3);
                transform: translateY(-2px);
            }
        }
        
        @keyframes iconBounce {
            0% {
                transform: scale(1) rotate(0deg);
            }
            50% {
                transform: scale(1.3) rotate(10deg);
            }
            100% {
                transform: scale(1.2) rotate(0deg);
            }
        }
        
        @keyframes textSlide {
            0% {
                transform: translateX(-5px);
                opacity: 0.8;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
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
            animation: contentSlideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes contentSlideIn {
            0% {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }
            50% {
                opacity: 0.7;
                transform: translateY(10px) scale(0.99);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Loading state for tab switching */
        .tab-content.loading {
            position: relative;
        }
        
        .tab-content.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #e2e8f0;
            border-top: 2px solid #0ea5e9;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .tab-navigation {
                flex-direction: column;
            }
            
            .tab-btn {
                text-align: center;
                justify-content: center;
            }
        }
        /* --- Tree Bracket Layout --- */
        .bracket-tree {
            overflow-x: auto;
            background: #fff;
            padding: 20px 0;
            position: relative;
            min-height: 400px;
        }
        .bracket-tree-inner {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            position: relative;
            min-width: 900px;
        }
        .tree-col {
            position: relative;
            min-width: 220px;
            width: 220px;
            height: 1000px; /* will be auto by JS if needed */
        }
        .tree-col-label {
            position: relative;
            z-index: 2;
            background: #f1f5f9;
            border-radius: 8px;
            margin-bottom: 8px;
            padding: 8px 0;
            font-size: 1.1em;
        }
        .tree-match {
            position: absolute;
            left: 0;
            width: 200px;
            min-height: 80px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 0;
            z-index: 2;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: stretch;
        }
        .tree-match .participant {
            margin: 0;
            border-radius: 0;
            border: none;
            box-shadow: none;
            min-width: 0;
            max-width: 100%;
        }
        /* Konektor antar match (garis) */
        .tree-connector {
            position: absolute;
            width: 40px;
            height: 2px;
            background: #0ea5e9;
            left: 200px;
            z-index: 1;
        }
        .tree-connector-vertical {
            position: absolute;
            width: 2px;
            background: #0ea5e9;
            left: 220px;
            z-index: 1;
        }
        @media (max-width: 1200px) {
            .bracket-tree-inner { min-width: 700px; }
            .tree-col, .tree-match { width: 160px; min-width: 160px; }
            .tree-col { min-width: 160px; }
        }
        @media (max-width: 768px) {
            .bracket-tree-inner { min-width: 500px; }
            .tree-col, .tree-match { width: 120px; min-width: 120px; }
            .tree-col { min-width: 120px; }
        }
        </style>
    <style>
    @media print {
        body * { visibility: hidden !important; }
        .container, .container * { visibility: visible !important; }
        .container { position: absolute !important; left: 0; top: 0; width: 100vw !important; box-shadow: none !important; background: #fff !important; }
        .bracket-save-btn, #printBracketBtn, .btn-primary, #bracketNotif, form, [style*="position:absolute"], .tab-content:not(.active), .bracket-actions-container, .table-header, .tab-navigation, .table-wrap, h1 { display: none !important; }
        .competition-title, #keterangan-bracket, .bracket-bg, .bracket-container { display: block !important; }
        /* Keep bracket horizontal in print */
        .bracket { display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: flex-start !important; justify-content: flex-start !important; white-space: nowrap !important; }
        .round { display: flex !important; flex-direction: column !important; flex-shrink: 0 !important; flex-grow: 0 !important; break-inside: avoid !important; page-break-inside: avoid !important; }
        .match { break-inside: avoid !important; page-break-inside: avoid !important; }
        .bracket-container { overflow: visible !important; white-space: nowrap !important; }
        .bracket-bg, .bracket-container, .bracket { overflow: visible !important; background: #fff !important; }
        .bracket-match { border: 2px solid #e2e8f0 !important; }
        .bracket-slot.blue-slot { background: #e3f2fd !important; border-left: 3px solid #2196f3 !important; }
        .bracket-slot.red-slot { background: #ffebee !important; border-left: 3px solid #f44336 !important; }
        .bracket-match::after { border-top: 3px solid #3b82f6 !important; }
        .bracket-col:nth-last-child(2) .bracket-match::after { border-top: 3px solid #22c55e !important; }
        .bracket-match.connector-to-blue::after { top: 20% !important; }
        .bracket-match.connector-to-red::after { top: 80% !important; }
        .bracket-match.connector-curve::after { display: block !important; }
        .bracket-cols { gap: 32px !important; }
    }
    </style>
</head>
<body>
<div class="container">
<?php if (isset($_GET['competition_id'])): ?>
    <div style="position:absolute;left:24px;top:24px;z-index:10;">
        <a href="perlombaan-detail.php?id=<?php echo (int)$_GET['competition_id']; ?>" class="btn-primary" style="display:inline-block;padding:10px 28px;font-size:1.08em;border-radius:8px;background:#0ea5e9;color:#fff;font-weight:700;text-decoration:none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Perlombaan
        </a>
    </div>
<?php endif; ?>
    <h1><i class="fas fa-sitemap"></i> Bagan Tanding - Hasil Drawing</h1>
    
    <!-- Navigation Tabs -->
    <div class="table-container">
        <div class="table-header">
                            <div class="tab-navigation">
                    <button class="tab-btn active" onclick="showTab('generate-bracket')">
                        <i class="fas fa-sitemap"></i>
                        <span>Generate Bracket</span>
                    </button>
                </div>
        </div>
    
        <!-- Generate Bracket Tab -->
        <div id="generate-bracket-tab" class="tab-content active">
    <form method="GET" style="display:flex;gap:18px;align-items:end;justify-content:center;margin-bottom:24px; flex-wrap: wrap;">
        <?php if (isset($_GET['competition_id'])): ?>
            <input type="hidden" name="competition_id" value="<?= htmlspecialchars($_GET['competition_id']) ?>">
        <?php endif; ?>
        <div class="form-group" style="min-width:220px;">
            <label for="draw_id">Pilih Hasil Drawing</label>
            <select name="draw_id" id="draw_id" class="form-control" onchange="this.form.submit()">
                <option value="">-- Pilih Hasil Drawing --</option>
                <?php foreach ($batches as $batch): ?>
                    <?php 
                    // Ambil jumlah peserta untuk batch ini
                    $stmt_count = $pdo->prepare("SELECT urutan FROM daftar_peserta_draws WHERE id = ?");
                    $stmt_count->execute([$batch['draw_id']]);
                    $urutan_count = json_decode($stmt_count->fetchColumn(), true);
                    $peserta_count = $urutan_count ? count($urutan_count) : 0;
                    ?>
                    <option value="<?= $batch['draw_id'] ?>" <?= $selected_draw_id == $batch['draw_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($batch['batch_name']) ?> (<?= $peserta_count ?> peserta, <?= $batch['created_at'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="jumlah_peserta">Jumlah Peserta</label>
            <select name="jumlah_peserta" id="jumlah_peserta" class="form-control" onchange="this.form.submit()">
                <?php 
                $max_peserta = $batch_peserta_count > 0 ? $batch_peserta_count : 36;
                $min_peserta = 3;
                $current_selection = isset($_GET['jumlah_peserta']) ? (int)$_GET['jumlah_peserta'] : $max_peserta;
                
                // Pastikan current_selection tidak melebihi max_peserta
                if ($current_selection > $max_peserta) {
                    $current_selection = $max_peserta;
                }
                
                for ($i = $min_peserta; $i <= $max_peserta; $i++): 
                ?>
                    <option value="<?= $i ?>" <?= ($current_selection == $i) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <?php if ($batch_peserta_count > 0): ?>
                <small style="color: #64748b; font-size: 0.9rem; margin-top: 4px; display: block;">
                    Batch ini memiliki <?= $batch_peserta_count ?> peserta
                </small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="versi_bagan">Versi Bagan</label>
            <select name="versi_bagan" id="versi_bagan" class="form-control" onchange="this.form.submit()">
                <option value="versi1" <?= ($versi_bagan == 'versi1') ? 'selected' : '' ?>>Versi 1</option>
                <option value="versi2" <?= ($versi_bagan == 'versi2') ? 'selected' : '' ?>>Versi 2</option>
            </select>
        </div>
    </form>
    
    <?php
    $jumlah_peserta = isset($_GET['jumlah_peserta']) ? max(3, min($batch_peserta_count, (int)$_GET['jumlah_peserta'])) : $batch_peserta_count;
    
    // Jika belum ada batch dipilih, gunakan default
    if ($batch_peserta_count == 0) {
        $jumlah_peserta = isset($_GET['jumlah_peserta']) ? max(3, min(36, (int)$_GET['jumlah_peserta'])) : 3;
    }
    
    $displayPeserta = array_slice($pesertaUrut, 0, $jumlah_peserta);
    ?>
    
    <?php if ($selected_draw_id && $displayPeserta): ?>
    <!-- Validasi Jumlah Peserta -->
    <?php if ($jumlah_peserta > $batch_peserta_count): ?>
    <div style="background: rgba(239,68,68,0.10); border-radius: 12px; padding: 18px 20px; margin-bottom: 24px; font-size: 1.2rem; color: #ef4444; font-weight: 700; text-align: center;">
        <i class="fas fa-exclamation-triangle"></i> Peringatan: Jumlah peserta yang dipilih (<?= $jumlah_peserta ?>) melebihi jumlah peserta dalam batch (<?= $batch_peserta_count ?>). Sistem akan menggunakan <?= $batch_peserta_count ?> peserta pertama.
    </div>
    <?php endif; ?>
    

    <?php endif; ?>
    
    <?php if ($selected_draw_id && $displayPeserta): ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Nama Peserta</th>
                        <th>Kontingen</th>
                        <th>Kategori Umur</th>
                        <th>Jenis Kelamin</th>
                        <th>Jenis Kompetisi</th>
                        <th>Kategori Tanding</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($displayPeserta as $i => $p): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($p['nama']) ?></td>
                        <td><?= htmlspecialchars($p['kontingen']) ?></td>
                        <td><?= htmlspecialchars($p['kategori_umur']) ?></td>
                        <td><?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : ($p['jenis_kelamin'] == 'P' ? 'Perempuan' : htmlspecialchars($p['jenis_kelamin'])) ?></td>
                        <td><?= htmlspecialchars($p['jenis_kompetisi']) ?></td>
                        <td><?= htmlspecialchars($p['kategori_tanding']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Keterangan Filter -->
        <div id="keterangan-bracket" style="background: rgba(14,165,233,0.10); border-radius: 12px; padding: 18px 20px; margin-top: 24px; font-size: 1.4rem; color: #0ea5e9; font-weight: 800; text-align: center;">
            <?php 
            $keterangan_parts = [];
            
            // Jenis Kompetisi
            if ($filter_jenis_kompetisi) {
                $keterangan_parts[] = strtoupper($filter_jenis_kompetisi);
            }
            
            // Kategori Tanding (jika jenis kompetisi tanding)
            if ($filter_jenis_kompetisi && stripos($filter_jenis_kompetisi, 'tanding') !== false && $filter_kategori_tanding) {
                $keterangan_parts[] = strtoupper($filter_kategori_tanding);
            }
            
            // Kategori Umur
            if ($filter_kategori_umur) {
                $keterangan_parts[] = strtoupper($filter_kategori_umur);
            }
            
            // Jenis Kelamin
            if ($filter_jenis_kelamin) {
                $jenis_kelamin_text = ($filter_jenis_kelamin == 'L') ? 'PUTRA' : (($filter_jenis_kelamin == 'P') ? 'PUTRI' : strtoupper($filter_jenis_kelamin));
                $keterangan_parts[] = $jenis_kelamin_text;
            }
            
            // Jumlah Peserta
            $keterangan_parts[] = count($displayPeserta) . ' PESERTA';
            
            if (!empty($keterangan_parts)) {
                echo '<strong>' . implode(' / ', $keterangan_parts) . '</strong>';
            }
            ?>
        </div>
        <div style="margin-top:36px;">
        <?php
        $participantCount = count($displayPeserta);
        
        $pesertaArr = [];
        foreach ($displayPeserta as $i => $p) {
            $pesertaArr[$i+1] = [
                'name' => $p['nama'],
                'id' => $i + 1
            ];
        }
        
        if ($versi_bagan === 'versi2') {
            require_once __DIR__ . '/bracket-logic2.php';
        } else {
            require_once __DIR__ . '/bracket-logic1.php';
        }
        $bracket = new BracketGenerator($participantCount, $versi_bagan, $pesertaArr);
        $structure = $bracket->getBracketStructure();
        
        if (!$structure || !is_array($structure)) $structure = [];
        $structure = array_filter($structure, 'is_array');
        $structureIndexed = array_values($structure);
        
        echo '<div class="bracket-bg">';
        
        function getCustomRoundLabels($structure, $pesertaCount) {
            if (!$structure || !is_array($structure)) $structure = [];
            $structure = array_filter($structure, 'is_array');
            $labels = [];
            $roundCount = count($structure);
            $matchCounts = array_values(array_map('count', $structure));
            
            // Custom labels based on common tournament rounds
            if ($pesertaCount > 16) {
                $labels[] = 'Babak Awal'; // For larger brackets, first round might be play-in
            }
            
            // Determine standard round labels
            $currentPowerOf2 = pow(2, floor(log($pesertaCount, 2)));
            if ($currentPowerOf2 < $pesertaCount) { // If not a perfect power of 2, there's a play-in round
                $currentPowerOf2 *= 2; // Next power of 2 for the main bracket
            }
            
            while ($currentPowerOf2 >= 1) {
                if ($currentPowerOf2 == 32) $labels[] = 'Per 32';
                else if ($currentPowerOf2 == 16) $labels[] = 'Per 16';
                else if ($currentPowerOf2 == 8) $labels[] = 'Per 8';
                else if ($currentPowerOf2 == 4) $labels[] = 'Per 4';
                else if ($currentPowerOf2 == 2) $labels[] = 'Semifinal';
                else if ($currentPowerOf2 == 1) $labels[] = 'Final';
                else $labels[] = 'Babak ' . ($currentPowerOf2); // Fallback for other sizes
                
                if ($currentPowerOf2 == 1) break; // Stop after Final
                $currentPowerOf2 /= 2;
            }
            
            // Trim labels to match actual rounds generated
            return array_slice($labels, count($labels) - $roundCount);
        }

        $roundLabels = getCustomRoundLabels($structure, $participantCount);
        
        // --- Ambil data pemenang dari database untuk draw_id ini ---
        $winnerMap = [];
        $winnerPlayerMap = [];
        if ($selected_draw_id) {
            $stmt = $pdo->prepare("SELECT round, match_id, winner_player_id FROM bracket_results WHERE draw_id = ?");
            $stmt->execute([$selected_draw_id]);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $winnerMap[$row['round'] . '-' . $row['match_id']] = $row['winner_player_id'];
                $winnerPlayerMap[$row['round']][$row['match_id']] = $row['winner_player_id'];
            }
        }
        
        echo '<div class="bracket-container">';
        echo '<div class="bracket">';
        $colIdx = 0;
        $totalCols = count($structure);
        $matchHeights = [];
        $matchGap = 24; // px, jarak antar match
        // Hitung tinggi match per babak
        foreach ($structure as $roundName => $matches) {
            $matchHeights[$colIdx] = [];
            $numMatches = count($matches);
            for ($i = 0; $i < $numMatches; $i++) {
                $matchHeights[$colIdx][$i] = 80; // default tinggi 80px per match
            }
            $colIdx++;
        }
        // Render bracket horizontal dengan offset margin-top dinamis
        $colIdx = 0;
        $matchId = 1;
        $prevMatchOffsets = [];
        foreach ($structure as $roundName => $matches) {
            $numMatches = count($matches);
            echo '<div class="round" data-round="' . ($colIdx+1) . '" style="display:flex;flex-direction:column;align-items:center;position:relative;">';
            echo '<h3>' . htmlspecialchars($roundLabels[$colIdx] ?? $roundName) . '</h3>';
            $matchOffsets = [];
            for ($i = 0; $i < $numMatches; $i++) {
                $marginTop = 0;
                if ($colIdx > 0) {
                    // Setiap match babak berikutnya berada di tengah-tengah dua match babak sebelumnya
                    $prevIdx1 = $i * 2;
                    $prevIdx2 = $i * 2 + 1;
                    $prev1 = isset($prevMatchOffsets[$prevIdx1]) ? $prevMatchOffsets[$prevIdx1] : 0;
                    $prev2 = isset($prevMatchOffsets[$prevIdx2]) ? $prevMatchOffsets[$prevIdx2] : ($prev1 + 60 + $matchGap);
                    $marginTop = ($prev1 + $prev2) / 2 - ($i > 0 ? array_sum(array_slice($matchOffsets, 0, $i)) + $i * 60 + $i * $matchGap : 0);
                }
                $matchOffsets[] = $marginTop;
                echo '<div class="match" data-round="' . ($colIdx+1) . '" data-match="' . ($i+1) . '" style="margin-top:' . ($marginTop > 0 ? $marginTop : 0) . 'px;">';
                // Player 1
                $m = $matches[$i];
                $p1id = $m['player1']['id'];
                $p2id = $m['player2']['id'];
                $p1_name = $m['player1']['name'];
                $p2_name = $m['player2']['name'];
                
                // Determine waiting state for next rounds (no winner set yet)
                $waiting1 = false;
                $actual_p1id = $p1id; // Store original p1id for winner comparison
                if ($colIdx > 0 && $p1id !== 'bye') {
                    $prevMatchId1 = $i * 2 + 1; // 1-based
                    if (!isset($winnerPlayerMap[$colIdx][$prevMatchId1])) {
                        $waiting1 = true;
                    } else {
                        $actual_p1id = $winnerPlayerMap[$colIdx][$prevMatchId1];
                    }
                }
                
                // Now determine winner status using the actual player ID that will be displayed
                $winner1_active = (isset($winnerMap[($colIdx+1).'-'.($i+1)]) && (string)$winnerMap[($colIdx+1).'-'.($i+1)]===(string)$actual_p1id);

                // Player 1 slot
                if ($waiting1) {
                    echo '<div class="participant"><div class="participant-info"><span class="participant-name" style="color:#bdbdbd;font-style:italic;">menunggu pemenang</span></div></div>';
                } else if ($p1id === 'bye') {
                    echo '<div class="participant bye"><div class="participant-info"><span class="participant-name">BYE</span></div></div>';
                } else if ($actual_p1id && isset($displayPeserta[$actual_p1id-1])) {
                    $p1_data = $displayPeserta[$actual_p1id-1];
                    $p1_name = htmlspecialchars($p1_data['nama']);
                    $p1_kontingen = htmlspecialchars($p1_data['kontingen']);
                    $class1 = 'participant' . ($winner1_active ? ' winner' : '');
                    echo '<div class="' . $class1 . '"><div class="participant-number">' . $actual_p1id . '</div><div class="participant-info"><span class="participant-name">' . $p1_name . '</span>';
                    if ($p1_kontingen) echo '<span class="participant-continent">' . $p1_kontingen . '</span>';
                    if ($winner1_active) echo '<span class="lolos-label" style="color:#22c55e;font-weight:700;font-size:0.8em;margin-top:1px;">Lolos</span>';
                    echo '</div><div class="radio-button' . ($winner1_active ? ' active' : '') . '" data-player="' . $actual_p1id . '" data-round="' . ($colIdx+1) . '" data-match="' . ($i+1) . '"></div></div>';
                } else {
                    echo '<div class="participant"><div class="participant-info"><span class="participant-name" style="color:#bdbdbd;font-style:italic;">-</span></div></div>';
                }
                // Player 2 slot
                $waiting2 = false;
                $actual_p2id = $p2id; // Store original p2id for winner comparison
                if ($colIdx > 0 && $p2id !== 'bye') {
                    $prevMatchId2 = $i * 2 + 2; // 1-based
                    if (!isset($winnerPlayerMap[$colIdx][$prevMatchId2])) {
                        $waiting2 = true;
                    } else {
                        $actual_p2id = $winnerPlayerMap[$colIdx][$prevMatchId2];
                    }
                }
                
                // Now determine winner status using the actual player ID that will be displayed
                $winner2_active = (isset($winnerMap[($colIdx+1).'-'.($i+1)]) && (string)$winnerMap[($colIdx+1).'-'.($i+1)]===(string)$actual_p2id);

                if ($waiting2) {
                    echo '<div class="participant"><div class="participant-info"><span class="participant-name" style="color:#bdbdbd;font-style:italic;">menunggu pemenang</span></div></div>';
                } else if ($p2id === 'bye') {
                    echo '<div class="participant bye"><div class="participant-info"><span class="participant-name">BYE</span></div></div>';
                } else if ($actual_p2id && isset($displayPeserta[$actual_p2id-1])) {
                    $p2_data = $displayPeserta[$actual_p2id-1];
                    $p2_name = htmlspecialchars($p2_data['nama']);
                    $p2_kontingen = htmlspecialchars($p2_data['kontingen']);
                    $class2 = 'participant' . ($winner2_active ? ' winner' : '');
                    echo '<div class="' . $class2 . '"><div class="participant-number">' . $actual_p2id . '</div><div class="participant-info"><span class="participant-name">' . $p2_name . '</span>';
                    if ($p2_kontingen) echo '<span class="participant-continent">' . $p2_kontingen . '</span>';
                    if ($winner2_active) echo '<span class="lolos-label" style="color:#22c55e;font-weight:700;font-size:0.8em;margin-top:1px;">Lolos</span>';
                    echo '</div><div class="radio-button' . ($winner2_active ? ' active' : '') . '" data-player="' . $actual_p2id . '" data-round="' . ($colIdx+1) . '" data-match="' . ($i+1) . '"></div></div>';
                } else {
                    echo '<div class="participant"><div class="participant-info"><span class="participant-name" style="color:#bdbdbd;font-style:italic;">-</span></div></div>';
                }
                echo '</div>'; // .match
                $matchId++;
            }
            // Simpan offset absolut untuk babak ini
            $absOffsets = [];
            $abs = 0;
            for ($i = 0; $i < $numMatches; $i++) {
                $abs += ($i == 0 ? 0 : $matchOffsets[$i]);
                $absOffsets[$i] = $abs;
                $abs += 60 + $matchGap;
            }
            $prevMatchOffsets = $absOffsets;
            echo '</div>'; // .round
            $colIdx++;
        }

        // Tambahkan kolom Winner (paling kanan) untuk menampilkan pemenang final
        $winnerRoundNum = $totalCols + 1;
        echo '<div class="round" data-round="' . $winnerRoundNum . '" style="display:flex;flex-direction:column;align-items:center;position:relative;">';
        echo '<h3>Pemenang</h3>';
        echo '<div class="match" data-round="' . $winnerRoundNum . '" data-match="1" style="margin-top:40px;">';

        // Cek pemenang babak terakhir (final)
        $finalWinnerId = isset($winnerPlayerMap[$totalCols][1]) ? $winnerPlayerMap[$totalCols][1] : null;

        // Slot atas (utama) untuk pemenang
        if ($finalWinnerId && $finalWinnerId !== 'bye' && isset($displayPeserta[$finalWinnerId-1])) {
            $w_data = $displayPeserta[$finalWinnerId-1];
            $w_name = htmlspecialchars($w_data['nama']);
            $w_kontingen = htmlspecialchars($w_data['kontingen']);
            echo '<div class="participant winner"><div class="participant-number">' . $finalWinnerId . '</div><div class="participant-info">';
            echo '<span class="participant-name">' . $w_name . '</span>';
            if ($w_kontingen) echo '<span class="participant-continent">' . $w_kontingen . '</span>';
            echo '<span class="winner-tag" style="color:#22c55e;font-weight:800;font-size:0.85em;margin-top:2px;">winner</span>';
            echo '</div><div class="radio-button bracket-check-disabled" data-player="' . $finalWinnerId . '" data-round="' . $winnerRoundNum . '" data-match="1"></div></div>';
        } else {
            echo '<div class="participant"><div class="participant-info">';
            echo '<span class="participant-name" style="color:#bdbdbd;font-style:italic;">menunggu pemenang</span>';
            echo '</div><div class="radio-button bracket-check-disabled" data-player="" data-round="' . $winnerRoundNum . '" data-match="1"></div></div>';
        }

        echo '</div>'; // .match
        echo '</div>'; // .round (Winner)

        echo '</div>'; // .bracket
        echo '</div>'; // .bracket-container
        
        // Button container untuk bracket actions
        echo '<div class="bracket-actions-container">';
        echo '<div class="bracket-actions">';
        echo '<button class="bracket-action-btn print-btn" id="printBracketBtn">';
        echo '<i class="fas fa-print"></i> Print Bracket';
        echo '</button>';
        echo '<button class="bracket-action-btn save-btn" id="saveBracketBtn">';
        echo '<i class="fas fa-save"></i> Simpan Update Bracket';
        echo '</button>';
        echo '<button class="bracket-action-btn reset-btn" id="resetBracketBtn">';
        echo '<i class="fas fa-undo"></i> Reset Bracket';
        echo '</button>';
        echo '</div>';
        echo '<div id="bracketNotif" class="bracket-notif" style="display:none"></div>';
        echo '</div>';
        ?>
        <script>
        function addLolosLabel(participantEl) {
            if (!participantEl) return;
            var info = participantEl.querySelector('.participant-info');
            if (!info) return;
            if (!info.querySelector('.lolos-label')) {
                var span = document.createElement('span');
                span.className = 'lolos-label';
                span.style.color = '#22c55e';
                span.style.fontWeight = '700';
                span.style.fontSize = '0.8em';
                span.style.marginTop = '1px';
                span.textContent = 'Lolos';
                info.appendChild(span);
            }
        }

        function removeLolosLabel(participantEl) {
            if (!participantEl) return;
            var label = participantEl.querySelector('.participant-info .lolos-label');
            if (label) label.remove();
        }

        function updateNextRoundSlot(round, matchIndex) {
            var currentRound = parseInt(round, 10);
            var currentMatch = parseInt(matchIndex, 10);
            var nextRound = currentRound + 1;
            var nextMatchIndex = Math.floor((currentMatch - 1) / 2) + 1;
            var slotIndex = (currentMatch % 2 === 1) ? 0 : 1;

            var sourceMatch = document.querySelector('.match[data-round="' + currentRound + '"][data-match="' + currentMatch + '"]');
            var nextRoundEl = document.querySelector('.round[data-round="' + nextRound + '"]');
            if (!sourceMatch || !nextRoundEl) return;

            var winnerBtn = sourceMatch.querySelector('.radio-button.active');
            if (!winnerBtn) return;

            var sourceParticipant = winnerBtn.closest('.participant');
            if (!sourceParticipant) return;

            var playerId = winnerBtn.getAttribute('data-player');
            var playerNumber = sourceParticipant.querySelector('.participant-number')?.textContent || playerId;
            var playerName = sourceParticipant.querySelector('.participant-name')?.textContent || '';
            var playerKontingen = sourceParticipant.querySelector('.participant-continent')?.textContent || '';

            var targetMatch = nextRoundEl.querySelector('.match[data-round="' + nextRound + '"][data-match="' + nextMatchIndex + '"]');
            if (!targetMatch) return;
            var targetParticipants = targetMatch.querySelectorAll('.participant');
            if (!targetParticipants || targetParticipants.length < 2) return;
            var targetParticipant = targetParticipants[slotIndex];

            targetParticipant.classList.remove('bye');
            var numEl = targetParticipant.querySelector('.participant-number');
            if (!numEl) {
                numEl = document.createElement('div');
                numEl.className = 'participant-number';
                targetParticipant.insertBefore(numEl, targetParticipant.firstChild);
            }
            numEl.textContent = playerNumber;

            var info = targetParticipant.querySelector('.participant-info');
            if (!info) {
                info = document.createElement('div');
                info.className = 'participant-info';
                while (targetParticipant.firstChild) targetParticipant.removeChild(targetParticipant.firstChild);
                targetParticipant.appendChild(numEl);
                targetParticipant.appendChild(info);
            }
            var nameEl = info.querySelector('.participant-name');
            if (!nameEl) {
                nameEl = document.createElement('span');
                nameEl.className = 'participant-name';
                info.appendChild(nameEl);
            }
            nameEl.textContent = playerName;

            var kontEl = info.querySelector('.participant-continent');
            if (playerKontingen) {
                if (!kontEl) {
                    kontEl = document.createElement('span');
                    kontEl.className = 'participant-continent';
                    info.appendChild(kontEl);
                }
                kontEl.textContent = playerKontingen;
            } else if (kontEl) {
                kontEl.remove();
            }

            var radios = targetMatch.querySelectorAll('.radio-button');
            var targetRadio = radios[slotIndex];
            if (!targetRadio) {
                targetRadio = document.createElement('div');
                targetRadio.className = 'radio-button';
                targetParticipant.appendChild(targetRadio);
            }
            targetRadio.setAttribute('data-player', playerId);
            targetRadio.setAttribute('data-round', String(nextRound));
            targetRadio.setAttribute('data-match', String(nextMatchIndex));
            targetRadio.classList.remove('active');
        }

        // Auto-propagate BYE winners across all rounds on load
        function autoPropagateByes() {
            var rounds = document.querySelectorAll('.round');
            var changed = true;
            var guard = 0;
            // Iterate until no more changes or safety guard trips
            while (changed && guard < 10) {
                changed = false;
                guard++;
                rounds.forEach(function(roundEl) {
                    var roundNum = parseInt(roundEl.getAttribute('data-round') || '0', 10);
                    roundEl.querySelectorAll('.match').forEach(function(matchEl) {
                        var matchNum = parseInt(matchEl.getAttribute('data-match') || '0', 10);
                        var participants = matchEl.querySelectorAll('.participant');
                        if (participants.length < 2) return;
                        var topIsBye = participants[0].classList.contains('bye');
                        var bottomIsBye = participants[1].classList.contains('bye');
                        var alreadyPicked = !!matchEl.querySelector('.radio-button.active');

                        if (!alreadyPicked && (topIsBye || bottomIsBye)) {
                            var winnerIndex = topIsBye ? 1 : 0;
                            var winnerParticipant = participants[winnerIndex];
                            var winnerRadio = matchEl.querySelectorAll('.radio-button')[winnerIndex];
                            if (winnerRadio) {
                                winnerRadio.classList.add('active');
                                winnerParticipant.classList.add('winner');
                                matchEl.classList.add('has-winner');
                                addLolosLabel(winnerParticipant);
                                updateNextRoundSlot(roundNum, matchNum);
                                changed = true;
                            }
                        }
                    });
                });
            }
        }

        function updateWinnerColumn() {
            var rounds = document.querySelectorAll('.round');
            if (rounds.length < 2) return;
            var winnerRound = rounds[rounds.length - 1]; // last column (Pemenang)
            var finalRound = rounds[rounds.length - 2]; // before last (Final)
            if (!winnerRound || !finalRound) return;

            var finalMatch = finalRound.querySelector('.match[data-match="1"]');
            if (!finalMatch) return;
            var winnerRadio = finalMatch.querySelector('.radio-button.active');
            var winnerSlot = winnerRound.querySelector('.match .participant');
            if (!winnerSlot) return;

            if (winnerRadio) {
                var sourceParticipant = winnerRadio.closest('.participant');
                var playerId = winnerRadio.getAttribute('data-player');
                var playerNumber = sourceParticipant.querySelector('.participant-number')?.textContent || playerId;
                var playerName = sourceParticipant.querySelector('.participant-name')?.textContent || '';
                var playerKontingen = sourceParticipant.querySelector('.participant-continent')?.textContent || '';

                winnerSlot.classList.remove('bye');
                winnerSlot.classList.add('winner');
                // Samakan warna dengan slot pemenang (atas: blue, bawah: red)
                var isTop = sourceParticipant.parentElement && sourceParticipant === sourceParticipant.parentElement.children[0];
                winnerSlot.classList.remove('winner-blue', 'winner-red');
                if (isTop) {
                    winnerSlot.classList.add('winner-blue');
                } else {
                    winnerSlot.classList.add('winner-red');
                }
                var numEl = winnerSlot.querySelector('.participant-number');
                if (!numEl) {
                    numEl = document.createElement('div');
                    numEl.className = 'participant-number';
                    winnerSlot.insertBefore(numEl, winnerSlot.firstChild);
                }
                numEl.textContent = playerNumber;

                var info = winnerSlot.querySelector('.participant-info');
                if (!info) {
                    info = document.createElement('div');
                    info.className = 'participant-info';
                    while (winnerSlot.firstChild) winnerSlot.removeChild(winnerSlot.firstChild);
                    winnerSlot.appendChild(numEl);
                    winnerSlot.appendChild(info);
                }
                var nameEl = info.querySelector('.participant-name');
                if (!nameEl) {
                    nameEl = document.createElement('span');
                    nameEl.className = 'participant-name';
                    info.appendChild(nameEl);
                }
                nameEl.textContent = playerName;

                var kontEl = info.querySelector('.participant-continent');
                if (playerKontingen) {
                    if (!kontEl) {
                        kontEl = document.createElement('span');
                        kontEl.className = 'participant-continent';
                        info.appendChild(kontEl);
                    }
                    kontEl.textContent = playerKontingen;
                } else if (kontEl) {
                    kontEl.remove();
                }

                // Tambahkan tag 'winner' di bawah data peserta
                var info = winnerSlot.querySelector('.participant-info');
                var wtag = info.querySelector('.winner-tag');
                if (!wtag) {
                    wtag = document.createElement('span');
                    wtag.className = 'winner-tag';
                    wtag.style.color = '#22c55e';
                    wtag.style.fontWeight = '800';
                    wtag.style.fontSize = '0.85em';
                    wtag.style.marginTop = '2px';
                    wtag.textContent = 'winner';
                    info.appendChild(wtag);
                } else {
                    wtag.textContent = 'winner';
                }
            } else {
                // No winner chosen yet, show waiting message
                var info = winnerSlot.querySelector('.participant-info');
                if (!info) {
                    while (winnerSlot.firstChild) winnerSlot.removeChild(winnerSlot.firstChild);
                    info = document.createElement('div');
                    info.className = 'participant-info';
                    winnerSlot.appendChild(info);
                }
                var nameEl = info.querySelector('.participant-name');
                if (!nameEl) {
                    nameEl = document.createElement('span');
                    nameEl.className = 'participant-name';
                    info.appendChild(nameEl);
                }
                nameEl.textContent = 'menunggu pemenang';
                nameEl.style.color = '#bdbdbd';
                nameEl.style.fontStyle = 'italic';
            }
        }

        document.addEventListener('click', function(e) {
            var target = e.target;
            if (!target.classList || !target.classList.contains('radio-button')) return;
            if (target.classList.contains('bracket-check-disabled')) return;

            var match = target.getAttribute('data-match');
            var round = target.getAttribute('data-round');
            var participant = target.closest('.participant');
            var matchContainer = target.closest('.match');

            if (target.classList.contains('active')) {
                target.classList.remove('active');
                if (participant) participant.classList.remove('winner');
                if (matchContainer) matchContainer.classList.remove('has-winner');
                removeLolosLabel(participant);
                return;
            }

            document.querySelectorAll('.match[data-round="'+round+'"][data-match="'+match+'"]').forEach(function(m) {
                m.querySelectorAll('.radio-button').forEach(function(r) {
                    r.classList.remove('active');
                    var p = r.closest('.participant');
                    if (p) p.classList.remove('winner');
                    removeLolosLabel(p);
                });
            });

            target.classList.add('active');
            if (participant) participant.classList.add('winner');
            if (matchContainer) matchContainer.classList.add('has-winner');
            addLolosLabel(participant);

            if (round && match) {
                updateNextRoundSlot(round, match);
                // Jika match ini adalah final, update kolom Pemenang
                var rnum = parseInt(round, 10);
                var totalCols = document.querySelectorAll('.round').length - 1; // last is Winner col
                if (rnum === totalCols) {
                    updateWinnerColumn();
                }
            }
        });

        document.getElementById('resetBracketBtn').addEventListener('click', function(e) {
            e.preventDefault();
            if (!confirm('Apakah Anda yakin ingin mereset bracket ini? Semua hasil akan dihapus.')) {
                return;
            }
            var drawId = '<?= $selected_draw_id ?>';
            fetch('reset-bracket.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ draw_id: drawId })
            })
            .then(res => res.json())
            .then(data => {
                var notif = document.getElementById('bracketNotif');
                notif.style.display = 'block';
                if (data.success) {
                    notif.textContent = 'Bracket berhasil direset!';
                    notif.className = 'bracket-notif success';
                    setTimeout(function() { window.location.reload(); }, 600);
                } else {
                    notif.textContent = 'Gagal reset bracket: ' + (data.message || 'Unknown error');
                    notif.className = 'bracket-notif error';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                var notif = document.getElementById('bracketNotif');
                notif.style.display = 'block';
                notif.textContent = 'Gagal reset bracket! Terjadi kesalahan jaringan.';
                notif.className = 'bracket-notif error';
            });
        });

        document.getElementById('saveBracketBtn').addEventListener('click', function() {
            var winners = [];
            document.querySelectorAll('.match').forEach(function(matchDiv) {
                var matchId = matchDiv.getAttribute('data-match');
                var round = matchDiv.getAttribute('data-round');
                var checked = matchDiv.querySelector('.radio-button.active');
                if (checked) {
                    winners.push({
                        match_id: matchId,
                        player_id: checked.getAttribute('data-player'),
                        round: round
                    });
                }
            });
            
            var drawId = '<?= $selected_draw_id ?>';
            var kategori_umur = '<?= addslashes($filter_kategori_umur) ?>';
            var jenis_kelamin = '<?= addslashes($filter_jenis_kelamin) ?>';
            var jenis_kompetisi = '<?= addslashes($filter_jenis_kompetisi) ?>';
            var kategori_tanding = '<?= addslashes($filter_kategori_tanding) ?>';

            fetch('update-bracket.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    draw_id: drawId, 
                    winners: winners, 
                    kategori_umur: kategori_umur, 
                    jenis_kelamin: jenis_kelamin, 
                    jenis_kompetisi: jenis_kompetisi, 
                    kategori_tanding: kategori_tanding 
                })
            })
            .then(res => res.json())
            .then(data => {
                var notif = document.getElementById('bracketNotif');
                notif.style.display = 'block';
                if (data.success) {
                    notif.textContent = 'Berhasil menyimpan update bracket!';
                    notif.className = 'bracket-notif success';
                    winners.forEach(function(w) {
                        if (w.round && w.match_id) {
                            updateNextRoundSlot(parseInt(w.round, 10), parseInt(w.match_id, 10));
                        }
                    });
                    // Pastikan kolom Pemenang ter-update jika final sudah dipilih
                    updateWinnerColumn();
                } else {
                    notif.textContent = 'Gagal menyimpan update bracket: ' + (data.message || 'Unknown error');
                    notif.className = 'bracket-notif error';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                var notif = document.getElementById('bracketNotif');
                notif.style.display = 'block';
                notif.textContent = 'Gagal menyimpan update bracket! Terjadi kesalahan jaringan.';
                notif.className = 'bracket-notif error';
            });
        });

        if (document.getElementById('printBracketBtn')) {
            document.getElementById('printBracketBtn').addEventListener('click', function() {
                window.print();
            });
        }

        // Tambahkan class has-winner pada match yang sudah memiliki pemenang dan auto propagate BYE
        document.addEventListener('DOMContentLoaded', function() {
            var activeRadios = Array.from(document.querySelectorAll('.radio-button.active'));
            // Sort by round asc, match asc to propagate in order
            activeRadios.sort(function(a, b) {
                var ra = parseInt(a.getAttribute('data-round') || '0', 10);
                var rb = parseInt(b.getAttribute('data-round') || '0', 10);
                if (ra !== rb) return ra - rb;
                var ma = parseInt(a.getAttribute('data-match') || '0', 10);
                var mb = parseInt(b.getAttribute('data-match') || '0', 10);
                return ma - mb;
            });
            activeRadios.forEach(function(radio) {
                var matchContainer = radio.closest('.match');
                if (matchContainer) {
                    matchContainer.classList.add('has-winner');
                }
                var participant = radio.closest('.participant');
                if (participant) {
                    participant.classList.add('winner');
                    addLolosLabel(participant);
                }
                var r = radio.getAttribute('data-round');
                var m = radio.getAttribute('data-match');
                if (r && m) {
                    updateNextRoundSlot(r, m);
                }
            });

            // Auto select winners where opponent is BYE and propagate forward
            autoPropagateByes();
        });


        


        // Update jumlah peserta dropdown ketika batch berubah
        document.getElementById('draw_id').addEventListener('change', function() {
            // Reset jumlah peserta ke default ketika batch berubah
            const jumlahPesertaSelect = document.getElementById('jumlah_peserta');
            if (jumlahPesertaSelect) {
                // Akan diupdate oleh PHP ketika form disubmit
                this.form.submit();
            }
        });
        </script>
        </div>
    <?php elseif ($selected_draw_id): ?>
        <div class="empty-state">
            <h3>Data peserta tidak ditemukan untuk hasil drawing ini.</h3>
            <p>Batch ini mungkin kosong atau tidak memiliki data peserta yang valid.</p>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h3>Belum ada batch dipilih</h3>
            <p>Silakan pilih hasil drawing terlebih dahulu untuk menampilkan bracket.</p>
        </div>
        
    <?php endif; ?>
</div>

<script>
// Tab Navigation Functionality - Consistent with perlombaan-detail.php
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}
</script>
</body>
</html>



