<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";
//$csvdir = "../csv/";
//$xmldir = "../";
//$xmldir_old_hotel = "/home2/chroot/home/xhankyu/public_html/photo_db/hotelxml";
$xmldir_hotel = "hotel/";
$xmldir_old_hotel = "./hotelxml";



$xml_arr = array();
$hotel_arr = array();
$flag_ab_hotel_list = false;
$p_hatsu_arr = array();
//除外リスト
$jyogailist_ab_hotel_list = array();
//元々XMLファイル名
$old_xmlfile_list = array();

$max_size_ab_hotel_list = 12;

//$flag_ab_hotel_list = MakeXml_ab_hotel_list("ab_hotel_list.csv");
//if($flag_ab_hotel_list)
//{
//	print "ＸＭＬを出力しました！\r\n";
//}

function get_hatsu_ab_hotel_list()
{
	global $p_hatsu_arr,$xmldir;

	if(!is_file($xmldir."i_p_hatsu_url_v2.xml")) return;

	$xml = simplexml_load_file($xmldir."i_p_hatsu_url_v2.xml");

	foreach($xml as $key=>$node)
	{
		$p_h_code = htmlentities($node->attributes()->p_hatsucode, ENT_QUOTES, "utf-8");
		$key01 = substr($key,1);
		$p_hatsu_arr[$key01] = $p_h_code;
	}
}

/**
 * 指定のディレクトリにXMLファイル名を取得する
 * @param $dirName　ディレクトリ
 * @return 無し
 */
function getXMLFileList($dirName)
{
	global $old_xmlfile_list;

	if ( $handle = opendir( $dirName ) )
	{
		while ( false !== ( $item = readdir( $handle ) ) )
		{
			if ( $item != "." && $item != ".." )
			{
				if ( is_dir( $dirName."/".$item ) )
				{
					//処理しない
				} else {
					$old_xmlfile_list[] = $item;
				}
			}
		}
		closedir( $handle );
	}
}

/**
 * 指定のディレクトリにXMLファイルを削除する
 * @param $dirName　ディレクトリ
 * @return 無し
 */
function delFile_ab_hotel_list( $dirName )
{
  if ( $handle = opendir( $dirName ) ) {
   while ( false !== ( $item = readdir( $handle ) ) ) {
     if ( $item != "." && $item != ".." ) {
       if ( is_dir( $dirName."/".$item ) ) {
         //delDirAndFile( $dirName/$item );
       } else {
         unlink( $dirName."/".$item );
       }
     }
   }
   closedir( $handle );
  }
}


