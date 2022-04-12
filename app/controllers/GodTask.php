<?php
declare(strict_types=1);

namespace Robot\Controllers;

use Phalcon\Db\Enum;
use Robot\Library\Helper;

class GodTask extends \Phalcon\Cli\Task
{
    public function mainAction(string $task)
    {
        list($subject, $header, $sender ,$sql) = $this->getParams($task);
        $message = $this->transport->createMessage();
        if(!empty($sender)) $message->sender($sender);
        $message->header($header);
        $message->subject($subject);
        $message->content($this->compute($sql));
        
        // Send message
        if($message->send()) 
            echo "$task task success send";
        else 
            echo "$task task empty data";
    }

    private function compute(string $sql) : array
    {
        return $this->db->fetchAll($sql, Enum::FETCH_NUM);
    }

    private function getParams(string $task) : array
    {
        list($description, $header, $sender) = Helper::getIniTask($task);
        $sql = Helper::getSqlTask($task);
        return [
            $description,
            $header,
            $sender,
            $sql
        ];
    }
}
