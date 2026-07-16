<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'login_required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$body    = json_decode(file_get_contents('php://input'), true);

if (!$body) {
    echo json_encode(['error' => 'invalid_data']);
    exit;
}

$levels = ['beginner', 'intermediate', 'advanced'];

// Build arrays of steps and totals
$beginner_steps     = json_encode($body['beginner']     ?? []);
$beginner_total     = count($body['beginner']           ?? []);
$intermediate_steps = json_encode($body['intermediate'] ?? []);
$intermediate_total = count($body['intermediate']       ?? []);
$advanced_steps     = json_encode($body['advanced']     ?? []);
$advanced_total     = count($body['advanced']           ?? []);

// Check if row exists for this user
$existing = $pdo->prepare("SELECT id FROM user_roadmap WHERE user_id = ?");
$existing->execute([$user_id]);

if ($existing->fetch()) {
    // UPDATE existing row
    $pdo->prepare("
        UPDATE user_roadmap SET
            beginner_steps     = ?,
            beginner_total     = ?,
            intermediate_steps = ?,
            intermediate_total = ?,
            advanced_steps     = ?,
            advanced_total     = ?,
            updated_at         = NOW()
        WHERE user_id = ?
    ")->execute([
        $beginner_steps,
        $beginner_total,
        $intermediate_steps,
        $intermediate_total,
        $advanced_steps,
        $advanced_total,
        $user_id,
    ]);
} else {
    // INSERT new row
    $pdo->prepare("
        INSERT INTO user_roadmap
            (user_id, beginner_steps, beginner_total, intermediate_steps, intermediate_total, advanced_steps, advanced_total, updated_at)
        VALUES
            (?, ?, ?, ?, ?, ?, ?, NOW())
    ")->execute([
        $user_id,
        $beginner_steps,
        $beginner_total,
        $intermediate_steps,
        $intermediate_total,
        $advanced_steps,
        $advanced_total,
    ]);
}
echo json_encode([
    'success'=>true,
    'session_user'=>$_SESSION['user_id'] ?? null,
    'received'=>$body
]);