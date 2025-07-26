<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";
//$csvdir = "./csv/";
//$xmldir = "./xml/";
$p_carr_code_arr = array();
$flag_air_carr_list = false;
//除外リスト
$jyogailist_air_carr_list = array();
$max_size_air_carr_list = 10;

//$flag_air_carr_list = MakeXml_air_carr_list("air_carr_list.csv");
//if($flag_air_carr_list)
//{
//	print "ＸＭＬを出力しました！\r\n";
//}

/*
 * 関数名：MakeXml_air_carr_list
 * 関数説明：ｘｍｌを出力する（検索航空用　air航空会社）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_air_carr_list($filename)
{
	global $csvdir,$xmldir,$p_carr_code_arr,$flag_air_carr_list,$max_size_air_carr_list;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_air_carr_list($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	$row = 1;
	//もし最後の行ではない場合
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt > 0)
		{
			if($cnt == $max_size_air_carr_list)
			{
				$exists_flg = CheckExists_air_carr_list($csvarr);
				if($exists_flg)
				{
					continue;
				}

				if($row == 1)
				{
					$p_carr_code_arr[] = $csvarr[8];
					$tmpstr = mb_convert_kana($csvarr[9],"KV","utf-8");
					$tmpstr = str_replace("&","＆",$tmpstr);
					$xml .= "   <A".$csvarr[8]."  carrname=\"".$tmpstr."\" />\r\n";
				}
				else
				{
					$p_carr_code01 = $csvarr[8];

					$p_carr_num = count($p_carr_code_arr);
					for($i = 0; $i < $p_carr_num; $i++)
					{
						if($p_carr_code01 == $p_carr_code_arr[$i])
						{
							$flag_air_carr_list = true;
							break;
						}
					}

					$tmpstr = mb_convert_kana($csvarr[9],"KV","utf-8");
					$tmpstr = str_replace("&","＆",$tmpstr);
					if($flag_air_carr_list == false)
					{
						$xml .= "   <A".$csvarr[8]."  carrname=\"".$tmpstr."\" />\r\n";
						$p_carr_code_arr[] = $csvarr[8];
					}
					$flag_air_carr_list = false;
				}
			}
		}
		$row++;
	}
	$xml .= "</root>";

	fclose($file);		//先開いたのCSVファイルを閉じます

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);		//write
	fclose($file);			//close

	return true;
}

/*
 * 関数名：CheckExists_air_carr_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_air_carr_list($src_line)
{
	global $jyogailist_air_carr_list,$max_size_air_carr_list;

	$exitedflg = false;

	$end = count($jyogailist_air_carr_list);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_air_carr_list[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_air_carr_list; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_air_carr_list)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_air_carr_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_air_carr_list($filename)
{
	global $jyogailist_air_carr_list,$csvdir,$max_size_air_carr_list;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_air_carr_list)
		{
			$jyogailist_air_carr_list[] = $csvarr;
		}
	}
}
?>