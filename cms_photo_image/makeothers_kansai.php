<?php
########################################
#	/kansai用のxmlとjsを出力する
########################################
/*
出力先：
	others/kansai/xml
	others/kansai/js
*/


/*--------------------
	初期設定
----------------------*/
$xRootPath = '/home2/chroot/home/xhankyu/public_html/photo_db/';
//$xRootPath = '/home/xhankyu/public_html/photo_db/';
$MyMainDir = $xRootPath . 'others/kansai/';

/*ディレクトリ*/
$MyXmlDir = $MyMainDir . 'xml/';
$MyJsDir = $MyMainDir . 'js/';
$MyCsvDir = $xRootPath . 'csv/';

/*ファイル名*/
$FileNameAry = array(
	 'i' => array(	//海外
		 'HWI' => 'hawaii'
		,'MNA' => 'hawaii'
		,'BEU' => 'eur'
		,'AAS' => 'asia'
		,'DNU' => 'america'
		,'ESU' => 'america'
		,'FOC' => 'oceania'
		,'CAF' => 'africa'
		,'CHT' => 'africa'
	)
	,'d' => array(	//国内
		 '10' => 'hokkaido'
		,'11' => 'tohoku'
		,'12' => 'tohoku'
		,'21' => 'tohoku'
		,'13' => 'chubu'
		,'14' => 'chubu'
		,'16' => 'kinki'
		,'17' => 'chushikoku'
		,'18' => 'chushikoku'
		,'19' => 'kyushu'
		,'20' => 'okinawa'
	)
);


/*--------------------
	初期化
----------------------*/
/*やってみるよー*/
MakeKansaiList('ab_destination_list.csv');
MakeKansaiList('dome_destination_list.csv');



