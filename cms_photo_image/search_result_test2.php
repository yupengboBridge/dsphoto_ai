
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
setImagesResultCookie("3105","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3106","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3107","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3108","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3109","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3110","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3111","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3112","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3113","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3114","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3115","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3116","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3117","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3118","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3119","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3120","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3121","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3122","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3123","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3124","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3125","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3126","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3127","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3128","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3129","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3130","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3131","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3132","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3133","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3134","images_result");
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
<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="first page">[1]</a>&nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="previous page">BACK<<</a>&nbsp;<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="page 1">1</a> &nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="page 2">2</a> &nbsp;<a href="/photo_db/search_result.php?pageID=3&amp;ppage=30#hl" title="page 3">3</a> &nbsp;<a href="/photo_db/search_result.php?pageID=4&amp;ppage=30#hl" title="page 4">4</a> &nbsp;<a href="/photo_db/search_result.php?pageID=5&amp;ppage=30#hl" title="page 5">5</a> &nbsp;<a href="/photo_db/search_result.php?pageID=6&amp;ppage=30#hl" title="page 6">6</a> &nbsp;<a href="/photo_db/search_result.php?pageID=7&amp;ppage=30#hl" title="page 7">7</a> &nbsp;<a href="/photo_db/search_result.php?pageID=8&amp;ppage=30#hl" title="page 8">8</a> &nbsp;<a href="/photo_db/search_result.php?pageID=9&amp;ppage=30#hl" title="page 9">9</a> &nbsp;<a href="/photo_db/search_result.php?pageID=10&amp;ppage=30#hl" title="page 10">10</a> &nbsp;<a href="/photo_db/search_result.php?pageID=11&amp;ppage=30#hl" title="page 11">11</a> &nbsp;<a href="/photo_db/search_result.php?pageID=12&amp;ppage=30#hl" title="page 12">12</a> &nbsp;<a href="/photo_db/search_result.php?pageID=13&amp;ppage=30#hl" title="page 13">13</a> &nbsp;<a href="/photo_db/search_result.php?pageID=14&amp;ppage=30#hl" title="page 14">14</a> &nbsp;<a href="/photo_db/search_result.php?pageID=15&amp;ppage=30#hl" title="page 15">15</a> &nbsp;<a href="/photo_db/search_result.php?pageID=16&amp;ppage=30#hl" title="page 16">16</a> &nbsp;<a href="/photo_db/search_result.php?pageID=17&amp;ppage=30#hl" title="page 17">17</a> &nbsp;<a href="/photo_db/search_result.php?pageID=18&amp;ppage=30#hl" title="page 18">18</a> &nbsp;<a href="/photo_db/search_result.php?pageID=19&amp;ppage=30#hl" title="page 19">19</a> &nbsp;<a href="/photo_db/search_result.php?pageID=20&amp;ppage=30#hl" title="page 20">20</a> &nbsp;<a href="/photo_db/search_result.php?pageID=21&amp;ppage=30#hl" title="page 21">21</a> &nbsp;<a href="/photo_db/search_result.php?pageID=22&amp;ppage=30#hl" title="page 22">22</a> &nbsp;<a href="/photo_db/search_result.php?pageID=23&amp;ppage=30#hl" title="page 23">23</a> &nbsp;<a href="/photo_db/search_result.php?pageID=24&amp;ppage=30#hl" title="page 24">24</a> &nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="page 25">25</a> &nbsp;26 &nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="page 27">27</a> &nbsp;<a href="/photo_db/search_result.php?pageID=28&amp;ppage=30#hl" title="page 28">28</a> &nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="page 29">29</a> &nbsp;<a href="/photo_db/search_result.php?pageID=30&amp;ppage=30#hl" title="page 30">30</a> &nbsp;&nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="next page">NEXT>></a>&nbsp;<a href="/photo_db/search_result.php?pageID=703&amp;ppage=30#hl" title="last page">[703]</a>		</li>
		</ul>
