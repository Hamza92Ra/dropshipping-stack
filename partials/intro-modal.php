<?php
if (!defined('APP')) die('Direct access not allowed');
require_once __DIR__ . '/../functions.php';

$shopifyLogo = tool_logo_url(['slug' => 'shopify', 'website_url' => 'https://shopify.com']);
$mineaLogo   = tool_logo_url(['slug' => 'minea', 'website_url' => 'https://minea.com']);
$zendropLogo = tool_logo_url(['slug' => 'zendrop', 'website_url' => 'https://zendrop.com']);
$wooLogo     = tool_logo_url(['slug' => 'woocommerce', 'website_url' => 'https://woocommerce.com']);
$dsersLogo   = tool_logo_url(['slug' => 'dsers', 'website_url' => 'https://dsers.com']);
$canvaLogo   = tool_logo_url(['slug' => 'canva-pro', 'website_url' => 'https://canva.com']);
?>
<!-- ═══════════════════════════════════════════════════════════
     INTRO ONBOARDING (Task 3: Full-screen interactive on phones, centered on desktop)
     ═══════════════════════════════════════════════════════════ -->
<div class="intro-overlay" id="introOverlay" role="dialog" aria-modal="true" aria-label="Welcome to DropshippingStack">
    <div class="intro-container">

        <!-- Animated Ambient Gradient Mesh (Phone background) -->
        <div class="intro-mesh" aria-hidden="true">
            <div class="intro-blob blob-1"></div>
            <div class="intro-blob blob-2"></div>
        </div>

        <!-- Top Header Bar -->
        <header class="intro-topbar">
            <div class="intro-brand">
                <span class="intro-brand-icon"><?= icon('sparkles', '', 20) ?></span>
                <span class="intro-brand-name"><?= e(SITE_NAME) ?></span>
            </div>

            <!-- Animated Step Progress Dots -->
            <nav class="intro-progress-dots" aria-label="Intro Steps">
                <button type="button" class="intro-dot active" data-step="0" aria-label="Step 1: Discover vetted tools" aria-current="step"></button>
                <button type="button" class="intro-dot" data-step="1" aria-label="Step 2: Compare and decide"></button>
                <button type="button" class="intro-dot" data-step="2" aria-label="Step 3: Build your stack"></button>
            </nav>

            <button type="button" class="intro-skip-btn" id="introSkipBtn" onclick="finishIntro('skip')">
                <span>Skip</span>
                <?= icon('arrow-right', '', 14) ?>
            </button>
        </header>

        <!-- Slides Viewport -->
        <div class="intro-slides-viewport" id="introViewport">

            <!-- SLIDE 1: Discover vetted tools -->
            <section class="intro-slide active" data-index="0" aria-label="Step 1 of 3: Discover vetted tools">
                <div class="intro-slide-content">
                    <div class="intro-icon-badge">
                        <?= icon('search', '', 24) ?>
                    </div>
                    <span class="intro-step-pill">Step 1 of 3 · Honest Directory</span>
                    <h2 class="intro-title">Discover vetted tools</h2>
                    <p class="intro-subtitle">
                        Honest breakdowns, verified pricing models, and community ROI for top platforms.
                    </p>

                    <!-- Interactive Visual 1: Floating Tool Cards Stack with Drag / Swipe -->
                    <div class="intro-floating-stack" id="introFloatingStack">
                        <div class="stack-card card-0" id="stackCard0" onclick="swipeAwayCard(0)">
                            <div class="stack-card-inner">
                                <img src="<?= e($shopifyLogo) ?>" width="40" height="40" alt="Shopify" loading="lazy">
                                <div class="stack-card-info">
                                    <strong>Shopify</strong>
                                    <span>Store Builder · $29/mo</span>
                                </div>
                                <span class="stack-badge vetted"><?= icon('shield-check', '', 12) ?> Vetted</span>
                            </div>
                            <div class="stack-hint">Tap card to peek next <?= icon('arrow-right', '', 11) ?></div>
                        </div>

                        <div class="stack-card card-1" id="stackCard1" onclick="swipeAwayCard(1)">
                            <div class="stack-card-inner">
                                <img src="<?= e($mineaLogo) ?>" width="40" height="40" alt="Minea" loading="lazy">
                                <div class="stack-card-info">
                                    <strong>Minea</strong>
                                    <span>Ad Spy & Winning Ads · $49/mo</span>
                                </div>
                                <span class="stack-badge vetted"><?= icon('shield-check', '', 12) ?> Top ROI</span>
                            </div>
                            <div class="stack-hint">Tap card to peek next <?= icon('arrow-right', '', 11) ?></div>
                        </div>

                        <div class="stack-card card-2" id="stackCard2" onclick="swipeAwayCard(2)">
                            <div class="stack-card-inner">
                                <img src="<?= e($zendropLogo) ?>" width="40" height="40" alt="Zendrop" loading="lazy">
                                <div class="stack-card-info">
                                    <strong>Zendrop</strong>
                                    <span>Fast US/EU Supply · Free tier</span>
                                </div>
                                <span class="stack-badge free">Free</span>
                            </div>
                            <div class="stack-hint">Tap card to reset stack <?= icon('arrow-right', '', 11) ?></div>
                        </div>
                    </div>
                </div>

                <footer class="intro-slide-footer">
                    <span class="intro-counter">1 / 3</span>
                    <div class="intro-action-btns">
                        <button type="button" class="btn-primary intro-next-btn" onclick="nextIntroSlide()">
                            <span>Next</span>
                            <?= icon('arrow-right', '', 16) ?>
                        </button>
                    </div>
                </footer>
            </section>

            <!-- SLIDE 2: Compare side-by-side -->
            <section class="intro-slide" data-index="1" aria-label="Step 2 of 3: Compare and decide">
                <div class="intro-slide-content">
                    <div class="intro-icon-badge">
                        <?= icon('scale', '', 24) ?>
                    </div>
                    <span class="intro-step-pill">Step 2 of 3 · Decision Clarity</span>
                    <h2 class="intro-title">Compare and decide</h2>
                    <p class="intro-subtitle">
                        Compare competing tools side-by-side to understand true costs before scaling.
                    </p>

                    <!-- Interactive Visual 2: Sliding Comparison with Animated Price Bars -->
                    <div class="intro-compare-visual">
                        <div class="compare-card-pair">
                            <div class="mini-tool-side side-shopify">
                                <img src="<?= e($shopifyLogo) ?>" width="32" height="32" alt="Shopify" loading="lazy">
                                <span class="side-name">Shopify</span>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill fill-shopify" style="width: 82%;"></div>
                                </div>
                                <span class="side-price">From $29/mo</span>
                                <span class="side-tag">Hosted · 1-Click</span>
                            </div>

                            <div class="mini-vs-badge">VS</div>

                            <div class="mini-tool-side side-woo">
                                <img src="<?= e($wooLogo) ?>" width="32" height="32" alt="WooCommerce" loading="lazy">
                                <span class="side-name">WooCommerce</span>
                                <div class="mini-bar-track">
                                    <div class="mini-bar-fill fill-woo" style="width: 35%;"></div>
                                </div>
                                <span class="side-price">$0/mo + Hosting</span>
                                <span class="side-tag">Self-Hosted · Full Control</span>
                            </div>
                        </div>

                        <div class="compare-metric-row">
                            <div class="mini-metric-pill">
                                <span>Gateway fee</span>
                                <strong>2.0% vs 0%</strong>
                            </div>
                            <div class="mini-metric-pill">
                                <span>Setup speed</span>
                                <strong>15m vs 3h</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="intro-slide-footer">
                    <button type="button" class="intro-back-btn" onclick="prevIntroSlide()">
                        <?= icon('chevron-left', '', 16) ?>
                        <span>Back</span>
                    </button>
                    <div class="intro-action-btns">
                        <button type="button" class="btn-primary intro-next-btn" onclick="nextIntroSlide()">
                            <span>Next</span>
                            <?= icon('arrow-right', '', 16) ?>
                        </button>
                    </div>
                </footer>
            </section>

            <!-- SLIDE 3: Build your stack -->
            <section class="intro-slide" data-index="2" aria-label="Step 3 of 3: Build your stack">
                <div class="intro-slide-content">
                    <div class="intro-icon-badge">
                        <?= icon('layers', '', 24) ?>
                    </div>
                    <span class="intro-step-pill">Step 3 of 3 · Run-Rate Clarity</span>
                    <h2 class="intro-title">Build your stack</h2>
                    <p class="intro-subtitle">
                        Select your experience and monthly budget to view your exact software run-rate.
                    </p>

                    <!-- Interactive Visual 3: Interactive Chips + Live Updating Stack Preview -->
                    <div class="intro-build-visual">
                        <div class="build-chip-group">
                            <span class="chip-group-label">Experience</span>
                            <div class="build-chips" id="introExpChips">
                                <button type="button" class="build-chip active" data-exp="beginner" onclick="selectIntroExp(this, 'beginner')">Beginner</button>
                                <button type="button" class="build-chip" data-exp="scaling" onclick="selectIntroExp(this, 'scaling')">Scaling</button>
                                <button type="button" class="build-chip" data-exp="pro" onclick="selectIntroExp(this, 'pro')">Advanced</button>
                            </div>
                        </div>

                        <div class="build-chip-group">
                            <span class="chip-group-label">Monthly Tool Budget</span>
                            <div class="build-chips" id="introBudChips">
                                <button type="button" class="build-chip active" data-bud="low" onclick="selectIntroBud(this, 'low')">&lt; $50/mo</button>
                                <button type="button" class="build-chip" data-bud="mid" onclick="selectIntroBud(this, 'mid')">$50-$150</button>
                                <button type="button" class="build-chip" data-bud="free" onclick="selectIntroBud(this, 'free')">Free ($0)</button>
                            </div>
                        </div>

                        <!-- Live Updating Preview Box -->
                        <div class="build-live-box" id="introLiveBox">
                            <div class="live-box-top">
                                <div>
                                    <span class="live-box-label" id="introStackLabel">Starter Foundation</span>
                                    <h4 class="live-box-name" id="introStackName">Shopify + DSers + Canva</h4>
                                </div>
                                <div class="live-box-pill">
                                    <span>Est.</span>
                                    <strong id="introStackPrice">$29/mo</strong>
                                </div>
                            </div>
                            <div class="live-box-logos" id="introStackLogos">
                                <img src="<?= e($shopifyLogo) ?>" width="24" height="24" alt="Shopify" class="live-logo">
                                <img src="<?= e($dsersLogo) ?>" width="24" height="24" alt="DSers" class="live-logo">
                                <img src="<?= e($canvaLogo) ?>" width="24" height="24" alt="Canva" class="live-logo">
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="intro-slide-footer">
                    <button type="button" class="intro-back-btn" onclick="prevIntroSlide()">
                        <?= icon('chevron-left', '', 16) ?>
                        <span>Back</span>
                    </button>
                    <div class="intro-action-btns">
                        <a href="/stack-builder.php" class="btn-primary intro-next-btn" onclick="finishIntro('build')">
                            <span>Build My Stack</span>
                            <?= icon('arrow-right', '', 16) ?>
                        </a>
                    </div>
                </footer>
            </section>
        </div>
    </div>
