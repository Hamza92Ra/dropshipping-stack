<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_admin();

// Approve / delete actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'approve') {
        $pdo->prepare("UPDATE reviews SET approved=1 WHERE id=?")->execute([$id]);
        // Update rating on tool
        $pdo->prepare("UPDATE tools t SET
            rating = (SELECT AVG(r.rating) FROM reviews r WHERE r.tool_id=t.id AND r.approved=1),
            review_count = (SELECT COUNT(*) FROM reviews r WHERE r.tool_id=t.id AND r.approved=1)
            WHERE t.id = (SELECT tool_id FROM reviews WHERE id=?)")->execute([$id]);
    }
} elseif (($_POST['action'] ?? '') === 'delete') {
    $stmt = $pdo->prepare("SELECT tool_id FROM reviews WHERE id=?");
    $stmt->execute([$id]);
    $toolId = $stmt->fetchColumn();

    $pdo->prepare("DELETE FROM reviews WHERE id=?")->execute([$id]);

    if ($toolId) {
        $pdo->prepare("UPDATE tools SET
            rating = COALESCE((SELECT AVG(rating) FROM reviews WHERE tool_id=? AND approved=1), 0),
            review_count = (SELECT COUNT(*) FROM reviews WHERE tool_id=? AND approved=1)
            WHERE id=?")->execute([$toolId, $toolId, $toolId]);
    }
}

$reviews = $pdo->query("
    SELECT r.*, t.name AS tool_name, t.slug AS tool_slug
    FROM reviews r
    LEFT JOIN tools t ON t.id = r.tool_id
    ORDER BY r.approved ASC, r.created_at DESC
")->fetchAll();

$page = 'reviews';
include __DIR__ . '/../partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Reviews (<?= count($reviews) ?>)</h1>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Reviewer</th>
                        <th>Tool</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><strong><?= e($r['user_name']) ?></strong><br><span style="font-size:11px;color:var(--muted)"><?= e($r['user_email']) ?></span></td>
                            <td><?= e($r['tool_name']) ?></td>
                            <td><?= str_repeat('★', $r['rating']) ?></td>
                            <td style="max-width:280px;font-size:12px"><?= e(substr($r['comment'] ?? '', 0, 120)) ?>...</td>
                            <td style="font-size:12px"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                            <td><span style="font-size:11px;font-weight:600;color:<?= $r['approved'] ? 'var(--green)' : 'var(--amber)' ?>"><?= $r['approved'] ? 'Approved' : 'Pending' ?></span></td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <?php if (!$r['approved']): ?>
                                        <button name="action" value="approve" style="background:none;border:none;color:var(--green);font-size:12px;font-weight:600;cursor:pointer">Approve</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="delete" onclick="return confirmDelete(event, this)" style="background:none;border:none;color:var(--red);font-size:12px;font-weight:600;cursor:pointer;margin-left:8px">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>


<div id="deleteModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:360px;width:90%;text-align:center">
        <div style="font-size:32px;margin-bottom:12px">🗑️</div>
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px">Delete this review?</h3>
        <p style="font-size:13px;color:var(--muted);margin-bottom:20px">This action can't be undone.</p>
        <div style="display:flex;gap:10px;justify-content:center">
            <button onclick="closeDeleteModal()" style="padding:8px 18px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--ink);font-weight:600;font-size:13px;cursor:pointer">Cancel</button>
            <button onclick="proceedDelete()" style="padding:8px 18px;border-radius:8px;border:none;background:var(--red);color:#fff;font-weight:600;font-size:13px;cursor:pointer">Delete</button>
        </div>
    </div>
</div>

<script>
    let pendingDeleteBtn = null;

    function confirmDelete(e, btn) {
        e.preventDefault();
        pendingDeleteBtn = btn;
        document.getElementById('deleteModalOverlay').style.display = 'flex';
        return false;
    }

    function closeDeleteModal() {
        document.getElementById('deleteModalOverlay').style.display = 'none';
        pendingDeleteBtn = null;
    }

    function proceedDelete() {
        if (pendingDeleteBtn) {
            pendingDeleteBtn.form.requestSubmit(pendingDeleteBtn);
        }
        closeDeleteModal();
    }
</script>

<?php include __DIR__ . '/../partials/foot.php'; ?>
<?php include __DIR__ . '/../partials/foot.php'; ?>