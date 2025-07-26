<?php

require_once dirname(__FILE__).'/../Environment.php';
require_once dirname(__FILE__).'/../Router.php';
require_once dirname(__FILE__).'/../entity/Response.php';
require_once dirname(__FILE__).'/../exception/ViewException.php';
require_once dirname(__FILE__).'/../plugin/TwigFactory.php';

abstract class Controller {

	private $parameter;
	private $view;
	private $path;

	public function __construct(Parameter $parameter) {
		$this->parameter = $parameter;
		$this->view = TwigFactory::createTwig();
		$this->path = $parameter->createPagePath();
	}

	protected function getParameter() {
		return $this->parameter;
	}

	protected function getPath() {
		return $this->path;
	}

	protected function setPath($path) {
		$this->path = $path;
	}

	public function beforeAction() {}

	public function afterAction() {}

	protected function redirectInternal($path) {
		$router = new Router($path);
		$router->run();
	}

	protected function createResponse($data, $errorCode = '0') {
		return new Response($data, $errorCode);
	}

	protected function render($context = array()) {
		$twigFilename = $this->path.'.twig';
		try {
			echo $this->view->render($twigFilename, $context);
		} catch (Exception $e) {
			if (Environment::isLocal()) {
				echo $e->getMessage();
			}
			throw new ViewException($e);
		}
	}
}
