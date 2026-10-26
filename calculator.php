<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$page_title      = 'Dropshipping Cost & Profit Calculator';
$page_heading    = 'Profit & Cost Calculator';
$page_subheading = 'Model your store revenue, ad spend, and SaaS tool expenses with real margins';
$page_desc       = 'Enter your monthly revenue and see exactly how much each dropshipping tool costs vs how much you will probably make.';

// Load saved settings if logged in
$saved = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $saved = $stmt->fetch();
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings']) && isset($_SESSION['user_id'])) {
    $revenue = (int)($_POST['revenue'] ?? 5000);
    $margin  = (int)($_POST['margin'] ?? 25);
    $adspend = (int)($_POST['adspend'] ?? 500);
    $tools   = $_POST['tools'] ?? '';

    $check = $pdo->prepare("SELECT id FROM user_settings WHERE user_id = ?");
    $check->execute([$_SESSION['user_id']]);

    if ($check->fetch()) {
        $pdo->prepare("UPDATE user_settings SET calculator_revenue=?, calculator_margin=?, calculator_adspend=?, calculator_tools=?, updated_at=NOW() WHERE user_id=?")
            ->execute([$revenue, $margin, $adspend, $tools, $_SESSION['user_id']]);
    } else {
        $pdo->prepare("INSERT INTO user_settings (user_id, calculator_revenue, calculator_margin, calculator_adspend, calculator_tools) VALUES (?,?,?,?,?)")
            ->execute([$_SESSION['user_id'], $revenue, $margin, $adspend, $tools]);
    }
    $saved = ['calculator_revenue' => $revenue, 'calculator_margin' => $margin, 'calculator_adspend' => $adspend, 'calculator_tools' => $tools];
}

$def_revenue = $saved['calculator_revenue'] ?? 5000;
$def_margin  = $saved['calculator_margin']  ?? 25;
$def_adspend = $saved['calculator_adspend'] ?? 500;
$def_tools   = $saved ? explode(',', $saved['calculator_tools'] ?? '') : ['shopify','klaviyo','minea'];

include __DIR__ . '/header.php';
?>

<style>
.calc-wrap {
    max-width: 1080px;
    margin: 0 auto;
    padding: 10px 0 60px;
}

.calc-hero {
    text-align: center;
    margin-bottom: 36px;
}

.calc-badge {
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

.calc-hero h1 {
    font-size: clamp(26px, 4vw, 42px);
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.025em;
    margin-bottom: 10px;
    line-height: 1.15;
}

.calc-hero h1 span {
    color: var(--accent);
}

.calc-hero p {
    font-size: 15px;
    color: var(--muted);
    max-width: 520px;
    margin: 0 auto;
    line-height: 1.65;
}

.calc-grid {
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 28px;
    align-items: start;
}

/* Inputs Card */
.calc-inputs {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 28px;
    box-shadow: var(--shadow-sm);
    position: sticky;
    top: 90px;
}

.calc-inputs h3 {
    font-size: 16px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 20px;
}

.input-group {
    margin-bottom: 20px;
}

.input-group label {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 8px;
}

.input-with-prefix {
    display: flex;
    align-items: center;
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    background: var(--surface-2);
    transition: border-color var(--dur-fast) var(--ease);
}

.input-with-prefix:focus-within {
    border-color: var(--accent);
    background: var(--surface);
    box-shadow: 0 0 0 3px var(--accent-light);
}

.input-prefix {
    padding: 10px 14px;
    background: transparent;
    font-size: 14px;
    font-weight: 700;
    color: var(--muted);
    flex-shrink: 0;
}

.input-suffix {
    padding: 10px 14px;
    background: transparent;
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    flex-shrink: 0;
    white-space: nowrap;
}

.input-with-prefix input {
    border: none;
    background: none;
    padding: 10px 12px;
    font-size: 15px;
    font-weight: 700;
    color: var(--text);
    width: 100%;
    outline: none;
}

.range-input {
    width: 100%;
    accent-color: var(--accent);
    margin-top: 8px;
    cursor: pointer;
}

.input-val {
    font-size: 13px;
    font-weight: 700;
    color: var(--accent);
}

/* Tool Checklist */
.tool-toggles {
    margin-top: 24px;
    border-top: 1px solid var(--border-subtle);
    padding-top: 20px;
}

.tool-toggles h4 {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 14px;
}

.tool-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-subtle);
}

