<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$like = '%' . $q . '%';
$stmt = $pdo->prepare("
    SELECT t.name, t.slug, t.tagline, c.name AS category
    FROM tools t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.is_active = 1 AND (t.name LIKE ? OR t.tagline LIKE ?)
    ORDER BY t.is_featured DESC, t.rating DESC
    LIMIT 6
");
$stmt->execute([$like, $like]);
$results = $stmt->fetchAll();

echo json_encode($results);
