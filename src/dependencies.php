<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../templates', [
        'cache' => '.cache'
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};

$container['pdo'] = function ($c) {
    $pdo = new PDO('sqlite:' . __DIR__ . '/..' . getenv('PATH4DB'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};
/*
$container['errorHandler'] = function ($c) {
    return function ($req, $res) use ($c) {
        return $c['response']->withStatus(500)->write('500 Internal Server Error');
    };
};

$container['notFoundHandler'] = function ($c) {
    return function ($req, $res) use ($c) {
        return $c['response']->withStatus(404)->write('404 Not Found');
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($req, $res) use ($c) {
        return $c['response']->withStatus(405)->write('405 Method Not Allowed');
    };
};*/