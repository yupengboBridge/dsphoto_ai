<?php
require_once(dirname(__FILE__) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
date_default_timezone_set('Asia/Tokyo');

try {
    $where = " dto < DATE_ADD(CURDATE(), INTERVAL 3 MONTH) AND is_mall = 1 AND is_extension=1";
    // ＤＢへ接続します。
    $db_link = db_connect();
    // イメージ検索のクラス
    $img_all = new ImageSearch();
    $img_all->istart = 0;
    $img_all->iend = 5000;
    // 写真を取得
    $img_all->select_image_registed($db_link, $where, "");
    // イメージ総数を取得する
    if (!empty($img_all->images)) {
        $img_ary = $img_all->images;
        $ph_img_all = new PhotoImageDataAll();
        for ($i = 0; $i < count($img_ary); $i++) {
            $photoImageDataItem = $img_ary[$i];

            $log_message = date("Y-m-d H:i:s") . ",admin,BUD管理者," . $photoImageDataItem->photo_mno . ",";
            $log_message .= preg_replace("/,/", " ", preg_replace("'([\r\n])[\s]+'", " ", $photoImageDataItem->photo_name));
            $log_message .= "," . preg_replace("/,/", " ", preg_replace("'([\r\n][,])[\s]+'", " ", $photoImageDataItem->photo_explanation)) . ",";
            $log_message .= preg_replace("/,/", " ", preg_replace("'([\r\n])[\s]+'", " ", $photoImageDataItem->bud_photo_no)) . ",";
            $log_message .= $photoImageDataItem->dfrom . ",";
            $log_message .= $photoImageDataItem->dto . ",";
            $log_message .= $photoImageDataItem->registration_person . "\r\n";

            // PhotoImageのインスタンスを生成します。
            $pi = new PhotoImageDB ();
            $pi->photo_id = $photoImageDataItem->photo_id;
            $pi->update_data_for_kikan($db_link);

            write_log_to_file($log_message);
        }
    } else {
        $log_message = date("Y-m-d H:i:s");
        $log_message .= "自動３年延長の画像が見つかりません。";
        write_log_to_file($log_message);
    }
} catch (Exception $e) {
    $log_message = date("Y-m-d H:i:s");
    $log_message .= $e->getMessage();
    write_log_to_file($log_message);
}

/*
 * 関数名：write_log_to_file
 * 関数説明：画像を削除すると、削除した画像はログファイルに出力する
 * パラメタ：$log_msg:ログ情報
 * 戻り値：無し
 */
function write_log_to_file($log_msg)
{
    // CSVファイルを出力する
    $file = fopen(dirname(__FILE__) . "/log/extend_" . date("Y-m-d") . ".log", "a+");
    fwrite($file, $log_msg);
    fclose($file);
}

?>