<?php
require_once('./config.php');
require_once('./lib.php');

// タイムゾーンを設定します。
date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

// セッション（$_SESSION）より情報を取得します。
$s_login_id = array_get_value($_SESSION, 's_login_id');													// ログインID
$s_login_name = array_get_value($_SESSION, 's_login_name');												// ログイン名
$s_security_level = array_get_value($_SESSION, 's_security_level');										// セキュリティーレベル
$s_user_id = array_get_value($_SESSION, 's_user_id');													// ユーザーID

// for Debug
$s_user_id = 1;
$s_login_name = "中尾　友一";
$s_login_id = "nakao";

//一ページ内に表示する件数
$page_images_cnt = 30;
//一行表示の最大イメージ数
$disp_max = 10;
//DBリンク
$db_link = NULL;
//ImageSearchクラスの対象
$is = NULL;
//イメージの総数
$img_count = 0;

// ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	//header('Location: ' . $logout_page);
	header_out($logout_page);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ピックアップ一覧</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<link rel="stylesheet" href="./js/jquery.localscroll-1.2.6/jScrollToMin.css" type="text/css" media="all"/>
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="./js/jquery.localscroll-1.2.6/jquery-1.2.6.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/jquery.localscroll-1.2.6/jquery.scrollTo-min.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/kirikae.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/select.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/common.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/slide.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
<?php
print "var max = " .$GLOBALS["disp_max"]. ";\r\n";
//if (!empty($GLOBALS["s_user_id"]))
//{
//	print "var uid = ".$GLOBALS["s_user_id"].";";
//} else {
//	print "var uid = '';";
//}
?>
/*
 * 関数名：move2
 * 関数説明：画面の「＜」と「＞」ボタンを押す処理
 * パラメタ：
 * adj:「＜」ボタンをクリックする時、「-1」を設定し；「＞」ボタンをクリックする時、「１」を設定する
 * 戻り値：無し
 */
var cnt=1;
function move2(adj)
{
	cnt += adj;
	if (cnt < 1)
	{
		cnt = 1;
		return;
	}

	var page_total = 0;

	// クッキー識別子を作成します。
	var ck_id = "mita_images";
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.split(",");
	var res = id_a.length/max;
	if (id_a.length % max == 0)
	{
		page_total = res;
	} else {
		if (res < 1)
		{
			page_total = 1;
		} else {
			page_total = parseInt(res) + 1;
		}
	}

	var page_number_key = "page_number";
	var tags = document.getElementById(page_number_key);
	if (tags)
	{
		var s_html = "<dt>ページ数</dt>\r\n";
		s_html = s_html + "<dd>" + cnt + "/" + page_total + "</dd>\r\n"
		tags.innerHTML = s_html;
	}
	moveNext(page_total);
}

/*
 * 関数名：moveNext
 * 関数説明：画面の「＜」と「＞」ボタンを押す処理
 * パラメタ：
 * pages_cnt:１０個ずつ一行に表示し、行総数を設定する
 * 戻り値：無し
 */
function moveNext(pages_cnt)
{
	var div_key = "";

	for (var i = 1; i < cnt; i++)
	{
		div_key = "div_img_" + i;
		target=document.getElementById(div_key);
		if (target) target.style.display = "none";
	}

	div_key = "div_img_" + cnt;
	target=document.getElementById(div_key);
	if (target) target.style.display = "block";

	for (var i = cnt + 1; i <= pages_cnt; i++)
	{
		div_key = "div_img_" + i;
		target=document.getElementById(div_key);
		if (target) target.style.display = "none";
	}

	if (cnt == pages_cnt)
	{
		var obj_next = document.getElementById("photo_sc_contents_next");
		if (obj_next)
		{
			obj_next.style.display = "none";
		}
	} else {
		var obj_next = document.getElementById("photo_sc_contents_next");
		if (obj_next) obj_next.style.display = "block";
	}
}

/*
 * 関数名：pickup_change_on
 * 関数説明：画面の「ピックアップ」ボタンと「最近見た画像」ボタンの切り替え
 * パラメタ：
 * di:フラグ設定　「ピックアップ」ボタンを押すと、「０」を設定する；「最近見た画像」ボタンを押すと、「１」を設定する
 * 戻り値：無し
 */
function pickup_change_on(di)
{
	// 「最近見た画像」ボタンを押す
	if (di == 1)
	{
		var url = "./pickup_ichiran2.php";
		parent.middle2.location.href = url;
	// 「ピックアップ」ボタンを押す
	} else {
		var url = "./pickup_ichiran1.php?ShowFlag=0";
		parent.middle2.location.href = url;
	}
}

/*
 * 関数名：disp_ImageInformation
 * 関数説明：イメージの詳細情報画面へ遷移する
 * パラメタ：
 * id:イメージID
 * 戻り値：無し
 */
