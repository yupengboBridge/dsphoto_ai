<?php
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

if (!empty($s_security_level)) $s_security_level = (int)$s_security_level;

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

// 変数を初期化します。
$msg = array();																							// メッセージ
$err = false;																							// エラー

// (分類)--------------------------------------------------------------------------------------------------
//$take_picture_time_id = array();																		// 撮影時期１
//$take_picture_time_name = array();
//$take_picture_time2_id = array();																		// 撮影時期2
//$take_picture_time2_name = array();

$classification_id = array();																			// 分類ID
$classification_name = array();																			// 分類
$direction_id = array();																				// 方面ID
$direction_name = array();																				// 方面
$country_prefecture_id = array();																		// 国・都道府県ID
$country_prefecture_name = array();																		// 国・都道府県
//$place_id = array();																					// 地名ID
//$place_name = array();																					// 地名

$category_id = array();																					// カテゴリーID
$category_name = array();																				// カテゴリー名

$range_id = array();																					// 使用範囲ID
$range_name = array();																					// 使用範囲名

$borrow_id = array();																					// 写真入手元ID
$borrow_name = array();																					// 写真入手元

// PhotoImageのインスタンスを生成します。


$pi = new PhotoImageDB();

try
{
	$db_link = db_connect();

//	$pi->get_take_picture_time($db_link,$take_picture_time_id,$take_picture_time_name);						// 撮影時期１
//	$pi->get_take_picture_time2($db_link,$take_picture_time2_id,$take_picture_time2_name);					// 撮影時期2

	$pi->get_classification($db_link, $classification_id, $classification_name);							// 分類
	$pi->get_direction($db_link,$direction_id,$direction_name,$classification_id);
	$pi->get_country_prefecture($db_link,$country_prefecture_id,$country_prefecture_name,$direction_id);
//	$pi->get_place($db_link,$place_id,$place_name,$country_prefecture_id);
	$pi->get_category($db_link,$category_id,$category_name);												//カテゴリ
	$pi->get_range_of_use($db_link,$range_id,$range_name);													// 使用範囲
	$pi->get_borrowing_ahead($db_link,$borrow_id,$borrow_name);												// 写真入手元
}
catch(Exception $cla)
{
	$msg[] = $e->getMessage();
	error_exit($msg);
}

///*
// * 関数名：take_picture_time2
// * 関数説明：「撮影時期」の季節を出力する
// * パラメタ：
// * $c_id：	季節ID
// * $c_name：　季節
// * 戻り値：無し
// */
//function take_picture_time2($c_id, $c_name)
//{
//	$ed = count($c_id);
//	for ($i = 0 ; $i<$ed ; $i++)
//	{
//		$key_id = "rad_kisetu".$i;
//		$key_lbl = "rad_kisetu_lbl".$i;
//		//print "<label id=\"".$key_lbl."\"style=\"display:none\"><input name='rad_kisetu' id=\"".$key_id."\" type='radio' value='".$c_name[$i]."' />".$c_name[$i]."</label>";
//		print "<label><input name='rad_kisetu' id=\"".$key_id."\" type='radio' value='".$c_name[$i]."' />".$c_name[$i]."</label> &nbsp;&nbsp;";
//	}
//}

///*
// * 関数名：take_picture_time
// * 関数説明：「撮影時期」の月と「掲載期間」の月を出力する
// * パラメタ：
// * $c_id：	月ID
// * $c_name：　月
// * $flg:出力フラグ「1」：掲載期間の月；「0」：撮影時期の月
// * 戻り値：無し
// */
//function take_picture_time($c_id, $c_name, $flg)
//{
//	//print"<label><select id='take_picture_time_id' name='take_picture_time_name'>";
//	print"<select id='take_picture_time_id' name='take_picture_time_name'>";
//	$ed = count($c_id);
//	// 掲載期間の月を出力する時
//	if ($flg == 1)
//	{
//		print "		<option value='-1'>未定&nbsp;&nbsp;</option>\r\n";
//	// 撮影時期の月を出力する時
//	} else {
//		print "		<option value='-1'>お選びください</option>\r\n";
//	}
//	for ($i = 0 ; $i < $ed ; $i++)
//	{
//		print "<option value='" . $c_id[$i] . "'>" . $c_name[$i] . "</option>\r\n";
//	}
//	print"</select>";
//	//print"</label>";
//}

/*
 * 関数名：disp_classification
 * 関数説明：「登録分類」を出力する
 * パラメータ：
 * c_id:分類ID；c_name:分類名;
 * 戻り値：無し
 */
function disp_classification($c_id, $c_name)
{
	print "<select id='p_classification_id' name='p_classification_id'>\r\n";
	print "<option value='-1'></option>\r\n";
	$ed = count($c_id);
	for ($i = 0 ; $i < $ed ; $i++)
	{
		print "<option value='" . $c_id[$i] . "'>" . $c_name[$i] . "</option>\r\n";
	}
	print "</select>\r\n";
}

/*
 * 関数名：disp_direction
 * 関数説明：「登録分類」を出力する、必ず、disp_classification()の後に実行してください。
 * パラメータ：
 * d_id:方面ID；d_name:方面名;c_id:分類ID;
 * 戻り値：無し
 */
function disp_direction($d_id, $d_name, $c_id)
{
	// 方面を表示します。
	//print "<label>\r\n";
	print "<select id='p_direction_id' name='p_direction_id' style=\"width:105px\">\r\n";
	print "<option value='-1'>方　面</option>\r\n";
	$ed = count($c_id);
	for ($i = 0 ; $i < $ed ; $i++)
	{
		print "<optgroup label='" . $c_id[$i] . "'>\r\n";
		$ed2 = count($d_id[$i]);
		for ($j = 0 ; $j < $ed2 ; $j++)
		{
			print "<option value='" . $d_id[$i][$j] . "'>" . santen_reader($d_name[$i][$j], 18) . "</option>\r\n";
		}
		print "</optgroup>\r\n";
	}
	print "</select>\r\n";
	//print "</label>\r\n";
}

/*
 * 関数名：disp_country_prefecture
 * 関数説明：「登録分類」を出力する、必ず、disp_direction()の後に実行してください。
 * パラメータ：
 * cp_id:国・都道府県ID；cp_name:国・都道府県名;
 * d_id:方面ID;
  * 戻り値：無し
 */
function disp_country_prefecture($cp_id, $cp_name, $d_id)
{
	//print "<label>\r\n";
	print "<select id='p_country_prefecture_id' name='p_country_prefecture_id' style=\"width:195px\">\r\n";
	print "<option value='-1'>国・都道府県</option>\r\n";
	// 国・都道府県を表示します。
	$ed = count($d_id);
	// 方面（海外）に分けて、国を取得します。
	for ($i = 0 ; $i < $ed ; $i++)
	{
		$ed2 = count($d_id[$i]);
		for ($j = 0 ; $j < $ed2 ; $j++)
		{
			print "<optgroup label='" . $d_id[$i][$j] . "'>\r\n";
			$ed3 = count($cp_id[$i][$j]);
			for ($k = 0 ; $k < $ed3 ; $k++)
			{
				print "<option value='" . $cp_id[$i][$j][$k] . "'>" . santen_reader($cp_name[$i][$j][$k], 18) . "</option>\r\n";
			}
		}
		print "</optgroup>\r\n";
	}
	print "</select>\r\n";
	//print "</label>\r\n";
}

///*
// * 関数名：disp_place
// * 関数説明：「登録分類」を出力する、必ず、disp_country_prefecture()の後に実行してください。
// * パラメータ：
// * p_id:都市ID；p_name:都市名;
// * cp_id:国・都道府県ID;
//  * 戻り値：無し
// */
//function disp_place($p_id, $p_name, $cp_id)
//{
//	print "<label>";
//	print "<select id='p_place_id' name='p_place_id'>";
//	print "<option value='-1'>都市&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";
//	// 地名を表示します。
//	$ed = count($p_id);
//	for ($i = 0 ; $i < $ed ; $i++)
//	{
//		$ed2 = count($p_id[$i]);
//		for ($j = 0 ; $j < $ed2 ; $j++)
//		{
//			$ed3 = count($p_id[$i][$j]);
//			for ($k = 0 ; $k < $ed3 ; $k++)
//			{
//				print "<optgroup label='" . $cp_id[$i][$j][$k] . "'>\r\n";
//				$ed4 = count($p_id[$i][$j][$k]);
//				for ($l = 0 ; $l < $ed4 ;$l++)
//				{
//					print "<option value='" . $p_id[$i][$j][$k][$l] . "'>" . santen_reader($p_name[$i][$j][$k][$l], 18) . "</option>\r\n";
//				}
//			}
//			print "</optgroup>\r\n";
//		}
//	}
//	print "</select>\r\n";
//	print "</label>";
//}

/*
 * 関数名：dis_category
 * 関数説明：「カテゴリー」を出力する
 * パラメタ：
 * $cg_id：	　カテゴリーID
 * $cg_name：　カテゴリー
 * 戻り値：無し
 */
function dis_category($cg_id,$cg_name)
{

	$dc = count($cg_id);
	print "<ul>";
	for ($i=0;$i < $dc;$i++)
	{
		print "<li class='list_dot'> <em>";
		$id = "ct_" . $i . "_0";
		print "<input id='".$id."' category='0' type='checkbox' value='".dp($cg_name[$i][0])."' />&nbsp;".$cg_name[$i][0]."</em>";
		print "<ul class='list_child'>";
		$dc2 = count($cg_id[$i]);
		for($j = 1;$j < $dc2; $j++)
		{
			$id = "ct_" . $i . "_" . $j;
			print "<li><input id='".$id."' category='0' type='checkbox' value='".dp($cg_name[$i][$j])."' />&nbsp;".$cg_name[$i][$j]."</li>";
		}
		print "</ul>";
		print "</li>";
	}
	print "</ul>";
}

///*
// * 関数名：take_picture_year
// * 関数説明：「日付指定」の「年」を出力する
// * パラメタ：無し
// * 戻り値：無し
// */
//function take_picture_year()
//{
//	print"<select name=\"select_year\" id=\"select_year\" onChange='change_year();'>\r\n";
//	// システム日付の「年」を取得する
//	$now_year = (int)substr(date("Y-m-d"),0,4);
//	print "		<option value='-1'>未定&nbsp;&nbsp;</option>\r\n";
//	// システム日付から、11年後まで、「年」を出力する
//	for ($i = 0; $i <= 10; $i++)
//	{
//		$ed_year = $now_year + $i;
//		print"	<option value=\"".$ed_year."\">".dp($ed_year)."</option>\r\n";
//	}
//	print"</select>\r\n";
//}

