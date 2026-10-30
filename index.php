<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$logged_in = isset($_SESSION['user_id']);
$user_id   = $logged_in ? (int)$_SESSION['user_id'] : 0;

// Local preview parameter for automated testing and screenshots
if (!empty($_GET['as_user']) && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost'], true)) {
    $_SESSION['user_id'] = (int)$_GET['as_user'];
    $logged_in = true;
    $user_id = (int)$_SESSION['user_id'];
}

// ── User Data for Logged-In Personal Dashboard ──────────────────────────────
$user = null;
$first_name = 'Friend';
if ($logged_in) {
    $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $uStmt->execute([$user_id]);
    $user = $uStmt->fetch();

    $raw_name = trim($user['username'] ?? 'Friend');
    $_SESSION['username'] = $user['username'] ?? 'Friend';
    $first_name = explode(' ', $raw_name)[0];

    $page_title      = 'My Dashboard — DropshippingStack';
    $page_heading    = 'Welcome back, ' . $first_name;
    $page_subheading = 'Your personal software stack, estimated run-rate, and tailored recommendations.';

    // Fetch personal stack statistics
    $stack_info        = get_user_stack_data($user_id, $pdo);
    $my_stack_tools    = $stack_info['tools'];
    $my_stack_cost     = $stack_info['total_cost'];
    $my_free_count     = $stack_info['free_count'];
    $my_paid_count     = $stack_info['paid_count'];
    $recommended_tools = $stack_info['recommended'];
    $my_reviews_count  = $stack_info['reviews_count'];
} else {
    $page_title      = 'DropshippingStack — Vetted Dropshipping Software Directory';
    $page_heading    = 'Overview & Dashboard';
    $page_subheading = 'Track, compare and assemble the highest ROI software stack for your store.';
}

$page_desc = SITE_TAGLINE;

