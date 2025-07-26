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

$search_where = urldecode(array_get_value($_REQUEST,"search_where",""));

//ログインしているかをチェックします。
if (empty($s_login_id) || $s_security_level != 4 || $s_security_level != "4")
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
$user_all = new UserManger();

function getcount()
{
	global $db_link, $search_where;

	$db_link = db_connect();

	if(!empty($search_where)){
        $sql = "SELECT count(*) cnt FROM `user` where user_name like '%".$search_where."%' ORDER BY user_id";
    }else{
        $sql = "SELECT count(*) cnt FROM `user` ORDER BY user_id";
    }

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
	global $page_records_cnt,$page_links_cnt,$list_reg_cnt,$pager_links,$search_where;

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
		'fileName'  => "account_list.php?pageID=%d&ppage=".$page_records_cnt
	);
	if(!empty($search_where)){
        $option["fileName"] = "account_list.php?search_where=".urlencode($search_where)."&pageID=%d&ppage=".$page_records_cnt;
    }

	// ページングのインスタンスを生成します。
	$pager =& Pager::factory($option);

	// 表示する行数を決定します。
	// 開始行を決定します。
	$pg = $pager->getCurrentPageID();
	if ($pg <= 0) $pg = 1;

	$list_reg_cnt = $tmpcntitems;
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
function disp_user()
{
	global $list_reg_cnt,$user_all,$page_records_cnt,$search_where;

	$tmpcur_page_global = (int)array_get_value($_REQUEST,"pageID","0");
	if ($tmpcur_page_global == 0) $tmpcur_page_global = 1;

	$tmpend = $tmpcur_page_global * $page_records_cnt;
	$tmpstart = $tmpend - $page_records_cnt;

	$user_all->set_istart($tmpstart);
	$user_all->set_iend($tmpend);
	
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();
		// 写真を取得
		$user_all->select_user($db_link,$search_where);
		// イメージ総数を取得する
		if (!empty($user_all->users))
		{
			$users_ary = $user_all->users;

			ShowPagesList();
			if((int)$tmpend > (int)$list_reg_cnt) $user_all->set_iend($list_reg_cnt);
			
			print "<dd><ul class=\"txt2\"><li class=\"txt_num\">".($user_all->istart+1)."-".$user_all->iend."件表示（".$list_reg_cnt."件中）</li></ul></dd>\r\n";
			dispay_pagelist();
			print "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"csv_management\">\r\n";
			print "	<tr>\r\n";
			print "		<th class=\"account_ttl\">No</th>\r\n";
			print "		<th class=\"account_name\">ユーザー名</th>\r\n";
			print "		<th class=\"account_syozoku\">所属</th>\r\n";
			print "		<th class=\"account_id\">ID</th>\r\n";
			print "		<th class=\"account_pass\">パスワード</th>\r\n";
			print "		<th class=\"account_img_num\">画像番号</th>\r\n";
			print "		<th class=\"account_other\">検索</th>\r\n";
			print "		<th class=\"account_other\">登録<br />申請</th>\r\n";
			print "		<th class=\"account_other\">登録<br />許可</th>\r\n";
			print "		<th class=\"account_other\">DB<br />管理</th>\r\n";
			print "		<th class=\"account_status\">ステータス</th>\r\n";
			print "		<th class=\"account_non\">&nbsp;</th>\r\n";
			print "	</tr>\r\n";

			$usr_all = new UserManger();
			for ($i = 0 ; $i < count($users_ary); $i++)
			{
				$usr_all = $users_ary[$i];
				print "	<tr>\r\n";
				print "		<td>".$usr_all->ID."</td>\r\n";
				print "		<td>".$usr_all->user_name."</td>\r\n";
				print "		<td>".$usr_all->user_group."</td>\r\n";
				print "		<td>".$usr_all->user_login_id."</td>\r\n";
				print "		<td>".$usr_all->user_password."</td>\r\n";
				print "		<td class=\"csv_left\">".$usr_all->user_comp_code."</td>\r\n";
				$level = intval($usr_all->user_security_level);
				if($level == 4)
				{
					print "		<td>○</td>\r\n";
					print "		<td>○</td>\r\n";
					print "		<td>○</td>\r\n";
					print "		<td>○</td>\r\n";
				} elseif ($level == 3) {
					print "		<td>○</td>\r\n";
					print "		<td>○</td>\r\n";
					print "		<td>○</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
				} elseif ($level == 2) {
					print "		<td>○</td>\r\n";
					print "		<td>○</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
				} elseif ($level == 1) {
					print "		<td>○</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
				//xu add it on 20110131 start
				} elseif ($level == 5) {
					print "		<td>○</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
				//xu add it on 20110131 end
				} 	else {
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
					print "		<td>&nbsp;</td>\r\n";
				}
				
				$user_kikan = $usr_all->user_kikan;
				if($user_kikan == "sitei")
				{
					$date1 = date("Y-m-d");
					$daydiff1 = (strtotime($date1)-strtotime($usr_all->start_date))/(3600*24);
					if($daydiff1 >= 0)
					{
						$daydiff2 = (strtotime($date1)-strtotime($usr_all->end_date))/(3600*24);
						if($daydiff2 > 0)
						{
							print "		<td>停止中</td>\r\n";
						} else {
							print "		<td>使用中</td>\r\n";
						}
					} else {
						print "		<td>停止中</td>\r\n";
					}
				} elseif ($user_kikan == "mukigenn") {
					print "		<td>使用中</td>\r\n";
				}
				print "		<td class=\"account_edit\"><a href = \"#\" onclick=\"user_edit($usr_all->ID);\"> <input type=\"button\" name=\"button\" id=\"button\" value=\"編　集\" /></a></td>\r\n";
				print "	</tr>\r\n";
			}
			print "	</table>\r\n";
			
			print "<dd><ul class=\"txt2\"><li class=\"txt_num\">".($user_all->istart+1)."-".$user_all->iend."件表示（".$list_reg_cnt."件中）</li></ul></dd>\r\n";
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
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>アカウント一覧</title>
<link rel="stylesheet" href="css/base.css" type="text/css" media="all" />
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<style type="text/css">
    .user_search_bar_div{
        height: 50px;
        margin-left: 30px;
    }
    .span_hit{
        margin-right: 15px;
        font-size: 14px;
    }
    #search_user_name{
        display: inline-block;
        width: 200px;
        height: 30px;
    }
    #search_user_btn{
        width: 80px;
        padding: 5px;
    }
