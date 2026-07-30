<?php
/**
 * Dynamic sitemap.xml generator
 * Pulls active tools and all categories live from the database.
 * Served at: https://www.hdropshipping.com/sitemap.xml (via .htaccess rewrite)
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = 'https://www.hdropshipping.com';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

/**
 * Helper to output a single <url> block
 */
function outputUrl($baseUrl, $path, $lastmod, $changefreq, $priority)
{
    $loc = htmlspecialchars($baseUrl . $path, ENT_XML1, 'UTF-8');
    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    if ($lastmod) {
        echo "    <lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>\n";
    }
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

// ---------------------------------------------------------
// 1. Static pages
// ---------------------------------------------------------
$staticPages = [
    ['path' => '/',                       'changefreq' => 'daily',  'priority' => '1.0'],
    ['path' => '/about.php',              'changefreq' => 'monthly','priority' => '0.5'],
    ['path' => '/privacy.php',            'changefreq' => 'yearly', 'priority' => '0.3'],
    ['path' => '/affiliate-disclosure.php','changefreq' => 'yearly','priority' => '0.3'],
    ['path' => '/submit.php',             'changefreq' => 'monthly','priority' => '0.5'],
    ['path' => '/search.php',             'changefreq' => 'monthly','priority' => '0.3'],
];

foreach ($staticPages as $page) {
    outputUrl($baseUrl, $page['path'], null, $page['changefreq'], $page['priority']);
}

// ---------------------------------------------------------
// 2. Category pages: /category/{slug}
// ---------------------------------------------------------
$catResult = $conn->query("SELECT slug, created_at FROM categories ORDER BY sort_order ASC");

if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        outputUrl(
            $baseUrl,
            '/category/' . $row['slug'],
            $row['created_at'],
            'weekly',
            '0.8'
        );
    }
}

// ---------------------------------------------------------
// 3. Tool pages: /tool/{slug}  (only active tools)
// ---------------------------------------------------------
$toolResult = $conn->query("SELECT slug, created_at, is_featured FROM tools WHERE is_active = 1 ORDER BY id ASC");

if ($toolResult) {
    while ($row = $toolResult->fetch_assoc()) {
        $priority = $row['is_featured'] ? '0.9' : '0.7';
        outputUrl(
            $baseUrl,
            '/tool/' . $row['slug'],
            $row['created_at'],
            'weekly',
            $priority
        );
    }
}

echo '</urlset>';