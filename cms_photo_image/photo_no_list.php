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

//ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}
//print_r($_GET);
//exit;
if(isset($_POST['action'])&&$_POST['action']=="csv_export")
{
	// ＤＢへ接続します。
	$db_link = db_connect();
	$img_data = new ImageSearch();
	// 写真を取得
	$sel_data = $_COOKIE['photo_no_val'];
	$img_data->select_image_csv($db_link,$sel_data);
//	print_r($img_data->images);
//	exit;
	export_csv($img_data->images);
	exit;
}

//一ページ内に表示する件数
$page_records_cnt = 20;
//一ページ内に表示するリンク数
$page_links_cnt = 20;
//一ページ内に表示するリンク数
$list_reg_cnt = 0;
//リンク
$pager_links = NULL;

// イメージ検索のクラス
$img_all = new ImageSearch();

function getcount()
{
	global $db_link;

	$db_link = db_connect();

	$sql = "SELECT count(*) cnt FROM photoimg";
	$sql .= " WHERE photoimg.publishing_situation_id = 2";
	$sql .= " ORDER BY photo_id ";

	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();

	if ($result == true)
	{
		// 最終番号を取得します。
		$max = $stmt->fetch(PDO::FETCH_ASSOC);
		return $max['cnt'];
	} else {
		return 0;
	}
}
	function i($strInput)
	{
		return iconv('utf-8','shift-jis',$strInput);
	}

	/**
		*导出数据转换
		* @param $result
		*/
	function array_to_string($result)
	{
		if(empty($result)){
			return i("データ無し");
		}
		
		$data=i('ID,申請日 ,申請アカウント,写真管理番号,写真名,バドフォト番号');
		$data .="\n";
		
		foreach($result as $val) {
			$data .=i($val->photo_id).",";
			$date = new DateTime($val->register_date);
			$data .=i($date->format('Y-m-d')).",";
			$data .=i($val->registration_person).",";
			$data .=i($val->photo_mno).",";
			$data .=i($val->photo_name).",";
			$data .=i($val->bud_photo_no).",";
			$data .="\n";
		}
		return $data;
	}
	
	function export_csv($data)
	{
		$filename = date('YmdHis').".csv";
		header("Content-type:text/csv");
		header("Content-Disposition:attachment;filename=".$filename);
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo array_to_string($data);
	}
/*
 * 関数名：ShowPagesList
 * 関数説明：ページングの処理と出力
 * パラメタ：無し
 * 戻り値：無し
 */
