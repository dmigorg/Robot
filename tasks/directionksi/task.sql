WITH par AS (
  SELECT s."SettingValue"::int AS cur_org 
  FROM "ApplicationSettings" s
  WHERE s."SettingName" = 'CurrentOrganizationID'  
)
, rec AS (
  SELECT
    doc."DocFlowStageId" AS stage_id,
    COALESCE(org_exam."Number" , org_req."Number") AS orgname,
    p."SNILS" AS snils,
    doc."CreateTime"::date,
    doc."RegDate"::date,
    er."AppointmentDT"::date,
    exam."ExamTime"::date,
    exam."TransferDate"::date, -- ПДО
    concl."DecisionDate"::date,
    points."IsInPresence", -- форма проведения
    exam."DocsIssued",
    rsn.val AS req_special_notes, -- особые отметки в направлении 26 - паллиатив, 28 - ампутант
    ecsn.val AS exam_special_notes, -- особые отметки в экспертизе
    working_days.val AS working_days
  FROM "Document" doc
  JOIN "Request" req ON req."DocumentID" = doc."ID" 
  JOIN "Person" p ON p."PersonID" = req."RequesterID"
  LEFT JOIN "ExaminationRecord" er ON er."OriginRequestId" = req."DocumentID" 
  LEFT JOIN "ExaminationRecordExamination" ere ON ere."ExaminationRecordId" = er."Id"
  LEFT JOIN "Examination" exam ON exam."Id" = ere."ExaminationId"
  LEFT JOIN "ExaminationPointsOfOrderMse" points ON points."ExaminationID" = exam."Id" 
  LEFT JOIN "ExaminationConclusion" concl ON concl."ExaminationId" = exam."Id"
  LEFT JOIN LATERAL (
    SELECT array_agg(ecsn."SpecialSocStatusId") 
    FROM "ExaminationConclusionSpecialNote" ecsn
    WHERE ecsn."ExaminationId" = exam."Id"
  ) ecsn(val) ON TRUE
  LEFT JOIN LATERAL (
    SELECT array_agg(rsn."SpecialSocStatusId") 
    FROM "RequestSpecialNote" rsn
    WHERE rsn."RequestId" = doc."ID"
  ) rsn(val) ON TRUE
  LEFT JOIN LATERAL(
    SELECT array_agg(d."date") AS dates  
    FROM (
      SELECT "CalendarOptions_IsWorkingDay"( (SELECT cur_org FROM par), generate_series::date) AS is_working, generate_series::date AS "date"
      FROM generate_series(doc."CreateTime"::date, 
        CASE WHEN concl."DecisionDate" IS NOT NULL THEN concl."DecisionDate"::date
          WHEN exam."ExamTime" IS NOT NULL THEN exam."ExamTime"::date
          WHEN er."AppointmentDT" IS NOT NULL THEN er."AppointmentDT"::date
          WHEN doc."RegDate" IS NOT NULL THEN doc."RegDate"::date
          ELSE current_date END 
      , '1 day'::interval)
    ) d
    WHERE is_working = TRUE
  ) AS working_days(val) ON TRUE  
  LEFT JOIN "DicOrganization" org_req ON org_req."ORGANIZATION_ID" = doc."DocFlowStageId" AND doc."DocFlowStageId" NOT IN (1,-1)
  LEFT JOIN "DicOrganization" org_exam ON org_exam."ORGANIZATION_ID" = exam."ExamBuroId"
  WHERE req."RequestTypeID" IN (4,5) AND doc."RegNumber" IS NOT NULL AND doc."RegDate" IS NOT NULL
    AND doc."CreateTime"  > current_date - INTERVAL '30 day'
)

/*
-- Направление 
Дата поступления -- начальная точка
Дата регистрации <=1 дней на регистрацию

-- Запись
Дата записи <=1 дней на создание

-- Экспертиза
Дата проведения <=10 дней на начало проведения (Дата записи == Дата проведения)
ПДО не ограничено в днях
Дата вынесения решения <=10 дней на завершение за вычетом ПДО

-- Общее количество дней <=10
*/

