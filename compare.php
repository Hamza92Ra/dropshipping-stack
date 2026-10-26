<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Get tools for comparison from URL params
$slug1 = preg_replace('/[^a-z0-9-]/', '', $_GET['a'] ?? '');
$slug2 = preg_replace('/[^a-z0-9-]/', '', $_GET['b'] ?? '');

$tool1 = $tool2 = null;

if ($slug1 && $slug2) {
    $stmt = $pdo->prepare("SELECT t.*, c.name AS cat_name, c.slug AS cat_slug FROM tools t LEFT JOIN categories c ON c.id = t.category_id WHERE t.slug = ? AND t.is_active = 1");
    $stmt->execute([$slug1]);
    $tool1 = $stmt->fetch();

    $stmt->execute([$slug2]);
    $tool2 = $stmt->fetch();
}

// All tools for the selector
$all_tools = $pdo->query("SELECT name, slug FROM tools WHERE is_active=1 ORDER BY name")->fetchAll();

$page_title      = $tool1 && $tool2
    ? $tool1['name'] . ' vs ' . $tool2['name'] . ' — Side by Side Comparison'
    : 'Compare Dropshipping Tools Side-by-Side';
$page_heading    = $tool1 && $tool2 ? e($tool1['name']) . ' vs ' . e($tool2['name']) : 'Compare Tools';
$page_subheading = 'Detailed side-by-side feature, pricing, and pros/cons analysis';
$page_desc       = $tool1 && $tool2
    ? 'Side-by-side comparison of ' . $tool1['name'] . ' and ' . $tool2['name'] . ' — features, pricing, pros and cons.'
    : 'Compare the best dropshipping tools side by side.';

include __DIR__ . '/header.php';

// Popular comparisons
$popular = [
    ['Shopify vs WooCommerce', 'shopify', 'woocommerce'],
    ['Shopify vs BigCommerce', 'shopify', 'bigcommerce'],
    ['Zendrop vs Spocket', 'zendrop', 'spocket'],
    ['Klaviyo vs Omnisend', 'klaviyo', 'omnisend'],
    ['Minea vs AdSpy', 'minea', 'adspy'],
    ['AutoDS vs DSers', 'autods', 'dsers'],
];
?>

<style>
.compare-wrap {
    max-width: 1080px;
    margin: 0 auto;
    padding: 10px 0 60px;
}

.compare-hero {
    text-align: center;
    margin-bottom: 36px;
}

.compare-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--accent-light);
    color: var(--accent);
    font-size: 12px;
    font-weight: 700;
    padding: 5px 16px;
    border-radius: var(--radius-full);
    margin-bottom: 14px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.compare-hero h1 {
    font-size: clamp(26px, 4vw, 40px);
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.025em;
    margin-bottom: 10px;
}

.compare-hero h1 span {
    color: var(--accent);
}

.compare-hero p {
    font-size: 15px;
    color: var(--muted);
    max-width: 500px;
    margin: 0 auto;
}

/* Selector Card */
.compare-selector {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 28px 32px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 32px;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 20px;
    align-items: end;
}

.compare-selector label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted);
    display: block;
    margin-bottom: 8px;
}

.compare-selector select {
    width: 100%;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 14px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    background: var(--surface-2);
    outline: none;
    cursor: pointer;
    transition: border-color var(--dur-fast) var(--ease);
}

.compare-selector select:focus {
    border-color: var(--accent);
    background: var(--surface);
}

.vs-badge {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 800;
    flex-shrink: 0;
    align-self: flex-end;
    margin-bottom: 2px;
}

.compare-btn {
    grid-column: 1 / -1;
    width: fit-content;
    margin: 10px auto 0;
    padding: 11px 28px;
    font-size: 14px;
}

/* Popular comparisons */
.popular-section {
    margin-bottom: 36px;
}

.popular-title {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 12px;
}

