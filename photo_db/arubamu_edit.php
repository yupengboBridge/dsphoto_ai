<?php
require_once('Pager.php');
require_once('./config.php');
require_once('./lib.php');

// タイムゾーンを設定します。
date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

// セッション（$_SESSION）より情報を取得します。
$s_login_id = array_get_value($_SESSION, 's_login_id');							// ログインID
$s_login_name = array_get_value($_SESSION, 's_login_name');						// ログイン名
$s_security_level = array_get_value($_SESSION, 's_security_level');				// セキュリティーレベル
$s_user_id = array_get_value($_SESSION, 's_user_id');								// ユーザーID

// for Debug
$s_user_id = 1;
$s_login_name = "中尾　友一";
$s_login_id = "nakao";

$change_value = array_get_value($_COOKIE,"change_value");
if (empty($change_value))
{
	// 一ページ内に表示する件数
	$page_records_cnt = 30;
	// 一ページ内に表示するリンク数
	$page_links_cnt = 30;
} else {
	// 一ページ内に表示する件数
	$page_records_cnt = $change_value;
	// 一ページ内に表示するリンク数
	$page_links_cnt = $change_value;
}
// 開始のインデックス
$startcnt = 0;
// 終了のインデックス
$lastcnt = 0;
// ページング対象データの総数
$list_arubamu_cnt = 0;
// ページングのリンク
$pager_links = NULL;

// アルバムID
$albm_id = array_get_value($_REQUEST,"album_id");
// イメージIDの文字列
$photo_id_str = array_get_value($_REQUEST,"photo_id_str");
// ゼロイメージのフラグ
$imgzero = array_get_value($_REQUEST,"imgzero");
// アルバム更新フラグ
$update_flg = array_get_value($_REQUEST,"update_flg");

// アルバムを更新する
if (!empty($update_flg))
{
	if ($update_flg == 1)
	{
		updateToDB();
	}
}

/*
 * 関数名：updateToDB
 * 関数説明：DBの更新処理
 * パラメタ：無し
 * 戻り値：無し
 */
function updateToDB()
{
	global $albm_id;

	// アルバム名
	$name_value_php = array_get_value($_REQUEST,"c_name");
	// 説明文
	$content_value_php = array_get_value($_REQUEST,"content_value");

	$ck_id = "arubamu_images";
	// アルバム中のイメージIDの文字列
	$idstr = array_get_value($_COOKIE,$ck_id);

	$aru = new AlbumDetail();
	// アルバム名を入力した場合
	if (!empty($name_value_php))
	{
		$aru->set_album_name($name_value_php);
	} else {
		$aru->set_album_name("");
	}

	// 説明文を入力した場合
	if (!empty($content_value_php))
	{
		$aru->set_album_explanation($content_value_php);
	} else {
		$aru->set_album_explanation("");
	}

	// ＤＢへ接続します。
	$db_link = db_connect();
	try
	{
		// DBにアルバムを更新する
		$aru->update_data_detail($db_link,$albm_id,$idstr);
		print "<script type=\"text/javascript\">";
		print "alert(\"ＤＢを更新しました。\");";
		print "</script>";
	}
	catch(Exception $e)
	{
		// 異常の出力
		$msg[] = $e->getMessage();
		error_exit($msg);
	}
}

/*
 * 関数名：ShowPagesList
 * 関数説明：ページングの処理と出力
 * パラメタ：無し
 * 戻り値：無し
 */
