<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

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
SELECT
r.*,
u.username
FROM reviews r
LEFT JOIN users u
ON u.id = r.user_id
WHERE r.tool_id = ?
AND r.approved = 1
ORDER BY r.created_at DESC
");

$reviews->execute([$t['id']]);
$reviews = $reviews->fetchAll();

// Handle review submission
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!csrf_verify()) {
        $flash = '<div class="flash flash-error">⚠ Security check failed. Please try again.</div>';
    } else {
        if (!isset($_SESSION['user_id'])) {

            $flash = '<div class="flash flash-error">
    Login required to review tools
    </div>';
        } else {

            $rating = (int)($_POST['rating'] ?? 0);
            $comment = trim($_POST['comment'] ?? '');

            if ($rating >= 1 && $rating <= 5 && $comment) {

                // Check if user already reviewed
                $already = $pdo->prepare("
                SELECT id
                FROM reviews
                WHERE tool_id = ?
                AND user_id = ?
                ");

                $already->execute([
                    $t['id'],
                    $_SESSION['user_id']
                ]);

                if ($already->fetch()) {

                    $flash = '<div class="flash flash-error">
                    You already reviewed this tool.
                    </div>';

                } else {

                    $ins = $pdo->prepare("
                    INSERT INTO reviews
                    (
                        tool_id,
                        user_id,
                        rating,
                        comment,
                        approved
                    )
                    VALUES
                    (
                        ?,?,?,?,1
                    )
                    ");

                    $ins->execute([
                        $t['id'],
                        $_SESSION['user_id'],
                        $rating,
                        $comment
                    ]);

                    // update tool stats
                    $pdo->prepare("
                    UPDATE tools
                    SET
                    review_count =
                    (
                        SELECT COUNT(*)
                        FROM reviews
                        WHERE tool_id = ?
                        AND approved = 1
                    ),

                    rating =
                    (
                        SELECT COALESCE(AVG(rating),0)
                        FROM reviews
                        WHERE tool_id = ?
                        AND approved = 1
                    )

                    WHERE id = ?
                    ")->execute([
                        $t['id'],
                        $t['id'],
                        $t['id']
                    ]);

                    $flash = '<div class="flash flash-success">
                    ✓ Review added successfully
                    </div>';
                }
            }
        }
    }
}

$pros = json_decode($t['pros'] ?? '[]', true) ?: [];
$cons = json_decode($t['cons'] ?? '[]', true) ?: [];
$stars = str_repeat('★', round($t['rating'])) . str_repeat('☆', 5 - round($t['rating']));

$page_title = $t['name'] . ' Review ' . date('Y') . ' — ' . SITE_NAME;
$page_desc  = $t['tagline'];

include __DIR__ . '/../header.php';
?>

