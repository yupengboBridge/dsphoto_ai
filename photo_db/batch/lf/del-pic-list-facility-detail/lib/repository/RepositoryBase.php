<?php

require_once dirname(__FILE__).'/../../../sharing/setting/setting.php';
require_once dirname(__FILE__).'/../plugin/idiorm/idiorm.php';

abstract class RepositoryBase {

	private static $initialized;

	static public function initialize() {
		if (self::isInitialized()) {
			return;
		}
		self::configure();
		self::markInitialized();
	}

	static private function isInitialized() {
		return self::$initialized === true;
	}

	static private function configure() {
		$options = array(
			'{dbname}' => DB_NAME,
			'{host}' => DB_HOST,
			'{port}' => DB_PORT,
		);
		ORM::configure(str_replace(array_keys($options), array_values($options), 'mysql:dbname={dbname};host={host};port={port}'));
		ORM::configure('username', DB_USER);
		ORM::configure('password', DB_PASS);
		ORM::configure('driver_options', array(
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		));
	}

	static private function markInitialized() {
		self::$initialized = true;
	}
} RepositoryBase::initialize();
