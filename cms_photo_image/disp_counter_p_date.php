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

//// for Debug
//$s_user_id = 1;
//$s_login_name = "BUD管理者";
//$s_login_id = "admin";

// ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

//一ページ内に表示する件数
$page_records_cnt = 20;
//一ページ内に表示するリンク数
$page_links_cnt = 20;
//開始のインデックス
$startcnt = 0;
//終了のインデックス
$lastcnt = 0;
//一ページ内に表示するリンク数
$list_reg_cnt = 0;
//リンク
$pager_links = NULL;

/*
 * 関数名：ShowPagesList
 * 関数説明：ページングの処理と出力
 * パラメタ：無し
 * 戻り値：無し
 */
function ShowPagesList()
{
	global $page_records_cnt,$page_links_cnt,$startcnt,$lastcnt,$list_reg_cnt,$pager_links;

	// Pagerのパラメータを設定します。
	$option = array(
		'mode'      => 'Jumping', 						// 表示タイプ(Jumping/Sliding)
		'perPage'   => $page_records_cnt,				// 一ページ内に表示する件数
		'delta'     => $page_links_cnt,					// 一ページ内に表示するリンク数
		'totalItems'=> $list_reg_cnt,					// ページング対象データの総数
		'separator' => ' ',								// ページリンクのセパレータ文字列
		'prevImg'   => 'BACK<<',						// 戻るリンク(imgタグ使用可)
		'nextImg'   => 'NEXT>>',						// 次へリンク(imgタグ使用可)
		'importQuery'=> FALSE,							// 自動的にPOST値をページングのHTMLタグに付与しません
		'append'=> FALSE,								// 自動でページをアペンドしません。
		'fileName'  => "disp_counter_p_date.php?pageID=%d&ppage=".$page_records_cnt
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
	if ($lastcnt >= $list_reg_cnt) $lastcnt = $list_reg_cnt;

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
	print "					<ul class=\"txt2\">\r\n";
	print "					<li class=\"txt_num\">\r\n";
	print($pager_links["all"]);
	print "					</li>\r\n";
	print "					</ul>\r\n";
	print "				</dd>\r\n";
	//ページングの処理---------------------------------------------------End
}

/*
 * 関数名：showDataList
 * 関数説明：一覧データを作成する
 * パラメタ：無し
 * 戻り値：無し
 */
function showDataList()
{
	global $pager_links,$list_reg_cnt,$startcnt,$lastcnt;

	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();

		$disp = new DispCounter();

		$ok_flg = $disp->select_data2($db_link);

		if ($ok_flg == false) return;

		$tmp_ary = array();

		$tmp_ary = $disp->disp_cnt_ary;

		if (count($tmp_ary) > 0)
		{
			$list_reg_cnt = count($tmp_ary);
			ShowPagesList();
			$tmp_dip = new DispCounter();
			$date_prev = "";
			for ($i = $startcnt ; $i < $lastcnt ; $i++)
			{
				$tmp_dip = $tmp_ary[$i];
				print "<tr>\r\n";
				if ($date_prev != $tmp_dip->disp_date)
				{
					print "	<td>".dp($tmp_dip->disp_date)."</td>\r\n";
					$date_prev = $tmp_dip->disp_date;
				} else {
					print "	<td></td>\r\n";
				}
				print "	<td>".dp($tmp_dip->photo_mno)."</td>\r\n";
				print "	<td>".dp($tmp_dip->counter)."</td>\r\n";
				print "</tr>\r\n";
			}
			if (!empty($pager_links["all"]))
			{
				print "<tr>\r\n";
				print "	<td colSpan=3 style='text-align: right;'>".$pager_links["all"]."</td>\r\n";
				print "</tr>\r\n";
			}
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>画像表示回数</title>
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
			<h2>画像表示回数 一覧</h2>
			<div class="list_contents">
				<table border="0" cellspacing="0" cellpadding="0" class="disp_counter">
					<tr>
						<th>表 示 日  付</th>
						<th>画像管理番号</th>
						<th>合　　　　計</th>
					</tr>
					<?php  showDataList(); ?>
				</table>
			</div>
		</div>
	</div>
</div>
</body>
</html>