<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
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
            redirect('/admin/tools/');
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
            <div>
                <div style="display:flex;align-items:center;gap:10px">
                    <h1 style="margin:0;font-size:24px;font-weight:800;letter-spacing:-0.02em;color:var(--text)">Tools (<span id="totalCount"><?= count($tools) ?></span>)</h1>
                    <span class="badge" style="background:var(--accent-light);color:var(--accent);font-size:11.5px;font-weight:700;padding:3px 9px;border-radius:999px">Admin</span>
                </div>
                <p style="color:var(--muted);font-size:13.5px;margin:4px 0 0">Manage software directory listings, pricing tiers, and visibility.</p>
            </div>
            <a href="/admin/tools/add.php" class="btn-primary" style="max-width:140px;padding:9px 18px;font-size:13.5px">+ Add Tool</a>
        </div>

        <?php if ($flash): ?><div class="flash flash-success"><?= e($flash) ?></div><?php endif; ?>

        <!-- Filter & Search Bar -->
        <div class="admin-tools-toolbar">
            <div class="admin-filter-chips">
                <button type="button" class="tool-filter active" data-filter="all">All (<span id="cntAll"><?= count($tools) ?></span>)</button>
                <button type="button" class="tool-filter" data-filter="active">Visible (<span id="cntActive"><?= count($tools) - $hiddenCount ?></span>)</button>
                <button type="button" class="tool-filter" data-filter="hidden">Hidden (<span id="cntHidden"><?= $hiddenCount ?></span>)</button>
            </div>
            <div class="admin-search-wrap">
                <?= icon('search', '', 15) ?>
                <input type="search" id="adminToolSearch" placeholder="Filter tools by name..." class="admin-tool-search-input">
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="admin-desktop-table-container">
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
                        <td>
                            <span style="display:inline-flex;align-items:center;gap:3px;color:var(--amber)">
                                <?= icon('star', '', 12) ?> <?= number_format($t['rating'], 1) ?>
                            </span>
                        </td>
                        <td><?= number_format($t['clicks']) ?></td>
                        <td>
                            <span class="status-label" style="font-size:11px;font-weight:600;color:<?= $t['is_active'] ? 'var(--green)' : 'var(--red)' ?>">
                                <?= $t['is_active'] ? 'Active' : 'Hidden' ?>
                            </span>
                        </td>
                        <td style="display:flex;gap:12px;align-items:center">
                            <a href="/admin/tools/edit.php?slug=<?= e($t['slug']) ?>" style="color:var(--accent);font-size:12px;font-weight:600">Edit</a>
                            <a href="/tool/index.php?slug=<?= e($t['slug']) ?>" target="_blank" style="font-size:12px">View</a>
                            <button type="button" class="btn-toggle" data-id="<?= (int)$t['id'] ?>" style="background:none;border:none;color:<?= $t['is_active'] ? 'var(--amber)' : 'var(--green)' ?>;font-size:12px;font-weight:600;cursor:pointer"><?= $t['is_active'] ? 'Hide' : 'Show' ?></button>
                            <button type="button" class="btn-delete" data-id="<?= (int)$t['id'] ?>" data-name="<?= e($t['name']) ?>" style="background:none;border:none;color:var(--red);font-size:12px;font-weight:600;cursor:pointer">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List View (<= 900px) -->
        <div class="admin-mobile-cards-list" id="mobileCardsList">
            <?php foreach ($tools as $t): ?>
            <div class="admin-tool-card"
                 data-id="<?= (int)$t['id'] ?>"
                 data-name="<?= e($t['name']) ?>"
                 data-status="<?= $t['is_active'] ? 'active' : 'hidden' ?>"
                 style="<?= $t['is_active'] ? '' : 'opacity:.7' ?>">
                <div class="admin-tool-card-head">
                    <div>
                        <strong class="admin-tool-card-title"><?= e($t['name']) ?></strong>
                        <?php if ($t['is_featured']): ?>
                            <span class="admin-badge-featured">Featured</span>
                        <?php endif; ?>
                    </div>
                    <span class="status-badge status-<?= $t['is_active'] ? 'active' : 'hidden' ?>">
                        <?= $t['is_active'] ? 'Active' : 'Hidden' ?>
                    </span>
                </div>

                <div class="admin-tool-card-fields">
                    <div class="admin-tool-field">
                        <span class="admin-tool-field-k">Category</span>
                        <span class="admin-tool-field-v"><?= e($t['cat_name'] ?? '—') ?></span>
                    </div>
                    <div class="admin-tool-field">
                        <span class="admin-tool-field-k">Pricing</span>
                        <span class="admin-tool-field-v"><?= (float)$t['price_from'] > 0 ? '$' . number_format((float)$t['price_from'], 0) . '/mo' : ucfirst($t['price_type']) ?></span>
                    </div>
                    <div class="admin-tool-field">
                        <span class="admin-tool-field-k">Rating</span>
                        <span class="admin-tool-field-v" style="color:#F59E0B">
                            <?= icon('star', '', 12) ?> <?= number_format($t['rating'], 1) ?>
                        </span>
                    </div>
                    <div class="admin-tool-field">
                        <span class="admin-tool-field-k">Clicks</span>
                        <span class="admin-tool-field-v"><?= number_format($t['clicks']) ?></span>
                    </div>
                </div>

                <div class="admin-tool-card-actions">
                    <a href="/admin/tools/edit.php?slug=<?= e($t['slug']) ?>" class="admin-card-btn edit">
                        <?= icon('edit', '', 14) ?>
                        <span>Edit</span>
                    </a>
                    <a href="/tool/index.php?slug=<?= e($t['slug']) ?>" class="admin-card-btn view" target="_blank">
                        <?= icon('external-link', '', 14) ?>
                        <span>View</span>
                    </a>
                    <button type="button" class="admin-card-btn toggle btn-toggle" data-id="<?= (int)$t['id'] ?>">
                        <?= $t['is_active'] ? 'Hide' : 'Show' ?>
                    </button>
                    <button type="button" class="admin-card-btn delete btn-delete" data-id="<?= (int)$t['id'] ?>" data-name="<?= e($t['name']) ?>" aria-label="Delete">
                        <?= icon('trash-2', '', 14) ?>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
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

