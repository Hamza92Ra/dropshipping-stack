<?php
// ============================================================
// price_alerts.php — AJAX endpoint to turn price alerts on/off
// Called from the checkbox in dashboard.php
// ============================================================

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/config.php'; // must provide $conn (mysqli)

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$tool_id = isset($data['tool_id']) ? (int) $data['tool_id'] : 0;
$enable = !empty($data['enable']);

if ($tool_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid tool.']);
    exit;
}

if ($enable) {
    // Insert or reactivate
    $stmt = $conn->prepare("
        INSERT INTO price_alerts (user_id, tool_id, is_active)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE is_active = 1
    ");
    $stmt->bind_param("ii", $user_id, $tool_id);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("UPDATE price_alerts SET is_active = 0 WHERE user_id = ? AND tool_id = ?");
    $stmt->bind_param("ii", $user_id, $tool_id);
    $stmt->execute();
}

echo json_encode(['success' => true, 'enabled' => $enable]);