function MakeKansaiList($filename){
	global $MyXmlDir, $MyJsDir, $MyCsvDir, $FileNameAry;

	$FileNameFullPath = $MyCsvDir.$filename;
	//CSVファイルが無かったらサヨナラ
	if(!is_file($FileNameFullPath)){
		return;
	}

	//海外国内の判定
	if(strpos($filename, 'ab_') !== false){
		$Naigai = 'i';
		$FlgHatsu = '003';
	}
	elseif(strpos($filename, 'dome_') !== false){
		$Naigai = 'd';
		$FlgHatsu = '106';
	}
	else{	//国内外の判別が付かなかったらサヨナラ
		return;
	}

	$handle = fopen($FileNameFullPath, "r");
	if ($handle) {
		while (!feof($handle)) {
			$buffer = rtrim(fgets($handle, 9999));	//日本語ファイルはfgetcsv使うのやめておく
			if(!empty($buffer)){
				$data = explode(',', str_replace('"', '', $buffer));
				/* 大阪発以外はサヨナラ */
				if($data[0] != $FlgHatsu){
					continue;
				}
				/* 方面毎変数の作成 */
				//海外の場合
				if($Naigai == 'i'){
					$DestCode = $data[2];
					$CountryCode = $data[4];
					$CityCode = $data[6];
					if($DestCode == NULL || $CountryCode == NULL || $CityCode == NULL){	//形式チェック
						continue;
					}
					$PageName = $FileNameAry[$Naigai][$DestCode];
					$TgList[$PageName][$DestCode][$CountryCode]['Jname'] = mb_convert_kana($data[5],"KVrn","utf-8");
					$TgList[$PageName][$DestCode][$CountryCode][$CityCode] = mb_convert_kana($data[7],"KVrn","utf-8");
				}
				//国内の場合
				else{
					$DestCode = $data[4];
					$CountryCode = $data[6];
					$CityCode = $data[8];
					if($DestCode == NULL || $CountryCode == NULL || $CityCode == NULL){	//形式チェック
						continue;
					}
					$CityCode = $data[8];
					$PageName = $FileNameAry[$Naigai][$DestCode];
					$TgList[$PageName][$DestCode][$CountryCode]['Jname'] = mb_convert_kana($data[7],"KVrn","utf-8");
					$TgList[$PageName][$DestCode][$CountryCode][$CityCode] = mb_convert_kana($data[9],"KVrn","utf-8");
				}

			}
		}
		fclose($handle);
	}


	/*できあがったリストを回す*/
	if(!empty($TgList)){

		//JSだけALLデータ作る
		$JsDataForTop = 'CountryList_' .$Naigai. ' = new Object();' . "\n";	//国の初期化（トップ）
		$JsDataForTop .= 'CityList_' .$Naigai. ' = new Object();' . "\n";	//都市の初期化（トップ）
		$OutFileNameJForTop = $MyJsDir . $Naigai . '_ALL.js';

		foreach ($TgList as $PageNameOne => $ListData){
			//出力ファイル名
			$OutFileNameX = $MyXmlDir . $PageNameOne . '.xml';
			$OutFileNameJ = $MyJsDir . $PageNameOne . '.js';
			//内容の初期化
			$XmlData = $JsData = $JsData2 = $JsDataTopForTopCity = $JsDataTopForTop = $JsDataTop = NULL;
			$JsDataTop .= 'CityList = new Object();' . "\n";	//都市の初期化（各）

			foreach ($ListData as $Dest => $CountryAry) {
				//JSのオブジェクト宣言
				$JsDataTopForTop .= 'CountryList_' .$Naigai. '["' .$Dest. '"]=new Object();' . "\n";	//国[方面]の初期化（トップ）
				foreach($CountryAry as $CountryCode => $Detail){
					//XMLのデータ
					$XmlData .= '<country code="' . $CountryCode . '">' . $Detail['Jname'] . '</country>' . "\n";
					//JSのデータ
					$JsDataTopForTop .= 'CountryList_' .$Naigai. '["' .$Dest. '"]["' .$CountryCode. '"]="' .$Detail['Jname']. '";' . "\n";	//国データ（トップ）
					$JsDataTopForTopCity .= 'CityList_' .$Naigai. '["' .$CountryCode. '"]=new Object();' . "\n";	//都市[国]の初期化（トップ）
					$JsDataTop .= 'CityList["' .$CountryCode. '"]=new Object();' . "\n";	//都市[国]の初期化（各）
					foreach($Detail as $CityKey => $CityName){
						if($CityKey != 'Jname'){
							$JsData .= 'CityList["' .$CountryCode. '"]["' .$CityKey. '"]="' .$CityName. '";' . "\n";	//各
							$JsData2 .= 'CityList_' .$Naigai. '["' .$CountryCode. '"]["' .$CityKey. '"]="' .$CityName. '";' . "\n";	//トップ
						}
					}
				}
				$JsDataForTop .= $JsDataTopForTop . $JsDataTopForTopCity . $JsData2 . "\n\n";

			}

			//書き出し
			$XmlOutData =<<<EOD
<?xml version="1.0" encoding="UTF-8" ?>
<root>
$XmlData
</root>

EOD;
			$JsData = $JsDataTop . $JsData;
			if(!empty($XmlOutData)){
				WriteFile($XmlOutData, $OutFileNameX);
			}
			if(!empty($JsData)){
				WriteFile($JsData, $OutFileNameJ);
			}
		}

		if(!empty($JsDataForTop)){
			WriteFile($JsDataForTop, $OutFileNameJForTop);
		}
	}

}


function WriteFile($OutData, $OutFile){
	//ファイルオープン
	if (!$handle = fopen($OutFile, 'w')) {
		echo "Cannot open file ($OutFile)";
		exit;
	}
	// オープンしたファイルに値を書き込みます
	if (fwrite($handle, $OutData) === FALSE) {
		echo "Cannot write to file ($OutData)";
		exit;
	}
	fclose($handle);

}





