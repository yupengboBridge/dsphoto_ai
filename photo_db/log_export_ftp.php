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
//$home2_log_dir = "/home2/chroot";
$home2_log_dir = "";
$log_dir = $home2_log_dir."/home/xhankyu/public_html/photo_db/log/";
$karenda_log_csv = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-beacon-csv/";
$karenda_log_tar = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-beacon-tar/";

$private_key = "~/.ssh/id_xhankyu_nopass";
$account = "itec";
$remot_log_dir = "/var/wb/export/protected/users/itec/calendar/";

$private_key_test = "~/.ssh/id_xhankyu_test_nopass";
$account_test = "itec-test";
$remot_log_dir_test = "/var/wb/export_staging/protected/users/itec-test/calendar/";

// $ipaddress = "150.48.8.105";
$ipaddress = "27.121.60.36";
$local_log_tar = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-beacon-tar/";


$SERVER	  = "10.254.2.39";// データベースのサーバーIP
$DB_NAME  = "ximage";// データベース名前	
$DB_USR   = "ximage";// データベースのユーザー	
$DB_PSD   = "kCK!7wu4";// データベースのパスワード	
		
//debug
//put_csv_delet('calendar_log_20100812.tar.gz');
		
imgaedb();

function imgaedb()
{
	writelog("CSVファイルをエクスポット開始");
	
	writelog("三日間前のデータを削除開始");
	/*DBの三つ間以前のデータを削除*/
	delete_3day_data();
	writelog("三日間前のデータを削除終了");

	writelog("前日のデータをエクスポット開始");
	/*システム日付よりDBから前日のデータをCSVファイルに吐き出す*/
	$upload_csv_name = get_1day_data();
	writelog("前日のデータをエクスポット終了");

	writelog("CSVファイル→tarファイルに変更開始");
	/*ｃｓｖファイルをtar.gz形式に変更する*/
	$new_tar_name = tar_csv($upload_csv_name);
	if($new_tar_name===false)
	{
		writelog("tar failure");
		writelog("CSVファイルのエクスポットは異常に終了しました。");
		exit(0);
	}
	else
	{
		writelog("CSVファイル→tarファイルに変更終了");
		
		writelog("CSVファイルをFTPに転送し、FTPの三日間前のデータを削除　開始");
		/*ｆｔｐサーバーの三つ間以前のファイルを削除*/	
		put_csv_delet($new_tar_name);
		writelog("CSVファイルをFTPに転送し、FTPの三日間前のデータを削除　終了");
		
		//writelog("ローカールCSVファイルとtarファイルを削除　開始");
		/*ローカールのcsvとtarファイルを削除*/
		//delete_lcoal_file($upload_csv_name);
		//delete_lcoal_file($new_tar_name);
		//writelog("ローカールCSVファイルとtarファイルを削除　終了");
	}
	writelog("CSVファイルをエクスポット終了");
}

