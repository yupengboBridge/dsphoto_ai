<?php
require_once ('./config.php');
require_once ('./lib.php');
require_once ('./downloadutil.php');
set_time_limit(3600);
date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

$s_login_id = array_get_value($_SESSION, 'login_id', "");
$s_login_name = array_get_value($_SESSION, 'user_name', "");
$s_security_level = array_get_value($_SESSION, 'security_level', "");
$comp_code = array_get_value($_SESSION, 'compcode', "");
$s_group_id = array_get_value($_SESSION, 'group', "");
$s_user_id = array_get_value($_SESSION, 'user_id', "");

$csv_dir = "./csv/";
$data = "";

$end = "NG";

$init_flag = 1;

$csv_file_name = "photo_data_all_list.csv";

// ログインしているかをチェックします。
if (empty($s_login_id)) {
    // ログイン後のTOPページへリダイレクトします。
    header_out($logout_page);
}
// xiazai
if ($_GET["c"] == "download") {
    downloads($csv_file_name);
}

// ＤＢへ接続します。
$db_link = db_connect();

if (isset($_POST['action']) && $_POST['action'] == "csv_export") {
    export_csv();
}

function i($strInput)
{
    // return iconv('utf-8','shift-jis',$strInput);
    return $strInput;
}

function replaceNewLine($strInput)
{
    return '"' . str_replace("\r", " ", str_replace("\n", " ", str_replace("\r\n", " ", str_replace(",", "，", str_replace("NULL", "", i($strInput)))))) . '"' . ",";
}

/**
 * 下载csv文件
 *
 * @param
 *            $name文件名
 */
function downloads($name)
{
    global $csv_file_name;
    $file_dir = "./csv/";
    if (! file_exists($file_dir . $name)) {
        header("Content-type: text/html; charset=utf-8");
        echo "CSVファイル(" . $csv_file_name . ")がありません!";
        exit();
    } else {
        // $file = fopen($file_dir.$name,"r");
        // Header("Content-type: application/octet-stream");
        // Header("Accept-Ranges: bytes");
        // Header("Accept-Length: ".filesize($file_dir . $name));
        // Header("Content-Disposition: attachment; filename=".$name);
        // echo fread($file, filesize($file_dir.$name));
        // fclose($file);
        downloadfile($file_dir . $name, $name);
        // exit;
    }
}

/**
 * 导出数据转换
 *
 * @param
 *            $result
 */
function array_to_string($result, $flg = 1)
{
    global $csv_dir, $data, $csv_file_name;
    
    if (empty($result)) {
        return i("データ無し");
    }
    
    if ($flg == 1) {
        // $data=i('ID,画像管理番号,掲載状況,登録区分,素材管理番号,BUD_PHOTO番号,被写体の名称,素材（画像）の詳細内容,撮影時期,撮影時期,掲載期間From,掲載期間To,期間,写真入手元,写真入手元　その他,掲載可能範囲,外部出稿条件付き,要クレジット,要使用許可 ,このアカウントのみ使用可,版権所有者,お客様情報　部署名,お客様情報　名前,新規者のアカント,新規者の名前,許可者のアカント,許可者の名前,許可日,備考,新規日付');
        $data = i('"ID","画像管理番号","掲載状況","登録区分","素材管理番号","BUD_PHOTO番号","被写体の名称","素材（画像）の詳細内容","撮影時期","撮影時期","掲載期間From","掲載期間To","期間","写真入手元","写真入手元　その他","掲載可能範囲","外部出稿条件付き","要クレジット","要使用許可 ","このアカウントのみ使用可","版権所有者","お客様情報　部署名","お客様情報　名前","新規者のアカント","新規者の名前","許可者のアカント","許可者の名前","許可日","備考","新規日付"');
        $data .= "\n";
        if (is_file($csv_dir . $csv_file_name)) {
            unlink($csv_dir . $csv_file_name);
        }
    } else {
        $data = "";
    }
    
    foreach ($result as $val) {
        $data .= replaceNewLine($val->photo_id);
        $data .= replaceNewLine($val->photo_mno);
        $data .= replaceNewLine($val->publishing_situation_id);
        $data .= replaceNewLine($val->registration_division_id);
        $data .= replaceNewLine($val->source_image_no);
        $data .= replaceNewLine($val->bud_photo_no);
        $data .= replaceNewLine($val->photo_name);
        $data .= replaceNewLine($val->photo_explanation);
        $data .= replaceNewLine($val->take_picture_time_id);
        $data .= replaceNewLine($val->take_picture_time2_id);
        $data .= replaceNewLine($val->dfrom);
        $data .= replaceNewLine($val->dto);
        $data .= replaceNewLine($val->kikan);
        $data .= replaceNewLine($val->borrowing_ahead_id);
        $data .= replaceNewLine($val->content_borrowing_ahead);
        $data .= replaceNewLine($val->range_of_use_id);
        $data .= replaceNewLine($val->use_condition);
        $data .= replaceNewLine($val->additional_constraints1);
        $data .= replaceNewLine($val->additional_constraints2);
        $data .= replaceNewLine($val->monopoly_use);
        $data .= replaceNewLine($val->copyright_owner);
        $data .= replaceNewLine($val->customer_section);
        $data .= replaceNewLine($val->customer_name);
        $data .= replaceNewLine($val->registration_account);
        $data .= replaceNewLine($val->registration_person);
        $data .= replaceNewLine($val->permission_account);
        $data .= replaceNewLine($val->permission_person);
        if (($val->permission_date == "0000-00-00") || ($val->permission_date == "NULL") || empty($val->permission_date)) {
            $data .= "\"\",";
        } else {
            $date = new DateTime($val->permission_date);
            $data .= "\"" . i($date->format('Y-m-d')) . "\",";
        }
        $data .= "\"" . str_replace("\r", " ", str_replace("\n", " ", str_replace("\r\n", " ", str_replace("，", ",", str_replace("", "NULL", i($val->note)))))) . "\",";
        if (($val->register_date == "0000-00-00") || ($val->register_date == "NULL") || empty($val->register_date)) {
            $data .= "\"\",";
        } else {
            $date = new DateTime($val->register_date);
            $data .= "\"" . i($date->format('Y-m-d')) . "\"";
        }
        $data .= "\n";
        
        $handle = fopen($csv_dir . $csv_file_name, "a");
        fwrite($handle, $data);
        fclose($handle);
        
        $data = "";
    }
}

