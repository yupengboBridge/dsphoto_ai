<?php
########################################################################
#
# トップ系出発地選択ビーコン用(ID,page,date,kyotenID,URL,status)
# 
#  ◆取得したいファイルにパラメータをつけ、returnでimgタグを入れ込む(開いた時:ajax_select_kyoten_top.php)
#  ◆ボタンを押した時は直接このファイルをキックしに来る
#  ◆値を取得し、x.hankyu(サーバ) ximage(DB) tbl_deptop_acc_rog(table) へ登録
#  値：acc_page　：アクセスされたページ
#			 acc_date　：アクセスタイム
#			 acc_select　：選択した拠点ID
#			 acc_url	：アクセスされたページをトラベルコムの綺麗なURLにしたもの（パラメータとか排除）
#			 acc_status	：動作フラグ 1：ページが開いた時 2：設定ボタンが押された時 3：閉じるを押した時
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
//$start_time = microtime();

date_default_timezone_set("Asia/Tokyo");

//画像を返すのは開いた時のアクションだけ
if(!isset($_GET['No_img'])){
	echo file_get_contents("./parts/noimage.gif");
}
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
$acc_url ='';
$acc_page ='';
$acc_SetKyotenVal ='';
$acc_flg ='';
$string = '';
	//urlとページをセット
	if(!empty($_GET['url']) && !empty($_GET['AC_flg'])){
		if(strpos($_GET['url'],'www.hankyu-travel.com/') !== false){
	
				if(strpos($_GET['url'],'/kaigai/') !== false){
					$acc_page = 'kaigai';
					$acc_url = 'http://www.hankyu-travel.com/kaigai/';
				}
				elseif(strpos($_GET['url'],'/kokunai/') !== false){
					$acc_page = 'kokunai';
					$acc_url = 'http://www.hankyu-travel.com/kokunai/';
				}
				elseif(strpos($_GET['url'],'/air/') !== false){
					$acc_page = 'air';
					$acc_url = 'http://www.hankyu-travel.com/air/';
				}
				else{
					$acc_page = 'top';
					$acc_url = 'http://www.hankyu-travel.com/';
				}
				
				
				//アクセスフラグをセット
				if(!empty($_GET['AC_flg'])){
					$acc_flg = $_GET['AC_flg'];
					if($acc_flg == '2'){
						//kyotenIDをセット
						if(!empty($_GET['SetKyotenVal'])){
							$acc_SetKyotenVal = $_GET['SetKyotenVal'];
							if(strpos($acc_SetKyotenVal,'-') !== false){
								$SetKyotenVal = strstr($acc_SetKyotenVal, '-');
								$acc_SetKyotenVal = str_replace('-','',$SetKyotenVal);
							}
						}
						else{
							return false;
						}	
					}
				}
		
				//時刻をセット
				$p_date = date("Y-m-d_H:i:s");		
		}
	}

	//全て空ならリターン
	if(empty($acc_url) && empty($acc_flg) && empty($acc_page)){
		return false;
	}

/*$string = $acc_url.",".$p_date.",".$acc_page.",".$acc_SetKyotenVal.",".$acc_flg."\n";
$filepath = "write_test.txt"; // ファイルへのパスを変数に格納	
$fp = fopen($filepath, "a"); // 新規書き込みモードで開く
@fwrite( $fp, $string, strlen($string) ); // ファイルへの書き込み
fclose($fp);
*/

	$result = FALSE;

	//DB処理
	$db = new importcsv();

	$db->db_host = SERVER;
	$db->db_name = DB_NAME;
	$db->db_user = DB_USR;
	$db->db_password = DB_PSD;
	$db->db_charset = "utf8";
	$db_link = $db->db_connect();
	
	//$sql = "insert into tbl_kyotentab_log(acc_url,acc_kyotenID,acc_date,create_time) values (:CURL,:CKYOTENID,:CDATE,:NDATE) ";
	$sql = "insert into tbl_deptop_acc_rog(acc_page,acc_date,acc_select,acc_url,acc_status) values (:CPAGE,:CDATE,:CKYOTENID,:CURL,:CFLG) ";
	
	$stmt = $db_link->prepare($sql);
	$stmt->bindValue(':CPAGE',$acc_page);
	$stmt->bindValue(':CDATE',$p_date);
	$stmt->bindValue(':CKYOTENID',$acc_SetKyotenVal);
	$stmt->bindValue(':CURL',$acc_url);
	$stmt->bindValue(':CFLG',$acc_flg);
	$result = $stmt->execute();
	
	if($result)
	{
	}
	else
	{
	}

}
?>
