<?php

define('SITE_NAME', 'MediNest Pharmacy');
define('DB_HOST', 'sql213.infinityfree.com');
define('DB_USER', 	'if0_42439810');
define('DB_PASSWORD', 'kcsIM8Zpbprhc6t');
define('DB_NAME', 'if0_42439810_db_medinest');

date_default_timezone_set('Asia/Manila');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ));
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

