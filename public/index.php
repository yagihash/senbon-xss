<?php

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

$dot_env = __DIR__ . '/../.env';
if (is_readable($dot_env)) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
    $dotenv->load();
}

Resque::setBackend('localhost:6379');

ini_set('session.gc_maxlifetime', 86400);
session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);
$bootstrap = new \SenbonXSS\Bootstrap($app);

// Start app
$bootstrap->start();
