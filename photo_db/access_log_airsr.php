<?php
########################################################################
#
# 航空券ビーコン用
# 
#  ◆取得したいファイルにパラメータをつけたimgタグを入れ込む(ヘッダ部分)

#  ◆値を取得し、x.hankyu(サーバ) ximage(DB) tbl_airsr_log(table) へ登録
#  値：fareID			商品ID
#			 gCityCode	発ID(master_air_hatsu.csv、元は国内空港拠点振分け_.xls)であて換えてあるはず
#			 access_time
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
	//商品IDをセット
	$acc_fare_id = $_GET['fareID'];
	//発IDをセット
	$acc_gcity_code = $_GET['gCityCode'];
	//時刻をセット
	$p_date = $_GET['access_time'];

	//全て空ならリターン
	if(empty($acc_fare_id) && empty($gCityCode) && empty($p_date)){
		return false;
	}
	//英数値型でなかったら何もしない
	if(!preg_match("/^[0-9A-Za-z]+$/",$acc_fare_id)){
		return false;
	}
/*
	//国内空港拠点振り分け対応表を読みこみ
	$handle = fopen('log_save/master_air_hatsu.csv', "r");
	if($handle){
		while (!feof($handle)) {
		
			$buffer = rtrim(fgets($handle, 9999));	//日本語ファイルはfgetcsv使うのやめておく
			$buffer = str_replace('"', '', $buffer);	//ダブルクォーテーション不要
			//空白行はサヨナラ
			if(empty($buffer)){
				continue;
			}
			$data = explode("\t", $buffer);
			//排除フラグあり、担当部署なしは無視	
			if( !empty($data[5]) || empty($data[6]))
			{
				continue;
			}
			$key = $data[0];
			//空港コードor都市コードにあったらTYO or FUKにセットする
			if(strpos($gCityCode,$data[1]) !== false || strpos($gCityCode,$data[2]) !== false){
				if(strpos($data[6],'FIT東京') !== false){
					$hatsuCode = 'TYO';
				}elseif(strpos($data[6],'FIT福岡') !== false){
					$hatsuCode = 'FUK';
				}else{
					$hatsuCode = '';
				}
				$acc_gcity_code = $hatsuCode;
				//セットしたら脱出
				break;
			}
		}
		fclose($handle);
	}
	//時刻処理
	$p_date = str_replace("_"," ",$p_date);
	$p_date = date("Y-m-d H:i:s",strtotime($p_date));
*/
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
	
	$sql = "insert into tbl_airsr_log(acc_fare_id,acc_gcity_code,acc_date,create_time) values (:CID,:CITYCODE,:CDATE,:NDATE) ";
	
	$stmt = $db_link->prepare($sql);
	$stmt->bindValue(':CID',$acc_fare_id);
	$stmt->bindValue(':CITYCODE',$acc_gcity_code);
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
