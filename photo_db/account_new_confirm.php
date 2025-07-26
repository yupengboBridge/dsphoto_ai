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

// イメージ検索のクラス
$user_all = new UserManger();

// ユーザーのインスタンスを生成します。
$usr = new UserManger();

$db_link;

$p_action = array_get_value($_REQUEST, 'p_action',"");// アクション

try
{
	if ($p_action == "insert")
	{
		// ＤＢへ接続します。
		$db_link = db_connect();
	
		// 新規する
		set_insertdata();
		//print_r($usr);
		$usr->insert_data($db_link);
		print "<script src='./js/common.js'  type='text/javascript'  charset='utf-8'></script>\r\n";
		print "<script type=\"text/javascript\" charset='utf-8'>\r\n";
		print "alert(\"新規ユーザーを追加しました。\");";
		print "parent.bottom.location.href = \"./account_list.php\";";
		print "</script>";
	}
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
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

/*
 * 関数名：set_insertdata
 * 関数説明：ユーザーの新規
 * パラメタ：無し
 * 戻り値：無し
 */
function set_insertdata()
{
	global $usr;

	$usr->set_user_name(array_get_value($_POST,"user_name",""));
	$tmp_user_group = array_get_value($_POST,"user_group","");
	$usr->set_user_group($tmp_user_group);
	$usr->set_user_login_id(array_get_value($_POST,"user_login_id",""));
	$usr->set_user_password(array_get_value($_POST,"user_password",""));
	if($tmp_user_group == "HEI")
	{
		$usr->set_user_comp_code(array_get_value($_POST,"hd_user_comp_code_hei",""));
	} else {
		$usr->set_user_comp_code(array_get_value($_POST,"hd_user_comp_code",""));
	}
	$usr->set_user_security_level(intval(array_get_value($_POST,"user_security_level","")));
	
	$kikan = array_get_value($_POST,"user_kikan","");
	$usr->set_user_kikan($kikan);
	if($kikan == "sitei")
	{
		$tmpdate_start = array_get_value($_POST,"start_date","");
		$tmpdate_end = array_get_value($_POST,"end_date","");
		
		$usr->set_start_date($tmpdate_start);
		$usr->set_end_date($tmpdate_end);
	} else {
		$usr->set_start_date("");
		$usr->set_end_date("");
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>新規アカウント作成｜登録内容確認</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
</head>
<body>
<form name="inputForm" action="" method="post">
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<p class="new_account"> 新規アカウント作成：<span>登録内容確認</span></p>
			<table border="0" cellspacing="0" cellpadding="0" class="account_form">
				<tr>
					<th>No</th>
					<td>
						<?php
							echo "<input type=\"hidden\" name=\"hd_ID\" id=\"hd_ID\" value=\"".getmaxID()."\" >\r\n";
							echo getmaxID();
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
							echo "<input type=\"hidden\" name=\"user_password\" id=\"user_password\" value=\"".array_get_value($_SESSION,"acc_user_password","")."\">\r\n";
							echo array_get_value($_SESSION,"acc_user_password","")."\r\n"; 
						?>
					</td>
				</tr>
				<tr>
					<th>画像番号</th>
					<td>
						<?php
							echo "<input type=\"hidden\" name=\"hd_user_comp_code\" id=\"hd_user_comp_code\" value=\"".sprintf("%05d",getmaxID())."\" >\r\n";
							echo "<input type=\"hidden\" name=\"hd_user_comp_code_hei\" id=\"hd_user_comp_code_hei\" value=\"".sprintf("%05d",getmaxID_HEI())."\" >\r\n";
							$tmp_group = array_get_value($_SESSION,"acc_user_group","");
							if($tmp_group == "BUD")
							{
								echo sprintf("%05d",getmaxID());
							} else {
								echo sprintf("%05d",getmaxID_HEI());
							}
						?>
					</td>
				</tr>
				<tr>
					<th>権限設定</th>
					<td><ul>
						<li>
							<?php 
								$usr_sec_level = array_get_value($_SESSION,"acc_user_security_level","");
								echo "<input type=\"hidden\" name=\"user_security_level\" id=\"user_security_level\" value=\"".$usr_sec_level."\">\r\n";
								if($usr_sec_level == 1 || $usr_sec_level == "1")
								{
									echo "<label>検索</label>\r\n";
								} elseif($usr_sec_level == 2 || $usr_sec_level == "2") {
									echo "<label>検索</label>\r\n";
								} elseif($usr_sec_level == 3 || $usr_sec_level == "3") {
									echo "<label>検索・権限切り替え</label>\r\n";
								} elseif($usr_sec_level == 4 || $usr_sec_level == "4") {
									echo "<label>検索・権限切り替え・管理</label>\r\n";
								}
							?>
						</li>
					</ul>
					</td>
				</tr>
				<tr>
					<th>設定期間</th>
					<?php 
						$user_kikan = array_get_value($_SESSION,"acc_user_kikan","");
						$start_date = array_get_value($_SESSION,"acc_start_date_new","");
						$end_date = array_get_value($_SESSION,"acc_end_date_new","");
						
						if($user_kikan == "mukigenn")
						{
							echo "<td><label>無期限</label>&nbsp;</td>";
						} elseif ($user_kikan == "sitei") {
							$s_date_ary = split("-",$start_date);
							$e_date_ary = split("-",$end_date);
							
							echo "<td><label>期間指定&nbsp;&nbsp;&nbsp;&nbsp;</label>".$s_date_ary[0]." 年 ".$s_date_ary[1]." 月 ".$s_date_ary[2]." 日 ～ ".$e_date_ary[0]." 年 ".$e_date_ary[1]." 月 ".$e_date_ary[2]." 日 </td>";
						}
						echo "<input type=\"hidden\" name=\"user_kikan\" id=\"user_kikan\" value=\"".$user_kikan."\">\r\n";
						echo "<input type=\"hidden\" name=\"start_date\" id=\"start_date\" value=\"".$start_date."\">\r\n";
						echo "<input type=\"hidden\" name=\"end_date\" id=\"end_date\" value=\"".$end_date."\">\r\n";
					?>
				</tr>
			</table>
			<div class="account_btn">
				<ul>
					<li class="account_confirm01">
						<p><a href="#" onclick="document.inputForm.action = './account_new_confirm.php?p_action=insert';document.inputForm.submit();" title="登録">登録</a></p>
					</li>
					<li class="account_corection"><?php echo "<a href=\"#\" onclick=\"parent.bottom.location.href = './account_new.php?id=".array_get_value($_SESSION,"acc_userid","")."&p_action=reback'\" title=\"修正\">修正</a>\r\n" ?></li>
				</ul>
			</div>
		</div>
	</div>
</div>
</form>
</body>
</html>
