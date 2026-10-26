<?php
if (!defined('APP')) die('Direct access not allowed');
?>
<!-- ═══════════════════════════════════════════════════════════
     INTRO ONBOARDING MODAL / OVERLAY (Part 2)
     Framer-Motion spring animations, 3 slides, mini UI previews,
     accessible keyboard & swipe, expanding clip-path reveal into dashboard.
     ═══════════════════════════════════════════════════════════ -->
<div class="intro-overlay" id="introOverlay" role="dialog" aria-modal="true" aria-label="Welcome to DropshippingStack">
    <div class="intro-container">

        <!-- Top Header Bar -->
        <header class="intro-topbar">
            <div class="intro-brand">
                <span class="intro-brand-icon"><?= icon('sparkles', '', 20) ?></span>
                <span class="intro-brand-name"><?= e(SITE_NAME) ?></span>
            </div>

            <!-- Animated Step Progress Dots -->
            <nav class="intro-progress-dots" aria-label="Intro Steps">
                <button type="button" class="intro-dot active" data-step="0" aria-label="Slide 1: Discover vetted tools" aria-current="step"></button>
                <button type="button" class="intro-dot" data-step="1" aria-label="Slide 2: Build your stack"></button>
                <button type="button" class="intro-dot" data-step="2" aria-label="Slide 3: Compare and decide"></button>
            </nav>

            <button type="button" class="intro-skip-btn" id="introSkipBtn" onclick="finishIntro('skip')">
                <span>Skip</span>
                <?= icon('arrow-right', '', 14) ?>
            </button>
        </header>

        <!-- Slides Wrapper -->
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
                        Honest breakdowns, verified pricing models, and community-validated reviews for 20+ top dropshipping platforms.
                    </p>

                    <!-- Mini UI Preview Card: Realistic Tool Card -->
                    <div class="intro-preview-card preview-stagger">
                        <div class="mini-tool-top">
                            <div class="mini-tool-logo">
                                <img src="https://img.logo.dev/shopify.com?token=pk_IPkJggMaTFC35CqF8UE3hg&size=64" alt="Shopify" class="skeleton-img">
                            </div>
                            <div class="mini-tool-meta">
                                <div class="mini-tool-title">
                                    <span>Shopify</span>
                                    <span class="mini-badge-vetted"><?= icon('shield-check', '', 12) ?> Vetted</span>
                                </div>
                                <span class="mini-tool-cat">Store Builders</span>
                            </div>
                            <span class="mini-tool-price">From $29/mo</span>
                        </div>
                        <div class="mini-tool-tagline">The benchmark ecommerce conversion platform for scaling stores.</div>
                        <div class="mini-tool-features">
                            <span class="mini-feature-chip"><?= icon('check', '', 12) ?> 1-Click Checkout</span>
                            <span class="mini-feature-chip"><?= icon('check', '', 12) ?> Global Multi-Currency</span>
                            <span class="mini-feature-chip"><?= icon('check', '', 12) ?> 8,000+ App Ecosystem</span>
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

            <!-- SLIDE 2: Build your stack -->
            <section class="intro-slide" data-index="1" aria-label="Step 2 of 3: Build your stack">
                <div class="intro-slide-content">
                    <div class="intro-icon-badge">
                        <?= icon('layers', '', 24) ?>
                    </div>
                    <span class="intro-step-pill">Step 2 of 3 · Run-Rate Clarity</span>
                    <h2 class="intro-title">Build your stack</h2>
                    <p class="intro-subtitle">
                        Select your store vertical and budget to see your exact monthly tool run-rate with zero hidden surprises.
                    </p>

                    <!-- Mini UI Preview Card: Stack Builder Deck -->
                    <div class="intro-preview-card preview-stagger">
                        <div class="mini-stack-header">
                            <div>
                                <span class="mini-stack-label">Starter Foundation</span>
                                <h4 class="mini-stack-title">Curated Toolkit</h4>
                            </div>
                            <div class="mini-runrate-pill">
                                <span class="mini-runrate-label">Est.</span>
                                <span class="mini-runrate-val">$29/mo</span>
                            </div>
                        </div>

                        <div class="mini-stack-rows">
                            <div class="mini-stack-row">
                                <div class="mini-row-left">
                                    <div class="mini-row-icon green"><?= icon('shopping-bag', '', 14) ?></div>
                                    <div>
                                        <div class="mini-row-name">Shopify</div>
                                        <div class="mini-row-desc">High-converting store checkout</div>
                                    </div>
                                </div>
                                <span class="mini-row-tag core">CORE</span>
                            </div>

                            <div class="mini-stack-row">
                                <div class="mini-row-left">
                                    <div class="mini-row-icon"><?= icon('package', '', 14) ?></div>
                                    <div>
                                        <div class="mini-row-name">DSers</div>
                                        <div class="mini-row-desc">AliExpress supply automation</div>
                                    </div>
                                </div>
                                <span class="mini-row-tag free">FREE</span>
                            </div>

                            <div class="mini-stack-more">
                                <?= icon('plus', '', 12) ?>
                                <span>1 more tool (Canva Pro)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="intro-slide-footer">
                    <button type="button" class="intro-back-btn" onclick="prevIntroSlide()">
                        <?= icon('arrow-right', 'icon-flip', 15) ?>
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

            <!-- SLIDE 3: Compare and decide -->
            <section class="intro-slide" data-index="2" aria-label="Step 3 of 3: Compare and decide">
                <div class="intro-slide-content">
                    <div class="intro-icon-badge">
                        <?= icon('scale', '', 24) ?>
                    </div>
                    <span class="intro-step-pill">Step 3 of 3 · Confident Decisions</span>
                    <h2 class="intro-title">Compare and decide</h2>
                    <p class="intro-subtitle">
                        Compare competing tools side-by-side and calculate true net store margin after every software fee.
                    </p>

                    <!-- Mini UI Preview Card: Compare & Calculator -->
                    <div class="intro-preview-card preview-stagger">
                        <div class="mini-compare-bar">
                            <span class="mini-tool-chip">Shopify</span>
                            <span class="mini-vs-badge">VS</span>
                            <span class="mini-tool-chip">WooCommerce</span>
                        </div>

                        <div class="mini-compare-metrics">
                            <div class="mini-metric-item">
                                <div class="mini-metric-label">Payment Gateway Fee</div>
                                <div class="mini-metric-val">2.0% third-party vs 0% native</div>
                            </div>
                            <div class="mini-metric-item">
                                <div class="mini-metric-label">Estimated Setup Speed</div>
                                <div class="mini-metric-val">15 mins vs 2-3 hours</div>
                            </div>
                        </div>

                        <div class="mini-margin-box">
                            <div class="mini-margin-left">
                                <div class="mini-margin-icon"><?= icon('calculator', '', 16) ?></div>
                                <div>
                                    <div class="mini-margin-title">Profit Margin Simulation</div>
                                    <div class="mini-margin-sub">Software run-rate: 2.1% of gross revenue</div>
                                </div>
                            </div>
                            <span class="mini-margin-badge">+28.4% NET</span>
                        </div>
                    </div>
                </div>

                <footer class="intro-slide-footer last-slide-footer">
                    <button type="button" class="intro-back-btn" onclick="prevIntroSlide()">
                        <?= icon('arrow-right', 'icon-flip', 15) ?>
                        <span>Back</span>
                    </button>

                    <div class="intro-final-actions">
                        <a href="/login.php" class="btn-secondary intro-auth-btn" onclick="recordIntroSeen()">
                            I already have an account
                        </a>
                        <a href="/register.php" class="btn-primary intro-auth-btn" onclick="recordIntroSeen()">
                            <span>Get started</span>
                            <?= icon('arrow-right', '', 15) ?>
                        </a>
                        <button type="button" class="intro-explore-btn" onclick="finishIntro('explore')" title="Reveal live dashboard">
                            <span>Explore dashboard →</span>
                        </button>
                    </div>
                </footer>
            </section>

        </div><!-- /.intro-slides-viewport -->

    </div><!-- /.intro-container -->