, rec2 AS(
  SELECT
    CASE 
      WHEN rec.stage_id NOT IN (-1, 1)
        AND rec."RegDate" IS NULL
        AND array_position(rec.working_days, current_date) > 1 
      THEN 'Ошибка! Cрок регистрации направления превышает 1 рабочий день'
      WHEN rec.stage_id NOT IN (-1, 1) 
        AND rec."AppointmentDT" IS NULL
        AND array_position(rec.working_days, current_date) > 1
      THEN 'Предупреждение! Отсутствует предварительная запись на МСЭ'
      WHEN rec.stage_id NOT IN (-1, 1) 
        AND rec."AppointmentDT" IS NOT NULL AND rec.req_special_notes && '{26,28}'
        AND rec."ExamTime" IS NULL
        AND COALESCE(array_position(rec.working_days, rec."AppointmentDT"), 3) > 2 -- превышение сроков записи на МСЭ больше 2 дней
      THEN 'Ошибка! Дата предварительной записи на МСЭ паллиатива или ампутации превышает 2 рабочих дня'
      WHEN rec.stage_id NOT IN (-1, 1) 
        AND rec."AppointmentDT" IS NOT NULL
        AND rec."ExamTime" IS NULL
        AND COALESCE(array_position(rec.working_days, rec."AppointmentDT"), 8) > 7 -- превышение сроков записи на МСЭ больше 7 дней
      THEN 'Предупреждение! Выбран срок превышающий 7 рабочих дней для проведения МСЭ'
      WHEN rec."DecisionDate" IS NULL
        AND rec."ExamTime" IS NOT NULL
        AND rec.exam_special_notes && '{26,28}'
        AND rec."DocsIssued" = FALSE
        AND COALESCE(array_position(rec.working_days, rec."ExamTime"), 2) > 1 -- превышение сроков проведения на МСЭ больше 1 дня
      THEN 'Предупреждение! Срок проведения МСЭ паллиатива или ампутации превышает 1 рабочий день'    
      WHEN rec."DecisionDate" IS NULL
        AND rec."ExamTime" IS NOT NULL
        AND rec."DocsIssued" = FALSE
        AND COALESCE(array_position(rec.working_days, rec."ExamTime"), 8) > 7 -- превышение сроков проведения на МСЭ больше 7 дней
      THEN 'Предупреждение! Срок проведения МСЭ превышает 7 рабочих дней'    
       WHEN rec."DecisionDate" IS NOT NULL
        AND rec.exam_special_notes && '{26,28}'
        AND rec."DocsIssued" = FALSE
        AND COALESCE(array_position(rec.working_days, rec."DecisionDate"), 4) > 3 -- превышение сроков проведения на МСЭ больше 3 дней
      THEN 'Предупреждение! Срок окончания экспертизы по паллиативу или ампутации превышает 3 рабочих дня'    
       WHEN rec."DecisionDate" IS NOT NULL
        AND rec."DocsIssued" = FALSE 
        AND COALESCE(array_position(rec.working_days, rec."DecisionDate"), 8) > 7 -- превышение сроков проведения на МСЭ больше 7 дней
      THEN 'Предупреждение! Срок окончания экспертизы превышает 7 рабочих дней'    
      ELSE ''
    END AS message,
    'Бюро №' || rec.orgname AS orgname,
    rec.snils,
    to_char(rec."CreateTime", 'dd.mm.yyyy') AS "CreateTime"
    -- to_char(rec."RegDate", 'dd.mm.yyyy') AS "RegDate",
    -- to_char(rec."AppointmentDT", 'dd.mm.yyyy') AS "AppointmentDT",
    -- to_char(rec."ExamTime", 'dd.mm.yyyy') AS "ExamTime",
    -- to_char(rec."TransferDate", 'dd.mm.yyyy') AS "TransferDate", 
    -- to_char(rec."DecisionDate", 'dd.mm.yyyy') AS "DecisionDate"
  FROM rec
  WHERE rec.stage_id != -1 AND rec."RegDate" IS NOT NULL
  ORDER BY rec.orgname ASC
)
SELECT * FROM rec2 WHERE message != ''