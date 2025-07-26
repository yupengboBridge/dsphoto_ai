<?php
if (PHP_SAPI === 'cli') {
    $root_path = str_replace("/malltools","",dirname(__FILE__));
} else {
    $root_path = "..";
}

require_once ($root_path.'/config.php');
require_once ($root_path.'/lib.php');
require_once ($root_path.'/malltools/mall_image_batch.php');
require_once ($root_path.'/malltools/Config.php');
require_once ($root_path.'/malltools/Img.php');
require_once ($root_path.'/malltools/Log.php');
require_once ($root_path.'/malltools/service/TaskService.php');
require_once ($root_path.'/malltools/service/S3/S3.php');
require_once ($root_path.'/malltools/TaskResult.php');
require_once ($root_path.'/malltools/exception/BaseException.php');
require_once ($root_path.'/malltools/exception/WarningException.php');
require_once ($root_path.'/malltools/exception/CrashException.php');
require_once ($root_path.'/malltools/Mail.php');


class Task
{

    private $config;
    private $taskResult;
    private $taskService;
    private $s3;
    private $s3Config;
    private $image;
    private $rows = 1;
    public $cropConfig;
    public $connect;
    public $extension_day;
    public $download_path;
    public $image_path;
    public $csv_path;
    public $processed_path;
    public $crop_save_path;
    public $crop_config_file;
    public $csv_name;
    public $download_image_dir_name;
	public $cmykIccPath;
	public $srgbIccPath;

    //初期化クラス
    public function __construct()
    {
        global $root_path;

        try {
            $this->crop_config_file = $root_path.'/malltools/config/config.ini';
            $this->download_path = $root_path.'/malltools/download/';
            $this->processed_path = $root_path.'/malltools/download/processed/';
            $this->download_image_dir_name = "images";
            $this->image_path = $root_path.'/malltools/download/'.$this->download_image_dir_name.'/';
            $this->csv_name = "mall_dsphoto_list.csv";
            $this->csv_path = $root_path.'/malltools/download/csv/'.$this->csv_name;
            $this->crop_save_path = $root_path.'/malltools/webLimited/';

            $this->config = new Config($this->crop_config_file); //Config
            $this->s3Config = $this->config->readConfig('S3');
            $this->s3 = new S3($this->s3Config['key'], $this->s3Config['secret'], $this->s3Config['region']);
            $this->image = new Img();
			
			$this->cmykIccPath = $root_path.'/malltools/icc/JapanColor2011Coated.icc';
			$this->srgbIccPath = $root_path.'/malltools/icc/sRGB_v4_ICC_preference_displayclass.icc';
			
			$this->image->cmykIccPath = $this->cmykIccPath;
			$this->image->srgbIccPath = $this->srgbIccPath;
			
            $this->taskService = new TaskService();
            $this->taskResult = TaskResult::getInstance();
            $this->taskResult->startTime = time();
            $this->connect = db_connect();
            $this->cropConfig = $this->config->readConfig('crop');
            $this->extension_day = date('Y-m-d H:i:s', strtotime('+3 year'));
        } catch (Exception $e) {
            CommonUtil::writeUploadPhotoImageLog("Task:::__construct:::".$e->getMessage(),$root_path);
            throw new CrashException($e->getMessage());
        }
    }

