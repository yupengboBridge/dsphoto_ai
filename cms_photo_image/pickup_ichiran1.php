<?php
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

//// for Debug
//$s_user_id = 1;
//$s_login_name = "BUD管理者";
//$s_login_id = "admin";

//ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

// 開始行
$startcnt = 0;
// 終了行
$lastcnt = 0;
//一ページ内に表示する件数
$page_images_cnt = 30;
//一ページ内に表示するリンク数
$page_links_cnt = 30;
//ページリンク
$pager_links = NULL;
//ページ番号
$cur_page = 0;
//一行表示の最大イメージ数
$disp_max = 10;
//ピックID
$p_pickupid = "";
//DBリンク
$db_link = NULL;
//ImageSearchクラスの対象
$is = NULL;
//イメージの総数
$img_count = 0;

// 画面の初期表示フラグ
$init_flg = array_get_value($_REQUEST, 'init',"");
// 一ページにイメージの枚数の表示フラグ
// 「０」：一ページに１０枚イメージを表示する
// 「１」：一ページに３０枚イメージを表示する
$showFlag = array_get_value($_REQUEST, 'ShowFlag',"");
// ピックアップフレーム表示するかどうかフラグ
$hide_flg = array_get_value($_REQUEST, 'hide_all',"");
// 「最近見た画像」ボタンを押すと、「クリア」ボタンを表示するフラグ
$flg3 = array_get_value($_REQUEST, 'flg3',"");
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
<script type="text/javascript">
<?php
print "var max = " .$GLOBALS["disp_max"]. ";\r\n";
if (!empty($GLOBALS["s_user_id"]))
{
	print "var uid = ".$GLOBALS["s_user_id"].";\r\n";
} else {
	print "var uid = '';";
}
?>
var cnt=1;

/*
 * 関数名：gotopage
 * 関数説明：指定のページを定位する
 * パラメタ：p_cnt:ページ数
 * 戻り値：無し
 */
 function gotopage(p_cnt)
 {
 	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + uid;
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.explode(",");
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
		s_html = s_html + "<dd>" + p_cnt + "/" + page_total + "</dd>\r\n"
		tags.innerHTML = s_html;
	}

	var div_key = "";

	for (var i = 1; i < p_cnt; i++)
	{
		div_key = "div_img_" + i;
		target=document.getElementById(div_key);
		if (target) target.style.display = "none";
	}

	div_key = "div_img_" + p_cnt;
	target=document.getElementById(div_key);
	if (target) target.style.display = "block";

	for (var i = p_cnt + 1; i <= page_total; i++)
	{
		div_key = "div_img_" + i;
		target=document.getElementById(div_key);
		if (target) target.style.display = "none";
	}

	if (p_cnt == page_total)
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
	cnt = p_cnt;
 }


/*
 * 関数名：move2
 * 関数説明：画面の「＜」と「＞」ボタンを押す処理
 * パラメタ：
 * adj:「＜」ボタンをクリックする時、「-1」を設定し；「＞」ボタンをクリックする時、「１」を設定する
 * 戻り値：無し
 */
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
	var ck_id = "pickup_images_id_" + uid;
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.explode(",");
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
 * 関数名：change_on
 * 関数説明：画面の「もっと見る」と「閉じる」ボタンの処理
 * パラメタ：無し
 * 戻り値：無し
 */
