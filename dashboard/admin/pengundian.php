<?php
session_start();
require_once '../../config/database.php';

// Inisialisasi session untuk menyimpan jumlah acak per filter_id
if (!isset($_SESSION['acak_count'])) {
    $_SESSION['acak_count'] = [];
}

// Ambil batch hasil filter

$stmt = $pdo->query("SELECT f.filter_id, COUNT(*) as jumlah, MIN(f.created_at) as waktu, b.batch_name FROM daftar_peserta_filtered f LEFT JOIN daftar_peserta_filter_batches b ON f.filter_id = b.filter_id GROUP BY f.filter_id, b.batch_name ORDER BY f.filter_id DESC");
$filter_batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil filter dari GET
$selected_filter_id = isset($_GET['filter_id']) ? trim($_GET['filter_id']) : '';
$kategori_umur = $_GET['kategori_umur'] ?? '';
$jenis_kelamin = $_GET['jenis_kelamin'] ?? '';
$jenis_kompetisi = $_GET['jenis_kompetisi'] ?? '';
$kategori_tanding = $_GET['kategori_tanding'] ?? '';
$competition_id = $_GET['competition_id'] ?? '';

// Ambil data peserta sesuai batch & filter
$peserta = [];
$filter_info = []; // Tambahkan array untuk menyimpan info filter
$has_existing_draw = false; // Tambahkan flag untuk mengecek apakah sudah ada hasil acak
$existing_urutan = []; // Tambahkan array untuk menyimpan urutan yang sudah ada
$acak_count = 0; // Tambahkan variabel untuk menyimpan jumlah acak
if ($selected_filter_id) {
    $sql = "SELECT p.* FROM daftar_peserta_filtered f
            JOIN daftar_peserta p ON f.peserta_id = p.id
            WHERE f.filter_id = ?";
    $params = [$selected_filter_id];

    if ($kategori_umur) {
        $sql .= " AND p.kategori_umur = ?";
        $params[] = $kategori_umur;
    }
    if ($jenis_kelamin) {
        $sql .= " AND p.jenis_kelamin = ?";
        $params[] = $jenis_kelamin;
    }
    if ($jenis_kompetisi) {
        $sql .= " AND p.jenis_kompetisi = ?";
        $params[] = $jenis_kompetisi;
    }
    if ($jenis_kompetisi && stripos($jenis_kompetisi, 'tanding') !== false && $kategori_tanding) {
        $sql .= " AND p.kategori_tanding = ?";
        $params[] = $kategori_tanding;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $peserta = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ambil info filter dari data peserta yang sudah difilter
    if (!empty($peserta)) {
        $filter_info = [
            'kategori_umur' => $peserta[0]['kategori_umur'] ?? '',
            'jenis_kelamin' => $peserta[0]['jenis_kelamin'] ?? '',
            'jenis_kompetisi' => $peserta[0]['jenis_kompetisi'] ?? '',
            'kategori_tanding' => $peserta[0]['kategori_tanding'] ?? ''
        ];
    }
    
    // Cek apakah sudah ada hasil acak untuk filter_id ini
    $stmt_draw = $pdo->prepare("SELECT urutan, created_at FROM daftar_peserta_draws WHERE filter_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt_draw->execute([$selected_filter_id]);
    $existing_draw = $stmt_draw->fetch(PDO::FETCH_ASSOC);
    
    if ($existing_draw && $existing_draw['urutan']) {
        $has_existing_draw = true;
        $existing_urutan = json_decode($existing_draw['urutan'], true);
        
        // Hitung jumlah acak yang sudah dilakukan berdasarkan session
        $acak_count = isset($_SESSION['acak_count'][$selected_filter_id]) ? $_SESSION['acak_count'][$selected_filter_id] : 1;
    }
}

// Ambil opsi filter (kategori umur, jenis kompetisi, dst) dari daftar_peserta
$kategori_umur_options = $pdo->query("SELECT DISTINCT kategori_umur FROM daftar_peserta ORDER BY kategori_umur")->fetchAll(PDO::FETCH_COLUMN);
$jenis_kompetisi_options = $pdo->query("SELECT DISTINCT jenis_kompetisi FROM daftar_peserta ORDER BY jenis_kompetisi")->fetchAll(PDO::FETCH_COLUMN);
$kategori_tanding_options = $pdo->query("SELECT DISTINCT kategori_tanding FROM daftar_peserta ORDER BY kategori_tanding")->fetchAll(PDO::FETCH_COLUMN);
$jenis_kelamin_options = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengundian Peserta - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 1400px;
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            padding: 40px 30px 50px 30px;
            position: relative;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
        }
        .page-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            margin: 0;
        }
        .page-subtitle {
            color: #fff;
            font-size: 1.1rem;
            margin: 8px 0 0 0;
        }
        .page-actions {
            display: flex;
            gap: 12px;
        }
        .table-container {
            background: #f8fafc;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;
        }
        .table-header {
            margin-bottom: 20px;
        }
        .table-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background: #fff;
        }
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .btn {
            background: linear-gradient(90deg,#38bdf8,#0ea5e9);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin: 0;
            box-shadow: 0 4px 16px rgba(56,189,248,0.25);
            transition: all 0.3s ease;
            outline: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .btn:hover::before {
            left: 100%;
        }
        .btn:active {
            transform: scale(0.95);
            box-shadow: 0 2px 8px rgba(56,189,248,0.15);
        }
        .btn:hover {
            background: linear-gradient(90deg,#0ea5e9,#38bdf8);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(56,189,248,0.35);
        }
        .btn-primary {
            background: linear-gradient(90deg,#3b82f6,#1d4ed8);
            box-shadow: 0 4px 16px rgba(59,130,246,0.25);
        }
        .btn-primary:hover {
            background: linear-gradient(90deg,#1d4ed8,#3b82f6);
            box-shadow: 0 8px 24px rgba(59,130,246,0.35);
        }
        .btn-success {
            background: linear-gradient(90deg,#10b981,#059669);
            box-shadow: 0 4px 16px rgba(16,185,129,0.25);
        }
        .btn-success:hover {
            background: linear-gradient(90deg,#059669,#10b981);
            box-shadow: 0 8px 24px rgba(16,185,129,0.35);
        }
        .btn-secondary {
            background: linear-gradient(90deg,#6b7280,#4b5563);
            box-shadow: 0 4px 16px rgba(107,114,128,0.25);
        }
        .btn-secondary:hover {
            background: linear-gradient(90deg,#4b5563,#6b7280);
            box-shadow: 0 8px 24px rgba(107,114,128,0.35);
        }
        .btn-danger {
            background: linear-gradient(90deg,#ef4444,#dc2626);
            box-shadow: 0 4px 16px rgba(239,68,68,0.25);
        }
        .btn-danger:hover {
            background: linear-gradient(90deg,#dc2626,#ef4444);
            box-shadow: 0 8px 24px rgba(239,68,68,0.35);
        }
        .btn i {
            font-size: 1.05rem;
            transition: transform 0.3s ease;
        }
        .btn:hover i {
            transform: scale(1.1);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 16px;
            color: #64748b;
            margin: 30px auto;
            max-width: 600px;
            border: 2px dashed #cbd5e1;
        }
        .empty-state i {
            font-size: 3rem;
            color: #94a3b8;
            margin-bottom: 16px;
        }
        .empty-state h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #475569;
            margin: 0 0 8px 0;
        }
        .empty-state p {
            font-size: 1rem;
            margin: 0;
        }
        @media (max-width: 1200px) {
            .container { max-width: 95%; padding: 30px 20px 40px 20px; }
        }
        @media (max-width: 768px) {
            .container { max-width: 98%; padding: 20px 15px 30px 15px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .page-title { font-size: 1.8rem; }
            .table-container { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Pengundian Peserta</h1>
                <p class="page-subtitle">Kelola dan lakukan pengundian peserta berdasarkan hasil filter</p>
            </div>
            <div class="page-actions">
                <a href="perlombaan-detail.php?id=<?= $competition_id ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Detail Perlombaan
                </a>
            </div>
        </div>
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Pilih Batch Hasil Filter & Filter Lanjutan</h2>
            </div>
            <form method="GET" style="display: flex; flex-wrap: wrap; gap: 18px; align-items: end; margin-bottom: 0; background: #f1f5f9; border-radius: 10px; padding: 18px 20px 10px 20px;">
                <?php if ($competition_id): ?>
                <input type="hidden" name="competition_id" value="<?= htmlspecialchars($competition_id) ?>">
                <?php endif; ?>
                <div class="form-group" style="min-width:220px;flex:1;">
                    <label for="filter_id">Batch Hasil Filter</label>
                    <select name="filter_id" id="filter_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Pilih Batch --</option>
                        <?php foreach ($filter_batches as $batch): ?>
                            <option value="<?= $batch['filter_id'] ?>" <?= $selected_filter_id == $batch['filter_id'] ? 'selected' : '' ?>>
                                Batch #<?= $batch['filter_id'] ?><?= $batch['batch_name'] ? ' - ' . htmlspecialchars($batch['batch_name']) : '' ?> (<?= $batch['jumlah'] ?> peserta, <?= $batch['waktu'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($selected_filter_id && $jenis_kompetisi && stripos($jenis_kompetisi, 'tanding') !== false): ?>
                <div class="form-group" style="min-width:180px;flex:1;">
                    <label for="kategori_tanding">Kategori Tanding</label>
                    <select name="kategori_tanding" id="kategori_tanding" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <?php foreach ($kategori_tanding_options as $opt): ?>
                            <option value="<?= $opt ?>" <?= $kategori_tanding == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>
        </div>
        
        <?php if ($selected_filter_id && count($peserta) > 0): ?>
        <!-- Section Proses Acak -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Proses Acak Urutan Peserta</h2>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 24px;">
                <button class="btn btn-primary" id="btnAcak"><i class="fas fa-random"></i> Acak Urutan</button>
                <button class="btn btn-success" id="btnSimpan"><i class="fas fa-save"></i> Simpan Hasil Acak</button>
                <a href="export-batch-xlsx.php?filter_id=<?= urlencode($selected_filter_id) ?>
                    <?php if ($kategori_umur) echo '&kategori_umur=' . urlencode($kategori_umur); ?>
                    <?php if ($jenis_kelamin) echo '&jenis_kelamin=' . urlencode($jenis_kelamin); ?>
                    <?php if ($jenis_kompetisi) echo '&jenis_kompetisi=' . urlencode($jenis_kompetisi); ?>
                    <?php if ($peserta && $jenis_kompetisi && stripos($jenis_kompetisi, 'tanding') !== false && ($peserta[0]['kategori_tanding'] ?? '')) echo '&kategori_tanding=' . urlencode($peserta[0]['kategori_tanding']); ?>
                    <?php if ($competition_id) echo '&competition_id=' . urlencode($competition_id); ?>"
                   class="btn btn-secondary" target="_blank">
                    <i class="fas fa-file-excel"></i> Export XLSX
                </a>
                <button class="btn btn-danger" id="btnReset" style="display: none; background: linear-gradient(90deg,#ef4444,#dc2626);box-shadow: 0 4px 16px rgba(239,68,68,0.25);">
                    <i class="fas fa-undo"></i> Reset Hasil Acak
                </button>
                    </div>
            
            <!-- Keterangan Filter -->
            <div style="background: rgba(14,165,233,0.10); border-radius: 12px; padding: 18px 20px; margin-bottom: 24px; font-size: 1.4rem; color: #0ea5e9; font-weight: 800; text-align: center;">
                <?php 
                $keterangan_parts = [];
                
                // Jenis Kompetisi
                if ($filter_info['jenis_kompetisi']) {
                    $keterangan_parts[] = strtoupper($filter_info['jenis_kompetisi']);
                }
                
                // Kategori Tanding (jika jenis kompetisi tanding)
                if ($filter_info['jenis_kompetisi'] && stripos($filter_info['jenis_kompetisi'], 'tanding') !== false && $filter_info['kategori_tanding']) {
                    $keterangan_parts[] = strtoupper($filter_info['kategori_tanding']);
                }
                
                // Kategori Umur
                if ($filter_info['kategori_umur']) {
                    $keterangan_parts[] = strtoupper($filter_info['kategori_umur']);
                }
                
                // Jenis Kelamin
                if ($filter_info['jenis_kelamin']) {
                    $jenis_kelamin_text = ($filter_info['jenis_kelamin'] == 'L') ? 'PUTRA' : (($filter_info['jenis_kelamin'] == 'P') ? 'PUTRI' : strtoupper($filter_info['jenis_kelamin']));
                    $keterangan_parts[] = $jenis_kelamin_text;
                }
                
                // Jumlah Peserta
                $keterangan_parts[] = count($peserta) . ' PESERTA';
                
                if (!empty($keterangan_parts)) {
                    echo '<strong>' . implode(' / ', $keterangan_parts) . '</strong>';
                }
                ?>
            </div>
            
            <div id="hourglass-anim" style="display:none;position:absolute;top:0;left:0;right:0;bottom:0;z-index:20;background:rgba(255,255,255,0.85);backdrop-filter:blur(15px);border-radius:20px;flex-direction:column;justify-content:center;align-items:center;gap:20px;">
                <div style="position:relative;width:120px;height:120px;display:flex;justify-content:center;align-items:center;">
                    <!-- Outer ring -->
                    <div style="position:absolute;width:100%;height:100%;border:3px solid rgba(59,130,246,0.2);border-radius:50%;animation:pulse 2s ease-in-out infinite;"></div>
                    <!-- Inner ring -->
                    <div style="position:absolute;width:80%;height:80%;border:2px solid rgba(16,185,129,0.3);border-radius:50%;animation:pulse 2s ease-in-out infinite 0.5s;"></div>
                    <!-- Hourglass icon -->
                    <div style="background:linear-gradient(135deg,rgba(59,130,246,0.9),rgba(16,185,129,0.9));padding:20px;border-radius:50%;box-shadow:0 8px 32px rgba(59,130,246,0.3);animation:float 3s ease-in-out infinite;">
                        <i class="fas fa-hourglass-half" style="font-size:3rem;color:#fff;animation:spin 1.5s linear infinite;"></i>
                    </div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.2rem;font-weight:700;color:#374151;margin-bottom:8px;animation:fadeInUp 0.6s ease-out;">Mengacak Peserta...</div>
                    <div style="font-size:0.95rem;color:#6b7280;animation:fadeInUp 0.6s ease-out 0.2s;">Mohon tunggu sebentar</div>
                </div>
                <!-- Animated dots -->
                <div style="display:flex;gap:8px;animation:fadeInUp 0.6s ease-out 0.4s;">
                    <div style="width:8px;height:8px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);border-radius:50%;animation:bounce 1.4s ease-in-out infinite;"></div>
                    <div style="width:8px;height:8px;background:linear-gradient(135deg,#10b981,#059669);border-radius:50%;animation:bounce 1.4s ease-in-out infinite 0.2s;"></div>
                    <div style="width:8px;height:8px;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:50%;animation:bounce 1.4s ease-in-out infinite 0.4s;"></div>
                </div>
            </div>
            
            <div id="undian-counter" style="<?= $has_existing_draw ? 'display:block;' : 'display:none;' ?>background:linear-gradient(135deg,rgba(16,185,129,0.1),rgba(59,130,246,0.1));border:2px solid rgba(16,185,129,0.2);border-radius:16px;padding:16px 20px;margin:20px 0;text-align:center;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;bottom:0;background:linear-gradient(135deg,rgba(16,185,129,0.05),rgba(59,130,246,0.05));"></div>
                <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:center;gap:12px;">
                    <div style="background:linear-gradient(135deg,#10b981,#059669);width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(16,185,129,0.3);animation:pulse 2s ease-in-out infinite;">
                        <i class="fas fa-dice" style="color:#fff;font-size:1.2rem;"></i>
                    </div>
                    <div style="text-align:left;">
                        <div style="font-size:1.1rem;font-weight:700;color:#374151;margin-bottom:2px;">Hasil Acak</div>
                        <div style="font-size:1rem;color:#6b7280;">Sudah diacak <span id="undian-count" style="color:#10b981;font-weight:700;font-size:1.1rem;">0</span> kali</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(59,130,246,0.3);animation:pulse 2s ease-in-out infinite 1s;">
                        <i class="fas fa-check" style="color:#fff;font-size:1.1rem;"></i>
                    </div>
                </div>
            </div>
            
            <div class="table-wrap" id="tableAcak" style="position:relative;">
                <div style="display:grid;grid-template-columns:1fr 60px 1fr;gap:0;background:#f8fafc;border-radius:20px;padding:24px;box-shadow:0 10px 40px rgba(0,0,0,0.1);">
                    <!-- Kolom Urutan Asli -->
                    <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                        <div style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;padding:20px;text-align:center;font-weight:700;font-size:1.2rem;letter-spacing:1px;">
                            <i class="fas fa-list-ol" style="margin-right:8px;"></i>URUTAN ASLI
                        </div>
                        <div style="padding:0;">
                            <div style="background:#f1f5f9;padding:12px 20px;font-weight:600;color:#374151;font-size:1rem;border-bottom:1px solid #e2e8f0;">
                                <div style="display:grid;grid-template-columns:60px 1fr;gap:16px;align-items:center;">
                                    <span>No</span>
                                    <span>Nama Peserta</span>
                                </div>
                            </div>
                            <div id="tbodyAsli" style="padding:0;">
                                <!-- Diisi oleh JS -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Spacer -->
                    <div style="display:flex;align-items:center;justify-content:center;">
                        <div style="width:4px;height:60px;background:linear-gradient(180deg,#3b82f6,#1d4ed8);border-radius:2px;"></div>
                    </div>
                    
                    <!-- Kolom Urutan Setelah Diacak -->
                    <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                        <div style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:20px;text-align:center;font-weight:700;font-size:1.2rem;letter-spacing:1px;">
                            <i class="fas fa-random" style="margin-right:8px;"></i>URUTAN SETELAH DIACAK
                        </div>
                        <div style="padding:0;">
                            <div style="background:#f1f5f9;padding:12px 20px;font-weight:600;color:#374151;font-size:1rem;border-bottom:1px solid #e2e8f0;">
                                <div style="display:grid;grid-template-columns:60px 1fr;gap:16px;align-items:center;">
                                    <span>No</span>
                                    <span>Nama Peserta</span>
                                </div>
                            </div>
                            <div id="tbodyAcak" style="padding:0;">
                                <!-- Diisi oleh JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif ($selected_filter_id): ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <h3>Tidak ada peserta pada filter ini</h3>
                        <p>Silakan ubah filter untuk melihat data peserta.</p>
                    </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-filter"></i>
                    <h3>Belum ada batch dipilih</h3>
                    <p>Silakan pilih batch hasil filter terlebih dahulu.</p>
                </div>
            <?php endif; ?>
    </div>
</body>
<script>
const peserta = <?= json_encode($peserta) ?>;
let urutanAcak = peserta.map((p, i) => i); // index array
const jenisKompetisiFilter = <?= json_encode(strtolower($jenis_kompetisi)) ?>;
const hasExistingDraw = <?= json_encode($has_existing_draw) ?>;
const existingUrutan = <?= json_encode($existing_urutan) ?>;
const acakCount = <?= json_encode($acak_count) ?>;

function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

function pesertaInfoHTML(p) {
    let html = `<div class='peserta-info'>`;
    html += `<span class='peserta-nama'>${p.nama}</span>`;
    html += `<span class='peserta-kontingen'>${p.kontingen}</span>`;
    html += `</div>`;
    return html;
}

function renderTable(animate = false) {
    const tbodyAsli = document.getElementById('tbodyAsli');
    const tbodyAcak = document.getElementById('tbodyAcak');
    let htmlAsli = '';
    let htmlAcak = '';
    
    for (let i = 0; i < peserta.length; i++) {
        const pAwal = peserta[i];
        const idxAcak = urutanAcak[i];
        const pAcak = peserta[idxAcak];
        
        // Kolom Asli
        htmlAsli += `<div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;transition:all 0.3s ease;${i % 2 === 0 ? 'background:#fafbfc;' : 'background:#fff;'}">
            <div style="display:grid;grid-template-columns:60px 1fr;gap:16px;align-items:center;">
                <div style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;">${i+1}</div>
                <div>${pesertaInfoHTML(pAwal)}</div>
            </div>
        </div>`;
        
        // Kolom Acak
        htmlAcak += `<div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;transition:all 0.3s ease;${animate ? 'animation:slideIn 0.5s ease;' : ''}${i % 2 === 0 ? 'background:#fafbfc;' : 'background:#fff;'}">
            <div style="display:grid;grid-template-columns:60px 1fr;gap:16px;align-items:center;">
                <div style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;">${i+1}</div>
                <div>${pesertaInfoHTML(pAcak)}</div>
            </div>
        </div>`;
    }
    
    tbodyAsli.innerHTML = htmlAsli;
    tbodyAcak.innerHTML = htmlAcak;
    
    if (animate) {
        setTimeout(() => {
            document.querySelectorAll('[style*="animation:slideIn"]').forEach(el => {
                el.style.animation = '';
            });
        }, 800);
    }
}

function renderEmptyTable() {
    const tbodyAsli = document.getElementById('tbodyAsli');
    const tbodyAcak = document.getElementById('tbodyAcak');
    let htmlAsli = '';
    let htmlAcak = '';
    
    for (let i = 0; i < peserta.length; i++) {
        const pAwal = peserta[i];
        
        // Kolom Asli
        htmlAsli += `<div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;transition:all 0.3s ease;${i % 2 === 0 ? 'background:#fafbfc;' : 'background:#fff;'}">
            <div style="display:grid;grid-template-columns:60px 1fr;gap:16px;align-items:center;">
                <div style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;">${i+1}</div>
                <div>${pesertaInfoHTML(pAwal)}</div>
            </div>
        </div>`;
        
        // Kolom Acak (kosong)
        htmlAcak += `<div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;transition:all 0.3s ease;${i % 2 === 0 ? 'background:#fafbfc;' : 'background:#fff;'}">
            <div style="display:grid;grid-template-columns:60px 1fr;gap:16px;align-items:center;">
                <div style="background:#e5e7eb;color:#9ca3af;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem;">-</div>
                <div style="color:#9ca3af;font-style:italic;">Menunggu acak...</div>
            </div>
        </div>`;
    }
    
    tbodyAsli.innerHTML = htmlAsli;
    tbodyAcak.innerHTML = htmlAcak;
}

let undianCount = 0;
const undianCounter = document.getElementById('undian-counter');
const undianCountSpan = document.getElementById('undian-count');
const hourglass = document.getElementById('hourglass-anim');
const tableWrap = document.getElementById('tableAcak');
const btnReset = document.getElementById('btnReset');

if (peserta.length > 0) {
    // Inisialisasi urutan awal
    for (let i = 0; i < peserta.length; i++) urutanAcak[i] = i;
    
    // Jika sudah ada hasil acak, gunakan urutan yang tersimpan
    if (hasExistingDraw && existingUrutan.length > 0) {
        // Konversi ID database ke index array
        const pesertaMap = {};
        peserta.forEach((p, idx) => {
            pesertaMap[p.id] = idx;
        });
        
        urutanAcak = existingUrutan.map(id => pesertaMap[id] || 0);
        renderTable(false); // Render tanpa animasi
        undianCount = acakCount || 1; // Gunakan acakCount dari PHP, default 1
        undianCountSpan.textContent = undianCount;
        // Counter sudah ditampilkan melalui PHP, tidak perlu set display lagi
        
        // Tampilkan button reset dan counter jika sudah ada hasil acak
        if (btnReset) {
            btnReset.style.display = 'inline-flex';
        }
        if (undianCounter) {
            undianCounter.style.display = 'block';
        }
    } else {
        renderEmptyTable(); // Tampilkan tabel kosong di awal
        // Sembunyikan counter jika belum ada hasil acak
        if (undianCounter) {
            undianCounter.style.display = 'none';
        }
    }
    
    document.getElementById('btnAcak').addEventListener('click', function() {
        // Tampilkan jam pasir, sembunyikan tabel
        hourglass.style.display = 'flex';
        hourglass.style.flexDirection = 'column';
        hourglass.style.justifyContent = 'center';
        hourglass.style.alignItems = 'center';
        hourglass.style.gap = '20px';
        tableWrap.style.display = 'none';
        setTimeout(() => {
            shuffleArray(urutanAcak);
            renderTable(true);
            hourglass.style.display = 'none';
            tableWrap.style.display = '';
            undianCount++;
            undianCountSpan.textContent = undianCount;
            undianCounter.style.display = 'block';
            // Tampilkan button reset setelah melakukan acak
            if (btnReset) {
                btnReset.style.display = 'inline-flex';
            }
        }, 2500);
    });
    
    document.getElementById('btnSimpan').addEventListener('click', function() {
        if (!confirm('Simpan hasil acak ini sebagai bracket?')) return;
        const urutanPeserta = urutanAcak.map(idx => peserta[idx].id);
        fetch('simpan-hasil-acak.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                filter_id: <?= json_encode($selected_filter_id) ?>,
                urutan: urutanPeserta,
                acak_count: undianCount
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Hasil acak berhasil disimpan!');
                // Tampilkan button reset setelah berhasil menyimpan
                if (btnReset) {
                    btnReset.style.display = 'inline-flex';
                }
            } else {
                alert('Gagal simpan: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => alert('Gagal simpan: ' + err));
    });

    // Tambahkan event listener untuk button reset jika ada
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            if (!confirm('Reset hasil acak untuk filter ini?')) return;
            fetch('reset-hasil-acak.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    filter_id: <?= json_encode($selected_filter_id) ?>
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Hasil acak berhasil direset!');
                    // Sembunyikan button reset dan counter
                    if (btnReset) {
                        btnReset.style.display = 'none';
                    }
                    if (undianCounter) {
                        undianCounter.style.display = 'none';
                    }
                    // Reset counter dan tampilkan tabel kosong
                    undianCount = 0;
                    undianCountSpan.textContent = '0';
                    renderEmptyTable();
                } else {
                    alert('Gagal reset: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => alert('Gagal reset: ' + err));
        });
    }
}

// CSS untuk styling yang sama dengan proses-acak.php
const style = document.createElement('style');
style.textContent = `
.btn {
    background: linear-gradient(90deg,#38bdf8,#0ea5e9);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 14px 28px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    margin: 0;
    box-shadow: 0 4px 16px rgba(56,189,248,0.25);
    transition: all 0.3s ease;
    outline: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn:active {
    transform: scale(0.95);
    box-shadow: 0 2px 8px rgba(56,189,248,0.15);
}

.btn:hover {
    background: linear-gradient(90deg,#0ea5e9,#38bdf8);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(56,189,248,0.35);
}

.btn-primary {
    background: linear-gradient(90deg,#3b82f6,#1d4ed8);
    box-shadow: 0 4px 16px rgba(59,130,246,0.25);
}

.btn-primary:hover {
    background: linear-gradient(90deg,#1d4ed8,#3b82f6);
    box-shadow: 0 8px 24px rgba(59,130,246,0.35);
}

.btn-success {
    background: linear-gradient(90deg,#10b981,#059669);
    box-shadow: 0 4px 16px rgba(16,185,129,0.25);
}

.btn-success:hover {
    background: linear-gradient(90deg,#059669,#10b981);
    box-shadow: 0 8px 24px rgba(16,185,129,0.35);
}

.btn-secondary {
    background: linear-gradient(90deg,#6b7280,#4b5563);
    box-shadow: 0 4px 16px rgba(107,114,128,0.25);
}

.btn-secondary:hover {
    background: linear-gradient(90deg,#4b5563,#6b7280);
    box-shadow: 0 8px 24px rgba(107,114,128,0.35);
}

.btn-danger {
    background: linear-gradient(90deg,#ef4444,#dc2626);
    box-shadow: 0 4px 16px rgba(239,68,68,0.25);
}

.btn-danger:hover {
    background: linear-gradient(90deg,#dc2626,#ef4444);
    box-shadow: 0 8px 24px rgba(239,68,68,0.35);
}

.btn i {
    font-size: 1.05rem;
    transition: transform 0.3s ease;
}

.btn:hover i {
    transform: scale(1.1);
}

.table-wrap {
    overflow-x: auto;
    margin-top: 10px;
    position: relative;
}
table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #334155;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(30,41,59,0.15);
    border: 1px solid #475569;
}
th, td {
    padding: 18px 16px;
    border-bottom: 1px solid #475569;
    font-size: 1.08rem;
    text-align: left;
}
th {
    background: linear-gradient(135deg,#0ea5e9,#38bdf8);
    color: #fff;
    font-size: 1.15rem;
    font-weight: 700;
    text-align: center;
    padding: 20px 16px;
    letter-spacing: 0.5px;
}
tr:last-child td {
    border-bottom: none;
}
.col-title {
    background: #1e293b;
    color: #38bdf8;
    font-weight: 700;
    font-size: 1.15rem;
    border-radius: 8px 0 0 8px;
    transition: all 0.3s ease;
}
.col-acak {
    background: #0f172a;
    color: #22d3ee;
    font-weight: 700;
    font-size: 1.15rem;
    border-radius: 0 8px 8px 0;
    transition: all 0.3s ease;
}
.col-empty {
    background: #475569;
    color: #94a3b8;
    font-weight: 500;
    font-size: 1.1rem;
    border-radius: 0 8px 8px 0;
    text-align: center;
    font-style: italic;
}
.col-acak.animate {
    animation: flash 0.7s;
}
.col-title:hover, .col-acak:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
@keyframes flash {
    0% { background: #facc15; color: #0f172a; transform: scale(1.05); }
    40% { background: #facc15; color: #0f172a; transform: scale(1.05); }
    100% { background: #0f172a; color: #22d3ee; transform: scale(1); }
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.6; }
    50% { transform: scale(1.1); opacity: 1; }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

@keyframes fadeInUp {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

@keyframes bounce {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
    40% { transform: scale(1.2); opacity: 1; }
}
.peserta-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.peserta-nama {
    font-weight: 700;
    font-size: 1.05em;
    color: #1e293b;
}
.peserta-kontingen {
    color: #fbbf24;
    font-size: 0.95em;
    font-weight: 600;
}
.peserta-jenis-kompetisi {
    color: #3b82f6;
    font-size: 0.9em;
    font-weight: 500;
}
.peserta-kategori-umur {
    color: #10b981;
    font-size: 0.9em;
    font-weight: 500;
}
.peserta-jenis-kelamin {
    color: #8b5cf6;
    font-size: 0.9em;
    font-weight: 500;
}
.peserta-tanding {
    color: #f472b6;
    font-size: 0.9em;
    font-weight: 500;
}
@keyframes slideIn {
    0% { opacity: 0; transform: translateX(20px); }
    100% { opacity: 1; transform: translateX(0); }
}
`;
document.head.appendChild(style);
</script>
</html> 