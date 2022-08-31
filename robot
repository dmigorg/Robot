#!/usr/bin/env php
<?php

set_exception_handler(function ($exception) use ($container) {
    // Log unhandled exception as an error
    $logger = $container->get('logger');
    $logger->error($exception->getMessage());
    $logger->debug($exception->getFile() . ':' . $exception->getLine());
    $logger->debug("StackTrace:\r\n" . $exception->getTraceAsString() . "\r\n");
    echo 'App terminates with the error';
    exit(255);
});

include __DIR__ . "/app/bootstrap.php";
