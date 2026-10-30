<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?redirect=profile');
    exit;
}

$user_id = $_SESSION['user_id'];

// ── Fetch user info ──────────────────────────────────────────────────────────
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$user_id]);
$user = $user->fetch();

// ── Fetch calculator settings ────────────────────────────────────────────────
$calc = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$calc->execute([$user_id]);
$calc = $calc->fetch();

// ── Fetch roadmap progress ───────────────────────────────────────────────────
$roadmap = $pdo->prepare("SELECT * FROM user_roadmap WHERE user_id = ?");
$roadmap->execute([$user_id]);
$roadmap = $roadmap->fetch();

// ── Fetch stack builder saved stack ─────────────────────────────────────────
$stack = $pdo->prepare("SELECT * FROM user_stack WHERE user_id = ?");
$stack->execute([$user_id]);
$stack = $stack->fetch();

// ── Fetch upvoted tools ──────────────────────────────────────────────────────
$upvotes = $pdo->prepare("
    SELECT t.*, uv.created_at AS voted_at, t.upvotes AS total_votes
    FROM upvotes uv
    JOIN tools t ON t.id = uv.tool_id
    WHERE uv.user_id = ?
    ORDER BY uv.created_at DESC
");
$upvotes->execute([$user_id]);
$upvoted_tools = $upvotes->fetchAll();

// ── Handle profile update ────────────────────────────────────────────────────
$update_success = false;
$update_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_username = trim($_POST['username'] ?? '');
    $new_email    = trim($_POST['email'] ?? '');

    if (!$new_username || !$new_email) {
        $update_error = 'Username and email are required.';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $update_error = 'Invalid email address.';
    } else {
        $conflict = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $conflict->execute([$new_email, $new_username, $user_id]);
        if ($conflict->fetch()) {
            $update_error = 'Username or email already taken by another account.';
        } else {
            $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?")
                ->execute([$new_username, $new_email, $user_id]);
            $_SESSION['username'] = $new_username;
            $user['username']     = $new_username;
            $user['email']        = $new_email;
            $update_success       = true;
        }
    }
}

// ── Handle password change ───────────────────────────────────────────────────
$pw_success = false;
$pw_error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current  = $_POST['current_password'] ?? '';
    $new_pw   = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password'])) {
        $pw_error = 'Current password is incorrect.';
    } elseif (strlen($new_pw) < 8) {
        $pw_error = 'New password must be at least 8 characters.';
    } elseif ($new_pw !== $confirm) {
        $pw_error = 'Passwords do not match.';
    } else {
        $hash = password_hash($new_pw, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user_id]);
        $pw_success = true;
    }
}

// ── Handle account deletion ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $pdo->prepare("DELETE FROM upvotes       WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM user_settings WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM user_roadmap  WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM user_stack    WHERE user_id = ?")->execute([$user_id]);
    $pdo->prepare("DELETE FROM users         WHERE id = ?")->execute([$user_id]);
    session_destroy();
    header('Location: /index.php');
    exit;
}

// ── Build stats ──────────────────────────────────────────────────────────────
$member_since   = date('M Y', strtotime($user['created_at'] ?? 'now'));
$upvote_count   = count($upvoted_tools);
$roadmap_levels = ['beginner', 'intermediate', 'advanced'];

$roadmap_pct = [];
foreach ($roadmap_levels as $lvl) {
    $steps_json = $roadmap[$lvl . '_steps'] ?? '[]';
    $steps      = json_decode($steps_json, true) ?? [];
    $total      = $roadmap[$lvl . '_total'] ?? 0;
    $done       = count(array_filter($steps));
    $roadmap_pct[$lvl] = $total > 0 ? round(($done / $total) * 100) : 0;
}

$stack_tools = $stack ? json_decode($stack['tools_json'] ?? '[]', true) : [];

$page_title = 'My Profile — ' . e($user['username']);

include __DIR__ . '/header.php';
?>

