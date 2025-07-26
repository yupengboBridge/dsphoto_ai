var display_max = 10;

/*
 * 関数名：search_resultbig
 * 関数説明：画面の「大」ボタンの処理
 * パラメタ：無し
 * 戻り値：無し
 */
var bt_big=1;

 function search_resultbig()
{
  	var key_change_value = "change_value";
  	var new_value = Number(getCookie(key_change_value));
	var ua = navigator.userAgent.toLowerCase();

	var obj_frame = top.document.getElementById('iframe_bottom');
	if(bt_big==1)
	{
		//Firefox Browser
		if (obj_frame.contentDocument)
		{
			if (obj_frame.contentDocument.body.offsetHeight)
			{
				var frm_height = obj_frame.contentDocument.body.offsetHeight + 16;
			}
		//IExplorer Browser
		} else if (obj_frame.Document) {
			if (obj_frame.Document.body.scrollHeight)
			{
				var frm_height = obj_frame.Document.body.scrollHeight;
			}
		//他のBrowser
		} else {
			if (new_value == null || new_value == "")
			{
				var frm_height = 1800;
			} else {
				if (new_value == 30) var frm_height = 1800;
				if (new_value == 60) var frm_height = 2408;
				if (new_value == 90) var frm_height = 3800;
			}
		}
		obj_frame.style.height = Number(frm_height);
		if (ua.indexOf('msie') != -1)
		{
			obj_frame.height = Number(frm_height);
		}
	} else {
		obj_frame.style.height = 840;
		if (ua.indexOf('msie') != -1)
		{
			obj_frame.height = 840;
		}
	}
	
	var urltmp = parent.bottom.location.href;
	var iposurl = urltmp.indexOf("#hl");
	if(iposurl > 0)
	{
		var tmp_url = urltmp.substr(0,iposurl);
		//alert(tmp_url);
		parent.bottom.location = tmp_url;
	} else {
		//alert(parent.bottom.location.href);
		parent.bottom.location = parent.bottom.location.href;
	}
}

/*
 * 関数名：search_resultmidlle
 * 関数説明：画面の「中」ボタンの処理
 * パラメタ：無し
 * 戻り値：無し
 */
var bt_midlle=1;
 function search_resultmidlle()
{
  	var key_change_value = "change_value";
  	var new_value = Number(getCookie(key_change_value));
	var ua = navigator.userAgent.toLowerCase();

	if(bt_midlle==1)
	{
		var obj_frame = top.document.getElementById('iframe_bottom');
		//Firefox Browser
		if (obj_frame.contentDocument)
		{
			if (obj_frame.contentDocument.body.offsetHeight)
			{
				var frm_height = obj_frame.contentDocument.body.offsetHeight + 66;
			}
		//IExplorer Browser
		} else if (obj_frame.Document) {
			if (obj_frame.Document.body.scrollHeight)
			{
				var frm_height = obj_frame.Document.body.scrollHeight;
			}
		//他のBrowser
		} else {
			if (new_value == null || new_value == "")
			{
				var frm_height = 840;
			} else {
				if (new_value == 30) var frm_height = 840;
				if (new_value == 60) var frm_height = 1400;
				if (new_value == 90) var frm_height = 2000;
			}
		}
		obj_frame.style.height = Number(frm_height);
		if (ua.indexOf('msie') != -1)
		{
			obj_frame.height = Number(frm_height);
		}
		//parent.bottom.location = parent.bottom.location.href;
	} else {
		top.document.getElementById('iframe_bottom').style.height = 840;
		if (ua.indexOf('msie') != -1)
		{
			obj_frame.height = 840;
		}
		//parent.bottom.location = parent.bottom.location.href;
	}
	
	var urltmp = parent.bottom.location.href;
	var iposurl = urltmp.indexOf("#hl");
	if(iposurl > 0)
	{
		var tmp_url = urltmp.substr(0,iposurl);
		//alert(tmp_url);
		parent.bottom.location = tmp_url;
	} else {
		//alert(parent.bottom.location.href);
		parent.bottom.location = parent.bottom.location.href;
	}
}

/*
 * 関数名：search_resultsmall
 * 関数説明：画面の「小」ボタンの処理
 * パラメタ：無し
 * 戻り値：無し
 */
var bt_small=1;
 function search_resultsmall()
{
  	var key_change_value = "change_value";
  	var new_value = Number(getCookie(key_change_value));
	var ua = navigator.userAgent.toLowerCase();

	if(bt_small==1)
	{
		var obj_frame = top.document.getElementById('iframe_bottom');
		//Firefox Browser
		if (obj_frame.contentDocument)
		{
			if (obj_frame.contentDocument.body.offsetHeight)
			{
				var frm_height = obj_frame.contentDocument.body.offsetHeight + 66;
			}
		//IExplorer Browser
		} else if (obj_frame.Document) {
			if (obj_frame.Document.body.scrollHeight)
			{
				var frm_height = obj_frame.Document.body.scrollHeight;
			}
		//他のBrowser
		} else {
			if (new_value == null || new_value == "")
			{
				var frm_height = 790;
			} else {
				if (new_value == 30) var frm_height = 790;
				if (new_value == 60) var frm_height = 1050;
				if (new_value == 90) var frm_height = 1450;
			}
		}
		obj_frame.style.height = Number(frm_height);
		if (ua.indexOf('msie') != -1)
		{
			obj_frame.height = Number(frm_height);
		}
		//parent.bottom.location = parent.bottom.location.href;
	} else {
		top.document.getElementById('iframe_bottom').style.height = 840;
		if (ua.indexOf('msie') != -1)
		{
			obj_frame.height = 840;
		}
		//parent.bottom.location = parent.bottom.location.href;
	}
	var urltmp = parent.bottom.location.href;
	var iposurl = urltmp.indexOf("#hl");
	if(iposurl > 0)
	{
		var tmp_url = urltmp.substr(0,iposurl);
		//alert(tmp_url);
		parent.bottom.location = tmp_url;
	} else {
		//alert(parent.bottom.location.href);
		parent.bottom.location = parent.bottom.location.href;
	}
}
