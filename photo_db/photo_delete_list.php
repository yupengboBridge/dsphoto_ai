<?php
ini_set("display_errors", "On");
require_once ('./Pager.php');
require_once ('./config.php');
require_once ('./lib.php');
require_once ('./downloadutil.php');

date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

$s_login_id = array_get_value($_SESSION, 'login_id', "");
$s_login_name = array_get_value($_SESSION, 'user_name', "");
$s_security_level = array_get_value($_SESSION, 'security_level', "");
$comp_code = array_get_value($_SESSION, 'compcode', "");
$s_group_id = array_get_value($_SESSION, 'group', "");
$s_user_id = array_get_value($_SESSION, 'user_id', "");

define("SAVECSVPATH", "./csv/");
define("SENDMAILTO", "");
define("SENDMAILFROM", "");
define("SENDMAILMESSAGE", "削除予定の画像リストを送付いたします。");
define("SENDMAILTITLE", "削除予定の画像リスト");

$csv_file_name = "photo_data_delete_list.csv";

// ログインしているかをチェックします。
if (empty($s_login_id)) {
    // ログイン後のTOPページへリダイレクトします。
    header_out($logout_page);
}

// print_r($_GET);
// exit;
$now = date("Y-m-d");
$now_ten = date("Y-m-d", strtotime("+10 day"));
$now_two_month = date("Y-m-d", strtotime("+2 month"));
// $where = " dto >= '" . $now . " 00:00:00'";
// $where .= " AND dto <= '" . $now_ten . " 23:59:59'";
$where = " 1=1 ";

// added by wangtongchao 2011-12-06 begin
if (isset($_POST['action']) || ! isset($_GET['pageID'])) {
    unset($_SESSION['date_from_delete']);
    unset($_SESSION['date_to_delete']);
}
// added by wangtongchao 2011-12-06 end
$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : "";
$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : "";
// added by wangtongchao 2011-12-06 begin
if ($date_from != "") {
    $_SESSION['date_from_delete'] = $date_from;
} elseif (isset($_SESSION['date_from_delete'])) {
    $date_from = $_SESSION['date_from_delete'];
}

if ($date_to != "") {
    $_SESSION['date_to_delete'] = $date_to;
} elseif (isset($_SESSION['date_to_delete'])) {
    $date_to = $_SESSION['date_to_delete'];
}

// added by wangtongchao 2011-12-06 end

if (! empty($date_from) && ! empty($date_to)) {
    $where .= " AND dto >= '" . $date_from . " 00:00:00'";
    $where .= " AND dto <= '" . $date_to . " 23:59:59'";
} elseif (empty($date_from) && ! empty($date_to)) {
    $where .= " AND dto <= '" . $date_to . " 23:59:59'";
} elseif (! empty($date_from) && empty($date_to)) {
    $where .= " AND dto >= '" . $date_from . " 00:00:00'";
} else {
    $where .= " AND dto >= '" . $now . " 00:00:00'";
    $where .= " AND dto <= '" . $now_two_month . " 23:59:59'";
}

