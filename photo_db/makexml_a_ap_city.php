<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";

$max_size_p_city_new = 3;
//MakeXml_p_ar_cityname("p_ar_cityname.csv");
/*
 * 関数名：MakeXml_p_ar_cityname
 * 関数説明：ｘｍｌを出力する（air(検索air)）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_a_ap_city($filename)
{
	global $csvdir,$xmldir,$max_size_p_city_new;

	if(!is_file($csvdir.$filename)) return;

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

		if($row != 1 && $cnt == $max_size_p_city_new)
		{
			$p_ar_cityname = mb_convert_kana($csvarr[2],"KVrn","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
			
			$xml .= "   <ar p_ar=\"$csvarr[0]\" p_citycode=\"$csvarr[1]\" p_ar_cityname=\"$csvarr[2]\" />\r\n";
		}

		$row++;
	}

	$xml .= "</root>";

	//CSVファイルを閉じる
	fclose($file);

	//XMLファイル名を作成する
	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";
//	$filename01 = "p_ar_cityname.xml";

	//XMLファイルに書き込む
	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);
	fclose($file);

	return true;
}

?>