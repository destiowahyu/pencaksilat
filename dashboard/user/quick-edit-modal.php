<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    exit('Unauthorized');
}

$registration_id = $_GET['id'] ?? 0;

if (!$registration_id) {
    http_response_code(400);
    exit('Registration ID required');
}

// Get registration details
$stmt = $pdo->prepare("
    SELECT 
        r.*,
        c.nama_perlombaan,
        a.nama as athlete_name,
        a.user_id as athlete_user_id,
        k.nama_kontingen,
        ct.nama_kompetisi,
        ac.nama_kategori as age_category_name,
        cc.nama_kategori as competition_category_name
    FROM registrations r
    JOIN competitions c ON r.competition_id = c.id
    JOIN athletes a ON r.athlete_id = a.id
    JOIN kontingen k ON r.kontingen_id = k.id
    LEFT JOIN competition_types ct ON r.competition_type_id = ct.id
    LEFT JOIN age_categories ac ON r.age_category_id = ac.id
    LEFT JOIN competition_categories cc ON r.category_id = cc.id
    WHERE r.id = ?
");
$stmt->execute([$registration_id]);
$registration = $stmt->fetch();

if (!$registration || $registration['athlete_user_id'] != $_SESSION['user_id']) {
    http_response_code(404);
    exit('Registration not found');
}

// Get user's athletes
$stmt = $pdo->prepare("
    SELECT a.*, k.nama_kontingen 
    FROM athletes a 
    JOIN kontingen k ON a.kontingen_id = k.id 
    WHERE a.user_id = ? 
    ORDER BY a.nama
");
$stmt->execute([$_SESSION['user_id']]);
$athletes = $stmt->fetchAll();

// Get competition types
$stmt = $pdo->prepare("SELECT * FROM competition_types WHERE competition_id = ? ORDER BY nama_kompetisi");
$stmt->execute([$registration['competition_id']]);
$competition_types = $stmt->fetchAll();

// Get age categories
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$registration['competition_id']]);
$age_categories = $stmt->fetchAll();
?>

