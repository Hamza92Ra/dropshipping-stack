<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php?redirect=dashboard');
    exit;
}

// When logged in, /dashboard or dashboard.php shows the personal dashboard
require __DIR__ . '/index.php';
