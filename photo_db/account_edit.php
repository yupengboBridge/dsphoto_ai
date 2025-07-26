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

//ログインしているかをチェックします。
if (empty($s_login_id) || $s_security_level != 4 || $s_security_level != "4")
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

$p_user_id = array_get_value($_REQUEST, "id","");	//ID
	
$p_action = array_get_value($_REQUEST, 'p_action',"");							// アクション
// ユーザーのインスタンスを生成します。
$usr = new UserManger();
try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	if ($p_action == "update_confirm")
	{
		set_updatedataToSession();
		print "<script src='./js/common.js'  type='text/javascript'  charset='utf-8'></script>\r\n";
		print "<script type=\"text/javascript\">\r\n";
		print "parent.bottom.location.href = \"./account_edit_confirm.php\";";
		print "</script>";
	} elseif ($p_action == "reback") {
		$usr->set_ID(array_get_value($_SESSION,"acc_userid",""));
		$usr->set_user_name(array_get_value($_SESSION,"acc_user_name",""));
		$usr->set_user_group(array_get_value($_SESSION,"acc_user_group",""));
		$usr->set_user_login_id(array_get_value($_SESSION,"acc_user_login_id",""));
		$usr->set_user_comp_code(array_get_value($_SESSION,"acc_user_comp_code",""));
		$usr->set_user_kikan(array_get_value($_SESSION,"acc_user_kikan",""));
		$usr->set_user_security_level(array_get_value($_SESSION,"acc_user_security_level",""));
		if($usr->user_kikan != "mukigenn")
		{
			$usr->set_start_date(array_get_value($_SESSION,"acc_start_date_new",""));
			$usr->set_end_date(array_get_value($_SESSION,"acc_end_date_new",""));
		}
	} else {
		$usr->select_data($db_link,$p_user_id);
	}
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

/*
 * 関数名：set_updatedata
 * 関数説明：イメージの更新
 * パラメタ：無し
 * 戻り値：無し
 */
function set_updatedata()
{
	global $usr, $p_user_id, $db_link;

	$usr->set_ID($p_user_id);
	
	$usr->set_user_password(array_get_value($_POST,"account_pass",""));
	$usr->set_user_security_level(intval(array_get_value($_POST,"account_search","")));
	
	$kikan = array_get_value($_POST,"account_period","");
	$usr->set_user_kikan($kikan);
	if($kikan == "sitei")
	{
		$tmpdate_start = date("Y-m-d",array_get_value($_POST,"account_start_date",""));
		$tmpdate_end = date("Y-m-d",array_get_value($_POST,"account_end_date",""));
		
		$usr->set_start_date = $tmpdate_start;
		$usr->set_end_date = $tmpdate_end;
	}
}

function set_updatedataToSession()
{
	global $usr;
	
	$_SESSION["acc_userid"] = array_get_value($_POST,"userid","");
	$_SESSION["acc_user_name"] = array_get_value($_POST,"user_name","");
	$_SESSION["acc_user_group"] = array_get_value($_POST,"user_group","");
	$_SESSION["acc_user_login_id"] = array_get_value($_POST,"user_login_id","");
	$_SESSION["acc_user_password"] = array_get_value($_POST,"account_pass","");
	$_SESSION["acc_user_password_old"] = array_get_value($_POST,"account_pass_old","");
	if(empty($_SESSION["acc_user_password"]) || $_SESSION["acc_user_password"] == null)
	{
		$_SESSION["acc_user_password"] = array_get_value($_POST,"account_pass_old","");
	}
	$_SESSION["acc_user_comp_code"] = array_get_value($_POST,"user_comp_code","");
	$_SESSION["acc_user_kikan"] = array_get_value($_POST,"account_period","");
	$_SESSION["acc_user_security_level"] = array_get_value($_POST,"account_search","");
	//$_SESSION["acc_user_security_level_old"] = array_get_value($_POST,"user_security_level","");
	
	$s_yy = array_get_value($_POST,"account_yy_start","");
	if(empty($s_yy))
	{
		$_SESSION["acc_start_date_new"] = "";
		$_SESSION["acc_end_date_new"] = "";
	} else {
		$s_mm = array_get_value($_POST,"account_mm_start","");
		$s_dd = array_get_value($_POST,"account_dd_start","");
		$e_yy = array_get_value($_POST,"account_yy_end","");
		$e_mm = array_get_value($_POST,"account_mm_end","");
		$e_dd = array_get_value($_POST,"account_dd_end","");
		
		$_SESSION["acc_start_date_new"] = sprintf("%04d-%02d-%02d",$s_yy,$s_mm,$s_dd);
		$_SESSION["acc_end_date_new"] = sprintf("%04d-%02d-%02d",$e_yy,$e_mm,$e_dd);
	}
}

