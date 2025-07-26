<?php

class Environment {

	const PRODUCTION = 1;
	const TEST = 2;
	const CMS = 3;
	const DEVELOP = 4;
	const LOCAL = 5;

	private function __construct() {}

	public static function detect() {
		$httpServer = filter_input(INPUT_SERVER, 'HTTP_HOST');
		var_dump($httpServer);
		switch ($httpServer) {
			case 'www.hankyu-travel.com':
			case 'x.hankyu-travel.com':
				$current = self::PRODUCTION;
				break;
			case 'www-test.hankyu-travel.com':
				$current = self::TEST;
				break;
			case 'cms.hankyu-travel.com':
			case 'www-cms.hankyu-travel.com':
				$current = self::CMS;
				break;
			case 'bud-dev.leafnet.jp':
			case 'x-bud.leafnet.jp':
				$current = self::DEVELOP;
				break;
			default:
				$current = self::LOCAL;
				break;
		}
		return $current;
	}

	public static function isProduction() {
		return self::detect() === self::PRODUCTION;
	}

	public static function isTest() {
		return self::detect() === self::TEST;
	}

	public static function isCms() {
		return self::detect() === self::CMS;
	}

	public static function isDevelop() {
		return self::detect() === self::DEVELOP;
	}

	public static function isLocal() {
		return self::detect() === self::LOCAL;
	}
}
