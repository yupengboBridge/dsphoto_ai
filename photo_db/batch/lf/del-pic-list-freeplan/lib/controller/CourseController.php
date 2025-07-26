<?php

require_once 'Controller.php';
require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../exception/IllegalParameterException.php';
require_once dirname(__FILE__).'/../service/RichCourseService.php';

class CourseController extends Controller {

	private $config;

	public function beforeAction() {
		$this->config = new RichCourseConfig();
	}

	public function iAction() {
		$hei = filter_input(INPUT_GET, 'hei');
		$heiNames = $this->createHeiNames();
		if (!array_key_exists($hei, $heiNames)) {
//			throw new IllegalParameterException();
		}
		$this->render(array(
//			'heiName' => $heiNames[$hei],
			'rpp' => $this->config->rpp,
			'productionUrl' => $this->config->productionUrl,
			'inspectionUrl' => $this->config->inspectionUrl,
            'today' => date("Y-m-d"),
            'hei' => $hei
		));
	}

		public function dAction() {
		$hei = filter_input(INPUT_GET, 'hei');
		$heiNames = $this->createHeiNames();
		if (!array_key_exists($hei, $heiNames)) {
//			throw new IllegalParameterException();
		}
		$this->render(array(
//			'heiName' => $heiNames[$hei],
			'rpp' => $this->config->rpp,
			'productionUrl' => $this->config->productionUrl,
            'productionUrlD' => $this->config->productionUrlD,
			'inspectionUrl' => $this->config->inspectionUrl,
            'today' => date("Y-m-d"),
            'hei' => $hei
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
