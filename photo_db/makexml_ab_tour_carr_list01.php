<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";
//$csvdir = "../csv/";
//$xmldir = "../";
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
$xmldoc_ab_tour_carr_list01 = "";
//除外リスト
$jyogailist_ab_tour_carr_list01 = array();

//フィールドのサイズ
$max_size_ab_tour_carr_list01 = 6;

//arrayの定義
$i_p_hatsu_url = array();

//MakeXml_ab_tour_carr_list01("ab_tour_carr_list.csv");

function Load_i_p_hatsu_url_ab_tour_carr_list01()
{
	global $csvdir,$i_p_hatsu_url;

	if(!is_file($csvdir."i_p_hatsu_url_v3.csv")) return;

	//CSVファイルを読み込む
	$file = fopen($csvdir."i_p_hatsu_url_v3.csv","rb");
	$row = 1;
	//繰り返し
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));
		if($row !== 1)
		{
			//行の内容は配列にする
			$key = $csvarr[0];

			$i_p_hatsu_url[$key] = array(
						"hatsucode"=>$csvarr[0],
						"hatsuname"=>$csvarr[1],
						"URL"=>$csvarr[2],
						"top"=>$csvarr[3],
						"p_hatsuname"=>$csvarr[4],
						"p_hatsucode"=>$csvarr[5]
							);
		}
		$row++;
	}

	//CSVファイルを閉じる
	fclose($file);
}

//$flag = MakeXml_ab_tour_carr_list("ab_tour_carr_list.csv");
//if($flag)
//{
//	print "ＸＭＬを出力しました！\r\n";
//}


/*
 * 関数名：MakeXml_ab_tour_carr_list
 * 関数説明：ｘｍｌを出力する（検索海外用　検索条件：出発地、方面、国、都市）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_ab_tour_carr_list01($filename)
{
	global $csvdir,$xmldir,$i_p_hatsu_url;

	if(!is_file($csvdir.$filename)) return;

	//Load_i_p_hatsu_url_ab_tour_carr_list01();

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_tour_carr_list01($filename01);

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

	$xml = array();

	//繰り返し
	while(! feof($file))
	{
		$flag = false;
		$csvarr = (fgetcsv($file));
		$exists_flg = CheckExists_ab_tour_carr_list01($csvarr);
		if($exists_flg)
		{
			continue;
		}
		$key = $csvarr[0];
		$air_code = $csvarr[4];
		$air_name = mb_convert_kana($csvarr[5],"KV","utf-8");
		
		if(!empty($key))
		{
			if(isset($xml[$key]))
			{
				$ed = count($xml[$key]);
				for($i=0;$i<$ed;$i++)
				{
					if($air_code == $xml[$key][$i]['air_code'])
					{
						$flag = true;
						break;
					}
				}

				if($flag == false)
				{
					if($key == "101" || $key == "130"){
						$xml['101_130'][] = array("air_code"=>$csvarr[4],"air_name"=>$air_name);
					}
					$xml[$key][] = array("air_code"=>$csvarr[4],"air_name"=>$air_name);
				}
			}
			else
			{
				if($key == "101" || $key == "130"){
					$xml['101_130'][] = array("air_code"=>$csvarr[4],"air_name"=>$air_name);
				}
				$xml[$key][] = array("air_code"=>$csvarr[4],"air_name"=>$air_name);
			}
		}
	}
	ksort($xml);

//print_r($xml);


	//CSVファイルを閉じる
	fclose($file);

	$xmldoc_ab_tour_carr_list01 = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xmldoc_ab_tour_carr_list01 .= "<root>\r\n";
	if(!empty($xml))
	{
		$xmldoc_ab_tour_carr_list01 .= "    <!-- root -->\r\n";
	}

	foreach($xml as $key=>$value)
	{
		$xmldoc_ab_tour_carr_list01 .= "    <dep".$key.">\r\n";
//		$xmldoc_ab_tour_carr_list01 .= "    <!-- dep -->\r\n";

		for($i=0;$i<count($value);$i++)
		{
			$xmldoc_ab_tour_carr_list01 .= "    	<air air_code=\"".$value[$i]['air_code']."\" air_name=\"".$value[$i]['air_name']."\" />\r\n";
		}

		$xmldoc_ab_tour_carr_list01 .= "    </dep".$key.">\r\n";
	}

	$xmldoc_ab_tour_carr_list01 .= "</root>\r\n";

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."01.xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xmldoc_ab_tour_carr_list01);		//write
	fclose($file);

	return true;
}

/*
 * 関数名：CheckExists_ab_tour_carr_list01
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_ab_tour_carr_list01($src_line)
{
	global $jyogailist_ab_tour_carr_list01,$max_size_ab_tour_carr_list01;

	$exitedflg = false;

	$end = count($jyogailist_ab_tour_carr_list01);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_tour_carr_list01[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_ab_tour_carr_list01; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_ab_tour_carr_list01)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_ab_tour_carr_list01
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_ab_tour_carr_list01($filename)
{
	global $jyogailist_ab_tour_carr_list01,$csvdir,$max_size_ab_tour_carr_list01;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_ab_tour_carr_list01)
		{
			$jyogailist_ab_tour_carr_list01[] = $csvarr;
		}
	}
}
?>