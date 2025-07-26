<?php
//header("Content-Type:image/jpg");
require_once('./search_kikan_config.php');
require_once('./kikanCommon.php');

$db_link = null;

try {
    $photo_mno = $_REQUEST['p_photo_mno'];
    $db_link = db_connect();
    $sql = "SELECT photo_filename_th13 from photoimg WHERE photo_mno = '{$photo_mno}'";
    $stmt = $db_link->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    $img = $data['photo_filename_th13'];
    $img = str_replace($image_url,"",$img);

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

} catch (Exception $e) {
} finally {
    if (isset($db_link)) {
        $db_link = null;
    }
}

?>