</div><!-- /.intro-overlay -->

<style>
/* ═══════════════════════════════════════════════════════════
   INTRO ONBOARDING STYLES (Light & Dark, Sage Green Tokens)
   ═══════════════════════════════════════════════════════════ */
.intro-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    background: var(--bg);
    display: none;
    align-items: center;
    justify-content: center;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 24px;
    opacity: 0;
    transition: opacity var(--dur) var(--ease), clip-path 0.65s cubic-bezier(0.16, 1, 0.3, 1), transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
    clip-path: circle(150% at 50% 50%);
}

.intro-overlay.active {
    display: flex;
    opacity: 1;
}

.intro-overlay.closing {
    opacity: 0;
    transform: scale(1.04);
    clip-path: circle(0% at 50% 50%);
    pointer-events: none;
}

.intro-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    width: 100%;
    max-width: 700px;
    min-height: 600px;
    box-shadow: var(--shadow-lg);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    animation: introContainerIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes introContainerIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Topbar */
.intro-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 28px;
    border-bottom: 1px solid var(--border-subtle);
}

.intro-brand {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.01em;
}

.intro-brand-icon {
    color: var(--accent);
    display: inline-flex;
}

/* Step Progress Dots */
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
    padding: 0;
    transition: width var(--dur) var(--ease), background var(--dur) var(--ease);
}

