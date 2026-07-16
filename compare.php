    <?php
    @define('APP', true);
    require_once __DIR__ . '/config.php';

    // Get tools for comparison from URL params
    $slug1 = preg_replace('/[^a-z0-9-]/', '', $_GET['a'] ?? '');
    $slug2 = preg_replace('/[^a-z0-9-]/', '', $_GET['b'] ?? '');

    $tool1 = $tool2 = null;

    if ($slug1 && $slug2) {
        $stmt = $pdo->prepare("SELECT t.*, c.name AS cat_name FROM tools t LEFT JOIN categories c ON c.id = t.category_id WHERE t.slug = ? AND t.is_active = 1");
        $stmt->execute([$slug1]);
        $tool1 = $stmt->fetch();

        $stmt->execute([$slug2]);
        $tool2 = $stmt->fetch();
    }

    // All tools for the selector
    $all_tools = $pdo->query("SELECT name, slug FROM tools WHERE is_active=1 ORDER BY name")->fetchAll();

    $page_title = $tool1 && $tool2
        ? $tool1['name'] . ' vs ' . $tool2['name'] . ' — Which is Better?'
        : 'Compare Dropshipping Tools';
    $page_desc = $tool1 && $tool2
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
    .compare-wrap { max-width: 1100px; margin: 0 auto; padding: 40px 24px 80px; }

    .compare-hero { text-align: center; margin-bottom: 40px; }
    .compare-hero h1 { font-size: clamp(24px, 4vw, 40px); font-weight: 800; color: var(--ink); letter-spacing: -0.025em; margin-bottom: 10px; }
    .compare-hero h1 span { color: var(--accent); }
    .compare-hero p { font-size: 15px; color: var(--muted); }

    .compare-selector {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px;
        margin-bottom: 32px;
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 16px;
        align-items: end;
    }

    .compare-selector label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--muted); display: block; margin-bottom: 6px; }
    .compare-selector select { width: 100%; border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 10px 12px; font-family: var(--font); font-size: 14px; color: var(--ink); background: var(--bg); outline: none; cursor: pointer; }
    .compare-selector select:focus { border-color: var(--accent); }

    .vs-badge {
        width: 44px; height: 44px; border-radius: 50%;
        background: var(--dark); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 800;
        flex-shrink: 0; align-self: flex-end; margin-bottom: 2px;
    }

    .compare-btn {
        background: var(--accent); color: #fff; border: none;
        padding: 11px 24px; border-radius: var(--radius-sm);
        font-family: var(--font); font-size: 14px; font-weight: 700;
        cursor: pointer; transition: background 0.15s; margin-top: 8px;
        grid-column: 1 / -1; width: fit-content; margin: 8px auto 0;
    }
    .compare-btn:hover { background: var(--accent-h); }

    /* Popular comparisons */
    .popular-section { margin-bottom: 36px; }
    .popular-title { font-size: 13px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; }
    .popular-grid { display: flex; gap: 8px; flex-wrap: wrap; }
    .popular-link {
        background: var(--surface); border: 1px solid var(--border);
        padding: 7px 14px; border-radius: 20px;
        font-size: 13px; font-weight: 600; color: var(--body);
        transition: all 0.15s; white-space: nowrap;
    }
    .popular-link:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }

    /* Comparison table */
    .compare-table-wrap {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .compare-header {
        display: grid;
        grid-template-columns: 200px 1fr 1fr;
        background: var(--dark);
        color: #fff;
    }

    .compare-header-cell {
        padding: 24px 20px;
        text-align: center;
        border-left: 1px solid var(--dark-border);
    }
    .compare-header-cell:first-child { border-left: none; text-align: left; }

    .compare-tool-name { font-size: 18px; font-weight: 800; margin-bottom: 4px; }
    .compare-tool-cat { font-size: 11px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.06em; }

    .compare-row {
        display: grid;
        grid-template-columns: 200px 1fr 1fr;
        border-top: 1px solid var(--border);
    }
    .compare-row:nth-child(even) { background: var(--bg); }

    .compare-row-label {
        padding: 16px 20px;
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
        color: var(--body);
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .compare-cell.winner { background: #f0fdf4; font-weight: 700; color: var(--green); }
    .compare-cell.loser { color: var(--muted); }

    .check { color: var(--green); font-size: 18px; }
    .cross { color: var(--red); font-size: 18px; }

    .stars-sm { color: var(--featured); font-size: 13px; }

    /* Verdict */
    .verdict-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
    .verdict-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
    }
    .verdict-card h3 { font-size: 16px; font-weight: 800; color: var(--ink); margin-bottom: 14px; }
    .verdict-item { display: flex; gap: 10px; margin-bottom: 10px; font-size: 13px; color: var(--body); line-height: 1.5; }
    .verdict-item::before { content: '✓'; color: var(--green); font-weight: 700; flex-shrink: 0; }
    .verdict-item.con::before { content: '✗'; color: var(--red); }

    .cta-banner {
        background: var(--dark);
        border-radius: var(--radius);
        padding: 32px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .cta-tool { text-align: center; padding: 20px; background: rgba(255,255,255,0.04); border: 1px solid var(--dark-border); border-radius: var(--radius-sm); }
    .cta-tool-name { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 6px; }
    .cta-tool-price { font-size: 13px; color: rgba(255,255,255,0.4); margin-bottom: 16px; }
    .cta-tool-btn { display: inline-block; background: var(--accent); color: #fff; padding: 10px 20px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 700; transition: background 0.15s; }
    .cta-tool-btn:hover { background: var(--accent-h); }

    .empty-compare { text-align: center; padding: 60px 24px; color: var(--muted); }
    .empty-compare .icon { font-size: 56px; margin-bottom: 14px; }
    .empty-compare h2 { font-size: 20px; font-weight: 700; color: var(--ink); margin-bottom: 8px; }
    </style>

    <div class="compare-wrap">

        <div class="compare-hero">
            <?php if ($tool1 && $tool2): ?>
                <h1><?= e($tool1['name']) ?> <span>vs</span> <?= e($tool2['name']) ?></h1>
                <p>Side-by-side comparison to help you choose the right tool for your store.</p>
            <?php else: ?>
                <h1>Compare <span>Dropshipping Tools</span></h1>
                <p>Pick any two tools and see them side by side — features, pricing, pros and cons.</p>
            <?php endif; ?>
        </div>

        <!-- Selector -->
        <form class="compare-selector" method="GET">
            <div>
                <label>First Tool</label>
                <select name="a">
                    <option value="">Select a tool...</option>
                    <?php foreach ($all_tools as $t): ?>
                        <option value="<?= e($t['slug']) ?>" <?= $t['slug'] === $slug1 ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="vs-badge">VS</div>
            <div>
                <label>Second Tool</label>
                <select name="b">
                    <option value="">Select a tool...</option>
                    <?php foreach ($all_tools as $t): ?>
                        <option value="<?= e($t['slug']) ?>" <?= $t['slug'] === $slug2 ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="compare-btn">Compare Now →</button>
        </form>

        <!-- Popular -->
        <div class="popular-section">
            <div class="popular-title">Popular Comparisons</div>
            <div class="popular-grid">
                <?php foreach ($popular as $p): ?>
                    <a href="http://localhost/dropshipping/compare.php?a=<?= $p[1] ?>&b=<?= $p[2] ?>" class="popular-link"><?= $p[0] ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($tool1 && $tool2): ?>

        <?php
        $pros1 = json_decode($tool1['pros'] ?? '[]', true) ?: [];
        $cons1 = json_decode($tool1['cons'] ?? '[]', true) ?: [];
        $pros2 = json_decode($tool2['pros'] ?? '[]', true) ?: [];
        $cons2 = json_decode($tool2['cons'] ?? '[]', true) ?: [];

        $price1 = $tool1['price_type'] === 'free' ? 'Free' : ($tool1['price_type'] === 'freemium' ? 'Freemium' : 'From $' . number_format($tool1['price_from'], 0) . '/mo');
        $price2 = $tool2['price_type'] === 'free' ? 'Free' : ($tool2['price_type'] === 'freemium' ? 'Freemium' : 'From $' . number_format($tool2['price_from'], 0) . '/mo');

        $rating1_winner = $tool1['rating'] >= $tool2['rating'];
        $rating2_winner = $tool2['rating'] >= $tool1['rating'];
        $price1_winner = $tool1['price_from'] <= $tool2['price_from'];
        $price2_winner = $tool2['price_from'] <= $tool1['price_from'];
        ?>

        <!-- Comparison Table -->
        <div class="compare-table-wrap">
            <div class="compare-header">
                <div class="compare-header-cell" style="display:flex;align-items:center">
                    <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.4)">Comparison</span>
                </div>
                <div class="compare-header-cell">
                    <div style="font-size:32px;margin-bottom:8px"><?= e($tool1['cat_icon'] ?? '🔧') ?></div>
                    <div class="compare-tool-name"><?= e($tool1['name']) ?></div>
                    <div class="compare-tool-cat"><?= e($tool1['cat_name']) ?></div>
                </div>
                <div class="compare-header-cell">
                    <div style="font-size:32px;margin-bottom:8px"><?= e($tool2['cat_icon'] ?? '🔧') ?></div>
                    <div class="compare-tool-name"><?= e($tool2['name']) ?></div>
                    <div class="compare-tool-cat"><?= e($tool2['cat_name']) ?></div>
                </div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Rating</div>
                <div class="compare-cell <?= $rating1_winner ? 'winner' : '' ?>">
                    <span class="stars-sm"><?= str_repeat('★', round($tool1['rating'])) ?></span>&nbsp;<?= number_format($tool1['rating'], 1) ?>
                </div>
                <div class="compare-cell <?= $rating2_winner ? 'winner' : '' ?>">
                    <span class="stars-sm"><?= str_repeat('★', round($tool2['rating'])) ?></span>&nbsp;<?= number_format($tool2['rating'], 1) ?>
                </div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Reviews</div>
                <div class="compare-cell <?= $tool1['review_count'] >= $tool2['review_count'] ? 'winner' : '' ?>"><?= $tool1['review_count'] ?> reviews</div>
                <div class="compare-cell <?= $tool2['review_count'] >= $tool1['review_count'] ? 'winner' : '' ?>"><?= $tool2['review_count'] ?> reviews</div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Pricing</div>
                <div class="compare-cell <?= $price1_winner ? 'winner' : '' ?>"><?= e($price1) ?></div>
                <div class="compare-cell <?= $price2_winner ? 'winner' : '' ?>"><?= e($price2) ?></div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Free Plan</div>
                <div class="compare-cell"><?= in_array($tool1['price_type'], ['free','freemium']) ? '<span class="check">✓</span>' : '<span class="cross">✗</span>' ?></div>
                <div class="compare-cell"><?= in_array($tool2['price_type'], ['free','freemium']) ? '<span class="check">✓</span>' : '<span class="cross">✗</span>' ?></div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Category</div>
                <div class="compare-cell"><?= e($tool1['cat_name']) ?></div>
                <div class="compare-cell"><?= e($tool2['cat_name']) ?></div>
            </div>

            <div class="compare-row">
                <div class="compare-row-label">Commission</div>
                <div class="compare-cell"><?= e($tool1['commission'] ?: '—') ?></div>
                <div class="compare-cell"><?= e($tool2['commission'] ?: '—') ?></div>
            </div>
        </div>

        <!-- Pros/Cons -->
        <div class="verdict-grid">
            <div class="verdict-card">
                <h3><?= e($tool1['name']) ?></h3>
                <?php foreach (array_slice($pros1, 0, 4) as $p): ?>
                    <div class="verdict-item"><?= e($p) ?></div>
                <?php endforeach; ?>
                <?php foreach (array_slice($cons1, 0, 2) as $c): ?>
                    <div class="verdict-item con"><?= e($c) ?></div>
                <?php endforeach; ?>
            </div>
            <div class="verdict-card">
                <h3><?= e($tool2['name']) ?></h3>
                <?php foreach (array_slice($pros2, 0, 4) as $p): ?>
                    <div class="verdict-item"><?= e($p) ?></div>
                <?php endforeach; ?>
                <?php foreach (array_slice($cons2, 0, 2) as $c): ?>
                    <div class="verdict-item con"><?= e($c) ?></div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- CTA -->
        <div class="cta-banner">
            <div class="cta-tool">
                <div class="cta-tool-name"><?= e($tool1['name']) ?></div>
                <div class="cta-tool-price"><?= e($price1) ?></div>
                <a href="http://localhost/dropshipping/go.php?slug=<?= e($tool1['slug']) ?>" class="cta-tool-btn" target="_blank">Try <?= e($tool1['name']) ?> →</a>
                <button class="bookmark-btn" data-tool-id="<?= $tool1['id'] ?>">🤍 Save</button>
            </div>
            <div class="cta-tool">
                <div class="cta-tool-name"><?= e($tool2['name']) ?></div>
                <div class="cta-tool-price"><?= e($price2) ?></div>
                <a href="http://localhost/dropshipping/go.php?slug=<?= e($tool2['slug']) ?>" class="cta-tool-btn" target="_blank">Try <?= e($tool2['name']) ?> →</a>
                <button class="bookmark-btn" data-tool-id="<?= $tool2['id'] ?>">🤍 Save</button>
            </div>
        </div>

        <?php else: ?>

        <div class="empty-compare">
            <div class="icon">⚖️</div>
            <h2>Pick two tools above to compare</h2>
            <p>Or click one of the popular comparisons to get started instantly.</p>
        </div>

        <?php endif; ?>

    </div>
    <script src="/assets/js/bookmarks.js"></script>
    <script src="/assets/js/darkmode.js"></script>
    <?php include __DIR__ . '/footer.php'; ?>
