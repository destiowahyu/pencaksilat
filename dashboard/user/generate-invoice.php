<?php
    session_start();
    require_once '../../config/database.php';
    
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
        header('Location: ../../index.php');
        exit();
    }
    
    $type = $_GET['type'] ?? 'athlete';
    $action = $_GET['action'] ?? 'view';
    
    // Function to generate unique invoice code
    function generateInvoiceCode($type, $id, $competition_id) {
        $prefix = $type === 'athlete' ? 'ATL' : 'KTG';
        $date = date('Ymd');
        $comp_code = str_pad($competition_id, 3, '0', STR_PAD_LEFT);
        $id_code = str_pad($id, 4, '0', STR_PAD_LEFT);
        $random = strtoupper(substr(md5(uniqid()), 0, 3));
        
        return "INV-{$prefix}-{$date}-{$comp_code}-{$id_code}-{$random}";
    }
    
    // Variabel untuk menampung data invoice
    $invoice_info = null;
    $invoice_items = [];
    $invoice_title = "Invoice";
    $invoice_code = "";
    
    try {
        if ($type === 'kontingen') {
            $competition_id = $_GET['competition_id'] ?? null;
            if (!$competition_id) die('Competition ID is required.');
    
            // Get competition and user info
            $stmt = $pdo->prepare("
                SELECT c.nama_perlombaan, c.tanggal_pelaksanaan, c.lokasi,
                       k.nama_kontingen, k.provinsi, k.kota,
                       u.nama as user_name, u.email, u.whatsapp, u.alamat,
                       c.id as competition_id
                FROM competitions c
                JOIN registrations r ON c.id = r.competition_id
                JOIN athletes a ON r.athlete_id = a.id
                JOIN kontingen k ON a.kontingen_id = k.id
                JOIN users u ON a.user_id = u.id
                WHERE c.id = ? AND a.user_id = ?
                GROUP BY c.id, k.id, u.id
                LIMIT 1
            ");
            $stmt->execute([$competition_id, $_SESSION['user_id']]);
            $invoice_info = $stmt->fetch();
    
            if (!$invoice_info) die('Invoice data not found or you do not have permission.');
    
            // Get all registered athletes for the contingent
            $stmt = $pdo->prepare("
                SELECT r.*, a.nama as athlete_name, a.nik, a.jenis_kelamin,
                       cc.nama_kategori as category_name,
                       ac.nama_kategori as age_category_name,
                       ct.nama_kompetisi, ct.biaya_pendaftaran
                FROM registrations r
                JOIN athletes a ON r.athlete_id = a.id
                LEFT JOIN competition_categories cc ON r.category_id = cc.id
                LEFT JOIN age_categories ac ON r.age_category_id = ac.id
                LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
                WHERE r.competition_id = ? AND a.user_id = ?
                ORDER BY a.nama ASC
            ");
            $stmt->execute([$competition_id, $_SESSION['user_id']]);
            $invoice_items = $stmt->fetchAll();
    
            if (empty($invoice_items)) die('No registered athletes found for this contingent.');
    
            $invoice_code = generateInvoiceCode('kontingen', $competition_id, $competition_id);
            $invoice_title = "Invoice Kontingen - " . $invoice_info['nama_perlombaan'];
    
        } else { // Athlete type
            $registration_id = $_GET['registration_id'] ?? null;
            if (!$registration_id) die('Registration ID is required.');
    
            // Get registration details
            $stmt = $pdo->prepare("
                SELECT r.*, c.nama_perlombaan, c.tanggal_pelaksanaan, c.lokasi,
                       a.nama as athlete_name, a.nik, a.jenis_kelamin, a.tanggal_lahir,
                       k.nama_kontingen, k.provinsi, k.kota,
                       u.nama as user_name, u.email, u.whatsapp, u.alamat,
                       cc.nama_kategori as category_name,
                       ac.nama_kategori as age_category_name,
                       ct.nama_kompetisi, ct.biaya_pendaftaran
                FROM registrations r
                JOIN competitions c ON r.competition_id = c.id
                JOIN athletes a ON r.athlete_id = a.id
                JOIN kontingen k ON a.kontingen_id = k.id
                JOIN users u ON a.user_id = u.id
                LEFT JOIN competition_categories cc ON r.category_id = cc.id
                LEFT JOIN age_categories ac ON r.age_category_id = ac.id
                LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
                WHERE r.id = ? AND a.user_id = ?
                LIMIT 1
            ");
            $stmt->execute([$registration_id, $_SESSION['user_id']]);
            $invoice_info = $stmt->fetch();
    
            if (!$invoice_info) die('Invoice data not found or you do not have permission.');
    
            $invoice_items = [$invoice_info];
            $invoice_code = generateInvoiceCode('athlete', $registration_id, $invoice_info['competition_id']);
            $invoice_title = "Invoice Pendaftaran Atlet";
        }
    
        // Calculate total amount
        $total_amount = 0;
        foreach ($invoice_items as $item) {
            $total_amount += $item['biaya_pendaftaran'] ?? 0;
        }
    
        // Ambil $competition_id dari invoice_info
        $competition_id = $invoice_info['competition_id'];
        $stmt = $pdo->prepare("
            SELECT pm.*
            FROM payment_methods pm
            JOIN competition_payment_methods cpm ON pm.id = cpm.payment_method_id
            WHERE cpm.competition_id = ? AND pm.status = 'active'
            ORDER BY pm.nama_bank ASC
        ");
        $stmt->execute([$competition_id]);
        $payment_methods = $stmt->fetchAll();
    
    } catch (Exception $e) {
        die('Error generating invoice: ' . $e->getMessage());
    }
    
    // Handle print action
    if ($action === 'print') {
        echo '<style>@media print { .no-print { display: none; } }</style>';
        echo '<script>window.onload = function() { window.print(); }</script>';
    }
    ?>
    
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($invoice_title); ?></title>
        <!-- Library untuk membuat PDF di sisi client -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            body {
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #374151;
                background-color: #f8fafc;
            }
            .invoice-container {
                max-width: 700px;
                margin: 0 auto;
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                border: 1px solid #e5e7eb;
                overflow: hidden;
            }
            .invoice-header {
                margin-top: 0 !important;
                padding-top: 24px !important;
                background: linear-gradient(135deg, #667eea, #764ba2);
                color: #fff;
                padding: 24px 16px 16px 16px;
                text-align: center;
                page-break-inside: avoid;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
            }
            .invoice-header h1 { margin: 0 0 10px 0; font-size: 2rem; font-weight: 700; }
            .invoice-number { opacity: 0.95; font-size: 1rem; }
            .invoice-body { padding: 20px 16px 16px 16px; page-break-inside: avoid; }
            .invoice-info { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px; }
            .info-section h3 { color: #374151; margin: 0 0 10px 0; font-size: 1.08rem; border-bottom: 2px solid #e5e7eb; padding-bottom: 4px; }
            .info-item { display: flex; margin-bottom: 6px; }
            .info-label { font-weight: 600; color: #6b7280; min-width: 110px; }
            .info-value { color: #374151; }
            .athletes-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 0.98rem; }
            .athletes-table th { background: #f3f4f6; color: #374151; padding: 10px; text-align: left; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
            .athletes-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; }
            .athletes-table tr:hover { background: #f9fafb; }
            .total-section { background: #f8fafc; padding: 14px; border-radius: 8px; border-left: 4px solid #667eea; page-break-inside: avoid; margin-bottom: 10px; }
            .total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
            .total-row.grand-total { font-size: 1.08rem; font-weight: 700; color: #667eea; border-top: 2px solid #e5e7eb; padding-top: 8px; margin-top: 8px; }
            .payment-info { margin-top: 18px; padding: 14px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b; page-break-inside: avoid; }
            .payment-info h4 { color: #92400e; margin: 0 0 10px 0; }
            .bank-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; }
            .bank-item { background: white; padding: 10px; border-radius: 6px; border: 1px solid #fbbf24; }
            .bank-name { font-weight: 600; color: #92400e; margin-bottom: 4px; }
            .bank-account { font-family: monospace; font-size: 1.05rem; color: #374151; margin-bottom: 2px; }
            .bank-owner { font-size: 0.9rem; color: #6b7280; }
            .footer { text-align: center; padding: 14px; color: #6b7280; font-size: 0.92rem; border-top: 1px solid #e5e7eb; }
            .no-print { margin: 14px auto; text-align: center; max-width: 700px; }
            .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; margin: 0 5px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-weight: 600; transition: background-color 0.3s; }
            .btn-primary { background: #667eea; color: white; }
            .btn-primary:hover { background: #5a67d8; }
            .btn-success { background: #34d399; color: white; }
            .btn-success:hover { background: #10b981; }
            .btn-secondary { background: #6b7280; color: white; }
            .btn-secondary:hover { background: #4b5563; }
            @media print {
                body, html { background: #fff !important; margin: 0 !important; padding: 0 !important; }
                .invoice-container {
                    max-width: 700px !important;
                    width: 100% !important;
                    margin: 0 auto !important;
                    box-shadow: none !important;
                    border-radius: 8px !important;
                    border: 1px solid #e5e7eb !important;
                    padding: 0 !important;
                }
                .invoice-header, .invoice-body, .total-section, .payment-info { page-break-inside: avoid; }
                .no-print { display: none !important; }
            }
            @media (max-width: 768px) {
                .invoice-info { grid-template-columns: 1fr; gap: 20px; }
                .bank-info { grid-template-columns: 1fr; }
                .athletes-table { font-size: 0.9rem; }
                .athletes-table th, .athletes-table td { padding: 8px; }
            }
        </style>
    </head>
    <body>
        <div class="invoice-container" id="invoice">
            <div class="invoice-header">
                <h1><?php echo htmlspecialchars($invoice_title); ?></h1>
                <div class="invoice-number">
                    Invoice #<?php echo htmlspecialchars($invoice_code); ?>
                </div>
            </div>
            
            <div class="invoice-body">
                <div class="invoice-info">
                    <div class="info-section">
                        <h3>Informasi Perlombaan</h3>
                        <div class="info-item">
                            <span class="info-label">Nama Perlombaan:</span>
                            <span class="info-value"><?php echo htmlspecialchars($invoice_info['nama_perlombaan']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tanggal:</span>
                            <span class="info-value"><?php echo date('d M Y', strtotime($invoice_info['tanggal_pelaksanaan'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Lokasi:</span>
                            <span class="info-value"><?php echo htmlspecialchars($invoice_info['lokasi'] ?? '-'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kontingen:</span>
                            <span class="info-value"><?php echo htmlspecialchars($invoice_info['nama_kontingen']); ?></span>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3>Informasi Pendaftar</h3>
                        <div class="info-item">
                            <span class="info-label">Nama:</span>
                            <span class="info-value"><?php echo htmlspecialchars($invoice_info['user_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($invoice_info['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">WhatsApp:</span>
                            <span class="info-value"><?php echo htmlspecialchars($invoice_info['whatsapp']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tanggal Invoice:</span>
                            <span class="info-value"><?php echo date('d M Y'); ?></span>
                        </div>
                    </div>
                </div>
                
                <table class="athletes-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Atlet</th>
                            <th>Kategori</th>
                            <th>Jenis Kompetisi</th>
                            <th>Biaya</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($invoice_items as $item): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['athlete_name']); ?></strong>
                                    <br>
                                    <small>NIK: <?php echo htmlspecialchars($item['nik']); ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($item['category_name'])): ?>
                                        <?php echo htmlspecialchars($item['category_name']); ?>
                                        <br>
                                    <?php endif; ?>
                                    <?php if (!empty($item['age_category_name'])): ?>
                                        <small><?php echo htmlspecialchars($item['age_category_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['nama_kompetisi'] ?? '-'); ?></td>
                                <td><?php echo formatRupiah($item['biaya_pendaftaran'] ?? 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="total-section">
                    <div class="total-row">
                        <span>Jumlah Atlet:</span>
                        <span><?php echo count($invoice_items); ?> atlet</span>
                    </div>
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span><?php echo formatRupiah($total_amount); ?></span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total Pembayaran:</span>
                        <span><?php echo formatRupiah($total_amount); ?></span>
                    </div>
                </div>
                
                <?php
                if (!empty($payment_methods)):
                ?>
                <div class="payment-info">
                    <h4>Informasi Pembayaran</h4>
                    <p>Silakan lakukan pembayaran ke salah satu rekening berikut:</p>
                    <div class="bank-info">
                        <?php foreach ($payment_methods as $method): ?>
                            <div class="bank-item">
                                <div class="bank-name"><?php echo htmlspecialchars($method['nama_bank']); ?></div>
                                <div class="bank-account"><?php echo htmlspecialchars($method['nomor_rekening']); ?></div>
                                <div class="bank-owner">a.n. <?php echo htmlspecialchars($method['pemilik_rekening']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="margin-top: 15px; font-size: 0.9rem; color: #92400e;">
                        <strong>Catatan:</strong> Setelah melakukan pembayaran, silakan upload bukti pembayaran melalui sistem untuk verifikasi.
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="footer">
                <p>Invoice ini dibuat secara otomatis oleh sistem pada <?php echo date('d M Y H:i'); ?></p>
                <p>Untuk pertanyaan, silakan hubungi panitia perlombaan.</p>
            </div>
        </div>
        
        <div class="no-print">
            <a href="perlombaan.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Invoice
            </button>
        </div>
    
        <script>
            function downloadPDF() {
                const element = document.getElementById('invoice');
                const filename = '<?php echo "invoice_" . str_replace(" ", "_", $invoice_info['nama_perlombaan']) . ".pdf"; ?>';
                
                const opt = {
                    margin: 0,
                    filename: filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 0.8, useCORS: true },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['avoid-all'] }
                };
                // Menggunakan html2pdf untuk membuat dan menyimpan PDF
                html2pdf().set(opt).from(element).save();
            }
        </script>
    </body>
    </html>
