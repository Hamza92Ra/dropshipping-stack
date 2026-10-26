<?php
@define('APP', true);
require_once __DIR__ . '/config.php';

// Redirect to dashboard with intro flag so that the transition into the dashboard can be fully experienced
if (isset($_GET['reopen']) || empty($_COOKIE['seen_intro'])) {
    header('Location: /index.php?intro=1');
    exit;
}

header('Location: /index.php?intro=1');
exit;
