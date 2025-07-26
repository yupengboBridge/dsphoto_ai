<?php
require_once('./config.php');
require_once('./lib.php');
$db_link = db_connect();

$p_photo_filename = $_FILES['p_photo_filename']["name"];
$photo_id = $_POST['hidden_photo_id'];
$back_url = $_POST['hidden_back_url'];

function showJsAlterMessageAndHistory($alter_message,$back_url){
    $alter_html = "<script type=\"text/javascript\">\r\n";
    $alter_html .= "alert('".$alter_message."');\r\n";
    $alter_html .= "javascript:history.back();\r\n";
    $alter_html .= "top.document.getElementById('iframe_bottom').src='".$back_url."';</script>\r\n";
    $alter_html .= "</script>\r\n";
    print($alter_html);
    exit();
}

if(empty($p_photo_filename)){
    showJsAlterMessageAndHistory("写真がアップロードされていないので、何も変更していないです。",$back_url);
}

try
{
    // PhotoImageのインスタンスを生成します。
    $pi_select = new PhotoImageDB ();
    $pi_select->select_data($db_link,$photo_id);

    // アップロード用のインスタンスを生成します。
    $fl = new FileUpload($_FILES['p_photo_filename'], "", "", "", "", $pi_select->additional_constraints1, "");
    // ファイルをアップロードします。
    $fl->upload();
    // サムネイルを作成します。
    $fl->make_thumbfile();

    $pi = new PhotoImageDB ();
    $pi->photo_id = $photo_id;										// 画像ID
    $pi->photo_filename = $fl->up_url[0];							// アップロードURL
    $pi->photo_filename_th1 = isset($fl->up_url[1])?$fl->up_url[1]:"";						// サムネイル1
    $pi->photo_filename_th2 = isset($fl->up_url[2])?$fl->up_url[2]:"";						// サムネイル2
    $pi->photo_filename_th3 = isset($fl->up_url[3])?$fl->up_url[3]:"";						// サムネイル3
    $pi->photo_filename_th4 = isset($fl->up_url[4])?$fl->up_url[4]:"";						// サムネイル4
    $pi->photo_filename_th5 = isset($fl->up_url[5])?$fl->up_url[5]:"";						// サムネイル5
    $pi->photo_filename_th6 = isset($fl->up_url[6])?$fl->up_url[6]:"";						// サムネイル6
    $pi->photo_filename_th7 = isset($fl->up_url[7])?$fl->up_url[7]:"";						// サムネイル7
    $pi->photo_filename_th8 = isset($fl->up_url[8])?$fl->up_url[8]:"";						// サムネイル8
    $pi->photo_filename_th9 = isset($fl->up_url[9])?$fl->up_url[9]:"";						// サムネイル9
    $pi->photo_filename_th10 = isset($fl->up_url[10])?$fl->up_url[10]:"";					// サムネイル10

    $pi->update_thumb_1_10($db_link);

    if ((int)$pi_select->publishing_situation_id == 2)
    {
        // イメージをバイナリを変換して、DBに保存する
        $pi->write_imagetodb($db_link, $photo_id);
    }

    $sql = "UPDATE photoimg SET ds_change_image = 1 WHERE photo_id = ?";
    $stmt = $db_link->prepare($sql);
    $stmt->bindParam(1,$photo_id);
    $stmt->execute();

    showJsAlterMessageAndHistory("正常に変更されました。",$back_url);
}
catch(Exception $e)
{
    // アップロードしたファイルを削除します。
    if ($fl!=null)
    {
        $fl->delete_upfile();
    }
    js_alter_history($e->getMessage());

    return false;
}
?>