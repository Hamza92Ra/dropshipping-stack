<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$page_title = 'Privacy Policy';
$page_desc  = 'Our privacy policy';

include __DIR__ . '/header.php';
?>

<div class="submit-wrap" style="max-width:760px">
    <div class="submit-card">
        <h1>Terms &amp; Privacy Policy</h1>
        <p class="sub">Last updated: <?= date('F Y') ?></p>

        <div class="detail-section">
            <h3>1. Acceptance of terms</h3>
            <p class="detail-desc">By creating an account on this site, you agree to these terms and our handling of your data as described below.</p>
        </div>

        <div class="detail-section">
            <h3>2. Data we collect</h3>
            <p class="detail-desc">We store your username, email address, and password (hashed, never in plain text) to operate your account, along with any bookmarks, price alerts, or reviews you create.</p>
        </div>

        <div class="detail-section">
            <h3>3. How we use your data</h3>
            <p class="detail-desc">Your email is used only for account verification, password resets, and price alert notifications you opt into. We do not sell or share your data with third parties.</p>
        </div>

        <div class="detail-section">
            <h3>4. Affiliate disclosure</h3>
            <p class="detail-desc">Some tool listings include affiliate links. We may earn a commission if you sign up through them, at no extra cost to you.</p>
        </div>

        <div class="detail-section">
            <h3>5. Your rights</h3>
            <p class="detail-desc">You can request account deletion or data export at any time by contacting us using the details in the footer.</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>