<!-- Delete confirmation modal (Bottom Sheet on Mobile) -->
<div id="deleteToolModalOverlay" class="admin-modal-overlay" style="display:none">
    <div class="admin-confirm-sheet">
        <div class="admin-sheet-handle" onclick="closeDeleteModal()"></div>
        <div style="width:48px;height:48px;border-radius:50%;background:var(--negative-bg);color:var(--negative-text);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
            <?= icon('trash-2', '', 22) ?>
        </div>
        <h3 style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px">Delete <span id="deleteToolName"></span>?</h3>
        <p style="font-size:13px;color:var(--muted);margin-bottom:20px">This action permanently deletes this tool and cannot be undone.</p>
        <div style="display:flex;gap:10px;justify-content:center">
            <button type="button" id="deleteCancel" class="btn-cancel-action" style="padding:10px 22px;border-radius:var(--radius-full);border:1px solid var(--border);background:transparent;color:var(--text);font-weight:600;font-size:13.5px;cursor:pointer">Cancel</button>
            <button type="button" id="deleteConfirm" style="padding:10px 24px;border-radius:var(--radius-full);border:none;background:var(--negative);color:#fff;font-weight:700;font-size:13.5px;cursor:pointer">Delete</button>
        </div>
    </div>
</div>

<script>
(function () {
    let csrfToken = <?= json_encode(csrf_token()) ?>;
    let currentFilter = 'all';
    let searchQuery = '';
    let pendingDeleteIds = [];

    const $  = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

    const rows      = () => $$('.admin-table tbody tr');
    const rowById   = id => $('.admin-table tbody tr[data-id="' + id + '"]');
    const cards     = () => $$('.admin-tool-card');
    const cardById  = id => $('.admin-tool-card[data-id="' + id + '"]');
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
            data.deleted.forEach(id => {
                const r = rowById(id); if (r) r.remove();
                const c = cardById(id); if (c) c.remove();
            });
        }
        if (data.states) {
            Object.keys(data.states).forEach(id => {
                const row = rowById(id);
                const card = cardById(id);
                applyState(row, data.states[id]);
                applyCardState(card, data.states[id]);
                if (row) $('.row-check', row).checked = false;
            });
        }

        toast(data.message);
        refresh();
    }

    // ---------- update one row & card visually ----------
    function applyState(row, active) {
        if (!row) return;
        row.dataset.status = active ? 'active' : 'hidden';
        row.style.opacity = active ? '' : '.55';

        const label = $('.status-label', row);
        if (label) {
            label.textContent = active ? 'Active' : 'Hidden';
            label.style.color = active ? 'var(--green)' : 'var(--red)';
        }

        const btn = $('.btn-toggle', row);
        if (btn) {
            btn.textContent = active ? 'Hide' : 'Show';
            btn.style.color = active ? 'var(--amber)' : 'var(--green)';
        }
    }

    function applyCardState(card, active) {
        if (!card) return;
        card.dataset.status = active ? 'active' : 'hidden';
        card.style.opacity = active ? '' : '.7';

        const badge = $('.status-badge', card);
        if (badge) {
            badge.className = 'status-badge status-' + (active ? 'active' : 'hidden');
            badge.textContent = active ? 'Active' : 'Hidden';
        }

        const btn = $('.btn-toggle', card);
        if (btn) {
            btn.textContent = active ? 'Hide' : 'Show';
        }
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
            const matchesFilter = currentFilter === 'all' || r.dataset.status === currentFilter;
            const matchesSearch = !searchQuery || (r.dataset.name || '').toLowerCase().includes(searchQuery);
            const show = matchesFilter && matchesSearch;
            r.style.display = show ? '' : 'none';
            if (!show) $('.row-check', r).checked = false;
        });

        cards().forEach(c => {
            const matchesFilter = currentFilter === 'all' || c.dataset.status === currentFilter;
            const matchesSearch = !searchQuery || (c.dataset.name || '').toLowerCase().includes(searchQuery);
            c.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
        });

        updateSelection();
    }

    function updateSelection() {
        const n = selected().length;
        $('#selCount').textContent = n;
        $('#bulkBar').style.display = n ? 'flex' : 'none';

        const selRows   = selected().map(id => rowById(id));
        const hasActive = selRows.some(r => r && r.dataset.status === 'active');
        const hasHidden = selRows.some(r => r && r.dataset.status === 'hidden');
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

    // Delegated toggle & delete for desktop table
    $('.admin-table tbody').addEventListener('click', e => {
        const toggle = e.target.closest('.btn-toggle');
        if (toggle) { send('toggle', [toggle.dataset.id]); return; }

        const del = e.target.closest('.btn-delete');
        if (del) {
            const row = rowById(del.dataset.id);
            openDeleteModal([del.dataset.id], '"' + (row ? row.dataset.name : 'this tool') + '"');
        }
    });

    // Delegated toggle & delete for mobile cards
    const mobileCardsWrap = $('#mobileCardsList');
    if (mobileCardsWrap) {
        mobileCardsWrap.addEventListener('click', e => {
            const toggle = e.target.closest('.btn-toggle');
            if (toggle) { send('toggle', [toggle.dataset.id]); return; }

            const del = e.target.closest('.btn-delete');
            if (del) {
                const card = cardById(del.dataset.id);
                openDeleteModal([del.dataset.id], '"' + (card ? card.dataset.name : 'this tool') + '"');
            }
        });
    }

    // Search input
    const searchInput = $('#adminToolSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            searchQuery = this.value.trim().toLowerCase();
            refresh();
        });
    }

    $$('.tool-filter').forEach(btn => btn.addEventListener('click', function() {
        $$('.tool-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentFilter = this.dataset.filter;
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
        document.body.style.overflow = 'hidden';
    }
    function closeDeleteModal() {
        $('#deleteToolModalOverlay').style.display = 'none';
        pendingDeleteIds = [];
        document.body.style.overflow = '';
    }
    window.closeDeleteModal = closeDeleteModal;
    $('#deleteCancel').addEventListener('click', closeDeleteModal);
    $('#deleteConfirm').addEventListener('click', () => {
        const ids = pendingDeleteIds.slice();
        closeDeleteModal();
        if (ids.length) send('delete', ids);
    });
})();
</script>

<?php include __DIR__ . '/../partials/foot.php'; ?>