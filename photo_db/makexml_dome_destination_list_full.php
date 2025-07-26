<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";

//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";



$xmldata_dome_destination_list_full = array();

//除外リスト
$jyogailist_dome_destination_list_full = array();
$max_size_dome_destination_list_full = 10;

/*
$flag = MakeXml_dome_destination_list_full("dome_destination_list.csv");
if($flag)
{
	print "ＸＭＬを出力しました！\n";
}
*/

function MakeXml_dome_destination_list_full($filename)
{
	global $csvdir,$xmldir,$xmldata_dome_destination_list_full;


	if(!is_file($csvdir.$filename)) return;


	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_dome_destination_list_full($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");
	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	$xml .= "<root>\n";

	//もし最後の行ではない場合
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$exists_flg = CheckExists_dome_destination_list_full($csvarr);
		if($exists_flg)
		{
			continue;
		}

		$depcode = $csvarr[0];
		$depname = mb_convert_kana($csvarr[1],"KVrn","utf-8");
		$subdepcode = $csvarr[2];
		$subdepname = mb_convert_kana($csvarr[3],"KVrn","utf-8");
		$discode = $csvarr[4];
		$disname = mb_convert_kana($csvarr[5],"KVrn","utf-8");
		$precode = $csvarr[6];
		$prename = mb_convert_kana($csvarr[7],"KVrn","utf-8");
		$citycode = $csvarr[8];
		$cityname = mb_convert_kana($csvarr[9],"KVrn","utf-8");

		if(empty($depcode)){
			continue;
		}

		if(!isset($xmldata_dome_destination_list_full[$depcode])){
			$xmldata_dome_destination_list_full[$depcode] = array(
				'place_departure_code'=>$depcode,
				'place_departure_name'=>$depname
			);
		}
		if(!isset($xmldata_dome_destination_list_full[$depcode][$subdepcode])){
			$xmldata_dome_destination_list_full[$depcode][$subdepcode] = array(
				'place_subdeparture_code'=>$subdepcode,
				'place_subdeparture_name'=>$subdepname
			);
		}
		if(!isset($xmldata_dome_destination_list_full[$depcode][$subdepcode][$discode])){
			$xmldata_dome_destination_list_full[$depcode][$subdepcode][$discode] = array(
				'district_code'=>$discode,
				'district_name'=>$disname,
			);
		}
		if(!isset($xmldata_dome_destination_list_full[$depcode][$subdepcode][$discode][$precode])){
			$xmldata_dome_destination_list_full[$depcode][$subdepcode][$discode][$precode] = array(
				'prefecture_code'=>$precode,
				'prefecture_name'=>$prename,
			);
		}
		if(!isset($xmldata_dome_destination_list_full[$depcode][$subdepcode][$discode][$precode][$citycode])){
			$xmldata_dome_destination_list_full[$depcode][$subdepcode][$discode][$precode][$citycode] = array(
				'city_code'=>$citycode,
				'city_name'=>$cityname,
			);
		}
	}

	//XMLを書き出します

	foreach($xmldata_dome_destination_list_full as $hatsu){
		//出発地
		$xml .= "\t<departure place_departure_code=\"".$hatsu['place_departure_code']."\" place_departure_name=\"".$hatsu['place_departure_name']."\">\n";

		//発サブ
		foreach($hatsu as $hatusabu){
			if(is_array($hatusabu)){
				$xml .= "\t\t<subdeparture place_subdeparture_code=\"".$hatusabu['place_subdeparture_code']."\" place_subdeparture_name=\"".$hatusabu['place_subdeparture_name']."\">\n";

				//方面
				foreach($hatusabu as $dest){
					if(is_array($dest)){
						$xml .= "\t\t\t<district district_code=\"".$dest['district_code']."\" district_name=\"".$dest['district_name']."\">\n";

						//都道府県
						foreach($dest as $pref){
							if(is_array($pref)){
								$xml .= "\t\t\t\t<prefecture  prefecture_code=\"".$pref['prefecture_code']."\" prefecture_name=\"".$pref['prefecture_name']."\">\n";

								//都市
								foreach($pref as $city){
									if(is_array($city)){
										$xml .= "\t\t\t\t\t<city city_code=\"".$city['city_code']."\" city_name=\"".$city['city_name']."\" />\n";
									}
								}
								$xml .= "\t\t\t\t</prefecture>\n";
							}
						}
						$xml .= "\t\t\t</district>\n";
					}
				}
				$xml .= "\t\t</subdeparture>\n";
			}
		}
		$xml .= "\t</departure>\n";
	}
	$xml .= "</root>\n";


//	$i = 0;
//
//	foreach($xmldata_dome_destination_list_full as $key=>$value)
//	{
//		if($i == 0)
//		{
//			$xml .= "    <departure place_departure_code=\"".$value['place_departure_code']."\" place_departure_name=\"".$value['place_departure_name']."\">\n";
//
//			foreach($value as $key01=>$value01)
//			{
//				if(is_array($value01))
//				{
//					$xml .= "        <district district_code=\"".$value01['district_code']."\" district_name=\"".$value01['district_name']."\">\n";
//
//					foreach($value01 as $key02=>$value02)
//					{
//						if(is_array($value02))
//						{
//							$xml .= "            <prefecture  prefecture_code=\"".$value02['prefecture_code']."\" prefecture_name=\"".$value02['prefecture_name']."\">\n";
//
//							foreach($value02 as $key03=>$value03){
//								if(is_array($value03)){
//									$xml .= "                <city city_code=\"".$value03['city_code']."\" city_name=\"".$value03['city_name']."\" />\n";
//								}
//							}
//							$xml .="            </prefecture>\n";
//						}
//					}
//					$xml .="        </district>\n";
//				}
//			}
//			$i++;
//		}
//		else if(!empty($value['place_departure_code']))
//		{
//    		$xml .="     </departure>\n";
//
//			$xml .= "    <departure place_departure_code=\"".$value['place_departure_code']."\" place_departure_name=\"".$value['place_departure_name']."\">\n";
//
//			foreach($value as $key01=>$value01)
//			{
//				if(is_array($value01))
//				{
//					$xml .= "        <district district_code=\"".$value01['district_code']."\" district_name=\"".$value01['district_name']."\">\n";
//
//					foreach($value01 as $key02=>$value02)
//					{
//						if(is_array($value02))
//						{
//							$xml .= "            <prefecture  prefecture_code=\"".$value02['prefecture_code']."\" prefecture_name=\"".$value02['prefecture_name']."\">\n";
//
//							foreach($value02 as $key03=>$value03){
//								if(is_array($value03)){
//									$xml .= "                <city city_code=\"".$value03['city_code']."\" city_name=\"".$value03['city_name']."\" />\n";
//								}
//							}
//							$xml .="            </prefecture>\n";
//						}
//					}
//					$xml .="        </district>\n";
//				}
//			}
//		}
//	}
//
//	$xml .= "    </departure>\n";
//	$xml .= "</root>\n";

	fclose($file);		//先開いたのCSVファイルを閉じます


	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_full.xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);		//write
	fclose($file);			//close

	return true;
}

/*
 * 関数名：CheckExists_dome_destination_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_dome_destination_list_full($src_line)
{
	global $jyogailist_dome_destination_list_full,$max_size_dome_destination_list_full;

	$exitedflg = false;

	$end = count($jyogailist_dome_destination_list_full);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_dome_destination_list_full[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_dome_destination_list_full; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_dome_destination_list_full)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_dome_destination_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_dome_destination_list_full($filename)
{
	global $jyogailist_dome_destination_list_full,$csvdir,$max_size_dome_destination_list_full;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_dome_destination_list_full)
		{
			$jyogailist_dome_destination_list_full[] = $csvarr;
		}
	}
}
?>