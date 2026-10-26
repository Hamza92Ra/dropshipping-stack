<?php
if (!defined('APP')) die('Direct access not allowed');
require_once __DIR__ . '/functions.php';
?>

        </main><!-- /.app-content -->

        <!-- ═══════════════════════════════════════════════════════════
             NEWSLETTER EMBEDDED CARD (Centered ~1100px, Rounded Surface)
             ═══════════════════════════════════════════════════════════ -->
        <section class="newsletter-wrap" id="newsletter" aria-label="Newsletter Subscription">
            <div class="newsletter-card">
                <div class="newsletter-content">
                    <div class="newsletter-badge">
                        <span style="color:var(--accent);display:inline-flex"><?= icon('sparkles', '', 16) ?></span>
                        <span>Free Dropshipping Kit</span>
                    </div>
                    <h3 class="newsletter-title">Get the Dropshipping Starter Kit</h3>
                    <p class="newsletter-sub">
                        Join 2,000+ dropshippers receiving weekly vetted tool breakdowns, margin benchmarks, and private software discounts.
                    </p>
                    <p class="newsletter-trust">
                        <?= icon('check', '', 13) ?>
                        <span>One-click unsubscribe anytime. Zero spam.</span>
                    </p>
                </div>

                <div class="newsletter-action">
                    <form class="newsletter-form" id="newsletterForm" onsubmit="handleNewsletterSubmit(event)">
                        <div class="newsletter-pill-group" id="newsletterInputGroup">
                            <span class="newsletter-input-icon"><?= icon('mail', '', 18) ?></span>
                            <input type="email"
                                   id="newsletterEmail"
                                   name="email"
                                   class="newsletter-input"
                                   placeholder="Enter your email address..."
                                   required
                                   autocomplete="email"
                                   aria-label="Email address for starter kit">
                            <button type="submit" class="newsletter-btn" id="newsletterSubmitBtn">
                                <span class="btn-text">Get Free Access</span>
                                <?= icon('arrow-right', '', 15) ?>
                            </button>
                        </div>
                        <div class="newsletter-feedback" id="newsletterFeedback" role="status" aria-live="polite"></div>
                    </form>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════════
             SITE FOOTER (Clean 5-Column Grid Desktop, 2-Col Mobile)
             ═══════════════════════════════════════════════════════════ -->
        <footer class="app-footer" id="site-footer">
            <div class="footer-inner">
                <!-- Brand & Affiliate Disclosure Column -->
                <div class="footer-brand-col">
                    <a href="/index.php" class="footer-logo">
                        <span class="footer-logo-icon"><?= icon('sparkles', '', 20) ?></span>
                        <span class="footer-logo-text"><?= SITE_NAME ?></span>
                    </a>
                    <p class="footer-tagline"><?= SITE_TAGLINE ?></p>
                    <p class="footer-affiliate-note">
                        <?= icon('info', '', 13) ?>
                        <span>Some links on this platform are affiliate links. We may earn a commission at no extra cost to you.</span>
                    </p>
                </div>

                <!-- Link Columns (Tools, Compare, Roadmap, Calculator, Legal) -->
                <div class="footer-links-grid">
                    <div class="footer-col">
                        <h4 class="footer-col-title">Tools</h4>
                        <a href="/tool/index.php?slug=shopify">Shopify</a>
                        <a href="/tool/index.php?slug=minea">Minea</a>
                        <a href="/tool/index.php?slug=zendrop">Zendrop</a>
                        <a href="/tool/index.php?slug=klaviyo">Klaviyo</a>
                        <a href="/tool/index.php?slug=triple-whale">Triple Whale</a>
                        <a href="/index.php">All 20+ Tools</a>
                    </div>

                    <div class="footer-col">
                        <h4 class="footer-col-title">Compare</h4>
                        <a href="/compare.php?a=shopify&b=woocommerce">Shopify vs WooCommerce</a>
                        <a href="/compare.php?a=zendrop&b=spocket">Zendrop vs Spocket</a>
                        <a href="/compare.php?a=minea&b=adspy">Minea vs AdSpy</a>
                        <a href="/compare.php?a=autods&b=dsers">AutoDS vs DSers</a>
                        <a href="/compare.php">Comparison Hub</a>
                    </div>

                    <div class="footer-col">
                        <h4 class="footer-col-title">Roadmap</h4>
                        <a href="/roadmap.php#stage-beginner">Beginner Stage</a>
                        <a href="/roadmap.php#stage-intermediate">Intermediate Stage</a>
                        <a href="/roadmap.php#stage-advanced">Scaling Stage</a>
                        <a href="/roadmap.php">Execution Roadmap</a>
                    </div>

                    <div class="footer-col">
                        <h4 class="footer-col-title">Calculator</h4>
                        <a href="/calculator.php">Profit Calculator</a>
                        <a href="/calculator.php#simulate">Margin Simulator</a>
                        <a href="/stack-builder.php">Stack Builder</a>
                    </div>

                    <div class="footer-col">
                        <h4 class="footer-col-title">Company</h4>
                        <a href="/about.php">About DropshippingStack</a>
                        <a href="/privacy.php">Privacy Policy</a>
                        <a href="/affiliate-disclosure.php">Affiliate Disclosure</a>
                        <button type="button" class="footer-btn-link" onclick="openIntroModal()">How It Works</button>
                        <a href="/submit.php">Submit a Tool</a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom Row -->
            <div class="footer-bottom-bar">
                <div class="footer-bottom-left">
                    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
                    <span class="footer-verified-badge">
                        <?= icon('shield-check', '', 14) ?>
                        <span>Verified Software Directory</span>
                    </span>
                </div>

                <div class="footer-social-links">
                    <a href="mailto:contact@dropshippingstack.com" aria-label="Email support" title="Email us">
                        <?= icon('mail', '', 16) ?>
                        <span>Email</span>
                    </a>
                    <a href="https://www.instagram.com/hdro.pshipping/#" target="_blank" rel="noopener noreferrer" aria-label="Instagram profile" title="Instagram">
                        <span>Instagram</span>
                    </a>
                    <a href="https://www.facebook.com/share/18ojiJyzhF/" target="_blank" rel="noopener noreferrer" aria-label="Facebook page" title="Facebook">
                        <span>Facebook</span>
                    </a>
                </div>
            </div>
        </footer>

    </div><!-- /.app-shell -->

    <!-- Chatbot Integration -->
    <?php if (file_exists(__DIR__ . '/partials/chatbot.php')): ?>
        <?php include __DIR__ . '/partials/chatbot.php'; ?>
    <?php else: ?>
        <script src="/assets/js/chat-widget.js" data-api="/api/chat-api.php" defer></script>
    <?php endif; ?>

    <!-- Newsletter AJAX Submission Handler -->
    <script>
    async function handleNewsletterSubmit(e) {
        e.preventDefault();
        const input = document.getElementById('newsletterEmail');
        const btn = document.getElementById('newsletterSubmitBtn');
        const group = document.getElementById('newsletterInputGroup');
        const feedback = document.getElementById('newsletterFeedback');

        if (!input || !btn || !feedback) return;
        const email = input.value.trim();

        if (!email || !email.includes('@')) {
            group.classList.add('has-error');
            feedback.className = 'newsletter-feedback error';
            feedback.innerHTML = '<span>Please enter a valid email address.</span>';
            input.focus();
            return;
        }

        group.classList.remove('has-error');
        group.classList.add('is-loading');
        btn.disabled = true;
        btn.innerHTML = '<span>Joining...</span>';
        feedback.className = 'newsletter-feedback';
        feedback.textContent = '';

        try {
            const formData = new FormData();
            formData.append('email', email);

            const res = await fetch('/api/newsletter.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                group.style.display = 'none';
                feedback.className = 'newsletter-feedback success';
                feedback.innerHTML = `
                    <div class="newsletter-success-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <div>
                            <strong>You're on the list!</strong>
                            <p>We've sent the complete Starter Kit to <em>${escapeHtml(email)}</em>.</p>
                        </div>
                    </div>
                `;
            } else {
                group.classList.remove('is-loading');
                group.classList.add('has-error');
                btn.disabled = false;
                btn.innerHTML = '<span class="btn-text">Get Free Access</span><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>';
                feedback.className = 'newsletter-feedback error';
                feedback.textContent = data.error || 'Submission failed. Please try again.';
            }
        } catch (err) {
            group.classList.remove('is-loading');
            btn.disabled = false;
            btn.innerHTML = '<span class="btn-text">Get Free Access</span>';
            feedback.className = 'newsletter-feedback error';
            feedback.textContent = 'Connection error. Please try again later.';
        }
    }

    function escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
    </script>
    <?php if (isset($_GET['scroll']) && $_GET['scroll'] === 'footer'): ?>
    <style>
        .app-content { display: none !important; }
    </style>
    <?php endif; ?>
</body>
</html>