if (isset($_POST['action']) && $_POST['action'] == "send_mail") {
    // ＤＢへ接続します。
    $db_link = db_connect();
    $img_data = new ImageSearch();
    // 写真を取得
    $export_where = " dto >= '" . $now . " 00:00:00'";
    $export_where .= " AND dto <= '" . $now_ten . " 23:59:59'";
    $img_data->select_image_csv($db_link, "", $export_where);
    
    $filename = SAVECSVPATH . date('YmdHis') . ".csv";
    export_csv($img_data->images, $filename);
    $result = sendmail(SENDMAILTO, SENDMAILTITLE, SENDMAILMESSAGE, SENDMAILFROM, "", array(
        $filename
    ));
    if ($result) {
        echo "<script language='javascript'>
				alert('メールを送信しました。ご確認して下さい。');
			  </script>";
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}

// ダウンロード
if (isset($_POST['action']) && $_POST['action'] == "download") {
    // ＤＢへ接続します。
    $db_link = db_connect();
    $img_data = new ImageSearch();
    // 写真を取得
    $img_data->select_image_registed($db_link, $where);
    
    $filename = SAVECSVPATH . $csv_file_name;
    unlink($filename);
    export_csv($img_data->images, $filename);
    if (! file_exists($filename)) {
        header("Content-type: text/html; charset=utf-8");
        echo "CSVファイル(" . $csv_file_name . ")がありません!";
        exit();
    } else {
        downloadfile($filename, $csv_file_name);
    }
}

// 一ページ内に表示する件数
$page_records_cnt = 20;
// 一ページ内に表示するリンク数
$page_links_cnt = 20;
// 一ページ内に表示するリンク数
$list_reg_cnt = 0;
// リンク
$pager_links = NULL;

// イメージ検索のクラス
$img_all = new ImageSearch();

function getcount()
{
    global $db_link, $where;
    
    $db_link = db_connect();
    
    $sql = "SELECT count(*) cnt FROM photoimg";
    $sql .= " WHERE photoimg.publishing_situation_id = 2 and photo_server_flg=0 ";
    if (! empty($where)) {
        $sql .= " AND " . $where;
    }
    $sql .= " ORDER BY photo_id ";
    
    $stmt = $db_link->prepare($sql);
    $result = $stmt->execute();
    
    if ($result == true) {
        // 最終番号を取得します。
        $max = $stmt->fetch(PDO::FETCH_ASSOC);
        return $max['cnt'];
    } else {
        return 0;
    }
}

/**
 * 去除换行符，首尾添加双引号
 *
 * @param string $strInput            
 * @param bool $isEnd
 *            是否为行的结尾，不是的话添加逗号，是则添加
 * @return string 替换后的字符串
 */
function i($strInput, $isEnd = false)
{
    // return iconv('utf-8', 'shift-jis', $strInput);
    return '"' . str_replace("\r", " ", str_replace("\n", " ", str_replace("\r\n", " ", str_replace(",", "，", str_replace("NULL", "", $strInput))))) . '"' . ($isEnd ? '' : ',');
}

/**
 * 导出数据转换
 *
 * @param
 *            $result
 */
function array_to_string($result)
{
    if (empty($result)) {
        return i("データ無し");
    }
    
    $data = 'ID,申請日 ,申請アカウント,写真管理番号,写真名,バドフォト番号,詳細内容,掲載期間';
    $data .= "\n";
    
    foreach ($result as $val) {
        $data .= $val->photo_id . ',';
        $date = new DateTime($val->register_date);
        $data .= i($date->format('Y/m/d'));
        $data .= i($val->registration_person);
        $data .= i($val->photo_mno);
        $data .= i($val->photo_name);
        $data .= i($val->bud_photo_no);
        $data .= i($val->photo_explanation);
        $dfrom = new DateTime($val->dfrom);
        $dto = new DateTime($val->dto);
        $data .= i($dfrom->format('Y/m/d') . "―" . $dto->format('Y/m/d'), true);
        $data .= "\n";
    }
    return $data;
}

function export_csv($data, $filename)
{
    $file_open = fopen($filename, "w");
    if ($file_open) {
        fwrite($file_open, array_to_string($data));
    }
}

function sendmail($to, $subject, $message, $from, $content_type, $attache = "")
{
    if (! empty($from))
        $head = "From:   $from\n";
    if (empty($content_type))
        $content_type = "text/plain";
    
    if (is_array($attache)) {
        $boundary = "===" . md5(uniqid("")) . "===";
        $head .= "Mime-Version:   1.0\nContent-Type:   multipart/mixed;   boundary=\"";
        $head .= "$boundary\"\n\nThis   is   a   multi-part   message   in   MIME   format.\n\n";
        $head .= "--$boundary\n";
        $head .= "Content-Type:   $content_type;charset=utf-8\n";
        $head .= "\n$message\n\n";
        
        while (list ($key, $val) = each($attache)) {
            $fd = fopen("$val", "r") or die("unable   to   open   file   $val");
            $contents = chunk_split(base64_encode(fread($fd, filesize("$val"))));
            fclose($fd);
            $head .= "--$boundary\n";
            $head .= "Content-Type:   application/octet-stream;   name=\"" . basename($val);
            $head .= "\"\nContent-Transfer-Encoding:   BASE64\n";
            $head .= "Content-Disposition:   attachment;   filename=\"" . basename($val);
            $head .= "\"\n\n" . $contents . "\n\n";
        }
        $head .= "--" . $boundary . "--\n\n";
    } else {
        if (! empty($content_type)) {
            $head .= "Content-Type:   $content_type\n";
            $head .= "\n$message\n";
        }
    }
    $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
    return mail($to, $subject, "", $head);
}
/*
 * 関数名：ShowPagesList
 * 関数説明：ページングの処理と出力
 * パラメタ：無し
 * 戻り値：無し
 */
function ShowPagesList()
{
    global $page_records_cnt, $page_links_cnt, $list_reg_cnt, $pager_links;
    // global $startcnt,$lastcnt;
    
    $tmpcntitems = getcount();
    
    // Pagerのパラメータを設定します。
    $option = array(
        'mode' => 'Jumping', // 表示タイプ(Jumping/Sliding)
        'perPage' => $page_records_cnt, // 一ページ内に表示する件数
        'delta' => $page_links_cnt, // 一ページ内に表示するリンク数
        'totalItems' => $tmpcntitems, // ページング対象データの総数
        'separator' => ' ', // ページリンクのセパレータ文字列
        'prevImg' => 'BACK<<', // 戻るリンク(imgタグ使用可)
        'nextImg' => 'NEXT>>', // 次へリンク(imgタグ使用可)
        'importQuery' => FALSE, // 自動的にPOST値をページングのHTMLタグに付与しません
        'append' => FALSE, // 自動でページをアペンドしません。
        'fileName' => "photo_delete_list.php?pageID=%d&ppage=" . $page_records_cnt
    );
    
    // ページングのインスタンスを生成します。
    $pager = & Pager::factory($option);
    
    // 表示する行数を決定します。
    // 開始行を決定します。
    $pg = $pager->getCurrentPageID();
    if ($pg <= 0) {
        $pg = 1;
    }
    
    $list_reg_cnt = $tmpcntitems;
    
    $pager_links = $pager->getLinks();
}

/*
 * 関数名：dispay_pagelist
 * 関数説明：ページングの処理と出力
 * パラメタ：無し
 * 戻り値：無し
 */
function dispay_pagelist()
{
    global $pager_links;
    
    print "				<dd>\r\n";
    print "					<ul class=\"txt2\">\r\n";
    print "					<li class=\"txt_num\">\r\n";
    print($pager_links["all"]);
    print "					</li>\r\n";
    print "					</ul>\r\n";
    print "				</dd>\r\n";
    // ページングの処理---------------------------------------------------End
}

/*
 * 関数名：disp_img
 * 関数説明：画面の表示
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_img()
{
    global $list_reg_cnt, $img_all, $page_records_cnt, $where, $orderby;
    // global $startcnt,$lastcnt;
    
    $tmpcur_page_global = (int) array_get_value($_REQUEST, "pageID", "0");
    if ($tmpcur_page_global == 0) {
        $tmpcur_page_global = 1;
    }
    
    $tmpend = $tmpcur_page_global * $page_records_cnt;
    
    $tmpstart = $tmpend - $page_records_cnt;
    
    $img_all->istart = $tmpstart;
    $img_all->per_page = $page_records_cnt;
    $img_all->iend = $tmpend;
    
    try {
        // ＤＢへ接続します。
        $db_link = db_connect();
        // 写真を取得
        $img_all->select_image_registed($db_link, $where, $orderby);
        if (empty($img_all->images)) {
            print "<dt class='form_ttl'>削除予定画像一覧<span>（0件）</span></dt>\r\n";
            print "<dd class=\"form_contents_indent\" style=\"width:960px\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"photo_album\" style=\"width:960px\">\r\n";
            print "	<tr>\r\n";
            $btn_val_day = "申請日";
            $btn_val_account = "申請者アカウント";
            $btn_val_mno = "写真管理番号";
            $btn_val_photo_name = "写真名";
            $btn_val_budno = "バドフォト番号";
            $btn_val_explanation = "詳細内容";
            print "		<th class=\"day\">" . $btn_val_day . "</th>\r\n";
            print "		<th style=\"width:14%\">" . $btn_val_account . "</th>\r\n";
            print "		<th style=\"width:12%\">" . $btn_val_mno . "</th>\r\n";
            print "		<th style=\"width:20%\">" . $btn_val_photo_name . "</th>\r\n";
            print "		<th style=\"width:12%\">" . $btn_val_budno . "</th>\r\n";
            print "		<th>" . $btn_val_explanation . "</th>\r\n";
            print "		<th style=\"width:11%\">掲載期間</th>\r\n";
            print "	</tr>\r\n";
            print "	</table>\r\n";
            print "</dd>\r\n";
            
            return;
        }
        // イメージ総数を取得する
        if (! empty($img_all->images)) {
            $img_ary = $img_all->images;
            
            ShowPagesList();
            if ((int) $tmpend > (int) $list_reg_cnt) {
                $img_all->iend = $list_reg_cnt;
            }
            print "<dt class='form_ttl'>削除予定画像一覧<span>（" . $img_all->iend . "件/" . $list_reg_cnt . "件中）</span></dt>\r\n";
            dispay_pagelist();
            print "<dd class=\"form_contents_indent\" style=\"width:960px\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"photo_album\" style=\"width:960px\">\r\n";
            print "	<tr>\r\n";
            $btn_val_day = "申請日";
            $btn_val_account = "申請者アカウント";
            $btn_val_mno = "写真管理番号";
            $btn_val_photo_name = "写真名";
            $btn_val_budno = "バドフォト番号";
            $btn_val_explanation = "詳細内容";
            print "		<th class=\"day\">" . $btn_val_day . "</th>\r\n";
            print "		<th style=\"width:14%\">" . $btn_val_account . "</th>\r\n";
            print "		<th style=\"width:12%\">" . $btn_val_mno . "</th>\r\n";
            print "		<th style=\"width:20%\">" . $btn_val_photo_name . "</th>\r\n";
            print "		<th style=\"width:12%\">" . $btn_val_budno . "</th>\r\n";
            print "		<th>" . $btn_val_explanation . "</th>\r\n";
            print "		<th style=\"width:11%\">掲載期間</th>\r\n";
            print "	</tr>\r\n";
            
            $ph_img_all = new PhotoImageDataAll();
            for ($i = 0; $i < count($img_ary); $i ++) {
                $ph_img_all = $img_ary[$i];
                $date_tmp = substr($ph_img_all->register_date, 2, 2) . "." . substr($ph_img_all->register_date, 5, 2) . "." . substr($ph_img_all->register_date, 8, 2);
                print "<tr>\r\n";
                print "	<td>" . $date_tmp . "</td>\r\n";
                print "	<td>" . $ph_img_all->registration_person . "</td>\r\n";
                print "	<td>" . $ph_img_all->photo_mno . "</td>\r\n";
                print "	<td>" . $ph_img_all->photo_name . "</td>\r\n";
                print "	<td>" . $ph_img_all->bud_photo_no . "</td>\r\n";
                print "	<td>" . $ph_img_all->photo_explanation . "</td>\r\n";
                $dfrom_tmp = substr($ph_img_all->dfrom, 2, 2) . "." . substr($ph_img_all->dfrom, 5, 2) . "." . substr($ph_img_all->dfrom, 8, 2);
                $dto_tmp = substr($ph_img_all->dto, 2, 2) . "." . substr($ph_img_all->dto, 5, 2) . "." . substr($ph_img_all->dto, 8, 2);
                print "	<td>" . $dfrom_tmp . "～" . $dto_tmp . "</td>\r\n";
                print "</tr>\r\n";
            }
            print "	</table>\r\n";
            print "</dd>\r\n";
            dispay_pagelist();
        }
    } catch (Exception $e) {
        $msg[] = $e->getMessage();
        error_exit($msg);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>削除予定の画像リスト</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css"
	media="all" />
<style type="text/css">
<!--
.ui-datepicker select.ui-datepicker-month,.ui-datepicker select.ui-datepicker-year
	{
	width: 100%;
}
-->
</style>
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<!-- 调用时间控件 -->
<script src="./js/jquery.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type='text/javascript' src='./js/dateformat/dateformat.js'
	charset="utf-8"></script>
<script language="JavaScript" src="./js/jquery.min.js"
	type="text/javascript" charset="utf-8"></script>
<link href="./css/jquery-ui.css" rel="stylesheet" type="text/css" />
<script
	src="./js/jquery-ui.min.js"></script>
<script
	src="./js/jquery.ui.datepicker-ja.js">
</script>
<script>
$(document).ready(function() {
$("#action").val("search");
$("#sort_field").val("register_date desc");
var dateFormat = new DateFormat("yyyy/MM/dd");
$("#datepicker_from").datepicker({ /*showButtonPanel: true,*/
    changeMonth: true,
    changeYear: true,
    showOn:'button',
    buttonImage:'./img/calendar.gif',
    dateFormat: 'yy/mm/dd', 
    buttonImageOnly: true,
    onClose: function(selectedDate) {
        $("#datepicker_to").datepicker("option", "minDate", selectedDate);
        var date = new Date(selectedDate);
        date.setMonth(date.getMonth() + 2);
        $("#datepicker_to").datepicker("option", "maxDate", dateFormat.format(date));
    }
});
$("#datepicker_to").datepicker({ /*showButtonPanel: true,*/
	changeMonth: true,
	changeYear: true,
	showOn:'button',
	buttonImage:'./img/calendar.gif',
	dateFormat: 'yy/mm/dd', 
	buttonImageOnly: true,
	onClose: function(selectedDate) {
		var date = new Date(selectedDate);
        date.setMonth(date.getMonth() - 2);
        $("#datepicker_from").datepicker("option", "minDate", dateFormat.format(date));
        $("#datepicker_from").datepicker("option", "maxDate", selectedDate);
    }
});
$("#send_mail").click(
	function(){
		$("#action").val("send_mail");
		$("#csv_form").submit();
	}
);

