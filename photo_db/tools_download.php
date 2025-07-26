<?php
require_once('./config.php');
require_once('./lib.php');

// ログファイルのPATHを設定
$logdir = "./log/";

// アクション
$p_action = array_get_value($_REQUEST, 'p_action' ,"");
// ダウンロードファイル名
$downloadfile = array_get_value($_REQUEST,'downloadfile' ,"");

try
{
	if ($p_action == "downloadfilelog")
	{
		download();
	}
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

/*
 * 関数名：download
 * 関数説明：CSVファイルとイメージファイルをダウンロードする
 * パラメタ：無し
 * 戻り値：無し
 */
function download()
{
	global $downloadfile,$p_action,$logdir;

	try
	{
		// エラーメッセージ
		$err_msg = "";
		// ファイルパス
		$file_dir = "";

		$file_dir = $logdir.$downloadfile;
		$err_msg = "ログファイルは見つかりません。ご確認ください。";

		$file = is_file($file_dir);
		// ファイルのオープンはエラーの場合
		if (!$file)
		{
			print "<script type=\"text/javascript\">";
			print "alert(\"「".$downloadfile."」".$err_msg."\");";
			print "</script>";
			return;
		} else {
			// ファイルの情報を出力する
			$file = fopen($file_dir,"r");
			Header("Pragma:public") ;
			Header("Expires:0");
			Header("Cache-Control:must-revalidate,post-check=0,pre-check-0");
			Header("Cache-Control:private",false);
			Header("Content-Type:applicateion/octet-stream");
			Header("Content-Disposition:attachment;filename=\"".$downloadfile."\";");
			Header("Content-Transfer-Encoding:binary");
			Header("Content-Length:".filesize($file_dir));
			// ファイルの内容を出力する
			echo fread($file,filesize($file_dir));
			fclose($file);
		}
	}
	catch (Exception $e)
	{
		// 異常を出力する
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}
}
?>