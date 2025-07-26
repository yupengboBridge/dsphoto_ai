<?php
########################################################################
#
# 拠点タブビーコン用(ID,URL,kyotenID,date,time)
# 
#  ◆取得したいファイルにパラメータをつけたimgタグを入れ込む(ヘッダ部分)

#  ◆値を取得し、x.hankyu(サーバ) ximage(DB) tbl_kyotentab_log(table) へ登録
#  値：
#			 URL	：
#			 kyotenID　：見た拠点の子ID
#
#  @copyright  2011 BUD International
#  @version    1.0.0
########################################################################
/********
* include
*********/
include('./log_save/db.inc.php');
require_once('./log_save/config.php');

/*********
* 処理開始
**********/
$start_time = microtime();

date_default_timezone_set("Asia/Tokyo");
echo file_get_contents("./parts/noimage.gif");

//テスト用にセット
//$_GET['fareID'] = '178043';
//$_GET['gCityCode'] = 'OSA';
//$_GET['access_time'] = '2011-03-09_10:30:53';

save_to_db();

/*******************************************************
 * save_to_db()
 * 
 * DBに書き込む
*******************************************************/
function save_to_db()
{
	//URLをセット
	$acc_url = $_GET['url'];
	//kyotenIDをセット
	$acc_kyotenID = $_GET['kyotenID'];
	//時刻をセット
	$p_date = $_GET['access_time'];

	//全て空ならリターン
	if(empty($acc_url) && empty($acc_kyotenID)){
		return false;
	}


	//時刻処理
	$p_date = str_replace("_"," ",$p_date);
	$p_date = date("Y-m-d H:i:s",strtotime($p_date));

	$now_date = date("Y-m-d H:i:s");
	$result = FALSE;

	//DB処理
	$db = new importcsv();

	$db->db_host = SERVER;
	$db->db_name = DB_NAME;
	$db->db_user = DB_USR;
	$db->db_password = DB_PSD;
	$db->db_charset = "utf8";
	$db_link = $db->db_connect();
	
	$sql = "insert into tbl_kyotentab_log(acc_url,acc_kyotenID,acc_date,create_time) values (:CURL,:CKYOTENID,:CDATE,:NDATE) ";
	
	$stmt = $db_link->prepare($sql);
	$stmt->bindValue(':CURL',$acc_url);
	$stmt->bindValue(':CKYOTENID',$acc_kyotenID);
	$stmt->bindValue(':CDATE',$p_date);
	$stmt->bindValue(':NDATE',$now_date);
	
	$result = $stmt->execute();
	
	if($result)
	{
	}
	else
	{
	}

}
?>