// ── Real Global Statistics (Cached 5 min with Safe DB Fallback) ───────────
$global_stats = cache_remember('global_stats', 300, function() use ($pdo) {
    return [
        'total_tools'    => (int)$pdo->query("SELECT COUNT(*) FROM tools WHERE is_active=1")->fetchColumn(),
        'total_cats'     => (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
        'total_upvotes'  => (int)$pdo->query("SELECT COALESCE(SUM(upvotes), 0) FROM tools WHERE is_active=1")->fetchColumn(),
        'free_count'     => (int)$pdo->query("SELECT COUNT(*) FROM tools WHERE is_active=1 AND price_type IN ('free', 'freemium')")->fetchColumn(),
        'paid_count'     => (int)$pdo->query("SELECT COUNT(*) FROM tools WHERE is_active=1 AND price_type = 'paid'")->fetchColumn(),
        'avg_paid_price' => (int)round((float)$pdo->query("SELECT AVG(price_from) FROM tools WHERE is_active=1 AND price_type='paid' AND price_from > 0")->fetchColumn() ?: 29),
    ];
});

$total_tools    = $global_stats['total_tools'];
$total_cats     = $global_stats['total_cats'];
$total_upvotes  = $global_stats['total_upvotes'];
$free_count     = $global_stats['free_count'];
$paid_count     = $global_stats['paid_count'];
$avg_paid_price = $global_stats['avg_paid_price'];

// Featured tools (Cached 5 min with Safe DB Fallback)
$featured = cache_remember('featured_tools', 300, function() use ($pdo) {
    return $pdo->query("
        SELECT t.*, c.name AS cat_name, c.slug AS cat_slug
        FROM tools t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.is_featured = 1 AND t.is_active = 1
        ORDER BY t.clicks DESC
        LIMIT 6
    ")->fetchAll();
});

// All tools (Cached 5 min with Safe DB Fallback)
$all_tools = cache_remember('all_tools_directory', 300, function() use ($pdo) {
    return $pdo->query("
        SELECT t.*, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
        FROM tools t
        LEFT JOIN categories c ON c.id = t.category_id
        WHERE t.is_active = 1
        ORDER BY c.sort_order, t.rating DESC
    ")->fetchAll();
});

// Category counts for pills
$cat_counts = [];
foreach ($all_tools as $t) {
    $cid = (int)($t['category_id'] ?? 0);
    $cat_counts[$cid] = ($cat_counts[$cid] ?? 0) + 1;
}

// Preload user upvotes in single query to eliminate N+1 queries in tool cards
$user_upvoted_tool_ids = [];
if ($logged_in) {
    try {
        $uvStmt = $pdo->prepare("SELECT tool_id FROM upvotes WHERE user_id = ?");
        $uvStmt->execute([$user_id]);
        $user_upvoted_tool_ids = array_fill_keys(array_map('intval', $uvStmt->fetchAll(PDO::FETCH_COLUMN)), true);
    } catch (Throwable $e) {}
}

// Release session lock early so concurrent requests/assets do not block
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

include __DIR__ . '/header.php';
?>

<?php if ($logged_in): ?>
    <!-- ═══════════════════════════════════════════════════════════
         PART 1: LOGGED-IN PERSONAL DASHBOARD
         Simple, readable, personal. Built around the user.
         ═══════════════════════════════════════════════════════════ -->

    <!-- Welcome Registered Banner (shown once right after registration) -->
    <?php if (!empty($_SESSION['just_registered'])): ?>
        <div class="welcome-registered-banner" role="status">
            <div class="welcome-registered-left">
                <div class="welcome-registered-icon"><?= icon('sparkles', '', 20) ?></div>
                <div>
                    <h2 class="welcome-registered-title">Welcome to DropshippingStack, <?= e($first_name) ?>!</h2>
                    <p class="welcome-registered-sub">Your personal workspace is ready. Build your custom stack below to track your real monthly software expenses.</p>
                </div>
            </div>
            <a href="/stack-builder.php" class="btn-primary" style="padding:10px 22px;font-size:13.5px;white-space:nowrap">
                <span>Build your stack</span>
                <?= icon('arrow-right', '', 14) ?>
            </a>
        </div>
        <?php unset($_SESSION['just_registered']); ?>
    <?php endif; ?>

    <!-- Mobile Greeting Block (Greeting + One Primary Action First) -->
    <div class="mobile-greeting-block">
        <h1 class="mobile-greeting-title">Welcome back, <?= e($first_name) ?></h1>
        <p class="mobile-greeting-sub">Your personal software stack, estimated run-rate, and tailored recommendations.</p>
        <div class="mobile-greeting-cta">
            <a href="/stack-builder.php" class="btn-primary mobile-primary-btn">
                <span>Build / edit my stack</span>
                <?= icon('arrow-right', '', 15) ?>
            </a>
            <a href="/calculator.php" class="btn-secondary mobile-sec-btn">
                <?= icon('calculator', '', 15) ?>
                <span>Profit Calculator</span>
            </a>
        </div>
    </div>

    <!-- ── Stat Cards (Compact 2x2 Grid, Real Data Only) ─────── -->
    <section class="stats-grid personal-stats-grid" aria-label="Personal Stack Summary">
        <!-- StatCard 1: Tools in my stack -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">Tools in Stack</span>
                <div class="stat-card-icon"><?= icon('layers', '', 18) ?></div>
            </div>
            <div class="stat-card-val" id="statStackCount"><?= count($my_stack_tools) ?></div>
            <div class="stat-card-meta">Saved &amp; active</div>
        </div>

        <!-- StatCard 2: Monthly Stack Cost -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">Est. Monthly Cost</span>
                <div class="stat-card-icon"><?= icon('dollar-sign', '', 18) ?></div>
            </div>
            <div class="stat-card-val" id="statStackCost">
                $<?= number_format($my_stack_cost, 0) ?><span class="stat-card-unit">/mo</span>
            </div>
            <div class="stat-card-meta">Sum of subscriptions</div>
        </div>

        <!-- StatCard 3: Free vs Paid Tools -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">Free vs Paid</span>
                <div class="stat-card-icon"><?= icon('scale', '', 18) ?></div>
            </div>
            <div class="stat-card-val" id="statFreePaidVal" style="font-size:20px">
                <?= $my_free_count ?> Free · <?= $my_paid_count ?> Paid
            </div>
            <div class="stat-card-meta">Tier breakdown</div>
        </div>

        <!-- StatCard 4: My Reviews -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">My Reviews</span>
                <div class="stat-card-icon"><?= icon('message-square', '', 18) ?></div>
            </div>
            <div class="stat-card-val"><?= $my_reviews_count ?></div>
            <div class="stat-card-meta">Contributions</div>
        </div>
    </section>

    <!-- ── Two Clear Primary Actions Only (Desktop) ─────────── -->
    <div class="personal-actions-row">
        <a href="/stack-builder.php" class="btn-primary personal-cta-primary">
            <span>Build / edit my stack</span>
            <?= icon('arrow-right', '', 16) ?>
        </a>
        <a href="/calculator.php" class="btn-secondary personal-cta-secondary">
            <?= icon('calculator', '', 16) ?>
            <span>Profit Calculator</span>
        </a>
    </div>

    <!-- ── Main Cards: My Stack & Recommended For You ───────── -->
    <section class="personal-hero-grid" aria-label="My Stack and Recommendations">

        <!-- Main Card: My Stack -->
        <div class="personal-card main-stack-card" id="my-stack">
            <div class="personal-card-head">
                <div>
                    <h3 class="personal-card-title">My Stack</h3>
                    <p class="personal-card-sub" id="myStackSubtitle">
                        <?= count($my_stack_tools) ?> tool<?= count($my_stack_tools) !== 1 ? 's' : '' ?> in your active kit
                    </p>
                </div>
                <?php if (!empty($my_stack_tools)): ?>
                    <a href="/stack-builder.php" class="cat-pill" style="padding:6px 14px;font-size:12.5px">
                        <?= icon('edit', '', 13) ?>
                        <span>Edit Stack</span>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (empty($my_stack_tools)): ?>
                <!-- Friendly Empty State -->
                <div class="stack-empty-state" id="myStackEmptyState">
                    <div class="stack-empty-icon"><?= icon('layers', '', 32) ?></div>
                    <h4 class="stack-empty-title">Your stack is currently empty</h4>
                    <p class="stack-empty-desc">
                        Answer 3 quick questions about your store budget and vertical to assemble your tailored dropshipping toolstack with honest monthly pricing.
                    </p>
                    <a href="/stack-builder.php" class="btn-primary" style="padding:12px 28px;font-size:14px">
                        <span>Build your first stack</span>
                        <?= icon('arrow-right', '', 16) ?>
                    </a>
                </div>
            <?php else: ?>
                <!-- Active Tools Rows (Swipe to Reveal Compare & Remove) -->
                <div class="my-stack-rows-list" id="myStackRowsList">
                    <?php
                    $stack_row_i = 0;
                    foreach ($my_stack_tools as $tool):
                        $stack_row_i++;
                        $is_first_swiped = (!empty($_GET['swipe_first']) && $stack_row_i === 1);
                        $domain = !empty($tool['website_url']) ? parse_url($tool['website_url'], PHP_URL_HOST) : '';
                        $price_text = $tool['price_type'] === 'free'
                            ? 'Free'
                            : ($tool['price_type'] === 'freemium' ? 'Freemium' : 'From $' . round($tool['price_from']) . '/mo');
                    ?>
                        <div class="my-stack-row swipe-row-wrap" data-tool-id="<?= (int)$tool['id'] ?>" data-tool-name="<?= e($tool['name']) ?>" data-tool-slug="<?= e($tool['slug']) ?>">
                            <div class="my-stack-row-left">
                                <div class="my-stack-logo">
                                    <img src="<?= e(tool_logo_url($tool)) ?>"
                                         alt="<?= e($tool['name']) ?>"
                                         class="skeleton-img"
                                         width="40"
                                         height="40"
                                         style="width:100%;height:100%;object-fit:contain;border-radius:8px"
                                         loading="lazy"
                                         decoding="async"
                                         onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                                </div>
                                <div class="my-stack-info">
                                    <div class="my-stack-name"><?= e($tool['name']) ?></div>
                                    <span class="my-stack-category"><?= e($tool['cat_name'] ?? 'General') ?></span>
                                </div>
                            </div>

                            <div class="my-stack-row-right">
                                <span class="my-stack-price-pill"><?= e($price_text) ?></span>
                                <a href="/compare.php?a=<?= urlencode($tool['slug']) ?>"
                                   class="stack-ghost-btn stack-compare-btn"
                                   title="Compare <?= e($tool['name']) ?> Side-by-Side"
                                   aria-label="Compare <?= e($tool['name']) ?> Side-by-Side">
                                    <?= icon('scale', '', 16) ?>
                                </a>
                                <button type="button"
                                        class="stack-ghost-btn stack-remove-btn"
                                        onclick="triggerRowRemoval(<?= (int)$tool['id'] ?>, this)"
                                        title="Remove <?= e($tool['name']) ?> from My Stack"
                                        aria-label="Remove <?= e($tool['name']) ?> from My Stack">
                                    <?= icon('trash-2', '', 16) ?>
                                </button>
                                <button type="button"
                                        class="stack-ghost-btn row-more-btn"
                                        onclick="openToolActionSheet(<?= (int)$tool['id'] ?>, '<?= e(addslashes($tool['name'])) ?>', '<?= e($tool['slug']) ?>', '<?= e($price_text) ?>')"
                                        aria-label="More actions for <?= e($tool['name']) ?>"
                                        title="More actions">
                                    <?= icon('more-horizontal', '', 18) ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Secondary Card: Recommended for you -->
        <div class="personal-card recommended-card">
            <div class="personal-card-head">
                <div>
                    <h3 class="personal-card-title">Recommended for you</h3>
                    <p class="personal-card-sub">Hand-picked from categories missing in your stack</p>
                </div>
                <span class="cat-pill" style="padding:4px 10px;font-size:11.5px">Top ROI</span>
            </div>

            <div class="recommended-rows-list">
                <?php foreach ($recommended_tools as $rec):
                    $rdomain = !empty($rec['website_url']) ? parse_url($rec['website_url'], PHP_URL_HOST) : '';
                    $rprice_text = $rec['price_type'] === 'free'
                        ? 'Free'
                        : ($rec['price_type'] === 'freemium' ? 'Freemium' : 'From $' . round($rec['price_from']) . '/mo');
                ?>
                    <div class="recommended-row" data-tool-id="<?= (int)$rec['id'] ?>">
                        <div class="my-stack-row-left">
                            <div class="my-stack-logo">
                                <img src="<?= e(tool_logo_url($rec)) ?>"
                                     alt="<?= e($rec['name']) ?>"
                                     class="skeleton-img"
                                     width="40"
                                     height="40"
                                     style="width:100%;height:100%;object-fit:contain;border-radius:8px"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                            </div>
                            <div class="my-stack-info">
                                <div class="my-stack-name"><?= e($rec['name']) ?></div>
                                <span class="my-stack-category"><?= e($rec['cat_name'] ?? 'General') ?></span>
                            </div>
                        </div>

                        <div class="my-stack-row-right">
                            <span class="my-stack-price-pill"><?= e($rprice_text) ?></span>
                            <button type="button"
                                    class="stack-add-btn"
                                    onclick="addToolToStack(<?= (int)$rec['id'] ?>, this)">
                                <?= icon('plus', '', 13) ?>
                                <span>Add</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </section>

<?php else: ?>
    <!-- ═══════════════════════════════════════════════════════════
         PUBLIC DASHBOARD (Logged-Out Visitors)
         Real data only: tools, categories, upvotes, pricing.
         No fake delta pills, no fake sparklines, no hardcoded numbers.
         ═══════════════════════════════════════════════════════════ -->

    <!-- Real Stats Grid (Compact 2x2 on Mobile) -->
    <section class="stats-grid" aria-label="Key Performance Indicators">
        <!-- StatCard 1: Tools Reviewed -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">Tools Reviewed</span>
                <div class="stat-card-icon"><?= icon('package', '', 18) ?></div>
            </div>
            <div class="stat-card-val"><?= $total_tools ?></div>
            <div class="stat-card-meta">Curated software directory</div>
        </div>

        <!-- StatCard 2: Categories Covered -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">Categories</span>
                <div class="stat-card-icon"><?= icon('layers', '', 18) ?></div>
            </div>
            <div class="stat-card-val"><?= $total_cats ?></div>
            <div class="stat-card-meta">Full store workflow</div>
        </div>

        <!-- StatCard 3: Community Upvotes -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">Community Upvotes</span>
                <div class="stat-card-icon"><?= icon('thumbs-up', '', 18) ?></div>
            </div>
            <div class="stat-card-val"><?= $total_upvotes ?></div>
            <div class="stat-card-meta">Verified merchant votes</div>
        </div>

        <!-- StatCard 4: Free & Freemium Tools -->
        <div class="stat-card">
            <div class="stat-card-head">
                <span class="stat-card-title">Free &amp; Freemium</span>
                <div class="stat-card-icon"><?= icon('sparkles', '', 18) ?></div>
            </div>
            <div class="stat-card-val"><?= $free_count ?></div>
            <div class="stat-card-meta">Zero upfront cost software</div>
        </div>
    </section>

    <!-- Overview Hero Grid (Real Data Only) -->
    <section class="wallet-hero-grid" aria-label="Stack Performance Overview">
        <!-- Left: Tool Pricing Breakdown -->
        <div class="wallet-balance-card">
            <div>
                <div class="wallet-balance-header">
                    <span class="wallet-badge-label">Average Paid Tool Price</span>
                </div>
                <div class="wallet-balance-val">$<?= $avg_paid_price ?><span style="font-size:16px;font-weight:500;color:var(--muted);margin-left:6px">/mo</span></div>

                <div class="wallet-substats">
                    <div class="wallet-substat-box">
                        <div class="substat-icon-wrap in">
                            <?= icon('check', '', 16) ?>
                        </div>
                        <div>
                            <div class="wallet-substat-label">Free &amp; Freemium</div>
                            <div class="wallet-substat-val"><?= $free_count ?> Tools</div>
                        </div>
                    </div>

                    <div class="wallet-substat-box">
                        <div class="substat-icon-wrap out">
                            <?= icon('dollar-sign', '', 16) ?>
                        </div>
                        <div>
                            <div class="wallet-substat-label">Paid / Premium</div>
                            <div class="wallet-substat-val"><?= $paid_count ?> Tools</div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:14px;flex-wrap:wrap">
                <a href="/stack-builder.php" class="btn-primary" style="padding:12px 24px;font-size:14px">
                    <span>Build Your Custom Stack</span>
                    <?= icon('arrow-right', '', 16) ?>
                </a>
                <a href="/calculator.php" class="btn-secondary" style="padding:12px 20px;font-size:14px">
                    <?= icon('calculator', '', 15) ?>
                    <span>Profit Calculator</span>
                </a>
            </div>
        </div>

        <!-- Right: Essential Stack Deck -->
        <div class="wallet-showcase-card">
            <div class="wallet-showcase-head">
                <div>
                    <h3 style="font-size:18px;font-weight:800;color:var(--text);letter-spacing:-0.02em">Essential Starter Toolkit</h3>
                    <p style="font-size:12.5px;color:var(--muted);margin-top:2px">Top vetted tools to launch your store</p>
                </div>
                <a href="/roadmap.php" class="cat-pill" style="padding:5px 12px;font-size:12px">
                    <?= icon('compass', '', 13) ?>
                    <span>Roadmap</span>
                </a>
            </div>

            <div class="wallet-stack-deck">
                <?php
                $deck_tools = array_slice($featured ?: $all_tools, 0, 3);
                foreach ($deck_tools as $dt):
                    $dt_domain = !empty($dt['website_url']) ? parse_url($dt['website_url'], PHP_URL_HOST) : '';
                    $dt_rating = (float)($dt['rating'] ?? 0);
                ?>
                    <a href="/tool/index.php?slug=<?= e($dt['slug']) ?>" class="deck-item">
                        <div class="deck-tool-left">
                            <div class="deck-tool-logo">
                                <img src="<?= e(tool_logo_url($dt)) ?>"
                                     alt="<?= e($dt['name']) ?>"
                                     class="skeleton-img"
                                     width="36"
                                     height="36"
                                     style="width:100%;height:100%;object-fit:contain;border-radius:6px"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                            </div>
                            <div>
                                <div class="deck-tool-name"><?= e($dt['name']) ?></div>
                                <div class="deck-tool-cat"><?= e($dt['cat_name'] ?? 'Software') ?></div>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:10px">
                            <?php if ($dt_rating > 0): ?>
                                <span style="font-size:13px;font-weight:700;color:var(--text);display:inline-flex;align-items:center;gap:3px">
                                    <span style="color:#F59E0B"><?= icon('star', '', 13) ?></span>
                                    <?= number_format($dt_rating, 1) ?>
                                </span>
                            <?php else: ?>
                                <span class="price-badge price-<?= e($dt['price_type']) ?>" style="font-size:11px;padding:3px 8px">
                                    <?= $dt['price_type'] === 'free' ? 'Free' : ($dt['price_type'] === 'freemium' ? 'Freemium' : '$' . round($dt['price_from']) . '/mo') ?>
                                </span>
                            <?php endif; ?>
                            <?= icon('chevron-right', '', 16) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:var(--muted)">
                <span>Based on verified community votes</span>
                <a href="/compare.php" style="color:var(--accent);font-weight:600;display:inline-flex;align-items:center;gap:4px">
                    <span>Compare side-by-side</span>
                    <?= icon('arrow-up-right', '', 12) ?>
                </a>
            </div>
        </div>
    </section>

<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     CATEGORY FILTER PILLS (Sticky with Faded Edges)
     ═══════════════════════════════════════════════════════════ -->
<div class="category-pills-wrap">
    <div class="category-pills-bar <?= $logged_in ? 'pills-quieter' : '' ?>" id="categoryPillsBar" role="tablist" aria-label="Filter by category">
        <button type="button" class="cat-pill active-pill" data-category="all" role="tab" aria-selected="true" aria-pressed="true">
            <?= icon('layout-grid', '', 14) ?>
            <span>All Tools</span>
            <span class="cat-pill-count">(<?= $total_tools ?>)</span>
        </button>
        <?php foreach ($cats as $cat): ?>
            <button type="button" class="cat-pill" data-category="<?= e($cat['slug']) ?>" role="tab" aria-selected="false" aria-pressed="false">
                <span><?= e($cat['name']) ?></span>
                <span class="cat-pill-count">(<?= (int)($cat_counts[$cat['id']] ?? 0) ?>)</span>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     FEATURED TOOLS SECTION (Embla Swipe Carousel with Peek & Dots)
     ═══════════════════════════════════════════════════════════ -->
<?php if (!empty($featured)): ?>
    <section id="featuredSection" aria-labelledby="featured-heading" class="featured-section <?= $logged_in ? 'section-quieter' : '' ?>">
        <div class="dashboard-section-head featured-section-head">
            <div class="section-head-top-row">
                <div class="section-title-wrap">
                    <h2 id="featured-heading" class="section-title">
                        <?= icon('sparkles', '', 20) ?>
                        <span>Featured Tools</span>
                    </h2>
                </div>

                <!-- Carousel Navigation Arrows (Bug 2 & Task 1 Fix: Right-aligned with section title) -->
                <div class="featured-arrows embla-arrows-desktop">
                    <button type="button" class="featured-arrow-btn embla-arrow-btn" id="featuredPrev" aria-label="Previous slide" title="Previous tools">
                        <?= icon('chevron-left', '', 18) ?>
                    </button>
                    <button type="button" class="featured-arrow-btn embla-arrow-btn" id="featuredNext" aria-label="Next slide" title="Next tools">
                        <?= icon('chevron-right', '', 18) ?>
                    </button>
                </div>
            </div>
            <p class="section-desc">Hand-picked software with high community ROI</p>
        </div>

        <!-- Carousel Viewport (Native Scroll-Snap with Smooth Scrolling) -->
        <div class="featured-carousel embla-carousel" id="featuredEmbla" role="region" aria-roledescription="carousel" aria-label="Featured Tools">
            <div class="featured-track embla-container" role="presentation">
                <?php
                $hide_featured_badge = true;
                $f_idx = 0;
                foreach ($featured as $t):
                    $f_idx++;
                ?>
                    <div class="featured-card-wrapper embla-slide" role="group" aria-roledescription="slide" aria-label="<?= e($t['name']) ?> (<?= $f_idx ?> of <?= count($featured) ?>)">
                        <?php include __DIR__ . '/partials/tool-card.php'; ?>
                    </div>
                <?php endforeach;
                unset($hide_featured_badge);
                ?>
            </div>
        </div>

        <!-- Morphing Pagination Dots -->
        <div class="embla-dots" id="featuredDots" role="tablist" aria-label="Featured Tools pagination"></div>
    </section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     ALL TOOLS DIRECTORY (Normal Vertical Scroll with Compact Cards)
     ═══════════════════════════════════════════════════════════ -->
<section aria-labelledby="all-tools-heading" class="<?= $logged_in ? 'section-quieter' : '' ?>" id="allToolsSection">
    <div class="dashboard-section-head directory-section-head">
        <div class="section-head-top-row">
            <div class="section-title-wrap">
                <h2 id="all-tools-heading" class="section-title">
                    <?= icon('package', '', 20) ?>
                    <span>All Tools Directory</span>
                </h2>
                <span class="tools-count-badge" id="dirToolsCount">
                    <?= count($all_tools) ?> tools
                </span>
            </div>

            <!-- Carousel Navigation Controls (Shown in carousel mode, aligned with title) -->
            <div class="dir-carousel-controls" id="dirCarouselControls" style="display:none;" aria-label="Carousel navigation">
                <span class="dir-carousel-counter" id="dirCounter" aria-live="polite">1 / <?= count($all_tools) ?></span>
                <button type="button" class="dir-arrow-btn" id="dirPrev" aria-label="Previous tools" title="Previous tools" disabled>
                    <?= icon('chevron-left', '', 18) ?>
                </button>
                <button type="button" class="dir-arrow-btn" id="dirNext" aria-label="Next tools" title="Next tools">
                    <?= icon('chevron-right', '', 18) ?>
                </button>
            </div>
        </div>

        <p class="section-desc">Complete catalogue of verified dropshipping tools</p>

        <div class="section-filters-pill" id="dirFilterPills" role="tablist" aria-label="Filter pricing">
            <button type="button" class="filter-btn active" data-filter="all" role="tab" aria-selected="true" aria-pressed="true">All</button>
            <button type="button" class="filter-btn" data-filter="free" role="tab" aria-selected="false" aria-pressed="false">Free / Freemium</button>
            <button type="button" class="filter-btn" data-filter="paid" role="tab" aria-selected="false" aria-pressed="false">Paid</button>
            <button type="button" class="filter-btn" data-filter="featured" role="tab" aria-selected="false" aria-pressed="false">Featured</button>
        </div>
    </div>

    <!-- Screen-reader live status announcement (Task 2) -->
    <div id="dirLiveRegion" class="sr-only" role="status" aria-live="polite"></div>

    <!-- Empty state container (hidden by default) -->
    <div id="dirEmptyState" class="dir-empty-state" style="display:none;" role="status">
        <div class="dir-empty-icon"><?= icon('package', '', 36) ?></div>
        <p class="dir-empty-msg">No tools match this filter.</p>
        <button type="button" class="btn-action-link" id="dirShowAllBtn">
            Show all tools <?= icon('arrow-right', '', 14) ?>
        </button>
    </div>

    <!-- Skeleton placeholder during transitions -->
    <div id="dirSkeleton" class="dir-skeleton-wrap" style="display:none;" aria-hidden="true">
        <div class="skeleton-card"><div class="skeleton-shimmer"></div></div>
        <div class="skeleton-card"><div class="skeleton-shimmer"></div></div>
        <div class="skeleton-card"><div class="skeleton-shimmer"></div></div>
    </div>

    <div class="tools-grid" id="mainToolsGrid" tabindex="0" role="region" aria-label="Tools Directory">
        <?php foreach ($all_tools as $t): ?>
            <?php include __DIR__ . '/partials/tool-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     TOOL ACTION BOTTOM SHEET (Hidden by Default - Bug 3 Fix)
     ═══════════════════════════════════════════════════════════ -->
<div class="action-sheet-overlay" id="toolActionOverlay" onclick="closeToolActionSheet()" hidden></div>
<div class="action-sheet" id="toolActionSheet" role="dialog" aria-modal="true" aria-label="Tool Options" hidden>
    <div class="action-sheet-handle" onclick="closeToolActionSheet()"></div>
    <div class="action-sheet-header">
        <h3 class="action-sheet-title" id="actionSheetTitle">Tool Name</h3>
        <span class="action-sheet-price" id="actionSheetPrice">Price</span>
    </div>
    <div class="action-sheet-actions">
        <a href="#" id="actionSheetCompareLink" class="action-sheet-btn compare">
            <?= icon('scale', '', 18) ?>
            <span>Compare Side-by-Side</span>
        </a>
        <button type="button" id="actionSheetRemoveBtn" class="action-sheet-btn remove">
            <?= icon('trash-2', '', 18) ?>
            <span>Remove from My Stack</span>
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     5-SECOND UNDO TOAST FOR SWIPE REMOVAL
     ═══════════════════════════════════════════════════════════ -->
<div class="undo-toast" id="undoToast" role="status" aria-live="polite">
    <div class="undo-toast-inner">
        <span class="undo-toast-msg" id="undoToastMsg">Removed tool from stack</span>
        <button type="button" class="undo-toast-btn" id="undoToastBtn" onclick="undoRowRemoval()">Undo</button>
    </div>
    <div class="undo-progress-bar" id="undoProgressBar"></div>
</div>

<!-- Include Full-Screen Intro Experience (Only for new visitors or when ?intro is requested) -->
<?php if (empty($_COOKIE['intro_seen']) || isset($_GET['intro'])): ?>
    <?php include __DIR__ . '/partials/intro-modal.php'; ?>
<?php endif; ?>

<!-- Vendored Embla Carousel Library -->
<script src="/assets/js/embla-carousel.umd.js" defer></script>

<script>
// ── Filter Tools Directory Delegate ─────────────────────────
function filterTools(type, btn) {
    if (typeof window.applyDirectoryFilter === 'function') {
        window.applyDirectoryFilter(type, true);
    }
}

// ════════════════════════════════════════════════════════════
// FEATURED TOOLS CAROUSEL (Bug 2 Fix: Smooth Native Scroll-Snap)
// ════════════════════════════════════════════════════════════
(function initFeaturedCarousel() {
    const carousel = document.getElementById('featuredEmbla');
    if (!carousel) return;
    const container = carousel.querySelector('.embla-container, .featured-track') || carousel;
    const prevBtn = document.getElementById('featuredPrev');
    const nextBtn = document.getElementById('featuredNext');

    function getCardWidth() {
        const slide = container.querySelector('.embla-slide, .featured-card-wrapper');
        return slide ? (slide.offsetWidth + 16) : 340;
    }

    function updateArrowStates() {
        if (!prevBtn || !nextBtn) return;
        const scrollLeft = container.scrollLeft;
        const maxScroll = container.scrollWidth - container.clientWidth;

        const isStart = scrollLeft <= 4;
        const isEnd = scrollLeft >= maxScroll - 4;

        prevBtn.disabled = isStart;
        prevBtn.setAttribute('aria-disabled', isStart ? 'true' : 'false');

        nextBtn.disabled = isEnd;
        nextBtn.setAttribute('aria-disabled', isEnd ? 'true' : 'false');
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            container.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            container.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
        });
    }

    container.addEventListener('scroll', updateArrowStates, { passive: true });
    window.addEventListener('resize', updateArrowStates, { passive: true });
    updateArrowStates();

    // Arrow Key Navigation
    carousel.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') container.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
        if (e.key === 'ArrowLeft') container.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
    });
})();

