<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

if (is_admin()) redirect('/admin/index.php');

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
            redirect('/admin/index.php');
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
    <meta name="color-scheme" content="light dark">
    <script>
        (function() {
            var t = null;
            try { t = localStorage.getItem('theme'); } catch (e) {}
            if (t !== 'dark' && t !== 'light') {
                t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/theme.css">
    <script src="/assets/js/theme.js" defer></script>
    <meta name="robots" content="noindex">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg);margin:0;font-family:var(--font);">
    <div style="width:100%;max-width:400px;padding:24px;box-sizing:border-box;">
        <div style="text-align:center;margin-bottom:28px">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--surface);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;box-shadow:var(--shadow-sm)">
                <?= icon('shield', '', 26) ?>
            </div>
            <h1 style="font-size:22px;font-weight:800;color:var(--text);margin:0;letter-spacing:-0.02em;"><?= SITE_NAME ?> Admin</h1>
            <p style="font-size:13px;color:var(--muted);margin:4px 0 0;">Enter your administrative credentials</p>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-2xl);padding:32px;box-shadow:var(--shadow-sm);">
            <?php if ($error): ?>
                <div class="profile-alert profile-alert-error" style="margin-bottom:20px;">
                    <?= icon('alert-circle', '', 16) ?> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Username</label>
                    <input type="text" name="username" required autofocus autocomplete="username" class="form-input-styled" style="width:100%;box-sizing:border-box;padding:12px 16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-xl);color:var(--text);font-family:var(--font);outline:none;">
                </div>
                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Password</label>
                    <input type="password" name="password" required autocomplete="current-password" class="form-input-styled" style="width:100%;box-sizing:border-box;padding:12px 16px;background:var(--surface-2);border:1px solid var(--border);border-radius:var(--radius-xl);color:var(--text);font-family:var(--font);outline:none;">
                </div>
                <button type="submit" class="btn-primary" style="width:100%;padding:12px;border-radius:var(--radius-full);font-weight:700;justify-content:center;display:flex;align-items:center;gap:6px;">
                    <?= icon('log-in', '', 15) ?> Log In to Dashboard
                </button>
            </form>
        </div>

        <p style="text-align:center;margin-top:20px;font-size:13px;color:var(--muted)">
            <a href="/" style="color:var(--text);text-decoration:none;font-weight:600;">&larr; Return to main site</a>
        </p>
    </div>
</body>
</html>
