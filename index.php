<?php
@define('APP', true);
$show_browse_pills = true;
require_once __DIR__ . '/config.php';

if (empty($_COOKIE['seen_intro'])) {
    setcookie('seen_intro', '1', time() + 60 * 60 * 24 * 365, '/'); // 1 year
    header('Location: /dropshipping/intro.php');
    exit;
}

$page_title = 'Best Dropshipping Tools & Software';
$page_desc  = SITE_TAGLINE;

// Featured tools
$featured = $pdo->query("
    SELECT t.*, c.name AS cat_name, c.slug AS cat_slug
    FROM tools t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.is_featured = 1 AND t.is_active = 1
    ORDER BY t.clicks DESC
")->fetchAll();

// All tools grouped by category
$all_tools = $pdo->query("
    SELECT t.*, c.name AS cat_name, c.slug AS cat_slug, c.icon AS cat_icon
    FROM tools t
    LEFT JOIN categories c ON c.id = t.category_id
    WHERE t.is_active = 1
    ORDER BY c.sort_order, t.rating DESC
")->fetchAll();

// Total counts for hero
$total_tools     = $pdo->query("SELECT COUNT(*) FROM tools WHERE is_active=1")->fetchColumn();
$total_cats      = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_reviews   = $pdo->query("SELECT COUNT(*) FROM reviews WHERE approved=1")->fetchColumn();

include __DIR__ . '/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <h1>Every tool you need to<br><span>crush dropshipping</span></h1>
        <p>Honest reviews and affiliate links for <?= $total_tools ?>+ dropshipping tools — vetted so you don't waste money on the wrong software.</p>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-val"><?= $total_tools ?>+</span>
                <div class="hero-stat-label">Tools Reviewed</div>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val"><?= $total_cats ?></span>
                <div class="hero-stat-label">Categories</div>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val"><?= $total_reviews ?>+</span>
                <div class="hero-stat-label">User Reviews</div>
            </div>
        </div>
    </div>
</section>

<div class="page-wrap">

    <!-- FEATURED -->
    <?php if ($featured): ?>
    <div class="section-head">
        <h2 class="section-title">⭐ Featured Tools</h2>
    </div>
    <div class="tools-grid">
        <?php foreach ($featured as $t): ?>
            <?php include __DIR__ . '/partials/tool-card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ALL TOOLS WITH FILTERS -->
    <div class="section-head">
        <h2 class="section-title">🔧 All Tools</h2>
    </div>

    <div class="filters-bar">
        <span class="filter-label">Filter:</span>
        <button class="filter-btn active" onclick="filterTools('all')">All</button>
        <button class="filter-btn" onclick="filterTools('free')">Free / Freemium</button>
        <button class="filter-btn" onclick="filterTools('paid')">Paid</button>
        <button class="filter-btn" onclick="filterTools('featured')">Featured</button>
    </div>

    <div class="tools-grid">
        <?php foreach ($all_tools as $t): ?>
            <?php include __DIR__ . '/partials/tool-card.php'; ?>
        <?php endforeach; ?>
    </div>

    <div style="height:40px"></div>
</div>
<script>
document.querySelectorAll('.upvote-btn').forEach(btn=>{

    btn.addEventListener('click',async function(){

        const toolId=this.dataset.toolId;

        const res=await fetch(
            '/dropshipping/upvote.php',
            {
                method:'POST',
                headers:{
                    'Content-Type':'application/x-www-form-urlencoded'
                },
                body:'tool_id='+toolId
            }
        );

        const data=await res.json();

        if(data.error==="login_required"){
            window.location="/dropshipping/login.php";
            return;
        }

        this.querySelector(
            '.upvote-count'
        ).textContent=data.count;

        this.classList.toggle(
            'upvoted',
            data.action==='added'
        );

    });

});
</script>
<?php include __DIR__ . '/footer.php'; ?>
