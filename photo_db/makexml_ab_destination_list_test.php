<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//$csvdir = "../csv/";
//$xmldir = "../";
$xmldata = array();
//除外リスト
$jyogailist_ab_destination_list = array();

//フィールドのサイズ
$max_size_ab_destination_list = 8;

//arrayの定義
$hatsucode_ab_destination_list = array();
$p_hatsuname_ab_destination_list = array();
$p_hatsucode_ab_destination_list = array();
$place_departure_ab_destination_list = "";

//MakeXml_ab_destination_list("ab_destination_list.csv");

/*
 * 関数名：MakeXml_ab_destination_list
 * 関数説明：ｘｍｌを出力する（検索海外用　検索条件：出発地、方面、国、都市）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_ab_destination_list($filename)
{
	global $csvdir,$xmldir,$xmldata,$max_size_ab_destination_list,$p_hatsuname_ab_destination_list;
	
	$xml = "";
	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";		//XMLファイル名を作成する
	$file_xml = fopen($xmldir.$filename01,"w");
	fwrite($file_xml,$xml);		//write
	fclose($file_xml);	
	LoadJyogaiCSV_ab_destination_list("ab_destination_list_no.csv");
//	Load_i_p_hatsu_url_ab_destination_list("i_p_hatsu_url.csv");
	Load_i_p_hatsu_url_ab_destination_list("i_p_hatsu_url_v3.csv");
	sort_csv_ab_destination_list($filename);

	$pos = strpos($filename,".");
	$filename_sort = substr($filename,0,$pos)."01.csv";		//ファイル名を作成する
	
//	$homepage = file_get_contents($csvdir.$filename_sort);
//	print_r($homepage);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file_csv = fopen($csvdir.$filename_sort,"rb");
	$row = 1;
	//もし最後の行ではない場合
	while(! feof($file_csv))
	{
		$csvarr = (fgetcsv($file_csv));				//行の内容は配列にする

//print_r($csvarr);
//echo "\n";
		$cnt = count($csvarr);


		if($cnt == $max_size_ab_destination_list)
		{
			$exists_flg = CheckExists_ab_destination_list($csvarr);
			if($exists_flg)
			{
				continue;
			}

			//最初は東京（成田＋成田＋横浜）
			if($row == 1 && !empty($csvarr[0]))
			{
			
				$depcode = $csvarr[0];
				$sortkey = $depcode;

				$tmp = $csvarr[1];
				//$place_departure_name = mb_convert_kana($tmp,"KVrn","utf-8") . "発";
				$place_departure_name = mb_convert_kana($tmp,"KVrn","utf-8");
				
				//出発地 出発地変更
				$xmldata[$sortkey] = array(
								'place_departure_code'=>$depcode,
								'place_departure_name'=>$place_departure_name
								);

				//方面
				$discode = $csvarr[2];
				//MTRを除外
				if($discode == 'MTR'){
					continue;
				}
				
				$xmldata[$sortkey][$discode] = array(
									'district_code'=>$discode,
									'district_name'=>mb_convert_kana($csvarr[3],"KVrn","utf-8"),
								);

				//国
				$coucode = $csvarr[4];
				$xmldata[$sortkey][$discode][$coucode] = array(
									'country_code'=>$coucode,
									'country_name'=>mb_convert_kana($csvarr[5],"KVrn","utf-8"),
								);
								
				//都市
				$citycode = $csvarr[6];
				$xmldata[$sortkey][$discode][$coucode][$citycode] = array(
									'city_code'=>$citycode,
									'city_name'=>mb_convert_kana($csvarr[7],"KVrn","utf-8"),
				);
				$row++;
			}
			//2行目以降、発コードあり、発コードが横浜ではない
			if($row != 1 && !empty($csvarr[0]) && $csvarr[0] != 133)
			{
				$depcode = $csvarr[0];
				$sortkey = $depcode;

				$tmp = $csvarr[1];
				//$place_departure_name = mb_convert_kana($tmp,"KVrn","utf-8") . "発";
				$place_departure_name = mb_convert_kana($tmp,"KVrn","utf-8");
							
				//置換する！
				$place_departure_name = str_replace(array('東京(成田)','東京(羽田)'),
				array('成田','羽田'),$place_departure_name);

				
				$discode = $csvarr[2];
				$district_name = mb_convert_kana($csvarr[3],"KVrn","utf-8");
				$coucode = $csvarr[4];
				$country_name = mb_convert_kana($csvarr[5],"KVrn","utf-8");
				$citycode = $csvarr[6];
				$city_name = mb_convert_kana($csvarr[7],"KVrn","utf-8");
				$row++;
				
				if(!array_key_exists($depcode,$xmldata))
				{

					//出発地
					$xmldata[$sortkey] = array(
									'place_departure_code'=>$depcode,
									'place_departure_name'=>$place_departure_name
									);
	
					//方面
					$xmldata[$sortkey][$discode] = array(
										'district_code'=>$discode,
										'district_name'=>mb_convert_kana($csvarr[3],"KVrn","utf-8"),
									);
	
					//国
					$xmldata[$sortkey][$discode][$coucode] = array(
										'country_code'=>$coucode,
										'country_name'=>mb_convert_kana($csvarr[5],"KVrn","utf-8"),
									);
									
					//都市
					$xmldata[$sortkey][$discode][$coucode][$citycode] = array(
										'city_code'=>$citycode,
										'city_name'=>mb_convert_kana($csvarr[7],"KVrn","utf-8"),
					);
				} elseif(!array_key_exists($discode,$xmldata[$depcode])) {
					//方面
					$xmldata[$sortkey][$discode] = array(
										'district_code'=>$discode,
										'district_name'=>mb_convert_kana($csvarr[3],"KVrn","utf-8"),
									);
	
					//国
					$xmldata[$sortkey][$discode][$coucode] = array(
										'country_code'=>$coucode,
										'country_name'=>mb_convert_kana($csvarr[5],"KVrn","utf-8"),
									);
									
					//都市
					$xmldata[$sortkey][$discode][$coucode][$citycode] = array(
										'city_code'=>$citycode,
										'city_name'=>mb_convert_kana($csvarr[7],"KVrn","utf-8"),
					);
					
				} elseif(!array_key_exists($coucode,$xmldata[$depcode][$discode])) {
					//国
					$xmldata[$sortkey][$discode][$coucode] = array(
										'country_code'=>$coucode,
										'country_name'=>mb_convert_kana($csvarr[5],"KVrn","utf-8"),
									);
									
					//都市
					$xmldata[$sortkey][$discode][$coucode][$citycode] = array(
										'city_code'=>$citycode,
										'city_name'=>mb_convert_kana($csvarr[7],"KVrn","utf-8"),
					);
				} elseif(!array_key_exists($citycode,$xmldata[$depcode][$discode][$coucode])){
					//都市
					$xmldata[$sortkey][$discode][$coucode][$citycode] = array(
										'city_code'=>$citycode,
										'city_name'=>mb_convert_kana($csvarr[7],"KVrn","utf-8"),
					);
				}
			}
		}
	}

	fclose($file_csv);		//先開いたのCSVファイルを閉じます

	write_xml($filename01, $filename_sort);
}

function write_xml($filename01, $filename_sort)
{
	global $xmldata,$xmldir,$csvdir;

	$xml = "";
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";
	foreach($xmldata as $key01=>$dep)
	{
	
		$depcode = $dep['place_departure_code'];
		$depname = $dep['place_departure_name'];
		$xml .= "    <departure place_departure_code=\"$depcode\" place_departure_name=\"$depname\">\r\n";
		
		foreach($dep as $key02=>$dis)
		{
			$tmp_discode = $dis['district_code'];
		
			if(is_array($dis) && !empty($tmp_discode))
			{
				$discode = $dis['district_code'];
				$disname = $dis['district_name'];
				$xml .= "        <district district_code=\"$discode\" district_name=\"$disname\">\r\n";
			
				foreach($dis as $key03=>$cou)
				{
					if(is_array($cou))
					{
						$coucode = $cou['country_code'];
						$couname = $cou['country_name'];
						$xml .= "            <country country_code=\"$coucode\" country_name=\"$couname\">\r\n";
						
						foreach($cou as $key04=>$city)
						{
							if(is_array($city))
							{
								$citycode = $city['city_code'];
								$cityname = $city['city_name'];
								$xml .= "                <city city_code=\"$citycode\" city_name=\"$cityname\" />\r\n";
							}
						}
						$xml .= "            </country>\r\n";
					}
				}
				$xml .= "            </district>\r\n";
			}
		}
		$xml .= "     </departure>\r\n";
	}
	$xml .= "</root>\r\n";
	
	$file_xml = fopen($xmldir.$filename01,"a");
	fwrite($file_xml,$xml);		//write
	
	fclose($file_xml);			//close

	unlink($csvdir.$filename_sort);

	return true;
	
	
}

/*
 * 関数名：LoadJyogaiCSV_ab_destination_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_ab_destination_list($filename)
{
	global $jyogailist_ab_destination_list,$csvdir,$max_size_ab_destination_list;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_ab_destination_list)
		{
			$jyogailist_ab_destination_list[] = $csvarr;
		}
	}
}

/*
 * 関数名：CheckExists_ab_destination_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_ab_destination_list($src_line)
{
	global $jyogailist_ab_destination_list,$max_size_ab_destination_list;

	$exitedflg = false;

	$end = count($jyogailist_ab_destination_list);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_destination_list[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_ab_destination_list; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_ab_destination_list)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：Load_i_p_hatsu_url
 * 関数説明：p_hatsunameを読み込みする（出発地のXML(検索海外)）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function Load_i_p_hatsu_url_ab_destination_list($filename)
{
	global $csvdir,$hatsucode_ab_destination_list,$p_hatsuname_ab_destination_list,$p_hatsucode_ab_destination_list,$max_size_ab_destination_list;
	if(!is_file($csvdir.$filename)) return;

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");
	$row = 1;

	$tmp_hatsucode = "";
	//繰り返し
	while(! feof($file))
	{
		//行の内容は配列にする
		$csvarr = (fgetcsv($file));
		$cnt = count($csvarr);

		//一行目を飛び読む
		if($row != 1 && $cnt > 0)
		{
			if($cnt == 7)
			{
				if(!isset($hatsucode_ab_destination_list[$csvarr[0]]))
				{
					$hatsucode_ab_destination_list[$csvarr[0]] = $csvarr[6];
					
				}

				if(!isset($p_hatsuname_ab_destination_list[$csvarr[0]]))
				{
					$p_hatsucode_ab_destination_list[$csvarr[0]] = $csvarr[6];
					$p_hatsuname_ab_destination_list[$csvarr[0]] = $csvarr[1];
				}
			}
		}

		$row++;
	}
	asort($p_hatsucode_ab_destination_list);

	//CSVファイルを閉じる
	fclose($file);

}

/*
 * 関数名：Get_p_hatsucode
 * 関数説明：p_hatsucodeを読み込みする（出発地のXML(検索海外)）
 * パラメタ：hatsucode_src
 * 戻り値：p_hatsucode　
 */