<style>
    .profile-wrap {
        max-width: 1040px;
        margin: 0 auto;
        padding: 8px 0 60px;
    }

    /* ── Header Banner Card ── */
    .profile-banner-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-2xl);
        padding: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
    }

    .profile-banner-card::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -80px;
        width: 240px;
        height: 240px;
        background: radial-gradient(circle, var(--skeleton-b) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .profile-banner-left {
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        z-index: 1;
    }

    .profile-avatar-large {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 800;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }

    .profile-banner-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 6px;
        letter-spacing: -0.02em;
    }

    .profile-banner-meta {
        font-size: 13px;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .profile-banner-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .profile-banner-stats {
        display: flex;
        gap: 14px;
        position: relative;
        z-index: 1;
    }

    .profile-mini-stat {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 12px 18px;
        text-align: center;
        min-width: 90px;
    }

    .profile-mini-stat-val {
        font-size: 20px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.1;
    }

    .profile-mini-stat-label {
        font-size: 11px;
        color: var(--muted);
        font-weight: 600;
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* ── Navigation Tabs ── */
    .profile-tabs-nav {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        overflow-x: auto;
        padding-bottom: 4px;
    }

    .profile-nav-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: var(--radius-full);
        background: var(--surface);
        border: 1px solid var(--border);
        color: var(--body);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        white-space: nowrap;
        transition: all var(--dur-fast) var(--ease);
        font-family: var(--font);
    }

    .profile-nav-btn:hover {
        background: var(--surface-hover);
        color: var(--text);
        border-color: var(--border-focus);
    }

    .profile-nav-btn.active {
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        border-color: var(--btn-primary-bg);
        box-shadow: var(--shadow-xs);
    }

    /* ── Tab Panels ── */
    .profile-panel {
        display: none;
    }

    .profile-panel.active {
        display: block;
        animation: fadeIn 0.25s var(--ease) both;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ── Section Cards ── */
    .profile-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-2xl);
        padding: 28px;
        margin-bottom: 20px;
        box-shadow: var(--shadow-sm);
    }

    .profile-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .profile-card-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .profile-card-subtitle {
        font-size: 13px;
        color: var(--muted);
        margin-top: 2px;
    }

    /* ── Roadmap Progress Rows ── */
    .profile-progress-group {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .profile-progress-item {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 16px 20px;
    }

    .profile-progress-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .profile-progress-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-progress-pct {
        font-size: 13px;
        font-weight: 700;
        color: var(--accent);
    }

    .profile-progress-track {
        height: 8px;
        background: var(--border);
        border-radius: var(--radius-full);
        overflow: hidden;
    }

    .profile-progress-bar {
        height: 100%;
        background: var(--accent);
        border-radius: var(--radius-full);
        transition: width 0.4s var(--ease);
    }

    /* ── Preset Cards ── */
    .preset-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 20px;
    }

    .preset-stat-item {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 18px;
        text-align: center;
    }

    .preset-stat-num {
        font-size: 22px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.1;
        margin-bottom: 4px;
    }

    .preset-stat-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .tool-pills-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }

    .tool-pill-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-full);
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
    }

    .tool-pill-item img {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        object-fit: contain;
    }

    .btn-action-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 18px;
        padding: 10px 20px;
        border-radius: var(--radius-full);
        background: var(--surface-2);
        border: 1px solid var(--border);
        color: var(--text);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all var(--dur-fast) var(--ease);
    }

    .btn-action-link:hover {
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        border-color: var(--btn-primary-bg);
    }

    /* ── Stack Grid ── */
    .stack-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 14px;
    }

    .stack-card-single {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 18px;
        text-align: center;
        text-decoration: none;
        transition: all var(--dur-fast) var(--ease);
        display: block;
    }

    .stack-card-single:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-sm);
        border-color: var(--accent);
    }

    .stack-card-icon-box {
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg, 12px);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stack-card-icon-box img {
        width: 28px;
        height: 28px;
        object-fit: contain;
        border-radius: 6px;
    }

    .stack-card-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 2px;
    }

    .stack-card-category {
        font-size: 11px;
        color: var(--muted);
    }

    /* ── Forms ── */
    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .form-field-group {
        margin-bottom: 16px;
    }

    .form-field-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
    }

    .form-input-styled {
        width: 100%;
        padding: 12px 16px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        color: var(--text);
        font-size: 14px;
        font-family: var(--font);
        box-sizing: border-box;
        outline: none;
        transition: all var(--dur-fast) var(--ease);
    }

    .form-input-styled:focus {
        background: var(--surface);
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--skeleton-b);
    }

    .btn-submit-primary {
        padding: 11px 24px;
        background: var(--btn-primary-bg);
        border: none;
        border-radius: var(--radius-full);
        color: var(--btn-primary-text);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--font);
        box-shadow: var(--shadow-xs);
        transition: all var(--dur-fast) var(--ease);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-submit-primary:hover {
        background: var(--btn-primary-hover);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    /* ── Alerts ── */
    .profile-alert {
        padding: 14px 18px;
        border-radius: var(--radius-xl);
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-alert-success {
        background: var(--positive-bg);
        border: 1px solid var(--positive-border);
        color: var(--positive-text);
    }

    .profile-alert-error {
        background: var(--negative-bg);
        border: 1px solid var(--negative-border);
        color: var(--negative-text);
    }

    /* ── Danger Zone ── */
    .danger-zone-box {
        border: 1px solid var(--negative-border);
        background: var(--negative-bg);
        border-radius: var(--radius-2xl);
        padding: 24px;
        margin-top: 24px;
    }

    .danger-zone-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--negative-text);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .danger-zone-desc {
        font-size: 13px;
        color: var(--body);
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .btn-danger-action {
        padding: 10px 20px;
        background: var(--surface);
        border: 1.5px solid var(--negative-border);
        border-radius: var(--radius-full);
        color: var(--negative-text);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: var(--font);
        transition: all var(--dur-fast) var(--ease);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-danger-action:hover {
        background: var(--negative);
        color: #fff;
        border-color: var(--negative);
    }

    /* ── Empty State ── */
    .profile-empty-state {
        text-align: center;
        padding: 48px 24px;
        color: var(--muted);
    }

    .profile-empty-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 12px;
        color: var(--muted-light);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .profile-empty-text {
        font-size: 14px;
        line-height: 1.5;
        color: var(--body);
        max-width: 400px;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        .profile-banner-card {
            flex-direction: column;
            align-items: flex-start;
        }

        .profile-banner-stats {
            width: 100%;
            justify-content: space-between;
        }

        .profile-mini-stat {
            flex: 1;
            padding: 10px;
        }

        .form-grid-2 {
            grid-template-columns: 1fr;
        }

        .preset-stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-wrap">

    <!-- ── Profile Header Banner ── -->
    <div class="profile-banner-card">
        <div class="profile-banner-left">
            <div class="profile-avatar-large"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
            <div>
                <h1 class="profile-banner-title"><?= e($user['username']) ?></h1>
                <div class="profile-banner-meta">
                    <span><?= icon('mail', '', 14) ?> <?= e($user['email']) ?></span>
                    <span><?= icon('calendar', '', 14) ?> Joined <?= $member_since ?></span>
                </div>
            </div>
        </div>

        <div class="profile-banner-stats">
            <div class="profile-mini-stat">
                <div class="profile-mini-stat-val"><?= $upvote_count ?></div>
                <div class="profile-mini-stat-label">Upvotes</div>
            </div>
            <div class="profile-mini-stat">
                <div class="profile-mini-stat-val"><?= count($stack_tools) ?></div>
                <div class="profile-mini-stat-label">Stack Tools</div>
            </div>
            <div class="profile-mini-stat">
                <div class="profile-mini-stat-val"><?= max($roadmap_pct) ?>%</div>
                <div class="profile-mini-stat-label">Progress</div>
            </div>
        </div>
    </div>

    <!-- ── Navigation Tabs ── -->
    <div class="profile-tabs-nav" role="tablist">
        <button class="profile-nav-btn active" data-tab="overview">
            <?= icon('activity', '', 16) ?> Overview
        </button>
        <button class="profile-nav-btn" data-tab="calculator">
            <?= icon('calculator', '', 16) ?> Calculator
        </button>
        <button class="profile-nav-btn" data-tab="roadmap">
            <?= icon('map', '', 16) ?> Roadmap
        </button>
        <button class="profile-nav-btn" data-tab="stack">
            <?= icon('layers', '', 16) ?> Stack
        </button>
        <button class="profile-nav-btn" data-tab="upvotes">
            <?= icon('thumbs-up', '', 16) ?> Upvotes
        </button>
        <button class="profile-nav-btn" data-tab="settings">
            <?= icon('settings', '', 16) ?> Settings
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- OVERVIEW TAB                                                       -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel active" id="panel-overview">

        <!-- Roadmap snapshot -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('map', '', 18) ?> Roadmap Progress</h2>
                    <div class="profile-card-subtitle">Your step-by-step progress across dropshipping stages</div>
                </div>
            </div>
            <div class="profile-progress-group">
                <?php
                $roadmap_meta = [
                    'beginner'     => ['Beginner Foundations', $roadmap_pct['beginner']],
                    'intermediate' => ['Intermediate Scaling',   $roadmap_pct['intermediate']],
                    'advanced'     => ['Advanced Automation',    $roadmap_pct['advanced']],
                ];
                foreach ($roadmap_meta as $key => [$label, $pct]): ?>
                    <div class="profile-progress-item">
                        <div class="profile-progress-meta">
                            <span class="profile-progress-name"><?= $label ?></span>
                            <span class="profile-progress-pct"><?= $pct ?>%</span>
                        </div>
                        <div class="profile-progress-track">
                            <div class="profile-progress-bar" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="/roadmap.php" class="btn-action-link">
                Continue Roadmap <?= icon('arrow-right', '', 14) ?>
            </a>
        </div>

        <!-- Calculator snapshot -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('calculator', '', 18) ?> Saved Calculator Preset</h2>
                    <div class="profile-card-subtitle">Saved monthly revenue, target margins, and ad budget</div>
                </div>
            </div>
            <?php if ($calc): ?>
                <div class="preset-stats-grid">
                    <div class="preset-stat-item">
                        <div class="preset-stat-num">$<?= number_format($calc['calculator_revenue']) ?></div>
                        <div class="preset-stat-label">Monthly Revenue</div>
                    </div>
                    <div class="preset-stat-item">
                        <div class="preset-stat-num"><?= $calc['calculator_margin'] ?>%</div>
                        <div class="preset-stat-label">Profit Margin</div>
                    </div>
                    <div class="preset-stat-item">
                        <div class="preset-stat-num">$<?= number_format($calc['calculator_adspend']) ?></div>
                        <div class="preset-stat-label">Ad Spend / mo</div>
                    </div>
                </div>
                <?php
                $saved_tool_slugs = array_filter(explode(',', $calc['calculator_tools'] ?? ''));
                if ($saved_tool_slugs):
                    $placeholders = implode(',', array_fill(0, count($saved_tool_slugs), '?'));
                    $tool_rows = $pdo->prepare("SELECT * FROM tools WHERE slug IN ($placeholders)");
                    $tool_rows->execute($saved_tool_slugs);
                    $tool_rows = $tool_rows->fetchAll();
                ?>
                    <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em;margin-top:14px;margin-bottom:8px;">Included Tools</div>
                    <div class="tool-pills-wrap">
                        <?php foreach ($tool_rows as $t): ?>
                            <span class="tool-pill-item">
                                <img src="<?= e(tool_logo_url($t)) ?>" alt="<?= e($t['name']) ?>" width="18" height="18" loading="lazy" decoding="async" style="width:18px;height:18px;object-fit:contain;border-radius:4px" onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                                <?= e($t['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="/calculator.php" class="btn-action-link">
                    Open in Calculator <?= icon('arrow-right', '', 14) ?>
                </a>
            <?php else: ?>
                <div class="profile-empty-state">
                    <div class="profile-empty-icon"><?= icon('calculator', '', 36) ?></div>
                    <div class="profile-empty-text">No saved calculator presets yet. Open the profit calculator to configure and save your metrics.</div>
                    <a href="/calculator.php" class="btn-action-link" style="margin-top:14px">
                        Open Calculator <?= icon('arrow-right', '', 14) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upvoted tools snapshot -->
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('thumbs-up', '', 18) ?> Recently Upvoted Tools</h2>
                    <div class="profile-card-subtitle">Tools you voted for to help the community</div>
                </div>
            </div>
            <?php if ($upvoted_tools): ?>
                <div class="list-panel" style="border:none;padding:0;background:transparent;box-shadow:none;min-width:0;box-sizing:border-box;max-width:100%">
                    <?php foreach (array_slice($upvoted_tools, 0, 4) as $t): ?>
                        <a href="/tool/<?= e($t['slug']) ?>" class="list-row" style="text-decoration:none;border-radius:var(--radius-xl);background:var(--surface-2);margin-bottom:8px;border:1px solid var(--border);box-sizing:border-box;max-width:100%;min-width:0">
                            <div class="list-row-left" style="min-width:0;flex:1 1 auto;overflow:hidden">
                                <div class="list-row-icon" style="background:var(--surface);width:36px;height:36px;flex-shrink:0;border:1px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden">
                                    <img src="<?= e(tool_logo_url($t)) ?>" alt="<?= e($t['name']) ?>" width="24" height="24" loading="lazy" decoding="async" style="width:24px;height:24px;object-fit:contain" onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                                </div>
                                <div class="list-row-info" style="min-width:0;flex:1;overflow:hidden">
                                    <div class="list-row-title" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($t['name']) ?></div>
                                    <div class="list-row-subtitle" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Voted <?= date('M d, Y', strtotime($t['voted_at'])) ?></div>
                                </div>
                            </div>
                            <div class="list-row-right" style="flex-shrink:0;min-width:0;white-space:nowrap">
                                <span class="stat-delta positive" style="display:inline-flex;align-items:center;gap:4px">
                                    <?= icon('thumbs-up', '', 12) ?> <?= number_format($t['total_votes']) ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <?php if (count($upvoted_tools) > 4): ?>
                    <button class="btn-action-link" data-tab="upvotes" style="border:none;cursor:pointer;background:var(--surface-2)">
                        See all <?= count($upvoted_tools) ?> upvotes <?= icon('arrow-right', '', 14) ?>
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="profile-empty-state">
                    <div class="profile-empty-icon"><?= icon('thumbs-up', '', 36) ?></div>
                    <div class="profile-empty-text">You haven't upvoted any tools yet. Explore tools and upvote your favorites.</div>
                    <a href="/index.php" class="btn-action-link" style="margin-top:14px">
                        Explore Directory <?= icon('arrow-right', '', 14) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- CALCULATOR TAB                                                     -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-calculator">
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('calculator', '', 18) ?> Saved Profit Calculation</h2>
                    <div class="profile-card-subtitle">Summary of your saved financial model</div>
                </div>
            </div>
            <?php if ($calc): ?>
                <div class="preset-stats-grid">
                    <div class="preset-stat-item">
                        <div class="preset-stat-num">$<?= number_format($calc['calculator_revenue']) ?></div>
                        <div class="preset-stat-label">Monthly Revenue</div>
                    </div>
                    <div class="preset-stat-item">
                        <div class="preset-stat-num"><?= $calc['calculator_margin'] ?>%</div>
                        <div class="preset-stat-label">Profit Margin</div>
                    </div>
                    <div class="preset-stat-item">
                        <div class="preset-stat-num">$<?= number_format($calc['calculator_adspend']) ?></div>
                        <div class="preset-stat-label">Ad Spend / mo</div>
                    </div>
                </div>

                <?php
                $rev        = $calc['calculator_revenue'];
                $gp         = $rev * ($calc['calculator_margin'] / 100);
                $ads        = $calc['calculator_adspend'];
                $tool_slugs = array_values(array_filter(explode(',', $calc['calculator_tools'] ?? '')));
                $tool_cost  = 0;
                $tool_names = [];
                if ($tool_slugs) {
                    $ph = implode(',', array_fill(0, count($tool_slugs), '?'));
                    $tr = $pdo->prepare("SELECT *, price_from AS price FROM tools WHERE slug IN ($ph)");
                    $tr->execute($tool_slugs);
                    foreach ($tr->fetchAll() as $r) {
                        $tool_cost += $r['price'];
                        $tool_names[] = $r;
                    }
                }
                $net = $gp - $ads - $tool_cost;
                ?>

                <div style="display:flex;gap:12px;margin:20px 0;flex-wrap:wrap;">
                    <div class="preset-stat-item" style="flex:1;min-width:140px">
                        <div class="preset-stat-num" style="color:var(--text);">$<?= number_format($gp) ?></div>
                        <div class="preset-stat-label">Gross Profit</div>
                    </div>
                    <div class="preset-stat-item" style="flex:1;min-width:140px">
                        <div class="preset-stat-num" style="color:var(--amber);">$<?= number_format($tool_cost) ?></div>
                        <div class="preset-stat-label">Tool Costs</div>
                    </div>
                    <div class="preset-stat-item" style="flex:1;min-width:140px">
                        <div class="preset-stat-num" style="color:<?= $net >= 0 ? 'var(--positive-text)' : 'var(--negative-text)' ?>">
                            $<?= number_format($net) ?>
                        </div>
                        <div class="preset-stat-label">Net Profit</div>
                    </div>
                </div>

                <?php if ($tool_names): ?>
                    <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em;margin-bottom:10px;">Selected Tools & Subscriptions</div>
                    <div class="tool-pills-wrap">
                        <?php foreach ($tool_names as $t): ?>
                            <span class="tool-pill-item">
                                <img src="<?= e(tool_logo_url($t)) ?>" alt="<?= e($t['name']) ?>" width="18" height="18" loading="lazy" decoding="async" style="width:18px;height:18px;object-fit:contain;border-radius:4px" onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                                <?= e($t['name']) ?> <span style="opacity:.5">·</span> $<?= number_format($t['price']) ?>/mo
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="font-size:12px;color:var(--muted);margin-top:16px;">
                    Last updated: <?= date('M d, Y \a\t H:i', strtotime($calc['updated_at'] ?? $calc['created_at'] ?? 'now')) ?>
                </div>
                <a href="/calculator.php" class="btn-action-link">
                    Edit in Calculator <?= icon('arrow-right', '', 14) ?>
                </a>
            <?php else: ?>
                <div class="profile-empty-state">
                    <div class="profile-empty-icon"><?= icon('calculator', '', 36) ?></div>
                    <div class="profile-empty-text">Open the calculator, enter your revenue and margin targets, and hit <strong>Save My Settings</strong>.</div>
                    <a href="/calculator.php" class="btn-action-link" style="margin-top:14px">
                        Open Calculator <?= icon('arrow-right', '', 14) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- ROADMAP TAB                                                        -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-roadmap">
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('map', '', 18) ?> Roadmap Milestones</h2>
                    <div class="profile-card-subtitle">Your dropshipping journey broken down by difficulty tier</div>
                </div>
            </div>
            <?php
            $roadmap_details = [
                'beginner'     => ['Beginner Foundations', 'fill-beginner'],
                'intermediate' => ['Intermediate Scaling',   'fill-intermediate'],
                'advanced'     => ['Advanced Automation',    'fill-advanced'],
            ];
            foreach ($roadmap_details as $key => [$label, $cls]):
                $pct   = $roadmap_pct[$key];
                $steps = $roadmap ? json_decode($roadmap[$key . '_steps'] ?? '[]', true) : [];
                $total = $roadmap[$key . '_total'] ?? 0;
                $done  = count(array_filter($steps ?? []));
            ?>
                <div class="profile-progress-item" style="margin-bottom:16px;">
                    <div class="profile-progress-meta">
                        <div class="profile-progress-name"><?= $label ?></div>
                        <div class="profile-progress-pct"><?= $done ?>/<?= $total ?> steps (<?= $pct ?>%)</div>
                    </div>
                    <div class="profile-progress-track">
                        <div class="profile-progress-bar" style="width:<?= $pct ?>%"></div>
                    </div>
                    <?php if ($pct === 100): ?>
                        <div style="margin-top:8px;font-size:12px;color:var(--positive-text);font-weight:600;display:flex;align-items:center;gap:4px">
                            <?= icon('check-circle', '', 14) ?> Level complete!
                        </div>
                    <?php elseif ($pct > 0): ?>
                        <div style="margin-top:8px;font-size:12px;color:var(--muted);"><?= $total - $done ?> steps remaining.</div>
                    <?php else: ?>
                        <div style="margin-top:8px;font-size:12px;color:var(--muted);">Not started yet.</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <a href="/roadmap.php" class="btn-action-link">
                Continue on Roadmap <?= icon('arrow-right', '', 14) ?>
            </a>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- STACK BUILDER TAB                                                  -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-stack">
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('layers', '', 18) ?> Saved Tool Stack</h2>
                    <div class="profile-card-subtitle">Tools curated through the Stack Builder</div>
                </div>
            </div>
            <?php if ($stack && !empty($stack_tools)): ?>
                <?php
                $stack_tools = array_values(array_filter($stack_tools));
                $stack_rows = [];
                if ($stack_tools) {
                    $ph = implode(',', array_fill(0, count($stack_tools), '?'));
                    $stack_rows = $pdo->prepare("SELECT *, category_id AS category FROM tools WHERE slug IN ($ph)");
                    $stack_rows->execute($stack_tools);
                    $stack_rows = $stack_rows->fetchAll();
                }
                ?>
                <div style="font-size:13px;color:var(--muted);margin-bottom:16px;">
                    <?= count($stack_rows) ?> tools in your stack · Last saved <?= date('M d, Y', strtotime($stack['updated_at'] ?? $stack['created_at'] ?? 'now')) ?>
                </div>
                <div class="stack-cards-grid">
                    <?php foreach ($stack_rows as $t): ?>
                        <a href="/tool/<?= e($t['slug']) ?>" class="stack-card-single">
                            <div class="stack-card-icon-box">
                                <img src="<?= e(tool_logo_url($t)) ?>" alt="<?= e($t['name']) ?>" width="28" height="28" loading="lazy" decoding="async" style="width:28px;height:28px;object-fit:contain;border-radius:6px" onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                            </div>
                            <div class="stack-card-name"><?= e($t['name']) ?></div>
                            <div class="stack-card-category">Category #<?= e($t['category']) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a href="/stack-builder.php" class="btn-action-link" style="margin-top:20px;">
                    Edit Stack <?= icon('arrow-right', '', 14) ?>
                </a>
            <?php else: ?>
                <div class="profile-empty-state">
                    <div class="profile-empty-icon"><?= icon('layers', '', 36) ?></div>
                    <div class="profile-empty-text">You haven't saved a stack yet. Use the Stack Builder quiz to generate a tailored toolkit.</div>
                    <a href="/stack-builder.php" class="btn-action-link" style="margin-top:14px">
                        Build My Stack <?= icon('arrow-right', '', 14) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- UPVOTES TAB                                                        -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-upvotes">
        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('thumbs-up', '', 18) ?> All Upvoted Tools (<?= $upvote_count ?>)</h2>
                    <div class="profile-card-subtitle">Tools you've recommended to other dropshippers</div>
                </div>
            </div>
            <?php if ($upvoted_tools): ?>
                <div class="list-panel" style="border:none;padding:0;background:transparent;box-shadow:none;min-width:0;box-sizing:border-box;max-width:100%">
                    <?php foreach ($upvoted_tools as $t): ?>
                        <a href="/tool/<?= e($t['slug']) ?>" class="list-row" style="text-decoration:none;border-radius:var(--radius-xl);background:var(--surface-2);margin-bottom:8px;border:1px solid var(--border);box-sizing:border-box;max-width:100%;min-width:0">
                            <div class="list-row-left" style="min-width:0;flex:1 1 auto;overflow:hidden">
                                <div class="list-row-icon" style="background:var(--surface);width:36px;height:36px;flex-shrink:0;border:1px solid var(--border);border-radius:50%;display:flex;align-items:center;justify-content:center;overflow:hidden">
                                    <img src="<?= e(tool_logo_url($t)) ?>" alt="<?= e($t['name']) ?>" width="24" height="24" loading="lazy" decoding="async" style="width:24px;height:24px;object-fit:contain" onerror="this.onerror=null;this.src='/assets/img/logos/placeholder.svg';">
                                </div>
                                <div class="list-row-info" style="min-width:0;flex:1;overflow:hidden">
                                    <div class="list-row-title" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($t['name']) ?></div>
                                    <div class="list-row-subtitle" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">Voted <?= date('M d, Y', strtotime($t['voted_at'])) ?></div>
                                </div>
                            </div>
                            <div class="list-row-right" style="flex-shrink:0;min-width:0;white-space:nowrap">
                                <span class="stat-delta positive" style="display:inline-flex;align-items:center;gap:4px">
                                    <?= icon('thumbs-up', '', 12) ?> <?= number_format($t['total_votes']) ?> votes
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="profile-empty-state">
                    <div class="profile-empty-icon"><?= icon('thumbs-up', '', 36) ?></div>
                    <div class="profile-empty-text">You haven't upvoted any tools yet. Upvoting helps the community find the best dropshipping tools.</div>
                    <a href="/index.php" class="btn-action-link" style="margin-top:14px">
                        Browse Tools <?= icon('arrow-right', '', 14) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- SETTINGS TAB                                                       -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-settings">

        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('user', '', 18) ?> Account Information</h2>
                    <div class="profile-card-subtitle">Manage your username and primary email address</div>
                </div>
            </div>
            <?php if ($update_success): ?>
                <div class="profile-alert profile-alert-success">
                    <?= icon('check-circle', '', 16) ?> Profile updated successfully.
                </div>
            <?php endif; ?>
            <?php if ($update_error): ?>
                <div class="profile-alert profile-alert-error">
                    <?= icon('alert-circle', '', 16) ?> <?= e($update_error) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-grid-2">
                    <div class="form-field-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-input-styled" value="<?= e($user['username']) ?>" required>
                    </div>
                    <div class="form-field-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-input-styled" value="<?= e($user['email']) ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit-primary">
                    <?= icon('check', '', 15) ?> Save Changes
                </button>
            </form>
        </div>

        <div class="profile-card">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><?= icon('lock', '', 18) ?> Change Password</h2>
                    <div class="profile-card-subtitle">Update your password to keep your account secure</div>
                </div>
            </div>
            <?php if ($pw_success): ?>
                <div class="profile-alert profile-alert-success">
                    <?= icon('check-circle', '', 16) ?> Password changed successfully.
                </div>
            <?php endif; ?>
            <?php if ($pw_error): ?>
                <div class="profile-alert profile-alert-error">
                    <?= icon('alert-circle', '', 16) ?> <?= e($pw_error) ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="change_password" value="1">
                <div class="form-field-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-input-styled" placeholder="••••••••" required>
                </div>
                <div class="form-grid-2">
                    <div class="form-field-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-input-styled" placeholder="••••••••" required>
                    </div>
                    <div class="form-field-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input-styled" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit-primary">
                    <?= icon('lock', '', 15) ?> Change Password
                </button>
            </form>
        </div>

        <div class="danger-zone-box">
            <div class="danger-zone-title">
                <?= icon('alert-circle', '', 18) ?> Danger Zone
            </div>
            <div class="danger-zone-desc">Deleting your account is permanent. All your saved settings, upvotes, and custom stacks will be permanently wiped.</div>
            <button class="btn-danger-action" id="deleteAccountBtn">
                <?= icon('trash', '', 14) ?> Delete My Account
            </button>
        </div>

    </div>

</div>

<script>
    function switchTab(name) {
        document.querySelectorAll('.profile-nav-btn').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.profile-panel').forEach(p => p.classList.remove('active'));
        const panel = document.getElementById('panel-' + name);
        if (panel) panel.classList.add('active');
        const tabBtn = document.querySelector('.profile-nav-btn[data-tab="' + name + '"]');
        if (tabBtn) tabBtn.classList.add('active');
    }

    document.querySelectorAll('[data-tab]').forEach(function (el) {
        el.addEventListener('click', function () {
            switchTab(el.dataset.tab);
        });
    });

    // Auto-switch tab based on hash or query param (e.g. #settings or ?tab=settings)
    (function() {
        const hashTab = (window.location.hash || '').replace('#', '');
        const paramTab = new URLSearchParams(window.location.search).get('tab');
        const initialTab = hashTab || paramTab;
        if (initialTab && document.getElementById('panel-' + initialTab)) {
            switchTab(initialTab);
        }
    })();

    function confirmDelete() {
        if (confirm('Are you sure you want to permanently delete your account? This cannot be undone.')) {
            fetch('/profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'delete_account=1&csrf_token=<?= csrf_token() ?>'
            }).then(() => {
                window.location = '/index.php';
            });
        }
    }

    document.getElementById('deleteAccountBtn')?.addEventListener('click', confirmDelete);
</script>

<?php include __DIR__ . '/footer.php'; ?>
