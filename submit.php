<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$page_title = 'Submit a Tool';
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $flash = '<div class="flash flash-error">⚠ Security check failed. Please try again.</div>';
    } else {
        $name    = trim($_POST['tool_name'] ?? '');
        $url     = trim($_POST['website_url'] ?? '');
        $desc    = trim($_POST['description'] ?? '');
        $email   = trim($_POST['contact_email'] ?? '');
        $plan    = in_array($_POST['plan'] ?? '', ['free','featured']) ? $_POST['plan'] : 'free';

        if ($name && $url && filter_var($url, FILTER_VALIDATE_URL)) {
            $ins = $pdo->prepare("INSERT INTO submissions (tool_name, website_url, description, contact_email, plan) VALUES (?,?,?,?,?)");
            $ins->execute([$name, $url, $desc, $email, $plan]);
            $flash = '<div class="flash flash-success">✓ Thanks! Your submission has been received. We\'ll review it within 48 hours.</div>';
        } else {
            $flash = '<div class="flash flash-error">⚠ Please provide a valid tool name and URL.</div>';
        }
    }
}

include __DIR__ . '/header.php';
?>

<div class="submit-wrap">
    <div class="submit-card">
        <h1>Submit a Tool</h1>
        <p class="sub">Know a great dropshipping tool we haven't covered? Submit it below and we'll review it within 48 hours.</p>

        <?= $flash ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label>Tool Name *</label>
                <input type="text" name="tool_name" placeholder="e.g. Shopify, Zendrop" required maxlength="100">
            </div>

            <div class="form-group">
                <label>Website URL *</label>
                <input type="url" name="website_url" placeholder="https://example.com" required>
            </div>

            <div class="form-group">
                <label>Short Description</label>
                <textarea name="description" placeholder="What does this tool do? Who is it for?"></textarea>
            </div>

            <div class="form-group">
                <label>Your Email (optional)</label>
                <input type="email" name="contact_email" placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label>Listing Type</label>
                <div class="plan-grid">
                    <label class="plan-option selected">
                        <input type="radio" name="plan" value="free" checked>
                        <div class="plan-title">Free Listing</div>
                        <div class="plan-price">$0 — Standard review</div>
                        <div class="plan-feature">Basic placement</div>
                    </label>
                    <label class="plan-option">
                        <input type="radio" name="plan" value="featured">
                        <div class="plan-title">⭐ Featured Listing</div>
                        <div class="plan-price">Contact us</div>
                        <div class="plan-feature">Top placement + badge</div>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-primary">Submit Tool →</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
