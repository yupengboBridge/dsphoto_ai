<?php

class RichCourse {

	public $hei;
	public $courseId;
	// 方面
	public $destinationCode;
	// コース名
    public $courseName;
    // スタッフコード
    public $courseStaffCode;
    // オフィスコード
    public $courseOfficeCode;
	public $richCourseCategoryId;
	// ファイル名1つ目の数字
    public $fstNm;
    // ファイル名2つ目の数字
    public $sndNm;
    // 旅行日、段数
    public $exNm;
	public $photoNo;
	public $overview;
	// 識別用更新日
    public $identDay;
	public $updatedAt;
	
	public function __construct($hei,
                                $courseId,
                                $destinationCode,
                                $courseName,
                                $courseStaffCode,
                                $courseOfficeCode,
                                $richCourseCategoryId,
                                $fstNm,
                                $sndNm,
                                $exNm,
                                $photoNo,
                                $overview,
                                $identDay,
                                $updatedAt) {
		$this->hei = $hei;
		$this->courseId = $courseId;
        $this->destinationCode = $destinationCode;
        $this->courseName = $courseName;
        $this->courseStaffCode = $courseStaffCode;
        $this->courseOfficeCode = $courseOfficeCode;
		$this->richCourseCategoryId = $richCourseCategoryId;
        $this->fstNm = $fstNm;
        $this->sndNm = $sndNm;
        $this->exNm = $exNm;
		$this->photoNo = $photoNo;
		$this->overview = $overview;
        $this->identDay = $identDay;
		$this->updatedAt = $updatedAt;
	}
}
