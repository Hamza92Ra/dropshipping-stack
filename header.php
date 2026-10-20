<?php
if (!defined('APP')) die('Direct access not allowed');
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Fetch all categories for nav
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();

$logged_in   = isset($_SESSION['user_id']);
$username    = $logged_in ? ($_SESSION['username'] ?? '') : '';
$user_letter = $username !== '' ? strtoupper(mb_substr($username, 0, 1)) : '?';
$more_pages  = ['calculator', 'submit', 'profile', 'login', 'register'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($page_title ?? SITE_NAME) ?> — <?= e(SITE_NAME) ?></title>
    <script>
        (function() {
            var saved = localStorage.getItem('theme');
            var systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.setAttribute('data-theme', saved || (systemDark ? 'dark' : 'light'));
        })();
    </script>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
    <link rel="stylesheet" href="/assets/css/mobile.css?v=<?= filemtime(__DIR__ . '/assets/css/mobile.css') ?>">
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

    <!-- Top loading bar — shows on navigation, helps on slow connections/CPUs -->
    <div class="page-loader" id="pageLoader"></div>

    <header class="site-header">
        <div class="header-inner">

            <!-- Logo -->
            <a href="/index.php" class="logo">
                <span class="logo-icon">⚡</span>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </a>

            <!-- Main Nav — desktop only (phones use the bottom tab bar) -->
            <nav class="main-nav">
                <a href="/index.php" class="nav-link <?= $current_page === 'index' ? 'active' : '' ?>"><span class="nav-ico">🏠</span> Home</a>
                <a href="/roadmap.php" class="nav-link <?= $current_page === 'roadmap' ? 'active' : '' ?>"><span class="nav-ico">🗺️</span> Roadmap</a>
                <a href="/stack-builder.php" class="nav-link <?= $current_page === 'stack-builder' ? 'active' : '' ?>"><span class="nav-ico">🎯</span> Stack Builder</a>
                <a href="/compare.php" class="nav-link <?= $current_page === 'compare' ? 'active' : '' ?>"><span class="nav-ico">⚖️</span> Compare</a>
                <a href="/calculator.php" class="nav-link <?= $current_page === 'calculator' ? 'active' : '' ?>"><span class="nav-ico">💰</span> Calculator</a>
            </nav>

            <!-- Right side actions -->
            <div class="header-actions">
                <?php if ($logged_in): ?>
                    <a href="/profile.php" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none">👤 <?= e($username) ?></a>
                    <a href="/logout.php" class="desktop-logout-link" style="color:rgba(255,255,255,0.4);font-size:13px;text-decoration:none">Logout</a>
                <?php else: ?>
                    <a href="/login.php" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none">Login</a>
                    <a href="/register.php" class="btn-submit">Register</a>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <a href="/submit.php" class="btn-submit">+ Submit Tool</a>
                <?php endif; ?>
                <button class="theme-toggle" id="themeToggle" type="button" aria-label="Toggle theme">
                    <span class="icon">🌙</span><span class="tt-label">Theme</span>
                </button>
            </div>

        </div>
    </header>

    <!-- ═══ Bottom tab bar (phones/tablets) — Instagram style ═══ -->
    <nav class="bottom-nav" aria-label="Main">
        <a href="/index.php" class="bottom-tab <?= $current_page === 'index' ? 'active' : '' ?>" aria-label="Home" title="Home">
            <svg viewBox="0 0 24 24"><path class="fill-on-active" d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z" /></svg>
        </a>
        <a href="/roadmap.php" class="bottom-tab <?= $current_page === 'roadmap' ? 'active' : '' ?>" aria-label="Roadmap" title="Roadmap">
            <svg viewBox="0 0 24 24"><path class="fill-on-active" d="M9 4 3 6.5V20l6-2.5 6 2.5 6-2.5V4l-6 2.5z" /><path d="M9 4v13.5M15 6.5V20" stroke="currentColor" /></svg>
        </a>
        <a href="/stack-builder.php" class="bottom-tab <?= $current_page === 'stack-builder' ? 'active' : '' ?>" aria-label="Stack Builder" title="Stack Builder">
            <svg viewBox="0 0 24 24"><path class="fill-on-active" d="m12 3 9 5-9 5-9-5z" /><path d="m3 13 9 5 9-5" /></svg>
        </a>
        <a href="/compare.php" class="bottom-tab <?= $current_page === 'compare' ? 'active' : '' ?>" aria-label="Compare" title="Compare">
            <svg viewBox="0 0 24 24"><path d="M4 8h15l-3.5-3.5M20 16H5l3.5 3.5" /></svg>
        </a>
        <button type="button" class="bottom-tab <?= in_array($current_page, $more_pages, true) ? 'active' : '' ?>" id="moreTab" onclick="toggleSheet()" aria-label="Profile and more" aria-expanded="false" aria-controls="moreSheet">
            <?php if ($logged_in): ?>
                <span class="tab-avatar"><?= e($user_letter) ?></span>
            <?php else: ?>
                <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" /></svg>
            <?php endif; ?>
        </button>
    </nav>

    <!-- "More" sheet -->
    <div class="sheet-overlay" id="sheetOverlay" onclick="toggleSheet(false)"></div>
    <div class="more-sheet" id="moreSheet" role="dialog" aria-label="More">
        <div class="sheet-handle"></div>

        <?php if ($logged_in): ?>
            <a href="/profile.php" class="sheet-user">
                <span class="tab-avatar"><?= e($user_letter) ?></span>
                <span>
                    <div class="sheet-user-name"><?= e($username) ?></div>
                    <div class="sheet-user-sub">View your profile</div>
                </span>
            </a>
        <?php endif; ?>

        <a href="/calculator.php" class="sheet-link"><span class="sheet-ico">💰</span> Calculator</a>
        <?php if (is_admin()): ?>
            <a href="/submit.php" class="sheet-link"><span class="sheet-ico">➕</span> Submit a Tool</a>
        <?php endif; ?>
        <?php if ($logged_in): ?>
            <a href="/logout.php" class="sheet-link danger"><span class="sheet-ico">🚪</span> Logout</a>
        <?php else: ?>
            <a href="/login.php" class="sheet-link"><span class="sheet-ico">🔑</span> Login</a>
            <a href="/register.php" class="sheet-link"><span class="sheet-ico">📝</span> Register</a>
        <?php endif; ?>
    </div>

    <script>
        function toggleSheet(force) {
            var sheet = document.getElementById('moreSheet');
            var overlay = document.getElementById('sheetOverlay');
            var tab = document.getElementById('moreTab');
            if (!sheet || !overlay) return;
            var open = (typeof force === 'boolean') ? force : !sheet.classList.contains('open');
            sheet.classList.toggle('open', open);
            overlay.classList.toggle('open', open);
            if (tab) tab.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') toggleSheet(false);
        });

        // ── Top page-load progress bar ──────────────────────────
        (function() {
            var bar = document.getElementById('pageLoader');
            if (!bar) return;

            function start() {
                bar.style.transition = 'none';
                bar.style.width = '0%';
                bar.style.opacity = '1';
                // force reflow so the transition below actually animates
                void bar.offsetWidth;
                bar.style.transition = 'width 8s cubic-bezier(0.1, 0.6, 0.2, 1), opacity 0.2s ease';
                bar.style.width = '90%';
            }

            function finish() {
                bar.style.transition = 'width 0.25s ease, opacity 0.3s ease 0.15s';
                bar.style.width = '100%';
                setTimeout(function() {
                    bar.style.opacity = '0';
                }, 150);
            }

            // Show immediately on same-tab, same-origin link clicks
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a');
                if (!link) return;
                if (link.target === '_blank' || link.hasAttribute('download')) return;
                if (link.origin !== window.location.origin) return;
                if (link.getAttribute('href') && link.getAttribute('href').startsWith('#')) return;
                start();
            });

            // Show on form submits (search, review form, etc.)
            document.addEventListener('submit', function() {
                start();
            });

            // Hide once the page has actually finished loading
            window.addEventListener('load', finish);

            // Handle back/forward-cache restores (bar could be stuck mid-way)
            window.addEventListener('pageshow', function(e) {
                if (e.persisted) {
                    bar.style.transition = 'none';
                    bar.style.width = '0%';
                    bar.style.opacity = '0';
                }
            });
        })();

        // ── Image skeleton reveal ────────────────────────────────
        // Any <img class="skeleton-img"> fades in once loaded, hiding
        // the shimmer placeholder behind it (helps on slow connections
        // where external logos take a moment to arrive).
        // Runs on DOMContentLoaded since this script sits in the header,
        // before the tool cards it needs to find even exist yet.
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('img.skeleton-img').forEach(function(img) {
                function reveal() {
                    img.classList.add('loaded');
                }
                if (img.complete && img.naturalWidth > 0) {
                    reveal();
                } else {
                    img.addEventListener('load', reveal);
                    img.addEventListener('error', reveal);
                }
            });
        });
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
                <a href="/index.php"
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