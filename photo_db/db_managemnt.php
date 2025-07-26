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

//// for Debug
//$s_user_id = 1;
//$s_login_name = "BUD管理者";
//$s_login_id = "admin";

// ログインしているかをチェックします。
if (empty($s_login_id) || $s_security_level != 4 || $s_security_level != "4")
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

// CSVファイルのPATHを設定
$csvdir = "./csv/";
// イメージファイルのPATHを設定
$imgdir = "./uploads/";
// XMLファイルのPATHを設定
$xmldir = "./xml/";
// ログファイルのPATHを設定
$logdir = "./log/";

// FTP サーバー
$ftp_server = "";
// FTP ユーザー
$ftp_user = "";
// FTP パスワード
$fpt_password = "";
// FTP パス
$fpt_path = "";
// ローカル パス
$local_path = "";
// CSVの内容
$csv_fields = "";

// アクション
$p_action = array_get_value($_REQUEST, 'p_action' ,"");
// アップロードCSVとかイメージとかのインデックス
$p_file_key = array_get_value($_REQUEST, 'txt_key' ,"");
// XMLファイル名
$xmlfile = array_get_value($_REQUEST,'xmlfile' ,"");
// ダウンロードファイル名
$downloadfile = array_get_value($_REQUEST,'downloadfile' ,"");


