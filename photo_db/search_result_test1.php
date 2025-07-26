
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
setImagesResultCookie("1","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("2","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("3","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("4","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("5","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("6","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("7","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("8","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("9","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("11","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("12","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("13","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("14","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("15","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("16","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("17","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("18","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("19","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("20","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("21","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("22","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("23","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("24","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("25","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("26","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("27","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("28","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("29","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("30","images_result");
</script>
<script type="text/javascript">
setImagesResultCookie("31","images_result");
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
1 &nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="page 2">2</a> &nbsp;<a href="/photo_db/search_result.php?pageID=3&amp;ppage=30#hl" title="page 3">3</a> &nbsp;<a href="/photo_db/search_result.php?pageID=4&amp;ppage=30#hl" title="page 4">4</a> &nbsp;<a href="/photo_db/search_result.php?pageID=5&amp;ppage=30#hl" title="page 5">5</a> &nbsp;<a href="/photo_db/search_result.php?pageID=6&amp;ppage=30#hl" title="page 6">6</a> &nbsp;<a href="/photo_db/search_result.php?pageID=7&amp;ppage=30#hl" title="page 7">7</a> &nbsp;<a href="/photo_db/search_result.php?pageID=8&amp;ppage=30#hl" title="page 8">8</a> &nbsp;<a href="/photo_db/search_result.php?pageID=9&amp;ppage=30#hl" title="page 9">9</a> &nbsp;<a href="/photo_db/search_result.php?pageID=10&amp;ppage=30#hl" title="page 10">10</a> &nbsp;<a href="/photo_db/search_result.php?pageID=11&amp;ppage=30#hl" title="page 11">11</a> &nbsp;<a href="/photo_db/search_result.php?pageID=12&amp;ppage=30#hl" title="page 12">12</a> &nbsp;<a href="/photo_db/search_result.php?pageID=13&amp;ppage=30#hl" title="page 13">13</a> &nbsp;<a href="/photo_db/search_result.php?pageID=14&amp;ppage=30#hl" title="page 14">14</a> &nbsp;<a href="/photo_db/search_result.php?pageID=15&amp;ppage=30#hl" title="page 15">15</a> &nbsp;<a href="/photo_db/search_result.php?pageID=16&amp;ppage=30#hl" title="page 16">16</a> &nbsp;<a href="/photo_db/search_result.php?pageID=17&amp;ppage=30#hl" title="page 17">17</a> &nbsp;<a href="/photo_db/search_result.php?pageID=18&amp;ppage=30#hl" title="page 18">18</a> &nbsp;<a href="/photo_db/search_result.php?pageID=19&amp;ppage=30#hl" title="page 19">19</a> &nbsp;<a href="/photo_db/search_result.php?pageID=20&amp;ppage=30#hl" title="page 20">20</a> &nbsp;<a href="/photo_db/search_result.php?pageID=21&amp;ppage=30#hl" title="page 21">21</a> &nbsp;<a href="/photo_db/search_result.php?pageID=22&amp;ppage=30#hl" title="page 22">22</a> &nbsp;<a href="/photo_db/search_result.php?pageID=23&amp;ppage=30#hl" title="page 23">23</a> &nbsp;<a href="/photo_db/search_result.php?pageID=24&amp;ppage=30#hl" title="page 24">24</a> &nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="page 25">25</a> &nbsp;<a href="/photo_db/search_result.php?pageID=26&amp;ppage=30#hl" title="page 26">26</a> &nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="page 27">27</a> &nbsp;<a href="/photo_db/search_result.php?pageID=28&amp;ppage=30#hl" title="page 28">28</a> &nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="page 29">29</a> &nbsp;<a href="/photo_db/search_result.php?pageID=30&amp;ppage=30#hl" title="page 30">30</a> &nbsp;&nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="next page">NEXT>></a>&nbsp;<a href="/photo_db/search_result.php?pageID=703&amp;ppage=30#hl" title="last page">[703]</a>		</li>
		</ul>
</div>
<div id = "photo_contents" class="photo_contents">
<div><dl class='photo140'>
<input type='hidden' value='00000-EBP07-00000.jpg八幡坂' id='code1' name='code1' />
<dt class='number'>00000-EBP07-00000.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161747011216th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="1" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("1");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("1", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("1"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>八幡坂</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00001.jpg函館市街　函館山より' id='code2' name='code2' />
<dt class='number'>00000-EBP07-00001.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161747011314th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="2" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("2");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("2", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("2"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>函館市街　函館山より</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00002.jpg小樽運河' id='code3' name='code3' />
<dt class='number'>00000-EBP07-00002.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161747021684th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="3" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("3");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("3", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("3"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>小樽運河</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00003.jpg万里の長城' id='code4' name='code4' />
<dt class='number'>00000-EBP07-00003.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/6/200812161747029712th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="4" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("4");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("4", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("4"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>万里の長城</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00004.jpg九寨溝' id='code5' name='code5' />
<dt class='number'>00000-EBP07-00004.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161747037261th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="5" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("5");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("5", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("5"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>九寨溝</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00005.jpg四川省　九寨溝' id='code6' name='code6' />
<dt class='number'>00000-EBP07-00005.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/20081216174703941th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="6" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("6");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("6", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("6"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>四川省　九寨溝</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-EBP07-00006.jpg守礼門' id='code7' name='code7' />
<dt class='number'>00000-EBP07-00006.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/6/200812161747042129th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="7" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("7");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("7", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("7"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>守礼門</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00007.jpgナゴパイナップルパーク' id='code8' name='code8' />
<dt class='number'>00000-EBP07-00007.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/200812161747045837th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="8" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("8");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("8", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("8"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ナゴパイナップルパーク</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00008.jpg琉球村シーサー' id='code9' name='code9' />
<dt class='number'>00000-EBP07-00008.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/5/200812161747044405th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="9" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("9");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("9", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("9"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>琉球村シーサー</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00010.jpg白神山地　　　ブナ' id='code11' name='code11' />
<dt class='number'>00000-EBP07-00010.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161747079303th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="11" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("11");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("11", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("11"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>白神山地　　　ブナ</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00011.jpg九重夢大吊橋　九重”夢”大吊橋' id='code12' name='code12' />
<dt class='number'>00000-EBP07-00011.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161747078670th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="12" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("12");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("12", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("12"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>九重夢大吊橋　九重&rdquo;夢&rdquo;大&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00012.jpg九重夢大吊橋　九重”夢”大吊橋' id='code13' name='code13' />
<dt class='number'>00000-EBP07-00012.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/20081216174707347th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="13" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("13");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("13", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("13"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>九重夢大吊橋　九重&rdquo;夢&rdquo;大&hellip;</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-EBP07-00013.jpg九重夢大吊橋　九重”夢”大吊橋' id='code14' name='code14' />
<dt class='number'>00000-EBP07-00013.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/200812161747084769th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="14" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("14");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("14", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("14"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>九重夢大吊橋　九重&rdquo;夢&rdquo;大&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00014.jpg大山祇神社　拝殿　しまなみ海道' id='code15' name='code15' />
<dt class='number'>00000-EBP07-00014.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/200812161747081812th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="15" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("15");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("15", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("15"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>大山祇神社　拝殿　しまなみ&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00015.jpgエジンバラ　　　エジンバラ城' id='code16' name='code16' />
<dt class='number'>00000-EBP07-00015.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/200812161747084046th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="16" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("16");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("16", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("16"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>エジンバラ　　　エジンバラ&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00016.jpg異人館　ラインの館' id='code17' name='code17' />
<dt class='number'>00000-EBP07-00016.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/5/200812161747099257th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="17" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("17");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("17", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("17"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>異人館　ラインの館</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00017.jpgビーナスブリッジより神戸夜景' id='code18' name='code18' />
<dt class='number'>00000-EBP07-00017.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/0/200812161747091900th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="18" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("18");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("18", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("18"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ビーナスブリッジより神戸夜&hellip;</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00018.jpg南京町広場' id='code19' name='code19' />
<dt class='number'>00000-EBP07-00018.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/1/200812161747115521th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="19" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("19");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("19", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("19"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>南京町広場</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-EBP07-00019.jpgオリエンタルホテルと観覧車' id='code20' name='code20' />
<dt class='number'>00000-EBP07-00019.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/200812161747137535th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="20" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("20");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("20", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("20"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>オリエンタルホテルと観覧車</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00020.jpg南京町　長安門' id='code21' name='code21' />
<dt class='number'>00000-EBP07-00020.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161747141862th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="21" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("21");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("21", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("21"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>南京町　長安門</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00021.jpg神戸市街' id='code22' name='code22' />
<dt class='number'>00000-EBP07-00021.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/3/200812161747142105th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="22" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("22");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("22", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("22"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>神戸市街</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00022.jpg神戸市街' id='code23' name='code23' />
<dt class='number'>00000-EBP07-00022.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161747145105th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="23" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("23");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("23", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("23"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>神戸市街</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00023.jpg神戸ポートタワー' id='code24' name='code24' />
<dt class='number'>00000-EBP07-00023.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161747157632th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="24" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("24");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("24", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("24"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>神戸ポートタワー</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00024.jpg春　桜　イメージ' id='code25' name='code25' />
<dt class='number'>00000-EBP07-00024.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/8/200812161747153219th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="25" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("25");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("25", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("25"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>春　桜　イメージ</dd>
</dl>
</div><div><dl class='photo140'>
<input type='hidden' value='00000-EBP07-00025.jpg兼六園　霞ヶ池と徽軫灯籠' id='code26' name='code26' />
<dt class='number'>00000-EBP07-00025.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/2/20081216174716361th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="26" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("26");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("26", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("26"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>兼六園　霞ヶ池と徽軫灯籠</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00026.jpg桂林　蘆笛岩　鍾乳洞' id='code27' name='code27' />
<dt class='number'>00000-EBP07-00026.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161747168964th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="27" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("27");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("27", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("27"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>桂林　蘆笛岩　鍾乳洞</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00027.jpg浅草寺　雷門' id='code28' name='code28' />
<dt class='number'>00000-EBP07-00027.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/9/200812161747162479th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="28" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("28");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("28", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("28"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>浅草寺　雷門</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00028.jpgラベンダー　イメージ' id='code29' name='code29' />
<dt class='number'>00000-EBP07-00028.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/7/200812161747172795th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="29" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("29");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("29", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("29"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>ラベンダー　イメージ</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00029.jpg菖蒲　花菖蒲　イメージ' id='code30' name='code30' />
<dt class='number'>00000-EBP07-00029.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/4/200812161747171498th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="30" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("30");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("30", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("30"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>菖蒲　花菖蒲　イメージ</dd>
</dl>
<dl class='photo140'>
<input type='hidden' value='00000-EBP07-00030.jpg秋　もみじ　紅葉　イメージ' id='code31' name='code31' />
<dt class='number'>00000-EBP07-00030.jpg</dt>
<dd><img height='105px' width='140px' src=http://x.hankyu-travel.com/photo_db/./thumb3/5/200812161747188672th3.jpg alt='イメージ'/></dd>
<dd class='list'>
<ul>
<li class='check_box'>
<input name='img_chk' type='checkbox' value="31" onclick="setCookie_CheckBox(this,'pickup_chk');"/>
</li>
<li class='icon_bt_info' title='詳細情報'><a href='#' onclick='disp_ImageInformation("31");return false;'>情報</a></li>
<li class='icon_bt_pickup' title='ピックアップ'><a href='#' onclick='if (pickup("31", 1)==false){alert("既にピックアップしています。");} return false;'>ピックアップ</a></li>
<li class='icon_bt_copy' title='DSコピー'><a href='#' onclick='setClipboard("31"); alert("写真情報をクリップボードにコピーしました。"); return false;'>コピー</a></li>
</ul>
</dd>
<dd class='p_name'>秋　もみじ　紅葉　イメージ</dd>
</dl>
</div></div>
<div class="pickup_bt pickup_bt_bottom">
		<ul class="txt">
		<li>
1 &nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="page 2">2</a> &nbsp;<a href="/photo_db/search_result.php?pageID=3&amp;ppage=30#hl" title="page 3">3</a> &nbsp;<a href="/photo_db/search_result.php?pageID=4&amp;ppage=30#hl" title="page 4">4</a> &nbsp;<a href="/photo_db/search_result.php?pageID=5&amp;ppage=30#hl" title="page 5">5</a> &nbsp;<a href="/photo_db/search_result.php?pageID=6&amp;ppage=30#hl" title="page 6">6</a> &nbsp;<a href="/photo_db/search_result.php?pageID=7&amp;ppage=30#hl" title="page 7">7</a> &nbsp;<a href="/photo_db/search_result.php?pageID=8&amp;ppage=30#hl" title="page 8">8</a> &nbsp;<a href="/photo_db/search_result.php?pageID=9&amp;ppage=30#hl" title="page 9">9</a> &nbsp;<a href="/photo_db/search_result.php?pageID=10&amp;ppage=30#hl" title="page 10">10</a> &nbsp;<a href="/photo_db/search_result.php?pageID=11&amp;ppage=30#hl" title="page 11">11</a> &nbsp;<a href="/photo_db/search_result.php?pageID=12&amp;ppage=30#hl" title="page 12">12</a> &nbsp;<a href="/photo_db/search_result.php?pageID=13&amp;ppage=30#hl" title="page 13">13</a> &nbsp;<a href="/photo_db/search_result.php?pageID=14&amp;ppage=30#hl" title="page 14">14</a> &nbsp;<a href="/photo_db/search_result.php?pageID=15&amp;ppage=30#hl" title="page 15">15</a> &nbsp;<a href="/photo_db/search_result.php?pageID=16&amp;ppage=30#hl" title="page 16">16</a> &nbsp;<a href="/photo_db/search_result.php?pageID=17&amp;ppage=30#hl" title="page 17">17</a> &nbsp;<a href="/photo_db/search_result.php?pageID=18&amp;ppage=30#hl" title="page 18">18</a> &nbsp;<a href="/photo_db/search_result.php?pageID=19&amp;ppage=30#hl" title="page 19">19</a> &nbsp;<a href="/photo_db/search_result.php?pageID=20&amp;ppage=30#hl" title="page 20">20</a> &nbsp;<a href="/photo_db/search_result.php?pageID=21&amp;ppage=30#hl" title="page 21">21</a> &nbsp;<a href="/photo_db/search_result.php?pageID=22&amp;ppage=30#hl" title="page 22">22</a> &nbsp;<a href="/photo_db/search_result.php?pageID=23&amp;ppage=30#hl" title="page 23">23</a> &nbsp;<a href="/photo_db/search_result.php?pageID=24&amp;ppage=30#hl" title="page 24">24</a> &nbsp;<a href="/photo_db/search_result.php?pageID=25&amp;ppage=30#hl" title="page 25">25</a> &nbsp;<a href="/photo_db/search_result.php?pageID=26&amp;ppage=30#hl" title="page 26">26</a> &nbsp;<a href="/photo_db/search_result.php?pageID=27&amp;ppage=30#hl" title="page 27">27</a> &nbsp;<a href="/photo_db/search_result.php?pageID=28&amp;ppage=30#hl" title="page 28">28</a> &nbsp;<a href="/photo_db/search_result.php?pageID=29&amp;ppage=30#hl" title="page 29">29</a> &nbsp;<a href="/photo_db/search_result.php?pageID=30&amp;ppage=30#hl" title="page 30">30</a> &nbsp;&nbsp;<a href="/photo_db/search_result.php?pageID=2&amp;ppage=30#hl" title="next page">NEXT>></a>&nbsp;<a href="/photo_db/search_result.php?pageID=703&amp;ppage=30#hl" title="last page">[703]</a>		</li>
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
