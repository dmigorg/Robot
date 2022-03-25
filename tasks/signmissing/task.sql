SELECT --exam."Id",
 org."SHORTNAME", to_char(concl."DecisionDate",'dd.mm.yyyy') AS "DecisionDate", p."SNILS", COUNT(1) AS cnt
FROM "Examination" exam
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId" 
JOIN "ExaminationConclusion" concl ON concl."ExaminationId" = exam."Id" 
LEFT JOIN LATERAL (
  SELECT files."CertThumbprint", doc."ExpDocTypeId" 
  FROM "ExaminationExpDoc" doc
  JOIN "ExaminationExpDocFiles" eedf ON eedf."ExaminationExpDocId" = doc."Id" 
  JOIN "FileStorage" files ON files."FileID" = eedf."FileStorageFileID" 
  WHERE doc."ExaminationId" = exam."Id" 
    AND "ExpDocTypeId" NOT IN (8, 15)
  ORDER BY files."UploadTime" DESC
  LIMIT 1
) files ON (TRUE)
WHERE (exam."StateId" = 2 AND exam."DocsIssued" = TRUE)
  AND concl."DecisionDate" BETWEEN current_date - INTERVAL '1 days' AND current_date + INTERVAL '1 day'
  AND files."CertThumbprint" IS NULL
GROUP BY exam."Id", org."SHORTNAME", concl."DecisionDate", p."SNILS"