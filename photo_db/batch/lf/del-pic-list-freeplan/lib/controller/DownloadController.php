<?php

require_once 'Controller.php';
require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../exception/IllegalParameterException.php';

class DownloadController extends Controller {

	private $config;

	public function beforeAction() {
		$this->config = new RichCourseConfig();
	}

	public function downloadAction() {
		$heiNames = $this->createHeiNames();
		$this->render(array(
			'heiNames' => $heiNames,
		));
	}

	private function createHeiNames() {
		$heiNames = array();
		foreach ($this->config->heiList as $hei) {
			$heiNames[$hei->heiCode] = $hei->heiName;
		}
		return $heiNames;
	}
	
}
