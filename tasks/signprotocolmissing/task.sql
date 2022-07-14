SELECT --exam."Id",
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
  SELECT COUNT(1)
  FROM "ExaminationSpecialistData" esd
  JOIN "RDSpecialist" rs ON rs."Id" = esd."SpecialistId"
  WHERE esd."ExaminationId" = exam."Id"
) AS "User"("Count") ON TRUE 
LEFT JOIN LATERAL (
    SELECT COUNT(1)
    -- u."LastName", u."Name", u."SecondName" 
  FROM "ExaminationExpDocFiles" AS eedf
  JOIN "ExaminationExpDoc" eed ON eed."Id" = eedf."ExaminationExpDocId" 
  LEFT JOIN LATERAL (
    SELECT "SignUserId" FROM "FileStorage" WHERE eedf."FileStorageFileID" = "FileID"  
    UNION ALL
     SELECT "SignUserId" FROM "FileSignature" WHERE eedf."FileStorageFileID" = "FileId"
  ) AS "SignUser"("Id") ON TRUE
  LEFT OUTER JOIN "User" AS u ON "SignUser"."Id" = u."Id"
  WHERE (eed."ExpDocTypeId" = 10 OR eed."ExpDocTypeId" = (10 + g."Id" * 100))
    AND eed."ExaminationId" = exam."Id"
) AS "SignUser"("Count") ON TRUE 
WHERE (exam."StateId" = 2 AND exam."DocsIssued" = TRUE) AND concl."DecisionDate"::date = current_date
    -- Только свой узел
  AND exam."ExamBuroId" = ANY(
    SELECT o."ORGANIZATION_ID"
    FROM "DicOrganization" o
    WHERE o."PARENT_ORGANIZATION_ID" = (SELECT s."SettingValue"::int 
      FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID'
  ))
  AND "User"."Count" != "SignUser"."Count"