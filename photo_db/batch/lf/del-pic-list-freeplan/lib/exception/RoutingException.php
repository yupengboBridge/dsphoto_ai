<?php

require_once 'ApplicationException.php';

class RoutingException extends ApplicationException {

	protected $data = 'Routing failed.';
	protected $errorCode = '1';
}
