<?php
/**
 * プログラム機能説明：
 * 1.DBの三つ間以前のデータを削除
 *（例：今日2010/08/06→「2010/08/05、08/04、08/03」データを残り、その以外が全部削除）
 * 2.システム日付よりDBから前日のデータをCSVファイルに吐き出す
 * 3.ｃｓｖファイルをtar.gz形式に変更して、FTPに転送します。
 * 4.ｆｔｐサーバーの三つ間以前のファイルを削除
 * */
ini_set( "display_errors", "On");
date_default_timezone_set("Asia/Tokyo");

$log_dir = "/var/www/html/test/photo_db/log/";

$FTP_HOST = "www.chapan.biz";// ftpサーバーのIP	
$FTP_PORT = "21";// ｆｔｐサーバーのポート
$FTP_USR  = "admin";// ｆｔｐユーザー名	
$FTP_PSD  = "admin";// ｆｔｐパスワード
$FTP_DIR  = "testftp";// ｆｔｐディレクトリ（○○.tar.gz）

$SERVER	  = "mysql88.heteml.jp";// データベースのサーバーIP
$DB_NAME  = "_ximage";// データベース名前	
$DB_USR   = "_ximage";// データベースのユーザー	
$DB_PSD   = "222222";// データベースのパスワード	
		
imgaedb();

function imgaedb()
{
	writelog("CSVファイルをエクスポット開始");
	
	writelog("三日間前のデータを削除開始");
	/*DBの三つ間以前のデータを削除*/
	delete_3day_data();
	writelog("三日間前のデータを削除終了");

//	writelog("前日のデータをエクスポット開始");
//	/*システム日付よりDBから前日のデータをCSVファイルに吐き出す*/
//	$upload_csv_name = get_1day_data();
//	writelog("前日のデータをエクスポット終了");
//
//	writelog("CSVファイル→tarファイルに変更開始");
//	/*ｃｓｖファイルをtar.gz形式に変更する*/
//	$new_tar_name = tar_csv($upload_csv_name);
//	if($new_tar_name===false)
//	{
//		writelog("tar failure");
//		writelog("CSVファイルのエクスポットは異常に終了しました。");
//		exit(0);
//	}
//	else
//	{
//		writelog("CSVファイル→tarファイルに変更終了");
//		
//		writelog("CSVファイルをFTPに転送し、FTPの三日間前のデータを削除　開始");
//		/*ｆｔｐサーバーの三つ間以前のファイルを削除*/	
//		put_csv_delet($new_tar_name);
//		writelog("CSVファイルをFTPに転送し、FTPの三日間前のデータを削除　終了");
//		
//		writelog("ローカールCSVファイルとtarファイルを削除　開始");
//		/*ローカールのcsvとtarファイルを削除*/
//		//delete_lcoal_file($upload_csv_name);
//		delete_lcoal_file($new_tar_name);
//		writelog("ローカールCSVファイルとtarファイルを削除　終了");
//	}
//	writelog("CSVファイルをエクスポット終了");
}

