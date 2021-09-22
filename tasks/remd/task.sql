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