<?php
declare(strict_types=1);

namespace Robot\Task;

class FriTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $subject = 'Данные не переданные во ФРИ';
        $header = 'Подразделение; СНИЛС; Номер протокола; Дата вынесения решения; Количество в днях';

        $message = $this->transport->createMessage();
        $message->subject($subject, $header);
        $message->content($this->compute());
        
        // Send message
        if($message->send()) 
            echo 'FriTask success send';
        else 
            echo 'FriTask empty data';
    }

    private function compute()
    {
        $query = <<<EOD
        SELECT
         sev."ExamBuroName",
         sev."SNILS",
         sev."ProtocolNumber",
         to_char(sev."DecisionDate", 'dd.mm.yyyy') AS "DecisionDate",
         (current_date - sev."DecisionDate"::date)::int AS "CountDay"
        FROM "SearchExamView" sev
        LEFT JOIN LATERAL (
         SELECT (xpath('/list/i/@ExpDocTypeId', sev."ExpDocsXml")::TEXT)::int[]
        ) AS expdoc(val) ON TRUE 
        WHERE (sev."ExamPassedToFriState" = FALSE OR sev."ExamPassedToFriState" IS NULL)
        AND sev."MetaStateId" = 4
        AND sev."DecisionDate" BETWEEN current_date - '1 month'::INTERVAL AND current_date - '27 hour'::INTERVAL 
        AND (expdoc.val && '{528, 529}'
            OR expdoc.val && '{5, 38, 40, 45}'
        )
        -- только свой узел
        AND sev."ExamBuroId" IN (
         SELECT o."ORGANIZATION_ID"
          FROM "DicOrganization" o
          WHERE o."PARENT_ORGANIZATION_ID" IN (SELECT s."SettingValue"::int 
          FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID')
        )
        ORDER BY sev."ExamBuroId", sev."DecisionDate" DESC
        EOD;
        
        return $this->db->fetchAll($query, \Phalcon\Db\Enum::FETCH_NUM);
    }
}
