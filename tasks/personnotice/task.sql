WITH rec AS (
  SELECT doc."ID",
    p."RegistryPersonId",
    p."SNILS",
    org."SHORTNAME",
    req."RequestTypeID" = 2 AS "IsRequest",
    req."IsInPresence" = TRUE AS "IsInPresence",
    --из них возвращено по причине неполного перечня медицинских обследований
    req."DirectionReturnRemdDocId" IS NOT NULL AS "IsReturn",
    er."RecordId" > 0 AS "RecordExist",
    "IsPDO" > 0 AS "PdoExist",
    "IsHome" > 0 AS "IsHome"
  FROM "Document" doc
  JOIN "Request" req ON req."DocumentID" = doc."ID"
  JOIN "Person" AS p ON p."PersonID" = COALESCE(req."RepresentativeID", req."RequesterID")
  JOIN "DicOrganization" org ON org."ORGANIZATION_ID" = COALESCE(NULLIF(doc."DocFlowStageId", -1), doc."PrevDocFlowStageId")
  -- созданные записи
  LEFT JOIN LATERAL (
    SELECT 
      COUNT(1) FILTER (WHERE er."Id" IS NOT NULL),
      COUNT((SELECT 1 FROM "Examination" e WHERE e."Id"= ere."ExaminationId" AND e."TransferDate" IS NOT NULL)),
      COUNT(1) FILTER (WHERE er."ExaminationPlaceId" != 1)
    FROM "ExaminationRecord" er
    LEFT JOIN "ExaminationRecordExamination" ere ON ere."ExaminationRecordId" = er."Id" 
    WHERE er."OriginRequestId" = doc."ID"
  ) AS er("RecordId", "IsPDO", "IsHome") ON TRUE
  WHERE
    -- только направления
    req."RequestTypeID" IN (2,4,5)
    -- присутствует дата регистрации
    AND doc."RegDate" IS NOT NULL
    -- нет даты смерти
    AND NOT EXISTS (
      SELECT 1 FROM "Person" p2
      WHERE p2."PersonID" = p."RegistryPersonId" AND p2."DeathDate" IS NOT NULL
    )
    -- не отклоненые направления
    AND NOT EXISTS (
      SELECT dh."DocumentCloseReasonID" 
      FROM "DocumentHistory" dh
      WHERE dh."DocumentID" = doc."ID" AND dh."DocumentCloseReasonID" IS NOT NULL
      ORDER BY dh."HistoryWhen" DESC 
      LIMIT 1
    )
    -- по дате получения направления МСЭ(Э)
    AND ((doc."CreateTime" >= '20220701') AND doc."CreateTime" BETWEEN current_date - '1 month'::INTERVAL AND current_timestamp)
  ORDER BY org."Number"
)
, msg AS (
  SELECT --"ID",
    CASE 
      WHEN "IsReturn" AND NOT(pn."NoticeTypeIds" && '{2}') THEN 'Отсутствует "Уведомление о причинах возврата направления"'
      WHEN "IsRequest" = FALSE AND NOT(pn."NoticeTypeIds" && '{1}') THEN 'Отсутствует "Уведомление о регистрации направления на МСЭ"'
      WHEN "IsRequest" AND NOT(pn."NoticeTypeIds" && '{3}') THEN 'Отсутствует "Уведомление о регистрации заявления об обжаловании..."'
      WHEN "IsInPresence" AND "IsHome" = FALSE AND "RecordExist"  AND ("NullExamRecordsType4" > 0 OR NOT(pn."NoticeTypeIds" && '{4}')) THEN 'Отсутствует "Уведомление о проведении МСЭ (Приглашение)"'
      WHEN ("IsInPresence" = FALSE OR ("IsInPresence" AND "IsHome")) AND "RecordExist" AND ("NullExamRecordsType5" > 0 OR NOT(pn."NoticeTypeIds" && '{5}')) THEN 'Отсутствует "Уведомление о проведении МСЭ (Уведомление о дате и времени проведения МСЭ)"'
      WHEN "IsInPresence" = FALSE AND "PdoExist" AND ("NullExaminationSourceId" > 0 OR NOT(pn."NoticeTypeIds" && '{6}')) THEN 'Отсутствует "Уведомление о ПДО (Назначение ПДО)"'
      WHEN "IsInPresence" = FALSE AND "PdoExist" AND NOT(pn."NoticeTypeIds" && '{7}') THEN 'Отсутствует "Уведомление о ПДО (Ответ о назначении ПДО)"'
    ELSE NULL
    END AS message,
    "SHORTNAME",
    "SNILS"
  FROM rec
  LEFT JOIN LATERAL (
    SELECT array_agg(pn."NoticeTypeId"),
      SUM(1) FILTER (WHERE pn."NoticeTypeId" = 4 AND pn."ExamRecordSourceId" IS NULL),
      SUM(1) FILTER (WHERE pn."NoticeTypeId" = 5 AND pn."ExamRecordSourceId" IS NULL),
      SUM(1) FILTER (WHERE pn."NoticeTypeId" = 6 AND pn."ExaminationSourceId" IS NULL)
    FROM "PersonNotice" pn
    JOIN "Person" p2 ON p2."PersonID" = pn."RecipientPersonId"
    WHERE p2."RegistryPersonId" = rec."RegistryPersonId"
  ) AS pn("NoticeTypeIds", "NullExamRecordsType4", "NullExamRecordsType5", "NullExaminationSourceId") ON TRUE
)

SELECT * FROM msg WHERE message IS NOT NULL