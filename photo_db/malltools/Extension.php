<?php
if (PHP_SAPI === 'cli') {
    $root_path = str_replace("/malltools","",dirname(__FILE__));
} else {
    $root_path = "..";
}

require_once ($root_path.'/config.php');
require_once ($root_path.'/lib.php');
require_once ($root_path.'/malltools/Config.php');
require_once ($root_path.'/malltools/Log.php');

class Extension
{
	private $config;

	private $log;

    public $connect;


	//初期化クラス
	public function __construct()
	{
        global $root_path;

        try {
            $this->log = Log::getInstance();
            $this->log->logFile = "extension.log";
            $this->config = new Config($root_path.'/malltools/config/config.ini'); //Config
	        $this->extensionConfig = $this->config->readConfig('extension');
            $this->connect = db_connect();
        }catch (Exception $e){
            $this->log->log($e->getMessage());
        }

    }

	//タスクの実行
	public function run(){
        $this->log->log("Extension Start");

		if($this->extensionConfig['enable'] !== 'run'){
			$this->log->log('任务（タスクまたはスクリプト）が起動していません');
			die();
		}
		if(!is_numeric($this->extensionConfig['check_days']) && $this->extensionConfig['check_days']>1){
			$this->log->log('タスクの起動に失敗しました、引数が不正です');
			die();
		}
		if(!is_numeric($this->extensionConfig['extension_days']) && $this->extensionConfig['extension_days']>1){
			$this->log->log('タスクの起動に失敗しました、引数が不正です');
			die();
		}
		$this->setExtensionImgs();
		$this->log->log("Extension End");

	}

	public function setExtensionImgs(){
		$sql = "SELECT photo_id,bud_photo_no,dto FROM photoimg WHERE is_extension = 1 AND DATEDIFF(dto, NOW()) <= :check_days;";
		$stmt = $this->connect->prepare($sql);
        $stmt->bindValue(':check_days', $this->extensionConfig['check_days'], PDO::PARAM_INT);
        $stmt->execute();
		while($img = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$this->setExcetion($img);
		}
	}

	private function setExcetion($img){
        $daysAfter = strtotime("+ {$this->extensionConfig['extension_days']}days",strtotime($img['dto']));
        $dateAfterDays = date("Y-m-d", $daysAfter);
        $this->updateImgDto($img['photo_id'],$dateAfterDays);
        //LOG 日志
        $this->log->log("更新画像[{$img['photo_id']}][{$img['bud_photo_no']}],画像の有効期限:{$img['dto']},更新後の日付:{$dateAfterDays}");
	}

	private function updateImgDto($bpn,$date){
		$sql = "update photoimg set dto = '{$date}'  where photo_id = {$bpn}";
		$stmt = $this->connect->prepare($sql);
		$stmt->execute();
	}
}
?>