<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in and is superadmin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    http_response_code(403);
    exit('Unauthorized');
}

$competition_id = intval($_GET['id'] ?? 0);

if (!$competition_id) {
    http_response_code(400);
    exit('Invalid competition ID');
}

// Get competition details
$stmt = $pdo->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM registrations WHERE competition_id = c.id) as total_peserta
    FROM competitions c 
    WHERE c.id = ?
");
$stmt->execute([$competition_id]);
$competition = $stmt->fetch();

if (!$competition) {
    http_response_code(404);
    exit('Competition not found');
}

// Get assigned admins
$stmt = $pdo->prepare("
    SELECT ca.*, u.nama, u.email, u.whatsapp
    FROM competition_admins ca
    JOIN users u ON ca.admin_id = u.id
    WHERE ca.competition_id = ?
    ORDER BY ca.assigned_at DESC
");
$stmt->execute([$competition_id]);
$assigned_admins = $stmt->fetchAll();

// Get all available admins (excluding already assigned ones)
$stmt = $pdo->prepare("
    SELECT u.id, u.nama, u.email, u.whatsapp
    FROM users u
    WHERE u.role = 'admin' 
    AND u.id NOT IN (
        SELECT admin_id 
        FROM competition_admins 
        WHERE competition_id = ?
    )
    ORDER BY u.nama
");
$stmt->execute([$competition_id]);
$available_admins = $stmt->fetchAll();
?>

<div style="padding: 20px;">
    <!-- Competition Information -->
    <div style="margin-bottom: 30px;">
        <h3>Informasi Perlombaan</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
            <div>
                <strong>Nama Perlombaan:</strong><br>
                <?php echo htmlspecialchars($competition['nama_perlombaan']); ?>
            </div>
            <div>
                <strong>Status:</strong><br>
                <span class="status-badge <?php echo $competition['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo $competition['status'] === 'active' ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
            <div>
                <strong>Lokasi:</strong><br>
                <?php echo htmlspecialchars($competition['lokasi'] ?? 'Tidak ditentukan'); ?>
            </div>
            <div>
                <strong>Tanggal Pelaksanaan:</strong><br>
                <?php echo $competition['tanggal_pelaksanaan'] ? date('d M Y', strtotime($competition['tanggal_pelaksanaan'])) : 'Tidak ditentukan'; ?>
            </div>
            <div>
                <strong>Deskripsi:</strong><br>
                <?php echo htmlspecialchars($competition['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
            </div>
            <div>
                <strong>Total Peserta:</strong><br>
                <?php echo $competition['total_peserta']; ?> orang
            </div>
        </div>
    </div>
    
    <!-- Assigned Admins -->
    <div style="margin-bottom: 30px;">
        <h3>Admin yang Bertanggung Jawab (<?php echo count($assigned_admins); ?>)</h3>
        <?php if (empty($assigned_admins)): ?>
            <div style="margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: center;">
                <i class="fas fa-user-tie" style="font-size: 2rem; color: #ccc; margin-bottom: 10px;"></i>
                <p style="color: #666; margin: 0;">Belum ada admin yang ditugaskan</p>
            </div>
        <?php else: ?>
            <div style="margin-top: 15px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6;">Nama Admin</th>
                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6;">Email</th>
                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6;">WhatsApp</th>
                            <th style="padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6;">Ditugaskan</th>
                            <th style="padding: 10px; text-align: center; border-bottom: 1px solid #dee2e6;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assigned_admins as $admin): ?>
                            <tr>
                                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                                    <strong><?php echo htmlspecialchars($admin['nama']); ?></strong>
                                </td>
                                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                                    <?php echo htmlspecialchars($admin['email']); ?>
                                </td>
                                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                                    <?php if ($admin['whatsapp']): ?>
                                        <a href="https://wa.me/<?php echo preg_replace('/^0/', '62', $admin['whatsapp']); ?>" 
                                           target="_blank" style="color: #25d366; text-decoration: none;">
                                            <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($admin['whatsapp']); ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                                    <?php echo date('d M Y H:i', strtotime($admin['assigned_at'])); ?>
                                </td>
                                <td style="padding: 10px; border-bottom: 1px solid #dee2e6; text-align: center;">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus admin ini?')">
                                        <input type="hidden" name="action" value="remove_admin">
                                        <input type="hidden" name="competition_id" value="<?php echo $competition_id; ?>">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Hapus Admin">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Add Admin Form -->
    <div>
        <h3>Tambah Admin</h3>
        <?php if (empty($available_admins)): ?>
            <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
                <i class="fas fa-info-circle" style="color: #856404;"></i>
                <span style="color: #856404;">Semua admin sudah ditugaskan untuk perlombaan ini.</span>
            </div>
        <?php else: ?>
            <form method="POST" style="margin-top: 15px;">
                <input type="hidden" name="action" value="assign_admin">
                <input type="hidden" name="competition_id" value="<?php echo $competition_id; ?>">
                <div style="display: flex; gap: 10px; align-items: end;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label for="admin_id">Pilih Admin:</label>
                        <select name="admin_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">Pilih Admin</option>
                            <?php foreach ($available_admins as $admin): ?>
                                <option value="<?php echo $admin['id']; ?>">
                                    <?php echo htmlspecialchars($admin['nama']); ?> (<?php echo htmlspecialchars($admin['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-plus"></i> Tambah
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