function ShowPagesList()
{
	global $page_records_cnt,$page_links_cnt,$startcnt,$lastcnt,$list_arubamu_cnt,$pager_links;

	// クッキーからアルバムIDを取得する
	//$tmp_album_id = array_get_value($_COOKIE,"tmp_album_id");

	//$url_submit = "arubamu_edit.php?pageID=%d&ppage=".$page_records_cnt."&album_id=".$tmp_album_id;
	$url_submit = "arubamu_edit.php?pageID=%d&ppage=".$page_records_cnt;

	// Pagerのパラメータを設定します。
	$option = array(
		'mode'      => 'Jumping', 						// 表示タイプ(Jumping/Sliding)
		'perPage'   => $page_records_cnt,				// 一ページ内に表示する件数
		'delta'     => $page_links_cnt,					// 一ページ内に表示するリンク数
		'totalItems'=> $list_arubamu_cnt,				// ページング対象データの総数
		'separator' => ' ',								// ページリンクのセパレータ文字列
		'prevImg'   => 'BACK<<',						// 戻るリンク(imgタグ使用可)
		'nextImg'   => 'NEXT>>',						// 次へリンク(imgタグ使用可)
		'importQuery'=> FALSE,							// 自動的にPOST値をページングのHTMLタグに付与しません
		'append'=> FALSE,								// 自動でページをアペンドしません。
		'fileName'  => $url_submit
	);

	// ページングのインスタンスを生成します。
	$pager =& Pager::factory($option);

	// 表示する行数を決定します。
	// 開始行を決定します。
	$pg = $pager->getCurrentPageID();
	if ($pg <= 0)
	{
		$pg = 1;
	}

	$startcnt = ($pg - 1) * $page_records_cnt;

	// 終了行を決定します。
	$lastcnt = $startcnt + $page_records_cnt;
	if ($lastcnt >= $list_arubamu_cnt) $lastcnt = $list_arubamu_cnt;

	$pager_links = $pager->getLinks();
}

/*
 * 関数名：dispay_pagelist
 * 関数説明：ページングの処理と出力
 * パラメタ：無し
 * 戻り値：無し
 */
function dispay_pagelist()
{
	global $pager_links;

	print "<ul class=\"txt\">\r\n";
	print "<li class=\"txt_num\">\r\n";
	print($pager_links["all"]);
	print "</li>\r\n";
	print "</ul>\r\n";
	//ページングの処理---------------------------------------------------End
}

/*
 * 関数名：dispSelectValue
 * 関数説明：画面の「表示数」「Select」コントロールの設定と表示
 * パラメタ：無し
  * 戻り値：無し
 */
function dispSelectValue()
{
	global $change_value;

	if (empty($change_value))
	{
		print "<select name=\"select2\" id=\"select2\" onChange=\"select_change(this);\">\r\n";
		print "		<option value=\"30\" selected=\"selected\">30</option>\r\n";
		print "		<option value=\"60\">60</option>\r\n";
		print "		<option value=\"90\">90</option>\r\n";
		print "</select>\r\n";
	} else {
		switch ($change_value)
		{
			case 30:
				print "<select name=\"select2\" id=\"select2\" onChange=\"select_change(this);\">\r\n";
				print "		<option value=\"30\" selected=\"selected\">30</option>\r\n";
				print "		<option value=\"60\">60</option>\r\n";
				print "		<option value=\"90\">90</option>\r\n";
				print "</select>\r\n";
				break;
			case 60:
				print "<select name=\"select2\" id=\"select2\" onChange=\"select_change(this);\">\r\n";
				print "		<option value=\"30\">30</option>\r\n";
				print "		<option value=\"60\" selected=\"selected\">60</option>\r\n";
				print "		<option value=\"90\">90</option>\r\n";
				print "</select>\r\n";
				break;
			case 90:
				print "<select name=\"select2\" id=\"select2\" onChange=\"select_change(this);\">\r\n";
				print "		<option value=\"30\">30</option>\r\n";
				print "		<option value=\"60\">60</option>\r\n";
				print "		<option value=\"90\" selected=\"selected\">90</option>\r\n";
				print "</select>\r\n";
		}
	}
}

/*
 * 関数名：getSearchCount
 * 関数説明：イメージの総数を取得する
 * パラメタ：無し
 * 戻り値：無し
 */

/*
 * 関数名：getSearchCount
 * 関数説明：アイテムの数を出力
 * パラメタ：無し
  * 戻り値：無し
 */
function getSearchCount()
{
	global $list_arubamu_cnt;

	print"	<div class=\"pickup_result\">\r\n";
	if ($list_arubamu_cnt <= 0)
	{
		print"		<p>アイテムが見つかりました！</p>\r\n";
	} else {
		print"		<p>".$list_arubamu_cnt."アイテムが登録されています！</p>\r\n";
	}
	print"		<dl class=\"pickup_size\">\r\n";
	print"			<dt class=\"size_name\">サムネイルサイズ</dt>\r\n";
	print"			<dd class=\"pickup_size_bt\">\r\n";
	print"				<ul>\r\n";
	print"					<li><a href=\"#\" class=\"big\" onclick=\"change_class(200);search_resultbig();\" >大</a></li>\r\n";
	print"					<li><a href=\"#\" class=\"midlle\" onclick=\"change_class(140);search_resultmidlle();\">中</a></li>\r\n";
	print"					<li><a href=\"#\" class=\"small\" onclick=\"change_class(100);search_resultsmall();\">小</a></li>\r\n";
	print"				</ul>\r\n";
	print"			</dd>\r\n";
	print"			<dd class=\"pickup_number\"> 表示数\r\n";
	dispSelectValue();
	print"			</dd>\r\n";
	print"		</dl>\r\n";
	print"	</div>\r\n";
}