function disp_ImageInformation(id)
{
	var url = "./image_detail.php?p_photo_id=" + id + "&gamen_flg=3";
	parent.bottom.location.href = url;
}

/*
 * 関数名：clear_listimage
 * 関数説明：見た画像をクリアします
 * パラメタ：無し
 * 戻り値：無し
 */
function clear_listimage()
{
	// クッキー識別子を作成します。
	var ck_id = "mita_images";
	var idstr = "";
	// クッキーを設定します。
	setCookie(ck_id, idstr);

	//var url = "./pickup_ichiran2.php?ShowFlag=1";
	var url = "./pickup_ichiran2.php";
	parent.middle2.location.href = url;
}

/*
 * 関数名：delete_listimage
 * 関数説明：画像を見た画像から削除します
 * パラメタ：
 * id:イメージID；
 * 戻り値：無し
 */
function delete_listimage(id)
{
	var frame_width = top.document.getElementById('iframe_middle2').height;
	// クッキー識別子を作成します。
	var ck_id = "mita_images";
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.split(",");
	// 既にクッキーで設定されているものについては、除外します。
	var idx = check_array(id_a, id);
	if (idx == -1)	return false;
	id_a[idx] = "";
	// 配列を文字列に変換します。
	idstr = array_to_str(id_a);
	// クッキーを設定します。
	setCookie(ck_id, idstr);

	//var url = "./pickup_ichiran2.php?p_pickupid=" + idstr + "&ShowFlag=1";
	var url = "./pickup_ichiran2.php?p_pickupid=" + idstr;
	parent.middle2.location.href = url;
}

/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
	set_frameheight('iframe_middle2',380);
}

window.onload = function()
{
	init();
}
</script>
<!-- javascript ここまで -->
</head>
<body>
<div id="zentai">
<div id="contents">
<div id="slide_s">
<!-- ↓Photo Scroll -->
<div id="photo_sc" class = "photo_sc" style = "">
	<ul>
		<li id="photo_bt1_off" class="photo_bt1_off" onclick="return pickup_change_on(0);" ><a href="#" >ピックアップ</a></li>
		<li id="photo_bt2" class="photo_bt2" onclick="return pickup_change_on(1);"><a href="#">最近見た画像</a></li>
	</ul>
</div>
<?php disp_slider();?>
</div>
</div>
</div>
</body>
</html>
<?php
/*
 * 関数名：getPageCounts
 * 関数説明：ページ総数を取得する
 * パラメタ：無し
 * 戻り値：無し
 */
function getPageCounts(){
	global $img_count, $disp_max;

	$src_res = $img_count/$disp_max;

	$var = (int)substr($src_res,0,strpos($src_res,"."));

	if ($var == 0 || $var == "0")
	{
		if ($src_res < 1) $src_res = 1;
		return $src_res;
	} else {
		return $var + 1;
	}
}

/*
 * 関数名：showPageNumber
 * 関数説明：1ページに１０個ずつ移動し、画面の下部分のページ数を変わる
 * パラメタ：
 * $page_index：今のページのインデックス
 * 戻り値：無し
 */
function showPageNumber($page_index)
{
	global $img_count, $disp_max;
	//ページ総数の取得
	$pageCounts = getPageCounts();
	print "<dl id=\"page_number\">\r\n";
	print "<dt>ページ数</dt>\r\n";
	print "<dd>".dp($page_index."/".$pageCounts)."</dd>\r\n";
	print "</dl>\r\n";
}

