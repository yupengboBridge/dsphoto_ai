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

$xmldata_dome_destination_list = array();

//除外リスト
$jyogailist_dome_destination_list = array();
$max_size_dome_destination_list = 10;
$d_p_hatsu_pull_ary = array();
$dome_destination_ary = array();

//定数定義
define("D_P_HATUS_URL", "d_p_hatsu_url_v3.csv");
define("SIZE_D_P_HATUS_URL", 12);
define("DOME_DESTINATION_LIST", "dome_destination_list.csv");
define("SIZE_DOME_DESTINATION_LIST", 10);
/*
$flag = MakeXml_dome_destination_list("dome_destination_list.csv");
if($flag)
{
	print "ＸＭＬを出力しました！\r\n";
}
*/
function MakeXml_dome_destination_list($filename)
{
	global $csvdir,$xmldir,$xmldata_dome_destination_list;
	global $d_p_hatsu_pull_ary,$dome_destination_ary;

	//プルダウン順の配列を生成　[sortnum] - [p_hatsucode] - [hatsucode_sub_hbos]
	$d_p_hatsu_pull_ary = Make_Group_List();
	
	//先にファイル内容を入れる
	$dome_destination_ary = Read_dome_destination_list();

	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	foreach($dome_destination_ary as $sortKey => $value){
	
		if($value['hatsusubs']){
			$depcode = $value['hatsusubs'];					//出発地コード(p_hatsu_sub群)
		}
		if($value['hatsu_name']){
			$depname =$value['hatsu_name'];					//出発地名(方面＋都道府県名)
		}

		foreach($value as $valKey => $data)	{

			if(strpos($valKey,'hatsusubs') !== false){
				$depcode = $data;					//出発地コード(p_hatsu_sub群)
			}
			elseif(strpos($valKey,'hatsu_name') !== false){
				$depname =$data;					//出発地名(方面＋都道府県名)
			}
			else{
				//分割処理
				$data_p_Ary = explode('_',$data);
				$discode = $data_p_Ary[0];
				$disname = !empty($data_p_Ary[1])?$data_p_Ary[1]:"";
				$precode = !empty($data_p_Ary[2])?$data_p_Ary[2]:"";
				$prename = !empty($data_p_Ary[3])?$data_p_Ary[3]:"";
			}
			

				//if($xmldata_dome_destination_list[$depcode])
			if(isset($xmldata_dome_destination_list[$sortKey])){
				//if($xmldata_dome_destination_list[$depcode][$discode])
				if(isset($xmldata_dome_destination_list[$sortKey][$discode])){
					//if($xmldata_dome_destination_list[$depcode][$discode][$precode])
					if(isset($xmldata_dome_destination_list[$sortKey][$discode][$precode])){
					}
					else{
						$xmldata_dome_destination_list[$sortKey][$discode][$precode] = array(
												'prefecture_code'=>$precode,
												'prefecture_name'=>$prename,
									);
					}
				}
				else{
					$xmldata_dome_destination_list[$sortKey][$discode] = array(
										'district_code'=>$discode,
										'district_name'=>$disname,
									);
					$xmldata_dome_destination_list[$sortKey][$discode][$precode] = array(
												'prefecture_code'=>$precode,
												'prefecture_name'=>$prename,
									);
				}
			}
			else{
			
				$xmldata_dome_destination_list[$sortKey] = array(
									'place_departure_code'=>$depcode,
									'place_departure_name'=>$depname
									);
				$xmldata_dome_destination_list[$sortKey][$discode] = array(
										'district_code'=>$discode,
										'district_name'=>$disname,
									);
				$xmldata_dome_destination_list[$sortKey][$discode][$precode] = array(
												'prefecture_code'=>$precode,
												'prefecture_name'=>$prename,
									);
			}
	
			if(empty($xmldata_dome_destination_list))
			{

				//出発地
				$xmldata_dome_destination_list[$sortKey] = array(
									'place_departure_code'=>$depcode,
									'place_departure_name'=>$depname
									);
				$xmldata_dome_destination_list[$sortKey][$discode] = array(
										'district_code'=>$discode,
										'district_name'=>$disname,
									);
				$xmldata_dome_destination_list[$sortKey][$discode][$precode] = array(
												'prefecture_code'=>$precode,
												'prefecture_name'=>$prename,
									);
			}
		}
	}

	$i = 0;

	foreach($xmldata_dome_destination_list as $key=>$value)
	{
		if($i == 0)
		{
			$xml .= "    <departure place_departure_code=\"".$value['place_departure_code']."\" place_departure_name=\"".$value['place_departure_name']."\">\r\n";

			foreach($value as $key01=>$value01)
			{
				if(is_array($value01) && !empty($value01['district_code']))
				{
						$xml .= "        <district district_code=\"".$value01['district_code']."\" district_name=\"".$value01['district_name']."\">\r\n";
					foreach($value01 as $key02=>$value02)
					{
						if(is_array($value02) && !empty($value02['prefecture_code']))
						{
							$xml .= "            <prefecture  prefecture_code=\"".$value02['prefecture_code']."\" prefecture_name=\"".$value02['prefecture_name']."\" />\r\n";
						}
					}
					$xml .="        </district>\r\n";
				}
			}
			$i++;
		}
		else if(!empty($value['place_departure_code']))
		{
    		$xml .="     </departure>\r\n";

			$xml .= "    <departure place_departure_code=\"".$value['place_departure_code']."\" place_departure_name=\"".$value['place_departure_name']."\">\r\n";

			foreach($value as $key01=>$value01)
			{
				if(is_array($value01)  && !empty($value01['district_code']))
				{
					$xml .= "        <district district_code=\"".$value01['district_code']."\" district_name=\"".$value01['district_name']."\">\r\n";

					foreach($value01 as $key02=>$value02)
					{
						if(is_array($value02) && !empty($value02['prefecture_code']))
						{
							$xml .= "            <prefecture  prefecture_code=\"".$value02['prefecture_code']."\" prefecture_name=\"".$value02['prefecture_name']."\" />\r\n";
						}
					}
					$xml .="        </district>\r\n";
				}
			}
		}
	}

	$xml .= "    </departure>\r\n";
	$xml .= "</root>\r\n";

	
	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);		//write
	fclose($file);			//close

	return true;
}