function display_date()
{
	global $usr;
	
	if($usr->user_kikan == "sitei")
	{
		$tmp_date_start = $usr->start_date;
		$tmp_date_end = $usr->end_date;
		
		$tmp_date_start_ary = explode("-",$tmp_date_start);
		$tmp_date_end_ary = explode("-",$tmp_date_end);
		
		$i = 0;
		
		$html = "<input type=\"hidden\" name=\"start_date\" id=\"start_date\" value=\"".$tmp_date_start."\"> \r\n";
		$html .= "<input type=\"hidden\" name=\"end_date\" id=\"end_date\" value=\"".$tmp_date_end."\"> \r\n";
		$html .= "<input type=\"hidden\" name=\"user_kikan\" id=\"user_kikan\" value=\"".$usr->user_kikan."\"> \r\n";
		$html .= "<select id=\"account_yy_start\" name=\"account_yy_start\">\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=2016;$i<=2050;$i++)
		{
			if($i == intval($tmp_date_start_ary[0]))
			{
				$html .= "	<option value=\"".$i."\" selected>".$i."</option>\r\n";
			} else {
				$html .= "	<option value=\"".$i."\">".$i."</option>\r\n";
			}
		}
		$html .= "</select>";
		$html .= "<label>年</label>\r\n";
		$html .= "<select id=\"account_mm_start\" name=\"account_mm_start\">\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=12;$i++)
		{
			if($i == intval($tmp_date_start_ary[1]))
			{
				$html .= "	<option value=\"".sprintf("%02d",$i)."\" selected>".sprintf("%02d",$i)."</option>\r\n";
			} else {
				$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
			}
		}
		$html .= "</select>";
		$html .= "<label>月</label>\r\n";
		$html .= "<select id=\"account_dd_start\" name=\"account_dd_start\">\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=31;$i++)
		{
			if($i == intval($tmp_date_start_ary[2]))
			{
				$html .= "	<option value=\"".sprintf("%02d",$i)."\" selected>".sprintf("%02d",$i)."</option>\r\n";
			} else {
				$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
			}
		}
		$html .= "</select>";
		$html .= "<label>日 ～ </label>\r\n";
		$html .= "<select id=\"account_yy_end\" name=\"account_yy_end\">\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=2016;$i<=2050;$i++)
		{
			if($i == intval($tmp_date_end_ary[0]))
			{
				$html .= "	<option value=\"".$i."\" selected>".$i."</option>\r\n";
			} else {
				$html .= "	<option value=\"".$i."\">".$i."</option>\r\n";
			}
		}
		$html .= "</select>";
		$html .= "<label>年</label>\r\n";
		$html .= "<select id=\"account_mm_end\" name=\"account_mm_end\">\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=12;$i++)
		{
			if($i == intval($tmp_date_end_ary[1]))
			{
				$html .= "	<option value=\"".sprintf("%02d",$i)."\" selected>".sprintf("%02d",$i)."</option>\r\n";
			} else {
				$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
			}
		}
		$html .= "</select>";
		$html .= "<label>月</label>\r\n";
		$html .= "<select id=\"account_dd_end\" name=\"account_dd_end\">\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=31;$i++)
		{
			if($i == intval($tmp_date_end_ary[2]))
			{
				$html .= "	<option value=\"".sprintf("%02d",$i)."\" selected>".sprintf("%02d",$i)."</option>\r\n";
			} else {
				$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
			}
		}
		$html .= "</select>";
		$html .= "<label>日</label>\r\n";
	} else {
		$html = "<input type=\"hidden\" name=\"start_date\" id=\"start_date\"  value=\"\"> \r\n";
		$html .= "<input type=\"hidden\" name=\"end_date\" id=\"end_date\"  value=\"\"> \r\n";
		$html .= "<input type=\"hidden\" name=\"user_kikan\" id=\"user_kikan\"  value=\"".$usr->user_kikan."\"> \r\n";
		$html .= "<select id=\"account_yy_start\" name=\"account_yy_start\" disabled>\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=2016;$i<=2050;$i++)
		{
			$html .= "	<option value=\"".$i."\">".$i."</option>\r\n";
		}
		$html .= "</select>";
		$html .= "<label>年</label>\r\n";
		$html .= "<select id=\"account_mm_start\" name=\"account_mm_start\" disabled>\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=12;$i++)
		{
			$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
		}
		$html .= "</select>";
		$html .= "<label>月</label>\r\n";
		$html .= "<select id=\"account_dd_start\" name=\"account_dd_start\" disabled>\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=31;$i++)
		{
			$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
		}
		$html .= "</select>";
		$html .= "<label>日 ～ </label>\r\n";
		$html .= "<select id=\"account_yy_end\" name=\"account_yy_end\" disabled>\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=2016;$i<=2050;$i++)
		{
			$html .= "	<option value=\"".sprintf("%02d",$i)."\">".$i."</option>\r\n";
		}
		$html .= "</select>";
		$html .= "<label>年</label>\r\n";
		$html .= "<select id=\"account_mm_end\" name=\"account_mm_end\" disabled>\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=12;$i++)
		{
			$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
		}
		$html .= "</select>";
		$html .= "<label>月</label>\r\n";
		$html .= "<select id=\"account_dd_end\" name=\"account_dd_end\" disabled>\r\n";
		$html .= "	<option value=\"\">&nbsp;</option>\r\n";
		for($i=1;$i<=31;$i++)
		{
			$html .= "	<option value=\"".sprintf("%02d",$i)."\">".sprintf("%02d",$i)."</option>\r\n";
		}
		$html .= "</select>";
		$html .= "<label>日</label>\r\n";
	}
	return $html;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アカウント編集</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
function form_clear()
{
	var obj_pwd = document.getElementById("account_pass");
	if(obj_pwd)
	{
		obj_pwd.value = "";
	}
	
	var objs = document.getElementsByName("account_period");
	if(objs)
	{
		var obj_hd_kikan = document.getElementById("user_kikan");
		var init_val_kikan = obj_hd_kikan.value;
		var obj_yy_s = document.getElementById("account_yy_start");
		var obj_mm_s = document.getElementById("account_mm_start");
		var obj_dd_s = document.getElementById("account_dd_start");
		var obj_yy_e = document.getElementById("account_yy_end");
		var obj_mm_e = document.getElementById("account_mm_end");
		var obj_dd_e = document.getElementById("account_dd_end");

		if(init_val_kikan == "mukigenn")
		{
			if(objs[0] && objs[1])
			{
				objs[0].checked = true;
				objs[1].checked = false;
			}
			obj_yy_s.selectedIndex = 0;
			obj_mm_s.selectedIndex = 0;
			obj_dd_s.selectedIndex = 0;
			obj_yy_e.selectedIndex = 0;
			obj_mm_e.selectedIndex = 0;
			obj_dd_e.selectedIndex = 0;
			
			obj_yy_s.disabled = true;
			obj_mm_s.disabled = true;
			obj_dd_s.disabled = true;
			obj_yy_e.disabled = true;
			obj_mm_e.disabled = true;
			obj_dd_e.disabled = true;
		} else if(init_val_kikan == "sitei") {
			if(objs[0] && objs[1])
			{
				objs[0].checked = false;
				objs[1].checked = true;
			}
			//日付の設定
			var start_date_hd = document.getElementById("start_date");
			var end_date_hd = document.getElementById("end_date");
			
			var start_date_hd_val = start_date_hd.value;
			var end_date_hd_val = end_date_hd.value;
			
			var start_date_hd_val_ary = start_date_hd_val.explode("-");
			var end_date_hd_val_ary = end_date_hd_val.explode("-");
			
			if(obj_yy_s)
			{
				obj_yy_s.disabled = false;
				obj_yy_s.value = start_date_hd_val_ary[0];
			}
			if(obj_mm_s)
			{
				obj_mm_s.disabled = false;
				obj_mm_s.value = start_date_hd_val_ary[1];
			}
			if(obj_dd_s)
			{
				obj_dd_s.disabled = false;
				obj_dd_s.value = start_date_hd_val_ary[2];
			}
			if(obj_yy_e)
			{
				obj_yy_e.disabled = false;
				obj_yy_e.value = end_date_hd_val_ary[0];
			}
			if(obj_mm_e)
			{
				obj_mm_e.disabled = false;
				obj_mm_e.value = end_date_hd_val_ary[1];
			}
			if(obj_dd_e)
			{
				obj_dd_e.disabled = false;
				obj_dd_e.value = end_date_hd_val_ary[2];
			}
		}
	}
	
	objs = document.getElementsByName("account_search");
	if(objs)
	{
		var obj_hd_sec = document.getElementById("user_security_level");
		var init_val_sec = parseInt(obj_hd_sec.value);
		if(init_val_sec == 1)
		{
			objs[0].checked = true;
			objs[1].checked = false;
			objs[2].checked = false;
			objs[3].checked = false;
		} else if(init_val_sec == 2) {
			objs[0].checked = false;
			objs[1].checked = true;
			objs[2].checked = false;
			objs[3].checked = false;
		} else if(init_val_sec == 3) {
			objs[0].checked = false;
			objs[1].checked = false;
			objs[2].checked = true;
			objs[3].checked = false;
		} else if(init_val_sec == 4) {
			objs[0].checked = false;
			objs[1].checked = false;
			objs[2].checked = false;
			objs[3].checked = true;
		}
	}
}

function form_submit()
{
	var msg = "パスワードは変更せずにDBを更新しますか？";
	var errmsg = "期間指定の年月日を確認してください。";
	
	var obj_pwd = document.getElementById("account_pass");
	if(obj_pwd)
	{
		var obj_pwd_val = obj_pwd.value;
		if(obj_pwd_val.length <= 0)
		{
			var ret = confirm(msg);
			if(!ret)
			{
				return false;
			}
		}

		if(obj_pwd_val.length > 0)
		{
			var patrn = /[a-zA-Z0-9_\.\-\,\!\@\$\%\&\*\?\^\~]$/ig;
			var patrn1 = /[' "]/;
			if(!patrn.exec(obj_pwd_val) || patrn1.exec(obj_pwd_val))
			{
				alert("パスワードを確認してください。");
				return false;
			}
		}
	}
	
	var objs = document.getElementsByName("account_period");
	if(objs)
	{
		var obj = objs[1];
		if(obj.checked == true)
		{
			var obj_yy_s = document.getElementById("account_yy_start");
			var obj_mm_s = document.getElementById("account_mm_start");
			var obj_dd_s = document.getElementById("account_dd_start");
			var obj_yy_e = document.getElementById("account_yy_end");
			var obj_mm_e = document.getElementById("account_mm_end");
			var obj_dd_e = document.getElementById("account_dd_end");
			
			var errflg = 0;
			if(obj_yy_s)
			{
				if(obj_yy_s.value.length <= 0) errflg = 1;
			}
			if(obj_mm_s && errflg == 0)
			{
				if(obj_mm_s.value.length <= 0) errflg = 1;
			}
			if(obj_dd_s && errflg == 0)
			{
				if(obj_dd_s.value.length <= 0) errflg = 1;
			}
			if(obj_yy_e && errflg == 0)
			{
				if(obj_yy_e.value.length <= 0) errflg = 1;
			}
			if(obj_mm_e && errflg == 0)
			{
				if(obj_mm_e.value.length <= 0) errflg = 1;
			}
			if(obj_dd_e && errflg == 0)
			{
				if(obj_dd_e.value.length <= 0) errflg = 1;
			}
			
			if(obj_yy_s && obj_yy_e && errflg == 0)
			{
				var s_yy = parseInt(obj_yy_s.value);
				var e_yy = parseInt(obj_yy_e.value);
				if(e_yy < s_yy)
				{
					errflg = 1;
				}
			}
			
			if(obj_yy_s && obj_yy_e && obj_mm_s && obj_mm_e && errflg == 0)
			{
				var s_yy = parseInt(obj_yy_s.value);
				var e_yy = parseInt(obj_yy_e.value);
				if(s_yy == e_yy)
				{
					var s_mm = parseInt(obj_mm_s.value);
					var e_mm = parseInt(obj_mm_e.selectedIndex);
					if(e_mm < s_mm) 
					{
						errflg = 1;
					}
				}
			}
			
			if(obj_yy_s && obj_yy_e && obj_mm_s && obj_mm_e && obj_dd_s && obj_dd_e && errflg == 0)
			{
				var s_yy = parseInt(obj_yy_s.value);
				var e_yy = parseInt(obj_yy_e.value);
				var s_mm = parseInt(obj_mm_s.value);
				var e_mm = parseInt(obj_mm_e.value);
				if(s_yy == e_yy && e_mm == s_mm)
				{
					var s_dd = parseInt(obj_dd_s.value);
					var e_dd = parseInt(obj_dd_e.selectedIndex);
					if(e_dd < s_dd) 
					{
						errflg = 1;
					}
				}
			}
			
			if(errflg == 1)
			{
				alert(errmsg);
				return false;
			}
		}
		
		document.inputForm.action = "./account_edit.php?p_action=update_confirm";
		document.inputForm.submit();
		//setCookie("usr_edit_url",parent.bottom.location.href);
		//parent.bottom.location.href = url;
	} else {
		return false;
	}
}

function change_kikan(obj)
{
	var tmpval = obj.value;
	if(tmpval == "mukigenn")
	{
		var obj_yy_s = document.getElementById("account_yy_start");
		var obj_mm_s = document.getElementById("account_mm_start");
		var obj_dd_s = document.getElementById("account_dd_start");
		var obj_yy_e = document.getElementById("account_yy_end");
		var obj_mm_e = document.getElementById("account_mm_end");
		var obj_dd_e = document.getElementById("account_dd_end");
		
		obj_yy_s.selectedIndex = 0;
		obj_mm_s.selectedIndex = 0;
		obj_dd_s.selectedIndex = 0;
		obj_yy_e.selectedIndex = 0;
		obj_mm_e.selectedIndex = 0;
		obj_dd_e.selectedIndex = 0;
		
		obj_yy_s.disabled = true;
		obj_mm_s.disabled = true;
		obj_dd_s.disabled = true;
		obj_yy_e.disabled = true;
		obj_mm_e.disabled = true;
		obj_dd_e.disabled = true;
	} else {
		var obj_yy_s = document.getElementById("account_yy_start");
		var obj_mm_s = document.getElementById("account_mm_start");
		var obj_dd_s = document.getElementById("account_dd_start");
		var obj_yy_e = document.getElementById("account_yy_end");
		var obj_mm_e = document.getElementById("account_mm_end");
		var obj_dd_e = document.getElementById("account_dd_end");
		
		obj_yy_s.disabled = false;
		obj_mm_s.disabled = false;
		obj_dd_s.disabled = false;
		obj_yy_e.disabled = false;
		obj_mm_e.disabled = false;
		obj_dd_e.disabled = false;
	}
}
</script>
</head>
<body>
<form name="inputForm" id="inputForm" action="" method="post">
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<p class="new_account">アカウント編集</p>
			<table border="0" cellspacing="0" cellpadding="0" class="account_form">
				<tr>
					<th>No</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"userid\" id=\"userid\" value=\"".$usr->ID."\">";
							echo $usr->ID; 
						?>
					</td>
				</tr>
				<tr>
					<th>ユーザー名</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_name\" id=\"user_name\" value=\"".$usr->user_name."\">";
							echo $usr->user_name; 
						?>
					</td>
				</tr>
				<tr>
					<th>所属</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_group\" id=\"user_group\" value=\"".$usr->user_group."\">";
							echo $usr->user_group; 
						?>
					</td>
				</tr>
				<tr>
					<th>ID</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_login_id\" id=\"user_login_id\" value=\"".$usr->user_login_id."\">";
							echo $usr->user_login_id; 
						?>
					</td>
				</tr>
				<tr>
					<th>パスワード</th>
					<td>
					<input type="text" id="account_pass" name="account_pass" size="30" style="ime-mode:disabled" />
					<?php
						if($p_action == "reback")
						{
							$password_old = array_get_value($_SESSION,"acc_user_password_old","");
							echo "<input type=\"hidden\" id=\"account_pass_old\" name=\"account_pass_old\" size=\"30\" style=\"ime-mode:disabled\" value=\"".$password_old."\" >\r\n";
						} else {
							echo "<input type=\"hidden\" id=\"account_pass_old\" name=\"account_pass_old\" size=\"30\" style=\"ime-mode:disabled\" value=\"".$usr->user_password."\" >\r\n";
						}
					?>
					　現在のパスワード：<span><?php if($p_action == "reback") echo array_get_value($_SESSION,"acc_user_password_old",""); else echo $usr->user_password; ?></span></td>
				</tr>
				<tr>
					<th>画像番号</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_comp_code\" id=\"user_comp_code\" value=\"".$usr->user_comp_code."\">";
							echo $usr->user_comp_code; 
						?>
					</td>
				</tr>
				<tr>
					<th>権限設定</th>
					<td><ul>
                            <?php echo "<input type=\"hidden\" name=\"user_security_level\" id=\"user_security_level\" value=\"".$usr->user_security_level."\">"; ?>
                            <?php if($usr->user_security_level == 1){ ?>
							<li>
								<label><input name="account_search" type="radio" id="reg_situation0" value="1" checked="checked"/>：検索</label>
							</li>
                            <li>
                                <label><input name="account_search" id="reg_situation2" type="radio" value="3"/>：検索・権限切り替え</label>
                            </li>
                            <li>
                                <label><input name="account_search" id="reg_situation3" type="radio" value="4" />：検索・権限切り替え・管理</label>
                            </li>
                            <?php } ?>

                            <?php if($usr->user_security_level == 2){ ?>
                            <li>
                                <label><input name="account_search" id="reg_situation1" type="radio" value="2" checked="checked"/>：検索</label>
                            </li>
                            <li>
                                <label><input name="account_search" id="reg_situation2" type="radio" value="3"/>：検索・権限切り替え</label>
                            </li>
                            <li>
                                <label><input name="account_search" id="reg_situation3" type="radio" value="4" />：検索・権限切り替え・管理</label>
                            </li>
                            <?php } ?>

                            <?php if($usr->user_security_level == 3){ ?>
							<li>
								<label><input name="account_search" type="radio" id="reg_situation0" value="1"/>：検索</label>
							</li>
                            <li>
                                <label><input name="account_search" id="reg_situation2" type="radio" value="3" checked="checked"/>：検索・権限切り替え</label>
                            </li>
                            <li>
                                <label><input name="account_search" id="reg_situation3" type="radio" value="4" />：検索・権限切り替え・管理</label>
                            </li>
                            <?php } ?>

                            <?php if($usr->user_security_level == 4){ ?>
                            <li>
                                <label><input name="account_search" type="radio" id="reg_situation0" value="1" />：検索</label>
                            </li>
                            <li>
                                <label><input name="account_search" id="reg_situation2" type="radio" value="3"/>：検索・権限切り替え</label>
                            </li>
                            <li>
                                <label><input name="account_search" id="reg_situation3" type="radio" value="4" checked="checked"/>：検索・権限切り替え・管理</label>
                            </li>
                            <?php } ?>
						</ul></td>
				</tr>
				<tr>
					<th>設定期間</th>
					<td><ul class="limit">
							<li>
								<label>
								<?php echo "<input type=\"hidden\" name=\"user_kikan\" id=\"user_kikan\" value=\"".$usr->user_kikan."\">"; ?>
								<?php if($usr->user_kikan == "mukigenn"){ ?>
								<input name="account_period" type="radio" id="account_period" onclick='change_kikan(this);' value="mukigenn" checked="checked"/>
								<?php } else { ?>
								<input name="account_period" type="radio" id="account_period" onclick='change_kikan(this);' value="mukigenn" />
								<?php } ?>
								：無期限</label>
							</li>
							<li>
								<label>
								<?php if($usr->user_kikan == "sitei"){ ?>
								<input name="account_period" type="radio" id="account_period" onclick='change_kikan(this);' value="sitei" checked="checked"/>
								<?php } else { ?>
								<input name="account_period" type="radio" id="account_period" onclick='change_kikan(this);' value="sitei" />
								<?php } ?>
								：期間指定</label>
							</li>
						</ul>
						<p>
						<?php echo display_date();?>
						</p></td>
				</tr>
			</table>
			<div class="account_btn">
				<ul>
					<li class="account_cont_confirm">
						<p><a href="#" onclick="form_submit();return false;" title="内容確認">内容確認</a></p>
					</li>
					<li class="account_clear"><a href="#" onclick="form_clear();" title="リセット">リセット</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>
</form>
</body>
</html>
