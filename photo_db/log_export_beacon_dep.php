<?php
########################################################################
#
#  DBからトップの出発地選択のアクセス情報をCSVに生成
# 
#  DB:ximage table:tbl_deptop_acc_rog
# 
#  対象ページ
#  ・トップ、海外・国内・航空券トップ
#
#  【プログラム機能説明】
#  ・DBよりデータ抽出
#  ・抽出したデータより集計
#  ・３ヶ月前のDBデータを削除、出力したCSVを削除 
#   
#  @copyright  2011 BUD International AOKI
#  @version    1.0.0
########################################################################

/*********
* 初期設定
**********/
ini_set('error_reporting', E_ALL);
ini_set( "display_errors", "On");
date_default_timezone_set("Asia/Tokyo");
ini_set("memory_limit","2048M");

//$home2_log_dir = "/home2/chroot";
$home2_log_dir = "";	//テスト用

//ログ
//$log_dir = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-dep_ac-log/";
//$log_dir = "/var/www/html/aoki_test/ac_rog/log-dep_ac/";
//CSV配置
$dep_log_csv = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-dep_ac-csv/";
//$dep_log_csv = "/var/www/html/aoki_test/ac_rog/log-dep_ac-csv/";
//tar配置
//$karenda_log_tar = $home2_log_dir."/home/xhankyu/public_html/photo_db/log-beacon-tar/";

//DBサーバ
$SERVER	  = "10.254.2.39";// データベースのサーバーIP
$DB_NAME  = "ximage";// データベース名前	
$DB_USR   = "ximage";// データベースのユーザー	
$DB_PSD   = "kCK!7wu4";// データベースのパスワード

/*$SERVER	  = "localhost";// データベースのサーバーIP
$DB_NAME  = "a_bid_test";// データベース名前	
$DB_USR   = "root";// データベースのユーザー	
$DB_PSD   = "222222";// データベースのパスワード	
*/		
//テーブル配列　今のとこ出発地選択のビーコンログ
$tbl_ary = array('tbl_deptop_acc_rog');//増えたらここに追加
		

/*********
* 処理開始
**********/

MakeKyotenTabLogCsv();


/*******************************************************
 * MakeKyotenTabLogCsv()
 * 処理の一連
*******************************************************/
function MakeKyotenTabLogCsv()
{
	//DBの３ヶ月前のデータを削除
	delete_3month_data();
	//CSVファイルの削除
	DirCleanling();

	$last_7_day_sor = mktime(0, 0, 0, date("m")  , date("d")-7, date("Y"));
	get_data($last_7_day_sor);		//DBからデータ抽出＆csv出力	

}

/*******************************************************
 * delete_3month_data()
 * 
 * DBの３ヶ月前のデータを削除
*******************************************************/
function delete_3month_data()
{
	//$last_3_month_sor = mktime(date("H") , date("i")-10, 0, date("m")  , date("d"), date("Y"));
	//$last_3_month = date("Y-m-d_H:i:s",$last_3_month_sor);
	$last_3_month_sor  = mktime(0, 0, 0, date("m")-3  , date("d"), date("Y"));
	$last_3_month = date("Y-m-d",$last_3_month_sor);
	dbconnect();
	$sql = "delete from tbl_deptop_acc_rog where acc_date<'$last_3_month'";
	//print $sql;
	$resultSet = mysql_query($sql);
}

