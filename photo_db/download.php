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

// FTP サーバー
$ftp_server = "";
// FTP ユーザー
$ftp_user = "";
// FTP パスワード
$fpt_password = "";
// FTP パス
$fpt_path = "";

// ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	//header('Location: ' . $logout_page);
	header_out($logout_page);
}

// CSVファイルのPATHを設定
$csvdir = "./csv/";
// イメージファイルのPATHを設定
$imgdir = "./uploads/";

// アクション
$p_action = array_get_value($_REQUEST, 'p_action');
// ダウンロードファイル名
$downloadfile = array_get_value($_REQUEST,'downloadfile');

try
{
	// エラーメッセージ
	$err_msg = "";
	// ファイルパス
	$file_dir = "";

	// ＤＢへ接続します。
	$db_link = db_connect();
	// CSVクラス
	$csv = new CsvFile();

	// CSVファイルをダウロードする時
	if ($p_action == "downloadfilecsv")
	{
		$file_dir = $csvdir.$downloadfile;
		$err_msg = "CSVファイルは見つかりません。ご確認ください。";
	// イメージファイルをダウロードする時
	} elseif ($p_action == "downloadfileimg") {
		$file_dir = $imgdir.$downloadfile;
		$err_msg = "イメージファイルは見つかりません。ご確認ください。";
	}

	$file = @file($file_dir);
	// ファイルのオープンはエラーの場合
	if (!$file)
	{
		print "<script type=\"text/javascript\">";
		print "alert(\"「".$downloadfile."」".$err_msg."\");";
		print "</script>";
		return;
	} else {
		// ファイルの情報を出力する
//		$file = fopen($file_dir,"r");
//		//change_up_download_state($downloadfile,2);
//		print "<script type=\"text/javascript\">";
//		print "parent.bottom.location.href  = \"./db_managemnt.php\";";
//		print "</script>";
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: ".filesize($file_dir));
		Header("Content-Disposition: attachment; filename=" .$downloadfile);
		// ファイルの内容を出力する
//		if (is_dir("C:\\temp")==false) mkdir("C:\\temp");
//		$file1 = fopen("C:\\temp\\".$downloadfile,"w");
//		fwrite($file1,file_get_contents($file_dir));
		//fclose($file);
//		fclose($file1);
//ftpUpload($downloadfile);
	}
}
catch (Exception $e)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

/*
 * 関数名：getFtpLoginInfor
 * 関数説明：FTPログインの情報を取得する
 * パラメタ：無し
 * 戻り値：無し
 */
function getFtpLoginInfor()
{
	global $ftp_server,$ftp_user,$fpt_password,$fpt_path,$local_path;

	try
	{
		// FTPXMLを読み込む
		$ftpxml = simplexml_load_file("ftpxml/ftpxml.xml");
		// FTP サーバー
		$ftp_server = dp($ftpxml->server);
		// FTP ユーザー
		$ftp_user = dp($ftpxml->username);
		// FTP パスワード
		$fpt_password = dp($ftpxml->password);
		// FTP パス
		$fpt_path = dp($ftpxml->serverpath);
		// ローカル パス
		$local_path = dp($ftpxml->localpath);
	}
	catch(Exception $cla)
	{
		// 異常を出力する
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}
}

/*
 * 関数名：ftpUpload
 * 関数説明：FTPでファイルをアップロードする
 * パラメタ：xmlfilename:XMLファイル名
 * 戻り値：無し
 */
function ftpUpload($dir)
{

	global $ftp_server,$ftp_user,$fpt_password,$fpt_path,$local_path;

getFtpLoginInfor();


	try
	{
//		if ($file = @file($dir,"r"))
//		{
			// FTPを接続する
			$conn = ftp_connect($ftp_server);
			// FTPを登録する
			$login_ok = ftp_login($conn, $ftp_user, $fpt_password);
			// 失敗した場合
			if (!$login_ok)
			{
				ftp_close($conn);
				return;
			}

			ftp_chdir($conn,"/");
	//$root_tmp = $_SERVER['DOCUMENT_ROOT'];
	$root_tmp = "var/www/html";
	$path_parts = pathinfo($_SERVER['PHP_SELF']);
	//$tmp = $path_parts["dirname"];
	$tmp = "/test/photodb1105";
	$dir_tmp = $root_tmp.$tmp."/csv";

			ftp_chdir($conn,$dir_tmp);

			// FTPでアップロードする
			$download = ftp_get($conn, "aa.csv", ftp_pwd($conn)."/".$dir, FTP_BINARY);
			if (!$download)
			{
				print "<script type=\"text/javascript\">";
				print "alert(\"ＦＴＰのアップロードは失敗です。\");";
				print "parent.bottom.location.href  = \"./db_managemnt.php\";";
				print "</script>";
			} else {
				print "<script type=\"text/javascript\">";
				print "alert(\"ＦＴＰにアップロードしました。\");";
				print "parent.bottom.location.href  = \"./db_managemnt.php\";";
				print "</script>";
			}
			// FTPを閉じる
			ftp_close($conn);
//		} else {
//			print "<script type=\"text/javascript\">";
//			print "alert(\"XMLファイルは見つかりません。ご確認ください。\");";
//			print "</script>";
//			return;
//		}
	}
	catch(Exception $cla)
	{
		// 異常を出力する
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}
}

/*
 * 関数名：change_up_download_state
 * 関数説明：アップロードとダウロードの状態を管理する
 * パラメタ：
 * filename:CSVファイル名
 * flg：「1」：アップロードを更新する；
 *     「2」：ダウロードを更新する；
 * 戻り値：無し
 */
function change_up_download_state($filename,$flg)
{

	global $s_login_id, $csv, $db_link;

	$csv->file_name = $filename;
	// アップロードを更新する
	if ((int)$flg == 1)
	{
		$csv->up_user = $s_login_id;
		$csv->up_time = date("Y-d-m h:i:s");
		$csv->update_data($db_link,$flg);
	// ダウロードを更新する
	} elseif ((int)$flg == 2) {
		$csv->down_user = $s_login_id;
		$csv->down_time = date("Y-d-m h:i:s");
		$csv->update_data($db_link,$flg);
	}
}
?>