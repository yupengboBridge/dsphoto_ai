<?php

class RichCourseRequest {

	private $url;

	public function __construct($url) {
		$this->url = $url;
	}

	public function getResponse() {
		$response = file_get_contents($this->url);
		return json_decode($response);
	}
}
