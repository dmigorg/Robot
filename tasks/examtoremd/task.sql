SELECT
  org."SHORTNAME",
    p."SNILS",
    (SELECT doc."Number"
      FROM "ExaminationExpDoc" doc
      WHERE doc."ExaminationId" = exam."Id" AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100))
    LIMIT 1) AS "ProtocolNumber",
    to_char(concl."DecisionDate", 'dd.mm.yyyy') AS "DecisionDate"
FROM "Examination" exam
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
JOIN "ExaminationConclusion" concl ON concl."ExaminationId" = exam."Id"
JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN LATERAL (
  SELECT
    remd."Id" AS "BackTicketLpuSemdId",
    remd."CreateTime" AS "BackTicketLpuSemdCreateTime",
    remd."ExportTime" AS "BackTicketLpuExportTime",
    remd."ExportRemdTime" AS "BackTicketLpuExportRemdTime",
    f."CertThumbprint" AS "BackTicketLpuCertThumbprint"
  FROM "ExamRemdDoc" remd
  LEFT JOIN "FileStorage" f ON remd."FileId" = f."FileID"
  WHERE remd."ExaminationId" = exam."Id"
  ORDER BY remd."CreateTime" DESC
  LIMIT 1
) lastremd ON true
WHERE "BackTicketLpuSemdCreateTime" IS NOT NULL
  AND "BackTicketLpuSemdCreateTime" < current_timestamp - interval '24 hours'
  AND "BackTicketLpuExportRemdTime" IS NULL
  AND concl."DecisionDate" > current_timestamp - interval '3 days'
ORDER BY org."Number", concl."DecisionDate"