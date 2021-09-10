<?php
declare(strict_types=1);

class RemdTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $subject = 'Необработанные электронные направления';
        $header = 'Стадия; Дата поступления; СНИЛС; МО';
        
        $message = $this->transport->createMessage();
        $message->subject($subject, $header);
        $message->content($this->compute());
        
        // Send message
        if($message->send()) 
            echo 'RemdTask success send';
        else 
            echo 'RemdTask empty data';
    }

    private function compute()
    {
        $query = <<<EOD
        SELECT
            CASE rlv."StageId" WHEN 1 THEN 'Единая регистратура' ELSE rlv."Stage" END AS stage,
            to_char(rlv."CreateTime", 'dd.mm.yyyy hh24:mi') AS createtime,
            rlv."CorrPersonSnils" AS snils,
            rlv."RefferalOrgName" AS orgname
        FROM "RequestListView" rlv 
        WHERE rlv."RequestType" = 5
            AND rlv."RegNumber" IS NULL 
            AND rlv."StageId" != -1
        ORDER BY rlv."StageId" 
        EOD;

        $data =  $this->db->fetchAll($query, \Phalcon\Db\Enum::FETCH_NUM);

        return $data;
    }
}