    //タスクの実行
    public function run($argv){
        global $root_path;

        $manual = isset($argv[1]) ? $argv[1] : "";
        // try {
        //     $this->s3->restoreObject($this->s3Config['bucket'], "20241127120004");
        // }catch (Exception $e){
        //     $error_message = "S3バケットにはディレクトリ移動するとき、エラーが発生しました。エラーメッセージ：".$e->getMessage();
        //     CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
        //     $this->taskResult->isError = true;
        //     $this->taskResult->setErrorMsg($error_message);
        // }
        // exit();

        // $processing_dir = "20241127150004";
        // try {
        //     $this->s3->moveObject($this->s3Config['bucket'], $processing_dir, true);
        // }catch (Exception $e){
        //     $error_message = "S3バケットにはディレクトリ移動するとき、エラーが発生しました。エラーメッセージ：".$e->getMessage();
        //     CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
        //     $this->taskResult->isError = true;
        //     $this->taskResult->setErrorMsg($error_message);
        // }
        // exit();

        if(empty($manual)){
            $sql_exists = "SELECT COUNT(*) AS CNT FROM mall_task WHERE task_start_datetime IS NOT NULL ";
            $sql_exists .= " AND task_start_datetime <> '' AND (task_end_datetime IS NULL OR task_end_datetime = '')";
            // SQL文法のチェック
            $stmt = $this->connect->prepare($sql_exists);
            $result = $stmt->execute();
            // 実行結果をチェックします。
            if ($result == true)
            {
                $pcnt = $stmt->fetch(PDO::FETCH_ASSOC);
                $tmpcnt = $pcnt['CNT'];

                //既に存在の場合
                if ((int)$tmpcnt > 0) {
                    $error_message = "MALLと連携タスクは進行中なので、後ほど試してみてください。";
                    CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                    return;
                }
            }

            $sql = "SELECT id FROM mall_task WHERE task_start_datetime IS NULL OR task_start_datetime = ''";
            $sql .= " ORDER BY id ASC LIMIT 1";
            // SQL文法のチェック
            $stmt = $this->connect->prepare($sql);
            $result = $stmt->execute();
            // 実行結果をチェックします。
            if ($result == false) {
                $error_message = "MALL連携タスクが見つかりませんでした.";
                CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                return;
            }

            $task_info = $stmt->fetch(PDO::FETCH_ASSOC);
            $task_id = "";
            if(isset($task_info['id'])){
                $task_id = $task_info['id'];
            }
            if(empty($task_id) || $task_id == 0) {
                $error_message = "MALL連携タスクが見つかりませんでした.";
                CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                return;
            }
        }

        if(empty($manual)){
            $processing_dirs = [];

            $processed_dirs = $this->s3->getBucketPaths($this->s3Config['bucket'],"processed",true,true,false);
            $dirs = $this->s3->getBucketPaths($this->s3Config['bucket'],"",true,false,true);
            if(count($dirs) == 0){
                $error_message = "S3バケットには連携必要なディレクトリが見つかりませんでした.";
                CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                return;
            }

            foreach($dirs as $dir_one){
                if(!in_array($dir_one,$processed_dirs)){
                    $processing_dirs[] = $dir_one;
                }
            }

            if(count($processing_dirs)==0){
                $error_message = "S3バケットには出力したディレクトリが既に処理済みでした.";
                CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                return;
            }
        }

        if($manual=="M"){
            $processing_dirs[] = "20240101";
        }

        for($i=0;$i<count($processing_dirs);$i++){
            $processing_dir = $processing_dirs[$i];
            $this->rows = 1;
            try{
                
                if(empty($manual)){
                    $this->downLoadFile($processing_dir);

                    if(!is_file($this->csv_path)){
                        $error_message = "CSVファイルが存在しません.:::".$this->csv_path;
                        CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                        $this->taskResult->isError = true;
                        $this->taskResult->setErrorMsg($error_message);
                        continue;
                    }
                }

                setlocale(LC_ALL, 'ja_JP.utf8');
                $handle = fopen($this->csv_path, 'rb');
                if (!$handle) {
                    $error_message = "CSVファイルを開くのに失敗しました.:::".$this->csv_path;
                    CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                    $this->taskResult->isError = true;
                    $this->taskResult->setErrorMsg($error_message);
                    continue;
                }

                if(empty($manual)){
                    // SQL文法のチェック
                    $sql = "update mall_task set task_start_datetime=now() where id=".$task_id;
                    $stmt = $this->connect->prepare($sql);
                    $result = $stmt->execute();
                    // 実行結果をチェックします。
                    if ($result == false) {
                        $error_message = "TaskID:".$task_id.":::MALL連携タスクのtask_start_datetime更新が失敗しました。";
                        CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                        $this->taskResult->isError = true;
                        $this->taskResult->setErrorMsg($error_message);
                        return;
                    }
                }

                stream_filter_append($handle, 'convert.iconv.CP932/UTF-8');
                while (!feof($handle)) {
                    $buffer = rtrim(fgets($handle));    //日本語ファイルはfgetcsv使うのやめておく
                    if (empty($buffer) | $this->rows == 1) {
                        $this->rows++;
                        continue;
                    }
                    if ($this->rows !== 1) {
                        mb_internal_encoding("UTF8");
                        mb_http_output("UTF8");
                        //$buffer = mb_convert_encoding($buffer, 'UTF-8', 'Shift_JIS');
                        $line = explode("\t", $buffer);
                        try {
                            if($line[0] != 'D'){
                                $validate_flag = self::fileValidate($line);
                                if($validate_flag === false){
                                    $this->rows++;
                                    continue;
                                }
                            }
                            $check_csv_flg = checkCsvData($line);
                            if($check_csv_flg === false){
                                $this->rows++;
                                continue;
                            }
                            if ($line[0]=='U'){
                                $mall_no = funcGetMallNo($line[1]);
                                $photo = CommonPhotoImage::getPhotoByMallNo($this->connect,$mall_no);
                                if(is_null($photo)){
                                    $this->process_add($line);
                                }else{
                                    $this->process_update($line);
                                }
                            }elseif ($line[0]=='A'){
                                $this->process_add($line);
                            }elseif ($line[0]=='D'){
                                deleteImage($line);
                            }else{
                                $error_message = "MALL番号:".$line[1].":::不正なオプション.:::".$this->csv_path;
                                CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                                $this->taskResult->isError = true;
                                $this->taskResult->setErrorMsg($error_message);
                            }
                        } catch (Exception $e) {
                            $this->taskResult->isError = true;
                            array_push($this->taskResult->errorRows, $this->rows);

                            $this->taskResult->setErrorMsg($e->getMessage());
                            CommonUtil::writeUploadPhotoImageLog(
                                "MALL番号【{$line[1]}】にはエラーが発生しました、エラーの原因:" . $e->getMessage(),
                                $root_path
                            );
                        }
                    }
                    $this->rows++;
                }
                fclose($handle);

                if(empty($manual)){ 
                    $currentDate = date('YmdHis');
                    $processed_dir = $this->processed_path.$currentDate;
                    @mkdir($processed_dir, 0777, true);
                    @mkdir($processed_dir."/csv", 0777, true);
                    @rename($this->csv_path,$processed_dir."/csv/".$this->csv_name);
                    $src_image_path = substr($this->image_path,0,-1);
                    $desc_image_path = $processed_dir."/".$this->download_image_dir_name;
                    @mkdir($desc_image_path, 0777, true);
                    //@rename($src_image_path,$desc_image_path);
                    @exec("\cp -rf ".$this->image_path."* ".$desc_image_path."/");
                    @exec("rm -rf ".$this->image_path."*");
                    @exec("rm -rf ".$this->crop_save_path."*");
                    //@mkdir($this->image_path);

                    try {
                        $this->s3->moveObject($this->s3Config['bucket'], $processing_dir, true);
                    }catch (Exception $e){
                        $error_message = "S3バケットにはディレクトリ移動するとき、エラーが発生しました。エラーメッセージ：".$e->getMessage();
                        CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                        $this->taskResult->isError = true;
                        $this->taskResult->setErrorMsg($error_message);
                    }
                }

            }catch(Exception $e){
                CommonUtil::writeUploadPhotoImageLog(
                    "CSVファイルを開くのに失敗しました.:::".$e->getMessage(),
                    $root_path
                );
                $this->taskResult->isError = true;
                $this->taskResult->setErrorMsg($e->getMessage());
            }
        }

        if(empty($manual)){
            // SQL文法のチェック
            $sql = "update mall_task set task_end_datetime=now() where id=".$task_id;
            $stmt = $this->connect->prepare($sql);
            $result = $stmt->execute();
            // 実行結果をチェックします。
            if ($result == false) {
                $error_message = "TaskID:".$task_id.":::MALL連携タスクのtask_end_datetime更新が失敗しました。";
                CommonUtil::writeUploadPhotoImageLog($error_message,$root_path);
                $this->taskResult->isError = true;
                $this->taskResult->setErrorMsg($error_message);
            }
        }

        $this->taskResult->endTime = time();
        if($this->taskResult->isCrash || $this->taskResult->isError){
            $this->taskService->sendMail($this->taskResult);
        }

        return $this->taskResult;
    }

