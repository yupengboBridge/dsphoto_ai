var TimeID=null;
function setFocus(fn){
	clearTimeout(TimeID);
	document.forms[0][fn].focus();
}
	/*
 * 関数名：trim
 * 関数説明：スペースを削除する
 * パラメタ：str：文字列
 * 戻り値：無し
 */
function trim(str){
 return str.replace(/(^\s*)|(\s*$)/g, "");
}

/*
 * 関数名：unChecked
 * 関数説明：画面の「チェックをクリア」ボタンの処理
 * パラメタ：無し
 * 戻り値：無し
 */
function unChecked(obj_key,ck_key) {
	var obj = document.getElementsByName(obj_key);
	var len = obj.length;
	for(var i=0;i<len;i++){
	   if(obj[i].checked==true) obj[i].checked = false;
	}
	clearCookie(ck_key);
}

/*
 * 関数名：setCookie_CheckBox
 * 関数説明：画面のチェックボックスのクッキーの設定処理
 * パラメタ：
 * chkObj:クリックのチェックボックス
 * 戻り値：無し
 */
function setCookie_CheckBox(chkObj,ck_key)
{
	//チェックボックスを選択した場合
	if (chkObj.checked) {
		// クッキー識別子を作成します。
		var ck_id = ck_key;
		// クッキーを取得します。
		var idstr = getCookie(ck_id);
		// カンマ区切りの文字列を配列にします。
		var id_a = new Array();
		id_a = idstr.split(",");
		// 既にクッキーで設定されているものについては、除外します。
		if (check_array(id_a, chkObj.value) == -1)
		{
			if (idstr != null && idstr != "" && typeof(idstr) != "undefined") {
				if (idstr.length >= 1) idstr = idstr + "," + chkObj.value;
			} else idstr = chkObj.value;
		} else return false;
		// クッキーを設定します。
		setCookie(ck_id, idstr);
	} else {
		// クッキー識別子を作成します。
		var ck_id = ck_key;
		// クッキーを取得します。
		var idstr = getCookie(ck_id);
		// カンマ区切りの文字列を配列にします。
		var id_a = new Array();
		id_a = idstr.split(",");
		// 既にクッキーで設定されているものについては、除外します。
		var idx = check_array(id_a, chkObj.value);
		if (idx == -1) return false;
		id_a[idx] = "";
		// 配列を文字列に変換します。
		idstr = array_to_str(id_a);
		// クッキーを設定します。
		setCookie(ck_id, idstr);
	}
}

//yupengbo add 2011/12/15 start
function msieversion() { 
	var ua = window.navigator.userAgent;
	var msie = ua.indexOf("MSIE ");
	if (msie > 0)      // If Internet Explorer, return version number 
		return parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)))
	else return 0;// If another browser, return 0 
}
//yupengbo add 2011/12/15 end

/*
 * 関数名：set_framewidth
 * 関数説明：フレームの高さの設定
 * パラメタ：無
  * 戻り値：無し
 */
