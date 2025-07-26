<?php
// データーベース情報です
$db_host = '10.254.2.63';
//$db_host = 'localhost';
$db_user = 'ximage';
//$db_user = 'root';
$db_password = 'kCK!7wu4';
//$db_password = '222222';
$db_name = 'ximage';
//$db_name = 'photo';
$db_charset = 'utf8';
$db_link;

date_default_timezone_set('Asia/Tokyo');

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();
	$p_photo_mno = '00021-ESP09-07717.jpg';
	$sql = "select * from photoimg where photo_mno='".$p_photo_mno."'";

	$stmt = $db_link->prepare($sql);
	// SQLを実行します。
	$result = $stmt->execute();

	// 実行結果をチェックします。
	if ($result == true)
	{
		// 実行結果がOKの場合の処理です。
		$icount = $stmt->rowCount();
		echo "aa>>".$icount;
		while($img = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			echo "aa".$img["bud_photo_no"]."<br/>";
			echo "bb".$img["publishing_situation_id"]."<br/>";
		}
		
	}
	else
	{
		echo "select error>>";
	}
}
catch(Exception $e)
{
	echo "Exception error>>";
}

function db_connect()
{
	global $db_host, $db_name, $db_user, $db_password, $db_charset, $is_connect, $db_link;

	$is_connect = false;

	// パスワード以外が空の場合はエラーとします。
	if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_charset))
	{
		$err_message = "データベース情報に不備があります。";
		throw new Exception($err_message);
	}
	// データベースキャラクターセットのチェックをします。（省略）

	// データベースに接続します。
	$hostdb = "mysql:host=". $db_host . "; dbname=" . $db_name;
	$pdo = new PDO($hostdb, $db_user, $db_password);

	// 使用するキャラクターセットを設定します。
	//$sql = "set character SET :DBCHAR";
	$sql = "set names :DBCHAR";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':DBCHAR', $db_charset);
	$result = $stmt->execute();

	$is_connect = $result;

	// PDOのインスタンスを返します。
	return $pdo;
}
?>