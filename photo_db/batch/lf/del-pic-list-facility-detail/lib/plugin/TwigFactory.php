<?php

require_once dirname(__FILE__).'/../plugin/twig/lib/Twig/Autoloader.php';

class TwigFactory {

	private function __construct() {}

	public static function createTwig() {
		Twig_Autoloader::register();
		$templatePath = dirname(__FILE__).'/../view';
		$loader = new Twig_Loader_Filesystem($templatePath);
		return new Twig_Environment($loader);
	}
}
