<?php
// This file shows the modifications needed for the existing perlombaan.php
// Add edit functionality to the registered athletes table

// In the registered athletes table, add an "Aksi" column and edit button:
?>

<!-- Registered Athletes Table -->
<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Atlet</th>
                <th>Kontingen</th>
                <th>Jenis Kompetisi</th>
                <th>Kategori Umur</th>
                <th>Kategori Tanding</th>
                <th>Biaya</th>
                <th>Status Pembayaran</th>
                <th>Tanggal Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($registered_athletes as $index => $reg): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td>
                    <div class="athlete-info">
                        <strong><?php echo htmlspecialchars($reg['athlete_name']); ?></strong>
                        <small><?php echo htmlspecialchars($reg['nik']); ?></small>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($reg['nama_kontingen']); ?></td>
                <td>
                    <span class="competition-type-badge">
                        <?php echo htmlspecialchars($reg['nama_kompetisi']); ?>
                    </span>
                </td>
                <td>
                    <?php if ($reg['age_category_name']): ?>
                        <span class="category-badge age-category">
                            <?php echo htmlspecialchars($reg['age_category_name']); ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($reg['competition_category_name']): ?>
                        <span class="category-badge competition-category">
                            <?php echo htmlspecialchars($reg['competition_category_name']); ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="price-amount">
                        <?php echo $reg['biaya_pendaftaran'] ? formatRupiah($reg['biaya_pendaftaran']) : 'Gratis'; ?>
                    </span>
                </td>
                <td>
                    <span class="status-badge status-<?php echo $reg['payment_status']; ?>">
                        <?php 
                        switch($reg['payment_status']) {
                            case 'pending': echo 'Belum Bayar'; break;
                            case 'paid': echo 'Sudah Bayar'; break;
                            case 'verified': echo 'Terverifikasi'; break;
                            default: echo 'Unknown';
                        }
                        ?>
                    </span>
                </td>
                <td><?php echo date('d M Y', strtotime($reg['created_at'])); ?></td>
                <td>
                    <div class="action-buttons">
                        <?php if ($reg['payment_status'] === 'pending'): ?>
                            <a href="edit-pendaftaran.php?id=<?php echo $reg['registration_id']; ?>" 
                               class="btn-action btn-edit" 
                               title="Edit Pendaftaran - Ubah Atlet, Kategori, atau Jenis Kompetisi">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <button onclick="cancelRegistration(<?php echo $reg['registration_id']; ?>)" 
                                    class="btn-action btn-delete" 
                                    title="Batalkan Pendaftaran">
                                <i class="fas fa-trash"></i>
                                <span>Batal</span>
                            </button>
                        <?php elseif ($reg['payment_status'] === 'paid'): ?>
                            <a href="upload-bukti-bayar.php?id=<?php echo $reg['registration_id']; ?>" 
                               class="btn-action btn-upload" 
                               title="Upload Bukti Pembayaran">
                                <i class="fas fa-upload"></i>
                                <span>Upload</span>
                            </a>
                        <?php else: ?>
                            <span class="text-success">
                                <i class="fas fa-check-circle"></i> Selesai
                            </span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.8rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s;
    font-weight: 500;
    min-width: 70px;
    justify-content: center;
}

.btn-edit {
    background: #17a2b8;
    color: white;
}

.btn-edit:hover {
    background: #138496;
    transform: translateY(-1px);
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.btn-upload {
    background: #28a745;
    color: white;
}

.btn-upload:hover {
    background: #218838;
    transform: translateY(-1px);
}

.athlete-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.athlete-info strong {
    color: var(--text-color);
    font-size: 0.9rem;
}

.athlete-info small {
    color: var(--text-light);
    font-size: 0.75rem;
}

.competition-type-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.category-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
}

.age-category {
    background: #f3e5f5;
    color: #7b1fa2;
}

.competition-category {
    background: #e8f5e8;
    color: #2e7d32;
}

.price-amount {
    color: var(--success-color);
    font-weight: 600;
    font-size: 0.9rem;
}

.text-success {
    color: var(--success-color);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.8rem;
}

.text-muted {
    color: #6c757d;
    font-style: italic;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        gap: 5px;
    }
    
    .btn-action {
        min-width: 60px;
        padding: 6px 10px;
        font-size: 0.75rem;
    }
    
    .btn-action span {
        display: none;
    }
}
</style>

<script>
function cancelRegistration(registrationId) {
    if (confirm('Apakah Anda yakin ingin membatalkan pendaftaran ini?')) {
        // Create form to submit cancellation
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'cancel-registration.php';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'registration_id';
        input.value = registrationId;
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
