<?php
ini_set( "display_errors", "On");
require_once('./Pager.php');
require_once('./config.php');
require_once('./lib.php');

date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

$s_login_id = array_get_value($_SESSION,'login_id' ,"");
$s_login_name = array_get_value($_SESSION,'user_name' ,"");
$s_security_level = array_get_value($_SESSION,'security_level' ,"");
$comp_code = array_get_value($_SESSION,'compcode' ,"");
$s_group_id = array_get_value($_SESSION,'group' ,"");
$s_user_id = array_get_value($_SESSION,'user_id' ,"");

//define("SAVECSVPATH","./csv/");
//define("SENDMAILTO","");
//define("SENDMAILFROM","");
//define("SENDMAILMESSAGE","削除された画像リストを送付いたします。");
//define("SENDMAILTITLE","削除画像リスト");

//ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

## Connect to a local database server (or die) ##
$dbH = mysqli_connect($db_host, $db_user, $db_password) or die('Could not connect to MySQL server.<br>' . mysqli_error());

## Select the database to insert to ##
mysqli_select_db($dbH,$db_name) or die('Could not select database.<br>' . mysqli_error());

//added by wangtongchao 2011-12-06 begin
if(isset($_POST['action']) || !isset($_GET['pageID']))
{
	unset($_SESSION['date_from_deleted']);
	unset($_SESSION['date_to_deleted']);
	unset($_SESSION['search_keyword_deleted']);
}
//added by wangtongchao 2011-12-06 end
$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : "";
$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : "";
//added by wangtongchao 2011-12-05 begin
$date_from = str_replace("/","-",$date_from);
$date_to = str_replace("/","-",$date_to);
//added by wangtongchao 2011-12-05 end

//added by wangtongchao 2011-12-06 begin
if($date_from != "")
{
	$_SESSION['date_from_deleted'] = $date_from;
}elseif(isset($_SESSION['date_from_deleted'])){
	$date_from = $_SESSION['date_from_deleted'];
}

if($date_to != "")
{
	$_SESSION['date_to_deleted'] = $date_to;
}elseif(isset($_SESSION['date_to_deleted'])){
	$date_to = $_SESSION['date_to_deleted'];
}

//added by wangtongchao 2011-12-06 end

//modify by jinxin 2012/02/09
//$where = " AND nopermit_date NOT LIKE '%画像管理BUD番号%'";
$where = "";
if(!empty($date_from) && !empty($date_to)){
	$where .= " AND nopermit_date >= '".$date_from." 00:00:00'";
	$where .= " AND nopermit_date <= '".$date_to." 23:59:59'";
} elseif (empty($date_from) && !empty($date_to)) {
	$where .= " AND nopermit_date <= '".$date_to." 23:59:59'";
} elseif (!empty($date_from) && empty($date_to)) {
	$where .= " AND nopermit_date >= '".$date_from." 00:00:00'";
}

$search_keyword = isset($_POST['search_keyword']) ? $_POST['search_keyword'] : "";
//added by wangtongchao 2011-12-06 begin
if($search_keyword != "")
{
	$_SESSION['search_keyword_deleted'] = $search_keyword;
}elseif(isset($_SESSION['search_keyword_deleted'])){
	$search_keyword = $_SESSION['search_keyword_deleted'];
}
//added by wangotngchao 2011-12-06 end
$search_keyword = str_replace("　"," ",$search_keyword);
$ary_search_keyword = array();
// スペース区切りの文字列を配列にします。
$ary_search_keyword = explode(" ", $search_keyword);
$where_keyword = "";
for($i=0;$i<count($ary_search_keyword);$i++)
{
	if($i > 0) $where_keyword  .= " OR ";
	$where_keyword .= " concat( IFNULL( nopermit_personid, \"\" ), IFNULL( photo_mno, \"\" ) , IFNULL( bud_photo_no, \"\" ) , IFNULL( photo_name, \"\" ) , IFNULL( photo_explanation, \"\" ) ) LIKE '%".$ary_search_keyword[$i]."%'";
}
if(!empty($where_keyword)) $where .= " AND (".$where_keyword.")";

