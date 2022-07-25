SELECT --exam."Id",
 org."SHORTNAME", p."SNILS", 
 COALESCE(protocol.num, 'n/a') AS num,
 to_char(concl."DecisionDate",'dd.mm.yyyy') AS "DecisionDate"
FROM "Examination" exam
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId" 
JOIN "ExaminationConclusion" concl ON concl."ExaminationId" = exam."Id" 
LEFT JOIN LATERAL (
  SELECT files."CertThumbprint"
  FROM "ExaminationExpDoc" doc
  JOIN "ExaminationExpDocFiles" eedf ON eedf."ExaminationExpDocId" = doc."Id" 
  JOIN "FileStorage" files ON files."FileID" = eedf."FileStorageFileID" 
  WHERE doc."ExaminationId" = exam."Id"
    -- 8 - скан справки МСЭ; 15 - документы-основание; 30 - Лист информирования гражданина
    AND "ExpDocTypeId" NOT IN (8, 15, 30)
  ORDER BY files."UploadTime" DESC
  LIMIT 1
) files ON (TRUE)
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN LATERAL (
  SELECT doc."Number"
  FROM "ExaminationExpDoc" doc
  WHERE doc."ExaminationId" = exam."Id" 
    AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100)) 
  LIMIT 1
) AS protocol(num) ON TRUE
WHERE (exam."StateId" = 2 AND exam."DocsIssued" = TRUE)
  -- Только свой узел
  AND exam."ExamBuroId" = ANY(
    SELECT o."ORGANIZATION_ID"
    FROM "DicOrganization" o
    WHERE o."PARENT_ORGANIZATION_ID" = (SELECT s."SettingValue"::int 
      FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID'
  ))
  AND concl."DecisionDate" BETWEEN current_date - INTERVAL '1 days' AND current_date + INTERVAL '1 day'
  AND files."CertThumbprint" IS NULL
GROUP BY exam."Id", org."SHORTNAME", concl."DecisionDate", p."SNILS", COALESCE(protocol.num, 'n/a')