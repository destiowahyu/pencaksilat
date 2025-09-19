<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
http_response_code(403);
exit('Unauthorized');
}

$competition_id = $_GET['id'] ?? 0;

// Get competition details
$stmt = $pdo->prepare("SELECT * FROM competitions WHERE id = ? AND status = 'active'");
$stmt->execute([$competition_id]);
$competition = $stmt->fetch();

if (!$competition) {
http_response_code(404);
exit('Competition not found');
}

// Get documents
$stmt = $pdo->prepare("SELECT * FROM competition_documents WHERE competition_id = ?");
$stmt->execute([$competition_id]);
$documents = $stmt->fetchAll();

// Get contacts
$stmt = $pdo->prepare("SELECT * FROM competition_contacts WHERE competition_id = ?");
$stmt->execute([$competition_id]);
$contacts = $stmt->fetchAll();

// Get categories
$stmt = $pdo->prepare("
SELECT cc.*, ac.nama_kategori as age_category_name, ac.usia_min, ac.usia_max
FROM competition_categories cc 
LEFT JOIN age_categories ac ON cc.age_category_id = ac.id 
WHERE cc.competition_id = ?
ORDER BY cc.nama_kategori
");
$stmt->execute([$competition_id]);
$categories = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$competition_id]);
$competition_types = $stmt->fetchAll();

// Helper function to format currency
function formatRupiah($number) {
return 'Rp ' . number_format($number, 0, ',', '.');
}

// Helper function to format date
function formatDate($date) {
if (!$date) return '-';
return date('d F Y', strtotime($date));
}

// Determine registration status
$registration_status = $competition['registration_status'] ?? 'coming_soon';
if ($registration_status === 'auto' || empty($registration_status)) {
$today = date('Y-m-d');
$open_date = $competition['tanggal_open_regist'];
$close_date = $competition['tanggal_close_regist'];

if ($open_date && $close_date) {
    if ($today < $open_date) {
        $registration_status = 'coming_soon';
    } elseif ($today >= $open_date && $today <= $close_date) {
        $registration_status = 'open_regist';
    } else {
        $registration_status = 'close_regist';
    }
}
}
?>

<div class="competition-detail">
<!-- Poster Section -->
<?php if (!empty($competition['poster'])): ?>
<div class="detail-poster">
    <img src="../../uploads/posters/<?php echo htmlspecialchars($competition['poster']); ?>" 
         alt="<?php echo htmlspecialchars($competition['nama_perlombaan']); ?>"
         class="detail-poster-image">
</div>
<?php endif; ?>

<!-- Basic Information -->
<div class="detail-section">
    <h3><i class="fas fa-info-circle"></i> Informasi Umum</h3>
    <div class="detail-grid">
        <div class="detail-item">
            <label>Nama Perlombaan:</label>
            <span><?php echo htmlspecialchars($competition['nama_perlombaan']); ?></span>
        </div>
        <div class="detail-item">
            <label>Status Pendaftaran:</label>
            <span class="status-badge status-<?php echo $registration_status; ?>">
                <?php 
                switch($registration_status) {
                    case 'coming_soon': echo 'Segera Hadir'; break;
                    case 'open_regist': echo 'Buka Pendaftaran'; break;
                    case 'close_regist': echo 'Tutup Pendaftaran'; break;
                    default: echo 'Tidak Aktif';
                }
                ?>
            </span>
        </div>
        <div class="detail-item">
            <label>Lokasi:</label>
            <span><?php echo htmlspecialchars($competition['lokasi'] ?? '-'); ?></span>
        </div>
        <div class="detail-item">
            <label>Tanggal Buka Pendaftaran:</label>
            <span><?php echo formatDate($competition['tanggal_open_regist']); ?></span>
        </div>
        <div class="detail-item">
            <label>Tanggal Tutup Pendaftaran:</label>
            <span><?php echo formatDate($competition['tanggal_close_regist']); ?></span>
        </div>
        <div class="detail-item">
            <label>Tanggal Pelaksanaan:</label>
            <span><?php echo formatDate($competition['tanggal_pelaksanaan']); ?></span>
        </div>
        <?php if (!empty($competition['whatsapp_group'])): ?>
        <div class="detail-item">
            <label>WhatsApp Group:</label>
            <span>
                <a href="<?php echo htmlspecialchars($competition['whatsapp_group']); ?>" target="_blank" class="btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> Join Group
                </a>
            </span>
        </div>
        <?php endif; ?>
        <div class="detail-item full-width">
            <label>Deskripsi:</label>
            <span><?php echo nl2br(htmlspecialchars($competition['deskripsi'] ?? '-')); ?></span>
        </div>
    </div>