///*
// * 関数名：take_picture_day
// * 関数説明：「日付指定」の「日」を出力する
// * パラメタ：無し
// * 戻り値：無し
// */
//function take_pictrue_day()
//{
//
//	print"	<select name=\"select_day\" id=\"select_day\">\r\n";
//	print "		<option value='-1'>未定&nbsp;&nbsp;</option>\r\n";
//	// １～３１日を出力する
//	for ($i = 1; $i <= 31; $i++)
//	{
//		$s_day = $i."日";
//		print"		<option value=\"".$i."\">".dp($s_day)."</option>\r\n";
//	}
//	print"	</select>\r\n";
//}

/*
 * 関数名：disp_range
 * 関数説明：「掲載可能範囲」を出力する
 * パラメタ：
 * $r_id：	　掲載可能範囲ID
 * $r_name：  　掲載可能範囲
 * 戻り値：無し
 */
function disp_range($r_id,$r_name)
{
	$ed = count($r_id);
	// 掲載可能範囲より繰り返し
	for ($i=0;$i < $ed;$i++)
	{
		// 掲載可能範囲IDを作成する
		$key_id = "reg_pub_possible".$i;
		// 掲載可能範囲の「外部出稿条件付き」を選択した場合
		$key = "reg_pub_possible_lbl".$i;
		if ((int)$r_id[$i] == 3)
		{
			print"		<label id=\"".$key."\" style=\"display:none\"><input name=\"reg_pub_possible\" id=\"".$key_id."\" type=\"radio\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
			print"		<input name=\"reg_pub_possible_txt\" type=\"text\" style='display:none;width:140px;' id=\"reg_pub_possible_txt\" size=\"30\" onkeypress=\"save_search_conidition(event,this);\" disabled/>\r\n";
		} else {
			print"		<label id=\"".$key."\" style=\"display:none\"><input name=\"reg_pub_possible\" id=\"".$key_id."\" type=\"radio\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
		}
	}
}

/*
 * 関数名：disp_borrowing_ahead
 * 関数説明：「写真入手元」を出力する
 * パラメタ：
 * $r_id：	　写真入手元ID
 * $r_name：  　写真入手元
 * 戻り値：無し
 */
 //added by wangtongchao 2011-11-28 begin
 function disp_borrowing_ahead($b_head_id,$b_head_name)
{
	$ed = count($b_head_id);

	for ($i=0;$i < $ed;$i++)
	{
		$key_id = "reg_p_obtaining".$i;
		// 写真入手元の「その他」を選択した場合
		if ((int)$b_head_id[$i] == 2)
		{
			print"		<label id=".$key_id." style=\"display:none\"><input name=\"reg_p_obtaining\" id=\"".$key_id."\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' style=\"display:none\" checked=\"checked\" /></label>\r\n";
			print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" style=\"display:none;width:170px;\" size=\"30\" onkeypress=\"save_search_conidition(event,this);\" />\r\n";
		} else {
			print"		<label id=".$key_id." style=\"display:none\"><input name=\"reg_p_obtaining\" id=\"".$key_id."\"type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' style=\"display:none\" /></label>\r\n";
		}
	}
}
//added by wangtongchao 2011-11-28 end
//deleted by wangtongchao 2011-11-28 begin
//function disp_borrowing_ahead($b_head_id,$b_head_name)
//{
//	$ed = count($b_head_id);
//
//	for ($i=0;$i < $ed;$i++)
//	{
//		$key_id = "reg_p_obtaining".$i;
//		// 写真入手元の「その他」を選択した場合
//		if ((int)$b_head_id[$i] == 2)
//		{
//			print"		<label id=".$key_id." style=\"display:none\"><input name=\"reg_p_obtaining\" id=\"".$key_id."\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//			print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" style=\"display:none;width:170px;\" size=\"30\" onkeypress=\"save_search_conidition(event,this);\" disabled/>\r\n";
//		} else {
//			print"		<label id=".$key_id." style=\"display:none\"><input name=\"reg_p_obtaining\" id=\"".$key_id."\"type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//		}
//	}
//}
//deleted by wangtongchao 2011-11-28 end
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title></title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<?php if ($s_security_level == 4) { ?>
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<?php } elseif ($s_security_level == 3) { ?>
<link rel="stylesheet" href="./css/master_c.css" type="text/css" media="all" />
<?php } elseif ($s_security_level == 2) { ?>
<link rel="stylesheet" href="./css/master_b.css" type="text/css" media="all" />
<?php } elseif ($s_security_level == 1) { ?>
<link rel="stylesheet" href="./css/master_a.css" type="text/css" media="all" />
<!-- xu add it on 20110131 start -->
<?php }elseif ($s_security_level == 5) { ?>
<link rel="stylesheet" href="./css/master_a.css" type="text/css" media="all" />
<!-- xu add it on 20110131 end -->
<?php } ?>
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="./js/ConnectedSelect/ConnectedSelect2.js" type="text/javascript" charset="utf-8"></script>
<script src="./js/jquery.js"  type="text/javascript" charset="utf-8"></script>
<script src="./js/common.js"  type="text/javascript" charset="utf-8"></script>
<!--<script type='text/javascript' src='./js/dateformat/dateformat.js' charset="utf-8"></script> -->
<script type="text/javascript">
<?php
if (!empty($GLOBALS["s_user_id"]))
{
	print "var uid = ".$GLOBALS["s_user_id"].";\r\n";
} else {
	print "var uid = '';";
}
?>
<!--
// 日付のフォーマット
//var dateFormat = new DateFormat("yyyy-MM-dd");
var c_array = new Array();
var detail_search_bt=0;
function changedetail_search()
{
	detail_search_bt++;
	if (detail_search_bt==2)
	{
		detail_search_bt = 0;
		top.document.getElementById('iframe_top').rows = "75,170,200,*";
		top.document.getElementById('iframe_middle1').style.height = 90;
	}
	else
	{
		top.document.getElementById('iframe_top').rows = "75,500,200,*";
		top.document.getElementById('iframe_middle1').style.height = 620;
	}
	$("dl#detail_search dd.search_contents").slideToggle("fast");
	var radobj = window.parent.frames[1].document.getElementsByName("publishing_include");
	if (radobj[0].checked == true)
	{
		radobj[0].checked = true;
		radobj[1].checked = false;
		radobj[2].checked = false;
		radobj[3].checked = false;
	} else if (radobj[1].checked == true) {
		radobj[0].checked = false;
		radobj[1].checked = true;
		radobj[2].checked = false;
		radobj[3].checked = false;
	} else if (radobj[2].checked == true) {
		radobj[0].checked = false;
		radobj[1].checked = false;
		radobj[2].checked = true;
		radobj[3].checked = false;
	} else if (radobj[3].checked == true) {
		radobj[0].checked = false;
		radobj[1].checked = false;
		radobj[2].checked = false;
		radobj[3].checked = true;
	} else {
		radobj[0].checked = true;
		radobj[1].checked = false;
		radobj[2].checked = false;
		radobj[3].checked = false;
	}

	var obj1 = window.parent.frames[1].document.getElementsByName("category_include");
	if(obj1[1].checked == false)
	{
		obj1[1].checked = false;
		obj1[0].checked = true;
	} else {
		obj1[1].checked = true;
		obj1[0].checked = false;
	}

	var obj = document.getElementById("detail_href");
	if(obj)
	{
		if(obj.className == "bt_details_close")
		{
			obj.className = "bt_details_add";
		} else {
			obj.className = "bt_details_close";
		}
	}
	return false;
}

/*
 * 関数名：convertKana
 * 関数説明：半角カナ⇒全角カナの転換
 * パラメタ：txtstring：文字列
 * 戻り値：無し
 */
function convertKana(txtstring)
{
	var tmp = txtstring;

	var fullKana = new Array("ヴ","ガ","ギ","グ","ゲ","ゴ","ザ","ジ","ズ","ゼ","ゾ","ダ","ヂ","ヅ","デ","ド","バ","ビ","ブ","ベ","ボ","パ","ピ","プ","ペ","ポ","゛","。","「","」","、","・","ヲ","ァ","ィ","ゥ","ェ","ォ","ャ","ュ","ョ","ッ","ー","ア","イ","ウ","エ","オ","カ","キ","ク","ケ","コ","サ","シ","ス","セ","ソ","タ","チ","ツ","テ","ト","ナ","ニ","ヌ","ネ","ノ","ハ","ヒ","フ","ヘ","ホ","マ","ミ","ム","メ","モ","ヤ","ユ","ヨ","ラ","リ","ル","レ","ロ","ワ","ン","゜");

	var halfKana = new Array("ｳﾞ","ｶﾞ","ｷﾞ","ｸﾞ","ｹﾞ","ｺﾞ","ｻﾞ","ｼﾞ","ｽﾞ","ｾﾞ","ｿﾞ","ﾀﾞ","ﾁﾞ","ﾂﾞ","ﾃﾞ","ﾄﾞ","ﾊﾞ","ﾋﾞ","ﾌﾞ","ﾍﾞ","ﾎﾞ","ﾊﾟ","ﾋﾟ","ﾌﾟ","ﾍﾟ","ﾎﾟ","ﾞ","｡","｢","｣","､","･","ｦ","ｧ","ｨ","ｩ","ｪ","ｫ","ｬ","ｭ","ｮ","ｯ","ｰ","ｱ","ｲ","ｳ","ｴ","ｵ","ｶ","ｷ","ｸ","ｹ","ｺ","ｻ","ｼ","ｽ","ｾ","ｿ","ﾀ","ﾁ","ﾂ","ﾃ","ﾄ","ﾅ","ﾆ","ﾇ","ﾈ","ﾉ","ﾊ","ﾋ","ﾌ","ﾍ","ﾎ","ﾏ","ﾐ","ﾑ","ﾒ","ﾓ","ﾔ","ﾕ","ﾖ","ﾗ","ﾘ","ﾙ","ﾚ","ﾛ","ﾜ","ﾝ","ﾟ");

	for(i = 0; i < 89; i++){
		re = new RegExp(halfKana[i],"g")
		tmp=tmp.replace(re, fullKana[i]);
	}
	return tmp;
}

/*
 * 関数名：check_search_condition
 * 関数説明：入力の検索条件をチェックする
 * パラメタ：s_value:入力の検索条件
 * 戻り値：無し
 */
