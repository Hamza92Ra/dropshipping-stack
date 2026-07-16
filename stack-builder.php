<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
$savedStack = null;

if (isset($_SESSION['user_id'])) {

    $stmt = $pdo->prepare(
        "SELECT tools_json
FROM user_stack
WHERE user_id=?"
    );

    $stmt->execute([
        $_SESSION['user_id']
    ]);

    $row = $stmt->fetch();

    if ($row) {
        $savedStack = json_decode(
            $row['tools_json'],
            true
        );
    }
}

$page_title = 'Build Your Stack — Find the Perfect Dropshipping Tools';
$page_desc  = 'Answer 3 quick questions and get a personalized dropshipping tool stack recommendation.';

include __DIR__ . '/header.php';


?>
<style>
    .quiz-wrap {
        max-width: 760px;
        margin: 0 auto;
        padding: 48px 24px 80px;
    }

    .quiz-hero {
        text-align: center;
        margin-bottom: 48px;
    }

    .quiz-hero .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--accent-light);
        color: var(--accent);
        font-size: 12px;
        font-weight: 700;
        padding: 5px 14px;
        border-radius: 20px;
        margin-bottom: 18px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .quiz-hero h1 {
        font-size: clamp(28px, 4vw, 44px);
        font-weight: 800;
        color: var(--ink);
        letter-spacing: -0.025em;
        margin-bottom: 14px;
        line-height: 1.15;
    }

    .quiz-hero h1 span {
        color: var(--accent);
    }

    .quiz-hero p {
        font-size: 16px;
        color: var(--muted);
        max-width: 480px;
        margin: 0 auto;
        line-height: 1.7;
    }

    /* Progress */
    .progress-bar-wrap {
        background: var(--border);
        border-radius: 99px;
        height: 5px;
        margin-bottom: 40px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent), #8b5cf6);
        border-radius: 99px;
        transition: width 0.4s ease;
    }

    .step-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 10px;
    }

    /* Steps */
    .quiz-step {
        display: none;
    }

    .quiz-step.active {
        display: block;
    }

    .quiz-question {
        font-size: 22px;
        font-weight: 800;
        color: var(--ink);
        margin-bottom: 24px;
        letter-spacing: -0.01em;
        line-height: 1.3;
    }

    .quiz-options {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 32px;
    }

    .quiz-option {
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 18px 20px;
        cursor: pointer;
        transition: all 0.15s;
        background: var(--surface);
        text-align: left;
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }

    .quiz-option:hover {
        border-color: var(--accent);
        background: var(--accent-light);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .quiz-option.selected {
        border-color: var(--accent);
        background: var(--accent-light);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }

    .option-icon {
        font-size: 28px;
        flex-shrink: 0;
        line-height: 1;
    }

    .option-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 3px;
    }

    .option-desc {
        font-size: 12px;
        color: var(--muted);
        line-height: 1.5;
    }

    .quiz-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 8px;
    }

    .btn-back {
        background: none;
        border: 1.5px solid var(--border);
        color: var(--body);
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        font-family: var(--font);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s;
    }

    .btn-back:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .btn-next {
        background: var(--accent);
        color: #fff;
        border: none;
        padding: 12px 28px;
        border-radius: var(--radius-sm);
        font-family: var(--font);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-next:hover {
        background: var(--accent-h);
        transform: translateY(-1px);
    }

    .btn-next:disabled {
        opacity: 0.4;
        cursor: not-allowed;
        transform: none;
    }

    /* Results */
    .results-wrap {
        display: none;
    }

    .results-wrap.show {
        display: block;
    }

    .results-header {
        text-align: center;
        margin-bottom: 36px;
    }

    .results-header h2 {
        font-size: 30px;
        font-weight: 800;
        color: var(--ink);
        margin-bottom: 8px;
        letter-spacing: -0.02em;
    }

    .results-header p {
        font-size: 15px;
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
        border: 1.5px solid var(--border);
        border-radius: var(--radius);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 10px;
        transition: all 0.15s;
    }

    .stack-card:hover {
        border-color: var(--accent);
        box-shadow: var(--shadow-sm);
    }

    .stack-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: var(--bg);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stack-card-info {
        flex: 1;
    }

    .stack-card-name {
        font-size: 16px;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 2px;
    }

    .stack-card-reason {
        font-size: 12px;
        color: var(--muted);
        line-height: 1.5;
    }

    .stack-card-right {
        text-align: right;
        flex-shrink: 0;
    }

    .stack-card-price {
        font-size: 13px;
        font-weight: 700;
        color: var(--body);
        margin-bottom: 6px;
    }

    .stack-card-btn {
        display: inline-block;
        background: var(--accent);
        color: #fff;
        padding: 7px 14px;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 700;
        transition: background 0.15s;
    }

    .stack-card-btn:hover {
        background: var(--accent-h);
    }

    .stack-card.essential {
        border-color: var(--featured);
    }

    .stack-card.essential .stack-card-icon {
        background: #fef3c7;
    }

    .total-cost {
        background: var(--dark);
        color: #fff;
        border-radius: var(--radius);
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 24px;
    }

    .total-cost-label {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.5);
    }

    .total-cost-val {
        font-size: 28px;
        font-weight: 800;
        color: var(--cyan);
        letter-spacing: -0.02em;
    }

    .total-cost-note {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.3);
        margin-top: 2px;
    }

    .restart-btn {
        display: block;
        text-align: center;
        margin-top: 24px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        background: none;
        border: none;
        font-family: var(--font);
        text-decoration: underline;
    }
