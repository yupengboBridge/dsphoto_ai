<?php
header("Content-type: text/html; charset=UTF-8");
require_once('./config.php');
require_once('./lib.php');

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();
	
	$sp_mno = array("00002-ESP09-08326.jpg","00002-ELM09-00676.jpg","00002-ESP09-08327.jpg","00002-ESP09-08328.jpg","00002-ESP09-08329.jpg","00002-ESP09-08330.jpg","00002-ESP09-08331.jpg","00002-ESP09-08332.jpg","00002-ESP09-08333.jpg","00002-ESP09-08334.jpg","00002-ESP09-08335.jpg","00002-ESP09-08336.jpg","00002-ESP09-08337.jpg","00002-ESP09-08338.jpg","00002-ESP09-08340.jpg","00002-ESP09-08341.jpg","00002-ESP09-08342.jpg","00002-ELM09-00677.jpg","00002-ESP09-08343.jpg","00002-ESP09-08344.jpg","00002-ESP09-08345.jpg","00002-ESP09-08346.jpg","00002-ESP09-08347.jpg","00002-ESP09-08348.jpg","00002-ESP09-08349.jpg","00002-ESP09-08350.jpg","00002-ESP09-08351.jpg","00002-ESP09-08352.jpg","00002-ESP09-08353.jpg","00002-ESP09-08354.jpg","00002-ESP09-08355.jpg","00002-ESP09-08356.jpg","00002-ESP09-08357.jpg","00002-ESP09-08358.jpg","00002-ESP09-08359.jpg","00002-ESP09-08360.jpg","00002-ESP09-08362.jpg","00002-ESP09-08363.jpg","00002-ESP09-08364.jpg","00002-ESP09-08365.jpg","00002-ESP09-08366.jpg","00002-ESP09-08367.jpg","00002-ESP09-08368.jpg","00002-ESP09-08369.jpg","00002-ESP09-08370.jpg","00002-ESP09-08371.jpg","00002-ESP09-08372.jpg","00002-ESP09-08373.jpg","00002-ESP09-08374.jpg","00002-ESP09-08375.jpg","00002-ESP09-08376.jpg","00002-ESP09-08377.jpg","00002-ESP09-08378.jpg","00002-ESP09-08379.jpg","00002-ESP09-08380.jpg","00002-ESP09-08381.jpg","00002-ESP07-01146.jpg","00002-ESP07-01147.jpg","00002-ESP07-01148.jpg","00002-ESP07-01149.jpg","00002-ESP07-01150.jpg","00002-ESP07-01151.jpg","00002-ESP07-01152.jpg","00002-ESP07-01153.jpg","00002-ESP07-01154.jpg","00002-ESP07-01155.jpg","00002-ESP07-01156.jpg","00002-ESP07-01157.jpg","00002-ESP07-01158.jpg","00002-ESP07-01159.jpg","00002-ESP07-01160.jpg","00002-ESP07-01161.jpg","00002-ESP07-01162.jpg","00002-ESP07-01163.jpg","00002-ESP08-01567.jpg","00002-EHP08-00064.jpg","00002-ESP08-01568.jpg","00002-ESP08-01569.jpg","00002-ESP08-01570.jpg","00002-ESP08-01571.jpg","00002-ESP08-01572.jpg","00002-ESP08-01573.jpg","00002-ESP08-01574.jpg","00002-ESP08-01575.jpg","00002-ESP08-01576.jpg","00002-ESP08-01577.jpg","00002-ESP08-01578.jpg","00002-ESP08-01579.jpg","00002-ESP08-01580.jpg","00002-EHP09-00022.jpg");
	
	for ($i = 0; $i < count($sp_mno); $i++)
	{
		$sp_photo_id = "";
		
		//写真IDを検索する（写真管理番号より）
		$sql_select = "SELECT photo_id FROM photoimg WHERE photo_mno = '".$sp_mno[$i]."'";
		$stmt_select = $db_link->prepare($sql_select);
		$result_select = $stmt_select->execute();
		if ($result_select == true)
		{
			$photo_data = $stmt_select->fetch(PDO::FETCH_ASSOC);
			$sp_photo_id = $photo_data['photo_id'];
			
			if(!empty($sp_photo_id))
			{
				$sql_delete = "DELETE FROM keyword WHERE photo_id = ".$sp_photo_id;
				$stmt_delete = $db_link->prepare($sql_delete);
				$result_delete = $stmt_delete->execute();
				if ($result_delete == true)
				{
					$str = "delete->".$sql_delete.">>>OK";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					flush();
				} else {
					$str = "delete->".$sql_delete.">>>ERR";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					flush();
				}
//				$sql_update = "UPDATE photoimg SET publishing_situation_id = 1 WHERE photo_id = ".$sp_photo_id;
//				$stmt_update = $db_link->prepare($sql_update);
//				$result_update = $stmt_update->execute();
//				if ($result_update == true)
//				{
//					$str = "update->".$sql_update.">>>OK";
//					print $str . str_repeat(' ', 256);
//					print "<br/>";
//					flush();
//				} else {
//					$str = "update->".$sql_update.">>>ERR";
//					print $str . str_repeat(' ', 256);
//					print "<br/>";
//					flush();
//				}
			} else {
				$str = "写真管理番号->".$sp_mno[$i].">>>写真IDが見つかりません。";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				flush();
			}
		} else {
			$str = "写真管理番号->".$sp_mno[$i].">>>検索できません。";
			print $str . str_repeat(' ', 256);
			print "<br/>";
			flush();
		}
	}
}
catch(Exception $e)
{
	$message= $e->getMessage();
	throw new Exception($message);
}
?>