/*
 * 関数名：ShowPageHeaderFooter
 * 関数説明：ヘッダーとフッターの表示
 * パラメタ：
 * $headFooterFlag：ヘッダーとフッターの表示フラグ　１：ヘッダー表示　０：フッター表示
 * 戻り値：無し
 */
function ShowPageHeaderFooter($headFooterFlag)
{
	if ($headFooterFlag == 1)
	{
		print"	<div class=\"pickup_bt pickup_bt_top\">\r\n";
	} elseif ($headFooterFlag == 0) {
		print"	<div class=\"pickup_bt pickup_bt_bottom\">\r\n";
	}
	print"		<ul>\r\n";
	print"			<li class=\"btn\"><a href=\"#\"><img src=\"parts/bt_delete02.gif\" alt=\"チェックした画像を削除する\" onclick='deleteImage(-1,1);'/></a></li>\r\n";
	print"			<li class=\"btn\"><a href=\"#\"><img src=\"parts/bt_pickup_clear.gif\" alt=\"チェックをクリア\" onclick=\"unChecked('img_chk','arubamu_chk');\"/></a></li>\r\n";
	print"		</ul>\r\n";
	dispay_pagelist();
	print"	</div>\r\n";
}

/*
 * 関数名：disp_image_zero
 * 関数説明：選択のアルバムの情報を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_image_zero()
{
	global $albm_id;

	if (empty($albm_id)) return;

	//ＤＢへ接続します。
	$db_link = db_connect();

	$aru_find = new AlbumSearch();

	$aru_find->select_data2($db_link,$albm_id);

	if (!empty($aru_find->albums))
	{
		$arubamu_cnt = count($aru_find->albums);

		if ($arubamu_cnt > 0)
		{
			$tmp_arm_cls = new Album();
			$tmp_arm_cls = $aru_find->albums[0];
		}
	}

	print"	<div class=\"pickup_ttl pickup_ttl_top\">\r\n";
	print"		<p>検索条件： 紅葉特集の更新用</p>\r\n";
	print"		<ul class=\"change_preserved\">\r\n";
	print"			<li class=\"disc\"><a href=\"#\" onclick='updateDB();'>変更内容を保存</a></li>\r\n";
	print"			<li class=\"mail\"><a href=\"#\">このアルバムのURLをメールで送る</a></li>\r\n";
	print"		</ul>\r\n";
	print"	</div>\r\n";

	print"	<div class=\"pickup_result\">\r\n";
	print"		<p>アイテムが見つかりました！</p>\r\n";
	print"		<dl class=\"pickup_size\">\r\n";
	print"			<dt class=\"size_name\">サムネイルサイズ</dt>\r\n";
	print"			<dd class=\"pickup_size_bt\">\r\n";
	print"				<ul>\r\n";
	print"					<li><a href=\"#\" class=\"big\" >大</a></li>\r\n";
	print"					<li><a href=\"#\" class=\"midlle\" >中</a></li>\r\n";
	print"					<li><a href=\"#\" class=\"small\" >小</a></li>\r\n";
	print"				</ul>\r\n";
	print"			</dd>\r\n";
	print"			<dd class=\"pickup_number\"> 表示数\r\n";
	print "				<select name=\"select2\" id=\"select2\">\r\n";
	print "					<option value=\"30\" selected=\"selected\">30</option>\r\n";
	print "					<option value=\"60\">60</option>\r\n";
	print "					<option value=\"90\">90</option>\r\n";
	print "				</select>\r\n";
	print"			</dd>\r\n";
	print"		</dl>\r\n";
	print"	</div>\r\n";

	print"	<div class=\"pickup_bt pickup_bt_top\">\r\n";
	print"		<ul>\r\n";
	print"			<li class=\"btn\"><a href=\"#\"><img src=\"parts/bt_delete02.gif\" alt=\"チェックした画像を削除する\" /></a></li>\r\n";
	print"			<li class=\"btn\"><a href=\"#\"><img src=\"parts/bt_pickup_clear.gif\" alt=\"チェックをクリア\" /></a></li>\r\n";
	print"		</ul>\r\n";
	print"	</div>\r\n";

	$name_value_php = array_get_value($_REQUEST,"c_name");
	$content_value_php = array_get_value($_REQUEST,"content_value");

	print"<input type=\"hidden\" id=\"album_id\" name=\"album\" value=".$tmp_arm_cls->sp_album_id." />";
	print"	<dl>\r\n";
	print"		<dt class=\"form_ttl_name\">アルバム名</dt>\r\n";
	//画面のテキストボックス値を保持する
	if (!empty($name_value_php))
	{
		print"		<dd><input name=\"albm_name\" id = \"albm_id\" type=\"text\" value=".$name_value_php." size=\"30\" /></dd>\r\n";
	} else {
		print"		<dd><input name=\"albm_name\" id = \"albm_id\" type=\"text\" value=".$tmp_arm_cls->sp_album_name." size=\"30\" /></dd>\r\n";
	}
	print"	</dl>\r\n";

	print"	<dl>\r\n";
	print"		<dt class=\"form_ttl_name\">説明文</dt>\r\n";
	//画面のテキストボックス値を保持する
	if (!empty($content_value_php))
	{
		print"		<dd><textarea name=\"arubamu_content\" cols=\"85\" rows=\"4\">".$content_value_php."</textarea></dd>\r\n";
	} else {
		if(empty($tmp_arm_cls->sp_album_explanation))
		{
			print"		<dd><textarea name=\"arubamu_content\" cols=\"85\" rows=\"4\"></textarea></dd>\r\n";
		} else {
			print"		<dd><textarea name=\"arubamu_content\" cols=\"85\" rows=\"4\">".$tmp_arm_cls->sp_album_explanation."</textarea></dd>\r\n";
		}
	}

	print"	</dl>\r\n";

	print"	<BR><BR><BR><BR><BR><BR>";

	print"	<div class=\"pickup_bt pickup_bt_bottom\">\r\n";
	print"		<ul>\r\n";
	print"			<li class=\"btn\"><a href=\"#\"><img src=\"parts/bt_delete02.gif\" alt=\"チェックした画像を削除する\" /></a></li>\r\n";
	print"			<li class=\"btn\"><a href=\"#\"><img src=\"parts/bt_pickup_clear.gif\" alt=\"チェックをクリア\" /></a></li>\r\n";
	print"		</ul>\r\n";
	print"	</div>\r\n";

	print"	<div class=\"pickup_ttl pickup_ttl_top\">\r\n";
	print"		<p>検索条件： 紅葉特集の更新用</p>\r\n";
	print"		<ul class=\"change_preserved\">\r\n";
	print"			<li class=\"disc\"><a href=\"#\" onclick='updateDB();'>変更内容を保存</a></li>\r\n";
	print"			<li class=\"mail\"><a href=\"#\">このアルバムのURLをメールで送る</a></li>\r\n";
	print"		</ul>\r\n";
	print"	</div>\r\n";

	print"	<div class=\"pickup_result\">\r\n";
	print"		<p>アイテムが見つかりました！</p>\r\n";
	print"		<dl class=\"pickup_size\">\r\n";
	print"			<dt class=\"size_name\">サムネイルサイズ</dt>\r\n";
	print"			<dd class=\"pickup_size_bt\">\r\n";
	print"				<ul>\r\n";
	print"					<li><a href=\"#\" class=\"big\" >大</a></li>\r\n";
	print"					<li><a href=\"#\" class=\"midlle\" >中</a></li>\r\n";
	print"					<li><a href=\"#\" class=\"small\" >小</a></li>\r\n";
	print"				</ul>\r\n";
	print"			</dd>\r\n";
	print"			<dd class=\"pickup_number\"> 表示数\r\n";
	print "				<select name=\"select2\" id=\"select2\">\r\n";
	print "					<option value=\"30\" selected=\"selected\">30</option>\r\n";
	print "					<option value=\"60\">60</option>\r\n";
	print "					<option value=\"90\">90</option>\r\n";
	print "				</select>\r\n";
	print"			</dd>\r\n";
	print"		</dl>\r\n";
	print"	</div>\r\n";

	if ($arubamu_cnt > 0)
	{
		print "<script type=\"text/javascript\">\r\n";
		print "set_framewidth_php(30);\r\n";
		print "</script>\r\n";
	}
}

/*
 * 関数名：disp
 * 関数説明：選択のアルバムの情報とイメージの情報を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp()
{
	global $albm_id,$list_arubamu_cnt,$change_value,$photo_id_str,$imgzero;

	if (empty($albm_id)) return;

	if (!empty($imgzero))
	{
		disp_image_zero();
		return;
	}

	//ＤＢへ接続します。
	$db_link = db_connect();

	$aru_find = new AlbumSearch();

	if (empty($photo_id_str))
	{
		$aru_find->select_data2($db_link,$albm_id);
	} else {
		$aru_find->select_data3($db_link,$albm_id,$photo_id_str);
	}

	if (!empty($aru_find->albums))
	{
		$list_arubamu_cnt = count($aru_find->albums);

		if ($list_arubamu_cnt > 0)
		{
			$tmp_arm_cls = new Album();
			for ($j = 0; $j < $list_arubamu_cnt; $j++)
			{
				$tmp_arm_cls = $aru_find->albums[$j];

				print "<script type=\"text/javascript\">\r\n";
				print "setImagesResultCookie(\"".$tmp_arm_cls->sp_photo_id."\",\"arubamu_images\");\r\n";
				print "</script>\r\n";
			}
		}

		ShowPagesList();

		print"	<div class=\"pickup_ttl pickup_ttl_top\">\r\n";
		print"		<p>検索条件： 紅葉特集の更新用</p>\r\n";
		print"		<ul class=\"change_preserved\">\r\n";
		print"			<li class=\"disc\"><a href=\"#\" onclick='updateDB();'>変更内容を保存</a></li>\r\n";
		print"			<li class=\"mail\"><a href=\"#\">このアルバムのURLをメールで送る</a></li>\r\n";
		print"		</ul>\r\n";
		print"	</div>\r\n";
		getSearchCount();
		ShowPageHeaderFooter(1);

		$opt = false;
		for ($i = $GLOBALS["startcnt"] ; $i < $GLOBALS["lastcnt"] ; $i++)
		{
			$arm_cls = new Album();
			$arm_cls = $aru_find->albums[$i];

			if ($opt == false)
			{
				print"<input type=\"hidden\" id=\"album_id\" name=\"album\" value=".$arm_cls->sp_album_id." />";

				print"	<dl>\r\n";
				print"		<dt class=\"form_ttl_name\">アルバム名</dt>\r\n";
				$name_value_php = array_get_value($_REQUEST,"c_name");
				$content_value_php = array_get_value($_REQUEST,"content_value");
				//画面のテキストボックス値を保持する
				if (!empty($name_value_php))
				{
					print"		<dd><input name=\"albm_name\" id = \"albm_name\" type=\"text\" value=".$name_value_php." size=\"30\" /></dd>\r\n";
				} else {
					print"		<dd><input name=\"albm_name\" id = \"albm_name\" type=\"text\" value=".$arm_cls->sp_album_name." size=\"30\" /></dd>\r\n";
				}
				print"	</dl>\r\n";

				print"	<dl>\r\n";
				print"		<dt class=\"form_ttl_name\">説明文</dt>\r\n";
				//画面のテキストボックス値を保持する
				if (!empty($content_value_php))
				{
					print"		<dd><textarea name=\"arubamu_content\" id=\"arubamu_content\"　cols=\"85\" rows=\"4\">".$content_value_php."</textarea></dd>\r\n";
				} else {
					if(empty($arm_cls->sp_album_explanation))
					{
						print"		<dd><textarea name=\"arubamu_content\" id=\"arubamu_content\"　cols=\"85\" rows=\"4\"></textarea></dd>\r\n";
					} else {
						print"		<dd><textarea name=\"arubamu_content\" id=\"arubamu_content\"　cols=\"85\" rows=\"4\">".$arm_cls->sp_album_explanation."</textarea></dd>\r\n";
					}
				}
				print"	</dl>\r\n";

				print "<div id = \"photo_contents\" class=\"photo_contents\">\r\n";
				$opt = true;
			}

			print"		<dl class=\"photo140\">\r\n";
			if (empty($arm_cls->sp_photo_mno))
			{
				print"			<dt class=\"number\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</dt>\r\n";
			} else {
				print"			<dt class=\"number\">".santen_reader($arm_cls->sp_photo_mno,10)."</dt>\r\n";
			}
			print"			<dd><img height='100px' width='140px' src=\"".$arm_cls->sp_up_url[1]."\" alt=\"イメージ\" /></dd>\r\n";
			print"			<dd class=\"list\">\r\n";
			print"				<ul>\r\n";
			print"					<li class=\"check_box\"><input name=\"img_chk\" type=\"checkbox\" value=\"".$arm_cls->sp_photo_id."\" onclick=\"setCookie_CheckBox(this,'arubamu_chk');\"/></li>\r\n";
			print"					<li class=\"icon_bt_delete\" title=\"削除\"><a href=\"#\" onclick='deleteImage(\"".$arm_cls->sp_photo_id."\",0);'>削除</a></li>\r\n";
			print"					<li class=\"icon_bt_info\" title=\"情報\"><a href=\"#\" onclick='disp_ImageInformation(\"".$arm_cls->sp_photo_id."\");'>情報</a></li>\r\n";
			print"					<li class=\"icon_bt_copy\" title=\"コピー\"><a href=\"#\">コピー</a></li>\r\n";
			print"				</ul>\r\n";
			print"			</dd>\r\n";
			if (empty($arm_cls->sp_photo_name))
			{
				print"			<dd class=\"p_name\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</dd>\r\n";
			} else {
				print"			<dd class=\"p_name\">".$arm_cls->sp_photo_name."</dd>\r\n";
			}
			print"		</dl>\r\n";
		}
		print"	</div>\r\n";

		ShowPageHeaderFooter(0);
		print"	<div class=\"pickup_ttl pickup_ttl_top\">\r\n";
		print"		<p>検索条件： 紅葉特集の更新用</p>\r\n";
		print"		<ul class=\"change_preserved\">\r\n";
		print"			<li class=\"disc\"><a href=\"#\" onclick='updateDB();'>変更内容を保存</a></li>\r\n";
		print"			<li class=\"mail\"><a href=\"#\">このアルバムのURLをメールで送る</a></li>\r\n";
		print"		</ul>\r\n";
		print"	</div>\r\n";
		getSearchCount();

		if ($list_arubamu_cnt > 0)
		{
			print "<script type=\"text/javascript\">\r\n";
			if (empty($change_value))
			{
				print "set_framewidth_php(30);\r\n";
			} else {
				print "set_framewidth_php(".$change_value.");\r\n";
			}
			print "</script>\r\n";
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アルバム更新用</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script type="text/javascript" src="js/common.js" charset="utf-8"></script>
<script type="text/javascript" src="js/image_disp.js"  charset="utf-8"></script>
<script type="text/javascript" src="js/kirikae.js"  charset="utf-8"></script>
<script type="text/javascript" src="js/prototype.js"  charset="utf-8"></script>
<script type="text/javascript" src="js/effects.js"  charset="utf-8"></script>
<script type="text/javascript" src="js/window.js"  charset="utf-8"></script>
<script type="text/javascript">
<!--
/*
 * 関数名：updateDB
 * 関数説明：DBに更新する。
 * パラメタ：無し
 * 戻り値：無し
 */
