<?php
class CrashException extends BaseException
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);

		$this->_parseExcetiopn($message);
	}

	public function _parseExcetiopn($message){
        $this->taskResult = TaskResult::getInstance();
        $this->taskResult->endTime = time();
        $this->taskResult->isCrash = true;
        $this->taskResult->crashMessage = $message;

		//写log
		$log = LOG::getInstance();
		$log->log('タスクがクラッシュしました、クラッシュの原因：'.$this->message);

		//发邮件
		$service = new TaskService();
        $service->sendMail($this->taskResult);
	}
}
