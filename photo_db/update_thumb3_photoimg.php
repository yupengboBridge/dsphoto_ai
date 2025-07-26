<?php
require_once('./config.php');
require_once('./lib.php');

$site_url = 'https://x.hankyu-travel.com/photo_db/';


// ＤＢへ接続します。
$db_link = db_connect();

$sql = "select * from photoimg order by photo_id";

$stmt = $db_link->prepare($sql);
$result = $stmt->execute();
if ($result == true)
{
	$p_photo_id = "";

	while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$p_photo_id = $image_data['photo_id'];
		$photo_filename_th2 = $image_data['photo_filename_th2'];
		$tmp = substr($photo_filename_th2,strpos($photo_filename_th2,"./"));
		$tmp1 = str_replace("th2","th3",$tmp);
		$tmp2 = str_replace("thumb2","thumb3",$tmp1);
		$photo_filename_th3 = $site_url.$tmp2;

		$update_tmp = " photo_filename_th3 = '".$photo_filename_th3."'";

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