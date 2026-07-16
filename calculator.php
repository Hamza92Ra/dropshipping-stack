<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$page_title = 'Dropshipping Cost Calculator — What Tools Will Cost You';
$page_desc  = 'Enter your monthly revenue and see exactly how much each dropshipping tool costs vs how much you will probably make.';

// Load saved settings if logged in
$saved = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $saved = $stmt->fetch();
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings']) && isset($_SESSION['user_id'])) {
    $revenue  = (int)($_POST['revenue'] ?? 5000);
    $margin   = (int)($_POST['margin'] ?? 25);
    $adspend  = (int)($_POST['adspend'] ?? 500);
    $tools    = $_POST['tools'] ?? '';

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
    $save_success = true;
}

$def_revenue = $saved['calculator_revenue'] ?? 5000;
$def_margin  = $saved['calculator_margin']  ?? 25;
$def_adspend = $saved['calculator_adspend'] ?? 500;
$def_tools   = $saved ? explode(',', $saved['calculator_tools'] ?? '') : ['shopify','klaviyo','minea'];

include __DIR__ . '/header.php';
?>

<style>
.calc-wrap { max-width: 900px; margin: 0 auto; padding: 48px 24px 80px; }
.calc-hero { text-align: center; margin-bottom: 44px; }
.calc-hero .badge { display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; color: #15803d; font-size: 12px; font-weight: 700; padding: 5px 14px; border-radius: 20px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.06em; }
.calc-hero h1 { font-size: clamp(26px, 4vw, 42px); font-weight: 800; color: var(--ink); letter-spacing: -0.025em; margin-bottom: 12px; line-height: 1.15; }
.calc-hero h1 span { color: var(--green); }
.calc-hero p { font-size: 15px; color: var(--muted); max-width: 480px; margin: 0 auto; line-height: 1.7; }
.calc-grid { display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: start; }
.calc-inputs { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 24px; position: sticky; top: 80px; }
.calc-inputs h3 { font-size: 15px; font-weight: 800; color: var(--ink); margin-bottom: 20px; }
.input-group { margin-bottom: 18px; }
.input-group label { display: block; font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
.input-with-prefix { display: flex; align-items: center; border: 1.5px solid var(--border); border-radius: var(--radius-sm); overflow: hidden; background: var(--bg); }
.input-prefix { padding: 10px 12px; background: var(--border); font-size: 14px; font-weight: 700; color: var(--muted); border-right: 1.5px solid var(--border); flex-shrink: 0; }
.input-with-prefix input { border: none; background: none; padding: 10px 12px; font-family: var(--font); font-size: 15px; font-weight: 600; color: var(--ink); width: 100%; outline: none; }
.range-input { width: 100%; accent-color: var(--accent); margin-top: 6px; }
.input-val { font-size: 13px; font-weight: 700; color: var(--accent); margin-top: 4px; }
.tool-toggles { margin-top: 20px; }
.tool-toggles h4 { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px; }
.tool-toggle { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid var(--border); font-size: 13px; }
.tool-toggle:last-child { border-bottom: none; }
.tool-toggle-name { font-weight: 600; color: var(--ink); }
.tool-toggle-price { font-size: 11px; color: var(--muted); }
.toggle-switch { position: relative; width: 36px; height: 20px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: var(--border); border-radius: 20px; cursor: pointer; transition: background 0.2s; }
.toggle-slider::before { content: ''; position: absolute; width: 14px; height: 14px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: transform 0.2s; }
.toggle-switch input:checked + .toggle-slider { background: var(--accent); }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(16px); }
.calc-results { display: flex; flex-direction: column; gap: 16px; }
.result-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; display: flex; align-items: center; gap: 16px; transition: all 0.2s; }
.result-card.highlight { border-color: var(--green); background: #f0fdf4; }
.result-card.danger { border-color: var(--red); background: #fef2f2; }
.result-icon { font-size: 28px; flex-shrink: 0; }
.result-info { flex: 1; }
.result-name { font-size: 15px; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
.result-detail { font-size: 12px; color: var(--muted); }
.result-numbers { text-align: right; flex-shrink: 0; }
.result-cost { font-size: 14px; font-weight: 700; color: var(--red); }
.result-roi { font-size: 12px; color: var(--green); font-weight: 600; margin-top: 2px; }
.summary-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
.summary-card { background: var(--dark); color: #fff; border-radius: var(--radius); padding: 18px; text-align: center; }
.summary-val { font-size: 26px; font-weight: 800; color: var(--cyan); letter-spacing: -0.02em; display: block; line-height: 1; }
.summary-label { font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
.profit-meter { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; margin-bottom: 16px; }
.profit-meter-label { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: var(--muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.06em; }
.profit-meter-bar { height: 10px; background: var(--border); border-radius: 99px; overflow: hidden; }
.profit-meter-fill { height: 100%; border-radius: 99px; transition: width 0.4s ease; background: linear-gradient(90deg, var(--green), #34d399); }

/* Save button */
.save-bar { margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border); }
.btn-save { width: 100%; padding: 11px; background: linear-gradient(135deg,#6c63ff,#5a52e0); border: none; border-radius: 8px; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
.btn-save:hover { opacity: 0.9; }
.save-success { text-align: center; font-size: 12px; color: var(--green); margin-top: 8px; font-weight: 600; display: none; }
</style>

<div class="calc-wrap">
    <div class="calc-hero">
        <div class="badge">💰 Free Calculator</div>
        <h1>See Your Real<br><span>Dropshipping Profit</span></h1>
        <p>Enter your revenue and see exactly what each tool costs vs what you will probably earn — so you only pay for tools that make sense.</p>
    </div>

    <div class="calc-grid">
        <!-- Inputs -->
        <div class="calc-inputs">
            <h3>Your Numbers</h3>

            <div class="input-group">
                <label>Monthly Revenue</label>
                <div class="input-with-prefix">
                    <span class="input-prefix">$</span>
                    <input type="number" id="revenue" value="<?= $def_revenue ?>" min="0" max="1000000" oninput="calculate()">
                </div>
            </div>

            <div class="input-group">
                <label>Profit Margin: <span class="input-val" id="marginVal"><?= $def_margin ?>%</span></label>
                <input type="range" class="range-input" id="margin" min="5" max="60" value="<?= $def_margin ?>" oninput="document.getElementById('marginVal').textContent=this.value+'%';calculate()">
            </div>

            <div class="input-group">
                <label>Ad Spend: <span class="input-val" id="adspendVal">$<?= number_format($def_adspend) ?>/mo</span></label>
                <input type="range" class="range-input" id="adspend" min="0" max="10000" step="100" value="<?= $def_adspend ?>" oninput="document.getElementById('adspendVal').textContent='$'+parseInt(this.value).toLocaleString()+'/mo';calculate()">
            </div>

            <div class="tool-toggles">
                <h4>Select Your Tools</h4>
                <?php
                $calc_tools = [
                    ['shopify',      'Shopify',        '🏪', 29],
                    ['klaviyo',      'Klaviyo',        '📧', 45],
                    ['minea',        'Minea',          '🔍', 49],
                    ['zendrop',      'Zendrop',        '📦', 49],
                    ['triple-whale', 'Triple Whale',   '📊', 129],
                    ['adspy',        'AdSpy',          '🕵️', 149],
                    ['autods',       'AutoDS',         '⚙️', 27],
                    ['loox',         'Loox',           '⭐', 10],
                    ['tidio',        'Tidio',          '💬', 19],
                    ['adcreative',   'AdCreative.ai',  '🤖', 29],
                ];
                foreach ($calc_tools as [$slug, $name, $icon, $price]):
                    $checked = in_array($slug, $def_tools) ? 'checked' : '';
                ?>
                <div class="tool-toggle">
                    <div>
                        <div class="tool-toggle-name"><?= $icon ?> <?= $name ?></div>
                        <div class="tool-toggle-price">$<?= $price ?>/mo</div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" class="tool-check"
                            data-price="<?= $price ?>"
                            data-name="<?= $name ?>"
                            data-icon="<?= $icon ?>"
                            data-slug="<?= $slug ?>"
                            <?= $checked ?>
                            onchange="calculate()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Save bar -->
            <div class="save-bar">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn-save" onclick="saveSettings()">
                        💾 Save My Settings
                    </button>
                    <div class="save-success" id="saveSuccess">✅ Settings saved!</div>
                    <?php if (isset($save_success)): ?>
                        <script>document.addEventListener('DOMContentLoaded',()=>{ document.getElementById('saveSuccess').style.display='block'; });</script>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="/dropshipping/login.php"
                        style="display:flex;align-items:center;justify-content:center;gap:6px;width:100%;padding:11px;background:rgba(108,99,255,0.1);border:1.5px dashed rgba(108,99,255,0.4);border-radius:8px;color:#6c63ff;font-size:13px;font-weight:600;text-decoration:none;box-sizing:border-box">
                        🔒 Login to save your settings
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Results -->
        <div class="calc-results" id="calcResults"></div>
    </div>
</div>

<script>
function calculate() {
    const revenue    = parseFloat(document.getElementById('revenue').value) || 0;
    const margin     = parseFloat(document.getElementById('margin').value) / 100;
    const adspend    = parseFloat(document.getElementById('adspend').value) || 0;
    const grossProfit = revenue * margin;
    const netBeforeTools = grossProfit - adspend;

    const tools = [...document.querySelectorAll('.tool-check:checked')].map(el => ({
        name:  el.dataset.name,
        icon:  el.dataset.icon,
        price: parseFloat(el.dataset.price),
        slug:  el.dataset.slug,
    }));

    const totalToolCost = tools.reduce((s, t) => s + t.price, 0);
    const netProfit = netBeforeTools - totalToolCost;
    const toolPct = grossProfit > 0 ? Math.min((totalToolCost / grossProfit) * 100, 100) : 0;

    const fmt = n => '$' + Math.round(n).toLocaleString();
    const profitColor = netProfit >= 0 ? 'var(--green)' : 'var(--red)';

    let html = `
        <div class="summary-cards">
            <div class="summary-card"><span class="summary-val">${fmt(grossProfit)}</span><div class="summary-label">Gross Profit</div></div>
            <div class="summary-card"><span class="summary-val" style="color:${netProfit>=0?'var(--cyan)':'#fca5a5'}">${fmt(netProfit)}</span><div class="summary-label">Net Profit</div></div>
            <div class="summary-card"><span class="summary-val">${fmt(totalToolCost)}</span><div class="summary-label">Tool Cost</div></div>
        </div>
        <div class="profit-meter">
            <div class="profit-meter-label">
                <span>Tools as % of profit</span>
                <span style="color:${toolPct>50?'var(--red)':'var(--green)'}">${Math.round(toolPct)}%</span>
            </div>
            <div class="profit-meter-bar">
                <div class="profit-meter-fill" style="width:${toolPct}%;background:${toolPct>50?'linear-gradient(90deg,var(--red),#f87171)':'linear-gradient(90deg,var(--green),#34d399)'}"></div>
            </div>
        </div>
        <div class="result-card highlight">
            <div class="result-icon">📊</div>
            <div class="result-info">
                <div class="result-name">Ad Spend</div>
                <div class="result-detail">${Math.round((adspend/revenue)*100)||0}% of revenue — ${adspend/revenue<0.3?'✅ healthy ratio':'⚠️ high ad spend'}</div>
            </div>
            <div class="result-numbers"><div class="result-cost" style="color:var(--amber)">${fmt(adspend)}/mo</div></div>
        </div>`;

    tools.forEach(t => {
        const pct = grossProfit > 0 ? ((t.price/grossProfit)*100).toFixed(1) : 0;
        const roiRev = grossProfit > 0 ? (grossProfit/t.price).toFixed(1) : 0;
        const isWorth = t.price/grossProfit < 0.05;
        html += `
            <div class="result-card ${isWorth?'highlight':''}">
                <div class="result-icon">${t.icon}</div>
                <div class="result-info">
                    <div class="result-name">${t.name}</div>
                    <div class="result-detail">${pct}% of gross profit · ${roiRev}x ROI potential</div>
                </div>
                <div class="result-numbers">
                    <div class="result-cost">${fmt(t.price)}/mo</div>
                    <div class="result-roi">${isWorth?'✓ Worth it':'⚠ Monitor ROI'}</div>
                </div>
            </div>`;
    });

    if (tools.length === 0) {
        html += `<div class="result-card"><div class="result-icon">👆</div><div class="result-info"><div class="result-name">Select tools on the left</div><div class="result-detail">Toggle tools to see their cost impact on your profit</div></div></div>`;
    }

    html += `
        <div class="result-card ${netProfit<0?'danger':'highlight'}">
            <div class="result-icon">${netProfit>=0?'🎯':'⚠️'}</div>
            <div class="result-info">
                <div class="result-name">Bottom Line</div>
                <div class="result-detail">${netProfit>=0?'Your tools are profitable at this revenue level':'You need more revenue or fewer tools to be profitable'}</div>
            </div>
            <div class="result-numbers">
                <div class="result-cost" style="color:${profitColor};font-size:18px;font-weight:800">${fmt(netProfit)}/mo</div>
            </div>
        </div>`;

    document.getElementById('calcResults').innerHTML = html;
}

function saveSettings() {
    const revenue = document.getElementById('revenue').value;
    const margin  = document.getElementById('margin').value;
    const adspend = document.getElementById('adspend').value;
    const tools   = [...document.querySelectorAll('.tool-check:checked')].map(el => el.dataset.slug).join(',');

    fetch('/dropshipping/calculator.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `save_settings=1&revenue=${revenue}&margin=${margin}&adspend=${adspend}&tools=${tools}`
    }).then(() => {
        const msg = document.getElementById('saveSuccess');
        msg.style.display = 'block';
        setTimeout(() => msg.style.display = 'none', 3000);
    });
}

calculate();
</script>
<script src="/assets/js/darkmode.js"></script>
<?php include __DIR__ . '/footer.php'; ?>