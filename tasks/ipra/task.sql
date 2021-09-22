SELECT 
  org."SHORTNAME" AS "ExamBuroName",
  "examAct"."Number",
  p."SNILS",
  to_char(concl."DecisionDate", 'dd.mm.yyyy') AS "DecisionDate"
FROM "ExaminationExpDoc" expdoc
JOIN "Examination" exam ON exam."Id" = expdoc."ExaminationId"
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId" 
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId" 
JOIN "ExaminationConclusion" concl ON concl."ExaminationId" = exam."Id" 
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN LATERAL (
  SELECT doc."Number"
  FROM "ExaminationExpDoc" doc
  WHERE doc."ExaminationId" = exam."Id" AND (doc."ExpDocTypeId" = g."ActTypeId" OR doc."ExpDocTypeId" = 0)
  ORDER BY doc."CreateTime" DESC
LIMIT 1) "examAct" ON true
WHERE expdoc."ExpDocTypeId" IN (528,529) 
  AND expdoc."IssueDate" IS NULL
  AND expdoc."CreateTime" BETWEEN current_date - '1 month'::INTERVAL AND current_timestamp