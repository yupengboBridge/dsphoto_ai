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

//added by wangtongchao 2011-12-06 begin
if(isset($_GET['action']) && $_GET['action'] == "deletePhoto")
{
	$db_link = db_connect();
	if(!empty($_COOKIE['photo_id']))
	{
		$sele_data = explode(",",$_COOKIE['photo_id']);
		setcookie("photo_id","",time() - 3600);
		if(count($sele_data)>0)
		{
			$delete_img = new PhotoImageDB();
			foreach($sele_data as $val)
			{
				//
				$p_photo_mno = "";
				$p_photo_name = "";
				$photo_explanation = "";
				$bud_photo_no = "";
				$registration_person = "";
				$date_from = "";
				$date_to = "";
				$delete_img->get_photo_mno($db_link,$val,$p_photo_mno,$p_photo_name,
				                    $photo_explanation,$bud_photo_no,$registration_person,
				                    $date_from,$date_to);
				
				$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",".$p_photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$p_photo_name));
				$logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$bud_photo_no)).",";
				$logstr .= $date_from.",".$date_to.",1,".$registration_person."\r\n";
				if (!empty($logstr))
				{
					write_log_tofile($logstr);
				}
				$delete_img->delete_data($db_link,$val);
				//
			}
		}
	}
}
/*
 * 関数名：write_log_tofile
 * 関数説明：ユーザーを削除すると、削除したユーザーはログファイルに出力する
 * パラメタ：logmsg:ログ情報
 * 戻り値：無し
 */
function write_log_tofile($logmsg)
{
	// CSVファイルを出力する
	$file = fopen("./log/delete_image.log","a+");
	fwrite($file,$logmsg);
	fclose($file);
}
//added by wangtongchao 2011-12-06 end

