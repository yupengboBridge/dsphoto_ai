<?php
require_once('./config.php');
require_once('./lib.php');

// ＤＢへ接続します。
$db_link = db_connect();

$sql = "select * from photoimg where photo_id > 0 order by photo_id ASC";

$stmt = $db_link->prepare($sql);
$result = $stmt->execute();
if ($result == true)
{
	$p_photo_mno = "";

	while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$p_photo_mno = $image_data['photo_mno'];

		$sql_exists = "SELECT count(*) cnt FROM keyword WHERE keyword_name like '%".$p_photo_mno."%'";
		// SQL文法のチェック
		$stmt1 = $db_link->prepare($sql_exists);
		$result1 = $stmt1->execute();
		// 実行結果をチェックします。
		if ($result1 == true)
		{
			$pcnt = $stmt1->fetch(PDO::FETCH_ASSOC);
			$tmpcnt = $pcnt['cnt'];

			//既に存在の場合に更新する
			if ((int)$tmpcnt > 1)
			{
				$str = "p_photo_mno->".$p_photo_mno.">>>重複";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				flush();
			} else {
				$str = "p_photo_mno->".$p_photo_mno.">>>OK";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				flush();
			}
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