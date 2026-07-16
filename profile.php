<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /dropshipping/login.php?redirect=profile');
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
    SELECT t.name, t.slug, t.logo_url AS icon, t.upvotes AS total_votes, uv.created_at AS voted_at
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
    header('Location: /dropshipping/index.php');
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
    max-width: 960px;
    margin: 0 auto;
    padding: 40px 24px 80px;
}

/* ── Header banner ── */
.profile-banner {
    background: var(--dark);
    border-radius: var(--radius);
    padding: 32px;
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}
.profile-banner::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(108,99,255,0.3) 0%, transparent 70%);
    border-radius: 50%;
}
.profile-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6c63ff, #5a52e0);
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; font-weight: 800; color: #fff;
    flex-shrink: 0;
    position: relative; z-index: 1;
    border: 3px solid rgba(255,255,255,0.15);
}
.profile-banner-info { position: relative; z-index: 1; flex: 1; }
.profile-banner-name { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 4px; }
.profile-banner-meta { font-size: 13px; color: rgba(255,255,255,0.5); }
.profile-banner-stats { display: flex; gap: 28px; position: relative; z-index: 1; }
.banner-stat { text-align: center; }
.banner-stat-val {
    display: block;
    font-size: 22px; font-weight: 800; color: #a5b4fc; line-height: 1;
}
.banner-stat-label {
    font-size: 11px; color: rgba(255,255,255,0.4);
    text-transform: uppercase; letter-spacing: 0.06em; margin-top: 3px;
}

/* ── Tabs ── */
.profile-tabs {
    display: flex; gap: 4px;
    border-bottom: 2px solid var(--border);
    margin-bottom: 28px;
    overflow-x: auto;
}
.profile-tab {
    padding: 10px 20px;
    font-size: 14px; font-weight: 600; color: var(--muted);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    white-space: nowrap;
    transition: all 0.15s;
    background: none; border-top: none; border-left: none; border-right: none;
    font-family: var(--font);
}
.profile-tab:hover { color: var(--accent); }
.profile-tab.active { color: var(--accent); border-bottom-color: var(--accent); }

/* ── Tab panels ── */
.profile-panel { display: none; }
.profile-panel.active { display: block; }

/* ── Section card ── */
.section-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 20px;
}
.section-card-title {
    font-size: 15px; font-weight: 800; color: var(--ink);
    margin-bottom: 18px;
    display: flex; align-items: center; gap: 8px;
}

/* ── Form elements ── */
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 12px; font-weight: 700; color: var(--muted);
    text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px;
}
.form-input {
    width: 100%; padding: 11px 14px;
    background: var(--bg); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); color: var(--ink);
    font-size: 14px; font-family: var(--font);
    box-sizing: border-box; outline: none; transition: border-color 0.15s;
}
.form-input:focus { border-color: var(--accent); }
.btn-primary {
    padding: 11px 24px;
    background: linear-gradient(135deg, #6c63ff, #5a52e0);
    border: none; border-radius: var(--radius-sm);
    color: #fff; font-size: 14px; font-weight: 700;
    cursor: pointer; font-family: var(--font);
    box-shadow: 0 4px 12px rgba(108,99,255,0.25); transition: opacity 0.15s;
}
.btn-primary:hover { opacity: 0.9; }

/* ── Alert ── */
.alert { padding: 11px 16px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 16px; }
.alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
.alert-error   { background: #fff0f0; border: 1px solid #fca5a5; color: #b91c1c; }

/* ── Calculator preset ── */
.preset-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 14px; margin-bottom: 20px;
}
.preset-stat {
    background: var(--dark); border-radius: var(--radius-sm);
    padding: 16px; text-align: center;
}
.preset-stat-val {
    font-size: 22px; font-weight: 800; color: var(--cyan);
    display: block; line-height: 1; margin-bottom: 4px;
}
.preset-stat-label {
    font-size: 11px; color: rgba(255,255,255,0.4);
    text-transform: uppercase; letter-spacing: 0.05em;
}
.tool-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.tool-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px;
    background: var(--accent-light); color: var(--accent);
    border-radius: 99px; font-size: 12px; font-weight: 600;
}
.tool-pill img { width: 16px; height: 16px; object-fit: contain; border-radius: 3px; }
.btn-edit-link {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 16px; padding: 9px 18px;
    border: 1.5px solid var(--border); border-radius: var(--radius-sm);
    color: var(--muted); font-size: 13px; font-weight: 600;
    text-decoration: none; transition: all 0.15s;
}
.btn-edit-link:hover { border-color: var(--accent); color: var(--accent); }

