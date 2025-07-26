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

// ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out("./tools_login.php");
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>写真DB　ツール</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<script type="text/javascript" src="js/common.js"  charset="utf-8"></script>
<script type="text/javascript">
<!--
/*
 * 関数名：enabled_split
 * 関数説明：状態を変わる
 * パラメタ：無し
 * 戻り値：無し
 */
function enabled_split()
{
	var txtobj = document.getElementById('p_filename');
	if (txtobj) txtobj.disabled = false;
	var p_line_no = document.getElementById("p_s_line_no");
	if (p_line_no) p_line_no.disabled = false;
	var p_file_cnt = document.getElementById("p_file_cnt");
	if (p_file_cnt) p_file_cnt.disabled = false;
	var btn = document.getElementById("split_exec");
	if (btn) btn.disabled = false;

	txtobj = document.getElementById('csv_insert_file');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("batch_insert");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('csv_del_file');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("batch_del");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('p_mno');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("btn_show");
	if (btn) btn.disabled = true;
}

/*
 * 関数名：enabled_login
 * 関数説明：状態を変わる
 * パラメタ：無し
 * 戻り値：無し
 */
function enabled_login()
{
	var txtobj = document.getElementById('p_filename');
	if (txtobj) txtobj.disabled = true;
	var p_line_no = document.getElementById("p_s_line_no");
	if (p_line_no) p_line_no.disabled = true;
	var p_file_cnt = document.getElementById("p_file_cnt");
	if (p_file_cnt) p_file_cnt.disabled = true;
	var btn = document.getElementById("split_exec");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('csv_insert_file');
	if (txtobj) txtobj.disabled = false;
	btn = document.getElementById("batch_insert");
	if (btn) btn.disabled = false;

	txtobj = document.getElementById('csv_del_file');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("batch_del");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('p_mno');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("btn_show");
	if (btn) btn.disabled = true;
}

/*
 * 関数名：enabled_del
 * 関数説明：状態を変わる
 * パラメタ：無し
 * 戻り値：無し
 */
function enabled_del()
{
	var txtobj = document.getElementById('p_filename');
	if (txtobj) txtobj.disabled = true;
	var p_line_no = document.getElementById("p_s_line_no");
	if (p_line_no) p_line_no.disabled = true;
	var p_file_cnt = document.getElementById("p_file_cnt");
	if (p_file_cnt) p_file_cnt.disabled = true;
	var btn = document.getElementById("split_exec");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('csv_insert_file');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("batch_insert");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('csv_del_file');
	if (txtobj) txtobj.disabled = false;
	btn = document.getElementById("batch_del");
	if (btn) btn.disabled = false;

	txtobj = document.getElementById('p_mno');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("btn_show");
	if (btn) btn.disabled = true;
}

/*
 * 関数名：enabled_show
 * 関数説明：状態を変わる
 * パラメタ：無し
 * 戻り値：無し
 */
function enabled_show()
{
	var txtobj = document.getElementById('p_filename');
	if (txtobj) txtobj.disabled = true;
	var p_line_no = document.getElementById("p_s_line_no");
	if (p_line_no) p_line_no.disabled = true;
	var p_file_cnt = document.getElementById("p_file_cnt");
	if (p_file_cnt) p_file_cnt.disabled = true;
	var btn = document.getElementById("split_exec");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('csv_insert_file');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("batch_insert");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('csv_del_file');
	if (txtobj) txtobj.disabled = true;
	btn = document.getElementById("batch_del");
	if (btn) btn.disabled = true;

	txtobj = document.getElementById('p_mno');
	if (txtobj) txtobj.disabled = false;
	btn = document.getElementById("btn_show");
	if (btn) btn.disabled = false;
}