<div class="quick-edit-modal">
    <div class="modal-header">
        <h3><i class="fas fa-edit"></i> Edit Cepat Pendaftaran</h3>
        <button class="close-modal" onclick="closeQuickEditModal()">&times;</button>
    </div>

    <div class="modal-body">
        <div class="current-registration">
            <h4>Pendaftaran Saat Ini:</h4>
            <div class="current-info">
                <span><strong><?php echo htmlspecialchars($registration['athlete_name']); ?></strong></span>
                <span><?php echo htmlspecialchars($registration['nama_kompetisi']); ?></span>
                <?php if ($registration['age_category_name']): ?>
                    <span><?php echo htmlspecialchars($registration['age_category_name']); ?></span>
                <?php endif; ?>
                <?php if ($registration['competition_category_name']): ?>
                    <span><?php echo htmlspecialchars($registration['competition_category_name']); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <form id="quickEditForm" method="POST" action="update-registration.php">
            <input type="hidden" name="registration_id" value="<?php echo $registration_id; ?>">
            
            <div class="form-group">
                <label for="quick_athlete_id">Ganti Atlet:</label>
                <select id="quick_athlete_id" name="athlete_id" onchange="updateAthleteInfo()">
                    <?php foreach ($athletes as $athlete): ?>
                        <option value="<?php echo $athlete['id']; ?>" 
                                <?php echo ($athlete['id'] == $registration['athlete_id']) ? 'selected' : ''; ?>
                                data-age="<?php echo date_diff(date_create($athlete['tanggal_lahir']), date_create('today'))->y; ?>"
                                data-weight="<?php echo $athlete['berat_badan']; ?>">
                            <?php echo htmlspecialchars($athlete['nama']); ?> - <?php echo htmlspecialchars($athlete['nama_kontingen']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quick_competition_type_id">Ganti Jenis Kompetisi:</label>
                <select id="quick_competition_type_id" name="competition_type_id" onchange="handleQuickCompetitionTypeChange()">
                    <?php foreach ($competition_types as $comp_type): ?>
                        <option value="<?php echo $comp_type['id']; ?>" 
                                <?php echo ($comp_type['id'] == $registration['competition_type_id']) ? 'selected' : ''; ?>
                                data-is-tanding="<?php echo (stripos($comp_type['nama_kompetisi'], 'tanding') !== false) ? '1' : '0'; ?>">
                            <?php echo htmlspecialchars($comp_type['nama_kompetisi']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="quickAgeCategoryGroup">
                <label for="quick_age_category_id">Ganti Kategori Umur:</label>
                <select id="quick_age_category_id" name="age_category_id" onchange="loadQuickCompetitionCategories()">
                    <option value="">Pilih Kategori Umur</option>
                    <?php foreach ($age_categories as $age_cat): ?>
                        <option value="<?php echo $age_cat['id']; ?>" 
                                <?php echo ($age_cat['id'] == $registration['age_category_id']) ? 'selected' : ''; ?>
                                data-min="<?php echo $age_cat['usia_min']; ?>"
                                data-max="<?php echo $age_cat['usia_max']; ?>">
                            <?php echo htmlspecialchars($age_cat['nama_kategori']); ?> (<?php echo $age_cat['usia_min']; ?>-<?php echo $age_cat['usia_max']; ?> tahun)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="quickCategoryGroup">
                <label for="quick_category_id">Ganti Kategori Tanding:</label>
                <select id="quick_category_id" name="category_id">
                    <option value="">Pilih Kategori Tanding</option>
                </select>
            </div>

            <div class="change-summary" id="changeSummary" style="display: none;">
                <h4><i class="fas fa-exclamation-triangle"></i> Perubahan yang Akan Dilakukan:</h4>
                <ul id="changesList"></ul>
            </div>
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn-cancel" onclick="closeQuickEditModal()">
            <i class="fas fa-times"></i> Batal
        </button>
        <button type="submit" form="quickEditForm" class="btn-save" id="quickSaveBtn">
            <i class="fas fa-save"></i> Simpan Perubahan
        </button>
    </div>
</div>

<style>
.quick-edit-modal {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 20px;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close-modal {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-modal:hover {
    background: rgba(255,255,255,0.2);
}

.modal-body {
    padding: 25px;
}

.current-registration {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.current-registration h4 {
    margin: 0 0 10px 0;
    color: var(--primary-color);
}

.current-info {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.current-info span {
    background: white;
    padding: 5px 10px;
    border-radius: 12px;
    border: 1px solid #dee2e6;
    font-size: 0.9rem;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-color);
}

.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid var(--border-color);
    border-radius: 6px;
    font-size: 0.9rem;
}

.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.change-summary {
    background: #fff3cd;
    border: 2px solid #ffc107;
    padding: 15px;
    border-radius: 8px;
    margin-top: 20px;
}

.change-summary h4 {
    margin: 0 0 10px 0;
    color: #856404;
    display: flex;
    align-items: center;
    gap: 8px;
}

.change-summary ul {
    margin: 0;
    padding-left: 20px;
}

.change-summary li {
    color: #856404;
    margin-bottom: 5px;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.btn-cancel, .btn-save {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #5a6268;
}

.btn-save {
    background: var(--primary-color);
    color: white;
}

.btn-save:hover {
    background: var(--primary-dark);
}

@media (max-width: 768px) {
    .quick-edit-modal {
        width: 95%;
        margin: 10px;
    }
    
    .modal-footer {
        flex-direction: column;
    }
}
</style>

<script>
const COMPETITION_ID = <?php echo $registration['competition_id']; ?>;
const CURRENT_ATHLETE_ID = <?php echo $registration['athlete_id']; ?>;
const CURRENT_COMPETITION_TYPE_ID = <?php echo $registration['competition_type_id']; ?>;
const CURRENT_AGE_CATEGORY_ID = <?php echo $registration['age_category_id'] ?: 'null'; ?>;
const CURRENT_CATEGORY_ID = <?php echo $registration['category_id'] ?: 'null'; ?>;

function updateAthleteInfo() {
    checkForChanges();
    validateAgeCategories();
}

function handleQuickCompetitionTypeChange() {
    const select = document.getElementById('quick_competition_type_id');
    const option = select.options[select.selectedIndex];
    const isTanding = option.dataset.isTanding === '1';
    
    const ageCategoryGroup = document.getElementById('quickAgeCategoryGroup');
    const categoryGroup = document.getElementById('quickCategoryGroup');
    
    if (isTanding) {
        ageCategoryGroup.style.display = 'block';
        categoryGroup.style.display = 'block';
    } else {
        ageCategoryGroup.style.display = 'none';
        categoryGroup.style.display = 'none';
        document.getElementById('quick_age_category_id').value = '';
        document.getElementById('quick_category_id').value = '';
    }
    
    checkForChanges();
}

function loadQuickCompetitionCategories() {
    const ageCategoryId = document.getElementById('quick_age_category_id').value;
    const categorySelect = document.getElementById('quick_category_id');
    
    categorySelect.innerHTML = '<option value="">Pilih Kategori Tanding</option>';
    
    if (ageCategoryId) {
        fetch(`get-competition-categories.php?competition_id=${COMPETITION_ID}&age_category_id=${ageCategoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    data.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.nama_kategori;
                        
                        if (CURRENT_CATEGORY_ID && category.id == CURRENT_CATEGORY_ID) {
                            option.selected = true;
                        }
                        
                        categorySelect.appendChild(option);
                    });
                }
                checkForChanges();
            });
    }
    checkForChanges();
}

function validateAgeCategories() {
    const athleteSelect = document.getElementById('quick_athlete_id');
    const athleteOption = athleteSelect.options[athleteSelect.selectedIndex];
    const athleteAge = parseInt(athleteOption.dataset.age);
    
    const ageCategorySelect = document.getElementById('quick_age_category_id');
    const options = ageCategorySelect.options;
    
    for (let i = 1; i < options.length; i++) {
        const option = options[i];
        const minAge = parseInt(option.dataset.min);
        const maxAge = parseInt(option.dataset.max);
        
        if (athleteAge >= minAge && athleteAge <= maxAge) {
            option.disabled = false;
            option.style.color = '';
        } else {
            option.disabled = true;
            option.style.color = '#ccc';
        }
    }
}

function checkForChanges() {
    const changes = [];
    const changeSummary = document.getElementById('changeSummary');
    const changesList = document.getElementById('changesList');
    const saveBtn = document.getElementById('quickSaveBtn');
    
    // Check athlete change
    const currentAthleteId = document.getElementById('quick_athlete_id').value;
    if (currentAthleteId != CURRENT_ATHLETE_ID) {
        const athleteName = document.getElementById('quick_athlete_id').options[document.getElementById('quick_athlete_id').selectedIndex].text;
        changes.push(`Atlet akan diganti menjadi: ${athleteName.split(' - ')[0]}`);
    }
    
    // Check competition type change
    const currentCompetitionTypeId = document.getElementById('quick_competition_type_id').value;
    if (currentCompetitionTypeId != CURRENT_COMPETITION_TYPE_ID) {
        const competitionTypeName = document.getElementById('quick_competition_type_id').options[document.getElementById('quick_competition_type_id').selectedIndex].text;
        changes.push(`Jenis kompetisi akan diganti menjadi: ${competitionTypeName}`);
    }
    
    // Check age category change
    const currentAgeCategoryId = document.getElementById('quick_age_category_id').value;
    if (currentAgeCategoryId != CURRENT_AGE_CATEGORY_ID) {
        if (currentAgeCategoryId) {
            const ageCategoryName = document.getElementById('quick_age_category_id').options[document.getElementById('quick_age_category_id').selectedIndex].text;
            changes.push(`Kategori umur akan diganti menjadi: ${ageCategoryName}`);
        } else {
            changes.push('Kategori umur akan dihapus');
        }
    }
    
    // Check competition category change
    const currentCategoryId = document.getElementById('quick_category_id').value;
    if (currentCategoryId != CURRENT_CATEGORY_ID) {
        if (currentCategoryId) {
            const categoryName = document.getElementById('quick_category_id').options[document.getElementById('quick_category_id').selectedIndex].text;
            changes.push(`Kategori tanding akan diganti menjadi: ${categoryName}`);
        } else {
            changes.push('Kategori tanding akan dihapus');
        }
    }
    
    if (changes.length > 0) {
        changesList.innerHTML = changes.map(change => `<li>${change}</li>`).join('');
        changeSummary.style.display = 'block';
        saveBtn.disabled = false;
    } else {
        changeSummary.style.display = 'none';
        saveBtn.disabled = true;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    handleQuickCompetitionTypeChange();
    validateAgeCategories();
    if (document.getElementById('quick_age_category_id').value) {
        loadQuickCompetitionCategories();
    }
    
    // Add change listeners
    document.getElementById('quick_athlete_id').addEventListener('change', updateAthleteInfo);
    document.getElementById('quick_competition_type_id').addEventListener('change', handleQuickCompetitionTypeChange);
    document.getElementById('quick_age_category_id').addEventListener('change', loadQuickCompetitionCategories);
    document.getElementById('quick_category_id').addEventListener('change', checkForChanges);
});
</script>
