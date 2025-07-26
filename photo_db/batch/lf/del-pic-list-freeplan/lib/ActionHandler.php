<?php

require_once 'controller/Controller.php';
require_once 'exception/ActionNotFoundException.php';

class ActionHandler {

	private function __construct() {}

	public static function handle(Controller $controller, $actionName) {
		if (!method_exists($controller, $actionName)) {
			throw new ActionNotFoundException();
		}
		$controller->beforeAction();
		$result = $controller->$actionName();
		$controller->afterAction();
		return $result;
	}
}
