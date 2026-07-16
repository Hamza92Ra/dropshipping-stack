    <?php
    // Expects $t = tool row with cat_name, cat_slug
    $stars = str_repeat('★', round($t['rating'])) . str_repeat('☆', 5 - round($t['rating']));
    $price_label = $t['price_type'] === 'freemium' ? 'Freemium' : ($t['price_type'] === 'free' ? 'Free' : 'From $' . number_format($t['price_from'], 0) . '/mo');
    $domain = $t['website_url'] ? parse_url($t['website_url'], PHP_URL_HOST) : '';
    ?>
    <div class="tool-card <?= $t['is_featured'] ? 'featured' : '' ?>"
        data-price="<?= e($t['price_type']) ?>"
        data-featured="<?= $t['is_featured'] ?>">

        <?php if ($t['is_featured']): ?>
            <div class="featured-badge">⭐ Featured</div>
        <?php endif; ?>

        <div class="tool-card-top">
            <div class="tool-logo">
                <?php if ($t['logo_url']): ?>
                    <img src="<?= e($t['logo_url']) ?>"
                        alt="<?= e($t['name']) ?>"
                        style="width:100%;height:100%;object-fit:contain;border-radius:8px;padding:4px"
                        onerror="this.src='https://logo.clearbit.com/<?= e($domain) ?>'">
                <?php elseif ($domain): ?>
                    <img src="https://logo.clearbit.com/<?= e($domain) ?>"
                        alt="<?= e($t['name']) ?>"
                        style="width:100%;height:100%;object-fit:contain;border-radius:8px;padding:4px"
                        onerror="this.style.display='none';this.nextSibling.style.display='block'">
                    <span style="display:none;font-size:22px;text-align:center;width:100%"><?= e($t['cat_icon'] ?? '🔧') ?></span>
                <?php else: ?>
                    <span style="font-size:22px"><?= e($t['cat_icon'] ?? '🔧') ?></span>
                <?php endif; ?>
            </div>
            <div class="tool-info">
                <div class="tool-name"><?= e($t['name']) ?></div>
                <div class="tool-category"><?= e($t['cat_name'] ?? '') ?></div>
            </div>
        </div>

        <p class="tool-tagline"><?= e($t['tagline']) ?></p>

        <div class="tool-meta">
            <div class="tool-rating">
                <span class="stars"><?= $stars ?></span>
                <span><?= number_format($t['rating'], 1) ?></span>
                <span class="review-count">(<?= $t['review_count'] ?>)</span>
            </div>
            <span class="price-badge price-<?= e($t['price_type']) ?>"><?= e($price_label) ?></span>
        </div>

        <?php if ($t['commission']): ?>
            <div class="commission-line">
                💰 Commission: <?= e($t['commission']) ?>
            </div>
        <?php endif; ?>

        <div class="tool-card-actions">
            <?php
            $hasUpvoted = false;

            if (isset($_SESSION['user_id'])) {

                $check = $pdo->prepare("
        SELECT id
        FROM upvotes
        WHERE user_id=?
        AND tool_id=?
    ");

                $check->execute([
                    $_SESSION['user_id'],
                    $t['id']
                ]);

                $hasUpvoted = $check->fetch() ? true : false;
            }
            ?>

            <button
                class="upvote-btn <?= $hasUpvoted ? 'upvoted' : '' ?>"
                data-tool-id="<?= $t['id'] ?>"

                style="display:inline-flex;
align-items:center;
gap:6px;
padding:8px 14px;
background:rgba(255,255,255,0.05);
border:1px solid rgba(255,255,255,0.1);
border-radius:8px;
color:rgba(255,255,255,0.6);
font-size:13px;
font-weight:600;
cursor:pointer;
transition:all 0.15s">

                👍
                <span class="upvote-count">
                    <?= (int)$t['upvotes'] ?>
                </span>

            </button>
            <a href="/dropshipping/tool/index.php?slug=<?= e($t['slug']) ?>" class="btn-secondary">Review</a>
            <a href="/dropshipping/go.php?slug=<?= e($t['slug']) ?>" class="btn-primary" target="_blank" rel="noopener noreferrer">Visit Site →</a>
        </div>
    </div>