function Get_p_hatsucode_ab_destination_list($hatsucode_ab_destination_list_src)
{
	global $hatsucode_ab_destination_list;

	$ret_code = "-1";
	if(isset($hatsucode_ab_destination_list[$hatsucode_ab_destination_list_src]))
	{
		$tmp_p = $hatsucode_ab_destination_list[$hatsucode_ab_destination_list_src];

		foreach($hatsucode_ab_destination_list as $key=>$item)
		{
			if($item == $tmp_p)
			{
				$ret_code = $tmp_p;
				break;
			}
		}
	}

	return $ret_code;
}

/*
 * 関数名：Get_p_hatsuname
 * 関数説明：p_hatsucodeを読み込みする（出発地のXML(検索海外)）
 * パラメタ：p_hatsucode
 * 戻り値：p_hatsuname　
 */
function Get_p_hatsuname_ab_destination_list($p_hatsucode_ab_destination_list)
{
	global $p_hatsuname_ab_destination_list;

	$ret_code = "";

	if(isset($p_hatsuname_ab_destination_list[$p_hatsucode_ab_destination_list]))
	{
		$ret_code = $p_hatsuname_ab_destination_list[$p_hatsucode_ab_destination_list];
	}

	return $ret_code;
}

/*
 * 関数名：sort_csv
 * 関数説明：CSVファイルをソートする（出発地のXML(検索海外)）
 * パラメタ：filename：CSVファイル名
 * 戻り値：無し　
 */
