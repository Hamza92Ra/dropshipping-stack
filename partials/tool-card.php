<?php
// Expects $t = tool row with cat_name, cat_slug
if (!defined('APP')) die('Direct access not allowed');
require_once __DIR__ . '/../functions.php';

$rating_val = (float)($t['rating'] ?? 0);
$round_rating = (int)round($rating_val);
$price_type = $t['price_type'] ?? 'paid';
$price_label = $price_type === 'freemium'
    ? 'Freemium'
    : ($price_type === 'free' ? 'Free' : 'From $' . number_format((float)($t['price_from'] ?? 0), 0) . '/mo');
$domain = !empty($t['website_url']) ? parse_url($t['website_url'], PHP_URL_HOST) : '';

$hasUpvoted = false;
if (isset($user_upvoted_tool_ids) && is_array($user_upvoted_tool_ids)) {
    $hasUpvoted = !empty($user_upvoted_tool_ids[(int)$t['id']]) || in_array((int)$t['id'], $user_upvoted_tool_ids, true);
} elseif (isset($_SESSION['user_id']) && isset($pdo)) {
    static $user_upvotes_cache = null;
    if ($user_upvotes_cache === null) {
        try {
            $stmt = $pdo->prepare("SELECT tool_id FROM upvotes WHERE user_id = ?");
            $stmt->execute([(int)$_SESSION['user_id']]);
            $user_upvotes_cache = array_fill_keys(array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN)), true);
        } catch (Throwable $e) {
            $user_upvotes_cache = [];
        }
    }
    $hasUpvoted = !empty($user_upvotes_cache[(int)$t['id']]);
}
?>
<div class="tool-card <?= !empty($t['is_featured']) ? 'featured' : '' ?>"
     data-price="<?= e($price_type) ?>"
     data-price-type="<?= e($price_type) ?>"
     data-featured="<?= !empty($t['is_featured']) ? '1' : '0' ?>"
     data-category="<?= (int)($t['category_id'] ?? 0) ?>"
     data-category-slug="<?= e($t['cat_slug'] ?? '') ?>"
     data-name="<?= strtolower(e($t['name'])) ?>">

    <?php if (!empty($t['is_featured']) && empty($hide_featured_badge)): ?>
        <div class="featured-badge">
            <?= icon('sparkles', '', 11) ?>
            <span>Featured</span>
        </div>
    <?php endif; ?>

    <div class="tool-card-top">
        <div class="tool-logo">
            <img src="<?= e(tool_logo_url($t)) ?>"
                 srcset="<?= e(tool_logo_srcset($t)) ?>"
                 alt="<?= e($t['name']) ?>"
                 class="skeleton-img"
                 width="44"
                 height="44"
                 style="width:100%;height:100%;object-fit:contain;border-radius:10px"
                 loading="lazy"
                 decoding="async"
                 onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
        </div>

        <div class="tool-info">
            <h3 class="tool-name"><?= e($t['name']) ?></h3>
            <span class="tool-category"><?= e($t['cat_name'] ?? 'General') ?></span>
        </div>
    </div>

    <p class="tool-tagline"><?= e($t['tagline'] ?? '') ?></p>

    <div class="tool-meta">
        <?php if ($rating_val > 0): ?>
            <div class="tool-rating">
                <span class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span style="display:inline-flex;color:<?= $i <= $round_rating ? '#F59E0B' : 'rgba(125,140,128,0.3)' ?>">
                            <?= icon('star', '', 13) ?>
                        </span>
                    <?php endfor; ?>
                </span>
                <span><?= number_format($rating_val, 1) ?></span>
                <?php if (!empty($t['review_count'])): ?>
                    <span class="review-count">(<?= (int)$t['review_count'] ?>)</span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="tool-unrated-tag">
                <span style="color:var(--accent);display:inline-flex"><?= icon('shield-check', '', 13) ?></span>
                <span>Vetted Tool</span>
            </div>
        <?php endif; ?>
        <span class="price-badge price-<?= e($price_type) ?>"><?= e($price_label) ?></span>
    </div>

    <div class="tool-card-actions">
        <button type="button"
                class="upvote-btn <?= $hasUpvoted ? 'upvoted' : '' ?>"
                data-tool-id="<?= (int)$t['id'] ?>"
                aria-label="Upvote <?= e($t['name']) ?>"
                title="Upvote tool">
            <?= icon('thumbs-up', '', 14) ?>
            <span class="upvote-count"><?= (int)($t['upvotes'] ?? 0) ?></span>
        </button>

        <a href="/tool/index.php?slug=<?= e($t['slug']) ?>" class="btn-secondary">
            Review
        </a>

        <a href="/go.php?slug=<?= e($t['slug']) ?>"
           class="btn-primary"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Visit <?= e($t['name']) ?>">
            <span>Visit Site</span>
            <?= icon('arrow-up-right', '', 14) ?>
        </a>
    </div>
</div>
