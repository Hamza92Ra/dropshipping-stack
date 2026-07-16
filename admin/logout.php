<?php
@define('APP', true);
require_once __DIR__ . '/../config.php';
session_destroy();
redirect('/admin/login.php');
