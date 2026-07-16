<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_admin();

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM tools WHERE slug = ?");
$stmt->execute([$slug]);
$t = $stmt->fetch();
if (!$t) redirect('/dropshipping/admin/tools/');

$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $fields = [
        'name'           => trim($_POST['name'] ?? ''),
        'tagline'        => trim($_POST['tagline'] ?? ''),
        'description'    => trim($_POST['description'] ?? ''),
        'affiliate_link' => trim($_POST['affiliate_link'] ?? ''),
        'website_url'    => trim($_POST['website_url'] ?? ''),
        'commission'     => trim($_POST['commission'] ?? ''),
        'price_type'     => $_POST['price_type'] ?? 'paid',
        'price_from'     => (float)($_POST['price_from'] ?? 0),
        'is_featured'    => isset($_POST['is_featured']) ? 1 : 0,
        'is_active'      => isset($_POST['is_active']) ? 1 : 0,
        'category_id'    => (int)($_POST['category_id'] ?? 0),
        'pros'           => $_POST['pros'] ?? '[]',
        'cons'           => $_POST['cons'] ?? '[]',
    ];

    $pdo->prepare("UPDATE tools SET name=?,tagline=?,description=?,affiliate_link=?,website_url=?,commission=?,price_type=?,price_from=?,is_featured=?,is_active=?,category_id=?,pros=?,cons=? WHERE id=?")
        ->execute(array_merge(array_values($fields), [$t['id']]));

    $_SESSION['flash'] = 'Tool updated successfully.';
    redirect('/dropshipping/admin/tools/');
}

$page = 'tools';
include __DIR__ . '/../partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Edit: <?= e($t['name']) ?></h1>
            <a href="/dropshipping/admin/tools/" style="font-size:13px;color:var(--accent)">← Back to Tools</a>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:720px">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?= e($t['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $t['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Tagline</label>
                <input type="text" name="tagline" value="<?= e($t['tagline']) ?>">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" style="min-height:120px"><?= e($t['description']) ?></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Affiliate Link</label>
                    <input type="url" name="affiliate_link" value="<?= e($t['affiliate_link']) ?>">
                </div>
                <div class="form-group">
                    <label>Website URL</label>
                    <input type="url" name="website_url" value="<?= e($t['website_url']) ?>">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Commission</label>
                    <input type="text" name="commission" value="<?= e($t['commission']) ?>">
                </div>
                <div class="form-group">
                    <label>Price Type</label>
                    <select name="price_type">
                        <option value="free" <?= $t['price_type']==='free'?'selected':'' ?>>Free</option>
                        <option value="freemium" <?= $t['price_type']==='freemium'?'selected':'' ?>>Freemium</option>
                        <option value="paid" <?= $t['price_type']==='paid'?'selected':'' ?>>Paid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price From ($/mo)</label>
                    <input type="number" name="price_from" value="<?= $t['price_from'] ?>" step="0.01" min="0">
                </div>
            </div>

            <div class="form-group">
                <label>Pros (one per line)</label>
                <textarea name="pros" style="min-height:80px;font-family:monospace"><?= e(implode("\n", json_decode($t['pros']??'[]',true)?:[])) ?></textarea>
            </div>
            <div class="form-group">
                <label>Cons (one per line)</label>
                <textarea name="cons" style="min-height:80px;font-family:monospace"><?= e(implode("\n", json_decode($t['cons']??'[]',true)?:[])) ?></textarea>
            </div>

            <div style="display:flex;gap:24px;margin-bottom:20px">
                <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
                    <input type="checkbox" name="is_featured" <?= $t['is_featured']?'checked':'' ?>> Featured
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
                    <input type="checkbox" name="is_active" <?= $t['is_active']?'checked':'' ?>> Active
                </label>
            </div>

            <button type="submit" class="btn-primary" style="max-width:180px">Save Changes</button>
        </form>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../partials/foot.php'; ?>
