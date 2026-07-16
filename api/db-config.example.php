<?php
/**
 * /api/db-config.php
 *
 * Fill in your real database credentials below.
 * Keep this file OUT of version control / public repos (add to .gitignore).
 */
@define('GROQ_API_KEY', 'your-groq-api-key-here');
@define('DB_HOST', 'localhost');
@define('DB_USER', 'your-db-username');
@define('DB_NAME', 'your-db-name');
@define('DB_PASS', 'your-db-password');
@define('MAIL_PASSWORD', 'your-gmail-app-password');

function ds_get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}