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
    <link rel="stylesheet" href="/dropshipping/assets/css/style.css">
    <meta name="description" content="<?= e($page_desc ?? SITE_TAGLINE) ?>">
    <meta property="og:title" content="<?= e($page_title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= e($page_desc ?? SITE_TAGLINE) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="/dropshipping/assets/css/darkmode.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
</head>

<body>

    <header class="site-header">
        <div class="header-inner">

            <!-- Logo -->
            <a href="http://localhost/dropshipping/index.php" class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </a>

            <!-- Main Nav — Tools links (every page) -->
            <nav class="main-nav">
                <a href="http://localhost/dropshipping/index.php" class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>">🏠 Home</a>
                <a href="http://localhost/dropshipping/roadmap.php" class="nav-link <?= $current_page === 'roadmap' ? 'active' : '' ?>">🗺️ Roadmap</a>
                <a href="http://localhost/dropshipping/stack-builder.php" class="nav-link <?= $current_page === 'stack-builder' ? 'active' : '' ?>">🎯 Stack Builder</a>
                <a href="http://localhost/dropshipping/compare.php" class="nav-link <?= $current_page === 'compare' ? 'active' : '' ?>">⚖️ Compare</a>
                <a href="http://localhost/dropshipping/calculator.php" class="nav-link <?= $current_page === 'calculator' ? 'active' : '' ?>">💰 Calculator</a>
                <button id="theme-toggle-btn" class="theme-toggle">🌙 Dark mode</button>
            </nav>

            <!-- Right side actions -->
            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/dropshipping/profile.php" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none">👤 <?= e($_SESSION['username']) ?></a>
                    <a href="/dropshipping/logout.php" style="color:rgba(255,255,255,0.4);font-size:13px;text-decoration:none">Logout</a>
                <?php else: ?>
                    <a href="/dropshipping/login.php" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none">Login</a>
                    <a href="/dropshipping/register.php" class="btn-submit">Register</a>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <a href="http://localhost/dropshipping/submit.php" class="btn-submit">+ Submit Tool</a>
                <?php endif; ?>
                <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="Menu">☰</button>
            </div>

        </div>

        <!-- Mobile nav -->
        <div class="mobile-nav" id="mobileNav">
            <a href="http://localhost/dropshipping/index.php" class="mobile-nav-link">🏠 Home</a>
            <a href="http://localhost/dropshipping/roadmap.php" class="mobile-nav-link">🗺️ Roadmap</a>
            <a href="http://localhost/dropshipping/stack-builder.php" class="mobile-nav-link">🎯 Stack Builder</a>
            <a href="http://localhost/dropshipping/compare.php" class="mobile-nav-link">⚖️ Compare</a>
            <a href="http://localhost/dropshipping/calculator.php" class="mobile-nav-link">💰 Calculator</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dropshipping/logout.php" class="mobile-nav-link">🚪 Logout</a>
                <a href="/dropshipping/profile.php" class="mobile-nav-link">👤 My Profile</a>
                <a href="/dropshipping/logout.php" class="mobile-nav-link">🚪 Logout</a>
            <?php else: ?>

                <a href="/dropshipping/login.php" class="mobile-nav-link">🔑 Login</a>
                <a href="/dropshipping/register.php" class="mobile-nav-link">📝 Register</a>
            <?php endif; ?>
            <?php if (is_admin()): ?>
                <a href="http://localhost/dropshipping/submit.php" class="mobile-nav-link">+ Submit a Tool</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Search bar -->
    <div class="search-bar-wrap" id="searchWrap">
        <div class="search-inner">
            <form action="/dropshipping/search.php" method="GET" role="search">
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
        <div style="background:var(--dark);border-bottom:1px solid rgba(255,255,255,0.08);padding:12px 24px;overflow-x:auto;white-space:nowrap;text-align:center">
            <div style="max-width:1200px;margin:0 auto;display:flex;gap:8px;align-items:center;justify-content:center">
                <span style="font-size:12px;font-weight:700;color:rgba(255,255,255,0.3);text-transform:uppercase;letter-spacing:0.08em;flex-shrink:0">Browse:</span>

                <!-- All Tools pill — active only when no category is selected -->
                <a href="http://localhost/dropshipping/index.php" class="cat-pill <?= empty($active_cat) ? 'active-pill' : '' ?>"
                    style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;color:#fff;border:1px solid <?= empty($active_cat) ? 'var(--accent)' : 'rgba(255,255,255,0.1)' ?>;background:<?= empty($active_cat) ? 'var(--accent)' : 'rgba(255,255,255,0.05)' ?>;text-decoration:none;flex-shrink:0">
                    All Tools
                </a>

                <!-- Category pills — active when slug matches current page -->
                <?php foreach ($cats as $nav_cat): ?>
                    <a href="/dropshipping/category/index.php?slug=<?= e($nav_cat['slug']) ?>"
                        class="cat-pill <?= (isset($active_cat) && $active_cat === $nav_cat['slug']) ? 'active-pill' : '' ?>"
                        style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;color:<?= (isset($active_cat) && $active_cat === $nav_cat['slug']) ? '#fff' : 'rgba(255,255,255,0.6)' ?>;border:1px solid <?= (isset($active_cat) && $active_cat === $nav_cat['slug']) ? 'var(--accent)' : 'rgba(255,255,255,0.1)' ?>;background:<?= (isset($active_cat) && $active_cat === $nav_cat['slug']) ? 'var(--accent)' : 'rgba(255,255,255,0.05)' ?>;text-decoration:none;flex-shrink:0;transition:all 0.15s">
                        <?= e($nav_cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <script src="/dropshipping/assets/js/darkmode.js"></script>