</style>

<div class="quiz-wrap">

    <div class="quiz-hero">
        <div class="badge">✨ Free Tool</div>
        <h1>Build Your Perfect<br><span>Dropshipping Stack</span></h1>
        <p>Answer 3 quick questions and get a personalized tool recommendation based on your budget, experience, and niche.</p>
    </div>

    <!-- Quiz -->
    <div class="quiz-container" id="quizContainer">
        <div class="step-label" id="stepLabel">Step 1 of 3</div>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" id="progressBar" style="width:33%"></div>
        </div>

        <!-- Step 1: Experience -->
        <div class="quiz-step active" id="step1">
            <div class="quiz-question">What's your experience level with dropshipping?</div>
            <div class="quiz-options">
                <label class="quiz-option" onclick="selectOption(this, 'experience', 'beginner')">
                    <div class="option-icon">🌱</div>
                    <div>
                        <div class="option-title">Complete Beginner</div>
                        <div class="option-desc">Never run a dropshipping store before</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'experience', 'intermediate')">
                    <div class="option-icon">📈</div>
                    <div>
                        <div class="option-title">Some Experience</div>
                        <div class="option-desc">Made a few sales, learning the ropes</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'experience', 'advanced')">
                    <div class="option-icon">🚀</div>
                    <div>
                        <div class="option-title">Experienced</div>
                        <div class="option-desc">Running a store, want to scale</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'experience', 'pro')">
                    <div class="option-icon">💎</div>
                    <div>
                        <div class="option-title">Pro / Agency</div>
                        <div class="option-desc">Managing multiple stores</div>
                    </div>
                </label>
            </div>
            <div class="quiz-nav">
                <span></span>
                <button class="btn-next" id="next1" onclick="nextStep(2)" disabled>Continue →</button>
            </div>
        </div>

        <!-- Step 2: Budget -->
        <div class="quiz-step" id="step2">
            <div class="quiz-question">What's your monthly tool budget?</div>
            <div class="quiz-options">
                <label class="quiz-option" onclick="selectOption(this, 'budget', 'free')">
                    <div class="option-icon">🆓</div>
                    <div>
                        <div class="option-title">Free only</div>
                        <div class="option-desc">Just starting, no budget yet</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'budget', 'low')">
                    <div class="option-icon">💵</div>
                    <div>
                        <div class="option-title">Under $100/mo</div>
                        <div class="option-desc">Essential tools only</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'budget', 'mid')">
                    <div class="option-icon">💰</div>
                    <div>
                        <div class="option-title">$100–$300/mo</div>
                        <div class="option-desc">Ready to invest in growth</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'budget', 'high')">
                    <div class="option-icon">🏦</div>
                    <div>
                        <div class="option-title">$300+/mo</div>
                        <div class="option-desc">Best tools, no compromises</div>
                    </div>
                </label>
            </div>
            <div class="quiz-nav">
                <button class="btn-back" onclick="prevStep(1)">← Back</button>
                <button class="btn-next" id="next2" onclick="nextStep(3)" disabled>Continue →</button>
            </div>
        </div>

        <!-- Step 3: Niche -->
        <div class="quiz-step" id="step3">
            <div class="quiz-question">What type of products do you want to sell?</div>
            <div class="quiz-options">
                <label class="quiz-option" onclick="selectOption(this, 'niche', 'general')">
                    <div class="option-icon">🛍️</div>
                    <div>
                        <div class="option-title">General Store</div>
                        <div class="option-desc">Multiple categories, trend-based</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'niche', 'fashion')">
                    <div class="option-icon">👗</div>
                    <div>
                        <div class="option-title">Fashion & Beauty</div>
                        <div class="option-desc">Clothing, accessories, cosmetics</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'niche', 'home')">
                    <div class="option-icon">🏠</div>
                    <div>
                        <div class="option-title">Home & Garden</div>
                        <div class="option-desc">Furniture, decor, kitchen</div>
                    </div>
                </label>
                <label class="quiz-option" onclick="selectOption(this, 'niche', 'tech')">
                    <div class="option-icon">📱</div>
                    <div>
                        <div class="option-title">Tech & Gadgets</div>
                        <div class="option-desc">Electronics, accessories, tools</div>
                    </div>
                </label>
            </div>
            <div class="quiz-nav">
                <button class="btn-back" onclick="prevStep(2)">← Back</button>
                <button class="btn-next" id="next3" onclick="showResults()" disabled>Build My Stack →</button>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="results-wrap" id="resultsWrap">
        <div class="results-header">
            <div style="font-size:48px;margin-bottom:12px">🎯</div>
            <h2>Your Perfect Stack</h2>
            <p id="resultsSubtitle">Based on your answers, here are the best tools for you.</p>
        </div>

        <div id="stackResults"></div>

        <button class="restart-btn" onclick="restartQuiz()">← Start over with different answers</button>
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
        const stepMap = {
            experience: 'next1',
            budget: 'next2',
            niche: 'next3'
        };
        document.getElementById(stepMap[key]).disabled = false;
    }

    function nextStep(n) {
        document.querySelectorAll('.quiz-step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + n).classList.add('active');
        document.getElementById('stepLabel').textContent = `Step ${n} of 3`;
        document.getElementById('progressBar').style.width = (n / 3 * 100) + '%';
    }

    function prevStep(n) {
        document.querySelectorAll('.quiz-step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + n).classList.add('active');
        document.getElementById('stepLabel').textContent = `Step ${n} of 3`;
        document.getElementById('progressBar').style.width = (n / 3 * 100) + '%';
    }

    const stacks = {
        // [experience][budget] → tool list
        beginner: {
            free: [{
                    name: 'WooCommerce',
                    icon: '🛒',
                    reason: 'Free store builder — perfect to start with zero cost',
                    price: 'Free',
                    slug: 'woocommerce',
                    essential: true
                },
                {
                    name: 'DSers',
                    icon: '📦',
                    reason: 'Free AliExpress dropshipping automation',
                    price: 'Free',
                    slug: 'dsers',
                    essential: true
                },
                {
                    name: 'Canva Pro',
                    icon: '🎨',
                    reason: 'Design ads and product images for free',
                    price: 'Free plan',
                    slug: 'canva-pro',
                    essential: false
                },
            ],
            low: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Best beginner store builder — easiest to set up',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'DSers',
                    icon: '📦',
                    reason: 'Free AliExpress supplier integration',
                    price: 'Free',
                    slug: 'dsers',
                    essential: true
                },
                {
                    name: 'Minea',
                    icon: '🔍',
                    reason: 'Find winning products from real ad data',
                    price: 'From $49/mo',
                    slug: 'minea',
                    essential: false
                },
                {
                    name: 'Tidio',
                    icon: '💬',
                    reason: 'Live chat to recover abandoning visitors',
                    price: 'Freemium',
                    slug: 'tidio',
                    essential: false
                },
            ],
            mid: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Best store builder for growing brands',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Zendrop',
                    icon: '📦',
                    reason: 'US-based suppliers with fast 3-5 day shipping',
                    price: 'Freemium',
                    slug: 'zendrop',
                    essential: true
                },
                {
                    name: 'Minea',
                    icon: '🔍',
                    reason: 'Find winning products before competitors',
                    price: 'From $49/mo',
                    slug: 'minea',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Email marketing to maximize repeat sales',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Loox',
                    icon: '⭐',
                    reason: 'Photo reviews to build trust and increase conversions',
                    price: 'From $10/mo',
                    slug: 'loox',
                    essential: false
                },
            ],
            high: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Best store builder — non-negotiable at this level',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Spocket',
                    icon: '📦',
                    reason: 'Premium US/EU suppliers, fast shipping',
                    price: 'Freemium',
                    slug: 'spocket',
                    essential: true
                },
                {
                    name: 'AdSpy',
                    icon: '🔍',
                    reason: 'Largest ad database to find winning products',
                    price: 'From $149/mo',
                    slug: 'adspy',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Best email + SMS marketing platform',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Triple Whale',
                    icon: '📊',
                    reason: 'Accurate profit tracking and attribution',
                    price: 'From $129/mo',
                    slug: 'triple-whale',
                    essential: false
                },
                {
                    name: 'AdCreative.ai',
                    icon: '🤖',
                    reason: 'AI-generated ads that convert',
                    price: 'From $29/mo',
                    slug: 'adcreative-ai',
                    essential: false
                },
            ],
        },
        intermediate: {
            free: [{
                    name: 'WooCommerce',
                    icon: '🛒',
                    reason: 'Free and powerful — move from basic platforms',
                    price: 'Free',
                    slug: 'woocommerce',
                    essential: true
                },
                {
                    name: 'Spocket',
                    icon: '📦',
                    reason: 'Better suppliers than AliExpress',
                    price: 'Freemium',
                    slug: 'spocket',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Free up to 250 contacts — start building your list',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
            ],
            low: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Upgrade from WooCommerce for better conversion',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Zendrop',
                    icon: '📦',
                    reason: 'Faster shipping = better reviews',
                    price: 'Freemium',
                    slug: 'zendrop',
                    essential: true
                },
                {
                    name: 'Sell The Trend',
                    icon: '🔍',
                    reason: 'All-in-one product research + store builder',
                    price: 'From $40/mo',
                    slug: 'sell-the-trend',
                    essential: false
                },
                {
                    name: 'Omnisend',
                    icon: '📧',
                    reason: 'Email + SMS automation for more sales',
                    price: 'Freemium',
                    slug: 'omnisend',
                    essential: false
                },
            ],
            mid: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Scale with Shopify\'s advanced features',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'AutoDS',
                    icon: '⚙️',
                    reason: 'Automate your entire dropshipping operation',
                    price: 'From $27/mo',
                    slug: 'autods',
                    essential: true
                },
                {
                    name: 'Minea',
                    icon: '🔍',
                    reason: 'Spy on winning ads to find your next product',
                    price: 'From $49/mo',
                    slug: 'minea',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Email sequences that recover abandoned carts',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Loox',
                    icon: '⭐',
                    reason: 'Social proof that increases conversions by 15%',
                    price: 'From $10/mo',
                    slug: 'loox',
                    essential: false
                },
                {
                    name: 'Tidio',
                    icon: '💬',
                    reason: 'AI chatbot handles customer questions 24/7',
                    price: 'Freemium',
                    slug: 'tidio',
                    essential: false
                },
            ],
            high: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'The foundation of every serious store',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Spocket',
                    icon: '📦',
                    reason: 'Premium US/EU suppliers, 2-5 day delivery',
                    price: 'Freemium',
                    slug: 'spocket',
                    essential: true
                },
                {
                    name: 'AdSpy',
                    icon: '🔍',
                    reason: 'Find competitors\' winning ads instantly',
                    price: 'From $149/mo',
                    slug: 'adspy',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Email + SMS that generates 20-40% of revenue',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Triple Whale',
                    icon: '📊',
                    reason: 'Know your real ROAS and profit',
                    price: 'From $129/mo',
                    slug: 'triple-whale',
                    essential: false
                },
                {
                    name: 'Gorgias',
                    icon: '🎧',
                    reason: 'Customer support that scales with your store',
                    price: 'From $10/mo',
                    slug: 'gorgias',
                    essential: false
                },
            ],
        },
        advanced: {
            free: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'You need Shopify to scale — worth the investment',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Free tier is powerful enough to start',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: true
                },
            ],
            low: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Best platform for scaling dropshipping',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'AutoDS',
                    icon: '⚙️',
                    reason: 'Automate fulfillment and price monitoring',
                    price: 'From $27/mo',
                    slug: 'autods',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Email revenue should be 30%+ of your total',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Loox',
                    icon: '⭐',
                    reason: 'Video reviews are the highest converting social proof',
                    price: 'From $10/mo',
                    slug: 'loox',
                    essential: false
                },
            ],
            mid: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Advanced plan for lower transaction fees at scale',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Spocket',
                    icon: '📦',
                    reason: 'Branded invoicing + faster US/EU suppliers',
                    price: 'Freemium',
                    slug: 'spocket',
                    essential: true
                },
                {
                    name: 'Triple Whale',
                    icon: '📊',
                    reason: 'You can\'t scale what you can\'t measure accurately',
                    price: 'From $129/mo',
                    slug: 'triple-whale',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Advanced flows and segmentation for high AOV',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'AdCreative.ai',
                    icon: '🤖',
                    reason: 'Scale ad creative production with AI',
                    price: 'From $29/mo',
                    slug: 'adcreative-ai',
                    essential: false
                },
                {
                    name: 'Gorgias',
                    icon: '🎧',
                    reason: 'Handle high volume support tickets efficiently',
                    price: 'From $10/mo',
                    slug: 'gorgias',
                    essential: false
                },
            ],
            high: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Shopify Plus territory — lowest fees at high volume',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Spocket',
                    icon: '📦',
                    reason: 'Private label + branded packaging at scale',
                    price: 'Freemium',
                    slug: 'spocket',
                    essential: true
                },
                {
                    name: 'AdSpy',
                    icon: '🔍',
                    reason: 'Stay ahead of competitors with ad intelligence',
                    price: 'From $149/mo',
                    slug: 'adspy',
                    essential: true
                },
                {
                    name: 'Triple Whale',
                    icon: '📊',
                    reason: 'Real-time profit dashboard and attribution',
                    price: 'From $129/mo',
                    slug: 'triple-whale',
                    essential: true
                },
                {
                    name: 'Northbeam',
                    icon: '📈',
                    reason: 'Multi-touch attribution for high ad spend',
                    price: 'From $250/mo',
                    slug: 'northbeam',
                    essential: false
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Predictive analytics and VIP segmentation',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Gorgias',
                    icon: '🎧',
                    reason: 'Enterprise support with Shopify deep integration',
                    price: 'From $10/mo',
                    slug: 'gorgias',
                    essential: false
                },
            ],
        },
        pro: {
            free: [],
            low: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Essential for managing multiple stores',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'AutoDS',
                    icon: '⚙️',
                    reason: 'Multi-store automation from one dashboard',
                    price: 'From $27/mo',
                    slug: 'autods',
                    essential: true
                },
            ],
            mid: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Multiple store management made easy',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'AutoDS',
                    icon: '⚙️',
                    reason: 'Automate all stores from one place',
                    price: 'From $27/mo',
                    slug: 'autods',
                    essential: true
                },
                {
                    name: 'Triple Whale',
                    icon: '📊',
                    reason: 'Consolidated analytics across all stores',
                    price: 'From $129/mo',
                    slug: 'triple-whale',
                    essential: true
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Separate email lists per store, one platform',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Gorgias',
                    icon: '🎧',
                    reason: 'Centralized support inbox for all stores',
                    price: 'From $10/mo',
                    slug: 'gorgias',
                    essential: false
                },
            ],
            high: [{
                    name: 'Shopify',
                    icon: '🏪',
                    reason: 'Shopify Plus for multi-store management',
                    price: 'From $29/mo',
                    slug: 'shopify',
                    essential: true
                },
                {
                    name: 'Spocket',
                    icon: '📦',
                    reason: 'White-label products across all stores',
                    price: 'Freemium',
                    slug: 'spocket',
                    essential: true
                },
                {
                    name: 'AdSpy',
                    icon: '🔍',
                    reason: 'Monitor competitors across all your niches',
                    price: 'From $149/mo',
                    slug: 'adspy',
                    essential: true
                },
                {
                    name: 'Northbeam',
                    icon: '📈',
                    reason: 'Agency-level attribution reporting',
                    price: 'From $250/mo',
                    slug: 'northbeam',
                    essential: true
                },
                {
                    name: 'Triple Whale',
                    icon: '📊',
                    reason: 'Real-time profit for every store',
                    price: 'From $129/mo',
                    slug: 'triple-whale',
                    essential: false
                },
                {
                    name: 'Klaviyo',
                    icon: '📧',
                    reason: 'Email infrastructure for all brands',
                    price: 'Freemium',
                    slug: 'klaviyo',
                    essential: false
                },
                {
                    name: 'Gorgias',
                    icon: '🎧',
                    reason: 'One support platform for all stores',
                    price: 'From $10/mo',
                    slug: 'gorgias',
                    essential: false
                },
                {
                    name: 'AdCreative.ai',
                    icon: '🤖',
                    reason: 'Scale creative production across all brands',
                    price: 'From $29/mo',
                    slug: 'adcreative-ai',
                    essential: false
                },
            ],
        }
    };

    function showResults() {
        const {
            experience,
            budget,
            niche
        } = answers;
        if (!experience || !budget || !niche) return;

        document.getElementById('quizContainer').style.display = 'none';
        const resultsWrap = document.getElementById('resultsWrap');
        resultsWrap.classList.add('show');

        const tools = stacks[experience]?.[budget] || stacks['beginner']['low'];
        saveStack({
            experience,
            budget,
            niche
        });

        const expLabels = {
            beginner: 'Beginner',
            intermediate: 'Intermediate',
            advanced: 'Advanced',
            pro: 'Pro'
        };
        const budgetLabels = {
            free: 'Free',
            low: 'Under $100/mo',
            mid: '$100–$300/mo',
            high: '$300+/mo'
        };
        const nicheLabels = {
            general: 'General Store',
            fashion: 'Fashion & Beauty',
            home: 'Home & Garden',
            tech: 'Tech & Gadgets'
        };

        document.getElementById('resultsSubtitle').textContent =
            `${expLabels[experience]} · ${budgetLabels[budget]} · ${nicheLabels[niche]}`;

        const essential = tools.filter(t => t.essential);
        const optional = tools.filter(t => !t.essential);

        let pricedTools = tools.filter(t => {
            const match = t.price.match(/\$(\d+)/);
            return match;
        });
        let total = pricedTools.reduce((sum, t) => {
            const match = t.price.match(/\$(\d+)/);
            return sum + (match ? parseInt(match[1]) : 0);
        }, 0);

        const renderCard = t => `
        <div class="stack-card ${t.essential ? 'essential' : ''}">
            <div class="stack-card-icon">${t.icon}</div>
            <div class="stack-card-info">
                <div class="stack-card-name">${t.name} ${t.essential ? '<span style="font-size:10px;background:#fef3c7;color:#b45309;padding:2px 6px;border-radius:4px;font-weight:700;margin-left:4px">MUST HAVE</span>' : ''}</div>
                <div class="stack-card-reason">${t.reason}</div>
            </div>
            <div class="stack-card-right">
                <div class="stack-card-price">${t.price}</div>
                <a href="http://localhost/dropshipping/go.php?slug=${t.slug}" class="stack-card-btn" target="_blank">Get it →</a>
            </div>
        </div>
    `;

        let html = '';
        if (essential.length) {
            html += `<div class="stack-section"><div class="stack-section-title">⭐ Essential Tools</div>${essential.map(renderCard).join('')}</div>`;
        }
        if (optional.length) {
            html += `<div class="stack-section"><div class="stack-section-title">💡 Recommended Add-ons</div>${optional.map(renderCard).join('')}</div>`;
        }

        html += `
        <div class="total-cost">
            <div>
                <div class="total-cost-label">Estimated monthly cost</div>
                <div class="total-cost-val">$${total}/mo</div>
                <div class="total-cost-note">Free tiers not included in estimate</div>
            </div>
            <div style="text-align:right">
                <div style="font-size:13px;color:rgba(255,255,255,0.5);margin-bottom:4px">${tools.length} tools recommended</div>
                <div style="font-size:12px;color:rgba(255,255,255,0.3)">All links are affiliate links</div>
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
        ['next1', 'next2', 'next3'].forEach(id => document.getElementById(id).disabled = true);
    }
</script>

<script>
    async function saveStack(data) {

        try {

            await fetch(
                '/dropshipping/save-stack.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                }
            );

        } catch (e) {
            console.log("Save failed:", e);
        }

    }

    const savedStack = <?= json_encode($savedStack ?? null) ?>;

    if (savedStack) {

        answers.experience = savedStack.experience;
        answers.budget = savedStack.budget;
        answers.niche = savedStack.niche;

        showResults();

    }
</script>;
<script src="/assets/js/darkmode.js"></script>
<?php include __DIR__ . '/footer.php'; ?>