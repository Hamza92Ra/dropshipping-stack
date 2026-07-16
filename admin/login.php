<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

if (is_admin()) redirect('/dropshipping/admin/');;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Security check failed.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('/dropshipping/admin/');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/dropshipping/assets/css/style.css">
    <meta name="robots" content="noindex">
</head>

<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg)">
    <div style="width:100%;max-width:380px;padding:24px">
        <div style="text-align:center;margin-bottom:32px">
            <div style="font-size:32px">⚡</div>
            <h1 style="font-size:22px;font-weight:800;color:var(--ink);margin-top:8px"><?= SITE_NAME ?> Admin</h1>
        </div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:28px">
            <?php if ($error): ?>
                <div class="flash flash-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required autofocus autocomplete="username">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;margin-top:8px">Log In</button>
            </form>
        </div>
        <p style="text-align:center;margin-top:16px;font-size:12px;color:var(--muted)">
            <a href="/" style="color:var(--accent)">← Back to site</a>
        </p>
    </div>
</body>

</html>