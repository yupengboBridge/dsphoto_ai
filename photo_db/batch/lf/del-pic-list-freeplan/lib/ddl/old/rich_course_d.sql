CREATE TABLE IF NOT EXISTS `rich_course_d` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hei` varchar(5) NOT NULL,
  `course_id` varchar(20) NOT NULL,
  `rich_course_category_id` int(10) NOT NULL,
  `photo_no` varchar(30) NOT NULL,
  `overview` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_search` (`hei`,`course_id`),
  KEY `rich_course_category_id` (`rich_course_category_id`),
  KEY `photo_no` (`photo_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
