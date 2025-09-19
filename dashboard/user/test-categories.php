<?php
// File untuk testing kategori tanding
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    die('Unauthorized');
}

$competition_id = $_GET['competition_id'] ?? 5; // Default ke competition ID 5
$age_category_id = $_GET['age_category_id'] ?? null;

echo "<h2>Testing Categories for Competition ID: $competition_id</h2>";

// Test 1: Get all age categories
echo "<h3>1. Age Categories:</h3>";
$stmt = $pdo->prepare("SELECT * FROM age_categories WHERE competition_id = ? ORDER BY nama_kategori");
$stmt->execute([$competition_id]);
$age_categories = $stmt->fetchAll();

echo "<ul>";
foreach ($age_categories as $cat) {
    echo "<li>ID: {$cat['id']} - {$cat['nama_kategori']} ({$cat['usia_min']}-{$cat['usia_max']} tahun)</li>";
}
echo "</ul>";

// Test 2: Get all competition categories
echo "<h3>2. All Competition Categories:</h3>";
$stmt = $pdo->prepare("
    SELECT cc.*, ac.nama_kategori as age_category_name 
    FROM competition_categories cc 
    LEFT JOIN age_categories ac ON cc.age_category_id = ac.id 
    WHERE cc.competition_id = ?
    ORDER BY cc.nama_kategori
");
$stmt->execute([$competition_id]);
$all_categories = $stmt->fetchAll();

echo "<ul>";
foreach ($all_categories as $cat) {
    echo "<li>ID: {$cat['id']} - {$cat['nama_kategori']} (Age Cat: {$cat['age_category_name']})</li>";
}
echo "</ul>";

// Test 3: Get categories for specific age category
if ($age_category_id) {
    echo "<h3>3. Categories for Age Category ID: $age_category_id</h3>";
    $stmt = $pdo->prepare("
        SELECT cc.*, ac.nama_kategori as age_category_name 
        FROM competition_categories cc 
        LEFT JOIN age_categories ac ON cc.age_category_id = ac.id 
        WHERE cc.competition_id = ? AND cc.age_category_id = ?
        ORDER BY cc.nama_kategori
    ");
    $stmt->execute([$competition_id, $age_category_id]);
    $filtered_categories = $stmt->fetchAll();
    
    echo "<ul>";
    foreach ($filtered_categories as $cat) {
        echo "<li>ID: {$cat['id']} - {$cat['nama_kategori']} (Age Cat: {$cat['age_category_name']})</li>";
    }
    echo "</ul>";
}

// Test 4: Test API endpoint
echo "<h3>4. Test API Endpoint:</h3>";
$api_url = "get-competition-categories.php?competition_id=$competition_id";
if ($age_category_id) {
    $api_url .= "&age_category_id=$age_category_id";
}
echo "<p>API URL: <a href='$api_url' target='_blank'>$api_url</a></p>";

// Test form
echo "<h3>5. Test Form:</h3>";
echo "<form method='GET'>";
echo "<input type='hidden' name='competition_id' value='$competition_id'>";
echo "<select name='age_category_id' onchange='this.form.submit()'>";
echo "<option value=''>Select Age Category</option>";
foreach ($age_categories as $cat) {
    $selected = ($age_category_id == $cat['id']) ? 'selected' : '';
    echo "<option value='{$cat['id']}' $selected>{$cat['nama_kategori']}</option>";
}
echo "</select>";
echo "</form>";
?>
