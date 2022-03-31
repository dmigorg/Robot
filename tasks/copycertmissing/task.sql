SELECT --exam."Id",
  org."SHORTNAME", p."SNILS", to_char(concl."DecisionDate",'dd.mm.yyyy') AS "DecisionDate"
FROM "Examination" exam
JOIN "ExaminationConclusion" concl ON concl."ExaminationId"  = exam."Id"
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
LEFT JOIN LATERAL (
    SELECT ARRAY_AGG(expdoc."ExpDocTypeId")
    FROM "ExaminationExpDoc" expdoc
    WHERE expdoc."ExaminationId" = exam."Id" 
) AS expdoc(id) ON TRUE
WHERE exam."StateId" = 2
  AND concl."DecisionDate" BETWEEN current_date - INTERVAL '1 days' AND current_date + INTERVAL '1 day'
  -- есть выписка/справка
  AND expdoc.id && '{5, 7, 38, 39, 45, 46, 40, 41}'
  -- нет сканированной копии справки
  AND NOT (expdoc.id && '{8}')
  -- нет прикрепленной скан-копии
  AND NOT EXISTS (
    SELECT 1 
    FROM "ExaminationExpDoc" eed 
    JOIN "ExaminationExpDocFiles" eedf ON eedf."ExaminationExpDocId" = eed."Id" 
    WHERE eed."ExpDocTypeId"= 8 AND eed."ExaminationId" = exam."Id"
  )