<?php

require_once 'ApplicationException.php';

class ViewException extends ApplicationException {

	protected $data = 'View failed.';
	protected $errorCode = '5';
}
