SELECT
  -- exam."Id",
  org."SHORTNAME" AS "SHORTNAME", 
  p."SNILS" AS "SNILS",
  ( SELECT doc."Number"
    FROM "ExaminationExpDoc" doc
    WHERE doc."ExaminationId" = exam."Id" AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100))
  LIMIT 1) AS "ProtocolNumber",
  to_char(exam."RequestDate", 'dd.mm.yyyy') AS "RequestDate",
  working_day.val - COALESCE(pdo_days.val, 0) AS "Days"
FROM "Examination" exam
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
LEFT JOIN "ExaminationDiagnosis" diagnoz ON exam."Id" = diagnoz."ExaminationId"
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
-- ПДО. Исключается день проведения
LEFT JOIN LATERAL (SELECT DATE_PART('day', exam."TransferDate" - exam."ExamTime") -1) AS pdo_days(val) ON TRUE
-- кол-во рабочих дней
LEFT JOIN LATERAL(
  SELECT SUM(("CalendarOptions_IsWorkingDay"(org."PARENT_ORGANIZATION_ID", generate_series::date))::int)
  FROM generate_series(exam."RequestDate", current_date, '1 day')
) AS working_day(val) ON TRUE
WHERE
  exam."IsStopped" = FALSE AND exam."DocsIssued" = FALSE
  -- только свой узел
  AND exam."ExamBuroId" IN (
    SELECT o."ORGANIZATION_ID"
    FROM "DicOrganization" o
    WHERE o."PARENT_ORGANIZATION_ID" = (SELECT s."SettingValue"::int 
      FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID'
    )
  )
  -- предупреждать за 29 дней
  AND working_day.val - COALESCE(pdo_days.val, 0) >= 29
ORDER BY org."Number", "Days" DESC