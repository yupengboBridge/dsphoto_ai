<?php

require_once 'ApplicationException.php';

class ControllerNotFoundException extends ApplicationException {

	protected $data = 'Controller not found.';
	protected $errorCode = '2';
}
