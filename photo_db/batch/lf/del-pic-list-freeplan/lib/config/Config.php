<?php

require_once dirname(__FILE__).'/../exception/ConfigNotFoundException.php';

abstract class Config {

	protected $configFile = '';

	public function __construct() {
		$configFile = empty($this->configFile) ? get_class($this).'.json' : $this->configFile;
		$configFilePath = dirname(__FILE__).'/'.$configFile;
		if (!is_file($configFilePath)) {
			throw new ConfigNotFoundException();
		}
		$decoded = json_decode(file_get_contents($configFilePath));
		foreach ($decoded as $key => $value) {
			$this->$key = $value;
		}
	}
}