.tool-toggle:last-child {
    border-bottom: none;
}

.tool-toggle-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.tool-toggle-icon {
    color: var(--accent);
    display: flex;
    align-items: center;
}

.tool-toggle-name {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text);
}

.tool-toggle-price {
    font-size: 11.5px;
    color: var(--muted);
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    width: 38px;
    height: 22px;
    flex-shrink: 0;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    inset: 0;
    background: var(--border);
    border-radius: 999px;
    cursor: pointer;
    transition: background 0.2s var(--ease);
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s var(--ease);
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--accent);
}

.toggle-switch input:checked + .toggle-slider::before {
    transform: translateX(16px);
}

/* Summary Cards (Fintech StatCards format) */
.summary-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}

.calc-summary-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 22px 20px;
    box-shadow: var(--shadow-sm);
    text-align: left;
    transition: transform var(--dur) var(--ease), box-shadow var(--dur) var(--ease);
}

.calc-summary-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-hover);
}

.calc-summary-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 8px;
}

.calc-summary-val {
    font-size: clamp(22px, 3vw, 30px);
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.03em;
    line-height: 1.1;
}

/* Profit Meter */
.profit-meter {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-2xl);
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-sm);
}

.profit-meter-label {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.profit-meter-bar {
    height: 10px;
    background: var(--surface-2);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-full);
    overflow: hidden;
}

.profit-meter-fill {
    height: 100%;
    border-radius: var(--radius-full);
    transition: width 0.4s var(--ease);
}

/* Dynamic Result Cards */
.result-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-xl);
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: var(--shadow-sm);
    margin-bottom: 12px;
    transition: transform var(--dur-fast) var(--ease);
}

.result-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-hover);
}

.result-card.highlight {
    border-color: rgba(50, 99, 70, 0.25);
    background: var(--surface);
}

.result-card.danger {
    border-color: rgba(220, 38, 38, 0.25);
    background: var(--surface);
}

.result-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--surface-2);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.result-icon-box.green {
    background: var(--positive-bg);
    color: var(--positive-text);
}

.result-icon-box.amber {
    background: var(--amber-light);
    color: var(--amber-text);
}

.result-icon-box.red {
    background: var(--negative-bg);
    color: var(--negative-text);
}

.result-info {
    flex: 1;
    min-width: 0;
}

.result-name {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 2px;
}

.result-detail {
    font-size: 12.5px;
    color: var(--muted);
}

.result-numbers {
    text-align: right;
    flex-shrink: 0;
}

.result-cost {
    font-size: 14.5px;
    font-weight: 700;
    color: var(--text);
}

.result-roi {
    font-size: 12px;
    color: var(--positive-text);
    font-weight: 700;
    margin-top: 2px;
}

