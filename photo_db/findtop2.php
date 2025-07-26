<?php
require_once('./config.php');
require_once('./lib.php');

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

$download_flg = array_get_value($_REQUEST,'download' ,"");
if($download_flg == "download")
{
	download();
}
/*
 * 関数名：download
 * 関数説明：CSVファイルとイメージファイルをダウンロードする
 * パラメタ：無し
 * 戻り値：無し
 */
function download()
{
	try
	{
		$file = is_file("./manual/photoDB_manual_1.pdf");
		// ファイルのオープンはエラーの場合
		if (!$file)
		{
			print "<script type=\"text/javascript\">";
			print "alert(\"「./manual/photoDB_manual_1.pdf」\r\n見つかりませんでした。\");\r\n";
			print "</script>";
			return;
		} else {
			// ファイルの情報を出力する
			$file = fopen("./manual/photoDB_manual_1.pdf","r");
//			Header("Pragma:public") ;
//			Header("Expires:0");
//			Header("Cache-Control:must-revalidate,post-check=0,pre-check-0");
//			Header("Cache-Control:private",false);
			Header("Content-Type:applicateion/octet-stream");
			Header("Content-Disposition:attachment;filename=photoDB_manual_1.pdf;");
//			Header("Content-Transfer-Encoding:binary");
//			Header("Content-Length:".filesize("./manual/photoDB_manual_1.pdf"));
			// ファイルの内容を出力する
			echo fread($file,filesize("./manual/photoDB_manual_1.pdf"));
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title></title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<link rel="stylesheet" href="./css/master_b.css" type="text/css" media="all" />
<script type="text/javascript" src="./js/common.js"></script>
<script type="text/javascript">
<!--
<?php
if (!empty($s_user_id))
{
	print "var uid = " . $s_user_id . ";\r\n";
}
?>

/*
 * 関数名：go_search
 * 関数説明：イメージ検索の画面へ遷移する
 * パラメタ：無し
 * 戻り値：無し
 */
function go_search()
{
	top.middle1.location = "./search_menu.php";
	// ピックアップ用のURLを作成します。
	// クッキー識別子を作成します。
	var ck_id = "pickup_images_id_" + uid;

	// クッキーを取得します。
	var idstr = getCookie(ck_id);

	// URLを決定します。
	var url2 = "pickup_ichiran1.php?p_pickupid=" + idstr;
	top.middle2.location = url2;
	top.bottom.location = "./search_result.php?init=1";
	top.document.getElementById('iframe_middle1').style.height = "";
	//top.document.getElementById('iframe_middle1').style.display = "block";
}

/*
 * 関数名：go_registration
 * 関数説明：イメージの登録画面へ遷移する
 * パラメタ：無し
 * 戻り値：無し
 */
function go_registration()
{
	top.bottom.location = "./register_image_input.php?initflg=1";
}

function init()
{
}

window.onload = init;
//-->
</script>
</head>
<body>
<!-- findtop3 -->
<div id="zentai">
	<div id="header">
		<div>
			<h1>BUD PHOTO WEB</h1>
			<ul id="navi_menu">
				<li class="search"><a href="#" onclick='go_search();' title="画面検索" >画面検索</a></li>
<!--				<li class="application"><a href="#" onclick='go_registration();' title="画像登録申請" >画像登録申請</a></li>-->
			</ul>
			<ul id="navi">
				<!--<li class="tab_mypage"><a href="#">MYページ</a></li>-->
				<li class="tab_help"><a href="./findtop4.php?download=download" title="ヘルプ" >ヘルプ</a></li>
			</ul>
			<ul id="user">
				<li>ユーザー：<?php echo $s_login_id?></li>
				<li>所属：<?php echo $s_group_id ?></li>
				<li style="display:inline;"><a href="<?php echo $logout_page; ?>" onclick="document.location.href='<?php echo $logout_page; ?>>'">ログアウト</a></li>
			</ul>
		</div>
	</div>
</div>
</body>
</html>