.intro-dot.active {
    width: 26px;
    background: var(--accent);
}

.intro-skip-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: transparent;
    border: none;
    color: var(--muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: var(--radius-sm);
    transition: color var(--dur-fast) var(--ease), background var(--dur-fast) var(--ease);
}

.intro-skip-btn:hover {
    color: var(--text);
    background: var(--surface-2);
}

/* Slides Viewport */
.intro-slides-viewport {
    position: relative;
    flex: 1;
    display: flex;
    min-height: 500px;
}

.intro-slide {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 24px 32px 22px;
    opacity: 0;
    transform: translateX(40px) scale(0.97);
    pointer-events: none;
    transition: opacity 0.35s var(--ease), transform 0.35s var(--ease);
}

.intro-slide.active {
    opacity: 1;
    transform: translateX(0) scale(1);
    pointer-events: auto;
}

.intro-slide.exited-left {
    opacity: 0;
    transform: translateX(-40px) scale(0.97);
}

.intro-slide-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.intro-icon-badge {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: var(--accent-light);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    box-shadow: var(--shadow-xs);
}

.intro-step-pill {
    display: inline-flex;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--accent);
    margin-bottom: 6px;
}

.intro-title {
    font-size: 24px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.025em;
    line-height: 1.15;
    margin: 0 0 6px;
}

.intro-subtitle {
    font-size: 13.5px;
    color: var(--muted);
    line-height: 1.45;
    max-width: 480px;
    margin: 0 0 18px;
}

/* Mini Preview Cards (Real UI lookalike) */
.intro-preview-card {
    width: 100%;
    max-width: 520px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 16px 18px;
    text-align: left;
    box-shadow: var(--shadow-sm);
    transition: transform var(--dur) var(--ease);
}

/* Slide 1 mini card */
.mini-tool-top {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.mini-tool-logo {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: var(--surface);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px;
}

.mini-tool-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.mini-tool-meta {
    flex: 1;
}

.mini-tool-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
}

.mini-badge-vetted {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 11px;
    font-weight: 700;
    background: var(--positive-bg);
    color: var(--positive-text);
    padding: 2px 7px;
    border-radius: var(--radius-full);
}

.mini-tool-cat {
    font-size: 12px;
    color: var(--muted);
}

.mini-tool-price {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 4px 10px;
    border-radius: var(--radius-full);
}