// ════════════════════════════════════════════════════════════
// MY STACK ROWS, BOTTOM SHEET & 5-SEC UNDO TOAST
// ════════════════════════════════════════════════════════════
let pendingRemovalTimeout = null;
let pendingRemovalToolId = null;
let pendingRemovalRow = null;

// Bottom Sheet Handling (Bug 3 Fix)
function openToolActionSheet(id, name, slug, price) {
    const sheet = document.getElementById('toolActionSheet');
    const overlay = document.getElementById('toolActionOverlay');
    const titleEl = document.getElementById('actionSheetTitle');
    const priceEl = document.getElementById('actionSheetPrice');
    const compareLink = document.getElementById('actionSheetCompareLink');
    const removeBtn = document.getElementById('actionSheetRemoveBtn');

    if (titleEl) titleEl.textContent = name;
    if (priceEl) priceEl.textContent = price;
    if (compareLink) compareLink.href = `/compare.php?a=${encodeURIComponent(slug)}`;
    if (removeBtn) {
        removeBtn.onclick = () => {
            closeToolActionSheet();
            const wrap = document.querySelector(`.my-stack-row[data-tool-id="${id}"], .swipe-row-wrap[data-tool-id="${id}"]`);
            if (wrap) triggerRowRemoval(id, wrap);
        };
    }

    if (sheet) {
        sheet.hidden = false;
        sheet.style.display = 'block';
        requestAnimationFrame(() => sheet.classList.add('open'));
    }
    if (overlay) {
        overlay.hidden = false;
        overlay.style.display = 'block';
        requestAnimationFrame(() => overlay.classList.add('open'));
    }
}

