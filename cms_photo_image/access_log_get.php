<?php
	include('./log_save/db.inc.php');
	require_once('./log_save/config.php');

	date_default_timezone_set("Asia/Tokyo");

	access_log_get();
	
	function access_log_get()
	{
		$p_test = isset($_GET['p_test'])?$_GET['p_test']:"";
		$p_preview = isset($_GET['p_preview'])?$_GET['p_preview']:"";
		
		if((1==$p_test)||(1==$p_preview))
		{
			echo file_get_contents("./parts/noimage.gif");
			return;
		}
		
		if(((1!=$p_test)&&(1!=$p_preview))||(!isset($p_test)&&!isset($p_preview)))
		{
			save_to_db();
		}
	}
	
	function save_to_db()
	{
		$p_courseid = $_GET['p_course_no'];
		$p_date = $_GET['access_time'];
		
		if(isset($p_courseid)&&$p_courseid!=""&&isset($p_date)&&$p_date!="")
		{
			$p_date = str_replace("_"," ",$p_date);
			$p_date = date("Y-m-d H:i:s",strtotime($p_date));
			$now_date = date("Y-m-d H:i:s");
			$result = FALSE;
			
			$db = new importcsv();
			
			$db->db_host = SERVER;
			$db->db_name = DB_NAME;
			$db->db_user = DB_USR;
			$db->db_password = DB_PSD;
			$db->db_charset = "utf8";
			$db_link = $db->db_connect();
			
			$sql = "insert into tbl_log(acc_course_id,acc_date,create_time) values (:CID,:CDATE,:NDATE) ";
			
			$stmt = $db_link->prepare($sql);
			$stmt->bindValue(':CID',$p_courseid);
			$stmt->bindValue(':CDATE',$p_date);
			$stmt->bindValue(':NDATE',$now_date);
			
			//$result = $stmt->execute();
			//
			//if($result)
			//{
			//	echo file_get_contents("./parts/noimage.gif");
			//}
			//else
			//{
			//	echo file_get_contents("./parts/noimage.gif");	
			//}

		}
		else 
		{
			echo file_get_contents("./parts/noimage.gif");
		}
	}
?>