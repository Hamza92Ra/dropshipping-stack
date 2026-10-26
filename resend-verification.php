<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/send-verification.php';   // ← shared helper

$message = '';
$error   = '';

$email = trim($_GET['email'] ?? '');

if ($email) {
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Always show the same message to prevent email enumeration
    $message = "If that account exists and is unverified, a new verification email has been sent.";

    if ($user && !$user['is_verified']) {
        $token = bin2hex(random_bytes(32));

        $pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?")
            ->execute([$token, $user['id']]);

        sendVerificationEmail($email, $token);   // ← PHPMailer now, not mail()
    }
}

$page_title = 'Resend Verification';
include __DIR__ . '/header.php';
?>


<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:var(--bg);font-family:var(--font)">
    <div style="width:100%;max-width:420px">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-2xl);padding:40px;box-shadow:var(--shadow-sm);text-align:center">

            <div style="color:var(--accent);margin-bottom:16px;display:flex;justify-content:center;"><?= icon('mail', '', 44) ?></div>
            <h1 style="font-size:24px;font-weight:700;color:var(--text);margin:0 0 12px">Check your inbox</h1>

            <?php if ($message): ?>
                <div style="background:var(--green-light);border:1px solid var(--green);color:var(--green-text);padding:14px 16px;border-radius:var(--radius-sm);margin-bottom:24px;font-size:14px;line-height:1.5">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php else: ?>
                <p style="color:var(--muted);font-size:14px;line-height:1.6;margin:0 0 24px">
                    Enter your email below and we'll resend your verification link.
                </p>
                <form method="GET">
                    <div style="margin-bottom:16px">
                        <input type="email" name="email" placeholder="you@example.com" class="auth-input" required
                            style="width:100%;padding:12px 16px;border-radius:var(--radius-sm);font-size:14px;box-sizing:border-box;outline:none;font-family:var(--font)">
                    </div>
                    <button type="submit" class="btn-primary"
                        style="width:100%;padding:13px;font-size:14px;font-weight:700;box-sizing:border-box">
                        Resend Email →
                    </button>
                </form>
            <?php endif; ?>

            <div style="margin-top:24px">
                <a href="/login.php" style="font-size:13px;color:var(--accent-h);font-weight:600;text-decoration:none">← Back to login</a>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>