/*
 * 関数名：setImagesResultCookie
 * 関数説明：検索したイメージIDをクッキーに設定する
 * パラメタ：
 * id:イメージID;ck_key:クッキーのキー
 * 戻り値：無し
 */
function setImagesResultCookie(id,ck_key)
{
	// クッキー識別子を作成します。
	var ck_id = ck_key;
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.split(",");

	// 既にクッキーで設定されているものについては、除外します。
	if (check_array(id_a, id) == -1)
	{
		if (idstr.length >= 1)
		{
			idstr = idstr + "," + id;
			//idstr =  id + "," + idstr;
		}
		else
		{
			idstr = id;
		}
	}
	// クッキーを設定します。
	setCookie(ck_id, idstr);
}

/*
 * 関数名：default_set_framewidth
 * 関数説明：画面の「表示数」を変わる時の処理
 * パラメタ：
 * obj:画面の「Select」コントロール
 * 戻り値：無し
 */
function default_set_framewidth(obj)
{
  	var new_value = obj.value;
  	var objs = document.getElementsByName("select2");
  	if (objs)
  	{
		for(var i = 0; i < objs.length; i++)
		{
	  		var obj_one = objs[i];
	  		if (obj_one)
	  		{
		  		if (obj_one.value != new_value)
		  		{
		  			obj_one.value = new_value;
		  		}
		  	}
		}
  	}
  	var key_change_value = "change_value";
  	setCookie(key_change_value,new_value);

	if (new_value == 30) top.document.getElementById('iframe_bottom').style.height = "840px";
	if (new_value == 60) top.document.getElementById('iframe_bottom').style.height = "1500px";
	if (new_value == 90) top.document.getElementById('iframe_bottom').style.height = "2100px";

	if (new_value == 30) top.document.getElementById('iframe_bottom').height = 840;
	if (new_value == 60) top.document.getElementById('iframe_bottom').height = 1500;
	if (new_value == 90) top.document.getElementById('iframe_bottom').height = 2100;
}

/*
 * 関数名：set_framewidth_image
 * 関数説明：画面の「表示数」を変わる時の処理
 * パラメタ：
 * obj:画面の「Select」コントロール
 * 戻り値：無し
 */
function set_framewidth_image(obj)
{
	default_set_framewidth(obj);

	var obj_frame = top.document.getElementById('iframe_bottom');
	//Firefox Browser
	if (obj_frame.contentDocument)
	{
		if (obj_frame.contentDocument.body.offsetHeight)
		{
			//var frm_height = obj_frame.contentDocument.body.offsetHeight + 16;
			var frm_height = 1200;
		}
	//IExplorer Browser
	} else if (obj_frame.Document) {
		if (obj_frame.Document.body.scrollHeight)
		{
			var frm_height = obj_frame.Document.body.scrollHeight;
		}
	}
	obj_frame.style.height = Number(frm_height);
	obj_frame.height = Number(frm_height);
}

/*
 * 関数名：set_framewidth_php
 * 関数説明：画面の「表示数」を変わる時の処理(PHP)
 * パラメタ：
 * new_value:画面の「Select」コントロールの選択値
 * 戻り値：無し
 */
function set_framewidth_php(new_value)
{
	var browser_flg = 0;
	var obj_frame = top.document.getElementById('iframe_bottom');
	//Firefox Browser
	if (obj_frame.contentDocument)
	{
		if (obj_frame.contentDocument.body.offsetHeight)
		{
			var frm_height = obj_frame.contentDocument.body.offsetHeight + 16;
		} else {
			browser_flg = 1;
		}
	//IExplorer Browser
	} else if (obj_frame.Document) {
		if (obj_frame.Document.body.scrollHeight)
		{
			var frm_height = obj_frame.Document.body.scrollHeight;
		} else {
			browser_flg = 1;
		}
	//他のBrowser
	} else {
		browser_flg = 1;
	}

	if (browser_flg == 1)
	{
		if (new_value == null || typeof(new_value) == "undefined")
		{
			var frm_height = 840;
		} else if(new_value == 30) {
			var frm_height = 840;
		}else if (new_value == 60) {
			var frm_height = 1400;
		}else if (new_value == 90) {
			var frm_height = 2000;
		}
	}
	obj_frame.style.height = Number(frm_height);
	obj_frame.height = Number(frm_height);
}

/*
 * 関数名：change_class
 * 関数説明：画面の表示
 * パラメタ：
 * siz:イメージのサイズ　大（200）　中（140）　小（100）
 * 戻り値：無し
 */
function change_class(siz)
{
	//var flg = false;

	//var tags = document.getElementById("photo_contents").getElementsByTagName("dl");
	//for(var i = 0 ; i < tags.length ; i++)
	//{
	//	tags[i].className = "photo" + siz;
	//	flg = true;
	//}
	//if (flg)
	//{
		var tmpcls = "photo" + siz;
		setCookie("classname", tmpcls);
	//}
	//var tags = document.getElementById("photo_contents").getElementsByTagName("img");
	//for(var i = 0 ; i < tags.length ; i++)
	//{
	//	if (tags[i].alt == "イメージ")
	//	{
	//		if (parseInt(siz) == 200)
	//		{
	//			tags[i].height = 150;
	//		} else if (parseInt(siz) == 140) {
	//			tags[i].height = 105;
	//		} else if (parseInt(siz) == 100) {
	//			tags[i].height = 75;
	//		}
	//		tags[i].width = siz;
	//	}
	//}
}