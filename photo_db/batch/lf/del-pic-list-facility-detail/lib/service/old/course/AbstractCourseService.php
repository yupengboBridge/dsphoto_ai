<?php

require_once dirname(__FILE__).'/../../entity/csv/AbstractCsv.php';
require_once dirname(__FILE__).'/../../entity/Course.php';
require_once dirname(__FILE__).'/../../entity/RichCourse.php';

class AbstractCourseService {

	private $csv;
	private $course;
	protected $categoryId;

	public function __construct(AbstractCsv $csv, $course) {
		$this->csv = $csv;
		$this->course = $course;
	}

	public function createRichCourses() {
		return array();
	}

	protected function createRichCourse($imgKey, $textKey, $group = null) {
		$imgRow = $this->csv->fetch($imgKey, $group);
		$textRow = $this->csv->fetch($textKey, $group);
		$img = $this->scrapeImage(empty($imgRow['TEXT']) ? '' : $imgRow['TEXT']);
		$text = empty($textRow['TEXT']) ? '' : $textRow['TEXT'];
		$updatedAt = $this->csv->updatedAt();
		return new RichCourse(
			$this->course->hei,
			$this->course->courseId,
			$this->categoryId,
			$img,
			$text,
			$updatedAt
		);
	}

	private function scrapeImage($path) {
		preg_match('/[0-9a-zA-Z\\-_]*\\.(jpg|gif|png)$/', $path, $matches);
		return empty($matches[0]) ? '' : $matches[0];
	}
}