</div>
<div id = "photo_contents" class="photo_contents">
<div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00083.jpg紅葉のいろは坂' id='code3105' name='code3105' />
<dt class='number'>00000-ELM08-00083.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/200812161842024037th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3105" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3105");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3105", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3105"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>紅葉のいろは坂</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00084.jpg新緑の渡月橋' id='code3106' name='code3106' />
<dt class='number'>00000-ELM08-00084.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161842023670th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3106" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3106");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3106", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3106"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>新緑の渡月橋</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00085.jpg草千里と烏帽子岳' id='code3107' name='code3107' />
<dt class='number'>00000-ELM08-00085.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161842039942th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3107" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3107");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3107", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3107"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>草千里と烏帽子岳</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00086.jpg草千里と烏帽子岳' id='code3108' name='code3108' />
<dt class='number'>00000-ELM08-00086.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/5/200812161842035222th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3108" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3108");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3108", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3108"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>草千里と烏帽子岳</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00087.jpg桜と中尊寺金色堂' id='code3109' name='code3109' />
<dt class='number'>00000-ELM08-00087.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/6/200812161842048952th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3109" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3109");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3109", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3109"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>桜と中尊寺金色堂</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00088.jpg成田山新勝寺' id='code3110' name='code3110' />
<dt class='number'>00000-ELM08-00088.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161842058551th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3110" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3110");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3110", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3110"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>成田山新勝寺</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00089.jpgシェーンブルン宮殿の大広間' id='code3111' name='code3111' />
<dt class='number'>00000-ELM08-00089.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/200812161842059432th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3111" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3111");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3111", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3111"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>シェーンブルン宮殿の大広間</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00090.jpg台湾民主記念館　' id='code3112' name='code3112' />
<dt class='number'>00000-ELM08-00090.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/6/200812161842065965th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3112" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3112");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3112", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3112"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>台湾民主記念館　</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00091.jpg故宮博物院' id='code3113' name='code3113' />
<dt class='number'>00000-ELM08-00091.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161842067044th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3113" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3113");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3113", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3113"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>故宮博物院</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00092.jpg故宮博物院' id='code3114' name='code3114' />
<dt class='number'>00000-ELM08-00092.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161842079267th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3114" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3114");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3114", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3114"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>故宮博物院</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00093.jpgバラの花咲く旧古河庭園' id='code3115' name='code3115' />
<dt class='number'>00000-ELM08-00093.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/20081216184207154th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3115" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3115");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3115", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3115"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>バラの花咲く旧古河庭園</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00094.jpg鶴岡八幡宮源平池のサクラ' id='code3116' name='code3116' />
<dt class='number'>00000-ELM08-00094.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161842079811th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3116" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3116");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3116", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3116"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>鶴岡八幡宮源平池のサクラ</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00095.jpg中正公園より望む基隆港と市街' id='code3117' name='code3117' />
<dt class='number'>00000-ELM08-00095.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161842102495th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3117" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3117");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3117", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3117"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>中正公園より望む基隆港と市&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00096.jpg根尾淡墨桜' id='code3118' name='code3118' />
<dt class='number'>00000-ELM08-00096.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161842105342th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3118" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3118");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3118", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3118"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>根尾淡墨桜</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00097.jpg夏の白川郷' id='code3119' name='code3119' />
<dt class='number'>00000-ELM08-00097.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/20081216184210719th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3119" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3119");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3119", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3119"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>夏の白川郷</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00098.jpg白川郷' id='code3120' name='code3120' />
<dt class='number'>00000-ELM08-00098.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/4/200812161842112925th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3120" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3120");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3120", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3120"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>白川郷</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00099.jpg白川郷萩町' id='code3121' name='code3121' />
<dt class='number'>00000-ELM08-00099.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/6/200812161842124987th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3121" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3121");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3121", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3121"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>白川郷萩町</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00100.jpgマチュピチュ遺跡　太陽の神殿' id='code3122' name='code3122' />
<dt class='number'>00000-ELM08-00100.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/4/200812161842125186th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3122" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3122");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3122", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3122"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>マチュピチュ遺跡　太陽の神&hellip;</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00101.jpg峨眉山　金頂' id='code3123' name='code3123' />
<dt class='number'>00000-ELM08-00101.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161842134768th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3123" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3123");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3123", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3123"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>峨眉山　金頂</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00102.jpg赤の広場　聖ワシリー寺院　　スパスカヤ塔' id='code3124' name='code3124' />
<dt class='number'>00000-ELM08-00102.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/20081216184214116th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3124" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3124");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3124", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3124"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>赤の広場　聖ワシリー寺院　&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00103.jpg運河と血の上の教会　' id='code3125' name='code3125' />
<dt class='number'>00000-ELM08-00103.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161842147334th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3125" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3125");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3125", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3125"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>運河と血の上の教会　</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00104.jpg蘇州　運河' id='code3126' name='code3126' />
<dt class='number'>00000-ELM08-00104.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/6/200812161842143780th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3126" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3126");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3126", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3126"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>蘇州　運河</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00105.jpgケンジントン宮殿' id='code3127' name='code3127' />
<dt class='number'>00000-ELM08-00105.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161842151633th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3127" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3127");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3127", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3127"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ケンジントン宮殿</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00106.jpgルクソール神殿　ラムセスⅡ世像' id='code3128' name='code3128' />
<dt class='number'>00000-ELM08-00106.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161842153240th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3128" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3128");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3128", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3128"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ルクソール神殿　ラムセスⅡ&hellip;</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-ELM08-00107.jpgスフィンクスとピラミッド' id='code3129' name='code3129' />
<dt class='number'>00000-ELM08-00107.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/200812161842167768th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3129" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3129");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3129", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3129"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>スフィンクスとピラミッド</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00108.jpgサハラ砂漠を行く観光ラクダ' id='code3130' name='code3130' />
<dt class='number'>00000-ELM08-00108.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161842178084th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3130" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3130");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3130", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3130"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>サハラ砂漠を行く観光ラクダ</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00109.jpgパムッカレ' id='code3131' name='code3131' />
<dt class='number'>00000-ELM08-00109.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161842171906th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3131" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3131");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3131", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3131"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>パムッカレ</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00110.jpg夕日に染まるアルハンブラ宮殿' id='code3132' name='code3132' />
<dt class='number'>00000-ELM08-00110.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/4/200812161842176333th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3132" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3132");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3132", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3132"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>夕日に染まるアルハンブラ宮&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00111.jpgトレムブラン山と湖' id='code3133' name='code3133' />
<dt class='number'>00000-ELM08-00111.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/200812161842188658th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3133" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3133");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3133", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3133"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>トレムブラン山と湖</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-ELM08-00112.jpgルービンとマウントクック' id='code3134' name='code3134' />
<dt class='number'>00000-ELM08-00112.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161842183702th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3134" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3134");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3134", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3134"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ルービンとマウントクック</dd>
</dl>
</div></div>
<div class="pickup_bt pickup_bt_bottom">
		<ul class="txt">
		<li>
