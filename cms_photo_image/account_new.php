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

//ログインしているかをチェックします。
if (empty($s_login_id) || $s_security_level != 4 || $s_security_level != "4")
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

$p_action = array_get_value($_REQUEST, 'p_action',"");

// イメージ検索のクラス
$usr = new UserManger();

try
{
	if ($p_action == "insert_confirm")
	{
		set_insertdataToSession();
		print "<script src='./js/common.js'  type='text/javascript'  charset='utf-8'></script>\r\n";
		print "<script type=\"text/javascript\">\r\n";
		print "parent.bottom.location.href = \"./account_new_confirm.php\";";
		print "</script>";
	} elseif ($p_action == "reback") {
		$usr->set_user_name(array_get_value($_SESSION,"acc_user_name",""));
		$usr->set_user_group(array_get_value($_SESSION,"acc_user_group",""));
		$usr->set_user_login_id(array_get_value($_SESSION,"acc_user_login_id",""));
		$usr->set_user_kikan(array_get_value($_SESSION,"acc_user_kikan",""));
		$usr->set_user_security_level(array_get_value($_SESSION,"acc_user_security_level",""));
		//xu add it on 2010-12-3 start
		$usr->set_user_email(array_get_value($_SESSION,"acc_user_email",""));
		//xu add it on 2010-12-3 end
		if($usr->user_kikan != "mukigenn")
		{
			$usr->set_start_date(array_get_value($_SESSION,"acc_start_date_new",""));
			$usr->set_end_date(array_get_value($_SESSION,"acc_end_date_new",""));
		}
	} else {
		$_SESSION["acc_user_name"] = "";
		$_SESSION["acc_user_group"] = "";
		$_SESSION["acc_user_login_id"] = "";
		$_SESSION["acc_user_password"] = "";
		$_SESSION["acc_user_kikan"] = "";
		$_SESSION["acc_user_security_level"] = "";
		$_SESSION["acc_start_date_new"] = "";
		$_SESSION["acc_end_date_new"] = "";
		//xu add it on 2010-12-4 start
		$_SESSION["acc_user_email"] = "";
		//xu add it on 2010-12-4 end
	}
} catch(Exception $cla) {
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

$db_link;

function set_insertdataToSession()
{
	$_SESSION["acc_user_name"] = array_get_value($_POST,"user_name","");
	$_SESSION["acc_user_group"] = array_get_value($_POST,"syozoku","");
	$_SESSION["acc_user_login_id"] = array_get_value($_POST,"account_id","");
	$_SESSION["acc_user_password"] = array_get_value($_POST,"account_pass","");
	$_SESSION["acc_user_kikan"] = array_get_value($_POST,"account_period","");
	$_SESSION["acc_sp_auth"] = array_get_value($_POST,"sp_auth","");
	$_SESSION["acc_user_security_level"] = array_get_value($_POST,"account_search","");
	//xu add it on 2010-12-3 start
	$_SESSION["acc_user_email"] = array_get_value($_POST,"account_email","");
	//xu add it on 2010-12-3 end
	
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

function getmaxID()
{
	global $db_link;

	$db_link = db_connect();

	$sql = "SELECT max(user_id)+1 as max_id FROM `user`";

	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();

	if ($result == true)
	{
		// 最終番号を取得します。
		$max = $stmt->fetch(PDO::FETCH_ASSOC);
		return $max['max_id'];
	} else {
		return 0;
	}
}

function getmaxID_HEI()
{
	global $db_link;

	$db_link = db_connect();

	$sql = "SELECT max( `compcode` ) AS max_compcode FROM `user` WHERE `group` = 'HEI'";

	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();

	if ($result == true)
	{
		// 最終番号を取得します。
		$max = $stmt->fetch(PDO::FETCH_ASSOC);
		return (int)$max['max_compcode']+1;
	} else {
		return 0;
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
		
		$html = "<select id=\"account_yy_start\" name=\"account_yy_start\">\r\n";
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
		$html = "<select id=\"account_yy_start\" name=\"account_yy_start\" disabled>\r\n";
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
			$html .= "	<option value=\"".sprintf("%04d",$i)."\">".$i."</option>\r\n";
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
<title>新規アカウント作成</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
function form_clear()
{
	var obj_pwd = document.getElementById("account_pass");
	if(obj_pwd) obj_pwd.value = "";
	
	var user_name = document.getElementById("user_name");
	if(user_name) user_name.value = "";
	
	var account_id = document.getElementById("account_id");
	if(account_id) account_id.value = "";
	
	//yupengbo add 20110105 start
	var account_email = document.getElementById("account_email");
	if(account_email) account_email.value = "";
	//yupengbo add 20110105 end
	
	var syozoku = document.getElementById("syozoku");
	if(syozoku) syozoku.selectedIndex = 0;
	
	var objs = document.getElementsByName("account_period");
	if(objs)
	{
		if(objs[0] && objs[1]) objs[0].checked = true;objs[1].checked = false;
		
		var obj_yy_s = document.getElementById("account_yy_start");
		var obj_mm_s = document.getElementById("account_mm_start");
		var obj_dd_s = document.getElementById("account_dd_start");
		var obj_yy_e = document.getElementById("account_yy_end");
		var obj_mm_e = document.getElementById("account_mm_end");
		var obj_dd_e = document.getElementById("account_dd_end");

		if(obj_yy_s) obj_yy_s.selectedIndex = 0;obj_yy_s.disabled = true;
		if(obj_mm_s) obj_mm_s.selectedIndex = 0;obj_mm_s.disabled = true;
		if(obj_dd_s) obj_dd_s.selectedIndex = 0;obj_dd_s.disabled = true;
		if(obj_yy_e) obj_yy_e.selectedIndex = 0;obj_yy_e.disabled = true;
		if(obj_mm_e) obj_mm_e.selectedIndex = 0;obj_mm_e.disabled = true;
		if(obj_dd_e) obj_dd_e.selectedIndex = 0;obj_dd_e.disabled = true;
	}
	
	objs = document.getElementsByName("account_search");
	if(objs)
	{
		//xu modified it on 20110131 start
		if(objs[0] && objs[1] && objs[2] && objs[3]&& objs[4]) objs[0].checked = true;objs[1].checked = false;objs[2].checked = false;objs[3].checked = false;objs[4].checked = false;
		//xu modified it on 20110131 start
	}
}

function digit(check)
{
	return (('0'<=check) && (check<='9'));
}

function alpha(check)
{
	return ((('a'<=check) && (check<='z')) || (('A'<=check) && (check<='Z')))
}

function form_submit()
{
	var obj = document.getElementById("user_name");
	var obj_val = null;
	if(obj)
	{
		obj_val = obj.value;
		if(obj_val.length <= 0)
		{
			alert("ユーザー名を入力してください。");
			return false;
		}
	}
	
	obj = document.getElementById("syozoku");
	if(obj)
	{
		if(obj.selectedIndex == 0)
		{
			alert("所属を選択してください。");
			return false;
		}
	}
	
	obj = document.getElementById("account_id");
	if(obj)
	{
		obj_val = obj.value;
		if(obj_val.length <= 0)
		{
			alert("IDを入力してください。");
			return false;
		}

		if(obj_val.match(/[ｱ-ﾝｧｨｩｪｫｬｭｮ]/))
		{
			alert("IDを確認してください。");
			return false;
		}
		if(obj_val.match(/[ア-ンァィゥェォッャュョ]/))
		{
			alert("IDを確認してください。");
			return false;
		}
//		for (index=0; index < obj_val.length; index++)
//		{
//			check = obj_val.charAt(index);
//			if (!(digit(check) || alpha(check)))
//			{
//				alert("IDを確認してください。");
//				return false;
//			}
//		}
	}
	
	obj = document.getElementById("account_pass");
	if(obj)
	{
		obj_val = obj.value;
		if(obj_val.length <= 0)
		{
			alert("パスワードを入力してください。");
			return false;
		} else {
			var obj_pwd_val = obj_val;
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
				var s_yy = Number(obj_yy_s.value);
				var e_yy = Number(obj_yy_e.value);
				
				if(e_yy < s_yy) errflg = 1;
			}
			
			if(obj_yy_s && obj_yy_e && obj_mm_s && obj_mm_e && errflg == 0)
			{
				var s_yy = parseInt(obj_yy_s.value);
				var e_yy = parseInt(obj_yy_e.value);
				if(s_yy == e_yy)
				{
					var s_mm = Number(obj_mm_s.value);
					var e_mm = Number(obj_mm_e.selectedIndex);

					if(e_mm < s_mm) errflg = 1;
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
					var s_dd = Number(obj_dd_s.value);
					var e_dd = Number(obj_dd_e.selectedIndex);
					if(e_dd < s_dd) errflg = 1;
				}
			}
			
			if(errflg == 1)
			{
				alert("期間指定の年月日を確認してください。");
				return false;
			}
		}
		
		document.inputForm.action = "./account_new.php?p_action=insert_confirm";
		document.inputForm.submit();
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

function SelectGroupChange(obj)
{
	if(obj)
	{
		var obj_val = obj.value;
		if(obj_val == "BUD")
		{
			var obj_td = document.getElementById("user_comp_code");
			if(obj_td)
			{
				var obj_hidden = document.getElementById("hd_user_comp_code");
				var obj_user_comp_code_val = "";
				
				if(obj_hidden)
				{
					obj_user_comp_code_val = obj_hidden.value;
				}
				obj_td.innerHTML = "						" + obj_user_comp_code_val;
			}
		} else {
			var obj_td = document.getElementById("user_comp_code");
			if(obj_td)
			{
				var obj_hidden = document.getElementById("hd_user_comp_code_hei");
				var obj_user_comp_code_val = "";
				
				if(obj_hidden)
				{
					obj_user_comp_code_val = obj_hidden.value;
				}
				obj_td.innerHTML = "						" + obj_user_comp_code_val;
			}
		}
	}
}
</script>
</head>
<body>
<form name="inputForm" action="" method="post">
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<p class="new_account"> 新規アカウント作成 </p>
			<table border="0" cellspacing="0" cellpadding="0" class="account_form">
				<tr>
					<th>No</th>
					<td><?php echo getmaxID();?></td>
				</tr>
				<tr>
					<th>ユーザー名</th>
					<td><input name="user_name" type="text" id="user_name" size="30" value="<?php echo $usr->user_name; ?>"/></td>
				</tr>
				<tr>
					<th>所属</th>
					<td><select id="syozoku" name="syozoku" onchange="SelectGroupChange(this);">
							<option selected="selected">選択してください</option>
							<option value="HEI" <?php $usg = array_get_value($_SESSION,"acc_user_group",""); if($usg == "HEI") echo "selected=\"selected\"";?> >hei</option>
							<option value="BUD" <?php $usg = array_get_value($_SESSION,"acc_user_group",""); if($usg == "BUD") echo "selected=\"selected\"";?> >bud</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>ID</th>
					<td><input name="account_id" type="text" id="account_id" size="30" style="ime-mode:disabled" value="<?php echo $usr->user_login_id; ?>"/></td>
				</tr>
				<tr>
					<th>パスワード</th>
					<td><input name="account_pass" type="text" id="account_pass" size="30" style="ime-mode:disabled" value=""/></td>
				</tr>
				<tr>
					<th>画像番号</th>
					<td id="user_comp_code">
					<?php 
							$tmp_group = array_get_value($_SESSION,"acc_user_group","");
							if($tmp_group == "BUD")
							{
								echo sprintf("%05d",getmaxID());
							} else {
								echo sprintf("%05d",getmaxID_HEI());
							}
					?>
					</td>
					<td>
						<?php 
							echo "<input type=\"hidden\" name=\"hd_user_comp_code\" id=\"hd_user_comp_code\" value=\"".sprintf("%05d",getmaxID())."\" >\r\n";
							echo "<input type=\"hidden\" name=\"hd_user_comp_code_hei\" id=\"hd_user_comp_code_hei\" value=\"".sprintf("%05d",getmaxID_HEI())."\" >\r\n";
						?>
					</td>
				</tr>
				<tr>
					<th>権限設定</th>
					<td><ul>
							<li>
								<label>
								<input name="account_search" type="radio" id="reg_situation0"  value="1" <?php if(empty($usr->user_security_level) || $usr->user_security_level == 1 || $usr->user_security_level == "1") echo "checked=\"checked\""; ?> />
								：検索</label>
							</li>
							<!-- xu add it on 20110131 start-->
							<li>
								<label>
								<input name="account_search" type="radio" id="reg_situation4" value="5" <?php if($usr->user_security_level == 5 || $usr->user_security_level == "5") echo "checked=\"checked\""; ?> />
								：検索+お客様情報検索</label>
							</li>
							<!-- xu add it on 20110131 end-->
							<li>
								<label>
								<input name="account_search" id="reg_situation1" type="radio" value="2" <?php if($usr->user_security_level == 2 || $usr->user_security_level == "2") echo "checked=\"checked\""; ?> />
								：検索・登録申請</label>
							</li>
							<li>
								<label>
								<input name="account_search" id="reg_situation2" type="radio" value="3" <?php if($usr->user_security_level == 3 || $usr->user_security_level == "3") echo "checked=\"checked\""; ?> />
								：検索・登録申請・登録許可</label>
							</li>
							<li>
								<label>
								<input name="account_search" id="reg_situation3" type="radio" value="4" <?php if($usr->user_security_level == 4 || $usr->user_security_level == "4") echo "checked=\"checked\""; ?> />
								：検索・登録申請・登録許可・管理</label>
							</li>
						</ul></td>
				</tr>
				<tr>
					<th>設定期間</th>
					<td><ul class="limit">
							<li>
								<label>
								<input name="account_period" type="radio" id="reg_situation4" onclick='change_kikan(this);' value="mukigenn" <?php if($usr->user_kikan == "mukigenn" || empty($usr->user_kikan)) echo "checked=\"checked\""; ?> />
								：無期限</label>
							</li>
							<li>
								<label>
								<input name="account_period" id="reg_situation5" type="radio" value="sitei" onclick='change_kikan(this);' <?php if($usr->user_kikan == "sitei") echo "checked=\"checked\""; ?> />
								：期間指定</label>
							</li>
						</ul>
						<p>
							<?php echo display_date();?>
						</p>
					</td>
				</tr>
				<tr>
					<th>SP権限</th>
					<td><ul class="limit">
							<li>
								<label>
								<?php
									$db_link = db_connect();
									$select_show_sp = "SELECT show_sp FROM user WHERE user_id = '{$usr->ID}' ";
									$sp_stmt = $db_link->prepare($select_show_sp);
									$sp_stmt->execute();
									$is_sp_data = $sp_stmt->fetch(PDO::FETCH_ASSOC);
									$is_show_sp = $is_sp_data['show_sp'] ;
								?>
								<?php if($is_show_sp == '1'){ ?>
								<input name="sp_auth" type="radio" id="sp_auth" onclick='change_spauth(this);' value="あり" checked="checked"/>
								<?php } else { ?>
								<input name="sp_auth" type="radio" id="sp_auth" onclick='change_spauth(this);' value="あり"/>
								<?php } ?>
								あり</label>
							</li>
							<li>
								<label>
								<?php if($is_show_sp != '1'){ ?>
								<input name="sp_auth" type="radio" id="sp_auth" onclick='change_spauth(this);' value="なし" checked="checked"/>
								<?php } else { ?>
								<input name="sp_auth" type="radio" id="sp_auth" onclick='change_spauth(this);' value="なし"/>
								<?php } ?>
								なし</label>
							</li>
						</ul>
					</td>
				</tr>
				<!-- xu add it on 2010-12-03 start -->
				<tr>
					<th>メールアドレス</th>
					<td><input name="account_email" type="text" id="account_email" size="50" style="ime-mode:disabled" value="<?php echo $usr->user_email; ?>"/></td>
				</tr>
				<!-- xu add it on 2010-12-03 end -->
			</table>
			<div class="account_btn">
				<ul>
					<li class="account_confirm">
						<p><a href="#" onclick="form_submit();return false;" title="登録確認">登録確認</a></p>
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