</div>

<!-- Documents Section -->
<?php if (!empty($documents)): ?>
<div class="detail-section">
    <h3><i class="fas fa-file-alt"></i> Dokumen</h3>
    <div class="document-list">
        <?php foreach ($documents as $doc): ?>
        <div class="document-item">
            <i class="fas fa-file-pdf"></i>
            <span><?php echo htmlspecialchars($doc['nama_dokumen']); ?></span>
            <a href="../../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn-download">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<div class="detail-section">
    <h3><i class="fas fa-list"></i> Kategori Tanding</h3>
    <div class="category-list">
        <?php foreach ($categories as $cat): ?>
        <div class="category-item">
            <div class="category-header">
                <strong><?php echo htmlspecialchars($cat['nama_kategori']); ?></strong>
            </div>
            <div class="category-details">
                <?php if ($cat['age_category_name']): ?>
                    <div class="category-detail">
                        <span class="detail-label">Kategori Umur:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($cat['age_category_name']); ?> (<?php echo $cat['usia_min']; ?>-<?php echo $cat['usia_max']; ?> tahun)</span>
                    </div>
                <?php endif; ?>
                <?php if ($cat['berat_min'] || $cat['berat_max']): ?>
                    <div class="category-detail">
                        <span class="detail-label">Berat Badan:</span>
                        <span class="detail-value">
                            <?php 
                            if ($cat['berat_min'] && $cat['berat_max']) {
                                echo $cat['berat_min'] . ' - ' . $cat['berat_max'] . ' kg';
                            } elseif ($cat['berat_min']) {
                                echo 'Min ' . $cat['berat_min'] . ' kg';
                            } elseif ($cat['berat_max']) {
                                echo 'Max ' . $cat['berat_max'] . ' kg';
                            }
                            ?>
                        </span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($cat['deskripsi'])): ?>
                    <div class="category-detail">
                        <span class="detail-label">Deskripsi:</span>
                        <span class="detail-value"><?php echo nl2br(htmlspecialchars($cat['deskripsi'])); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Competition Types Section -->
<?php if (!empty($competition_types)): ?>
<div class="detail-section">
    <h3><i class="fas fa-star"></i> Jenis Kompetisi</h3>
    <div class="competition-type-list">
        <?php foreach ($competition_types as $comp_type): ?>
        <div class="competition-type-item">
            <div class="competition-type-header">
                <strong><?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?></strong>
                <?php if ($comp_type['biaya_pendaftaran']): ?>
                    <span class="price-tag"><?php echo formatRupiah($comp_type['biaya_pendaftaran']); ?></span>
                <?php else: ?>
                    <span class="price-tag free">Gratis</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($comp_type['deskripsi'])): ?>
                <div class="competition-type-description">
                    <?php echo nl2br(htmlspecialchars($comp_type['deskripsi'])); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Contacts Section -->
<?php if (!empty($contacts)): ?>
<div class="detail-section">
    <h3><i class="fas fa-address-book"></i> Kontak Panitia</h3>
    <div class="contact-list">
        <?php foreach ($contacts as $contact): ?>
        <div class="contact-item">
            <div class="contact-info">
                <strong><?php echo htmlspecialchars($contact['nama_kontak']); ?></strong>
                <?php if (!empty($contact['jabatan'])): ?>
                    <span class="contact-position"><?php echo htmlspecialchars($contact['jabatan']); ?></span>
                <?php endif; ?>
            </div>
            <div class="contact-details">
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contact['nomor_whatsapp']); ?>" target="_blank" class="contact-whatsapp">
                    <i class="fab fa-whatsapp"></i> <?php echo htmlspecialchars($contact['nomor_whatsapp']); ?>
                </a>
                <?php if (!empty($contact['email'])): ?>
                    <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="contact-email">
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['email']); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Registration Button -->
<?php if ($registration_status == 'open_regist'): ?>
<div class="detail-actions">
    <button class="btn-register-now" onclick="registerNow(<?php echo $competition_id; ?>)">
        <i class="fas fa-user-plus"></i> Daftar Sekarang
    </button>