$orderby = "nopermit_date desc";
if(isset($_POST['action'])&&$_POST['action']=="search"){
	$orderby = isset($_POST['sort_field']) ? $_POST['sort_field'] : "";
}
//added by wangtongchao 2011-12-05 begin
if(isset($_GET['sort_field']))
{
	$orderby = $_GET['sort_field'];
}
//added by wangtongchao 2011-12-05 end
//if(isset($_POST['action'])&&$_POST['action']=="send_mail"){
//	// ＤＢへ接続します。
//	$db_link = db_connect();
//	$img_data = new ImageSearch();
//	// 写真を取得
//	$img_data->select_image_deleted($db_link);
//
//	$filename =SAVECSVPATH.date('YmdHis').".csv";
//	export_csv($img_data->images,$filename);
//	$result = sendmail(SENDMAILTO,SENDMAILTITLE,SENDMAILMESSAGE,SENDMAILFROM,"",array($filename));
//	if($result)
//	{
//		echo "<script language='javascript'>
//				alert('メールを送信しました。ご確認して下さい。');
//			  </script>";
//		if(file_exists($filename))
//		{
//			 unlink($filename);
//		}
//	}
//}

//一ページ内に表示する件数
$page_records_cnt = 20;
//一ページ内に表示するリンク数
$page_links_cnt = 20;
//一ページ内に表示するリンク数
$list_reg_cnt = 0;
//リンク
$pager_links = NULL;

// イメージ検索のクラス
$img_all = new ImageSearch();

function getcount()
{
	global $db_link,$where,$table_log_name;

	$db_link = db_connect();

	$sql = "SELECT count(*) cnt FROM photoimg";
	$sql .= " WHERE photoimg.publishing_situation_id = 3 AND photo_server_flg = 1";
	if(!empty($where)) {
		$sql .= $where;
	}

	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();

	if ($result == true)
	{
		// 最終番号を取得します。
		$max = $stmt->fetch(PDO::FETCH_ASSOC);
//		echo $max['cnt'];
		return $max['cnt'];
	} else {
		return 0;
	}
}
	function i($strInput)
	{
		return iconv('utf-8','shift-jis',$strInput);
	}

	/**
		*导出数据转换
		* @param $result
		*/
	function array_to_string($result)
	{
		if(empty($result)){
			return i("データ無し");
		}

		$data=i('不許可日 ,不許可者,写真管理番号,写真名,バドフォト番号,詳細内容,掲載期間,不許可理由');
		//modify by jinxin
		$data .="\n";

		foreach($result as $val) {
			$date = new DateTime($val->nopermit_date);
			$data .=i($date->format('Y/m/d')).",";
			$data .=i($val->nopermit_personid).",";
			$data .=i($val->photo_mno).",";
			$data .=i($val->photo_name).",";
			$data .=i($val->bud_photo_no).",";
			$data .=i($val->photo_explanation).",";
			$dfrom = new DateTime($val->dfrom);
			$dto = new DateTime($val->dto);
			$data .=i($dfrom->format('Y/m/d')).i("―").i($dto->format('Y/m/d')).",";
//			if($val->log_flag == "0")
//			{
//				$data .=i("自動");
//			}else{
//				$data .=i("手動");
//			}
			$data .=i($val->nopermission);
			$data .="\n";
		}
		return $data;
	}

/*
 * 関数名：ShowPagesList
 * 関数説明：ページングの処理と出力
 * パラメタ：無し
 * 戻り値：無し
 */
