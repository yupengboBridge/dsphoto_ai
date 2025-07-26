<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";

//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//除外リスト
$jyogailist_p_city = array();
$max_size_p_city = 3;

/*
 * 関数名：MakeXml_p_city
 * 関数説明：ｘｍｌを出力する（国(検索海外)）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_p_city($filename)
{
	global $csvdir,$xmldir,$max_size_p_city;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_p_city($filename01);

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");
	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	//繰り返し
	while(! feof($file))
	{
		//行の内容は配列にする
		$csvarr = fgetcsv($file,null,",");
		
		if(is_array($csvarr)){  
			$cnt = count($csvarr); 
		}else{
			$cnt = 0;
		}

		//一行目を飛び読む
		if($row != 1 && $cnt > 0)
		{
			if($cnt == $max_size_p_city)
			{
				$exists_flg = CheckExists_p_city($csvarr);
				if($exists_flg)
				{
					continue;
				}
//				$new_p_city = mb_convert_kana($csvarr[1],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				$new_p_cityname = mb_convert_kana($csvarr[2],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				$new_p_cityname = str_replace("&","＆",$new_p_cityname);

				$xml .= "   <c".$csvarr[0]."  new_p_city=\"".$csvarr[1]."\" new_p_cityname=\"$new_p_cityname\"/>\r\n";
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
 * 関数名：CheckExists_p_city
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_p_city($src_line)
{
	global $jyogailist_p_city,$max_size_p_city;

	$exitedflg = false;

	$end = count($jyogailist_p_city);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_p_city[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_p_city; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_p_city)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_p_city
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_p_city($filename)
{
	global $jyogailist_p_city,$csvdir,$max_size_p_city;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_p_city)
		{
			$jyogailist_p_city[] = $csvarr;
		}
	}
}
?>