</style>
<script src="js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
function user_edit(sp_id)
{
	var url = "./account_edit.php?id=" + sp_id;
	setCookie("usr_edit_url",parent.bottom.location.href);
	parent.bottom.location.href = url;
}

function search_user(){
    var url = "./account_list.php";
    var search_where = document.getElementById("search_user_name");
    if(search_where){
        if(search_where.value.length>0){
            url = url + "?search_where="+encodeURIComponent(search_where.value)
        }
    }
    parent.bottom.location = url;
}
</script>
</head>
<body>
<div id="zentai">
	<div id="contents">
		<div class="photo_pickup">
			<dl class="new_account">
				<dt>新規アカウント：</dt>
				<dd>
					<p class="bt_new_account"><a href="./account_new.php">作　成</a></p>
				</dd>
			</dl>
            <div class="user_search_bar_div">
                <span class="span_hit">ユーザー名で検索</span>
                <input type="text" value="<?php echo $search_where;?>" name="search_user_name" id="search_user_name" />
                <input type="button" name="search_user_btn" id="search_user_btn" value="検索" onclick="search_user();"/>
            </div>
			<h2>作成アカウント 一覧</h2>
			<div class="list_contents">
				<?php disp_user();?>
			</div>
		</div>
	</div>
</div>
</body>
</html>
