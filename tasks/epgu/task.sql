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