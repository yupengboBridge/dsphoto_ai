<?php
require_once('./config.php');
require_once('./lib.php');

date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

// セッション（$_SESSION）より情報を取得します。
$s_login_id = array_get_value($_SESSION, 's_login_id');						// ログインID
$s_login_name = array_get_value($_SESSION, 's_login_name');					// ログイン名
$s_security_level = array_get_value($_SESSION, 's_security_level');			// セキュリティーレベル
$s_user_id = array_get_value($_SESSION, 's_user_id');						// ユーザーID

// for Debug
$s_user_id = 1;
$s_login_name = "中尾　友一";
$s_login_id = "nakao";

// ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	//header('Location: ' . $logout_page);
	header_out($logout_page);
}

// CSVファイルのPATHを設定
$csvdir = "./csv/";

/*
 * 関数名：showDataList
 * 関数説明：一覧データを作成する
 * パラメタ：無し
 * 戻り値：無し
 */
function showDataList()
{
	global $csvdir;

	try
	{
		$file = @file($csvdir."i_p_hatsu_url.csv");
		// ファイルは見つからない場合
		if (!$file)
		{
			// エラーメッセージを表示する
			print "<script type=\"text/javascript\">";
			print "alert(\"「i_p_hatsu_url.csv」CSVファイルは見つかりません。ご確認ください。\");";
			print "parent.bottom.location.href  = \"./db_managemnt.php\";";
			print "</script>";
			return;
		}

		// CSVファイルを開く
		$file = fopen($csvdir."i_p_hatsu_url.csv","r");

		// ファイルオープンエラー
		if (!$file)
		{
			// エラーメッセージを表示する
			print "<script type=\"text/javascript\">";
			print "alert(\"CSVファイルのオープンは失敗です。CSVファイルをご確認ください。\");";
			print "parent.bottom.location.href  = \"./db_managemnt.php\";";
			print "</script>";
			return;
		}

		// CSVファイルからフィールド名を取得する
		if (!feof($file))
		{
			// CSVの内容
			$csv_fields = (fgetcsv($file));
		} else {
			// CSVファイルを閉じる
			fclose($file);
		}

		// ファイルの内容より繰り返し一覧データを作成する
		while(!feof($file))
		{
			// 行の内容は配列にする
			$csv_content = (fgetcsv($file));
			print "<tr>\r\n";
			print "	<td>".dp($csv_content[0])."</td>\r\n";
			print "	<td>".dp($csv_content[1])."</td>\r\n";
			print "	<td class=\"csv_left\">".dp($csv_content[2])."</td>\r\n";
			print "	<td>".dp($csv_content[3])."</td>\r\n";
			print "	<td class=\"csv_left\">".dp($csv_content[4])."</td>\r\n";
			print "</tr>\r\n";
		}

		// CSVファイルを閉じる
		fclose($file);
	}
	catch (Exception $e)
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
<title>アルバム登録</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
<!--
/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
	//----------フレームの設定  開始---------------
	var obj_frame = top.document.getElementById('iframe_middle1');
	if(obj_frame) obj_frame.height = 0;
	var obj_frame = top.document.getElementById('iframe_middle2');
	if(obj_frame) obj_frame.height = 0;
	//var obj_frame = top.document.getElementById('iframe_bottom');
	//if(obj_frame) obj_frame.height = 900;
	set_frameheight('iframe_bottom',500);
	//----------フレームの設定  終了---------------
}

window.onload = function()
{
	init();
}
-->
</script>
</head>
<body>
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<h2>i_p_hatsu_url.csv 一覧</h2>
			<div class="list_contents">
				<table border="0" cellspacing="0" cellpadding="0" class="csv_management">
					<tr>
						<th class="csv_ttl">p_hatsu</th>
						<th class="csv_dep">出発地</th>
						<th>URL</th>
						<th class="csv_top">_top</th>
						<th class="csv_dep">出発地振分け</th>
					</tr>
					<?php  showDataList(); ?>
				</table>
			</div>
		</div>
	</div>
</div>
</body>
</html>