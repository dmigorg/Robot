<?php
declare(strict_types=1);

class EpguTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $subject = 'Необработанные заявления с ЕПГУ';
        $header = 'Стадия; Дата поступления; СНИЛС; Вид услуги';

        $message = $this->transport->createMessage();
        $message->subject($subject, $header);
        $message->content($this->compute());
        
        // Send message
        if($message->send()) 
            echo 'EpguTask success send';
        else 
            echo 'EpguTask empty data';
    }

    private function compute()
    {
        $query = <<<EOD
        SELECT 
         rlv."Stage",
         to_char(rlv."CreateTime" at time zone 'utc' at time zone 'msk', 'dd.mm.yyyy hh24:mi') AS createtime,
         rlv."CorrPersonSnils" AS snils,
         pgu."Value" AS service
        FROM "RequestListView" rlv
        JOIN "DicPguService" AS pgu ON pgu."ID" = rlv."PguServiceId" 
        WHERE
         -- заявления принятые с ЕПГУ
         rlv."RequestType" = 6
         -- Принято ведомством
         AND rlv."PguStateId" = 2
        ORDER BY pgu."Value" , rlv."CreateTime" DESC
        EOD;

        return $this->db->fetchAll($query, \Phalcon\Db\Enum::FETCH_NUM);
    }
}
