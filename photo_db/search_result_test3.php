
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
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="./js/jquery.js"  type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="./js/kirikae.js"     charset="utf-8"></script>
<script type="text/javascript" src="./js/common.js"      charset="utf-8"></script>
<script type="text/javascript" src="./js/image_disp.js"  charset="utf-8"></script>
<script type="text/javascript">
var uid = 1;
var g_index = '';var g_search_value = '';var g_syousai_content = '';var g_c_array = '';var g_init = '';<!--
var ua = navigator.userAgent.toLowerCase();
var is_pc_ie  = ( (ua.indexOf('msie') != -1 ) && ( ua.indexOf('win') != -1 ) && ( ua.indexOf('opera') == -1 ) && ( ua.indexOf('webtv') == -1 ) );

//function setClipboard(pid)
//{
//	var objkey = "code"+pid;
//	if (is_pc_ie)
//	{
//		var copytext = document.getElementById(objkey).createTextRange();
//		copytext.execCommand("Copy");
//	}else{
//		//document.getElementById('copy').innerHTML = "";
//		var swf = "<embed src='./js/setClipboard.swf' FlashVars='code="+encodeURIComponent(document.getElementById(objkey).value)+"' width='0' height='0' type='application/x-shockwave-flash'></embed>";
//		//alert(swf);
//		//document.getElementById('copy').innerHTML = swf;
//	}
//}

