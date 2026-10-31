<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
$canonical_url   = 'https://hdropshipping.com/about.php';
$page_title      = 'About DropshippingStack — Independent Software Directory';
$page_heading    = 'About ' . SITE_NAME;
$page_subheading = 'Our editorial mission, methodology, and vetting standards';
$page_desc       = 'Learn about DropshippingStack: our independent software testing methodology, community vetting process, and mission to help dropshippers build profitable stores.';

$breadcrumb_items = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'About', 'url' => '/about.php']
];

include __DIR__ . '/header.php';
?>
<div class="page-wrap" style="max-width:760px;padding-top:24px;padding-bottom:80px">
    <nav class="breadcrumb-nav" aria-label="Breadcrumb" style="margin-bottom:20px;display:flex;align-items:center;gap:8px;font-size:13px;color:var(--muted)">
        <a href="/index.php" style="color:var(--muted)">Home</a>
        <?= icon('chevron-right', '', 12) ?>
        <span style="color:var(--text);font-weight:600">About</span>
    </nav>
    <h1 style="font-size:30px;font-weight:800;color:var(--ink);margin-bottom:8px">About <?= SITE_NAME ?></h1>
    <div style="line-height:1.9;color:var(--body);margin-top:24px">
        <p style="margin-bottom:16px"><?= SITE_NAME ?> is an independent directory of dropshipping tools and software. We review, rate, and compare every major tool so you can make informed decisions without wasting money.</p>
        <p style="margin-bottom:16px">We cover everything from store builders and product research tools to email marketing, analytics, and automation platforms.</p>
        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">How We Review Tools</h2>
        <p style="margin-bottom:16px">Each tool is evaluated on ease of use, value for money, feature set, customer support, and real user feedback. Ratings are a combination of our editorial score and community reviews.</p>
        <h2 style="font-size:18px;font-weight:700;color:var(--ink);margin:28px 0 12px">Affiliate Disclosure</h2>
        <p>Some links on this site are affiliate links — <a href="/affiliate-disclosure.php" style="color:var(--accent)">read our full disclosure</a>. Affiliate relationships do not affect our reviews or ratings.</p>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
