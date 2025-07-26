<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";

//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//除外リスト
$jyogailist_d_p_hatsu_url = array();
$max_size_d_p_hatsu_url = 13;

/*
$flag = MakeXml_d_p_hatsu_url_v3("d_p_hatsu_url_v3.csv");
if($flag)
{
	print "ＸＭＬを出力しました！\r\n";
}
*/

//変数定義
$hatsucode_cel 			= 0;
$hatsuname 					= 1;
$hatsucode_sub 			= 2;
$hatsucode_sub_hbos = 3;
$hatsuname_sub 			= 4;
$hatsuname_sub_hbos = 5;
$URL 								= 6;
$top 								= 7;
$p_hatsuname 				= 8;
$p_hatsucode 				= 9;
$sortnum 						= 10;
$pull_flg						= 11;
$prefecture_code		= 12;

/*
 * 関数名：MakeXml_d_p_hatsu_url_v3
 * 関数説明：ｘｍｌを出力する（出発地URL カレンダー国内用　 検索国内共有）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_d_p_hatsu_url_v3($filename)
{
	global $csvdir,$xmldir,$max_size_d_p_hatsu_url;

	if(!is_file($csvdir.$filename)) return;

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_d_p_hatsu_url_v3($filename01);
	
	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	//繰り返し
	while(! feof($file))
	{
		//行の内容は配列にする
		$csvarr = (fgetcsv($file));
		if(is_array($csvarr)){  
			$cnt = count($csvarr); 
		}else{
			$cnt = 0;
		}

		//一行目を飛び読む
		if($row != 1 && $cnt > 0)
		{
			if($cnt == $max_size_d_p_hatsu_url)
			{
				$exists_flg = CheckExists_d_p_hatsu_url_v3($csvarr);
				if($exists_flg)
				{
					continue;
				}
				//方面行はとばす
				if(empty($csvarr[2])){
					continue;
				}
				
				$hatsuname = mb_convert_kana($csvarr[1],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				$hatsuname_sub = mb_convert_kana($csvarr[4],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				$hatsuname_sub_hbos = mb_convert_kana($csvarr[5],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				$p_hatsuname = mb_convert_kana($csvarr[8],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				
//				$xml .= "   <h".$csvarr[0]."  hatsuname=\"".$hatsuname."\"  hatsucode_sub=\"".$csvarr[2]."\"  hatsuname_sub=\"".$hatsuname_sub."\"  href=\"".$csvarr[4]."\"  top=\"".$csvarr[5]."\"  p_hatsuname=\"".$p_hatsuname."\"  p_hatsucode=\"".$csvarr[7]."\" />\r\n";
				$xml .= "   <h".$csvarr[9]."  hatsuname=\"".$hatsuname_sub_hbos."\"  hatsucode_sub=\"".$csvarr[3]."\"  hatsuname_sub=\"".$hatsuname_sub."\"  href=\"".$csvarr[6]."\"  top=\"".$csvarr[7]."\"  p_hatsuname=\"".$p_hatsuname."\"  p_hatsucode=\"".$csvarr[9]."\" />\r\n";
			}
		}

		$row++;
	}

	$xml .= "</root>";

	//CSVファイルを閉じる
	fclose($file);

	//XMLファイル名を作成する
	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";

	//XMLファイルに書き込む
	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);
	fclose($file);
	
	return true;
}

/*
 * 関数名：CheckExists_d_p_hatsu_url_v3
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_d_p_hatsu_url_v3($src_line)
{
	global $jyogailist_d_p_hatsu_url,$max_size_d_p_hatsu_url;

	$exitedflg = false;

	$end = count($jyogailist_d_p_hatsu_url);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_d_p_hatsu_url[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_d_p_hatsu_url; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_d_p_hatsu_url)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_d_p_hatsu_url_v3
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_d_p_hatsu_url_v3($filename)
{
	global $jyogailist_d_p_hatsu_url,$csvdir,$max_size_d_p_hatsu_url;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_d_p_hatsu_url)
		{
			$jyogailist_d_p_hatsu_url[] = $csvarr;
		}
	}
}
?>