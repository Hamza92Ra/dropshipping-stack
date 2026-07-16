<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_admin();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    if (($_POST['action'] ?? '') === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM tools WHERE id=?")->execute([$id]);
        $_SESSION['flash'] = 'Tool deleted.';
        redirect('/dropshipping/admin/tools/');
    }
}

$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);

$tools = $pdo->query("
    SELECT t.*, c.name AS cat_name
    FROM tools t LEFT JOIN categories c ON c.id = t.category_id
    ORDER BY c.sort_order, t.name
")->fetchAll();

$page = 'tools';
include __DIR__ . '/../partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header" style="display:flex;align-items:center;justify-content:space-between">
            <h1>Tools (<?= count($tools) ?>)</h1>
            <a href="/dropshipping/admin/tools/add.php" class="btn-primary" style="max-width:140px">+ Add Tool</a>
        </div>

        <?php if ($flash): ?><div class="flash flash-success"><?= e($flash) ?></div><?php endif; ?>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tool</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Rating</th>
                        <th>Clicks</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tools as $t): ?>
                    <tr>
                        <td>
                            <strong><?= e($t['name']) ?></strong>
                            <?php if ($t['is_featured']): ?><span style="font-size:10px;background:#fef3c7;color:var(--amber);padding:2px 6px;border-radius:4px;margin-left:6px">Featured</span><?php endif; ?>
                        </td>
                        <td><?= e($t['cat_name'] ?? '—') ?></td>
                        <td><?= e(ucfirst($t['price_type'])) ?></td>
                        <td>⭐ <?= number_format($t['rating'],1) ?></td>
                        <td><?= number_format($t['clicks']) ?></td>
                        <td>
                            <span style="font-size:11px;font-weight:600;color:<?= $t['is_active'] ? 'var(--green)' : 'var(--red)' ?>">
                                <?= $t['is_active'] ? 'Active' : 'Hidden' ?>
                            </span>
                        </td>
                        <td style="display:flex;gap:12px;align-items:center">
                            <a href="/dropshipping/admin/tools/edit.php?slug=<?= e($t['slug']) ?>" style="color:var(--accent);font-size:12px;font-weight:600">Edit</a>
                            <a href="/dropshipping/tool/<?= e($t['slug']) ?>" style="font-size:12px">View</a>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button type="submit" name="action" value="delete" onclick="return confirmToolDelete(event, this, '<?= e(addslashes($t['name'])) ?>')" style="background:none;border:none;color:var(--red);font-size:12px;font-weight:600;cursor:pointer">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="deleteToolModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:360px;width:90%;text-align:center">
        <div style="font-size:32px;margin-bottom:12px">🗑️</div>
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px">Delete <span id="deleteToolName"></span>?</h3>
        <p style="font-size:13px;color:var(--muted);margin-bottom:20px">This action can't be undone.</p>
        <div style="display:flex;gap:10px;justify-content:center">
            <button onclick="closeToolDeleteModal()" style="padding:8px 18px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--ink);font-weight:600;font-size:13px;cursor:pointer">Cancel</button>
            <button onclick="proceedToolDelete()" style="padding:8px 18px;border-radius:8px;border:none;background:var(--red);color:#fff;font-weight:600;font-size:13px;cursor:pointer">Delete</button>
        </div>
    </div>
</div>

<script>
let pendingToolDeleteBtn = null;

function confirmToolDelete(e, btn, toolName) {
    e.preventDefault();
    pendingToolDeleteBtn = btn;
    document.getElementById('deleteToolName').textContent = toolName;
    document.getElementById('deleteToolModalOverlay').style.display = 'flex';
    return false;
}

function closeToolDeleteModal() {
    document.getElementById('deleteToolModalOverlay').style.display = 'none';
    pendingToolDeleteBtn = null;
}

function proceedToolDelete() {
    if (pendingToolDeleteBtn) {
        pendingToolDeleteBtn.form.requestSubmit(pendingToolDeleteBtn);
    }
    closeToolDeleteModal();
}
</script>

<?php include __DIR__ . '/../partials/foot.php'; ?>