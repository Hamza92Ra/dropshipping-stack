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

            $pdo->prepare("INSERT INTO users (username, email, password, verification_token, is_verified, email_verified) VALUES (?, ?, ?, ?, 1, 1)")
                ->execute([$username, $email, $hash, $token]);

            $new_user_id = (int)$pdo->lastInsertId();

            // Try sending verification email in background/safe mode
            try {
                @sendVerificationEmail($email, $token);
            } catch (\Throwable $e) {}

            // Immediate login onboarding
            $_SESSION['user_id']         = $new_user_id;
            $_SESSION['username']        = $username;
            $_SESSION['just_registered'] = true;

            // Set seen_intro cookie
            setcookie('seen_intro', '1', time() + 60 * 60 * 24 * 365, '/');

            header('Location: /index.php');
            exit;
        }
    }
}

$page_title      = 'Create Account';
$page_heading    = 'Registration';
$page_subheading = 'Join dropshippers vetting software, calculating stacks, and tracking benchmarks';

include __DIR__ . '/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <div style="text-align:center;margin-bottom:28px">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--accent-light);color:var(--accent);display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px">
                <?= icon('sparkles', '', 24) ?>
            </div>
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join 2,000+ dropshippers optimizing their tools</p>
        </div>

        <?php if ($error): ?>
            <div style="background:var(--negative-bg);border:1px solid var(--negative-border);color:var(--negative-text);padding:12px 16px;border-radius:var(--radius);margin-bottom:20px;font-size:13px;display:flex;align-items:flex-start;gap:8px">
                <?= icon('info', '', 16) ?>
                <div><?= e($error) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:var(--positive-bg);border:1px solid var(--positive-border);color:var(--positive-text);padding:14px 18px;border-radius:var(--radius);margin-bottom:20px;font-size:13.5px;display:flex;align-items:flex-start;gap:10px">
                <?= icon('check-circle', '', 18) ?>
                <div>
                    <strong style="display:block;margin-bottom:2px">Verification Email Sent</strong>
                    <?= e($success) ?>
                </div>
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= e($_POST['username'] ?? '') ?>" placeholder="dropseller" required>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@domain.com" autocomplete="email" required>
                </div>

                <div class="form-group">
                    <label for="passwordInput">Password</label>
                    <div style="position:relative;display:flex;align-items:center">
                        <input type="password" id="passwordInput" name="password" placeholder="Min. 8 characters" required style="padding-right:44px">
                        <button type="button" id="togglePasswordBtn" class="search-clear-btn" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);display:flex;padding:6px" aria-label="Toggle password visibility">
                            <?= icon('eye', '', 18) ?>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom:20px;display:flex;align-items:flex-start;gap:10px">
                    <input type="checkbox" name="agree_terms" id="agreeTerms" required style="margin-top:3px;accent-color:var(--accent);cursor:pointer">
                    <label for="agreeTerms" style="font-size:12.5px;color:var(--body);line-height:1.45;cursor:pointer">
                        I agree to the <a href="/privacy.php" style="color:var(--accent);font-weight:600">Privacy Policy</a> and <a href="/affiliate-disclosure.php" style="color:var(--accent);font-weight:600">Affiliate Terms</a>
                    </label>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:14px">
                    <span>Create Account</span>
                    <?= icon('arrow-right', '', 15) ?>
                </button>
            </form>
        <?php endif; ?>

        <div style="display:flex;align-items:center;gap:12px;margin:24px 0">
            <div style="flex:1;height:1px;background:var(--border)"></div>
            <span style="font-size:12px;color:var(--muted)">or</span>
            <div style="flex:1;height:1px;background:var(--border)"></div>
        </div>

        <a href="/login.php" class="btn-secondary" style="display:block;text-align:center;padding:11px;font-size:13.5px">
            Already have an account? Sign In
        </a>

    </div>
</div>

<script>
(function () {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('passwordInput');
    if (toggleBtn && pwdInput) {
        toggleBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const isPassword = pwdInput.type === 'password';
            pwdInput.type = isPassword ? 'text' : 'password';
        });
    }
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