function MakeXml_ab_destination_list_full($filename)
{
	global $csvdir,$xmldir,$xmldata_ab_destination_list_full;


	if(!is_file($csvdir.$filename)) return;


	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_destination_list_full($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");
	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	$xml .= "<root>\n";

	//もし最後の行ではない場合
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$exists_flg = CheckExists_ab_destination_list_full($csvarr);
		if($exists_flg)
		{
			continue;
		}

		$depcode = $csvarr[0];
		$depname = mb_convert_kana($csvarr[1],"KVrn","utf-8");
		$discode = $csvarr[2];
		$disname = mb_convert_kana($csvarr[3],"KVrn","utf-8");
		$cntrcode = $csvarr[4];
		$cntrname = mb_convert_kana($csvarr[5],"KVrn","utf-8");
		$citycode = $csvarr[6];
		$cityname = mb_convert_kana($csvarr[7],"KVrn","utf-8");

		if(empty($depcode)){
			continue;
		}

		if(!isset($xmldata_ab_destination_list_full[$depcode])){
			$xmldata_ab_destination_list_full[$depcode] = array(
				'place_departure_code'=>$depcode,
				'place_departure_name'=>$depname
			);
		}
		if(!isset($xmldata_ab_destination_list_full[$depcode][$discode])){
			$xmldata_ab_destination_list_full[$depcode][$discode] = array(
				'district_code'=>$discode,
				'district_name'=>$disname,
			);
		}
		if(!isset($xmldata_ab_destination_list_full[$depcode][$discode][$cntrcode])){
			$xmldata_ab_destination_list_full[$depcode][$discode][$cntrcode] = array(
				'country_code'=>$cntrcode,
				'country_name'=>$cntrname,
			);
		}
		if(!isset($xmldata_ab_destination_list_full[$depcode][$discode][$cntrcode][$citycode])){
			$xmldata_ab_destination_list_full[$depcode][$discode][$cntrcode][$citycode] = array(
				'city_code'=>$citycode,
				'city_name'=>$cityname,
			);
		}
	}

	//XMLを書き出します

	foreach($xmldata_ab_destination_list_full as $hatsu){
		//出発地
		$xml .= "\t<departure place_departure_code=\"".$hatsu['place_departure_code']."\" place_departure_name=\"".$hatsu['place_departure_name']."\">\n";

		//方面
		foreach($hatsu as $dest){
			if(is_array($dest)){
				$xml .= "\t\t\t<district district_code=\"".$dest['district_code']."\" district_name=\"".$dest['district_name']."\">\n";

				//国
				foreach($dest as $cntr){
					if(is_array($cntr)){
						$xml .= "\t\t\t\t<country  country_code=\"".$cntr['country_code']."\" country_name=\"".$cntr['country_name']."\">\n";

						//都市
						foreach($cntr as $city){
							if(is_array($city)){
								$xml .= "\t\t\t\t\t<city city_code=\"".$city['city_code']."\" city_name=\"".$city['city_name']."\" />\n";
							}
						}
						$xml .= "\t\t\t\t</country>\n";
					}
				}
				$xml .= "\t\t\t</district>\n";
			}
		}
		$xml .= "\t</departure>\n";
	}
	$xml .= "</root>\n";


	fclose($file);		//先開いたのCSVファイルを閉じます


	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_full.xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);		//write
	fclose($file);			//close

	return true;
}

/*
 * 関数名：CheckExists_ab_destination_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_ab_destination_list_full($src_line)
{
	global $jyogailist_ab_destination_list_full,$max_size_ab_destination_list_full;

	$exitedflg = false;

	$end = count($jyogailist_ab_destination_list_full);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_destination_list_full[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_ab_destination_list_full; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_ab_destination_list_full)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_ab_destination_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_ab_destination_list_full($filename)
{
	global $jyogailist_ab_destination_list_full,$csvdir,$max_size_ab_destination_list_full;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_ab_destination_list_full)
		{
			$jyogailist_ab_destination_list_full[] = $csvarr;
		}
	}
}
?>