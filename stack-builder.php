<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$savedStack = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT tools_json FROM user_stack WHERE user_id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row) {
        $savedStack = json_decode($row['tools_json'], true);
    }
}

$page_title      = 'Build Your Dropshipping Stack — Personalized Software Stack';
$page_heading    = 'Stack Builder';
$page_subheading = 'Interactive configuration quiz to assemble your ideal toolkit';
$page_desc       = 'Answer 3 quick questions and get a personalized dropshipping tool stack recommendation.';

include __DIR__ . '/header.php';
?>

<style>
.quiz-wrap {
    max-width: 840px;
    margin: 0 auto;
    padding: 10px 0 60px;
}

.quiz-hero {
    text-align: center;
    margin-bottom: 36px;
}

.quiz-badge {
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

.quiz-hero h1 {
    font-size: clamp(28px, 4vw, 42px);
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.025em;
    margin-bottom: 12px;
    line-height: 1.15;
}

.quiz-hero h1 span {
    color: var(--accent);
}

.quiz-hero p {
    font-size: 15px;
    color: var(--muted);
    max-width: 500px;
    margin: 0 auto;
    line-height: 1.65;
}

/* Quiz Card Container */
.quiz-container {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 36px;
    box-shadow: var(--shadow-sm);
}

.step-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 12px;
}

.progress-bar-wrap {
    background: var(--surface-2);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-full);
    height: 8px;
    margin-bottom: 36px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--accent), #16a34a);
    border-radius: var(--radius-full);
    transition: width 0.35s var(--ease);
}

.quiz-step {
    display: none;
}

.quiz-step.active {
    display: block;
    animation: fadeRise 0.3s var(--ease) both;
}

.quiz-question {
    font-size: 22px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 24px;
    letter-spacing: -0.02em;
    line-height: 1.25;
}

.quiz-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 32px;
}

.quiz-option {
    border: 1.5px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 20px;
    cursor: pointer;
    transition: all var(--dur-fast) var(--ease);
    background: var(--surface-2);
    text-align: left;
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.quiz-option:hover {
    border-color: var(--accent);
    background: var(--surface);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.quiz-option.selected {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 3px var(--accent-light);
}

.option-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--accent-light);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.option-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 2px;
}

.option-desc {
    font-size: 12.5px;
    color: var(--muted);
    line-height: 1.45;
}

.quiz-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
}

/* Results Section */
.results-wrap {
    display: none;
}

.results-wrap.show {
    display: block;
    animation: fadeRise 0.35s var(--ease) both;
}

.results-header {
    text-align: center;
    margin-bottom: 36px;
}

.results-header-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--accent-light);
    color: var(--accent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
}

.results-header h2 {
    font-size: 28px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 6px;
    letter-spacing: -0.02em;
}

.results-header p {
    font-size: 14.5px;
    color: var(--muted);
}

.stack-section {
    margin-bottom: 32px;
}

.stack-section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.stack-section-title::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.stack-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 12px;
    box-shadow: var(--shadow-sm);
    transition: transform var(--dur-fast) var(--ease), box-shadow var(--dur-fast) var(--ease);
}

.stack-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
    border-color: rgba(50, 99, 70, 0.25);
}

.stack-card-icon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius);
    background: var(--accent-light);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stack-card-info {
    flex: 1;
    min-width: 0;
}

.stack-card-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 3px;
}

.stack-card-reason {
    font-size: 12.5px;
    color: var(--muted);
    line-height: 1.45;
}

.stack-card-right {
    text-align: right;
    flex-shrink: 0;
}

.stack-card-price {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 6px;
}

.total-cost-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 28px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 28px;
    box-shadow: var(--shadow-sm);
}

.total-cost-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

.total-cost-val {
    font-size: 32px;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.03em;
}

