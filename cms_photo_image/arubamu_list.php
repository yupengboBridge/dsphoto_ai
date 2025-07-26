<?php
require_once('Pager.php');
require_once('./config.php');
require_once('./lib.php');

// タイムゾーンを設定します。
date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

//セッション（$_SESSION）より情報を取得します。
$s_login_id = array_get_value($_SESSION, 's_login_id');							// ログインID
$s_login_name = array_get_value($_SESSION, 's_login_name');						// ログイン名
$s_security_level = array_get_value($_SESSION, 's_security_level');				// セキュリティーレベル
$s_user_id = array_get_value($_SESSION, 's_user_id');								// ユーザーID

// for Debug
$s_user_id = 1;
$s_login_name = "中尾　友一";
$s_login_id = "nakao";

//一ページ内に表示する件数
$page_records_cnt = 20;
//一ページ内に表示するリンク数
$page_links_cnt = 20;
//開始のインデックス
$startcnt = 0;
//終了のインデックス
$lastcnt = 0;
$list_arubamu_cnt = 0;
$pager_links = NULL;

$del_flg = array_get_value($_REQUEST,"delete_flg");
if (!empty($del_flg))
{
	deleteRecord();
}

//アルバム編集画面から引き続き
$delete_edit_flg= array_get_value($_REQUEST,"delete_edit_flg");
$album_id= array_get_value($_REQUEST,"album_id");
if (!empty($delete_edit_flg) && !empty($album_id))
{
	deleteRecord_edit();
}

/*
 * 関数名：deleteRecord
 * 関数説明：選択記録を削除する
 * パラメタ：無し
 * 戻り値：無し
 */
function deleteRecord()
{
	//ＤＢへ接続します。
	$db_link = db_connect();
	$aru = new AlbumDetail();
	$album_id_str = array_get_value($_COOKIE,"ck_checkbox");
	if (!empty($album_id_str))
	{
		$aru->delete_data($db_link,$album_id_str);
	}
}

/*
 * 関数名：deleteRecord_edit
 * 関数説明：アルバムを削除する
 * パラメタ：無し
 * 戻り値：無し
 */
