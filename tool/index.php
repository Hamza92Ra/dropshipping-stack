<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? basename($_SERVER['PHP_SELF'], '.php'));

$tool = $pdo->prepare("
    SELECT t.*, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
    FROM tools t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.slug = ? AND t.is_active = 1
");
$tool->execute([$slug]);
$t = $tool->fetch();

if (!$t) {
    http_response_code(404);
    die('Tool not found.');
}

// Reviews
$reviews = $pdo->prepare("
    SELECT r.*, u.username
    FROM reviews r
    LEFT JOIN users u ON u.id = r.user_id
    WHERE r.tool_id = ? AND r.approved = 1
    ORDER BY r.created_at DESC
");
$reviews->execute([$t['id']]);
$reviews = $reviews->fetchAll();

// Handle review submission
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!csrf_verify()) {
        $flash = '<div class="alert-error" style="padding:12px 16px;border-radius:12px;background:var(--negative-bg);color:var(--negative-text);margin-bottom:16px">Security check failed. Please refresh and try again.</div>';
    } else {
        if (!isset($_SESSION['user_id'])) {
            $flash = '<div class="alert-error" style="padding:12px 16px;border-radius:12px;background:var(--negative-bg);color:var(--negative-text);margin-bottom:16px">You must be logged in to leave a review. <a href="/login.php" style="text-decoration:underline">Sign in here</a>.</div>';
        } else {
            $rating = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');

            if ($rating >= 1 && $rating <= 5 && $comment) {
                // Check if user already reviewed
                $already = $pdo->prepare("SELECT id FROM reviews WHERE tool_id = ? AND user_id = ?");
                $already->execute([$t['id'], $_SESSION['user_id']]);

                if ($already->fetch()) {
                    $flash = '<div class="alert-error" style="padding:12px 16px;border-radius:12px;background:var(--negative-bg);color:var(--negative-text);margin-bottom:16px">You have already submitted a review for this software.</div>';
                } else {
                    $ins = $pdo->prepare("INSERT INTO reviews (tool_id, user_id, rating, comment, approved) VALUES (?,?,?,?,1)");
                    $ins->execute([$t['id'], $_SESSION['user_id'], $rating, $comment]);

                    // Update tool aggregated review counts and average rating
                    $pdo->prepare("
                        UPDATE tools
                        SET review_count = (SELECT COUNT(*) FROM reviews WHERE tool_id = ? AND approved = 1),
                            rating = (SELECT COALESCE(AVG(rating),0) FROM reviews WHERE tool_id = ? AND approved = 1)
                        WHERE id = ?
                    ")->execute([$t['id'], $t['id'], $t['id']]);

                    $flash = '<div class="alert-success" style="padding:12px 16px;border-radius:12px;background:var(--positive-bg);color:var(--positive-text);margin-bottom:16px">Your review has been published. Thank you!</div>';

                    // Re-fetch reviews and tool
                    $reviews = $pdo->prepare("SELECT r.*, u.username FROM reviews r LEFT JOIN users u ON u.id = r.user_id WHERE r.tool_id = ? AND r.approved = 1 ORDER BY r.created_at DESC");
                    $reviews->execute([$t['id']]);
                    $reviews = $reviews->fetchAll();

                    $tool->execute([$slug]);
                    $t = $tool->fetch();
                }
            } else {
                $flash = '<div class="alert-error" style="padding:12px 16px;border-radius:12px;background:var(--negative-bg);color:var(--negative-text);margin-bottom:16px">Please select a star rating and enter review text.</div>';
            }
        }
    }
}

$pros = json_decode($t['pros'] ?? '[]', true) ?: [];
$cons = json_decode($t['cons'] ?? '[]', true) ?: [];
$round_rating = (int)round((float)$t['rating']);
$domain = !empty($t['website_url']) ? parse_url($t['website_url'], PHP_URL_HOST) : '';

$page_title      = $t['name'] . ' In-Depth Review (' . date('Y') . ') — ' . SITE_NAME;
$page_heading    = e($t['name']);
$page_subheading = e($t['tagline']);
$page_desc       = $t['tagline'];

include __DIR__ . '/../header.php';
?>

<style>
.tool-detail-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 32px;
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0 60px;
}

/* Breadcrumbs */
.breadcrumb-nav {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 24px;
}

.breadcrumb-nav a {
    color: var(--muted);
}

.breadcrumb-nav a:hover {
    color: var(--text);
}

/* Header Card */
.tool-hero-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 32px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: flex-start;
    gap: 24px;
    margin-bottom: 24px;
}

