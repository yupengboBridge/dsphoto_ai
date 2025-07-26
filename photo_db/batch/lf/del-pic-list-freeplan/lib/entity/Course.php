<?php

class Course {

	public $hei;
	public $courseId;

	public function __construct($hei, $courseId) {
		$this->hei = $hei;
		$this->courseId = $courseId;
	}
}
