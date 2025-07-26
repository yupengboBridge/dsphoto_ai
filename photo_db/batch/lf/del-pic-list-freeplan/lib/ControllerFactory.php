<?php

require_once 'Parameter.php';
require_once 'exception/ControllerNotFoundException.php';

class ControllerFactory {

	private function __construct() {}

	public static function createController($controllerName, Parameter $parameter) {
		$controllerPath = dirname(__FILE__).'/controller/'.$controllerName.'.php';
		if (is_file($controllerPath)) {
			require_once $controllerPath;
			return new $controllerName($parameter);
		} else {
			throw new ControllerNotFoundException();
		}
	}
}
