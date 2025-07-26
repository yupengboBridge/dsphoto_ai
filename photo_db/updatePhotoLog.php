<?php
error_reporting(E_ALL&~E_NOTICE);
set_time_limit(0); 
require_once('config.php');
date_default_timezone_set('Asia/Tokyo');
header("Content-type: text/html; charset=utf-8"); 

//测试
$file_path = dirname(__FILE__)."/log/back";

$dbH = mysql_connect($db_host, $db_user, $db_password) or die('Could not connect to MySQL server.<br>' . mysql_error());
mysql_select_db($db_name) or die('Could not select database.<br>' . mysql_error());
mysql_query("SET NAMES UTF8");

echo "Begin";
if(is_dir($file_path)){
	$file_list = readPath($file_path);
	writeToDb($file_list,$file_path);
}elseif(is_file($file_path)){
	readFileContent($file_path);
}

echo "end";
function readPath($path){
	$file_list = array();
	$handler = opendir($path);
	while( ($filename = readdir($handler)) !== false )
	{
		  if($filename != "." && $filename != "..")
	      {
	      	   array_push($file_list,$filename);
		  }
	}
	closedir($handler);
	rsort($file_list);
	return  $file_list;
}

function writeToDb($file_list,$path){
	if(count($file_list)<=0){
		return;
	}
	foreach($file_list as $filename){
		readFileContent($path.'/'.$filename);
	}
}

function readFileContent($filename){

	$f= fopen($filename,"r");
	while (!feof($f))
	{
	  $log_info = fgets($f);
	  insertIntoPhotoImg($log_info);
	}
	fclose($f);
}

function insertIntoPhotoImg($log_info){
	## Connect to a local database server (or die) ##
	global $dbH;

	$arr_log = array();
	if(!empty($log_info)){
		$arr_log = explode(',',$log_info);
	}
	$check_sql = "select * from photo_log 
					where register_date ='".$arr_log[0]."' 
					and login_account='".$arr_log[1]."' 
					and login_account_name='".$arr_log[2]."' 
					and photo_mno='".$arr_log[3]."' 
					and photo_name='".$arr_log[4]."' 
					and photo_explanation='".$arr_log[5]."' 
					and bud_photo_no='".$arr_log[6]."' 
					and dfrom='".$arr_log[7]."' 
					and dto='".$arr_log[8]."' 
					and log_flag='".$arr_log[9]."' 
					and registration_person='".$arr_log[10]."'";

	$result = mysql_query($check_sql,$dbH);
	if(mysql_num_rows($result)>0){
	
		return ;
	}
	
	$update_sql = "update photo_log set 
					register_date ='".$arr_log[0]."' 
					,login_account='".$arr_log[1]."' 
					,login_account_name='".$arr_log[2]."' 
					,photo_mno='".$arr_log[3]."' 
					,photo_name='".$arr_log[4]."' 
					,photo_explanation='".$arr_log[5]."' 
					,dfrom='".$arr_log[7]."' 
					,dto='".$arr_log[8]."' 
					,log_flag='".$arr_log[9]."' 
					,registration_person='".$arr_log[10]."'
					where bud_photo_no='".$arr_log[6]."'";
	mysql_query($update_sql,$dbH);
}
