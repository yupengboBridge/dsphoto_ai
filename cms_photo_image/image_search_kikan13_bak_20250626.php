<?php
header("Content-Type:image/jpg");
require_once('./search_kikan_config.php');

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

    $content = file_get_contents($img);
    echo $content;

} catch (Exception $e) {
} finally {
    if (isset($db_link)) {
        $db_link = null;
    }
}

?>