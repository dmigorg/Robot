SELECT --exam."Id"
  org."SHORTNAME" AS "SHORTNAME", 
  COALESCE(p."SNILS",'нет') AS "SNILS",
  COALESCE(protocol.num, 'n/a') AS num,
  to_char(concl."DecisionDate",'dd.mm.yyyy') AS "DecisionDate",
  "User"."Count" - "SignUser"."Count" AS miss_sign
FROM "Examination" exam
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
JOIN "ExaminationConclusion" concl ON concl."ExaminationId" = exam."Id"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN LATERAL (
  SELECT doc."Number"
  FROM "ExaminationExpDoc" doc
  WHERE doc."ExaminationId" = exam."Id" 
    AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100)) 
  LIMIT 1
) AS protocol(num) ON TRUE
LEFT JOIN LATERAL (
  SELECT count(1)
  FROM (
    SELECT 1
    FROM (
      SELECT rs."LastName", rs."FirstName", rs."SecondName" 
      FROM "ExaminationSpecialistData" esd
      JOIN "RDSpecialist" rs ON rs."Id" = esd."SpecialistId" 
      WHERE esd."ExaminationId" = exam."Id"    
      UNION ALL
      SELECT rs."LastName", rs."FirstName", rs."SecondName" 
      FROM "ExaminationConclusion" ec
      JOIN "RDSpecialist" rs ON rs."Id" = ec."BuroManagerId" 
      WHERE ec."ExaminationId" = exam."Id"
      UNION ALL
      SELECT rs."LastName", rs."FirstName", rs."SecondName" 
      FROM "ExaminationConclusion" ec
      JOIN "RDSpecialist" rs ON rs."Id" = ec."ExpSostavManagerId" 
      WHERE ec."ExaminationId" = exam."Id"
    ) t
    GROUP BY t."LastName", t."FirstName", t."SecondName"
  ) t
) AS "User"("Count") ON true
LEFT JOIN LATERAL (
  SELECT file."FileID"
  FROM "ExaminationExpDoc" eed
  JOIN "ExaminationExpDocFiles" AS eedf ON eed."Id" = eedf."ExaminationExpDocId"
  JOIN "FileStorage" AS file ON file."FileID" = eedf."FileStorageFileID"
  WHERE (eed."ExpDocTypeId" = 10 OR eed."ExpDocTypeId" = (10 + g."Id" * 100)) AND eed."ExaminationId" = exam."Id"
  ORDER BY file."UploadTime" desc
  LIMIT 1
) AS "File"("Id") ON TRUE
LEFT JOIN LATERAL (
  SELECT COUNT(1)
  FROM (
    SELECT 1
    FROM "User" u
    LEFT JOIN lateral (
      SELECT "SignUserId" FROM "FileStorage" WHERE "FileID" = "File"."Id"
      UNION ALL
       SELECT "SignUserId" FROM "FileSignature" WHERE "FileId" = "File"."Id"
    ) AS "SignUser"("Id") ON TRUE
    WHERE "SignUser"."Id" = u."Id"
    GROUP BY u."LastName", u."Name", u."SecondName"
  ) t
) AS "SignUser"("Count") ON TRUE
WHERE (exam."StateId" = 2 AND exam."DocsIssued" = TRUE)
    -- Только свой узел
  AND exam."ExamBuroId" = ANY (
    SELECT o."ORGANIZATION_ID"
    FROM "DicOrganization" o
    WHERE o."PARENT_ORGANIZATION_ID" = (SELECT s."SettingValue"::int 
      FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID')
  )
  AND "User"."Count" != "SignUser"."Count"
  AND concl."DecisionDate" BETWEEN current_date - INTERVAL '1 days' AND current_date + INTERVAL '1 day'
  