function updateDB()
{
	// クッキーを取得します。
	var idstr = getCookie("arubamu_images");
	if (idstr == "")
	{
		var ret = confirm("このアルバムを完全に削除しますか?");
		if (ret) {
			var albm_id = document.getElementById("album_id").value;
			var url = "arubamu_list.php?album_id=" + albm_id + "&delete_edit_flg=1";
			parent.bottom.location.href = url;
		}
	} else {
		var albm_id = document.getElementById("album_id").value;
		var url = "arubamu_edit.php?album_id=" + albm_id + "&update_flg=1";

		var name_value = document.getElementById("albm_name");
		var content_value = document.getElementById("arubamu_content");

		if (name_value != null && typeof(name_value) != "undefined")
		{
			if (name_value.value == "" || name_value.value == null)
			{
				//メッセージ出力
				var msg = "アルバム名を入力してください。";
				alert(msg);
				return;
			} else {
				url = url + "&c_name=" + name_value.value;
			}
		}

		if (content_value != null && typeof(content_value) != "undefined")
		{
			if (content_value.value != "" && content_value.value != null)
			{
				url = url + "&content_value=" + content_value.value;
			}
		}
		parent.bottom.location.href = url;
	}
}

/*
 * 関数名：deleteImage
 * 関数説明：クッキーからイメージを削除する
 * パラメタ：
 * id:イメージID;
 * flg:「1」複数イメージ削除フラグ；「0」一枚イメージ削除フラグ
 * 戻り値：無し
 */
