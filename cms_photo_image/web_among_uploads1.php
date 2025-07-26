<?php
header('Content-Type: application/json');
require_once('soap_login_image_batch_limi2.php');
session_start();
$s_login_id = array_get_value($_SESSION,'login_id' ,"");
$s_login_name = array_get_value($_SESSION,'user_name' ,"");
$successList = [];
$errorList = [];
$repeatList = [];
$DBerrorList = [];
$csvcontentList = $_GET["csvcontentList"];
//$loginInfo = $_GET["s_logininfo"];
$loginInfo = $s_login_id.";".$s_login_name;
$db_link = db_connect();
foreach($csvcontentList as $csvcontent){
    $bud_photo_no = explode("\t",$csvcontent)[5];
    // 判断是否存在
    $sql = "SELECT * FROM photoimg WHERE bud_photo_no = '{$bud_photo_no}'";


    $path = dirname(__FILE__);
    $tmp_file = $path.'/webLimited/'.$bud_photo_no;
    $target_file = $path.'/limited/'.$bud_photo_no;
    file_put_contents('webCsvUpload_log.txt', "$tmp_file".'->'.$target_file.PHP_EOL,FILE_APPEND);
    copy($tmp_file,$target_file);

    $stmt = $db_link->prepare($sql);
    $result = $stmt->execute();
    if ($result == true)
    {
        $icount = $stmt->rowCount();
        if ($icount > 0)
        {
            array_push($repeatList,$bud_photo_no);
            continue;
        }
    }else{
        array_push($DBerrorList,$bud_photo_no);
        continue;
    }
    $return = uploadfiles($csvcontent,$loginInfo);
    if($return == "OK"){
        array_push($successList,$bud_photo_no);
    }
    if($return == "ERR"){
        array_push($errorList,$bud_photo_no);
    }
    unlink($tmp_file);
}
$res = [$successList,$errorList,$repeatList,$DBerrorList];
echo json_encode($res);
