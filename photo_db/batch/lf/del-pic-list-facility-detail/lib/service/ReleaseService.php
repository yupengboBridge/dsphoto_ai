<?php

require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../repository/RichCourseIRepository.php';
require_once dirname(__FILE__).'/../repository/RichCourseDRepository.php';
require_once dirname(__FILE__).'/../service/Logger.php';
require_once dirname(__FILE__).'/../entity/Course.php';
require_once dirname(__FILE__).'/../entity/RichCourse.php';

/**
 * ReleaseService
 * ALTER TABLEの前に該当テーブルのデータを削除するバッチ（リリース専用）
 *
 * TBのデータを削除しますので、リリース以外には使用しないでください！！
 */
class ReleaseService {

    /**
     * ReleaseService constructor.
     */
	public function __construct() {
        $this->config = new RichCourseConfig();
		// TB初期化
        $repository = new RichCourseIRepository();
        Logger::info('Start initializing the rich_course_i table...');
        $repository->deleteAll();
        Logger::info('End initializing the rich_course_i table...');
        $repository = new RichCourseDRepository();
        Logger::info('Start initializing the rich_course_d table...');
        $repository->deleteAll();
        Logger::info('End initializing the rich_course_d table...');
	}
}
