<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$like = '%' . $q . '%';
$prefix = $q . '%';

$stmt = $pdo->prepare("
    SELECT t.id, t.name, t.slug, t.tagline, t.price_type, t.price_from, t.rating, t.website_url,
           c.name AS category, c.icon AS category_icon
    FROM tools t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.is_active = 1 AND (t.name LIKE ? OR t.tagline LIKE ? OR c.name LIKE ?)
    ORDER BY (t.name LIKE ?) DESC, (t.name LIKE ?) DESC, t.is_featured DESC, t.clicks DESC, t.rating DESC
    LIMIT 6
");
$stmt->execute([$like, $like, $like, $prefix, $like]);
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($tools as &$tool) {
    $domain = '';
    if (!empty($tool['website_url'])) {
        $host = parse_url($tool['website_url'], PHP_URL_HOST);
        $domain = preg_replace('/^www\./i', '', $host ?: '');
    }
    $tool['domain'] = $domain;
    $tool['rating_formatted'] = number_format((float)$tool['rating'], 1);
}
unset($tool);

echo json_encode($tools);
