<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$q = trim($_GET['q'] ?? '');
$page_title = $q ? 'Search: ' . $q : 'Search Tools';
$results    = [];

if (strlen($q) >= 2) {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("
        SELECT t.*, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
        FROM tools t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.is_active = 1
          AND (t.name LIKE ? OR t.tagline LIKE ? OR t.description LIKE ?)
        ORDER BY t.is_featured DESC, t.rating DESC
        LIMIT 30
    ");
    $stmt->execute([$like, $like, $like]);
    $results = $stmt->fetchAll();
}

include __DIR__ . '/header.php';
?>

<div class="page-wrap" style="padding-top:32px">
    <?php if ($q): ?>
        <h1 style="font-size:22px;font-weight:800;color:var(--ink);margin-bottom:6px">
            Search results for "<?= e($q) ?>"
        </h1>
        <p class="text-muted" style="margin-bottom:24px"><?= count($results) ?> tool<?= count($results) !== 1 ? 's' : '' ?> found</p>

        <?php if ($results): ?>
        <div class="tools-grid">
            <?php foreach ($results as $t): ?>
                <?php include __DIR__ . '/partials/tool-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>No tools found for "<?= e($q) ?>". Try a different keyword.</p>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>Enter a search term above to find tools.</p>
        </div>
    <?php endif; ?>
    <div style="height:40px"></div>
</div>
<script src="assets/js/darkmode.js"></script>
<?php include __DIR__ . '/footer.php'; ?>