.popular-grid {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.popular-link {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 8px 16px;
    border-radius: var(--radius-full);
    font-size: 13px;
    font-weight: 600;
    color: var(--body);
    transition: all var(--dur-fast) var(--ease);
    box-shadow: var(--shadow-xs);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.popular-link:hover {
    border-color: var(--accent);
    color: var(--accent);
    background: var(--accent-light);
    transform: translateY(-1px);
}

/* Comparison Table */
.compare-table-wrap {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: var(--shadow-sm);
}

.compare-header {
    display: grid;
    grid-template-columns: 220px 1fr 1fr;
    background: var(--surface-2);
    border-bottom: 1px solid var(--border);
}

.compare-header-cell {
    padding: 24px 20px;
    text-align: center;
    border-left: 1px solid var(--border);
}

.compare-header-cell:first-child {
    border-left: none;
    text-align: left;
    display: flex;
    align-items: center;
}

.compare-tool-name {
    font-size: 19px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 4px;
    letter-spacing: -0.015em;
}

.compare-tool-cat {
    font-size: 12px;
    color: var(--accent);
    font-weight: 600;
}

.compare-row {
    display: grid;
    grid-template-columns: 220px 1fr 1fr;
    border-top: 1px solid var(--border);
}

.compare-row:nth-child(even) {
    background: var(--surface-2);
}

.compare-row-label {
    padding: 16px 22px;
    font-size: 13px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: flex;
    align-items: center;
}

.compare-cell {
    padding: 16px 20px;
    border-left: 1px solid var(--border);
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.compare-cell.winner {
    background: var(--positive-bg);
    color: var(--positive-text);
    font-weight: 700;
}

.compare-cell.loser {
    color: var(--muted);
}

/* Verdict */
.verdict-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 28px;
}

.verdict-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 28px;
    box-shadow: var(--shadow-sm);
}

.verdict-card h3 {
    font-size: 17px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.verdict-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
    font-size: 13.5px;
    color: var(--body);
    line-height: 1.5;
}

.verdict-icon {
    flex-shrink: 0;
    margin-top: 2px;
}

.verdict-icon.pro {
    color: var(--positive);
}

.verdict-icon.con {
    color: var(--negative);
}

/* Bottom CTA Banner */
.cta-banner {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 32px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    box-shadow: var(--shadow-sm);
}

.cta-tool {
    text-align: center;
    padding: 24px;
    background: var(--surface-2);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
}

.cta-tool-name {
    font-size: 19px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 4px;
}

.cta-tool-price {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 18px;
}

.empty-compare {
    text-align: center;
    padding: 64px 24px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    color: var(--muted);
}

.empty-compare-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--accent-light);
    color: var(--accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
}

