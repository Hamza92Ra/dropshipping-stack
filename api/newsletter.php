<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please enter a valid email address.']);
    exit;
}

// Ensure newsletter subscribers table exists or log
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(191) NOT NULL UNIQUE,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter_subscribers (email, ip_address) VALUES (?, ?)");
    $stmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '']);
} catch (\Throwable $e) {
    // Graceful fallback
    error_log("Newsletter error: " . $e->getMessage());
}

echo json_encode([
    'success' => true,
    'message' => "You're in! Check your inbox for the complete Starter Kit."
]);