function check_search_condition(s_value)
{
	if (trim(s_value) != "")
	{
		var error_msg = "入力の検索条件の書式は間違いです。\r\n例：\"自然\" \"ハワイ\"\r\n※項目の間にはスペースがあります。ご注意ください。";
		// 「"」を検索する
		var ipos = parseInt(s_value.indexOf("\""));
		if (ipos >= 0)
		{
			// 「-」と「or」を検索する
			//var ipos2 = parseInt(s_value.indexOf("-"));
			var ipos3 = parseInt(s_value.indexOf("or"));
			var ipos4 = parseInt(s_value.indexOf("OR"));
			if (ipos2 >=0 || ipos3 >=0 || ipos4 >=0)
			{
				alert(error_msg);
				return false;
			} else {
				// --------「"」の数は偶数ではないかどうかチェックする(開始)---------
				var cnt = 0;
				var ipos2 = -1;
				for (var i = 0; i < s_value.length; i++)
				{
					if (ipos2 >= 0)
					{
						cnt = cnt + 1;
						ipos2 = parseInt(s_value.indexOf("\"",ipos2 + 1));
						if (ipos2 < 0 ) break;
					} else {
						ipos2 = parseInt(s_value.indexOf("\"",0));
					}
				}
				var id_a = new Array();
				var tmp_1 = s_value.replace(/　/g," ");

				id_a = tmp_1.explode(" ");
				var ed = id_a.length * 2;
				if (cnt == 0 || cnt != ed)
				{
					alert(error_msg);
					return false;
				}
				// --------「"」の数は偶数ではないかどうかチェックする(終了)---------
			}
		}

		// スペースを検索する
		var tmpstr = "=" + s_value;
		var tmp_1 = tmpstr.replace(/　/g," ");
		var ipos = parseInt(tmp_1.indexOf(" "));
		if (ipos > 0)
		{
			var ipos1 = parseInt(tmp_1.indexOf(" ",ipos+1));
			// 連続に二つスペースを入力した場合、エラーとなる
			if (ipos1 > 0 && (ipos1-ipos) == 1)
			{
				alert("入力の検索条件の書式は間違いです。\r\n スペースの入力はエラーです。ご確認ください。");
				return false;
			}
		}
	}

	return true;
}

/*
 * 関数名：init_condition
 * 関数説明：検索条件を初期化する
 * パラメタ：無し
 * 戻り値：無し
 */
function init_condition()
{
	document.getElementById("lbl_search_where").style.display="none";
//	document.getElementById("reg_pub_period0_lbl").style.display="none";
//	document.getElementById("reg_pub_period1_lbl").style.display="none";
//	document.getElementById("reg_pub_period2_lbl").style.display="none";
//	document.getElementById("reg_pub_period3_lbl").style.display="none";
//	document.getElementById("reg_pub_period4_lbl").style.display="none";
//	document.getElementById("year_lbl").style.display="none";
//	document.getElementById("reg_pub_time_lbl").style.display="none";
//	document.getElementById("day_lbl").style.display="none";
	for (var i = 0; i < 3; i++)
	{
		var keyid = "reg_pub_possible_lbl" + i;
		document.getElementById(keyid).style.display="none";
	}
	document.getElementById("reg_pub_possible_txt").style.display="none";
	document.getElementById("reg_account_lbl").style.display="none";
	document.getElementById("reg_p_obtaining0").style.display="none";
	document.getElementById("reg_p_obtaining1").style.display="none";
	document.getElementById("reg_p_obtaining_txt").style.display="none";
	document.getElementById("reg_bud_number0").style.display="none";
	document.getElementById("reg_bud_number1").style.display="none";
	document.getElementById("reg_bud_number_txt").style.display="none";
	for (var i = 0; i < 3; i++)
	{
		var keyid = "reg_addition" + i + "_lbl";
		document.getElementById(keyid).style.display="none";
	}
	document.getElementsByName("reg_addition_txt")[0].style.display="none";
	document.getElementsByName("reg_addition_txt")[1].style.display="none";
	//document.getElementById("search_bt").style.left = "475px";
	document.getElementById("search_bt").style.left = "525px";
	//-------------2008-12-15追加開始--------------------------------
	//----------掲載可能範囲------------------------------------
	document.getElementById("reg_pub_possible0").checked = false;
	document.getElementById("reg_pub_possible1").checked = false;
	document.getElementById("reg_pub_possible2").checked = false;
	document.getElementById("reg_pub_possible_txt").value = "";

//	//----------掲載期間----------------------------------------
//	document.getElementById("reg_pub_period0").checked = false;
//	document.getElementById("reg_pub_period1").checked = false;
//	document.getElementById("reg_pub_period2").checked = false;
//	document.getElementById("reg_pub_period3").checked = false;
//	document.getElementById("reg_pub_period4").checked = false;
//	document.getElementById("select_year").selectedIndex = 0;
//	document.getElementsByName("take_picture_time_name")[0].selectedIndex = 0;
//	document.getElementById("select_day").selectedIndex = 0;

	//----------追加条件----------------------------------------
	document.getElementById("reg_addition0").checked = false;
	document.getElementById("reg_addition1").checked = false;
	document.getElementById("reg_addition2").checked = false;
	document.getElementById("reg_addition_txt0").value = "";
	document.getElementById("reg_addition_txt1").value = "";

	//----------このアカウントのみ使用可------------------------
	document.getElementById("reg_account").checked = false;

	//----------写真入手元-------------------------------------
	document.getElementsByName("reg_p_obtaining")[0].checked = false;
	document.getElementsByName("reg_p_obtaining")[1].checked = true;
	document.getElementById("reg_p_obtaining_txt").value = "";

	//----------BUD_PHOTO番号----------------------------------
	document.getElementsByName("reg_bud_number")[0].checked = false;
	document.getElementsByName("reg_bud_number")[1].checked = false;
	document.getElementById("reg_bud_number_txt").value = "";

	document.getElementById("search_where").value = "";

//	document.getElementById("select_year").disabled = true;
//	document.getElementsByName("take_picture_time_name")[0].disabled = true;
//	document.getElementById("select_day").disabled = true;
	document.getElementById("reg_pub_possible_txt").disabled = true;
	document.getElementById("reg_addition_txt0").disabled = true;
	document.getElementById("reg_addition_txt1").disabled = true;
	//changed by wangtongchao 2011-11-28 begin ture to false
	document.getElementById("reg_p_obtaining_txt").disabled = false;
	//changed by wangtongchao 2011-11-28 end ture to false
	document.getElementById("reg_bud_number_txt").disabled = true;
	//-------------2008-12-15追加終了--------------------------------
}

/*
 * 関数名：change_p_hatsu
 * 関数説明：検索条件をコントロールする
 * パラメタ：obj：Ｓｅｌｅｃｔコントロール
 * 戻り値：無し
 */
function change_p_hatsu(obj)
{
	init_condition();
	var val = obj.value;
	if(val)
	{
		if(val.length > 0)
		{
			if(val == "BUDPHOTO")
			{
				document.getElementById("reg_bud_number0").style.display="inline";
				document.getElementById("reg_bud_number1").style.display="inline";
				document.getElementById("reg_bud_number_txt").style.display="inline";
				document.getElementById("search_bt").style.left = "530px";
				return;
			}
		}
	}
//	if (obj.selectedIndex == 4) {
//		document.getElementById("reg_pub_period0_lbl").style.display="inline";
//		document.getElementById("reg_pub_period1_lbl").style.display="inline";
//		document.getElementById("reg_pub_period2_lbl").style.display="inline";
//		document.getElementById("reg_pub_period3_lbl").style.display="inline";
//		document.getElementById("reg_pub_period4_lbl").style.display="inline";
//		document.getElementById("year_lbl").style.display="inline";
//		document.getElementById("reg_pub_time_lbl").style.display="inline";
//		document.getElementById("day_lbl").style.display="inline";
//		document.getElementById("search_bt").style.left = "675px";
	// 掲載可能範囲(使用範囲)
//	} else if (obj.selectedIndex == 5) {
	if (obj.selectedIndex == 4) {
		for (var i = 0; i < 3; i++)
		{
			var keyid = "reg_pub_possible_lbl" + i;
			document.getElementById(keyid).style.display="inline";
		}
		document.getElementById("reg_pub_possible_txt").style.display="inline";
		document.getElementById("search_bt").style.left = "645px";
	// 付加条件
	} else if (obj.selectedIndex == 5) {
		for (var i = 0; i < 3; i++)
		{
			var keyid = "reg_addition" + i + "_lbl";
			document.getElementById(keyid).style.display="inline";
		}
		document.getElementsByName("reg_addition_txt")[0].style.display="inline";
		document.getElementsByName("reg_addition_txt")[1].style.display="inline";
		document.getElementById("search_bt").style.left = "670px";
	// このアカウントのみ使用可
	} else if (obj.selectedIndex == 6) {
		document.getElementById("reg_account_lbl").style.display="inline";
	// 写真入手元
	} else if (obj.selectedIndex == 7) {
		document.getElementById("reg_p_obtaining0").style.display="inline";
		document.getElementById("reg_p_obtaining1").style.display="inline";
		document.getElementById("reg_p_obtaining_txt").style.display="inline";
		//document.getElementById("search_bt").style.left = "530px";
		document.getElementById("search_bt").style.left = "525px";
	// BUD_PHOTO番号
	} else if (obj.selectedIndex == 10) {
		document.getElementById("reg_bud_number0").style.display="inline";
		document.getElementById("reg_bud_number1").style.display="inline";
		document.getElementById("reg_bud_number_txt").style.display="inline";
		//document.getElementById("search_bt").style.left = "530px";
		document.getElementById("search_bt").style.left = "525px";
	} else {
		document.getElementById("lbl_search_where").style.display="inline";
	}
}

///*
// * 関数名：check_date_range
// * 関数説明：日付の範囲のチェック
// * パラメタ：para_year：年；para_month：月；para_day：日
// * 戻り値：無し
// */
//function check_date_range(para_year,para_month,para_day)
//{
//	var now_date = new Date();
//	var now_year = parseInt(now_date.getFullYear());
//	var now_month = parseInt(now_date.getMonth()) + 1;
//	var now_day = parseInt(now_date.getDate());
//
//	// 選択の「年」はシステム日付の「年」の以前の場合
//	if (now_year > parseInt(para_year))
//	{
//		return false;
//	// 選択の「年」はシステム日付の「年」の以後の場合
//	} else if (now_year < parseInt(para_year)) {
//		return true;
//	// 選択の「年」はシステム日付の「年」と同じ場合
//	} else if (now_year == parseInt(para_year)) {
//		// 選択の「月」はシステム日付の「月」の以前の場合
//		if (now_month > parseInt(para_month))
//		{
//			return false;
//		// 選択の「月」はシステム日付の「月」の以後の場合
//		} else if (now_month < parseInt(para_month)) {
//			return true;
//		// 選択の「月」はシステム日付の「月」と同じ場合
//		} else if (now_month == parseInt(para_month)) {
//			// 選択の「日」はシステム日付の「日」の以前の場合
//			if (now_day > parseInt(para_day))
//			{
//				return false;
//			} else {
//				return true;
//			}
//		}
//	}
//}

