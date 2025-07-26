<?php

require_once 'Controller.php';
require_once dirname(__FILE__).'/../api/RichCourseRequest.php';
require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../exception/ApiRequestFailureException.php';
require_once dirname(__FILE__).'/../service/Logger.php';
require_once dirname(__FILE__).'/../service/ReleaseService.php';

/**
 * ReleaseController
 * ALTER TABLEの前に該当テーブルのデータを削除するバッチ（リリース専用）
 *
 * TBのデータを削除しますので、リリース以外には使用しないでください！！
 */
class ReleaseController extends Controller {

	private $config;

	public function beforeAction() {
		$this->config = new RichCourseConfig();
	}

	public function allAction() {
        $this->delete();
	}

	private function delete() {
        //memory不足回避
        ini_set('memory_limit', '400M');
        // TBからデータ削除
        $service = new ReleaseService();
        Logger::info('  Success');
	}
}
