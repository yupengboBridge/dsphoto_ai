ALTER TABLE rich_course_i
  ADD destination_code CHAR(5) AFTER course_id,
  ADD course_name text AFTER destination_code,
  ADD course_staff_code varchar(30) AFTER course_name,
  ADD course_office_code int(10) AFTER course_staff_code,
  ADD fst_nm int(10) DEFAULT NULL AFTER rich_course_category_id,
  ADD snd_nm int(10) DEFAULT NULL AFTER fst_nm,
  ADD ex_nm int(10) DEFAULT NULL AFTER snd_nm,
  ADD ident_day varchar(30) DEFAULT NULL AFTER overview,
  ADD INDEX (ident_day)