<aside class="admin-sidebar">
    <a href="/" class="logo">
        <span class="logo-icon">⚡</span>
        <span class="logo-text"><?= SITE_NAME ?></span>
    </a>

    <div class="admin-welcome">
        <div class="admin-welcome-avatar"><?= strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)) ?></div>
        <div>
            <div class="admin-welcome-label">Welcome back</div>
            <div class="admin-welcome-name"><?= e($_SESSION['admin_username'] ?? 'Admin') ?></div>
        </div>
    </div>

    <nav class="admin-nav">
        <a href="/dropshipping/admin/" class="admin-nav-link <?= ($page??'')==='dashboard'?'active':'' ?>">
            <span class="admin-nav-icon">📊</span> Dashboard
        </a>
        <a href="/dropshipping/admin/tools/" class="admin-nav-link <?= ($page??'')==='tools'?'active':'' ?>">
            <span class="admin-nav-icon">🔧</span> Tools
        </a>
        <a href="/dropshipping/admin/reviews/" class="admin-nav-link <?= ($page??'')==='reviews'?'active':'' ?>">
            <span class="admin-nav-icon">💬</span> Reviews
        </a>
        <a href="/dropshipping/admin/submissions/" class="admin-nav-link <?= ($page??'')==='submissions'?'active':'' ?>">
            <span class="admin-nav-icon">📥</span> Submissions
        </a>
    </nav>

    <a href="/dropshipping/logout.php" class="admin-nav-link admin-logout">
        <span class="admin-nav-icon">🚪</span> Log Out
    </a>
</aside>