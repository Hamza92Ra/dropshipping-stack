<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
$canonical_url   = 'https://hdropshipping.com/privacy.php';
$page_title      = 'Privacy Policy — ' . SITE_NAME;
$page_heading    = 'Privacy Policy';
$page_subheading = 'How we collect, use, and protect your account data';
$page_desc       = 'Privacy Policy for ' . SITE_NAME . '. Learn about the minimal data we collect, our cookie usage, and how your information is protected.';

$breadcrumb_items = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Privacy Policy', 'url' => '/privacy.php']
];

include __DIR__ . '/header.php';
?>
<div class="page-wrap" style="max-width:760px;padding-top:24px;padding-bottom:80px">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb" style="margin-bottom:20px;display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
        <a href="/index.php" style="color:var(--muted)">Home</a>
        <?= icon('chevron-right', '', 12) ?>
        <span style="color:var(--text);font-weight:600">Privacy Policy</span>
    </nav>
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