/* ── Roadmap progress bars ── */
.roadmap-rows { display: flex; flex-direction: column; gap: 16px; }
.roadmap-row-label {
    display: flex; justify-content: space-between;
    font-size: 13px; font-weight: 700; color: var(--ink); margin-bottom: 6px;
}
.roadmap-row-pct { color: var(--accent); }
.progress-track { height: 8px; background: var(--border); border-radius: 99px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 99px; transition: width 0.5s ease; }
.fill-beginner     { background: linear-gradient(90deg, #10b981, #34d399); }
.fill-intermediate { background: linear-gradient(90deg, #f59e0b, #fcd34d); }
.fill-advanced     { background: linear-gradient(90deg, #6366f1, #a5b4fc); }

/* ── Upvoted tools list ── */
.upvote-list { display: flex; flex-direction: column; gap: 10px; }
.upvote-item {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px;
    background: var(--bg); border: 1px solid var(--border);
    border-radius: var(--radius-sm); transition: border-color 0.15s;
}
.upvote-item:hover { border-color: #c7d2fe; }
.upvote-icon { width: 36px; height: 36px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.upvote-icon img { width: 36px; height: 36px; object-fit: contain; border-radius: 6px; }
.upvote-info { flex: 1; }
.upvote-name { font-size: 14px; font-weight: 700; color: var(--ink); }
.upvote-date { font-size: 11px; color: var(--muted); margin-top: 2px; }
.upvote-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px;
    background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c;
    border-radius: 99px; font-size: 12px; font-weight: 700;
}

/* ── Stack builder ── */
.stack-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; }
.stack-item {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 14px;
    text-align: center; transition: border-color 0.15s;
}
.stack-item:hover { border-color: #c7d2fe; }
.stack-item-icon { height: 36px; display: flex; align-items: center; justify-content: center; margin-bottom: 8px; }
.stack-item-icon img { width: 32px; height: 32px; object-fit: contain; border-radius: 6px; }
.stack-item-name { font-size: 12px; font-weight: 700; color: var(--ink); }
.stack-item-cat  { font-size: 11px; color: var(--muted); margin-top: 2px; }

/* ── Empty states ── */
.empty-state { text-align: center; padding: 40px 20px; color: var(--muted); }
.empty-state-icon { font-size: 40px; margin-bottom: 12px; }
.empty-state-text { font-size: 14px; line-height: 1.6; }
.empty-state-link {
    display: inline-block; margin-top: 14px; padding: 9px 20px;
    background: var(--accent-light); color: var(--accent);
    border-radius: var(--radius-sm); font-size: 13px; font-weight: 700; text-decoration: none;
}

/* ── Danger zone ── */
.danger-zone { border: 1px solid #fca5a5; border-radius: var(--radius); padding: 20px 24px; }
.danger-title { font-size: 14px; font-weight: 800; color: #b91c1c; margin-bottom: 6px; }
.danger-desc  { font-size: 13px; color: var(--muted); margin-bottom: 14px; }
.btn-danger {
    padding: 9px 20px; background: #fef2f2;
    border: 1.5px solid #fca5a5; border-radius: var(--radius-sm);
    color: #b91c1c; font-size: 13px; font-weight: 700;
    cursor: pointer; font-family: var(--font);
}
.btn-danger:hover { background: #fff0f0; }

@media (max-width: 640px) {
    .profile-banner { flex-direction: column; text-align: center; }
    .profile-banner-stats { justify-content: center; }
    .form-row { grid-template-columns: 1fr; }
    .preset-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="profile-wrap">

    <!-- ── Banner ── -->
    <div class="profile-banner">
        <div class="profile-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
        <div class="profile-banner-info">
            <div class="profile-banner-name"><?= e($user['username']) ?></div>
            <div class="profile-banner-meta">📧 <?= e($user['email']) ?> &nbsp;·&nbsp; 🗓 Member since <?= $member_since ?></div>
        </div>
        <div class="profile-banner-stats">
            <div class="banner-stat">
                <span class="banner-stat-val"><?= $upvote_count ?></span>
                <div class="banner-stat-label">Upvotes</div>
            </div>
            <div class="banner-stat">
                <span class="banner-stat-val"><?= count($stack_tools) ?></span>
                <div class="banner-stat-label">Stack Tools</div>
            </div>
            <div class="banner-stat">
                <span class="banner-stat-val"><?= max($roadmap_pct) ?>%</span>
                <div class="banner-stat-label">Best Progress</div>
            </div>
        </div>
    </div>

    <!-- ── Tabs ── -->
    <div class="profile-tabs">
        <button class="profile-tab active" onclick="switchTab('overview', this)">📊 Overview</button>
        <button class="profile-tab" onclick="switchTab('calculator', this)">💰 Calculator</button>
        <button class="profile-tab" onclick="switchTab('roadmap', this)">🗺 Roadmap</button>
        <button class="profile-tab" onclick="switchTab('stack', this)">🧱 Stack</button>
        <button class="profile-tab" onclick="switchTab('upvotes', this)">👍 Upvotes</button>
        <button class="profile-tab" onclick="switchTab('settings', this)">⚙️ Settings</button>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- OVERVIEW                                                           -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel active" id="panel-overview">

        <!-- Roadmap snapshot -->
        <div class="section-card">
            <div class="section-card-title">🗺 Roadmap Progress</div>
            <div class="roadmap-rows">
                <?php
                $roadmap_meta = [
                    'beginner'     => ['🌱 Beginner',    'fill-beginner'],
                    'intermediate' => ['📈 Intermediate', 'fill-intermediate'],
                    'advanced'     => ['🚀 Advanced',      'fill-advanced'],
                ];
                foreach ($roadmap_meta as $key => [$label, $cls]): ?>
                    <div>
                        <div class="roadmap-row-label">
                            <span><?= $label ?></span>
                            <span class="roadmap-row-pct"><?= $roadmap_pct[$key] ?>%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill <?= $cls ?>" style="width:<?= $roadmap_pct[$key] ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="/dropshipping/roadmap.php" class="btn-edit-link">🗺 Continue Roadmap →</a>
        </div>

        <!-- Calculator snapshot -->
        <div class="section-card">
            <div class="section-card-title">💰 Calculator Preset</div>
            <?php if ($calc): ?>
                <div class="preset-grid">
                    <div class="preset-stat">
                        <span class="preset-stat-val">$<?= number_format($calc['calculator_revenue']) ?></span>
                        <div class="preset-stat-label">Monthly Revenue</div>
                    </div>
                    <div class="preset-stat">
                        <span class="preset-stat-val"><?= $calc['calculator_margin'] ?>%</span>
                        <div class="preset-stat-label">Profit Margin</div>
                    </div>
                    <div class="preset-stat">
                        <span class="preset-stat-val">$<?= number_format($calc['calculator_adspend']) ?></span>
                        <div class="preset-stat-label">Ad Spend / mo</div>
                    </div>
                </div>
                <?php
                $saved_tool_slugs = array_filter(explode(',', $calc['calculator_tools'] ?? ''));
                if ($saved_tool_slugs):
                    $placeholders = implode(',', array_fill(0, count($saved_tool_slugs), '?'));
                    $tool_rows = $pdo->prepare("SELECT name, logo_url AS icon, slug FROM tools WHERE slug IN ($placeholders)");
                    $tool_rows->execute($saved_tool_slugs);
                    $tool_rows = $tool_rows->fetchAll();
                ?>
                    <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px;">Selected Tools</div>
                    <div class="tool-pills">
                        <?php foreach ($tool_rows as $t): ?>
                            <span class="tool-pill">
                                <img src="<?= e($t['icon']) ?>" onerror="this.style.display='none'">
                                <?= e($t['name']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="/dropshipping/calculator.php" class="btn-edit-link">💰 Edit in Calculator →</a>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💰</div>
                    <div class="empty-state-text">No saved calculator settings yet.</div>
                    <a href="/dropshipping/calculator.php" class="empty-state-link">Open Calculator →</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upvoted tools snapshot -->
        <div class="section-card">
            <div class="section-card-title">👍 Recently Upvoted</div>
            <?php if ($upvoted_tools): ?>
                <div class="upvote-list">
                    <?php foreach (array_slice($upvoted_tools, 0, 4) as $t): ?>
                        <div class="upvote-item">
                            <div class="upvote-icon">
                                <img src="<?= e($t['icon']) ?>" onerror="this.style.display='none'">
                            </div>
                            <div class="upvote-info">
                                <div class="upvote-name"><?= e($t['name']) ?></div>
                                <div class="upvote-date">Voted <?= date('M d, Y', strtotime($t['voted_at'])) ?></div>
                            </div>
                            <div class="upvote-badge">▲ <?= number_format($t['total_votes']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($upvoted_tools) > 4): ?>
                    <button class="btn-edit-link" onclick="switchTab('upvotes', this)" style="border:none;cursor:pointer;margin-top:12px;">
                        See all <?= count($upvoted_tools) ?> upvotes →
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">👍</div>
                    <div class="empty-state-text">You haven't upvoted any tools yet.</div>
                    <a href="/dropshipping/index.php" class="empty-state-link">Browse Tools →</a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- CALCULATOR                                                         -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-calculator">
        <div class="section-card">
            <div class="section-card-title">💰 Saved Calculator Preset</div>
            <?php if ($calc): ?>
                <div class="preset-grid">
                    <div class="preset-stat">
                        <span class="preset-stat-val">$<?= number_format($calc['calculator_revenue']) ?></span>
                        <div class="preset-stat-label">Monthly Revenue</div>
                    </div>
                    <div class="preset-stat">
                        <span class="preset-stat-val"><?= $calc['calculator_margin'] ?>%</span>
                        <div class="preset-stat-label">Profit Margin</div>
                    </div>
                    <div class="preset-stat">
                        <span class="preset-stat-val">$<?= number_format($calc['calculator_adspend']) ?></span>
                        <div class="preset-stat-label">Ad Spend / mo</div>
                    </div>
                </div>

                <?php
                $rev        = $calc['calculator_revenue'];
                $gp         = $rev * ($calc['calculator_margin'] / 100);
                $ads        = $calc['calculator_adspend'];
                $tool_slugs = array_filter(explode(',', $calc['calculator_tools'] ?? ''));
                $tool_cost  = 0;
                $tool_names = [];
                if ($tool_slugs) {
                    $ph = implode(',', array_fill(0, count($tool_slugs), '?'));
                    $tr = $pdo->prepare("SELECT name, logo_url AS icon, slug, price_from AS price FROM tools WHERE slug IN ($ph)");
                    $tr->execute($tool_slugs);
                    foreach ($tr->fetchAll() as $r) {
                        $tool_cost += $r['price'];
                        $tool_names[] = $r;
                    }
                }
                $net = $gp - $ads - $tool_cost;
                ?>

                <div style="display:flex;gap:12px;margin:20px 0;flex-wrap:wrap;">
                    <div style="flex:1;min-width:120px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:var(--radius-sm);padding:14px;text-align:center;">
                        <div style="font-size:18px;font-weight:800;color:#15803d;">$<?= number_format($gp) ?></div>
                        <div style="font-size:11px;color:#15803d;margin-top:2px;">Gross Profit</div>
                    </div>
                    <div style="flex:1;min-width:120px;background:#fff7ed;border:1px solid #fed7aa;border-radius:var(--radius-sm);padding:14px;text-align:center;">
                        <div style="font-size:18px;font-weight:800;color:#c2410c;">$<?= number_format($tool_cost) ?></div>
                        <div style="font-size:11px;color:#c2410c;margin-top:2px;">Tool Costs</div>
                    </div>
                    <div style="flex:1;min-width:120px;background:<?= $net >= 0 ? '#f0fdf4' : '#fff0f0' ?>;border:1px solid <?= $net >= 0 ? '#bbf7d0' : '#fca5a5' ?>;border-radius:var(--radius-sm);padding:14px;text-align:center;">
                        <div style="font-size:18px;font-weight:800;color:<?= $net >= 0 ? '#15803d' : '#b91c1c' ?>;">$<?= number_format($net) ?></div>
                        <div style="font-size:11px;color:<?= $net >= 0 ? '#15803d' : '#b91c1c' ?>;margin-top:2px;">Net Profit</div>
                    </div>
                </div>

                <?php if ($tool_names): ?>
                    <div style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">Your Tools</div>
                    <div class="tool-pills">
                        <?php foreach ($tool_names as $t): ?>
                            <span class="tool-pill">
                                <img src="<?= e($t['icon']) ?>" onerror="this.style.display='none'">
                                <?= e($t['name']) ?> <span style="opacity:.6">·</span> $<?= number_format($t['price']) ?>/mo
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div style="font-size:12px;color:var(--muted);margin-top:14px;">
                    Last updated: <?= date('M d, Y \a\t H:i', strtotime($calc['updated_at'] ?? $calc['created_at'] ?? 'now')) ?>
                </div>
                <a href="/dropshipping/calculator.php" class="btn-edit-link">✏️ Edit in Calculator →</a>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💰</div>
                    <div class="empty-state-text">Open the calculator, enter your numbers, and hit <strong>Save My Settings</strong>.</div>
                    <a href="/dropshipping/calculator.php" class="empty-state-link">Open Calculator →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- ROADMAP                                                            -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-roadmap">
        <div class="section-card">
            <div class="section-card-title">🗺 Your Roadmap Progress</div>
            <?php
            $roadmap_details = [
                'beginner'     => ['🌱 Beginner',    'fill-beginner',     '#10b981'],
                'intermediate' => ['📈 Intermediate', 'fill-intermediate', '#f59e0b'],
                'advanced'     => ['🚀 Advanced',      'fill-advanced',     '#6366f1'],
            ];
            foreach ($roadmap_details as $key => [$label, $cls, $color]):
                $pct   = $roadmap_pct[$key];
                $steps = $roadmap ? json_decode($roadmap[$key . '_steps'] ?? '[]', true) : [];
                $total = $roadmap[$key . '_total'] ?? 0;
                $done  = count(array_filter($steps ?? []));
            ?>
            <div style="margin-bottom:24px;padding:20px;background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-sm);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <div style="font-size:15px;font-weight:800;color:var(--ink);"><?= $label ?></div>
                    <div style="font-size:13px;font-weight:700;color:<?= $color ?>;"><?= $done ?>/<?= $total ?> steps · <?= $pct ?>%</div>
                </div>
                <div class="progress-track">
                    <div class="progress-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div>
                </div>
                <?php if ($pct === 100): ?>
                    <div style="margin-top:10px;font-size:12px;color:#15803d;font-weight:600;">🎉 Level complete!</div>
                <?php elseif ($pct > 0): ?>
                    <div style="margin-top:10px;font-size:12px;color:var(--muted);">Keep going — <?= $total - $done ?> steps remaining.</div>
                <?php else: ?>
                    <div style="margin-top:10px;font-size:12px;color:var(--muted);">Not started yet.</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <a href="/dropshipping/roadmap.php" class="btn-edit-link">🗺 Continue on Roadmap →</a>
        </div>

        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:16px 20px;font-size:13px;color:#92400e;">
            💡 Your roadmap progress is saved automatically when you check steps. Make sure you are logged in to keep it synced.
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- STACK BUILDER                                                      -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-stack">
        <div class="section-card">
            <div class="section-card-title">🧱 Saved Tool Stack</div>
            <?php if ($stack && !empty($stack_tools)): ?>
                <?php
                $ph = implode(',', array_fill(0, count($stack_tools), '?'));
                $stack_rows = $pdo->prepare("SELECT name, logo_url AS icon, slug, category_id AS category FROM tools WHERE slug IN ($ph)");
                $stack_rows->execute($stack_tools);
                $stack_rows = $stack_rows->fetchAll();
                ?>
                <div style="font-size:13px;color:var(--muted);margin-bottom:16px;">
                    <?= count($stack_rows) ?> tools in your stack · Last saved <?= date('M d, Y', strtotime($stack['updated_at'] ?? $stack['created_at'] ?? 'now')) ?>
                </div>
                <div class="stack-grid">
                    <?php foreach ($stack_rows as $t): ?>
                        <a href="/dropshipping/tool/<?= e($t['slug']) ?>" style="text-decoration:none;">
                            <div class="stack-item">
                                <div class="stack-item-icon">
                                    <img src="<?= e($t['icon']) ?>" onerror="this.style.display='none'">
                                </div>
                                <div class="stack-item-name"><?= e($t['name']) ?></div>
                                <div class="stack-item-cat">Category #<?= e($t['category']) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a href="/dropshipping/stack-builder.php" class="btn-edit-link" style="margin-top:16px;">✏️ Edit Stack →</a>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🧱</div>
                    <div class="empty-state-text">You haven't saved a stack yet.<br>Use the Stack Builder to pick your tools and save them here.</div>
                    <a href="/dropshipping/stack-builder.php" class="empty-state-link">Build My Stack →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- UPVOTES                                                            -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-upvotes">
        <div class="section-card">
            <div class="section-card-title">👍 Tools You've Upvoted <span style="font-size:13px;font-weight:400;color:var(--muted);">(<?= $upvote_count ?>)</span></div>
            <?php if ($upvoted_tools): ?>
                <div class="upvote-list">
                    <?php foreach ($upvoted_tools as $t): ?>
                        <a href="/dropshipping/tool/<?= e($t['slug']) ?>" style="text-decoration:none;">
                            <div class="upvote-item">
                                <div class="upvote-icon">
                                    <img src="<?= e($t['icon']) ?>" onerror="this.style.display='none'">
                                </div>
                                <div class="upvote-info">
                                    <div class="upvote-name"><?= e($t['name']) ?></div>
                                    <div class="upvote-date">Voted on <?= date('M d, Y', strtotime($t['voted_at'])) ?></div>
                                </div>
                                <div class="upvote-badge">▲ <?= number_format($t['total_votes']) ?> votes</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">   
                    <div class="empty-state-icon">👍</div>
                    <div class="empty-state-text">You haven't upvoted any tools yet.<br>Upvoting helps the community find the best tools.</div>
                    <a href="/dropshipping/index.php" class="empty-state-link">Browse Tools →</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════ -->
    <!-- SETTINGS                                                           -->
    <!-- ══════════════════════════════════════════════════════════════════ -->
    <div class="profile-panel" id="panel-settings">

        <div class="section-card">
            <div class="section-card-title">👤 Account Information</div>
            <?php if ($update_success): ?>
                <div class="alert alert-success">✅ Profile updated successfully.</div>
            <?php endif; ?>
            <?php if ($update_error): ?>
                <div class="alert alert-error">⚠️ <?= e($update_error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-input" value="<?= e($user['username']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-input" value="<?= e($user['email']) ?>">
                    </div>
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>

        <div class="section-card">
            <div class="section-card-title">🔒 Change Password</div>
            <?php if ($pw_success): ?>
                <div class="alert alert-success">✅ Password changed successfully.</div>
            <?php endif; ?>
            <?php if ($pw_error): ?>
                <div class="alert alert-error">⚠️ <?= e($pw_error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-input" placeholder="••••••••">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-input" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-input" placeholder="••••••••">
                    </div>
                </div>
                <button type="submit" class="btn-primary">Change Password</button>
            </form>
        </div>

        <div class="danger-zone">
            <div class="danger-title">⚠️ Danger Zone</div>
            <div class="danger-desc">Deleting your account is permanent. All your saved settings, upvotes, and progress will be removed.</div>
            <button class="btn-danger" onclick="confirmDelete()">Delete My Account</button>
        </div>

    </div>

</div>
<script>
    function switchTab(name, el) {
    document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.profile-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    if (el) el.classList.add('active');
}

function confirmDelete() {
    if (confirm('Are you sure you want to permanently delete your account? This cannot be undone.')) {
        fetch('/dropshipping/profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'delete_account=1&csrf_token=<?= csrf_token() ?>'
        }).then(() => { window.location = '/dropshipping/index.php'; });
    }
}
</script>

<?php include __DIR__ . '/footer.php'; ?>