<?php

$loader = new \Phalcon\Loader();

$loader->registerNamespaces(
    [
        'Robot\Task' => APP_PATH . '/tasks',
        'Phalcon' => APP_PATH . '/library/Phalcon',
        'Cron' => APP_PATH . './library/Cron',
        'Sid\Cron' => APP_PATH . './library/Sid/Cron',
        'Sid\Phalcon\Cron' => APP_PATH . './library/Sid/Phalcon/Cron',
        'Egulias\EmailValidator' => APP_PATH . './library/Egulias',
        'Doctrine\Common\Lexer' => APP_PATH . './library/Doctrine/Common/Lexer',
    ]
);

$loader->register();