/**
 * 全角であるかをチェックします。
 *
 * @param チェックする値
 * @return ture : 全角 / flase : 全角以外
 */
function checkIsZenkaku(value)
{
	for (var i = 0; i < value.length; ++i)
	{
		var c = value.charCodeAt(i);
		//  半角カタカナは不許可
		if (c < 256 || (c >= 0xff61 && c <= 0xff9f))
		{
			return false;
		}
	}
	return true;
}

/*
 * 関数名：get_condition
 * 関数説明：検索条件の取得
 * パラメタ：無し
 * 戻り値：検索の条件
 */
function get_condition()
{
	var selobj = document.getElementById("p_hatsu");

	if (selobj)
	{
//		if (selobj.selectedIndex == 4) {
//			//掲載期間のチェック
//			var reg_pub_periods = document.getElementsByName("reg_pub_period");
//			if (reg_pub_periods)
//			{
//				// 掲載期間（To)のコンボボックス、年・月・日→YYYY-MM-DDにします。
//				// 年
//				var sel_y_obj = document.getElementById("select_year");
//				idx = sel_y_obj.selectedIndex;
//				var p_year = sel_y_obj.options[idx].value;
//
//				// 月
//				var objs_month = document.getElementsByName("take_picture_time_name");
//				if (objs_month) var obj_month = objs_month[0];
//				idx = obj_month.selectedIndex;
//				var p_month = obj_month.options[idx].value;
//
//				// 日
//				var sel_d_obj = document.getElementById("select_day");
//				idx = sel_d_obj.selectedIndex;
//				var p_day = sel_d_obj.options[idx].value;
//
//				// 条件を保存する
//				var ret_val = "";

//				if (reg_pub_periods[0].checked == true)
//				{
//					// 無期限の場合の掲載期間（To）を設定します。
//					document.getElementById("p_dto").value = "2100-01-01";
//					ret_val = reg_pub_periods[0].value + ";" + "2100-01-01";
//
//					var tmp = "";
//					tmp = reg_pub_periods[0].nextSibling.nodeValue;
//					var tmp1 = tmp.replace(/\t/g,"");
//					var tmp2 = tmp1.replace(/\s/gi,"");
//					if (c_array.length <= 0)
//					{
//						c_array[0] = tmp2 + ";" + "2100-01-01";
//					} else {
//						c_array[c_array.length-1] = tmp2 + ";" + "2100-01-01";
//					}
//					return ret_val;
//				} else if (reg_pub_periods[1].checked == true) {
//					ret_val = reg_pub_periods[1].value + ";" + document.getElementById("p_dto").value;
//
//					var tmp = "";
//					tmp = reg_pub_periods[1].nextSibling.nodeValue;
//					var tmp1 = tmp.replace(/\t/g,"");
//					var tmp2 = tmp1.replace(/\s/gi,"");
//					if (c_array.length <= 0)
//					{
//						c_array[0] = tmp2 + ";" + document.getElementById("p_dto").value;
//					} else {
//						c_array[c_array.length-1] = tmp2 + ";" + document.getElementById("p_dto").value;
//					}
//					return ret_val;
//				} else if (reg_pub_periods[2].checked == true) {
//					ret_val = reg_pub_periods[2].value + ";" + document.getElementById("p_dto").value;
//
//					var tmp = "";
//					tmp = reg_pub_periods[2].nextSibling.nodeValue;
//					var tmp1 = tmp.replace(/\t/g,"");
//					var tmp2 = tmp1.replace(/\s/gi,"");
//					if (c_array.length <= 0)
//					{
//						c_array[0] = tmp2 + ";" + document.getElementById("p_dto").value;
//					} else {
//						c_array[c_array.length-1] = tmp2 + ";" + document.getElementById("p_dto").value;
//					}
//					return ret_val;
//				} else if (reg_pub_periods[3].checked == true) {
//					ret_val = reg_pub_periods[3].value + ";" + document.getElementById("p_dto").value;
//
//					var tmp = "";
//					tmp = reg_pub_periods[3].nextSibling.nodeValue;
//					var tmp1 = tmp.replace(/\t/g,"");
//					var tmp2 = tmp1.replace(/\s/gi,"");
//					if (c_array.length <= 0)
//					{
//						c_array[0] = tmp2 + ";" + document.getElementById("p_dto").value;
//					} else {
//						c_array[c_array.length-1] = tmp2 + ";" + document.getElementById("p_dto").value;
//					}
//					return ret_val;
//				} else if (reg_pub_periods[4].checked == true) {
//					// 年月日をチェックします。
//					// 無期限の場合は、日付のチェックを行いません。
//					if(check_date(p_year, p_month, p_day) != 0)
//					{
//						alert("正しい日付ではありません。");
//						sel_y_obj.focus();
//						return "-1";
//					}
//					if(check_date_range(p_year, p_month, p_day) != true)
//					{
//						alert("正しい日付範囲ではありません。");
//						sel_y_obj.focus();
//						return "-1";
//					}
//					// 年月日を設定します。
//					var tdt = new Date(p_year, p_month - 1, p_day);
//					// 掲載期間（To）を設定します。
//					disp_to = dateFormat.format(tdt);
//					document.getElementById("p_dto").value = disp_to;
//					ret_val = reg_pub_periods[4].value + ";" + disp_to;
//
//					var tmp = "";
//					tmp = reg_pub_periods[4].nextSibling.nodeValue;
//					var tmp1 = tmp.replace(/\t/g,"");
//					var tmp2 = tmp1.replace(/\s/gi,"");
//					if (c_array.length <= 0)
//					{
//						c_array[0] = tmp2 + ";" + disp_to;
//					} else {
//						c_array[c_array.length-1] = tmp2 + ";" + disp_to;
//					}
//					return ret_val;
//				} else {
//					alert('掲載期間を選択してください。\r\n');
//					reg_pub_periods[0].focus();
//					return "-1";
//				}
//			}
		// 掲載可能範囲(使用範囲)
//		} else if (selobj.selectedIndex == 5) {
		var val = selobj.value;
		if (selobj.selectedIndex == 4) {
			//掲載可能範囲のチェック
			var reg_pub_possibles = document.getElementsByName("reg_pub_possible");
			if (reg_pub_possibles)
			{
				// トラベルコムのみ
				if (reg_pub_possibles[0].checked == true)
				{
					var tmp = "";
					tmp = reg_pub_possibles[0].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2;
					} else {
						c_array[c_array.length-1] = tmp2;
					}
					return reg_pub_possibles[0].value;
				// 外部出稿可
				} else if (reg_pub_possibles[1].checked == true) {
					var tmp = "";
					tmp = reg_pub_possibles[1].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2;
					} else {
						c_array[c_array.length-1] = tmp2;
					}
					return reg_pub_possibles[1].value;
				// 外部出稿条件付きを選択した場合
				} else if (reg_pub_possibles[2].checked == true) {
					var obj_txt = document.getElementById("reg_pub_possible_txt");
					// 条件は未入力の場合
					if (trim(obj_txt.value).length <= 0)
					{
						alert("外部出稿条件付きを入力してください。");
						obj_txt.focus();
						return "-1";
					}
					// 条件を保存する
					var ret_val = "";
					ret_val = reg_pub_possibles[2].value + ";" + obj_txt.value;

					var tmp = "";
					tmp = reg_pub_possibles[2].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2 + ";" + obj_txt.value;
					} else {
						c_array[c_array.length-1] = tmp2 + ";" + obj_txt.value;
					}
					return ret_val;
				} else {
					alert('掲載可能範囲を選択してください。\r\n');
					reg_pub_possibles[0].focus();
					return "-1";
				}
			}
		// 付加条件
		} else if (selobj.selectedIndex == 5) {
			// 付加条件のチェック
			var reg_adds = document.getElementsByName("reg_addition");
			var msg = "";
			if (reg_adds)
			{
				// 付加条件の「要クレジット」を選択した場合
				if (reg_adds[1].checked == true)
				{
					var obj_txt = document.getElementById("reg_addition_txt0");
					// 付加条件を入力しない場合、エラーメッセージを出力する
					if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
					{
						alert("要クレジットを入力してください。");
						obj_txt.focus();
						return "-1";
					}

					var tmp = "";
					tmp = reg_adds[1].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2 + ";" + obj_txt.value;
					} else {
						c_array[c_array.length-1] = tmp2 + ";" + obj_txt.value;
					}
					return obj_txt.value;
				// 付加条件の「要使用許可」を選択した場合
				} else if (reg_adds[2].checked == true) {
					var obj_txt = document.getElementById("reg_addition_txt1");
					// 付加条件を入力しない場合、エラーメッセージを出力する
					if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
					{
						alert("要使用許可を入力してください。");
						obj_txt.focus();
						return "-1";
					}

					var tmp = "";
					tmp = reg_adds[2].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2 + ";" + obj_txt.value;
					} else {
						c_array[c_array.length-1] = tmp2 + ";" + obj_txt.value;
					}
					return obj_txt.value;
				// 付加条件の「なし」を選択した場合
				} else if (reg_adds[0].checked == true) {
					if (c_array.length <= 0)
					{
						c_array[0] = "なし";
					} else {
						c_array[c_array.length-1] = "なし";
					}
					return "";
				} else {
					alert("付加条件を選択してください。");
					reg_adds[0].focus();
					return "-1";
				}
			}
		// このアカウントのみ使用可
		} else if (selobj.selectedIndex == 6) {
			var reg_account = document.getElementById("reg_account");
			if (reg_account.checked == true)
			{
				var tmp = "";
				tmp = reg_account.nextSibling.nodeValue;
				var tmp1 = tmp.replace(/\t/g,"");
				var tmp2 = tmp1.replace(/\s/gi,"");
				if (c_array.length <= 0)
				{
					c_array[0] = tmp2;
				} else {
					c_array[c_array.length-1] = tmp2;
				}
				return "_1";
			}
			if (reg_account.checked == false) return "_0";
		// 写真入手元
		} else if (selobj.selectedIndex == 7) {
			//写真入手元のチェック
			var reg_p_obtainings = document.getElementsByName("reg_p_obtaining");
			if (reg_p_obtainings)
			{
				if (reg_p_obtainings[0].checked == true)
				{
					var tmp = "";
					tmp = reg_p_obtainings[0].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2;
					} else {
						c_array[c_array.length-1] = tmp2;
					}
					return reg_p_obtainings[0].value;
				// 写真入手元の「その他」を選択した場合
				} else if (reg_p_obtainings[1].checked == true) {
					var obj_txt = document.getElementById("reg_p_obtaining_txt");
					// 写真入手元を入力しない場合、エラーメッセージを出力する
					if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
					{
						alert("写真入手元を入力してください。");
						obj_txt.focus();
						return "-1";
					}
					// 条件を保存する
					var ret_val = "";
					ret_val = reg_p_obtainings[1].value + ";" + obj_txt.value;
					//deleted by wangtongchao 2011-11-28 begin
					//var tmp = "";
					//tmp = reg_p_obtainings[1].nextSibling.nodeValue;
					//var tmp1 = tmp.replace(/\t/g,"");
					//var tmp2 = tmp1.replace(/\s/gi,"");
					//deleted by wangtongchao 2011-11-28 end
					if (c_array.length <= 0)
					{
						//changed by wangtongchao 2011-11-28 begin
						//c_array[0] = tmp2 + ";" + obj_txt.value;
						c_array[0] = obj_txt.value;
						//changed by wangtongchao 2011-11-28 end
					} else {
						//changed by wangtongchao 2011-11-28 begin
						//c_array[c_array.length-1] = tmp2 + ";" + obj_txt.value;
						c_array[c_array.length-1] = obj_txt.value;
						//changed by wangtongchao 2011-11-28 end
					}
					return ret_val;
				} else {
					alert('写真入手元を選択してください。\r\n');
					reg_p_obtainings[0].focus();
					return "-1";
				}
			}
		// BUD_PHOTO番号
		} else if (selobj.selectedIndex == 10 || val == "BUDPHOTO") {
			// BUD_PHOTO番号のチェック
			var reg_bud_numbers = document.getElementsByName("reg_bud_number");
			if (reg_bud_numbers)
			{
				if (reg_bud_numbers[0].checked == true)
				{
					var obj_txt = document.getElementById("reg_bud_number_txt");
					// BUD_PHOTO番号を入力しない場合、エラーメッセージを出力する
					if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
					{
						alert("BUD_PHOTO番号を入力してください。");
						obj_txt.focus();
						return "-1";
					}
					var obj_red = document.getElementById("reg_bud_number_txt");
					if (obj_red.value.length > 0)
					{
						//全角漢字をチェック
						if (checkIsZenkaku(obj_red.value))
						{
							alert("BUD_PHOTO番号には半角を入力してください。");
							obj_red.focus();
							return "-1";
						}
					}
					var tmp = "";
					tmp = reg_bud_numbers[0].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2 + ";" + obj_txt.value;
					} else {
						c_array[c_array.length-1] = tmp2 + ";" + obj_txt.value;
					}
					return obj_txt.value;
				} else if (reg_bud_numbers[1].checked == true) {
					var tmp = "";
					tmp = reg_bud_numbers[1].nextSibling.nodeValue;
					var tmp1 = tmp.replace(/\t/g,"");
					var tmp2 = tmp1.replace(/\s/gi,"");
					if (c_array.length <= 0)
					{
						c_array[0] = tmp2;
					} else {
						c_array[c_array.length-1] = tmp2;
					}
					return "_";
				} else {
					alert("BUD_PHOTO番号の種類を選択してください。");
					reg_bud_numbers[0].focus();
					return "-1";
				}
			}
		} else {
			// 検索内容を取得する
			var search_obj = document.getElementById("search_where");
			// 書式のチェック
			var ok_flg = check_search_condition(search_obj.value);
			if (ok_flg == false) return "-1";
			else
			{
				if (c_array.length <= 0)
				{
					c_array[0] = search_obj.value;
				} else {
					c_array[c_array.length-1] = search_obj.value;
				}
				return search_obj.value;
			}
		}
	} else {
		// 検索内容を取得する
		var search_obj = document.getElementById("search_where");
		// 書式のチェック
		var ok_flg = check_search_condition(search_obj.value);
		if (ok_flg == false) return "-1";
		else
		{
			if (c_array.length <= 0)
			{
				c_array[0] = search_obj.value;
			} else {
				c_array[c_array.length-1] = search_obj.value;
			}
			return search_obj.value;
		}
	}
}