function set_frameheight(frame_id,def_height)
{
	var browser_flg = 0;

	var obj_frame = top.document.getElementById(frame_id);
	if(obj_frame)
	{
		//Firefox IE8/9 Browser
		if (obj_frame.contentDocument)
		{
			if (obj_frame.contentDocument.body.offsetHeight)
			{
				var frm_height = obj_frame.contentDocument.body.offsetHeight + 16;
			} else {
				browser_flg = 1;
			}
			if(msieversion() > 7)
			{
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

		if (browser_flg == 1) var frm_height = def_height;

		obj_frame.style.height = Number(frm_height);
	}
	//obj_frame.height = Number(frm_height);
}

// クッキーからデータを取得します。
function getCookie(key, tmp1, tmp2, xx1, xx2, xx3)
{
	tmp1 = " " + document.cookie + ";";
	xx1 = xx2 = 0;
	len = tmp1.length;
	while (xx1 < len)
	{
		xx2 = tmp1.indexOf(";", xx1);
		tmp2 = tmp1.substring(xx1 + 1, xx2);
		xx3 = tmp2.indexOf("=");
		if (tmp2.substring(0, xx3) == key)
		{
			return(unescape(tmp2.substring(xx3 + 1, xx2 - xx1 - 1)));
		}
		xx1 = xx2 + 1;
	}
	return("");
}

// クッキーにデータをセットします。
function setCookie(key, val, tmp)
{
	tmp = key + "=" + escape(val) + "; ";
	// tmp += "path=" + location.pathname + "; ";
	//tmp += "expires=Tue, 31-Dec-2030 23:59:59; ";

	document.cookie = tmp;
}

// クッキーからデータをクリアします。
function clearCookie(key)
{
	document.cookie = key + "=" + "xx; expires=Tue, 1-Jan-1980 00:00:00;";
	var obj = top.document.getElementById('iframe_middle2');
	if(obj) obj.style.height = 190;
	//top.document.getElementById('iframe_middle2').height = 190;
}

// カンマを消去します。
function removeComma(value)
{
	var s = "" + value;
    return s.split(",").join("");
}

// カンマと\マーク、円を消去します。
function removeCommaYen(value)
{
	var s = "" + value;
	s = s.split("ﾂ円").join("");
	s = s.split("\\").join("");
    return s.split(",").join("");
}

// 配列にデータがあるかチェックします。
function check_array(ids, id)
{
	for (var i = 0 ; i < ids.length ; i++)
	{
		if (ids[i] == id)
		{
			return i;
		}
	}

	return -1;
}

// 配列をカンマ区切りの文字列に変換します。
function array_to_str(ids)
{
	var idstr = "";
	for (var i = 0 ; i < ids.length ; i++)
	{

		if (ids[i].length != 0)
		{
			if (idstr.length != 0)
			{
				idstr += ",";
			}
			idstr += ids[i];
		}
	}

	return idstr;
}

/*
 * 関数名：pickup
 * 関数説明：画像をピックアップ対象に追加します
 * パラメタ：
 * id:イメージID；userid：ユーザーID
 * 戻り値：無し
 */
function pickup(id, userid)
{
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + userid;
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
			//idstr = idstr + "," + id;
			idstr =  id + "," + idstr;
		}
		else
		{
			idstr = id;
		}
	}
	else
	{
		return false;
	}

	// クッキーを設定します。
	setCookie(ck_id, idstr);
	setCookie("bt_cnt",0);

	var url = "./pickup_ichiran1.php?p_pickupid=" + idstr;

	parent.middle2.location.href = url;
}

/*
 * 関数名：getPickUpIDStr
 * 関数説明：ピックアップイメージIDの文字列
 * パラメタ：
 * indx_id:インデックス；userid：ユーザーID；
 * flg：「1」：ピックアップ画面から引き続き；「2」：検索結果画面から引き続き
 * 戻り値：無し
 */
function getPickUpIDStr(userid,indx_id,flg)
{
	if (flg == 1)
	{
		// クッキー識別子を作成します。
		var ck_id = "pickup_images_id_" + userid;
	}

	if (flg == 2)
	{
		// クッキー識別子を作成します。
		var ck_id = "images_result";
	}

	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.split(",");

	res_str = id_a[indx_id];
	return res_str;
}

//ピックアップイメージのArrayのサイズ
function getPickUpArrayLength(userid)
{
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + userid;
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.split(",");
	return id_a.length;
}

/*
 * 関数名：delete_pickup
 * 関数説明：画像をピックアップ対象から削除します
 * パラメタ：
 * id:イメージID；userid：ユーザーID
 * 戻り値：無し
 */
function delete_pickup(id, userid)
{
	var frame_height = top.document.getElementById('iframe_middle2').style.height;
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + userid;
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

	// クッキー識別子を作成します。
	var ck_id_all = "pickup_chk";
	// クッキーを取得します。
	var idstr_all = getCookie(ck_id_all);
	// カンマ区切りの文字列を配列にします。
	var id_a_all = new Array();
	id_a_all = idstr_all.split(",");
	// 既にクッキーで設定されているものについては、除外します。
	var idx_all = check_array(id_a_all, id);
	if (idx_all != -1)
	{
		id_a_all[idx_all] = "";
		// 配列を文字列に変換します。
		idstr_all = array_to_str(id_a_all);
		// クッキーを設定します。
		setCookie(ck_id_all, idstr_all);
	}

	if (frame_height == null)
	{
		var url = "./pickup_ichiran1.php?p_pickupid=" + idstr;
	} else
	{
		var tmp = frame_height.indexOf("px");
		if (tmp != null && tmp != "")
		{
			if (parseInt(tmp) > 0)
			{
				var tmp1 = frame_height.substr(0,tmp);
				if (tmp1 != null && tmp1 != "")
				{
					if (parseInt(tmp1) > 0)
					{
						if (parseInt(tmp1) > 0 && parseInt(tmp1) < 200)
						{
							var url = "./pickup_ichiran1.php?p_pickupid=" + idstr + "&ShowFlag=0";
						}

						if (parseInt(tmp1) > 200)
						{
							var url = "./pickup_ichiran1.php?p_pickupid=" + idstr + "&ShowFlag=1";
						}
					}
				}
			}
		}
	}
	//set_frameheight('iframe_middle2',380);
	parent.middle2.location.href = url;
}

