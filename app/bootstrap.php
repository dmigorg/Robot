<?php
declare(strict_types=1);

use Phalcon\Cli\Console;
use Phalcon\Cli\Dispatcher;
use Phalcon\Di\FactoryDefault\Cli as CliDi;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('TASK_PATH', BASE_PATH . '/tasks');

/**
 * The FactoryDefault Dependency Injector automatically registers the services that
 * provide a full stack framework. These default services can be overidden with custom ones.
 */
$container = new CliDi();
$dispatcher = new Dispatcher();

$dispatcher->setDefaultNamespace('Robot\Controllers');
$container->setShared('dispatcher', $dispatcher);
/**
 * Include Services
 */
include APP_PATH . '/config/services.php';

/**
 * Include Autoloader
 */
include APP_PATH . '/config/loader.php';

/**
 * Create a console application
 */
$console = new Console($container);
$container->setShared("console", $console);

/**
 * Process the console arguments
 */
$container->set('task', function() use($argv) {
        return ['name' => $argv[1] ?? 'help', 'arg' => $argv[2] ?? '' ];
    });

try {
    /**
     * Handle
     */
    $console->handle();
} 
catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
    echo get_class($e);
    exit(255);
}
