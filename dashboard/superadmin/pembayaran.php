<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit();
}

$message = '';
$error = '';
$edit_mode = false;
$edit_data = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_payment'])) {
        $nama_bank = trim($_POST['nama_bank']);
        $nomor_rekening = trim($_POST['nomor_rekening']);
        $pemilik_rekening = trim($_POST['pemilik_rekening']);
        $competitions_selected = isset($_POST['competitions']) ? $_POST['competitions'] : [];
        
        if (empty($nama_bank) || empty($nomor_rekening) || empty($pemilik_rekening)) {
            $error = 'Semua field harus diisi!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO payment_methods (nama_bank, nomor_rekening, pemilik_rekening, nama_pemilik) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nama_bank, $nomor_rekening, $pemilik_rekening, $pemilik_rekening])) {
                $payment_id = $pdo->lastInsertId();
                // Simpan relasi ke competition_payment_methods
                if (!empty($competitions_selected)) {
                    $comp_stmt = $pdo->prepare("INSERT INTO competition_payment_methods (payment_method_id, competition_id) VALUES (?, ?)");
                    foreach ($competitions_selected as $comp_id) {
                        $comp_stmt->execute([$payment_id, $comp_id]);
                    }
                }
                $message = 'Metode pembayaran berhasil ditambahkan!';
            } else {
                $error = 'Gagal menambahkan metode pembayaran!';
            }
        }
    }
    
    if (isset($_POST['update_payment'])) {
        $id = $_POST['id'];
        $nama_bank = trim($_POST['nama_bank']);
        $nomor_rekening = trim($_POST['nomor_rekening']);
        $pemilik_rekening = trim($_POST['pemilik_rekening']);
        $competitions_selected = isset($_POST['competitions']) ? $_POST['competitions'] : [];
        
        if (empty($nama_bank) || empty($nomor_rekening) || empty($pemilik_rekening)) {
            $error = 'Semua field harus diisi!';
        } else {
            $stmt = $pdo->prepare("UPDATE payment_methods SET nama_bank = ?, nomor_rekening = ?, pemilik_rekening = ? WHERE id = ?");
            if ($stmt->execute([$nama_bank, $nomor_rekening, $pemilik_rekening, $id])) {
                // Hapus relasi lama
                $pdo->prepare("DELETE FROM competition_payment_methods WHERE payment_method_id = ?")->execute([$id]);
                // Tambah relasi baru
                if (!empty($competitions_selected)) {
                    $comp_stmt = $pdo->prepare("INSERT INTO competition_payment_methods (payment_method_id, competition_id) VALUES (?, ?)");
                    foreach ($competitions_selected as $comp_id) {
                        $comp_stmt->execute([$id, $comp_id]);
                    }
                }
                $message = 'Metode pembayaran berhasil diperbarui!';
            } else {
                $error = 'Gagal memperbarui metode pembayaran!';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = 'Metode pembayaran berhasil dihapus!';
    } else {
        $error = 'Gagal menghapus metode pembayaran!';
    }
}

// Handle edit
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM payment_methods WHERE id = ?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch();
}

// Get all payment methods
$stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY created_at DESC");
$payment_methods = $stmt->fetchAll();

// Get all competitions (aktif & open_regist)
$competitions = $pdo->query("SELECT id, nama_perlombaan FROM competitions WHERE status IN ('active', 'open_regist') ORDER BY nama_perlombaan")->fetchAll();

