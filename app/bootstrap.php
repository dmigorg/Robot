<?php

declare(strict_types=1);

use Phalcon\Cli\Console;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as CliDi;

const BASE_PATH = dirname(__DIR__);
const APP_PATH = BASE_PATH . '/app';
const TASK_PATH = BASE_PATH . '/tasks';
/**
 * The FactoryDefault Dependency Injector automatically registers the services that
 * provide a full stack framework. These default services can be overidden with custom ones.
 */
$container = new CliDi();
$dispatcher = new Dispatcher();

$dispatcher->setDefaultNamespace('Robot\Controllers');
$container->setShared('dispatcher', $dispatcher);

/**
 * Include Autoloader
 */
include APP_PATH . '/config/loader.php';

/**
 * Include Services
 */
include APP_PATH . '/config/services.php';


/**
 * Create a console application
 */
$console = new Console($container);
$container->setShared("console", $console);

/**
 * Process the console arguments
 */
$container->set('task', function () use ($argv) {
    return ['name' => $argv[1] ?? 'help', 'arg' => $argv[2] ?? ''];
});

set_exception_handler(function ($exception) use ($container) {
    // Log unhandled exception as an error
    $logger = $container->get('logger');
    $logger->error($exception->getMessage());
    $logger->debug($exception->getFile() . ':' . $exception->getLine());
    $logger->debug("StackTrace:\r\n" . $exception->getTraceAsString() . "\r\n");
    echo 'App terminates with the error';
    exit(255);
});

/**
 * Handle
 */
$console->handle();