function export_csv()
{
    global $db_link, $csv_dir, $data, $end, $init_flag, $csv_file_name;
    
    $images = array();
    
    if ($db_link) {
        $sql = "select * from photoimg where publishing_situation_id = 2 and photo_server_flg = 0";
        
        $stmt = $db_link->prepare($sql);
        $result = $stmt->execute();
        
        if ($result == true) {
            $count = $stmt->rowCount();
            $i = 1;
            $maxOneTime = $count > 10 ? 10 : $count;
            if (is_file($csv_dir . $csv_file_name)) {
                unlink($csv_dir . $csv_file_name);
            }
            while (! ! ($image_data = $stmt->fetch(PDO::FETCH_ASSOC))) {
                $photo_idata = new PhotoImageDataAll();
                $photo_idata->set_data($image_data);
                $images[] = $photo_idata;
                
                if ($i == $maxOneTime) {
                    array_to_string($images, $init_flag);
                    if ($init_flag == 1) {
                        $init_flag = $init_flag + 1;
                    }
                    $i = 1;
                    unset($images);
                } else {
                    $i = $i + 1;
                }
            }
            
            if ($i > 1) {
                array_to_string($images, $init_flag);
            }
            $end = "OK";
        }
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>写真DBデータのダウンロード</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css"
	media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="./js/jquery.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="./js/datepicker/WdatePicker.js"></script>
<script type="text/javascript">
/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
	//----------フレームの設定  開始---------------
	var obj_frame = top.document.getElementById('iframe_middle1');
	if(obj_frame) obj_frame.style.height = 0;
	var obj_frame = top.document.getElementById('iframe_middle2');
	if(obj_frame) obj_frame.style.height = 0;
	set_frameheight('iframe_bottom',800);
	//----------フレームの設定  終了---------------
}

window.onload = function()
{
	init();
}

function csv_export_click()
{
	$("#msgwait").show();
	$("#csv_export").hide();
	$("#msgdl").hide();
    document.csv_form.submit();
}

</script>
<!-- javascript ここまで -->
</head>
<body>
	<form name="csv_form" action="<?php echo $_SERVER['PHP_SELF'];?>"
		method="post">
		<input type="hidden" name="action" value="csv_export" />
		<div id="zentai">
			<!-- メインコンテンツ　ここから -->
			<div id="contents">
				<div class="photo_pickup">
					<h2>写真DBデータのダウンロード</h2>
					<div class="pickup_contents">
						<dl class="album_registering">
							<dt>ここから写真DBデータをダウンロードできます。</dt>
						</dl>
						<br />
                <?php
                if ($end == "OK") {
                    ?>
                    <div id="msgwait"
							style="display: none; color: red; font-size: 14px">
							CSVデータをエクスポートしています。しばらくお待ちください。<img src="./parts/jindutiao.gif" />
						</div>
						<br />
						<div align="right">
							<input type="button" id="csv_export" value="実　　行"
								onclick="csv_export_click();" style="display: block" />
						</div>
						<br />
						<div id="msgdl"
							style="display: block; color: red; font-size: 14px">
							データの生成が完了しました、ここから、ファイルをダウンロードしてください。 <a
								href="./photo_data_download.php?c=download"
								style="font-size: 18px">ダウンロード</a>
						</div>
                <?php
                } else {
                    ?>
                    <div id="msgwait"
							style="display: none; color: red; font-size: 14px">
							CSVデータをエクスポートしています。しばらくお待ちください。<img src="./parts/jindutiao.gif" />
						</div>
						<br />
						<div id="msgdl" style="display: none; color: red; font-size: 14px">
							データの生成が完了しました、ここから、ファイルをダウンロードしてください。 <a
								href="./photo_data_download.php?c=download"
								style="font-size: 18px">ダウンロード</a>
						</div>
						<br />
						<div align="right">
							<input type="button" id="csv_export" value="実　　行"
								onclick="csv_export_click();" style="display: block" />
						</div>
                <?php
                }
                ?>
			</div>
				</div>
			</div>
		</div>
	</form>
</body>
</html>
