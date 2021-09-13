<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault\Cli as CliDi;
use Phalcon\Cli\Console as ConsoleApp;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

/**
 * The FactoryDefault Dependency Injector automatically registers the services that
 * provide a full stack framework. These default services can be overidden with custom ones.
 */
$di = new CliDi();

/**
 * Include Services
 */
include APP_PATH . '/config/services.php';

/**
 * Get config service for use in inline setup below
 */
$config = $di->getConfig();

/**
 * Include Autoloader
 */
include APP_PATH . '/config/loader.php';

/**
 * Create a console application
 */
$console = new ConsoleApp($di);

$di->setShared("console", $console);


$di->setShared('dispatcher', function () {
    $dispatcher = new Phalcon\CLI\Dispatcher();
    $dispatcher->setDefaultNamespace('Robot\Task');
    return $dispatcher;
});

/**
 * Process the console arguments
 */
$arguments = [];

foreach ($argv as $k => $arg) {
    if ($k == 1) {
        $arguments['task'] = $arg;
    } elseif ($k == 2) {
        $arguments['action'] = $arg;
    } elseif ($k >= 3) {
        $arguments['params'][] = $arg;
    }
}

try {

    /**
     * Handle
     */
    $console->handle($arguments);

} 
catch (Phalcon\Cli\Dispatcher\Exception $e) {
    $arguments['task'] = 'help';
    $console->handle($arguments);
}
catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    echo get_class($e);
    exit(255);
}