.mini-tool-tagline {
    font-size: 12.5px;
    color: var(--body);
    line-height: 1.4;
    margin-bottom: 12px;
}

.mini-tool-features {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.mini-feature-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 600;
    background: var(--surface);
    border: 1px solid var(--border-subtle);
    padding: 4px 9px;
    border-radius: var(--radius-sm);
    color: var(--text);
}

.mini-feature-chip svg {
    color: var(--accent);
}

/* Slide 2 mini card */
.mini-stack-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.mini-stack-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--muted);
}

.mini-stack-title {
    font-size: 14.5px;
    font-weight: 800;
    color: var(--text);
    margin: 2px 0 0;
}

.mini-runrate-pill {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 5px 12px;
    border-radius: var(--radius-full);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.mini-runrate-label {
    font-size: 11px;
    color: var(--muted);
    font-weight: 600;
}

.mini-runrate-val {
    font-size: 14px;
    font-weight: 800;
    color: var(--text);
}

.mini-stack-rows {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mini-stack-row {
    background: var(--surface);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-sm);
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.mini-row-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mini-row-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: var(--surface-2);
    color: var(--muted);
    display: flex;
    align-items: center;
    justify-content: center;
}

.mini-row-icon.green {
    background: var(--accent-light);
    color: var(--accent);
}

.mini-row-icon.amber {
    background: var(--amber-light);
    color: var(--amber-text);
}

.mini-row-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
}

.mini-row-desc {
    font-size: 11px;
    color: var(--muted);
}

.mini-row-tag {
    font-size: 10px;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: var(--radius-full);
}

.mini-row-tag.core {
    background: var(--accent-light);
    color: var(--accent);
}

.mini-row-tag.free {
    background: var(--positive-bg);
    color: var(--positive-text);
}

/* Slide 3 mini card */
.mini-compare-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 12px;
}

.mini-tool-chip {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 6px 14px;
    border-radius: var(--radius-full);
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text);
}

.mini-vs-badge {
    font-size: 11px;
    font-weight: 800;
    color: var(--muted);
}

.mini-compare-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 12px;
}

.mini-metric-item {
    background: var(--surface);
    border: 1px solid var(--border-subtle);
    padding: 10px 12px;
    border-radius: var(--radius-sm);
}

.mini-metric-label {
    font-size: 11px;
    color: var(--muted);
    margin-bottom: 4px;
}

.mini-metric-val {
    font-size: 12px;
    font-weight: 700;
    color: var(--text);
}

.mini-margin-box {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.mini-margin-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.mini-margin-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: var(--accent-light);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
}

.mini-margin-title {
    font-size: 12.5px;
    font-weight: 700;
    color: var(--text);
}

.mini-margin-sub {
    font-size: 11px;
    color: var(--muted);
}

.mini-margin-badge {
    font-size: 12px;
    font-weight: 800;
    background: var(--positive-bg);
    color: var(--positive-text);
    padding: 4px 9px;
    border-radius: var(--radius-full);
}

/* Footer / Actions */
.intro-slide-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 20px;
    border-top: 1px solid var(--border-subtle);
    margin-top: auto;
}

.intro-counter {
    font-size: 13px;
    font-weight: 600;
    color: var(--muted);
}

.intro-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 18px;
    border-radius: var(--radius-full);
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--dur-fast) var(--ease);
}

.intro-back-btn:hover {
    background: var(--surface-2);
}

.icon-flip {
    transform: rotate(180deg);
}

.intro-next-btn {
    padding: 11px 24px;
    font-size: 14px;
}

.last-slide-footer {
    flex-wrap: wrap;
    gap: 12px;
}

.intro-final-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.intro-auth-btn {
    padding: 11px 20px;
    font-size: 13.5px;
    white-space: nowrap;
}

.intro-explore-btn {
    background: transparent;
    border: none;
    color: var(--accent);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: var(--radius-sm);
    transition: color var(--dur-fast) var(--ease), background var(--dur-fast) var(--ease);
}