/*
 * 関数名：go_search
 * 関数説明：イメージを検索する「検索結果画面へ遷移する」
 * パラメタ：無し
 * 戻り値：無し
 */
function go_search()
{
	c_array.splice(0,c_array.length);
	// ピックアップ用のURLを作成します。
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + uid;

	// クッキーを取得します。
	var idstr = getCookie(ck_id);

	// URLを決定します。
	var url2 = "pickup_ichiran1.php?p_pickupid=" + idstr;

	// 検索結果用のURLを作成します。
	var url = "./search_result.php";
	var selobj = document.getElementById("p_hatsu");
	var val = selobj.value;

	// 検索内容を入力した場合
	var search_value = get_condition();

	if (search_value == "-1") return false;

	if (trim(search_value) != "")
	{
		if (selobj)
		{
			if (parseInt(selobj.selectedIndex) == 5)
			{
				var objs = document.getElementsByName("reg_addition");
				if (objs[1].checked == true)
				{
					url = url + "?selIndex=51";
				}
				if (objs[2].checked == true)
				{
					url = url + "?selIndex=52";
				}
			} else {
				if(val == "BUDPHOTO")
				{
					url = url + "?selIndex=10";
				} else if(val == "CUSTOMER") {
					url = url + "?selIndex=11";
				} else {
					url = url + "?selIndex="+selobj.selectedIndex;
				}
			}
			url = url + "&search_value=" + encodeURIComponent(search_value);
		} else {
			url = url + "?search_value=" + encodeURIComponent(search_value);
		}

	}

	if (c_array.length > 0)
	{
		var tmp1 = "";
		for (var i = 0; i < c_array.length; i++)
		{
			if (c_array[i] != null && c_array[i] != "")
			{
				if (c_array[i].length > 0)
				{
					tmp1 = tmp1 + c_array[i] + " ";
				}
			}
		}
		if (tmp1.length > 0)
		{
			if (url == "./search_result.php")
			{
				url = url + "?c_array=" + encodeURIComponent(tmp1);
			} else {
				url = url + "&c_array=" + encodeURIComponent(tmp1);
			}
			setCookie("c_array_ck",encodeURIComponent(tmp1));
		} else {
			setCookie("c_array_ck","");
		}
	}

	// 詳細検索の条件を設定する(ここから)
	// 「何で検索しますか？」を選択した場合
	// 詳細検索の条件
	var syousai_content = "";
//	// 撮影時期(季節)の検索条件を構築する
//	var rad_kisetu = new Array();
//	rad_kisetu = document.getElementsByName("rad_kisetu");
//	if (rad_kisetu)
//	{
//		var s_rad_kisetu = "";
//		if (rad_kisetu[0].checked == true)
//		{
//			s_rad_kisetu = "春";
//		} else if (rad_kisetu[1].checked == true) {
//			s_rad_kisetu = "夏";
//		} else if (rad_kisetu[2].checked == true) {
//			s_rad_kisetu = "秋";
//		} else if (rad_kisetu[3].checked == true) {
//			s_rad_kisetu = "冬";
//		}
//		if (s_rad_kisetu != "" && s_rad_kisetu != null)
//		{
//			syousai_content = syousai_content + s_rad_kisetu;
//		}
//	}

//	// 撮影時期(月)の検索条件を構築する
//	var objs = document.getElementsByName("take_picture_time_name");
//	if (objs)
//	{
//		var obj = objs[1];
//		if (obj.selectedIndex > 0)
//		{
//			if (syousai_content != "" && syousai_content != null)
//			{
//				syousai_content = syousai_content + " " + obj.options[obj.selectedIndex].text;
//			} else {
//				syousai_content = obj.options[obj.selectedIndex].text;
//			}
//		}
//	}

	// 登録分類の検索条件を構築する（ここから）
	// 方面
	var obj = document.getElementById("p_direction_id");
	if (obj)
	{
		if (obj.selectedIndex > 0)
		{
			if (syousai_content != "" && syousai_content != null)
			{
				syousai_content = syousai_content + " " + obj.options[obj.selectedIndex].text;
			} else {
				syousai_content = obj.options[obj.selectedIndex].text;
			}
		}
	}
	// 国・都道府県
	var obj = document.getElementById("p_country_prefecture_id");
	if (obj)
	{
		if (obj.selectedIndex > 0)
		{
			if (syousai_content != "" && syousai_content != null)
			{
				syousai_content = syousai_content + " " + obj.options[obj.selectedIndex].text;
			} else {
				syousai_content = obj.options[obj.selectedIndex].text;
			}
		}
	}
//	// 地名
//	var obj = document.getElementById("p_place_id");
//	if (obj)
//	{
//		if (obj.selectedIndex > 0)
//		{
//			if (syousai_content != "" && syousai_content != null)
//			{
//				syousai_content = syousai_content + " " + obj.options[obj.selectedIndex].text;
//			} else {
//				syousai_content = obj.options[obj.selectedIndex].text;
//			}
//		}
//	}
	// 登録分類の検索条件を構築する（ここまで）

	// カテゴリの検索条件を構築する（ここから）
	// カテゴリを選択した場合、必ずに「カテゴリーで絞り込む」或は「カテゴリーを除外」を選択する
	var checkBoxs = new Array();
	checkBoxs = document.getElementsByTagName("input");
	for(var i=0; i<checkBoxs.length; i++)
	{
		if(checkBoxs[i].type == "checkbox" && checkBoxs[i].id != "reg_account")
		{
			if (checkBoxs[i].checked == true)
			{
				//カテゴリーオプションのチェック
				var category_include = document.getElementsByName("category_include");
				if (category_include)
				{
					if (category_include[0].checked == false && category_include[1].checked == false)
					{
						alert('カテゴリーオプションを選択してください。\r\n');
						category_include[0].focus();
						return false;
					}
				}
			}
		}
	}

	var obj_rad = document.getElementsByName("category_include");

	if (obj_rad[0].checked == true)
	{
		// カテゴリーをスペース区切りの文字列（Keyword_str）へ変換します。
		var keyword_str = "";
		var tags = document.body.getElementsByTagName("*");
		for(var i = 0 ; i < tags.length ; i++)
		{
			var grp = tags[i].getAttribute("category");
			if(grp != undefined)
			{
				if (tags[i].checked == true)
				{
					if (keyword_str.length != 0)
					{
						keyword_str += " ";
					}

					keyword_str += tags[i].value;
				}
			}
		}
		if (syousai_content != "" && syousai_content != null)
		{
			syousai_content = syousai_content + " " + keyword_str;
		} else {
			syousai_content = keyword_str;
		}
	// カテゴリーを除外
	} else if(obj_rad[1].checked == true) {
		// カテゴリーをスペース区切りの文字列（Keyword_str）へ変換します。
		var keyword_str = "";
		var tags = document.body.getElementsByTagName("*");
		for(var i = 0 ; i < tags.length ; i++)
		{
			var grp = tags[i].getAttribute("category");
			if(grp != undefined)
			{
				if (tags[i].checked == true)
				{
					if (keyword_str.length != 0)
					{
						keyword_str += " -";
					}

					keyword_str += tags[i].value;
				}
			}
		}
		if (syousai_content != "" && syousai_content != null)
		{
			syousai_content = syousai_content + " -" + keyword_str;
		} else {
			syousai_content = " -" + keyword_str;
		}
	}
	// カテゴリの検索条件を構築する（ここまで）

	if (trim(syousai_content).length > 0 && trim(search_value).length > 0)
	{
		url = url + "&syousai_content=" + encodeURIComponent(syousai_content);
	} else if (trim(syousai_content).length > 0 && trim(search_value).length <= 0) {
		if (selobj)
		{
			url = url + "?selIndex="+selobj.selectedIndex;
			url = url + "&syousai_content=" + encodeURIComponent(syousai_content);
		} else {
			url = url + "?syousai_content=" + encodeURIComponent(syousai_content);
		}
		setCookie("syousai_content_ck",encodeURIComponent(syousai_content));
	} else {
		setCookie("syousai_content_ck","");
	}

	var objs = document.getElementsByName('publishing_include');
	if(objs)
	{
		//「3ヵ月未満を除外」
		if(objs[1].checked)
		{
			if (trim(syousai_content).length <= 0 && trim(search_value).length <= 0)
			{
				url = url + "?p_kikan=3";
			} else {
				url = url + "&p_kikan=3";
			}
		//「6ヵ月未満を除外」
		} else if (objs[2].checked) {
			if (trim(syousai_content).length <= 0 && trim(search_value).length <= 0)
			{
				url = url + "?p_kikan=6";
			} else {
				url = url + "&p_kikan=6";
			}
		//「期限のないもの」
		} else if (objs[3].checked) {
			if (trim(syousai_content).length <= 0 && trim(search_value).length <= 0)
			{
				url = url + "?p_kikan=9";
			} else {
				url = url + "&p_kikan=9";
			}
		}
	}
	// 詳細検索の条件を設定する(ここまで)

	// クーキーのクリアー
	clearCookie("pickup_chk");
	clearCookie("change_value");
	clearCookie("image_information");
	clearCookie("bt_cnt");
	clearCookie("image_search");
	clearCookie("images_result");
	clearCookie("selIndex");
	clearCookie("search_value");
	clearCookie("submit_url");

	$("dl#detail_search dt.bt_search").click();
	if (detail_search_bt > 0)
	{
		changedetail_search();
	}
	var obj_s_w = document.getElementById('search_where');
	if (obj_s_w)
	{
		setCookie("search_where_save",document.getElementById('search_where').value);
	}

	//------------------------------------------------------------------------------------------------------
	/*
	* 修正日付：2008/12/26
	* 修正原因：スペードは遅いですから、「検索」ボタンを押すと、検索ボタンなどを無効になる
	* 担当者　：于彭波
	*/
	var strhtml = "<img src='parts/search_bt_off.gif' alt='検索' onclick='return false;'/>";
	//strhtml = strhtml + "<a href='#'><img src='parts/bt_re_set.gif' alt='リセット'  onclick='return false;' /></a>";
	//strhtml = strhtml + "<span><a href='#' class='bt_syousai' onclick='return false;'>詳細条件を追加する</a></span>";

	var obj = window.parent.frames[1].document.getElementsByTagName('p');
	$("p#search_bt").html(strhtml);

	var objs=document.getElementsByTagName("input");
	for(var i=0;i<objs.length;i++)
	{
		objs[i].disabled = true;
	}

	var objs=document.getElementsByTagName("select");
	for(var i=0;i<objs.length;i++)
	{
		objs[i].disabled = true;
	}

	//2008/01/19 仕様変更 下記のURLより行う****************************************
	//http://www3.bud-international.co.jp/hei/cms/090119_photo_db/sample_index.html
	var obj_frm = window.parent.frames[3];
	if(obj_frm)
	{
		var obj = obj_frm.document.getElementById('div_pickup_result');
		if(obj)
		{
			var obj_p = obj.getElementsByTagName("p");
			if(obj_p)
			{
				if(obj_p[0])
				{
					obj_p[0].innerHTML = "検索結果：";
					obj_p[0].className = 'retrieving_it';
					//obj_p[0].innerHTML = "検索結果：検索中";
				}
			}
		}
	}
	//******************************************************************************
	//------------------------------------------------------------------------------------------------------

	parent.middle2.location = url2;
	parent.bottom.location = url;
}

