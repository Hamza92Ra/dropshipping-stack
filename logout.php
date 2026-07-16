<?php
@define('APP', true);
require_once __DIR__ . '/config.php';
session_destroy();
header('Location: /dropshipping/index.php');
exit;