<div class="page-wrap">
    <div class="tool-detail-wrap">

        <!-- MAIN CONTENT -->
        <main class="tool-detail-main">

            <!-- Breadcrumb -->
            <nav style="font-size:12px;color:var(--muted);margin-bottom:20px;padding-top:24px">
                <a href="/">Home</a> ›
                <a href="/category/<?= e($t['cat_slug']) ?>"><?= e($t['cat_name']) ?></a> ›
                <?= e($t['name']) ?>
            </nav>

            <!-- Tool Header -->
            <div class="tool-detail-header">
                <div class="tool-logo-lg">
                    <?php if ($t['logo_url']): ?>
                        <img src="<?= e($t['logo_url']) ?>" alt="<?= e($t['name']) ?>">
                    <?php else: ?>
                        <?= e($t['cat_icon'] ?? '🔧') ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="tool-detail-title"><?= e($t['name']) ?></h1>
                    <p class="tool-detail-tagline"><?= e($t['tagline']) ?></p>
                    <div class="tool-detail-meta">
                        <span class="stars" style="font-size:16px"><?= $stars ?></span>
                        <strong><?= number_format($t['rating'], 1) ?></strong>
                        <span class="text-muted">(<?= $t['review_count'] ?> reviews)</span>
                        <span class="price-badge price-<?= e($t['price_type']) ?>">
                            <?= $t['price_type'] === 'freemium' ? 'Freemium' : ($t['price_type'] === 'free' ? 'Free' : 'From $' . number_format($t['price_from'], 0) . '/mo') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="detail-section">
                <h3>About <?= e($t['name']) ?></h3>
                <p class="detail-desc"><?= nl2br(e($t['description'])) ?></p>
            </div>

            <!-- Pros & Cons -->
            <?php if ($pros || $cons): ?>
                <div class="detail-section">
                    <h3>Pros & Cons</h3>
                    <div class="pros-cons-grid">
                        <div>
                            <h4 style="color:var(--green);font-size:14px;margin-bottom:10px">✓ Pros</h4>
                            <ul class="pros-list">
                                <?php foreach ($pros as $p): ?>
                                    <li><?= e($p) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div>
                            <h4 style="color:var(--red);font-size:14px;margin-bottom:10px">✗ Cons</h4>
                            <ul class="cons-list">
                                <?php foreach ($cons as $c): ?>
                                    <li><?= e($c) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reviews -->
            <div class="detail-section">
                <h3>User Reviews (<?= count($reviews) ?>)</h3>

                <?= $flash ?>

                <?php if ($reviews): ?>
                    <?php foreach ($reviews as $r): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <span class="reviewer-name">
                                    <?= e($r['username']) ?>
                                </span>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <span class="stars"><?= str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']) ?></span>
                                    <span class="review-date"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
                                </div>
                            </div>
                            <p class="review-text"><?= nl2br(e($r['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted" style="margin-bottom:20px">No reviews yet. Be the first!</p>
                <?php endif; ?>

                <!-- Review Form -->
                <div class="review-form">
                    <h3>Leave a Review</h3>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="submit_review" value="1">
                    
                        <div class="form-group">
                            <label>Rating *</label>
                            <div class="star-picker">
                                <input type="hidden" name="rating" value="0">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" style="font-size:24px;cursor:pointer;color:#e2e8f0">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Your Review *</label>
                            <textarea name="comment" placeholder="Share your experience with <?= e($t['name']) ?>..." required minlength="20"></textarea>
                        </div>
                        <button type="submit" class="btn-primary" style="max-width:200px">Submit Review</button>
                    </form>
                </div>
            </div>

        </main>

        <!-- SIDEBAR -->
        <aside class="tool-sidebar">
            <div style="position:sticky;top:80px">
                <div class="sidebar-cta">
                    <h3><?= e($t['name']) ?></h3>
                    <p>Click below to visit the official site and start your free trial.</p>
                    <a href="/go/<?= e($t['slug']) ?>" class="btn-visit" target="_blank" rel="noopener noreferrer">
                        Visit <?= e($t['name']) ?> →
                    </a>
                    <?php if ($t['commission']): ?>
                        <div class="commission-badge">
                            💰 <strong><?= e($t['commission']) ?></strong> affiliate commission
                        </div>
                    <?php endif; ?>
                </div>

                <div class="sidebar-info">
                    <h4>Quick Facts</h4>
                    <div class="info-row">
                        <span class="info-key">Category</span>
                        <span class="info-val"><?= e($t['cat_name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Pricing</span>
                        <span class="info-val">
                            <?php if ($t['price_type'] === 'free'): ?>Free
                            <?php elseif ($t['price_type'] === 'freemium'): ?>Freemium
                            <?php else: ?>From $<?= number_format($t['price_from'], 0) ?>/mo<?php endif; ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Rating</span>
                        <span class="info-val"><?= number_format($t['rating'], 1) ?> / 5.0</span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Reviews</span>
                        <span class="info-val"><?= $t['review_count'] ?></span>
                    </div>
                    <?php if ($t['website_url']): ?>
                        <div class="info-row">
                            <span class="info-key">Website</span>
                            <a href="<?= e($t['website_url']) ?>" target="_blank" rel="noopener" class="info-val" style="color:var(--accent)">
                                <?= parse_url($t['website_url'], PHP_URL_HOST) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="sidebar-info">
                    <h4>Affiliate Disclosure</h4>
                    <p style="font-size:12px;color:var(--muted);line-height:1.6">
                        The "Visit Site" button is an affiliate link. We may earn a commission if you sign up — at no extra cost to you.
                        <a href="/affiliate-disclosure.php" style="color:var(--accent)">Learn more</a>
                    </p>
                </div>
            </div>
        </aside>

    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>