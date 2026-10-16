<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_admin();

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function json_out(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ---------------------------------------------------------------
// Actions: toggle / hide / show / delete  (one tool or many)
// ---------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_verify()) {
        if ($isAjax) json_out(['ok' => false, 'message' => 'Security check failed. Reload the page.'], 403);
    } else {
        $action = $_POST['action'] ?? '';

        // ids[] for bulk, id for the old single-form fallback
        $rawIds = (array)($_POST['ids'] ?? []);
        if (isset($_POST['id'])) $rawIds[] = $_POST['id'];
        $ids = array_values(array_unique(array_filter(array_map('intval', $rawIds))));

        if ($ids && in_array($action, ['toggle', 'hide', 'show', 'delete'], true)) {
            $in  = implode(',', array_fill(0, count($ids), '?'));
            $out = ['ok' => true];

            if ($action === 'delete') {
                $pdo->prepare("DELETE FROM tools WHERE id IN ($in)")->execute($ids);
                $out['deleted'] = $ids;
                $msg = count($ids) === 1 ? 'Tool deleted.' : count($ids) . ' tools deleted.';
            } else {
                if ($action === 'toggle') {
                    $pdo->prepare("UPDATE tools SET is_active = 1 - is_active WHERE id IN ($in)")->execute($ids);
                } else {
                    $pdo->prepare("UPDATE tools SET is_active = ? WHERE id IN ($in)")
                        ->execute(array_merge([$action === 'show' ? 1 : 0], $ids));
                }

                $st = $pdo->prepare("SELECT id, name, is_active FROM tools WHERE id IN ($in)");
                $st->execute($ids);
                $rows = $st->fetchAll();

                $states = [];
                foreach ($rows as $r) $states[$r['id']] = (int)$r['is_active'];
                $out['states'] = $states;

                if (count($rows) === 1) {
                    $msg = '"' . $rows[0]['name'] . '" is now ' . ($rows[0]['is_active'] ? 'visible' : 'hidden') . '.';
                } elseif ($action === 'hide') {
                    $msg = count($rows) . ' tools hidden.';
                } elseif ($action === 'show') {
                    $msg = count($rows) . ' tools are now visible.';
                } else {
                    $msg = count($rows) . ' tools updated.';
                }
            }

            $out['message'] = $msg;

            if ($isAjax) {
                $out['csrf'] = csrf_token();
                json_out($out);
            }

            // non-JS fallback
            $_SESSION['flash'] = $msg;
            redirect('/dropshipping/admin/tools/');
        }

        if ($isAjax) json_out(['ok' => false, 'message' => 'Nothing selected.'], 400);
    }
}

$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);

