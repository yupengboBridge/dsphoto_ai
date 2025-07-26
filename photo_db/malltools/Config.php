<?php

class Config {
	private $configData;
	private $configFilePath;

	public function __construct($configFilePath) {
        if (!file_exists($configFilePath)) {
            throw new Exception("設定ファイル '{$configFilePath}' が見つかりません。");
        }
		$this->configFilePath = $configFilePath;
		$this->loadConfig();
	}

	private function loadConfig() {
		$this->configData = parse_ini_file($this->configFilePath, true);
        if ($this->configData === false) {
            throw new Exception("プロファイルを解析できませんでした '{$this->configFilePath}'");
        }
	}

	public function readConfig($section = null) {
		if ($section === null) {
			return $this->configData;
		}
		return isset($this->configData[$section]) ? $this->configData[$section] : null;
	}

	public function updateConfig($section, $key, $value) {
		if (!isset($this->configData[$section])) {
			$this->configData[$section] = array();
		}
		$this->configData[$section][$key] = $value;
		$this->saveConfig();
	}

	public function createConfig($section, $key, $value) {
		if (!isset($this->configData[$section])) {
			$this->configData[$section] = array();
		}
		$this->configData[$section][$key] = $value;
		$this->saveConfig();
	}

	private function saveConfig() {
		$configString = '';
		foreach ($this->configData as $section => $values) {
			$configString .= "[$section]\n";
			foreach ($values as $key => $value) {
				$configString .= "$key = \"$value\"\n";
			}
			$configString .= "\n";
		}
        if (file_put_contents($this->configFilePath, $configString) === false) {
            throw new Exception("設定ファイルの書き込みに失敗しました： '{$this->configFilePath}'");
        }
    }
}