function ShowPagesList()
{
	global $page_records_cnt,$page_links_cnt,$list_reg_cnt,$pager_links,/*added by wangtongchao 2011-12-05 begin*/$orderby/*added by wangtongchao 2011-12-05 end*/;
	//global $startcnt,$lastcnt;

	$tmpcntitems = getcount();

	// Pagerのパラメータを設定します。
	$option = array(
		'mode'      => 'Jumping', 						// 表示タイプ(Jumping/Sliding)
		'perPage'   => $page_records_cnt,				// 一ページ内に表示する件数
		'delta'     => $page_links_cnt,					// 一ページ内に表示するリンク数
		'totalItems'=> $tmpcntitems,					// ページング対象データの総数
		'separator' => ' ',								// ページリンクのセパレータ文字列
		'prevImg'   => 'BACK<<',						// 戻るリンク(imgタグ使用可)
		'nextImg'   => 'NEXT>>',						// 次へリンク(imgタグ使用可)
		'importQuery'=> FALSE,							// 自動的にPOST値をページングのHTMLタグに付与しません
		'append'=> FALSE,								// 自動でページをアペンドしません。
		//changed by wangtongchao 2011-12-05 begin
		//'fileName'  => "photo_nopermis_list.php?pageID=%d&ppage=".$page_records_cnt
		'fileName'  => "photo_nopermis_list.php?pageID=%d&ppage=".$page_records_cnt."&sort_field=".$orderby
		//changed by wangtongchao 2011-12-05 end
	);

	// ページングのインスタンスを生成します。
	$pager =& Pager::factory($option);

	// 表示する行数を決定します。
	// 開始行を決定します。
	$pg = $pager->getCurrentPageID();
	if ($pg <= 0) $pg = 1;

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
	//ページングの処理---------------------------------------------------End
}