function closeToolActionSheet() {
    const sheet = document.getElementById('toolActionSheet');
    const overlay = document.getElementById('toolActionOverlay');
    if (sheet) {
        sheet.classList.remove('open');
        setTimeout(() => {
            if (!sheet.classList.contains('open')) {
                sheet.hidden = true;
                sheet.style.display = 'none';
            }
        }, 250);
    }
    if (overlay) {
        overlay.classList.remove('open');
        setTimeout(() => {
            if (!overlay.classList.contains('open')) {
                overlay.hidden = true;
                overlay.style.display = 'none';
            }
        }, 250);
    }
}

// 5-Second Undo Toast & Row Removal Execution
function triggerRowRemoval(toolId, triggerEl) {
    const wrap = triggerEl.closest('.my-stack-row, .swipe-row-wrap');
    if (!wrap) return;

    if (pendingRemovalTimeout) {
        commitRemoval();
    }

    const toolName = wrap.getAttribute('data-tool-name') || 'Tool';
    pendingRemovalToolId = toolId;
    pendingRemovalRow = wrap;

    // Animate row removal visually
    wrap.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
    wrap.style.transform = 'translateX(-20px)';
    wrap.style.opacity = '0';
    setTimeout(() => {
        wrap.style.display = 'none';
    }, 250);

    // Optimistically update counter
    const countEl = document.getElementById('statStackCount');
    const subTitleEl = document.getElementById('myStackSubtitle');
    if (countEl) {
        const cur = parseInt(countEl.textContent || '0', 10);
        if (cur > 0) countEl.textContent = cur - 1;
        if (subTitleEl) subTitleEl.textContent = `${Math.max(0, cur - 1)} tool${cur - 1 !== 1 ? 's' : ''} in your active kit`;
    }

    // Show Undo Toast
    const toast = document.getElementById('undoToast');
    const msg = document.getElementById('undoToastMsg');
    const pbar = document.getElementById('undoProgressBar');
    const uBtn = document.getElementById('undoToastBtn');

    if (msg) msg.textContent = `Removed ${toolName} from stack`;
    if (uBtn) uBtn.style.display = '';
    if (pbar) {
        pbar.style.display = '';
        pbar.style.transition = 'none';
        pbar.style.transform = 'scaleX(1)';
        requestAnimationFrame(() => {
            pbar.style.transition = 'transform 5s linear';
            pbar.style.transform = 'scaleX(0)';
        });
    }
    if (toast) toast.classList.add('show');

    pendingRemovalTimeout = setTimeout(commitRemoval, 5000);
}

