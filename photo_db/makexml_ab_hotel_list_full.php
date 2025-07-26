<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";			//-------test
//$xmldir = "./xml/";			//-------test
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//除外リスト
$jyogailist_ab_h_list_full = array();
$max_size_ab_h_list_full = 12;

//MakeXml_ab_h_list_full("ab_hotel_list.csv");		//-------test

/*
 * 関数名：MakeXml_ab_h_list_full
 * 関数説明：ｘｍｌを出力する（出発地のXML(検索海外)）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_ab_h_list_full($filename)
{
	global $csvdir,$xmldir,$max_size_ab_h_list_full;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_h_list_full($filename01);

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	$xml .= "<root>\n";

	$p_hotel_code_arr_tmp = array();
	$p_hotel_code01 = "";
	

	$HotelDataAry = NULL;
	//繰り返し
	while(! feof($file))
	{
		$flag = false;
		//行の内容は配列にする
		$csvarr = (fgetcsv($file));

		$exists_flg = CheckExists_ab_h_list_full($csvarr);
		if($exists_flg){
			continue;
		}
		if(empty($csvarr[10])){
			continue;
		}

		$dest = $csvarr[4];
		$country = $csvarr[6];
		$city = $csvarr[8];
		$code = $csvarr[10];
		$name = mb_convert_kana($csvarr[11], 'KV', 'UTF-8');
		//$name = str_replace("&","＆",$name);
		$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8',false);
		//$HotelDataAry[$dest][$country][$city][$code] = str_replace('&', '&amp;', $name);
		$HotelDataAry[$dest][$country][$city][$code] = $name;
	}

	foreach($HotelDataAry as $dest => $aryCountry){
		$xml .= '<dest code="' . $dest .'">' . "\n";

		foreach($aryCountry as $country => $aryCity){
			$xml .= '<country code="' . $country .'">' . "\n";

			foreach($aryCity as $city => $aryCode){
				$xml .= '<city code="' . $city .'">' . "\n";

				foreach($aryCode as $code => $name){
					$xml .=<<<EOD
<hotel>
<code>$code</code>
<name>$name</name>
</hotel>

EOD;
				}
				$xml .= "</city>\n";
			}

			$xml .= "</country>\n";
		}
		$xml .= "</dest>\n";
	}


	$xml .= "</root>";

	//CSVファイルを閉じる
	fclose($file);

	//XMLファイル名を作成する
	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_full.xml";

	//XMLファイルに書き込む
	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);
	fclose($file);

	return true;
}

/*
 * 関数名：CheckExists_ab_h_list_full
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_ab_h_list_full($arr)
{
	global $jyogailist_ab_h_list_full;

	$hotel_name = $arr[11];
	
	if(!empty($hotel_name))
	{
		if(in_array($hotel_name,$jyogailist_ab_h_list_full))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

function CheckExists_ab_h_list_full_01($src_line)
{
	global $jyogailist_ab_h_list_full,$max_size_ab_h_list_full;

	$exitedflg = false;

	$end = count($jyogailist_ab_h_list_full);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_h_list_full[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_ab_h_list_full; $j++)
		{
			$src_line_tmp = "__".$src_line[$j];
			$strpos1 = strpos($src_line_tmp,$csvarr);
			if($strpos1 > 0)
			{
				$exitedflg = true;
				break;
			}
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_ab_h_list_full
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_ab_h_list_full($filename)
{
	global $jyogailist_ab_h_list_full,$csvdir,$max_size_ab_h_list_full;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		if($csvarr == false) return;
		if(count($csvarr) > 0)
		{
			$jyogailist_ab_h_list_full = $jyogailist_ab_h_list_full + $csvarr;
		}
	}
}
?>