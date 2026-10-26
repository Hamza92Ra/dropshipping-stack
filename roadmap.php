<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

// Load saved roadmap if logged in
$roadmap = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_roadmap WHERE user_id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $roadmap = $stmt->fetch();
}

$page_title      = 'Dropshipping Roadmap 2026 — Step by Step Guide';
$page_heading    = 'Growth Roadmap';
$page_subheading = 'Structured step-by-step milestones to scale from zero to $50k+/month';
$page_desc       = 'The complete dropshipping roadmap for beginners, intermediate and advanced sellers. Follow the exact steps to build a profitable store.';

include __DIR__ . '/header.php';
?>

<style>
.roadmap-wrap {
    max-width: 960px;
    margin: 0 auto;
    padding: 10px 0 60px;
}

/* Hero Badge */
.roadmap-hero {
    text-align: center;
    margin-bottom: 36px;
}

.roadmap-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--accent-light);
    color: var(--accent);
    font-size: 12px;
    font-weight: 700;
    padding: 5px 16px;
    border-radius: var(--radius-full);
    margin-bottom: 14px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.roadmap-hero h1 {
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.025em;
    margin-bottom: 12px;
    line-height: 1.15;
}

.roadmap-hero h1 span {
    color: var(--accent);
}

.roadmap-hero p {
    font-size: 15px;
    color: var(--muted);
    max-width: 560px;
    margin: 0 auto;
    line-height: 1.65;
}

/* Level Tabs */
.level-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 32px;
    justify-content: center;
    flex-wrap: wrap;
}

.level-tab {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: var(--radius-full);
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--muted);
    transition: all var(--dur-fast) var(--ease);
    box-shadow: var(--shadow-xs);
}

.level-tab:hover {
    border-color: var(--accent);
    color: var(--text);
    background: var(--surface-2);
}

.level-tab.active {
    background: var(--btn-primary-bg);
    border-color: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    box-shadow: var(--shadow-sm);
}

/* Roadmap Levels */
.roadmap-level {
    display: none;
}

.roadmap-level.active {
    display: block;
    animation: fadeRise 0.35s var(--ease) both;
}

/* Level Header Card */
.level-header-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 24px 28px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 24px;
}

.level-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: var(--radius-xl);
    background: var(--accent-light);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.level-header-info {
    flex: 1;
}

.level-header-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 4px;
}

.level-header-sub {
    font-size: 13.5px;
    color: var(--muted);
    line-height: 1.5;
}

.level-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--positive-text);
    background: var(--positive-bg);
    border: 1px solid var(--positive-border);
    padding: 3px 10px;
    border-radius: var(--radius-full);
    margin-top: 8px;
}

/* Progress Section */
.progress-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 22px 28px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 32px;
}

.progress-info {
    flex: 1;
}

.progress-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 10px;
}

.progress-bar-wrap {
    background: var(--surface-2);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-full);
    height: 8px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent), #16a34a);
    border-radius: var(--radius-full);
    transition: width 0.4s var(--ease);
}

.progress-pct {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.02em;
}

/* Phase Section */
.phase-group {
    margin-bottom: 32px;
}

.phase-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}

.phase-number {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 800;
    flex-shrink: 0;
}

.phase-title {
    font-size: 17px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.015em;
}

.phase-time {
    font-size: 12px;
    color: var(--muted);
    font-weight: 600;
    margin-top: 1px;
}

/* Step Cards */
.steps-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.step-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 18px 22px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: flex-start;
    gap: 16px;
    transition: transform var(--dur-fast) var(--ease), box-shadow var(--dur-fast) var(--ease), border-color var(--dur-fast) var(--ease);
}

.step-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
    border-color: rgba(50, 99, 70, 0.25);
}

.step-check {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid var(--border);
    background: var(--surface-2);
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--dur-fast) var(--ease);
}

.step-check:hover {
    border-color: var(--accent);
}

.step-check.done {
    background: var(--positive);
    border-color: var(--positive);
}

.step-check.done::after {
    content: '';
    width: 6px;
    height: 10px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg) translate(-1px, -1px);
}

.step-body {
    flex: 1;
}

.step-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 4px;
    transition: color var(--dur-fast) var(--ease);
}

