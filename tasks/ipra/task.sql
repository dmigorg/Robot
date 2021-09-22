SELECT
"ExamBuroName",
"ActNumber",
"SNILS",
to_char("DecisionDate", 'dd.mm.yyyy') AS "DecisionDate"
FROM "SearchExamView" t
WHERE date_part('year', t."DecisionDate" ) = date_part('year', now())
and (t."MetaStateId" IN (4))
AND (EXISTS(
  SELECT 1 FROM (SELECT * FROM "ExaminationExpDoc" WHERE ("ExpDocTypeId" = 528) OR ("ExpDocTypeId" = 529)) t1 
  WHERE ((t1."IssueDate" IS NULL)) AND t1."ExaminationId" = t."Id"))
ORDER BY "ExamBuroName" DESC