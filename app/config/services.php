<?php
declare(strict_types=1);

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return new Phalcon\Config\Adapter\Ini(BASE_PATH . '/config.ini');
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $params = [
        'host'     => $config->db->host,
        'username' => $config->db->username,
        'password' => $config->db->password,
        'dbname'   => $config->db->dbname,
        'port'  => $config->db->port
    ];

    return new Phalcon\Db\Adapter\Pdo\Postgresql($params);
});

$di->setShared(
    'transport',
    function () {
        $config = $this->getConfig();
        switch ($config->app->transport) {
            // Transport XMPP
            case 'xmpp':
                return new \Phalcon\XMPPHP\XmppTransport(
                    [
                    'host'       => $config->xmpp->host,
                    'port'       => $config->xmpp->port,
                    'username'   => $config->xmpp->username,
                    'password'   => $config->xmpp->password,
                    'resource'   => $config->xmpp->resource,
                    'sender'     => $config->xmpp->sender
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
                        'username'   => $config->mail->username,
                        'password'   => $config->mail->password,
                        'from'       => [
                            'email'  => $config->mail->from['email'],
                            'name'   => $config->mail->from['name'],
                        ],
                        'to'       => [
                            'email'  => $config->mail->to['email']
                        ],
                        'bcc'       => [
                            'email'  => $config->mail->bcc['email'] ?? null
                        ],
                    ]
                );
        }
    }
);

$di->set(
    "cron",
    function () {
        $config = $this->getConfig();
        $cron = new Sid\Phalcon\Cron\Manager();

        foreach($config->task->toArray() as $task => $time)
        {
            $cron->add(
                new Sid\Phalcon\Cron\Job\Phalcon(
                    $time, ["task" => $task]
                )
            );
        }

        return $cron;
    }
);
