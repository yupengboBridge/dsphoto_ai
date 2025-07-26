<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//$csvdir = './csv/';
//$xmldir = './xml/';
$p_carr_code_arr = array();
$flag_air_carr_list_full = false;
//除外リスト
$jyogailist_air_carr_list_full = array();
$max_size_air_carr_list_full = 10;

//$flag_air_carr_list_full = MakeXml_air_carr_list_full("air_carr_list.csv");
//if($flag_air_carr_list_full)
//{
//	print "ＸＭＬを出力しました！\r\n";
//}


/*
 * 関数名：MakeXml_air_carr_list_full
 * 関数説明：ｘｍｌを出力する（検索航空用　air航空会社）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_air_carr_list_full($filename)
{
	global $csvdir,$xmldir,$p_carr_code_arr,$flag_air_carr_list_full,$max_size_air_carr_list_full;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_air_carr_list_full($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	$AirDataAry = NULL;
	//もし最後の行ではない場合
	while(! feof($file)){
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt > 0)
		{
			if($cnt == $max_size_air_carr_list_full)
			{
				$exists_flg = CheckExists_air_carr_list_full($csvarr);
				if($exists_flg)
				{
					continue;
				}

				$hatsu = $csvarr[0];
				$dest = $csvarr[2];
				$country = $csvarr[4];
				$city = $csvarr[6];
				$code = $csvarr[8];
				$name = mb_convert_kana($csvarr[9], 'KV', 'UTF-8');

				$AirDataAry[$hatsu][$dest][$country][$city][$code] = str_replace('&', '&amp;', $name);

			}
		}
	}
	foreach($AirDataAry as $hatsu => $aryDest){
		$xml .= '<hatsu code="' . $hatsu .'">' . "\n";

		foreach($aryDest as $dest => $aryCountry){
			$xml .= '<dest code="' . $dest .'">' . "\n";

			foreach($aryCountry as $country => $aryCity){
				$xml .= '<country code="' . $country .'">' . "\n";

				foreach($aryCity as $city => $aryCode){
					$xml .= '<city code="' . $city .'">' . "\n";

					foreach($aryCode as $code => $name){
						$xml .=<<<EOD
<carr>
<code>$code</code>
<name>$name</name>
</carr>

EOD;
					}
					$xml .= "</city>\n";
				}
				$xml .= "</country>\n";
			}
			$xml .= "</dest>\n";
		}
		$xml .= "</hatsu>\n";
	}


	$xml .= "</root>";

	fclose($file);		//先開いたのCSVファイルを閉じます

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_full.xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);		//write
	fclose($file);			//close

	return true;
}

/*
 * 関数名：CheckExists_air_carr_list_full
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_air_carr_list_full($src_line)
{
	global $jyogailist_air_carr_list_full,$max_size_air_carr_list_full;

	$exitedflg = false;

	$end = count($jyogailist_air_carr_list_full);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_air_carr_list_full[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_air_carr_list_full; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_air_carr_list_full)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_air_carr_list_full
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_air_carr_list_full($filename)
{
	global $jyogailist_air_carr_list_full,$csvdir,$max_size_air_carr_list_full;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_air_carr_list_full)
		{
			$jyogailist_air_carr_list_full[] = $csvarr;
		}
	}
}
?>