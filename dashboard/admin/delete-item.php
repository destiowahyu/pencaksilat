<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;
$competition_id = $_GET['competition_id'] ?? 0;

if (!$type || !$id || !$competition_id) {
    header('Location: perlombaan.php');
    exit();
}

// Verify admin has access to this competition
$stmt = $pdo->prepare("
    SELECT c.* FROM competitions c 
    JOIN competition_admins ca ON c.id = ca.competition_id 
    WHERE c.id = ? AND ca.admin_id = ?
");
$stmt->execute([$competition_id, $_SESSION['user_id']]);
$competition = $stmt->fetch();

if (!$competition) {
    header('Location: perlombaan.php');
    exit();
}

try {
    switch ($type) {
        case 'contact':
            $stmt = $pdo->prepare("DELETE FROM competition_contacts WHERE id = ? AND competition_id = ?");
            $stmt->execute([$id, $competition_id]);
            break;
            
        case 'document':
            // Get file path first to delete the file
            $stmt = $pdo->prepare("SELECT file_path FROM competition_documents WHERE id = ? AND competition_id = ?");
            $stmt->execute([$id, $competition_id]);
            $document = $stmt->fetch();
            
            if ($document) {
                // Delete file from server
                $file_path = '../../uploads/documents/' . $document['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Delete from database
                $stmt = $pdo->prepare("DELETE FROM competition_documents WHERE id = ? AND competition_id = ?");
                $stmt->execute([$id, $competition_id]);
            }
            break;
            
        case 'category':
            $stmt = $pdo->prepare("DELETE FROM competition_categories WHERE id = ? AND competition_id = ?");
            $stmt->execute([$id, $competition_id]);
            break;
            
        case 'age_category':
            $stmt = $pdo->prepare("DELETE FROM age_categories WHERE id = ? AND competition_id = ?");
            $stmt->execute([$id, $competition_id]);
            break;
            
        case 'competition_type':
            $stmt = $pdo->prepare("DELETE FROM competition_types WHERE id = ? AND competition_id = ?");
            $stmt->execute([$id, $competition_id]);
            break;
    }
    
    $success = true;
} catch (PDOException $e) {
    $success = false;
}

// Redirect back to edit page
header('Location: perlombaan-edit.php?id=' . $competition_id . ($success ? '&deleted=1' : '&error=1'));
exit();
?>
