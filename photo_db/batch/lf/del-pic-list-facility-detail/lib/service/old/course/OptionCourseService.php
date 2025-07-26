<?php

require_once dirname(__FILE__).'/AbstractCourseService.php';

class OptionCourseService extends AbstractCourseService {

	protected $categoryId = 5;

	public function createRichCourses() {
		$courses = array();
		$courses[] = $this->createRichCourse('opImg1', 'opPoint1');
		$courses[] = $this->createRichCourse('opImg2', 'opPoint2');
		$courses[] = $this->createRichCourse('opImg3', 'opPoint3');
		return $courses;
	}
}
