<?php

require_once dirname(__FILE__).'/../config/RichCourseConfig.php';
require_once dirname(__FILE__).'/../entity/Course.php';

class RichCourseService {

	private $config;

	public function __construct() {
		$this->config = new RichCourseConfig();
	}

	public function createCourses($all = true) {
		$courses = array();
		foreach ($this->extract($all) as $heiCourseId) {
			list($hei, $courseId) = explode('_', $heiCourseId);
			$courses[] = new Course($hei, $courseId);
		}
		return $courses;
	}

	private function extract($all = true) {
		$heiCourseIds = array();
		$dh = opendir($this->config->richDirectory);
		while (($filename = readdir($dh)) !== false) {
			if (!preg_match('/^'.$this->config->filePrefix.'/', $filename)) {
				continue;
			}
			list(, $hei, $courseId) = explode('_', $filename);
			$heiCourseId = sprintf('%s_%s', $hei, $courseId);
			$hit = $all ? true : $this->isRecent($filename);
			if (array_search($heiCourseId, $heiCourseIds) === false && $hit) {
				$heiCourseIds[] = $heiCourseId;
			}
		}
		closedir($dh);
		return array_unique($heiCourseIds);
	}

	private function isRecent($filename) {
		return filectime($this->config->richDirectory.'/'.$filename) > time() - (int)$this->config->recentSeconds;
	}
}
