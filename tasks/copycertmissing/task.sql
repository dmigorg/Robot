SELECT --exam."Id",
  org."SHORTNAME", p."SNILS",
  COALESCE(protocol.num, 'n/a') AS num,
  to_char(concl."DecisionDate",'dd.mm.yyyy') AS "DecisionDate"
FROM "Examination" exam
JOIN "ExaminationConclusion" concl ON concl."ExaminationId"  = exam."Id"
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
LEFT JOIN LATERAL (
    SELECT ARRAY_AGG(expdoc."ExpDocTypeId")
    FROM "ExaminationExpDoc" expdoc
    WHERE expdoc."ExaminationId" = exam."Id" 
) AS expdoc(id) ON TRUE
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN LATERAL (
  SELECT doc."Number"
  FROM "ExaminationExpDoc" doc
  WHERE doc."ExaminationId" = exam."Id" 
    AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100)) 
  LIMIT 1
) AS protocol(num) ON TRUE
WHERE exam."StateId" = 2
  AND concl."DecisionDate" BETWEEN current_date - INTERVAL '1 days' AND current_date + INTERVAL '1 day'
  -- есть выписка/справка
  AND expdoc.id && '{5, 7, 38, 39, 45, 46, 40, 41}'
  AND (
    -- нет сканированной копии справки
    NOT(expdoc.id && '{8}')
    -- нет прикрепленной скан-копии
    OR NOT EXISTS (
    SELECT 1 
    FROM "ExaminationExpDoc" eed 
    JOIN "ExaminationExpDocFiles" eedf ON eedf."ExaminationExpDocId" = eed."Id" 
    WHERE eed."ExpDocTypeId"= 8 AND eed."ExaminationId" = exam."Id"
    )
  )
ORDER BY org."Number", concl."DecisionDate"