function deleteImage(id,flg)
{
	var ed_len = 0;
	var id_chk = new Array();

	if (flg == 1)
	{
		// クッキーを取得します。
		var idstr_chk = getCookie("arubamu_chk");
		if (idstr_chk != null && idstr_chk != "" && typeof(idstr_chk) != "undefined")
		{
			unChecked('img_chk','arubamu_chk');
			id_chk_ary = idstr_chk.split(",");
			ed_len = id_chk_ary.length;
		} else {
			var msg = "イメージを選択してください。";
			alert(msg);
			return;
		}
	}

	// クッキー識別子を作成します。
	var ck_id = "arubamu_images";
	// クッキーを取得します。
	var idstr = getCookie(ck_id);
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.split(",");

	if (flg == 1)
	{
		for (var i = 0; i < ed_len; i++)
		{
			var idx = check_array(id_a, id_chk_ary[i]);
			if (idx != -1) id_a[idx] = "";
		}
	} else {
		var idx = check_array(id_a, id);
		if (idx != -1) id_a[idx] = "";
	}

	// 配列を文字列に変換します。
	idstr = array_to_str(id_a);
	// クッキーを設定します。
	setCookie(ck_id, idstr);

	var name_value = document.getElementById("albm_name");
	var content_value = document.getElementById("arubamu_content");
	if (idstr == "")
	{
		var albm_id = document.getElementById("album_id").value;
		var url = "arubamu_edit.php?album_id=" + albm_id + "&imgzero=1";
		url = url + "&c_name=" + name_value.value;
		url = url + "&content_value=" + content_value.value;
		parent.bottom.location.href = url;
	} else {
		var albm_id = document.getElementById("album_id").value;
		var url = "arubamu_edit.php?album_id=" + albm_id + "&photo_id_str=" + idstr;
		url = url + "&c_name=" + name_value.value;
		url = url + "&content_value=" + content_value.value;
		parent.bottom.location.href = url;
	}
}

