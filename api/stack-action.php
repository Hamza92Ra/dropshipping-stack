<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'login_required']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?? $_POST;

$action = $data['action'] ?? '';
$tool_id = (int)($data['tool_id'] ?? 0);

if ($tool_id <= 0 || !in_array($action, ['add', 'remove'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_parameters']);
    exit;
}

// ── 1. Fetch current user_stack record ──────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM user_stack WHERE user_id = ?");
$stmt->execute([$user_id]);
$stackRow = $stmt->fetch();

$currentToolIds = [];

// Quiz mapping fallback for legacy parameters
$quizMap = [
    'beginner' => [
        'free' => ['woocommerce', 'dsers', 'canva-pro'],
        'low' => ['shopify', 'dsers', 'minea', 'tidio'],
        'mid' => ['shopify', 'zendrop', 'minea', 'klaviyo'],
        'high' => ['shopify', 'spocket', 'adspy', 'triple-whale']
    ],
    'intermediate' => [
        'free' => ['woocommerce', 'spocket'],
        'low' => ['shopify', 'autods'],
        'mid' => ['shopify', 'zendrop', 'klaviyo'],
        'high' => ['shopify', 'triple-whale', 'adspy']
    ],
    'advanced' => [
        'free' => ['shopify'],
        'low' => ['shopify', 'klaviyo'],
        'mid' => ['shopify', 'triple-whale', 'adspy', 'klaviyo'],
        'high' => ['shopify', 'triple-whale', 'adspy', 'gorgias']
    ]
];

if ($stackRow && !empty($stackRow['tools_json'])) {
    $parsed = json_decode($stackRow['tools_json'], true);
    if (is_array($parsed)) {
        if (isset($parsed['tools']) && is_array($parsed['tools'])) {
            $currentToolIds = array_map('intval', $parsed['tools']);
        } elseif (isset($parsed[0]) && is_numeric($parsed[0])) {
            $currentToolIds = array_map('intval', $parsed);
        } elseif (isset($parsed['experience']) && isset($parsed['budget'])) {
            $exp = $parsed['experience'] ?? 'beginner';
            $bud = $parsed['budget'] ?? 'low';
            $slugs = $quizMap[$exp][$bud] ?? ['shopify', 'dsers'];
            if (!empty($slugs)) {
                $inQuery = implode(',', array_fill(0, count($slugs), '?'));
                $sStmt = $pdo->prepare("SELECT id FROM tools WHERE slug IN ($inQuery)");
                $sStmt->execute($slugs);
                $currentToolIds = $sStmt->fetchAll(PDO::FETCH_COLUMN);
                $currentToolIds = array_map('intval', $currentToolIds);
            }
        }
    }
}

// Also merge with bookmarks for this user
$bStmt = $pdo->prepare("SELECT tool_id FROM bookmarks WHERE user_id = ?");
$bStmt->execute([$user_id]);
$bookmarkIds = array_map('intval', $bStmt->fetchAll(PDO::FETCH_COLUMN));
$currentToolIds = array_values(array_unique(array_merge($currentToolIds, $bookmarkIds)));

// ── 2. Apply Action ────────────────────────────────────────────────────────
if ($action === 'remove') {
    $currentToolIds = array_values(array_diff($currentToolIds, [$tool_id]));
    // Remove from bookmarks
    $del = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND tool_id = ?");
    $del->execute([$user_id, $tool_id]);
} elseif ($action === 'add') {
    if (!in_array($tool_id, $currentToolIds, true)) {
        $currentToolIds[] = $tool_id;
    }
    // Add to bookmarks table too
    $ins = $pdo->prepare("INSERT IGNORE INTO bookmarks (user_id, tool_id) VALUES (?, ?)");
    $ins->execute([$user_id, $tool_id]);
}

// ── 3. Save updated user_stack record ──────────────────────────────────────
$jsonPayload = json_encode($currentToolIds);
$upStmt = $pdo->prepare("
    INSERT INTO user_stack (user_id, tools_json, updated_at)
    VALUES (?, ?, NOW())
    ON DUPLICATE KEY UPDATE tools_json = ?, updated_at = NOW()
");
$upStmt->execute([$user_id, $jsonPayload, $jsonPayload]);

// ── 4. Compute updated real stats ──────────────────────────────────────────
$count = count($currentToolIds);
$totalCost = 0.0;
$freeCount = 0;
$paidCount = 0;

if ($count > 0) {
    $inQuery = implode(',', array_fill(0, $count, '?'));
    $tStmt = $pdo->prepare("SELECT id, price_type, price_from FROM tools WHERE id IN ($inQuery) AND is_active = 1");
    $tStmt->execute($currentToolIds);
    $toolsData = $tStmt->fetchAll();

    foreach ($toolsData as $t) {
        if ($t['price_type'] === 'paid') {
            $paidCount++;
            $totalCost += (float)$t['price_from'];
        } else {
            $freeCount++;
        }
    }
}

echo json_encode([
    'success'    => true,
    'action'     => $action,
    'tool_id'    => $tool_id,
    'count'      => $count,
    'total_cost' => round($totalCost, 2),
    'free_count' => $freeCount,
    'paid_count' => $paidCount,
    'empty'      => ($count === 0)
]);