</div>

<style>
/* ── INTRO OVERLAY BASE ── */
.intro-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(10, 15, 12, 0.85);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    opacity: 0;
    transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.intro-overlay.active {
    display: flex;
    opacity: 1;
}

.intro-overlay.closing {
    opacity: 0;
    pointer-events: none;
}

/* ── MODAL CONTAINER (Desktop / Tablet Centered) ── */
.intro-container {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    width: 100%;
    max-width: 580px;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.4);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

/* ── LIGHTWEIGHT GPU GRADIENT MESH ── */
.intro-mesh {
    position: absolute;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    opacity: 0.55;
    z-index: 0;
}

.intro-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    will-change: transform;
}

.intro-blob.blob-1 {
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(50, 99, 70, 0.5) 0%, transparent 70%);
    top: -50px;
    right: -50px;
    animation: blobDrift1 18s ease-in-out infinite alternate;
}

.intro-blob.blob-2 {
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(22, 163, 74, 0.35) 0%, transparent 70%);
    bottom: -60px;
    left: -60px;
    animation: blobDrift2 22s ease-in-out infinite alternate;
}

@keyframes blobDrift1 {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    100% { transform: translate3d(-35px, 45px, 0) scale(1.12); }
}

@keyframes blobDrift2 {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    100% { transform: translate3d(40px, -40px, 0) scale(0.92); }
}

