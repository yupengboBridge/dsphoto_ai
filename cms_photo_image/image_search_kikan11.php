<?php
require_once('./kikanConfig.php');
require_once('./kikanCommon.php');

$db_link = null;
$font_name = "./sazanami-gothic.ttf";
$credit_fontsize = array(8, 10, 14, 18, 22, 26);
date_default_timezone_set('Asia/Tokyo');

#
$photo_mno=$_REQUEST['p_photo_mno'];
$db_link = db_connect();
$sql = "SELECT photo_filename_th11 from photoimg WHERE photo_mno = '{$photo_mno}'";
$stmt = $db_link->prepare($sql);

$stmt->execute();
$data = $stmt->fetch(PDO::FETCH_ASSOC);
$img = $data['photo_filename_th11'] ;



$content=file_get_contents($img);
						$webp = strpos($_SERVER['HTTP_ACCEPT'], 'image/webp');
						define('IS_WEBP', $webp === false ? 0 : 1);
								
						// 原始图片路径
						$original_url = $img;

						if(empty($original_url)){
							print_kikan_noimage();
							return;
						}

						// 构造 .webp 的 URL（从原始 URL 转换）
						$path_info = pathinfo($original_url);
						$l_webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';

						// 构造本地的文件路径
						$l_jpg_file_path = '../'.explode($_SERVER['SERVER_NAME'], $original_url)[1];
						$l_webp_file_path = '../'.explode($_SERVER['SERVER_NAME'], $l_webp_path)[1];

						if (IS_WEBP && file_exists($l_webp_file_path)) {
							print_kikan_image("webp", $l_webp_path);
						} else if (file_exists($l_jpg_file_path)) {
							print_kikan_image("jpeg", $original_url);
						} else {
							print_kikan_noimage();
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