<?php
########################################################################
#
#  DBから拠点タブのアクセス情報をCSVに生成
# 
#  DB:ximage table:tbl_kyotentab_log
# 
#  対象ページ
#  ・６大TOP,専門店
#
#  【プログラム機能説明】
#  ・DBよりデータ抽出
#  ・抽出したデータより集計
#  ・３ヶ月前のDBデータを削除、出力したｃｓｖを削除（バックアップはdaily=5 weekly=３　montly=2） 
#   
#  @copyright  2011 BUD International houda
#  @version    1.0.0
########################################################################

/*********
* 初期設定
**********/
ini_set('error_reporting', E_ALL);
ini_set( "display_errors", "On");
date_default_timezone_set("Asia/Tokyo");
//$home2_log_dir = "/home2/chroot";
$home2_log_dir = "";	//テスト用

//ログ
$log_dir = $home2_log_dir."/home/xhankyu/public_html/photo_db/log/";
//CSV配置
$karenda_log_csv = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-beacon-csv/";
//tar配置
$karenda_log_tar = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-beacon-tar/";

//DBサーバ
$SERVER	  = "10.254.2.63";// データベースのサーバーIP
$DB_NAME  = "ximage";// データベース名前	
$DB_USR   = "ximage";// データベースのユーザー	
$DB_PSD   = "kCK!7wu4";// データベースのパスワード	
		
//テーブル配列　今のとこ航空券、トラベルコムのビーコンログ
$tbl_ary = array('tbl_kyotentab_log');//tbl_airsr_logも増える予定
		

/*********
* 処理開始
**********/
MakeKyotenTabLogCsv();


/*******************************************************
 * MakeKyotenTabLogCsv()
 * 
 * 処理の一連
*******************************************************/
function MakeKyotenTabLogCsv()
{
	//DBの３ヶ月前のデータを削除
	//delete_3month_data();

	//今日の情報取得
	$today = date('j');		//日付
	$week = date('w');	//曜日
	$csv_kind = '';
	
//debug
//$week = '1';
//$today = '1';


//echo "今日は" . $today . "日" . $week . "曜日\n";
	
	//１日
	if(strpos($today,'1') !== false){
		$last_2_day_sor = mktime(0, 0, 0, date("m")-1  , date("d"), date("Y"));
		$csv_kind = 'monthly_';
		get_data($last_2_day_sor,$csv_kind);		//DBからデータ抽出＆csv出力	
	
	}//月曜日
	if(strpos($week,'1') !== false){

		$last_2_day_sor = mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));
		$csv_kind = 'weekly_';
		get_data($last_2_day_sor,$csv_kind);		//DBからデータ抽出＆csv出力	
	}
	
	//毎日
	$last_2_day_sor = mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"));

	
	$csv_kind = 'daily_';
	get_data($last_2_day_sor,$csv_kind);		//DBからデータ抽出＆csv出力	


	//ローカールのcsvとtarファイルを削除*/
	//delete_lcoal_file($upload_csv_name);
	//delete_lcoal_file($new_tar_name);
}

/*******************************************************
 * delete_3month_data()
 * 
 * DBの３ヶ月前のデータを削除
*******************************************************/
function delete_3month_data()
{
	$last_3_month_sor  = mktime(0, 0, 0, date("m")-2  , date("d"), date("Y"));
	$last_3_month = date("Y-m-d",$last_3_month_sor);
	dbconnect();
	$sql = "delete from tbl_kyotentab_log where acc_date<'$last_3_month'";
	//print $sql;
	$resultSet = mysql_query($sql);
}