function setClipboard(pid) {
	var objkey = "code"+pid;
	var maintext = document.getElementById(objkey).value;

	if (window.clipboardData) {
		return (window.clipboardData.setData("Text", maintext));
	}
	else if (window.netscape) {
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
	return false;
}

/*
 * 関数名：pickupAll
 * 関数説明：画面の「チェックした写真をピックアップ」ボタンの処理
 * パラメタ：無し
 * 戻り値：無し
 */
function pickupAll()
{
	// クッキー識別子を作成します。
	var ck_id = "pickup_chk";
	// クッキーを取得します。
	var idstr_chk = getCookie(ck_id);

	if (idstr_chk != null && idstr_chk != "" && typeof(idstr_chk) != "undefined") {
		unChecked('img_chk','pickup_chk');

		// クッキー識別子を作成します。
		var ck_id = "pickup_images_id_" + uid;
		// クッキーを取得します。
		var idstr = getCookie(ck_id);
		var id_pickup_ary = new Array();
		id_pickup_ary = idstr.explode(",");

		var id_chk = new Array();
		id_chk_ary = idstr_chk.explode(",");
		for (var i = 0; i < id_chk_ary.length; i++)
		{
			// 既にクッキーで設定されているものについては、除外します。
			if (check_array(id_pickup_ary, id_chk_ary[i]) == -1)
			{
				if (idstr.length >= 1)
				{
					idstr = id_chk_ary[i] + "," + idstr;
					//idstr = idstr + "," + id_chk_ary[i];
				}
				else
				{
					idstr = id_chk_ary[i];
				}
			}
		}
		// クッキーを設定します。
		setCookie(ck_id, idstr);

		setCookie("bt_cnt",0);
		var url = "./pickup_ichiran1.php?p_pickupid=" + idstr;
		parent.middle2.location.href = url;
	} else {
		var msg = "イメージを選択してください。";
		alert(msg);
		return;
	}
}

/*
 * 関数名：select_change
 * 関数説明：画面の「表示数」を変わる時の処理
 * パラメタ：
 * obj:画面の「Select」コントロール
 * 戻り値：無し
 */
function select_change(obj)
{
	set_framewidth_image(obj);
	url = "./search_result.php";
	url1 = "";
	if (g_init != '')
	{
		url1 = url1 + "?init=" + g_init;
	}
	if (g_index != '')
	{
		if (url1.length > 0) url1 = url1 + "&selIndex=" + g_index;
		else url1 = url1 + "?selIndex=" + g_index;
	}
	if (g_search_value.length > 0)
	{
		if (url1.length > 0) url1 = url1 + "&search_value=" + encodeURIComponent(g_search_value);
		else url1 = url1 + "?search_value=" + encodeURIComponent(g_search_value);
	}
	if (g_syousai_content.length > 0)
	{
		if (url1.length > 0) url1 = url1 + "&syousai_content=" + encodeURIComponent(g_syousai_content);
		else url1 = url1 + "?syousai_content=" + encodeURIComponent(g_syousai_content);
	}
	if (g_c_array.length > 0)
	{
		if (url1.length > 0) url1 = url1 + "&c_array=" + encodeURIComponent(g_c_array);
		else url1 = url1 + "?c_array=" + encodeURIComponent(g_c_array);
	}

	if (url1.length > 0) url = url + url1;

	//setCookie("classname","");

	parent.bottom.location.href = url;
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
	var url = "./image_detail.php?p_photo_id=" + id + "&gamen_flg=2";
	setCookie("submit_url",parent.bottom.location.href);
	setCookie("bottom_url",parent.bottom.location.href);
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
	var obj_frame = top.document.getElementById('iframe_bottom');
	var ua = navigator.userAgent.toLowerCase();


	if (parseInt(obj_frame.height) < 1000 || g_init == 1)
	{
		//Firefox Browser
		if (obj_frame.contentDocument)
		{
			if (obj_frame.contentDocument.body.offsetHeight)
			{
				var frm_height = obj_frame.contentDocument.body.offsetHeight + 26;
				obj_frame.style.height = frm_height;
			}
		//IExplorer Browser
		} else if (obj_frame.Document) {
			if (obj_frame.Document.body.scrollHeight)
			{
				var frm_height = obj_frame.Document.body.scrollHeight;
				obj_frame.style.height = Number(frm_height);
			}
		}
	} else {
		if (ua.indexOf('msie') != -1)
		{
			obj_frame.style.height = parseInt(obj_frame.height);
			obj_frame.height = parseInt(obj_frame.height);
		} else {
			obj_frame.style.height = parseInt(obj_frame.height) + 100;
		}
	}
}

window.onload = function()
{
	init();
}
//-->
</script>
</head>
<!-- javascript ここまで -->
<body>
<div id="zentai">
	<div id="contents">
		<script type="text/javascript">
setImagesResultCookie("3229","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3230","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3231","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3232","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3233","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3234","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3235","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3236","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3237","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3238","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3239","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3240","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3241","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3242","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3243","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3244","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3245","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3246","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3247","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3248","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3249","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3250","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3251","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3252","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3253","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3254","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3255","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3256","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3257","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3258","images_result");
</script>
	<div class="pickup_ttl pickup_ttl_top">
		<div id='hl'><p>検索条件：   </p></div>
	</div>
	<div class="pickup_result" id="div_pickup_result">
<p>検索結果：21087アイテムが見つかりました</p>		<dl class="pickup_size">
			<dt class="size_name">サムネイルサイズ</dt>
			<dd class="pickup_size_bt">
				<ul>
					<li><a href="#" class="big" title="大" onclick='change_class(200);search_resultbig();return false;' >大</a></li>
					<li><a href="#" class="midlle" title="中" onclick='change_class(140);search_resultmidlle();return false;'>中</a></li>
					<li><a href="#" class="small" title="小" onclick='change_class(100);search_resultsmall();return false;'>小</a></li>
				</ul>
			</dd>
			<dd class="pickup_number"> 表示数
<select name="select2" id="select2" onChange="select_change(this);return false;" disabled>
		<option value="30" selected="selected">30</option>
		<option value="60">60</option>
		<option value="90">90</option>
</select>
			</dd>
		</dl>
	</div>
<div class="pickup_bt pickup_bt_top">
		<ul>
			<li class="btn"><a href="#"><img src="parts/bt_pickup.gif" alt="チェックした写真をピックアップ" title="チェックした写真をピックアップ" width="163" height="22" onclick="pickupAll();return false;"/></a></li>
			<li class="btn"><a href="#"><img src="parts/bt_pickup_clear.gif" alt="チェックをクリア" title="チェックをクリア" width="93" height="22" onclick="unChecked('img_chk','pickup_chk');return false;"/></a></li>
		</ul>
		<dl class="icon_explanation">
			<dt>アイコンの説明：</dt>
			<dd>
				<ul>
					<li title="コピー" class="icon_explanation_copy">クリップボードにコピー</li>
					<li title="ピックアップ" class="icon_explanation_pickup">ピックアップ</li>
					<li title="情報" class="icon_explanation_info">画像詳細情報</li>
				</ul>
			</dd>
		</dl>
		<dl class="expiration_date">
			<dt>有効期限：</dt>
			<dd class="three_months">3ヵ月未満</dd>
			<dd class="six_months">6ヵ月未満</dd>
		</dl>
		<ul class="txt">
		<li>
<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="first page">[1]</a>&nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="previous page">BACK<<</a>&nbsp;<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="page 1">1</a> &nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="page 2">2</a> &nbsp;<a href="/photo_db/search_result.php?pageID=3&amp;ppage=30#hl" title="page 3">3</a> &nbsp;<a href="/photo_db/search_result.php?pageID=4&amp;ppage=30#hl" title="page 4">4</a> &nbsp;<a href="/photo_db/search_result.php?pageID=5&amp;ppage=30#hl" title="page 5">5</a> &nbsp;<a href="/photo_db/search_result.php?pageID=6&amp;ppage=30#hl" title="page 6">6</a> &nbsp;<a href="/photo_db/search_result.php?pageID=7&amp;ppage=30#hl" title="page 7">7</a> &nbsp;<a href="/photo_db/search_result.php?pageID=8&amp;ppage=30#hl" title="page 8">8</a> &nbsp;<a href="/photo_db/search_result.php?pageID=9&amp;ppage=30#hl" title="page 9">9</a> &nbsp;<a href="/photo_db/search_result.php?pageID=10&amp;ppage=30#hl" title="page 10">10</a> &nbsp;<a href="/photo_db/search_result.php?pageID=11&amp;ppage=30#hl" title="page 11">11</a> &nbsp;<a href="/photo_db/search_result.php?pageID=12&amp;ppage=30#hl" title="page 12">12</a> &nbsp;<a href="/photo_db/search_result.php?pageID=13&amp;ppage=30#hl" title="page 13">13</a> &nbsp;<a href="/photo_db/search_result.php?pageID=14&amp;ppage=30#hl" title="page 14">14</a> &nbsp;<a href="/photo_db/search_result.php?pageID=15&amp;ppage=30#hl" title="page 15">15</a> &nbsp;<a href="/photo_db/search_result.php?pageID=16&amp;ppage=30#hl" title="page 16">16</a> &nbsp;<a href="/photo_db/search_result.php?pageID=17&amp;ppage=30#hl" title="page 17">17</a> &nbsp;<a href="/photo_db/search_result.php?pageID=18&amp;ppage=30#hl" title="page 18">18</a> &nbsp;<a href="/photo_db/search_result.php?pageID=19&amp;ppage=30#hl" title="page 19">19</a> &nbsp;<a href="/photo_db/search_result.php?pageID=20&amp;ppage=30#hl" title="page 20">20</a> &nbsp;<a href="/photo_db/search_result.php?pageID=21&amp;ppage=30#hl" title="page 21">21</a> &nbsp;<a href="/photo_db/search_result.php?pageID=22&amp;ppage=30#hl" title="page 22">22</a> &nbsp;<a href="/photo_db/search_result.php?pageID=23&amp;ppage=30#hl" title="page 23">23</a> &nbsp;<a href="/photo_db/search_result.php?pageID=24&amp;ppage=30#hl" title="page 24">24</a> &nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="page 25">25</a> &nbsp;<a href="/photo_db/search_result.php?pageID=26&amp;ppage=30#hl" title="page 26">26</a> &nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="page 27">27</a> &nbsp;<a href="/photo_db/search_result.php?pageID=28&amp;ppage=30#hl" title="page 28">28</a> &nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="page 29">29</a> &nbsp;30 &nbsp;&nbsp;<a href="/photo_db/search_result.php?pageID=31&amp;ppage=30#hl" title="next page">NEXT>></a>&nbsp;<a href="/photo_db/search_result.php?pageID=703&amp;ppage=30#hl" title="last page">[703]</a>		</li>
		</ul>
</div>
<div id = "photo_contents" class="photo_contents">
<div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00207.jpg市街とアディージェ川' id='code3229' name='code3229' />
<dt class='number'>00000-ELM08-00207.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/200812161843096122th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3229" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3229");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3229", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3229"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>市街とアディージェ川</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00208.jpg蔵王の御釜' id='code3230' name='code3230' />
<dt class='number'>00000-ELM08-00208.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161843095489th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3230" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3230");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3230", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3230"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>蔵王の御釜</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00209.jpgコーラルポイント　ゴルフ場' id='code3231' name='code3231' />
<dt class='number'>00000-ELM08-00209.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/20081216184310495th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3231" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3231");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3231", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3231"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>コーラルポイント　ゴルフ場</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00210.jpg花菖蒲咲く殿町通り　' id='code3232' name='code3232' />
<dt class='number'>00000-ELM08-00210.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/5/200812161843101358th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3232" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3232");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3232", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3232"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>花菖蒲咲く殿町通り　</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00211.jpg草津温泉　湯畑' id='code3233' name='code3233' />
<dt class='number'>00000-ELM08-00211.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161843112960th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3233" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3233");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3233", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3233"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>草津温泉　湯畑</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00212.jpg虹とイグアスの滝　イグアス国立公園' id='code3234' name='code3234' />
<dt class='number'>00000-ELM08-00212.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/200812161843117239th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3234" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3234");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3234", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3234"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>虹とイグアスの滝　イグアス&hellip;</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00213.jpgゴンドラと教会' id='code3235' name='code3235' />
<dt class='number'>00000-ELM08-00213.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161843114246th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3235" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3235");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3235", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3235"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ゴンドラと教会</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00214.jpg氷河特急　オーバーアルプ峠' id='code3236' name='code3236' />
<dt class='number'>00000-ELM08-00214.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161843122459th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3236" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3236");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3236", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3236"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>氷河特急　オーバーアルプ峠</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00215.jpgカッパドキア　夕陽' id='code3237' name='code3237' />
<dt class='number'>00000-ELM08-00215.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161843123329th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3237" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3237");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3237", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3237"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>カッパドキア　夕陽</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00216.jpgバザール　烏魯木斉　シルクロード' id='code3238' name='code3238' />
<dt class='number'>00000-ELM08-00216.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161843132347th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3238" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3238");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3238", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3238"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>バザール　烏魯木斉　シルク&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00217.jpg樽とワイン' id='code3239' name='code3239' />
<dt class='number'>00000-ELM08-00217.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/200812161843131182th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3239" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3239");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3239", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3239"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>樽とワイン</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00218.jpg函館夜景　' id='code3240' name='code3240' />
<dt class='number'>00000-ELM08-00218.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161843595793th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3240" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3240");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3240", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3240"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>函館夜景　</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00219.jpgサントリーニ島　教会の続く風景' id='code3241' name='code3241' />
<dt class='number'>00000-ELM08-00219.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/20081216184359364th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3241" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3241");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3241", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3241"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>サントリーニ島　教会の続く&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00220.jpgクノッソス宮殿' id='code3242' name='code3242' />
<dt class='number'>00000-ELM08-00220.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/200812161843595674th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3242" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3242");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3242", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3242"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>クノッソス宮殿</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00221.jpgクシャダス郊外　レディースビーチ' id='code3243' name='code3243' />
<dt class='number'>00000-ELM08-00221.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161844006605th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3243" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3243");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3243", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3243"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>クシャダス郊外　レディース&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00222.jpg大劇場　エフェス遺跡' id='code3244' name='code3244' />
<dt class='number'>00000-ELM08-00222.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/6/200812161844008144th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3244" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3244");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3244", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3244"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>大劇場　エフェス遺跡</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00223.jpg宝川温泉露天風呂' id='code3245' name='code3245' />
<dt class='number'>00000-ELM08-00223.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/200812161844019417th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3245" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3245");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3245", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3245"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>宝川温泉露天風呂</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00224.jpg由布島の植物園' id='code3246' name='code3246' />
<dt class='number'>00000-ELM08-00224.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/200812161844018956th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3246" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3246");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3246", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3246"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>由布島の植物園</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00225.jpg唐人墓' id='code3247' name='code3247' />
<dt class='number'>00000-ELM08-00225.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/20081216184402823th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3247" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3247");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3247", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3247"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>唐人墓</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00226.jpg菜の花畑と大雪山旭岳' id='code3248' name='code3248' />
<dt class='number'>00000-ELM08-00226.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161844021027th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3248" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3248");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3248", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3248"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>菜の花畑と大雪山旭岳</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00227.jpg広東料理' id='code3249' name='code3249' />
<dt class='number'>00000-ELM08-00227.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161844035406th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3249" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3249");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3249", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3249"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>広東料理</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00228.jpg二階建て電車　コーズウェイベイ　トラム' id='code3250' name='code3250' />
<dt class='number'>00000-ELM08-00228.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/5/200812161844039157th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3250" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3250");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3250", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3250"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>二階建て電車　コーズウェイ&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00229.jpgピラミッド' id='code3251' name='code3251' />
<dt class='number'>00000-ELM08-00229.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/4/200812161844057265th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3251" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3251");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3251", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3251"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ピラミッド</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00230.jpg首里城と守礼門のライトアップ' id='code3252' name='code3252' />
<dt class='number'>00000-ELM08-00230.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/5/200812161844066283th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3252" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3252");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3252", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3252"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>首里城と守礼門のライトアッ&hellip;</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00231.jpg谷川岳　一ノ倉岳' id='code3253' name='code3253' />
<dt class='number'>00000-ELM08-00231.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161844068873th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3253" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3253");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3253", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3253"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>谷川岳　一ノ倉岳</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00232.jpg水無しの立岩と神威岬' id='code3254' name='code3254' />
<dt class='number'>00000-ELM08-00232.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161844075440th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3254" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3254");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3254", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3254"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>水無しの立岩と神威岬</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00233.jpgローレンシャン高原　トレンブラン湖' id='code3255' name='code3255' />
<dt class='number'>00000-ELM08-00233.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/200812161844079188th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3255" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3255");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3255", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3255"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ローレンシャン高原　トレン&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00234.jpgヤマツツジと茶臼岳' id='code3256' name='code3256' />
<dt class='number'>00000-ELM08-00234.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161844085537th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3256" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3256");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3256", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3256"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ヤマツツジと茶臼岳</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00235.jpg河口湖と富士山' id='code3257' name='code3257' />
<dt class='number'>00000-ELM08-00235.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/200812161844104319th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3257" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3257");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3257", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3257"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>河口湖と富士山</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00236.jpg身延山久遠寺　しだれ桜' id='code3258' name='code3258' />
<dt class='number'>00000-ELM08-00236.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161844112223th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3258" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3258");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3258", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3258"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>身延山久遠寺　しだれ桜</dd>
</dl>
</div></div>
<div class="pickup_bt pickup_bt_bottom">
		<ul class="txt">
		<li>