function getcount()
{
	//deleted by wangtongchao 2011-12-06 begin
	//global $db_link;
	//deleted by wangtongchao 2011-12-06 end

	$db_link = db_connect();

	$sql = "SELECT count(*) cnt FROM photoimg";
	$sql .= " WHERE photoimg.publishing_situation_id = 1 AND photo_server_flg = 1";
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
		'fileName'  => "registration_list.php?pageID=%d&ppage=".$page_records_cnt
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
	
	$img_all->iend = $tmpend;
	//$img_all->iend = $page_records_cnt;
	
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();
		// 写真を取得
		$img_all->select_image_registration($db_link);
		// イメージ総数を取得する
		if (!empty($img_all->images))
		{
			$img_ary = $img_all->images;

			ShowPagesList();
			if((int)$tmpend > (int)$list_reg_cnt)
			{
				$img_all->iend = $list_reg_cnt;
			}
			//added by wangtongchao 2012-02-29 begin
			print "<div id=\"bg\"></div>";
			print "<div id=\"show\"></div> ";
			//added by wangtongchao 2012-02-29 end
			print "<dt class='form_ttl'>登録申請中の画像一覧 <span>（".$img_all->iend."件/".$list_reg_cnt."件中）</span></dt>\r\n";
			dispay_pagelist();
			print "<dd class=\"form_contents_indent\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"photo_album\">\r\n";
			print "	<tr>\r\n";
			//added by wangtongchao 2011-12-06 begin
			print "		<th class=\"day\"></th>\r\n";
			//added by wangtongchao 2011-12-06 end
			print "		<th class=\"day\">申請日</th>\r\n";
			print "		<th class=\"account\">申請者アカウント</th>\r\n";
			print "		<th class=\"photoname\">写真名</th>\r\n";
			print "		<th class=\"bud_photono\">バドフォト番号</th>\r\n";
			print "		<th class=\"day\">&nbsp;</th>\r\n";
			print "	</tr>\r\n";

			$ph_img_all = new PhotoImageDataAll();
			for ($i = 0 ; $i < count($img_ary); $i++)
			{
				$ph_img_all = $img_ary[$i];
				//added by wangtongchao 2011-12-06 begin
				$img_check = false;
				if(isset($_COOKIE['photo_id']))
				{
				    $sele_data = explode(",",$_COOKIE['photo_id']);
				    if(count($sele_data)>0)
				    {
				        foreach($sele_data as $val)
				        {
				            if($val == $ph_img_all->photo_id)
				            {
				                $img_check = true;
				                break;
				            }
				        }
				    }
				}
				//added by wangtongchao 2011-12-06 end
				$date_tmp = substr($ph_img_all->register_date,2,2).".".substr($ph_img_all->register_date,5,2).".".substr($ph_img_all->register_date,8,2);
				print "<tr>\r\n";
				//added by wangtongchao 2011-12-06 begin
				//changed by wangtongchao 2012-02-14 begin onclick add check_all_judgment()
				if($img_check)
				{
					print "	<td><input type='checkbox' name='photo_id[]' checked='true' value='".$ph_img_all->photo_id."' onclick=\"check_judgment(this);check_all_judgment()\" ></td>\r\n";
				}
				else 
				{
					print "	<td><input type='checkbox' name='photo_id[]' value='".$ph_img_all->photo_id."' onclick=\"check_judgment(this);check_all_judgment()\" ></td>\r\n";
				}
				//changed by wangtongchao 2012-02-14 end
				//added by wangtongchao 2011-12-06 end
				print "	<td>".$date_tmp."</td>\r\n";
				print "	<td class=\"point\">".$ph_img_all->registration_person."</td>\r\n";
				print "	<td>".$ph_img_all->photo_name."</td>\r\n";
				print "	<td>".$ph_img_all->bud_photo_no."</td>\r\n";
				print "	<td><label>\r\n";
				print "		<input type=\"button\" name=\"button\" id=\"button\" value=\"許可画面へ\" onclick='go_reg_edit(".$ph_img_all->photo_id.");'/>\r\n";
				print "	</label></td>\r\n";
				print "</tr>\r\n";
			}
			print "	</table>\r\n";
			print "</dd>\r\n";
			dispay_pagelist();
			//added by wangtognchao 2011-12-06 begin
			print "<br/><br/><div style=\"text-align:right\">";
			//added by wangtongchao 2012-02-13 begin
			print "<div style='float:left;padding:20px 50px'><input type='checkbox' name='all' onclick=\"check_all(this)\" id=\"checkAll\">全てチェック/全て外す</div>\r\n";
			//added by wangtongchao 2012-02-13 end
			print "<input type=\"button\" value=\"一括に許可する\" id=\"permitPhoto\" style=\"width:150px;height:50px\">";
			print "<input type=\"button\" value=\"一括に削除する\" id=\"deletePhoto\" style=\"width:150px;height:50px\">";
			print "</div>\r\n";
			//added by wangtongchao 2011-12-06 end
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
<title>登録申請一覧</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script src="./js/jquery.js"  type="text/javascript"  charset="utf-8"></script>
<script src="./js/kirikae.js" type="text/javascript"  charset="utf-8"></script>
<script src="./js/select.js"  type="text/javascript"  charset="utf-8"></script>
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
	//setCookie("reg_edit_url",document.location.href);
	setCookie("reg_edit_url",parent.bottom.location.href);
	//document.location.href = url;
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
	set_frameheight('iframe_bottom',1000);
	//----------フレームの設定  終了---------------
	//added by wangtongchao 2012-02-14 begin
	check_all_judgment();
	//added by wangtongchao 2012-02-14 end
}

window.onload = function()
{
	//added by wangtongchao 2011-12-06 begin
	$("#deletePhoto").click(
		function(){
			if(getCookie('photo_id').length>0)
			{
				var isSure = confirm('選択された画像を削除しますか?');
				if(isSure)
				{
					go_delete();
				}
			}else{
				alert('画像を選択してください。');
			}
		}
	);
	//added by wangtongchao 2011-12-06 end
	//added by wangtongchao 2011-12-07 begin
	$("#permitPhoto").click(
		function(){
			if(getCookie('photo_id').length>0)
			{
				var isSure = confirm('選択された画像を許可しますか?');
				if(isSure)
				{
					showdiv();
					//modified by wangtongchao 2012-02-28 begin
//					var url = "./permit_photo_batch.php";
//					window.showModalDialog(url,window,"center=yes;dialogHeight=20px;dialogWidth=300px;help=no,menubar=no,location=no,status=no,resizable=no" );
					var res = "";
					$.ajax(
					{
						async:false,
						url: "permit_photo_batch_help.php",
						success: function(msg)
						{
							res = msg;
						}
					});
					sleep(500);
					if(res != "" && res != 0)
					{
						hidediv();
						//deleted by wangtongchao 2012-02-28 begin
						//clearCookie('res',"");
						//deleted by wangtongchao 2012-02-28 end
						var isPrintLog = confirm('処理失敗のデータは'+res+'条がありますが\nlog日誌を開けて結果を調べるかどうか？');
						if(isPrintLog)
						{
							url = "./permit_photo_batch_log.php";
							//window.showModalDialog(url,window,"center=yes;dialogHeight=500px;dialogWidth=810px;help=no,menubar=yes,location=no,status=no,resizable=no" );
							window.open(url,window,"center=yes,height=500px,width=810px,help=no,menubar=yes,location=no,status=no,resizable=no" );
						}
					}
					parent.bottom.location.href="./registration_list.php";
				}
			}else{
				alert('画像を選択してください。');
			}
		}
	);
	//added by wangtongchao 2011-12-07 end
	init();
}

//added by wangtongchao 2011-12-06 begin
/*
 * 関数名：go_delete
 * 関数説明：画像編集画面へ遷移する
 * パラメタ：無し
 * 戻り値：無し
 */
function go_delete()
{
	var url = "./registration_list.php?action=deletePhoto";
	//document.location.href=url;//debug
	parent.bottom.location.href = url;
}

//added by wangtongchao 2011-12-06 end
//-->
//added by wangtongchao 2012-02-13 begin
function check_all(obj)
{
	var objs = document.getElementsByName("photo_id[]");
	var count = 0;
	if(objs)
	{
		for(i=0;i<objs.length;i++)
		{
			obj_one = objs[i];
			if(obj_one.checked != true)
			{
				count++;
			} 
		}
	}
	// クッキーを取得します。
	var idstr = getCookie('photo_id');
	// カンマ区切りの文字列を配列にします。
	var id_a = new Array();
	id_a = idstr.split(",");
	
	var obj_one = null;
	if(id_a.length+count<=30)
	{
		if(objs)
		{
			for(i=0;i<objs.length;i++)
			{
				obj_one = objs[i];
				if(obj.checked)
				{
					obj_one.checked = true;
				} else {
					obj_one.checked = false;
				}
				setCookie_CheckBox(obj_one,"photo_id");
			}
		}
	}else{
		obj.checked = false;
		alert("一括に許可できる件数を超えた。MAX：30件\r\n残り選択できる件数："+(30-id_a.length)+"件");
	}
}
//added by wangtongchao 2012-02-13 end
//added by wangtongchao 2012-02-14 begin
function check_all_judgment()
{
	var objs = document.getElementsByName("photo_id[]");
	var obj_one = null;
	var obj_all = document.getElementById("checkAll");
	obj_all.checked = true;
	if(objs)
	{
		for(i=0;i<objs.length;i++)
		{
			obj_one = objs[i];
			if(obj_one.checked != true)
			{
				obj_all.checked = false;
			}
		}
	}
}
function check_judgment(obj)
{
	if(obj.checked == false)
	{
		setCookie_CheckBox(obj,'photo_id');
	}else{
		// クッキーを取得します。
		var idstr = getCookie('photo_id');
		// カンマ区切りの文字列を配列にします。
		var id_a = new Array();
		id_a = idstr.split(",");
		
		if(id_a.length<=29)
		{
			setCookie_CheckBox(obj,'photo_id');
		}else{
			obj.checked = false;
			alert("一括に許可できる件数を超えた。MAX：30件");
		}
	}
}
//added by wangtongchao 2012-02-14 end
</script>
<!-- javascript ここまで -->
<!-- added by wangtongchao 2012-02-29 begin -->
<script language="javascript" type="text/javascript">
function sleep(numberMillis) {
	var now = new Date();
	var exitTime = now.getTime() + numberMillis;
	while (true) {
		now = new Date();
		if (now.getTime() > exitTime)
		return;
	}
} 
function showdiv() 
{
	document.getElementById("bg").style.display = "block";
	document.getElementById("show").style.display = "block";
}
function hidediv() 
{
	document.getElementById("bg").style.display = 'none';
	document.getElementById("show").style.display = 'none';
}
</script>
<style type="text/css">
#bg{ display: none;  position: absolute;  top: 0%;  left: 0%;  width: 100%;  height: 100%;  background-color: #CCCCCC;  z-index:1001;  -moz-opacity: 0.7;  opacity:.70;  filter: alpha(opacity=70);}
#show{display: none;  position: absolute;  top: 38%;  left: 40%;  width: 126px;  height: 126px;  padding: 0px;  border: 1px solid #FFFFFF;  background-color: white;  z-index:1002;  overflow: auto; background-image:url("img/wait.gif")}
</style>
<!-- added by wangtongchao 2012-02-29 end -->
</head>
<body>
<div id="zentai">
	<!-- メインコンテンツ　ここから -->
	<div id="contents">
		<div class="photo_pickup">
			<h2>登録申請一覧</h2>
			<div class="pickup_contents">
				<dl class="album_registering">
					<?php  disp_img(); ?>
				</dl>
			</div>
		</div>
	</div>
</div>
</body>
</html>