    private function downLoadFile($prefix){
        try{
            $this->s3->downloadAll($this->s3Config['bucket'],$this->download_path,$prefix);
        }catch (Exception $e){
            throw new CrashException('S3 with Error:'.$e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function process_add($data){
        try {
            $this->image = new Img();
            
            $this->image->cmykIccPath = $this->cmykIccPath;
            $this->image->srgbIccPath = $this->srgbIccPath;
            
            $this->image->load($this->image_path.$data[1]);
            $prefix = strtoupper(substr($data[1], 0, 2));
            if ($prefix === 'LF' || $prefix === 'LH') {
                $this->image->cropForLHAndLF($this->cropConfig['width'], $this->cropConfig['height']);
            } else {
                $this->image->crop($this->cropConfig['width'], $this->cropConfig['height']);
            }
            $outputFile = $this->crop_save_path.$data[1];
            $this->image->save($outputFile);
            $this->image->clean();
            insertPhotoImage($data);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function process_update($data){
        try {
            $mall_no = funcGetMallNo($data[1]);
            $photo = CommonPhotoImage::getPhotoByMallNo($this->connect,$mall_no);
            if(is_null($photo)){
                //
            }else{
                $ret_flag = CommonPhotoImage::checkAdditionalConstraints1($this->connect,$photo['bud_photo_no'],$data[11]);
                if($ret_flag){
                    $ext = pathinfo($data[1], PATHINFO_EXTENSION);
                    if("EPS" != strtoupper($ext)){
                        $this->image = new Img();
                        
                        $this->image->cmykIccPath = $this->cmykIccPath;
                        $this->image->srgbIccPath = $this->srgbIccPath;
                    
                        $this->image->load($this->image_path.$data[1]);
                        $prefix = strtoupper(substr($data[1], 0, 2));
                        if ($prefix === 'LF' || $prefix === 'LH') {
                            $this->image->cropForLHAndLF($this->cropConfig['width'], $this->cropConfig['height']);
                        } else {
                            $this->image->crop($this->cropConfig['width'], $this->cropConfig['height']);
                        }
                        $outputFile = $this->crop_save_path.$data[1];
                        $this->image->save($outputFile);
                        $this->image->clean();
                    }
                }

                updatePhotoImage($data);
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    private function fileValidate($line){
        global $root_path;

        try{

            $file_check = true;
            $mall_no = funcGetMallNo($line[1]);
            $photo = CommonPhotoImage::getPhotoByMallNo($this->connect,$mall_no);
            if(is_null($photo)){
                $file_check = true;
            }else{
                $ret_flag = CommonPhotoImage::checkAdditionalConstraints1($this->connect,$photo['bud_photo_no'],$line[11]);
                if($ret_flag){
                    $file_check = true;
                }else{
                    $file_check = false;
                }
            }

            if($file_check === true){
                if(@is_file($this->image_path.$line[1])==false) {
                    $ret_error_msg = "MALL番号:".$line[1].":::画像ファイルが存在しません";
                    CommonUtil::writeUploadPhotoImageLog("画像ファイルが存在しません：".print_r($line,true),$root_path);
                    throw new Exception($ret_error_msg);
                    return false;
                }
                if(@file_exists($this->image_path.$line[1])==false) {
                    $ret_error_msg = "MALL番号:".$line[1].":::画像ファイルが存在しません";
                    CommonUtil::writeUploadPhotoImageLog("画像ファイルが存在しません：".print_r($line,true),$root_path);
                    throw new Exception($ret_error_msg);
                    return false;
                }
                if(@filesize($this->image_path.$line[1]) == 0 || @filesize($this->image_path.$line[1])==false){
                    $ret_error_msg = "MALL番号:".$line[1].":::画像ファイルのサイズが０なので崩れる可能性があります。";
                    CommonUtil::writeUploadPhotoImageLog("画像ファイルのサイズが０なので崩れる可能性があります。".print_r($line,true),$root_path);
                    throw new Exception($ret_error_msg);
                    return false;
                }
            }

            $ext = pathinfo($line[1], PATHINFO_EXTENSION);
            if(
                "AI" == strtoupper($ext) 
                || "TIFF" == strtoupper($ext) 
                || "TIF" == strtoupper($ext) 
                || "PSD" == strtoupper($ext)
            )
            {
                return false;
                //エラーがなく除外する
            }else{
                if("EPS" == strtoupper($ext)){
                    return true;
                }else{
                    if("JPG" != strtoupper($ext) && "PNG" != strtoupper($ext) && "GIF" != strtoupper($ext)){
                        $ret_error_msg = "MALL番号:".$line[1].":::画像ファイルの拡張子が認識できない。「jpg,png,gif」だけ認識できます。";
                        CommonUtil::writeUploadPhotoImageLog("画像ファイルの拡張子が認識できない。「jpg,png,gif」だけ認識できます。".print_r($line,true),$root_path);
                        throw new Exception($ret_error_msg);
                        return false;
                    }
                }

            }
            return true;

        } catch (Exception $e) {
            throw $e;
        }
    }
}
?>
