<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
$csvdir = "./csv/";
$xmldir = "./xml/";

//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";

$xmldata_ab_destination_list_full = array();

//除外リスト
$jyogailist_ab_destination_list_full = array();
$max_size_ab_destination_list_full = 10;


//$flag = MakeXml_ab_destination_list_para("ab_destination_list.csv");
//if($flag)
//{
//	print "ＸＭＬを出力しました！\n";
//}


/*やってみるよー*/
MakeXml_ab_destination_list_para("ab_destination_list.csv");

function MakeXml_ab_destination_list_para($filename)
{
	global $csvdir,$xmldir,$xmldata_ab_destination_list_full;


	if(!is_file($csvdir.$filename)) return;


	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_destination_list_full($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");

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
		//方面は先に入れちゃう
		$destkey = $discode . '--';
		$csvAry[$destkey] = $disname;

		//国も先に入れちゃう
		$statetkey = $discode . '-' . $cntrcode . '-';
		$csvAry[$statetkey] = $disname . '-' . $cntrname;

		//都市を入れる
		
		//都市
			$key = $discode . '-' . $cntrcode . '-' . $citycode;
			$name = $disname . '-' . $cntrname . '-' . $cityname;
			$csvAry[$key] = $name;

	}
	fclose($file);

	ksort($csvAry);
	$forWriteData = "";
	//1行目はタイトル行のため省く
	foreach($csvAry as $key => $data){
		$forWriteData .= $key . "\t" . $data . "\n";
	}

	//CSV出力
	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_para.csv";
	$file = fopen($csvdir.$filename01,"w");
	fwrite($file,$forWriteData);		//write
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