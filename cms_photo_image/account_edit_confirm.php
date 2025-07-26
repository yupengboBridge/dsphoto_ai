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

$p_action = array_get_value($_REQUEST, 'p_action',"");// アクション

// ユーザーのインスタンスを生成します。
$usr = new UserManger();

// ユーザーのインスタンスを生成します。
$usr_old = new UserManger();

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	$p_user_id = array_get_value($_SESSION,"acc_userid","");
	$usr_old->select_data($db_link,$p_user_id);
	//print_r($usr_old);
	if ($p_action == "update")
	{
		// 更新する
		set_updatedata();
		$usr->update_data($db_link);
		print "<script src='./js/common.js'  type='text/javascript'  charset='utf-8'></script>\r\n";
		print "<script type=\"text/javascript\">\r\n";
		print "alert(\"ユーザー設定を更新しました。\");";
		print "parent.bottom.location.href = getCookie('usr_edit_url');";
		print "</script>";
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

	global $usr;
	global $db_link;
	#echo array_get_value($_SESSION,"acc_sp_auth","");
	switch (array_get_value($_SESSION,"acc_sp_auth","")) {
		case 'あり':
			$user_id = array_get_value($_POST,"userid","");
			$sql = "UPDATE user set show_sp = '1' where user_id = '{$user_id}'";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			break;
		case 'なし':
			$user_id = array_get_value($_POST,"userid","");
			$sql = "UPDATE user set show_sp = '0' where user_id = '{$user_id}'";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			break;
	}
	$usr->set_ID(array_get_value($_POST,"userid",""));
	$usr->set_user_password(array_get_value($_POST,"user_password",""));
	$usr->set_user_security_level(intval(array_get_value($_POST,"user_security_level","")));
	//xu add it on 2010-12-03 start
	$usr->set_user_email(array_get_value($_POST,"user_email",""));
	//xu add it on 2010-12-03 end
	
	$kikan = array_get_value($_POST,"user_kikan","");
	$usr->set_user_kikan($kikan);
	if($kikan == "sitei")
	{
		$tmpdate_start = array_get_value($_POST,"start_date","");
		$tmpdate_end = array_get_value($_POST,"end_date","");
		
		$usr->set_start_date($tmpdate_start);
		$usr->set_end_date($tmpdate_end);
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アカウント編集｜変更内容確認</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
function form_submit()
{
	var url = "./account_edit_confirm.php?p_action=update";
	//setCookie("usr_edit_url",parent.bottom.location.href);
	document.inputForm.action = url;
	document.inputForm.submit();
}
</script>
</head>
<body>
<form name="inputForm" id="inputForm" action="" method="post">
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<p class="new_account"> アカウント編集：<span>変更内容確認</span></p>
			<table border="0" cellspacing="0" cellpadding="0" class="account_form">
				<tr>
					<th>No</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"userid\" id=\"userid\" value=\"".array_get_value($_SESSION,"acc_userid","")."\">\r\n";
							echo array_get_value($_SESSION,"acc_userid",""); 
						?>
					</td>
				</tr>
				<tr>
					<th>ユーザー名</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_name\" id=\"user_name\" value=\"".array_get_value($_SESSION,"acc_user_name","")."\">\r\n";
							echo array_get_value($_SESSION,"acc_user_name",""); 
						?>
					</td>
				</tr>
				<tr>
					<th>所属</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_group\" id=\"user_group\" value=\"".array_get_value($_SESSION,"acc_user_group","")."\">\r\n";
							echo array_get_value($_SESSION,"acc_user_group",""); 
						?>
					</td>
				</tr>
				<tr>
					<th>ID</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_login_id\" id=\"user_login_id\" value=\"".array_get_value($_SESSION,"acc_user_login_id","")."\">\r\n";
							echo array_get_value($_SESSION,"acc_user_login_id",""); 
						?>
					</td>
				</tr>
				<tr>
					<th>パスワード</th>
					<td><?php 
							$new_password = array_get_value($_SESSION,"acc_user_password","");
							echo "<input type=\"hidden\" name=\"user_password\" id=\"user_password\" value=\"".$new_password."\">\r\n";
							$old_password = $usr_old->user_password;
							if(!empty($new_password) && ($new_password != $old_password))
							{
								echo "<em>".array_get_value($_SESSION,"acc_user_password","")."</em>\r\n"; 
							} else {
								echo array_get_value($_SESSION,"acc_user_password","")."\r\n"; 
							}
						?>
					</td>
				</tr>
				<tr>
					<th>画像番号</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"user_comp_code\" id=\"user_comp_code\" value=\"".array_get_value($_SESSION,"acc_user_comp_code","")."\">\r\n";
							echo array_get_value($_SESSION,"acc_user_comp_code",""); 
						?>
					</td>
				</tr>
				<tr>
					<th>権限設定</th>
					<td><ul>
						<li>
							<?php 
								$usr_sec_level = array_get_value($_SESSION,"acc_user_security_level","");
								$usr_sec_level_old = $usr_old->user_security_level;
								echo "<input type=\"hidden\" name=\"user_security_level\" id=\"user_security_level\" value=\"".$usr_sec_level."\">\r\n";
								if($usr_sec_level == 1 || $usr_sec_level == "1")
								{
									if((int)$usr_sec_level_old != (int)$usr_sec_level)
									{
										echo "<em><label>検索</label></em>\r\n";
									} else {
										echo "<label>検索</label>\r\n";
									}
								} elseif($usr_sec_level == 2 || $usr_sec_level == "2") {
									if((int)$usr_sec_level_old != (int)$usr_sec_level)
									{
										echo "<em><label>検索・登録申請</label></em>\r\n";
									} else {
										echo "<label>検索・登録申請</label>\r\n";
									}
								} elseif($usr_sec_level == 3 || $usr_sec_level == "3") {
									if((int)$usr_sec_level_old != (int)$usr_sec_level)
									{
										echo "<em><label>検索・登録申請・登録許可</label></em>\r\n";
									} else {
										echo "<label>検索・登録申請・登録許可</label>\r\n";
									}
								} elseif($usr_sec_level == 4 || $usr_sec_level == "4") {
									if((int)$usr_sec_level_old != (int)$usr_sec_level)
									{
										echo "<em><label>検索・登録申請・登録許可・管理</label></em>\r\n";
									} else {
										echo "<label>検索・登録申請・登録許可・管理</label>\r\n";
									}
								//xu add it on 20110131 start
								}elseif($usr_sec_level == 5 || $usr_sec_level == "5") {
									if((int)$usr_sec_level_old != (int)$usr_sec_level)
									{
										echo "<em><label>検索+お客様情報検索</label></em>\r\n";
									} else {
										echo "<label>検索+お客様情報検索</label>\r\n";
									}
								}
								//xu add it on 20110131 end
							?>
						</li>
					</ul>
					</td>
				</tr>
				<tr>
					<th>設定期間</th>
					<?php
						$user_kikan = array_get_value($_SESSION,"acc_user_kikan","");
						$user_kikan_old = $usr_old->user_kikan;
						
						if($user_kikan != $user_kikan_old)
						{
							$start_date = array_get_value($_SESSION,"acc_start_date_new","");
							$end_date = array_get_value($_SESSION,"acc_end_date_new","");
							
							if($user_kikan == "mukigenn")
							{
								echo "<td><label><em>無期限</em></label>&nbsp;</td>";
							} elseif ($user_kikan == "sitei") {
								$s_date_ary = explode("-",$start_date);
								$e_date_ary = explode("-",$end_date);
								
								echo "<td><label><em>期間指定&nbsp;&nbsp;&nbsp;&nbsp;</label>".$s_date_ary[0]." 年 ".$s_date_ary[1]." 月 ".$s_date_ary[2]." 日 ～ ".$e_date_ary[0]." 年 ".$e_date_ary[1]." 月 ".$e_date_ary[2]." 日 </em></td>";
							}
						} else {
							$start_date = array_get_value($_SESSION,"acc_start_date_new","");
							$end_date = array_get_value($_SESSION,"acc_end_date_new","");
							
							$start_date_old = $usr_old->start_date;
							$end_date_old = $usr_old->end_date;
							
							if($user_kikan == "mukigenn")
							{
								echo "<td><label>無期限</label>&nbsp;</td>";
							} elseif ($user_kikan == "sitei") {
								$s_date_ary = explode("-",$start_date);
								$e_date_ary = explode("-",$end_date);
								
								if($start_date != $start_date_old || $end_date != $end_date_old)
								{
									echo "<td><label><em>期間指定&nbsp;&nbsp;&nbsp;&nbsp;</label>".$s_date_ary[0]." 年 ".$s_date_ary[1]." 月 ".$s_date_ary[2]." 日 ～ ".$e_date_ary[0]." 年 ".$e_date_ary[1]." 月 ".$e_date_ary[2]." 日 </em></td>";
								} else {
									echo "<td><label>期間指定&nbsp;&nbsp;&nbsp;&nbsp;</label>".$s_date_ary[0]." 年 ".$s_date_ary[1]." 月 ".$s_date_ary[2]." 日 ～ ".$e_date_ary[0]." 年 ".$e_date_ary[1]." 月 ".$e_date_ary[2]." 日 </td>";
								}
							}
						}
						echo "<input type=\"hidden\" name=\"user_kikan\" id=\"user_kikan\" value=\"".$user_kikan."\">\r\n";
						echo "<input type=\"hidden\" name=\"start_date\" id=\"start_date\" value=\"".$start_date."\">\r\n";
						echo "<input type=\"hidden\" name=\"end_date\" id=\"end_date\" value=\"".$end_date."\">\r\n";
					?>
				</tr>
				<!-- add liucongxu 20211117-->
				<tr>
					<th>SP权限</th>
					<td><?php 
							echo "<input type=\"hidden\" name=\"acc_sp_auth\" id=\"acc_sp_auth\" value=\"".array_get_value($_SESSION,"acc_sp_auth","")."\">\r\n";
							echo array_get_value($_SESSION,"acc_sp_auth",""); 
						?>
					</td>
				</tr>
				<!-- xu add it on 2010-12-03 start -->
				<tr>
					<th>メールアドレス</th>
					<td><?php 
							$new_email = array_get_value($_SESSION,"acc_user_email","");
							$old_email = $usr_old->user_email;
							if(!empty($new_email) && ($new_email != $old_email))
							{
								echo "<em>".array_get_value($_SESSION,"acc_user_email","")."</em>\r\n"; 
							} else {
								echo array_get_value($_SESSION,"acc_user_email","")."\r\n"; 
							}
							echo "<input type=\"hidden\" name=\"user_email\" id=\"user_email\" value=\"".array_get_value($_SESSION,"acc_user_email","")."\">\r\n";
						?>
					</td>
				</tr>
				<!-- xu add it on 2010-12-03 end -->
			</table>
			<div class="account_btn">
				<ul>
					<li class="account_reload">
						<p><a href="#" onclick="form_submit();return false;" title="更新">更新</a></p>
					</li>
					<li class="account_corection">
						<?php echo "<a href=\"#\" onclick=\"parent.bottom.location.href = './account_edit.php?id=".array_get_value($_SESSION,"acc_userid","")."&p_action=reback'\" title=\"修正\">修正</a>\r\n" ?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
</form>
</body>
</html>
