<?php

require_once dirname(__FILE__).'/../../config/RichCourseConfig.php';
require_once dirname(__FILE__) . '/../../service/Logger.php';
require_once dirname(__FILE__) . '/ReadCsv.php';

/**
 * UtilCsv
 * csvファイルからコース情報を取得するクラス
 */
class UtilCsv {

	private $config;

    /**
     * UtilCsv constructor.
     */
	public function __construct() {
		$this->config = new RichCourseConfig();
	}

	/**
	 * 専門店のタイトル画像のcsvから取得
	 * @return mixed
	 */
	public function getCsvList() {

		$list = array();
		$return_list = array();
		$getCsv = new ReadCsv();
		$list = $getCsv->basicReadCsv($this->config->freeplanICsvUrl);
		$return_list['i'] = $list;
		$list = array();
		$list = $getCsv->basicReadCsv($this->config->freeplanDCsvUrl);
		$return_list['d'] = $list;

		return $return_list;
	}
}
