<?php

require_once 'ApplicationException.php';

class ConfigNotFoundException extends ApplicationException {

	protected $data = 'Config not found.';
	protected $errorCode = '4';
}
