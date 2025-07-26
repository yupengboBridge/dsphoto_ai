<?php

require_once 'ApplicationException.php';

class ApiRequestFailureException extends ApplicationException {

	protected $data = 'Api request failed.';
	protected $errorCode = '7';
}