$tools = $pdo->query("
    SELECT t.*, c.name AS cat_name
    FROM tools t LEFT JOIN categories c ON c.id = t.category_id
    ORDER BY c.sort_order, t.name
")->fetchAll();

$hiddenCount = count(array_filter($tools, fn($x) => !$x['is_active']));

$page = 'tools';
include __DIR__ . '/../partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header" style="display:flex;align-items:center;justify-content:space-between">
            <h1>Tools (<span id="totalCount"><?= count($tools) ?></span>)</h1>
            <a href="/dropshipping/admin/tools/add.php" class="btn-primary" style="max-width:140px">+ Add Tool</a>
        </div>

        <?php if ($flash): ?><div class="flash flash-success"><?= e($flash) ?></div><?php endif; ?>

        <!-- Filter -->
        <div style="display:flex;gap:8px;margin-bottom:12px;font-size:13px">
            <button type="button" class="tool-filter" data-filter="all" style="padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--ink);cursor:pointer;font-weight:600">All (<span id="cntAll"><?= count($tools) ?></span>)</button>
            <button type="button" class="tool-filter" data-filter="active" style="padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--ink);cursor:pointer;font-weight:600">Visible (<span id="cntActive"><?= count($tools) - $hiddenCount ?></span>)</button>
            <button type="button" class="tool-filter" data-filter="hidden" style="padding:6px 14px;border-radius:8px;border:1px solid var(--border);background:var(--surface);color:var(--ink);cursor:pointer;font-weight:600">Hidden (<span id="cntHidden"><?= $hiddenCount ?></span>)</button>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" id="selectAll" title="Select all"></th>
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
                    <tr data-id="<?= (int)$t['id'] ?>"
                        data-name="<?= e($t['name']) ?>"
                        data-status="<?= $t['is_active'] ? 'active' : 'hidden' ?>"
                        style="<?= $t['is_active'] ? '' : 'opacity:.55' ?>">
                        <td><input type="checkbox" class="row-check" value="<?= (int)$t['id'] ?>"></td>
                        <td>
                            <strong><?= e($t['name']) ?></strong>
                            <?php if ($t['is_featured']): ?><span style="font-size:10px;background:#fef3c7;color:var(--amber);padding:2px 6px;border-radius:4px;margin-left:6px">Featured</span><?php endif; ?>
                        </td>
                        <td><?= e($t['cat_name'] ?? '—') ?></td>
                        <td><?= e(ucfirst($t['price_type'])) ?></td>
                        <td>⭐ <?= number_format($t['rating'],1) ?></td>
                        <td><?= number_format($t['clicks']) ?></td>
                        <td>
                            <span class="status-label" style="font-size:11px;font-weight:600;color:<?= $t['is_active'] ? 'var(--green)' : 'var(--red)' ?>">
                                <?= $t['is_active'] ? 'Active' : 'Hidden' ?>
                            </span>
                        </td>
                        <td style="display:flex;gap:12px;align-items:center">
                            <a href="/dropshipping/admin/tools/edit.php?slug=<?= e($t['slug']) ?>" style="color:var(--accent);font-size:12px;font-weight:600">Edit</a>
                            <a href="/dropshipping/tool/<?= e($t['slug']) ?>" style="font-size:12px">View</a>
                            <button type="button" class="btn-toggle" data-id="<?= (int)$t['id'] ?>" style="background:none;border:none;color:<?= $t['is_active'] ? 'var(--amber)' : 'var(--green)' ?>;font-size:12px;font-weight:600;cursor:pointer"><?= $t['is_active'] ? 'Hide' : 'Show' ?></button>
                            <button type="button" class="btn-delete" data-id="<?= (int)$t['id'] ?>" style="background:none;border:none;color:var(--red);font-size:12px;font-weight:600;cursor:pointer">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Bulk action bar (appears when something is selected) -->
<div id="bulkBar" style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:900;background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:10px 16px;box-shadow:0 8px 30px rgba(0,0,0,.25);align-items:center;gap:12px;font-size:13px;color:var(--ink)">
    <span><strong id="selCount">0</strong> selected</span>
    <button type="button" id="bulkHide" style="padding:7px 14px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--ink);font-weight:600;font-size:13px;cursor:pointer">Hide</button>
    <button type="button" id="bulkShow" style="padding:7px 14px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--ink);font-weight:600;font-size:13px;cursor:pointer">Show</button>
    <button type="button" id="bulkDelete" style="padding:7px 14px;border-radius:8px;border:none;background:var(--red);color:#fff;font-weight:600;font-size:13px;cursor:pointer">Delete</button>
    <button type="button" id="bulkClear" style="background:none;border:none;color:var(--muted);font-size:13px;cursor:pointer">Clear</button>
</div>

<!-- Toast -->
<div id="toast" style="display:none;position:fixed;bottom:90px;left:50%;transform:translateX(-50%);z-index:1100;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;color:#fff;background:#16a34a;box-shadow:0 6px 20px rgba(0,0,0,.25)"></div>

<!-- Delete confirmation modal -->
<div id="deleteToolModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:360px;width:90%;text-align:center">
        <div style="font-size:32px;margin-bottom:12px">🗑️</div>
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px">Delete <span id="deleteToolName"></span>?</h3>
        <p style="font-size:13px;color:var(--muted);margin-bottom:20px">This action can't be undone.</p>
        <div style="display:flex;gap:10px;justify-content:center">
            <button type="button" id="deleteCancel" style="padding:8px 18px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--ink);font-weight:600;font-size:13px;cursor:pointer">Cancel</button>
            <button type="button" id="deleteConfirm" style="padding:8px 18px;border-radius:8px;border:none;background:var(--red);color:#fff;font-weight:600;font-size:13px;cursor:pointer">Delete</button>
        </div>
    </div>
</div>

<script>
(function () {
    let csrfToken = <?= json_encode(csrf_token()) ?>;
    let currentFilter = 'all';
    let pendingDeleteIds = [];

    const $  = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

    const rows      = () => $$('.admin-table tbody tr');
    const rowById   = id => $('.admin-table tbody tr[data-id="' + id + '"]');
    const selected  = () => $$('.row-check:checked').map(c => c.value);

    // ---------- toast ----------
    let toastTimer;
    function toast(msg, isError) {
        const t = $('#toast');
        t.textContent = msg;
        t.style.background = isError ? '#dc2626' : '#16a34a';
        t.style.display = 'block';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => t.style.display = 'none', 2600);
    }

    // ---------- talk to the server (no page reload) ----------
    async function send(action, ids) {
        const fd = new FormData();
        fd.append('csrf_token', csrfToken);
        fd.append('action', action);
        ids.forEach(id => fd.append('ids[]', id));

        let data;
        try {
            const res = await fetch(window.location.pathname, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            data = await res.json();
        } catch (e) {
            toast('Something went wrong. Reload the page and try again.', true);
            return;
        }

        if (data.csrf) csrfToken = data.csrf;
        if (!data.ok) { toast(data.message || 'Error', true); return; }

        if (data.deleted) {
            data.deleted.forEach(id => { const r = rowById(id); if (r) r.remove(); });
        }
        if (data.states) {
            Object.keys(data.states).forEach(id => {
                const row = rowById(id);
                applyState(row, data.states[id]);
                if (row) $('.row-check', row).checked = false;   // deselect after hide/show
            });
        }

        toast(data.message);
        refresh();
    }

    // ---------- update one row visually ----------
    function applyState(row, active) {
        if (!row) return;
        row.dataset.status = active ? 'active' : 'hidden';
        row.style.opacity = active ? '' : '.55';

        const label = $('.status-label', row);
        label.textContent = active ? 'Active' : 'Hidden';
        label.style.color = active ? 'var(--green)' : 'var(--red)';

        const btn = $('.btn-toggle', row);
        btn.textContent = active ? 'Hide' : 'Show';
        btn.style.color = active ? 'var(--amber)' : 'var(--green)';
    }

    // ---------- counts, filter, selection bar ----------
    function refresh() {
        const all    = rows().length;
        const hidden = rows().filter(r => r.dataset.status === 'hidden').length;
        $('#totalCount').textContent = all;
        $('#cntAll').textContent     = all;
        $('#cntHidden').textContent  = hidden;
        $('#cntActive').textContent  = all - hidden;

        rows().forEach(r => {
            const show = currentFilter === 'all' || r.dataset.status === currentFilter;
            r.style.display = show ? '' : 'none';
            if (!show) $('.row-check', r).checked = false;   // don't keep hidden rows selected
        });

        updateSelection();
    }

    function updateSelection() {
        const n = selected().length;
        $('#selCount').textContent = n;
        $('#bulkBar').style.display = n ? 'flex' : 'none';

        // Only offer the action that makes sense for the selection:
        // visible tools selected -> "Hide", hidden tools selected -> "Show", mixed -> both
        const selRows   = selected().map(id => rowById(id));
        const hasActive = selRows.some(r => r.dataset.status === 'active');
        const hasHidden = selRows.some(r => r.dataset.status === 'hidden');
        $('#bulkHide').style.display = hasActive ? '' : 'none';
        $('#bulkShow').style.display = hasHidden ? '' : 'none';

        const visible = rows().filter(r => r.style.display !== 'none');
        const checked = visible.filter(r => $('.row-check', r).checked).length;
        const sa = $('#selectAll');
        sa.checked = visible.length > 0 && checked === visible.length;
        sa.indeterminate = checked > 0 && checked < visible.length;
    }

    // ---------- events ----------
    $('#selectAll').addEventListener('change', function () {
        rows().forEach(r => {
            if (r.style.display !== 'none') $('.row-check', r).checked = this.checked;
        });
        updateSelection();
    });

    $('.admin-table tbody').addEventListener('change', e => {
        if (e.target.classList.contains('row-check')) updateSelection();
    });

    $('.admin-table tbody').addEventListener('click', e => {
        const toggle = e.target.closest('.btn-toggle');
        if (toggle) { send('toggle', [toggle.dataset.id]); return; }

        const del = e.target.closest('.btn-delete');
        if (del) {
            const row = rowById(del.dataset.id);
            openDeleteModal([del.dataset.id], '"' + row.dataset.name + '"');
        }
    });

    $$('.tool-filter').forEach(btn => btn.addEventListener('click', () => {
        currentFilter = btn.dataset.filter;
        refresh();
    }));

    $('#bulkHide').addEventListener('click', () => send('hide', selected()));
    $('#bulkShow').addEventListener('click', () => send('show', selected()));
    $('#bulkClear').addEventListener('click', () => {
        $$('.row-check').forEach(c => c.checked = false);
        updateSelection();
    });
    $('#bulkDelete').addEventListener('click', () => {
        const ids = selected();
        openDeleteModal(ids, ids.length === 1 ? '1 tool' : ids.length + ' tools');
    });

    // ---------- delete modal ----------
    function openDeleteModal(ids, label) {
        pendingDeleteIds = ids;
        $('#deleteToolName').textContent = label;
        $('#deleteToolModalOverlay').style.display = 'flex';
    }
    function closeDeleteModal() {
        $('#deleteToolModalOverlay').style.display = 'none';
        pendingDeleteIds = [];
    }
    $('#deleteCancel').addEventListener('click', closeDeleteModal);
    $('#deleteConfirm').addEventListener('click', () => {
        const ids = pendingDeleteIds.slice();
        closeDeleteModal();
        if (ids.length) send('delete', ids);
    });
})();
</script>

<?php include __DIR__ . '/../partials/foot.php'; ?>