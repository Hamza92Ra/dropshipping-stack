<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$icons = [
    'store-builders'  => '🏪',
    'product-research'=> '🔍',
    'suppliers'       => '📦',
    'marketing'       => '📣',
    'analytics'       => '📊',
    'automation'      => '⚙️',
];

foreach ($icons as $slug => $icon) {
    $stmt = $pdo->prepare("UPDATE categories SET icon = ? WHERE slug = ?");
    $stmt->execute([$icon, $slug]);
    echo "✅ Updated: $slug → $icon <br>";
}

echo "<br>Done! <a href='/dropshipping/index.php'>Go back home</a>";
?>