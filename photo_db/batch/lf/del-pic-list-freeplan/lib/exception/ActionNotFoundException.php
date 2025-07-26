<?php

require_once 'ApplicationException.php';

class ActionNotFoundException extends ApplicationException {

	protected $data = 'Action not found.';
	protected $errorCode = '3';
}
