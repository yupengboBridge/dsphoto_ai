<?php
header("Content-type: text/html; charset=UTF-8");
require_once('./config.php');
require_once('./lib.php');

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	$sql = "select * from photoimg where photo_mno = '00000-EBP07-01582.jpg' or photo_mno = '00000-EBP07-01583.jpg' or photo_mno = '00000-EBP07-01781.jpg' or photo_mno = '00001-EAACX-00005.jpg' or photo_mno = '00001-EAASX-00000.jpg' or photo_mno = '00001-ESP08-01295.jpg' or photo_mno = '00001-EDKMX-00001.jpg' or photo_mno = '00001-EDOOH-00107.jpg'";
	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();

	if ($result == true)
	{
		$p_photo_id = "";
		$p_photo_mno = "";
		
		while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$p_photo_id = $image_data['photo_id'];
			$p_photo_mno = $image_data['photo_mno'];
			
			$sql_keyword = "select * from keyword where keyword_name like '%".$p_photo_mno."%'";
			
			$stmt_keyword = $db_link->prepare($sql_keyword);
			$result_keyword = $stmt_keyword->execute();
			
			if ($result_keyword == true)
			{
				$icount = $stmt_keyword->rowCount();

				echo "icount>>".$icount;
				
				if($icount > 1) 
				{					
					$tmp = "_".$p_photo_mno;
					$ipos = strpos($tmp,"-");
					if ($ipos > 0)
					{
						$lastnumbername = substr($tmp,$ipos+1,5);
						$sql_maxnumber = "select lastnumber from lastnumber where lastnumber_name = '".$lastnumbername."'";
						$stmt_maxnumber = $db_link->prepare($sql_maxnumber);
						$result_maxnumber = $stmt_maxnumber->execute();
						if($result_maxnumber == true)
						{
							$str = "画像管理番号：".$p_photo_mno.";maxnumber:".$stmt_maxnumber['lastnumber'];
							print $str . str_repeat(' ', 256);
							print "<br/>";
							flush();
						}
					}
				}
			}
		}
	}
}
catch(Exception $e)
{
	$message= $e->getMessage();
	throw new Exception($message);
}
?>