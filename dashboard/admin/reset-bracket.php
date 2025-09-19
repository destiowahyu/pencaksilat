<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['draw_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$draw_id = $input['draw_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM bracket_results WHERE draw_id = ?");
    $stmt->execute([$draw_id]);

    echo json_encode(['success' => true, 'message' => 'Bracket reset successfully.']);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>