</div>
<?php endif; ?>
</div>

<style>
.competition-detail {
max-width: 100%;
}

.detail-poster {
text-align: center;
margin-bottom: 30px;
}

.detail-poster-image {
max-width: 100%;
max-height: 400px;
object-fit: contain;
border-radius: 8px;
box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.detail-section {
margin-bottom: 30px;
padding-bottom: 20px;
border-bottom: 1px solid var(--border-color);
}

.detail-section:last-child {
border-bottom: none;
}

.detail-section h3 {
color: var(--primary-color);
margin-bottom: 15px;
display: flex;
align-items: center;
gap: 10px;
}

.detail-grid {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
gap: 15px;
}

.detail-item {
display: flex;
flex-direction: column;
gap: 5px;
}

.detail-item.full-width {
grid-column: 1 / -1;
}

.detail-item label {
font-weight: 600;
color: var(--text-color);
font-size: 0.9rem;
}

.detail-item span {
color: var(--text-light);
}

.btn-whatsapp {
background: #25d366;
color: white;
padding: 6px 12px;
border-radius: 4px;
text-decoration: none;
font-size: 0.9rem;
display: inline-flex;
align-items: center;
gap: 6px;
}

.document-list {
display: flex;
flex-direction: column;
gap: 10px;
}

.document-item {
display: flex;
align-items: center;
gap: 15px;
padding: 15px;
background: var(--light-color);
border-radius: 8px;
}

.document-item i {
color: var(--danger-color);
font-size: 1.2rem;
}

.btn-download {
background: var(--primary-color);
color: white;
padding: 6px 12px;
border-radius: 4px;
text-decoration: none;
font-size: 0.8rem;
display: flex;
align-items: center;
gap: 4px;
margin-left: auto;
}

.category-list {
display: flex;
flex-direction: column;
gap: 15px;
}

.category-item {
padding: 15px;
background: var(--light-color);
border-radius: 8px;
}

.category-header {
margin-bottom: 10px;
}

.category-details {
display: flex;
flex-direction: column;
gap: 8px;
}

.category-detail {
display: flex;
flex-direction: column;
gap: 3px;
}

.detail-label {
font-weight: 600;
color: var(--text-color);
font-size: 0.9rem;
}

.detail-value {
color: var(--text-light);
font-size: 0.95rem;
}

.competition-type-list {
display: flex;
flex-direction: column;
gap: 15px;
}

.competition-type-item {
padding: 15px;
background: var(--light-color);
border-radius: 8px;
}

.competition-type-header {
display: flex;
justify-content: space-between;
align-items: center;
margin-bottom: 10px;
}

.price-tag {
background: var(--primary-color);
color: white;
padding: 4px 12px;
border-radius: 12px;
font-size: 0.8rem;
font-weight: 600;
}

.price-tag.free {
background: var(--success-color);
}

.competition-type-description {
color: var(--text-light);
font-size: 0.9rem;
line-height: 1.5;
}

.contact-list {
display: flex;
flex-direction: column;
gap: 15px;
}

.contact-item {
padding: 15px;
background: var(--light-color);
border-radius: 8px;
}

.contact-info {
margin-bottom: 10px;
}

.contact-position {
color: var(--text-light);
font-size: 0.9rem;
margin-left: 10px;
}

.contact-details {
display: flex;
flex-direction: column;
gap: 8px;
}

.contact-whatsapp {
color: #25d366;
text-decoration: none;
display: flex;
align-items: center;
gap: 6px;
font-size: 0.9rem;
}

.contact-email {
color: var(--primary-color);
text-decoration: none;
display: flex;
align-items: center;
gap: 6px;
font-size: 0.9rem;
}

.detail-actions {
text-align: center;
margin-top: 30px;
padding-top: 20px;
border-top: 1px solid var(--border-color);
}

.btn-register-now {
background: var(--primary-color);
color: white;
padding: 15px 30px;
border: none;
border-radius: 8px;
font-size: 1.1rem;
font-weight: 600;
cursor: pointer;
display: inline-flex;
align-items: center;
gap: 10px;
transition: background 0.3s;
}

.btn-register-now:hover {
background: var(--primary-dark);
}

@media (max-width: 768px) {
.detail-grid {
    grid-template-columns: 1fr;
}

.competition-type-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.contact-details {
    flex-direction: column;
}
}
</style>