<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="first page">[1]</a>&nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="previous page">BACK<<</a>&nbsp;<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="page 1">1</a> &nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="page 2">2</a> &nbsp;<a href="/photo_db/search_result.php?pageID=3&amp;ppage=30#hl" title="page 3">3</a> &nbsp;<a href="/photo_db/search_result.php?pageID=4&amp;ppage=30#hl" title="page 4">4</a> &nbsp;<a href="/photo_db/search_result.php?pageID=5&amp;ppage=30#hl" title="page 5">5</a> &nbsp;<a href="/photo_db/search_result.php?pageID=6&amp;ppage=30#hl" title="page 6">6</a> &nbsp;<a href="/photo_db/search_result.php?pageID=7&amp;ppage=30#hl" title="page 7">7</a> &nbsp;<a href="/photo_db/search_result.php?pageID=8&amp;ppage=30#hl" title="page 8">8</a> &nbsp;<a href="/photo_db/search_result.php?pageID=9&amp;ppage=30#hl" title="page 9">9</a> &nbsp;<a href="/photo_db/search_result.php?pageID=10&amp;ppage=30#hl" title="page 10">10</a> &nbsp;<a href="/photo_db/search_result.php?pageID=11&amp;ppage=30#hl" title="page 11">11</a> &nbsp;<a href="/photo_db/search_result.php?pageID=12&amp;ppage=30#hl" title="page 12">12</a> &nbsp;<a href="/photo_db/search_result.php?pageID=13&amp;ppage=30#hl" title="page 13">13</a> &nbsp;<a href="/photo_db/search_result.php?pageID=14&amp;ppage=30#hl" title="page 14">14</a> &nbsp;<a href="/photo_db/search_result.php?pageID=15&amp;ppage=30#hl" title="page 15">15</a> &nbsp;<a href="/photo_db/search_result.php?pageID=16&amp;ppage=30#hl" title="page 16">16</a> &nbsp;<a href="/photo_db/search_result.php?pageID=17&amp;ppage=30#hl" title="page 17">17</a> &nbsp;<a href="/photo_db/search_result.php?pageID=18&amp;ppage=30#hl" title="page 18">18</a> &nbsp;<a href="/photo_db/search_result.php?pageID=19&amp;ppage=30#hl" title="page 19">19</a> &nbsp;<a href="/photo_db/search_result.php?pageID=20&amp;ppage=30#hl" title="page 20">20</a> &nbsp;<a href="/photo_db/search_result.php?pageID=21&amp;ppage=30#hl" title="page 21">21</a> &nbsp;<a href="/photo_db/search_result.php?pageID=22&amp;ppage=30#hl" title="page 22">22</a> &nbsp;<a href="/photo_db/search_result.php?pageID=23&amp;ppage=30#hl" title="page 23">23</a> &nbsp;<a href="/photo_db/search_result.php?pageID=24&amp;ppage=30#hl" title="page 24">24</a> &nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="page 25">25</a> &nbsp;<a href="/photo_db/search_result.php?pageID=26&amp;ppage=30#hl" title="page 26">26</a> &nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="page 27">27</a> &nbsp;<a href="/photo_db/search_result.php?pageID=28&amp;ppage=30#hl" title="page 28">28</a> &nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="page 29">29</a> &nbsp;30 &nbsp;&nbsp;<a href="/photo_db/search_result.php?pageID=31&amp;ppage=30#hl" title="next page">NEXT>></a>&nbsp;<a href="/photo_db/search_result.php?pageID=703&amp;ppage=30#hl" title="last page">[703]</a>		</li>
		</ul>
		<ul>
			<li class="btn"><a href="#"><img src="parts/bt_pickup.gif" alt="チェックした写真をピックアップ" width="163" height="22" onclick="pickupAll();return false;"/></a></li>
			<li class="btn"><a href="#"><img src="parts/bt_pickup_clear.gif" alt="チェックをクリア" width="93" height="22" onclick="unChecked('img_chk','pickup_chk');return false;"/></a></li>
		</ul>
		<dl class="icon_explanation">
			<dt>アイコンの説明：</dt>
			<dd>
				<ul>
					<li title="コピー" class="icon_explanation_copy">クリップボードにコピー</li>
					<li title="ピックアップ" class="icon_explanation_pickup">ピックアップ</li>
					<li title="情報" class="icon_explanation_info">画像詳細情報</li>
				</ul>
			</dd>
		</dl>
		<dl class="expiration_date">
			<dt>有効期限：</dt>
			<dd class="three_months">3ヵ月未満</dd>
			<dd class="six_months">6ヵ月未満</dd>
		</dl>