function sort_csv_ab_destination_list($filename)
{
	global $csvdir,$p_hatsucode_ab_destination_list,$p_hatsuname_ab_destination_list;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."01.csv";		//ファイル名を作成する

	$file = fopen($csvdir.$filename01,"w");

	$data_array = @file($csvdir.$filename);

	foreach($p_hatsucode_ab_destination_list as $key => $sortnum)
	{
		for($j=0;$j<count($data_array);$j++)
		{
			$parts_array[$j] = explode(",",$data_array[$j]);
			$tmpvalue = str_replace("\"","",$parts_array[$j][0]);
			$tmpcode_src = $tmpvalue;
			$tmpcode_desc = $key;
			//成田、羽田,横浜なら同じ配列にいれる
			if(trim($tmpcode_src) == trim($tmpcode_desc))
			{
				if($tmpcode_src == 101 || $tmpcode_src == 130 || $tmpcode_src == 133){
					//置換する！
					$data_array_tmp[$j] = str_replace(array('"101"','"130"','"133"','東京(成田)','東京(羽田)','横浜'),
					array('"101,130,133"','"101,130,133"','"101,130,133"','東京','東京','東京'),$data_array[$j]);

					$result_arraytmp[] = $data_array_tmp[$j];
				}
			}
		}
//print_r($result_arraytmp);

		if(isset($result_arraytmp))
		{
			for($k=0;$k<count($result_arraytmp);$k++)
			{
				$linestr = $result_arraytmp[$k];
				fwrite($file,$linestr);		//write
			}
			unset($result_arraytmp);
		}

		for($j=0;$j<count($data_array);$j++)
		{
			$parts_array[$j] = explode(",",$data_array[$j]);
			$tmpvalue = str_replace("\"","",$parts_array[$j][0]);
			$tmpcode_src = $tmpvalue;
			$tmpcode_desc = $key;

			if(trim($tmpcode_src) == trim($tmpcode_desc))
			{
				$result_array[] = $data_array[$j];
			}
		}

		if(isset($result_array))
		{
			for($k=0;$k<count($result_array);$k++)
			{
				$linestr = $result_array[$k];
				fwrite($file,$linestr);		//write
			}

			unset($result_array);
		}
		else{
			if(!($key == 101 || $key == 130 || $key == 133)){
				$dep_code = $key;
				$dep_name = $p_hatsuname_ab_destination_list[$key];
				$linestr = '"' . $dep_code . '","' . $dep_name . '","","","","","",""'. "\n";
					fwrite($file,$linestr);		//write
			}
		}
	}
	fclose($file);			//close

}


/*********************************************
* hatsuConversion()　p_hatsu変換
* 
* 引数
* 	$var				:p_hatsuの値
*  返り値
* 	$req_val		:変換後p_hatsuの値
**********************************************/
function makeNewHatsu()
{
	global $csvdir;
	//変換CSV読みこみ
	$ConversionFile = $csvdir . "i_p_hatsu_url_v3.csv";
	
	if (!file_exists($ConversionFile)) {
		return;
	}
	//CSVデータを取得
	$handle = @fopen($ConversionFile, "r");
	while (($data = fgetcsv($handle, 99999, ",")) !== FALSE)
	{
		//パラメータが正しく入っているものが[正]
		if( !empty($data[6]) )
		{
			//キーは新パラメータ値　データは旧パラメータ値
			$ConversionDataAry[$data[6]] = $data[0];
			$ConversionKeyAry[$data[7]] = $data[0];
		}
	}
	return array($ConversionDataAry, $ConversionKeyAry);
}
?>