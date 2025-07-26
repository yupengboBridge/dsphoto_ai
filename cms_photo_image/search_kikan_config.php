<?php
// データーベース情報です
$db_host = 'localhost';
$db_user = 'root';
$db_password = 'root@Hcst2022';
$db_name = 'photodb_image';
$db_charset = 'utf8';

$font_name = "./sazanami-gothic.ttf";
$credit_fontsize = array(8, 10, 14, 18, 22, 26);
date_default_timezone_set('Asia/Tokyo');
$image_url = 'http://cmsphotoimg.hcstec.com/';

function db_connect()
{
    global $db_host, $db_name, $db_user, $db_password, $db_charset, $is_connect, $db_link;

    $is_connect = false;

    // パスワード以外が空の場合はエラーとします。
    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_charset)) {
        $err_message = "データベース情報に不備があります。";
        throw new Exception($err_message);
    }
    // データベースキャラクターセットのチェックをします。（省略）

    // データベースに接続します。
    $hostdb = "mysql:host=" . $db_host . "; dbname=" . $db_name;
    $pdo = new PDO($hostdb, $db_user, $db_password);

    // 使用するキャラクターセットを設定します。
    $sql = "set names :DBCHAR";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':DBCHAR', $db_charset);
    $result = $stmt->execute();

    $is_connect = $result;

    // PDOのインスタンスを返します。
    return $pdo;
}
?>