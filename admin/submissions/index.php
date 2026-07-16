<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    $status = in_array($_POST['status']??'', ['approved','rejected']) ? $_POST['status'] : null;
    if ($status) {
        $pdo->prepare("UPDATE submissions SET status=? WHERE id=?")->execute([$status, $id]);
    }
}

$subs = $pdo->query("SELECT * FROM submissions ORDER BY status='pending' DESC, created_at DESC")->fetchAll();

$page = 'submissions';
include __DIR__ . '/../partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header"><h1>Submissions (<?= count($subs) ?>)</h1></div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
            <table class="admin-table">
                <thead>
                    <tr><th>Tool</th><th>URL</th><th>Plan</th><th>Contact</th><th>Date</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($subs as $s): ?>
                    <tr>
                        <td><strong><?= e($s['tool_name']) ?></strong><br><span style="font-size:12px;color:var(--muted)"><?= e(substr($s['description'],0,80)) ?></span></td>
                        <td><a href="<?= e($s['website_url']) ?>" target="_blank" style="color:var(--accent);font-size:12px"><?= e(parse_url($s['website_url'],PHP_URL_HOST)) ?></a></td>
                        <td><span style="font-size:11px;font-weight:600;color:<?= $s['plan']==='featured'?'var(--amber)':'var(--muted)' ?>"><?= ucfirst($s['plan']) ?></span></td>
                        <td style="font-size:12px"><?= e($s['contact_email']) ?></td>
                        <td style="font-size:12px"><?= date('M j', strtotime($s['created_at'])) ?></td>
                        <td><span style="font-size:11px;font-weight:600;color:<?= $s['status']==='approved'?'var(--green)':($s['status']==='rejected'?'var(--red)':'var(--amber)') ?>"><?= ucfirst($s['status']) ?></span></td>
                        <td>
                            <?php if ($s['status']==='pending'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button name="status" value="approved" style="background:none;border:none;color:var(--green);font-size:12px;font-weight:600;cursor:pointer">Approve</button>
                                <button name="status" value="rejected" style="background:none;border:none;color:var(--red);font-size:12px;font-weight:600;cursor:pointer;margin-left:8px">Reject</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../partials/foot.php'; ?>
