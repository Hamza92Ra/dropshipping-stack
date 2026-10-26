<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$page_title = 'Submit a Tool';
$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $flash = '<div class="profile-alert profile-alert-error">' . icon('alert-circle', '', 16) . ' Security check failed. Please try again.</div>';
    } else {
        $name    = trim($_POST['tool_name'] ?? '');
        $url     = trim($_POST['website_url'] ?? '');
        $desc    = trim($_POST['description'] ?? '');
        $email   = trim($_POST['contact_email'] ?? '');
        $plan    = in_array($_POST['plan'] ?? '', ['free','featured']) ? $_POST['plan'] : 'free';

        if ($name && $url && filter_var($url, FILTER_VALIDATE_URL)) {
            $ins = $pdo->prepare("INSERT INTO submissions (tool_name, website_url, description, contact_email, plan) VALUES (?,?,?,?,?)");
            $ins->execute([$name, $url, $desc, $email, $plan]);
            $flash = '<div class="profile-alert profile-alert-success">' . icon('check-circle', '', 16) . ' Thanks! Your submission has been received. We\'ll review it within 48 hours.</div>';
        } else {
            $flash = '<div class="profile-alert profile-alert-error">' . icon('alert-circle', '', 16) . ' Please provide a valid tool name and website URL.</div>';
        }
    }
}

include __DIR__ . '/header.php';
?>

<style>
    .submit-page-wrap {
        max-width: 680px;
        margin: 16px auto 60px;
        padding: 0 16px;
    }

    .submit-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-2xl);
        padding: 36px 32px;
        box-shadow: var(--shadow-sm);
    }

    .submit-header {
        margin-bottom: 24px;
    }

    .submit-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -0.02em;
        margin: 0 0 6px;
    }

    .submit-subtitle {
        font-size: 14px;
        color: var(--muted);
        line-height: 1.5;
        margin: 0;
    }

    .submit-field {
        margin-bottom: 20px;
    }

    .submit-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 8px;
    }

    .submit-input,
    .submit-textarea {
        width: 100%;
        padding: 12px 16px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        color: var(--text);
        font-size: 14px;
        font-family: var(--font);
        box-sizing: border-box;
        outline: none;
        transition: all var(--dur-fast) var(--ease);
    }

    .submit-input:focus,
    .submit-textarea:focus {
        background: var(--surface);
        border-color: var(--accent);
        box-shadow: 0 0 0 3px var(--skeleton-b);
    }

    .submit-textarea {
        min-height: 110px;
        resize: vertical;
        line-height: 1.5;
    }

    /* ── Plan selection cards ── */
    .submit-plan-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .submit-plan-option {
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 18px;
        cursor: pointer;
        position: relative;
        transition: all var(--dur-fast) var(--ease);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .submit-plan-option:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
    }

    .submit-plan-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .submit-plan-option.selected {
        border-color: var(--accent);
        background: var(--surface);
        box-shadow: 0 0 0 2px var(--accent);
    }

    .submit-plan-title {
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .submit-plan-price {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .submit-plan-badge {
        font-size: 11px;
        font-weight: 700;
        color: var(--accent);
    }

    .submit-btn-wrap {
        margin-top: 28px;
    }

    .submit-btn {
        width: 100%;
        padding: 13px 24px;
        background: var(--btn-primary-bg);
        color: var(--btn-primary-text);
        border: none;
        border-radius: var(--radius-full);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all var(--dur-fast) var(--ease);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: var(--shadow-sm);
    }

    .submit-btn:hover {
        background: var(--btn-primary-hover);
        transform: translateY(-1px);
        box-shadow: var(--shadow);
    }

    @media (max-width: 580px) {
        .submit-plan-grid {
            grid-template-columns: 1fr;
        }

        .submit-card {
            padding: 24px 20px;
        }
    }
</style>

<div class="submit-page-wrap">
    <div class="submit-card">
        <div class="submit-header">
            <h1 class="submit-title">Submit a Tool</h1>
            <p class="submit-subtitle">Know a great dropshipping tool we haven't listed yet? Submit it below for review by our editorial team within 48 hours.</p>
        </div>

        <?= $flash ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="submit-field">
                <label>Tool Name *</label>
                <input type="text" name="tool_name" class="submit-input" placeholder="e.g. Shopify, Zendrop, AutoDS" required maxlength="100">
            </div>

            <div class="submit-field">
                <label>Website URL *</label>
                <input type="url" name="website_url" class="submit-input" placeholder="https://example.com" required>
            </div>

            <div class="submit-field">
                <label>Short Description</label>
                <textarea name="description" class="submit-textarea" placeholder="What does this tool do? Key features, pricing model, and who it helps..."></textarea>
            </div>

            <div class="submit-field">
                <label>Your Email (optional)</label>
                <input type="email" name="contact_email" class="submit-input" placeholder="you@example.com">
            </div>

            <div class="submit-field">
                <label>Listing Tier</label>
                <div class="submit-plan-grid">
                    <label class="submit-plan-option selected" id="plan-free-label">
                        <input type="radio" name="plan" value="free" checked onchange="updatePlanSelection()">
                        <div>
                            <div class="submit-plan-title">Standard Review</div>
                            <div class="submit-plan-price">$0 · Free review</div>
                        </div>
                        <div class="submit-plan-badge">Organic placement</div>
                    </label>
                    <label class="submit-plan-option" id="plan-featured-label">
                        <input type="radio" name="plan" value="featured" onchange="updatePlanSelection()">
                        <div>
                            <div class="submit-plan-title"><?= icon('sparkles', '', 15) ?> Featured Listing</div>
                            <div class="submit-plan-price">Priority curation</div>
                        </div>
                        <div class="submit-plan-badge">Featured tag & top ranking</div>
                    </label>
                </div>
            </div>

            <div class="submit-btn-wrap">
                <button type="submit" class="submit-btn">
                    Submit Tool <?= icon('arrow-right', '', 15) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updatePlanSelection() {
        document.querySelectorAll('.submit-plan-option').forEach(el => {
            const input = el.querySelector('input[type="radio"]');
            if (input && input.checked) {
                el.classList.add('selected');
            } else {
                el.classList.remove('selected');
            }
        });
    }
</script>

<?php include __DIR__ . '/footer.php'; ?>
