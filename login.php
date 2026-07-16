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
            header('Location: /dropshipping/index.php');
            exit;
        }

    } else {
        $error = 'Invalid email or password.';
    }

} // ← this was missing!

$page_title = 'Login';
include __DIR__ . '/header.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --bg: #F6F4EE;
        --surface: #FFFFFF;
        --border: #E7E3D8;
        --ink: #2E332B;
        --body-c: #565B50;
        --muted: #9B9C8D;
        --accent: #7C9885;
        --accent-h: #6C8875;
        --accent-light: #E9EFE8;
        --teal: #6B93A0;
        --red: #C98B85;
        --shadow: 0 8px 32px rgba(46,51,43,0.08);
        --shadow-lg: 0 16px 40px rgba(46,51,43,0.12);
        --radius: 20px;
        --radius-sm: 10px;
        --ease: cubic-bezier(0.4, 0, 0.2, 1);
        --font: 'Inter', system-ui, sans-serif;
        --display: 'Fraunces', 'Inter', serif;
    }

    .auth-card { animation: authFadeUp 0.5s var(--ease) both; }
    @keyframes authFadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .auth-input {
        transition: border-color 0.25s var(--ease), box-shadow 0.25s var(--ease), background 0.25s var(--ease);
    }
    .auth-input:focus {
        border-color: var(--accent) !important;
        background: #fff !important;
        box-shadow: 0 0 0 3px var(--accent-light);
    }
    .auth-btn-primary {
        transition: transform 0.2s var(--ease), box-shadow 0.2s var(--ease);
    }
    .auth-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(124,152,133,0.35); }
    .auth-btn-secondary {
        transition: border-color 0.2s var(--ease), color 0.2s var(--ease), background 0.2s var(--ease);
    }
    .auth-btn-secondary:hover {
        border-color: var(--accent) !important;
        color: var(--accent-h) !important;
        background: var(--accent-light) !important;
    }
    .auth-link { transition: color 0.2s var(--ease); }
    @media (prefers-reduced-motion: reduce) {
        .auth-card { animation: none; }
        * { transition-duration: 0.01ms !important; }
    }
</style>

<div style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;background:var(--bg);font-family:var(--font)">
    <div style="width:100%;max-width:420px">

        <!-- Card -->
        <div class="auth-card" style="background:var(--surface);border-radius:var(--radius);padding:40px;box-shadow:var(--shadow);border:1px solid var(--border)">

            <!-- Header -->
            <div style="text-align:center;margin-bottom:32px">
                <div style="font-size:36px;margin-bottom:12px">👋</div>
                <h1 style="font-family:var(--display);font-size:28px;font-weight:600;letter-spacing:-0.01em;color:var(--ink);margin:0 0 8px">Welcome Back</h1>
                <p style="color:var(--muted);font-size:14px;margin:0">Login to upvote your favorite tools</p>
            </div>

            <?php if ($error): ?>
                <div style="background:#FBEEEC;border:1px solid var(--red);color:#A85A54;padding:12px 16px;border-radius:var(--radius-sm);margin-bottom:20px;font-size:13px">
                    ⚠️ <?= htmlspecialchars($error) ?>
                    <?php if (str_contains($error, 'verify your email')): ?>
                        <br><br>
                        <a href="/dropshipping/resend-verification.php?email=<?= urlencode($_POST['email'] ?? '') ?>"
                            class="auth-link"
                            style="color:var(--accent-h);font-weight:600;text-decoration:underline">
                            Resend verification email →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- Email -->
                <div style="margin-bottom:18px">
                    <label style="display:block;font-size:13px;font-weight:600;color:var(--body-c);margin-bottom:6px">Email address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="you@example.com" class="auth-input"
                        style="width:100%;padding:12px 16px;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--radius-sm);color:var(--ink);font-size:14px;box-sizing:border-box;outline:none;font-family:var(--font)">
                </div>

                <!-- Password -->
                <div style="margin-bottom:8px">
                    <label style="display:block;font-size:13px;font-weight:600;color:var(--body-c);margin-bottom:6px">Password</label>
                    <input type="password" name="password"
                        placeholder="••••••••" class="auth-input"
                        style="width:100%;padding:12px 16px;background:var(--bg);border:1.5px solid var(--border);border-radius:var(--radius-sm);color:var(--ink);font-size:14px;box-sizing:border-box;outline:none;font-family:var(--font)">
                </div>

                <!-- Forgot password -->
                <div style="text-align:right;margin-bottom:24px">
                    <a href="/dropshipping/forgot-password.php" class="auth-link" style="font-size:12px;color:var(--accent-h);text-decoration:none">Forgot password?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="auth-btn-primary"
                    style="width:100%;padding:14px;background:linear-gradient(135deg,var(--accent),var(--accent-h));border:none;border-radius:var(--radius-sm);color:#fff;font-size:15px;font-weight:700;cursor:pointer;letter-spacing:0.3px;box-shadow:0 4px 18px rgba(124,152,133,0.3);font-family:var(--font)">
                    Login →
                </button>
            </form>

            <!-- Divider -->
            <div style="display:flex;align-items:center;gap:12px;margin:24px 0">
                <div style="flex:1;height:1px;background:var(--border)"></div>
                <span style="font-size:12px;color:var(--muted)">or</span>
                <div style="flex:1;height:1px;background:var(--border)"></div>
            </div>

            <!-- Register link -->
            <a href="/dropshipping/register.php" class="auth-btn-secondary"
                style="display:block;text-align:center;padding:13px;border:1.5px solid var(--border);border-radius:var(--radius-sm);color:var(--body-c);font-size:14px;font-weight:600;text-decoration:none">
                Create an account
            </a>

        </div>

        <!-- Bottom text -->
        <p style="text-align:center;margin-top:20px;font-size:12px;color:var(--muted)">
            By logging in you agree to our <a href="#" class="auth-link" style="color:var(--accent-h)">Terms</a>
        </p>

    </div>
</div>
<script src="/assets/js/darkmode.js"></script>
<?php include __DIR__ . '/footer.php'; ?>