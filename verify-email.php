<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

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

<div style="max-width:420px;margin:80px auto;padding:0 20px;text-align:center">
    <div style="background:var(--card);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:40px">
        <?php if ($success): ?>
            <div style="font-size:48px;margin-bottom:16px">✅</div>
            <h1 style="font-size:22px;font-weight:800;margin-bottom:12px">Email Verified!</h1>
            <p style="color:#888;margin-bottom:24px"><?= htmlspecialchars($message) ?></p>
            <a href="/dropshipping/login.php"
                style="display:inline-block;padding:12px 32px;background:var(--accent);color:#fff;border-radius:8px;font-weight:700;text-decoration:none">
                Login Now
            </a>
        <?php else: ?>
            <div style="font-size:48px;margin-bottom:16px">❌</div>
            <h1 style="font-size:22px;font-weight:800;margin-bottom:12px">Verification Failed</h1>
            <p style="color:#888"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>