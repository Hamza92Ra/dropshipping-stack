<?php
// ============================================================
// bookmarks.php — AJAX endpoint for saving/removing tool bookmarks
// ============================================================

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to save tools.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;
$tool_id = isset($data['tool_id']) ? (int) $data['tool_id'] : 0;
$action = $data['action'] ?? 'toggle';

if ($tool_id <= 0 && $action !== 'list') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid tool.']);
    exit;
}

if ($action === 'list') {
    $stmt = $pdo->prepare("SELECT tool_id FROM bookmarks WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    echo json_encode(['success' => true, 'bookmarked' => $ids]);
    exit;
}

// Toggle: check if it exists, remove it; otherwise add it
$stmt = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND tool_id = ?");
$stmt->execute([$user_id, $tool_id]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare("DELETE FROM bookmarks WHERE id = ?");
    $stmt->execute([$existing['id']]);
    echo json_encode(['success' => true, 'bookmarked' => false]);
} else {
    $stmt = $pdo->prepare("INSERT INTO bookmarks (user_id, tool_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $tool_id]);
    echo json_encode(['success' => true, 'bookmarked' => true]);
}