/*
 * 関数名：disp_slider
 * 関数説明：画面の表示処理
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_slider()
{
	//global $showFlag,$s_user_id,$db_link,$is,$img_count;
	global $s_user_id,$db_link,$is,$img_count;

	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();

		// 画像検索用のインスタンスを生成します。
		$is = new ImageSearch();

		$p_list_images_id = array_get_value($_COOKIE,"mita_images");
		// 画像を検索します。
		$is->set_photo_id_str($p_list_images_id);
		$is->select_image_fmid($db_link);

		// イメージ総数を取得する
		$img_count = count($is->images);
	}
	catch(Exception $e)
	{
		// 異常の出力
		$msg[] = $e->getMessage();
		error_exit($msg);
	}

	disp_slider10();
}

/*
 * 関数名：disp_image_zero
 * 関数説明：イメージが無い場合の画面の表示処理
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_image_zero()
{
	print "<div id=\"photo_sc_contents\" class = \"photo_sc_contents\" style = \"\">\r\n";
	print "</div>\r\n";

	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn2\" style=\"display:block\">\r\n";
	print "	<ul class=\"btn\">\r\n";
	print "		<li class=\"clear\"><a href=\"#\"><img src=\"parts/bt_clear.gif\" alt=\"クリア\" /></a></li>\r\n";
	print "	</ul>\r\n";
	print "</div>\r\n";
}

/*
 * 関数名：disp_slider10
 * 関数説明：1ページに１０個ずつを表示する画面の出力処理
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_slider10()
{
	global $img_count, $disp_max, $is, $s_user_id, $hide_flg;

	print "<script type=\"text/javascript\">";
	print "setCookie(\"image_cnt\",0);";
	print "</script>";

	// ピックアップイメージがない場合
	if ($img_count == 0)
	{
		disp_image_zero();
		return;
	} else {
		print "<script type=\"text/javascript\">";
		print "setCookie(\"image_cnt\",".$img_count.");";
		print "</script>";

		print "<div id='photo_sc_contents' class = 'photo_sc_contents' style = ''>\r\n";
		$pageCount = getPageCounts();
		// javascript ページ総数をクッキーに設定---Start
		print "<script type=\"text/javascript\">\r\n";
		print "var grp_key = \"page_count\";\r\n";
		print "setCookie(grp_key,".$pageCount.");\r\n";
		print "</script>\r\n";
		// javascript ページ総数をクッキーに設定---End

		// 行目数の繰り返し
		for ($page_index = 1; $page_index <= $pageCount; $page_index++) {

			// 開始イメージのインデックスの取得
			$s_link_index = ($page_index - 1) * $disp_max;
			// 終了イメージのインデックスの取得
			$e_link_index = $s_link_index + $disp_max;
			if ($e_link_index > $img_count)
			{
				$e_link_index = $img_count;
			}

			// 一行目のイメージを表示する
			if ($page_index == 1)
			{
				print "<div id='div_img_".$page_index."' style=\"display:block\">\r\n";
				print "<ul>\r\n";
			} else {// 初期化時は一行目以外のイメージを表示しない。
//				// 一行目に１０個画像を表示し、1行目以上の場合、「＞」ボタンを出力する
//				if ($pageCount > 1)
//				{
//					print "<p id='photo_sc_contents_next'><a href='#' onclick='move2(1)'>NEXT&gt;&gt;</a></p>\r\n";
//				}
				print "<div id='div_img_".$page_index."' style=\"display:none\">\r\n";
				print "<p id='photo_sc_contents_back'><a href='#' onclick='move2(-1)'>&lt;&lt;BACK</a></p>\r\n";
				print "<ul>\r\n";
			}

			// 一行目中のイメージ数の繰り返し
			for ($i = $s_link_index; $i < $e_link_index; $i++) {
				$ph_img_all = $is->images[$i];

				print "<li>\r\n";
				print "<dl class='photo'>\r\n";
				if (!empty($ph_img_all->photo_explanation))
				{
					if (strlen($ph_img_all->photo_explanation) > 18)
					{
						print "<dt>".dp(santen_reader($ph_img_all->photo_explanation,16))."</dt>\r\n";
					} else {
						print "<dt>".dp($ph_img_all->photo_explanation)."</dt>\r\n";
					}
				} else {
					print "<dt>&nbsp&nbsp&nbsp</dt>\r\n";
				}
				print "<dd class='samneil'><a href='#'><img width='60px' height='45px' src='".$ph_img_all->up_url[1]."' alt='イメージ'  onclick='disp_ImageInformation(\"".$ph_img_all->photo_id."\");'/></a></dd>\r\n";
				print "<dd class='bt_delete'><a href='#'><img src='parts/bt_delete.gif' alt='削除' onclick='delete_listimage(\"".$ph_img_all->photo_id."\");'/></a></dd>\r\n";
				print "</dl>\r\n";// <dl 'photo'>終了
				print "</li>\r\n";
			}
			print "</ul>\r\n";// <div 'div_img_'>前の<ul>終了
			print "</div>\r\n";// <div 'div_img_'>終了
		}

		// 一行目に１０個画像を表示し、1行目以上の場合、「＞」ボタンを出力する
		if ($pageCount > 1)
		{
			print "<p id='photo_sc_contents_next'><a href='#' onclick='move2(1)'>NEXT&gt;&gt;</a></p>\r\n";
		}

		// 初期化時は1ページを表示する
		showPageNumber(1);
		print "</div>\r\n";// <div 'photo_sc_contents'>終了

		print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn2\" style=\"display:block\">\r\n";
		print "	<ul class=\"btn\">\r\n";
		print "<li class='clear'><a href='#'><img src='parts/bt_clear.gif' alt='クリア' onclick='clear_listimage();'/></a></li>\r\n";
		print "	</ul>\r\n";
		print "</div>\r\n";// <div 'photo_sc_btn2'>終了
	}
}
?>