function deleteRecord_edit()
{
	global $album_id;

	//ＤＢへ接続します。
	$db_link = db_connect();
	$aru = new AlbumDetail();

	if (!empty($album_id))
	{
		$aru->delete_data($db_link,$album_id);
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
		'fileName'  => "arubamu_list.php?pageID=%d&ppage=".$page_records_cnt
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

	print "				<dd>\r\n";
	print "					<ul class=\"txt\">\r\n";
	print "					<li class=\"txt_num\">\r\n";
	print($pager_links["all"]);
	print "					</li>\r\n";
	print "					</ul>\r\n";
	print "				</dd>\r\n";
	//ページングの処理---------------------------------------------------End
}

/*
 * 関数名：disp
 * 関数説明：アルバムのレストを出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp()
{
	global $list_arubamu_cnt;

	//ＤＢへ接続します。
	$db_link = db_connect();

	$aru_find = new AlbumSearch();
	$aru_find->select_data($db_link);
	if (!empty($aru_find->albums))
	{
		$list_arubamu_cnt = count($aru_find->albums);
		ShowPagesList();

		for ($i = $GLOBALS["startcnt"] ; $i < $GLOBALS["lastcnt"] ; $i++)
		{
			$arm_cls = new Album();
			$arm_cls = $aru_find->albums[$i];
			$img_cnt = $aru_find->get_imgcnts($db_link,$arm_cls->sp_album_id);
			print"		<tr>\r\n";
			print"			<td class=\"radio\"><label>\r\n";
			print"				<input name=\"album_chk\" id=\"checkbox\" type=\"checkbox\" value=".$arm_cls->sp_album_id." onclick=\"setCookie_CheckBox(this,'ck_checkbox');\"/>\r\n";
			print"				</label></td>\r\n";
			print"			<td>".dp($arm_cls->sp_registration_date)."</td>\r\n";
			print"			<td class=\"point\">".dp($img_cnt)."</td>\r\n";

			if (empty($arm_cls->sp_album_name))
			{
				print"			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\r\n";
			} else {
				print"			<td>".dp(santen_reader($arm_cls->sp_album_name,23))."</td>\r\n";
			}

			if (empty($arm_cls->sp_album_explanation))
			{
				print"			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\r\n";
			} else {
				print"			<td>".dp(santen_reader($arm_cls->sp_album_explanation,31))."</td>\r\n";
			}
			print"		</tr>\r\n";
		}
	}

	print "<script type=\"text/javascript\">";
	print "set_frameheight('iframe_bottom',500);";
	print "</script>";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アルバム一覧</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
<!--
/*
 * 関数名：recordmerge
 * 関数説明：チェックしたアルバムの削除処理
 * パラメタ：無し
 * 戻り値：無し
 */
function recordmerge()
{
}

/*
 * 関数名：deleteRecord
 * 関数説明：チェックしたアルバムの削除処理
 * パラメタ：無し
 * 戻り値：無し
 */
function deleteRecord()
{
	// クッキー識別子を作成します。
	var ck_id = "ck_checkbox";
	// クッキーを取得します。
	var idstr = getCookie(ck_id);

	if (idstr != null && idstr != "")
	{
		var url = "./arubamu_list.php?delete_flg=1";
		parent.bottom.location.href = url;
	} else {
		var msg = "アルバムを選択してください。";
		alert(msg);
	}
}

/*
 * 関数名：checkBoxMutil
 * 関数説明：複数アルバムを選択するかどうかチェックする
 * パラメタ：無し
 * 戻り値：無し
 */
function checkBoxMutil()
{
	var obj = document.getElementsByName("album_chk");
	var len = obj.length;
	var cnt = 0;
	for(var i=0;i<len;i++)
	{
	   if(obj[i].checked==true) cnt = cnt + 1;
	   if (cnt >= 2) break;
	}

	if (cnt >= 2)
	{
		var msg = "複数アルバムを選択しました。編集できません。";
		alert(msg);
		return false;
	} else if (cnt == 0) {
		var msg = "アルバムを選択してください。";
		alert(msg);
		return false;
	} else {
		return true;
	}
}

/*
 * 関数名：getAlbumID
 * 関数説明：選択したアルバムのIDを取得する
 * パラメタ：無し
 * 戻り値：無し
 */
function getAlbumID()
{
	var obj = document.getElementsByName("album_chk");
	var len = obj.length;
	var retid = -1;
	for(var i=0;i<len;i++)
	{
	   if(obj[i].checked==true)
	   {
			retid = obj[i].value;
			break;
	   }
	}

	return retid;
}

/*
 * 関数名：album_select
 * 関数説明：チェックしたアルバムの各処理
 * パラメタ：無し
 * 戻り値：無し
 */
function album_select()
{
	var obj = document.getElementById("opt_album_select");

	if (obj != null)
	{
		if (obj.selectedIndex == 0)
		{
			//編集する処理
			clearCookie("ck_checkbox");
			var flg = checkBoxMutil();
			if (flg)
			{
				var ret_val = getAlbumID();
				if (ret_val >= 0 )
				{
					clearCookie("arubamu_images");
					clearCookie("tmp_album_id");
					clearCookie("arubamu_chk");
					clearCookie("change_value");
					setCookie("tmp_album_id",ret_val);

					var url = "./arubamu_edit.php?album_id=" + ret_val;
					parent.bottom.location.href = url;
				}
			}
		} else if(obj.selectedIndex == 1) {
			//結合する処理
			clearCookie("ck_checkbox");
		} else if(obj.selectedIndex == 2) {
			//削除する処理
			deleteRecord();
		} else if(obj.selectedIndex == 3) {
			//メールを送信する処理
			clearCookie("ck_checkbox");
		}
	}
}

/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
	set_frameheight('iframe_bottom',500);
	unChecked('img_chk','ck_checkbox');
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
		<div class="photo_pickup">
			<h2><?php  echo $GLOBALS["s_login_name"] ?>さんのアルバム</h2>
			<div class="pickup_contents">
				<dl class="album_registering">
					<dt class="form_ttl">現在登録中のアルバム一覧</dt>
					<dd class="form_contents clear_fix">
						<p class="bt_album_fixation_icon">チェックしたアルバムを</p>
						<p class="bt_album_fixation_txt">
						<select name="opt_album_select" id="opt_album_select">
							<option selected="selected">編集する</option>
							<option>結合する</option>
							<option>削除する</option>
							<option>メール送信する</option>
						</select></p>
						<p class="bt_album_fixation"><a href="#" onclick='album_select();'>確定</a></p>
					</dd>
					<dd class="form_contents_indent">
					<table border="0" cellspacing="0" cellpadding="0" class="photo_album">
						<tr>
							<th class="radio">&nbsp;</th>
							<th class="day">登録日</th>
							<th class="point">点数</th>
							<th class="album">アルバム名</th>
							<th class="comment">コメント</th>
						</tr>
						<?php  disp(); ?>
					</table>
					</dd>
					<?php  dispay_pagelist(); ?>
				</dl>
			</div>
		</div>
	</div>
</div>
</body>
</html>