@media (max-width: 768px) {
    .compare-selector {
        grid-template-columns: 1fr;
        padding: 20px;
    }
    .vs-badge {
        align-self: center;
        margin: 0 auto;
    }
    .compare-header,
    .compare-row {
        grid-template-columns: 110px 1fr 1fr;
    }
    .compare-row-label,
    .compare-cell {
        padding: 12px 10px;
        font-size: 12px;
    }
    .verdict-grid,
    .cta-banner {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="compare-wrap">

    <div class="compare-hero">
        <div class="compare-badge">
            <?= icon('scale', '', 14) ?>
            <span>Side-by-Side Evaluator</span>
        </div>
        <?php if ($tool1 && $tool2): ?>
            <h1><?= e($tool1['name']) ?> <span>vs</span> <?= e($tool2['name']) ?></h1>
            <p>Direct comparison of feature architecture, monthly pricing, reviews, and verified pros/cons.</p>
        <?php else: ?>
            <h1>Compare <span>Dropshipping Tools</span></h1>
            <p>Select any two tools to analyze their core differences, pricing tiers, and community reviews.</p>
        <?php endif; ?>
    </div>

    <!-- Tool Selector Form -->
    <form class="compare-selector" method="GET" action="/compare.php">
        <div>
            <label for="selectA">First Tool</label>
            <select name="a" id="selectA" required>
                <option value="">Select first software...</option>
                <?php foreach ($all_tools as $t): ?>
                    <option value="<?= e($t['slug']) ?>" <?= $t['slug'] === $slug1 ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="vs-badge">VS</div>

        <div>
            <label for="selectB">Second Tool</label>
            <select name="b" id="selectB" required>
                <option value="">Select second software...</option>
                <?php foreach ($all_tools as $t): ?>
                    <option value="<?= e($t['slug']) ?>" <?= $t['slug'] === $slug2 ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn-primary compare-btn">
            <?= icon('scale', '', 15) ?>
            <span>Compare Now</span>
        </button>
    </form>

    <!-- Popular Comparisons -->
    <div class="popular-section">
        <div class="popular-title">Frequently Compared Stacks</div>
        <div class="popular-grid">
            <?php foreach ($popular as $p): ?>
                <a href="/compare.php?a=<?= urlencode($p[1]) ?>&b=<?= urlencode($p[2]) ?>" class="popular-link">
                    <?= icon('arrow-left-right', '', 13) ?>
                    <span><?= e($p[0]) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($tool1 && $tool2): ?>
        <?php
        $pros1 = json_decode($tool1['pros'] ?? '[]', true) ?: [];
        $cons1 = json_decode($tool1['cons'] ?? '[]', true) ?: [];
        $pros2 = json_decode($tool2['pros'] ?? '[]', true) ?: [];
        $cons2 = json_decode($tool2['cons'] ?? '[]', true) ?: [];

        $price1 = $tool1['price_type'] === 'free' ? 'Free' : ($tool1['price_type'] === 'freemium' ? 'Freemium' : 'From $' . number_format((float)$tool1['price_from'], 0) . '/mo');
        $price2 = $tool2['price_type'] === 'free' ? 'Free' : ($tool2['price_type'] === 'freemium' ? 'Freemium' : 'From $' . number_format((float)$tool2['price_from'], 0) . '/mo');

        $rating1_winner = $tool1['rating'] >= $tool2['rating'];
        $rating2_winner = $tool2['rating'] >= $tool1['rating'];
        $price1_winner  = (float)$tool1['price_from'] <= (float)$tool2['price_from'];
        $price2_winner  = (float)$tool2['price_from'] <= (float)$tool1['price_from'];
        ?>

        <!-- Comparison Table -->
        <div class="compare-table-wrap">
            <div class="compare-header">
                <div class="compare-header-cell">
                    <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--muted)">Metric</span>
                </div>
                <div class="compare-header-cell">
                    <div class="compare-tool-name"><?= e($tool1['name']) ?></div>
                    <div class="compare-tool-cat"><?= e($tool1['cat_name'] ?? 'Tool') ?></div>
                </div>
                <div class="compare-header-cell">
                    <div class="compare-tool-name"><?= e($tool2['name']) ?></div>
                    <div class="compare-tool-cat"><?= e($tool2['cat_name'] ?? 'Tool') ?></div>
                </div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Rating</div>
                <div class="compare-cell <?= $rating1_winner ? 'winner' : '' ?>">
                    <span style="color:#F59E0B;display:inline-flex;margin-right:4px"><?= icon('star', '', 14) ?></span>
                    <?= number_format((float)$tool1['rating'], 1) ?> / 5.0
                </div>
                <div class="compare-cell <?= $rating2_winner ? 'winner' : '' ?>">
                    <span style="color:#F59E0B;display:inline-flex;margin-right:4px"><?= icon('star', '', 14) ?></span>
                    <?= number_format((float)$tool2['rating'], 1) ?> / 5.0
                </div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Reviews</div>
                <div class="compare-cell <?= $tool1['review_count'] >= $tool2['review_count'] ? 'winner' : '' ?>">
                    <?= (int)$tool1['review_count'] ?> verified
                </div>
                <div class="compare-cell <?= $tool2['review_count'] >= $tool1['review_count'] ? 'winner' : '' ?>">
                    <?= (int)$tool2['review_count'] ?> verified
                </div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Pricing</div>
                <div class="compare-cell <?= $price1_winner ? 'winner' : '' ?>"><?= e($price1) ?></div>
                <div class="compare-cell <?= $price2_winner ? 'winner' : '' ?>"><?= e($price2) ?></div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Free Tier</div>
                <div class="compare-cell">
                    <?= in_array($tool1['price_type'], ['free','freemium'], true)
                        ? '<span style="color:var(--positive)">' . icon('check', '', 18) . '</span>'
                        : '<span style="color:var(--muted)">' . icon('x', '', 16) . '</span>' ?>
                </div>
                <div class="compare-cell">
                    <?= in_array($tool2['price_type'], ['free','freemium'], true)
                        ? '<span style="color:var(--positive)">' . icon('check', '', 18) . '</span>'
                        : '<span style="color:var(--muted)">' . icon('x', '', 16) . '</span>' ?>
                </div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Category</div>
                <div class="compare-cell"><?= e($tool1['cat_name'] ?? 'General') ?></div>
                <div class="compare-cell"><?= e($tool2['cat_name'] ?? 'General') ?></div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Commission</div>
                <div class="compare-cell"><?= e($tool1['commission'] ?: 'Standard') ?></div>
                <div class="compare-cell"><?= e($tool2['commission'] ?: 'Standard') ?></div>
            </div>
        </div>

        <!-- Pros and Cons Side-by-Side -->
        <div class="verdict-grid">
            <div class="verdict-card">
                <h3>
                    <?= icon('package', '', 18) ?>
                    <span><?= e($tool1['name']) ?> Highlights</span>
                </h3>
                <?php foreach (array_slice($pros1, 0, 4) as $p): ?>
                    <div class="verdict-item">
                        <span class="verdict-icon pro"><?= icon('check', '', 15) ?></span>
                        <span><?= e($p) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php foreach (array_slice($cons1, 0, 2) as $c): ?>
                    <div class="verdict-item">
                        <span class="verdict-icon con"><?= icon('x', '', 15) ?></span>
                        <span><?= e($c) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="verdict-card">
                <h3>
                    <?= icon('package', '', 18) ?>
                    <span><?= e($tool2['name']) ?> Highlights</span>
                </h3>
                <?php foreach (array_slice($pros2, 0, 4) as $p): ?>
                    <div class="verdict-item">
                        <span class="verdict-icon pro"><?= icon('check', '', 15) ?></span>
                        <span><?= e($p) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php foreach (array_slice($cons2, 0, 2) as $c): ?>
                    <div class="verdict-item">
                        <span class="verdict-icon con"><?= icon('x', '', 15) ?></span>
                        <span><?= e($c) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA Banners -->
        <div class="cta-banner">
            <div class="cta-tool">
                <div class="cta-tool-name"><?= e($tool1['name']) ?></div>
                <div class="cta-tool-price"><?= e($price1) ?></div>
                <a href="/go.php?slug=<?= e($tool1['slug']) ?>" class="btn-primary" target="_blank" rel="noopener noreferrer" style="margin:0 auto;display:inline-flex;padding:10px 24px">
                    <span>Try <?= e($tool1['name']) ?></span>
                    <?= icon('arrow-up-right', '', 14) ?>
                </a>
            </div>

            <div class="cta-tool">
                <div class="cta-tool-name"><?= e($tool2['name']) ?></div>
                <div class="cta-tool-price"><?= e($price2) ?></div>
                <a href="/go.php?slug=<?= e($tool2['slug']) ?>" class="btn-primary" target="_blank" rel="noopener noreferrer" style="margin:0 auto;display:inline-flex;padding:10px 24px">
                    <span>Try <?= e($tool2['name']) ?></span>
                    <?= icon('arrow-up-right', '', 14) ?>
                </a>
            </div>
        </div>

    <?php else: ?>

        <div class="empty-compare">
            <div class="empty-compare-icon">
                <?= icon('scale', '', 28) ?>
            </div>
            <h2 style="font-size:20px;font-weight:800;color:var(--text);margin-bottom:8px">Select two tools above to begin comparison</h2>
            <p style="font-size:14px;color:var(--muted)">Or choose one of the pre-configured comparisons from the frequently compared list.</p>
        </div>

    <?php endif; ?>

</div>

<?php include __DIR__ . '/footer.php'; ?>
