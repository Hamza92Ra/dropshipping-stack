<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

http_response_code(404);

$page_title      = '404 - Page Not Found';
$page_desc       = 'The page or tool you are looking for does not exist or has been moved.';
$noindex         = true;
$page_heading    = '404 Not Found';
$page_subheading = 'The requested resource could not be found';

include __DIR__ . '/header.php';
?>

<div class="page-wrap" style="max-width:860px;margin:0 auto;padding:48px 16px 80px;text-align:center">
    <div style="display:inline-flex;align-items:center;justify-content:center;width:72px;height:72px;border-radius:20px;background:var(--accent-light);color:var(--accent);margin-bottom:20px">
        <?= icon('compass', '', 36) ?>
    </div>

    <h1 style="font-size:32px;font-weight:800;letter-spacing:-0.02em;color:var(--text);margin:0 0 12px">
        Page Not Found (404)
    </h1>

    <p style="font-size:16px;color:var(--muted);max-width:540px;margin:0 auto 32px;line-height:1.6">
        Sorry, the page, software review, or category you requested cannot be found. It may have been moved or renamed.
    </p>

    <!-- Search Form -->
    <div style="max-width:480px;margin:0 auto 40px">
        <form action="/search.php" method="GET" style="display:flex;gap:8px">
            <input type="search"
                   name="q"
                   placeholder="Search 20+ vetted dropshipping tools..."
                   aria-label="Search tools"
                   style="flex:1;padding:12px 18px;border-radius:999px;border:1px solid var(--border);background:var(--surface);color:var(--text);font-size:14px;outline:none">
            <button type="submit" class="btn-primary" style="padding:12px 24px;border-radius:999px;white-space:nowrap">
                <?= icon('search', '', 16) ?>
                <span>Search</span>
            </button>
        </form>
    </div>

    <!-- Helpful Links Grid -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-2xl);padding:32px;text-align:left;box-shadow:var(--shadow-sm);margin-bottom:32px">
        <h2 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 16px;letter-spacing:-0.01em">
            Explore Popular Software Categories
        </h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:12px;margin-bottom:24px">
            <a href="/category/store-builders" class="cat-pill" style="justify-content:flex-start;padding:10px 14px">
                <span>🏪 Store Builders (Shopify, WooCommerce)</span>
            </a>
            <a href="/category/product-research" class="cat-pill" style="justify-content:flex-start;padding:10px 14px">
                <span>🔍 Product Research (Minea, AdSpy)</span>
            </a>
            <a href="/category/suppliers" class="cat-pill" style="justify-content:flex-start;padding:10px 14px">
                <span>📦 Sourcing &amp; Suppliers (Zendrop, Spocket)</span>
            </a>
            <a href="/category/marketing" class="cat-pill" style="justify-content:flex-start;padding:10px 14px">
                <span>📣 Marketing &amp; SMS (Klaviyo, Omnisend)</span>
            </a>
            <a href="/category/analytics" class="cat-pill" style="justify-content:flex-start;padding:10px 14px">
                <span>📊 Analytics &amp; Attribution (Triple Whale)</span>
            </a>
            <a href="/category/automation" class="cat-pill" style="justify-content:flex-start;padding:10px 14px">
                <span>⚙️ Fulfillment Automation (AutoDS, DSers)</span>
            </a>
        </div>

        <h2 style="font-size:17px;font-weight:800;color:var(--text);margin:0 0 16px;letter-spacing:-0.01em">
            Free Dropshipping Growth Tools
        </h2>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a href="/roadmap.php" class="btn-secondary" style="padding:10px 18px;font-size:13.5px">
                <?= icon('compass', '', 15) ?>
                <span>Execution Roadmap</span>
            </a>
            <a href="/stack-builder.php" class="btn-secondary" style="padding:10px 18px;font-size:13.5px">
                <?= icon('layers', '', 15) ?>
                <span>Stack Builder</span>
            </a>
            <a href="/calculator.php" class="btn-secondary" style="padding:10px 18px;font-size:13.5px">
                <?= icon('calculator', '', 15) ?>
                <span>Profit Calculator</span>
            </a>
            <a href="/compare.php" class="btn-secondary" style="padding:10px 18px;font-size:13.5px">
                <?= icon('scale', '', 15) ?>
                <span>Compare Tools</span>
            </a>
            <a href="/index.php" class="btn-primary" style="padding:10px 20px;font-size:13.5px">
                <?= icon('arrow-left', '', 15) ?>
                <span>Back to Homepage</span>
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
