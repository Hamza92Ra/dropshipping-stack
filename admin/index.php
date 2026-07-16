<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';
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
            <h1>Dashboard</h1>
            <p style="color:var(--muted);font-size:14px;margin-top:4px">Welcome back — here's what's happening.</p>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-card-val"><?= $stats['tools'] ?></div>
                <div class="stat-card-label">Active Tools</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-val" style="color:var(--amber)"><?= $stats['reviews'] ?></div>
                <div class="stat-card-label">Pending Reviews</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-val" style="color:var(--accent)"><?= $stats['submissions'] ?></div>
                <div class="stat-card-label">New Submissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-val" style="color:var(--green)"><?= number_format($stats['clicks']) ?></div>
                <div class="stat-card-label">Total Clicks</div>
            </div>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:24px">
            <h2 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:16px">Top Tools by Clicks</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Clicks</th>
                        <th>Rating</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_tools as $t): ?>
                        
                        <tr>
                            <td><strong><?= e($t['name']) ?></strong></td>
                            <td><?= number_format($t['clicks']) ?></td>
                            <td>⭐ <?= number_format($t['rating'], 1) ?></td>
                            <td>
                                <a href="/dropshipping/admin/tools/edit.php?slug=<?= e($t['slug']) ?>" style="color:var(--accent);font-size:12px;font-weight:600">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php include __DIR__ . '/partials/foot.php'; ?>