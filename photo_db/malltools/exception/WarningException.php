<?php

class WarningException extends BaseException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->_parseExcetiopn($message);

    }

    public function _parseExcetiopn($message){
        //写log
        $log = LOG::getInstance();
        $log->log($message);
    }
}