/*
 * 関数名：submit_split
 * 関数説明：CSVファイルを分ける時のチェック
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_split()
{
	var txtobj = document.getElementById('p_filename');
	if (txtobj)
	{
		var txtvalue = txtobj.value;
		if (txtvalue == null)
		{
			alert('ファイル名を入力してください。');
			txtobj.focus();
			return;
		} else if (txtvalue.length <= 0) {
			alert('ファイル名を入力してください。');
			txtobj.focus();
			return;
		}
	}

	var p_line_no = document.getElementById("p_s_line_no").value;
	var result1 = p_line_no.match(/[^0-9]/);
	if (!p_line_no || result1)
	{
		alert("開始行目は0～9の数字だけ入力してください");
		document.getElementById("p_s_line_no").focus();
		return;
	}

	var p_file_cnt = document.getElementById("p_file_cnt").value;
	var result = p_file_cnt.match(/[^0-9]/);
	if (!p_file_cnt || result)
	{
		alert("ファイルの行数は1～9の数字だけ入力してください");
		document.getElementById("p_file_cnt").focus();
		return;
	}

	var i_tmp = parseInt(p_file_cnt);
	if (i_tmp > 150)
	{
		alert("ファイルの行数は150以降の数字だけ入力してください");
		document.getElementById("p_file_cnt").focus();
		return;
	}

	parent.window.scrollTo(0,900);

	top.bottom.location = "./batch_file_split.php?p_filename=" + txtvalue + "&start_line_no="+p_line_no + "&group_cnt=" + p_file_cnt;
}

/*
 * 関数名：submit_insert
 * 関数説明：CSVで画像を登録する時のチェック
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_insert()
{
	var txtobj = document.getElementById('csv_insert_file');
	if (txtobj)
	{
		var txtvalue = txtobj.value;
		if (txtvalue == null)
		{
			alert('ファイル名を入力してください。');
			return;
		} else if (txtvalue.length <= 0) {
			alert('ファイル名を入力してください。');
			return;
		}
		if (txtvalue.length > 0)
		{
			var tmpval = "__" + txtvalue.toLowerCase();
			var ipos = tmpval.indexOf("limi");
			if (ipos > 0)
			{
				parent.window.scrollTo(0,900);
				top.bottom.location = "./login_image_batch_limi.php?p_filename=" + txtvalue;
				return;
			} else {
				var ipos = tmpval.indexOf("ejpl");
				if (ipos > 0)
				{
					parent.window.scrollTo(0,900);
					top.bottom.location = "./login_image_batch_ejpl.php?p_filename=" + txtvalue;
					return;
				} else {
					ipos = tmpval.indexOf("ejdi");
					if (ipos > 0)
					{
						parent.window.scrollTo(0,900);
						top.bottom.location = "./login_image_batch_ejdi.php?p_filename=" + txtvalue;
						return;
					} else {
						ipos = tmpval.indexOf("photoweb");
						if (ipos > 0)
						{
							parent.window.scrollTo(0,900);
							top.bottom.location = "./login_image_batch_webpd.php?p_filename=" + txtvalue;
							return;
						}
					}
				}
			}
		}
	}
}

/*
 * 関数名：submit_delete
 * 関数説明：CSVで画像を削除する時のチェック
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_delete()
{
	var txtobj = document.getElementById('csv_del_file');
	if (txtobj)
	{
		var txtvalue = txtobj.value;
		if (txtvalue == null)
		{
			alert('ファイル名を入力してください。');
			return;
		} else if (txtvalue.length <= 0) {
			alert('ファイル名を入力してください。');
			return;
		}
		if (txtvalue.length > 0)
		{
			parent.window.scrollTo(0,900);
			top.bottom.location = "./delete_image_batch.php?p_filename=" + txtvalue;
			return;
		}
	}
}

/*
 * 関数名：submit_imgshow
 * 関数説明：期間より画像を表示する
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_imgshow()
{
	var txtobj = document.getElementById('p_mno');
	if (txtobj)
	{
		var txtvalue = txtobj.value;
		if (txtvalue == null)
		{
			alert('画像管理番号を入力してください。');
			return;
		} else if (txtvalue.length <= 0) {
			alert('画像管理番号を入力してください。');
			return;
		}
		if (txtvalue.length > 0)
		{
			parent.window.scrollTo(0,900);
			top.bottom.location = "./image_search_kikan_test.php?p_photo_mno=" + txtvalue;
			return;
		}
	}
}

/*
 * 関数名：submit_imgkyoka
 * 関数説明：画像のバッチ許可
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_imgkyoka()
{
	parent.window.scrollTo(0,900);
	top.bottom.location = "./make_image_bainari.php";
}

/*
 * 関数名：submit_damidata
 * 関数説明：画像のファイルのパスを修正する
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_damidata()
{
	parent.window.scrollTo(0,900);
	top.bottom.location = "./dami_data_update.php";
}

/*
 * 関数名：submit_logshow
 * 関数説明：ログファイルを表示する
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_logshow()
{
	parent.window.scrollTo(0,900);
	top.bottom.location = "./kyoka_log_show.php";
}

/*
 * 関数名：submit_logdownload
 * 関数説明：ログファイルのダウンロード
 * パラメタ：無し
 * 戻り値：無し
 */
