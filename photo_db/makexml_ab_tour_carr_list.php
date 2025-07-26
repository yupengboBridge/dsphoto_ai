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
$p_carr_code_arr_ab = array();
//除外リスト
$jyogailist_ab_tour_carr_list = array();
$flag_ab_tour_carr_list = false;
$max_size_ab_tour_carr_list = 6;

//$flag_ab_tour_carr_list = MakeXml_ab_tour_carr_list("ab_tour_carr_list.csv");
//if($flag_ab_tour_carr_list)
//{
//	print "ＸＭＬを出力しました！\r\n";
//}

/*
 * 関数名：MakeXml_ab_tour_carr_list
 * 関数説明：ｘｍｌを出力する（ab_tour_carr_list）
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function MakeXml_ab_tour_carr_list($filename)
{
	global $csvdir,$xmldir,$max_size_ab_tour_carr_list;

	if(!is_file($csvdir.$filename)) return;

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_tour_carr_list($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	$row = 1;
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		if(is_array($csvarr)){  
			$cnt = count($csvarr); 
		}else{
			$cnt = 0;
		}
		if($cnt > 0)
		{
			if($cnt == $max_size_ab_tour_carr_list)
			{
				$exists_flg = CheckExists_ab_tour_carr_list($csvarr);
				if($exists_flg)
				{
					continue;
				}

				$carrname = mb_convert_kana($csvarr[5],"KV","utf-8");	//⇒  mb_convert_kana K :「半角(ﾊﾝｶｸ)片仮名」を「全角片仮名」に変換
				$carrname = str_replace("&","＆",$carrname);
				
				if($row == 1)
				{
					$p_carr_code_arr_ab[] = $csvarr[4];
					$xml .= "   <A".$csvarr[4]."  carrname=\"".$carrname."\" />\r\n";
				}
				else
				{
					$p_carr_code01 = $csvarr[4];

					$p_carr_num = count($p_carr_code_arr_ab);
					for($i = 0; $i < $p_carr_num; $i++)
					{
						if($p_carr_code01 == $p_carr_code_arr_ab[$i])
						{
							$flag_ab_tour_carr_list = true;
							break;
						}
					}

					if($flag_ab_tour_carr_list == false)
					{
						$xml .= "   <A".$csvarr[4]."  carrname=\"".$carrname."\" />\r\n";
						$p_carr_code_arr_ab[] = $csvarr[4];
					}
					$flag_ab_tour_carr_list = false;
				}
			}
		}
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
 * 関数名：CheckExists_ab_tour_carr_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_ab_tour_carr_list($src_line)
{
	global $jyogailist_ab_tour_carr_list,$max_size_ab_tour_carr_list;

	$exitedflg = false;

	$end = count($jyogailist_ab_tour_carr_list);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_tour_carr_list[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_ab_tour_carr_list; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_ab_tour_carr_list)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_ab_tour_carr_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_ab_tour_carr_list($filename)
{
	global $jyogailist_ab_tour_carr_list,$csvdir,$max_size_ab_tour_carr_list;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_ab_tour_carr_list)
		{
			$jyogailist_ab_tour_carr_list[] = $csvarr;
		}
	}
}
?>