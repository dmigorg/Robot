WITH rec AS (
  SELECT
    -- exam."Id",
    org."SHORTNAME",
    p."SNILS",
    (
      SELECT doc."Number"
      FROM "ExaminationExpDoc" doc
      WHERE doc."ExaminationId" = exam."Id" AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100))
      LIMIT 1
    ) AS "ProtocolNumber",
    (date_part('hour', points."StartedDateTime")*60 + date_part('minute', points."StartedDateTime")) - 
    (date_part('hour', points."ArrivedDateTime")*60 + date_part('minute', points."ArrivedDateTime")) AS delta
  FROM "Examination" AS exam
  JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
  JOIN "ExaminationPointsOfOrderMse" points ON exam."Id" = points."ExaminationID"
  JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
  LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
  WHERE
    -- МСЭ начато
    exam."IsStarted" = TRUE
    -- Очно
    AND points."IsInPresence" = TRUE
    -- На базе
    AND points."ExaminationPlaceID" = 1
    -- Не на изменение
    AND exam."ChangeRequestId" IS NULL
    -- Бюро и составы (обжалование)
    AND (points."ExamPointsOfOrderMseID" = 1 OR points."ExamPointsOfOrderMseID" IS NULL)
    -- есть запись и явился
    AND EXISTS (
      SELECT 1 
      FROM "ExaminationRecord" r
      LEFT JOIN "ExaminationRecordExamination" er ON er."ExaminationRecordId" = r."Id"
      WHERE er."ExaminationId" = exam."Id" AND r."WasAppeared" = TRUE
    )  
   AND exam."ExamTime" BETWEEN current_date - INTERVAL '1 days' AND current_date + INTERVAL '1 day'
  ORDER BY org."Number"
)
SELECT
  "SHORTNAME",
  "SNILS",
  "ProtocolNumber",
  COALESCE(delta::text, '-') AS "minute"
FROM rec
WHERE (delta NOT BETWEEN 0 AND 15 ) OR delta IS NULL