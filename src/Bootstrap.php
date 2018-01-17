<?php

namespace SenbonXSS;

use PDO;
use Slim\App;
use Slim\Views\PhpRenderer;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Slim\Csrf\Guard;
use Slim\Flash\Messages;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;

/**
 * Class Bootstrap
 */
class Bootstrap
{
    /** @var App */
    protected $app;

    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /**
     * Bootstrap constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->container = $this->app->getContainer();
    }

    /**
     * Start application
     */
    public function start()
    {
        $this->registerDependencies();
        $this->registerMiddleware();
    }

    /**
     * Register dependencies of app
     */
    protected function registerDependencies()
    {
        // view renderer
        $this->container['renderer'] = function ($c) {
            $settings = $c->get('settings')['renderer'];
            return new PhpRenderer($settings['template_path']);
        };

        // monolog
        $this->container['logger'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $logger = new Logger($settings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));
            return $logger;
        };

        // Register component on container
        $this->container['view'] = function ($container) {
            $view = new Twig(__DIR__ . '/../templates', [
                'cache' => '.cache'
            ]);

            // Instantiate and add Slim specific extension
            $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
            $view->addExtension(new TwigExtension($container['router'], $basePath));

            return $view;
        };

        $this->container['csrf'] = function () {
            return new Guard();
        };

        $this->container['pdo'] = function () {
            $pdo = new PDO('sqlite:' . __DIR__ . '/..' . getenv('PATH4DB'));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        };

        $this->container['flash'] = function () {
            return new Messages();
        };

        /*
        $this->container['errorHandler'] = function ($c) {
            return function ($req, $res) use ($c) {
                return $c['response']->withStatus(500)->write('500 Internal Server Error');
            };
        };

        $this->container['notFoundHandler'] = function ($c) {
            return function ($req, $res) use ($c) {
                return $c['response']->withStatus(404)->write('404 Not Found');
            };
        };

        $this->container['notAllowedHandler'] = function ($c) {
            return function ($req, $res) use ($c) {
                return $c['response']->withStatus(405)->write('405 Method Not Allowed');
            };
        };*/
    }

    /**
     * Register middleware of app
     */
    protected function registerMiddleware()
    {
        $this->app->add($this->container->get('csrf'));
    }
}