/*
 * 関数名：disp_img
 * 関数説明：画面の表示
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_img()
{
	global $list_reg_cnt,$img_all,$page_records_cnt,$where,$orderby;
	//global $startcnt,$lastcnt;

	$tmpcur_page_global = (int)array_get_value($_REQUEST,"pageID","0");
	if ($tmpcur_page_global == 0)
	{
		$tmpcur_page_global = 1;
	}

	$tmpend = $tmpcur_page_global * $page_records_cnt;

	$tmpstart = $tmpend - $page_records_cnt;

	$img_all->istart = $tmpstart;
	$img_all->per_page = $page_records_cnt;
	$img_all->iend = $tmpend;

	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();
		// 写真を取得
		$img_all->select_image_nopermit($db_link,$where,$orderby);
		if(empty($img_all->images))
		{
			print "<dt class='form_ttl'>不許可画像一覧<span>（0件）</span></dt>\r\n";
			print "<dd class=\"form_contents_indent\" style=\"width:1160px\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"photo_album\" style=\"width:1160px\">\r\n";
			print "	<tr>\r\n";
			$btn_val_day = "不許可日";
			$btn_val_account = "不許可者";
			$btn_val_mno = "写真管理番号";
			$btn_val_photo_name = "写真名";
			$btn_val_budno = "バドフォト番号";
			$btn_val_explanation = "詳細内容";
			print "		<th class=\"day\">".$btn_val_day."</th>\r\n";
			print "		<th style=\"width:10%\">".$btn_val_account."</th>\r\n";
			print "		<th style=\"width:12%\">".$btn_val_mno."</th>\r\n";
			print "		<th style=\"width:20%\">".$btn_val_photo_name."</th>\r\n";
			print "		<th style=\"width:12%\">".$btn_val_budno."</th>\r\n";
			print "		<th>".$btn_val_explanation."</th>\r\n";
			print "		<th style=\"width:9%\">掲載期間</th>\r\n";
			print "		<th style=\"width:12%\">不許可理由</th>\r\n";
			print "	</tr>\r\n";
			print "	</table>\r\n";
			print "</dd>\r\n";

			return;
		}
		// イメージ総数を取得する
		if (!empty($img_all->images))
		{
			$img_ary = $img_all->images;

			ShowPagesList();
			if((int)$tmpend > (int)$list_reg_cnt)
			{
				$img_all->iend = $list_reg_cnt;
			}
			print "<dt class='form_ttl'>不許可画像一覧<span>（".$img_all->iend."件/".$list_reg_cnt."件中）</span></dt>\r\n";
			dispay_pagelist();
			print "<dd class=\"form_contents_indent\" style=\"width:1160px\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"photo_album\" style=\"width:1160px\">\r\n";
			print "	<tr>\r\n";
			$btn_val_day = "不許可日";
			$btn_val_account = "不許可者";
			$btn_val_mno = "写真管理番号";
			$btn_val_photo_name = "写真名";
			$btn_val_budno = "バドフォト番号";
			$btn_val_explanation = "詳細内容";
			if($orderby == "nopermit_date desc"){
				$btn_val_day = "不許可日↓";
			}elseif($orderby == "nopermit_date asc"){
				$btn_val_day = "不許可日↑";
			}
			print "		<th style=\"width:60px\"><input type=\"button\" name=\"sort_day\" onmouseout=\"this.className='btn';\" onmouseover=\"this.className='btnover';\" class=\"btn\" value=\"".$btn_val_day."\" id=\"sort_day\" style=\"width:100%;height:100%\" /></th>\r\n";

			if($orderby == "nopermit_personid desc"){
				$btn_val_account .= "↓";
			}elseif($orderby == "nopermit_personid asc"){
				$btn_val_account .= "↑";
			}
			print "		<th style=\"width:90px\"><input type=\"button\" name=\"sort_account\" onmouseout=\"this.className='btn';\" onmouseover=\"this.className='btnover';\" class=\"btn\" value=\"".$btn_val_account."\" id=\"sort_account\" style=\"width:100%;height:100%\" /></th>\r\n";

			if($orderby == "photo_mno desc"){
				$btn_val_mno .= "↓";
			}elseif($orderby == "photo_mno asc"){
				$btn_val_mno .= "↑";
			}
			print "		<th style=\"width:120px\"><input type=\"button\" name=\"sort_mno\" onmouseout=\"this.className='btn';\" onmouseover=\"this.className='btnover';\" class=\"btn\" value=\"".$btn_val_mno."\" id=\"sort_mno\" style=\"width:100%;height:100%\" /></th>\r\n";

			if($orderby == "photo_name desc"){
				$btn_val_photo_name .= "↓";
			}elseif($orderby == "photo_name asc"){
				$btn_val_photo_name .= "↑";
			}
			print "		<th style=\"width:180px\"><input type=\"button\" name=\"sort_p_name\" onmouseout=\"this.className='btn';\" onmouseover=\"this.className='btnover';\" class=\"btn\" value=\"".$btn_val_photo_name."\" id=\"sort_p_name\" style=\"width:100%;height:100%\" /></th>\r\n";

			if($orderby == "bud_photo_no desc")
			{
				$btn_val_budno .= "↓";
			}elseif($orderby == "bud_photo_no asc"){
				$btn_val_budno .= "↑";
			}
			print "		<th style=\"width:120px\"><input type=\"button\" name=\"sort_budno\" onmouseout=\"this.className='btn';\" onmouseover=\"this.className='btnover';\" class=\"btn\" value=\"".$btn_val_budno."\" id=\"sort_budno\" style=\"width:100%;height:100%\" /></th>\r\n";

			if($orderby == "photo_explanation desc")
			{
				$btn_val_explanation .= "↓";
			}elseif($orderby == "photo_explanation asc"){
				$btn_val_explanation .= "↑";
			}
			print "		<th style=\"width:210px\"><input type=\"button\" name=\"sort_explanation\" onmouseout=\"this.className='btn';\" onmouseover=\"this.className='btnover';\" class=\"btn\" value=\"".$btn_val_explanation."\" id=\"sort_explanation\" style=\"width:100%;height:100%\" /></th>\r\n";

			print "		<th style=\"width:100px\">掲載期間</th>\r\n";
			print "		<th style=\"width:60px\">不許可理由</th>\r\n";
			print "	</tr>\r\n";

			$ph_img_all = new PhotoImageNopermit();
			for ($i = 0 ; $i < count($img_ary); $i++)
			{
				$ph_img_all = $img_ary[$i];
				if(!empty($ph_img_all->nopermit_date)){
					$date_tmp = substr($ph_img_all->nopermit_date,2,2).".".substr($ph_img_all->nopermit_date,5,2).".".substr($ph_img_all->nopermit_date,8,2);
				}else{
					$date_tmp = "";
				}
				print "<tr>\r\n";
				print "	<td>".$date_tmp."</td>\r\n";
				print "	<td>".$ph_img_all->nopermit_personid."</td>\r\n";
				print "	<td>".$ph_img_all->photo_mno."</td>\r\n";
				print "	<td>".$ph_img_all->photo_name."</td>\r\n";
				print "	<td>".$ph_img_all->bud_photo_no."</td>\r\n";
				print "	<td>".$ph_img_all->photo_explanation."</td>\r\n";
				$dfrom_tmp = substr($ph_img_all->dfrom,2,2).".".substr($ph_img_all->dfrom,5,2).".".substr($ph_img_all->dfrom,8,2);
				$dto_tmp = substr($ph_img_all->dto,2,2).".".substr($ph_img_all->dto,5,2).".".substr($ph_img_all->dto,8,2);
				if((!empty($ph_img_all->dfrom)) || (!empty($ph_img_all->dto)))
				{
					print "	<td>".$dfrom_tmp."～".$dto_tmp."</td>\r\n";
				} else {
					print "	<td>  </td>\r\n";
				}
//				if($ph_img_all->log_flag == "0")
//				{
//					print "	<td>　自動</td>\r\n";
//				}else{
//					print "	<td>　手動</td>\r\n";
//				}
				print "<td>".$ph_img_all->nopermission."</td>\r\n";
				print "</tr>\r\n";
			}
			print "	</table>\r\n";
			print "</dd>\r\n";
			dispay_pagelist();
		}
	}catch(Exception $e){
		$msg[] = $e->getMessage();
		error_exit($msg);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>不許可画像リスト</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<style type="text/css">
  <!--
  	.ui-datepicker select.ui-datepicker-month, .ui-datepicker select.ui-datepicker-year
	{
		width:100%;
	}
  -->
</style>
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<!-- 调用时间控件 -->
<script src="./js/jquery.js"  type="text/javascript"  charset="utf-8"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script language="JavaScript" src="./js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<link href="./css/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<script src="http://jquery-ui.googlecode.com/svn/trunk/ui/i18n/jquery.ui.datepicker-ja.js">
</script>
<script>
$(document).ready(function() {
$("#action").val("search");
$("#sort_field").val("nopermit_date desc");
$("#datepicker_from").datepicker( { /*showButtonPanel: true,*/
								changeMonth: true,
								changeYear: true,
								showOn:'button',
								buttonImage:'./img/calendar.gif',
								dateFormat: 'yy/mm/dd',
								buttonImageOnly: true

							}  );
