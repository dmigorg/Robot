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