<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

@define('GROQ_API_KEY', 'your-groq-api-key-here');
@define('APP', true);
// ── Database ──────────────────────────────
@define('DB_HOST', 'localhost');
@define('DB_USER', 'your-db-username');
@define('DB_NAME', 'dropshipping_stack');
@define('DB_PASS', 'your-db-password');
@define('DB_CHARSET', 'utf8mb4');

// ── Site Settings ─────────────────────────
@define('SITE_NAME', 'DropshippingStack');
@define('SITE_TAGLINE', 'Every tool you need to start and scale your dropshipping store');
@define('SITE_URL', ''); // need to add my domain name   
@define('SITE_EMAIL', '');
// ── Mail Settings ─────────────────────────
@define('MAIL_HOST',     'smtp.gmail.com');
@define('MAIL_PORT',     587);
@define('MAIL_USERNAME', 'your-email@gmail.com');
@define('MAIL_PASSWORD', 'your-gmail-app-password');
@define('MAIL_FROM',     'your-email@gmail.com');
@define('MAIL_NAME',     SITE_NAME);


// ── Security ──────────────────────────────
@define('SECRET_KEY', 'generate-a-random-secret-key-here');

// ── Database Connection ───────────────────
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Never show real error to users in production
    error_log("DB Error: " . $e->getMessage());
    die("Service temporarily unavailable. Please try again later.");
}

// ── Session ───────────────────────────────

// ── Helper Functions ─────────────────────

/**
 * Safely output escaped HTML
 */
function e(?string $str): string
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function csrf_verify(): bool
{
    return isset($_POST['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

/**
 * Redirect to URL
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Check if admin is logged in
 */
function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

/**
 * Require admin or redirect
 */
function require_admin(): void
{
    if (!is_admin()) {
        redirect('/admin/login.php');
    }
}


function make_slug(string $str): string
{
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}