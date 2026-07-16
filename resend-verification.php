<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
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


<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px 20px">
    <div style="width:100%;max-width:420px">
        <div style="background:#fff;border-radius:20px;padding:40px;box-shadow:0 4px 40px rgba(0,0,0,0.08);text-align:center">

            <div style="font-size:48px;margin-bottom:16px">📬</div>
            <h1 style="font-size:22px;font-weight:800;color:#1a1a2e;margin:0 0 12px">Check your inbox</h1>

            <?php if ($message): ?>
                <p style="color:#555;font-size:14px;line-height:1.6;margin:0 0 24px">
                    <?= htmlspecialchars($message) ?>
                </p>
            <?php else: ?>
                <p style="color:#555;font-size:14px;line-height:1.6;margin:0 0 24px">
                    Enter your email below and we'll resend your verification link.
                </p>
                <form method="GET">
                    <input type="email" name="email" placeholder="you@example.com" required
                        style="width:100%;padding:12px 16px;background:#f7f8fa;border:1.5px solid #e8e8e8;border-radius:10px;font-size:14px;box-sizing:border-box;margin-bottom:16px">
                    <button type="submit"
                        style="width:100%;padding:13px;background:linear-gradient(135deg,#6c63ff,#5a52e0);border:none;border-radius:10px;color:#fff;font-size:14px;font-weight:700;cursor:pointer">
                        Resend Email
                    </button>
                </form>
            <?php endif; ?>

            <a href="/dropshipping/login.php" style="font-size:13px;color:#6c63ff;text-decoration:none">← Back to login</a>
        </div>
    </div>
</div>