<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";

//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";

//除外リスト
$jyogailist_3letter_p_hei = array();
//フィールドのサイズ
$max_size_3letter_p_hei = 2;

//$flag = MakeXml_3letter_p_hei("3letter_p_hei.csv");
//if($flag)
//{
//	print "ＸＭＬを出力しました！\r\n";
//}

/*
 * 関数名：MakeXml_3letter_p_hei
 * 関数説明：ｘｍｌを出力する（カレンダー海外.カレンダー国内）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_3letter_p_hei($filename)
{
	global $csvdir,$xmldir,$max_size_3letter_p_hei;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_3letter_p_hei($filename01);
	
	//CSVファイルを読み込む
	$file = fopen($csvdir.$filename,"rb");

	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	//繰り返し
	while(! feof($file))
	{
		//行の内容は配列にする
		$csvarr = (fgetcsv($file));
		if(is_array($csvarr)){  
			$cnt = count($csvarr); 
		}else{
			$cnt = 0;
		}
		//一行目を飛び読む
		if($row != 1 && $cnt > 0)
		{
			if($cnt == $max_size_3letter_p_hei)
			{
				$exists_flg = CheckExists_3letter_p_hei($csvarr);
				if($exists_flg)
				{
					continue;
				}
				$xml .= "   <".$csvarr[0]."  p_hei=\"".$csvarr[1]."\"  />\r\n";
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
 * 関数名：CheckExists_3letter_p_hei
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_3letter_p_hei($src_line)
{
	global $jyogailist_3letter_p_hei,$max_size_3letter_p_hei;

	$exitedflg = false;

	$end = count($jyogailist_3letter_p_hei);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_3letter_p_hei[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_3letter_p_hei; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_3letter_p_hei)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_3letter_p_hei
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_3letter_p_hei($filename)
{
	global $jyogailist_3letter_p_hei,$csvdir,$max_size_3letter_p_hei;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_3letter_p_hei)
		{
			$jyogailist_3letter_p_hei[] = $csvarr;
		}
	}
}
?>