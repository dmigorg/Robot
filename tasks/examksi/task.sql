SELECT
  -- exam."Id",
  org."SHORTNAME" AS "SHORTNAME", 
  p."SNILS" AS "SNILS",
  ( SELECT doc."Number"
    FROM "ExaminationExpDoc" doc
    WHERE doc."ExaminationId" = exam."Id" AND (doc."ExpDocTypeId" = 10 OR doc."ExpDocTypeId" = (10 + g."Id" * 100))
  LIMIT 1) AS "ProtocolNumber",
  to_char(exam."RequestDate", 'dd.mm.yyyy') AS "RequestDate",
  working_day.val - pdo_days.val AS "Days"
FROM "Examination" exam
JOIN "Person" p ON p."PersonID" = exam."PatientPersonId"
LEFT JOIN "ExaminationDiagnosis" diagnoz ON exam."Id" = diagnoz."ExaminationId"
LEFT JOIN "DicPurposeGroup" g ON (g."Id" & exam."PurposeGroupId") = g."Id"
LEFT JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = exam."ExamBuroId"
-- Кол-во дней ПДО. Исключается день проведения
LEFT JOIN LATERAL (SELECT COALESCE(DATE_PART('day', exam."TransferDate" - exam."ExamTime") -1, 0)) AS pdo_days(val) ON TRUE
-- Кол-во рабочих дней
LEFT JOIN LATERAL(
  SELECT SUM(("CalendarOptions_IsWorkingDay"(org."PARENT_ORGANIZATION_ID", generate_series::date))::int)
  FROM generate_series(exam."RequestDate", current_date, '1 day')
) AS working_day(val) ON TRUE
WHERE
  exam."RequestDate" > '20210101' 
  -- Не:прекращено, Не:Завершено (документы выданы), Статус не удалено
  AND (exam."IsStopped" = FALSE AND exam."DocsIssued" = FALSE AND exam."StateId" != -1)
  -- Только свой узел
  AND exam."ExamBuroId" IN (
    SELECT o."ORGANIZATION_ID"
    FROM "DicOrganization" o
    WHERE o."PARENT_ORGANIZATION_ID" = (SELECT s."SettingValue"::int 
      FROM "ApplicationSettings" s WHERE s."SettingName" = 'CurrentOrganizationID'
    )
  )
  -- Предупреждать за 29 дней
  AND working_day.val - pdo_days.val >= 29
ORDER BY org."Number", "Days" DESC