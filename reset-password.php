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
        header("Location: /dropshipping/login.php?reset=1");
        exit;
    }
}
// Show the reset form with hidden token field

$page_title = 'Reset Password';
include __DIR__ . '/header.php';
?>

<style>
    label {
        color: #1a1a2e !important;
    }

    input[type="password"] {
        color: #1a1a2e !important;
        background: #fff !important;
        border: 1px solid #ddd !important;
    }
</style>

<div style="max-width:420px;margin:60px auto;padding:0 20px">
    <div style="background:var(--card);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:32px">
        <h1 style="font-size:24px;font-weight:800;margin-bottom:24px;text-align:center">Reset Password</h1>

        <?php if ($error): ?>
            <div style="background:rgba(255,80,80,0.1);border:1px solid rgba(255,80,80,0.3);color:#ff5050;padding:12px;border-radius:8px;margin-bottom:16px"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="background:rgba(80,255,120,0.1);border:1px solid rgba(80,255,120,0.3);color:#50ff78;padding:12px;border-radius:8px;margin-bottom:16px"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div style="margin-bottom:16px">
                    <label style="display:block;font-size:13px;margin-bottom:6px">New Password</label>
                    <input type="password" name="password"
                        style="width:100%;padding:10px 14px;border-radius:8px;font-size:14px;box-sizing:border-box">
                </div>
                <div style="margin-bottom:24px">
                    <label style="display:block;font-size:13px;margin-bottom:6px">Confirm Password</label>
                    <input type="password" name="confirm"
                        style="width:100%;padding:10px 14px;border-radius:8px;font-size:14px;box-sizing:border-box">
                </div>
                <button type="submit"
                    style="width:100%;padding:12px;background:var(--accent);border:none;border-radius:8px;color:#fff;font-size:15px;font-weight:700;cursor:pointer">
                    Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>