/*******************************************************
 *	get_data($last_7_day_sor);		//DBからデータ抽出＆csv出力	
 * 
 * 引数
 * 	$last_7_day_sor		:DB 取得用：対象日付のstart
 *  返り値
 * 	$this->bar：処理後データ 
 * システム日付よりDBからデータをCSVファイルに吐き出す
*******************************************************/
function get_data($last_7_day_sor)
{
	global $dep_log_csv;
	
	$data_nums   = 20000;

	$last_day = date("Y-m-d",$last_7_day_sor);
	
	//$today = date("Y-m-d",strtotime("now"));
	$today = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d"), date("Y"))) ;
	//$today = date("Y-m-d",strtotime("1 day"));
	//$yesterday = date("Ymd",mktime(0, 0, 0, date("m")  , date("d")-1, date("Y"))) ;
	$cronday = date("Ymd",mktime(0, 0, 0, date("m")  , date("d"), date("Y"))) ;
	//（手動でとりたい時使う）$last_day ='2011-09-26';
	//（手動でとりたい時使う）$today = '2011-09-26';
	//（手動でとりたい時使う）$cronday = '20110926';
	$connect = dbconnect();
	//$sql = "select count(id) as count from tbl_deptop_acc_rog where acc_date >='$last_day' and acc_date <'$today'";
	$sql = "select count(id) as count from tbl_deptop_acc_rog where acc_date >='$last_day' and acc_date <'$today'";

	$resultSet = mysql_query($sql);
	$row = mysql_fetch_assoc($resultSet);
	$count = $row['count'];
	$upload_csv_name = "";//アップロードファイル名
	$upload_csv_name = $dep_log_csv."dep_acc_rog_". $cronday.".csv";//アップロードファイル名
	
	/*既に作成された場合に削除します*/
	if(file_exists($upload_csv_name))
	{
		delete_lcoal_file($upload_csv_name);
	}
	
	for($i=0;$i<=$count;$i=$i+20000)
	{	
		//$sql2 = "select acc_url,acc_kyotenID,count(*) as 'acc_count' from tbl_deptop_acc_rog where acc_date >='$last_day' and acc_date <'$today' group by acc_url,acc_kyotenID having count(*)>1 order by acc_date limit $i , $data_nums";
		//select取り出す group by以下をグループ化　count(*) as 'acc_count'　当てはまる行の数を数えてその値の名前をacc_count'にする（*使ってない　having count(*)>1 order by　重複行があるデータ取る）　
$sql2 = "select DATE_FORMAT(acc_date, '%Y-%m-%d') as 'acc_ymd',acc_url,acc_status,acc_select,count(*) as 'acc_count' from tbl_deptop_acc_rog where acc_date >='$last_day' and acc_date <'$today' group by acc_ymd,acc_url,acc_status,acc_select limit $i , $data_nums";
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
		else{
		echo "toreta\n";
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
			$log_arr[$j]["acc_ymd"] = $row2["acc_ymd"];
			
			//$log_arr[$j]["acc_status"] = $row2["acc_status"];
			if($row2["acc_status"] == '1'){
				$log_arr[$j]["acc_status"] = 'view';
			}
			elseif($row2["acc_status"] == '2'){
				$log_arr[$j]["acc_status"] = 'select';
			}
			elseif($row2["acc_status"] == '3'){
				$log_arr[$j]["acc_status"] = 'close';
			}
			
			//acc_selectはacc_statutsが2の時のみ値が取れる
			if($row2["acc_status"] == '2'){
				$log_arr[$j]["acc_select"] = $row2["acc_select"];
			}
			else{
			$log_arr[$j]["acc_select"] = '';
			}
			
			
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
/*function WriteOutDataCsv ($TgFileName, $WriteData) {
	$WriteCsv = $TgDir . $TgFileName;
	//バックアップが必要な場合は、該当ディレクトリにコピー
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

}*/

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


/*---------------
	生成ファイルのお掃除
-----------------*/
/*昔のデータを掃除*/
function DirCleanling(){//手元お掃除

	global $dep_log_csv;
/*バックアップファイルを削除*/
	$path = $dep_log_csv."dep_acc_rog_". "*.csv";
	$rmTime = time() - 60*60*24*40; // 40日前の時間を求める
	foreach (glob($path) as $filename) {
	// 7日より前のファイルなら
		if (filemtime($filename) < $rmTime) {
		// 削除
		@unlink($filename);
		}
	}
}


/*******************************************************
 * writelog()
 * 
 * ログ生成
*******************************************************/
/*function writelog($str) {
	global $log_dir;

	$filename = "export_csv".date("Ymd").".log";
	$filelog = fopen($log_dir.$filename,"a");
	fwrite($filelog,$str."\r\n");
	fclose($filelog);
}*/

?>
