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

try
{
	$p_action = array_get_value($_REQUEST, 'p_action',"");
	
	if ($p_action == "import")
	{	
		$db_link = db_connect();
		upload();
	}
} catch(Exception $cla) {
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

function getmaxID()
{
	global $db_link;

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

function import($filepath)
{
	global $db_link;
	
	$handle = fopen ($filepath,"r");
	$sql="insert into `user`(`email`,`login_id`,`user_name`,`password`,`security_level`,`group`,`compcode`,`user_kikan`,`register_date`,`login_date`) values(\"";
	
	$flg_head = true;
	$icnt = 0;
	$error_flg = false;
	
	while ($data=fgetcsv($handle,3200,"\t"))
	{
		clearstatcache();
		if($flg_head == true)
		{
			$flg_head = false;
			continue;
		}
		
		//delete
		$sql_delete = "delete from `user` where `login_id` = \"".$data[1]."\"";
		$stmt_delete = $db_link->prepare($sql_delete);
		$okflg = $stmt_delete->execute();
		//delete
			
		$num = count ($data);
		
		for ($c=0; $c < $num; $c++)
		{
			$sql=$sql.$data[$c]."\",\"";
		}

		if($data[$num-1] == "BUD")
		{
			$sql=$sql.getmaxID()."\",\"";
		} elseif ($data[$num-1] == "HEI") {
			$sql=$sql.getmaxID_HEI()."\",\"";
		} else {
			$sql=$sql.getmaxID_HEI()."\",\"";
		}
		
		$sql=$sql."mukigenn"."\",";
		$sql=$sql."now(),now());";
		//print $sql;
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if($result == true)
		{
			//print $data[2]."ユーザーを追加しました。<br/>";
		} else {
			print "【".$data[2]."】ユーザーの追加は失敗しました。<br/>";
			$error_flg = true;
		}
		
		$sql="insert into `user`(`email`,`login_id`,`user_name`,`password`,`security_level`,`group`,`compcode`,`user_kikan`,`register_date`,`login_date`) values(\"";
		
		$icnt = $icnt + 1;
	}
	
	fclose($handle);
	
	if($error_flg == false && $icnt > 0)
	{
		$msg = "CSVファイルのユーザーは全てDBに導入されました。";
		print "<script type=\"text/javascript\">";
		print "alert(\"".$msg."\");";
		print "parent.bottom.location.href  = \"./import_userlist.php\";";
		print "</script>";
	}
}

/*
 * 関数名：upload
 * 関数説明：CSVファイルとイメージファイルをアップロードする
 * パラメタ：無し
 * 戻り値：無し
 */
function upload()
{
	// CSVファイルのPATHを設定
	$csvdir = "./usercsv/";
	
	try
	{
		// アップロードするのCSVファイル名を取得する
		$filename = $_FILES['user_csvfile']['name'];

		// ファイルパス
		$f_path = "";
		$f_path = $csvdir.$filename;
		if (move_uploaded_file($_FILES['user_csvfile']['tmp_name'], $f_path) == true)
		{
			import($f_path);
		} else {
			$err_exits_msg = "CSVファイルのアップロードは失敗です。";
			// エラーメッセージを出力する
			print "<script type=\"text/javascript\">";
			print "alert(\"".$err_exits_msg."\");";
			print "parent.bottom.location.href  = \"./import_userlist.php\";";
			print "</script>";
		}
	}
	catch(Exception $cla)
	{
		// 異常を出力する
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>新規アカウント作成</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script type="text/javascript" charset="utf-8">
function form_submit()
{
	var obj = document.getElementById('user_csvfile');
	if(obj)
	{
		if(obj.value.length > 0)
		{
			document.inputForm.action = "./import_userlist.php?p_action=import";
			document.inputForm.submit();
		} else {
			alert("CSVファイルを選択してください。");
			return false;
		}
	} else {
		alert("CSVファイルを選択してください。");
		return false;
	}
}
</script>
</head>
<body>
<form name="inputForm" action="" method="post"  enctype="multipart/form-data" >
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<p class="new_account"> アカウントインポート </p>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="file" style="width: 220px;" size="60" value="" id="user_csvfile" name="user_csvfile"><br/><br/><br/>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" onclick="form_submit();" value="インポート実行" id="csv_submit" name="csv_submit">
		</div>
	</div>
</div>
</form>
</body>