/*******************************************************
 *	get_data($last_2_day_sor,$log_kind);		//DBからデータ抽出＆csv出力	
 * 
 * 引数
 * 	$last_2_day_sor		:DB 取得用：対象日付のstart
 * 	$csv_kind					:出力データ種類(daily,weekly,monthly)
 *  返り値
 * 	$this->bar：処理後データ 
 * システム日付よりDBからデータをCSVファイルに吐き出す
*******************************************************/
function get_data($last_2_day_sor,$csv_kind)
{
	global $karenda_log_csv;
	
	$data_nums   = 20000;

	$last_day = date("Y-m-d",$last_2_day_sor);
	$today = date("Y-m-d",strtotime("now"));
//debug
//$today = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"))) ;
	
	$yesterday = date("Ymd",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"))) ;


	$connect = dbconnect();
	$sql = "select count(id) as count from tbl_kyotentab_log where acc_date >='$last_day' and acc_date <'$today'";

	$resultSet = mysql_query($sql);
	$row = mysql_fetch_assoc($resultSet);

	$count = $row['count'];
	$upload_csv_name = "";//アップロードファイル名
	$upload_csv_name = $karenda_log_csv."kyotentab_log_" . $csv_kind . $yesterday.".csv";//アップロードファイル名
	
	/*既に作成された場合に削除します*/
	if(file_exists($upload_csv_name))
	{
		delete_lcoal_file($upload_csv_name);
	}
	
	for($i=0;$i<=$count;$i=$i+20000)
	{	
//		$sql2 = "select acc_url,acc_kyotenID,acc_date from tbl_kyotentab_log where acc_date >='$last_day' and acc_date <'$today' order by acc_date limit $i , $data_nums";
		$sql2 = "select acc_url,acc_kyotenID,count(*) as 'acc_count' from tbl_kyotentab_log where acc_date >='$last_day' and acc_date <'$today' group by acc_url,acc_kyotenID having count(*)>1 order by acc_date limit $i , $data_nums";

//echo "sql2=$sql2\n";

		$resultSet2 = mysql_query($sql2);
		$log_arr = array();
echo "sql running.";		

		if (!$resultSet2) {
			echo "Could not successfully run query ($sql) from DB: " . mysql_error();
			mysql_free_result($resultSet2);
			continue;
		}	
		if (mysql_num_rows($resultSet2) == 0) {
			echo "No rows found, nothing to print so am exiting\n";
			mysql_free_result($resultSet2);
			continue;
		}
	
		$j = 0;
		while($row2 = mysql_fetch_assoc($resultSet2))
		{
echo ".";		
/*
			$log_arr[$j]["acc_url"] = $row2["acc_url"];
			$log_arr[$j]["acc_kyotenID"] = $row2["acc_kyotenID"];		
			$log_arr[$j]["acc_date"] = date("Y/m/d H:i:s",strtotime($row2["acc_date"]));;
*/
			$log_arr[$j]["acc_url"] = $row2["acc_url"];
			$log_arr[$j]["acc_kyotenID"] = $row2["acc_kyotenID"];
			if(!empty($log_arr[$j]["acc_count"])){
				$log_arr[$j]["acc_count"] = intval($row2["acc_count"]) + $log_arr[$j]["acc_count"];
			}else{
				$log_arr[$j]["acc_count"] = intval($row2["acc_count"]);
			}	
			$j++;
		}
		mysql_free_result($resultSet2);
		put_csv($log_arr,$upload_csv_name);
	}
	mysql_free_result($resultSet);
	mysql_close($connect);

}

###########
# CSVを書き込む関数
###########
function WriteOutDataCsv ($TgFileName, $WriteData) {
	$WriteCsv = $TgDir . $TgFileName;
	/*バックアップが必要な場合は、該当ディレクトリにコピー*/
	if(!empty($BkDir)){
		$BkCsv = $BkDir . $TgFileName;
		if(is_writable($WriteCsv)){
			MakeDir($BkDir);
			copy($WriteCsv, $BkCsv);
		}
	}
	ChangeMode($WriteCsv);

	MakeDir($TgDir);
	$fp = fopen($WriteCsv, "w");
	fwrite($fp, $WriteData);
	fclose($fp);
	ChangeMode($WriteCsv);

}

/*******************************************************
 * dbconnect()
 * 
 * MySQL サーバへの接続をオープンあるいは再利用
*******************************************************/
function dbconnect()
{	
	global $SERVER,$DB_NAME,$DB_USR,$DB_PSD;

	$connect = @mysql_connect($SERVER,$DB_USR,$DB_PSD) or die('can\'t connect to the db');
	@mysql_select_db($DB_NAME, $connect) or exit('can\'t find the db');
	return $connect;
}


/*******************************************************
 * delete_lcoal_file()
 * 
 * ローカルファイルを削除
*******************************************************/
function delete_lcoal_file($name)
{

	if (file_exists($name))
	{
		unlink($name);
	}

}

/*******************************************************
 * put_csv()
 * 
 * csvファイルに書き込み
*******************************************************/
function put_csv($log_arr,$upload_csv_name)
{
	
	$fp=fopen($upload_csv_name, 'a');
	foreach($log_arr as $key=>$val)
	{
		_fputcsv($fp,$val,",","\"");
	}
	fclose($fp);

}

/*******************************************************
 * _fputcsv()
 * 
 * csvファイルを生成
*******************************************************/
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
 	
	
	if($filePointer){
	  // Write the string to the file
  	fwrite($filePointer,$string);
	}
}

/*******************************************************
 * writelog()
 * 
 * ログ生成
*******************************************************/
function writelog($str) {
	global $log_dir;

	$filename = "export_csv".date("Ymd").".log";
	$filelog = fopen($log_dir.$filename,"a");
	fwrite($filelog,$str."\r\n");
	fclose($filelog);

}


