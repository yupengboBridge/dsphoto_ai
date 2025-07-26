<?php

class Parameter {

	private $controller;
	private $action;
	private $params;

	public function __construct($path) {
		$this->controller = empty($path[0]) ? '' : $path[0];
		$this->action = empty($path[1]) ? '' : $path[1];
		$this->params = array_slice($path, 2);
	}

	public function getController() {
		return $this->controller;
	}

	public function setController($controller) {
		$this->controller = $controller;
	}

	public function hasController() {
		return !empty($this->controller);
	}

	public function createControllerName() {
		return $this->ucFirstCamel($this->controller).'Controller';
	}

	public function getAction() {
		return $this->action;
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function hasAction() {
		return !empty($this->action);
	}

	public function getParams() {
		return $this->params;
	}

	public function createActionName() {
		return $this->lcFirstCamel($this->action).'Action';
	}

	public function setParams($params) {
		$this->params = $params;
	}

	public function hasParams() {
		return is_array($this->params) && count($this->params) > 0;
	}

	public function createPath() {
		$path = array();
		$path[] = $this->controller;
		$path[] = $this->action;
		foreach ($this->params as $param) {
			$path[] = $param;
		}
		return implode('/', $path);
	}

	public function createPagePath() {
		$path = array();
		$path[] = $this->controller;
		$path[] = $this->action;
		return implode('/', $path);
	}

	private function ucFirstCamel($text) {
		return $this->camel($text);
	}

	private function lcFirstCamel($text) {
		// lcfirst(): unsupported by php 5.2
		if (empty($text)) {
			return '';
		}
		if (strlen($text) === 1) {
			return strtolower($text);
		}
		return strtolower(substr($this->camel($text), 0, 1)).substr($this->camel($text), 1);
	}

	private function camel($text) {
		$ucs = array();
		foreach (explode('_', $text) as $token) {
			$ucs[] = ucfirst($token);
		};
		return implode('', $ucs);
	}
}