.intro-explore-btn:hover {
    color: var(--accent-h);
    background: var(--accent-light);
}

/* Mobile Responsiveness — 100dvh Full-Screen Layout */
@media (max-width: 640px) {
    .intro-overlay {
        padding: 0 !important;
        align-items: stretch !important;
        justify-content: stretch !important;
        height: 100dvh !important;
        max-height: 100dvh !important;
        width: 100vw !important;
        overflow: hidden !important;
    }

    .intro-container {
        border-radius: 0 !important;
        border: none !important;
        max-width: 100% !important;
        width: 100% !important;
        height: 100dvh !important;
        max-height: 100dvh !important;
        min-height: 100dvh !important;
        display: flex !important;
        flex-direction: column !important;
        animation: none !important;
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
        overflow: hidden !important;
    }

    .intro-slide {
        position: absolute !important;
        inset: 0 !important;
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        padding: 0 !important;
    }

    .intro-slide-content {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch !important;
        scrollbar-width: none !important;
        padding: 14px 18px 10px !important;
    }

    .intro-slide-content::-webkit-scrollbar {
        display: none !important;
    }

    .intro-title {
        font-size: 21px !important;
        margin: 6px 0 4px !important;
    }

    .intro-subtitle {
        font-size: 13px !important;
        line-height: 1.4 !important;
        margin-bottom: 12px !important;
    }

    .intro-icon-badge {
        width: 42px !important;
        height: 42px !important;
        margin-bottom: 6px !important;
    }

    .intro-step-pill {
        font-size: 11px !important;
        padding: 2px 8px !important;
    }

    .intro-preview-card {
        padding: 12px 14px !important;
        border-radius: var(--radius-xl) !important;
    }

    .mini-stack-rows {
        gap: 6px !important;
    }

    .mini-stack-row {
        padding: 7px 10px !important;
    }

    .mini-stack-more {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: var(--muted);
        padding: 3px 8px;
        background: var(--surface-2);
        border-radius: var(--radius-sm);
        margin-top: 4px;
        font-weight: 600;
    }

    .mini-row-tag,
    .mini-runrate-pill,
    .mini-badge-vetted,
    .mini-margin-badge {
        white-space: nowrap !important;
        flex-shrink: 0 !important;
    }

    .mini-row-desc {
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        max-width: 170px !important;
    }

    .mini-compare-metrics {
        grid-template-columns: 1fr !important;
        gap: 6px !important;
    }

    .intro-slide-footer,
    .last-slide-footer {
        flex-shrink: 0 !important;
        padding: 10px 18px calc(14px + env(safe-area-inset-bottom, 0px)) !important;
        background: var(--surface) !important;
        border-top: 1px solid var(--border) !important;
        position: sticky !important;
        bottom: 0 !important;
        z-index: 10 !important;
    }

    .last-slide-footer {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 8px !important;
    }

    .intro-final-actions {
        flex-direction: column !important;
        width: 100% !important;
        gap: 6px !important;
    }

    .intro-final-actions .btn-primary,
    .intro-final-actions .btn-secondary {
        width: 100% !important;
        min-height: 44px !important;
        text-align: center !important;
        justify-content: center !important;
    }
}

/* Prefers Reduced Motion */
@media (prefers-reduced-motion: reduce) {
    .intro-overlay {
        transition: opacity 0.2s ease !important;
        clip-path: none !important;
        transform: none !important;
    }
    .intro-slide {
        transition: opacity 0.2s ease !important;
        transform: none !important;
    }
    .intro-container {
        animation: none !important;
    }
}
</style>

