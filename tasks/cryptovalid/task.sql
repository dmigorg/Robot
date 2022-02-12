SELECT
    CASE doc."DocFlowStageId" WHEN 1 THEN 'Единая регистратура' ELSE org."SHORTNAME"  END AS stage,
    to_char(doc."CreateTime", 'dd.mm.yyyy hh24:mi') AS createtime,
    p."SNILS" AS snils,
    rd."RefferalOrgName" AS orgname
FROM "Document" doc  
JOIN "Request" req ON req."DocumentID" = doc."ID"
JOIN "RequestDetail" rd ON rd."ID" = req."RequestDetailID" 
JOIN "Person" p ON p."PersonID" = COALESCE(req."RepresentativeID", req."RequesterID") 
LEFT JOIN (
   SELECT *
   FROM dblink(
       'host=localhost port=5432 dbname=Remd user=mse_user password=123mse123',
        'SELECT id, cryptovalid 
        FROM remd
        WHERE document_id IS NOT NULL 
        AND export_time2 BETWEEN current_date - INTERVAL ''1 days'' AND current_date + INTERVAL ''1 day''
   ')
   AS remd(id uuid, cryptovalid bool)
) AS remd ON remd.id = req."GCode"::uuid
LEFT JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = doc."DocFlowStageId" AND doc."DocFlowStageId" != 1
WHERE req."RequestTypeID" = 5
    AND doc."RegNumber" IS NULL 
    AND doc."DocFlowStageId" != -1
    AND remd.cryptovalid = FALSE
ORDER BY doc."DocFlowStageId"