@media (max-width: 768px) {
    .calc-grid {
        grid-template-columns: 1fr;
    }
    .calc-inputs {
        position: static;
    }
    .summary-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="calc-wrap">

    <div class="calc-hero">
        <div class="calc-badge">
            <?= icon('calculator', '', 14) ?>
            <span>Fintech Profit Engine</span>
        </div>
        <h1>Model Your Real<br><span>Dropshipping Net Margins</span></h1>
        <p>Calculate your actual take-home profit after accounting for product costs, advertising spend, and subscription software.</p>
    </div>

    <div class="calc-grid">
        <!-- Inputs Card -->
        <div class="calc-inputs">
            <h3>Configuration Parameters</h3>

            <div class="input-group">
                <label for="revenue">Monthly Gross Revenue</label>
                <div class="input-with-prefix">
                    <span class="input-prefix">$</span>
                    <input type="number" id="revenue" value="<?= (int)$def_revenue ?>" min="0" step="100" inputmode="numeric" oninput="calculate()">
                </div>
            </div>

            <div class="input-group">
                <label for="margin">Product Gross Margin <span class="input-val" id="marginVal"><?= (int)$def_margin ?>%</span></label>
                <input type="range" class="range-input" id="margin" min="5" max="75" value="<?= (int)$def_margin ?>" oninput="document.getElementById('marginVal').textContent=this.value+'%';calculate()">
            </div>

            <div class="input-group">
                <label for="adspend">Monthly Paid Ad Spend</label>
                <div class="input-with-prefix">
                    <span class="input-prefix">$</span>
                    <input type="number" id="adspend" value="<?= (int)$def_adspend ?>" min="0" step="50" inputmode="numeric" oninput="calculate()">
                    <span class="input-suffix">/mo</span>
                </div>
            </div>

            <div class="tool-toggles">
                <h4>Active Tools in Stack</h4>
                <?php
                $calc_tools = [
                    ['shopify',      'Shopify',        'shopping-bag', 29],
                    ['klaviyo',      'Klaviyo',        'mail',         45],
                    ['minea',        'Minea',          'search',       49],
                    ['zendrop',      'Zendrop',        'package',      49],
                    ['triple-whale', 'Triple Whale',   'bar-chart-2',  129],
                    ['adspy',        'AdSpy',          'search',       149],
                    ['autods',       'AutoDS',         'settings',     27],
                    ['loox',         'Loox',           'star',         10],
                    ['tidio',        'Tidio',          'message-square', 19],
                    ['adcreative',   'AdCreative.ai',  'zap',          29],
                ];
                foreach ($calc_tools as [$slug, $name, $icon_name, $price]):
                    $checked = in_array($slug, $def_tools, true) ? 'checked' : '';
                ?>
                <div class="tool-toggle">
                    <div class="tool-toggle-left">
                        <div class="tool-toggle-icon">
                            <?= icon($icon_name, '', 16) ?>
                        </div>
                        <div>
                            <div class="tool-toggle-name"><?= e($name) ?></div>
                            <div class="tool-toggle-price">$<?= $price ?>/mo</div>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox"
                               class="tool-check"
                               data-price="<?= $price ?>"
                               data-name="<?= htmlspecialchars($name) ?>"
                               data-slug="<?= $slug ?>"
                               <?= $checked ?>
                               onchange="calculate()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top:24px">
                <?php if ($logged_in): ?>
                    <button type="button" class="btn-primary" onclick="saveSettings()" style="width:100%;padding:11px">
                        <?= icon('bookmark', '', 15) ?>
                        <span>Save to My Account</span>
                    </button>
                    <div id="saveSuccess" style="text-align:center;font-size:12px;color:var(--positive-text);margin-top:8px;font-weight:600;display:none;align-items:center;justify-content:center;gap:4px">
                        <?= icon('check-circle', '', 14) ?> Configuration saved successfully
                    </div>
                <?php else: ?>
                    <a href="/login.php" class="btn-secondary" style="display:flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:11px">
                        <?= icon('lock', '', 14) ?>
                        <span>Sign in to save presets</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Dynamic Results Container -->
        <div class="calc-results" id="calcResults"></div>
    </div>

</div>

<script>
function calculate() {
    const revenue = Math.max(parseFloat(document.getElementById('revenue').value) || 0, 0);
    const margin  = Math.max(parseFloat(document.getElementById('margin').value) || 0, 0) / 100;
    const adspend = Math.max(parseFloat(document.getElementById('adspend').value) || 0, 0);

    const grossProfit   = revenue * margin;
    const netBeforeTools = grossProfit - adspend;

    const tools = [...document.querySelectorAll('.tool-check:checked')].map(el => ({
        name:  el.dataset.name,
        slug:  el.dataset.slug,
        price: parseFloat(el.dataset.price)
    }));

    const totalToolCost = tools.reduce((s, t) => s + t.price, 0);
    const netProfit     = netBeforeTools - totalToolCost;

    const toolPct = grossProfit > 0
        ? Math.min((totalToolCost / grossProfit) * 100, 100)
        : (totalToolCost > 0 ? 100 : 0);

    const fmt = n => (n < 0 ? '-$' : '$') + Math.round(Math.abs(n)).toLocaleString();
    const isProfitable = netProfit >= 0;

    const adPct = revenue > 0 ? Math.round((adspend / revenue) * 100) : 0;
    const adPctText = revenue > 0
        ? `${adPct}% of total sales · ${adspend / revenue < 0.35 ? 'Healthy acquisition ratio' : 'High ad ratio'}`
        : 'Enter revenue to calculate percentage';

    let html = `
        <div class="summary-cards">
            <div class="calc-summary-card">
                <div class="calc-summary-label">Gross Margin</div>
                <div class="calc-summary-val">${fmt(grossProfit)}</div>
            </div>
            <div class="calc-summary-card">
                <div class="calc-summary-label">Estimated Net Profit</div>
                <div class="calc-summary-val" style="color:${isProfitable ? 'var(--positive-text)' : 'var(--negative-text)'}">
                    ${fmt(netProfit)}
                </div>
            </div>
            <div class="calc-summary-card">
                <div class="calc-summary-label">Stack Software Cost</div>
                <div class="calc-summary-val">${fmt(totalToolCost)}</div>
            </div>
        </div>

        <div class="profit-meter">
            <div class="profit-meter-label">
                <span>Tools Cost vs Gross Margin</span>
                <span style="color:${toolPct > 35 ? 'var(--negative-text)' : 'var(--positive-text)'}">${Math.round(toolPct)}% of margin</span>
            </div>
            <div class="profit-meter-bar">
                <div class="profit-meter-fill" style="width:${toolPct}%;background:${toolPct > 35 ? 'var(--negative)' : 'var(--accent)'}"></div>
            </div>
        </div>

        <div class="result-card highlight">
            <div class="result-icon-box amber">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
            </div>
            <div class="result-info">
                <div class="result-name">Paid Advertising Budget</div>
                <div class="result-detail">${adPctText}</div>
            </div>
            <div class="result-numbers">
                <div class="result-cost">${fmt(adspend)}/mo</div>
            </div>
        </div>`;

    tools.forEach(t => {
        const pct = grossProfit > 0 ? ((t.price / grossProfit) * 100).toFixed(1) : null;
        const roiRev = grossProfit > 0 ? (grossProfit / t.price).toFixed(1) : null;
        const isWorth = grossProfit > 0 && (t.price / grossProfit) < 0.08;
        const detail = grossProfit > 0
            ? `${pct}% of gross margin · ${roiRev}x margin ratio`
            : `Add revenue to evaluate cost ratio`;

        html += `
            <div class="result-card ${isWorth ? 'highlight' : ''}">
                <div class="result-icon-box ${isWorth ? 'green' : ''}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </div>
                <div class="result-info">
                    <div class="result-name">${t.name}</div>
                    <div class="result-detail">${detail}</div>
                </div>
                <div class="result-numbers">
                    <div class="result-cost">${fmt(t.price)}/mo</div>
                    <div class="result-roi" style="color:${isWorth ? 'var(--positive-text)' : 'var(--muted)'};display:inline-flex;align-items:center;gap:3px">
                        ${grossProfit > 0 ? (isWorth ? '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> High ROI' : 'Optimal at scale') : '—'}
                    </div>
                </div>
            </div>`;
    });

    if (tools.length === 0) {
        html += `
            <div class="result-card">
                <div class="result-icon-box">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                </div>
                <div class="result-info">
                    <div class="result-name">No software selected</div>
                    <div class="result-detail">Toggle items on the left to see their bottom-line impact</div>
                </div>
            </div>`;
    }

    html += `
        <div class="result-card ${isProfitable ? 'highlight' : 'danger'}" style="margin-top:16px">
            <div class="result-icon-box ${isProfitable ? 'green' : 'red'}">
                ${isProfitable
                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
                    : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>'
                }
            </div>
            <div class="result-info">
                <div class="result-name">Bottom-Line Viability</div>
                <div class="result-detail">
                    ${isProfitable
                        ? 'Your software overhead is well covered by current store volume.'
                        : 'Current tool overhead exceeds profit margin. Consider freemium tiers.'}
                </div>
            </div>
            <div class="result-numbers">
                <div class="result-cost" style="color:${isProfitable ? 'var(--positive-text)' : 'var(--negative-text)'};font-size:18px;font-weight:800">
                    ${fmt(netProfit)}/mo
                </div>
            </div>
        </div>`;

    document.getElementById('calcResults').innerHTML = html;
}

function saveSettings() {
    const revenue = document.getElementById('revenue').value;
    const margin  = document.getElementById('margin').value;
    const adspend = document.getElementById('adspend').value;
    const tools   = [...document.querySelectorAll('.tool-check:checked')].map(el => el.dataset.slug).join(',');

    fetch('/calculator.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `save_settings=1&revenue=${encodeURIComponent(revenue)}&margin=${encodeURIComponent(margin)}&adspend=${encodeURIComponent(adspend)}&tools=${encodeURIComponent(tools)}`
    }).then(() => {
        const msg = document.getElementById('saveSuccess');
        if (msg) {
            msg.style.display = 'block';
            setTimeout(() => msg.style.display = 'none', 3000);
        }
    });
}

calculate();
</script>

<?php include __DIR__ . '/footer.php'; ?>