function ShowPagesList()
{
	global $page_records_cnt,$page_links_cnt,$list_reg_cnt,$pager_links;
	//global $startcnt,$lastcnt;

	$tmpcntitems = getcount();

	// Pagerのパラメータを設定します。
	$option = array(
		'mode'      => 'Jumping', 						// 表示タイプ(Jumping/Sliding)
		'perPage'   => $page_records_cnt,				// 一ページ内に表示する件数
		'delta'     => $page_links_cnt,					// 一ページ内に表示するリンク数
		'totalItems'=> $tmpcntitems,					// ページング対象データの総数
		'separator' => ' ',								// ページリンクのセパレータ文字列
		'prevImg'   => 'BACK<<',						// 戻るリンク(imgタグ使用可)
		'nextImg'   => 'NEXT>>',						// 次へリンク(imgタグ使用可)
		'importQuery'=> FALSE,							// 自動的にPOST値をページングのHTMLタグに付与しません
		'append'=> FALSE,								// 自動でページをアペンドしません。
		'fileName'  => "photo_no_list.php?pageID=%d&ppage=".$page_records_cnt
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

	$list_reg_cnt = $tmpcntitems;
//	$startcnt = ($pg - 1) * $page_records_cnt;
//
//	// 終了行を決定します。
//	$lastcnt = $startcnt + $page_records_cnt;
//	if ($lastcnt >= $list_reg_cnt) $lastcnt = $list_reg_cnt;

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
 * 関数名：disp_img
 * 関数説明：画面の表示
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_img()
{
	global $list_reg_cnt,$img_all,$page_records_cnt;
	//global $startcnt,$lastcnt;

	$tmpcur_page_global = (int)array_get_value($_REQUEST,"pageID","0");
	if ($tmpcur_page_global == 0)
	{
		$tmpcur_page_global = 1;
	}

	$tmpend = $tmpcur_page_global * $page_records_cnt;

	$tmpstart = $tmpend - $page_records_cnt;

	$img_all->istart = $tmpstart;
	$img_all->per_page = $page_records_cnt;
	$img_all->iend = $tmpend;
	
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();
		// 写真を取得
		$img_all->select_image_registed($db_link);
		// イメージ総数を取得する
		if (!empty($img_all->images))
		{
			$img_ary = $img_all->images;
			
			ShowPagesList();
			if((int)$tmpend > (int)$list_reg_cnt)
			{
				$img_all->iend = $list_reg_cnt;
			}
			print "<dt class='form_ttl'>画像一覧<span>（".$img_all->iend."件/".$list_reg_cnt."件中）</span></dt>\r\n";
			dispay_pagelist();
			print "<dd class=\"form_contents_indent\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"photo_album\">\r\n";
			print "	<tr>\r\n";
			print "		<th ></th>\r\n";
			print "		<th class=\"day\">申請日</th>\r\n";
			print "		<th class=\"account\">申請者アカウント</th>\r\n";
			print "		<th >写真管理番号</th>\r\n";
			print "		<th class=\"photoname\">写真名</th>\r\n";
			print "		<th class=\"bud_photono\">バドフォト番号</th>\r\n";
			print "	</tr>\r\n";

			$ph_img_all = new PhotoImageDataAll();
			for ($i = 0 ; $i < count($img_ary); $i++)
			{
				$ph_img_all = $img_ary[$i];
				$img_check = false;
				$sele_data = split(",",$_COOKIE['photo_no_val']);
				if(count($sele_data)>0)
				{
					foreach($sele_data as $val)
					{
						if($val == $ph_img_all->photo_id)
						{
							$img_check = true;
						}
					}
				}
				$date_tmp = substr($ph_img_all->register_date,2,2).".".substr($ph_img_all->register_date,5,2).".".substr($ph_img_all->register_date,8,2);
				print "<tr>\r\n";
				if($img_check)
				{
					print "	<td><input type='checkbox' name='photo_id[]' checked='true' value='".$ph_img_all->photo_id."' onclick=\"setCookie_CheckBox(this,'photo_no_val')\" ></td>\r\n";
				}
				else 
				{
					print "	<td><input type='checkbox' name='photo_id[]' value='".$ph_img_all->photo_id."' onclick=\"setCookie_CheckBox(this,'photo_no_val')\" ></td>\r\n";
				}
				print "	<td>".$date_tmp."</td>\r\n";
				print "	<td class=\"point\">".$ph_img_all->registration_person."</td>\r\n";
				print "	<td>".$ph_img_all->photo_mno."</td>\r\n";
				print "	<td>".$ph_img_all->photo_name."</td>\r\n";
				print "	<td>".$ph_img_all->bud_photo_no."</td>\r\n";
				print "</tr>\r\n";
			}
			print "	</table>\r\n";
			print "</dd>\r\n";
			dispay_pagelist();
		}
	}
	catch(Exception $e)
	{
		$msg[] = $e->getMessage();
		error_exit($msg);
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>写真番号リスト作成</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="./js/jquery.js"  type="text/javascript"  charset="utf-8"></script>
<script src="./js/common.js"  type="text/javascript"  charset="utf-8"></script>
<script type="text/javascript">
<!--
/*
 * 関数名：go_reg_edit
 * 関数説明：画像編集画面へ遷移する
 * パラメタ：無し
 * 戻り値：無し
 */
function go_reg_edit(sp_id)
{
	var url = "./register_image_edit.php?p_photo_id=" + sp_id;
	setCookie("reg_edit_url",parent.bottom.location.href);
	parent.bottom.location.href = url;
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
	set_frameheight('iframe_bottom',500);
	//----------フレームの設定  終了---------------
}

window.onload = function()
{
	init();
}
//-->
</script>
<!-- javascript ここまで -->
</head>
<body>
<form name="csv_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
<input type="hidden" name="action" value="csv_export"/>
<div id="zentai">
	<!-- メインコンテンツ　ここから -->
	<div id="contents">
		<div class="photo_pickup">
			<h2>写真番号リスト作成</h2>
			<div class="pickup_contents">
				<dl class="album_registering">
					<?php  disp_img(); ?>
				</dl>
				<br/><br/><br/>
				<div align="right">
					<input type="submit" name="csv_export" value="写真番号リスト作成"/>
				</div>
			</div>
		</div>
	</div>
</div>
</form>
</body>
</html>
