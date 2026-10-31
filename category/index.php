<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');

$cat = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
$cat->execute([$slug]);
$cat = $cat->fetch();

if (!$cat) {
    http_response_code(404);
    include __DIR__ . '/../404.php';
    exit;
}

$tools = cache_remember('category_tools_' . $slug, 300, function() use ($pdo, $cat) {
    $stmt = $pdo->prepare("
        SELECT t.*, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
        FROM tools t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.category_id = ? AND t.is_active = 1
        ORDER BY t.is_featured DESC, t.rating DESC
    ");
    $stmt->execute([$cat['id']]);
    return $stmt->fetchAll();
});

$user_upvoted_tool_ids = [];
if (isset($_SESSION['user_id'])) {
    $uvStmt = $pdo->prepare("SELECT tool_id FROM upvotes WHERE user_id = ?");
    $uvStmt->execute([(int)$_SESSION['user_id']]);
    $user_upvoted_tool_ids = array_fill_keys(array_map('intval', $uvStmt->fetchAll(PDO::FETCH_COLUMN)), true);
}

// Release session lock early
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

$canonical_url   = 'https://hdropshipping.com/category/' . $cat['slug'];
$page_title      = 'Best ' . $cat['name'] . ' Tools & Software (' . date('Y') . ')';
$page_heading    = $cat['name'] . ' Tools';
$page_subheading = $cat['description'];
$page_desc       = 'Explore the top ' . $cat['name'] . ' dropshipping tools and software platforms. ' . ($cat['description'] ?? '');
$og_title        = $page_title . ' — ' . SITE_NAME;
$og_description  = $page_desc;
$active_cat      = $cat['slug'];

$breadcrumb_items = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => $cat['name'], 'url' => '/category/' . $cat['slug']]
];

$structured_data = [
    '@type' => 'CollectionPage',
    'name' => $page_title,
    'description' => $page_desc,
    'url' => $canonical_url
];

include __DIR__ . '/../header.php';
?>

<!-- Breadcrumbs -->
<nav class="breadcrumb-nav" aria-label="Breadcrumb" style="margin-bottom:16px;display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
    <a href="/index.php" style="color:var(--muted)">Home</a>
    <?= icon('chevron-right', '', 12) ?>
    <span style="color:var(--text);font-weight:600"><?= e($cat['name']) ?></span>
</nav>

<!-- Category Page H1 Heading -->
<div class="category-header-banner" style="margin-bottom:20px">
    <h1 style="font-size:26px;font-weight:800;letter-spacing:-0.02em;color:var(--text);margin-bottom:6px">
        <span><?= e($cat['icon'] ?? '📁') ?></span>
        <span>Best <?= e($cat['name']) ?> Dropshipping Tools</span>
    </h1>
    <p style="color:var(--muted);font-size:14px;max-width:680px;line-height:1.55;margin:0">
        <?= e($cat['description']) ?>
    </p>
</div>

<!-- Category Filter Pills -->
<div class="category-pills-bar" role="navigation" aria-label="Categories">
    <a href="/index.php" class="cat-pill">
        <?= icon('layout-grid', '', 14) ?>
        <span>All Tools</span>
    </a>
    <?php foreach ($cats as $c): ?>
        <a href="/category/<?= e($c['slug']) ?>" class="cat-pill <?= $c['slug'] === $active_cat ? 'active-pill' : '' ?>">
            <span><?= e($c['name']) ?></span>
        </a>
    <?php endforeach; ?>
</div>

<div class="dashboard-section-head" style="margin-top:20px">
    <h2 class="section-title">
        <?= icon('package', '', 20) ?>
        <span><?= count($tools) ?> Tools in <?= e($cat['name']) ?></span>
    </h2>

    <div class="section-filters-pill" role="tablist">
        <button type="button" class="filter-btn active" onclick="filterTools('all', this)">All</button>
        <button type="button" class="filter-btn" onclick="filterTools('free', this)">Free / Freemium</button>
        <button type="button" class="filter-btn" onclick="filterTools('paid', this)">Paid</button>
        <button type="button" class="filter-btn" onclick="filterTools('featured', this)">Featured</button>
    </div>
</div>

<?php if (!empty($tools)): ?>
    <div class="tools-grid" id="categoryToolsGrid">
        <?php foreach ($tools as $t): ?>
            <?php include __DIR__ . '/../partials/tool-card.php'; ?>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-2xl);padding:60px 24px;text-align:center;box-shadow:var(--shadow-sm)">
        <div style="width:52px;height:52px;border-radius:50%;background:var(--accent-light);color:var(--accent);display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px">
            <?= icon('package', '', 24) ?>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:var(--text);margin-bottom:6px">No software listed in this category yet</h3>
        <p style="font-size:14px;color:var(--muted);margin-bottom:20px">Suggest an essential tool or service to feature in this collection.</p>
        <a href="/submit.php" class="btn-primary" style="display:inline-flex;padding:10px 20px">Submit a Software Tool</a>
    </div>
<?php endif; ?>

<script>
function filterTools(type, btn) {
    if (btn) {
        btn.parentElement.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
    }

    const cards = document.querySelectorAll('#categoryToolsGrid .tool-card');
    cards.forEach(card => {
        const price = card.getAttribute('data-price');
        const featured = card.getAttribute('data-featured');

        let show = false;
        if (type === 'all') show = true;
        else if (type === 'free') show = (price === 'free' || price === 'freemium');
        else if (type === 'paid') show = (price === 'paid');
        else if (type === 'featured') show = (featured === '1');

        card.hidden = !show;
        card.classList.toggle('is-filtered-out', !show);
        card.style.display = show ? 'flex' : 'none';
    });
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>
