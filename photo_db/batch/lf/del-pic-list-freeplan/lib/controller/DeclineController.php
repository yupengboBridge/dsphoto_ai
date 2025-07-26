<?php

require_once 'Controller.php';
require_once dirname(__FILE__).'/../exception/DeclinedException.php';

class DeclineController extends Controller {

	public function __construct(Parameter $parameter) {
		$e = new DeclinedException();
		$e->response();
	}
}