</div>
		<dl>
		<dd style='height:11px'>
		</dd>
		</dl>
	<div class="pickup_ttl pickup_ttl_bottom">
		<div id='hl'><p>検索条件：   </p></div>
	</div>
	<div class="pickup_result" id="div_pickup_result">
<p>検索結果：21087アイテムが見つかりました</p>		<dl class="pickup_size">
			<dt class="size_name">サムネイルサイズ</dt>
			<dd class="pickup_size_bt">
				<ul>
					<li><a href="#" class="big" title="大" onclick='change_class(200);search_resultbig();return false;' >大</a></li>
					<li><a href="#" class="midlle" title="中" onclick='change_class(140);search_resultmidlle();return false;'>中</a></li>
					<li><a href="#" class="small" title="小" onclick='change_class(100);search_resultsmall();return false;'>小</a></li>
				</ul>
			</dd>
			<dd class="pickup_number"> 表示数
<select name="select2" id="select2" onChange="select_change(this);return false;" disabled>
		<option value="30" selected="selected">30</option>
		<option value="60">60</option>
		<option value="90">90</option>
</select>
			</dd>
		</dl>
	</div>
<script type='text/javascript' src='./js/image_disp.js'  charset='utf-8'></script>
<script type="text/javascript">
set_framewidth_php();
</script>
<script type="text/javascript">
document.getElementsByName('select2')[0].disabled = false;
document.getElementsByName('select2')[1].disabled = false;
var strhtml = "<a href='#'><img src='parts/search_bt.gif' alt='検索' onclick='go_search();return false;'/></a>";
strhtml = strhtml + "<a href='#'><img src='parts/bt_re_set.gif' alt='リセット'  onclick='clear_all_contents();return false;' /></a>";
strhtml = strhtml + "<span><a href='#' class='bt_syousai' onclick='changedetail_search();return false;'>詳細条件を追加する</a></span>";
var obj = window.parent.frames[1].document.getElementById('search_bt');
if(obj)
{
	obj.innerHTML = strhtml;
}
var objs=window.parent.frames[1].document.getElementsByTagName('input');
for(var i=0;i<objs.length;i++)
{
	objs[i].disabled = false;
}
var objs=window.parent.frames[1].document.getElementsByTagName('select');
for(var i=0;i<objs.length;i++)
{
	objs[i].disabled = false;
}
</script>
	</div>
</div>
</body>
</html>
