WITH rec AS (
  SELECT
    -- exam."Id",
    org."SHORTNAME",
    p."SNILS",
    ( SELECT doc."Number"
      FROM "ExaminationExpDoc" doc
      WHERE doc."ExaminationId" = exam."Id" AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100))
    LIMIT 1) AS "ProtocolNumber",
    COALESCE((date_part('hour', points."StartedDateTime")*60 +date_part('minute', points."StartedDateTime")) 
  - (date_part('hour', points."ArrivedDateTime")*60 + date_part('minute', points."ArrivedDateTime")), 0) AS delta
  FROM "Examination" AS exam
  JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
  JOIN "ExaminationPointsOfOrderMse" points ON exam."Id" = points."ExaminationID"
  JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
  LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
  WHERE exam."IsStarted" = TRUE 
    AND (points."IsInPresence" IS NULL OR points."IsInPresence" = TRUE)
    AND points."ExaminationPlaceID" = 1
    AND (NOT EXISTS(
      SELECT 1 FROM "ExaminationPurpose" t1 
      WHERE t1."ExaminationPurposeID" IN (19) AND t1."ExaminationID" = exam."Id"))
    AND EXISTS (
      SELECT 1 
      FROM "ExaminationRecord" r
      LEFT JOIN "ExaminationRecordExamination" er ON er."ExaminationRecordId" = r."Id"
      WHERE er."ExaminationId" = exam."Id" AND r."WasAppeared" = TRUE
    )
    AND exam."ExamTime" BETWEEN current_date - '1 month'::INTERVAL AND current_timestamp
)
SELECT
  "SHORTNAME",
  "SNILS",
  "ProtocolNumber",
  delta AS "minute"
FROM rec
WHERE delta NOT BETWEEN 0 AND 15