async function commitRemoval() {
    clearTimeout(pendingRemovalTimeout);
    pendingRemovalTimeout = null;
    const toast = document.getElementById('undoToast');
    if (toast) toast.classList.remove('show');

    if (!pendingRemovalToolId) return;

    const toolId = pendingRemovalToolId;
    const row = pendingRemovalRow;
    pendingRemovalToolId = null;
    pendingRemovalRow = null;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/stack-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', tool_id: toolId, csrf_token: csrfToken })
        });
        const data = await res.json();
        if (data.success) {
            if (row) row.remove();
            updateStackDOM(data);
        } else {
            // Revert
            if (row) {
                row.style.display = '';
                requestAnimationFrame(() => {
                    row.style.transform = '';
                    row.style.opacity = '1';
                });
            }
            if (typeof showActionToast === 'function') {
                showActionToast('Failed to remove tool.');
            }
        }
    } catch (e) {
        console.error('Failed to commit removal:', e);
        if (row) {
            row.style.display = '';
            requestAnimationFrame(() => {
                row.style.transform = '';
                row.style.opacity = '1';
            });
        }
        if (typeof showActionToast === 'function') {
            showActionToast('Connection error. Tool removal not saved.');
        }
    }
}

function undoRowRemoval() {
    clearTimeout(pendingRemovalTimeout);
    pendingRemovalTimeout = null;

    const toast = document.getElementById('undoToast');
    if (toast) toast.classList.remove('show');

    if (pendingRemovalRow) {
        pendingRemovalRow.style.display = '';
        requestAnimationFrame(() => {
            pendingRemovalRow.style.transform = 'translateX(0)';
            pendingRemovalRow.style.opacity = '1';
        });
    }

    // Revert counter
    const countEl = document.getElementById('statStackCount');
    const subTitleEl = document.getElementById('myStackSubtitle');
    if (countEl) {
        const cur = parseInt(countEl.textContent || '0', 10);
        countEl.textContent = cur + 1;
        if (subTitleEl) subTitleEl.textContent = `${cur + 1} tool${cur + 1 !== 1 ? 's' : ''} in your active kit`;
    }

    pendingRemovalToolId = null;
    pendingRemovalRow = null;
}

