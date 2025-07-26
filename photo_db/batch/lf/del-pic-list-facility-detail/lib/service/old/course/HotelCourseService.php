<?php

require_once dirname(__FILE__).'/AbstractCourseService.php';

class HotelCourseService extends AbstractCourseService {

	protected $categoryId = 2;

	public function createRichCourses() {
		$courses = array();
		$courses[] = $this->createRichCourse('htlImg1', 'htlName1');
		$courses[] = $this->createRichCourse('htlImg2', 'htlName2');
		$courses[] = $this->createRichCourse('htlImg3', 'htlName3');
		$courses[] = $this->createRichCourse('htlImg4', 'htlName4');
		$courses[] = $this->createRichCourse('htlImg5', 'htlName5');
		$courses[] = $this->createRichCourse('htlImg6', 'htlName6');
		return $courses;
	}
}
