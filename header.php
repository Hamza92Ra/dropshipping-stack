<?php
if (!defined('APP')) die('Direct access not allowed');
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Fetch all categories for nav
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? SITE_NAME) ?> — <?= e(SITE_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
    <meta name="description" content="<?= e($page_desc ?? SITE_TAGLINE) ?>">
    <meta property="og:title" content="<?= e($page_title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= e($page_desc ?? SITE_TAGLINE) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <?php if ($current_page === 'profile'): ?>
        <link href="/assets/css/darkmode.css" rel="stylesheet">
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
</head>

<body data-page="<?= e($current_page) ?>">

    <header class="site-header">
        <div class="header-inner">

            <!-- Mobile menu toggle (repositioned to far left on mobile) -->
            <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="Menu">☰</button>

            <!-- Logo -->
            <a href="http://hdropshipping.com/index.php" class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </a>

            <!-- Main Nav — Tools links (every page) -->
            <nav class="main-nav">
                <a href="http://hdropshipping.com/index.php" class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>">🏠 Home</a>
                <a href="http://hdropshipping.com/roadmap.php" class="nav-link <?= $current_page === 'roadmap' ? 'active' : '' ?>">🗺️ Roadmap</a>
                <a href="http://hdropshipping.com/stack-builder.php" class="nav-link <?= $current_page === 'stack-builder' ? 'active' : '' ?>">🎯 Stack Builder</a>
                <a href="http://hdropshipping.com/compare.php" class="nav-link <?= $current_page === 'compare' ? 'active' : '' ?>">⚖️ Compare</a>
                <a href="http://hdropshipping.com/calculator.php" class="nav-link <?= $current_page === 'calculator' ? 'active' : '' ?>">💰 Calculator</a>
            </nav>

            <!-- Right side actions -->
            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/profile.php" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none">👤 <?= e($_SESSION['username']) ?></a>
                    <a href="/logout.php" style="color:rgba(255,255,255,0.4);font-size:13px;text-decoration:none">Logout</a>
                <?php else: ?>
                    <a href="/login.php" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none">Login</a>
                    <a href="/register.php" class="btn-submit">Register</a>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <a href="http://hdropshipping.com/submit.php" class="btn-submit">+ Submit Tool</a>
                <?php endif; ?>
            </div>

        </div>

        <!-- Mobile nav -->
        <div class="mobile-nav-overlay" id="mobileNavOverlay" onclick="toggleMenu()"></div>
        <div class="mobile-nav" id="mobileNav">
            <button class="mobile-nav-close" onclick="toggleMenu()" aria-label="Close menu">✕</button>
            <a href="http://hdropshipping.com/index.php" class="mobile-nav-link">🏠 Home</a>
            <a href="http://hdropshipping.com/roadmap.php" class="mobile-nav-link">🗺️ Roadmap</a>
            <a href="http://hdropshipping.com/stack-builder.php" class="mobile-nav-link">🎯 Stack Builder</a>
            <a href="http://hdropshipping.com/compare.php" class="mobile-nav-link">⚖️ Compare</a>
            <a href="http://hdropshipping.com/calculator.php" class="mobile-nav-link">💰 Calculator</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/login.php" class="mobile-nav-link">🔑 Login</a>
                <a href="/register.php" class="mobile-nav-link">📝 Register</a>
            <?php endif; ?>
            <?php if (is_admin()): ?>
                <a href="http://hdropshipping.com/submit.php" class="mobile-nav-link">+ Submit a Tool</a>
            <?php endif; ?>
        </div>
    </header>

    <script>
        function toggleMenu() {
            var nav = document.getElementById('mobileNav');
            var overlay = document.getElementById('mobileNavOverlay');
            if (nav) nav.classList.toggle('open');
            if (overlay) overlay.classList.toggle('open');
        }
    </script>

    <!-- Search bar -->
    <div class="search-bar-wrap" id="searchWrap">
        <div class="search-inner">
            <form action="/search.php" method="GET" role="search">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="search" name="q" placeholder="Search 20+ dropshipping tools..."
                        value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" id="searchInput">
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </form>
            <div class="search-suggestions" id="searchSuggestions"></div>
        </div>
    </div>
    <?php if (!empty($show_browse_pills)): ?>
        <div class="browse-pills-outer">
            <div class="browse-pills-wrap">
                <span class="browse-label">Browse:</span>

                <!-- All Tools pill — active only when no category is selected -->
                <a href="http://localhost/dropshipping/index.php"
                    class="cat-pill <?= empty($active_cat) ? 'active-pill' : '' ?>">
                    All Tools
                </a>

                <!-- Category pills — active when slug matches current page -->
                <?php foreach ($cats as $nav_cat): ?>
                    <a href="/category/index.php?slug=<?= e($nav_cat['slug']) ?>"
                        class="cat-pill <?= (isset($active_cat) && $active_cat === $nav_cat['slug']) ? 'active-pill' : '' ?>">
                        <?= e($nav_cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($current_page === 'profile'): ?>
        <script src="/assets/js/darkmode.js"></script>
    <?php endif; ?>