///*
// * 関数名：change_kikan
// * 関数説明：掲載期間の設定
// * パラメタ：th:コントロール
// * 戻り値：無し
// */
//function change_kikan(th)
//{
//	// 掲載期間の「月」を取得する
//	//var objs_month = document.getElementsByName("take_picture_time_name");
//	obj_month = document.getElementById('take_picture_time_id');
//	//if (objs_month) var obj_month = objs_month[0];
//	// 掲載期間の「日付指定」を選択した時
//	if (th.value == 'shitei')
//	{
//		// 日付の「年」のインデックスを設定する
//		document.getElementById("select_year").selectedIndex = 0;
//		// 日付の「日」のインデックスを設定する
//		document.getElementById("select_day").selectedIndex = 0;
//		// 日付の「月」のインデックスを設定する
//		if (obj_month) obj_month.selectedIndex = 0;
//
//		// 日付の「年」は有効になる
//		document.getElementById("select_year").disabled = false;
//		// 日付の「日」は有効になる
//		document.getElementById("select_day").disabled = false;
//		// 日付の「月」は有効になる
//		if (obj_month) obj_month.disabled = false;
//		return ;
//	}
//
//	// システムの日付を取得する
//	var fdt = new Date();
//	// システム日付から、年を取得する
//	var year = fdt.getYear();
//	// システム日付から、月を取得する
//	var mon = fdt.getMonth();
//	// システム日付から、日を取得する
//	var day = fdt.getDate();
//	// 掲載期間の「無期限」を選択した場合
//	if (th.value == 'mukigen')
//	{
//		year += 100;
//	}
//	// 掲載期間の「三か月」を選択した場合
//	else if (th.value == 'sankagetu')
//	{
//		mon += 3;
//	}
//	// 掲載期間の「六か月」を選択した場合
//	else if (th.value == 'hantoshi')
//	{
//		mon += 6;
//	}
//	// 掲載期間の「一年間」を選択した場合
//	else if (th.value == 'ichinen')
//	{
//		year += 1;
//	}
//	if (year < 1900)
//	{
//		year += 1900;
//	}
//
//	// 設定後の日付をフォーマット
//	var tdt = new Date(year, mon, day);
//
//	// 掲載期間の「無期限」以外を選択した場合
//	if (th.value != 'mukigen')
//	{
//		// 年を取得する
//		yr = parseInt(tdt.getYear());
//		if (yr < 1900)
//		{
//			yr += 1900;
//		}
//		// 年を設定する
//		document.getElementById("select_year").value = yr;
//		// 月を設定する
//		if (obj_month) obj_month.value = tdt.getMonth() + 1;
//		// 日を設定する
//		document.getElementById("select_day").value = tdt.getDate();
//	}
//	else
//	{
//		// 年を設定する
//		document.getElementById("select_year").selectedIndex = 0;
//		// 月を設定する
//		if (obj_month) obj_month.selectedIndex = 0;
//		// 日を設定する
//		document.getElementById("select_day").selectedIndex = 0;
//	}
//
//	// 掲載期間（To）を設定する。
//	disp_to =  dateFormat.format(tdt);
//	document.getElementById("p_dto").value = disp_to;
//
//	// 年を無効になる
//	document.getElementById("select_year").disabled = true;
//	// 日を無効になる
//	document.getElementById("select_day").disabled = true;
//	// 月を無効になる
//	if (obj_month) obj_month.disabled = true;
//}

/*
 * 関数名：change_range_radio
 * 関数説明：掲載可能範囲の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_range_radio(obj)
{
	var key = "reg_pub_possible_txt";
	var obj_txt = document.getElementById(key);

	// 掲載可能範囲の「外部出稿条件付き」を選択した場合
	if (parseInt(obj.value) == 3)
	{
		// 掲載可能範囲の「外部出稿条件付き」テキストボックスは有効になる
		if (obj_txt)
		{
			obj_txt.value="";
			obj_txt.disabled = false;
		}
	// 掲載可能範囲の「外部出稿条件付き」以外を選択した場合
	} else {
		// 掲載可能範囲の「外部出稿条件付き」テキストボックスは無効になる
		if (obj_txt)
		{
			obj_txt.disabled  = true;
			obj_txt.value="";
		}
	}
}

/*
 * 関数名：change_obtaining_radio
 * 関数説明：写真入手元の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_obtaining_radio(obj)
{
	var key = "reg_p_obtaining_txt";
	var obj_txt = document.getElementById(key);

	// 写真入手元の「その他」を選択した場合
	if (parseInt(obj.value) == 2)
	{
		// 写真入手元の「その他」テキストボックスは有効になる
		if (obj_txt)
		{
			obj_txt.value="";
			obj_txt.disabled = false;
		}
	// 写真入手元の「アマナ」を選択した場合
	} else {
		// 写真入手元の「その他」テキストボックスは無効になる
		if (obj_txt)
		{
			obj_txt.disabled  = true;
			obj_txt.value="";
		}
	}
}

/*
 * 関数名：change_bud_number_radio
 * 関数説明：BUD_PHOTO番号の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_bud_number_radio(obj)
{
	var key = "reg_bud_number_txt";
	var obj_txt = document.getElementById(key);

	// BUD_PHOTO番号の「ある」を選択した場合
	if (parseInt(obj.value) == 1)
	{
		// BUD_PHOTO番号の「ある」テキストボックスは有効になる
		if (obj_txt)
		{
			obj_txt.disabled = false;
			obj_txt.value="";
		}
	// BUD_PHOTO番号の「なし」を選択した場合
	} else {
		// BUD_PHOTO番号の「ある」テキストボックスは無効になる
		if (obj_txt)
		{
			obj_txt.disabled  = true;
			obj_txt.value="";
		}
	}
}

/*
 * 関数名：change_reg_addition
 * 関数説明：付加条件の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_reg_addition(obj)
{
	var key = "reg_addition_txt";
	// 付加条件のテキストボックスを取得する
	var objs_txt = document.getElementsByName(key);

	// 選択した付加条件の値を取得する
	var indx1 = parseInt(obj.value);
	var indx2 = null;

	// 付加条件の「要使用許可」を選択した場合
	if (indx1 == 2)
	{
		objs_txt[0].disabled = true;
		objs_txt[1].disabled = true;
		return;
	}

	// 付加条件の「要使用許可」を選択した場合
	if (indx1 == 1) indx2 = 0;
	// 付加条件の「要クレジット」を選択した場合
	else indx2 = 1;

	// 選択した付加条件の対応テキストボックスを有効になる
	if (objs_txt && objs_txt[indx1])
	{
		objs_txt[indx1].disabled = false;
		objs_txt[indx1].value="";
	}

	// 選択しない付加条件の対応テキストボックスを無効になる
	if (objs_txt && objs_txt[indx2])
	{
		objs_txt[indx2].disabled = true;
		objs_txt[indx2].value="";
	}
}

/*
 * 関数名：clear_contents
 * 関数説明：条件のクリア
 * パラメタ：無し
 * 戻り値：無し
 */
