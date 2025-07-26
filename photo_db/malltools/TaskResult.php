<?php


class TaskResult
{
    public static $_instance;

	public $startTime;
	public $endTime;
	public $isCrash = false;
	public $crashMessage = '';
	public $isError = false;
	public $errorMsg = [];
	public $errorRows = [];

    private function __construct(){}
    private function __clone(){}

    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setErrorMsg($msg){
        $this->isError = true;
    	if(!in_array($msg,$this->errorMsg)){
    		array_push($this->errorMsg,$msg);
	    }
    }
}
