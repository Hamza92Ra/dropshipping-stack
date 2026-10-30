<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Release session lock immediately on read-only API endpoint (Task 4)
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$q        = trim($_GET['q'] ?? '');
$filter   = trim($_GET['filter'] ?? '');
$category = trim($_GET['category'] ?? '');

if (mb_strlen($q) < 2 && empty($filter) && empty($category)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([]);
    exit;
}

$params = [];
$whereClauses = ["t.is_active = 1"];

if (mb_strlen($q) >= 2) {
    $like = '%' . $q . '%';
    $prefix = $q . '%';
    $whereClauses[] = "(t.name LIKE ? OR t.tagline LIKE ? OR c.name LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if (!empty($category) && $category !== 'all') {
    $whereClauses[] = "(c.slug = ? OR t.category_id = ?)";
    $params[] = $category;
    $params[] = is_numeric($category) ? (int)$category : 0;
}

if (!empty($filter) && $filter !== 'all') {
    if ($filter === 'free') {
        $whereClauses[] = "(t.price_type = 'free' OR t.price_type = 'freemium')";
    } elseif ($filter === 'paid') {
        $whereClauses[] = "(t.price_type = 'paid')";
    } elseif ($filter === 'featured') {
        $whereClauses[] = "(t.is_featured = 1)";
    }
}

$whereSql = implode(' AND ', $whereClauses);
$orderSql = mb_strlen($q) >= 2
    ? "ORDER BY (t.name LIKE ?) DESC, (t.name LIKE ?) DESC, t.is_featured DESC, t.clicks DESC, t.rating DESC LIMIT 12"
    : "ORDER BY t.is_featured DESC, t.clicks DESC, t.rating DESC";

if (mb_strlen($q) >= 2) {
    $params[] = $prefix;
    $params[] = $like;
}

$stmt = $pdo->prepare("
    SELECT t.id, t.name, t.slug, t.tagline, t.price_type, t.price_from, t.rating, t.website_url, t.logo_url, t.is_featured,
           c.name AS category, c.slug AS cat_slug, c.icon AS category_icon
    FROM tools t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE {$whereSql}
    {$orderSql}
");
$stmt->execute($params);
$tools = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($tools as &$tool) {
    $domain = '';
    if (!empty($tool['website_url'])) {
        $host = parse_url($tool['website_url'], PHP_URL_HOST);
        $domain = preg_replace('/^www\./i', '', $host ?: '');
    }
    $tool['domain'] = $domain;
    $tool['logo_url'] = tool_logo_url($tool);
    $tool['logo_srcset'] = tool_logo_srcset($tool);
    $tool['rating_formatted'] = number_format((float)$tool['rating'], 1);
}
unset($tool);

$json = json_encode($tools);
$etag = '"' . md5($json) . '"';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');
header('ETag: ' . $etag);

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
    http_response_code(304);
    exit;
}

echo $json;

