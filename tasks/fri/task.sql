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
AND sev."DecisionDate" BETWEEN current_date - '1 week'::INTERVAL AND current_date - '27 hour'::INTERVAL 
AND (expdoc.val && '{528, 529}'
    OR expdoc.val && '{5, 38, 40, 45}'
    OR  (expdoc.val && '{3}' AND sev."PrevExamInvalidityGroupId" IN (1,2,3))
)
-- только свой узел
AND sev."ExamBuroId" IN (
  SELECT o."ORGANIZATION_ID"
  FROM "DicOrganization" o
  WHERE o."PARENT_ORGANIZATION_ID" IN (SELECT s."SettingValue"::int 
  FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID')
)
ORDER BY sev."ExamBuroId", sev."DecisionDate" DESC