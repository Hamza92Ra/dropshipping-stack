<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if (!$user['is_verified']) {
            $error = 'Please verify your email before logging in. Check your inbox.';
        } else {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: /index.php');
            exit;
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

$page_title      = 'Sign In';
$page_heading    = 'Account Authentication';
$page_subheading = 'Access your saved stack, preferences, and community votes';

include __DIR__ . '/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Header -->
        <div style="text-align:center;margin-bottom:28px">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--accent-light);color:var(--accent);display:inline-flex;align-items:center;justify-content:center;margin-bottom:14px">
                <?= icon('sparkles', '', 24) ?>
            </div>
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to manage your saved tools &amp; stacks</p>
        </div>

        <?php if ($error): ?>
            <div style="background:var(--negative-bg);border:1px solid var(--negative-border);color:var(--negative-text);padding:12px 16px;border-radius:var(--radius);margin-bottom:20px;font-size:13px;display:flex;align-items:flex-start;gap:8px">
                <?= icon('info', '', 16) ?>
                <div>
                    <?= e($error) ?>
                    <?php if (str_contains($error, 'verify your email')): ?>
                        <br>
                        <a href="/resend-verification.php?email=<?= urlencode($_POST['email'] ?? '') ?>" style="color:var(--accent);font-weight:700;text-decoration:underline;margin-top:4px;display:inline-block">
                            Resend verification email →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="name@company.com" autocomplete="email" required>
            </div>

            <!-- Password -->
            <div class="form-group" style="margin-bottom:8px">
                <label for="password">Password</label>
                <div style="position:relative;display:flex;align-items:center">
                    <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required style="padding-right:44px">
                    <button type="button" id="togglePasswordBtn" class="search-clear-btn" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);display:flex;padding:6px" aria-label="Toggle password visibility">
                        <?= icon('eye', '', 18) ?>
                    </button>
                </div>
            </div>

            <div style="text-align:right;margin-bottom:24px">
                <a href="/forgot-password.php" style="font-size:12.5px;color:var(--accent);font-weight:600;text-decoration:none">Forgot password?</a>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;padding:12px;font-size:14px">
                <span>Sign In</span>
                <?= icon('arrow-right', '', 15) ?>
            </button>
        </form>

        <div style="display:flex;align-items:center;gap:12px;margin:24px 0">
            <div style="flex:1;height:1px;background:var(--border)"></div>
            <span style="font-size:12px;color:var(--muted)">or</span>
            <div style="flex:1;height:1px;background:var(--border)"></div>
        </div>

        <a href="/register.php" class="btn-secondary" style="display:block;text-align:center;padding:11px;font-size:13.5px">
            Create an Account
        </a>

        <p style="text-align:center;margin-top:20px;font-size:12px;color:var(--muted)">
            By signing in you agree to our <a href="/privacy.php" style="color:var(--accent)">Privacy Policy</a>
        </p>

    </div>
</div>

<script>
(function () {
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('password');
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