/*
 * 関数名：delete_pickup2
 * 関数説明：画像をピックアップ対象から削除します
 * パラメタ：
 * id:イメージID；userid：ユーザーID
 * 戻り値：無し
 */
function delete_pickup2(id, userid)
{
	var frame_height = top.document.getElementById('iframe_middle2').style.height;
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + userid;
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

	// クッキー識別子を作成します。
	var ck_id_all = "pickup_chk";
	// クッキーを取得します。
	var idstr_all = getCookie(ck_id_all);
	// カンマ区切りの文字列を配列にします。
	var id_a_all = new Array();
	id_a_all = idstr_all.split(",");
	// 既にクッキーで設定されているものについては、除外します。
	var idx_all = check_array(id_a_all, id);
	if (idx_all != -1)
	{
		id_a_all[idx_all] = "";
		// 配列を文字列に変換します。
		idstr_all = array_to_str(id_a_all);
		// クッキーを設定します。
		setCookie(ck_id_all, idstr_all);
	}

	if (frame_height == null)
	{
		var url = "./pickup_ichiran1.php?p_pickupid=" + idstr;
	} else
	{
		var tmp = frame_height.indexOf("px");
		if (tmp != null && tmp != "")
		{
			if (parseInt(tmp) > 0)
			{
				var tmp1 = frame_height.substr(0,tmp);
				if (tmp1 != null && tmp1 != "")
				{
					if (parseInt(tmp1) > 0)
					{
						if (parseInt(tmp1) > 0 && parseInt(tmp1) < 200)
						{
							var url = "./pickup_ichiran1.php?p_pickupid=" + idstr + "&ShowFlag=0";
						}

						if (parseInt(tmp1) > 200)
						{
							var url = "./pickup_ichiran1.php?p_pickupid=" + idstr + "&ShowFlag=1";
						}
					}
				}
			}
		}
	}
	set_frameheight('iframe_middle2',380);
}

/*
 * 関数名：clear_pickup
 * 関数説明：ピックアップ対象をクリアします
 * パラメタ：
 * userid:ユーザーID
 * 戻り値：無し
 */
function clear_pickup(userid)
{
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + userid;
	var ck_id_all = "pickup_chk";

	var idstr = "";

	// クッキーを設定します。
	setCookie(ck_id, idstr);
	setCookie(ck_id_all, idstr);

	var url = "./pickup_ichiran1.php?p_pickupid=" + idstr;
	parent.middle2.location.href = url;
}

// return
//   -1 : 年が未入力です
//   -2 : 月が未入力です
//   -3 : 日が未入力です
//   -4 : 年の値が小さすぎます
//   -5 : 入力された日付は存在しません
//
function check_date(year, mon, day)
{
	var flag = true;

	if (year == "" || isNaN(year)) {
		return -1;
	}
	if (mon == "" || isNaN(mon)) {
		return -2;
	}
	if (day == "" || isNaN(day)) {
		return -3;
	}

	year = parseInt(year);
	mon = parseInt(mon) - 1;
	day = parseInt(day);

	if (year < 1900)
	{
		return -4;
	}

	var ckdate = new Date(year,mon,day);
	if (ckdate.getYear() < 1900)
	{
		if (year != ckdate.getYear() + 1900)
		{
			flag = false;
		}
	}
	else
	{
		if (year != ckdate.getYear())
		{
			flag = false;
		}
	}

	if (mon != ckdate.getMonth())
	{
		flag = false;
	}

	if (day != ckdate.getDate())
	{
		flag = false;
	}

	if (flag)
	{
		// 入力された日付は存在します。
		return 0;
	} else {
		// 入力された日付は存在しません。
		return -5;
	}
}

//计算字符长度
function calcUTFByte(str)  
{  
    var len=0;  
    for (var i=0;i<str.length;i++)　{  
        var temp = str.charCodeAt(i);  
        if　( temp >= 0 && temp <= 254)　{  
            //以下是0-255之内为全角的字符  
            if　( temp == 162  
                || temp == 163  
                || temp == 167  
                || temp == 168  
                || temp == 171  
                || temp == 172  
                || temp == 175  
                || temp == 176  
                || temp == 177  
                || temp == 180  
                || temp == 181  
                || temp == 182  
                || temp == 183  
                || temp == 184  
                || temp == 187  
                || temp == 215  
                || temp == 247)　{  
                len+=2;  
            }  
            len++;  
        }　else if　( temp >= 65377 && temp <= 65439)　{  
            if ( temp == 65381 ) {  
                len+=2;  
            }  
            len++;  
        } else {  
            len+=2;  
        }  
    }//for end  
    return len;  
}
