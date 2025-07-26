<?php
//ini_set( "display_errors", "Off");
header("Content-type: text/html; charset=UTF-8");
//ファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";

//$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
//$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";



$xmldata_ab_tour_carr_list_easy = array();

//除外リスト
$jyogailist_ab_tour_carr_list_easy = array();
$max_size_ab_tour_carr_list_easy = 10;


//$flag = MakeXml_ab_tour_carr_list("ab_tour_carr_list.csv");
//if($flag)
//{
//	print "ＸＭＬを出力しました！\n";
//}


/*やってみるよー*/
//MakeXml_ab_tour_carr_list_easy("ab_tour_carr_list.csv");




function MakeXml_ab_tour_carr_list_easy($filename)
{
	global $csvdir,$xmldir,$xmldata_ab_tour_carr_list_easy;


	if(!is_file($csvdir.$filename)) return;


	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_no.csv";
	LoadJyogaiCSV_ab_tour_carr_list_easy($filename01);

	//readonlyの方式でアップロッド（移動）したのCSVファイルを開く(オープンする)
	$file = fopen($csvdir.$filename,"rb");
	$row = 1;
	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
	$xml .= "<root>\n";

	//もし最後の行ではない場合
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$exists_flg = CheckExists_ab_tour_carr_list_easy($csvarr);
		if($exists_flg)
		{
			continue;
		}

		$carrcode = $csvarr[4];
		$carrname = mb_convert_kana($csvarr[5],"KVrn","utf-8");

		if(empty($carrcode)){
			continue;
		}

		if(!isset($xmldata_ab_tour_carr_list_easy[$carrcode])){
			$xmldata_ab_tour_carr_list_easy[$carrcode] = array(
				'p_carr_code'=>$carrcode,
				'p_carr_name'=>$carrname
			);
		}
	}

	ksort($xmldata_ab_tour_carr_list_easy);

	//XMLを書き出します

	foreach($xmldata_ab_tour_carr_list_easy as $carr){
		$xml .=<<<EOD
	<p_carr>
		<p_carr_code>{$carr['p_carr_code']}</p_carr_code>
		<p_carr_name>{$carr['p_carr_name']}</p_carr_name>
	</p_carr>

EOD;
	}
	$xml .= "</root>\n";


	fclose($file);		//先開いたのCSVファイルを閉じます


	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos)."_easy.xml";		//XMLファイル名を作成する

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
function CheckExists_ab_tour_carr_list_easy($src_line)
{
	global $jyogailist_ab_tour_carr_list_easy,$max_size_ab_tour_carr_list_easy;

	$exitedflg = false;

	$end = count($jyogailist_ab_tour_carr_list_easy);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_ab_tour_carr_list_easy[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_ab_tour_carr_list_easy; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_ab_tour_carr_list_easy)
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
function LoadJyogaiCSV_ab_tour_carr_list_easy($filename)
{
	global $jyogailist_ab_tour_carr_list_easy,$csvdir,$max_size_ab_tour_carr_list_easy;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_ab_tour_carr_list_easy)
		{
			$jyogailist_ab_tour_carr_list_easy[] = $csvarr;
		}
	}
}
?>