<?php
/**
 * Dynamic sitemap.xml generator
 * Follows the sitemaps.org protocol.
 * Pulls active tools and all categories live from the MySQL database.
 * Served at: https://hdropshipping.com/sitemap.xml (via .htaccess rewrite)
 */

// Disable error display to avoid breaking XML
ini_set('display_errors', '0');
error_reporting(0);

require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex, follow', true);

$canonicalDomain = defined('SITE_URL') && SITE_URL ? rtrim(SITE_URL, '/') : 'https://hdropshipping.com';

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

/**
 * Output a single <url> entry
 */
function outputSitemapUrl(string $baseUrl, string $path, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.5'): void
{
    $loc = htmlspecialchars($baseUrl . $path, ENT_XML1, 'UTF-8');
    echo "  <url>\n";
    echo "    <loc>{$loc}</loc>\n";
    if ($lastmod) {
        $timestamp = is_numeric($lastmod) ? (int)$lastmod : strtotime($lastmod);
        if ($timestamp && $timestamp > 0) {
            echo "    <lastmod>" . date('Y-m-d', $timestamp) . "</lastmod>\n";
        }
    }
    echo "    <changefreq>{$changefreq}</changefreq>\n";
    echo "    <priority>{$priority}</priority>\n";
    echo "  </url>\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// 1. Core Public Static Pages
// Excluded: cart, checkout, login, register, profile, admin, search, private/duplicate pages
// ─────────────────────────────────────────────────────────────────────────────
$staticPages = [
    ['path' => '/',                        'file' => '/index.php',                'changefreq' => 'daily',   'priority' => '1.0'],
    ['path' => '/roadmap.php',             'file' => '/roadmap.php',              'changefreq' => 'weekly',  'priority' => '0.8'],
    ['path' => '/stack-builder.php',        'file' => '/stack-builder.php',        'changefreq' => 'weekly',  'priority' => '0.8'],
    ['path' => '/compare.php',              'file' => '/compare.php',              'changefreq' => 'weekly',  'priority' => '0.8'],
    ['path' => '/calculator.php',           'file' => '/calculator.php',           'changefreq' => 'weekly',  'priority' => '0.7'],
    ['path' => '/about.php',               'file' => '/about.php',                'changefreq' => 'monthly', 'priority' => '0.5'],
    ['path' => '/submit.php',              'file' => '/submit.php',               'changefreq' => 'monthly', 'priority' => '0.5'],
    ['path' => '/privacy.php',             'file' => '/privacy.php',              'changefreq' => 'yearly',  'priority' => '0.3'],
    ['path' => '/affiliate-disclosure.php', 'file' => '/affiliate-disclosure.php', 'changefreq' => 'yearly',  'priority' => '0.3'],
];

foreach ($staticPages as $page) {
    $filePath = __DIR__ . $page['file'];
    $fileMod = file_exists($filePath) ? date('Y-m-d', filemtime($filePath)) : null;
    outputSitemapUrl($canonicalDomain, $page['path'], $fileMod, $page['changefreq'], $page['priority']);
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. Categories from Database (/category/{slug})
// ─────────────────────────────────────────────────────────────────────────────
try {
    if (isset($pdo)) {
        $catStmt = $pdo->query("SELECT slug, created_at FROM categories ORDER BY sort_order ASC, id ASC");
        if ($catStmt) {
            while ($row = $catStmt->fetch()) {
                if (!empty($row['slug'])) {
                    outputSitemapUrl(
                        $canonicalDomain,
                        '/category/' . $row['slug'],
                        $row['created_at'] ?? null,
                        'weekly',
                        '0.8'
                    );
                }
            }
        }
    }
} catch (Throwable $e) {
    error_log('Sitemap categories error: ' . $e->getMessage());
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Active Tools / Products from Database (/tool/{slug})
// ─────────────────────────────────────────────────────────────────────────────
try {
    if (isset($pdo)) {
        $toolStmt = $pdo->query("SELECT slug, created_at, is_featured FROM tools WHERE is_active = 1 ORDER BY is_featured DESC, id ASC");
        if ($toolStmt) {
            while ($row = $toolStmt->fetch()) {
                if (!empty($row['slug'])) {
                    $priority = !empty($row['is_featured']) ? '0.9' : '0.7';
                    outputSitemapUrl(
                        $canonicalDomain,
                        '/tool/' . $row['slug'],
                        $row['created_at'] ?? null,
                        'weekly',
                        $priority
                    );
                }
            }
        }
    }
} catch (Throwable $e) {
    error_log('Sitemap tools error: ' . $e->getMessage());
}

echo '</urlset>' . "\n";
