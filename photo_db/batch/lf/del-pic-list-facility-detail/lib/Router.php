<?php

require_once 'ActionHandler.php';
require_once 'ControllerFactory.php';
require_once 'Parameter.php';
require_once 'exception/ApplicationException.php';
require_once 'exception/RoutingException.php';

class Router {

	private $parameter;

	public function __construct($path) {
		$this->parameter = new Parameter(explode('/', trim($path, '/')));
		setlocale(LC_ALL, 'ja_JP.utf8');
	}

	public function run() {
		try {
			$this->fillDefaults();
			$controller = ControllerFactory::createController($this->createControllerName(), $this->parameter);
			ActionHandler::handle($controller, $this->createActionName());
		} catch (ApplicationException $e) {
			$e->response();
		}
	}

	private function fillDefaults() {
		$this->fillController();
		$this->fillAction();
	}

	private function fillController() {
		if (!$this->parameter->hasController()) {
			$this->parameter->setController('index');
		}
	}

	private function fillAction() {
		if (!$this->parameter->hasAction()) {
			$this->parameter->setAction('index');
		}
	}

	private function createControllerName() {
		if ($this->parameter->hasController()) {
			return $this->parameter->createControllerName();
		} else {
			throw new RoutingException();
		}
	}

	private function createActionName() {
		if ($this->parameter->hasAction()) {
			return $this->parameter->createActionName();
		} else {
			throw new RoutingException();
		}
	}
}
