<?php
require_once('./config.php');
require_once('./lib.php');

$site_url = 'https://x.hankyu-travel.com/photo_db/';
//$site_url = 'https://www.e-mon.vc/test/photodb0130/';


// ＤＢへ接続します。
$db_link = db_connect();

$sql = "select * from photoimg order by photo_id ASC";

$stmt = $db_link->prepare($sql);
$result = $stmt->execute();
if ($result == true)
{
	$p_photo_id = "";

	while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$p_photo_id = $image_data['photo_id'];
		$photo_filename_th1 = $image_data['photo_filename_th1'];
		$tmp = substr($photo_filename_th1,strpos($photo_filename_th1,"./"));
		$tmp1 = str_replace("th1","th4",$tmp);
		$tmp2 = str_replace("thumb1","thumb4",$tmp1);
		$photo_filename_th4 = $site_url.$tmp2;

		$update_tmp = " photo_filename_th4 = '".$photo_filename_th4."'";

		$update_sql = "UPDATE photoimg SET ".$update_tmp." WHERE photo_id = ".$p_photo_id;

		try
		{
			$stmt2 = $db_link->prepare($update_sql);
			$result2 = $stmt2->execute();
			if($result2)
			{
				$str = "updatesql->".$update_sql.">>>OK";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				flush();
			} else {
				$str = "updatesql->".$update_sql.">>>ERR";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				flush();
			}
		} catch(Exception $e) {
			// 例外をスローします。
			$msg = $e->getMessage();
			throw new Exception($msg);
		}
	}
}
else
{
	$err = $stmt->errorInfo();
	$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
	throw new Exception($this->message);
}
?>