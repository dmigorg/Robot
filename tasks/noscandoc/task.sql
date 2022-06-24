SELECT
  org."SHORTNAME", p."SNILS",
  COALESCE(protocol.num, 'n/a') AS protocol,
  to_char(exam."ExamTime",'dd.mm.yyyy') AS "ExamTime"
FROM "Examination" exam
JOIN "ExaminationConclusion" concl ON concl."ExaminationId"  = exam."Id"
JOIN "ExaminationPointsOfOrderMse" AS points ON exam."Id" = points."ExaminationID"
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN LATERAL (
  SELECT doc."Number"
  FROM "ExaminationExpDoc" doc
  WHERE doc."ExaminationId" = exam."Id" 
    AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100)) 
  LIMIT 1
) AS protocol(num) ON TRUE
WHERE 
  -- документы выданы и экспертиза не прекращена
  (exam."StateId" = 2 AND  exam."DocsIssued" = TRUE AND exam."IsStopped" = FALSE)
  -- только бюро и ЭС обжалования 
  AND (points."ExamPointsOfOrderMseID" IS NULL OR points."ExamPointsOfOrderMseID" = 1)
  -- исключаем период инвалидности/проценты 6 месяцев AND exam."IsCovid19Prolongation" IS NULL
  AND EXISTS (
    SELECT 1
    FROM "ExaminationExpDoc" eed
    WHERE eed."ExaminationId" = exam."Id" AND eed."ExpDocTypeId" = 15 
    AND (
      eed."ExpDocTypeOther" LIKE '%Направление на медико-социальную экспертизу%'
      OR eed."ExpDocTypeOther" LIKE '%Заявление%'
      OR eed."ExpDocTypeOther" LIKE '%Справка об отказе в направлении на медико-социальную экспертизу%'
    )
  )
  
  -- исключаем экспертизы на изменение
  AND NOT EXISTS(
    SELECT 1 FROM "ExaminationPurpose" t1 
    WHERE t1."ExaminationPurposeID" = 19 AND t1."ExaminationID" = exam."Id"
  )
  -- только свой узел
  AND exam."ExamBuroId" = ANY (
    SELECT o."ORGANIZATION_ID"
    FROM "DicOrganization" o
    WHERE o."PARENT_ORGANIZATION_ID" IN (SELECT s."SettingValue"::int 
    FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID')
  )
  -- нет прикрепленной скан-копии
  AND EXISTS (
    SELECT 1 
    FROM "ExaminationExpDoc" eed 
    LEFT JOIN "ExaminationExpDocFiles" eedf ON eedf."ExaminationExpDocId" = eed."Id" 
    WHERE eed."ExpDocTypeId"= 15 AND eed."ExaminationId" = exam."Id" AND eedf."FileStorageFileID" IS NULL
  )
  AND exam."ExamTime" BETWEEN current_date - INTERVAL '1 days' AND current_date + INTERVAL '1 day'
ORDER BY org."ORGANIZATION_TYPE_ID", org."Number"
