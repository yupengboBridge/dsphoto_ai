<?php

require_once 'ApplicationException.php';

class DeclinedException extends ApplicationException {

	protected $data = 'Declined with security problem';
	protected $errorCode = '8';
}
