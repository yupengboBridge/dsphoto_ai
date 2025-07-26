<?php

require_once 'Controller.php';
require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../exception/IllegalParameterException.php';
require_once dirname(__FILE__).'/../service/RichCourseService.php';
require_once dirname(__FILE__).'/../service/ComplementService.php';
require_once dirname(__FILE__).'/../service/SearchService.php';

require_once dirname(__FILE__).'/../../ChromePhp.php';

class ApiController extends Controller {

	private $config;

	public function beforeAction() {
		$this->config = new RichCourseConfig();
	}

	public function searchAction() {
		$hei = filter_input(INPUT_GET, 'hei');
		if (empty($hei)) {
//			throw new IllegalParameterException();
		}
		$page = filter_input(INPUT_GET, 'page');
		if (empty($page)) {
			$page = null;
		}
		$type = filter_input(INPUT_GET, 'type');
		$country = filter_input(INPUT_GET, 'country');

		$search = new SearchService($this->config);
		$data = $search->search($hei, $page, $type, $country);
//		$complemented = $this->complement($data, $type);
		$response = new Response($data);
		echo $response->toJson();
	}

    public function delAction() {
        $search = new SearchService($this->config);
        $hei = filter_input(INPUT_GET, 'hei');
        $courseId = filter_input(INPUT_GET, 'courseId');

        $type = filter_input(INPUT_GET, 'type');
        $img = filter_input(INPUT_GET, 'img');

        $fstNm = filter_input(INPUT_GET, 'fstNm');
        $sndNm = filter_input(INPUT_GET, 'sndNm');
        $exNm = filter_input(INPUT_GET, 'exNm');
        $ctgId = filter_input(INPUT_GET, 'ctgId');

        $data = $search->del($hei, $courseId, $img, $fstNm, $sndNm, $exNm, $type, $ctgId);
        $response = new Response($data);
        echo $response->toJson();
    }

    public function destselAction() {
        $hei = filter_input(INPUT_GET, 'hei');
        if (empty($hei)) {
//            throw new IllegalParameterException();
        }
        $courseId = filter_input(INPUT_GET, 'courseId');
        $page = filter_input(INPUT_GET, 'page');
        if (empty($page)) {
            $page = null;
        }
        $type = filter_input(INPUT_GET, 'type');
        $dest = filter_input(INPUT_GET, 'dest');
		$mainbrand = filter_input(INPUT_GET, 'mainbrand');
        $search = new SearchService($this->config);
        $data = $search->destsel($hei, $courseId, $page, $type, $dest, $mainbrand);
        //$complemented = $this->complement($data, $type);
        $response = new Response($data);
        echo $response->toJson();
    }
	public function countryselAction() {
		$hei = filter_input(INPUT_GET, 'hei');
		if (empty($hei)) {
//			throw new IllegalParameterException();
		}
		$page = filter_input(INPUT_GET, 'page');
		if (empty($page)) {
			$page = null;
		}
		$type = filter_input(INPUT_GET, 'type');
		$search = new SearchService($this->config);
		$data = $search->countrysel($hei, $page, $type);
		//$complemented = $this->complement($data, $type);
		$response = new Response($data);
		echo $response->toJson();
	}

    public function filecheckAction() {
        $day = filter_input(INPUT_GET, 'day');
	    $res = false;
	    // ファイルの存在判定
        if(file_exists($this->config->difcsvPathCr . $day . '.csv')){
            $res = true;
        }
        $response = new Response($res);
        echo $response->toJson();
    }

    public function downloadAction() {
        $day = filter_input(INPUT_GET, 'day');
        //ファイルのパスとファイル名
        $fpath = $this->config->difcsvPathCr . $day . '.csv';
        $fname = $day . '.csv';
        //ファイルのダウンロード
        header('Content-Type: application/octet-stream');
        header('Content-Length: '.filesize($fpath));
        header('Content-disposition: attachment; filename="'.$fname.'"');
        readfile($fpath);
    }

	private function complement($response, $type) {
		$service = new ComplementService($this->config, $response);
		$result = $service->search($type);
		return $service->combine($result);
	}
}