.step-check.done + .step-body .step-title {
    text-decoration: line-through;
    color: var(--muted);
}

.step-desc {
    font-size: 13.5px;
    color: var(--muted);
    line-height: 1.55;
    margin-bottom: 8px;
}

.step-tool {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: var(--radius-full);
    font-size: 12px;
    font-weight: 700;
    background: var(--accent-light);
    color: var(--accent);
    text-decoration: none;
    transition: all var(--dur-fast) var(--ease);
    border: 1px solid rgba(50, 99, 70, 0.15);
}

.step-tool:hover {
    background: var(--accent);
    color: #fff;
}

.step-tool.step-free {
    background: var(--positive-bg);
    color: var(--positive-text);
    border-color: var(--positive-border);
}

.step-tool.step-free:hover {
    background: var(--positive);
    color: #fff;
}

/* Roadmap CTA Card */
.roadmap-cta-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 36px 32px;
    box-shadow: var(--shadow-sm);
    text-align: center;
    margin-top: 48px;
}

.roadmap-cta-card h3 {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 8px;
}

.roadmap-cta-card p {
    font-size: 14px;
    color: var(--muted);
    margin-bottom: 24px;
}

.roadmap-cta-btns {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
</style>

<div class="roadmap-wrap">

    <div class="roadmap-hero">
        <div class="roadmap-badge">
            <?= icon('compass', '', 14) ?>
            <span>Structured Growth Framework</span>
        </div>
        <h1>The <span>Dropshipping Roadmap</span><br>Built for Consistent Execution</h1>
        <p>Follow vetted steps tailored to your exact milestone — from zero inventory to consistent, profitable $50,000+ months.</p>
    </div>

    <!-- Level Selector Tabs -->
    <div class="level-tabs" role="tablist">
        <button type="button" class="level-tab beginner active" onclick="showLevel('beginner')" role="tab" aria-selected="true">
            <?= icon('package', '', 15) ?>
            <span>Beginner (0 to First Sale)</span>
        </button>
        <button type="button" class="level-tab intermediate" onclick="showLevel('intermediate')" role="tab" aria-selected="false">
            <?= icon('trending-up', '', 15) ?>
            <span>Intermediate ($5k - $20k/mo)</span>
        </button>
        <button type="button" class="level-tab advanced" onclick="showLevel('advanced')" role="tab" aria-selected="false">
            <?= icon('zap', '', 15) ?>
            <span>Advanced ($50k+/mo Scale)</span>
        </button>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- LEVEL 1: BEGINNER -->
    <!-- ═══════════════════════════════════════ -->
    <div class="roadmap-level beginner active" id="level-beginner">

        <div class="level-header-card">
            <div class="level-icon-wrap">
                <?= icon('package', '', 24) ?>
            </div>
            <div class="level-header-info">
                <div class="level-header-title">Beginner Roadmap</div>
                <div class="level-header-sub">Starting from scratch. Follow these foundations in sequence to validate products and capture your first customer sales within 30–60 days.</div>
                <div class="level-header-badge">
                    <?= icon('clock', '', 13) ?>
                    <span>Estimated timeline: 30–60 days to first sales</span>
                </div>
            </div>
        </div>

        <div class="progress-card">
            <div class="progress-info">
                <div class="progress-label">Milestone Progress — <span id="beginner-done">0</span> of <span id="beginner-total">0</span> steps completed</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="beginner-bar" style="width:0%"></div>
                </div>
            </div>
            <div class="progress-pct" id="beginner-pct">0%</div>
        </div>

        <!-- Phase 1 -->
        <div class="phase-group">
            <div class="phase-header">
                <div class="phase-number">1</div>
                <div>
                    <div class="phase-title">Master the Fundamentals</div>
                    <div class="phase-time">Week 1 — Strategy & Market Validation</div>
                </div>
            </div>
            <div class="steps-list">
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Understand the core economics of dropshipping</div>
                        <div class="step-desc">Master the customer journey: store checkout, automated supplier dispatch, delivery window, and profit margin calculation.</div>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Select a dedicated niche</div>
                        <div class="step-desc">Avoid fragmented general catalogs. Choose a focused niche with passionate buyers (pet care, ergonomic work, specialty hobby gear).</div>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Audit competitor ad creatives</div>
                        <div class="step-desc">Analyze high-engagement ads on Meta Ad Library and TikTok Creative Center to discover winning angles and creative hooks.</div>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Validate demand with product spy software</div>
                        <div class="step-desc">Review viral ad metrics, engagement velocity, and trending order volume before committing funds.</div>
                        <a href="/go.php?slug=minea" class="step-tool step-free" target="_blank" rel="noopener">
                            <?= icon('search', '', 12) ?> <span>Try Minea Free</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase 2 -->
        <div class="phase-group">
            <div class="phase-header">
                <div class="phase-number">2</div>
                <div>
                    <div class="phase-title">Store Architecture & Infrastructure</div>
                    <div class="phase-time">Week 2 — Store Build & Compliance</div>
                </div>
            </div>
            <div class="steps-list">
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Deploy high-converting ecommerce storefront</div>
                        <div class="step-desc">Set up Shopify with a fast, modern clean theme. Ensure lightning-fast checkout and mobile responsiveness.</div>
                        <a href="/go.php?slug=shopify" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('shopping-bag', '', 12) ?> <span>Start Shopify Free Trial</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Configure legal & trust architecture</div>
                        <div class="step-desc">Deploy compliant Shipping Policy, Refund Policy, Terms of Service, and clear tracking pages.</div>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Connect direct payment gateways</div>
                        <div class="step-desc">Activate Stripe, Shopify Payments, and PayPal Express for multi-currency settlement.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase 3 -->
        <div class="phase-group">
            <div class="phase-header">
                <div class="phase-number">3</div>
                <div>
                    <div class="phase-title">Sourcing & Supplier Integration</div>
                    <div class="phase-time">Week 3 — Supplier Setup</div>
                </div>
            </div>
            <div class="steps-list">
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Filter products with 3x margin potential</div>
                        <div class="step-desc">Calculate landed costs including shipping and ad spend to protect minimum 30% net margin.</div>
                        <a href="/go.php?slug=sell-the-trend" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('search', '', 12) ?> <span>Browse Sell The Trend</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Connect fast dispatch fulfillment supplier</div>
                        <div class="step-desc">Integrate Zendrop or DSers for streamlined 1-click fulfillment and express delivery options.</div>
                        <a href="/go.php?slug=zendrop" class="step-tool step-free" target="_blank" rel="noopener">
                            <?= icon('package', '', 12) ?> <span>Test Zendrop Free</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Verify physical product sample</div>
                        <div class="step-desc">Order samples to inspect finish, packaging unboxing experience, and actual transit days.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase 4 -->
        <div class="phase-group">
            <div class="phase-header">
                <div class="phase-number">4</div>
                <div>
                    <div class="phase-title">Acquisition & Initial Conversions</div>
                    <div class="phase-time">Week 4 — First Traffic & Sales</div>
                </div>
            </div>
            <div class="steps-list">
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Organic TikTok and Reels short-form videos</div>
                        <div class="step-desc">Produce 2-3 daily demonstrations highlighting hook, problem solved, and immediate call to action.</div>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Launch controlled creative ad testing</div>
                        <div class="step-desc">Deploy test budgets ($15-25/day) across 3 creative variations with AI-generated ad copy.</div>
                        <a href="/go.php?slug=adcreative-ai" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('zap', '', 12) ?> <span>AdCreative.ai Tools</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Install real-time chat & objection handling</div>
                        <div class="step-desc">Engage hesitant checkout visitors and answer sizing and shipping queries instantly.</div>
                        <a href="/go.php?slug=tidio" class="step-tool step-free" target="_blank" rel="noopener">
                            <?= icon('message-square', '', 12) ?> <span>Add Tidio Live Chat</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- LEVEL 2: INTERMEDIATE -->
    <!-- ═══════════════════════════════════════ -->
    <div class="roadmap-level intermediate" id="level-intermediate">

        <div class="level-header-card">
            <div class="level-icon-wrap">
                <?= icon('trending-up', '', 24) ?>
            </div>
            <div class="level-header-info">
                <div class="level-header-title">Intermediate Roadmap</div>
                <div class="level-header-sub">Transitioning beyond initial sales into repeatable, predictable $5,000 to $20,000 monthly profit. Optimize conversion rates and retention.</div>
                <div class="level-header-badge">
                    <?= icon('trending-up', '', 13) ?>
                    <span>Target: $5,000–$20,000/month recurring volume</span>
                </div>
            </div>
        </div>

        <div class="progress-card">
            <div class="progress-info">
                <div class="progress-label">Milestone Progress — <span id="intermediate-done">0</span> of <span id="intermediate-total">0</span> steps completed</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="intermediate-bar" style="width:0%"></div>
                </div>
            </div>
            <div class="progress-pct" id="intermediate-pct">0%</div>
        </div>

        <!-- Intermediate Phases -->
        <div class="phase-group">
            <div class="phase-header">
                <div class="phase-number">1</div>
                <div>
                    <div class="phase-title">Attribution & Unit Economics</div>
                    <div class="phase-time">Week 1 — Financial Clarity</div>
                </div>
            </div>
            <div class="steps-list">
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Track net profit and multi-touch attribution</div>
                        <div class="step-desc">Audit blended ROAS, payment fees, shipping costs, and customer acquisition cost across channels.</div>
                        <a href="/go.php?slug=triple-whale" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('bar-chart-2', '', 12) ?> <span>Audit with Triple Whale</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Deploy photo social proof & video reviews</div>
                        <div class="step-desc">Incorporate UGC reviews and verified buyer badges to lift conversion rates by 25-40%.</div>
                        <a href="/go.php?slug=loox" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('star', '', 12) ?> <span>Integrate Loox Reviews</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="phase-group">
            <div class="phase-header">
                <div class="phase-number">2</div>
                <div>
                    <div class="phase-title">Automated Retention & Lifecycle Email</div>
                    <div class="phase-time">Week 2 — Automated Revenue</div>
                </div>
            </div>
            <div class="steps-list">
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Build core Klaviyo automated flows</div>
                        <div class="step-desc">Launch high-converting Abandoned Cart sequence, Welcome series, and Post-Purchase cross-sell flows.</div>
                        <a href="/go.php?slug=klaviyo" class="step-tool step-free" target="_blank" rel="noopener">
                            <?= icon('mail', '', 12) ?> <span>Deploy Klaviyo Free</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Switch to automated fulfillment & fast domestic shipping</div>
                        <div class="step-desc">Move off basic AliExpress to domestic warehouse fulfillment via AutoDS or Spocket to lower delivery times.</div>
                        <a href="/go.php?slug=autods" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('settings', '', 12) ?> <span>Automate with AutoDS</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- LEVEL 3: ADVANCED -->
    <!-- ═══════════════════════════════════════ -->
    <div class="roadmap-level advanced" id="level-advanced">

        <div class="level-header-card">
            <div class="level-icon-wrap">
                <?= icon('zap', '', 24) ?>
            </div>
            <div class="level-header-info">
                <div class="level-header-title">Advanced Scale Roadmap</div>
                <div class="level-header-sub">Scaling beyond $50,000 monthly run-rate. Building enterprise-grade operations, private labeling, attribution models, and asset equity.</div>
                <div class="level-header-badge">
                    <?= icon('zap', '', 13) ?>
                    <span>Scale Target: $50,000–$100,000+/month operating profit</span>
                </div>
            </div>
        </div>

        <div class="progress-card">
            <div class="progress-info">
                <div class="progress-label">Milestone Progress — <span id="advanced-done">0</span> of <span id="advanced-total">0</span> steps completed</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="advanced-bar" style="width:0%"></div>
                </div>
            </div>
            <div class="progress-pct" id="advanced-pct">0%</div>
        </div>

        <div class="phase-group">
            <div class="phase-header">
                <div class="phase-number">1</div>
                <div>
                    <div class="phase-title">Enterprise Attribution & Omnichannel Scale</div>
                    <div class="phase-time">Month 1 — Scale Ad Budgets</div>
                </div>
            </div>
            <div class="steps-list">
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Enterprise Ad Attribution & Competitor Tracking</div>
                        <div class="step-desc">Deploy Northbeam or Triple Whale pixel server-side events, and uncover competitor creative trends with AdSpy.</div>
                        <a href="/go.php?slug=adspy" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('search', '', 12) ?> <span>Spy with AdSpy</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Private Label Packaging & Bulk Logistics</div>
                        <div class="step-desc">Migrate from dropshipping to private label inventory with custom packaging and 3-5 day regional delivery.</div>
                        <a href="/go.php?slug=spocket" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('package', '', 12) ?> <span>Private Label on Spocket</span>
                        </a>
                    </div>
                </div>
                <div class="step-card">
                    <div class="step-check" onclick="toggleStep(this)"></div>
                    <div class="step-body">
                        <div class="step-title">Automate customer service desk with Gorgias</div>
                        <div class="step-desc">Free up 15+ hours weekly by integrating multi-channel support tickets, order lookups, and auto-macros.</div>
                        <a href="/go.php?slug=gorgias" class="step-tool" target="_blank" rel="noopener">
                            <?= icon('message-square', '', 12) ?> <span>Configure Gorgias</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Roadmap Navigation CTA -->
    <div class="roadmap-cta-card">
        <h3>Ready to Configure Your Infrastructure?</h3>
        <p>Use our interactive tools to assemble, price, and compare your custom store stack.</p>
        <div class="roadmap-cta-btns">
            <a href="/stack-builder.php" class="btn-primary" style="padding:11px 22px">
                <?= icon('layers', '', 15) ?>
                <span>Build My Custom Stack</span>
            </a>
            <a href="/calculator.php" class="btn-secondary" style="padding:11px 20px">
                <?= icon('calculator', '', 15) ?>
                <span>Profit Calculator</span>
            </a>
            <a href="/compare.php" class="btn-secondary" style="padding:11px 20px">
                <?= icon('scale', '', 15) ?>
                <span>Compare Tools</span>
            </a>
        </div>
    </div>

</div>

<script>
const savedRoadmap = <?= json_encode([
    'beginner'     => json_decode($roadmap['beginner_steps'] ?? '[]', true) ?: [],
    'intermediate' => json_decode($roadmap['intermediate_steps'] ?? '[]', true) ?: [],
    'advanced'     => json_decode($roadmap['advanced_steps'] ?? '[]', true) ?: []
]) ?>;

function showLevel(level) {
    document.querySelectorAll('.roadmap-level').forEach(l => l.classList.remove('active'));
    document.querySelectorAll('.level-tab').forEach(t => {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
    });

    const targetLevel = document.getElementById('level-' + level);
    if (targetLevel) targetLevel.classList.add('active');

    const targetTab = document.querySelector('.level-tab.' + level);
    if (targetTab) {
        targetTab.classList.add('active');
        targetTab.setAttribute('aria-selected', 'true');
    }
}

function toggleStep(el) {
    el.classList.toggle('done');
    updateProgress();
    saveRoadmap();
}

function updateProgress() {
    ['beginner', 'intermediate', 'advanced'].forEach(level => {
        const container = document.getElementById('level-' + level);
        if (!container) return;

        const all = container.querySelectorAll('.step-check');
        const done = container.querySelectorAll('.step-check.done');
        const pct = all.length ? Math.round((done.length / all.length) * 100) : 0;

        const doneEl = document.getElementById(level + '-done');
        const totalEl = document.getElementById(level + '-total');
        const pctEl = document.getElementById(level + '-pct');
        const barEl = document.getElementById(level + '-bar');

        if (doneEl) doneEl.textContent = done.length;
        if (totalEl) totalEl.textContent = all.length;
        if (pctEl) pctEl.textContent = pct + '%';
        if (barEl) barEl.style.width = pct + '%';
    });
}

async function saveRoadmap() {
    const data = {};
    ['beginner', 'intermediate', 'advanced'].forEach(level => {
        data[level] = [];
        document.querySelectorAll('#level-' + level + ' .step-check').forEach(step => {
            data[level].push(step.classList.contains('done'));
        });
    });

    try {
        await fetch('/save-roadmap.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
    } catch (err) {
        console.error('Roadmap save error:', err);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    ['beginner', 'intermediate', 'advanced'].forEach(level => {
        const checks = document.querySelectorAll('#level-' + level + ' .step-check');
        (savedRoadmap[level] || []).forEach((done, index) => {
            if (done && checks[index]) {
                checks[index].classList.add('done');
            }
        });
    });
    updateProgress();
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