function clear_contents()
{
	var objs = new Array();

	objs = document.getElementsByName("category_include");
	objs[0].checked = true;
	objs[1].checked = false;

	var tags = document.body.getElementsByTagName("*");
	for(var i = 0 ; i < tags.length ; i++)
	{
		var grp = tags[i].getAttribute("category");
		if(grp != undefined)
		{
			tags[i].checked = false;
		}
	}
}

//-------------2008-12-14 追加開始-----------------
/*
 * 関数名：clear_all_contents
 * 関数説明：条件のクリア
 * パラメタ：無し
 * 戻り値：無し
 */
function clear_all_contents()
{
	//-------------2008-12-15追加開始--------------------------------
	//----------掲載可能範囲------------------------------------
	document.getElementById("reg_pub_possible0").checked = false;
	document.getElementById("reg_pub_possible1").checked = false;
	document.getElementById("reg_pub_possible2").checked = false;
	document.getElementById("reg_pub_possible_txt").value = "";

//	//----------掲載期間----------------------------------------
//	document.getElementById("reg_pub_period0").checked = false;
//	document.getElementById("reg_pub_period1").checked = false;
//	document.getElementById("reg_pub_period2").checked = false;
//	document.getElementById("reg_pub_period3").checked = false;
//	document.getElementById("reg_pub_period4").checked = false;
//	document.getElementById("select_year").selectedIndex = 0;
//	document.getElementsByName("take_picture_time_name")[0].selectedIndex = 0;
//	document.getElementById("select_day").selectedIndex = 0;

	//----------追加条件----------------------------------------
	document.getElementById("reg_addition0").checked = false;
	document.getElementById("reg_addition1").checked = false;
	document.getElementById("reg_addition2").checked = false;
	document.getElementById("reg_addition_txt0").value = "";
	document.getElementById("reg_addition_txt1").value = "";

	//----------このアカウントのみ使用可------------------------
	document.getElementById("reg_account").checked = false;

	//----------写真入手元-------------------------------------
	document.getElementsByName("reg_p_obtaining")[0].checked = false;
	document.getElementsByName("reg_p_obtaining")[1].checked = true;//wangtongchao
	document.getElementById("reg_p_obtaining_txt").value = "";

	//----------BUD_PHOTO番号----------------------------------
	document.getElementsByName("reg_bud_number")[0].checked = false;
	document.getElementsByName("reg_bud_number")[1].checked = false;
	document.getElementById("reg_bud_number_txt").value = "";

	document.getElementById("search_where").value = "";

//	document.getElementById("select_year").disabled = true;
//	document.getElementsByName("take_picture_time_name")[0].disabled = true;
//	document.getElementById("select_day").disabled = true;
	document.getElementById("reg_pub_possible_txt").disabled = true;
	document.getElementById("reg_addition_txt0").disabled = true;
	document.getElementById("reg_addition_txt1").disabled = true;
	document.getElementById("reg_p_obtaining_txt").disabled = false;//wangtongchao
	document.getElementById("reg_bud_number_txt").disabled = true;

	var obj_p_hatsu = document.getElementById("p_hatsu");
	if (obj_p_hatsu)
	{
		obj_p_hatsu.selectedIndex = 0;
		change_p_hatsu(obj_p_hatsu);
	}

	//登録分類
	document.getElementById("p_classification_id").selectedIndex = 0;
	document.getElementById("p_direction_id").selectedIndex = 0;
	document.getElementById("p_country_prefecture_id").selectedIndex = 0;
//	document.getElementById("p_place_id").selectedIndex = 0;
	//-------------2008-12-15追加終了--------------------------------

	clear_contents();

	objs = document.getElementsByName("rad_kisetu");
	for (var i = 0; i < objs.length; i++)
	{
		objs[i].checked = false;
	}
	document.getElementById("p_classification_id").selectedIndex = 0;
	document.getElementById("p_direction_id").selectedIndex = 0;
	document.getElementById("p_country_prefecture_id").selectedIndex = 0;
//	document.getElementById("p_place_id").selectedIndex = 0;
//	document.getElementsByName("take_picture_time_name")[1].selectedIndex = 0;
	document.getElementsByName("publishing_include")[0].checked = true;
}
//-------------2008-12-14 追加終了-----------------

//Safariで検索条件を保存するために追加する(開始）
function save_search_conidition(eventTage,obj)
{
	var event = eventTage||window.event;
	if(event.keyCode==13)
	{
		var obj_p = document.getElementById('p_hatsu');
		if (obj_p)
		{
			var tmp_key = "A"+document.getElementById('p_hatsu').selectedIndex;
			var i_index = document.getElementById('p_hatsu').selectedIndex;
			if (parseInt(i_index) == 6)
			{
				var objs = document.getElementsByName('reg_addition');
				if (objs[1].checked)
				{
					setCookie('reg_addition_index',"1");
					setCookie('reg_addition0',obj.value);
				} else if (objs[2].checked) {
					setCookie('reg_addition_index',"2");
					setCookie('reg_addition1',obj.value);
				}
			}
			setCookie(tmp_key,obj.value);
			setCookie('p_hatsu_index',document.getElementById('p_hatsu').selectedIndex);
		} else {
			setCookie("search_where_save",obj.value);
		}
		go_search();
	}
}
//Safariで検索条件を保存するために追加する(終了）

function init()
{
	//ConnectedSelect(['p_classification_id', 'p_direction_id', 'p_country_prefecture_id', 'p_place_id']);
	ConnectedSelect(['p_classification_id', 'p_direction_id', 'p_country_prefecture_id']);
	//var obj_frame = top.document.getElementById('iframe_middle1');
	//if (obj_frame) obj_frame.style.height = 60;

	//Safariで検索条件を保存するために追加する(開始）
	var tmp_hatsu_index = getCookie('p_hatsu_index');
	if (tmp_hatsu_index != null && tmp_hatsu_index.length > 0)
	{
		var int_index = parseInt(tmp_hatsu_index);
		if (int_index >= 0)
		{
			var tmp_key = "A"+int_index;
			if (int_index == 0 || int_index == 1 ||
			    int_index == 2 || int_index == 3 ||
			    int_index == 8 || int_index == 9 ||
			    int_index == 11 || int_index == 12 ||
			    int_index == 13 || int_index == 14)
			{
				document.getElementById('search_where').value = getCookie(tmp_key);
				document.getElementById('p_hatsu').selectedIndex = int_index;
				change_p_hatsu(document.getElementById('p_hatsu'));
			} else if (int_index == 4) {
				var objs = document.getElementsByName('reg_pub_possible');
				objs[2].checked = true;
				document.getElementById('reg_pub_possible_txt').disabled = false;
				document.getElementById('reg_pub_possible_txt').value = getCookie(tmp_key);
				document.getElementById('p_hatsu').selectedIndex = 4;
				change_p_hatsu(document.getElementById('p_hatsu'));
			} else if (int_index == 5) {
				var r_a_i = getCookie('reg_addition_index');
				if (r_a_i != null)
				{
					var objs = document.getElementsByName('reg_addition');
					var int_r_a_i = parseInt(r_a_i);
					if (int_r_a_i == 1)
					{
						objs[1].checked = true;
						document.getElementById('reg_addition_txt0').value = getCookie('reg_addition0');
						document.getElementById('p_hatsu').selectedIndex = 5;
						change_p_hatsu(document.getElementById('p_hatsu'));
						document.getElementById('reg_addition_txt0').disabled = false;
						setCookie('reg_addition_index',"");
						setCookie('reg_addition0',"");
					} else if (int_r_a_i == 2) {
						objs[2].checked = true;
						document.getElementById('reg_addition_txt1').value = getCookie('reg_addition1');
						document.getElementById('p_hatsu').selectedIndex = 5;
						change_p_hatsu(document.getElementById('p_hatsu'));
						document.getElementById('reg_addition_txt1').disabled = false;
						setCookie('reg_addition_index',"");
						setCookie('reg_addition1',"");
					}
				}
			} else if (int_index == 7) {
				var objs = document.getElementsByName('reg_p_obtaining');
				objs[1].checked = true;
				document.getElementById('reg_p_obtaining_txt').disabled = false;
				document.getElementById('reg_p_obtaining_txt').value = getCookie(tmp_key);
				document.getElementById('p_hatsu').selectedIndex = 7;
				change_p_hatsu(document.getElementById('p_hatsu'));
			} else if (int_index == 10) {
				var objs = document.getElementsByName('reg_bud_number');
				objs[0].checked = true;
				document.getElementById('reg_bud_number_txt').disabled = false;
				document.getElementById('reg_bud_number_txt').value = getCookie(tmp_key);
				document.getElementById('p_hatsu').selectedIndex = 10;
				change_p_hatsu(document.getElementById('p_hatsu'));
			}

			setCookie(tmp_key,'');
			setCookie('p_hatsu_index','-1');
		}
	}

	var tmpvalue = getCookie("search_where_save");
	if (tmpvalue != null)
	{
		if (tmpvalue.length > 0)
		{
			document.getElementById('search_where').value = tmpvalue;
		}
	}

	var radobj = document.getElementsByName("category_include");
	if (radobj)
	{
		radobj[0].checked = true;
	}
	//Safariで検索条件を保存するために追加する(終了）
}

