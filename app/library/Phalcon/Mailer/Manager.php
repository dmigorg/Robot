<?php
/**
 * Manager.php 2014-08-31 04:11
 * ----------------------------------------------
 *
 * @author      Stanislav Kiryukhin <korsar.zn@gmail.com>
 * @copyright   Copyright (c) 2014-2021
 *
 * ----------------------------------------------
 * All Rights Reserved.
 * ----------------------------------------------
 */
namespace Phalcon\Mailer;

use Phalcon\Config;
use Phalcon\Mvc\View;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Events\ManagerInterface;
use Phalcon\Events\EventsAwareInterface;

/**
 * Class Manager
 */
class Manager extends Injectable implements EventsAwareInterface
{
    const AUTHENTICATION_MODE_CRAM_MD5  = 'CRAM-MD5';
    const AUTHENTICATION_MODE_LOGIN     = 'LOGIN';
    const AUTHENTICATION_MODE_NTLM      = 'NTLM';
    const AUTHENTICATION_MODE_PLAIN     = 'PLAIN';
    const AUTHENTICATION_MODE_OAUTH     = 'XOAUTH2';

    const ENCRYPTION_NONE = '';
    const ENCRYPTION_SSL  = 'ssl';
    const ENCRYPTION_TLS  = 'tls';

    /**
     * @var ManagerInterface
     */
    protected $eventsManager;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var \Swift_Transport
     */
    protected $transport;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Phalcon\Mvc\View\Simple
     */
    protected $view;

    /**
     * Create a new MailerManager component using $config for configuring
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    /**
     * Returns the internal event manager
     * @return ManagerInterface
     */
    public function getEventsManager(): ?ManagerInterface {
        return $this->eventsManager;
    }

    /**
     * Sets the events manager
     * @return void
     */
    public function setEventsManager(ManagerInterface $eventsManager): void {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Returns the internal dependency injector
     *
     * @return \Phalcon\DiInterface
     */
    public function getDI(): DiInterface
    {
        if (!($di = parent::getDI()) && !($di instanceof DiInterface)) {
            throw new \RuntimeException('A dependency injection object is required to access internal services');
        }

        return $di;
    }

    /**
     * Create a new Message instance.
     *
     * Events:
     * - mailer:beforeCreateMessage
     * - mailer:afterCreateMessage
     *
     * @return \Phalcon\Mailer\Message
     */
    public function createMessage()
    {
        $eventsManager = $this->getEventsManager();

        if ($eventsManager) {
            $eventsManager->fire('mailer:beforeCreateMessage', $this);
        }

        /** @var $message Message */
        $message = $this->getDI()->get('\Phalcon\Mailer\Message', [$this]);

        if (($from = $this->getConfig('from'))) {
            $message->from($from['email'], isset($from['name']) ? $from['name'] : null);
        }

        if (($to = $this->getConfig('to'))) {
            $message->to($to['email'], isset($to['name']) ? $to['name'] : null);
        }

        if (($bcc = $this->getConfig('bcc'))) {
            $message->bcc($bcc['email'], isset($bcc['name']) ? $bcc['name'] : null);
        }

        if ($eventsManager) {
            $eventsManager->fire('mailer:afterCreateMessage', $this, [$message]);
        }

        return $message;
    }

    /**
     * Create a new Message instance.
     * For the body of the message uses the result of render of view
     *
     * Events:
     * - mailer:beforeCreateMessage
     * - mailer:afterCreateMessage
     *
     * @param string $view
     * @param array $params optional
     * @param null|string $viewsDir optional
     *
     * @return \Phalcon\Mailer\Message
     *
     * @see \Phalcon\Mailer\Manager::createMessage()
     */
    public function createMessageFromView($view, $params = [], $viewsDir = null)
    {
        $message = $this->createMessage();
        $message->content($this->renderView($view, $params, $viewsDir), $message::CONTENT_TYPE_HTML);

        return $message;
    }

    /**
     * Return a {@link \Swift_Mailer} instance
     *
     * @return \Swift_Mailer
     */
    public function getSwift()
    {
        if (!$this->isInitSwiftMailer()) {
            $this->registerSwiftMailer();
        }

        return $this->mailer;
    }

    /**
     * Normalize IDN domains.
     *
     * @param $email
     *
     * @return string
     *
     * @see \Phalcon\Mailer\Manager::punycode()
     */
    public function normalizeEmail($email)
    {
        if (preg_match('#[^(\x20-\x7F)]+#', $email)) {

            list($user, $domain) = explode('@', $email);

            return $user . '@' . $this->punycode($domain);

        } else {
            return $email;
        }
    }

    /**
     * Configure MailerManager class
     *
     * @param array $config
     *
     * @see \Phalcon\Mailer\Manager::registerSwiftTransport()
     * @see \Phalcon\Mailer\Manager::registerSwiftMailer()
     */
    protected function configure(array $config)
    {
        $this->config = $config;
    }

    /**
     * Create a new Driver-mail of SwiftTransport instance.
     *
     * Supported driver-mail:
     * - smtp
     * - sendmail
     * - mail
     *
     */
    protected function registerSwiftTransport()
    {
        switch ($driver = $this->getConfig('driver')) {
            case 'smtp':
                $this->transport = $this->registerTransportSmtp();
                break;

            case 'mail':
                $this->transport = $this->registerTransportMail();
                break;

            case 'sendmail':
                $this->transport = $this->registerTransportSendmail();
                break;

            default:
                throw new \InvalidArgumentException(sprintf('Driver-mail "%s" is not supported', $driver));
        }
    }

    /**
     * Create a new SmtpTransport instance.
     *
     * @return \Swift_SmtpTransport
     *
     * @see \Swift_SmtpTransport
     */
    protected function registerTransportSmtp()
    {
        $config = $this->getConfig();

        /** @var $transport \Swift_SmtpTransport: */
        $transport = $this->getDI()->get('\Swift_SmtpTransport');

        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
            // Require verification of SSL certificate used.
            if (($verifypeer = $this->getConfig('verifypeer')) !== null) {
				$transport->setStreamOptions([
                    $config['encryption'] => ['verify_peer' => (boolean)$verifypeer]
                ]);
			}
        }

        if (isset($config['host'])) {
            $transport->setHost($config['host']);
        } else {
            $transport->setHost('localhost');
        }

        if (isset($config['port'])) {
            $transport->setPort($config['port']);
        } else {
            switch ($transport->getEncryption()) {
                case static::ENCRYPTION_SSL:
                    $transport->setPort(465);
                    break;

                case static::ENCRYPTION_TLS:
                    $transport->setPort(587);
                    break;

                default:
                    $transport->setPort(25);
            }
        }

        if (isset($config['auth_mode'])) {
            $transport->setAuthMode($config['auth_mode']);
        }

        if (isset($config['username'])) {
            $transport->setUsername($this->normalizeEmail($config['username']));
        }

        if (isset($config['password'])) {
            $transport->setPassword($config['password']);
        }

        return $transport;
    }

