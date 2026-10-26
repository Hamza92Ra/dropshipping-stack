<?php
if (!defined('APP')) die('Direct access not allowed');
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Fetch all categories
$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();

$logged_in   = isset($_SESSION['user_id']);
$username    = $logged_in ? ($_SESSION['username'] ?? '') : '';
$user_letter = $username !== '' ? strtoupper(mb_substr($username, 0, 1)) : '?';
$more_pages  = ['calculator', 'submit', 'profile', 'login', 'register'];

// Dynamic title and subtitle for top header
$heading_title = $page_heading ?? ($page_title ?? 'Overview & Tools');
$heading_sub   = $page_subheading ?? ($page_desc ?? 'Find, compare and optimize your dropshipping workflow');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($page_title ?? SITE_NAME) ?> — <?= e(SITE_NAME) ?></title>
    <meta name="color-scheme" content="light dark">
    <meta name="theme-color" content="#F4F6F4">
    <meta name="description" content="<?= e($page_desc ?? SITE_TAGLINE) ?>">
    <meta property="og:title" content="<?= e($page_title ?? SITE_NAME) ?>">
    <meta property="og:description" content="<?= e($page_desc ?? SITE_TAGLINE) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">

    <!-- Anti-flash theme script -->
    <script>
        (function() {
            var t = null;
            try {
                var urlParam = new URLSearchParams(window.location.search).get('theme');
                if (urlParam === 'dark' || urlParam === 'light') {
                    t = urlParam;
                } else {
                    t = localStorage.getItem('theme');
                }
            } catch (e) {}
            if (t !== 'dark' && t !== 'light') {
                t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>

    <!-- Google Fonts: Modern Sans (Plus Jakarta Sans & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/theme.css?v=<?= filemtime(__DIR__ . '/assets/css/theme.css') ?>">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
    <link rel="stylesheet" href="/assets/css/mobile.css?v=<?= filemtime(__DIR__ . '/assets/css/mobile.css') ?>">

    <!-- Scripts -->
    <script src="/assets/js/theme.js?v=<?= filemtime(__DIR__ . '/assets/js/theme.js') ?>" defer></script>
    <script src="/assets/js/main.js?v=<?= filemtime(__DIR__ . '/assets/js/main.js') ?>" defer></script>
</head>

<body data-page="<?= e($current_page) ?>" data-logged-in="<?= $logged_in ? '1' : '0' ?>">

    <!-- Page Loading Progress Bar -->
    <div class="page-loader" id="pageLoader"></div>

    <!-- ═══════════════════════════════════════════════════════════
         SLIM LEFT ICON SIDEBAR (Desktop)
         Clean navigation only: Home, Roadmap, Stack Builder, Compare, Calculator.
         Settings, theme toggle, and auth controls are removed (single location in header).
         ═══════════════════════════════════════════════════════════ -->
    <aside class="app-sidebar" aria-label="Sidebar Navigation">
        <!-- Logo / Brand Icon -->
        <a href="/index.php" class="sidebar-brand" data-tooltip="<?= e(SITE_NAME) ?>" aria-label="<?= e(SITE_NAME) ?>">
            <?= icon('sparkles', '', 24) ?>
        </a>

        <!-- Main Navigation Links -->
        <nav class="sidebar-nav">
            <a href="/index.php"
               class="sidebar-item <?= $current_page === 'index' ? 'active' : '' ?>"
               data-tooltip="Home"
               aria-label="Home">
                <?= icon('layout-grid', '', 20) ?>
            </a>

            <a href="/roadmap.php"
               class="sidebar-item <?= $current_page === 'roadmap' ? 'active' : '' ?>"
               data-tooltip="Roadmap"
               aria-label="Roadmap">
                <?= icon('compass', '', 20) ?>
            </a>

            <a href="/stack-builder.php"
               class="sidebar-item <?= $current_page === 'stack-builder' ? 'active' : '' ?>"
               data-tooltip="Stack Builder"
               aria-label="Stack Builder">
                <?= icon('layers', '', 20) ?>
            </a>

            <a href="/compare.php"
               class="sidebar-item <?= $current_page === 'compare' ? 'active' : '' ?>"
               data-tooltip="Compare"
               aria-label="Compare">
                <?= icon('scale', '', 20) ?>
            </a>

            <a href="/calculator.php"
               class="sidebar-item <?= $current_page === 'calculator' ? 'active' : '' ?>"
               data-tooltip="Calculator"
               aria-label="Calculator">
                <?= icon('calculator', '', 20) ?>
            </a>
        </nav>
    </aside>

    <!-- ═══════════════════════════════════════════════════════════
         FLOATING BOTTOM TAB BAR (Mobile — 5 Icons with Active Dark Pill)
         Home, Stack Builder, Compare, Calculator, Menu
         ═══════════════════════════════════════════════════════════ -->
    <nav class="bottom-nav" aria-label="Main Mobile Navigation">
        <a href="/index.php" class="bottom-tab <?= $current_page === 'index' ? 'active' : '' ?>" aria-label="Home">
            <span class="bottom-tab-icon"><?= icon('layout-grid', '', 19) ?></span>
            <span class="bottom-tab-label">Home</span>
        </a>
        <a href="/stack-builder.php" class="bottom-tab <?= $current_page === 'stack-builder' ? 'active' : '' ?>" aria-label="Stack Builder">
            <span class="bottom-tab-icon"><?= icon('layers', '', 19) ?></span>
            <span class="bottom-tab-label">Stack</span>
        </a>
        <a href="/compare.php" class="bottom-tab <?= $current_page === 'compare' ? 'active' : '' ?>" aria-label="Compare">
            <span class="bottom-tab-icon"><?= icon('scale', '', 19) ?></span>
            <span class="bottom-tab-label">Compare</span>
        </a>
        <a href="/calculator.php" class="bottom-tab <?= $current_page === 'calculator' ? 'active' : '' ?>" aria-label="Calculator">
            <span class="bottom-tab-icon"><?= icon('calculator', '', 19) ?></span>
            <span class="bottom-tab-label">Calc</span>
        </a>
        <button type="button" class="bottom-tab" id="mobileMenuTabBtn" onclick="openMobileMenu()" aria-label="Open Menu">
            <span class="bottom-tab-icon"><?= icon('menu', '', 19) ?></span>
            <span class="bottom-tab-label">Menu</span>
        </button>
    </nav>

    <!-- ═══════════════════════════════════════════════════════════
         MOBILE MENU BOTTOM SHEET
         Roadmap, Settings, How It Works, Submit, Account
         ═══════════════════════════════════════════════════════════ -->
    <div class="mobile-sheet-overlay" id="mobileMenuOverlay" onclick="closeMobileMenu()"></div>
    <div class="mobile-menu-sheet" id="mobileMenuSheet" role="dialog" aria-modal="true" aria-label="Mobile Navigation Menu">
        <div class="mobile-sheet-drag-handle" onclick="closeMobileMenu()"></div>

        <div class="mobile-sheet-profile-card">
            <?php if ($logged_in): ?>
                <div class="mobile-sheet-user">
                    <span class="mobile-sheet-avatar"><?= e($user_letter) ?></span>
                    <div class="mobile-sheet-user-meta">
                        <strong class="mobile-sheet-username"><?= e($username) ?></strong>
                        <span class="mobile-sheet-usersub">Active workspace &bull; Logged in</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="mobile-sheet-guest">
                    <div class="mobile-sheet-guest-info">
                        <strong><?= SITE_NAME ?></strong>
                        <p>Join to save custom stacks, calculate net profit, and track tools.</p>
                    </div>
                    <div class="mobile-sheet-guest-actions">
                        <a href="/login.php" class="mobile-sheet-btn-login">Login</a>
                        <a href="/register.php" class="mobile-sheet-btn-register">Register</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="mobile-sheet-links-list">
            <a href="/roadmap.php" class="mobile-sheet-link <?= $current_page === 'roadmap' ? 'active' : '' ?>">
                <?= icon('compass', '', 18) ?>
                <span>Roadmap Guide</span>
            </a>
            <?php if ($logged_in): ?>
                <a href="/profile.php" class="mobile-sheet-link <?= $current_page === 'profile' ? 'active' : '' ?>">
                    <?= icon('user', '', 18) ?>
                    <span>Profile & Saved Stacks</span>
                </a>
                <a href="/profile.php#settings" class="mobile-sheet-link">
                    <?= icon('settings', '', 18) ?>
                    <span>Settings & Preferences</span>
                </a>
            <?php endif; ?>
            <button type="button" class="mobile-sheet-link" onclick="openIntroModal(); closeMobileMenu();">
                <?= icon('info', '', 18) ?>
                <span>How It Works (Tour)</span>
            </button>
            <a href="/submit.php" class="mobile-sheet-link <?= $current_page === 'submit' ? 'active' : '' ?>">
                <?= icon('plus', '', 18) ?>
                <span>Submit a Tool</span>
            </a>
            <?php if ($logged_in): ?>
                <a href="/logout.php" class="mobile-sheet-link mobile-sheet-link-danger">
                    <?= icon('log-out', '', 18) ?>
                    <span>Log Out</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!empty($_GET['open_menu'])): ?>
        <script>window.addEventListener('DOMContentLoaded', () => setTimeout(openMobileMenu, 100));</script>
    <?php endif; ?>
    <?php if (!empty($_GET['open_search'])): ?>
        <script>window.addEventListener('DOMContentLoaded', () => setTimeout(openMobileSearch, 100));</script>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         MOBILE FULL-WIDTH SEARCH SHEET
         ═══════════════════════════════════════════════════════════ -->
    <div class="mobile-search-overlay" id="mobileSearchOverlay" onclick="closeMobileSearch()"></div>
    <div class="mobile-search-sheet" id="mobileSearchSheet" role="search" aria-label="Mobile Search">
        <div class="mobile-search-bar">
            <form action="/search.php" method="GET" class="mobile-search-form" id="mobileSearchForm">
                <span class="mobile-search-icon"><?= icon('search', '', 18) ?></span>
                <input type="search"
                       name="q"
                       id="mobileSearchInput"
                       class="mobile-search-input"
                       placeholder="Search 20+ vetted tools or categories..."
                       autocomplete="off"
                       spellcheck="false"
                       aria-label="Search or ask AI">
                <button type="button" class="mobile-search-clear" id="mobileSearchClear" onclick="clearMobileSearch()" aria-label="Clear">
                    <?= icon('x', '', 16) ?>
                </button>
            </form>
            <button type="button" class="mobile-search-cancel" onclick="closeMobileSearch()">Cancel</button>
        </div>
        <div class="mobile-search-results" id="mobileSearchResults"></div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         APP SHELL & TOP HEADER
         ═══════════════════════════════════════════════════════════ -->
    <div class="app-shell">
        <header class="app-topbar">
            <!-- Mobile Brand Bar (Visible on mobile only) -->
            <div class="topbar-mobile-header">
                <a href="/index.php" class="mobile-topbar-logo" aria-label="<?= e(SITE_NAME) ?>">
                    <span class="mobile-logo-icon"><?= icon('sparkles', '', 18) ?></span>
                    <span class="mobile-logo-text"><?= SITE_NAME ?></span>
                </a>
                <div class="mobile-topbar-actions">
                    <button type="button" class="topbar-icon-btn" id="mobileSearchTrigger" onclick="openMobileSearch()" aria-label="Search tools">
                        <?= icon('search', '', 18) ?>
                    </button>
                    <button type="button" class="topbar-icon-btn" id="themeToggleMobile" onclick="toggleTheme()" aria-label="Switch theme">
                        <?= icon('moon', '', 18) ?>
                    </button>
                    <?php if ($logged_in): ?>
                        <button type="button" class="topbar-avatar-btn" onclick="openMobileMenu()" aria-label="Account menu">
                            <span class="topbar-avatar"><?= e($user_letter) ?></span>
                        </button>
                    <?php else: ?>
                        <a href="/login.php" class="mobile-header-login-btn">Login</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Left: Desktop Page Title + Muted Subtitle -->
            <div class="topbar-titles">
                <h1 class="topbar-title"><?= e($heading_title) ?></h1>
                <p class="topbar-sub"><?= e($heading_sub) ?></p>
            </div>

            <!-- Right: Pill Search Bar + Theme Toggle + Account Control (Desktop) -->
            <div class="topbar-right">
                <!-- Search or ask AI pill -->
                <form action="/search.php" method="GET" class="topbar-search" id="searchForm" role="search">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="search-pill-box" id="searchBoxContainer">
                        <input type="search"
                               name="q"
                               id="searchInput"
                               placeholder="Search or ask AI..."
                               value="<?= e($_GET['q'] ?? '') ?>"
                               autocomplete="off"
                               spellcheck="false"
                               aria-label="Search or ask AI">
                        <button type="button" class="search-clear-btn" id="searchClearBtn" aria-label="Clear search" title="Clear">
                            <?= icon('x', '', 14) ?>
                        </button>
                        <button type="submit" class="search-pill-btn" id="searchSubmitBtn" aria-label="Search" title="Search">
                            <?= icon('search', '', 16) ?>
                        </button>
                    </div>
                    <div class="search-suggestions" id="searchSuggestions" role="listbox" aria-label="Search Suggestions"></div>
                </form>

                <!-- Single Theme Toggle (Header ONLY) -->
                <button class="topbar-icon-btn" id="themeToggle" type="button" aria-label="Switch theme">
                    <?= icon('moon', '', 18) ?>
                </button>

                <!-- Single Account Control (Top Right) -->
                <?php if ($logged_in): ?>
                    <div class="account-dropdown-wrap" id="accountDropdownWrap">
                        <button type="button" class="topbar-avatar-btn" id="accountDropdownBtn" aria-haspopup="true" aria-expanded="false" aria-label="Account menu">
                            <span class="topbar-avatar"><?= e($user_letter) ?></span>
                        </button>
                        <div class="account-dropdown-menu" id="accountDropdownMenu" role="menu">
                            <div class="dropdown-user-info">
                                <span class="dropdown-username"><?= e($username) ?></span>
                                <span class="dropdown-subtext">Active workspace</span>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="/profile.php" class="dropdown-item" role="menuitem">
                                <?= icon('user', '', 16) ?>
                                <span>Profile</span>
                            </a>
                            <a href="/index.php#my-stack" class="dropdown-item" role="menuitem">
                                <?= icon('layers', '', 16) ?>
                                <span>My Stack</span>
                            </a>
                            <a href="/profile.php#settings" class="dropdown-item" role="menuitem">
                                <?= icon('settings', '', 16) ?>
                                <span>Settings</span>
                            </a>
                            <button type="button" class="dropdown-item" role="menuitem" onclick="openIntroModal()">
                                <?= icon('info', '', 16) ?>
                                <span>How It Works</span>
                            </button>
                            <div class="dropdown-divider"></div>
                            <a href="/logout.php" class="dropdown-item dropdown-logout" role="menuitem">
                                <?= icon('log-out', '', 16) ?>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="topbar-auth-btns">
                        <a href="/login.php" class="topbar-btn-login">Login</a>
                        <a href="/register.php" class="topbar-btn-register">Register</a>
                    </div>
                <?php endif; ?>

                <?php if (is_admin()): ?>
                    <a href="/submit.php" class="topbar-cta-btn">
                        <?= icon('plus', '', 14) ?>
                        <span>Add Tool</span>
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Main Content Starts Here -->
        <main class="app-content">
