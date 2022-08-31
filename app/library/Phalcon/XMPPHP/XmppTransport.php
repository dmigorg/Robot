<?php

namespace Phalcon\XMPPHP;

/**
 * XmppTransport
 */
class XmppTransport
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $resource;
    protected $recipient;
    protected $content;
    protected $subject;
    protected $header;

    protected XMPP $conn;

    /**
     * __construct
     *
     * @param array config
     *
     * @return void
     */
    public function __construct(array $config)
    {
        $this->configure($config);
    }

    /**
     * __destruct
     *
     * @return void
     */
    public function __destruct()
    {
        $this->conn->disconnect();
    }

    protected function configure(array $config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->resource = $config['resource'] ?? 'robot';
    }

    public function createMessage()
    {
        try {
            $this->conn = new XMPP($this->host, $this->port, $this->username, $this->password, $this->resource);
            $this->conn->connect();
            $this->conn->processUntil('session_start');
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $this;
    }

    public function recipient($to)
    {
        $this->recipient = $to;
        return $this;
    }

    public function send()
    {
        $result = false;
        if (!empty($this->content)) {
            $result = $this->conn->message($this->recipient, $this->content, 'chat', $this->subject);
        }
        return $result !== false;
    }

    public function header($header)
    {
        $this->header = $header;
    }

    public function subject($subject)
    {
        $this->subject = $subject . " ($this->header)";
    }

    public function content($data = [])
    {
        $result = '';
        foreach ($data as $row) {
            $result .=  implode(' ', $row) . "\n";
        }
        if (!empty($result)) {
            $result = "$this->subject \n\r $result";
        }
        $this->content = $result;
    }
}