/*
 * 関数名：CheckExists_dome_destination_list
 * 関数説明：除外リストにあるかどうかチェックする
 * パラメタ：src_line　読み込みのCSVデータ
 * 戻り値：true/false　
 */
function CheckExists_dome_destination_list($src_line)
{
	global $jyogailist_dome_destination_list,$max_size_dome_destination_list;

	$exitedflg = false;

	$end = count($jyogailist_dome_destination_list);
	for($i = 0; $i < $end; $i++)
	{
		$csvarr = $jyogailist_dome_destination_list[$i];
		$cnt_field = 0;
		for($j = 0; $j < $max_size_dome_destination_list; $j++)
		{
			if($src_line[$j] == $csvarr[$j])
			{
				$cnt_field++;
			}
		}
		if($cnt_field == $max_size_dome_destination_list)
		{
			$exitedflg = true;
			break;
		}
	}

	return $exitedflg;
}

/*
 * 関数名：LoadJyogaiCSV_dome_destination_list
 * 関数説明：除外CSVを読み込む
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function LoadJyogaiCSV_dome_destination_list($filename)
{
	global $jyogailist_dome_destination_list,$csvdir,$max_size_dome_destination_list;

	if(!is_file($csvdir.$filename)) return;

	$file = fopen($csvdir.$filename,"rb");
	while(! feof($file))
	{
		$csvarr = (fgetcsv($file));				//行の内容は配列にする
		$cnt = count($csvarr);
		if($cnt == $max_size_dome_destination_list)
		{
			$jyogailist_dome_destination_list[] = $csvarr;
		}
	}
}
/*
 * 関数名：Make_Group_List
 * 関数説明：検索プルダウンの順番を生成する
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function Make_Group_List()
{
	global $csvdir;

	if(!is_file($csvdir. D_P_HATUS_URL)) return;
	$row = 0;
	$file = fopen($csvdir.D_P_HATUS_URL,"rb");
	while(! feof($file))
	{
		$data = (fgetcsv($file));				//行の内容は配列にする
		if(is_array($data)){  
			$cnt = count($data); 
		}else{
			$cnt = 0;
		}
		
		if($row < 1){
			$row++;
			continue;
		}
		if(!empty($data[11]))
		{
			//方面でかためる
			if(!empty($data[3])){
				$d_p_hatsu_dest_tmp[$data[9]][] = $data[3];
			}
			//ソート番号でかためる	
			$d_p_hatsu_pull_ary_tmp[$data[10]][] = $data;
		}
	}
	fclose($file);

	if(!is_array($d_p_hatsu_pull_ary_tmp)){
		return;	
	}
	
	foreach($d_p_hatsu_pull_ary_tmp as $sort_key => $val){
		
		$val_cnt = count($val);
		
		//都道府県で、p_hatsu_subをまとめる
		if($val_cnt > 1){
			foreach($val as $val_p){
				$key_name = $sort_key . '_' . $val_p[5];
				//$key_name = $sort_key;
				$d_p_hatsu_pull_ary[$key_name][] = $val_p[3];
			}
		}
		else{
			//方面は事前の配列を入れる
			if(empty($val[0][2])){	//方面の場合
				$key_name = $sort_key . '_' . $val[0][1];
				//$key_name =$sort_key;

				$d_p_hatsu_pull_ary[$key_name] = $d_p_hatsu_dest_tmp[$val[0][9]];
			}
			else{
				//都道府県はそのまま格納
				$key_name = $sort_key . '_' . $val[0][5];
				//$key_name = $sort_key;

				$d_p_hatsu_pull_ary[$key_name][] = $val[0][3];
			}
		}
	}
//	ksort($d_p_hatsu_url_ary);
	return $d_p_hatsu_pull_ary;
}


/*
 * 関数名：Read_dome_destination_list
 * 関数説明：
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function Read_dome_destination_list()
{
	global $csvdir,$d_p_hatsu_pull_ary;

	if(!is_file($csvdir. DOME_DESTINATION_LIST)) return;
	$row = 0;
	$file = fopen($csvdir. DOME_DESTINATION_LIST,"rb");
	while(! feof($file))
	{
		$data = (fgetcsv($file));				//行の内容は配列にする
		if(is_array($data)){  
			$cnt = count($data); 
		}else{
			$cnt = 0;
		}
		if($cnt !== SIZE_DOME_DESTINATION_LIST){
			continue;
		}
		$depcode = $data[2];
		$dest_code =  $data[4];
		$dest_name =  mb_convert_kana($data[5],"KVrn","utf-8");
		$pre_code =  $data[6];
		$pre_code =  mb_convert_kana($data[7],"KVrn","utf-8");
		
		$dome_destination_ary_tmp[$data[2]][] = $data[4] . '_' . $dest_name . '_' . $data[6] . '_' . $pre_code;
	}
	foreach($dome_destination_ary_tmp as $hatsusub => $val){
		$dome_destination_ary[$hatsusub] = array_unique($val);
	}
	fclose($file);

//print_r($dome_destination_ary);

	//ソート順にデータを収集
	foreach($d_p_hatsu_pull_ary as $sortnum => $hatsusub_ary){
		$hatsusubs = "";
		$sortnumAry = explode('_',$sortnum);
		$num = $sortnumAry[0];
		$name = $sortnumAry[1];
		
		if(is_array($hatsusub_ary)){
			foreach($hatsusub_ary as $hatsusub){
				if(!empty($dome_destination_ary[$hatsusub])){
					if(empty($hatsusubs)){
						$hatsusubs = $hatsusub;
					}else{
						$hatsusubs .= ',' . $hatsusub;
					}
					foreach( $dome_destination_ary[$hatsusub] as $key => $str ){
						$tmp[$num][] = $str;
					}
				}
				else{
					if(empty($hatsusubs)){
						$hatsusubs = $hatsusub;
					}else{
						$hatsusubs .= ',' . $hatsusub;
					}
				}
			}
			if(empty($tmp[$num][0])){
				$tmp[$num][0] = '';
			}

			if(is_array($tmp[$num])){
				$result = array_unique($tmp[$num]);
				$dome_tmp[$num] = array_unique($tmp[$num]);
				$dome_tmp[$num]['hatsusubs'] = $hatsusubs;
				$dome_tmp[$num]['hatsu_name'] = $name;
			}
		}	
	}
	return $dome_tmp;
}

?>