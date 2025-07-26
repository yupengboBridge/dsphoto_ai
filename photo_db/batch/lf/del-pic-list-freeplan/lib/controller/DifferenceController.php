<?php

require_once 'Controller.php';
require_once dirname(__FILE__).'/../api/RichCourseRequest.php';
require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../exception/ApiRequestFailureException.php';
require_once dirname(__FILE__).'/../service/LoggerDiff.php';
require_once dirname(__FILE__).'/../service/DisposeService.php';
require_once dirname(__FILE__).'/../service/DifferenceService.php';

class DifferenceController extends Controller {

	private $config;

	public function beforeAction() {
		$this->config = new RichCourseConfig();
	}

	public function createAction() {
        $this->create();
	}

	private function create() {
        LoggerDiff::info('Having hei and courseId list...');
        // DBから情報を取得し、CSVファイル作成
        $service = new DifferenceService();
        $service->create();
        LoggerDiff::info('  Success');
	}
}
