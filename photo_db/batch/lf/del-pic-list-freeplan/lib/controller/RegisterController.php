<?php

require_once 'Controller.php';
require_once dirname(__FILE__).'/../api/RichCourseRequest.php';
require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../exception/ApiRequestFailureException.php';
require_once dirname(__FILE__).'/../service/Logger.php';
require_once dirname(__FILE__).'/../service/DisposeService.php';
require_once dirname(__FILE__).'/../service/RegisterService.php';
require_once dirname(__FILE__).'/../entity/csv/UtilCsv.php';

class RegisterController extends Controller {

	private $config;

	public function beforeAction() {
		$this->config = new RichCourseConfig();
	}

	public function allAction() {
        $this->register();
	}

	public function recentAction() {
		$this->register();
	}

	private function register() {
		Logger::info('Requesting updated list...');
        $csv = new UtilCsv();
		$csv_ary = array();
        $csv_ary = $csv->getCsvList();
		Logger::info('DONE Get csv list...');
        // DB登録
        $service = new RegisterService($csv_ary);
        $service->register();
        Logger::info('  Success');
	}
}
