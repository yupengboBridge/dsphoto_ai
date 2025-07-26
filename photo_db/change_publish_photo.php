<?php
require_once('./config.php');
require_once('./lib.php');

date_default_timezone_set('Asia/Tokyo');

$s_login_id = array_get_value($_GET,'login_id' ,"");

$db_link = db_connect();

$photo_id = isset($_GET['photo_id'])?$_GET['photo_id']:"";
if(empty($photo_id)){
    echo "パラメータ「photo_id」は不足です。";exit();
}

$is_publish = isset($_GET['is_publish'])?$_GET['is_publish']:"0";

$update_user_id = empty($s_login_id) ? 0 : $s_login_id;
$sql = "UPDATE photoimg SET is_publish = ?,update_user=? WHERE photo_id = ?";
$stmt = $db_link->prepare($sql);
$stmt->bindParam(1,$is_publish);
$stmt->bindParam(2,$update_user_id);
$stmt->bindParam(3,$photo_id);
try{
    $result = $stmt->execute();
    if ($result == true){
        $log = "更新時刻：".date('Y-m-d H:i:s').PHP_EOL;
        $log .= "更新ユーザー：".$s_login_name."(".$s_login_id.")".PHP_EOL;
        $log .= "更新画像ID：".$photo_id.PHP_EOL;
        if($is_publish == 1){
            $log .= "更新公開状態：公開".PHP_EOL;
        }else{
            $log .= "更新公開状態：制限付き".PHP_EOL;
        }
        $log .= "更新結果：成功に更新しました。".PHP_EOL;
        file_put_contents("./log/change_publish_photo.log",$log.PHP_EOL.PHP_EOL,FILE_APPEND);
        echo "200";
    }else{
//        $errorInfo = $stmt->errorInfo();
//        echo "SQL Error: " . $errorInfo[2];
        $log = "更新時刻：".date('Y-m-d H:i:s').PHP_EOL;
        $log .= "更新ユーザー：".$s_login_name."(".$s_login_id.")".PHP_EOL;
        $log .= "更新画像ID：".$photo_id.PHP_EOL;
        if($is_publish == 1){
            $log .= "更新公開状態：公開".PHP_EOL;
        }else{
            $log .= "更新公開状態：制限付き".PHP_EOL;
        }
        $log .= "更新結果：更新失敗しました。".PHP_EOL;
        file_put_contents("./log/change_publish_photo.log",$log.PHP_EOL.PHP_EOL,FILE_APPEND);
        echo "500";
    }
}
catch(Exception $e)
{
    $msg = $e->getMessage();
    //echo $msg;
    $log = "更新時刻：".date('Y-m-d H:i:s').PHP_EOL;
    $log .= "更新ユーザー：".$s_login_name."(".$s_login_id.")".PHP_EOL;
    $log .= "更新画像ID：".$photo_id.PHP_EOL;
    if($is_publish == 1){
        $log .= "更新公開状態：公開".PHP_EOL;
    }else{
        $log .= "更新公開状態：制限付き".PHP_EOL;
    }
    $log .= "更新結果：更新失敗しました。(".$msg.")".PHP_EOL;
    file_put_contents("./log/change_publish_photo.log",$log.PHP_EOL.PHP_EOL,FILE_APPEND);
    echo "500";
}
