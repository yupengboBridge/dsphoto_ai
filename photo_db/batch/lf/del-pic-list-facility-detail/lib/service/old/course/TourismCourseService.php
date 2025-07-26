<?php

require_once dirname(__FILE__).'/AbstractCourseService.php';

class TourismCourseService extends AbstractCourseService {

	protected $categoryId = 1;

	public function createRichCourses() {
		$courses = array();
		$courses[] = $this->createRichCourse('mapImg1', 'mapAlt1');
		$courses[] = $this->createRichCourse('tourSpotImg1', 'tourSpotTtl1', 'A');
		$courses[] = $this->createRichCourse('tourSpotImg1', 'tourSpotTtl1', 'B');
		$courses[] = $this->createRichCourse('tourSpotImg2', 'tourSpotTtl2', 'B');
		$courses[] = $this->createRichCourse('tourSpotImg3', 'tourSpotTtl3', 'B');
		$courses[] = $this->createRichCourse('tourSpotImg1', 'tourSpotTtl1', 'C');
		$courses[] = $this->createRichCourse('tourSpotImg2', 'tourSpotTtl2', 'C');
		$courses[] = $this->createRichCourse('tourSpotImg3', 'tourSpotTtl3', 'C');
		$courses[] = $this->createRichCourse('tourSpotImg1', 'tourSpotAlt1', 'D');
		$courses[] = $this->createRichCourse('tourSpotImg2', 'tourSpotTtl1', 'D');
		return $courses;
	}
}