// Helper: get competitions for a payment method
function getPaymentCompetitions($pdo, $payment_id) {
    $stmt = $pdo->prepare("SELECT competition_id FROM competition_payment_methods WHERE payment_method_id = ?");
    $stmt->execute([$payment_id]);
    return array_column($stmt->fetchAll(), 'competition_id');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - SuperAdmin Panel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            min-height: 44px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            font-size: 1rem;
            padding: 4px 8px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 4px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-fist-raised"></i>
                <span>SuperAdmin Panel</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="data-admin.php"><i class="fas fa-users-cog"></i> Data Admin</a></li>
            <li><a href="data-user.php"><i class="fas fa-users"></i> Data User</a></li>
            <li><a href="data-kontingen.php"><i class="fas fa-flag"></i> Data Kontingen</a></li>
            <li><a href="perlombaan.php"><i class="fas fa-trophy"></i> Perlombaan</a></li>
            <li><a href="pembayaran.php" class="active"><i class="fas fa-credit-card"></i> Pembayaran</a></li>
            <li><a href="akun-saya.php"><i class="fas fa-user-circle"></i> Akun Saya</a></li>
            <li><a href="../../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Metode Pembayaran</h1>
            <p class="page-subtitle">Kelola metode pembayaran untuk sistem pendaftaran</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Add/Edit Form -->
        <div class="form-container">
            <div class="form-header">
                <h3>
                    <i class="fas fa-<?php echo $edit_mode ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $edit_mode ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'; ?>
                </h3>
                <?php if ($edit_mode): ?>
                <a href="pembayaran.php" class="btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
                <?php endif; ?>
            </div>
            <form method="POST" class="payment-form">
                <?php if ($edit_mode): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_bank">Nama Bank</label>
                        <input type="text" id="nama_bank" name="nama_bank" 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_data['nama_bank']) : ''; ?>" 
                               placeholder="Contoh: Bank BCA" required>
                    </div>
                    <div class="form-group">
                        <label for="nomor_rekening">Nomor Rekening</label>
                        <input type="text" id="nomor_rekening" name="nomor_rekening" 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_data['nomor_rekening']) : ''; ?>" 
                               placeholder="Contoh: 1234567890" required>
                    </div>
                    <div class="form-group">
                        <label for="pemilik_rekening">Pemilik Rekening</label>
                        <input type="text" id="pemilik_rekening" name="pemilik_rekening" 
                               value="<?php echo $edit_mode ? htmlspecialchars($edit_data['pemilik_rekening'] ?? $edit_data['nama_pemilik'] ?? '') : ''; ?>" 
                               placeholder="Contoh: John Doe" required>
                    </div>
                    <div class="form-group form-group-full">
                        <label for="competitions">
                            <i class="fas fa-trophy"></i> Perlombaan Terkait
                        </label>
                        <select id="competitions" name="competitions[]" multiple required>
                            <?php foreach ($competitions as $comp): ?>
                                <option value="<?= $comp['id'] ?>"
                                    <?php
                                    if ($edit_mode && in_array($comp['id'], getPaymentCompetitions($pdo, $edit_data['id']))) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    <?= htmlspecialchars($comp['nama_perlombaan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">
                            <i class="fas fa-info-circle"></i>
                            Pilih satu atau lebih perlombaan yang menggunakan metode pembayaran ini. 
                            <strong>Klik dan tahan Ctrl/Cmd untuk memilih multiple perlombaan.</strong>
                        </div>
                        <?php if ($edit_mode): ?>
                        <div class="current-selections">
                            <strong>Perlombaan yang sedang dipilih:</strong>
                            <div class="selected-competitions">
                                <?php 
                                $selected_comp_ids = getPaymentCompetitions($pdo, $edit_data['id']);
                                if ($selected_comp_ids) {
                                    foreach ($competitions as $comp) {
                                        if (in_array($comp['id'], $selected_comp_ids)) {
                                            echo '<span class="selected-tag">' . htmlspecialchars($comp['nama_perlombaan']) . '</span>';
                                        }
                                    }
                                } else {
                                    echo '<span class="no-selection">Belum ada perlombaan yang dipilih</span>';
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="<?php echo $edit_mode ? 'update_payment' : 'add_payment'; ?>" class="btn-primary">
                        <i class="fas fa-<?php echo $edit_mode ? 'save' : 'plus'; ?>"></i>
                        <?php echo $edit_mode ? 'Update' : 'Tambah'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Methods Table -->
        <div class="table-container">
            <div class="table-header">
                <h2 class="table-title">Daftar Metode Pembayaran</h2>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="searchPayment" placeholder="Cari metode pembayaran...">
                    </div>
                </div>
            </div>
            
            <?php if (empty($payment_methods)): ?>
            <div class="empty-state">
                <i class="fas fa-credit-card"></i>
                <h3>Belum Ada Metode Pembayaran</h3>
                <p>Tambahkan metode pembayaran pertama menggunakan form di atas.</p>
            </div>
            <?php else: ?>
            <table class="data-table" id="paymentTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bank</th>
                        <th>Nomor Rekening</th>
                        <th>Pemilik Rekening</th>
                        <th>Perlombaan Terkait</th>
                        <th>Ditambahkan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment_methods as $index => $payment): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($payment['nama_bank']); ?></strong>
                        </td>
                        <td>
                            <code><?php echo htmlspecialchars($payment['nomor_rekening']); ?></code>
                        </td>
                        <td><?php echo htmlspecialchars($payment['pemilik_rekening'] ?? $payment['nama_pemilik'] ?? '-'); ?></td>
                        <td>
                            <?php
                            $comp_ids = getPaymentCompetitions($pdo, $payment['id']);
                            $comp_names = array();
                            foreach ($competitions as $comp) {
                                if (in_array($comp['id'], $comp_ids)) {
                                    $comp_names[] = htmlspecialchars($comp['nama_perlombaan']);
                                }
                            }
                            if ($comp_names) {
                                echo '<div class="competition-tags">';
                                foreach ($comp_names as $comp_name) {
                                    echo '<span class="competition-tag">' . $comp_name . '</span>';
                                }
                                echo '</div>';
                            } else {
                                echo '<span class="no-competitions">Tidak ada perlombaan terkait</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($payment['created_at'])); ?></td>
                        <td>
                            <a href="?edit=<?php echo $payment['id']; ?>" class="btn-action btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button class="btn-action btn-delete" onclick="deletePayment(<?php echo $payment['id']; ?>)">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <script src="../../assets/js/script.js"></script>
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        function deletePayment(id) {
            if (confirmDelete('Apakah Anda yakin ingin menghapus metode pembayaran ini?')) {
                window.location.href = `?delete=${id}`;
            }
        }
        // Initialize search functionality & Select2
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof searchTable === 'function') {
                searchTable('searchPayment', 'paymentTable');
            }
            // Inisialisasi Select2 pada select perlombaan
            if (window.jQuery) {
                $('#competitions').select2({
                    placeholder: "Pilih perlombaan...",
                    width: '100%'
                });
            }
        });
    </script>

    <style>
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .form-header {
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
            background: var(--light-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-header h3 {
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .payment-form {
            padding: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .form-group-full {
            grid-column: 1 / -1;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--text-color);
            font-size: 0.9rem;
        }
        
        .form-group input {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        /* Competition Tags Styles */
        .competition-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        
        .competition-tag {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        
        .competition-tag::before {
            content: "🏆";
            font-size: 0.7rem;
        }
        
        .no-competitions {
            color: #6b7280;
            font-style: italic;
            font-size: 0.9rem;
        }
        
        /* Form Help Styles */
        .form-help {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 12px 15px;
            margin-top: 8px;
            font-size: 0.9rem;
            color: #0369a1;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        
        .form-help i {
            color: #0284c7;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        /* Current Selections Styles */
        .current-selections {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .current-selections strong {
            color: #374151;
            font-size: 0.9rem;
            display: block;
            margin-bottom: 10px;
        }
        
        .selected-competitions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .selected-tag {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        }
        
        .selected-tag::before {
            content: "✓";
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .no-selection {
            color: #9ca3af;
            font-style: italic;
            font-size: 0.9rem;
        }
        
        /* Enhanced Select2 Styles */
        .select2-container--default .select2-selection--multiple {
            min-height: 50px;
            border-radius: 8px;
            border: 2px solid #d1d5db;
            font-size: 1rem;
            padding: 8px 12px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .select2-container--default .select2-selection--multiple:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 20px;
            margin: 4px 4px 0 0;
            padding: 4px 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255, 255, 255, 0.8);
            margin-right: 6px;
            font-weight: bold;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white;
        }
        
        /* Table Responsive */
        @media (max-width: 768px) {
            .competition-tags {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .competition-tag {
                font-size: 0.75rem;
                padding: 3px 10px;
            }
            
            .selected-competitions {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .selected-tag {
                font-size: 0.8rem;
                padding: 4px 12px;
            }
        }
    </style>
</body>
</html>