function submit_logdownload()
{
	top.bottom.location = "./tools_download.php?p_action=downloadfilelog&downloadfile=bainari_image.log";
}

/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
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
		<h2>写真DB　ツール メニュー</h2>
		<div class="list_contents">
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">
				<tr>
					<td class="ttl_data">写真DB　ツール</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td class="btn dot"><label>
						<input type="button" name="button" id="button" value="CSVファイル分割" onclick="enabled_split();"/>
						</label></td>
					<td class="dot">CSVファイルの分割</td>
				</tr>
				<tr>
					<td class="btn dot"><input type="button" name="button2" id="button2" value="画像一括登録"  onclick="enabled_login();"/></td>
					<td class="dot">CSVでの画像一括登録はここから</td>
				</tr>
				<tr>
					<td class="btn dot"><input type="button" name="button3" id="button3" value="画像一括削除"  onclick="enabled_del();"/></td>
					<td class="dot">CSVでの画像一括削除はここから</td>
				</tr>
				<tr>
					<td class="btn dot"><input type="button" name="button4" id="button4" value="画像表示" onclick="enabled_show();"/></td>
					<td class="dot">期間より画像の表示</td>
				</tr>
				<tr>
					<td class="btn dot"><input type="button" name="button5" id="button5" value="画像許可" onclick="submit_imgkyoka();"/></td>
					<td class="btn dot"><input type="button" name="btn_log" id="btn_log" value="許可ログ表示" onclick="submit_logshow();"/></td>
					<td class="btn dot"><input type="button" name="btn_log" id="btn_log" value="許可ログダウンロード" onclick="submit_logdownload();"/></td>
					<td class="dot">画像のバッチ許可</td>
				</tr>
				<tr>
					<td class="btn dot"><input type="button" name="button5" id="button5" value="ダミーデータ作成" onclick="submit_damidata();"/></td>
					<td class="dot">画像のダミーデータを作成</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">
				<tr>
					<td class="ttl_data">CSVファイルの分割</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td width="100%">
						CSVファイル名：	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input name="p_filename" type="text" id="p_filename" value="" size="30" style="width:180px;"  disabled="disabled"/>
						<br/><br/>
						開始行目(0から)：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="text" id="p_s_line_no" value=""  disabled="disabled"/>
						<br/><br/>
						ファイルの行数(1-150)：<input type="text" id="p_file_cnt" value=""  disabled="disabled"/>
						<br/><br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<input type="button" name="split_exec" id="split_exec" title="CSVファイルを分ける" alt="CSVファイルを分ける" value="実　　　行"  onclick="submit_split();" disabled="disabled"/>
					</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">
				<tr>
					<td class="ttl_data">CSVで画像の登録</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td width="100%">
						CSVファイル名：	<input name="csv_insert_file" type="text" id="csv_insert_file" value="" size="30" style="width:220px;"  disabled="disabled"/>
						<input type="button" name="batch_insert" id="batch_insert" title="CSVで画像を登録する" alt="CSVで画像を登録する" value="実　　　行"  onclick="submit_insert();" disabled="disabled"/>
					</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">
				<tr>
					<td class="ttl_data">CSVで画像の削除</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td width="100%">
						CSVファイル名：	<input name="csv_del_file" type="text" id="csv_del_file" value="" size="30" style="width:220px;"  disabled="disabled"/>
						<input type="button" name="batch_del" id="batch_del" title="CSVで画像をDBから削除する" alt="CSVで画像をDBから削除する" value="実　　　行"  onclick="submit_delete();" disabled="disabled"/>
					</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management ttl_other_data">
				<tr>
					<td class="ttl_data">期間より画像を表示する</td>
				</tr>
			</table>
			<table width="800" border="0" cellspacing="0" cellpadding="0" class="db_management">
				<tr>
					<td width="100%">
						画像管理番号：	<input name="p_mno" type="text" id="p_mno" value="" size="30" style="width:220px;"  disabled="disabled"/>
						<input type="button" name="btn_show" id="btn_show" title="期間より画像を表示する" alt="期間より画像を表示する" value="実　　　行"  onclick="submit_imgshow();" disabled="disabled"/>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
</div>
</body>
</html>