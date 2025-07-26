<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";

//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//除外リスト
$jyogailist_air_destination_list = array();
$max_size_air_destination_list = 8;

//$flag = MakeXml_air_destination_list("air_destination_list.csv");
//if($flag)
//{
//	print "ＸＭＬを出力しました！\r\n";
//}

/*
 * 関数名：MakeXml_air_destination_list
 * 関数説明：ｘｍｌを出力する（検索航空用　検索条件：出発地、方面、国、都市）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_air_destination_list($filename)
{
	global $csvdir,$xmldir,$max_size_air_destination_list;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_air_destination_list($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");
	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";
	$i = 1;
	$j = 1;

	$AirDataAry = NULL;
	//もし最後の行ではない場合
	while(! feof($file)){
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt > 0)
		{
			if($cnt == $max_size_air_destination_list)
			{
				$exists_flg = CheckExists_air_destination_list($csvarr);
				if($exists_flg)
				{
					continue;
				}

				$place_departure_code = $csvarr[0];
				$place_departure_name = mb_convert_kana($csvarr[1],"KVrn","utf-8");
				$district_code = $csvarr[2];
				$district_name = mb_convert_kana($csvarr[3],"KVrn","utf-8");
				$country_code = $csvarr[4];
				$country_name = mb_convert_kana($csvarr[5],"KVrn","utf-8");
				$city_code = $csvarr[6];
				$city_name = mb_convert_kana($csvarr[7], 'KV', 'UTF-8');

				$AirDataAry[$place_departure_code . '_' . $place_departure_name][$district_code . '_' . $district_name][$country_code . '_' . $country_name][$city_code . '_' . $country_name] = str_replace('&', '&amp;', $city_name);

			}
		}
	}

//	print_r($AirDataAry);
	

	foreach($AirDataAry as $departure => $aryDest){
	
		$departureAry = explode('_',$departure);

		$xml .= "    <departure place_departure_code=\"".$departureAry[0]."\" place_departure_name=\"".$departureAry[1]."\">\r\n";

		foreach($aryDest as $dest => $aryCountry){
		
			$districtAry =  explode('_',$dest);
	
			$xml .= "        <district district_id=\"".$i."\" district_code=\"".$districtAry[0]."\" district_name=\"".$districtAry[1]."\">\r\n";
			$i++;
			
			foreach($aryCountry as $country => $aryCity){
			
				$countryAry = explode("_",$country);
				$xml .= "            <country country_id=\"".$j."\" country_code=\"".$countryAry[0]."\" country_name=\"".$countryAry[1]."\">\r\n";
				$j++;	

				foreach($aryCity as $city => $cityName){
				
					$cityAry =  explode("_",$city);
					$xml .= "                <city city_code=\"".$cityAry[0]."\" city_name=\"".$cityName."\" />\r\n";

				}
				$xml .= "            </country>\n";
			}
			$xml .= "        </district>\n";
		}
		$xml .= "    </departure>\n";
	}
	$xml .= "</root>";

//
//	//もし最後の行ではない場合
//	while(! feof($file))
//	{
//		$csvarr = (fgetcsv($file));				//行の内容は配列にする
//
//		$cnt = count($csvarr);
//
//		if($cnt == $max_size_air_destination_list)
//		{
//			$exists_flg = CheckExists_air_destination_list($csvarr);
//			if($exists_flg)
//			{
//				continue;
//			}
//
//			if($row == 1 && !empty($csvarr[0]))
//			{
//				$place_departure_code = $csvarr[0];
////				$place_departure_name = $csvarr[1];
//				$place_departure_name = mb_convert_kana($csvarr[1],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
//
//				$district_code = $csvarr[2];
////				$district_name = $csvarr[3];
//				$district_name = mb_convert_kana($csvarr[3],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
//
//				$country_code = $csvarr[4];
////				$country_name = $csvarr[5];
//				$country_name = mb_convert_kana($csvarr[5],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
//
//				$city_code = $csvarr[6];
////				$city_name = $csvarr[7];
//				$city_name = mb_convert_kana($csvarr[7],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
//
//				$district_id = 1;
//				$country_id = 1;
//				$xml .= "    <departure place_departure_code=\"".$csvarr[0]."\" place_departure_name=\"".$csvarr[1]."\">\r\n";
//				$xml .= "        <district district_id=\"".$i."\" district_code=\"".$csvarr[2]."\" district_name=\"".$csvarr[3]."\">\r\n";
//				$xml .= "            <country country_id=\"".$j."\" country_code=\"".$csvarr[4]."\" country_name=\"".$csvarr[5]."\">\r\n";
//				$xml .= "                <city city_code=\"".$csvarr[6]."\" city_name=\"".$csvarr[7]."\" />\r\n";
//				$i++;
//				$j++;
//
//			}
//			if($row !== 1 && !empty($csvarr[0]))
//			{
//				$place_departure_name = mb_convert_kana($csvarr[1],"KVrn","utf-8");
//				$district_name = mb_convert_kana($csvarr[3],"KVrn","utf-8");
//				$country_name = mb_convert_kana($csvarr[5],"KVrn","utf-8");
//				$city_name = mb_convert_kana($csvarr[7],"KVrn","utf-8");
//
//				if($place_departure_code !== $csvarr[0])
//				{
//					$xml .= "            </country>\r\n";
//					$xml .= "        </district>\r\n";
//					$xml .= "    </departure>\r\n";
//					$xml .= "    <departure place_departure_code=\"".$csvarr[0]."\" place_departure_name=\"".$place_departure_name."\">\r\n";
//					$xml .= "        <district district_id=\"".$i."\" district_code=\"".$csvarr[2]."\" district_name=\"".$district_name."\">\r\n";
//					$xml .= "            <country country_id=\"".$j."\" country_code=\"".$csvarr[4]."\" country_name=\"".$country_name."\">\r\n";
//					$xml .= "                <city city_code=\"".$csvarr[6]."\" city_name=\"".$city_name."\" />\r\n";
//					$place_departure_code = $csvarr[0];
//					$district_code = $csvarr[2];
//					$country_code = $csvarr[4];
//					$city_code = $csvarr[6];
//					$i++;
//					$j++;
//				}
//
//				elseif($district_code !== $csvarr[2])
//				{
//					$xml .= "            </country>\r\n";
//					$xml .= "        </district>\r\n";
//					$xml .= "        <district district_id=\"".$i."\" district_code=\"".$csvarr[2]."\" district_name=\"".$district_name."\">\r\n";
//					$xml .= "            <country country_id=\"".$j."\" country_code=\"".$csvarr[4]."\" country_name=\"".$country_name."\">\r\n";
//					$xml .= "                <city city_code=\"".$csvarr[6]."\" city_name=\"".$city_name."\" />\r\n";
//					$district_code = $csvarr[2];
//					$country_code = $csvarr[4];
//					$city_code = $csvarr[6];
//					$i++;
//					$j++;
//				}
//
//				elseif($country_code !== $csvarr[4])
//				{
//					$xml .= "            </country>\r\n";
//					$xml .= "            <country country_id=\"".$j."\" country_code=\"".$csvarr[4]."\" country_name=\"".$country_name."\">\r\n";
//					$xml .= "                <city city_code=\"".$csvarr[6]."\" city_name=\"".$city_name."\" />\r\n";
//					$country_code = $csvarr[4];
//					$city_code = $csvarr[6];
//					$j++;
//				}
//
//				elseif($city_code !== $csvarr[6])
//				{
//					$xml .= "                <city city_code=\"".$csvarr[6]."\" city_name=\"".$city_name."\" />\r\n";
//					$city_code = $csvarr[6];
//				}
//
//			}
//		}
//
//		$row++;
//	}
//
//	$xml .= "            </country>\r\n";
//	$xml .= "        </district>\r\n";
//	$xml .= "    </departure>\r\n";
//	$xml .= "</root>\r\n";

	fclose($file);		//先開いたのCSVファイルを閉じます

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);		//write
	fclose($file);			//close

	return true;
}

/*
 * 関数名：CheckExists_air_destination_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_air_destination_list($src_line)
{
	global $jyogailist_air_destination_list,$max_size_air_destination_list;

	$exitedflg = false;

	$end = count($jyogailist_air_destination_list);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_air_destination_list[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_air_destination_list; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_air_destination_list)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_air_destination_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_air_destination_list($filename)
{
	global $jyogailist_air_destination_list,$csvdir,$max_size_air_destination_list;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_air_destination_list)
		{
			$jyogailist_air_destination_list[] = $csvarr;
		}
	}
}
?>