.restart-btn {
    display: block;
    text-align: center;
    margin: 28px auto 0;
    color: var(--muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    background: none;
    border: none;
    text-decoration: underline;
    transition: color 0.15s;
}

.restart-btn:hover {
    color: var(--accent);
}

@media (max-width: 768px) {
    .quiz-options {
        grid-template-columns: 1fr;
    }
    .quiz-container {
        padding: 24px 18px;
    }
    .total-cost-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
}
</style>

<div class="quiz-wrap">

    <div class="quiz-hero">
        <div class="quiz-badge">
            <?= icon('layers', '', 14) ?>
            <span>Architecture Architect</span>
        </div>
        <h1>Build Your Optimized<br><span>Dropshipping Stack</span></h1>
        <p>Answer 3 quick benchmarks to generate a lean, maximum-ROI software stack matched to your current phase.</p>
    </div>

    <!-- Quiz Interactive Card -->
    <div class="quiz-container" id="quizContainer">
        <div class="step-label" id="stepLabel">Step 1 of 3</div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" id="progressBar" style="width:33%"></div>
        </div>

        <!-- Step 1: Experience -->
        <div class="quiz-step active" id="step1">
            <div class="quiz-question">What is your current dropshipping experience level?</div>
            <div class="quiz-options">
                <div class="quiz-option" onclick="selectOption(this, 'experience', 'beginner')">
                    <div class="option-icon"><?= icon('package', '', 20) ?></div>
                    <div>
                        <div class="option-title">Complete Beginner</div>
                        <div class="option-desc">Starting fresh, building first storefront</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'experience', 'intermediate')">
                    <div class="option-icon"><?= icon('trending-up', '', 20) ?></div>
                    <div>
                        <div class="option-title">Intermediate Operator</div>
                        <div class="option-desc">Consistent orders, seeking scale and margins</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'experience', 'advanced')">
                    <div class="option-icon"><?= icon('zap', '', 20) ?></div>
                    <div>
                        <div class="option-title">Advanced Brand</div>
                        <div class="option-desc">Scaling ad spend, private label supply</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'experience', 'pro')">
                    <div class="option-icon"><?= icon('sparkles', '', 20) ?></div>
                    <div>
                        <div class="option-title">Enterprise / Portfolio</div>
                        <div class="option-desc">Managing multi-brand operations</div>
                    </div>
                </div>
            </div>
            <div class="quiz-nav">
                <span></span>
                <button type="button" class="btn-primary" id="next1" onclick="nextStep(2)" disabled style="padding:10px 24px">
                    <span>Continue</span>
                    <?= icon('arrow-right', '', 15) ?>
                </button>
            </div>
        </div>

        <!-- Step 2: Budget -->
        <div class="quiz-step" id="step2">
            <div class="quiz-question">What is your monthly software tool budget?</div>
            <div class="quiz-options">
                <div class="quiz-option" onclick="selectOption(this, 'budget', 'free')">
                    <div class="option-icon"><?= icon('check-circle', '', 20) ?></div>
                    <div>
                        <div class="option-title">Zero / Freemium Tiers</div>
                        <div class="option-desc">Bootstrap mode with free trial setups</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'budget', 'low')">
                    <div class="option-icon"><?= icon('dollar-sign', '', 20) ?></div>
                    <div>
                        <div class="option-title">Lean (Under $100/mo)</div>
                        <div class="option-desc">Essential high-priority tools only</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'budget', 'mid')">
                    <div class="option-icon"><?= icon('activity', '', 20) ?></div>
                    <div>
                        <div class="option-title">Growth ($100–$300/mo)</div>
                        <div class="option-desc">Invested in email retention and research</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'budget', 'high')">
                    <div class="option-icon"><?= icon('shield-check', '', 20) ?></div>
                    <div>
                        <div class="option-title">Scale ($300+/mo)</div>
                        <div class="option-desc">Top-tier attribution, multi-channel tools</div>
                    </div>
                </div>
            </div>
            <div class="quiz-nav">
                <button type="button" class="btn-secondary" onclick="prevStep(1)">← Back</button>
                <button type="button" class="btn-primary" id="next2" onclick="nextStep(3)" disabled style="padding:10px 24px">
                    <span>Continue</span>
                    <?= icon('arrow-right', '', 15) ?>
                </button>
            </div>
        </div>

        <!-- Step 3: Niche -->
        <div class="quiz-step" id="step3">
            <div class="quiz-question">Which core vertical are you operating in?</div>
            <div class="quiz-options">
                <div class="quiz-option" onclick="selectOption(this, 'niche', 'general')">
                    <div class="option-icon"><?= icon('shopping-bag', '', 20) ?></div>
                    <div>
                        <div class="option-title">Broad Catalog / Multi-Product</div>
                        <div class="option-desc">Testing across diverse trending angles</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'niche', 'fashion')">
                    <div class="option-icon"><?= icon('star', '', 20) ?></div>
                    <div>
                        <div class="option-title">Fashion, Beauty & Wellness</div>
                        <div class="option-desc">High AOV, visual social-driven sales</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'niche', 'home')">
                    <div class="option-icon"><?= icon('layout-grid', '', 20) ?></div>
                    <div>
                        <div class="option-title">Home, Pet & Lifestyle</div>
                        <div class="option-desc">High repeat purchase and loyalty</div>
                    </div>
                </div>
                <div class="quiz-option" onclick="selectOption(this, 'niche', 'tech')">
                    <div class="option-icon"><?= icon('zap', '', 20) ?></div>
                    <div>
                        <div class="option-title">Consumer Tech & Gadgets</div>
                        <div class="option-desc">Problem-solving video demonstration items</div>
                    </div>
                </div>
            </div>
            <div class="quiz-nav">
                <button type="button" class="btn-secondary" onclick="prevStep(2)">← Back</button>
                <button type="button" class="btn-primary" id="next3" onclick="showResults()" disabled style="padding:10px 24px">
                    <span>Generate Stack</span>
                    <?= icon('arrow-right', '', 15) ?>
                </button>
            </div>
        </div>
    </div>

    <!-- Results Container -->
    <div class="results-wrap" id="resultsWrap">
        <div class="results-header">
            <div class="results-header-icon">
                <?= icon('layers', '', 26) ?>
            </div>
            <h2>Your Recommended Stack</h2>
            <p id="resultsSubtitle">Based on your parameters, here is your vetted toolkit setup.</p>
        </div>

        <div id="stackResults"></div>

        <button type="button" class="restart-btn" onclick="restartQuiz()">
            ← Re-calibrate with different parameters
        </button>
    </div>

