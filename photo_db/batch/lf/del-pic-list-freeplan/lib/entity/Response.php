<?php

class Response {

	public $errorCode;
	public $data;

	public function __construct($data, $errorCode = '0') {
		$this->data = $data;
		$this->errorCode = $errorCode;
	}

	public function toJson() {
		return json_encode($this);
	}
}
