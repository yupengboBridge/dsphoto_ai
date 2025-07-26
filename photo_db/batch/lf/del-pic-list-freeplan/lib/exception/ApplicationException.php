<?php

require_once dirname(__FILE__).'/../entity/Response.php';

class ApplicationException extends Exception {

	protected $data = 'Application failed.';
	protected $errorCode = '-1';

	public function response() {
		$response = new Response($this->data, $this->errorCode);
		echo $response->toJson();
		exit;
	}
}
