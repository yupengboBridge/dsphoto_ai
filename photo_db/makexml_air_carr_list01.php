<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//$csvdir = "./csv/";
//$xmldir = "./xml/";
$xml_size_air_carr_list01 = array();
$xmldoc_size_air_carr_list01 = "";
//除外リスト
$jyogailist_air_carr_list01 = array();
$max_size_air_carr_list01 = 10;

//$flag = MakeXml_air_carr_list("air_carr_list.csv");
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
function MakeXml_air_carr_list01($filename)
{
	global $csvdir,$xml_size_air_carr_list01,$xmldir,$xmldoc_size_air_carr_list01;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_air_carr_list01($filename01);

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

	//繰り返し
	while(! feof($file))
	{
		$flag = false;
		$csvarr = (fgetcsv($file));

		$exists_flg = CheckExists_air_carr_list01($csvarr);
		if($exists_flg)
		{
			continue;
		}

		$key = $csvarr[0];
		$air_code = $csvarr[8];
		$air_name = $csvarr[9];

		if(!empty($key))
		{
			if(isset($xml_size_air_carr_list01[$key]))
			{
				$ed = count($xml_size_air_carr_list01[$key]);
				for($i=0;$i<$ed;$i++)
				{
					if($air_code == $xml_size_air_carr_list01[$key][$i]['air_code'])
					{
						$flag = true;
						break;
					}
				}

				if($flag == false)
				{
					$tmpstr = mb_convert_kana($csvarr[9],"KV","utf-8");
					$tmpstr = str_replace("&","＆",$tmpstr);
					$xml_size_air_carr_list01[$key][] = array("air_code"=>$csvarr[8],"air_name"=>$tmpstr);
				}
			}
			else
			{
				$tmpstr = mb_convert_kana($csvarr[9],"KV","utf-8");
				$tmpstr = str_replace("&","＆",$tmpstr);
				$xml_size_air_carr_list01[$key][] = array("air_code"=>$csvarr[8],"air_name"=>$tmpstr);
			}
		}
	}

	//CSVファイルを閉じる
	fclose($file);

	$xmldoc_size_air_carr_list01 = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xmldoc_size_air_carr_list01 .= "<root>\r\n";

	foreach($xml_size_air_carr_list01 as $key=>$value)
	{
		$xmldoc_size_air_carr_list01 .= "    <".$key.">\r\n";

		for($i=0;$i<count($value);$i++)
		{
			$xmldoc_size_air_carr_list01 .= "    	<air air_code=\"".$value[$i]['air_code']."\" air_name=\"".$value[$i]['air_name']."\" />\r\n";
		}

		$xmldoc_size_air_carr_list01 .= "    </".$key.">\r\n";
	}

	$xmldoc_size_air_carr_list01 .= "</root>\r\n";

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."01.xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xmldoc_size_air_carr_list01);		//write
	fclose($file);

	return true;
}

/*
 * 関数名：CheckExists_air_carr_list01
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_air_carr_list01($src_line)
{
	global $jyogailist_air_carr_list01,$max_size_air_carr_list01;

	$exitedflg = false;

	$end = count($jyogailist_air_carr_list01);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_air_carr_list01[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_air_carr_list01; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_air_carr_list01)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_air_carr_list01
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_air_carr_list01($filename)
{
	global $jyogailist_air_carr_list01,$csvdir,$max_size_air_carr_list01;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_air_carr_list01)
		{
			$jyogailist_air_carr_list01[] = $csvarr;
		}
	}
}
?>