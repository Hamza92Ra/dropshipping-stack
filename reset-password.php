<?php
@define('APP', true);
require 'config.php';
$error   = '';
$success = '';

$token = $_GET['token'] ?? $_POST['token'] ?? '';

// Validate token
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    die("This reset link is invalid or has expired. Please request a new one.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Passwords don't match.";
    } elseif (strlen($password) < 8) {
        $error = "Minimum 8 characters.";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?")
            ->execute([$hash, $user['id']]);
        header("Location: /login.php?reset=1");
        exit;
    }
}
// Show the reset form with hidden token field

$page_title = 'Reset Password';
include __DIR__ . '/header.php';
?>

<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:var(--bg);font-family:var(--font)">
    <div style="width:100%;max-width:420px">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-2xl);padding:40px;box-shadow:var(--shadow-sm)">
            <div style="text-align:center;margin-bottom:28px">
                <div style="color:var(--text);margin-bottom:12px;display:flex;justify-content:center;"><?= icon('lock', '', 36) ?></div>
                <h1 style="font-size:26px;font-weight:700;letter-spacing:-0.01em;color:var(--text);margin:0 0 8px">Reset Password</h1>
                <p style="color:var(--muted);font-size:14px;margin:0">Create a new, strong password</p>
            </div>

            <?php if ($error): ?>
                <div class="profile-alert profile-alert-error" style="margin-bottom:20px;">
                    <?= icon('alert-circle', '', 15) ?> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="profile-alert profile-alert-success" style="margin-bottom:20px;">
                    <?= icon('check-circle', '', 15) ?> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- Password -->
                    <div style="margin-bottom:16px">
                        <label for="password" style="display:block;font-size:13px;font-weight:600;color:var(--body-c);margin-bottom:6px">New Password</label>
                        <div style="position:relative;display:flex;align-items:center">
                            <input type="password" id="password" name="password" required minlength="8"
                                placeholder="••••••••" class="auth-input" autocomplete="new-password"
                                style="width:100%;padding:12px 44px 12px 16px;border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;outline:none;font-family:var(--font)">
                            <button type="button" class="password-toggle-btn" data-target="password"
                                aria-label="Show password" title="Show password" aria-pressed="false"
                                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;padding:6px;cursor:pointer;color:var(--muted);display:flex;align-items:center;justify-content:center;border-radius:6px;line-height:0">
                                <svg class="eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div style="margin-bottom:24px">
                        <label for="confirm" style="display:block;font-size:13px;font-weight:600;color:var(--body-c);margin-bottom:6px">Confirm Password</label>
                        <div style="position:relative;display:flex;align-items:center">
                            <input type="password" id="confirm" name="confirm" required minlength="8"
                                placeholder="••••••••" class="auth-input" autocomplete="new-password"
                                style="width:100%;padding:12px 44px 12px 16px;border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;outline:none;font-family:var(--font)">
                            <button type="button" class="password-toggle-btn" data-target="confirm"
                                aria-label="Show password" title="Show password" aria-pressed="false"
                                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;padding:6px;cursor:pointer;color:var(--muted);display:flex;align-items:center;justify-content:center;border-radius:6px;line-height:0">
                                <svg class="eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary"
                        style="width:100%;padding:13px;font-size:14px;font-weight:700;box-sizing:border-box">
                        Update Password →
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.password-toggle-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const eyeShow = btn.querySelector('.eye-show');
            const eyeHide = btn.querySelector('.eye-hide');

            if (isPassword) {
                eyeShow.style.display = 'none';
                eyeHide.style.display = 'block';
                btn.setAttribute('aria-label', 'Hide password');
                btn.title = 'Hide password';
                btn.setAttribute('aria-pressed', 'true');
            } else {
                eyeShow.style.display = 'block';
                eyeHide.style.display = 'none';
                btn.setAttribute('aria-label', 'Show password');
                btn.title = 'Show password';
                btn.setAttribute('aria-pressed', 'false');
            }

            input.focus();
            const len = input.value.length;
            input.setSelectionRange(len, len);
        });
    });
</script>
<?php include __DIR__ . '/footer.php'; ?>