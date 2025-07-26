<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";			//-------test
//$xmldir = "./xml/";			//-------test
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//除外リスト
$jyogailist_ab_h_list_name = array();
$max_size_ab_h_list_name = 12;



//MakeXml_ab_h_list_name("ab_hotel_list.csv");		//-------test

/*
 * 関数名：MakeXml_ab_h_list_name
 * 関数説明：ｘｍｌを出力する（出発地のXML(検索海外)）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_ab_h_list_name($filename)
{
	global $csvdir,$xmldir,$max_size_ab_h_list_name;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_h_list_name($filename01);

	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	$p_hotel_code_arr_tmp = array();
	$p_hotel_code01 = "";
	

	//繰り返し
	while(! feof($file))
	{
		$flag = false;
		//行の内容は配列にする
		$csvarr = (fgetcsv($file));
		if(is_array($csvarr)){  
			$cnt = count($csvarr); 
		}else{
			$cnt = 0;
		}

		$exists_flg = CheckExists_ab_h_list_name($csvarr);
		if($exists_flg)
		{
			continue;
		}

		if($cnt == $max_size_ab_h_list_name)
		{
			if($row == 1)
			{
				$str = mb_convert_kana($csvarr[11],"KV","UTF-8");
				$str = str_replace("&","＆",$str);
				$xml .= "   <h".$csvarr[10]."  hotelname=\"".$str."\" />\r\n";

				$tmpkey = "h".$csvarr[10];
				$p_hotel_code_arr_tmp[$tmpkey] = $str;
			}
			else
			{
				$tmpkey = "h".$csvarr[10];
				if(isset($p_hotel_code_arr_tmp[$tmpkey]))
				{
					continue;
				}
				else
				{
					$str = mb_convert_kana($csvarr[11],"KV","UTF-8");
					$str = str_replace("&","＆",$str);
					$xml .= "   <h".$csvarr[10]."  hotelname=\"".$str."\" />\r\n";

					$tmpkey = "h".$csvarr[10];
					$p_hotel_code_arr_tmp[$tmpkey] = $str;
				}
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
 * 関数名：CheckExists_ab_h_list_name
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_ab_h_list_name($arr)
{
	global $jyogailist_ab_h_list_name;

	$hotel_name = $arr[11];
	
	if(!empty($hotel_name))
	{
		if(in_array($hotel_name,$jyogailist_ab_h_list_name))
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

function CheckExists_ab_h_list_name_01($src_line)
{
	global $jyogailist_ab_h_list_name,$max_size_ab_h_list_name;

	$exitedflg = false;

	$end = count($jyogailist_ab_h_list_name);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_h_list_name[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_ab_h_list_name; $j++)
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
 * 関数名：LoadJyogaiCSV_ab_h_list_name
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_ab_h_list_name($filename)
{
	global $jyogailist_ab_h_list_name,$csvdir,$max_size_ab_h_list_name;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		if($csvarr == false) return;
		if(count($csvarr) > 0)
		{
			$jyogailist_ab_h_list_name = $jyogailist_ab_h_list_name + $csvarr;
		}
	}
}
?>