/* ── TOPBAR ── */
.intro-topbar {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-subtle);
}

.intro-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: 14px;
    color: var(--text);
}

.intro-brand-icon {
    color: var(--accent);
    display: flex;
}

.intro-progress-dots {
    display: flex;
    align-items: center;
    gap: 8px;
}

.intro-dot {
    width: 8px;
    height: 8px;
    border-radius: var(--radius-full);
    background: var(--border);
    border: none;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    padding: 0;
}

.intro-dot.active {
    width: 24px;
    background: var(--accent);
}

.intro-skip-btn {
    background: transparent;
    border: none;
    color: var(--muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: color 0.15s ease;
    padding: 4px 8px;
}

.intro-skip-btn:hover {
    color: var(--text);
}

/* ── VIEWPORT & SLIDES ── */
.intro-slides-viewport {
    position: relative;
    z-index: 1;
    overflow: hidden;
    min-height: 480px;
}

.intro-slide {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 24px 28px;
    opacity: 0;
    transform: translateX(40px);
    pointer-events: none;
    transition: opacity 0.3s cubic-bezier(0.16, 1, 0.3, 1), transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.intro-slide.active {
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
}

.intro-slide.prev-slide {
    transform: translateX(-40px);
}

.intro-icon-badge {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-xl);
    background: var(--accent-light);
    color: var(--accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.intro-step-pill {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--accent);
    margin-bottom: 6px;
}

.intro-title {
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: var(--text);
    margin-bottom: 6px;
}

.intro-subtitle {
    font-size: 13.5px;
    color: var(--muted);
    line-height: 1.45;
    margin-bottom: 18px;
}

/* ── VISUAL 1: FLOATING CARD STACK ── */
.intro-floating-stack {
    position: relative;
    height: 140px;
    margin-bottom: 12px;
    display: flex;
    justify-content: center;
}

.stack-card {
    position: absolute;
    width: 100%;
    max-width: 360px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 14px 16px;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    user-select: none;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
}

.stack-card:active {
    transform: scale(0.98);
}

.stack-card.card-0 {
    top: 0;
    z-index: 3;
    transform: translateY(0) scale(1);
}

.stack-card.card-1 {
    top: 10px;
    z-index: 2;
    transform: translateY(12px) scale(0.95);
    opacity: 0.85;
}

.stack-card.card-2 {
    top: 20px;
    z-index: 1;
    transform: translateY(24px) scale(0.90);
    opacity: 0.65;
}

.stack-card.swiped {
    transform: translateX(120%) rotate(12deg) !important;
    opacity: 0 !important;
}

.stack-card-inner {
    display: flex;
    align-items: center;
    gap: 12px;
}

.stack-card-inner img {
    border-radius: 8px;
    object-fit: contain;
    background: var(--surface);
    padding: 3px;
    border: 1px solid var(--border-subtle);
}

.stack-card-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.stack-card-info strong {
    font-size: 14px;
    color: var(--text);
}

.stack-card-info span {
    font-size: 12px;
    color: var(--muted);
}

.stack-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: var(--radius-full);
}

.stack-badge.vetted {
    background: var(--positive-bg);
    color: var(--positive-text);
}

.stack-badge.free {
    background: var(--accent-light);
    color: var(--accent);
}

.stack-hint {
    margin-top: 8px;
    font-size: 11px;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 4px;
    justify-content: flex-end;
}

/* ── VISUAL 2: COMPARE PAIR ── */
.intro-compare-visual {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 16px;
    margin-bottom: 12px;
}

.compare-card-pair {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.mini-tool-side {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 6px;
}

.side-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}

.mini-bar-track {
    width: 100%;
    height: 6px;
    background: var(--border);
    border-radius: 99px;
    overflow: hidden;
}

.mini-bar-fill {
    height: 100%;
    border-radius: 99px;
    transition: width 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.mini-bar-fill.fill-shopify { background: #16a34a; }
.mini-bar-fill.fill-woo { background: #8b5cf6; }

.side-price {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text);
}

.side-tag {
    font-size: 11px;
    color: var(--muted);
}

.mini-vs-badge {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--surface);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 800;
    color: var(--muted);
    flex-shrink: 0;
}

.compare-metric-row {
    display: flex;
    gap: 8px;
    margin-top: 12px;
    padding-top: 10px;
    border-top: 1px solid var(--border-subtle);
}

.mini-metric-pill {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 11px;
    color: var(--muted);
}

.mini-metric-pill strong {
    font-size: 12px;
    color: var(--text);
}

/* ── VISUAL 3: BUILD LIVE PREVIEW ── */
.intro-build-visual {
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 16px;
    margin-bottom: 12px;
}

.build-chip-group {
    margin-bottom: 12px;
}

.chip-group-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
    margin-bottom: 6px;
}

.build-chips {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.build-chip {
    padding: 6px 12px;
    border-radius: var(--radius-full);
    background: var(--surface);
    border: 1px solid var(--border);
    font-size: 12px;
    font-weight: 600;
    color: var(--body);
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.build-chip:hover {
    border-color: var(--accent);
    color: var(--text);
}

.build-chip.active {
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border-color: var(--btn-primary-bg);
}

.build-chip:active {
    transform: scale(0.96);
}

.build-live-box {
    margin-top: 12px;
    padding: 12px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.live-box-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.live-box-label {
    font-size: 11px;
    color: var(--muted);
    display: block;
}

.live-box-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin: 0;
}

.live-box-pill {
    text-align: right;
}

.live-box-pill span {
    font-size: 10px;
    color: var(--muted);
    display: block;
}

.live-box-pill strong {
    font-size: 14px;
    color: var(--positive-text);
}

.live-box-logos {
    display: flex;
    gap: 6px;
    align-items: center;
}

.live-logo {
    border-radius: 6px;
    object-fit: contain;
}

/* ── FOOTER CONTROLS ── */
.intro-slide-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 16px;
    border-top: 1px solid var(--border-subtle);
    margin-top: auto;
}

.intro-counter {
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
}

.intro-action-btns {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: auto;
}

.intro-back-btn {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 16px;
    border-radius: var(--radius-full);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}

.intro-back-btn:hover {
    background: var(--surface-2);
}

.intro-next-btn {
    padding: 10px 22px;
    border-radius: var(--radius-full);
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.intro-next-btn:active,
.intro-back-btn:active {
    transform: scale(0.96);
}

/* ════════════════════════════════════════════════════════════
   PHONE-ONLY FULL SCREEN EXPERIENCE (under 768px)
   ════════════════════════════════════════════════════════════ */
@media (max-width: 767.98px) {
    .intro-overlay {
        padding: 0 !important;
        align-items: stretch !important;
        justify-content: stretch !important;
        height: 100vh !important;
        height: 100dvh !important;
        width: 100vw !important;
        overflow: hidden !important;
    }

    .intro-container {
        border-radius: 0 !important;
        border: none !important;
        max-width: 100% !important;
        width: 100% !important;
        height: 100vh !important;
        height: 100dvh !important;
        min-height: 100dvh !important;
        display: flex !important;
        flex-direction: column !important;
        padding-top: env(safe-area-inset-top, 0px) !important;
        padding-bottom: env(safe-area-inset-bottom, 0px) !important;
    }

    .intro-topbar {
        padding: 12px 16px !important;
        flex-shrink: 0 !important;
        height: 52px !important;
    }

    .intro-slides-viewport {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        position: relative !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch !important;
    }

    .intro-slide {
        padding: 18px 20px 20px !important;
        overflow-y: auto !important;
    }

    .intro-title {
        font-size: 20px !important;
    }

    .intro-subtitle {
        font-size: 13px !important;
        margin-bottom: 14px !important;
    }

    /* Landscape phones (short screens) */
    @media (max-height: 500px) {
        .intro-slide {
            padding: 12px 16px !important;
        }
        .intro-floating-stack {
            height: 110px !important;
        }
        .intro-compare-visual,
        .intro-build-visual {
            padding: 10px !important;
        }
    }
}

/* Reduced motion preference */
@media (prefers-reduced-motion: reduce) {
    .intro-blob,
    .intro-slide,
    .stack-card,
    .intro-next-btn,
    .intro-back-btn {
        transition: none !important;
        animation: none !important;
        transform: none !important;
    }
    .intro-mesh {
        display: none !important;
    }
}
</style>

<script>
(function() {
    let currentSlide = 0;
    const totalSlides = 3;

    const overlay = document.getElementById('introOverlay');
    const viewport = document.getElementById('introViewport');
    const dots = document.querySelectorAll('.intro-dot');
    const slides = document.querySelectorAll('.intro-slide');

    function updateSlide(newIdx) {
        if (newIdx < 0 || newIdx >= totalSlides) return;

        // Optional haptic vibration feedback
        if ('vibrate' in navigator) {
            try { navigator.vibrate(10); } catch (e) {}
        }

        slides.forEach((s, idx) => {
            s.classList.toggle('active', idx === newIdx);
            s.classList.toggle('prev-slide', idx < newIdx);
        });

        dots.forEach((dot, idx) => {
            const isActive = idx === newIdx;
            dot.classList.toggle('active', isActive);
            dot.setAttribute('aria-current', isActive ? 'step' : 'false');
        });

        currentSlide = newIdx;
    }

    window.nextIntroSlide = function() {
        if (currentSlide < totalSlides - 1) {
            updateSlide(currentSlide + 1);
        } else {
            finishIntro('complete');
        }
    };

    window.prevIntroSlide = function() {
        if (currentSlide > 0) {
            updateSlide(currentSlide - 1);
        }
    };

    window.finishIntro = function(reason) {
        try {
            localStorage.setItem('dstack_intro_seen', '1');
        } catch (e) {}

        // Set 1-year cookie so server skips intro markup on return visits (Task 3)
        document.cookie = 'intro_seen=1; path=/; max-age=31536000; SameSite=Lax';

        if (overlay) {
            overlay.classList.add('closing');
            setTimeout(() => {
                overlay.classList.remove('active', 'closing');
                overlay.style.display = 'none';
            }, 300);
        }
    };

    // Dot click listeners
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => updateSlide(idx));
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!overlay || !overlay.classList.contains('active')) return;
        if (e.key === 'ArrowRight' || e.key === 'Enter') {
            e.preventDefault();
            window.nextIntroSlide();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            window.prevIntroSlide();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            window.finishIntro('escape');
        }
    });

    // Touch Swipe Navigation
    let touchStartX = 0;
    let touchEndX = 0;
    if (viewport) {
        viewport.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        viewport.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchEndX - touchStartX;
            if (Math.abs(diff) > 45) {
                if (diff < 0) {
                    window.nextIntroSlide();
                } else {
                    window.prevIntroSlide();
                }
            }
        }, { passive: true });
    }

    // Interactive Card Stack (Slide 1)
    let cardOrder = [0, 1, 2];
    window.swipeAwayCard = function(cardIdx) {
        const card = document.getElementById('stackCard' + cardIdx);
        if (!card) return;

        if ('vibrate' in navigator) {
            try { navigator.vibrate(8); } catch (e) {}
        }

        card.classList.add('swiped');
        setTimeout(() => {
            // Rotate stack order
            cardOrder.push(cardOrder.shift());
            cardOrder.forEach((id, rank) => {
                const el = document.getElementById('stackCard' + id);
                if (el) {
                    el.className = 'stack-card card-' + rank;
                }
            });
        }, 320);
    };

    // Interactive Build Chips (Slide 3)
    const stackPresets = {
        'beginner-free': { title: '100% Free Lean Stack', name: 'WooCommerce + DSers', price: '$0/mo', logos: ['<?= e($wooLogo) ?>', '<?= e($dsersLogo) ?>'] },
        'beginner-low':  { title: 'Starter Foundation', name: 'Shopify + DSers + Canva', price: '$29/mo', logos: ['<?= e($shopifyLogo) ?>', '<?= e($dsersLogo) ?>', '<?= e($canvaLogo) ?>'] },
        'beginner-mid':  { title: 'Growth Starter', name: 'Shopify + Zendrop + Minea', price: '$78/mo', logos: ['<?= e($shopifyLogo) ?>', '<?= e($zendropLogo) ?>', '<?= e($mineaLogo) ?>'] },
        'scaling-free':   { title: 'Self-Hosted Growth', name: 'WooCommerce + Zendrop', price: '$0/mo', logos: ['<?= e($wooLogo) ?>', '<?= e($zendropLogo) ?>'] },
        'scaling-low':    { title: 'Conversion Scale', name: 'Shopify + Zendrop + Loox', price: '$49/mo', logos: ['<?= e($shopifyLogo) ?>', '<?= e($zendropLogo) ?>'] },
        'scaling-mid':    { title: 'Full Autopilot Scale', name: 'Shopify + Zendrop + Minea', price: '$128/mo', logos: ['<?= e($shopifyLogo) ?>', '<?= e($zendropLogo) ?>', '<?= e($mineaLogo) ?>'] },
        'pro-free':       { title: 'Developer Custom', name: 'WooCommerce Custom API', price: '$0/mo', logos: ['<?= e($wooLogo) ?>'] },
        'pro-low':        { title: 'Pro Streamlined', name: 'Shopify + AutoDS + Tidio', price: '$89/mo', logos: ['<?= e($shopifyLogo) ?>'] },
        'pro-mid':        { title: '7-Figure High Volume', name: 'Shopify + Triple Whale + Klaviyo', price: '$210/mo', logos: ['<?= e($shopifyLogo) ?>'] }
    };

    let selectedExp = 'beginner';
    let selectedBud = 'low';

    function updateBuildPreset() {
        const key = `${selectedExp}-${selectedBud}`;
        const p = stackPresets[key] || stackPresets['beginner-low'];
        const label = document.getElementById('introStackLabel');
        const name = document.getElementById('introStackName');
        const price = document.getElementById('introStackPrice');
        const logos = document.getElementById('introStackLogos');

        if (label) label.textContent = p.title;
        if (name) name.textContent = p.name;
        if (price) price.textContent = p.price;
        if (logos && p.logos) {
            logos.innerHTML = p.logos.map(src => `<img src="${src}" width="24" height="24" class="live-logo">`).join('');
        }
    }

    window.selectIntroExp = function(btn, exp) {
        document.querySelectorAll('#introExpChips .build-chip').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedExp = exp;
        updateBuildPreset();
    };

    window.selectIntroBud = function(btn, bud) {
        document.querySelectorAll('#introBudChips .build-chip').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        selectedBud = bud;
        updateBuildPreset();
    };

    // Initial modal trigger check
    window.openIntroModal = function() {
        if (overlay) {
            overlay.classList.remove('closing');
            overlay.classList.add('active');
            updateSlide(0);
        }
    };

    function initIntro() {
        const urlParams = new URLSearchParams(window.location.search);
        const force = urlParams.get('intro') === '1';
        let seen = false;
        try {
            seen = localStorage.getItem('dstack_intro_seen') === '1' || document.cookie.includes('intro_seen=1');
        } catch (e) {}

        if (force || (!seen && document.body.dataset.loggedIn !== '1')) {
            window.openIntroModal();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIntro);
    } else {
        initIntro();
    }
})();
</script>
