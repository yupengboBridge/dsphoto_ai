<?php

require_once dirname(__FILE__).'/../api/DomesticSearchClient.php';
require_once dirname(__FILE__).'/../api/InternationalSearchClient.php';

class DisposeService {

	private $config;
	private $repository;
	private $client;

	public function __construct(RichCourseConfig $config, $type = 'i') {
		$this->config = $config;
		$this->repository = $type === 'i' ? new RichCourseIRepository() : new RichCourseDRepository();
		$client = $type === 'i' ?
            new InternationalSearchClient($this->config->internationalSearchApiUrl) :
            new DomesticSearchClient($this->config->domesticSearchApiUrl);
		//$this->repository = new RichCourseIRepository($this->config->expirationDays, $this->config->rpp);
	}

	public function dispose() {
		$heiCourseIds = $this->retrieveHeiCourseIds();
		$chunk = $this->chunk($heiCourseIds);
		$this->delete($chunk);
	}

	private function retrieveHeiCourseIds() {
		return $this->repository->searchHeiCourseIds();
	}

	private function chunk($result) {
		return array_chunk($result, $this->config->requestChunk);
	}

	private function delete($chunk) {
		foreach ($chunk as $rows) {
			$response = $this->search($rows);
			$noHits = $this->createNoHits($this->createHitHeiCourseIds($response), $this->createRowsHeiCourseIds($rows));
			$this->repository->dispose($noHits);
		}
	}

	private function search($chunk) {
		//$client = new InternationalSearchClient($this->config->internationalSearchApiUrl);
		//$response = $client->request($this->createParameters($chunk));
		$response = $this->client->request($this->createParameters($chunk));
		return json_decode($response);
	}

	private function createParameters($chunk) {
		$heis = array();
		$courseIds = array();
		foreach ($chunk as $row) {
			$hei = $row->hei;
			$courseId = $row->courseId;
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
			'p_rtn_count' => $this->config->requestChunk,
		);
	}

	private function createHitHeiCourseIds($response) {
		$heiCourseIds = array();
		foreach ($response->response->docs as $doc) {
			$heiCourseIds[] = sprintf('%s_%s', $doc->p_hei, $doc->p_course_id);
		}
		return $heiCourseIds;
	}

	private function createRowsHeiCourseIds($rows) {
		$heiCourseIds = array();
		foreach ($rows as $row) {
			$heiCourseIds[] = sprintf('%s_%s', $row->hei, $row->courseId);
		}
		return $heiCourseIds;
	}

	private function createNoHits($rows, $hits) {
		return array_diff($hits, $rows);
	}
}
