<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
require_admin();

$stats = [
    'tools'       => $pdo->query("SELECT COUNT(*) FROM tools WHERE is_active=1")->fetchColumn(),
    'reviews'     => $pdo->query("SELECT COUNT(*) FROM reviews WHERE approved=0")->fetchColumn(),
    'submissions' => $pdo->query("SELECT COUNT(*) FROM submissions WHERE status='pending'")->fetchColumn(),
    'clicks'      => $pdo->query("SELECT SUM(clicks) FROM tools")->fetchColumn() ?: 0,
];

$top_tools = $pdo->query("SELECT name, slug, clicks, rating FROM tools ORDER BY clicks DESC LIMIT 8")->fetchAll();

$page = 'dashboard';
include __DIR__ . '/partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header">
            <div style="display:flex;align-items:center;gap:10px">
                <h1 style="margin:0;font-size:24px;font-weight:800;letter-spacing:-0.02em;color:var(--text)">Dashboard</h1>
                <span class="badge" style="background:var(--accent-light);color:var(--accent);font-size:11.5px;font-weight:700;padding:3px 9px;border-radius:999px">Admin</span>
            </div>
            <p style="color:var(--muted);font-size:13.5px;margin:4px 0 0">Platform performance, review queue, and tool activity.</p>
        </div>

        <div class="stats-grid admin-stats-grid">
            <!-- StatCard 1: Active Tools -->
            <div class="stat-card">
                <div class="stat-card-head">
                    <span class="stat-card-title">Active Tools</span>
                    <div class="stat-card-icon"><?= icon('package', '', 18) ?></div>
                </div>
                <div class="stat-card-val"><?= (int)$stats['tools'] ?></div>
                <div class="stat-card-meta">Live in directory</div>
            </div>

            <!-- StatCard 2: Pending Reviews -->
            <div class="stat-card">
                <div class="stat-card-head">
                    <span class="stat-card-title">Pending Reviews</span>
                    <div class="stat-card-icon" style="color:var(--amber)"><?= icon('message-square', '', 18) ?></div>
                </div>
                <div class="stat-card-val" style="color:<?= $stats['reviews'] > 0 ? 'var(--amber)' : 'inherit' ?>"><?= (int)$stats['reviews'] ?></div>
                <div class="stat-card-meta"><?= $stats['reviews'] > 0 ? 'Needs moderation' : 'Queue clear' ?></div>
            </div>

            <!-- StatCard 3: New Submissions -->
            <div class="stat-card">
                <div class="stat-card-head">
                    <span class="stat-card-title">New Submissions</span>
                    <div class="stat-card-icon" style="color:var(--accent)"><?= icon('inbox', '', 18) ?></div>
                </div>
                <div class="stat-card-val" style="color:<?= $stats['submissions'] > 0 ? 'var(--accent)' : 'inherit' ?>"><?= (int)$stats['submissions'] ?></div>
                <div class="stat-card-meta"><?= $stats['submissions'] > 0 ? 'Awaiting verification' : 'Queue clear' ?></div>
            </div>

            <!-- StatCard 4: Total Outbound Clicks -->
            <div class="stat-card">
                <div class="stat-card-head">
                    <span class="stat-card-title">Outbound Clicks</span>
                    <div class="stat-card-icon"><?= icon('external-link', '', 18) ?></div>
                </div>
                <div class="stat-card-val"><?= number_format((int)$stats['clicks']) ?></div>
                <div class="stat-card-meta">Affiliate outbound traffic</div>
            </div>
        </div>

        <!-- Needs Attention Section -->
        <div class="admin-attention-wrap">
            <h2 class="admin-section-subhead">Needs Attention</h2>
            <div class="admin-attention-grid">
                <?php if ($stats['reviews'] > 0): ?>
                    <div class="admin-attention-card warn">
                        <div class="attention-icon-box"><?= icon('message-square', '', 18) ?></div>
                        <div class="attention-content">
                            <strong><?= $stats['reviews'] ?> Pending Reviews</strong>
                            <p>Community reviews waiting for moderation.</p>
                        </div>
                        <a href="/admin/reviews/" class="attention-btn">Review &rarr;</a>
                    </div>
                <?php endif; ?>

                <?php if ($stats['submissions'] > 0): ?>
                    <div class="admin-attention-card info">
                        <div class="attention-icon-box"><?= icon('inbox', '', 18) ?></div>
                        <div class="attention-content">
                            <strong><?= $stats['submissions'] ?> Tool Submissions</strong>
                            <p>Submitted tools waiting in verification queue.</p>
                        </div>
                        <a href="/admin/submissions/" class="attention-btn">Inspect &rarr;</a>
                    </div>
                <?php endif; ?>

                <?php if ($stats['reviews'] == 0 && $stats['submissions'] == 0): ?>
                    <div class="admin-attention-card clear">
                        <div class="attention-icon-box"><?= icon('check-circle', '', 18) ?></div>
                        <div class="attention-content">
                            <strong>All Caught Up</strong>
                            <p>Zero pending submissions or unapproved reviews right now.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-2xl);padding:20px;box-shadow:var(--shadow-sm);overflow:hidden">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <h2 style="font-size:16px;font-weight:700;color:var(--text);margin:0">Top Tools by Outbound Clicks</h2>
                <a href="/admin/tools/" style="font-size:13px;font-weight:600;color:var(--accent);text-decoration:none">View all tools &rarr;</a>
            </div>

            <!-- Desktop Table View -->
            <div class="admin-desktop-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Tool</th>
                            <th>Clicks</th>
                            <th>Rating</th>
                            <th style="text-align:right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_tools as $t): ?>
                            <tr>
                                <td><strong><?= e($t['name']) ?></strong></td>
                                <td><?= number_format($t['clicks']) ?></td>
                                <td>
                                    <span style="display:inline-flex;align-items:center;gap:4px">
                                        <?= icon('star', '', 13) ?> <?= number_format($t['rating'], 1) ?>
                                    </span>
                                </td>
                                <td style="text-align:right">
                                    <a href="/admin/tools/edit.php?slug=<?= e($t['slug']) ?>" class="btn-action-link" style="padding:6px 14px;font-size:12px;margin:0">Edit</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="admin-mobile-cards-wrap">
                <?php foreach ($top_tools as $t): ?>
                    <div class="admin-mobile-card-row">
                        <div class="admin-mobile-card-main">
                            <strong class="admin-mobile-card-title"><?= e($t['name']) ?></strong>
                            <div class="admin-mobile-card-meta">
                                <span><?= number_format($t['clicks']) ?> clicks</span>
                                <span>&bull;</span>
                                <span style="display:inline-flex;align-items:center;gap:3px;color:#F59E0B">
                                    <?= icon('star', '', 12) ?> <?= number_format($t['rating'], 1) ?>
                                </span>
                            </div>
                        </div>
                        <a href="/admin/tools/edit.php?slug=<?= e($t['slug']) ?>" class="admin-mobile-card-action">Edit</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/partials/foot.php'; ?>