function dbconnect()
{	
	global $SERVER,$DB_NAME,$DB_USR,$DB_PSD;

	$connect = @mysqli_connect($SERVER,$DB_USR,$DB_PSD) or die('can\'t connect to the db');
	@mysqli_select_db($connect, $DB_NAME) or exit('can\'t find the db');
	return $connect;
}
function delete_3day_data()
{
	$last_3_day_sor  = mktime(0, 0, 0, date("m")  , date("d")-2, date("Y"));
	$last_3_day = date("Y-m-d",$last_3_day_sor);
	$connect = dbconnect();
	$sql = "delete from tbl_log where acc_date<'$last_3_day'";
	//print $sql;
	$resultSet = mysqli_query($connect,$sql);
}
function get_1day_data()
{
	global $karenda_log_csv;

	$last_2_day_sor = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));
	$last_2_day = date("Y-m-d",$last_2_day_sor);
	$today = date("Y-m-d",strtotime("now"));
	$yesterday = date("Ymd",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"))) ;
	$connect = dbconnect();
	$sql = "select count(id) as count from tbl_log  where acc_date >='$last_2_day' and acc_date <'$today'";
	$resultSet = mysqli_query($connect,$sql);
	$row = mysqli_fetch_assoc($resultSet);
	$count = $row['count'];
	
	$upload_csv_name = "";//アップロードファイル名
	$upload_csv_name = $karenda_log_csv."calendar_log_".$yesterday.".csv";//アップロードファイル名
	
	/*既に作成された場合に削除します*/
	if(file_exists($upload_csv_name))
	{
		delete_lcoal_file($upload_csv_name);
	}
	
	
	for($i=0;$i<=$count;$i=$i+20000)
	{	
		$data_nums   = 20000;
		$sql = "select acc_course_id,acc_date from tbl_log where acc_date >='$last_2_day' and acc_date <'$today' order by acc_date limit $i , $data_nums";
		$resultSet = mysqli_query($connect,$sql);
		$log_arr = array();
		$j = 0;
		while($row = mysqli_fetch_assoc($resultSet))
		{
			$log_arr[$j]["acc_course_id"] = $row["acc_course_id"];
			$log_arr[$j]["acc_date"] = date("Y/m/d H:i:s",strtotime($row["acc_date"]));;
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
	global $private_key,$account,$ipaddress,$remot_log_dir,$local_log_tar,$karenda_log_csv,$karenda_log_tar;
	global $private_key_test,$account_test,$remot_log_dir_test;

	$file_scp_name = basename($new_tar_name);

	$com ="scp -i ".$private_key." -p ".$local_log_tar.$file_scp_name." ".$account."@".$ipaddress.":".$remot_log_dir.$file_scp_name;
	//検証機もアップ
	//$com2 ="scp -i ".$private_key_test." -p ".$local_log_tar.$file_scp_name." ".$account_test."@".$ipaddress.":".$remot_log_dir_test.$file_scp_name;
	exec($com.' 2>&1',$ans);
	sleep(1);
	//exec($com2.' 2>&1',$ans2);

	//応答ログをとる
	//writelog($ans[0]);
	//writelog($ans2[0]);
	
	$dossier = opendir($karenda_log_csv);
	$file_name = "";
	$today = date("Y-m-d",strtotime("now"));
	while ($Fichier = readdir($dossier))
	{
		if ($Fichier != "." && $Fichier != ".." && $Fichier != "Thumbs.db")
		{
			$file_name = strtolower($Fichier);
			if (!is_dir ($karenda_log_csv.$file_name))
			{
				$tmp_value =  substr($file_name,13,8);
				$t2 = strtotime($today) - strtotime($tmp_value);
				if(intval($t2/86400) > 3)
				{
					unlink($karenda_log_csv.$file_name);
				}
			}
		}
	}

	$dossier = opendir($karenda_log_tar);
	$file_name = "";
	$today = date("Y-m-d",strtotime("now"));
	while ($Fichier = readdir($dossier))
	{
		if ($Fichier != "." && $Fichier != ".." && $Fichier != "Thumbs.db")
		{
			$file_name = strtolower($Fichier);
			if (!is_dir ($karenda_log_tar.$file_name))
			{
				$tmp_value =  substr($file_name,13,8);
				$t2 = strtotime($today) - strtotime($tmp_value);
				if(intval($t2/86400) > 3)
				{
					unlink($karenda_log_tar.$file_name);
				}
			}
		}
	}
}

function tar_csv($upload_csv_name)
{
	global $karenda_log_tar;

	try
	{	
		
		$tmp_name = explode(".",$upload_csv_name);
		$new_tar_name= $tmp_name[0].".tar.gz";
		
		if(file_exists($new_tar_name))
		{
			delete_lcoal_file($new_tar_name);
		}

		$cmd= "cd ".dirname($upload_csv_name).";tar -cvzf ".basename($new_tar_name)." ".basename($upload_csv_name).";mv ".$new_tar_name." ".$karenda_log_tar.basename($new_tar_name);
		//echo $cmd."\r\n";
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

