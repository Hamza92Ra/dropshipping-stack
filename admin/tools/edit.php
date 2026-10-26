<?php
@define('APP', true);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../functions.php';
require_admin();

$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM tools WHERE slug = ?");
$stmt->execute([$slug]);
$t = $stmt->fetch();
if (!$t) redirect('/admin/tools/');

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
        'pros'           => json_encode(array_values(array_filter(array_map('trim', explode("\n", $_POST['pros'] ?? ''))))),
        'cons'           => json_encode(array_values(array_filter(array_map('trim', explode("\n", $_POST['cons'] ?? ''))))),
    ];

    $pdo->prepare("UPDATE tools SET name=?,tagline=?,description=?,affiliate_link=?,website_url=?,commission=?,price_type=?,price_from=?,is_featured=?,is_active=?,category_id=?,pros=?,cons=? WHERE id=?")
        ->execute(array_merge(array_values($fields), [$t['id']]));

    $_SESSION['flash'] = 'Tool updated successfully.';
    redirect('/admin/tools/');
}

$domain = !empty($t['website_url']) ? parse_url($t['website_url'], PHP_URL_HOST) : '';

$page = 'tools';
include __DIR__ . '/../partials/head.php';
?>
<div class="admin-layout">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
    <main class="admin-main">
        <div class="admin-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
            <div>
                <a href="/admin/tools/" style="font-size:13px;font-weight:600;color:var(--accent);display:inline-flex;align-items:center;gap:4px;margin-bottom:6px">
                    &larr; Back to Tools List
                </a>
                <h1 style="margin:0;font-size:24px">Edit: <?= e($t['name']) ?></h1>
            </div>
            <a href="/tool/index.php?slug=<?= e($t['slug']) ?>" target="_blank" class="admin-card-btn view" style="padding:8px 16px;font-size:13px">
                <?= icon('external-link', '', 14) ?>
                <span>View Public Page</span>
            </a>
        </div>

        <form method="POST" id="editToolForm" class="admin-edit-form">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Group 1: Basic Information -->
            <div class="admin-form-section">
                <h2 class="admin-form-section-title">
                    <?= icon('package', '', 16) ?>
                    <span>Basic Information</span>
                </h2>

                <div class="admin-form-grid-2">
                    <div class="form-group">
                        <label for="inputName">Software Name *</label>
                        <input type="text" id="inputName" name="name" value="<?= e($t['name']) ?>" required class="admin-input">
                    </div>
                    <div class="form-group">
                        <label for="selectCategory">Category *</label>
                        <select id="selectCategory" name="category_id" class="admin-input">
                            <?php foreach ($cats as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $t['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="inputTagline">One-Line Tagline</label>
                    <input type="text" id="inputTagline" name="tagline" value="<?= e($t['tagline']) ?>" placeholder="e.g. All-in-one dropshipping automation platform" class="admin-input">
                </div>

                <div class="form-group">
                    <label for="inputDesc">Editorial Description & Overview</label>
                    <textarea id="inputDesc" name="description" rows="5" class="admin-input" style="min-height:120px"><?= e($t['description']) ?></textarea>
                </div>
            </div>

            <!-- Group 2: Pricing & Affiliate Links -->
            <div class="admin-form-section">
                <h2 class="admin-form-section-title">
                    <?= icon('dollar-sign', '', 16) ?>
                    <span>Pricing &amp; Affiliate Setup</span>
                </h2>

                <div class="admin-form-grid-2">
                    <div class="form-group">
                        <label for="inputUrl">Official Website URL</label>
                        <input type="url" id="inputUrl" name="website_url" value="<?= e($t['website_url']) ?>" placeholder="https://example.com" class="admin-input">
                    </div>
                    <div class="form-group">
                        <label for="inputAffiliate">Affiliate Redirect URL</label>
                        <input type="url" id="inputAffiliate" name="affiliate_link" value="<?= e($t['affiliate_link']) ?>" placeholder="https://partner.example.com/..." class="admin-input">
                    </div>
                </div>

                <div class="admin-form-grid-3">
                    <div class="form-group">
                        <label for="selectPriceType">Pricing Model</label>
                        <select id="selectPriceType" name="price_type" class="admin-input">
                            <option value="free" <?= $t['price_type']==='free'?'selected':'' ?>>Free</option>
                            <option value="freemium" <?= $t['price_type']==='freemium'?'selected':'' ?>>Freemium</option>
                            <option value="paid" <?= $t['price_type']==='paid'?'selected':'' ?>>Paid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="inputPriceFrom">Starting Fee ($/mo)</label>
                        <input type="number" id="inputPriceFrom" name="price_from" value="<?= $t['price_from'] ?>" step="0.01" min="0" class="admin-input">
                    </div>
                    <div class="form-group">
                        <label for="inputCommission">Commission Details</label>
                        <input type="text" id="inputCommission" name="commission" value="<?= e($t['commission']) ?>" placeholder="e.g. 20% recurring" class="admin-input">
                    </div>
                </div>
            </div>

            <!-- Group 3: Strengths & Limitations -->
            <div class="admin-form-section">
                <h2 class="admin-form-section-title">
                    <?= icon('scale', '', 16) ?>
                    <span>Pros &amp; Cons Analysis</span>
                </h2>

                <div class="admin-form-grid-2">
                    <div class="form-group">
                        <label for="inputPros">Strengths / Pros (one per line)</label>
                        <textarea id="inputPros" name="pros" rows="4" class="admin-input" style="font-family:monospace;min-height:90px"><?= e(implode("\n", json_decode($t['pros']??'[]',true)?:[])) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="inputCons">Limitations / Cons (one per line)</label>
                        <textarea id="inputCons" name="cons" rows="4" class="admin-input" style="font-family:monospace;min-height:90px"><?= e(implode("\n", json_decode($t['cons']??'[]',true)?:[])) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Group 4: Visibility & Promotion -->
            <div class="admin-form-section">
                <h2 class="admin-form-section-title">
                    <?= icon('shield-check', '', 16) ?>
                    <span>Listing Status &amp; Promotion</span>
                </h2>

                <div style="display:flex;gap:28px;flex-wrap:wrap">
                    <label class="admin-checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" <?= $t['is_featured']?'checked':'' ?>>
                        <div>
                            <strong>Featured Spotlight</strong>
                            <span style="font-size:12px;color:var(--muted);display:block">Displays badge on homepage &amp; tool lists</span>
                        </div>
                    </label>
                    <label class="admin-checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= $t['is_active']?'checked':'' ?>>
                        <div>
                            <strong>Active in Public Directory</strong>
                            <span style="font-size:12px;color:var(--muted);display:block">Visible to all visitors and stack builders</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Sticky Save Bar for Mobile & Desktop -->
            <div class="admin-form-sticky-bar">
                <div class="admin-form-sticky-inner">
                    <a href="/admin/tools/" class="btn-cancel-action" style="padding:10px 18px;text-decoration:none;font-size:13.5px;color:var(--muted)">Cancel</a>
                    <button type="submit" class="admin-save-btn">
                        <?= icon('check', '', 16) ?>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>
<?php include __DIR__ . '/../partials/foot.php'; ?>