try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	// CSVクラス
	$csv = new CsvFile();

	// 解除の処理
 	if ($p_action == "unlock")
 	{
		change_up_download_state($downloadfile,1);
		print "<script type=\"text/javascript\">";
		print "parent.bottom.location.href  = \"./db_managemnt.php\";";
		print "</script>";
	}

	// 海外拠点URLリストの「アップロード」と「ダウンロード」の状態
	$p_hatsu_state_ary = array();
	$p_hatsu_state_ary = get_up_down_state("i_p_hatsu_url.csv");

	// 国内拠点URLリストの「アップロード」と「ダウンロード」の状態
	$p_hatsu_state_ary_d = array();
	$p_hatsu_state_ary_d = get_up_down_state("d_p_hatsu_url.csv");

	// 海外拠点/専門店URLリストの「アップロード」と「ダウンロード」の状態
	$p_h_c_s_ary = array();
	$p_h_c_s_ary = get_up_down_state("i_hatsu_country_url.csv");

	// 国内拠点/専門店URLリストの「アップロード」と「ダウンロード」の状態
	$p_h_c_s_ary_d = array();
	$p_h_c_s_ary_d = get_up_down_state("d_hatsu_prefecture_url.csv");

	// 海外専門店URLリストの「アップロード」と「ダウンロード」の状態
	$p_c_ary = array();
	$p_c_ary = get_up_down_state("i_p_country_url.csv");

	// 国内専門店URLリストの「アップロード」と「ダウンロード」の状態
	$p_c_ary_d = array();
	$p_c_ary_d = get_up_down_state("d_p_prefecture_url.csv");

	// 海外拠点URLリストの「アップロード」と「ダウンロード」の状態
	$a_p_h_ary = array();
	$a_p_h_ary = get_up_down_state("a_p_hatsu_url.csv");

	// ブランドコードとp_hei対応表の「アップロード」と「ダウンロード」の状態
	$p_hei_ary = array();
	$p_hei_ary = get_up_down_state("3letter_p_hei.csv");

	// アップロードの処理
	if ($p_action == "uploadfilecsv" || $p_action == "uploadfileimg")
	{
		upload();
	// ＦＴＰでアップロードする時
	} elseif ($p_action == "sftpUpload") {
		getSFtpLoginInfor();
		sftpUpload($xmlfile);
	// ダウンロードの処理
	} elseif ($p_action == "downloadfilecsv" || $p_action == "downloadfileimg" || $p_action == "downloadfiledellog") {
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
 * 関数名：get_up_down_state
 * 関数説明：ダウンロードとアップロードの状態を取得する
 * パラメタ：
 * filename:CSVファイル名
 * 戻り値：無し
 */
function get_up_down_state($filename)
{
	global $csv, $db_link, $s_login_id;

	$csv->file_name = $filename;
	$ok_flg = $csv->select_data($db_link);
	$res_ary = array();

	// 検索は正常の場合
	if ((int)$ok_flg != -1)
	{
		// ダウンロードとアップロードは全部NULLの場合
		if ((empty($csv->up_user) && empty($csv->down_user)) || (strlen($csv->up_user) <=0 && strlen($csv->down_user) <=0))
		{
			$res_ary[0] = 0;		// ダウンロードの状態は無効になる
			$res_ary[1] = 1;		// アップロードの状態は有効になる
		// ダウンロードはNULL、アップロードはありの場合
		} elseif (!empty($csv->up_user) && empty($csv->down_user)) {
			$res_ary[0] = 1;		// ダウンロードの状態は有効になる
			$res_ary[1] = 0;		// アップロードの状態は無効になる
		// アップロードはNULL、ダウンロードはありの場合
		} elseif (empty($csv->up_user) && !empty($csv->down_user)) {
			// ダウンロードのユーザーとログインのユーザーは同じ場合
			if (($csv->down_user) == $s_login_id)
			{
				$res_ary[0] = 0;	// ダウンロードの状態は無効になる
				$res_ary[1] = 1;	// アップロードの状態は有効になる
			} else {
				$res_ary[0] = 0;	// ダウンロードの状態は無効になる
				$res_ary[1] = 0;	// アップロードの状態は無効になる
			}
		}
	}
	return $res_ary;
}

/*
 * 関数名：change_up_download_state
 * 関数説明：アップロードとダウンロードの状態を管理する
 * パラメタ：
 * filename:CSVファイル名
 * flg：「1」：アップロードを更新する；
 *     「2」：ダウンロードを更新する；
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
		$csv->up_time = "'".date("Y-d-m H:i:s")."'";
		$csv->update_data($db_link,$flg);
	// ダウンロードを更新する
	} elseif ((int)$flg == 2) {
		$csv->down_user = $s_login_id;
		$csv->down_time = "'".date("Y-d-m H:i:s")."'";
		$csv->update_data($db_link,$flg);
	}
}

/*
 * 関数名：csv_format_check
 * 関数説明：CSVファイルの書式のチェック
 * パラメタ：filename:ファイル名;
 * file_content:ファイル一行の内容
 * f_line_size:ファイル一行のサイズ
 * 戻り値：無し
 */
function csv_format_check($filename,$file_content,$f_line_size)
{
	// ファイルを読み込む
	$content = file_get_contents($filename);
	// ファイルコードのチェック
    if ($content == mb_convert_encoding(mb_convert_encoding($content, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
    {
        $ed = count($file_content);
        // ファイルの区切りとフィールド数のチェック
        if ((int)$ed == (int)$f_line_size)
        {
			return true;
        } else {
        	return false;
        }
    }
    else
    {
        return false;
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
	global $csvdir,$imgdir,$p_action,$p_file_key,$site_url;

	try
	{
		// アップロードするのCSVファイル名を取得する
		$filename = $_FILES[$p_file_key]['name'];

		if ( !empty($filename) && $filename != "i_p_hatsu_url.csv"
		    && $filename != "i_hatsu_country_url.csv" && $filename != "i_p_country_url.csv"
		    && $filename != "d_p_hatsu_url.csv" && $filename != "d_hatsu_prefecture_url.csv"
		    && $filename != "d_p_prefecture_url.csv" && $filename != "a_p_hatsu_url.csv"
		    && $filename != "3letter_p_hei.csv")
	    {
			// エラーメッセージを表示する
			print "<script type=\"text/javascript\">";
			print "alert(\"アップロードできないファイルです（ファイル名エラー）。\r\n\ （ファイル名：".$filename."）);";
			print "parent.bottom.location.href  = \"./db_managemnt.php\";";
			print "</script>";
			return;
	    }
		// ファイルパス
		$f_path = "";

		// CSVファイルをアップロードする時
		if ($p_action == "uploadfilecsv")
		{
			$f_path = $csvdir.$filename;
		// イメージファイルをアップロードする時
		} elseif ($p_action == "uploadfileimg") {
			$f_path = $imgdir.$filename;
		}

		// ローコールファイル
		$fileurl = array_get_value($_COOKIE,"fileurl","");
		$tmpstr = "\\"."\\";
		$tmp_fileurl = "";
		if (strpos($fileurl,$tmpstr) > 0)
		{
			$tmp_fileurl = $fileurl;
		} else {
			$tmp_fileurl = str_replace("\\",$tmpstr,$fileurl);
		}

		//----------指定PATHのファイルを存在かどうかを判断する-----------------
		//if (file_exists($tmp_fileurl))
		//{
			// アップロードを行う
			if (move_uploaded_file($_FILES[$p_file_key]['tmp_name'], $f_path) == true)
			{
				// CSVファイルをアップロードする時
				if ($p_action == "uploadfilecsv")
				{
					// サーバーのアップロードファイルURLを取得する
					$web_fileurl = $site_url.$csvdir.$filename;
				// イメージファイルをアップロードする時
				} elseif ($p_action == "uploadfileimg") {
					// サーバーのアップロードファイルURLを取得する
					$web_fileurl = $site_url.$imgdir.$filename;
				}

				// クーキーに設定する
				print "<script type=\"text/javascript\" src=\"js/common.js\"  charset=\"utf-8\"></script>\r\n";
				print "<script type=\"text/javascript\">\r\n";
				print "setCookie(".$p_file_key.","."\"\");\r\n";
				print "setCookie(".$p_file_key.",\"".$web_fileurl."\");\r\n";
				print "</script>\r\n";

				// CSVファイルをアップロードする時
				if ($p_action == "uploadfilecsv")
				{
					// XMLを作成する
					$xml_ok_flg = creteXML($filename);
					// XMLを作成する時、エラーが発生した場合
					if ($xml_ok_flg == false)
					{
						// エラーメッセージを表示する
//						print "<script type=\"text/javascript\">";
//						print "alert(\"XMLの作成は失敗です。CSVファイルをご確認ください。\");";
//						print "parent.bottom.location.href  = \"./db_managemnt.php\";";
//						print "</script>";
						return;
					}
				}

				// 正常のメッセージ
				$ok_msg = "";
				// CSVファイルをアップロードする時
				if ($p_action == "uploadfilecsv")
				{
					$ok_msg = "CSVファイルはアップロードしました。";
				// イメージファイルをアップロードする時
				} elseif ($p_action == "uploadfileimg") {
					$ok_msg = "イメージファイルはアップロードしました。";
				}

				// メッセージを出力する
				print "<script type=\"text/javascript\">";
				print "alert(\"".$ok_msg."\");";
				print "parent.bottom.location.href  = \"./db_managemnt.php\";";
				print "</script>";

				change_up_download_state($filename,1);
			} else {
				$err_exits_msg = "";
				// CSVファイルをアップロードする時
				if ($p_action == "uploadfilecsv")
				{
					$err_exits_msg = "CSVファイルのアップロードは失敗です。";
				// イメージファイルをアップロードする時
				} elseif ($p_action == "uploadfileimg") {
					$err_exits_msg = "イメージファイルのアップロードは失敗です。";
				}

				// エラーメッセージを出力する
				print "<script type=\"text/javascript\">";
				print "alert(\"".$err_exits_msg."\");";
				print "parent.bottom.location.href  = \"./db_managemnt.php\";";
				print "</script>";
			}
//		} else
//		{
//			// CSVファイルをアップロードする時
//			if ($p_action == "uploadfilecsv")
//			{
//				$err_exits_msg = "アップロードのCSVファイルは見つかりません。";
//			// イメージファイルをアップロードする時
//			} elseif ($p_action == "uploadfileimg") {
//				$err_exits_msg = "アップロードのイメージファイルは見つかりません。";
//			}
//			// エラーメッセージを表示する
//			print "<script type=\"text/javascript\">";
//			print "alert(\"".$err_exits_msg."\");";
//			print "parent.bottom.location.href  = \"./db_managemnt.php\";";
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
 * 関数名：download
 * 関数説明：CSVファイルとイメージファイルをダウンロードする
 * パラメタ：無し
 * 戻り値：無し
 */
function download()
{
	global $downloadfile,$p_action,$csvdir,$imgdir,$logdir;

	try
	{
		// エラーメッセージ
		$err_msg = "";
		// ファイルパス
		$file_dir = "";
		// CSVファイルをダウンロードする時
		if ($p_action == "downloadfilecsv")
		{
			$file_dir = $csvdir.$downloadfile;
			$err_msg = "CSVファイルは見つかりません。ご確認ください。";
		// イメージファイルをダウンロードする時
		} elseif ($p_action == "downloadfileimg") {
			$file_dir = $imgdir.$downloadfile;
			$err_msg = "イメージファイルは見つかりません。ご確認ください。";
		// 削除画像ログファイルをダウンロードする時
		} elseif ($p_action == "downloadfiledellog") {
			$file_dir = $logdir.$downloadfile;
			$err_msg = "ログファイルは見つかりません。ご確認ください。";
		}

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
			change_up_download_state($downloadfile,2);
		}
	}
	catch (Exception $e)
	{
		// 異常を出力する
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}
}

/*
 * 関数名：creteXML
 * 関数説明：CSVファイルよりXMLを作成する
 * パラメタ：
 * filename：ファイル名前
 * 戻り値：作成するかどうか　True/False
 */
function creteXML($filename)
{
	global $xmldir,$csvdir;
	try
	{
		// CSVファイルを開く
		$file = fopen($csvdir.$filename,"r");

		// CSVファイルからフィールド名を取得する
		if (!feof($file))
		{
			// CSVの内容
			$csv_fields = (fgetcsv($file));
		} else {
			// CSVファイルを閉じる
			fclose($file);
		}

		// XMLの内容
		$xml_content = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
		$xml_content .= "<root>\r\n";

		// ファイルの行数
		$i_line = 1;
		// ファイルの内容より繰り返し、XMLファイルを作成する
		while(!feof($file))
		{
			// 行の内容は配列にする
			//$csv_contentstr = fgets($file);
			$csv_content = (fgetcsv($file));

			$i_line = $i_line + 1;

			if (empty($csv_content)) continue;

			// i_p_hatsu_url.csvファイルの場合
			if ($filename == "i_p_hatsu_url.csv")
			{
				// i_p_hatsu_url.csvファイルのサイズをチェックする
				if ((int)count($csv_content) == 6)
				{
					// ファイルの書式のチェック
					if (csv_format_check($csvdir.$filename,$csv_content,6))
					{
						// i_p_hatsu_url.csvファイルよりXMLを作成する
						$xml_content .= "   <h".$csv_content[0]."  hatsuname=\"".$csv_content[1]."\"  href=\"".$csv_content[2]."\"";
						$xml_content .= " top=\"".$csv_content[3]."\"  p_hatsuname=\"".$csv_content[4]."\"  p_hatsucode=\"".$csv_content[5]."\"/>\r\n";
					} else {
						// CSVファイルを閉じる
						fclose($file);
						// エラーメッセージを表示する
						print "<script type=\"text/javascript\">";
						$msg = "「i_p_hatsu_url.csv」ファイルの書式は間違いです。行番号：".$i_line;
						print "alert(\"".$msg."\");";
						print "parent.bottom.location.href  = \"./db_managemnt.php\";";
						print "</script>";

						return false;
					}
				} else {
					// CSVファイルを閉じる
					fclose($file);
					// エラーメッセージを表示する
					print "<script type=\"text/javascript\">";
					print "alert(\"「i_p_hatsu_url.csv」ファイルのサイズは間違いです。ご確認ください。\");";
					print "parent.bottom.location.href  = \"./db_managemnt.php\";";
					print "</script>";

					return false;
				}
			// d_p_hatsu_url.csvファイルの場合
			}elseif ($filename == "d_p_hatsu_url.csv")
			{
				// d_p_hatsu_url.csvファイルのサイズをチェックする
				if ((int)count($csv_content) == 8)
				{
					// ファイルの書式のチェック
					if (csv_format_check($csvdir.$filename,$csv_content,8))
					{
						// d_p_hatsu_url.csvファイルよりXMLを作成する
						$xml_content .= "   <h".$csv_content[0]."  hatsuname=\"".$csv_content[1]."\"  hatsucode_sub=\"".$csv_content[2]."\"";
						$xml_content .= " hatsuname_sub=\"".$csv_content[3]."\" href=\"".$csv_content[4]."\" ";
						$xml_content .= " top=\"".$csv_content[5]."\"  p_hatsuname=\"".$csv_content[6]."\"  p_hatsucode=\"".$csv_content[7]."\"/>\r\n";
					} else {
						// CSVファイルを閉じる
						fclose($file);
						// エラーメッセージを表示する
						print "<script type=\"text/javascript\">";
						$msg = "「d_p_hatsu_url.csv」ファイルの書式は間違いです。行番号：".$i_line;
						print "alert(\"".$msg."\");";
						print "parent.bottom.location.href  = \"./db_managemnt.php\";";
						print "</script>";

						return false;
					}
				} else {
					// CSVファイルを閉じる
					fclose($file);
					// エラーメッセージを表示する
					print "<script type=\"text/javascript\">";
					print "alert(\"「d_p_hatsu_url.csv」ファイルのサイズは間違いです。ご確認ください。\");";
					print "parent.bottom.location.href  = \"./db_managemnt.php\";";
					print "</script>";

					return false;
				}
			// i_p_country_url.csv,d_p_prefecture_url.csvファイルの場合
			} elseif ($filename == "i_p_country_url.csv" || $filename == "d_p_prefecture_url.csv") {
				if ((int)count($csv_content) == 3)
				{
					// ファイルの書式のチェック
					if (csv_format_check($csvdir.$filename,$csv_content,3))
					{
						if ($filename == "d_p_prefecture_url.csv")
						{
							// d_p_prefecture_url.csvファイルよりXMLを作成する
							$xml_content .= "   <p".$csv_content[0]."  p_prefecture=\"".$csv_content[1]."\"  href=\"".$csv_content[2]."\"/>\r\n";
						} else {
							// i_p_country_url.csvファイルよりXMLを作成する
							$xml_content .= "   <".$csv_content[0]."  p_countryname=\"".$csv_content[1]."\"  href=\"".$csv_content[2]."\"/>\r\n";
						}
					} else {
						// CSVファイルを閉じる
						fclose($file);
						// エラーメッセージを表示する
						print "<script type=\"text/javascript\">";
						$msg = "「".$filename."」ファイルの書式は間違いです。行番号：".$i_line;
						print "alert(\"".$msg."\");";
						print "parent.bottom.location.href  = \"./db_managemnt.php\";";
						print "</script>";

						return false;
					}
				} else {
					// CSVファイルを閉じる
					fclose($file);
					// エラーメッセージを表示する
					print "<script type=\"text/javascript\">";
					print "alert(\"".$filename."ファイルのサイズは間違いです。ご確認ください。\");";
					print "parent.bottom.location.href  = \"./db_managemnt.php\";";
					print "</script>";

					return false;
				}
			// i_hatsu_country_url.csv,d_hatsu_prefecture_url.csvファイルの場合
			} elseif ($filename == "i_hatsu_country_url.csv" || $filename == "d_hatsu_prefecture_url.csv") {
				// i_hatsu_country_url.csvファイルのサイズをチェックする
				if ((int)count($csv_content) == 9)
				{
					if (csv_format_check($csvdir.$filename,$csv_content,9))
					{
						if ($filename == "i_hatsu_country_url.csv")
						{
							// i_hatsu_country_url.csvファイルよりXMLを作成する
							$xml_content .= "   <".$csv_content[0]."  p_countryname=\"".$csv_content[1]."\"  tyo=\"".$csv_content[2]."\"";
							$xml_content .= "   osa=\"".$csv_content[3]."\"  ngo=\"".$csv_content[4]."\"  fuk=\"".$csv_content[5]."\"";
							$xml_content .= "   spk=\"".$csv_content[6]."\"  sdj=\"".$csv_content[7]."\"  hij=\"".$csv_content[8]."\"/>\r\n";
						} else {
							// d_hatsu_prefecture_url.csvファイルよりXMLを作成する
							$xml_content .= "   <p".$csv_content[0]."  p_prefecture=\"".$csv_content[1]."\"  tyo=\"".$csv_content[2]."\"";
							$xml_content .= "   osa=\"".$csv_content[3]."\"  ngo=\"".$csv_content[4]."\"  fuk=\"".$csv_content[5]."\"";
							$xml_content .= "   spk=\"".$csv_content[6]."\"  sdj=\"".$csv_content[7]."\"  hij=\"".$csv_content[8]."\"/>\r\n";
						}
					} else {
						// CSVファイルを閉じる
						fclose($file);
						// エラーメッセージを表示する
						print "<script type=\"text/javascript\">";
						$msg = "「".$filename."」ファイルの書式は間違いです。行番号：".$i_line;
						print "alert(\"".$msg."\");";
						print "parent.bottom.location.href  = \"./db_managemnt.php\";";
						print "</script>";

						return false;
					}
				} else {
					// CSVファイルを閉じる
					fclose($file);
					// エラーメッセージを表示する
					print "<script type=\"text/javascript\">";
					print "alert(\"".$filename."ファイルのサイズは間違いです。ご確認ください。\");";
					print "parent.bottom.location.href  = \"./db_managemnt.php\";";
					print "</script>";

					return false;
				}
			// a_p_hatsu_url.csvファイルの場合
			} elseif ($filename == "a_p_hatsu_url.csv") {
				// a_p_hatsu_url.csvファイルのサイズをチェックする
				if ((int)count($csv_content) == 6)
				{
					// ファイルの書式のチェック
					if (csv_format_check($csvdir.$filename,$csv_content,6))
					{
						// a_p_hatsu_url.csvファイルよりXMLを作成する
						$xml_content .= "   <h".$csv_content[0]."  hatsuname=\"".$csv_content[1]."\"  href=\"".$csv_content[2]."\"";
						$xml_content .= " top=\"".$csv_content[3]."\"  p_hatsuname=\"".$csv_content[4]."\"  p_hatsucode=\"".$csv_content[5]."\"/>\r\n";
					} else {
						// CSVファイルを閉じる
						fclose($file);
						// エラーメッセージを表示する
						print "<script type=\"text/javascript\">";
						$msg = "「a_p_hatsu_url.csv」ファイルの書式は間違いです。行番号：".$i_line;
						print "alert(\"".$msg."\");";
						print "parent.bottom.location.href  = \"./db_managemnt.php\";";
						print "</script>";

						return false;
					}
				} else {
					// CSVファイルを閉じる
					fclose($file);
					// エラーメッセージを表示する
					print "<script type=\"text/javascript\">";
					print "alert(\"「a_p_hatsu_url.csv」ファイルのサイズは間違いです。ご確認ください。\");";
					print "parent.bottom.location.href  = \"./db_managemnt.php\";";
					print "</script>";

					return false;
				}
			// 3letter_p_hei.csvファイルの場合
			} elseif ($filename == "3letter_p_hei.csv") {
				if ((int)count($csv_content) == 2)
				{
					// ファイルの書式のチェック
					if (csv_format_check($csvdir.$filename,$csv_content,2))
					{
						// 3letter_p_hei.csvファイルよりXMLを作成する
						$xml_content .= "   <h".$csv_content[0]." p_web_brand=\"".$csv_content[0]."\" p_hei=\"".$csv_content[1]."\"/>\r\n";
					} else {
						// CSVファイルを閉じる
						fclose($file);
						// エラーメッセージを表示する
						print "<script type=\"text/javascript\">";
						$msg = "「3letter_p_hei.csv」ファイルの書式は間違いです。行番号：".$i_line;
						print "alert(\"".$msg."\");";
						print "parent.bottom.location.href  = \"./db_managemnt.php\";";
						print "</script>";

						return false;
					}
				} else {
					// CSVファイルを閉じる
					fclose($file);
					// エラーメッセージを表示する
					print "<script type=\"text/javascript\">";
					print "alert(\"3letter_p_hei.csvファイルのサイズは間違いです。ご確認ください。\");";
					print "parent.bottom.location.href  = \"./db_managemnt.php\";";
					print "</script>";

					return false;
				}
			} else {
				// CSVファイルを閉じる
				fclose($file);
				// エラーメッセージを表示する
				print "<script type=\"text/javascript\">";
				print "alert(\"ファイルの名前は間違いです。ご確認ください。\");";
				print "parent.bottom.location.href  = \"./db_managemnt.php\";";
				print "</script>";

				return false;
			}
		}
		$xml_content .= "</root>";

		// CSVファイルを閉じる
		fclose($file);

		// XMLファイルの名前を作成する
		$pos = strpos($filename,".");
		$filename01 = substr($filename,0,$pos).".xml";

		// XMLファイルを出力する
		$file = fopen($xmldir.$filename01,"w");
		fwrite($file,$xml_content);
		fclose($file);
		return true;
	}
	catch(Exception $cla)
	{
		// 異常を出力する
		$msg[] = $cla->getMessage();
		error_exit($msg);
		return false;
	}
}

/*
 * 関数名：getSFtpLoginInfor
 * 関数説明：FTPログインの情報を取得する
 * パラメタ：無し
 * 戻り値：無し
 */
function getSFtpLoginInfor()
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
 * 関数名：sftpUpload
 * 関数説明：FTPでファイルをアップロードする
 * パラメタ：xmlfilename:XMLファイル名
 * 戻り値：無し
 */
function sftpUpload($xmlfilename)
{
	global $ftp_server,$ftp_user,$fpt_password,$fpt_path,$local_path;

	try
	{
		//echo $local_path.$xmlfilename;
		//if ($file = @file($local_path.$xmlfilename,"r"))
		//{
			// SFTPを接続する
			$connection = ssh2_connect($ftp_server);
			// SFTPを登録する
			$login_ok = ssh2_auth_password($connection, $ftp_user, $fpt_password);
			// 失敗した場合
			if (!$login_ok) return;
			// SFTPでアップロードする
			$upload = ssh2_scp_send($connection, $local_path.$xmlfilename, "sftptest/".$xmlfilename, 0644);
			if (!$upload)
			{
				print "<script type=\"text/javascript\">";
				print "alert(\"ＳＦＴＰのアップロードは失敗です。\");";
				print "parent.bottom.location.href  = \"./db_managemnt.php\";";
				print "</script>";
			} else {
				print "<script type=\"text/javascript\">";
				print "alert(\"ＳＦＴＰにアップロードしました。\");";
				print "parent.bottom.location.href  = \"./db_managemnt.php\";";
				print "</script>";
			}
		//} else {
		//	print "<script type=\"text/javascript\">";
		//	print "alert(\"XMLファイルは見つかりません。ご確認ください。\");";
		//	print "</script>";
		//	return;
		//}
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
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>無題ドキュメント</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<script type="text/javascript" src="js/common.js"  charset="utf-8"></script>
<script type="text/javascript">
<!--
/*
 * 関数名：form_submit
 * 関数説明：フォームのサバミート
 * パラメタ：obj_key:テキストボックスＩＤ;
 * flg:「1」：CSVファイルをアップロードする；「2」：イメージファイルをアップロードする
 * 戻り値：無し
 */
function form_submit(obj_key,flg)
{
	// テキストボックスを取得する
	var flname = document.getElementById(obj_key);
	// エラーメッセージ
	var err_msg = "";

	// 入力チェック
	if (flname.value == null || flname.value.length == 0)
	{
		// CSVファイルをアップロードする時
		if (flg == 1)
		{
			err_msg = "CSVファイルを選択してください。";
		// イメージファイルをアップロードする時
		} else {
			err_msg = "イメージファイルを選択してください。";
		}

		alert(err_msg);
		flname.focus();
		return false;
	}

	// ファイル名のチェック
	// 海外拠点URLリスト
	if (obj_key == "csv_file3_1")
	{
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "i_p_hatsu_url.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「i_p_hatsu_url.csv」ファイルを選択してください。";
		}
	// 海外拠点/専門店URLリスト
	} else if (obj_key == "csv_file3_2") {
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "i_hatsu_country_url.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「i_hatsu_country_url.csv」ファイルを選択してください。";
		}
	// 海外専門店URLリスト
	} else if (obj_key == "csv_file3_3") {
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "i_p_country_url.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「i_p_country_url.csv」ファイルを選択してください。";
		}
	// 国内拠点URLリスト
	} else if (obj_key == "csv_file3_4") {
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "d_p_hatsu_url.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「d_p_hatsu_url.csv」ファイルを選択してください。";
		}
	// 国内拠点/専門店URLリスト
	} else if (obj_key == "csv_file3_5") {
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "d_hatsu_prefecture_url.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「d_hatsu_prefecture_url.csv」ファイルを選択してください。";
		}
	// 国内専門店URLリスト
	} else if (obj_key == "csv_file3_6") {
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "d_p_prefecture_url.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「d_p_prefecture_url.csv」ファイルを選択してください。";
		}
	// 航空券拠点URLリスト
	} else if (obj_key == "csv_file3_7") {
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "a_p_hatsu_url.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「a_p_hatsu_url.csv」ファイルを選択してください。";
		}
	// ブランドコードとp_hei対応表
	} else if (obj_key == "csv_file4") {
		var tmp = flname.value.replace(/.*\\/,"");
		if (tmp != "3letter_p_hei.csv" && flg == 1)
		{
			err_msg = "アップロッドできないCSVファイルです。\r\n 「3letter_p_hei.csv」ファイルを選択してください。";
		}
	}
	if (err_msg != "" && err_msg.length > 0)
	{
		alert(err_msg);
		flname.focus();
		return false;
	}
	// 拡張子を取得する
	var dotpos = flname.value.lastIndexOf(".");
	var ext = flname.value.substr(dotpos);
	ext = ext.toLowerCase();
	// CSVファイルをアップロードする時
	if (flg == 1)
	{
		// 拡張子のチェック
		if (ext != ".csv")
		{
			alert("アップロッドできない種類のファイルです。\r\n（拡張子.csvのみアップロッド可能です。）");
			flname.focus();
			return false;
		}
	// イメージファイルをアップロードする時
	} else if (flg == 2) {
		// 拡張子のチェック
		if (ext != ".jpg" && ext != ".jpeg" && ext != ".png" && ext != ".gif")
		{
			alert("アップロッドできない種類のファイルです。\r\n（拡張子.jpeg、.jpg、.png、.gifのみアップロッド可能です。）");
			flname.focus();
			return false;
		}
	}

	setCookie("fileurl",flname.value);

	// CSVファイルをアップロードする時
	if (flg == 1)
	{
		document.db_managemnt.action = "./db_managemnt.php?p_action=uploadfilecsv&txt_key=" + obj_key + "";
	// イメージファイルをアップロードする時
	} else if (flg == 2) {
		document.db_managemnt.action = "./db_managemnt.php?p_action=uploadfileimg&txt_key=" + obj_key + "";
	}

	document.db_managemnt.submit();
}
//20230615 新增
function openNewPage(url) {
	window.open(url, '_blank');
}
/*
 * 関数名：sftpUpload
 * 関数説明：FTPでアップロードする
 * パラメタ：xmlfilename：XMLファイル
 * 戻り値：無し
 */
function sftpUpload(xmlfilename)
{
	// FTPでアップロードする
	document.db_managemnt.action = "./db_managemnt.php?p_action=sftpUpload&xmlfile=" + xmlfilename;
	document.db_managemnt.submit();
}

/*
 * 関数名：download
 * 関数説明：CSVファイルとかイメージファイルとかダウンロードする
 * パラメタ：
 * d_filename：ダウンロードファイル名；
 * d_flg:ダウンロードフラグ　「1」：CSVファイルをダウンロードする
 * 　　　　　　　　　　　　　「2」：イメージファイルをダウンロードする
 * 　　　　　　　　　　　　　「3」：削除画像のログファイルをダウンロードする
 * obj_name:オブジェクト名
 * 戻り値：無し
 */
function download(d_filename,d_flg,obj_name)
{
	if (obj_name == "csv_download4")
	{
		document.getElementById(obj_name).disabled = true;
		document.getElementById("csv_submit4").disabled = false;
	} else {
		if (obj_name != null)
		{
			document.getElementById(obj_name).disabled = true;
			var key = "csv_submit3_" + obj_name.substr(obj_name.length - 1);
			document.getElementById(key).disabled = false;
		}
	}
	//alert(key);
	// CSVファイルをダウンロードする
	if (d_flg == 1)
	{
		document.db_managemnt.action = "./db_managemnt.php?p_action=downloadfilecsv&downloadfile=" + d_filename;
	// イメージファイルをダウンロードする
	} else if (d_flg == 2) {
		document.db_managemnt.action = "./db_managemnt.php?p_action=downloadfileimg&downloadfile=" + d_filename;
	// 削除画像のログファイルをダウンロードする
	} else if (d_flg == 3) {
		document.db_managemnt.action = "./db_managemnt.php?p_action=downloadfiledellog&downloadfile=" + d_filename;
	}

	document.db_managemnt.submit();
}

/*
 * 関数名：unlock
 * 関数説明：状態の解除
 * パラメタ：
 * key1：アップロードボタン名；
 * key2:ダウンロードボタン名
 * sfilename:ファイル名
 * 戻り値：無し
 */
function unlock(key1,key2,sfilename)
{
	document.getElementById(key1).disabled = true;
	document.getElementById(key2).disabled = false;
	document.db_managemnt.action = "./db_managemnt.php?p_action=unlock&downloadfile=" + sfilename;
	document.db_managemnt.submit();
}

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
	if(obj_frame) obj_frame.style.height = 0;
	var obj_frame = top.document.getElementById('iframe_middle2');
	if(obj_frame) obj_frame.style.height = 0;
	var obj_frame = top.document.getElementById('iframe_bottom');
	if(obj_frame) obj_frame.style.height = 1490;
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
<form enctype="multipart/form-data" name="db_managemnt" action="" method="post">
<div id="zentai">
<div id="contents">
	<div class="photo_pickup">
		<h2>管理DB メニュー</h2>
		<div class="list_contents">
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">
				<tr>
					<td class="ttl_data">画像 データベース</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td class="btn dot"><label>
						<a href="./account_list.php" target="bottom"><input type="button" name="button_user" id="button_user" value="ユーザー管理" onclick="document.cookie = 'user_id=xx; expires=Tue, 1-Jan-1980 00:00:00;';parent.bottom.location.href='./account_list.php'"/></a>
						</label></td>
					<td class="dot">ユーザーの管理はここから</td>
				</tr>
                <tr>
                    <td class="btn dot">
                        <input type="button" name="button_photo_no" id="button_photo_data_download" value="写真データダウンロード" onclick="document.cookie = 'photo_no_val=xx; expires=Tue, 1-Jan-1980 00:00:00;';parent.bottom.location.href='./photo_data_download.php';"/>
                    </td>
                    <td class="dot">DS PHOTO WEBから写真データをダウンロードする</td>

                </tr>
				<tr>
					<td class="btn dot"><input type="button" name="button3" id="button3" value="削除画像予定リスト" onclick="parent.bottom.width='1060px';parent.bottom.location.href='./photo_delete_list.php'" /></td>
					<td class="dot">2が月の削除予定の画像リストの確認はここから</td>

				</tr>
				<tr>
					<td class="btn dot"><input type="button" name="button4" id="button4" value="削除された画像リスト" onclick="parent.bottom.width='1060px';parent.bottom.location.href='./photo_deleted_list.php'" /></td>
					<td class="dot">削除された画像の確認はここから</td>
				</tr>
				<tr>
					<td class="btn dot"><input type="button" name="button4" id="button4" value="DBマスター管理" /></td>
					<td class="dot">申請中の画像の登録許可はここから</td>
				</tr>
<!--				<tr>
					<td class="btn"><input type="button" name="download_dellog" id="download_dellog" value="削除ログダウンロード"  onclick='download("delete_image.log",3);'/></td>
					<td>削除した画像のログをダウンロードします</td>
				</tr>-->
				<tr>
					<td class="btn dot"><input type="button" name="button5" id="button5" value="不許可画像リスト" onclick="parent.bottom.width='1360px';parent.bottom.location.href='./photo_nopermis_list.php'" /></td>
					<td class="dot">不許可画像リストの確認はここから</td>
				</tr>
					<tr>
					<td class="btn dot"><input type="button" name="button5" id="button7" value="写真一括登録"  onclick="openNewPage('./web_uploads.php')"  /></td>
					<td class="dot">写真一括登録</td>
				</tr>
				
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">
				<tr>

					<td class="ttl_data">定型文 データベース</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td class="btn"><input type="submit" name="button5" id="button8" value="ダミーダミーダミー" /></td>
					<td><span class="dot">ダミーダミーダミーダミーダミーダミーダミーダミーダミーダミーダミーダミーダミー</span></td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">

				<tr>
					<td class="ttl_data">その他管理データ</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td><table width="100%" border="0" cellspacing="0" cellpadding="0" class="db_ttl">
							<tr>
								<td class="ttl_date">■ DS キャリアロゴ画像</td>
								<td class="date">last update：<?php echo date("Y-m-d");?></td>
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents">
							<tr>
								<td style="color:#224272">【CSV】</td>
								<td>
									<label>
										<input name="csv_file1" type="file" id="csv_file1" value="" size="30" style="width:220px;" />
										<input type="button" name="csv_submit1" id="csv_submit1" value="アップロード" onclick='form_submit("csv_file1",1);' />
									</label>
								</td>
								<td rowspan="2">
									<ul>
										<li class="bt_reflection"><a href="#" onclick='sftpUpload("i_p_hatsu_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
										<li class="bt_list_display"><a href="./i_p_hatsu_url.php" title="一覧表示">一覧表示</a></li>
										<li class="bt_release"><a href="#" title="解除">解除</a></li>
									</ul>
								</td>
								<td class="delimitation">｜</td>
								<td>
									<input type="button" name="csv_download1" id="csv_download1" onclick='download("i_p_hatsu_url.csv",1);' value="CSVダウンロード" style="width:110px;" />
								</td>
							</tr>
							<tr>
								<td style="color:#224272">【画像】</td>
								<td>
									<input name="img_file1" type="file" id="img_file1" value="" size="30" style="width:220px;" />
									<input type="button" name="img_submit1" id="img_submit1" value="アップロード" onclick='form_submit("img_file1",2);' />
								</td>
								<td style="font-size:14px; color:#003366;">｜</td>
								<td>
									<input type="button" name="img_download1" id="img_download1" onclick='download("xxxx.jpg",2);' value="画像ダウンロード" style="width:110px;" />
								</td>
							</tr>
						</table></td>
				</tr>
				<tr>
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="db_ttl">
							<tr>
								<td class="ttl_date">■ DS 問い合わせ先画像</td>
								<td class="date">last update：<?php echo date("Y-m-d");?></td>
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents">
							<tr>
								<td style="color:#224272">【CSV】</td>
								<td>
									<label>
										<input name="csv_file2" type="file" id="csv_file2" value="" size="30" style="width:220px;" />
										<input type="button" name="csv_submit2" id="csv_submit2" value="アップロード" onclick='form_submit("csv_file2",1);' />
									</label>
								</td>
								<td rowspan="2">
									<ul>
										<li class="bt_reflection"><a href="#" onclick='sftpUpload("i_p_country_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
										<li class="bt_list_display"><a href="./i_p_country_url.php" title="一覧表示">一覧表示</a></li>
										<li class="bt_release"><a href="#" title="解除">解除</a></li>
									</ul>
								</td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
									<input type="button" name="csv_download2" id="csv_download2" onclick='download("i_p_country_url.csv",1);' value="CSVダウンロード" />
								</td>
							</tr>
							<tr>
								<td style="color:#224272">【画像】</td>
								<td>
									<input name="img_file2" type="file" id="img_file2" value="" size="30" style="width:220px;" />
									<input type="button" name="img_submit2" id="img_submit2" value="アップロード" onclick='form_submit("img_file2",2);'/>
								</td>
								<td style="font-size:14px; color:#003366;">｜</td>
								<td class="csv_download">
									<input type="button" name="img_download2" id="img_download2" onclick='download("xxxx.jpg",2);' value="画像ダウンロード" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td><table width="100%" border="0" cellspacing="0" cellpadding="0" class="db_ttl">
							<tr>
								<td class="ttl_date">■ 拠点別 戻り専門店 URL</td>
								<td class="date">last update：08/11/01</td>
							</tr>
						</table>
						<p class="csv_sbttl">●海外拠点URLリスト [ i_p_hatsu_url.csv ]</p>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents dot_line">
							<tr>
								<td class="csv_cap">【CSV】</td>
								<td class="csv_contents">
									<label>
										<input name="csv_file3_1" type="file" id="csv_file3_1" value="" size="30" style="width:220px;" />
										<?php
										if (count($p_hatsu_state_ary) > 0)
										{
											if ((int)$p_hatsu_state_ary[1] == 1) {
										?>
										<input type="button" name="csv_submit3_1" id="csv_submit3_1" value="アップロード" onclick='form_submit("csv_file3_1",1);'/>
										<?php 	} elseif ((int)$p_hatsu_state_ary[1] == 0) { ?>
										<input type="button" name="csv_submit3_1" id="csv_submit3_1" value="アップロード" disabled="disabled" onclick='form_submit("csv_file3_1",1);'/>
										<?php  	}
										} else {
										?>
										<input type="button" name="csv_submit3_1" id="csv_submit3_1" value="アップロード" onclick='form_submit("csv_file3_1",1);'/>
										<?php  } ?>
									</label>
								</td>
								<td height="50">
									<ul>
										<li class="bt_reflection"><a href="#" onclick='sftpUpload("i_p_hatsu_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
										<li class="bt_list_display"><a href="./i_p_hatsu_url.php" title="一覧表示">一覧表示</a></li>
										<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
										<li class="bt_release"><a href="#" onclick='unlock("csv_submit3_1","csv_download3_1","i_p_hatsu_url.csv")' title="解除">解除</a></li>
										<?php } ?>
									</ul>
								</td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
								<?php
								if (count($p_hatsu_state_ary) > 0)
								{
									if ((int)$p_hatsu_state_ary[0] == 1) {
								?>
								<input type="button" name="csv_download3_1" id="csv_download3_1" onclick='download("i_p_hatsu_url.csv",1,"csv_download3_1");' value="CSVダウンロード" />
								<?php 	} elseif ((int)$p_hatsu_state_ary[0] == 0) { ?>
								<input type="button" name="csv_download3_1" id="csv_download3_1" disabled="disabled" onclick='download("i_p_hatsu_url.csv",1,"csv_download3_1");' value="CSVダウンロード" />
								<?php  	}
								} else {
								?>
								<input type="button" name="csv_download3_1" id="csv_download3_1" onclick='download("i_p_hatsu_url.csv",1,"csv_download3_1");' value="CSVダウンロード" />
								<?php  } ?>
								</td>
							</tr>
						</table>
						<p class="csv_sbttl">●海外拠点/専門店URLリスト [ i_hatsu_country_url.csv ]</p>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents dot_line">
							<tr>
								<td class="csv_cap">【CSV】</td>
								<td><label>
									<input name="csv_file3_2" type="file" id="csv_file3_2" value="" size="30" style="width:220px;" />
									<?php
									if (count($p_h_c_s_ary) > 0)
									{
										if ((int)$p_h_c_s_ary[1] == 1) {
									?>
									<input type="button" name="csv_submit3_2" id="csv_submit3_2" value="アップロード" onclick='form_submit("csv_file3_2",1);'/>
									<?php 	} elseif ((int)$p_h_c_s_ary[1] == 0) { ?>
									<input type="button" name="csv_submit3_2" id="csv_submit3_2" value="アップロード" disabled="disabled" onclick='form_submit("csv_file3_2",1);'/>
									<?php  	}
									} else {
									?>
									<input type="button" name="csv_submit3_2" id="csv_submit3_2" value="アップロード" onclick='form_submit("csv_file3_2",1);'/>
									<?php  } ?>
								</label></td>
								<td height="50"><ul>
									<li class="bt_reflection"><a href="#" onclick='sftpUpload("i_hatsu_country_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
									<li class="bt_list_display"><a href="./i_hatsu_country_url.php" title="一覧表示">一覧表示</a></li>
									<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
									<li class="bt_release"><a href="#" onclick='unlock("csv_submit3_2","csv_download3_2","i_hatsu_country_url.csv")' title="解除">解除</a></li>
									<?php  } ?>
								</ul></td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
								<?php
								if (count($p_h_c_s_ary) > 0)
								{
									if ((int)$p_h_c_s_ary[0] == 1) {
								?>
								<input type="button" name="csv_download3_2" id="csv_download3_2" onclick='download("i_hatsu_country_url.csv",1,"csv_download3_2");' value="CSVダウンロード" />
								<?php 	} elseif ((int)$p_h_c_s_ary[0] == 0) { ?>
								<input type="button" name="csv_download3_2" id="csv_download3_2" disabled="disabled" onclick='download("i_hatsu_country_url.csv",1,"csv_download3_2");' value="CSVダウンロード" />
								<?php  	}
								} else {
								?>
								<input type="button" name="csv_download3_2" id="csv_download3_2" onclick='download("i_hatsu_country_url.csv",1,"csv_download3_2");' value="CSVダウンロード" />
								<?php  } ?>
								</td>
							</tr>
						</table>
						<p class="csv_sbttl">●海外専門店URLリスト [ i_p_country_url.csv ]</p>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents dot_line">
							<tr>
								<td class="csv_cap">【CSV】</td>
								<td><label>
									<input name="csv_file3_3" type="file" id="csv_file3_3" value="" size="30" style="width:220px;" />
									<?php
									if (count($p_c_ary) > 0)
									{
										if ((int)$p_c_ary[1] == 1) {
									?>
									<input type="button" name="csv_submit3_3" id="csv_submit3_3" value="アップロード" onclick='form_submit("csv_file3_3",1,"csv_submit3_3");'/>
									<?php 	} elseif ((int)$p_c_ary[1] == 0) { ?>
									<input type="button" name="csv_submit3_3" id="csv_submit3_3" value="アップロード" disabled="disabled" onclick='form_submit("csv_file3_3",1,"csv_submit3_3");'/>
									<?php  	}
									} else {
									?>
									<input type="button" name="csv_submit3_3" id="csv_submit3_3" value="アップロード" onclick='form_submit("csv_file3_3",1,"csv_submit3_3");'/>
									<?php  } ?>
								</label></td>
								<td height="50"><ul>
									<li class="bt_reflection"><a href="#" onclick='sftpUpload("i_p_country_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
									<li class="bt_list_display"><a href="./i_p_country_url.php" title="一覧表示">一覧表示</a></li>
									<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
									<li class="bt_release"><a href="#" onclick='unlock("csv_submit3_3","csv_download3_3","i_p_country_url.csv")' title="解除">解除</a></li>
									<?php  } ?>
								</ul></td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
								<?php
								if (count($p_c_ary) > 0)
								{
									if ((int)$p_c_ary[0] == 1) {
								?>
								<input type="button" name="csv_download3_3" id="csv_download3_3" onclick='download("i_p_country_url.csv",1,"csv_submit3_3");' value="CSVダウンロード" />
								<?php 	} elseif ((int)$p_c_ary[0] == 0) { ?>
								<input type="button" name="csv_download3_3" id="csv_download3_3" disabled="disabled" onclick='download("i_p_country_url.csv",1,"csv_submit3_3");' value="CSVダウンロード" />
								<?php  	}
								} else {
								?>
								<input type="button" name="csv_download3_3" id="csv_download3_3" onclick='download("i_p_country_url.csv",1,"csv_submit3_3");' value="CSVダウンロード" />
								<?php  } ?>
								</td>
							</tr>
						</table>
						<p class="csv_sbttl">●国内拠点URLリスト [ d_p_hatsu_url.csv ]</p>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents dot_line">
							<tr>
								<td class="csv_cap">【CSV】</td>
								<td><label>
									<input name="csv_file3_4" type="file" id="csv_file3_4" value="" size="30" style="width:220px;" />
									<?php
									if (count($p_hatsu_state_ary_d) > 0)
									{
										if ((int)$p_hatsu_state_ary_d[1] == 1) {
									?>
									<input type="button" name="csv_submit3_4" id="csv_submit3_4" value="アップロード" onclick='form_submit("csv_file3_4",1);'/>
									<?php 	} elseif ((int)$p_hatsu_state_ary_d[1] == 0) { ?>
									<input type="button" name="csv_submit3_4" id="csv_submit3_4" value="アップロード" disabled="disabled" onclick='form_submit("csv_file3_4",1);'/>
									<?php  	}
									} else {
									?>
									<input type="button" name="csv_submit3_4" id="csv_submit3_4" value="アップロード" onclick='form_submit("csv_file3_4",1);'/>
									<?php  } ?>
								</label></td>
								<td height="50"><ul>
									<li class="bt_reflection"><a href="#" onclick='sftpUpload("d_p_hatsu_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
									<li class="bt_list_display"><a href="./d_p_hatsu_url.php" title="一覧表示">一覧表示</a></li>
									<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
									<li class="bt_release"><a href="#" onclick='unlock("csv_submit3_4","csv_download3_4","d_p_hatsu_url.csv")' title="解除">解除</a></li>
									<?php  } ?>
								</ul></td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
								<?php
								if (count($p_hatsu_state_ary_d) > 0)
								{
									if ((int)$p_hatsu_state_ary_d[0] == 1) {
								?>
								<input type="button" name="csv_download3_4" id="csv_download3_4" onclick='download("d_p_hatsu_url.csv",1,"csv_download3_4");' value="CSVダウンロード" />
								<?php 	} elseif ((int)$p_hatsu_state_ary_d[0] == 0) { ?>
								<input type="button" name="csv_download3_4" id="csv_download3_4" disabled="disabled" onclick='download("d_p_hatsu_url.csv",1,"csv_download3_4");' value="CSVダウンロード" />
								<?php  	}
								} else {
								?>
								<input type="button" name="csv_download3_4" id="csv_download3_4" onclick='download("d_p_hatsu_url.csv",1,"csv_download3_4");' value="CSVダウンロード" />
								<?php  } ?>
								</td>
							</tr>
						</table>
						<p class="csv_sbttl">●国内拠点/専門店URLリスト [ d_hatsu_prefecture_url.csv ]</p>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents dot_line">
							<tr>
								<td class="csv_cap">【CSV】</td>
								<td><label>
									<input name="csv_file3_5" type="file" id="csv_file3_5" value="" size="30" style="width:220px;" />
									<?php
									if (count($p_h_c_s_ary_d) > 0)
									{
										if ((int)$p_h_c_s_ary_d[1] == 1) {
									?>
									<input type="button" name="csv_submit3_5" id="csv_submit3_5" value="アップロード" onclick='form_submit("csv_file3_5",1);'/>
									<?php 	} elseif ((int)$p_h_c_s_ary_d[1] == 0) { ?>
									<input type="button" name="csv_submit3_5" id="csv_submit3_5" value="アップロード" disabled="disabled" onclick='form_submit("csv_file3_5",1);'/>
									<?php  	}
									} else {
									?>
									<input type="button" name="csv_submit3_5" id="csv_submit3_5" value="アップロード" onclick='form_submit("csv_file3_5",1);'/>
									<?php  } ?>
								</label></td>
								<td height="50"><ul>
									<li class="bt_reflection"><a href="#" onclick='sftpUpload("d_hatsu_prefecture_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
									<li class="bt_list_display"><a href="./d_hatsu_prefecture_url.php" title="一覧表示">一覧表示</a></li>
									<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
									<li class="bt_release"><a href="#" onclick='unlock("csv_submit3_5","csv_download3_5","d_hatsu_prefecture_url.csv")' title="解除">解除</a></li>
									<?php  } ?>
								</ul></td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
								<?php
								if (count($p_h_c_s_ary_d) > 0)
								{
									if ((int)$p_h_c_s_ary_d[0] == 1) {
								?>
								<input type="button" name="csv_download3_5" id="csv_download3_5" onclick='download("d_hatsu_prefecture_url.csv",1,"csv_download3_5");' value="CSVダウンロード" />
								<?php 	} elseif ((int)$p_h_c_s_ary_d[0] == 0) { ?>
								<input type="button" name="csv_download3_5" id="csv_download3_5" disabled="disabled" onclick='download("d_hatsu_prefecture_url.csv",1,"csv_download3_5");' value="CSVダウンロード" />
								<?php  	}
								} else {
								?>
								<input type="button" name="csv_download3_5" id="csv_download3_5" onclick='download("d_hatsu_prefecture_url.csv",1,"csv_download3_5");' value="CSVダウンロード" />
								<?php  } ?>
								</td>
							</tr>
						</table>
						<p class="csv_sbttl">●国内専門店URLリスト [ d_p_prefecture_url.csv ]</p>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents dot_line">
							<tr>
								<td class="csv_cap">【CSV】</td>
								<td><label>
									<input name="csv_file3_6" type="file" id="csv_file3_6" value="" size="30" style="width:220px;" />
									<?php
									if (count($p_c_ary_d) > 0)
									{
										if ((int)$p_c_ary_d[1] == 1) {
									?>
									<input type="button" name="csv_submit3_6" id="csv_submit3_6" value="アップロード" onclick='form_submit("csv_file3_6",1);'/>
									<?php 	} elseif ((int)$p_c_ary_d[1] == 0) { ?>
									<input type="button" name="csv_submit3_6" id="csv_submit3_6" value="アップロード" disabled="disabled" onclick='form_submit("csv_file3_6",1);'/>
									<?php  	}
									} else {
									?>
									<input type="button" name="csv_submit3_6" id="csv_submit3_6" value="アップロード" onclick='form_submit("csv_file3_6",1);'/>
									<?php  } ?>
								</label></td>
								<td height="50"><ul>
									<li class="bt_reflection"><a href="#" onclick='sftpUpload("d_p_prefecture_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
									<li class="bt_list_display"><a href="./d_p_prefecture_url.php" title="一覧表示">一覧表示</a></li>
									<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
									<li class="bt_release"><a href="#" onclick='unlock("csv_submit3_6","csv_download3_6","d_p_prefecture_url.csv")' title="解除">解除</a></li>
									<?php  } ?>
								</ul></td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
								<?php
								if (count($p_c_ary_d) > 0)
								{
									if ((int)$p_c_ary_d[0] == 1) {
								?>
								<input type="button" name="csv_download3_6" id="csv_download3_6" onclick='download("d_p_prefecture_url.csv",1,"csv_download3_6");' value="CSVダウンロード" />
								<?php 	} elseif ((int)$p_c_ary_d[0] == 0) { ?>
								<input type="button" name="csv_download3_6" id="csv_download3_6" disabled="disabled" onclick='download("d_p_prefecture_url.csv",1,"csv_download3_6");' value="CSVダウンロード" />
								<?php  	}
								} else {
								?>
								<input type="button" name="csv_download3_6" id="csv_download3_6" onclick='download("d_p_prefecture_url.csv",1,"csv_download3_6");' value="CSVダウンロード" />
								<?php  } ?>
								</td>
							</tr>
						</table>
						<p class="csv_sbttl">●航空券拠点URLリスト [ a_p_hatsu_url.csv ]</p>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents dot_line">
							<tr>
								<td class="csv_cap">【CSV】</td>
								<td><label>
									<input name="csv_file3_7" type="file" id="csv_file3_7" value="" size="30" style="width:220px;" />
									<?php
									if (count($a_p_h_ary) > 0)
									{
										if ((int)$a_p_h_ary[1] == 1) {
									?>
									<input type="button" name="csv_submit3_7" id="csv_submit3_7" value="アップロード" onclick='form_submit("csv_file3_7",1);'/>
									<?php 	} elseif ((int)$a_p_h_ary[1] == 0) { ?>
									<input type="button" name="csv_submit3_7" id="csv_submit3_7" value="アップロード" disabled="disabled" onclick='form_submit("csv_file3_7",1);'/>
									<?php  	}
									} else {
									?>
									<input type="button" name="csv_submit3_7" id="csv_submit3_7" value="アップロード" onclick='form_submit("csv_file3_7",1);'/>
									<?php  } ?>
								</label></td>
								<td height="50"><ul>
									<li class="bt_reflection"><a href="#" onclick='sftpUpload("a_p_hatsu_url.xml");' title="XMLを出力する">XMLを出力する</a></li>
									<li class="bt_list_display"><a href="./a_p_hatsu_url.php" title="一覧表示">一覧表示</a></li>
									<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
									<li class="bt_release"><a href="#" onclick='unlock("csv_submit3_7","csv_download3_7","a_p_hatsu_url.csv")' title="解除">解除</a></li>
									<?php  } ?>
								</ul></td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
								<?php
								if (count($a_p_h_ary) > 0)
								{
									if ((int)$a_p_h_ary[0] == 1) {
								?>
								<input type="button" name="csv_download3_7" id="csv_download3_7" onclick='download("a_p_hatsu_url.csv",1,"csv_download3_7");' value="CSVダウンロード" />
								<?php 	} elseif ((int)$a_p_h_ary[0] == 0) { ?>
								<input type="button" name="csv_download3_7" id="csv_download3_7" disabled="disabled" onclick='download("a_p_hatsu_url.csv",1,"csv_download3_7");' value="CSVダウンロード" />
								<?php  	}
								} else {
								?>
								<input type="button" name="csv_download3_7" id="csv_download3_7" onclick='download("a_p_hatsu_url.csv",1,"csv_download3_7");' value="CSVダウンロード" />
								<?php  } ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td><table width="100%" border="0" cellspacing="0" cellpadding="0" class="db_ttl">

							<tr>
								<td class="ttl_date">■ ブランドコードとp_hei対応表</td>
								<td class="date">last update：<?php echo date("Y-m-d");?></td>
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents">
							<tr>
								<td style="color:#224272">【CSV】</td>
								<td>
									<label>
										<input name="csv_file4" type="file" id="csv_file4" value="" size="30" style="width:220px;" />
										<?php
										if (count($p_hei_ary) > 0)
										{
											if ((int)$p_hei_ary[1] == 1) {
										?>
										<input type="button" name="csv_submit4" id="csv_submit4" value="アップロード" onclick='form_submit("csv_file4",1);' />
										<?php 	} elseif ((int)$p_hei_ary[1] == 0) { ?>
										<input type="button" name="csv_submit4" id="csv_submit4" value="アップロード" disabled="disabled" onclick='form_submit("csv_file4",1);' />
										<?php  	}
										} else {
										?>
										<input type="button" name="csv_submit4" id="csv_submit4" value="アップロード" onclick='form_submit("csv_file4",1);' />
										<?php  } ?>
									</label>
								</td>
								<td>
									<ul>
										<li class="bt_reflection"><a href="#" onclick='sftpUpload("3letter_p_hei.xml");' title="XMLを出力する">XMLを出力する</a></li>
										<li class="bt_list_display"><a href="#" title="一覧表示">一覧表示</a></li>
										<?php if (!empty($s_security_level) && (int)$s_security_level == 1) { ?>
										<li class="bt_release"><a href="#" onclick='unlock("csv_submit4","csv_download4","3letter_p_hei.csv")' title="解除">解除</a></li>
										<?php  } ?>
									</ul>
								</td>
								<td class="delimitation">｜</td>
								<td class="csv_download">
									<?php
									if (count($p_hei_ary) > 0)
									{
										if ((int)$p_hei_ary[0] == 1) {
									?>
									<input type="button" name="csv_download4" id="csv_download4" onclick='download("3letter_p_hei.csv",1,"csv_download4");' value="CSVダウンロード" />
									<?php 	} elseif ((int)$p_hei_ary[0] == 0) { ?>
									<input type="button" name="csv_download4" id="csv_download4" disabled="disabled" onclick='download("3letter_p_hei.csv",1,"csv_download4");' value="CSVダウンロード" />
									<?php  	}
									} else {
									?>
									<input type="button" name="csv_download4" id="csv_download4" onclick='download("3letter_p_hei.csv",1,"csv_download4");' value="CSVダウンロード" />
									<?php  } ?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="db_ttl">
							<tr>
								<td class="ttl_date">■ 最適化パッチ処理データ</td>
								<td class="date">last update：<?php echo date("Y-m-d");?></td>
							</tr>
						</table>
						<table border="0" cellspacing="0" cellpadding="0" class="db_contents">
							<tr>
								<td>
									<ul class="bach">
										<li class="bt_reflection"><a href="#" title="XMLを出力する">XMLを出力する</a></li>
								