/*
 * 関数名：select_change
 * 関数説明：画面の「表示数」を変わる時の処理
 * パラメタ：
 * obj:画面の「Select」コントロール
 * 戻り値：無し
 */
function select_change(obj)
{
	set_framewidth_image(obj);
	var albm_id = document.getElementById("album_id").value;
	var url = "arubamu_edit.php?album_id=" + albm_id;
	parent.bottom.location.href = url;

}

/*
 * 関数名：disp_ImageInformation
 * 関数説明：イメージの詳細情報画面へ遷移する
 * パラメタ：
 * id:イメージID
 * 戻り値：無し
 */
function disp_ImageInformation(id)
{
	var albm_id = document.getElementById("album_id").value;
	var url = "./image_detail.php?p_photo_id=" + id + "&gamen_flg=4" + "&arubamu_id=" + albm_id;
	parent.bottom.location.href = url;
}

function init()
{
	var obj_frame = top.document.getElementById('iframe_bottom');
	//Firefox Browser
	if (obj_frame.contentDocument)
	{
		if (obj_frame.contentDocument.body.offsetHeight)
		{
			var frm_height = obj_frame.contentDocument.body.offsetHeight + 66;
		}
	//IExplorer Browser
	} else if (obj_frame.Document) {
		if (obj_frame.Document.body.scrollHeight)
		{
			var frm_height = obj_frame.Document.body.scrollHeight;
		}
	}
	obj_frame.height = Number(frm_height);
}

window.onload = function()
{
	init();
}
//-->
</script>
</head>
<body>
<div id="zentai">
	<div id="contents">
	<?php  disp(); ?>
	</div>
</div>
</body>
</html>