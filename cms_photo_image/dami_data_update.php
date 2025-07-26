<?php
require_once('./config.php');
require_once('./lib.php');

// ＤＢへ接続します。
$db_link = db_connect();

$sql = "select * from photoimg order by photo_id";

$stmt = $db_link->prepare($sql);
$result = $stmt->execute();
if ($result == true)
{
	$p_photo_id = "";
	$p_photo_filename = "";
	$p_photo_filename_th1 = "";
	$p_photo_filename_th2 = "";

	$i = 0;

	while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$p_photo_id = $image_data['photo_id'];

		$p_mno = $image_data['photo_mno'];

		$i = $i + 1;

		$mod = gmp_mod($i, 2);
		if((int)$mod != 0)
		{
			$p_photo_filename = "http://www.e-mon.vc/test/photodb1213/./uploads/2/200901131928187232.jpg";
			$p_photo_filename_th1 = "http://www.e-mon.vc/test/photodb1213/./thumb1/2/200901131928187232th1.jpg";
			$p_photo_filename_th2 = "http://www.e-mon.vc/test/photodb1213/./thumb2/2/200901131928187232th2.jpg";
		} else {
			$p_photo_filename = "http://www.e-mon.vc/test/photodb1213/./uploads/3/200901131928201938.jpg";
			$p_photo_filename_th1 = "http://www.e-mon.vc/test/photodb1213/./thumb1/3/200901131928201938th1.jpg";
			$p_photo_filename_th2 = "http://www.e-mon.vc/test/photodb1213/./thumb2/3/200901131928201938th2.jpg";
		}

		$update_tmp = " photo_filename = '".$p_photo_filename."'";
		$update_tmp .= ",photo_filename_th1 = '".$p_photo_filename_th1."'";
		$update_tmp .= ",photo_filename_th2 = '".$p_photo_filename_th2."'";

		$update_sql = "UPDATE photoimg SET ".$update_tmp." WHERE photo_id = ".$p_photo_id;

		$stmt2 = $db_link->prepare($update_sql);
		$result = $stmt2->execute();

		$str =  "画像管理番号：".$p_mno."を処理しました。</p>";
		print $str . str_repeat(' ', 256);
		print "<br/>";
		flush();
	}

	$str =  $i."件を処理しました。</p>";
	print $str . str_repeat(' ', 256);
	print "<br/>";
	flush();
}
else
{
	$err = $stmt->errorInfo();
	$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
	throw new Exception($this->message);
}
?>