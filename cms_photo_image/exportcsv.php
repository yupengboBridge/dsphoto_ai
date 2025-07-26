<?php
date_default_timezone_set("Asia/Tokyo");

set_time_limit(1800);

// データーベース情報です
//$db_host = '10.254.2.63';
$db_host = '127.0.0.1';
$db_user = 'ximage';
$db_password = 'kCK!7wu4';
$db_name = 'ximage';
$db_charset = 'utf8';
$db_link;
$csv_dir = "./csv/";

function db_connect()
{
  global $db_host,$db_user,$db_password,$db_name,$db_charset,$db_link;

	try {
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
	} catch(Exception $cla) {
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}
}

function error_exit($msg)
{
	// エラーが有った場合は、エラー画面を表示します&#65533;?
	global $charset;
	global $site_name;

	print "<html>\r\n";
	print "<head>\r\n";
	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">\r\n";
	print "<link rel=\"stylesheet\" type=\"text/css\" href=\"default.css\">\r\n";
	print "<title>$site_name</title>\r\n";
	print "</head>\r\n";
	print "<body>\r\n";

	print "<div align=\"center\">\r\n";
	print "<h1>$site_name</h1>\r\n";
	print "<font color=\"red\">";
	$ed = count($msg);
	for ($i = 0 ; $i < $ed ; $i++)
	{
		print $msg[$i] . "<br />";
	}
	print "</font>\r\n";
	print "</div>\r\n";
	print "</body>\r\n";
	print "</html>\r\n";

	exit (-1);
}
	
try{
	$db_link = db_connect();

	if($db_link) {
		$sql="select photo_mno,bud_photo_no from photoimg";
		$stmt = $db_link->prepare($sql);
		// SQLを実行します。
		$result = $stmt->execute();
		if($result == true) {
			$i = 1;
			$str = "";
			if(is_file($csv_dir."jpg_eps_list.csv")) {unlink($csv_dir."jpg_eps_list.csv");}

			while ($data = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$field1 = $data['photo_mno'];
				$field2 = $data['bud_photo_no'];
				
				$i = $i + 1;
				if($i == 1000) {
					$handle = fopen ($csv_dir."jpg_eps_list.csv","a");
					fwrite($handle,$str);
					fclose ($handle);
					$str = $field1."\t".$field2."\r\n";
					$i = 1;
				} else {
					$str .= $field1."\t".$field2."\r\n";
				}
			}
			if($i > 1) {
					$handle = fopen ($csv_dir."jpg_eps_list.csv","a");
					fwrite($handle,$str);
					fclose ($handle);
			}
		} else {
			$msg[] = $sql."SQL文の実行を失敗しました！";
			error_exit($msg);
		}
	} else {
		$msg[] = "データベースと接続できません。";
		error_exit($msg);
	}
} catch(Exception $cla) {
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

?>