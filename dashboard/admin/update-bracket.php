<?php
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['draw_id']) || !isset($input['winners'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$draw_id = $input['draw_id'];
$winners = $input['winners'];
$kategori_umur = $input['kategori_umur'] ?? null;
$jenis_kelamin = $input['jenis_kelamin'] ?? null;
$jenis_kompetisi = $input['jenis_kompetisi'] ?? null;
$kategori_tanding = $input['kategori_tanding'] ?? null;

// Debug: log the received data
error_log("Received bracket update data: " . json_encode($input));

try {
    $pdo->beginTransaction();

    foreach ($winners as $winner) {
        $round = $winner['round'];
        $match_id = $winner['match_id'];
        $player_id = $winner['player_id'];

        // Debug: log each winner entry
        error_log("Processing winner: round=$round, match_id=$match_id, player_id=$player_id");

        // Validate player_id - skip if it's 'bye' or not a valid integer
        if ($player_id === 'bye' || !is_numeric($player_id) || $player_id <= 0) {
            error_log("Skipping winner: invalid player_id=$player_id");
            continue; // Skip this winner entry
        }

        // Validate match_id and round
        if (!is_numeric($match_id) || $match_id <= 0 || !is_numeric($round) || $round <= 0) {
            error_log("Skipping winner: invalid match_id=$match_id or round=$round");
            continue; // Skip this winner entry
        }

        // Check if a record already exists for this draw_id, round, and match_id
        $stmt = $pdo->prepare("SELECT id FROM bracket_results WHERE draw_id = ? AND round = ? AND match_id = ?");
        $stmt->execute([$draw_id, $round, $match_id]);
        $existing_id = $stmt->fetchColumn();

        if ($existing_id) {
            // Update existing record
            $stmt = $pdo->prepare("UPDATE bracket_results SET winner_player_id = ?, kategori_umur = ?, jenis_kelamin = ?, jenis_kompetisi = ?, kategori_tanding = ? WHERE id = ?");
            $stmt->execute([$player_id, $kategori_umur, $jenis_kelamin, $jenis_kompetisi, $kategori_tanding, $existing_id]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("INSERT INTO bracket_results (draw_id, round, match_id, winner_player_id, kategori_umur, jenis_kelamin, jenis_kompetisi, kategori_tanding) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$draw_id, $round, $match_id, $player_id, $kategori_umur, $jenis_kelamin, $jenis_kompetisi, $kategori_tanding]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Bracket updated successfully.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>
