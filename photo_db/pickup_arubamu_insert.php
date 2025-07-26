<?php
require_once('./Pager.php');
require_once('./config.php');
require_once('./lib.php');

// タイムゾーンを設定します。
date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

// セッション（$_SESSION）より情報を取得します。
$s_login_id = array_get_value($_SESSION, 's_login_id');													// ログインID
$s_login_name = array_get_value($_SESSION, 's_login_name');												// ログイン名
$s_security_level = array_get_value($_SESSION, 's_security_level');										// セキュリティーレベル
$s_user_id = array_get_value($_SESSION, 's_user_id');													// ユーザーID

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

$submit_flg = array_get_value($_REQUEST,"submit_flg");
if (!empty($submit_flg))
{
	if ($submit_flg == 1)
	{
		$name_value_php = array_get_value($_REQUEST,"c_name");

		$content_value_php = array_get_value($_REQUEST,"content_value");
		$ck_id = "pickup_images_id_". $s_user_id;
		$idstr = array_get_value($_COOKIE,$ck_id);
		//ＤＢに登録する
		//ＤＢへ接続します。
		$db_link = db_connect();

		$aru = new AlbumDetail();
		if (!empty($name_value_php))
		{
			$aru->set_album_name($name_value_php);
		} else {
			$aru->set_album_name("");
		}

		if (!empty($content_value_php))
		{
			$aru->set_album_explanation($content_value_php);
		} else {
			$aru->set_album_explanation("");
		}

		try
		{
			$aru->insert_data_detail($db_link,$idstr,0);
		}
		catch(Exception $e)
		{
			$msg[] = $e->getMessage();
			error_exit($msg);
		}
	} elseif ($submit_flg == 2) {
		$p_album_id = array_get_value($_REQUEST,"album_id");

		$ck_id = "pickup_images_id_". $s_user_id;
		$idstr = array_get_value($_COOKIE,$ck_id);

		if (!empty($idstr))
		{
			//ＤＢに登録する
			//ＤＢへ接続します。
			$db_link = db_connect();

			$aru = new AlbumDetail();
			$aru->insert_image($db_link,$p_album_id,$idstr);
		}
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
		'fileName'  => "pickup_arubamu_insert.php?pageID=%d&ppage=".$page_records_cnt
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
		//for ($i = 0; $i < count($aru_find->albums); $i++)
		for ($i = $GLOBALS["startcnt"] ; $i < $GLOBALS["lastcnt"] ; $i++)
		{
			$arm_cls = new Album();
			$arm_cls = $aru_find->albums[$i];
			$img_cnt = $aru_find->get_imgcnts($db_link,$arm_cls->sp_album_id);
			print"		<tr>\r\n";
			print"			<td class=\"radio\"><input name=\"album\" type=\"radio\" value=".$arm_cls->sp_album_id." /></td>\r\n";
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
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アルバム登録</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="./js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">

/*
 * 関数名：arubamu_insert
 * 関数説明：アルバムの新規
 * パラメタ：無
  * 戻り値：無し
 */
function arubamu_insert()
{
	var url = "pickup_arubamu_insert.php?submit_flg=1";

	var name_value = document.getElementById("arubamu_id");
	var content_value = document.getElementById("arubamu_content_id");

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

/*
 * 関数名：image_add
 * 関数説明：アルバムの新規
 * パラメタ：無
  * 戻り値：無し
 */
function image_add()
{
	var objs = document.getElementsByName('album');
	var len = objs.length;
	var cnt = 0;
	var album_id = "";

	for(var i=0;i<len;i++)
	{
	   if(objs[i].checked == true)
	   {
	   		cnt = cnt + 1;
	   		album_id = objs[i].value;
	   		break;
	   }
	}

	//画像を選択しない場合
	if (cnt == 0)
	{
		//メッセージ出力
		var msg = "画像を選択してください。";
		alert(msg);
	} else {
		var url = "./pickup_arubamu_insert.php?submit_flg=2&album_id=" + album_id;
		parent.bottom.location.href = url;
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
}

window.onload = function()
{
	init();
}
</script>
<!-- javascript ここまで -->
</head>
<body>
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<h2>アルバム登録</h2>
			<div class="pickup_contents">
				<dl class="form_contents_new_registration">
					<dt class="form_ttl">
						現在のピックアップを新規（アルバム）登録する</dt>
					<dd class="form_contents_indent">
						<dl>
							<dt class="form_ttl_name">アルバム名<em>（必須）</em></dt>
							<dd><input name="arubamu_name" type="text" id="arubamu_id" size="30" /></dd>
						</dl>
					</dd>
					<dd class="form_contents_indent">
						<dl>
							<dt class="form_ttl_name">説明文</dt>
							<dd><textarea name="arubamu_content" id="arubamu_content_id" cols="85" rows="4"></textarea>
							</dd>
						</dl>
					</dd>
					<dd class="form_contents_indent">
					<p class="bt_new_registration">
					<a href="#" onclick="arubamu_insert();">新規登録</a>
					</p>
					</dd>
				</dl>
				<dl class="image_addition">
					<dt class="form_ttl">
						登録済みのアルバムに追加する　</dt>
					<dd class="form_contents_indent">
						<p class="bt_image_addition"><a href="#" onclick='image_add();'>画像追加</a></p><p class="bt_image_addition_txt">追加するアルバムを選んでクリック</p>
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
