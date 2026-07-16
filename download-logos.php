<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$tools = $pdo->query("SELECT id, name, website_url FROM tools WHERE is_active=1")->fetchAll();

$logo_dir = __DIR__ . '/assets/img/logos/';
if (!is_dir($logo_dir)) mkdir($logo_dir, 0755, true);

foreach ($tools as $t) {
    $domain = parse_url($t['website_url'], PHP_URL_HOST);
    $filename = preg_replace('/[^a-z0-9-]/', '', strtolower($domain)) . '.png';
    $filepath = $logo_dir . $filename;
    $local_url = '/dropshipping/assets/img/logos/' . $filename;

    // Skip if already downloaded
    if (file_exists($filepath)) {
        echo "⏭️ Skipped: {$t['name']}<br>";
        continue;
    }

    // Try Clearbit first
    $logo = @file_get_contents("https://logo.clearbit.com/{$domain}");

    // Fallback to Google favicon
    if (!$logo) {
        $logo = @file_get_contents("https://www.google.com/s2/favicons?domain={$domain}&sz=128");
    }

    if ($logo) {
        file_put_contents($filepath, $logo);
        $pdo->prepare("UPDATE tools SET logo_url = ? WHERE id = ?")->execute([$local_url, $t['id']]);
        echo "✅ Downloaded: {$t['name']}<br>";
    } else {
        echo "❌ Failed: {$t['name']}<br>";
    }

    // Small delay to avoid rate limiting
    usleep(300000); // 0.3s
}

echo "<br>✅ Done!";
?>