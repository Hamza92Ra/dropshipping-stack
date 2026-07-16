<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');

$tool = $pdo->prepare("SELECT id, name, affiliate_link, website_url FROM tools WHERE slug = ? AND is_active = 1");
$tool->execute([$slug]);
$tool = $tool->fetch();

if (!$tool || (!$tool['affiliate_link'] && !$tool['website_url'])) {
    redirect('/');
}

// Log the click (hash IP for privacy)
$ip_hash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
$pdo->prepare("INSERT INTO clicks (tool_id, ip_hash) VALUES (?,?)")->execute([$tool['id'], $ip_hash]);

// Increment counter
$pdo->prepare("UPDATE tools SET clicks = clicks + 1 WHERE id = ?")->execute([$tool['id']]);

// Redirect to affiliate link (prefer affiliate_link over website_url)
$url = $tool['affiliate_link'] ?: $tool['website_url'];
header("Location: $url");
exit;
