<?php

require_once dirname(__FILE__).'/AbstractCourseService.php';

class PlanCourseService extends AbstractCourseService {

	protected $categoryId = 4;

	public function createRichCourses() {
		$courses = array();
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '1');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '1');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '1');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '2');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '2');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '2');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '3');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '3');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '3');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '4');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '4');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '4');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '5');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '5');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '5');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '6');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '6');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '6');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '7');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '7');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '7');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '8');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '8');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '8');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '9');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '9');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '9');
		$courses[] = $this->createRichCourse('scImg1', 'scPoint1', '10');
		$courses[] = $this->createRichCourse('scImg2', 'scPoint2', '10');
		$courses[] = $this->createRichCourse('scImg3', 'scPoint3', '10');
		return $courses;
	}
}
