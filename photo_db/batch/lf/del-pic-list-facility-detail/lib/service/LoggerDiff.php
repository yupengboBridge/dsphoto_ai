<?php

class LoggerDiff {

	const DEBUG = 1;
	const INFO = 2;
	const WARN = 3;
	const ERROR = 4;
	const FATAL = 5;
	const DEFAULT_THRESHOLD = 1;
	const FILENAME_FORMAT = '{phpdir}/../cli/log_diff/{Y}{m}{d}.log';
	const KEEP_DAYS = 7;
	const DISPLAY_CONSOLE = true;

	public static function debug($message) {
		self::write(self::DEBUG, $message);
	}

	public static function info($message) {
		self::write(self::INFO, $message);
	}

	public static function warn($message) {
		self::write(self::WARN, $message);
	}

	public static function error($message) {
		self::write(self::ERROR, $message);
	}

	public static function fatal($message) {
		self::write(self::FATAL, $message);
	}

	public static function rotate() {
		$keepDays = self::KEEP_DAYS;
		$alives = array();
		for ($i = 0; $i < $keepDays; $i++) {
			$alives[] = self::createLogFilename($i);
		}
		$files = array();
		$dir = scandir(dirname(self::createLogFilename()));
		foreach ($dir as &$file) {
			if ($file === '.' || $file === '..') {
				continue;
			}
			$files[] = dirname(self::createLogFilename()).'/'.$file;
		}
		foreach ($files as $file) {
			if (!in_array($file, $alives)) {
				unlink($file);
			}
		}
	}

	private static function write($level, $message) {
		$threshold = self::DEFAULT_THRESHOLD;
		if ($threshold <= $level) {
			$logFilename = self::createLogFilename();
			$fp = fopen($logFilename, 'a+');
			if (!$fp) {
				throw new Exception("Log file '$logFilename' not writable. Original message is '$message'");
			}
			$text = sprintf('%s,%-5s,%s%s', date('Y/m/d H:i:s'), self::createLevelText($level), $message, "\r\n");
			fputs($fp, $text);
			fclose($fp);
			if (self::DISPLAY_CONSOLE) {
				echo $text;
			}
		}
	}

	private static function createLogFilename($days = 0) {
		$time = time() - 86400 * $days;
		$replacer = array(
			'{phpdir}' => dirname(__FILE__),
			'{Y}' => date('Y', $time),
			'{m}' => date('m', $time),
			'{d}' => date('d', $time),
		);
		return str_replace(array_keys($replacer), array_values($replacer), self::FILENAME_FORMAT);
	}

	private static function createLevelText($level) {
		$text = '';
		switch ($level) {
			case 1:
				$text = 'DEBUG';
				break;
			case 2:
				$text = 'INFO';
				break;
			case 3:
				$text = 'WARN';
				break;
			case 4:
				$text = 'ERROR';
				break;
			case 5:
				$text = 'FATAL';
				break;
			default:
				break;
		}
		return $text;
	}
}
