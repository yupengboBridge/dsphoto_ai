<?php

class TaskService
{
	private $mail;
	public function sendMail($task_result){
		$this->mail = new Mail();
		$this->mail->sendMail('MALL-DS連携エラーのお知らせ',$this->createContent($task_result));
	}

	public function createContent($task){
		$content = '担当者殿'.PHP_EOL.PHP_EOL;
        $content .= 'MALL-DSデータ連携でエラーとなり下記が登録されませんでした。'.PHP_EOL.PHP_EOL;
		$content .= '処理開始日時：'.date("Y-m-d H:i:s", $task->startTime).PHP_EOL;
        $content .= '処理終了日時：'.date("Y-m-d H:i:s", $task->startTime).PHP_EOL.PHP_EOL;
		if($task->isCrash){
            $content .= 'エラー内容:' .$task->crashMessage.PHP_EOL.PHP_EOL;
		}elseif ($task->isError){
			if(count($task->errorMsg)){
				$content .= 'エラー内容:'.PHP_EOL;
				$content .=implode(PHP_EOL,$task->errorMsg).PHP_EOL.PHP_EOL;
			}
            if(count($task->errorRows)){
                $content .= 'エラー行目:'.implode(',',$task->errorRows).PHP_EOL.PHP_EOL;
            }
        }
        return $content;
	}
}
