<?php
declare(strict_types=1);

class RequestTask extends \Phalcon\Cli\Task
{
    public function mainAction()
    {
        $subject = 'Незакрытые направления/заявления';
        $header = 'Подразделение; Дата регистрации; СНИЛС; Тип документа';

        $message = $this->transport->createMessage();
        $message->subject($subject, $header);
        $message->content($this->compute());
        
        // Send message
        if($message->send()) 
            echo 'RequestTask success send';
        else 
            echo 'RequestTask empty data';
    }

    private function compute()
    {
        $query = <<<EOD
        SELECT 
         org."SHORTNAME" AS orgname,
         to_char(doc."RegDate", 'dd.mm.yyyy') AS regdate,
         p."SNILS" AS snils,
         ddt."Value" AS doctype
        FROM "Document" doc
        JOIN "Request" req ON req."DocumentID" = doc."ID" 
        JOIN "Person" p ON p."PersonID" = req."RequesterID"
        JOIN "ExaminationRecord" er ON er."OriginRequestId" = req."DocumentID" 
        JOIN "ExaminationRecordExamination" ere ON ere."ExaminationRecordId" = er."Id"
        JOIN "Examination" exam ON exam."Id" = ere."ExaminationId" 
        JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId" 
        JOIN "DicDocumentType" ddt ON ddt."ID" = doc."DocumentTypeID" 
        WHERE exam."DocsIssued" = TRUE AND doc."DocFlowStageId" <> -1 
        EOD;

        return $this->db->fetchAll($query, \Phalcon\Db\Enum::FETCH_NUM);
    }
}
