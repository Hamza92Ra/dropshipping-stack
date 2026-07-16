<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/send-verification.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $agree    = isset($_POST['agree_terms']);

    if (!$username || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (!$agree) {
        $error = 'You must agree to the Terms & Privacy Policy to create an account.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must contain at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif (!preg_match('/[\W_]/', $password)) {
        $error = 'Password must contain at least one special character (!@#$%...).';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            $error = 'Username or email already taken.';
        } else {
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            $token = bin2hex(random_bytes(32));

            $pdo->prepare("INSERT INTO users (username, email, password, verification_token) VALUES (?, ?, ?, ?)")
                ->execute([$username, $email, $hash, $token]);

            sendVerificationEmail($email, $token);

            $success = 'Account created! Please check your email to verify your account.';
        }
    }
}

$page_title = 'Register';
include __DIR__ . '/header.php';
?>

<div class="submit-wrap">
    <div class="submit-card">

        <h1>Create account</h1>
        <p class="sub">Join to upvote your favorite tools</p>

        <?php if ($error): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="flash flash-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" placeholder="cooldropseller">
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label for="passwordInput">Password</label>
                <input type="password" id="passwordInput" name="password" placeholder="••••••••" oninput="checkStrength(this.value)">
            </div>

            <div class="mt-8" style="margin-bottom:16px">
                <div style="height:4px;background:var(--border);border-radius:4px;overflow:hidden">
                    <div id="strengthBar" style="height:100%;width:0%;transition:width .3s,background .3s;border-radius:4px"></div>
                </div>
                <p id="strengthText" style="font-family:var(--mono);font-size:11px;color:var(--muted);margin-top:4px"></p>
            </div>

            <div class="sidebar-info mb-24" style="padding:14px 16px">
                <div id="rule-length" style="font-size:12px;color:var(--muted)">☐ At least 8 characters</div>
                <div id="rule-upper" style="font-size:12px;color:var(--muted)">☐ One uppercase letter</div>
                <div id="rule-lower" style="font-size:12px;color:var(--muted)">☐ One lowercase letter</div>
                <div id="rule-number" style="font-size:12px;color:var(--muted)">☐ One number</div>
                <div id="rule-special" style="font-size:12px;color:var(--muted)">☐ One special character (!@#$%...)</div>
            </div>

            <div class="form-group" style="display:flex;align-items:flex-start;gap:8px">
                <input type="checkbox" id="agree_terms" name="agree_terms" required style="width:auto;margin-top:3px">
                <label for="agree_terms" style="font-weight:400;text-transform:none;letter-spacing:normal;font-size:13px;color:var(--body)">
                    I agree to the <a href="/dropshipping/politique.php" target="_blank" style="color:var(--accent);font-weight:600">Terms &amp; Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;padding:13px;font-size:14px">
                Create account →
            </button>
        </form>

        <div style="display:flex;align-items:center;gap:12px;margin:24px 0">
            <div style="flex:1;height:1px;background:var(--border)"></div>
            <span style="font-family:var(--mono);font-size:11px;color:var(--muted)">or</span>
            <div style="flex:1;height:1px;background:var(--border)"></div>
        </div>

        <a href="/dropshipping/login.php" class="btn-secondary" style="width:100%;box-sizing:border-box">
            Already have an account? Log in
        </a>
        <?php endif; ?>

    </div>
</div>

<script>
function checkStrength(password) {
    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');
    let score  = 0;

    const rules = {
        'rule-length':  password.length >= 8,
        'rule-upper':   /[A-Z]/.test(password),
        'rule-lower':   /[a-z]/.test(password),
        'rule-number':  /[0-9]/.test(password),
        'rule-special': /[\W_]/.test(password),
    };

    for (const [id, passed] of Object.entries(rules)) {
        const el = document.getElementById(id);
        el.textContent = (passed ? '✓' : '☐') + el.textContent.slice(1);
        el.style.color = passed ? 'var(--teal)' : 'var(--muted)';
        if (passed) score++;
    }

    const levels = [
        { width: '0%',   color: 'var(--border)', label: '' },
        { width: '25%',  color: 'var(--red)',     label: 'Very weak' },
        { width: '50%',  color: 'var(--amber)',   label: 'Weak' },
        { width: '75%',  color: 'var(--amber)',   label: 'Medium' },
        { width: '90%',  color: 'var(--teal)',    label: 'Strong' },
        { width: '100%', color: 'var(--accent)',  label: 'Very strong' },
    ];

    const level = levels[score];
    bar.style.width      = level.width;
    bar.style.background = level.color;
    text.textContent     = level.label;
}
</script>
<script src="/assets/js/darkmode.js"></script>
<?php include __DIR__ . '/footer.php'; ?>