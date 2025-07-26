<?php

require_once dirname(__FILE__).'/AbstractCourseService.php';

class InfoCourseService extends AbstractCourseService {

	protected $categoryId = 6;

	public function createRichCourses() {
		$courses = array();
		$courses[] = $this->createRichCourse('mainImg1', 'mainImgTxt1');
		$courses[] = $this->createRichCourse('mainImg2', 'mainImgTxt2');
		$courses[] = $this->createRichCourse('mainImg3', 'mainImgTxt3');
		$courses[] = $this->createRichCourse('mainImg4', 'mainImgTxt4');
		$courses[] = $this->createRichCourse('mainImg5', 'mainImgTxt5');
		$courses[] = $this->createRichCourse('mainImg6', 'mainImgTxt6');
		$courses[] = $this->createRichCourse('mainImg7', 'mainImgTxt7');
		$courses[] = $this->createRichCourse('mainImg8', 'mainImgTxt8');
		$courses[] = $this->createRichCourse('mainImg9', 'mainImgTxt9');
		$courses[] = $this->createRichCourse('mainImg10', 'mainImgTxt10');
		$courses[] = $this->createRichCourse('pointImg', 'pointAlt');
		return $courses;
	}
}