.tool-hero-logo {
    width: 72px;
    height: 72px;
    border-radius: var(--radius-xl);
    background: var(--surface-2);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    flex-shrink: 0;
}

.tool-hero-body {
    flex: 1;
}

.tool-hero-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.02em;
    margin-bottom: 4px;
}

.tool-hero-tagline {
    font-size: 14.5px;
    color: var(--muted);
    line-height: 1.5;
    margin-bottom: 16px;
}

.tool-hero-meta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.tool-hero-rating {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

/* Content Sections */
.detail-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 32px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 24px;
}

.detail-card h3 {
    font-size: 18px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 16px;
    letter-spacing: -0.015em;
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-desc-text {
    font-size: 14.5px;
    color: var(--body);
    line-height: 1.7;
}

/* Pros and Cons */
.pros-cons-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.pros-box, .cons-box {
    background: var(--surface-2);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
    padding: 20px;
}

.pros-box h4 {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--positive-text);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.cons-box h4 {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--negative-text);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.pros-cons-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.pros-cons-list li {
    font-size: 13px;
    color: var(--body);
    line-height: 1.45;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

/* Reviews List */
.review-item-card {
    background: var(--surface-2);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
    padding: 20px;
    margin-bottom: 14px;
}

.review-item-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
}

.reviewer-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text);
}

.review-item-text {
    font-size: 13.5px;
    color: var(--body);
    line-height: 1.55;
}

/* Sticky Sidebar */
.tool-sidebar-panel {
    position: sticky;
    top: 90px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.sidebar-cta-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 28px;
    box-shadow: var(--shadow-sm);
    text-align: center;
}

.sidebar-cta-card h3 {
    font-size: 20px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 8px;
}

.sidebar-cta-card p {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 20px;
    line-height: 1.5;
}

.sidebar-facts-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 24px 28px;
    box-shadow: var(--shadow-sm);
}

.sidebar-facts-card h4 {
    font-size: 12.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted);
    margin-bottom: 16px;
}

.facts-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-subtle);
    font-size: 13px;
}

.facts-row:last-child {
    border-bottom: none;
}

.facts-key {
    color: var(--muted);
    font-weight: 600;
}

.facts-val {
    color: var(--text);
    font-weight: 700;
}

@media (max-width: 900px) {
    .tool-detail-layout {
        grid-template-columns: 1fr;
    }
    .tool-sidebar-panel {
        position: static;
    }
    .pros-cons-grid {
        grid-template-columns: 1fr;
    }
}

/* Mobile Segmented Tab Bar */
.mobile-section-tabs {
    display: none;
}

@media (max-width: 900px) {
    .mobile-section-tabs {
        display: flex;
        background: var(--surface-2);
        border: 1px solid var(--border-subtle);
        border-radius: var(--radius-full);
        padding: 4px;
        margin-bottom: 24px;
        gap: 4px;
    }

    .mobile-section-tab {
        flex: 1;
        padding: 9px 12px;
        font-size: 13px;
        font-weight: 700;
        border: none;
        background: transparent;
        color: var(--muted);
        border-radius: var(--radius-full);
        cursor: pointer;
        text-align: center;
        transition: all var(--dur-fast) var(--ease);
    }

    .mobile-section-tab.active {
        background: var(--surface);
        color: var(--text);
        box-shadow: var(--shadow-xs);
    }

    .detail-section-group {
        display: block;
    }

    .detail-section-group.mobile-hidden {
        display: none !important;
    }

    /* Mobile Sticky Bottom Action Bar */
    .mobile-sticky-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        position: fixed;
        bottom: calc(76px + env(safe-area-inset-bottom, 0px));
        left: 16px;
        right: 16px;
        max-width: 440px;
        margin: 0 auto;
        padding: 10px 18px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-full);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.16);
        z-index: 180;
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .mobile-sticky-action-left {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .mobile-sticky-tool-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mobile-sticky-tool-price {
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
    }

    .mobile-sticky-visit-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        min-height: 44px;
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        font-size: 13.5px;
        font-weight: 700;
        border-radius: var(--radius-full);
        text-decoration: none;
        box-shadow: var(--shadow-xs);
        flex-shrink: 0;
    }

    #ds-chat-bubble {
        bottom: calc(146px + env(safe-area-inset-bottom, 0px)) !important;
    }
}
</style>