function dbconnect()
{	
	global $SERVER,$DB_NAME,$DB_USR,$DB_PSD;

	$connect = @mysql_connect($SERVER,$DB_USR,$DB_PSD) or die('can\'t connect to the db');
	@mysql_select_db($DB_NAME, $connect) or exit('can\'t find the db');
	return $connect;
}
function delete_3day_data()
{
	$last_3_day_sor  = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
	$last_3_day = date("Y-m-d",$last_3_day_sor);
	dbconnect();
	$sql = "delete from tbl_log where acc_date<'$last_3_day'";
	$resultSet = mysql_query($sql);
}
function get_1day_data()
{
	$last_2_day_sor = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
	$last_2_day = date("Y-m-d",$last_2_day_sor);
	$today = date("Y-m-d",strtotime("now"));
	$yesterday = date("Ymd",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"))) ;
	dbconnect();
	$sql = "select count(id) as count from tbl_log  where acc_date >='$last_2_day' and acc_date <'$today'";
	$resultSet = mysql_query($sql);
	$row = mysql_fetch_assoc($resultSet);
	$count = $row['count'];
	
	$upload_csv_name = "";//アップロードファイル名
	$upload_csv_name = "calendar_log_".$yesterday.".csv";//アップロードファイル名
	
	/*既に作成された場合に削除します*/
	if(file_exists($upload_csv_name))
	{
		delete_lcoal_file($upload_csv_name);
	}
	
	
	for($i=0;$i<=$count;$i=$i+20000)
	{	
		$data_nums   = 20000;
		$sql = "select acc_course_id,acc_date from tbl_log where acc_date >='$last_2_day' and acc_date <'$today' limit $i , $data_nums";
		$resultSet = mysql_query($sql);
		$log_arr = array();
		$j = 0;
		while($row = mysql_fetch_assoc($resultSet))
		{
			$log_arr[$j]["acc_course_id"] = $row["acc_course_id"];
			$log_arr[$j]["acc_date"] = $row["acc_date"];
			$j++;
		}
		put_csv($log_arr,$upload_csv_name);
	}
	return $upload_csv_name;
}
function put_csv($log_arr,$upload_csv_name)
{
	
	$fp=fopen($upload_csv_name, 'a');
	foreach($log_arr as $key=>$val)
	{
		_fputcsv($fp,$val,",","\"");
	}
	fclose($fp);

}

function put_csv_delet($new_tar_name)
{	
	global $FTP_HOST,$FTP_PORT,$FTP_USR,$FTP_PSD,$FTP_DIR;

	$conn = ftp_connect("$FTP_HOST",$FTP_PORT) or die("Could not connect");
	ftp_login($conn,"$FTP_USR","$FTP_PSD");

	ftp_chdir($conn,"$FTP_DIR");
	ftp_put($conn,$new_tar_name, $new_tar_name, FTP_BINARY);	
	
	ftp_cdup($conn);
	$list_ftp_files = array();
	$today = date("Y-m-d",strtotime("now"));
	$list_ftp_files = ftp_nlist($conn,$FTP_DIR);
	
	$delete_arr = array();
	foreach ($list_ftp_files as $key=>$value)
	{
		$tmp_value =  substr($value,13,8);
		$t2 = strtotime($today) - strtotime($tmp_value);
	
		if(intval($t2/86400) > 3)
		{
			$delete_arr[] = $value; 
		}
	}			
	
	ftp_chdir($conn,"$FTP_DIR");
	
	foreach ($delete_arr as $key=>$value)
	{	
		ftp_delete($conn,$value);
	}
	ftp_close($conn);	
}

function tar_csv($upload_csv_name)
{
	try
	{	
		
		$tmp_name = explode(".",$upload_csv_name);
		$new_tar_name= $tmp_name[0].".tar.gz";
		
		if(file_exists($new_tar_name))
		{
			delete_lcoal_file($new_tar_name);
		}
		
		$cmd= "tar -czf ".$new_tar_name." ".$upload_csv_name;
		exec($cmd);

		return $new_tar_name;
	}
	catch(Exception $e)
	{
		return false;
	}	
	
}

function delete_lcoal_file($name)
{

	if (file_exists($name))
	{
		unlink($name);
	}

}

function _fputcsv($filePointer,$dataArray,$delimiter,$enclosure)
{
  // Write a line to a file
  // $filePointer = the file resource to write to
  // $dataArray = the data to write out
  // $delimeter = the field separator
 
  // Build the string
  $string = "";
 
  // No leading delimiter
  $writeDelimiter = FALSE;
  foreach($dataArray as $dataElement)
   {
   // Replaces a double quote with two double quotes
   $dataElement=str_replace("\"", "\"\"", $dataElement);
  
   // Adds a delimiter before each field (except the first)
   if($writeDelimiter) $string .= $delimiter;
  
   // Encloses each field with $enclosure and adds it to the string
   $string .= $enclosure . $dataElement . $enclosure;
  
   // Delimiters are used every time except the first.
   $writeDelimiter = TRUE;
   } // end foreach($dataArray as $dataElement)
 
  // Append new line
  $string .= "\n";
 
  // Write the string to the file
  fwrite($filePointer,$string);
}
  
function writelog($str) {
	global $log_dir;

	$filename = "export_csv".date("Ymd").".log";
	$filelog = fopen($log_dir.$filename,"a");
	fwrite($filelog,$str."\r\n");
	fclose($filelog);
}