// Optimistic Add Tool to Stack (NO full-page reload)
async function addToolToStack(toolId, btn) {
    if (btn.disabled) return;
    btn.disabled = true;
    const oldHtml = btn.innerHTML;
    // Optimistic UI update
    btn.innerHTML = '<span>Added ✓</span>';
    btn.classList.add('btn-added');

    // Optimistically increment stack counter in DOM
    const countEl = document.getElementById('statStackCount');
    const subTitleEl = document.getElementById('myStackSubtitle');
    let prevCount = 0;
    if (countEl) {
        prevCount = parseInt(countEl.textContent || '0', 10);
        countEl.textContent = prevCount + 1;
    }
    if (subTitleEl) {
        subTitleEl.textContent = `${prevCount + 1} tools in your active kit`;
    }

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/api/stack-action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', tool_id: toolId, csrf_token: csrfToken })
        });
        const data = await res.json();

        if (data.success) {
            updateStackDOM(data);
            if (typeof showActionToast === 'function') {
                showActionToast('Tool added to your stack!');
            }
        } else {
            // Revert
            btn.innerHTML = oldHtml;
            btn.disabled = false;
            btn.classList.remove('btn-added');
            if (countEl) countEl.textContent = prevCount;
            if (subTitleEl) subTitleEl.textContent = `${prevCount} tools in your active kit`;
            if (typeof showActionToast === 'function') {
                showActionToast(data.error || 'Failed to add tool to stack.');
            }
        }
    } catch (e) {
        console.error('Failed to add tool:', e);
        // Revert
        btn.innerHTML = oldHtml;
        btn.disabled = false;
        btn.classList.remove('btn-added');
        if (countEl) countEl.textContent = prevCount;
        if (subTitleEl) subTitleEl.textContent = `${prevCount} tools in your active kit`;
        if (typeof showActionToast === 'function') {
            showActionToast('Connection error. Tool could not be added.');
        }
    }
}

