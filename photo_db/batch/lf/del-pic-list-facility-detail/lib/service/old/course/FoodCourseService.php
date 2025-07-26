<?php

require_once dirname(__FILE__).'/AbstractCourseService.php';

class FoodCourseService extends AbstractCourseService {

	protected $categoryId = 3;

	public function createRichCourses() {
		$courses = array();
		$courses[] = $this->createRichCourse('foodImg1', 'foodSbTtl1', '1');
		$courses[] = $this->createRichCourse('foodImg2', 'foodSbTtl2', '1');
		$courses[] = $this->createRichCourse('foodImg1', 'foodSbTtl1', '2');
		$courses[] = $this->createRichCourse('foodImg2', 'foodSbTtl2', '2');
		$courses[] = $this->createRichCourse('foodImg1', 'foodSbTtl1', '3');
		$courses[] = $this->createRichCourse('foodImg2', 'foodSbTtl2', '3');
		return $courses;
	}
}