</div>

<script>
const answers = {
    experience: null,
    budget: null,
    niche: null
};

function selectOption(el, key, val) {
    el.closest('.quiz-options').querySelectorAll('.quiz-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    answers[key] = val;
    const stepMap = { experience: 'next1', budget: 'next2', niche: 'next3' };
    const btn = document.getElementById(stepMap[key]);
    if (btn) btn.disabled = false;
}

function nextStep(n) {
    document.querySelectorAll('.quiz-step').forEach(s => s.classList.remove('active'));
    const step = document.getElementById('step' + n);
    if (step) step.classList.add('active');
    document.getElementById('stepLabel').textContent = `Step ${n} of 3`;
    document.getElementById('progressBar').style.width = (n / 3 * 100) + '%';
}

function prevStep(n) {
    document.querySelectorAll('.quiz-step').forEach(s => s.classList.remove('active'));
    const step = document.getElementById('step' + n);
    if (step) step.classList.add('active');
    document.getElementById('stepLabel').textContent = `Step ${n} of 3`;
    document.getElementById('progressBar').style.width = (n / 3 * 100) + '%';
}

const stacks = {
    beginner: {
        free: [
            { name: 'WooCommerce', reason: 'Open-source storefront foundation with zero upfront software fee', price: 'Free', slug: 'woocommerce', essential: true },
            { name: 'DSers', reason: 'Free AliExpress supply chain sync and fulfillment automation', price: 'Free tier', slug: 'dsers', essential: true },
            { name: 'Canva Pro', reason: 'Rapid asset creation and mockups for product listings', price: 'Free tier', slug: 'canva-pro', essential: false }
        ],
        low: [
            { name: 'Shopify', reason: 'Fastest turn-key checkout with integrated payment processing', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'DSers', reason: 'Automated catalog sync and 1-click supplier routing', price: 'Free tier', slug: 'dsers', essential: true },
            { name: 'Minea', reason: 'Spot trending ad creatives and viral engagement signals early', price: 'From $49/mo', slug: 'minea', essential: false },
            { name: 'Tidio', reason: 'Real-time live chat to recover hesitant cart abandoners', price: 'Free tier', slug: 'tidio', essential: false }
        ],
        mid: [
            { name: 'Shopify', reason: 'High-converting checkout engine designed for scale', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Zendrop', reason: 'Vetted suppliers with expedited domestic delivery windows', price: 'Free tier', slug: 'zendrop', essential: true },
            { name: 'Minea', reason: 'Data-driven product research and competitive ad intelligence', price: 'From $49/mo', slug: 'minea', essential: true },
            { name: 'Klaviyo', reason: 'Automated email flows to maximize checkout recovery', price: 'Free tier', slug: 'klaviyo', essential: false }
        ],
        high: [
            { name: 'Shopify', reason: 'Uncompromising enterprise infrastructure and checkout', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Spocket', reason: 'US & EU localized shipping with branded packaging support', price: 'From $39/mo', slug: 'spocket', essential: true },
            { name: 'AdSpy', reason: 'Deepest intelligence database across Meta ad networks', price: 'From $149/mo', slug: 'adspy', essential: true },
            { name: 'Triple Whale', reason: 'Multi-touch attribution to protect advertising profitability', price: 'From $129/mo', slug: 'triple-whale', essential: false }
        ]
    },
    intermediate: {
        free: [
            { name: 'WooCommerce', reason: 'Flexible open store infrastructure for customizable margin rules', price: 'Free', slug: 'woocommerce', essential: true },
            { name: 'Spocket', reason: 'US suppliers with fast delivery on freemium entry tier', price: 'Free tier', slug: 'spocket', essential: true }
        ],
        low: [
            { name: 'Shopify', reason: 'Core conversion engine', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'AutoDS', reason: 'Comprehensive inventory sync and automated order routing', price: 'From $27/mo', slug: 'autods', essential: true }
        ],
        mid: [
            { name: 'Shopify', reason: 'Scalable commerce foundation', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Zendrop', reason: 'Automated warehouse fulfillment', price: 'From $49/mo', slug: 'zendrop', essential: true },
            { name: 'Klaviyo', reason: 'High-margin automated retention engine', price: 'From $45/mo', slug: 'klaviyo', essential: true }
        ],
        high: [
            { name: 'Shopify', reason: 'Tier-1 store architecture', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Triple Whale', reason: 'Accurate ROAS attribution across Meta and TikTok', price: 'From $129/mo', slug: 'triple-whale', essential: true },
            { name: 'AdSpy', reason: 'High-velocity competitor ad intelligence', price: 'From $149/mo', slug: 'adspy', essential: true }
        ]
    },
    advanced: {
        free: [
            { name: 'Shopify', reason: 'Industry standard foundation', price: 'From $29/mo', slug: 'shopify', essential: true }
        ],
        low: [
            { name: 'Shopify', reason: 'Scale infrastructure', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Klaviyo', reason: 'Advanced customer lifecycle segmentation', price: 'From $45/mo', slug: 'klaviyo', essential: true }
        ],
        mid: [
            { name: 'Shopify', reason: 'Scale infrastructure', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Triple Whale', reason: 'Attribution & unit economics tracking', price: 'From $129/mo', slug: 'triple-whale', essential: true },
            { name: 'Klaviyo', reason: 'High-LTV email/SMS lifecycle', price: 'From $45/mo', slug: 'klaviyo', essential: true }
        ],
        high: [
            { name: 'Shopify', reason: 'Enterprise checkout framework', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Triple Whale', reason: 'Server-side attribution and P&L dashboards', price: 'From $129/mo', slug: 'triple-whale', essential: true },
            { name: 'AdSpy', reason: 'Omnichannel ad creative tracking', price: 'From $149/mo', slug: 'adspy', essential: true },
            { name: 'Gorgias', reason: 'Unified customer desk automation', price: 'From $10/mo', slug: 'gorgias', essential: false }
        ]
    },
    pro: {
        high: [
            { name: 'Shopify', reason: 'Enterprise checkout framework', price: 'From $29/mo', slug: 'shopify', essential: true },
            { name: 'Triple Whale', reason: 'Multi-store attribution and aggregated unit economics', price: 'From $129/mo', slug: 'triple-whale', essential: true },
            { name: 'AdSpy', reason: 'Ad database intelligence across global markets', price: 'From $149/mo', slug: 'adspy', essential: true }
        ]
    }
};

function showResults() {
    const { experience, budget, niche } = answers;
    if (!experience || !budget || !niche) return;

    document.getElementById('quizContainer').style.display = 'none';
    const resultsWrap = document.getElementById('resultsWrap');
    resultsWrap.classList.add('show');

    const tools = stacks[experience]?.[budget] || stacks['beginner']['low'];
    saveStack({ experience, budget, niche });

    const expLabels = { beginner: 'Beginner', intermediate: 'Intermediate', advanced: 'Advanced', pro: 'Portfolio Pro' };
    const budgetLabels = { free: 'Zero / Freemium', low: 'Under $100/mo', mid: '$100–$300/mo', high: '$300+/mo' };
    const nicheLabels = { general: 'Broad Catalog', fashion: 'Fashion & Beauty', home: 'Home & Pet', tech: 'Tech & Gadgets' };

    document.getElementById('resultsSubtitle').textContent =
        `${expLabels[experience] || 'Custom'} · ${budgetLabels[budget] || 'Flexible'} · ${nicheLabels[niche] || 'General'}`;

    const essential = tools.filter(t => t.essential);
    const optional = tools.filter(t => !t.essential);

    let pricedTools = tools.filter(t => t.price.match(/\$(\d+)/));
    let total = pricedTools.reduce((sum, t) => {
        const match = t.price.match(/\$(\d+)/);
        return sum + (match ? parseInt(match[1]) : 0);
    }, 0);

    const renderCard = t => `
        <div class="stack-card">
            <div class="stack-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <div class="stack-card-info">
                <div class="stack-card-name">
                    ${t.name}
                    ${t.essential ? '<span style="font-size:10.5px;background:var(--accent-light);color:var(--accent);padding:2px 8px;border-radius:999px;font-weight:700;margin-left:6px">CORE STACK</span>' : ''}
                </div>
                <div class="stack-card-reason">${t.reason}</div>
            </div>
            <div class="stack-card-right">
                <div class="stack-card-price">${t.price}</div>
                <a href="/go.php?slug=${t.slug}" class="btn-primary" target="_blank" rel="noopener" style="padding:6px 14px;font-size:12px">
                    <span>Visit</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                </a>
            </div>
        </div>
    `;

    let html = '';
    if (essential.length) {
        html += `<div class="stack-section"><div class="stack-section-title">Core Foundation (${essential.length})</div>${essential.map(renderCard).join('')}</div>`;
    }
    if (optional.length) {
        html += `<div class="stack-section"><div class="stack-section-title">High-ROI Add-ons (${optional.length})</div>${optional.map(renderCard).join('')}</div>`;
    }

    html += `
        <div class="total-cost-card">
            <div>
                <div class="total-cost-label">Estimated Monthly Tool Run-Rate</div>
                <div class="total-cost-val">$${total}<span style="font-size:16px;font-weight:500;color:var(--muted)">/mo</span></div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Free tiers and usage-based scaling excluded</div>
            </div>
            <div style="display:flex;gap:10px">
                <a href="/calculator.php" class="btn-secondary" style="padding:10px 18px">Simulate Net Margins</a>
                <a href="/roadmap.php" class="btn-primary" style="padding:10px 18px">View Growth Roadmap</a>
            </div>
        </div>
    `;

    document.getElementById('stackResults').innerHTML = html;
}

function restartQuiz() {
    answers.experience = null;
    answers.budget = null;
    answers.niche = null;
    document.getElementById('quizContainer').style.display = 'block';
    document.getElementById('resultsWrap').classList.remove('show');
    document.querySelectorAll('.quiz-step').forEach(s => s.classList.remove('active'));
    document.getElementById('step1').classList.add('active');
    document.getElementById('stepLabel').textContent = 'Step 1 of 3';
    document.getElementById('progressBar').style.width = '33%';
    document.querySelectorAll('.quiz-option').forEach(o => o.classList.remove('selected'));
    ['next1', 'next2', 'next3'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.disabled = true;
    });
}

async function saveStack(data) {
    try {
        await fetch('/save-stack.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
    } catch (e) {
        console.error("Save failed:", e);
    }
}

const savedStack = <?= json_encode($savedStack ?? null) ?>;
if (savedStack && savedStack.experience && savedStack.budget && savedStack.niche) {
    answers.experience = savedStack.experience;
    answers.budget = savedStack.budget;
    answers.niche = savedStack.niche;
    showResults();
}
</script>

<?php include __DIR__ . '/footer.php'; ?>
