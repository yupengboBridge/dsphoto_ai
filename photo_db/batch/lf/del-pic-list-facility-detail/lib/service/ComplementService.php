<?php

require_once dirname(__FILE__).'/../api/DomesticSearchClient.php';
require_once dirname(__FILE__).'/../api/InternationalSearchClient.php';

class ComplementService {

	private $config;
	private $response;

	public function __construct(RichCourseConfig $config, $response) {
		$this->config = $config;
		$this->response = $response;
	}

	public function search($type = 'i') {

        $client = $type === 'i' ?
            new InternationalSearchClient($this->config->internationalSearchApiUrl) :
            new DomesticSearchClient($this->config->domesticSearchApiUrl);
		$response = $client->request($this->createParameters());
		return json_decode($response);
	}

	public function combine($result) {
		$contents = $this->response->contents;
		if (!empty($result->response->docs)) {
			foreach ($result->response->docs as $doc) {
				foreach ($contents as $combined) {
					if ($this->hasBasicInfo($combined) && $combined->basicInfo->hei == $doc->p_hei && $combined->basicInfo->courseId === $doc->p_course_id) {
						$this->bindApiValues($combined, $doc);
					}
				}
			}
		}
		$combined = $this->response;
		$combined->contents = $contents;
		return $combined;
	}

	private function hasBasicInfo($content) {
		return !empty($content->basicInfo->hei) && !empty($content->basicInfo->courseId);
	}

	private function bindApiValues($content, $apiContent) {
		$content->basicInfo->courseName = $apiContent->p_course_name;
	}

	private function createParameters() {
		$heis = array();
		$courseIds = array();
		foreach ($this->response->contents as $key => $content) {
			list($hei, $courseId) = explode('_', $key);
			if (array_search($hei, $heis) === false) {
				$heis[] = $hei;
			}
			if (array_search($courseId, $courseIds) === false) {
				$courseIds[] = $courseId;
			}
		}
		return array(
			'p_hei' => implode(',', $heis),
			'p_course_id' => implode(',', $courseIds),
			'p_rtn_count' => $this->config->rpp,
		);
	}

}
