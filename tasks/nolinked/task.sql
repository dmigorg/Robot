SELECT --doc."ID",
-- Стадия
 CASE doc."PrevDocFlowStageId" WHEN 1 
  THEN 'Единая регистратура' 
  ELSE CASE WHEN org."ORGANIZATION_TYPE_ID" = 4 THEN 'Бюро №' || org."Number" ELSE 'ЭС №' || org."Number" - 100 END 
 END AS stage,
-- Дата поступления
 to_char(doc."CreateTime", 'dd.mm.yyyy hh24:mi') AS createtime,
 -- СНИЛС
 p."SNILS",
 -- МО
 rd."RefferalOrgName"
FROM "Request" req
JOIN "Document" doc ON req."DocumentID" = doc."ID"
JOIN "RequestDetail" rd ON rd."ID" = req."RequestDetailID"
JOIN "Person" AS p ON p."PersonID" = COALESCE(req."RepresentativeID", req."RequesterID")
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = doc."PrevDocFlowStageId"
LEFT JOIN LATERAL (
    SELECT TRUE
    FROM "Document" d 
    JOIN "DocumentRelations" dr ON dr."ParentDocID" = d."ID" OR dr."ChildDocID" = d."ID"
    WHERE dr."ChildDocID" = doc."ID" OR dr."ParentDocID" = doc."ID"
  LIMIT 1
) AS relations(val) ON (TRUE)
LEFT JOIN "ExaminationRecord" er ON er."OriginRequestId" = doc."ID" 
LEFT JOIN "JournalTFOMS" tfoms ON tfoms."RequestId" = req."DocumentID"
WHERE req."RequestTypeID" IN(4, 5)
  -- мед.орг
  AND rd."RefferalOrgTypeID" = 1
  -- стадия закрыто
  AND doc."DocFlowStageId" = -1
  -- нет записи
  AND er."Id" IS NULL
  -- нет связанных документов 
  AND relations.val IS NULL
  -- нет в журнале ТФОМС
  AND tfoms."Id" IS NULL
  AND doc."CreateTime" > '20211201'
  ORDER BY org."Number", doc."CreateTime"