/*
 * 関数名：MakeXml_ab_hotel_list
 * 関数説明：ｘｍｌを出力する（ホテル）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_ab_hotel_list($filename)
{
	global $csvdir,$xmldir,$xmldir_hotel,$xml_arr,$hotel_arr,$flag_ab_hotel_list,$p_hatsu_arr;
	global $xmldir_old_hotel,$old_xmlfile_list;

	if(!is_file($csvdir.$filename)) return;

	getXMLFileList($xmldir_old_hotel);
	delFile_ab_hotel_list($xmldir.$xmldir_hotel);

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_hotel_list($filename01);

	get_hatsu_ab_hotel_list();

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");

	//もし最後の行ではない場合
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
//print_r($jyogailist_ab_hotel_list);
		$exists_flg = CheckExists_ab_hotel_list($csvarr);
		if($exists_flg)
		{

//print_r($csvarr);
//print "<br/>";
			continue;
		}

		$flag_ab_hotel_list = false;
		$hatsu_code = $csvarr[0];
		if(!isset($p_hatsu_arr[$hatsu_code]))
		{
			continue;
		}
		
		//$hatsu_code = $p_hatsu_arr[$hatsu_code];
		$country_code = $csvarr[6];
		$city_code = $csvarr[8];
		$hotel_code = $csvarr[10];
//		$hotel_name = $csvarr[11];
		$hotel_name = mb_convert_kana($csvarr[11],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
		$hotel_name = str_replace("&","＆",$hotel_name);
		
		if(!empty($hotel_code))
		{
			
		
			if(empty($hotel_arr[$country_code]))
			{
				if($hatsu_code == "101" || $hatsu_code == "130"){
					$hatsu_code_tyo = '101_130';
					$hotel_arr[$country_code][$city_code.$hatsu_code_tyo] = $hatsu_code_tyo.$hotel_code;
					$xml_arr[$country_code][$city_code.$hatsu_code_tyo] = "	<h".$city_code.$hatsu_code_tyo." hotel_code=\"".$hotel_code."\" hotel_name=\"".$hotel_name."\" />";
				}

				$hotel_arr[$country_code][$city_code.$hatsu_code] = $hatsu_code.$hotel_code;
				$xml_arr[$country_code][$city_code.$hatsu_code] = "	<h".$city_code.$hatsu_code." hotel_code=\"".$hotel_code."\" hotel_name=\"".$hotel_name."\" />";
			}
			else
			{
				$ed = count($hotel_arr[$country_code]);
				for($i=0;$i<$ed;$i++)
				{
					if($hatsu_code.$hotel_code == $hotel_arr[$country_code][$i])
					{
						$flag_ab_hotel_list = true;
						break;
					}
				}

				if($flag_ab_hotel_list == false)
				{
					if($hatsu_code == "101" || $hatsu_code == "130"){
						$hatsu_code_tyo = '101_130';
						$hotel_arr[$country_code][$city_code.$hatsu_code_tyo] = $hatsu_code_tyo.$hotel_code;
						$xml_arr[$country_code][$city_code.$hatsu_code_tyo] = "	<h".$city_code.$hatsu_code_tyo." hotel_code=\"".$hotel_code."\" hotel_name=\"".$hotel_name."\" />";
					}

					$hotel_arr[$country_code][$city_code.$hatsu_code] = $hatsu_code.$hotel_code;
					$xml_arr[$country_code][$city_code.$hatsu_code] = "	<h".$city_code.$hatsu_code." hotel_code=\"".$hotel_code."\" hotel_name=\"".$hotel_name."\" />";
				}
			}
		}
	}
ksort($xml_arr);

	fclose($file);		//先開いたのCSVファイルを閉じます

	foreach($xml_arr as $key=>$value)
	{
		$filename = $key.".xml";
		$xml = "";
		if(isset($old_xmlfile_list))
		{
			if(!empty($old_xmlfile_list))
			{
				foreach($old_xmlfile_list as $key1 => $value1)
				{
					if($value1==$filename)
					{
						unset($old_xmlfile_list[$key1]);
					} else {
//						print "value1>>".$value1;
//						print "\r\n";
//						print "filename>>".$filename;
//						print "\r\n";x
					}
				}
			}
		}
		$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
		$xml .= "<root>\r\n";
		//$em = count($value);
		//for($i=0;$i<$em;$i++)
		foreach($value as $valuedata)
		{
			$xml .= $valuedata."\r\n";
		}
		$xml .= "</root>\r\n";

		$file = fopen($xmldir.$xmldir_hotel.$filename,"w");
		fwrite($file,$xml);		//write
		fclose($file);			//close
	}

//	print_r($old_xmlfile_list);

	if(isset($old_xmlfile_list))
	{
		if(!empty($old_xmlfile_list))
		{
			foreach($old_xmlfile_list as $key => $value)
			{
				$filename = $value;
				$file = fopen($xmldir.$xmldir_hotel.$filename,"w");
				$WriteFile = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
				$WriteFile .= "<root>\r\n";
				$WriteFile .= "</root>\r\n";
				fwrite($file,$WriteFile);		//write
				fclose($file);			//close
			}
		}
	}

	return true;
}

/*
 * 関数名：CheckExists_ab_hotel_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_ab_hotel_list($src_line)
{
	global $jyogailist_ab_hotel_list,$max_size_ab_hotel_list;

	$exitedflg = false;

	$end = count($jyogailist_ab_hotel_list);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_hotel_list[$i];
		for($j = 0; $j < $max_size_ab_hotel_list; $j++)
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
 * 関数名：LoadJyogaiCSV_ab_hotel_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_ab_hotel_list($filename)
{
	global $jyogailist_ab_hotel_list,$csvdir;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		if($csvarr == false) return;
		if(count($csvarr) > 0)
		{
			$jyogailist_ab_hotel_list = $jyogailist_ab_hotel_list + $csvarr;
		}
	}
}
?>