<?php
mb_internal_encoding('utf-8');
mb_http_output('utf-8');

// WSDLのURL
//$wsdl = "https://".$_SERVER["SERVER_NAME"].dirname($_SERVER["PHP_SELF"])."/"."soap_upload_check.wsdl";
$wsdl = "soap_upload_check.wsdl";

// データーベース情報です
//$db_host = 'localhost';
//$db_user = 'root';
//$db_password = '222222';
//$db_name = 'photo';
//$db_charset = 'utf8';
//$db_link;

// データーベース情報です
//$db_host = '10.254.2.39';
$db_host = '127.0.0.1';
$db_user = 'ximage';
$db_password = 'kCK!7wu4';
$db_name = 'ximage';
$db_charset = 'utf8';
$db_link;

/*
 * 関数名：query
 * 関数説明：ユーザーのチェック
 * パラメタ：
 * bud_photo：BUD_PHOTO番号
 * 戻り値：OK/ERR
 */
function query($bud_photo)
{
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();

		if(!$db_link)
		{
			return "ERR";
		}

		// ユーザー情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "select * from photoimg where bud_photo_no = '".$bud_photo."' and publishing_situation_id = 1";
		$stmt = $db_link->prepare($sql);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount >= 1)
			{
				return "ERR1";
			}
			else
			{
				return "OK";
			}
		}
		else
		{
			return "ERR";
		}
	}
	catch(Exception $e)
	{
		return "ERR";
	}
}

function db_connect()
{
	global $db_host, $db_name, $db_user, $db_password, $db_charset, $is_connect, $db_link;

	$is_connect = false;

	// パスワード以外が空の場合はエラーとします。
	if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_charset))
	{
		return;
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

// (1) SOAPサーバオブジェクトの作成
$server = new SoapServer($wsdl);

// (2) メソッドの追加
$server->addFunction('query');

// (3) リクエストの処理
$server->handle();
?>