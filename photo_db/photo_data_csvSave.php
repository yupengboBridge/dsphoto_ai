<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
set_time_limit(3600);
error_reporting(E_ALL);

ini_set("display_errors","On");
date_default_timezone_set('Asia/Tokyo');
$csv_dir = dirname(__FILE__).'/'.$csv['local_export_dir'];
//create_folders($csv_dir);
$data = "";

$end = "NG";

$init_flag = 1;

$csv_file_name = "ds_photo_data_all_list.csv";

// ＤＢへ接続します。
$db_link = db_connect();

export_csv();

function i($strInput)
{
    //return iconv('utf-8','shift-jis',$strInput);
    return $strInput;
}


/**
    *导出数据转换
    * @param $result
    */
function array_to_string($result,$flg=1)
{
    global $csv_dir,$data,$csv_file_name;

    if(empty($result)){
        return i("データ無し");
    }

    if($flg == 1)
    {
        //$data=i('ID,画像管理番号,掲載状況,登録区分,素材管理番号,BUD_PHOTO番号,被写体の名称,素材（画像）の詳細内容,撮影時期,撮影時期,掲載期間From,掲載期間To,期間,写真入手元,写真入手元　その他,掲載可能範囲,外部出稿条件付き,要クレジット,要使用許可 ,このアカウントのみ使用可,版権所有者,お客様情報　部署名,お客様情報　名前,新規者のアカント,新規者の名前,許可者のアカント,許可者の名前,許可日,備考,新規日付');
        $data=i('"ID","画像管理番号","掲載状況","登録区分","素材管理番号","BUD_PHOTO番号","被写体の名称","素材（画像）の詳細内容","撮影時期","撮影時期","掲載期間From","掲載期間To","期間","写真入手元","写真入手元　その他","掲載可能範囲","外部出稿条件付き","要クレジット","要使用許可 ","このアカウントのみ使用可","版権所有者","お客様情報　部署名","お客様情報　名前","新規者のアカント","新規者の名前","許可者のアカント","許可者の名前","許可日","備考","新規日付"');
        $data .="\n";
        if(is_file($csv_dir.$csv_file_name)) {unlink($csv_dir.$csv_file_name);}
    } else {
        $data = "";
    }

    foreach($result as $val) {
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->photo_id)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->photo_mno)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->publishing_situation_id)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->registration_division_id)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->source_image_no)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->bud_photo_no)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->photo_name)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->photo_explanation)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->take_picture_time_id)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->take_picture_time2_id)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->dfrom)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->dto)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->kikan)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->borrowing_ahead_id)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->content_borrowing_ahead)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->range_of_use_id)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->use_condition)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->additional_constraints1)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->additional_constraints2)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->monopoly_use)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->copyright_owner)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->customer_section)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->customer_name)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->registration_account)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->registration_person)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->permission_account)))))).'"'.",";
        $data .='"'.str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace(",","，",str_replace("NULL","",i($val->permission_person)))))).'"'.",";
        if(($val->permission_date == "0000-00-00") || ($val->permission_date == "NULL") || empty($val->permission_date))
        {
            $data .= "\"\",";
        } else {
            $date = new DateTime($val->permission_date);
            $data .="\"".i($date->format('Y-m-d'))."\",";
        }
        $data .="\"".str_replace("\r"," ",str_replace("\n"," ",str_replace("\r\n"," ",str_replace("，",",",str_replace("","NULL",i($val->note))))))."\",";
        if(($val->register_date == "0000-00-00") || ($val->register_date == "NULL") || empty($val->register_date))
        {
            $data .= "\"\",";
        } else {
            $date = new DateTime($val->register_date);
            $data .="\"".i($date->format('Y-m-d'))."\"";
        }
        $data .="\n";

        $handle = fopen ($csv_dir.$csv_file_name,"a");
        fwrite($handle,$data);
        fclose ($handle);

        $data = "";
    }
}

function export_csv()
{
    global $db_link,$csv_dir,$data,$end,$init_flag,$csv_file_name;

    $images = array();

    if($db_link) {
        $sql="select * from photoimg where publishing_situation_id = 2 and photo_server_flg = 0";
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        if($result == true) {
            $count = $stmt->rowCount();
            $i = 1;
            $maxOneTime = $count>10?10:$count;
            if(is_file($csv_dir.$csv_file_name)) {unlink($csv_dir.$csv_file_name);}
            while ($image_data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $photo_idata = new PhotoImageDataAll();
                $photo_idata->set_data($image_data);
                $images[] = $photo_idata;
                if($i == $maxOneTime) {
                    array_to_string($images,$init_flag);
                    if($init_flag == 1)
                    {
                        $init_flag = $init_flag + 1;
                    }
                    $i = 1;
                    unset($images);
                } else {
                    $i = $i + 1;
                }
            }
            if($i > 1) {
                array_to_string($images,$init_flag);
            }
            //print " image count: ".($i - 1)."\r\n";
            $end = "OK";
        }
    }
    sendMail($end);
}

function sendMail($isSucess){

    require_once 'PhpMailer/PHPMailerAutoload.php';
    define("Mode","always");
    

   $mailaddresses = array("nakao@bridg11e.vc","jiangtao@brid11ge.vc","tanaka@br11idge.vc ", "yaolili@brid11ge.vc");
    //$mailaddresses = array("wangjingdong@bridge.vc", "jiangtao@bridge.vc", "yaolili@bridge.vc");


    $headers['Date'] = date("r");

    $subject =      "ds_photo_data_all_list作成結果";
    $subject_b = mb_convert_encoding($subject,"JIS","UTF-8");
    $subject_f = "=?ISO-2022-JP?B?" . base64_encode($subject_b) . "?=";
    $body = "";
    if($isSucess == "OK"){
        $body = <<<EOS
ds_photo_data_all_listの作成に成功しました。
EOS;
    }else {
        $body = <<<EOS
ds_photo_data_all_listの作成に失敗しました。
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
    $mail->Password = 'bridge2008';                           // SMTP password
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
    //$mail->SMTPDebug = 1;
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
        else {

            //print "send mail success\r\n";
        }

    }catch (Exception $e){
        print $e->getMessage();
        exit();
    }

}

?>