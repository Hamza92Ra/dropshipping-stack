<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_admin();

$cats = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$error = '';

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_verify()) {
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        // Build a unique slug
        $baseSlug = slugify($name);
        $slug = $baseSlug;
        $i = 2;
        while (true) {
            $check = $pdo->prepare("SELECT id FROM tools WHERE slug = ?");
            $check->execute([$slug]);
            if (!$check->fetch()) break;
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        $fields = [
            'name'           => $name,
            'slug'           => $slug,
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
            'pros'           => json_encode(array_values(array_filter(array_map('trim', explode("\n", $_POST['pros'] ?? ''))))),
            'cons'           => json_encode(array_values(array_filter(array_map('trim', explode("\n", $_POST['cons'] ?? ''))))),
            'rating'         => 0,
            'review_count'   => 0,
            'clicks'         => 0,
        ];

        $columns = implode(',', array_keys($fields));
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $pdo->prepare("INSERT INTO tools ($columns) VALUES ($placeholders)")
            ->execute(array_values($fields));

        $_SESSION['flash'] = "\"$name\" was added successfully.";
        redirect('/dropshipping/admin/tools/');
    }
}

$page = 'tools';
include __DIR__ . '/../partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Add Tool</h1>
            <a href="/dropshipping/admin/tools/" style="font-size:13px;color:var(--accent)">← Back to Tools</a>
        </div>

        <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:28px;max-width:720px">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" required autofocus>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id">
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Tagline</label>
                <input type="text" name="tagline">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" style="min-height:120px"></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Affiliate Link</label>
                    <input type="url" name="affiliate_link">
                </div>
                <div class="form-group">
                    <label>Website URL</label>
                    <input type="url" name="website_url">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px">
                <div class="form-group">
                    <label>Commission</label>
                    <input type="text" name="commission">
                </div>
                <div class="form-group">
                    <label>Price Type</label>
                    <select name="price_type">
                        <option value="free">Free</option>
                        <option value="freemium">Freemium</option>
                        <option value="paid" selected>Paid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price From ($/mo)</label>
                    <input type="number" name="price_from" step="0.01" min="0" value="0">
                </div>
            </div>

            <div class="form-group">
                <label>Pros (one per line)</label>
                <textarea name="pros" style="min-height:80px;font-family:monospace"></textarea>
            </div>
            <div class="form-group">
                <label>Cons (one per line)</label>
                <textarea name="cons" style="min-height:80px;font-family:monospace"></textarea>
            </div>

            <div style="display:flex;gap:24px;margin-bottom:20px">
                <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
                    <input type="checkbox" name="is_featured"> Featured
                </label>
                <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer">
                    <input type="checkbox" name="is_active" checked> Active
                </label>
            </div>

            <button type="submit" class="btn-primary" style="max-width:180px">Add Tool</button>
        </form>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../partials/foot.php'; ?>