<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$competition_id = $_GET['competition_id'] ?? null;
$type = $_GET['type'] ?? null;

if (!$competition_id || !$type) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit();
}

try {
    // Verify user has athletes in this competition
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM registrations r 
        JOIN athletes a ON r.athlete_id = a.id 
        WHERE r.competition_id = ? AND a.user_id = ?
    ");
    $stmt->execute([$competition_id, $_SESSION['user_id']]);
    $access_check = $stmt->fetch();
    
    if ($access_check['count'] == 0) {
        echo json_encode(['success' => false, 'message' => 'No access to this competition']);
        exit();
    }

    switch ($type) {
        case 'bagan':
            $data = getBaganData($pdo, $competition_id);
            break;
        case 'jadwal':
            $data = getJadwalData($pdo, $competition_id);
            break;
        case 'penilaian':
            $athlete_id = $_GET['athlete_id'] ?? null;
            $data = getPenilaianData($pdo, $competition_id, $athlete_id);
            break;
        case 'medali':
            $data = getMedaliData($pdo, $competition_id);
            break;
        case 'rekapitulasi':
            $data = getRekapitulasiData($pdo, $competition_id);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid type']);
            exit();
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function getBaganData($pdo, $competition_id) {
    $stmt = $pdo->prepare("
        SELECT 
            cc.nama_kategori as category_name,
            m.id as match_id,
            m.round,
            m.match_number,
            a1.nama as athlete1_name,
            a2.nama as athlete2_name,
            k1.nama_kontingen as kontingen1_name,
            k2.nama_kontingen as kontingen2_name,
            m.winner_id,
            m.status,
            m.match_date,
            m.match_time
        FROM matches m
        LEFT JOIN competition_categories cc ON m.category_id = cc.id
        LEFT JOIN registrations r1 ON m.athlete1_id = r1.id
        LEFT JOIN registrations r2 ON m.athlete2_id = r2.id
        LEFT JOIN athletes a1 ON r1.athlete_id = a1.id
        LEFT JOIN athletes a2 ON r2.athlete_id = a2.id
        LEFT JOIN kontingen k1 ON r1.kontingen_id = k1.id
        LEFT JOIN kontingen k2 ON r2.kontingen_id = k2.id
        WHERE m.competition_id = ?
        ORDER BY cc.nama_kategori, m.round, m.match_number
    ");
    $stmt->execute([$competition_id]);
    $matches = $stmt->fetchAll();

    // Group by category
    $grouped = [];
    foreach ($matches as $match) {
        $category = $match['category_name'];
        if (!isset($grouped[$category])) {
            $grouped[$category] = [
                'category_name' => $category,
                'matches' => []
            ];
        }
        $grouped[$category]['matches'][] = $match;
    }

    return array_values($grouped);
}

function getJadwalData($pdo, $competition_id) {
    $filter = $_GET['filter'] ?? '';
    $where_clause = '';
    $params = [$competition_id];

    if ($filter === 'today') {
        $where_clause = ' AND DATE(m.match_date) = CURDATE()';
    } elseif ($filter === 'tomorrow') {
        $where_clause = ' AND DATE(m.match_date) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)';
    }

    $stmt = $pdo->prepare("
        SELECT 
            m.match_date,
            m.match_time,
            cc.nama_kategori as category_name,
            a1.nama as athlete1_name,
            a2.nama as athlete2_name,
            k1.nama_kontingen as kontingen1_name,
            k2.nama_kontingen as kontingen2_name,
            m.venue,
            m.status
        FROM matches m
        LEFT JOIN competition_categories cc ON m.category_id = cc.id
        LEFT JOIN registrations r1 ON m.athlete1_id = r1.id
        LEFT JOIN registrations r2 ON m.athlete2_id = r2.id
        LEFT JOIN athletes a1 ON r1.athlete_id = a1.id
        LEFT JOIN athletes a2 ON r2.athlete_id = a2.id
        LEFT JOIN kontingen k1 ON r1.kontingen_id = k1.id
        LEFT JOIN kontingen k2 ON r2.kontingen_id = k2.id
        WHERE m.competition_id = ? $where_clause
        ORDER BY m.match_date, m.match_time
    ");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getPenilaianData($pdo, $competition_id, $athlete_id = null) {
    $where_clause = '';
    $params = [$competition_id];

    if ($athlete_id) {
        $where_clause = ' AND r.id = ?';
        $params[] = $athlete_id;
    }

    $stmt = $pdo->prepare("
        SELECT 
            a.nama as athlete_name,
            cc.nama_kategori as category_name,
            s.teknik_score,
            s.gerakan_score,
            s.rasa_score,
            (s.teknik_score + s.gerakan_score + s.rasa_score) as total_score,
            u.nama as judge_name,
            s.created_at
        FROM scores s
        JOIN registrations r ON s.registration_id = r.id
        JOIN athletes a ON r.athlete_id = a.id
        JOIN competition_categories cc ON r.category_id = cc.id
        JOIN users u ON s.judge_id = u.id
        WHERE r.competition_id = ? $where_clause
        ORDER BY total_score DESC, s.created_at DESC
    ");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getMedaliData($pdo, $competition_id) {
    $stmt = $pdo->prepare("
        SELECT 
            cc.nama_kategori as category_name,
            a.nama as athlete_name,
            k.nama_kontingen as kontingen_name,
            med.medal_type,
            med.created_at
        FROM medals med
        JOIN registrations r ON med.registration_id = r.id
        JOIN athletes a ON r.athlete_id = a.id
        JOIN kontingen k ON r.kontingen_id = k.id
        JOIN competition_categories cc ON r.category_id = cc.id
        WHERE r.competition_id = ?
        ORDER BY cc.nama_kategori, 
                 CASE med.medal_type 
                     WHEN 'gold' THEN 1 
                     WHEN 'silver' THEN 2 
                     WHEN 'bronze' THEN 3 
                 END
    ");
    $stmt->execute([$competition_id]);
    return $stmt->fetchAll();
}

function getRekapitulasiData($pdo, $competition_id) {
    // Get summary statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN med.medal_type = 'gold' THEN 1 END) as total_gold,
            COUNT(CASE WHEN med.medal_type = 'silver' THEN 1 END) as total_silver,
            COUNT(CASE WHEN med.medal_type = 'bronze' THEN 1 END) as total_bronze,
            COUNT(*) as total_medals
        FROM medals med
        JOIN registrations r ON med.registration_id = r.id
        WHERE r.competition_id = ?
    ");
    $stmt->execute([$competition_id]);
    $summary = $stmt->fetch();

    // Get kontingen ranking
    $stmt = $pdo->prepare("
        SELECT 
            k.nama_kontingen as kontingen_name,
            COUNT(CASE WHEN med.medal_type = 'gold' THEN 1 END) as gold_count,
            COUNT(CASE WHEN med.medal_type = 'silver' THEN 1 END) as silver_count,
            COUNT(CASE WHEN med.medal_type = 'bronze' THEN 1 END) as bronze_count,
            COUNT(*) as total_medals,
            (COUNT(CASE WHEN med.medal_type = 'gold' THEN 1 END) * 3 + 
             COUNT(CASE WHEN med.medal_type = 'silver' THEN 1 END) * 2 + 
             COUNT(CASE WHEN med.medal_type = 'bronze' THEN 1 END) * 1) as points,
            CASE WHEN a.user_id = ? THEN 1 ELSE 0 END as is_user_kontingen
        FROM kontingen k
        JOIN registrations r ON k.id = r.kontingen_id
        JOIN athletes a ON r.athlete_id = a.id
        LEFT JOIN medals med ON r.id = med.registration_id
        WHERE r.competition_id = ?
        GROUP BY k.id, k.nama_kontingen, a.user_id
        HAVING total_medals > 0
        ORDER BY points DESC, gold_count DESC, silver_count DESC, bronze_count DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $competition_id]);
    $kontingen_ranking = $stmt->fetchAll();

    return [
        'summary' => $summary,
        'kontingen_ranking' => $kontingen_ranking
    ];
}
?>
