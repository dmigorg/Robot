<?php

namespace Phalcon\XMPPHP;

class XmppTransport
{
    protected $host;
    protected $port;
    protected $username;
    protected $password;
    protected $resource;
    protected $sender;
    protected $content;
    protected $subject;
    protected $header;

    protected $conn;

    public function __construct(array $config)
    {
        $this->configure($config);
    }

    function __destruct() {
        $this->conn->disconnect();
    }

    protected function configure(array $config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->resource = $config['resource'] ?? 'robot';
        $this->sender = $config['sender'];
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

    public function sender($to)
    {
        $this->sender = $to;
        return $this;
    }

    public function send()
    {
        if(!empty($this->content)){
            $this->conn->message($this->sender, $this->content, 'chat', $this->subject);
            return true;
        }
        
        return false;
    }

    public function header($header)
    {
        $this->header = $header;
    }

    public function subject($subject)
    {
        $this->subject = $subject." ($this->header)";
    }

    public function content($data = []){

        $result = '';
        foreach($data as $row){
            $result .=  implode(' ', $row)."\n";
        }

        if(!empty($result)) $result = "$this->subject \n\r $result";

        $this->content = $result;
    }
}