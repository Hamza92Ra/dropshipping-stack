<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
$page_title = 'Privacy Policy';
include __DIR__ . '/header.php';
?>
<div class="page-wrap" style="max-width:720px;padding-top:48px;padding-bottom:80px">
    <h1 style="font-size:30px;font-weight:800;color:var(--ink);margin-bottom:8px">Privacy Policy</h1>
    <p class="text-muted" style="margin-bottom:32px">Last updated: <?= date('F Y') ?></p>

    <div style="line-height:1.9;color:var(--body)">
        <p style="margin-bottom:16px"><?= SITE_NAME ?> ("we", "our", "us") is committed to protecting your privacy.</p>

        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">What We Collect</h2>
        <p style="margin-bottom:16px">We collect information you voluntarily provide (e.g. review submissions, tool submissions including your name and email). We also collect anonymised click data (hashed IP addresses) to track which tools are popular.</p>

        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">Cookies</h2>
        <p style="margin-bottom:16px">We use a single session cookie to protect form submissions (CSRF protection). We do not use tracking cookies or third-party advertising cookies.</p>

        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">Third Parties</h2>
        <p style="margin-bottom:16px">Clicking affiliate links will take you to third-party websites with their own privacy policies. We are not responsible for their data practices.</p>

        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">Contact</h2>
        <p>For privacy enquiries: <a href="mailto:<?= SITE_EMAIL ?>" style="color:var(--accent)"><?= SITE_EMAIL ?: 'contact us' ?></a></p>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
