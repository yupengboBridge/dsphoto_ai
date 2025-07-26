<?php
header("Cache-Control: no-cache, must-revalidate");
require_once('./config.php');
require_once('./lib.php');

// タイムゾーンを設定します。
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

// リクエスト（$_REQUEST）より情報を取得します。
$p_photo_id = array_get_value($_REQUEST, 'p_photo_id' ,"");
$g_arubamu_id = array_get_value($_REQUEST,"arubamu_id" ,"");
/*　$p_gamen_flg
 * 「１」：ピックアップ画面から引き続き場合
 * 「２」：検索結果画面から引き続き場合
 * 「３」：見た画像画面から引き続き場合
 * 「４」：アルバム更新画面から引き続き場合
 */
$p_gamen_flg = array_get_value($_REQUEST, 'gamen_flg' ,"");
if (empty($p_gamen_flg))
{
	$p_gamen_flg = 0;
} else {
	$p_gamen_flg = (int)$p_gamen_flg;
}
try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	// 画像検索用のインスタンスを生成します。
	$is = new ImageSearch();

	// 画像を検索します。
	$is->set_photo_id_str($p_photo_id);
	$is->select_image_fmid($db_link);

	$img_count = count($is->images);
}
catch(Exception $e)
{
	$msg[] = $e->getMessage();
	error_exit($msg);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>画像表示</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<link rel="stylesheet" href="./css/skin.css" type="text/css" media="all"/>
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
<?php
if (!empty($GLOBALS["s_user_id"]))
{
	print "var uid = ".$GLOBALS["s_user_id"].";";
} else {
	print "var uid = '';";
}

if (!empty($GLOBALS["p_gamen_flg"]))
{
	print "var gamen_flg = ".$GLOBALS["p_gamen_flg"].";";
} else {
	print "var gamen_flg = 0;";
}

if (!empty($GLOBALS["g_arubamu_id"]))
{
	print "var js_arubamu_id = ".$GLOBALS["g_arubamu_id"].";";
} else {
	print "var js_arubamu_id = -1;";
}
//print "var submit_url = \"".$_SERVER["HTTP_REFERER"]."\"";
?>

var ua = navigator.userAgent.toLowerCase();
var is_pc_ie  = ( (ua.indexOf('msie') != -1 ) && ( ua.indexOf('win') != -1 ) && ( ua.indexOf('opera') == -1 ) && ( ua.indexOf('webtv') == -1 ) );
function change_img_sp(file11,tempurl){

	var current_text = document.getElementById('show_img').innerHTML;
	var file_text = "<img src="+'"'+file11+'"'+" width=\"400\">";
	var ui =document.getElementById("upload_image");
	var sp_ui =document.getElementById("spp");
	set_frameheight('iframe_bottom',1200);
	if (current_text != file_text) {
		console.log('不相同');
		ui.style.display="";
		sp_ui.style.display="";
		document.getElementById('show_img').innerHTML="<img src="+file11+" width=\'400\'/>";
	}else{
		console.log('相同');
		ui.style.display="none";
		sp_ui.style.display="none";
		document.getElementById('show_img').innerHTML="<img src="+tempurl+" width=\'400\'/>";
	}
}
// function change_img_pc(file12){
// 	document.getElementById('show_img').innerHTML="<img src="+file12+" width=\'400\'/>";
// }
function setClipboard(pid)
{
	var objkey = "code"+pid;
	var maintext = document.getElementById(objkey).value;

	// 2020 修改
   const input = document.createElement('input');
    document.body.appendChild(input);
    input.setAttribute('readonly', 'readonly');/////控制移动端闪屏
    input.setAttribute('value', maintext );/////复制内容
    input.setSelectionRange(0, 999999);/////控制复制内容多少
    input.select();
    if (document.execCommand('copy')) {
        document.execCommand('copy');
		// alert("写真情報をクリップボードにコピーしました。");
        document.body.removeChild(input);
		return true;
//         console.log('复制成功');
    }else{

//       console.log('该浏览器不支持此功能');
		document.body.removeChild(input);
		return false;
   }
	/*
	if (window.clipboardData) {
		return (window.clipboardData.setData("Text", maintext));
	} else if (window.netscape) {

		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		} catch (e) {
			alert("Firefox設定されたセキュリティー制限のため、クリップボードを使用できません\n，もし使いたいなら、下記の手順に基づいて設定してください。\n　　①firefoxのWEBアドレスに「about:config」を入力して、コントロール パネルを呼びます。\n　　②「signed.applets.codebase_principal_support」に「true」を設定してください。");
			return false;
		}

		netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip) return;
		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans) return;
		trans.addDataFlavor('text/unicode');
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
		var copytext=maintext;
		str.data=copytext;
		trans.setTransferData("text/unicode",str,copytext.length*2);
		var clipid=Components.interfaces.nsIClipboard;
		if (!clip) return false;
		clip.setData(trans,null,clipid.kGlobalClipboard);
		return true;
	}
	*/
		return false;
}

/*
 * 関数名：move_image
 * 関数説明：画面の「前の画像へ」と「次の画像へ」ボタンの処理
 * パラメタ：
 * adj:「-1」：「前の画像へ」ボタンを押す；「1」：「次の画像へ」ボタンを押す
 * flg:「1」：ピックアップ画面から引き続き；「2」：検索結果画面から引き続き
 * 戻り値：無し
 */
function move_image(adj,flg)
{
	var prev_id = "";
	var next_id = "";

	if (adj < 0)
	{
		prev_id = document.getElementById("prev_photo_id").value;
		if (prev_id != "" && prev_id != null)
		{
			if (gamen_flg == 4)
			{
				var url = "image_detail.php?p_photo_id=" + prev_id + "&gamen_flg=" + flg + "&arubamu_id=" + js_arubamu_id;
			} else {
				var url = "image_detail.php?p_photo_id=" + prev_id + "&gamen_flg=" + flg;
			}
			parent.bottom.location.href = url;
		}
	}

	if (adj > 0)
	{
		next_id = document.getElementById("next_photo_id").value;
		if (next_id != "" && next_id != null)
		{
			if (gamen_flg == 4)
			{
				var url = "image_detail.php?p_photo_id=" + next_id + "&gamen_flg=" + flg + "&arubamu_id=" + js_arubamu_id;
			} else {
				var url = "image_detail.php?p_photo_id=" + next_id + "&gamen_flg=" + flg;
			}
			parent.bottom.location.href = url;
		}
	}
}

/*
 * 関数名：changeTabs
 * 関数説明：画面の「画像内容」ボタンと「ソース詳細」ボタンと「管理データ」ボタンの切り替え
 * パラメタ：
 * flg:「1」：「画像内容」ボタンを押す　「2」：「ソース詳細」ボタンを押す　「3」：「管理データ」ボタンを押す
 * 戻り値：無し
 */
function changeTabs(flg)
{
	if (flg == 1 || flg == 2)
	{
		var objTab = document.getElementById("qa01_area");
		if (objTab) objTab.style.display = "block";

		var objTab = document.getElementById("qa02_area");
		if (objTab) objTab.style.display = "none";

		var objTab = document.getElementById("qa03_area");
		if (objTab) objTab.style.display = "none";
	}

	if (flg == 2)
	{
		var objTab = document.getElementById("qa01_area");
		if (objTab) objTab.style.display = "none";

		var objTab = document.getElementById("qa02_area");
		if (objTab) objTab.style.display = "block";

		var objTab = document.getElementById("qa03_area");
		if (objTab) objTab.style.display = "none";
	}

	if (flg == 3)
	{
		var objTab = document.getElementById("qa01_area");
		if (objTab) objTab.style.display = "none";

		var objTab = document.getElementById("qa02_area");
		if (objTab) objTab.style.display = "none";

		var objTab = document.getElementById("qa03_area");
		if (objTab) objTab.style.display = "block";
	}
	//yupengbo modify 2011/12/19 start
	var obj = document.getElementById("kategori_cnt");
	if(obj)
	{
		var kategori_cnt = parseInt(obj.value);
		if(kategori_cnt > 20)
		{
			set_frameheight('iframe_bottom',1300);
		}else{
			set_frameheight('iframe_bottom',1000);
		}
	}
	//yupengbo modify 2011/12/19 end
}

/*
 * 関数名：goHistory
 * 関数説明：画面の「一覧に戻る」ボタンの処理
 * パラメタ：無し
 * 戻り値：無し
 */
function goHistory()
{
	if (gamen_flg == 4)
	{
		var url = "arubamu_edit.php?album_id=" + js_arubamu_id;
	} else {
		var url = getCookie("submit_url");
	}
//alert(url);
//alert(getCookie("bottom_url"));
	if (url.indexOf("pickup_ichiran",0) > 0)
	{
		top.middle2.location = url;
		top.bottom.location = getCookie("bottom_url");
	} else if (url.indexOf("search_result",0) > 0){
		top.bottom.location = url;
	}
}

/*
 * 関数名：setMitaImagesCookie
 * 関数説明：見た画像をクッキーに設定する
 * パラメタ：
 * p_id:イメージID
 * 戻り値：無し
 */
function setMitaImagesCookie(p_id)
{
	// クッキー識別子を作成します。
	var ck_id = "mita_images";
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	if(!idstr){
	    return;
    }
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.explode(",");
	// 既にクッキーで設定されているものについては、除外します。
	if (check_array(id_a, p_id) == -1)
	{
		if (idstr.length >= 1)
		{
			idstr =  p_id + "," + idstr;
		}
		else
		{
			idstr = p_id;
		}
	}

	// クッキーを設定します。
	setCookie(ck_id, idstr);
}

/*
 * 関数名：go_reg_edit
 * 関数説明：画像編集画面へ遷移する
 * パラメタ：無し
 * 戻り値：無し
 */
function go_reg_edit(sp_id)
{
	var url = "./register_image_edit.php?p_photo_id=" + sp_id;
	setCookie("reg_edit_url",parent.bottom.location.href);
	parent.bottom.location.href = url;
}

/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
	//yupengbo modify 2011/12/19 start
	var obj = document.getElementById("kategori_cnt");
	if(obj)
	{
		var kategori_cnt = parseInt(obj.value);
		if(kategori_cnt > 20)
		{
			set_frameheight('iframe_bottom',900);
		}else{
			set_frameheight('iframe_bottom',700);
		}
	}
	//yupengbo modify 2011/12/19 end
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
<div class="photo_detail">
<?php
disp_one_image();
?>
</div>
</div>
</div>
</body>
</html>
<?php
/*
 * 関数名：check_array_index
 * 関数説明：文字列の検索
 * パラメタ：
 * $ary：		　array
 * $fndstr：	　文字列
  * 戻り値：インデックス
 */
function check_array_index($ary,$fndstr)
{
	$flg = -1;
	for ($i = 0; $i < count($ary); $i++)
	{
		if (strcasecmp($ary[$i],$fndstr) == 0)
		{
			$flg = $i;
			break;
		}
	}
	return $flg;
}

/*
 * 関数名：disp_category
 * 関数説明：「カテゴリー」を出力する
 * パラメタ：
 * $cg_id：	　カテゴリーID
 * $cg_name：　カテゴリー
 * 戻り値：無し
 */
function disp_category()
{
	global $db_link,$p_photo_id;

	// PhotoImageのインスタンスを生成します。
	$pi = new PhotoImageDB();
	$category_id = array();										// カテゴリーID
	$category_name = array();									// カテゴリー名
	$pi->get_category($db_link,$category_id,$category_name);	// カテゴリ

	$pi->get_keyword_str($db_link, $p_photo_id);
	$kwd_a = array();
	$kwd_a = explode(" ", $pi->keyword_str);
	$tmp_cg_name = array();
	$tmp1_cg_name = "";
	$c_name = "";

	$dc = count($category_id);
	$tmp_cnt = 1;//yupengbo add 2011/12/19
	for ($i=0;$i < $dc;$i++)
	{
		if (check_array_index($kwd_a,$category_name[$i][0]) != -1)
		{
			$tmp_cg_name = $category_name[$i][0];
			$dc2 = count($category_id[$i]);
			$tmp1_cg_name = "";
			for($j = 1;$j < $dc2; $j++)
			{
				//yupengbo modify 2011/12/19 start
				if (check_array_index($kwd_a,$category_name[$i][$j]) != -1)
				{
					$tmp_cnt = $tmp_cnt + 1;
					$tmp1_cg_name.= $category_name[$i][$j]." | ";
				}
				//yupengbo modify 2011/12/19 end
			}
			//echo strlen($tmp1_cg_name)."<br>";
			if (strlen($tmp1_cg_name) > 0)
			{
				$c_name .= $tmp_cg_name." | ".substr($tmp1_cg_name,0,strlen($tmp1_cg_name)-2)."<br>";
			} else {
				//$c_name .= $tmp_cg_name." | ".$tmp1_cg_name."<br>";
				$c_name .= $tmp_cg_name."<br>";
			}
			$tmp_cnt = $tmp_cnt + 1;//yupengbo add 2011/12/19
		}
	}
	print "				<dl>\r\n";
	print "					<input type='hidden' name='kategori_cnt' id='kategori_cnt' value=".$tmp_cnt." />\r\n";//yupengbo add 2011/12/19
	print "					<dt>カテゴリー：</dt>\r\n";
	print "					<dd>".$c_name."</dd>\r\n";
	print "				</dl>\r\n";
}

/*
 * 関数名：disp_kikan2
 * 関数説明：画面の初期化の処理
 * パラメタ：
 * $p_k：期間の文字列;$p_dfrom:期間の開始;$p_dto:期間の終了
 * 戻り値：無し
 */
function disp_kikan2($p_k,$p_dfrom,$p_dto)
{
	print "				<dl>\r\n";
	print "					<dt>掲載期間</dt>\r\n";

	$p_d_from = substr($p_dfrom,0,4)."/".substr($p_dfrom,5,2)."/".substr($p_dfrom,-2);
	$p_d_to = substr($p_dto,0,4)."/".substr($p_dto,5,2)."/".substr($p_dto,-2);

	switch ($p_k)
	{
		case 'mukigen':
			print "					<dd> 無期限</dd>\r\n";
			break;
		case 'sankagetu':
			print "					<dd> ３ヶ月</dd>\r\n";
			break;
		case 'hantoshi':
			print "					<dd> 半年</dd>\r\n";
			break;
		case 'ichinen':
			print "					<dd> １年</dd>\r\n";
			break;
		//added by wangtongcaho 2011-12-02 begin
		case 'ninen':
			print "		<label>2年間 </label>\r\n";
			break;
		case 'sannen':
			print "		<label>3年間 </label>\r\n";
			break;
		//added by wangtongchao 2011-12-02 end
		case 'shitei':
			print "					<dd> 期間指定</dd>\r\n";
			break;
	}

	print "				</dl>\r\n";

	//2009/01/19 仕様変更　下記のURLより行う-----------------------------------------
	//https://www3.bud-international.co.jp/hei/cms/090119_photo_db/sample_index.html
	if($p_k != "mukigen")
	{
		print "				<dl>\r\n";
		print "					<dt>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</dt>\r\n";
		print "					<dd>".dp($p_d_from."  ～   ".$p_d_to)."</dd>\r\n";
		print "				</dl>\r\n";
	}
	//-----------------------------------------------------------------------------
}

/*
 * 関数名：get_registration_classification
 * 関数説明：登録分類をDBより取得します。
 * パラメタ：無し
 * 戻り値：登録分類
 */
function get_registration_classification()
{
	global $is,$p_photo_id,$db_link;

	$p_img_data_all = new PhotoImageDataAll();
	if (empty($is->images[0])) {
		//return "  ｜   ｜  ｜";
		return "  ";
	}
	$p_img_data_all = $is->images[0];

	$reg_class = new RegistrationClassifications();
	$reg_class = $p_img_data_all->registration_classifications;
	$res_regs = "";
	try
	{
		$reg_class->select_data($db_link,$p_photo_id);

		for ($i = 0; $i < count($reg_class->classification_name); $i++)
		{
			if (!empty($reg_class->classification_name[$i]))
			{
				$c_name = $reg_class->classification_name[$i];
			} else {
				$c_name = "";
			}

			if (!empty($reg_class->direction_name[$i]))
			{
				$d_name = $reg_class->direction_name[$i];
			} else {
				$d_name = "";
			}

			if (!empty($reg_class->country_prefecture_name[$i]))
			{
				$c_p_name = $reg_class->country_prefecture_name[$i];
			} else {
				$c_p_name = "";
			}

			if (!empty($reg_class->place_name[$i]))
			{
				$p_name = $reg_class->place_name[$i];
			} else {
				$p_name = "";
			}

			if (!empty($c_name)) $res_regs .= $c_name;
			if (!empty($d_name)) $res_regs .= "｜".$d_name;
			if (!empty($c_p_name)) $res_regs .= "｜".$c_p_name;
			if (!empty($p_name)) $res_regs .= "｜".$p_name;

			$res_regs .= "<br>";
		}

		return $res_regs;
	}
	catch(Exception $e)
	{
		$msg[] = $e->getMessage();
		error_exit($msg);
	}
}

/*
 * 関数名：get_range_username
 * 関数説明：掲載可能範囲をDBより取得します。
 * パラメタ：無し
 * 戻り値：掲載可能範囲の名前
 */
function get_range_username()
{
	global $is,$p_photo_id,$db_link;

	$range_of_use_id = array();		// 使用範囲ID
	$range_of_use_name = array();	// 使用範囲
	$r_name = "";

	$p_db = new PhotoImageDB();
	$p_db->get_range_of_use($db_link,$range_of_use_id,$range_of_use_name);

	$ed = count($range_of_use_id);

	for ($i = 0; $i < $ed; $i++)
	{
		$photo_idata = new PhotoImageDataAll();
		$photo_idata = $is->images[0];
		if ((int)$range_of_use_id[$i] == (int)$photo_idata->range_of_use_id)
		{
			$r_name = $range_of_use_name[$i];
			break;
		}
	}

	return $r_name;
}

/*
 * 関数名：getPrevAndNextPhotoID
 * 関数説明：掲載可能範囲をDBより取得します。
 * パラメタ：
 * $flg:「0」前のイメージIDを取得；「1」次のイメージIDを取得
 * $id:今のイメージID
 * 戻り値：前のイメージID或は次のイメージID
 */
function getPrevAndNextPhotoID($flg,$id)
{
	global $p_gamen_flg, $s_user_id;

	$ret_id = "";

	if (!empty($p_gamen_flg))
	{
		if ($p_gamen_flg == 1) 			//ピックアップ画面から引き続き場合
		{
			$cookie_key = "pickup_images_id_".$s_user_id;
		} elseif ($p_gamen_flg == 2)	//検索結果画面から引き続き場合
		{
			$cookie_key = "images_result";
		} elseif ($p_gamen_flg == 3)	//見た画像画面から引き続き場合
		{
			$cookie_key = "mita_images";
		} elseif ($p_gamen_flg == 4)	//アルバム更新画面から引き続き場合
		{
			$cookie_key = "arubamu_images";
		}

		$photo_id = array_get_value($_COOKIE,$cookie_key ,"");

		if (!empty($photo_id))
		{
			$photo_id_array = explode(",", $photo_id);
			$id_index = -1;

			for ($i = 0 ; $i < count($photo_id_array); $i++)
			{
				if ($photo_id_array[$i] == $id)
				{
					$id_index = $i;
					break;
				}
			}

			$ed = count($photo_id_array) - 1;
			if ($id_index == 0 && $flg == 1)
			{
				if (!empty($photo_id_array[1])) $ret_id = $photo_id_array[1];
			}

			if ($id_index == $ed && $flg == 0)
			{
				if (!empty($photo_id_array[$ed - 1])) $ret_id = $photo_id_array[$ed - 1];
			}

			if ($id_index > 0 && $id_index < $ed)
			{
				if ($flg == 0) $ret_id = $photo_id_array[$id_index - 1];
				if ($flg == 1) $ret_id = $photo_id_array[$id_index + 1];
			}
		}
	}

	return $ret_id;
}


function getpageurl()
{
    $pageURL = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function getCurrentPageUrl() {
    $pageURL = 'http';
    if(isset($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on"){
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    }else{
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

/*
 * 関数名：disp_one_image
 * 関数説明：イメージの詳細情報を出力する
 * パラメタ：
 * $cntItems：イメージの総数
 * 戻り値：無し
 */
function disp_one_image()
{
	global $is,$reg_class,$p_gamen_flg,$s_security_level, $s_user_id, $p_photo_id,$image_search_url,$tmp_url,$db_link;
    $mno = dp($is->images[0]->photo_mno);

	if (empty($is->images[0])) return;

	$reg_class_infor = get_registration_classification();
	$range_name = get_range_username();
	$p_prev_id = getPrevAndNextPhotoID(0,$is->images[0]->photo_id);
	$p_next_id = getPrevAndNextPhotoID(1,$is->images[0]->photo_id);

	print "<script type=\"text/javascript\">";
	print "setMitaImagesCookie(\"".$is->images[0]->photo_id."\");";
	print "</script>";

	// 検索結果画面から引き続き場合
	if ($p_gamen_flg == 2)
	{
		print "<p class=\"cap_details\">【検索結果一覧画像の詳細】</p>\r\n";
	}
	// ピックアップ画面から引き続き場合
	if ($p_gamen_flg == 1)
	{
		print "<p class=\"cap_details\">【ピックアップ中画像の詳細】</p>\r\n";
	}
	print "	<h2><span>画像番号：</span>".dp($is->images[0]->photo_mno)."<span>画像名：</span>".dp($is->images[0]->photo_name)."</h2>\r\n";
	print "	<div class=\"detail_contents\" style='height:3000px;'>\r\n";
	print "		<div class=\"detail_left_contents\">\r\n";
	print "			<input type=\"hidden\" id=\"prev_photo_id\" value=\"".$p_prev_id."\"/>";
	print "			<input type=\"hidden\" id=\"next_photo_id\" value=\"".$p_next_id."\"/>";
	print "			<ul class=\"detail_next_back\">\r\n";
	if (!empty($p_prev_id) && $p_prev_id != "")
	{
		print "				<li class=\"back\"><a href=\"#\" onclick='move_image(-1,".$p_gamen_flg.");return false;' title=\"前の画像へ\">前の画像へ</a></li>\r\n";
	}
	if (!empty($p_next_id) && $p_next_id != "")
	{
		print "				<li class=\"next\"><a href=\"#\" onclick='move_image(1,".$p_gamen_flg.");return false;'  title='次の画像へ'>次の画像へ</a></li>\r\n";
	}
	print "			</ul>\r\n";

	//2009/01/19 仕様変更 下記のURLより行う-----------------------------------------
	//https://www3.bud-international.co.jp/hei/cms/090119_photo_db/sample_index.html
	if($is->images[0]->kikan != "mukigen")
	{
		if (empty($p_prev_id) && $p_prev_id == "" && empty($p_next_id) && $p_next_id == "")
		{
			print "<ul class=\"detail_next_back\">\r\n";
			print "<li class=\"back\"></li>\r\n";
			print "</ul>\r\n";
		}
		$date_ary = explode("-",$is->images[0]->dto);
		$date_dto = sprintf("%02d/%02d/%02d",$date_ary[0],$date_ary[1],$date_ary[2]);
		print "<p id=\"expiration_date\">有効期限：".$date_dto."</p>\r\n";
	}
	//------------------------------------------------------------------------------

	//print "<p><img src=".$is->images[0]->up_url[1]." width=\"400\" height=\"300\" /></p>\r\n";
	$tmp_url = "./disp_register_image.php?p_photo_id=".$p_photo_id;

	//print "<p><img src=".$tmp_url." width=\"400\" height=\"300\" /></p>\r\n";
	$isize = $is->images[0]->image_size_x;
	if(!empty($isize))
	{
		if((int)$isize >= 400)
		{
			print "<p class=\"\" id='show_img'><img src=".$tmp_url." width=\"400\" /></p>\r\n";
		} else {
			print "<p class=\"\" id='show_img'><img src=".$tmp_url." width=\"400\" /></p>\r\n";
		}
	}

    $sql_sp = "SELECT is_mall,is_sp,photo_filename_th11 from photoimg WHERE photo_mno = '{$mno}'";
    $is_sp_data = CmsPhotoDbCore::findOne($db_link,$sql_sp);
	$select_show_sp = "SELECT show_sp FROM user WHERE user_id = '{$s_user_id}' ";
    $user_sp_auth = CmsPhotoDbCore::findOne($db_link,$select_show_sp);
    $sp_img = '';
    if ($user_sp_auth['show_sp'] == '1' && $is_sp_data['is_sp'] == '1') {
        $sp_img = "<button onclick='change_img_sp(\"" . $is_sp_data['photo_filename_th11'] . "\",\"" . $tmp_url . "\")'";
        $sp_img .= " style='height:20px;width:45px;background:url(./sppc/g_s.png);background-size:45px 20px;' />";
    }elseif($user_sp_auth['show_sp'] == '1' && $is_sp_data['is_sp'] == '2') {
        $sp_img = "<button onclick='change_img_sp(\"" . $is_sp_data['photo_filename_th11'] . "\",\"" . $tmp_url . "\")'";
        $sp_img .= " style='height:20px;width:45px;background:url(./sppc/y_s.png);background-size:45px 20px;' />";
    }

	$tmpkey = "code".$is->images[0]->photo_id;
	//print "<input type='hidden' value='".$is->images[0]->photo_mno.$is->images[0]->photo_name."' id='".$tmpkey."' name='".$tmpkey."' />\r\n";
	print "<input type='hidden' value='".$image_search_url.$is->images[0]->photo_mno."' id='".$tmpkey."' name='".$tmpkey."' />\r\n";

	//print "<input type='hidden' value='<img id=\"myTourPh\" src=\"https://x.hankyu-travel.com/photo_db/image_search_kikan.php?p_photo_mno=".$is->images[0]->photo_mno."\" />' id='p_mno' name='p_mno' />";
	print "			<ul class=\"detail_bt_eria\">\r\n";
	// 検索結果画面から引き続き場合
	if ($p_gamen_flg == 2)
	{
		print "				<li class=\"detail_bt_pickup\"><a href=\"#\" onclick='if (pickup(\"" .$is->images[0]->photo_id. "\", ".$s_user_id.")==false){alert(\"既にピックアップしています。\");}' title='ピックアップ'>ピックアップ</a></li>\r\n";
	}
	//print "				<li class=\"detail_bt_copy\"><a href=\"#\" title='ソースをコピー' onclick='setClipboard();alert(\"ソースをクリップボードにコピーしました。\");return false;'>ソースをコピー</a></li>\r\n";
	print "				<li class=\"detail_bt_copy\"><a href=\"#\" title='ソースをコピー' onclick='setClipboard(\"".$is->images[0]->photo_id."\"); alert(\"写真情報をクリップボードにコピーしました。\"); return false;'>ソースをコピー</a></li>\r\n";
	if ($p_gamen_flg == 1)
	{
		print "				<li class=\"detail_bt_pickup\"></li>\r\n";
	}
	print "				<li class=\"detail_bt_view\"><a href=\"#\" onclick='goHistory();return false;' title='一覧に戻る'>一覧に戻る</a></li>\r\n";
	print "			</ul>\r\n";
		#add liucongxu 2021
		print "<li>";
	print $sp_img;
	#print $pc_img;
		print "</li>";
    if($user_sp_auth['show_sp'] == '1' && ($is->images[0]->image_size_x==1252 && $is->images[0]->image_size_y==578)){
        print '<div id="upload_image" style="line-height:30px;">';
        print '<form enctype="multipart/form-data" 
				 method="post" 
				 action="./upload.php">';
        print '<input type="file" name="photo" id="photo" value=""/>';
        print '<input type="hidden" name="hidden_now_photo_filename_th11" value="'.$is_sp_data['photo_filename_th11'].'"/>';
        print '<input type="hidden" name="hidden_photo_id" value="'.$p_photo_id.'"/>';
        print '<input type="hidden" name="hidden_back_url" value="'.getCurrentPageUrl().'"/>';
        print '<input type="submit" value=" アップロード"/> ';
        print '</form>';
        print '</div>';
    }else{
        print '<div id="upload_image" style="display: none;line-height:30px;">';
        print '<form enctype="multipart/form-data" 
				 method="post" 
				 action="./upload.php">';
        print '<input type="file" name="photo" id="photo" value=""/>';
        print '<input type="hidden" name="hidden_now_photo_filename_th11" value="'.$is_sp_data['photo_filename_th11'].'"/>';
        print '<input type="hidden" name="hidden_photo_id" value="'.$p_photo_id.'"/>';
        print '<input type="hidden" name="hidden_back_url" value="'.getCurrentPageUrl().'"/>';
        print '<input type="submit" value=" アップロード"/> ';
        print '</form>';
        print '</div>';
    }

	if (($s_security_level == 3 || $s_security_level == 4) && ($is_sp_data['is_mall'] != '1'))
	{
		print "			<p class='correct_delet_bt'><a href='#' onclick='go_reg_edit(\"".$is->images[0]->photo_id."\");return false;' title='修正/削除'>修正/削除</a></p>\r\n";
	}
	print "			<dl class=\"photo_wide\">\r\n";
	print "				<dt>登録サイズ</dt>\r\n";
	print "				<dd>".dp($is->images[0]->image_size_x."×".$is->images[0]->image_size_y)."pix</dd>\r\n";
	print "				<p id='spp' style='display: none;'>SP用</p>\r\n";
	print "			</dl>\r\n";
	print "		</div>\r\n";
	print "		<div class=\"tabContainer\">\r\n";
	print "			<ul class=\"tabMenu\">\r\n";
	print "				<li id=\"qa01\"><a href=\"#qa01_area\" title=\"画像内容\" onclick='changeTabs(1);return false;'>画像内容</a></li>\r\n";

	if ($s_security_level == 3 || $s_security_level == 4)
	{
		print "				<li id=\"qa02\"><a href=\"#qa02_area\" title=\"ソース詳細\" onclick='changeTabs(2);return false;'>ソース詳細</a></li>\r\n";
		print "				<li id=\"qa03\" style=\"float:right;\"><a href=\"#qa03_area\" title=\"管理データ\" onclick='changeTabs(3);return false;'>管理データ</a></li>\r\n";
	}

	print "			</ul>\r\n";
	print "			<div id=\"qa01_area\" style=\"display:block\">\r\n";
	print "				<dl>\r\n";
	print "					<dt>掲載状況</dt>\r\n";
	print "					<dd>".dp($is->images[0]->publishing_situation_name)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>画像番号</dt>\r\n";
	print "					<dd>".dp($is->images[0]->photo_mno)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "				<dt>画像名</dt>\r\n";
	print "					<dd>".dp($is->images[0]->photo_name)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>登録分類</dt>\r\n";
	print "					<dd>".$reg_class_infor."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>内容</dt>\r\n";
	print "					<dd>".dp($is->images[0]->photo_explanation)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>撮影時期</dt>\r\n";
	if (!empty($is->images[0]->take_picture_time_name) && !empty($is->images[0]->take_picture_time2_name))
	{
		print "					<dd>".dp($is->images[0]->take_picture_time_name."｜".$is->images[0]->take_picture_time2_name)."</dd>\r\n";
	} elseif (!empty($is->images[0]->take_picture_time_name)) {
		print "					<dd>".dp($is->images[0]->take_picture_time_name)."</dd>\r\n";
	} elseif (!empty($is->images[0]->take_picture_time2_name)) {
		print "					<dd>".dp($is->images[0]->take_picture_time2_name)."</dd>\r\n";
	}
	print "				</dl>\r\n";
	disp_category();
	//if ($s_security_level != 3 && $s_security_level != 4)
	//{
	print "				<dl>\r\n";
	print "					<dt>BUD_PHOTO番号</dt>\r\n";
	print "					<dd>".dp($is->images[0]->bud_photo_no)."</dd>\r\n";
	print "				</dl>\r\n";
	//}
	print "			</div>\r\n";

	print "			<div id=\"qa02_area\" style=\"display:none\">\r\n";
	print "				<p>".dp("<img id=\"myTourPh\" src=\"".$image_search_url.$is->images[0]->photo_mno."\" />")."</p>\r\n";
	print "			</div>\r\n";
	print "			<div id=\"qa03_area\" style=\"display:none\">\r\n";
	print "				<p class=\"ttl_lead\">基本情報</p>\r\n";
	print "				<dl>\r\n";
	print "					<dt>掲載状況</dt>\r\n";
	print "					<dd>".dp($is->images[0]->publishing_situation_name)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>画像番号</dt>\r\n";
	print "					<dd>".dp($is->images[0]->photo_mno)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>画像名</dt>\r\n";
	print "					<dd>".dp($is->images[0]->photo_name)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>登録分類</dt>\r\n";
	print "					<dd>".$reg_class_infor."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>内容</dt>\r\n";
	print "					<dd>".dp($is->images[0]->photo_explanation)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<p class=\"ttl\">掲載条件</p>\r\n";

	disp_kikan2($is->images[0]->kikan,$is->images[0]->dfrom,$is->images[0]->dto);

	print "				<dl>\r\n";
	print "					<dt>撮影時期</dt>\r\n";
	if (!empty($is->images[0]->take_picture_time_name) && !empty($is->images[0]->take_picture_time2_name))
	{
		print "					<dd>".dp($is->images[0]->take_picture_time_name."｜".$is->images[0]->take_picture_time2_name)."</dd>\r\n";
	} elseif (!empty($is->images[0]->take_picture_time_name)) {
		print "					<dd>".dp($is->images[0]->take_picture_time_name)."</dd>\r\n";
	} elseif (!empty($is->images[0]->take_picture_time2_name)) {
		print "					<dd>".dp($is->images[0]->take_picture_time2_name)."</dd>\r\n";
	}
	print "				</dl>\r\n";
	disp_category();
	print "				<dl>\r\n";
	print "					<dt>掲載可能範囲</dt>\r\n";
	if (!empty($is->images[0]->use_condition))
	{
		print "					<dd>".dp($range_name)."『".$is->images[0]->use_condition."』</dd>\r\n";
	} else {
		print "					<dd>".dp($range_name)."</dd>\r\n";
	}
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>付加条件（クレジット）</dt>\r\n";

	if (empty($is->images[0]->additional_constraints1)) {
		print "					<dd>&nbsp;&nbsp;&nbsp;&nbsp;</dd>\r\n";
	} else {
		//yupengbo modify 20110105 start
		print "					<dd>".str_replace("=_=","<br/>",dp($is->images[0]->additional_constraints1))."</dd>\r\n";
		//yupengbo modify 20110105 end
	}

	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>付加条件（要確認）</dt>\r\n";

	if (empty($is->images[0]->additional_constraints2)) {
		print "					<dd>&nbsp;&nbsp;&nbsp;&nbsp;</dd>\r\n";
	} else {
		print "					<dd>".dp($is->images[0]->additional_constraints2)."</dd>\r\n";
	}

	print "				</dl>\r\n";
	print "				<p class=\"ttl\">版権情報</p>\r\n";
	print "				<dl>\r\n";
	print "					<dt>独占使用</dt>\r\n";
	if ((int)$is->images[0]->monopoly_use != 0)
	{
		print "					<dd>このアカウントのみ使用可</dd>\r\n";
	} else {
		print "					<dd>&nbsp;&nbsp;&nbsp;&nbsp;</dd>\r\n";
	}
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>写真入手元</dt>\r\n";
	$tmp = dp($is->images[0]->content_borrowing_ahead);
	//changed by wangtongchao 2011-11-28 begin
	if (!empty($tmp))
	{
		print "					<dd>".dp($is->images[0]->content_borrowing_ahead)."</dd>\r\n";
	} else {
		print "					<dd></dd>\r\n";
	}
	//changed by wangtongchao 2011-11-28 end
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>版権所有者</dt>\r\n";
	print "					<dd>".dp($is->images[0]->copyright_owner)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>元画像管理番号</dt>\r\n";
	print "					<dd>".dp($is->images[0]->source_image_no)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>BUD_PHOTO番号</dt>\r\n";
	print "					<dd>".dp($is->images[0]->bud_photo_no)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<p class=\"ttl\">登録情報</p>\r\n";
	print "				<dl>\r\n";
	print "					<dt>お客様情報</dt>\r\n";
	$tmp1 = dp($is->images[0]->customer_section);
	$tmp2 = dp($is->images[0]->customer_name);
	if (!empty($tmp1) && !empty($tmp2))
	{
		print "					<dd>".$tmp1."｜".$tmp2."</dd>\r\n";
	} elseif (empty($tmp1) && !empty($tmp2)) {
		print "					<dd>".$tmp2."</dd>\r\n";
	} elseif (!empty($tmp1) && empty($tmp2)) {
		print "					<dd>".$tmp1."</dd>\r\n";
	} elseif (empty($tmp1) && empty($tmp2)) {
		print "					<dd></dd>\r\n";
	}
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>登録申請者</dt>\r\n";
	print "					<dd>".dp($is->images[0]->registration_person)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>登録許可者</dt>\r\n";
	print "					<dd>".dp($is->images[0]->permission_person)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>登録申請アカウント</dt>\r\n";
	print "					<dd>".dp($is->images[0]->registration_account)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>登録許可アカウント</dt>\r\n";
	print "					<dd>".dp($is->images[0]->permission_account)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<dl>\r\n";
	print "					<dt>登録日</dt>\r\n";
	print "					<dd>".dp($is->images[0]->register_date)."</dd>\r\n";
	print "				</dl>\r\n";
	print "				<p class=\"ttl\">備考</p>\r\n";
	print "				<dl>\r\n";
	print "					<dt>備考</dt>\r\n";
	print "					<dd>".dp($is->images[0]->note)."</dd>\r\n";
	print "				</dl>\r\n";
	print "			</div>\r\n";
	print "		</div>\r\n";

}
?>