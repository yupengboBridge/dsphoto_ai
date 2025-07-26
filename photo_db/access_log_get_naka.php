<?php
	include('./log_save/db.inc.php');
	require_once('./log_save/config.php');

	date_default_timezone_set("Asia/Tokyo");
//	echo file_get_contents("./parts/noimage.gif");

$CsvFileName = './_naka_test/tbl_log.csv';

$handle = fopen($CsvFileName, "r");
if ($handle) {
	$db = new importcsv();
	
	$db->db_host = SERVER;
	$db->db_name = DB_NAME;
	$db->db_user = DB_USR;
	$db->db_password = DB_PSD;
	$db->db_charset = "utf8";
	$db_link = $db->db_connect();

	while (!feof($handle)) {
		$buffer = fgets($handle, 9999);	//日本語ファイルはfgetcsv使うのやめておく
		//空白行はサヨナラ
		if(empty($buffer)){
			continue;
		}
		//改行もイラナイ
		$buffer = rtrim($buffer);
		$data = explode(',', $buffer);

		//個別の処理





		save_to_db($data, $db, $db_link);

		$cnt++;
		if($cnt % 10000 == 0){
			echo $cnt . "\n";
		}
	}
}


//	access_log_get();
	
//	function access_log_get()
//	{
//		$p_test = isset($_GET['p_test'])?$_GET['p_test']:"";
//		$p_preview = isset($_GET['p_preview'])?$_GET['p_preview']:"";
//		
//		if((1==$p_test)||(1==$p_preview))
//		{
//			return;
//		}
//		
//		if(((1!=$p_test)&&(1!=$p_preview))||(!isset($p_test)&&!isset($p_preview)))
//		{
//			save_to_db();
//		}
//	}
	
	function save_to_db($data, $db, $db_link)
	{
		$p_courseid = $data[0];
		$p_date = $data[1];
		
		if(isset($p_courseid)&&$p_courseid!=""&&isset($p_date)&&$p_date!="")
		{
			//もし数値型でなかったら何もしない
			if(!preg_match("/^[0-9]+$/",$p_courseid)){
				return false;
			}
			
			$p_date = str_replace("_"," ",$p_date);
			$p_date = date("Y-m-d H:i:s",strtotime($p_date));
			$now_date = $data[2];
			$result = FALSE;
			
			
			$sql = "insert into tbl_log(acc_course_id,acc_date,create_time) values (:CID,:CDATE,:NDATE) ";
			
			$stmt = $db_link->prepare($sql);
			$stmt->bindValue(':CID',$p_courseid);
			$stmt->bindValue(':CDATE',$p_date);
			$stmt->bindValue(':NDATE',$now_date);
			
			//$result = $stmt->execute();
			//
			//if($result)
			//{
			//}
			//else
			//{
			//}
		}
		else 
		{
		}
	}
?>