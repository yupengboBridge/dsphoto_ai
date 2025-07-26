<?php
require_once('./config.php');
require_once('./lib.php');

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	$p_photo_id = array_get_value($_REQUEST,"p_photo_id","");

	$sql = "select image1,image2,image3,image4,image5 from photo_imgdata where photo_id=".$p_photo_id;

	$stmt = $db_link->prepare($sql);
	// SQLを実行します。
	$result = $stmt->execute();

	// 実行結果をチェックします。
	if ($result == true)
	{
		// 実行結果がOKの場合の処理です。
		$icount = $stmt->rowCount();
		if ($icount >= 0)
		{
			$img = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			// エラー情報をセットして、例外をスローします。
			$this->message = "画像データを取得できませんでした。（取得数<=0）";
			throw new Exception($this->message);
		}
	}
	else
	{
		// 実行結果がNGの場合の処理です。
		// エラー情報をセットして、例外をスローします。
		$err = $stmt->errorInfo();
		$message = "画像データを取得できませんでした。（条件設定エラー）";
		throw new Exception($message);
	}
	echo $img['image5'];
}
catch(Exception $e)
{
	echo "DBエラー（DB例外）";
	echo $e->getMessage();
}
?>