<script>
// ════════════════════════════════════════════════════════════
// INTRO CONTROLLER & ANIMATION SCRIPT
// Supports keyboard navigation, touch swipe, Framer Motion fallback,
// localStorage persistence, and expanding zoom reveal into dashboard.
// ════════════════════════════════════════════════════════════
(function () {
    const STORAGE_KEY = 'seen_intro';
    let currentSlide = 0;
    const slides = document.querySelectorAll('.intro-slide');
    const dots = document.querySelectorAll('.intro-dot');
    const overlay = document.getElementById('introOverlay');
    const totalSlides = slides.length;

    function hasSeen() {
        try {
            return localStorage.getItem(STORAGE_KEY) === '1' || document.cookie.includes('seen_intro=1');
        } catch (e) {
            return false;
        }
    }

    window.recordIntroSeen = function () {
        try {
            localStorage.setItem(STORAGE_KEY, '1');
            document.cookie = "seen_intro=1; path=/; max-age=31536000; SameSite=Lax";
        } catch (e) {}
    };

    window.openIntroModal = function () {
        currentSlide = 0;
        updateSlide(0);
        if (overlay) {
            overlay.classList.remove('closing');
            overlay.classList.add('active');
        }
    };

    window.closeIntroModal = function () {
        recordIntroSeen();
        if (!overlay) return;

        const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (prefersReduced) {
            overlay.classList.remove('active');
            return;
        }

        overlay.classList.add('closing');
        setTimeout(() => {
            overlay.classList.remove('active');
            overlay.classList.remove('closing');
        }, 600);
    };

    window.finishIntro = function (reason) {
        recordIntroSeen();
        closeIntroModal();
    };

    function updateSlide(targetIndex) {
        if (targetIndex < 0 || targetIndex >= totalSlides) return;

        slides.forEach((slide, i) => {
            slide.classList.remove('active', 'exited-left');
            if (i < targetIndex) {
                slide.classList.add('exited-left');
            } else if (i === targetIndex) {
                slide.classList.add('active');
            }
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === targetIndex);
            dot.setAttribute('aria-current', i === targetIndex ? 'step' : 'false');
        });

        currentSlide = targetIndex;
    }

    window.nextIntroSlide = function () {
        if (currentSlide < totalSlides - 1) {
            updateSlide(currentSlide + 1);
        } else {
            finishIntro('completed');
        }
    };

    window.prevIntroSlide = function () {
        if (currentSlide > 0) {
            updateSlide(currentSlide - 1);
        }
    };

    // Dot navigation
    dots.forEach((dot, idx) => {
        dot.addEventListener('click', () => updateSlide(idx));
    });

    // Keyboard navigation
    document.addEventListener('keydown', function (e) {
        if (!overlay || !overlay.classList.contains('active')) return;

        if (e.key === 'ArrowRight' || e.key === 'Enter') {
            e.preventDefault();
            nextIntroSlide();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            prevIntroSlide();
        } else if (e.key === 'Escape') {
            e.preventDefault();
            finishIntro('escape');
        }
    });

    // Touch Swipe Support for Mobile
    let touchStartX = 0;
    let touchEndX = 0;

    const viewport = document.getElementById('introViewport');
    if (viewport) {
        viewport.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        viewport.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });
    }

    function handleSwipe() {
        const diff = touchEndX - touchStartX;
        if (Math.abs(diff) > 45) {
            if (diff < 0) {
                // Swiped Left -> Next
                nextIntroSlide();
            } else {
                // Swiped Right -> Prev
                prevIntroSlide();
            }
        }
    }

    // Auto-open on first visit if not seen
    function initIntroCheck() {
        const urlParams = new URLSearchParams(window.location.search);
        const forceIntro = urlParams.get('intro') === '1';
        const startSlide = parseInt(urlParams.get('slide') || '0', 10);

        // Check if user is logged in
        const isLoggedIn = document.body.dataset.loggedIn === '1';

        if (forceIntro || (!isLoggedIn && !hasSeen())) {
            currentSlide = (startSlide >= 0 && startSlide < totalSlides) ? startSlide : 0;
            updateSlide(currentSlide);
            if (overlay) {
                overlay.classList.remove('closing');
                overlay.classList.add('active');
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initIntroCheck);
    } else {
        initIntroCheck();
    }
})();
</script>