    /**
     * Get option config or the entire array of config, if the parameter $key is not specified.
     *
     * @param null $key
     * @param null $default
     *
     * @return string|array
     */
    protected function getConfig($key = null, $default = null)
    {
        if ($key !== null) {
            if (isset($this->config[$key])) {
                return $this->config[$key];
            } else {
                return $default;
            }

        } else {
            return $this->config;
        }
    }

    /**
     * Convert UTF-8 encoded domain name to ASCII
     *
     * @param $str
     *
     * @return string
     */
    protected function punycode($str)
    {
        if (function_exists('idn_to_ascii')) {
            return idn_to_ascii($str);
        } else {
            return $str;
        }
    }

    /**
     * Create a new MailTransport instance.
     *
     * @return \Swift_MailTransport
     *
     * @see \Swift_MailTransport
     */
    protected function registerTransportMail()
    {
        return $this->getDI()->get('\Swift_MailTransport');
    }

    /**
     * Create a new SendmailTransport instance.
     *
     * @return \Swift_SendmailTransport
     *
     * @see \Swift_SendmailTransport
     */
    protected function registerTransportSendmail()
    {
        /** @var $transport \Swift_SendmailTransport */
        $transport = $this->getDI()->get('\Swift_SendmailTransport')
            ->setCommand($this->getConfig('sendmail', '/usr/sbin/sendmail -bs'));

        return $transport;
    }

    /**
     * Register SwiftMailer
     *
     * @see \Swift_Mailer
     */
    protected function registerSwiftMailer()
    {
        $this->registerSwiftTransport();
        $this->mailer = $this->getDI()->get('\Swift_Mailer', [$this->transport]);
    }

    /**
     * Renders a view
     *
     * @param $viewPath
     * @param $params
     * @param null $viewsDir
     *
     * @return string
     */
    protected function renderView($viewPath, $params, $viewsDir = null)
    {
        $view = $this->getView();

        if ($viewsDir !== null) {
            $view = clone $view;
            $view->setViewsDir($viewsDir);
        }

        if (!$view->getViewsDir()) {
            throw new \RuntimeException('Please set config property "viewsDir"');
        }

        return $view->render($viewPath, $params);
    }

    /**
     * Return a {@link \Phalcon\Mvc\View\Simple} instance
     *
     * @return \Phalcon\Mvc\View\Simple
     */
    protected function getView()
    {
        if ($this->view) {
            return $this->view;
        } else {

            /** @var $viewApp \Phalcon\Mvc\View */
            $viewApp = $this->getDI()->get('view');

            if (!($viewsDir = $this->getConfig('viewsDir'))) {
                $viewsDir = $viewApp->getViewsDir();
            }

            /** @var $view \Phalcon\Mvc\View\Simple */
            $view = $this->getDI()->get('\Phalcon\Mvc\View\Simple');
            $view->setViewsDir($viewsDir);

            if ($engines = $viewApp->getRegisteredEngines()) {
                $view->registerEngines($engines);
            }

            return $this->view = $view;
        }
    }

    /**
     * Check init SwiftMailer
     *
     * @return bool
     */
    protected function isInitSwiftMailer()
    {
        return $this->mailer && $this->transport;
    }

}
