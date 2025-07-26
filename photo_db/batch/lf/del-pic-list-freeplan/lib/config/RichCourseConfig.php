<?php

require_once 'Config.php';

class RichCourseConfig extends Config {

	public $apiUrlAll;
	public $apiUrlRecent;
	public $csvUrl;
	public $productionUrl;
	public $inspectionUrl;
	public $expirationDays;
    public $csvKeepDays;
	public $domesticSearchApiUrl;
	public $internationalSearchApiUrl;
	public $productionUrlD;
	public $csvPath;
    public $difcsvPath;
    public $difcsvPathCr;
    public $difcsvFilePath;
	public $rpp;
	public $requestChunk;
	public $csvMainField;
	public $heiList;
	public $csvList;
	public $categoryList;
    public $iDestMaster;
    public $dDestMaster;
	public $senmonICsvPath;
    public $senmonDCsvPath;
	public $senmonICategory;
	public $senmonDCategory;


}
