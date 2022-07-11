SELECT
  org."SHORTNAME" AS orgname,
  p."SNILS" AS snils,
  doc."RegNumber" AS num,
  to_char(doc."RegDate", 'dd.mm.yyyy') AS regdate
FROM "Document" doc
JOIN "Request" req ON req."DocumentID" = doc."ID"
JOIN "Person" p ON p."PersonID" = req."RequesterID"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = COALESCE(NULLIF(doc."DocFlowStageId", -1), doc."PrevDocFlowStageId")
JOIN "ExaminationRecord" er ON er."OriginRequestId" = doc."ID" 
WHERE
  -- по дате получения направления МСЭ(Э)
  (doc."CreateTime" >= '20220701' AND doc."CreateTime" BETWEEN current_date - '1 month'::INTERVAL AND current_timestamp)
  AND req."RequestTypeID" IN (4,5)
  AND req."IsInPresence" IS NULL
  AND doc."RegNumber" IS NOT NULL
ORDER BY org."Number", doc."RegDate" DESC