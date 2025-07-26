<?php
header("Content-Type:image/jpg");

// データーベース情報です
//$db_host = '10.254.2.39';
//$db_host = 'localhost';
$db_host = '127.0.0.1';
$db_user = 'ximage';
//$db_user = 'root';
$db_password = 'kCK!7wu4';
//$db_password = 'BZ99bRxcU6Jp';
//$db_password = '';
$db_name = 'ximage';
//$db_name = '_ximage';
$db_charset = 'utf8';
$db_link = null;
$font_name = "./sazanami-gothic.ttf";
$credit_fontsize = array(8, 10, 14, 18, 22, 26);
date_default_timezone_set('Asia/Tokyo');

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
						if(IS_WEBP == '1'){
							$l_ext = strrchr($img,'.');
							if ($l_ext == '.jpg'){
								$l_webp_path = str_ireplace('.jpg', '.webp', $img);
								# jpg文件路径
								$l_jpg_file_path = '../'.explode($_SERVER['SERVER_NAME'], $img)[1];
								# webp文件路径
								$l_webp_file_path = '../'.explode($_SERVER['SERVER_NAME'], $l_webp_path)[1];
								if(file_exists($l_webp_file_path)){
									$content=file_get_contents($l_webp_path);
									echo $content;
									# echo $l_webp_file_path;
								}else{
									try{
										$img_webp = imagecreatefromjpeg($l_jpg_file_path);
									    $TF = imagewebp($img_webp, $l_webp_file_path);
									    imagedestroy($img_webp);
									    $content=file_get_contents($TF);
									    echo $content;
									}catch(Exception $e){
										echo $content;
									}
								}
							}else{
								echo $content;
							}
						}else{
							echo $content;
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