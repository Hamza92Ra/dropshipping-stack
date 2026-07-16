<?php
define('APP', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'login_required']);
    exit;
}

$tool_id = (int)($_POST['tool_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$tool_id) {
    echo json_encode(['error' => 'invalid']);
    exit;
}

// Check if already voted
$existing = $pdo->prepare(
    "SELECT id
     FROM upvotes
     WHERE user_id=? AND tool_id=?"
);

$existing->execute([
    $user_id,
    $tool_id
]);

if($existing->fetch()){

    $pdo->prepare(
        "DELETE FROM upvotes
         WHERE user_id=? AND tool_id=?"
    )->execute([
        $user_id,
        $tool_id
    ]);

    $pdo->prepare(
        "UPDATE tools
         SET upvotes=upvotes-1
         WHERE id=?"
    )->execute([
        $tool_id
    ]);

    $action='removed';

}else{

    $pdo->prepare(
        "INSERT INTO upvotes(user_id,tool_id)
         VALUES(?,?)"
    )->execute([
        $user_id,
        $tool_id
    ]);

    $pdo->prepare(
        "UPDATE tools
         SET upvotes=upvotes+1
         WHERE id=?"
    )->execute([
        $tool_id
    ]);

    $action='added';
}

$count = $pdo->prepare(
    "SELECT upvotes
     FROM tools
     WHERE id=?"
);

$count->execute([$tool_id]);

$count = $count->fetchColumn();

echo json_encode([
    'action'=>$action,
    'count'=>$count
]);
exit;