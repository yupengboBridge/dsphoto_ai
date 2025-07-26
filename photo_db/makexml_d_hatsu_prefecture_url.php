<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//除外リスト
$jyogailist_d_hatsu_prefe_url = array();
$max_size_d_hatsu_prefe_url = 9;

/*
 * 関数名：MakeXml_d_hatsu_prefe_url
 * 関数説明：ｘｍｌを出力する（国(検索海外)）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_d_hatsu_prefe_url($filename)
{
	global $csvdir,$xmldir,$max_size_d_hatsu_prefe_url;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_d_hatsu_prefe_url($filename01);
	
	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

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
			if($cnt == $max_size_d_hatsu_prefe_url)
			{
				$exists_flg = CheckExists_d_hatsu_prefe_url($csvarr);
				if($exists_flg)
				{
					continue;
				}
				
				$p_prefecturename = mb_convert_kana($csvarr[1],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				$xml .= "   <h".$csvarr[0]."  p_prefecturename=\"".$p_prefecturename."\"  tyo=\"".$csvarr[2]."\"  osa=\"".$csvarr[3]."\"  ngo=\"".$csvarr[4]."\"  fuk=\"".$csvarr[5]."\"  spk=\"".$csvarr[6]."\"  sdj=\"".$csvarr[7]."\"  hij=\"".$csvarr[8]."\" />\r\n";
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
 * 関数名：CheckExists_d_hatsu_prefe_url
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_d_hatsu_prefe_url($src_line)
{
	global $jyogailist_d_hatsu_prefe_url,$max_size_d_hatsu_prefe_url;

	$exitedflg = false;

	$end = count($jyogailist_d_hatsu_prefe_url);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_d_hatsu_prefe_url[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_d_hatsu_prefe_url; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_d_hatsu_prefe_url)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_d_hatsu_prefe_url
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_d_hatsu_prefe_url($filename)
{
	global $jyogailist_d_hatsu_prefe_url,$csvdir,$max_size_d_hatsu_prefe_url;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_d_hatsu_prefe_url)
		{
			$jyogailist_d_hatsu_prefe_url[] = $csvarr;
		}
	}
}
?>