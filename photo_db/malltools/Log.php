<?php

if (PHP_SAPI === 'cli') {
	$root_path = str_replace("/malltools","",dirname(__FILE__));
} else {
	$root_path = "../";
}

define("LOGPATH", $root_path . '/log/');

class Log {

	public static $_instance;

	private $logFile;

	private function __construct(){}
	private function __clone(){}

	public static function getInstance(){
		if(!(self::$_instance instanceof self)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function log($message) {
		// Check if log file exists, if not create it
		// Append message to the log file
		$timestamp = date('Y-m-d H:i:s');
		if(!empty($this->logFile) && $this->logFile){
			//
		}else{
			$this->logFile = "system_error.log";
		}

		file_put_contents(LOGPATH.$this->logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
	}

	public function __set($name, $value)
	{
		if($name === 'logFile'){
			if (!file_exists(LOGPATH.$value)) {
				file_put_contents(LOGPATH.$value,'タスクが作成され、開始時間は:'.date('Y-m-d H:i:s').PHP_EOL);
			}
			$this->logFile = $value;
		}
	}
}
?>
