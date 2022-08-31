<?php

declare(strict_types=1);

$container->setShared(
    'config',
    function () {
        return new Phalcon\Config\Adapter\Ini(BASE_PATH . '/config.ini');
    }
);

/**
 * Get config service for use in inline setup below
 */
$config = $container->getConfig();

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$container->setShared(
    'db',
    function () use ($config) {
        $params = [
            'host'     => $config->db->host,
            'username' => $config->db->username,
            'password' => $config->db->password,
            'dbname'   => $config->db->dbname,
            'port'  => $config->db->port
        ];

        return new Phalcon\Db\Adapter\Pdo\Postgresql($params);
    }
);

$container->setShared(
    'transport',
    function () use ($config) {
        switch ($config->app->transport) {
                // Transport XMPP
            case 'xmpp':
                return new \Phalcon\XMPPHP\XmppTransport(
                    [
                        'host'       => $config->xmpp->host,
                        'port'       => $config->xmpp->port,
                        'username'   => $config->xmpp->username,
                        'password'   => $config->xmpp->password,
                        'resource'   => $config->xmpp->resource
                    ]
                );
                // Transport Mail
            case 'mail':
                // Swift autoload
                include APP_PATH . './library/Swiftmailer/swift_required.php';
                return new \Phalcon\Mailer\Manager(
                    [
                        'driver'     => $config->mail->driver,
                        'host'       => $config->mail->host,
                        'port'       => $config->mail->port,
                        'encryption' => $config->mail->encryption,
                        'verifypeer' => $config->mail->verifypeer ?? null,
                        'username'   => $config->mail->username,
                        'password'   => $config->mail->password,
                        'from'       => [
                            'email'  => $config->mail->from['email'],
                            'name'   => $config->mail->from['name'],
                        ],
                        'bcc'       => [
                            'email'  => $config->mail->bcc['email'] ?? null
                        ],
                    ]
                );
        }
    }
);

/**
 * Logger is created based in the parameters defined in the configuration file
 */
$container->setShared(
    'logger',
    function () use ($config) {
        $filename = BASE_PATH . ($config->logger->filename ?? '/logs/main.log');
        $adapter = new Phalcon\Logger\Adapter\Stream($filename);
        $logger  = new Phalcon\Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );

        $logger->setLogLevel($config->logger->level ?? Phalcon\Logger::INFO);

        return $logger;
    }
);

/**
 * Cron is created based in the parameters defined in the configuration file
 */
$container->setShared(
    'cron',
    function () use ($config) {
        $cron = new Sid\Phalcon\Cron\Manager();

        foreach ($config->task->toArray() as $task => $time) {
            $cron->add(
                new Sid\Phalcon\Cron\Job\Phalcon(
                    $time,
                    ['task' => 'father', 'params' => [$task]]
                )
            );
        }

        return $cron;
    }
);

/**
 * Locale is created based in the parameters defined in the configuration file
 */
$container->set('locale', (new \Robot\Config\Locale())->getTranslator());
