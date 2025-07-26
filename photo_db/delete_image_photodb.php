<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
date_default_timezone_set('Asia/Tokyo');

//// セッション管理をスタートします。
//session_start();
//
//$s_login_id = array_get_value($_SESSION,'login_id' ,"");
//$s_login_name = array_get_value($_SESSION,'user_name' ,"");
//$s_security_level = array_get_value($_SESSION,'security_level' ,"");
//$comp_code = array_get_value($_SESSION,'compcode' ,"");
//$s_group_id = array_get_value($_SESSION,'group' ,"");
//$s_user_id = array_get_value($_SESSION,'user_id' ,"");
//
////ログインしているかをチェックします。
//if (empty($s_login_id))
//{
//  // ログイン後のTOPページへリダイレクトします。
//  header_out($logout_page);
//}
$isSuccess = true;
try{

$photo_server_flg = 0;

$now = date("Y-m-d");
$where = " dto < '".$now." 00:00:00' AND `photo_server_flg` = ".$photo_server_flg;

// ＤＢへ接続します。
$db_link = db_connect();

// イメージ検索のクラス
$img_all = new ImageSearch();
// PhotoImageのインスタンスを生成します。
$pi = new PhotoImageDB ();

$img_all->istart = 0;
$img_all->iend = 5000;
// 写真を取得
$img_all->select_image_registed_forDelete($db_link,$where,"");
// イメージ総数を取得する
if (!empty($img_all->images))
{
    $img_ary = $img_all->images;

    $ph_img_all = new PhotoImageDataAll();
    for ($i = 0 ; $i < count($img_ary); $i++)
    {
        $ph_img_all = $img_ary[$i];

        $logstr = date("Y-m-d H:i:s").",admin,BUD管理者,".$ph_img_all->photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$ph_img_all->photo_name));
        $logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n][,])[\s]+'", " ",$ph_img_all->photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$ph_img_all->bud_photo_no)).",";
        $logstr .= $ph_img_all->dfrom.",".$ph_img_all->dto.",0,".$ph_img_all->registration_person."\r\n";
        $pi->delete_data($db_link, $ph_img_all->photo_id);
        if (!empty($logstr)) write_log_tofile($logstr);
    }   
    if(file_exists(dirname(__FILE__)."/log/".date("Y-m-d").".log"))
    {
        ## Connect to a local database server (or die) ##
        $dbH = mysqli_connect($db_host, $db_user, $db_password) or die('Could not connect to MySQL server.<br>' . mysqli_error($dbH));

        ## Select the database to insert to ##
        mysqli_select_db($dbH,$db_name) or die('Could not select database.<br>' . mysqli_error($dbH));

        ## CSV file to read in ##
        $CSVFile = dirname(__FILE__)."/log/".date("Y-m-d").".log";
        $sql = 'LOAD DATA LOCAL INFILE "'.$CSVFile.'" INTO TABLE '.$table_log_name.' FIELDS TERMINATED BY "," LINES TERMINATED BY "\\r\\n";';
        mysqli_query($dbH,$sql) or die('Error loading data file.<br>' . mysqli_error($dbH)); 
        ## Close database connection when finished ##
        mysqli_close($dbH);
    }
    $file_dir = dirname(__FILE__)."/log/back/";
    $dossier = opendir($file_dir);
    $file_name = "";
    while ($Fichier = readdir($dossier))
    {
        if ($Fichier != "." && $Fichier != ".." && $Fichier != "Thumbs.db")
        {
            $file_name = strtolower($Fichier);
            $filesize=abs(filesize($file_dir.$file_name));
            if($filesize==0)
            {
                exec("rm -rf ".$file_dir.$file_name);
            }
        }
    }
    print "画像削除を完了いたしました。";
} else {
    print "期限切れた画像が見つかりません。";
}
}catch (Exception $e){
    $isSuccess = false;
}
    sendMail($isSuccess);



/*
 * 関数名：write_log_tofile
 * 関数説明：画像を削除すると、削除した画像はログファイルに出力する
 * パラメタ：logmsg:ログ情報
 * 戻り値：無し
 */
function write_log_tofile($logmsg)
{
    // CSVファイルを出力する
    $file = fopen(dirname(__FILE__)."/log/".date("Y-m-d").".log","a+");
    fwrite($file,$logmsg);
    fclose($file);
}
function sendMail($isSucess){

    require_once 'PhpMailer/PHPMailerAutoload.php';
    define("Mode","always");

    if($isSucess){
        if(!(defined("Mode") && Mode=="always")){
            return ;
        }
    }

    $mailaddresses = array("xiecongwen@bridge.vc","jiangtao@bridge.vc");


    $headers['Date'] = date("r");

    $subject =      "photo_db delete batch";
    $subject_b = mb_convert_encoding($subject,"JIS","UTF-8");
    $subject_f = "=?ISO-2022-JP?B?" . base64_encode($subject_b) . "?=";
    $body = "";
    if($isSucess){
        $body = <<<EOS
delete_image_photodb.php 成功しました。
EOS;
    }else {
        $body = <<<EOS
delete_image_photodb.php 失敗しました。
EOS;
    }

    $body = $body . "\n\n";

    $body = $body . "\n";
    $body = $body . "＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊\n";
    $body = $body . "　株式会社ブリッジ\n";
    $body = $body . "　　〒104-0032\n";
    $body = $body . "　　　東京都中央区八丁堀4丁目12-20　第1SSビル8C\n";
    $body = $body . "　　　http://www.bridge.vc/\n";
    $body = $body . "　　　TEL　03-6222-3222\n";
    $body = $body . "　　　Mail info@bridge.vc\n";
    $body = $body . "＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊＊\n";
    $body_jis = mb_convert_encoding($body,"JIS","UTF-8");

    // ヘテムルを利用するときの設定
    $mail = new PHPMailer();
    $mail->CharSet = 'ISO-2022-JP';
    $mail->isSMTP();
    $mail->Host = 'mail35.heteml.jp';
    $mail->SMTPAuth = true;
    $mail->Port = 587;
    $mail->Username = 'info@bridge.vc';                 // SMTP username
    $mail->Password = 'infobridge2008';                           // SMTP password
    $mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
        )
    );
    //$mail->SMTPSecure = 'tls';                            // smtp認証を使うためtrue。デフォルトではfalse
    $mail->From = 'info@bridge.vc';
    $mail->FromName = 'bridge';
    foreach ($mailaddresses as $address){
        $mail->addAddress($address);     // Add a recipient
    }

//     $mail->addAddress('info@bridge.vc');
//     $mail->addReplyTo('info@bridge.vc');

//     $mail->addBCC('info@bridge.vc');

    $mail->Subject = $subject_f;
    $mail->Body    = $body_jis;

    try{

        // Create the mail object using the Mail::factory method
        if(!$mail->send()) {

            // 正しくメールが送れなかった場合
            $errmsg = "正しくメールが送れませんでした。（" .   $mail->ErrorInfo . "）\r\n";
            print $errmsg;

        }

    }catch (Exception $e){
        //print $e->getMessage();
        exit();
    }

    print "success";
}

?>