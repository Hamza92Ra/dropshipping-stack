    <?php
    @define('APP', true);
    require_once __DIR__ . '/config.php';
    // Load saved roadmap if logged in
    $roadmap = null;

    if (isset($_SESSION['user_id'])) {

        $stmt = $pdo->prepare(
            "SELECT * FROM user_roadmap WHERE user_id=?"
        );

        $stmt->execute([
            $_SESSION['user_id']
        ]);

        $roadmap = $stmt->fetch();
    }

    $page_title = 'Dropshipping Roadmap 2026 — Step by Step Guide';
    $page_desc  = 'The complete dropshipping roadmap for beginners, intermediate and advanced sellers. Follow the exact steps to build a profitable store.';

    include __DIR__ . '/header.php';
    ?>

    <style>
        .roadmap-wrap {
            max-width: 900px;
            margin: 0 auto;
            padding: 48px 24px 80px;
        }

        /* Hero */
        .roadmap-hero {
            text-align: center;
            margin-bottom: 48px;
        }

        .roadmap-hero .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent-light);
            color: var(--accent);
            font-size: 12px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 20px;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .roadmap-hero h1 {
            font-size: clamp(28px, 4vw, 46px);
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -0.025em;
            margin-bottom: 12px;
            line-height: 1.15;
        }

        .roadmap-hero h1 span {
            color: var(--accent);
        }

        .roadmap-hero p {
            font-size: 16px;
            color: var(--muted);
            max-width: 520px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* Level Tabs */
        .level-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 40px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .level-tab {
            padding: 10px 24px;
            border-radius: 99px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: 2px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            transition: all 0.2s;
            font-family: var(--font);
        }

        .level-tab:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .level-tab.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .level-tab.active.beginner {
            background: #10b981;
            border-color: #10b981;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
        }

        .level-tab.active.intermediate {
            background: #f59e0b;
            border-color: #f59e0b;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
        }

        .level-tab.active.advanced {
            background: #6366f1;
            border-color: #6366f1;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        /* Roadmap Content */
        .roadmap-level {
            display: none;
        }

        .roadmap-level.active {
            display: block;
        }

        /* Level Header */
        .level-header {
            background: var(--dark);
            border-radius: var(--radius);
            padding: 28px 32px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            overflow: hidden;
        }

        .level-header::before {
            content: '';
            position: absolute;
            top: -30px;
            right: -30px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            opacity: 0.1;
        }

        .beginner .level-header::before {
            background: #10b981;
        }

        .intermediate .level-header::before {
            background: #f59e0b;
        }

        .advanced .level-header::before {
            background: #6366f1;
        }

        .level-icon {
            font-size: 48px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .level-header-info {
            position: relative;
            z-index: 1;
        }

        .level-header-title {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }

        .level-header-sub {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.6;
        }

        .level-header-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 8px;
        }

        .beginner .level-header-badge {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .intermediate .level-header-badge {
            background: rgba(245, 158, 11, 0.2);
            color: #fcd34d;
        }

        .advanced .level-header-badge {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
        }

        /* Phase */
        .phase {
            margin-bottom: 36px;
        }

        .phase-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 16px;
        }

        .phase-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
        }

        .beginner .phase-number {
            background: #10b981;
        }

        .intermediate .phase-number {
            background: #f59e0b;
        }

        .advanced .phase-number {
            background: #6366f1;
        }

        .phase-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--ink);
        }

        .phase-time {
            font-size: 12px;
            color: var(--muted);
            font-weight: 500;
            margin-top: 2px;
        }

        /* Steps */
        .steps-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-left: 50px;
        }

        .step-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            transition: all 0.15s;
            position: relative;
        }

        .step-card:hover {
            border-color: #c7d2fe;
            box-shadow: var(--shadow-sm);
            transform: translateX(4px);
        }

        .step-check {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
            transition: all 0.15s;
            margin-top: 1px;
        }

        .step-check.done {
            border-color: var(--green);
            background: var(--green);
            color: #fff;
        }

        .step-check.done::after {
            content: '✓';
            font-size: 13px;
            font-weight: 700;
        }

        .step-body {
            flex: 1;
        }

        .step-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 4px;
        }

        .step-desc {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
        }

        .step-tool {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--accent-light);
            color: var(--accent);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 5px;
            margin-top: 8px;
            text-decoration: none;
            transition: background 0.15s;
        }

        .step-tool:hover {
            background: #c7d2fe;
        }

        .step-free {
            background: #dcfce7;
            color: #15803d;
        }

        /* Connector line */
        .phase-connector {
            width: 2px;
            height: 24px;
            margin-left: 67px;
            background: var(--border);
            border-radius: 2px;
            margin-bottom: 4px;
        }

        /* Progress */
        .progress-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 24px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .progress-info {
            flex: 1;
        }

        .progress-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .progress-bar-wrap {
            background: var(--border);
            border-radius: 99px;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.3s ease;
        }

        .beginner .progress-bar-fill {
            background: linear-gradient(90deg, #10b981, #34d399);
        }

        .intermediate .progress-bar-fill {
            background: linear-gradient(90deg, #f59e0b, #fcd34d);
        }

        .advanced .progress-bar-fill {
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
        }

        .progress-pct {
            font-size: 22px;
            font-weight: 800;
            color: var(--ink);
            flex-shrink: 0;
        }

        /* CTA Box */
        .roadmap-cta {
            background: var(--dark);
            border-radius: var(--radius);
            padding: 32px;
            text-align: center;
            margin-top: 40px;
            position: relative;
            overflow: hidden;
        }

        .roadmap-cta::before {
            content: '';
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 300px;
            height: 200px;
            background: radial-gradient(ellipse, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
        }

        .roadmap-cta h3 {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .roadmap-cta p {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .roadmap-cta-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .cta-btn-primary {
            background: var(--accent);
            color: #fff;
            padding: 11px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 700;
            transition: background 0.15s;
        }

        .cta-btn-primary:hover {
            background: var(--accent-h);
        }

        .cta-btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            padding: 11px 24px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: background 0.15s;
        }

        .cta-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
        }
    </style>

    <div class="roadmap-wrap">

        <div class="roadmap-hero">
            <div class="badge">🗺️ Free Roadmap</div>
            <h1>The <span>Dropshipping Roadmap</span><br>That Actually Works</h1>
            <p>Follow the exact steps based on your level — from zero to your first sale, or from existing store to scaling past $10k/month.</p>
        </div>

        <!-- Tabs -->
        <div class="level-tabs">
            <button class="level-tab beginner active" onclick="showLevel('beginner')">🌱 Beginner</button>
            <button class="level-tab intermediate" onclick="showLevel('intermediate')">📈 Intermediate</button>
            <button class="level-tab advanced" onclick="showLevel('advanced')">🚀 Advanced</button>
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- BEGINNER -->
        <!-- ═══════════════════════════════════════ -->
        <div class="roadmap-level beginner active" id="level-beginner">

            <div class="level-header">
                <div class="level-icon">🌱</div>
                <div class="level-header-info">
                    <div class="level-header-title">Beginner Roadmap</div>
                    <div class="level-header-sub">You're starting from zero. Follow these steps in order to make your first sale within 30–60 days.</div>
                    <div class="level-header-badge">Estimated time to first sale: 30–60 days</div>
                </div>
            </div>

            <div class="progress-section beginner">
                <div class="progress-info">
                    <div class="progress-label">Your Progress — <span id="beginner-done">0</span> of <span id="beginner-total">0</span> steps completed</div>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" id="beginner-bar" style="width:0%"></div>
                    </div>
                </div>
                <div class="progress-pct" id="beginner-pct">0%</div>
            </div>

            <!-- Phase 1 -->
            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">1</div>
                    <div>
                        <div class="phase-title">🧠 Learn the Basics</div>
                        <div class="phase-time">Week 1 — Foundation</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Understand how dropshipping works</div>
                            <div class="step-desc">Learn the model: you sell, supplier ships. No inventory needed. Watch 2–3 YouTube videos to get the concept clear before spending any money.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Choose your niche</div>
                            <div class="step-desc">Pick a specific niche — not "general store". Think: pet accessories, home gym, baby products, outdoor gear. Niche stores convert better.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Research your competition</div>
                            <div class="step-desc">Search your niche on Facebook and TikTok. Look at what ads are already running. If competitors exist, that means there's money in the niche.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Validate your niche with free tools</div>
                            <div class="step-desc">Use Google Trends to check search interest. Check TikTok hashtags for your niche. Make sure people are actively searching for it.</div><a href="http://localhost/dropshipping/go.php?slug=minea" class="step-tool step-free">🔍 Try Minea Free</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <!-- Phase 2 -->
            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">2</div>
                    <div>
                        <div class="phase-title">🏪 Build Your Store</div>
                        <div class="phase-time">Week 2 — Setup</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Choose your platform</div>
                            <div class="step-desc">Shopify is the best for beginners — easy to use, great apps, and designed for dropshipping. WooCommerce is free if budget is tight.</div><a href="http://localhost/dropshipping/go.php?slug=shopify" class="step-tool">🏪 Start Shopify Free Trial</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Pick a clean theme</div>
                            <div class="step-desc">Use Shopify's free "Dawn" theme — it's fast and converts well. Don't spend money on themes at this stage.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Set up essential pages</div>
                            <div class="step-desc">Create: Home, Product pages, About Us, Contact, Shipping Policy, Return Policy. Customers check these before buying.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Connect a payment provider</div>
                            <div class="step-desc">Enable Shopify Payments or PayPal. Make sure you can actually receive money before driving traffic.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <!-- Phase 3 -->
            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">3</div>
                    <div>
                        <div class="phase-title">📦 Find Products & Suppliers</div>
                        <div class="phase-time">Week 2–3 — Products</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Find your first winning product</div>
                            <div class="step-desc">Look for: solving a problem, hard to find in stores, good margin (sell for 3x cost), visually demonstrable (good for video ads).</div><a href="http://localhost/dropshipping/go.php?slug=sell-the-trend" class="step-tool">🔍 Find Products on Sell The Trend</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Connect a supplier</div>
                            <div class="step-desc">DSers (free) for AliExpress products. Zendrop for US-based faster shipping. Start with DSers to keep costs low.</div><a href="http://localhost/dropshipping/go.php?slug=zendrop" class="step-tool step-free">📦 Try Zendrop Free</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Order a sample first</div>
                            <div class="step-desc">Order your product before selling it. Check quality, packaging, and shipping time. Don't sell what you haven't seen.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Write great product descriptions</div>
                            <div class="step-desc">Focus on benefits, not features. Use ChatGPT to help. Add size charts, FAQs, and real photos. This directly affects conversion rate.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <!-- Phase 4 -->
            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">4</div>
                    <div>
                        <div class="phase-title">📢 Get Your First Sales</div>
                        <div class="phase-time">Week 3–4 — Traffic</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Create a TikTok organic strategy (free)</div>
                            <div class="step-desc">Post 3 videos per day showing your product. Film yourself unboxing it, showing results, solving the problem. TikTok can go viral with zero ad spend.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Set up Facebook/Instagram ads (paid)</div>
                            <div class="step-desc">Start with $10–20/day. Test 3–5 different creatives. Use video ads — they outperform images for dropshipping.</div><a href="http://localhost/dropshipping/go.php?slug=adcreative-ai" class="step-tool">🎨 Create Ads with AdCreative.ai</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Add live chat to recover visitors</div>
                            <div class="step-desc">Install Tidio — free plan is enough. Answer questions in real time. Converts hesitant visitors into buyers.</div><a href="http://localhost/dropshipping/go.php?slug=tidio" class="step-tool step-free">💬 Add Tidio Free</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">🎉 Celebrate your first sale!</div>
                            <div class="step-desc">When it comes in, fulfill the order immediately. Message the customer. Ask for a review. Then scale what's working.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- INTERMEDIATE -->
        <!-- ═══════════════════════════════════════ -->
        <div class="roadmap-level intermediate" id="level-intermediate">

            <div class="level-header">
                <div class="level-icon">📈</div>
                <div class="level-header-info">
                    <div class="level-header-title">Intermediate Roadmap</div>
                    <div class="level-header-sub">You've made some sales. Now it's time to build systems, optimize your store, and hit consistent $5k–$20k months.</div>
                    <div class="level-header-badge">Target: $5,000–$20,000/month in 60–90 days</div>
                </div>
            </div>

            <div class="progress-section intermediate">
                <div class="progress-info">
                    <div class="progress-label">Your Progress — <span id="intermediate-done">0</span> of <span id="intermediate-total">0</span> steps completed</div>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" id="intermediate-bar" style="width:0%"></div>
                    </div>
                </div>
                <div class="progress-pct" id="intermediate-pct">0%</div>
            </div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">1</div>
                    <div>
                        <div class="phase-title">📊 Understand Your Numbers</div>
                        <div class="phase-time">Week 1 — Analytics</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Track your real profit (not just revenue)</div>
                            <div class="step-desc">Revenue means nothing without knowing your COGS, ad spend, app costs, and refunds. Know your actual margin per order.</div><a href="http://localhost/dropshipping/go.php?slug=triple-whale" class="step-tool">📊 Track Profit with Triple Whale</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Identify your best performing ads</div>
                            <div class="step-desc">Look at which creatives have the best CTR and ROAS. Kill anything below 1.5x ROAS. Double budget on winners.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Find your best traffic source</div>
                            <div class="step-desc">Facebook, TikTok, Google Shopping, or organic? Double down on what's already working instead of spreading thin.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Install Hotjar or Microsoft Clarity (free)</div>
                            <div class="step-desc">Watch session recordings to see where visitors drop off. Fix those friction points and your conversion rate will improve.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">2</div>
                    <div>
                        <div class="phase-title">🏪 Upgrade Your Store</div>
                        <div class="phase-time">Week 2 — Conversion Optimization</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Add social proof — photo reviews</div>
                            <div class="step-desc">Install Loox or Judge.me. Import reviews from AliExpress or collect real ones. Stores with reviews convert 15–40% better.</div><a href="http://localhost/dropshipping/go.php?slug=loox" class="step-tool">⭐ Add Reviews with Loox</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Add upsells and cross-sells</div>
                            <div class="step-desc">Use "Frequently Bought Together" or post-purchase upsells. Increasing AOV by 20% doubles your profitability without new customers.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Optimize your product page</div>
                            <div class="step-desc">Add: video demo, trust badges, money-back guarantee, FAQ section, scarcity timer. Each element increases conversion rate.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Speed up your store</div>
                            <div class="step-desc">Compress images with TinyPNG. Remove unused apps. Slow stores lose customers — aim for under 3 seconds load time.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">3</div>
                    <div>
                        <div class="phase-title">📧 Build Your Email Engine</div>
                        <div class="phase-time">Week 3 — Retention</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Set up Klaviyo email flows</div>
                            <div class="step-desc">Build: Welcome series, Abandoned Cart (3 emails), Post-Purchase, Win-Back. These 4 flows alone can add 20–30% revenue.</div><a href="http://localhost/dropshipping/go.php?slug=klaviyo" class="step-tool step-free">📧 Start Klaviyo Free</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Add a pop-up to capture emails</div>
                            <div class="step-desc">Offer 10% off in exchange for an email. Use Klaviyo's built-in forms. Email list = money you own forever.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Send weekly campaigns</div>
                            <div class="step-desc">Promote new products, sales, and content to your list. Email is the highest ROI marketing channel at $36 return per $1 spent.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Segment your list</div>
                            <div class="step-desc">Separate buyers from non-buyers. Send different emails to VIP customers (spent $200+) vs new subscribers. Segmentation = higher open rates.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">4</div>
                    <div>
                        <div class="phase-title">📦 Upgrade Your Supply Chain</div>
                        <div class="phase-time">Week 3–4 — Operations</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Switch to faster suppliers</div>
                            <div class="step-desc">Move from AliExpress (15–30 days) to Zendrop or Spocket (3–7 days). Faster shipping = fewer refund requests and better reviews.</div><a href="http://localhost/dropshipping/go.php?slug=spocket" class="step-tool step-free">📦 Try Spocket Free</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Automate order fulfillment</div>
                            <div class="step-desc">Use AutoDS to auto-fulfill orders the moment they come in. No more manual ordering — saves hours every day.</div><a href="http://localhost/dropshipping/go.php?slug=autods" class="step-tool">⚙️ Automate with AutoDS</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Set up customer support properly</div>
                            <div class="step-desc">Install Tidio or Gorgias. Create templated replies for common questions. Fast support = better reviews and fewer chargebacks.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- ADVANCED -->
        <!-- ═══════════════════════════════════════ -->
        <div class="roadmap-level advanced" id="level-advanced">

            <div class="level-header">
                <div class="level-icon">🚀</div>
                <div class="level-header-info">
                    <div class="level-header-title">Advanced Roadmap</div>
                    <div class="level-header-sub">You're doing $10k+/month. Now it's about scaling profitably, building a real brand, and creating systems that run without you.</div>
                    <div class="level-header-badge">Target: $50,000–$100,000+/month</div>
                </div>
            </div>

            <div class="progress-section advanced">
                <div class="progress-info">
                    <div class="progress-label">Your Progress — <span id="advanced-done">0</span> of <span id="advanced-total">0</span> steps completed</div>
                    <div class="progress-bar-wrap">
                        <div class="progress-bar-fill" id="advanced-bar" style="width:0%"></div>
                    </div>
                </div>
                <div class="progress-pct" id="advanced-pct">0%</div>
            </div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">1</div>
                    <div>
                        <div class="phase-title">🎯 Master Paid Advertising</div>
                        <div class="phase-time">Month 1 — Scale Ad Spend</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Fix your attribution before scaling</div>
                            <div class="step-desc">At scale, Facebook pixel data is unreliable. Install Triple Whale or Northbeam for accurate ROAS across all channels before spending more.</div><a href="http://localhost/dropshipping/go.php?slug=northbeam" class="step-tool">📈 Get Northbeam Attribution</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Scale winning ad sets systematically</div>
                            <div class="step-desc">Increase budget by 20% every 48–72 hours on winning ad sets. Never double overnight — it resets the algorithm learning phase.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Spy on competitors at scale</div>
                            <div class="step-desc">Use AdSpy to monitor exactly which ads your competitors are running and how long they've been active. Long-running ads = profitable ads.</div><a href="http://localhost/dropshipping/go.php?slug=adspy" class="step-tool">🕵️ Spy with AdSpy</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Launch on Google Shopping</div>
                            <div class="step-desc">Facebook + Google together captures the full buyer journey. Google Shopping converts people who are already ready to buy.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Produce ads at scale with AI</div>
                            <div class="step-desc">Use AdCreative.ai to generate 50+ ad variations fast. Test more creatives = find winners faster = scale faster.</div><a href="http://localhost/dropshipping/go.php?slug=adcreative-ai" class="step-tool">🤖 Scale Ads with AdCreative.ai</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">2</div>
                    <div>
                        <div class="phase-title">🏷️ Build a Real Brand</div>
                        <div class="phase-time">Month 1–2 — Brand Building</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Add private label packaging</div>
                            <div class="step-desc">Get your logo on the packaging. Spocket offers branded invoicing. This reduces chargebacks and builds repeat customers.</div><a href="http://localhost/dropshipping/go.php?slug=spocket" class="step-tool">📦 Private Label with Spocket</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Build a real social media presence</div>
                            <div class="step-desc">Post consistently on TikTok and Instagram. User-generated content and influencer reposts are free traffic that compounds over time.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Start a loyalty/rewards program</div>
                            <div class="step-desc">Install Smile.io or LoyaltyLion. Repeat customers cost 5x less to convert than new ones. Reward them to keep them coming back.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Collect and showcase video reviews</div>
                            <div class="step-desc">Reach out to customers with a discount in exchange for a video testimonial. Video UGC is the most powerful social proof you can have.</div><a href="http://localhost/dropshipping/go.php?slug=loox" class="step-tool">⭐ Collect Video Reviews with Loox</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">3</div>
                    <div>
                        <div class="phase-title">⚙️ Build Systems & Hire</div>
                        <div class="phase-time">Month 2–3 — Operations</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Hire a customer support VA</div>
                            <div class="step-desc">At $50k+/month, support becomes a full-time job. Hire someone on Upwork for $5–10/hr and give them Gorgias access. Free your time for growth.</div><a href="http://localhost/dropshipping/go.php?slug=gorgias" class="step-tool">🎧 Set Up Gorgias for Your VA</a>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Hire a media buyer</div>
                            <div class="step-desc">A good media buyer pays for themselves 3–5x over. Find one with proven dropshipping experience on Upwork or Twitter/X.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Document all your SOPs</div>
                            <div class="step-desc">Write down every process in your business. This makes hiring easier and lets the business run without you being there every day.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Set up proper accounting</div>
                            <div class="step-desc">At this revenue, get a real accountant or use QuickBooks. Know your P&L monthly. Scaling without financial clarity is dangerous.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="phase-connector"></div>

            <div class="phase">
                <div class="phase-header">
                    <div class="phase-number">4</div>
                    <div>
                        <div class="phase-title">💎 Exit or Multiply</div>
                        <div class="phase-time">Month 3+ — Long Term</div>
                    </div>
                </div>
                <div class="steps-list">
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Launch a second store</div>
                            <div class="step-desc">Apply what worked in your first store to a new niche. Your second store will grow faster because you already know what to do.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Transition to wholesale / private label</div>
                            <div class="step-desc">Order larger quantities directly from manufacturers. Your COGS drops 40–60% and your margins explode.</div>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-check" onclick="toggleStep(this)"></div>
                        <div class="step-body">
                            <div class="step-title">Consider selling your store</div>
                            <div class="step-desc">Profitable Shopify stores sell for 3–5x annual profit on Empire Flippers or Acquire.com. A $10k/month profit store is worth $360k–$600k.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA -->
        <div class="roadmap-cta">
            <h3>Ready to Start? Get the Right Tools</h3>
            <p>Use our free tools to find the perfect stack for your level and budget.</p>
            <div class="roadmap-cta-btns">
                <a href="http://localhost/dropshipping/stack-builder.php" class="cta-btn-primary">🎯 Build My Stack</a>
                <a href="http://localhost/dropshipping/calculator.php" class="cta-btn-secondary">💰 Calculate My Costs</a>
                <a href="http://localhost/dropshipping/compare.php" class="cta-btn-secondary">⚖️ Compare Tools</a>
            </div>
        </div>

    </div>

    <script>
        const savedRoadmap = <?= json_encode([
                                    'beginner' => json_decode($roadmap['beginner_steps'] ?? '[]', true),
                                    'intermediate' => json_decode($roadmap['intermediate_steps'] ?? '[]', true),
                                    'advanced' => json_decode($roadmap['advanced_steps'] ?? '[]', true)
                                ]) ?>;

        function showLevel(level) {
            document.querySelectorAll('.roadmap-level')
                .forEach(l => l.classList.remove('active'));

            document.querySelectorAll('.level-tab')
                .forEach(t => t.classList.remove('active'));

            document
                .getElementById('level-' + level)
                .classList.add('active');

            document
                .querySelector('.level-tab.' + level)
                .classList.add('active');
        }

        function toggleStep(el) {
            el.classList.toggle('done');

            updateProgress();

            saveRoadmap();
        }

        function updateProgress() {

            ['beginner', 'intermediate', 'advanced']
            .forEach(level => {

                const container =
                    document.getElementById(
                        'level-' + level
                    );

                const all =
                    container.querySelectorAll(
                        '.step-check'
                    );

                const done =
                    container.querySelectorAll(
                        '.step-check.done'
                    );

                const pct = all.length ?
                    Math.round(
                        (done.length / all.length) * 100
                    ) :
                    0;

                document.getElementById(
                    level + '-done'
                ).textContent = done.length;

                document.getElementById(
                    level + '-total'
                ).textContent = all.length;

                document.getElementById(
                    level + '-pct'
                ).textContent = pct + '%';

                document.getElementById(
                    level + '-bar'
                ).style.width = pct + '%';
            });
        }

        async function saveRoadmap() {

            const data = {};

            ['beginner', 'intermediate', 'advanced']
            .forEach(level => {

                data[level] = [];

                document
                    .querySelectorAll(
                        '#level-' + level + ' .step-check'
                    )
                    .forEach(step => {

                        data[level].push(
                            step.classList.contains(
                                'done'
                            )
                        );

                    });

            });

            await fetch(
                '/dropshipping/save-roadmap.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                }
            );
        }

        document.addEventListener(
            'DOMContentLoaded',
            () => {

                ['beginner', 'intermediate', 'advanced']
                .forEach(level => {

                    const checks =
                        document.querySelectorAll(
                            '#level-' + level +
                            ' .step-check'
                        );

                    (savedRoadmap[level] || [])
                    .forEach((done, index) => {

                        if (done && checks[index]) {

                            checks[index]
                                .classList.add('done');

                        }

                    });

                });

                updateProgress();

            });
    </script>
    <script src="/assets/js/darkmode.js"></script>
    <div class="step-check" onclick="toggleStep(this)"></div>
    <?php include __DIR__ . '/footer.php'; ?>