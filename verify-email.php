<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$message = '';
$success = false;

$token = trim($_GET['token'] ?? '');

if (!$token) {
    $message = 'Invalid verification link.';
} else {
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if (!$user) {
        $message = 'Invalid or expired verification link.';
    } elseif ($user['is_verified']) {
        $message = 'Your email is already verified. You can log in!';
        $success = true;
    } else {
        $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?")
            ->execute([$user['id']]);
        $message = 'Email verified! You can now log in.';
        $success = true;
    }
}

$page_title = 'Verify Email';
include __DIR__ . '/header.php';
?>

<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:var(--bg);font-family:var(--font)">
    <div style="width:100%;max-width:420px;text-align:center">
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-2xl);padding:40px;box-shadow:var(--shadow-sm)">
            <?php if ($success): ?>
                <div style="color:var(--positive);margin-bottom:16px;display:flex;justify-content:center;"><?= icon('check-circle', '', 48) ?></div>
                <h1 style="font-size:24px;font-weight:700;color:var(--text);margin:0 0 12px">Email Verified!</h1>
                <p style="color:var(--muted);font-size:14px;line-height:1.6;margin:0 0 24px"><?= htmlspecialchars($message) ?></p>
                <a href="/login.php" class="btn-primary"
                    style="display:inline-flex;align-items:center;justify-content:center;padding:12px 32px;font-size:14px;font-weight:700;text-decoration:none">
                    Login Now →
                </a>
            <?php else: ?>
                <div style="color:var(--negative);margin-bottom:16px;display:flex;justify-content:center;"><?= icon('x-circle', '', 48) ?></div>
                <h1 style="font-size:24px;font-weight:700;color:var(--text);margin:0 0 12px">Verification Failed</h1>
                <p style="color:var(--muted);font-size:14px;line-height:1.6;margin:0 0 24px"><?= htmlspecialchars($message) ?></p>
                <a href="/resend-verification.php" class="btn-primary"
                    style="display:inline-flex;align-items:center;justify-content:center;padding:12px 32px;font-size:14px;font-weight:700;text-decoration:none">
                    Resend Verification →
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>