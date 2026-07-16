<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'error' => 'login_required'
    ]));
}

$data = json_decode(
    file_get_contents('php://input'),
    true
);

$stmt = $pdo->prepare(
    "
INSERT INTO user_stack
(user_id, tools_json)
VALUES(?,?)
ON DUPLICATE KEY UPDATE
tools_json=?,
updated_at=NOW()
"
);

$json = json_encode($data);

$stmt->execute([
    $_SESSION['user_id'],
    $json,
    $json
]);

echo json_encode([
    'success' => true
]);
