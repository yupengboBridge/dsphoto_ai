<?php

require_once 'ApplicationException.php';

class IllegalParameterException extends ApplicationException {

	protected $data = 'Illegal parameter.';
	protected $errorCode = '6';
}
