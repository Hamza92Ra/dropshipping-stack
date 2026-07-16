<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
$page_title = 'Affiliate Disclosure';
include __DIR__ . '/header.php';
?>
<div class="page-wrap" style="max-width:720px;padding-top:48px;padding-bottom:80px">
    <h1 style="font-size:30px;font-weight:800;color:var(--ink);margin-bottom:8px">Affiliate Disclosure</h1>
    <p class="text-muted" style="margin-bottom:32px">Last updated: <?= date('F Y') ?></p>

    <div style="line-height:1.9;color:var(--body)">
        <p style="margin-bottom:16px"><?= SITE_NAME ?> participates in affiliate marketing programs. This means that when you click on certain links on our site and make a purchase or sign up, we may receive a commission — at <strong>no additional cost to you</strong>.</p>

        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">What This Means For You</h2>
        <p style="margin-bottom:16px">Our affiliate relationships do <strong>not</strong> influence the content of our reviews. We only recommend tools we believe offer genuine value to dropshippers. Ratings and reviews are based on real-world testing, user feedback, and honest assessment.</p>

        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">How To Identify Affiliate Links</h2>
        <p style="margin-bottom:16px">Any "Visit Site" button or link that goes through <code style="background:var(--bg);padding:2px 6px;border-radius:4px"><?= SITE_URL ?>/go/</code> is an affiliate link. Direct links to websites are not affiliate links.</p>

        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">Our Commitment</h2>
        <p>We are committed to transparency. If you have questions about our affiliate relationships, please contact us at <a href="mailto:<?= SITE_EMAIL ?>" style="color:var(--accent)"><?= SITE_EMAIL ?: 'our email' ?></a>.</p>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
