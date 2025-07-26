<?php
require_once('./config.php');
require_once('./lib.php');
$p_action= array_get_value($_REQUEST, 'p_action');

$img = null;

// ＤＢへ接続します。
$db_link = db_connect();

if ($p_action == "view") {
	$sql = "select image1 from photoimg where photo_id=187";
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
			header("Content-type: text/html;charset=UTF-8");
			echo $img['image1'];
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
}
?>