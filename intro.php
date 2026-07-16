<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

setcookie('seen_intro', '1', time() + 60 * 60 * 24 * 365, '/');

$page_title = 'Welcome';
$page_desc  = SITE_TAGLINE;

$page_title = 'Welcome';
$page_desc  = SITE_TAGLINE;

$stats = $pdo->query("SELECT COUNT(*) FROM tools WHERE is_active=1")->fetchColumn();
$cats_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> — <?= e(SITE_NAME) ?></title>
    <meta name="description" content="<?= e($page_desc) ?>">
    <link rel="stylesheet" href="/dropshipping/assets/css/style.css">
    <link href="/dropshipping/assets/css/darkmode.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .intro-page {
            min-height: 100vh;
            background: var(--dark);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        .intro-page::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 15%, rgba(124,152,133,0.22), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(107,147,160,0.18), transparent 50%);
            pointer-events: none;
        }

        .intro-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 28px 40px;
            position: relative;
            z-index: 1;
        }
        .intro-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: var(--display);
            font-weight: 600;
            font-size: 20px;
            color: #fff;
        }
        .intro-skip {
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 600;
            color: rgba(246,244,238,0.45);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            transition: color 0.15s;
        }
        .intro-skip:hover { color: #fff; }

        .intro-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 24px 60px;
            position: relative;
            z-index: 1;
        }
        .intro-content {
            max-width: 640px;
            text-align: center;
            animation: introUp 0.7s cubic-bezier(0.4,0,0.2,1) both;
        }
        @keyframes introUp {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .intro-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(231,194,122,0.08);
            border: 1px solid var(--amber);
            color: var(--amber);
            font-family: var(--mono);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 999px;
            margin-bottom: 28px;
        }

        .intro-content h1 {
            font-family: var(--display);
            font-size: clamp(36px, 6vw, 58px);
            font-weight: 600;
            line-height: 1.1;
            letter-spacing: -0.01em;
            margin-bottom: 20px;
        }
        .intro-content h1 span { color: var(--amber); font-style: italic; }

        .intro-content p {
            font-size: 17px;
            color: rgba(246,244,238,0.6);
            line-height: 1.75;
            max-width: 480px;
            margin: 0 auto 36px;
        }

        .intro-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 40px;
        }
        .intro-feature {
            background: rgba(246,244,238,0.04);
            border: 1px solid rgba(246,244,238,0.1);
            border-radius: var(--radius);
            padding: 18px 14px;
            text-align: left;
        }
        .intro-feature-icon { font-size: 20px; margin-bottom: 8px; }
        .intro-feature-title { font-size: 13px; font-weight: 700; margin-bottom: 3px; }
        .intro-feature-desc { font-size: 12px; color: rgba(246,244,238,0.45); line-height: 1.5; }

        .intro-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--amber);
            color: var(--ink);
            padding: 15px 32px;
            border-radius: var(--radius-sm);
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: var(--shadow-sm);
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .intro-cta:hover { transform: translateY(-2px); box-shadow: var(--shadow); }

        .intro-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 44px;
        }
        .intro-stat-val {
            font-family: var(--mono);
            font-size: 24px;
            font-weight: 500;
            color: var(--amber);
            display: block;
        }
        .intro-stat-label {
            font-size: 11px;
            color: rgba(246,244,238,0.4);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-top: 4px;
        }

        @media (max-width: 640px) {
            .intro-features { grid-template-columns: 1fr; }
            .intro-top { padding: 20px; }
            .intro-stats { gap: 24px; }
        }
    </style>
</head>
<body>

<div class="intro-page">
    <div class="intro-top">
        <div class="intro-logo">
            <span>⚡</span> <?= SITE_NAME ?>
        </div>
        <a href="/dropshipping/index.php" class="intro-skip">Skip intro →</a>
    </div>

    <div class="intro-main">
        <div class="intro-content">
            <div class="intro-badge">
                <span style="width:6px;height:6px;background:var(--amber);border-radius:50%;display:inline-block"></span>
                New — <?= (int)$stats ?>+ tools reviewed
            </div>

            <h1>Stop guessing which<br><span>dropshipping tools</span> are worth it</h1>

            <p><?= e(SITE_TAGLINE) ?> — real ratings, honest comparisons, and a free calculator to see exactly what each tool costs vs what it earns you.</p>

            <div class="intro-features">
                <div class="intro-feature">
                    <div class="intro-feature-icon">🔍</div>
                    <div class="intro-feature-title">Compare tools</div>
                    <div class="intro-feature-desc">Side-by-side pricing, ratings, and pros/cons</div>
                </div>
                <div class="intro-feature">
                    <div class="intro-feature-icon">🎯</div>
                    <div class="intro-feature-title">Build your stack</div>
                    <div class="intro-feature-desc">Get a personalized tool recommendation in 3 questions</div>
                </div>
                <div class="intro-feature">
                    <div class="intro-feature-icon">💰</div>
                    <div class="intro-feature-title">Calculate real profit</div>
                    <div class="intro-feature-desc">See what tools actually cost vs what you'll earn</div>
                </div>
            </div>

            <a href="/dropshipping/index.php" class="intro-cta">
                Explore the tools →
            </a>

            <div class="intro-stats">
                <div>
                    <span class="intro-stat-val"><?= (int)$stats ?>+</span>
                    <div class="intro-stat-label">Tools reviewed</div>
                </div>
                <div>
                    <span class="intro-stat-val"><?= (int)$cats_count ?></span>
                    <div class="intro-stat-label">Categories</div>
                </div>
                <div>
                    <span class="intro-stat-val">Free</span>
                    <div class="intro-stat-label">Always</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/dropshipping/assets/js/darkmode.js"></script>
</body>
</html>