$("#datepicker_to").datepicker( { /*showButtonPanel: true,*/
								changeMonth: true,
								changeYear: true,
								showOn:'button',
								buttonImage:'./img/calendar.gif',
								dateFormat: 'yy/mm/dd',
								buttonImageOnly: true

							}  );
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

$("#sort_day").click(
	function(){
		var ipos = $("#sort_day").attr("value").indexOf("↑");
		if(ipos > 0)
		{
			$("#btn_value").val("不許可日↓");
			$("#sort_field").val("nopermit_date desc");
		} else {
			$("#btn_value").val("不許可日↑");
			$("#sort_field").val("nopermit_date asc");
		}
		$("#action").val("search");
		$("#csv_form").submit();
	}
);

$("#sort_account").click(
	function(){
		var ipos = $("#sort_account").attr("value").indexOf("↑");
		if(ipos > 0)
		{
			$("#btn_value").val("不許可者↓");
			$("#sort_field").val("nopermit_personid desc");
		} else {
			$("#btn_value").val("不許可者↑");
			$("#sort_field").val("nopermit_personid asc");
		}
		$("#action").val("search");
		$("#csv_form").submit();
	}
);

$("#sort_mno").click(
	function(){
		var ipos = $("#sort_mno").attr("value").indexOf("↑");
		if(ipos > 0)
		{
			$("#btn_value").val("写真管理番号↓");
			$("#sort_field").val("photo_mno desc");
		} else {
			$("#btn_value").val("写真管理番号↑");
			$("#sort_field").val("photo_mno asc");
		}
		$("#action").val("search");
		$("#csv_form").submit();
	}
);