<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="first page">[1]</a>&nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="previous page">BACK<<</a>&nbsp;<a href="/photo_db/search_result.php?pageID=1&amp;ppage=30#hl" title="page 1">1</a> &nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="page 2">2</a> &nbsp;<a href="/photo_db/search_result.php?pageID=3&amp;ppage=30#hl" title="page 3">3</a> &nbsp;<a href="/photo_db/search_result.php?pageID=4&amp;ppage=30#hl" title="page 4">4</a> &nbsp;<a href="/photo_db/search_result.php?pageID=5&amp;ppage=30#hl" title="page 5">5</a> &nbsp;<a href="/photo_db/search_result.php?pageID=6&amp;ppage=30#hl" title="page 6">6</a> &nbsp;<a href="/photo_db/search_result.php?pageID=7&amp;ppage=30#hl" title="page 7">7</a> &nbsp;<a href="/photo_db/search_result.php?pageID=8&amp;ppage=30#hl" title="page 8">8</a> &nbsp;<a href="/photo_db/search_result.php?pageID=9&amp;ppage=30#hl" title="page 9">9</a> &nbsp;<a href="/photo_db/search_result.php?pageID=10&amp;ppage=30#hl" title="page 10">10</a> &nbsp;<a href="/photo_db/search_result.php?pageID=11&amp;ppage=30#hl" title="page 11">11</a> &nbsp;<a href="/photo_db/search_result.php?pageID=12&amp;ppage=30#hl" title="page 12">12</a> &nbsp;<a href="/photo_db/search_result.php?pageID=13&amp;ppage=30#hl" title="page 13">13</a> &nbsp;<a href="/photo_db/search_result.php?pageID=14&amp;ppage=30#hl" title="page 14">14</a> &nbsp;<a href="/photo_db/search_result.php?pageID=15&amp;ppage=30#hl" title="page 15">15</a> &nbsp;<a href="/photo_db/search_result.php?pageID=16&amp;ppage=30#hl" title="page 16">16</a> &nbsp;<a href="/photo_db/search_result.php?pageID=17&amp;ppage=30#hl" title="page 17">17</a> &nbsp;<a href="/photo_db/search_result.php?pageID=18&amp;ppage=30#hl" title="page 18">18</a> &nbsp;<a href="/photo_db/search_result.php?pageID=19&amp;ppage=30#hl" title="page 19">19</a> &nbsp;<a href="/photo_db/search_result.php?pageID=20&amp;ppage=30#hl" title="page 20">20</a> &nbsp;<a href="/photo_db/search_result.php?pageID=21&amp;ppage=30#hl" title="page 21">21</a> &nbsp;<a href="/photo_db/search_result.php?pageID=22&amp;ppage=30#hl" title="page 22">22</a> &nbsp;<a href="/photo_db/search_result.php?pageID=23&amp;ppage=30#hl" title="page 23">23</a> &nbsp;<a href="/photo_db/search_result.php?pageID=24&amp;ppage=30#hl" title="page 24">24</a> &nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="page 25">25</a> &nbsp;26 &nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="page 27">27</a> &nbsp;<a href="/photo_db/search_result.php?pageID=28&amp;ppage=30#hl" title="page 28">28</a> &nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="page 29">29</a> &nbsp;<a href="/photo_db/search_result.php?pageID=30&amp;ppage=30#hl" title="page 30">30</a> &nbsp;&nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="next page">NEXT>></a>&nbsp;<a href="/photo_db/search_result.php?pageID=703&amp;ppage=30#hl" title="last page">[703]</a>		</li>
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