function updateStackDOM(data) {
    const countEl = document.getElementById('statStackCount');
    const costEl = document.getElementById('statStackCost');
    const freePaidEl = document.getElementById('statFreePaidVal');
    const subTitleEl = document.getElementById('myStackSubtitle');

    if (countEl) countEl.textContent = data.count;
    if (costEl) costEl.innerHTML = `$${Math.round(data.total_cost)}<span class="stat-card-unit">/mo</span>`;
    if (freePaidEl) freePaidEl.textContent = `${data.free_count} Free · ${data.paid_count} Paid`;
    if (subTitleEl) subTitleEl.textContent = `${data.count} tool${data.count !== 1 ? 's' : ''} in your active kit`;

    if (data.empty) {
        const list = document.getElementById('myStackRowsList');
        if (list) {
            list.outerHTML = `
                <div class="stack-empty-state" id="myStackEmptyState">
                    <div class="stack-empty-icon"><?= icon('layers', '', 32) ?></div>
                    <h4 class="stack-empty-title">Your stack is currently empty</h4>
                    <p class="stack-empty-desc">
                        Answer 3 quick questions about your store budget and vertical to assemble your tailored dropshipping toolstack with honest monthly pricing.
                    </p>
                    <a href="/stack-builder.php" class="btn-primary" style="padding:12px 28px;font-size:14px">
                        <span>Build your first stack</span>
                        <?= icon('arrow-right', '', 16) ?>
                    </a>
                </div>
            `;
        }
    }
}
</script>

<?php if (!empty($_GET['swipe_first'])): ?>
    <script>
    window.addEventListener('load', () => {
        setTimeout(() => {
            const f = document.querySelector('.swipe-row-wrap .my-stack-row-front');
            if (f) {
                f.style.transform = 'translateX(-140px)';
                f.classList.add('is-swiped');
            }
        }, 120);
    });
    </script>
<?php endif; ?>
<?php if (!empty($_GET['view']) && $_GET['view'] === 'featured'): ?>
    <style>
    .mobile-greeting-block, .personal-stats-grid, .personal-hero-grid, .stats-grid, .wallet-hero-grid, .personal-actions-row {
        display: none !important;
    }
    </style>
<?php endif; ?>
<?php if (!empty($_GET['view']) && $_GET['view'] === 'all_tools'): ?>
    <style>
    .mobile-greeting-block, .personal-stats-grid, .personal-hero-grid, .stats-grid, .wallet-hero-grid, .personal-actions-row, .featured-section {
        display: none !important;
    }
    </style>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