function change_on()
{
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + uid;
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	var bt_cnt=0;

	// 「もっと見る」ボタンのクリークの回数
	bt_cnt = getCookie("bt_cnt");
	bt_cnt++;

	// 「もっと見る」ボタンのクリーク回数が２回の場合
	if(bt_cnt==2)
	{
		//-----------一ページに１０枚イメージを表示する(開始)---------------
		bt_cnt=0;				// クリーク回数がゼロに設定する
		setCookie("bt_cnt",0);	// クリーク回数をセショーンに設定する
		top.document.getElementById('iframe_middle2').style.height = 190;
		document.image002.src="parts/bt_all0.gif";
		//var styl = document.getElementById("photo_sc_btn3").style.display;
		// 「最近見た画像」ボタンを押った場合
		//if (styl != "none")
		//{
		//	var url = "./pickup_ichiran1.php?ShowFlag=0&flg3=1";
		//} else {
			var url = "./pickup_ichiran1.php?ShowFlag=0";
		//}
		parent.middle2.location.href = url;
		//-----------一ページに１０枚イメージを表示する(終了)---------------
	}
	else
	{
		//-----------一ページに３０枚イメージを表示する(開始)---------------
		setCookie("bt_cnt",bt_cnt);// クリーク回数をセショーンに設定する
		var id_a = new Array();
		// カンマ区切りの文字列を配列にします。
		id_a = idstr.explode(",");
		if (id_a.length > 10)
		{
			set_frameheight('iframe_middle2',380);
			document.image002.src="parts/bt_all1.gif";
			//var styl = document.getElementById("photo_sc_btn3").style.display;
			// 「最近見た画像」ボタンを押った場合
			//if (styl != "none")
			//{
			//	var url = "./pickup_ichiran1.php?ShowFlag=1&flg3=1";
			//} else {
				var url = "./pickup_ichiran1.php?ShowFlag=1";
			//}
			parent.middle2.location.href = url;
		}
		//-----------一ページに３０枚イメージを表示する(終了)---------------
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
	// 「最近見た画像」ボタンを押った場合
	if (di == 1)
	{
		// 本画面の三つボタンを隠れる
		var obj = document.getElementById("photo_sc_contents");
		if (obj) obj.style.display = "none";
		document.getElementById("photo_sc_btn").style.display = "none";
		// 最近見た画像の画面へ遷移する
		//var url = "./pickup_ichiran2.php?ShowFlag=1";
		var url = "./pickup_ichiran2.php";
		parent.middle2.location.href = url;
	// 「ピックアップ」ボタンを押った場合
	} else {
		// 本画面の三つボタンを表示する
		var obj = document.getElementById("photo_sc_contents");
		if (obj) obj.style.display = "";
		document.getElementById("photo_sc_btn").style.display = "";
		// ピックアップ画像の画面へ遷移する
		var url = "./pickup_ichiran1.php?ShowFlag=1";
		parent.middle2.location.href = url;
	}
}

/*
 * 関数名：location_arubamu_insert
 * 関数説明：アルバムの新規画面へ遷移する
 * パラメタ：無し
 * 戻り値：無し
 */
function location_arubamu_insert()
{
	var img_cnt = getCookie("image_cnt");
	// アルバムにイメージを存在した場合
	if (img_cnt > 0)
	{
		document.getElementById("photo_sc_btn").style.display = "none";
		//document.getElementById("photo_sc_btn3").style.display = "";
		// アルバムの新規画面へ遷移する
		var url = "./pickup_arubamu_insert.php";
		parent.bottom.location.href = url;
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
	var url = "./image_detail.php?p_photo_id=" + id + "&gamen_flg=1";

	setCookie("submit_url",parent.middle2.location.href);
	var tmpurl = parent.bottom.location.href;
	if (tmpurl.indexOf("search_result.php") > 0 || tmpurl == null || tmpurl.length <= 0)
	{
		setCookie("bottom_url",parent.bottom.location.href);
	}

	setCookie("pickup_cur_page",cnt);
	parent.bottom.location.href = url;
}

///*
// * 関数名：clear_pickup
// * 関数説明：ピックアップ対象をクリアします
// * パラメタ：
// * userid:ユーザーID
// * 戻り値：無し
// */
//function clear_pickup3(userid)
//{
//	// クッキー識別子を作成します。
//	var ck_id = "pickup_images_id_" + userid;
//	var ck_id_all = "pickup_chk";
//
//	var idstr = "";
//
//	// クッキーを設定します。
//	setCookie(ck_id, idstr);
//	setCookie(ck_id_all, idstr);
//
//	var url = "./pickup_ichiran1.php?p_pickupid=" + idstr + "&flg3=1";
//	parent.middle2.location.href = url;
//}

/*
 * 関数名：hide_image
 * 関数説明：ピックアップ画面の「削除」ボタンを押すと、イメージを表示しない
 * パラメタ：id：イメージID
 * 戻り値：無し
 */
function hide_image(id)
{
	var keyid = "dl"+id;
	var obj = document.getElementById(keyid);
	obj.style.display = 'none';
}

/*
 * 関数名：Request
 * 関数説明：Requestの文字列を取得する
 * パラメタ：
 * name:Requestのキー
 * 戻り値：無し
 */
function Request(name){
	new RegExp("(^|&)"+name+"=([^&]*)").exec(window.location.search.substr(1));
	return RegExp.$2;
}

/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
	var showflg = Request("ShowFlag");
	if (showflg == 1)
	{
		set_frameheight('iframe_middle2',380);
	} else {
		//top.document.getElementById('iframe_middle2').style.height = 120;
		set_frameheight('iframe_middle2',180);
		var tmpcnt = getCookie("pickup_cur_page");
		if (tmpcnt == null || tmpcnt == "" || tmpcnt.length <=0)
		{
			//処理しない
		} else {
			var tmpicnt = parseInt(tmpcnt);
			gotopage(tmpicnt);
			clearCookie("pickup_cur_page");
		}
	}
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
		<li id="photo_bt1" class="photo_bt1"><a href="#" onclick="return false;" title="ピックアップ">ピックアップ</a></li>
		<!-- <li id="photo_bt1" class="photo_bt1"  onclick="return pickup_change_on(0);" ><a href="#" >ピックアップ</a></li>-->
		<!-- <li id="photo_bt2_off" class="photo_bt2_off" onclick="return pickup_change_on(1);"><a href="#">最近見た画像</a></li> -->
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
 * 関数説明：ページ総数の取得
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
 * 関数名：ShowPagesList
 * 関数説明：ページングの処理と出力
 * パラメタ：
 * $cntItems：イメージの総数
 * 戻り値：無し
 */
function ShowPagesList($cntItems)
{
	global $pager_links,$startcnt,$lastcnt;

	$url_submit = "pickup_ichiran1.php?pageID=%d&ppage=".$GLOBALS["page_images_cnt"]."&ShowFlag=1";
	//ページングの処理---------------------------------------------------Start
	// Pagerのパラメータを設定します。
	$option = array(
		'mode'      => 'Jumping', 						// 表示タイプ(Jumping/Sliding)
		'perPage'   => $GLOBALS["page_images_cnt"],		// 一ページ内に表示する件数
		'delta'     => $GLOBALS["page_links_cnt"],		// 一ページ内に表示するリンク数
		'totalItems'=> $cntItems,						// ページング対象データの総数
		'separator' => ' ',								// ページリンクのセパレータ文字列
		'prevImg'   => 'BACK<<',						// 戻るリンク(imgタグ使用可)
		'nextImg'   => 'NEXT>>',						// 次へリンク(imgタグ使用可)
		'importQuery'=> FALSE,							// 自動的にPOST値をページングのHTMLタグに付与しません
		'append'=> FALSE,								// 自動でページをアペンドしません。
		'fileName'  => $url_submit
	);
	// ページングのインスタンスを生成します。
	$pager =& Pager::factory($option);

	// 表示する行数を決定します。
	// 開始行を決定します。
	$pg = $pager->getCurrentPageID();
	if ($pg <= 0)
	{
		$pg = 1;
	}
	$GLOBALS["cur_page"] = $pg;
	$startcnt = ($pg - 1) * $GLOBALS["page_images_cnt"];

	// 終了行を決定します。
	$lastcnt = $startcnt + $GLOBALS["page_images_cnt"];
	if ($lastcnt > $cntItems)
	{
		$lastcnt = $cntItems;
	}
	$pager_links = $pager->getLinks();
	//ページングの処理---------------------------------------------------End
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
	print "<dd>".$page_index."/".$pageCounts."</dd>\r\n";
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
	global $showFlag,$init_flg,$s_user_id,$p_pickupid,$db_link,$is,$img_count;

	if (!empty($init_flg))
	{
		//画面の初期表示の処理
		if ($init_flg == 1)
		{
			// 一枚以上イメージを一緒にピックアップした場合、クッキーからイメージIDを取得する
			$ck_id = "pickup_images_id_".$s_user_id;
			$p_pickupid = array_get_value($_COOKIE, $ck_id, "");
			if (empty($p_pickupid))
			{
				$p_pickupid = NULL;
			}
		}
	} else {
		// 一枚イメージをピックアップした場合、イメージIDを取得する
		$p_pickupid = array_get_value($_REQUEST, 'p_pickupid', "");
		if (empty($p_pickupid))
		{
			// 一枚以上イメージを一緒にピックアップした場合、クッキーからイメージIDを取得する
			$ck_id = "pickup_images_id_".$s_user_id;
			$p_pickupid = array_get_value($_COOKIE, $ck_id, "");
		}
	}

	if (empty($p_pickupid) || $p_pickupid == NULL)
	{
		$img_count = 0;
	} else {
		try
		{
			// ＤＢへ接続します。
			$db_link = db_connect();

			// 画像検索用のインスタンスを生成します。
			$is = new ImageSearch();

			// 画像を検索します。
			$is->set_photo_id_str($p_pickupid);
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
	}

	// 一ページにイメージの枚数の表示フラグを設定した場合
	if (!empty($showFlag))
	{
		// 一ページに１０枚イメージを表示する
		if ($showFlag == 0)
		{
			disp_slider10();
		// 一ページに３０枚イメージを表示する
		} elseif ($showFlag == 1)
		{
			disp_slider30();
		}
	} else {
		disp_slider10();
	}
}

/*
 * 関数名：disp_image_zero
 * 関数説明：イメージが無い場合の画面の表示処理
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_image_zero()
{
	global $hide_flg,$flg3;

	// ピックアップフレームを表示する
	if (empty($hide_flg))
	{
		print "<div id=\"photo_sc_contents\" class = \"photo_sc_contents\" style = \"\">\r\n";
		print "</div>\r\n";
	// ピックアップフレームを隠れる
	} elseif ($hide_flg == 1) {
		print "<div id=\"photo_sc_contents\" class = \"photo_sc_contents\" style = \"display:none\">\r\n";
		print "</div>\r\n";
	}

	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn2\" style=\"display:none\">\r\n";

	print "	<ul class=\"btn\">\r\n";
	print "		<li class=\"clear\"><a href=\"#\"><img src=\"parts/bt_clear.gif\" alt=\"クリア\"  title=\"クリア\" /></a></li>\r\n";
	print "	</ul>\r\n";
	print "</div>\r\n";

	// 「最近見た画像」ボタンを押さない場合
	//if (empty($flg3))
	//{
	//	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn3\" style=\"display:none\">\r\n";
	//} else {
	//	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn3\" style=\"display:block\">\r\n";
	//}
	//print "	<ul class=\"btn\">\r\n";
	//print "		<li class=\"clear\"><a href=\"#\"><img src=\"parts/bt_clear.gif\" alt=\"クリア\" /></a></li>\r\n";
	//print "		<li class='all_display bt_all'><a href='#'>\r\n";
	//print "			<img src='parts/bt_all0.gif' alt='全て表示' id='image002' name='image002' /></a>";
	//print "		</li>\r\n";
	//print "	</ul>\r\n";
	//print "</div>\r\n";

	// ピックアップフレームを表示する
	if (empty($hide_flg))
	{
		// 「最近見た画像」ボタンを押さない場合
		//if (empty($flg3))
		//{
			print "<div class='photo_sc_btn' id='photo_sc_btn' style = \"\">\r\n";
			//2008/01/09 仕様変更 下記のURLより行う---------------------------------------------
			//http://www3.bud-international.co.jp/hei/cms/090119_photo_db/sample_index.html
			print "<p class=\"photo_sc_btn_cap\">▲ここにピックアップ画像が表示されます</p>\r\n";
			//----------------------------------------------------------------------------------
			print "<ul class ='btn'>";
		//} else {
		//	print "<div class='photo_sc_btn' id='photo_sc_btn' style = \"display:none\">\r\n";
		//	print "<ul class ='btn'>";
		//}
	// ピックアップフレームを隠れる
	} elseif ($hide_flg == 1) {
		print "<div class='photo_sc_btn' id='photo_sc_btn' style = \"display:none\">\r\n";
		print "<ul class ='btn'>";
	}

	//print "<li class='fixation'><a href='#'><img src='parts/bt_fixation.gif' alt='確定'/></a></li>\r\n";
	print "<li class='clear'><a href='#'><img src='parts/bt_clear.gif' alt='クリア' title=\"クリア\" /></a></li>\r\n";
	print "<li class='all_display bt_all'><a href='#'>\r\n";
	print "<img src='parts/bt_all0.gif' alt='もっと見る' id='image002' name='image002' title=\"もっと見る\"/></a></li>\r\n";
	print "</ul>\r\n";
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
	global $img_count, $disp_max, $is, $s_user_id, $hide_flg, $flg3;

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

		// ピックアップフレームを表示する
		if (empty($hide_flg))
		{
			print "<div id='photo_sc_contents' class = 'photo_sc_contents' style = ''>\r\n";
		// ピックアップフレームを隠れる
		} elseif ($hide_flg == 1) {
			print "<div id='photo_sc_contents' class = 'photo_sc_contents' style = \"display:none\">\r\n";
		}

		// ページ総数を取得する
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
				print "<div id='div_img_".$page_index."' style=\"display:block;\">\r\n";
				print "<ul>\r\n";
			} else {// 最初は一行目以外のイメージを表示しない。
//				// 一行目に１０個画像を表示し、1行目以上の場合、「＞」ボタンを出力する
//				if ($pageCount > 1)
//				{
//					print "<p id='photo_sc_contents_next'><a href='#' onclick='move2(1)'>NEXT&gt;&gt;</a></p>\r\n";
//				}
				print "<div id='div_img_".$page_index."' style=\"display:none;clear:both;\">\r\n";
				print "<p id='photo_sc_contents_back'><a href='#' onclick='move2(-1);return false;'>&lt;&lt;BACK</a></p>\r\n";
				print "<ul>\r\n";
			}

			// 一行目中のイメージ数の繰り返し
			for ($i = $s_link_index; $i < $e_link_index; $i++) {
				$ph_img_all = $is->images[$i];

				$keyid = "dl".$ph_img_all->photo_id;
				print "<li id='".$keyid."' style='display:block'>\r\n";
				print "<dl class='photo'>\r\n";
				if (!empty($ph_img_all->photo_explanation))
				{
					print "<dt>".dp(santen_reader($ph_img_all->photo_explanation,5))."</dt>\r\n";
				} else {
					print "<dt>&nbsp&nbsp&nbsp</dt>\r\n";
				}
				print "<dd class='samneil'><a href='#'><img width='60px' height='45px' src='".$ph_img_all->up_url[3]."' alt='イメージ'  onclick='disp_ImageInformation(\"".$ph_img_all->photo_id."\");return false;'/></a></dd>\r\n";
				//print "<dd class='bt_delete'><a href='#'><img src='parts/bt_delete.gif' alt='削除' onclick='hide_image(\"".$ph_img_all->photo_id."\");delete_pickup(\"".$ph_img_all->photo_id."\",".$s_user_id.");return false;'/></a></dd>\r\n";
				print "<dd class='bt_delete'><a href='#'><img src='parts/bt_delete.gif' alt='削除' onclick='delete_pickup(\"".$ph_img_all->photo_id."\",".$s_user_id.");return false;'/></a></dd>\r\n";
				print "</dl>\r\n";//<dl 'photo'>終了
				print "</li>\r\n";
			}
			print "</ul>\r\n";// <div 'div_img_'>前の<ul>終了
			print "</div>\r\n";// <div 'div_img_'>終了
		}

		// 一行目に１０個画像を表示し、1行目以上の場合、「＞」ボタンを出力する
		if ($pageCount > 1)
		{
			print "<p id='photo_sc_contents_next'><a href='#' onclick='move2(1);return false;'>NEXT&gt;&gt;</a></p>\r\n";
		}
		// 最初は1ページを表示する
		showPageNumber(1);
		print "</div>\r\n";// <div 'photo_sc_contents'>終了
		// 「最近見た画像」ボタンを押さない場合
		//if (empty($flg3))
		//{
		//	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn3\" style=\"display:none\">\r\n";
		//} else {
		//	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn3\" style=\"display:block\">\r\n";
		//}
		//print "	<ul class=\"btn\">\r\n";
		//print "<li class='clear'><a href='#'><img src='parts/bt_clear.gif' alt='クリア' onclick='clear_pickup3(".$GLOBALS["s_user_id"].");'/></a></li>\r\n";
		//print "<li class='all_display bt_all'><a href='#'>\r\n";
		//print "<img src='parts/bt_all0.gif' alt='全て表示' id='image002' name='image002' onclick='change_on();' /></a></li>\r\n";
		//print "</li>\r\n";
		//print "</ul>\r\n";
		//print "</div>\r\n";// <div 'photo_sc_btn3'>終了

		// ピックアップフレームを表示する
		if (empty($hide_flg))
		{
			//if (empty($flg3))
			//{
				print "<div class='photo_sc_btn' id='photo_sc_btn' style=\"display:block\">\r\n";
			//} else {
			//	print "<div class='photo_sc_btn' id='photo_sc_btn' style=\"display:none\">\r\n";
			//}
		// ピックアップフレームを隠れる
		} elseif ($hide_flg == 1) {
			print "<div class='photo_sc_btn' id='photo_sc_btn' style=\"display:none\">\r\n";
		}
		print "<ul class ='btn'>\r\n";
		//print "<li class='fixation'><a href='#'><img src='parts/bt_fixation.gif' alt='確定' onclick='location_arubamu_insert();'/></a></li>\r\n";
		print "<li class='clear'><a href='#'><img src='parts/bt_clear.gif' alt='クリア' onclick='clear_pickup(".$GLOBALS["s_user_id"].");return false;' title=\"クリア\" /></a></li>\r\n";
		print "<li class='all_display bt_all'><a href='#'>\r\n";
		print "<img src='parts/bt_all0.gif' alt='もっと見る' id='image002' name='image002' onclick='change_on();return false;' title=\"もっと見る\"/></a></li>\r\n";
		print "</ul>";
		print "</div>";// <div 'photo_sc_btn'>終了
	}
}

/*
 * 関数名：disp_slider30
 * 関数説明：1ページに３０個ずつを表示する画面の出力処理
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_slider30()
{
	global $img_count, $disp_max, $is, $s_user_id, $pager_links, $startcnt, $lastcnt, $hide_flg, $flg3;

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

		print "<div id='photo_sc_contents' class = 'photo_sc_contents'>\r\n";
		ShowPagesList($img_count);
		print "		<div id='div_img_".$GLOBALS["cur_page"]."' style=\"display:block;\">\r\n";
//		print "		<ul>\r\n";
		// 行目数の繰り返し
		$groupcnt = 0;

		for ($img_indx = $startcnt; $img_indx < $lastcnt; $img_indx++)
		{
			$ph_img_all = $is->images[$img_indx];

			$groupcnt = $groupcnt + 1;
			if ($groupcnt == 1)
			{
				print "		<ul>\r\n";
			}

			$keyid = "dl".$ph_img_all->photo_id;
			print "<li id='".$keyid."' style='display:block'>\r\n";
			print "<dl class='photo'>\r\n";
			if (!empty($ph_img_all->photo_explanation))
			{
				print "<dt>".dp(santen_reader($ph_img_all->photo_explanation,5))."</dt>\r\n";
			} else {
				print "			<dt>&nbsp&nbsp&nbsp</dt>\r\n";
			}
			print "				<dd class='samneil'><a href='#'><img width='60px' height='45px' src='".$ph_img_all->up_url[3]."' height='53px' alt='イメージ'  onclick='disp_ImageInformation(\"".$ph_img_all->photo_id."\");return false;'/></a></dd>\r\n";
			print "				<dd class='bt_delete'><a href='#'><img src='parts/bt_delete.gif' alt='削除' onclick='hide_image(\"".$ph_img_all->photo_id."\");delete_pickup2(\"".$ph_img_all->photo_id."\",".$s_user_id.");return false;'/></a></dd>\r\n";
			//print "				<dd class='bt_delete'><a href='#'><img src='parts/bt_delete.gif' alt='削除' onclick='delete_pickup(\"".$ph_img_all->photo_id."\",".$s_user_id.");return false;'/></a></dd>\r\n";
			print "			</dl>\r\n";// <div 'photo'>終了
			print "		</li>\r\n";

			if ($groupcnt == 10)
			{
				print "		</ul>\r\n";
				$groupcnt = 0;
			}
		}
		print "		</ul>\r\n";// <div 'div_img_'>前の<ul>終了
		print "		</div>\r\n";// <div 'div_img_'>終了

		// ページングの表示----Start
		print "		<div id=\"next_back\" style=\"display:block;height:10px;clear:both\">";
		print "		<ul id=\"next_back\">\r\n";
		print "			<li style=\"color:yellow\">\r\n";
		print($GLOBALS["pager_links"]["all"]);
		print "			</li>\r\n";
		print "		</ul>\r\n";
		print "		</div>\r\n";// <div 'next_back'>終了
		// ページングの表示----End

		print "</div>\r\n";// <div 'photo_sc_contents'>終了

		// 「最近見た画像」ボタンを押さない場合
		//if (empty($flg3))
		//{
		//	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn3\" style=\"display:none\">\r\n";
		//} else {
		//	print "<div class=\"photo_sc_btn\" id=\"photo_sc_btn3\" style=\"display:block\">\r\n";
		//}
		//print "	<ul class=\"btn\">\r\n";
		//print "		<li class=\"clear\"><a href=\"#\"><img src=\"parts/bt_clear.gif\" alt=\"クリア\" /></a></li>\r\n";
		//print "		<li class='all_display bt_all'><a href='#'>\r\n";

		// 「最近見た画像」ボタンを押さない場合
		//if (empty($flg3))
		//{
		//	print "		<img src='parts/bt_all0.gif' alt='全て表示' id='image002' name='image002' onclick='change_on();'/></a>";
		//} else {
		//	print "		<img src='parts/bt_all1.gif' alt='全て表示' id='image002' name='image002' onclick='change_on();'/></a>";
		//}

		//print "		</li>\r\n";
		//print "	</ul>\r\n";
		//print "</div>\r\n";// <div 'photo_sc_btn3'>終了

		// ピックアップフレームを表示する
		if (empty($hide_flg))
		{
			// 「最近見た画像」ボタンを押さない場合
			//if (empty($flg3))
			//{
				print "<div class='photo_sc_btn' id='photo_sc_btn' style = ''>\r\n";
			//} else {
			//	print "<div class='photo_sc_btn' id='photo_sc_btn' style=\"display:none\">\r\n";
			//}
		// ピックアップフレームを隠れる
		} elseif ($hide_flg == 1) {
			print "<div class='photo_sc_btn' id='photo_sc_btn' style=\"display:none\">\r\n";
		}
		print "		<ul class ='btn'>\r\n";
		//print "			<li class='fixation'><a href='#'><img src='parts/bt_fixation.gif' alt='確定' onclick='location_arubamu_insert();'/></a></li>\r\n";
		print "			<li class='clear'><a href='#'><img src='parts/bt_clear.gif' alt='クリア' onclick='clear_pickup(".$GLOBALS["s_user_id"].");return false;' title=\"クリア\" /></a></li>\r\n";
		print "			<li class='all_display bt_all'><a href='#'>\r\n";
		print "			<img src='parts/bt_all1.gif' alt='もっと見る' id='image002' name='image002' onclick='change_on();return false;' title=\"もっと見る\"/></a></li>\r\n";
		print "		</ul>";
		print "</div>";// <div 'photo_sc_btn'>終了
	}
}
?>