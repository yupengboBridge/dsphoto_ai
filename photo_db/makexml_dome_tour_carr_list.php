<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//$csvdir = "./csv/";
//$xmldir = "./xml/";
$xml_dome_tour_carr_list = array();
$xmldoc_dome_tour_carr_list = "";

//除外リスト
$jyogailist_dome_tour_carr_list = array();
$max_size_dome_tour_carr_list = 2;

/*
$flag = MakeXml_dome_tour_carr_list("dome_tour_carr_list.csv");
if($flag)
{
	print "ＸＭＬを出力しました！\r\n";
}

*/

/*
 * 関数名：MakeXml_dome_tour_carr_list
 * 関数説明：ｘｍｌを出力する（検索海外用　検索条件：出発地、方面、国、都市）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_dome_tour_carr_list($filename)
{
	global $csvdir,$xmldir,$xml_dome_tour_carr_list;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_dome_tour_carr_list($filename01);

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

	//繰り返し
	while(! feof($file))
	{
		$flag = false;
		$csvarr = (fgetcsv($file));

		$exists_flg = CheckExists_dome_tour_carr_list($csvarr);
		if($exists_flg)
		{
			continue;
		}

		$air_code = $csvarr[4];
		$air_name = mb_convert_kana($csvarr[5],"KV","utf-8");
		$air_name = str_replace("&","＆",$air_name);

//		$xml_dome_tour_carr_list[] = array("air_code"=>$air_code,"air_name"=>$air_name);
		$xml_dome_tour_carr_list[$air_code] = $air_name;

	}

	//CSVファイルを閉じる
	fclose($file);

	//ソート（暫定）
	ksort($xml_dome_tour_carr_list);
	
	$xmldoc_dome_tour_carr_list = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xmldoc_dome_tour_carr_list .= "<root>\r\n";


	foreach($xml_dome_tour_carr_list as $air_code => $air_name)
	{
		 if(empty($air_name)){
		 	continue;		 
		 }
		$xmldoc_dome_tour_carr_list .= "    <air air_code=\"".$air_code."\" air_name=\"".$air_name."\" />\r\n";
	}

	$xmldoc_dome_tour_carr_list .= "</root>\r\n";

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xmldoc_dome_tour_carr_list);		//write
	fclose($file);			//close

	return true;
}


/*
	//ここから旧　いつの間にCSVの仕様が変わったのか謎
	for($i=0;$i<count($xml_dome_tour_carr_list);$i++)
	{
		$xmldoc_dome_tour_carr_list .= "    <air air_code=\"".$xml_dome_tour_carr_list[$i]['air_code']."\" air_name=\"".$xml_dome_tour_carr_list[$i]['air_name']."\" />\r\n";
	}

	$xmldoc_dome_tour_carr_list .= "</root>\r\n";

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xmldoc_dome_tour_carr_list);		//write
	fclose($file);			//close

	return true;
}
	//ここまで旧　いつの間にCSVの仕様が変わったのか謎
*/
/*
 * 関数名：CheckExists_dome_tour_carr_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_dome_tour_carr_list($src_line)
{
	global $jyogailist_dome_tour_carr_list,$max_size_dome_tour_carr_list;

	$exitedflg = false;

	$end = count($jyogailist_dome_tour_carr_list);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_dome_tour_carr_list[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_dome_tour_carr_list; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_dome_tour_carr_list)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_dome_tour_carr_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_dome_tour_carr_list($filename)
{
	global $jyogailist_dome_tour_carr_list,$csvdir,$max_size_dome_tour_carr_list;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_dome_tour_carr_list)
		{
			$jyogailist_dome_tour_carr_list[] = $csvarr;
		}
	}
}
?>