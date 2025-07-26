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
//define("D_P_HATUS_URL", "d_p_hatsu_url_v3.csv");
define("DOME_TOUR_BOARDING_PLACE_LIST", "dome_tour_boarding_place_list.csv");
//define("SIZE_D_P_HATUS_URL", 12);
//define("DOME_DESTINATION_LIST", "dome_destination_list.csv");
//define("SIZE_DOME_DESTINATION_LIST", 10);

/*
$flag = MakeXml_dome_tour_boarding_place_list("dome_tour_boarding_place_list.csv");
if($flag)
{
	print "ＸＭＬを出力しました！\r\n";
}
*/
function MakeXml_dome_tour_boarding_place_list($filename)
{
	global $csvdir,$xmldir,$xmldata_dome_destination_list;
	global $d_p_hatsu_pull_ary,$dome_destination_ary;
	$boarding_placeAry = array();
	
	if(!is_file($csvdir.$filename)) return;

	//プルダウン順の配列を生成　[sortnum] - [p_hatsucode] - [hatsucode_sub_hbos]
	$d_p_hatsu_pull_ary = Make_Group_List_boarding_place();
	if (is_array($d_p_hatsu_pull_ary)) {

		foreach($d_p_hatsu_pull_ary as $pref_key => $data){
			$str = '';	
			$keyNameTmp = explode('_',$pref_key);
			$keyName = $keyNameTmp[1];
			foreach($data as $num => $val){
			
				if(!empty($num) && strpos($num,'p_boarding_place') !== false){

				
					if(is_array($data['p_boarding_place'])){
						foreach($data['p_boarding_place'] as $key1 => $data1){
						
							if(is_array($data1)){
								foreach($data1 as $no1 => $data2){
									$boarding_place_code = $data2[0];
									$boarding_place_name = $data2[1];
									$prefecture_code = $data2[2];
									if(!empty($boarding_place_code)){
										$boarding_placeAry[$keyName][$boarding_place_code] = array('boarding_place_name'=>$boarding_place_name,'prefecture_code'=>$prefecture_code);
									}
								}
							}
						}
					}
				}
				elseif(is_numeric($num)){
					//数字はhatsu_sub
					if(!empty($val) && !empty($str)){
						$str .= ',' . $val;				
					}
					elseif(!empty($val)){
						$str .= $val;
					}
					if(!empty($str)){
							$boarding_placeAry[$keyName]['p_hatsu_sub'] = $str;
					}
				}
			}
		}
	}

	$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
	$xml .= "<root>\r\n";

	if(is_array($boarding_placeAry)){

		foreach($boarding_placeAry as $Key => $value){
			$hatsu_name = $Key;
			$p_hatsu_sub = $value['p_hatsu_sub'];
			
			$xml .= "    <departure hatsu_name =\"".$hatsu_name."\" p_hatsu_sub =\"".$p_hatsu_sub."\">\r\n";
			foreach($value as $Key2 => $value2){
				if(strpos($Key2,'p_hatsu_sub') !== false){
					continue;
				}
				$boarding_place_code = $Key2;
				$boarding_place_name = $value2['boarding_place_name'];
		
				$xml .= "        <district boarding_place_code =\"".$boarding_place_code."\" boarding_place_name =\"".$boarding_place_name."\" />\r\n";
			}
			$xml .="    </departure>\r\n";
		}
	}
	$xml .= "</root>\r\n";

	$pos = strpos($filename,".");
	$filename01 = substr($filename,0,$pos).".xml";		//XMLファイル名を作成する

	$file = fopen($xmldir.$filename01,"w");
	fwrite($file,$xml);		//write
	fclose($file);			//close

	return true;
}
/*
 * 関数名：Make_Group_List_boarding_place
 * 関数説明：検索プルダウンの順番を生成する
 * パラメタ：filename　CSVファイル名
 * 戻り値：無し　
 */
function Make_Group_List_boarding_place()
{
	global $csvdir;
	$p_boarding_place_ary_tmp = array();

//echo $csvdir. DOME_TOUR_BOARDING_PLACE_LIST;

	if(!is_file($csvdir. DOME_TOUR_BOARDING_PLACE_LIST)) return;
	
	$row = 0;
	$file = fopen($csvdir. DOME_TOUR_BOARDING_PLACE_LIST,"rb");
	while(! feof($file))
	{
		$data = (fgetcsv($file));				//行の内容は配列にする
		if(is_array($data)){  
			$cnt = count($data); 
		}else{
			$cnt = 0;
		}
		
		if(!empty($data[0]))
		{
			//ソート番号でかためる	
			$p_boarding_place_ary_tmp[$data[2]][] = $data;
		}
	}
	fclose($file);

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
				$d_p_hatsu_dest_tmp[$data[9]][] = $data[3];	//p_hatsu_sub
				$prefecture_code = sprintf("%02d",$data[12]);

				if(!empty($p_boarding_place_ary_tmp[$prefecture_code])){	
					$d_p_hatsu_dest_tmp[$data[9]]['p_boarding_place'][] = $p_boarding_place_ary_tmp[$prefecture_code];
				}
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
		$hatsu_prefecture_code = '';
	
//echo "都道府県コード= ";		
		//都道府県で、p_hatsu_subをまとめる
		if($val_cnt > 1){
			foreach($val as $val_p){
				$key_name = $sort_key . '_' . $val_p[5];
				//北海道の場合
				if($sort_key == 1){
					$hatsu_prefecture_code = sprintf("%02d",$val_p[12]);
				}
				else{
					$hatsu_prefecture_code = $val_p[12];
				}
				//$key_name = $sort_key;
				$d_p_hatsu_pull_ary[$key_name][] = $val_p[3];
				if(isset($p_boarding_place_ary_tmp[$hatsu_prefecture_code])){
					$d_p_hatsu_pull_ary[$key_name]['p_boarding_place'][] = $p_boarding_place_ary_tmp[$hatsu_prefecture_code];
				}
			}
		}
		else{
			//方面は事前の配列を入れる
			if(empty($val[0][2])){	//方面の場合

				$key_name = $sort_key . '_' . $val[0][1];
				//$key_name =$sort_key;
				$d_p_hatsu_pull_ary[$key_name] = $d_p_hatsu_dest_tmp[$val[0][9]];
				if(isset($d_p_hatsu_dest_tmp[$val[0][9]]['p_boarding_place'])){
					$d_p_hatsu_pull_ary[$key_name]['p_boarding_place'] = $d_p_hatsu_dest_tmp[$val[0][9]]['p_boarding_place'];
				}
			}
			else{
				//都道府県はそのまま格納
				$key_name = $sort_key . '_' . $val[0][5];
				//$key_name = $sort_key;
				$hatsu_prefecture_code = sprintf("%02d",$val[0][12]);
				$d_p_hatsu_pull_ary[$key_name][] = $val[0][3];
				if(!empty($p_boarding_place_ary_tmp[$hatsu_prefecture_code])){
					$d_p_hatsu_pull_ary[$key_name]['p_boarding_place'][] = $p_boarding_place_ary_tmp[$hatsu_prefecture_code];
				}
			}
		}
	}
//	print_r($d_p_hatsu_pull_ary);
//	ksort($d_p_hatsu_url_ary);
	return $d_p_hatsu_pull_ary;
}

?>