window.onload = init;
//-->
</script>
<!-- javascript ここまで -->
</head>
<body>
<div id="zentai">
<div id="anchor_box"></div>
	<!-- メインコンテンツ　ここから -->
	<form method="post" name="searchmenu" id="searchmenu" >
	<div id="contents">
		<div id="form_contents">
			<ul id="form_search">
				<?php if ($s_security_level == 3 || $s_security_level == 4) { ?>
				<li>

					<select id="p_hatsu" name="p_hatsu" style="width:200px; font-size:12px;" onchange="change_p_hatsu(this);">
						<option value='' selected="selected">何で検索しますか？</option>
						<option value=''>画像管理番号</option>
						<option value=''>写真名</option>
						<option value=''>素材（画像）の詳細内容</option>
						<option value=''>掲載可能範囲(使用範囲)</option>
						<option value=''>付加条件</option>
						<option value=''>このアカウントのみ使用可</option>
						<option value=''>写真入手元</option>
						<option value=''>版権所有者</option>
						<option value=''>素材管理番号</option>
						<option value=''>BUD_PHOTO番号</option>
						<option value=''>お客様情報</option>
						<option value=''>登録申請者</option>
						<option value=''>登録許可者</option>
						<option value=''>備考</option>
					</select>

					<label id="lbl_search_where" style="display:inline">
						<input name="search_where" type="text" id="search_where" size="30" onkeypress="save_search_conidition(event,this);" />
					</label>
<!--
					<label id="reg_pub_period0_lbl" style="display:none">
						<input name="reg_pub_period" id="reg_pub_period0" type="radio" value="mukigen" onclick="change_kikan(this);"/>無期限
					</label>

					<label id="reg_pub_period1_lbl" style="display:none">
						<input name="reg_pub_period" id="reg_pub_period1" type="radio" value="sankagetu" onclick='change_kikan(this);'/>3ヵ月
					</label>

					<label id="reg_pub_period2_lbl" style="display:none">
						<input name="reg_pub_period" id="reg_pub_period2" type="radio" value="hantoshi" onclick='change_kikan(this);'/>6ヵ月
					</label>

					<label id="reg_pub_period3_lbl" style="display:none">
						<input name="reg_pub_period" id="reg_pub_period3" type="radio" value="ichinen" onclick='change_kikan(this);'/>1年間
					</label>

					<label id="reg_pub_period4_lbl" style="display:none">
						<input name="reg_pub_period" id="reg_pub_period4" type="radio" value="shitei" onclick='change_kikan(this);'/>日付指定
					</label>

					<label id="year_lbl" style="display:none">
						<?php  //take_picture_year(); ?>
					</label>

					<label id="reg_pub_time_lbl" style="display:none">
						<?php  //take_picture_time($take_picture_time_id, $take_picture_time_name, 1); ?>
					</label>

					<label id="day_lbl" style="display:none">
						<?php  //take_pictrue_day(); ?>
					</label>
 -->
					<?php  disp_range($range_id,$range_name); ?>

					<label id="reg_account_lbl" style="display:none"><input name="reg_account" id="reg_account" type="checkbox" value="1"/>この申請アカウント</label>

					<label id="reg_addition2_lbl" style="display:none"><input name="reg_addition" id="reg_addition2" type="radio" value="2" onclick='change_reg_addition(this);'/>なし </label>
					<label id="reg_addition0_lbl" style="display:none"><input name="reg_addition" id="reg_addition0" type="radio" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
					<input name="reg_addition_txt" style="display:none" type="text" id="reg_addition_txt0"  style="width:120px;" onkeypress="save_search_conidition(event,this);" disabled="disabled"/>
					<label id="reg_addition1_lbl" style="display:none"><input name="reg_addition" id="reg_addition1" type="radio" value="1" onclick='change_reg_addition(this);'/>要使用許可 </label>
					<input name="reg_addition_txt" style="display:none" type="text" id="reg_addition_txt1"  style="width:120px;" onkeypress="save_search_conidition(event,this);" disabled="disabled"/>

					<?php  disp_borrowing_ahead($borrow_id,$borrow_name) ?>

					<label id="reg_bud_number0" style="display:none">
						<input name="reg_bud_number" id="reg_bud_number0" type="radio" value="1" onclick="change_bud_number_radio(this);"/>ある
					</label>
					<input name="reg_bud_number_txt" style="display:none;width:160px;" id="reg_bud_number_txt"  type="text" size="30" onkeypress="save_search_conidition(event,this);" disabled="disabled"/>

					<label id="reg_bud_number1" style="display:none">
						<input name="reg_bud_number" id="reg_bud_number1" type="radio" value="0" onclick="change_bud_number_radio(this);" />なし
					</label>

				</li>

				<li>
					<dl id="classification">
					<dt class="classification_ttl">国・県名で検索</dt>
					<dd class="classification_contents">
						<?php 
							disp_classification($classification_id, $classification_name);
							disp_direction($direction_id,$direction_name,$classification_id);
							disp_country_prefecture($country_prefecture_id,$country_prefecture_name,$direction_id);
						?>
					</dd>
					</dl>
				</li>
				<?php } else { ?>
				<li>
					<?php if ($s_security_level == 5) { ?>
					<select id="p_hatsu" name="p_hatsu" style="width:200px; font-size:12px;" onchange="change_p_hatsu(this);">
						<option value='' selected="selected">何で検索しますか？</option>
						<option value='BUDPHOTO'>BUD_PHOTO番号</option>
						<option value='CUSTOMER'>お客様情報</option>
					</select>
					<?php } else { ?>
					<select id="p_hatsu" name="p_hatsu" style="width:200px; font-size:12px;" onchange="change_p_hatsu(this);">
						<option value='' selected="selected">何で検索しますか？</option>
						<option value='BUDPHOTO'>BUD_PHOTO番号</option>
					</select>
					<?php } ?>

					<label id="lbl_search_where" style="display:inline">
						<input name="search_where" type="text" id="search_where" size="30" style="left:100px" onkeypress="save_search_conidition(event,this);" />
					</label>

					<?php  disp_range($range_id,$range_name); ?>

					<label id="reg_account_lbl" style="display:none"><input name="reg_account" id="reg_account" type="checkbox" value="1"/>この申請アカウント</label>

					<label id="reg_addition2_lbl" style="display:none"><input name="reg_addition" id="reg_addition2" type="radio" value="2" onclick='change_reg_addition(this);'/>なし </label>
					<label id="reg_addition0_lbl" style="display:none"><input name="reg_addition" id="reg_addition0" type="radio" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
					<input name="reg_addition_txt" style="display:none" type="text" id="reg_addition_txt0"  style="width:120px;" onkeypress="save_search_conidition(event,this);" disabled="disabled"/>
					<label id="reg_addition1_lbl" style="display:none"><input name="reg_addition" id="reg_addition1" type="radio" value="1" onclick='change_reg_addition(this);'/>要使用許可 </label>
					<input name="reg_addition_txt" style="display:none" type="text" id="reg_addition_txt1"  style="width:120px;" onkeypress="save_search_conidition(event,this);" disabled="disabled"/>

					<?php  disp_borrowing_ahead($borrow_id,$borrow_name) ?>

					<label id="reg_bud_number0" style="display:none">
						<input name="reg_bud_number" id="reg_bud_number0" type="radio" value="1" onclick="change_bud_number_radio(this);"/>ある
					</label>
					<input name="reg_bud_number_txt" style="display:none;width:160px;" id="reg_bud_number_txt"  type="text" size="30" onkeypress="save_search_conidition(event,this);" disabled="disabled"/>

					<label id="reg_bud_number1" style="display:none">
						<input name="reg_bud_number" id="reg_bud_number1" type="radio" value="0" onclick="change_bud_number_radio(this);" />なし
					</label>
				</li>

				<li>
					<dl id="classification">
					<dt class="classification_ttl">国・県名で検索</dt>
					<dd class="classification_contents">
						<?php 
							disp_classification($classification_id, $classification_name);
							disp_direction($direction_id,$direction_name,$classification_id);
							disp_country_prefecture($country_prefecture_id,$country_prefecture_name,$direction_id);
						?>
					</dd>
					</dl>
				</li>
				<?php } ?>
			</ul>
			<dl id="detail_search">
				<!-- <dt class="bt_search"><a href="#"><img src="parts/detail_search_bt0.gif" alt="詳細検索" width="113" height="21" border="0" id="image001" name="image001" onclick="changedetail_search();" /></a></dt>  -->
				<dt class="bt_search">詳細検索</dt>
				<dd class="search_contents">
					<dl id="publishing_limit">
						<dt>掲載期限</dt>
						<dd>
						<ul>
							<li><input name="publishing_include" type="radio" checked value="-1" />指定なし</li>
							<li><input name="publishing_include" type="radio" value="3" />3ヵ月未満を除外</li>
							<li><input name="publishing_include" type="radio" value="6" />6ヵ月未満を除外</li>
							<li><input name="publishing_include" type="radio" value="9" />期限のないもの</li>
						</ul>
						</dd>
					</dl>
					<ul id="category_ttl">
						<li><input name="category_include" type="radio" checked value="" />カテゴリーで絞り込む</li>
						<li><input name="category_include" type="radio" value="" />カテゴリーを除外</li>
						<li><input style="height:20px" name="btn_clear" type="button" value="チェックを外す" onclick="clear_contents();"/></li>
					</ul>
					<?php  dis_category($category_id,$category_name);?>
				</dd>
			</dl>
			<!-- <p id="search_bt"><a href="#"><img src="parts/search_bt.gif" alt="検索"  onclick="go_search();return false;"/></a></p> -->
			<p id="search_bt">
				<a href="#"><img src="parts/search_bt.gif" id='searchbtn1' alt="検索"  onclick="go_search();return false;"/></a>
				<a href="#"><img src="parts/bt_re_set.gif" alt="リセット"  onclick="clear_all_contents();return false;" /></a>
				<!-- <span><a href="#" class="bt_syousai" onclick="changedetail_search();return false;">詳細条件を追加する</a></span> -->
				<span><a href="#" class="bt_details_add" onclick="changedetail_search();return false;" id="detail_href">詳細条件を追加する</a></span>
			</p>
		</div>
	</div>
	</form>
</div>
<!-- <input type="hidden" id="p_dto" name="p_dto" value="" />  -->
</body>
</html>