$("#sort_p_name").click(
	function(){
		var ipos = $("#sort_p_name").attr("value").indexOf("↑");
		if(ipos > 0)
		{
			$("#btn_value").val("写真名↓");
			$("#sort_field").val("photo_name desc");
		} else {
			$("#btn_value").val("写真名↑");
			$("#sort_field").val("photo_name asc");
		}
		$("#action").val("search");
		$("#csv_form").submit();
	}
);

$("#sort_budno").click(
	function(){
		var ipos = $("#sort_budno").attr("value").indexOf("↑");
		if(ipos > 0)
		{
			$("#btn_value").val("バドフォト番号↓");
			$("#sort_field").val("bud_photo_no desc");
		} else {
			$("#btn_value").val("バドフォト番号↑");
			$("#sort_field").val("bud_photo_no asc");
		}
		$("#action").val("search");
		$("#csv_form").submit();
	}
);

$("#sort_explanation").click(
	function(){
		var ipos = $("#sort_explanation").attr("value").indexOf("↑");
		if(ipos > 0)
		{
			$("#btn_value").val("詳細内容↓");
			$("#sort_field").val("photo_explanation desc");
		} else {
			$("#btn_value").val("詳細内容↑");
			$("#sort_field").val("photo_explanation asc");
		}
		$("#action").val("search");
		$("#csv_form").submit();
	}
);

});
</script>
<!-- 结束 -->
<!-- javascript ここまで -->
<style type="text/css">
.btn{
	width:100%;
	height:30px;
	border:1px #224272 solid;
}
.btnover{
	width:100%;
	height:30px;
	border:1px #224272 solid;
	background-color:#CCCCCC;
	cursor:pointer;
}
</style>

<script type="text/javascript">
window.onload=function(){
	var obj_frame = top.document.getElementById('iframe_bottom');
	if (obj_frame)
	{
		obj_frame.style.width = 1260;
		obj_frame.width = 1260;
		obj_frame.style.height = 2000;
		obj_frame.height = 2000;
	}
}
</script>
</head>
<body>
<!-- changed by wangtongchao 2011-12-06 added "?action=search" begin -->
<form name="csv_form" id="csv_form" action="./photo_nopermis_list.php?action=search" method="post">
<!-- changed by wangtongchao 2011-12-06 end -->
<input type="hidden" name="action" value="search" id="action"/>
<input type="hidden" name="sort_field" value="nopermit_date" id="sort_field"/>
<input type="hidden" name="btn_value" value="" id="btn_value"/>
<div id="zentai" style="width:1200px">
	<!-- メインコンテンツ　ここから -->
	<div id="contents" style="width:1200px">
		<div class="photo_pickup" style="width:1200px">
			<h2 style="width:1200px;background: url('./parts/details_bgttl.gif') scroll 0 0 transparent;">不許可画像リスト</h2>
			<div class="pickup_contents" style="width:1160px">
				<div align="right"  style="width:1160px">
					<table>
						<tr>
							<td align="right"><font size="2">不許可日付：</font></td>
							<td style="width:260px;text-align:left;">
								<input type="text"  name="date_from" style="IME-MODE: disabled;width:32%" maxlength="10" id ="datepicker_from" size="10px" value="<?php echo $GLOBALS['date_from'];?>"/><font size="3">　～　</font>
								<input type="text"  name="date_to" maxlength="10" id ="datepicker_to" style="IME-MODE: disabled;width:33%" size="10px"  value="<?php echo $GLOBALS['date_to'];?>"/>
							</td>
						</tr>
						<tr>
							<td><font size="2">キーワード：</font></td>
							<td style="width:240px;text-align:left;">
								<input type="text" name="search_keyword" id="search_keyword" style="width:100%" value="<?php echo $GLOBALS['search_keyword'];?>" />
							</td>
						</tr>
					</table>
					<input type="button" name="search" id="search" style="width:150px;height:40px" value="検索する"/>
					<input type="button" name="send_mail" value="メールする" id="send_mail" style="display:none"/>
				</div>
				<dl class="album_registering" style="width:1160px">
					<?php  disp_img(); ?>
				</dl>
			</div>
		</div>
	</div>
</div>
</form>
</body>
</html>