<div class="tool-detail-layout">

    <!-- Main Review Content -->
    <div>
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            <a href="/index.php">Home</a>
            <?= icon('chevron-right', '', 12) ?>
            <a href="/category/index.php?slug=<?= e($t['cat_slug']) ?>"><?= e($t['cat_name'] ?? 'Software') ?></a>
            <?= icon('chevron-right', '', 12) ?>
            <span style="color:var(--text);font-weight:600"><?= e($t['name']) ?></span>
        </nav>

        <!-- Tool Header Card -->
        <div class="tool-hero-card">
            <div class="tool-hero-logo">
                <?php if ($domain): ?>
                    <img src="https://img.logo.dev/<?= e($domain) ?>?token=pk_IPkJggMaTFC35CqF8UE3hg&size=128"
                         alt="<?= e($t['name']) ?>"
                         class="skeleton-img"
                         style="width:100%;height:100%;object-fit:contain;border-radius:10px"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;color:var(--accent)">
                        <?= icon('package', '', 32) ?>
                    </div>
                <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--accent)">
                        <?= icon('package', '', 32) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tool-hero-body">
                <h1 class="tool-hero-title"><?= e($t['name']) ?></h1>
                <p class="tool-hero-tagline"><?= e($t['tagline']) ?></p>

                <div class="tool-hero-meta">
                    <?php if ((int)$t['review_count'] > 0): ?>
                        <span class="tool-hero-rating">
                            <span style="color:#F59E0B;display:inline-flex;gap:2px">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span style="color:<?= $i <= $round_rating ? '#F59E0B' : 'rgba(125,140,128,0.3)' ?>">
                                        <?= icon('star', '', 14) ?>
                                    </span>
                                <?php endfor; ?>
                            </span>
                            <span><?= number_format((float)$t['rating'], 1) ?></span>
                            <span style="color:var(--muted);font-weight:500">(<?= (int)$t['review_count'] ?> review<?= (int)$t['review_count'] !== 1 ? 's' : '' ?>)</span>
                        </span>
                    <?php else: ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;color:var(--accent);background:var(--accent-light);padding:3px 10px;border-radius:999px">
                            <?= icon('shield-check', '', 13) ?>
                            <span>Vetted Tool</span>
                        </span>
                    <?php endif; ?>

                    <span class="price-badge price-<?= e($t['price_type']) ?>">
                        <?= $t['price_type'] === 'freemium' ? 'Freemium' : ($t['price_type'] === 'free' ? 'Free' : 'From $' . number_format((float)$t['price_from'], 0) . '/mo') ?>
                    </span>

                    <span class="tool-category"><?= e($t['cat_name'] ?? 'Tools') ?></span>
                </div>
            </div>
        </div>

        <!-- Mobile Segmented Tab Controls (Overview, Pricing & Facts, Reviews) -->
        <div class="mobile-section-tabs" id="mobileSectionTabs">
            <button type="button" class="mobile-section-tab active" data-tab="overview" onclick="switchDetailTab('overview')">Overview</button>
            <button type="button" class="mobile-section-tab" data-tab="pricing" onclick="switchDetailTab('pricing')">Pricing & Facts</button>
            <button type="button" class="mobile-section-tab" data-tab="reviews" onclick="switchDetailTab('reviews')">Reviews (<?= count($reviews) ?>)</button>
        </div>

        <!-- Section Group: Overview -->
        <div class="detail-section-group" id="detailSectionOverview">
            <!-- Description Card -->
            <div class="detail-card">
                <h3>
                    <?= icon('info', '', 18) ?>
                    <span>Overview & Capabilities</span>
                </h3>
                <div class="detail-desc-text">
                    <?= nl2br(e($t['description'])) ?>
                </div>
            </div>

            <!-- Pros & Cons -->
            <?php if (!empty($pros) || !empty($cons)): ?>
                <div class="detail-card">
                    <h3>
                        <?= icon('scale', '', 18) ?>
                        <span>Pros & Cons Analysis</span>
                    </h3>
                    <div class="pros-cons-grid">
                        <?php if (!empty($pros)): ?>
                            <div class="pros-box">
                                <h4>
                                    <?= icon('check-circle', '', 16) ?>
                                    <span>Strengths</span>
                                </h4>
                                <ul class="pros-cons-list">
                                    <?php foreach ($pros as $p): ?>
                                        <li>
                                            <span style="color:var(--positive);flex-shrink:0"><?= icon('check', '', 14) ?></span>
                                            <span><?= e($p) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($cons)): ?>
                            <div class="cons-box">
                                <h4>
                                    <?= icon('x', '', 16) ?>
                                    <span>Limitations</span>
                                </h4>
                                <ul class="pros-cons-list">
                                    <?php foreach ($cons as $c): ?>
                                        <li>
                                            <span style="color:var(--negative);flex-shrink:0"><?= icon('x', '', 14) ?></span>
                                            <span><?= e($c) ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section Group: Pricing & Facts (Mobile Mirror) -->
        <div class="detail-section-group mobile-hidden" id="detailSectionPricingMobile">
            <div class="detail-card">
                <h3>
                    <?= icon('dollar-sign', '', 18) ?>
                    <span>Software Benchmark Facts</span>
                </h3>
                <div class="facts-row">
                    <span class="facts-key">Category</span>
                    <span class="facts-val"><?= e($t['cat_name'] ?? 'General') ?></span>
                </div>
                <div class="facts-row">
                    <span class="facts-key">Pricing Model</span>
                    <span class="facts-val"><?= ucfirst($t['price_type']) ?></span>
                </div>
                <div class="facts-row">
                    <span class="facts-key">Base Starting Fee</span>
                    <span class="facts-val"><?= (float)$t['price_from'] > 0 ? '$' . number_format((float)$t['price_from'], 0) . '/mo' : 'Free tier' ?></span>
                </div>
                <div class="facts-row">
                    <span class="facts-key">Platform Rating</span>
                    <span class="facts-val"><?= number_format((float)$t['rating'], 1) ?> / 5.0</span>
                </div>
                <div class="facts-row">
                    <span class="facts-key">Community Votes</span>
                    <span class="facts-val"><?= (int)$t['upvotes'] ?></span>
                </div>
                <?php if (!empty($t['website_url'])): ?>
                    <div class="facts-row">
                        <span class="facts-key">Domain</span>
                        <a href="<?= e($t['website_url']) ?>" target="_blank" rel="noopener" class="facts-val" style="color:var(--accent)">
                            <?= parse_url($t['website_url'], PHP_URL_HOST) ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Group: Reviews -->
        <div class="detail-section-group" id="detailSectionReviews">
            <div class="detail-card">
                <h3>
                    <?= icon('message-square', '', 18) ?>
                    <span>User Reviews (<?= count($reviews) ?>)</span>
                </h3>

                <?= $flash ?>

                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $r): ?>
                        <div class="review-item-card">
                            <div class="review-item-head">
                                <span class="reviewer-name"><?= e($r['username'] ?: 'Verified User') ?></span>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <span style="display:inline-flex;color:#F59E0B">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <span style="color:<?= $s <= (int)$r['rating'] ? '#F59E0B' : 'rgba(125,140,128,0.3)' ?>">
                                                <?= icon('star', '', 12) ?>
                                            </span>
                                        <?php endfor; ?>
                                    </span>
                                    <span style="font-size:12px;color:var(--muted)"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
                                </div>
                            </div>
                            <p class="review-item-text"><?= nl2br(e($r['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size:13.5px;color:var(--muted);margin-bottom:24px">No community reviews submitted yet. Be the first to review <?= e($t['name']) ?>!</p>
                <?php endif; ?>

                <!-- Leave a Review Form -->
                <div style="margin-top:28px;padding-top:24px;border-top:1px solid var(--border-subtle)">
                    <h4 style="font-size:16px;font-weight:800;color:var(--text);margin-bottom:14px">Submit a Review</h4>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="submit_review" value="1">

                        <div class="form-group">
                            <label>Your Rating *</label>
                            <div class="star-picker" style="display:flex;gap:6px;align-items:center">
                                <input type="hidden" name="rating" id="ratingInput" value="5">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <button type="button" class="star-btn" data-value="<?= $i ?>" style="background:none;border:none;cursor:pointer;color:#F59E0B;padding:2px">
                                        <?= icon('star', '', 22) ?>
                                    </button>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reviewComment">Your Feedback *</label>
                            <textarea name="comment" id="reviewComment" rows="4" placeholder="Share your experience using <?= e($t['name']) ?>..." required minlength="15"></textarea>
                        </div>

                        <button type="submit" class="btn-primary" style="padding:10px 24px">
                            <?= icon('send', '', 14) ?>
                            <span>Submit Review</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar (Desktop) -->
    <aside class="tool-sidebar-panel" id="toolSidebarPanel">
        <div class="sidebar-cta-card">
            <h3><?= e($t['name']) ?></h3>
            <p>Visit the official site to explore plans or start a trial.</p>
            <a href="/go.php?slug=<?= e($t['slug']) ?>" class="btn-primary" target="_blank" rel="noopener noreferrer" style="width:100%;padding:12px;font-size:14px">
                <span>Visit <?= e($t['name']) ?></span>
                <?= icon('arrow-up-right', '', 15) ?>
            </a>
            <?php if (!empty($t['commission'])): ?>
                <div style="margin-top:14px;display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:var(--accent);background:var(--accent-light);padding:5px 12px;border-radius:999px">
                    <?= icon('dollar-sign', '', 13) ?>
                    <span><?= e($t['commission']) ?> affiliate program</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="sidebar-facts-card">
            <h4>Software Benchmark Facts</h4>
            <div class="facts-row">
                <span class="facts-key">Category</span>
                <span class="facts-val"><?= e($t['cat_name'] ?? 'General') ?></span>
            </div>
            <div class="facts-row">
                <span class="facts-key">Pricing Model</span>
                <span class="facts-val"><?= ucfirst($t['price_type']) ?></span>
            </div>
            <div class="facts-row">
                <span class="facts-key">Base Starting Fee</span>
                <span class="facts-val"><?= (float)$t['price_from'] > 0 ? '$' . number_format((float)$t['price_from'], 0) . '/mo' : 'Free tier' ?></span>
            </div>
            <div class="facts-row">
                <span class="facts-key">Platform Rating</span>
                <span class="facts-val"><?= number_format((float)$t['rating'], 1) ?> / 5.0</span>
            </div>
            <div class="facts-row">
                <span class="facts-key">Community Votes</span>
                <span class="facts-val"><?= (int)$t['upvotes'] ?></span>
            </div>
            <?php if (!empty($t['website_url'])): ?>
                <div class="facts-row">
                    <span class="facts-key">Domain</span>
                    <a href="<?= e($t['website_url']) ?>" target="_blank" rel="noopener" class="facts-val" style="color:var(--accent)">
                        <?= parse_url($t['website_url'], PHP_URL_HOST) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="sidebar-facts-card" style="font-size:12px;color:var(--muted);line-height:1.55">
            <h4 style="margin-bottom:8px">Affiliate Disclosure</h4>
            <p>We may receive compensation when you click on links and subscribe to software products. This helps support our independent directory vetting process at zero cost to you.</p>
        </div>
    </aside>

</div>

<!-- Sticky Bottom Action Bar for Mobile -->
<div class="mobile-sticky-action-bar">
    <div class="mobile-sticky-action-left">
        <span class="mobile-sticky-tool-name"><?= e($t['name']) ?></span>
        <span class="mobile-sticky-tool-price"><?= (float)$t['price_from'] > 0 ? '$' . number_format((float)$t['price_from'], 0) . '/mo' : ucfirst($t['price_type']) ?></span>
    </div>
    <a href="/go.php?slug=<?= e($t['slug']) ?>" class="mobile-sticky-visit-btn" target="_blank" rel="noopener noreferrer">
        <span>Visit Site</span>
        <?= icon('arrow-up-right', '', 14) ?>
    </a>
</div>

<script>
// Mobile tab switching logic
function switchDetailTab(tab) {
    if (window.innerWidth > 900) return;

    document.querySelectorAll('.mobile-section-tab').forEach(b => {
        b.classList.toggle('active', b.dataset.tab === tab);
    });

    const secOverview = document.getElementById('detailSectionOverview');
    const secPricing = document.getElementById('detailSectionPricingMobile');
    const secReviews = document.getElementById('detailSectionReviews');

    if (secOverview) secOverview.classList.toggle('mobile-hidden', tab !== 'overview');
    if (secPricing) secPricing.classList.toggle('mobile-hidden', tab !== 'pricing');
    if (secReviews) secReviews.classList.toggle('mobile-hidden', tab !== 'reviews');
}

// Initialise mobile tab state on small screens
if (window.innerWidth <= 900) {
    switchDetailTab('overview');
}
window.addEventListener('resize', () => {
    if (window.innerWidth <= 900) {
        const activeTab = document.querySelector('.mobile-section-tab.active')?.dataset.tab || 'overview';
        switchDetailTab(activeTab);
    } else {
        document.querySelectorAll('.detail-section-group').forEach(s => s.classList.remove('mobile-hidden'));
    }
});

<script>
// Star picker logic
document.querySelectorAll('.star-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const val = parseInt(this.dataset.value);
        document.getElementById('ratingInput').value = val;
        document.querySelectorAll('.star-btn').forEach(b => {
            const bVal = parseInt(b.dataset.value);
            b.style.color = bVal <= val ? '#F59E0B' : 'rgba(125,140,128,0.3)';
        });
    });
});
</script>

<?php include __DIR__ . '/../footer.php'; ?>
