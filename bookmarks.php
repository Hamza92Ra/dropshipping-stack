<?php
// ============================================================
// bookmarks.php — AJAX endpoint for saving/removing tool bookmarks
// Called via fetch() from tool cards, e.g.:
//   fetch('bookmarks.php', { method:'POST', headers:{'Content-Type':'application/json'},
//     body: JSON.stringify({ action: 'toggle', tool_id: 5 }) })
// ============================================================

session_start();
header('Content-Type: application/json');

// Adjust this include to match your project's DB connection file
require_once __DIR__ . '/config.php'; // must provide $conn (mysqli)

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to save tools.']);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$tool_id = isset($data['tool_id']) ? (int) $data['tool_id'] : 0;
$action = $data['action'] ?? 'toggle';

if ($tool_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid tool.']);
    exit;
}

if ($action === 'list') {
    // Return all tool_ids bookmarked by this user
    $stmt = $conn->prepare("SELECT tool_id FROM bookmarks WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = array_map(fn($row) => (int) $row['tool_id'], $result->fetch_all(MYSQLI_ASSOC));
    echo json_encode(['success' => true, 'bookmarked' => $ids]);
    exit;
}

// Toggle: check if it exists, remove it; otherwise add it
$stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND tool_id = ?");
$stmt->bind_param("ii", $user_id, $tool_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE id = ?");
    $stmt->bind_param("i", $existing['id']);
    $stmt->execute();
    echo json_encode(['success' => true, 'bookmarked' => false]);
} else {
    $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, tool_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $tool_id);
    $stmt->execute();
    echo json_encode(['success' => true, 'bookmarked' => true]);
}