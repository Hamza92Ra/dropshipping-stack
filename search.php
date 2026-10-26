<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

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

<div class="page-wrap" style="max-width:1200px;margin:0 auto;padding:12px 0 60px;">
    <?php if ($q): ?>
        <div style="margin-bottom:24px;">
            <h1 style="font-size:24px;font-weight:800;color:var(--text);letter-spacing:-0.02em;margin-bottom:4px;">
                Search results for &ldquo;<?= e($q) ?>&rdquo;
            </h1>
            <p style="color:var(--muted);font-size:14px;margin:0;">
                <?= count($results) ?> tool<?= count($results) !== 1 ? 's' : '' ?> found matching your query
            </p>
        </div>

        <?php if ($results): ?>
            <div class="tools-grid">
                <?php foreach ($results as $t): ?>
                    <?php include __DIR__ . '/partials/tool-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="profile-empty-state glass-card" style="padding:64px 24px;border-radius:var(--radius-2xl);">
                <div class="profile-empty-icon" style="width:52px;height:52px;margin:0 auto 16px;">
                    <?= icon('search', '', 48) ?>
                </div>
                <h3 style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px;">No tools found</h3>
                <p style="color:var(--muted);font-size:14px;max-width:380px;margin:0 auto 20px;">
                    No results matched &ldquo;<?= e($q) ?>&rdquo;. Try searching for broader terms like Shopify, ads, supplier, or product research.
                </p>
                <a href="/index.php" class="btn-action-link" style="display:inline-flex;">
                    Browse All Tools <?= icon('arrow-right', '', 14) ?>
                </a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="profile-empty-state glass-card" style="padding:64px 24px;border-radius:var(--radius-2xl);">
            <div class="profile-empty-icon" style="width:52px;height:52px;margin:0 auto 16px;">
                <?= icon('search', '', 48) ?>
            </div>
            <h3 style="font-size:17px;font-weight:700;color:var(--text);margin-bottom:6px;">Search the Directory</h3>
            <p style="color:var(--muted);font-size:14px;max-width:380px;margin:0 auto 20px;">
                Type a tool name, category, or keyword into the search bar above to explore dropshipping tools.
            </p>
            <a href="/index.php" class="btn-action-link" style="display:inline-flex;">
                Explore Categories <?= icon('arrow-right', '', 14) ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
