<?php
mb_internal_encoding('utf-8');
mb_http_output('utf-8');

// WSDLのURL
//$wsdl = "https://".$_SERVER["SERVER_NAME"].dirname($_SERVER["PHP_SELF"])."/"."soap_limi_selectuser.wsdl";
$wsdl = "soap_limi_selectuser.wsdl";

// データーベース情報です
//$db_host = 'localhost';
//$db_user = 'root';
//$db_password = '222222';
//$db_name = 'photo';
//$db_charset = 'utf8';
//$db_link;

// データーベース情報です
//$db_host = '10.254.2.63';
//$db_host = '10.254.2.39';
$db_host = '127.0.0.1';
$db_user = 'ximage';
$db_password = 'kCK!7wu4';
$db_name = 'ximage';
$db_charset = 'utf8';
$db_link;

/*
 * 関数名：select_user
 * 関数説明：申請者管理番号よりユーザーを検索する
 * パラメタ：
 * userid:ログインID
 * 戻り値：無し
 */
function select_user($userid)
{
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();

		// ユーザー情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "select * from user where login_id = ?";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $userid);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				// 正常にデータの取得ができたときの処理です。
				$user = $stmt->fetch(PDO::FETCH_ASSOC);
				$p_username = $user['user_name'];
				return $p_username;
			}
		} else {
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
	global $db_host, $db_name, $db_user, $db_password, $db_charset;

	// パスワード以外が空の場合はエラーとします。
	if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_charset))
	{
		return;
	}

	// データベースに接続します。
	$hostdb = "mysql:host=". $db_host . "; dbname=" . $db_name;
	$pdo = new PDO($hostdb, $db_user, $db_password);

	// 使用するキャラクターセットを設定します。
	//$sql = "set character SET :DBCHAR";
	$sql = "set names :DBCHAR";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':DBCHAR', $db_charset);
	$result = $stmt->execute();

	// PDOのインスタンスを返します。
	return $pdo;
}

// (1) SOAPサーバオブジェクトの作成
$server = new SoapServer($wsdl);

// (2) メソッドの追加
$server->addFunction("select_user");

// (3) リクエストの処理
$server->handle();
?>