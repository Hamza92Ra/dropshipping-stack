<!-- Desktop Admin Sidebar -->
<aside class="admin-sidebar">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <a href="/admin/" class="logo" style="margin-bottom:0">
            <span class="logo-icon"><?= icon('zap', '', 20) ?></span>
            <span class="logo-text"><?= SITE_NAME ?></span>
        </a>
        <span class="badge" style="background:var(--accent-light);color:var(--accent);font-size:11px;font-weight:700;padding:3px 8px;border-radius:999px;letter-spacing:0.02em">Admin</span>
    </div>

    <div class="admin-welcome">
        <div class="admin-welcome-avatar"><?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?></div>
        <div>
            <div class="admin-welcome-label">Logged in as</div>
            <div class="admin-welcome-name"><?= e($_SESSION['admin_username'] ?? 'Admin') ?></div>
        </div>
    </div>

    <nav class="admin-nav" style="flex:1">
        <a href="/admin/" class="admin-nav-link <?= ($page??'')==='dashboard'?'active':'' ?>">
            <span class="admin-nav-icon"><?= icon('activity', '', 16) ?></span> Dashboard
        </a>
        <a href="/admin/tools/" class="admin-nav-link <?= ($page??'')==='tools'?'active':'' ?>">
            <span class="admin-nav-icon"><?= icon('wrench', '', 16) ?></span> Tools
        </a>
        <a href="/admin/reviews/" class="admin-nav-link <?= ($page??'')==='reviews'?'active':'' ?>">
            <span class="admin-nav-icon"><?= icon('message-square', '', 16) ?></span> Reviews
        </a>
        <a href="/admin/submissions/" class="admin-nav-link <?= ($page??'')==='submissions'?'active':'' ?>">
            <span class="admin-nav-icon"><?= icon('inbox', '', 16) ?></span> Submissions
        </a>

        <div style="margin:16px 0;border-top:1px solid var(--border-subtle)"></div>

        <a href="/" class="admin-nav-link" style="color:var(--muted)">
            <span class="admin-nav-icon"><?= icon('arrow-left', '', 16) ?></span> Back to site
        </a>
    </nav>

    <a href="/logout.php" class="admin-nav-link admin-logout">
        <span class="admin-nav-icon"><?= icon('log-out', '', 16) ?></span> Log Out
    </a>
</aside>

<!-- Admin Mobile Top Header (<= 900px) -->
<header class="admin-mobile-header">
    <div style="display:flex;align-items:center;gap:6px">
        <a href="/admin/" class="admin-mobile-logo">
            <span class="admin-mobile-logo-icon"><?= icon('zap', '', 17) ?></span>
            <span class="admin-mobile-logo-text">Admin</span>
        </a>
        <span class="badge" style="background:var(--accent-light);color:var(--accent);font-size:10px;font-weight:700;padding:2px 7px;border-radius:999px">Console</span>
    </div>
    <div class="admin-mobile-actions">
        <a href="/" class="admin-mobile-btn" title="Back to site" aria-label="Back to site">
            <?= icon('arrow-left', '', 16) ?>
        </a>
        <button type="button" class="admin-mobile-btn" onclick="toggleTheme()" aria-label="Toggle theme">
            <?= icon('moon', '', 17) ?>
        </button>
        <a href="/admin/tools/add.php" class="admin-mobile-add-btn">+ Tool</a>
    </div>
</header>

<!-- Admin Mobile Floating Bottom Tab Bar (<= 900px) -->
<nav class="admin-bottom-nav" aria-label="Admin Navigation">
    <a href="/admin/" class="admin-bottom-tab <?= ($page??'')==='dashboard'?'active':'' ?>" aria-label="Overview">
        <span class="admin-tab-icon"><?= icon('activity', '', 18) ?></span>
        <span class="admin-tab-label">Overview</span>
    </a>
    <a href="/admin/tools/" class="admin-bottom-tab <?= ($page??'')==='tools'?'active':'' ?>" aria-label="Tools">
        <span class="admin-tab-icon"><?= icon('wrench', '', 18) ?></span>
        <span class="admin-tab-label">Tools</span>
    </a>
    <a href="/admin/reviews/" class="admin-bottom-tab <?= ($page??'')==='reviews'?'active':'' ?>" aria-label="Reviews">
        <span class="admin-tab-icon"><?= icon('message-square', '', 18) ?></span>
        <span class="admin-tab-label">Reviews</span>
    </a>
    <a href="/admin/submissions/" class="admin-bottom-tab <?= ($page??'')==='submissions'?'active':'' ?>" aria-label="Submissions">
        <span class="admin-tab-icon"><?= icon('inbox', '', 18) ?></span>
        <span class="admin-tab-label">Queue</span>
    </a>
    <button type="button" class="admin-bottom-tab" onclick="openAdminSheet()" aria-label="More Actions">
        <span class="admin-tab-icon"><?= icon('more-horizontal', '', 18) ?></span>
        <span class="admin-tab-label">More</span>
    </button>
</nav>

<!-- Admin Mobile More Bottom Sheet -->
<div class="admin-sheet-overlay" id="adminMoreOverlay" onclick="closeAdminSheet()"></div>
<div class="admin-more-sheet" id="adminMoreSheet" role="dialog" aria-modal="true" aria-label="More Admin Options">
    <div class="admin-sheet-handle" onclick="closeAdminSheet()"></div>
    <div class="admin-sheet-head">
        <strong>Admin Quick Actions</strong>
        <span>System tools &amp; shortcuts</span>
    </div>
    <div class="admin-sheet-links">
        <a href="/admin/tools/add.php" class="admin-sheet-link">
            <?= icon('plus', '', 18) ?>
            <span>Add New Tool</span>
        </a>
        <a href="/" class="admin-sheet-link">
            <?= icon('arrow-left', '', 18) ?>
            <span>Back to site</span>
        </a>
        <button type="button" class="admin-sheet-link" onclick="toggleTheme(); closeAdminSheet();">
            <?= icon('moon', '', 18) ?>
            <span>Toggle Dark/Light Mode</span>
        </button>
        <a href="/logout.php" class="admin-sheet-link admin-sheet-danger">
            <?= icon('log-out', '', 18) ?>
            <span>Log Out of Admin</span>
        </a>
    </div>
</div>

<script>
function openAdminSheet() {
    const o = document.getElementById('adminMoreOverlay');
    const s = document.getElementById('adminMoreSheet');
    if (o) o.classList.add('open');
    if (s) s.classList.add('open');
}
function closeAdminSheet() {
    const o = document.getElementById('adminMoreOverlay');
    const s = document.getElementById('adminMoreSheet');
    if (o) o.classList.remove('open');
    if (s) s.classList.remove('open');
}
</script>