$("#search").click(
	function(){
		$("#action").val("search");
		$("#csv_form").submit();
	}
);

$("#download").click(
	function(){
		$("#action").val("download");
		$("#csv_form").submit();
	}
);

});
</script>
<!-- 结束 -->
<script type="text/javascript">
window.onload=function(){
	var obj_frame = top.document.getElementById('iframe_bottom');
	if (obj_frame)
	{
		obj_frame.style.width = 1060;
		obj_frame.width = 1060;
		obj_frame.style.height = 2000;
		obj_frame.height = 2000;
	}
}
</script>
<!-- javascript ここまで -->
</head>
<body>
	<!-- changed by wangtongchao 2011-12-06 added "?action=search" begin -->
	<form name="csv_form" id="csv_form"
		action="./photo_delete_list.php?action=search" method="post">
		<!-- changed by wangtongchao 2011-12-06 end -->
		<input type="hidden" name="action" value="search" id="action" /> <input
			type="hidden" name="sort_field" value="register_date" id="sort_field" />
		<input type="hidden" name="btn_value" value="" id="btn_value" />
		<div id="zentai" style="width: 1000px">
			<!-- メインコンテンツ　ここから -->
			<div id="contents" style="width: 1000px">
				<div class="photo_pickup" style="width: 1000px">
					<h2
						style="width: 1000px; background: url('./parts/details_bgttl.gif') scroll 0 0 transparent;">削除予定リスト（２が月）</h2>
					<div class="pickup_contents" style="width: 960px">
						<div align="right" style="width: 960px">
							<table>
								<tr>
									<td align="right"><font size="2">削除日付：</font></td>
									<td><input type="text" name="date_from"
										style="IME-MODE: disabled;" maxlength="10"
										id="datepicker_from" size="10px"
										value="<?php echo $GLOBALS['date_from'];?>" /><font size="3">
											～ </font> <input type="text" name="date_to" maxlength="10"
										id="datepicker_to" style="IME-MODE: disabled;" size="10px"
										value="<?php echo $GLOBALS['date_to'];?>" /></td>
								</tr>
							</table>
							<input type="button" name="search" id="search"
								style="width: 150px; height: 40px" value="検索する" /> <input
								type="button" name="download" id="download"
								style="width: 150px; height: 40px" value="ダウンロード" /> <input
								type="button" name="send_mail" value="メールする" id="send_mail"
								style="display: none" />
						</div>
						<dl class="album_registering" style="width: 960px">
					<?php  disp_img(); ?>
				</dl>
					</div>
				</div>
			</div>
		</div>
	</form>
</body>
</html>
