<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_ROOT', APP_ROOT . DIRECTORY_SEPARATOR . 'public');

$config = require APP_ROOT . '/config/config.php';
$localConfigFile = APP_ROOT . '/config/config.local.php';

if (is_file($localConfigFile)) {
    $localConfig = require $localConfigFile;
    $config = array_replace_recursive($config, $localConfig);
}

date_default_timezone_set((string) ($config['timezone'] ?? 'Asia/Jakarta'));

if ((bool) ($config['debug'] ?? false)) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('frank_shines_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once APP_ROOT . '/app/database.php';
require_once APP_ROOT . '/app/functions.php';
require_once APP_ROOT . '/app/csrf.php';
require_once APP_ROOT . '/app/auth.php';
require_once APP_ROOT . '/app/data.php';
