<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');

$cat = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$cat->execute([$slug]);
$cat = $cat->fetch();

if (!$cat) {
    http_response_code(404);
    die('Category not found.');
}

$tools = $pdo->prepare("
    SELECT t.*, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
    FROM tools t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.category_id = ? AND t.is_active = 1
    ORDER BY t.is_featured DESC, t.rating DESC
");
$tools->execute([$cat['id']]);
$tools = $tools->fetchAll();

$page_title = $cat['name'] . ' Tools — Best ' . $cat['name'] . ' Software';
$page_desc  = $cat['description'];
$active_cat = $cat['slug'];
$show_browse_pills = true; 

include __DIR__ . '/../header.php';
?>

<div class="cat-hero">
    <div class="cat-icon"><?= e($cat['icon']) ?></div>
    <h1 class="cat-title"><?= e($cat['name']) ?></h1>
    <p class="cat-desc"><?= e($cat['description']) ?></p>
</div>

<div class="page-wrap">
    <div class="section-head">
        <h2 class="section-title"><?= count($tools) ?> Tools in <?= e($cat['name']) ?></h2>
    </div>

    <div class="filters-bar">
        <span class="filter-label">Filter:</span>
        <button class="filter-btn active" onclick="filterTools('all')">All</button>
        <button class="filter-btn" onclick="filterTools('free')">Free / Freemium</button>
        <button class="filter-btn" onclick="filterTools('paid')">Paid</button>
        <button class="filter-btn" onclick="filterTools('featured')">Featured</button>
    </div>

    <?php if ($tools): ?>
    <div class="tools-grid">
        <?php foreach ($tools as $t): ?>
            <?php include __DIR__ . '/../partials/tool-card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="icon"><?= e($cat['icon']) ?></div>
        <p>No tools in this category yet. <a href="/submit.php" style="color:var(--accent)">Submit one!</a></p>
    </div>
    <?php endif; ?>

    <div style="height:40px"></div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>