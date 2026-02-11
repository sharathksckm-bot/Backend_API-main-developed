-- View: vw_college_course_effective_fees
-- Purpose:
--   Expose course fees with fallback:
--   1) Use college_course.total_fees when present
--   2) Else, if entrance exams are linked, use the first (lowest) matching counseling_fees.fees
--      for those exam ids, grouped by (category, college_type)
--
-- Notes:
--   - This view is parameterless; apply filters in your SELECT (collegeid, sub_category, category, college_type).
--   - The fallback join is only applied when course_total_fees is NULL/empty to avoid duplicates.
--
-- MySQL 5.7+/8.0+

CREATE OR REPLACE VIEW vw_college_course_effective_fees AS
SELECT
  cc.collegeid,
  cc.courseid,
  c.sub_category,
  c.name,
  c.duration,
  cc.median_salary,
  cc.entrance_exams,
  IF(
    cc.entrance_exams IS NULL OR cc.entrance_exams = '',
    'N/A',
    (
      SELECT GROUP_CONCAT(ex.title)
      FROM exams ex
      WHERE FIND_IN_SET(ex.id, REPLACE(cc.entrance_exams, ' ', '')) > 0
    )
  ) AS examNames,
  cc.total_fees AS course_total_fees,
  cf_min.category AS counseling_category,
  cf_min.college_type AS counseling_college_type,
  cf_min.exam_fee AS entrance_exam_fee,
  COALESCE(NULLIF(TRIM(cc.total_fees), ''), cf_min.exam_fee) AS effective_total_fees
FROM college_course cc
LEFT JOIN courses c
  ON c.id = cc.courseid
LEFT JOIN (
  SELECT
    cc2.collegeid,
    cc2.courseid,
    cf.category,
    cf.college_type,
    SUBSTRING_INDEX(
      GROUP_CONCAT(cf.fees ORDER BY CAST(cf.fees AS UNSIGNED) ASC SEPARATOR ','),
      ',',
      1
    ) AS exam_fee
  FROM college_course cc2
  JOIN courses c2
    ON c2.id = cc2.courseid
  JOIN counseling_fees cf
    ON cf.sub_category = c2.sub_category
   AND cf.fees IS NOT NULL
   AND cf.fees <> ''
   AND cc2.entrance_exams IS NOT NULL
   AND cc2.entrance_exams <> ''
   AND FIND_IN_SET(cf.exam_id, REPLACE(cc2.entrance_exams, ' ', '')) > 0
  WHERE cc2.is_deleted = 0
  GROUP BY
    cc2.collegeid,
    cc2.courseid,
    cf.category,
    cf.college_type
) cf_min
  ON cf_min.collegeid = cc.collegeid
 AND cf_min.courseid = cc.courseid
 AND (cc.total_fees IS NULL OR TRIM(cc.total_fees) = '')
WHERE cc.is_deleted = 0;

