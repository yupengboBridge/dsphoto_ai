<?php
require_once('./config.php');
require_once('./lib.php');

date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

// 変数を初期化します。
$msg = array();																// メッセージ
$err = false;																// エラー

$registration_id = array();													// 登録区分ID
$registration_name = array();												// 登録区分

$take_picture_time_id = array();											// 撮影時期１
$take_picture_time_name = array();
$take_picture_time2_id = array();											// 撮影時期2
$take_picture_time2_name = array();

//------分類1-------------------------------------------------------------------------
$classification_id1 = array();												// 分類ID
$classification_name1 = array();											// 分類
$direction_id1 = array();													// 方面ID
$direction_name1 = array();													// 方面
$country_prefecture_id1 = array();											// 国・都道府県ID
$country_prefecture_name1 = array();										// 国・都道府県
$place_id1 = array();														// 地名ID
$place_name1 = array();														// 地名

//------分類2-------------------------------------------------------------------------
$classification_id2 = array();												// 分類ID
$classification_name2 = array();											// 分類
$direction_id2 = array();													// 方面ID
$direction_name2 = array();													// 方面
$country_prefecture_id2 = array();											// 国・都道府県ID
$country_prefecture_name2 = array();										// 国・都道府県
$place_id2 = array();														// 地名ID
$place_name2 = array();														// 地名

//------分類3-------------------------------------------------------------------------
$classification_id3 = array();												// 分類ID
$classification_name3 = array();											// 分類
$direction_id3 = array();													// 方面ID
$direction_name3 = array();													// 方面
$country_prefecture_id3 = array();											// 国・都道府県ID
$country_prefecture_name3 = array();										// 国・都道府県
$place_id3 = array();														// 地名ID
$place_name3 = array();														// 地名

//------分類4-------------------------------------------------------------------------
$classification_id4 = array();												// 分類ID
$classification_name4 = array();											// 分類
$direction_id4 = array();													// 方面ID
$direction_name4 = array();													// 方面
$country_prefecture_id4 = array();											// 国・都道府県ID
$country_prefecture_name4 = array();										// 国・都道府県
$place_id4 = array();														// 地名ID
$place_name4 = array();														// 地名

//------分類5-------------------------------------------------------------------------
$classification_id5 = array();												// 分類ID
$classification_name5 = array();											// 分類
$direction_id5 = array();													// 方面ID
$direction_name5 = array();													// 方面
$country_prefecture_id5 = array();											// 国・都道府県ID
$country_prefecture_name5 = array();										// 国・都道府県
$place_id5 = array();														// 地名ID
$place_name5 = array();														// 地名

$category_id = array();														// カテゴリーID
$category_name = array();													// カテゴリー名

$range_id = array();														// 使用範囲ID
$range_name = array();														// 使用範囲名

$borrow_id = array();														// 写真入手元ID
$borrow_name = array();														// 写真入手元

$p_situation_id = array();													// 掲載状況ID
$p_situation_name = array();												// 掲載状況

$s_login_id = array_get_value($_SESSION,'login_id' ,"");
$s_login_name = array_get_value($_SESSION,'user_name' ,"");
$s_security_level = array_get_value($_SESSION,'security_level' ,"");
$comp_code = array_get_value($_SESSION,'compcode' ,"");
$s_group_id = array_get_value($_SESSION,'group' ,"");
$s_user_id = array_get_value($_SESSION,'user_id' ,"");

//// for Debug
//$s_user_id = 1;
//$s_login_name = "BUD管理者";
//$s_login_id = "admin";

//ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

$p_action = array_get_value($_REQUEST, 'p_action' ,"");							// アクション
// PhotoImageのインスタンスを生成します。
$pi = new PhotoImageDB ();
try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	$p_photo_id = $pi->get_photo_lastid($db_link);

	if (empty($p_photo_id)) return;

	// 画像の削除
	if ($p_action == "delete")
	{
		$p_photo_mno = "";
		$p_photo_name = "";
		//yupengbo add 2011/11/18 start
		$photo_explanation = "";
		$bud_photo_no = "";
		$registration_person = "";
		$date_from = "";
		$date_to = "";
		//yupengbo add 2011/11/18 end
		
		//yupengbo modify 2011/11/18 start
		$pi->get_photo_mno($db_link,$p_photo_id,$p_photo_mno,$p_photo_name,
		                    $photo_explanation,$bud_photo_no,$registration_person,
		                    $date_from,$date_to);
		//yupengbo modify 2011/11/18 end

		$okflg = $pi->delete_data($db_link, $p_photo_id);
		if ($okflg)
		{
			if (!empty($p_photo_mno) && !empty($p_photo_name))
			{
				//yupengbo modify 2011/11/18 start
				$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",".$p_photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$p_photo_name));
				$logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$bud_photo_no)).",";
				$logstr .= $date_from.",".$date_to.",1,".$registration_person."\r\n";
				//yupengbo modify 2011/11/18 end
			} else {
//				//yupengbo modify 2011/11/18 start
//				$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",申請中,".$p_photo_name;
//				$logstr .= ",".$photo_explanation.",".$bud_photo_no.",";
//				$logstr .= $date_from.",".$date_to.",1,".$registration_person."\r\n";
//				//yupengbo modify 2011/11/18 end
			}
			write_log_tofile($logstr);

			print "<script type=\"text/javascript\">";
			print "alert(\"画像を削除しました。\");";
			print "parent.bottom.location.href  = './register_image_input.php?initflg=2'";
			print "</script>";
		} else {
			print "<script type=\"text/javascript\">";
			print "alert(\"画像を削除することができません。\");";
			print "parent.bottom.location.href  = './register_image_input.php?initflg=2'";
			print "</script>";
		}
	} else {
		$pi->select_data($db_link,$p_photo_id);
		$pi->registration_classifications->select_data($db_link,$p_photo_id);

		$pi->get_take_picture_time($db_link,$take_picture_time_id,$take_picture_time_name);						// 撮影時期１
		$pi->get_take_picture_time2($db_link,$take_picture_time2_id,$take_picture_time2_name);					// 撮影時期2

		//------分類1-------------------------------------------------------------------------
		$pi->get_classification($db_link, $classification_id1, $classification_name1);							// 分類
		$pi->get_direction($db_link,$direction_id1,$direction_name1,$classification_id1);						// 方面
		$pi->get_country_prefecture($db_link,$country_prefecture_id1,$country_prefecture_name1,$direction_id1);	// 国・都道府県
		$pi->get_place($db_link,$place_id1,$place_name1,$country_prefecture_id1);								// 地名
		//------分類2-------------------------------------------------------------------------
		$pi->get_classification($db_link, $classification_id2, $classification_name2);							// 分類
		$pi->get_direction($db_link,$direction_id2,$direction_name2,$classification_id2);						// 方面
		$pi->get_country_prefecture($db_link,$country_prefecture_id2,$country_prefecture_name2,$direction_id2);	// 国・都道府県
		$pi->get_place($db_link,$place_id2,$place_name2,$country_prefecture_id2);								// 地名
		//------分類3-------------------------------------------------------------------------
		$pi->get_classification($db_link, $classification_id3, $classification_name3);							// 分類
		$pi->get_direction($db_link,$direction_id3,$direction_name3,$classification_id3);						// 方面
		$pi->get_country_prefecture($db_link,$country_prefecture_id3,$country_prefecture_name3,$direction_id3);	// 国・都道府県
		$pi->get_place($db_link,$place_id3,$place_name3,$country_prefecture_id3);								// 地名
		//------分類4-------------------------------------------------------------------------
		$pi->get_classification($db_link, $classification_id4, $classification_name4);							// 分類
		$pi->get_direction($db_link,$direction_id4,$direction_name4,$classification_id4);						// 方面
		$pi->get_country_prefecture($db_link,$country_prefecture_id4,$country_prefecture_name4,$direction_id4);	// 国・都道府県
		$pi->get_place($db_link,$place_id4,$place_name4,$country_prefecture_id4);								// 地名
		//------分類5-------------------------------------------------------------------------
		$pi->get_classification($db_link, $classification_id5, $classification_name5);							// 分類
		$pi->get_direction($db_link,$direction_id5,$direction_name5,$classification_id5);						// 方面
		$pi->get_country_prefecture($db_link,$country_prefecture_id5,$country_prefecture_name5,$direction_id5);	// 国・都道府県
		$pi->get_place($db_link,$place_id5,$place_name5,$country_prefecture_id5);								// 地名

		$pi->get_category($db_link,$category_id,$category_name);												// カテゴリ

		$pi->get_registration_division2($db_link,$registration_id,$registration_name);							// 登録区分

		$pi->get_range_of_use($db_link,$range_id,$range_name);													// 使用範囲

		$pi->get_borrowing_ahead($db_link,$borrow_id,$borrow_name);												// 写真入手元

		$pi->get_publishing_situation($db_link,$p_situation_id,$p_situation_name);								// 掲載状況
	}
}
catch(Exception $cla)
{
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

function disp_image_file_url()
{
	global $pi;

	print "			<dl class=\"reg_filedata reg_clear reg_list_none_top\">\r\n";
	print "				<dt>画像ファイル</dt>\r\n";
	print "				<dd class=\"tag_txt\">".dp($pi->up_url[1])."</dd>\r\n";
	print "			</dl>\r\n";
}

/*
 * 関数名：disp_photo_name
 * 関数説明：「写真名（タイトル）」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_photo_name()
{
	global $pi;

	print "	<dl class=\"reg_subject reg_clear\">\r\n";
	print "		<dt>被写体の名称</dt>\r\n";
	print "		<dd>".dp($pi->photo_name)."</dd>\r\n";
	print "	</dl>\r\n";
}

/*
 * 関数名：registration_division
 * 関数説明：「登録区分」を出力する
 * パラメタ：
 * $c_id：	登録区分ID
 * $c_name：　登録区分名
 * 戻り値：無し
 */
function registration_division($c_id, $c_name)
{
	global $pi;
	$ed = count($c_id);
	for ($i = 0 ; $i<$ed ; $i++)
	{
		// 登録した登録区分は登録区分ラジオボタングループに存在した場合
		if ((int)$pi->registration_division_id == (int)$c_id[$i]) return $c_name[$i];
	}
	return "";
}

/*
 * 関数名：disp_div_classification
 * 関数説明：「登録分類」を出力する
 */
function disp_div_classification($c_id, $c_name, $no)
{
	global $pi;

	// 登録分類1
	global $classification_id1, $classification_name1, $direction_id1, $direction_name1;
	global $country_prefecture_id1, $country_prefecture_name1, $place_id1, $place_name1;

	// 登録分類2
	global $classification_id2, $classification_name2, $direction_id2, $direction_name2;
	global $country_prefecture_id2, $country_prefecture_name2, $place_id2, $place_name2;

	// 登録分類3
	global $classification_id3, $classification_name3, $direction_id3, $direction_name3;
	global $country_prefecture_id3, $country_prefecture_name3, $place_id3, $place_name3;

	// 登録分類4
	global $classification_id4, $classification_name4, $direction_id4, $direction_name4;
	global $country_prefecture_id4, $country_prefecture_name4, $place_id4, $place_name4;

	// 登録分類5
	global $classification_id5, $classification_name5, $direction_id5, $direction_name5;
	global $country_prefecture_id5, $country_prefecture_name5, $place_id5, $place_name5;

	// 登録分類のインデックス(1～5)
	$indx = (int)$no - 1;
	// 分類を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->classification_id[$indx]))
	{
		$tmp_class = $pi->registration_classifications->classification_id[$indx];
	} else {
		$tmp_class = -1;
	}
	$ed = count($c_id);
	// 登録分類を表示するかどうかフラグ
	$showflg = false;

	for ($i = 0 ; $i < $ed ; $i++)
	{
		// DBにこの登録分類を登録した場合、登録分類を表示する
		if ((int)$tmp_class == (int)$c_id[$i])
		{
			$showflg = true;
			break;
		}
	}

	// 今の登録分類
	$div_key = "div_classification".$no;
	$next = $no + 1;
	// 次の登録分類
	$div_key_next = "div_classification".$next;

	// この登録分類を表示する時
	if ($showflg)
	{
		print "<div id=\"".$div_key."\" style=\"display:block;\">\r\n";
	} else {
		print "<div id=\"".$div_key."\" style=\"display:none;\">\r\n";
	}

	print "<dl class=\"reg_classification reg_clear\">\r\n";
	print "<dt>登録分類".DBC_SBC($no)."</dt>\r\n";

	// 登録分類のインデックスは１の場合
	if ((int)$no == 1)
	{
		$cls = disp_classification($classification_id1, $classification_name1, 1);
		$dire = disp_direction($direction_id1, $direction_name1, $classification_id1, 1);
		$c_per = disp_country_prefecture($country_prefecture_id1, $country_prefecture_name1, $direction_id1, 1);
		$place = disp_place($place_id1, $place_name1, $country_prefecture_id1, 1);
		// 登録分類のインデックスは２の場合
	} elseif ((int)$no == 2) {
		$cls = disp_classification($classification_id2, $classification_name2, 2);
		$dire = disp_direction($direction_id2, $direction_name2, $classification_id2, 2);
		$c_per = disp_country_prefecture($country_prefecture_id2, $country_prefecture_name2, $direction_id2, 2);
		$place = disp_place($place_id2, $place_name2, $country_prefecture_id2, 2);
		// 登録分類のインデックスは３の場合
	} elseif ((int)$no == 3) {
		$cls = disp_classification($classification_id3, $classification_name3, 3);
		$dire = disp_direction($direction_id3, $direction_name3, $classification_id3, 3);
		$c_per = disp_country_prefecture($country_prefecture_id3, $country_prefecture_name3, $direction_id3, 3);
		$place = disp_place($place_id3, $place_name3, $country_prefecture_id3, 3);
		// 登録分類のインデックスは４の場合
	} elseif ((int)$no == 4) {
		$cls = disp_classification($classification_id4, $classification_name4, 4);
		$dire = disp_direction($direction_id4, $direction_name4, $classification_id4, 4);
		$c_per = disp_country_prefecture($country_prefecture_id4, $country_prefecture_name4, $direction_id4, 4);
		$place = disp_place($place_id4, $place_name4, $country_prefecture_id4, 4);
		// 登録分類のインデックスは５の場合
	} elseif ((int)$no == 5) {
		$cls = disp_classification($classification_id5, $classification_name5, 5);
		$dire = disp_direction($direction_id5, $direction_name5, $classification_id5, 5);
		$c_per = disp_country_prefecture($country_prefecture_id5, $country_prefecture_name5, $direction_id5, 5);
		$place = disp_place($place_id5, $place_name5, $country_prefecture_id5, 5);
	}

	$tmp1 = "";
	if (!empty($cls)) $tmp1 .= dp($cls);
	if (!empty($dire)) $tmp1 .= " | ".dp($dire);
	if (!empty($c_per)) $tmp1 .= " | ".dp($c_per);
	if (!empty($place)) $tmp1 .= " | ".dp($place);

	if (!empty($tmp1))
	{
		print "<dd>".$tmp1."</dd>\r\n";
	} else {
		print "<dd>&nbsp;&nbsp;&nbsp;</dd>\r\n";
	}
	print "</dl>\r\n";
	print "</div>\r\n";
}

/*
 * 関数名：disp_classification
 * 関数説明：「登録分類」を出力する
 * パラメータ：
 * c_id:分類ID；c_name:分類名;no:分類インデックス（分類１～分類５）
 * 戻り値：無し
 */
function disp_classification($c_id, $c_name, $no)
{
	global $pi;

	// 分類インデックス
	$indx = (int)$no - 1;
	// 分類を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->classification_id[$indx]))
	{
		$tmp_class = $pi->registration_classifications->classification_id[$indx];
	} else {
		$tmp_class = -1;
	}
	$ed = count($c_id);
	// 分類より繰り返し
	for ($i = 0 ; $i < $ed ; $i++)
	{
		// 登録した分類は分類レストに存在した場合
		if ((int)$tmp_class == (int)$c_id[$i]) return $c_name[$i];
	}
	return "";
}

/*
 * 関数名：disp_direction
 * 関数説明：「登録分類」を出力する、必ず、disp_classification()の後に実行してください。
 * パラメータ：
 * d_id:方面ID；d_name:方面名;c_id:分類ID;no:方面インデックス（方面１～方面５）
 * 戻り値：無し
 */
function disp_direction($d_id, $d_name, $c_id, $no)
{
	global $pi;

	// 方面インデックス
	$indx = (int)$no - 1;
	// 方面を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->direction_id[$indx]))
	{
		$tmp_direct = $pi->registration_classifications->direction_id[$indx];
	} else {
		$tmp_direct = -1;
	}

	$ed = count($c_id);
	// 分類より繰り返し
	for ($i = 0 ; $i < $ed ; $i++)
	{
		$ed2 = count($d_id[$i]);
		// 方面より繰り返し
		for ($j = 0 ; $j < $ed2 ; $j++)
		{
			// 登録した方面は方面レストに存在した場合
			if ((int)$tmp_direct == (int)$d_id[$i][$j]) return $d_name[$i][$j];
		}
	}
	return "";
}

/*
 * 関数名：disp_country_prefecture
 * 関数説明：「登録分類」を出力する、必ず、disp_direction()の後に実行してください。
 * パラメータ：
 * cp_id:国・都道府県ID；cp_name:国・都道府県名;
 * d_id:方面ID;
 * no:国・都道府県インデックス（国・都道府県１～国・都道府県５）
 * 戻り値：無し
 */
function disp_country_prefecture($cp_id, $cp_name, $d_id, $no)
{
	global $pi;

	// 国・都道府県インデックス
	$indx = (int)$no - 1;
	// 国・都道府県を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->country_prefecture_id[$indx]))
	{
		$tmp_country = $pi->registration_classifications->country_prefecture_id[$indx];
	} else {
		$tmp_country = -1;
	}

	$ed = count($d_id);
	// 方面より繰り返し
	for ($i = 0 ; $i < $ed ; $i++)
	{
		$ed2 = count($d_id[$i]);
		// 方面より繰り返し
		for ($j = 0 ; $j < $ed2 ; $j++)
		{
			$ed3 = count($cp_id[$i][$j]);
			// 国・都道府県より繰り返し
			for ($k = 0 ; $k < $ed3 ; $k++)
			{
				// 登録した国・都道府県は国・都道府県レストに存在した場合
				if ((int)$tmp_country == (int)$cp_id[$i][$j][$k]) return $cp_name[$i][$j][$k];
			}
		}
	}
	return "";
}

/*
 * 関数名：disp_place
 * 関数説明：「登録分類」を出力する、必ず、disp_country_prefecture()の後に実行してください。
 * パラメータ：
 * p_id:都市ID；p_name:都市名;
 * cp_id:国・都道府県ID;
 * no:都市インデックス（都市１～都市５）
 * 戻り値：無し
 */
function disp_place($p_id, $p_name, $cp_id, $no)
{
	global $pi;

	// 都市インデックス
	$indx = (int)$no - 1;
	// 地名を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->place_id[$indx]))
	{
		$tmp_place = $pi->registration_classifications->place_id[$indx];
	} else {
		$tmp_place = -1;
	}

	$ed = count($p_id);
	// 国・都道府県より繰り返し
	for ($i = 0 ; $i < $ed ; $i++)
	{
		$ed2 = count($p_id[$i]);
		// 国・都道府県より繰り返し
		for ($j = 0 ; $j < $ed2 ; $j++)
		{
			$ed3 = count($p_id[$i][$j]);
			// 国・都道府県より繰り返し
			for ($k = 0 ; $k < $ed3 ; $k++)
			{
				$ed4 = count($p_id[$i][$j][$k]);
				// 地名より繰り返し
				for ($l = 0 ; $l < $ed4 ;$l++)
				{
					// 登録した地名は地名レストに存在した場合
					if ((int)$tmp_place == (int)$p_id[$i][$j][$k][$l]) return $p_name[$i][$j][$k][$l];
				}
			}
		}
	}
	return "";
}

/*
 * 関数名：check_array_index
 * 関数説明：文字列の検索
 * パラメタ：
 * $ary：		　array
 * $fndstr：	　文字列
 * 戻り値：インデックス
 */
function check_array_index($ary,$fndstr)
{
	for ($i = 0; $i < count($ary); $i++)
	{
		// 文字列を存在した場合、インデックスを戻る
		if (strcasecmp($ary[$i],$fndstr) == 0) return $i;
	}
	return -1;
}

/*
 * 関数名：disp_category
 * 関数説明：「カテゴリー」を出力する
 * パラメタ：
 * $cg_id：	　カテゴリーID
 * $cg_name：　カテゴリー
 * 戻り値：無し
 */
function disp_category($cg_id,$cg_name)
{
	global $pi,$db_link,$p_photo_id;

	// PhotoIDよりキーワードーを取得する
	$pi->get_keyword_str($db_link, $p_photo_id);
	$kwd_a = array();
	// スペース区切りの文字列を配列にします。
	$kwd_a = preg_split(" ", $pi->keyword_str);
	$tmp_cg_name = "";
	$tmp1_cg_name = "";

	$dc = count($cg_id);
	//added by wangtongchao 2011-12-05 begin
	$cnt_catgr = 0;
	//added by wangtongchao 2011-12-05 end
	// カテゴリー（親）より繰り返し
	for ($i=0;$i < $dc;$i++)
	{
		// 登録したキーワードはキーワードの配列に存在した場合
		if (check_array_index($kwd_a,$cg_name[$i][0]) != -1)
		{
			$tmp_cg_name = $cg_name[$i][0];
			$dc2 = count($cg_id[$i]);
			$tmp1_cg_name = "";
			//added by wangtongchao 2011-12-05 begin
			$cnt_catgr = $cnt_catgr + 1;
			//added by wangtongchao 2011-12-05 end
			// カテゴリー（子）より繰り返し
			for($j = 1;$j < $dc2; $j++)
			{
				// 登録したキーワードはキーワードの配列に存在した場合
				if (check_array_index($kwd_a,$cg_name[$i][$j]) != -1) $tmp1_cg_name.= $cg_name[$i][$j]." | ";
			}
			if (strlen($tmp1_cg_name) > 0)
			{
				print "<ul><li>".dp($tmp_cg_name." | ".substr($tmp1_cg_name,0,strlen($tmp1_cg_name)-2))."</li></ul>\r\n";
			} else {
				print "<ul><li>".dp($tmp_cg_name." | ".$tmp1_cg_name)."</li></ul>\r\n";
			}
		}
	}
	//added by wangtongchao 2011-12-05 begin
	print "<input type=\"hidden\" id=\"catgr_cnt\" value=\"".$cnt_catgr."\" >\r\n";
	//added by wangtongchao 2011-12-05 end
}

/*
 * 関数名：take_picture_time2
 * 関数説明：「撮影時期」の季節を出力する
 * パラメタ：
 * $c_id：	季節ID
 * $c_name：　季節
 * 戻り値：無し
 */
function take_picture_time2($c_id, $c_name)
{
	global $pi;

	$ed = count($c_id);
	for ($i = 0 ; $i<$ed ; $i++)
	{
		// 登録した撮影時期の季節は撮影時期の季節のラジオボタングループに存在した場合
		if ((int)$pi->take_picture_time2_id == (int)$c_id[$i]) print "	<label>".dp($c_name[$i])."</label>";
	}
}

/*
 * 関数名：take_picture_time
 * 関数説明：「撮影時期」の月を出力する
 * パラメタ：
 * $c_id：	月ID
 * $c_name：月
 * $flg:出力フラグ「1」：掲載期間の月；「0」：撮影時期の月
 * 戻り値：無し
 */
function take_picture_time($c_id, $c_name, $flg)
{
	global $pi;

	$ed = count($c_id);
	for ($i = 1 ; $i <= $ed ; $i++)
	{
		// 撮影時期の月を出力する
		if ($flg == 0)
		{
			// 登録した撮影時期の月は撮影時期の月のレストに存在した場合
			if ((int)$pi->take_picture_time_id == (int)$i) print "<label>".$c_name[$i - 1]."</label>\r\n";
		}

		// 掲載期間の月を出力する
		if ($flg == 1)
		{
			// 登録した掲載期間(To)から掲載期間の月を取得する
			$i_month = (int)substr($pi->dto,5,2);
			// 掲載期間は「無期限」以外を選択した場合
			if ($pi->kikan != "mukigen")
			{
				// 登録した掲載期間の月は掲載期間の月のレストに存在した場合
				if ($i_month == (int)$i) print "	<option value=" .$i. " selected=\"selected\" >" . dp($c_name[$i - 1]) . "</option>\r\n";
			} else {
				print "	<option value=" .$i. ">" . dp($c_name[$i - 1]) . "</option>\r\n";
			}
		}
	}
}

/*
 * 関数名：disp_photo_explanation
 * 関数説明：「写真説明」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_photo_explanation()
{
	global $pi;

	print "	<dl class=\"reg_material reg_clear\">\r\n";
	print "		<dt>素材（画像）の詳細内容</dt>\r\n";
	print "		<dd>\r\n".dp($pi->photo_explanation)."</dd>\r\n";
	print "	</dl>\r\n";
}

/*
 * 関数名：disp_kikan
 * 関数説明：「期間」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_kikan()
{
	global $pi;

	print "	<dd>\r\n";
	switch ($pi->kikan)
	{
		// 「無期限」を選択した場合
		case 'mukigen':
			print "		<label>無期限</label>\r\n";
			break;
			// 「3ヵ月」を選択した場合
		case 'sankagetu':
			print "		<label>3ヵ月 </label>\r\n";
			break;
			// 「6ヵ月」を選択した場合
		case 'hantoshi':
			print "		<label>6ヵ月 </label>\r\n";
			break;
			// 「1年間」を選択した場合
		case 'ichinen':
			print "		<label>1年間 </label>\r\n";
			break;
		//added by wangtongcaho 2011-12-02 begin
			// 「2年間」を選択した場合
		case 'ninen':
			print "		<label>2年間 </label>\r\n";
			break;
			// 「3年間」を選択した場合
		case 'sannen':
			print "		<label>3年間 </label>\r\n";
			break;
		//added by wangtongchao 2011-12-02 end
		default:
			break;
	}
	print "	</dd>\r\n";
}

/*
 * 関数名：disp_kikan2
 * 関数説明：「期間」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_kikan2()
{
	global $pi,$take_picture_time_id,$take_picture_time_name;

	print "	<dd class=\"mounth\">\r\n";
	// 「日付指定」を選択した場合
	if ($pi->kikan == "shitei") print "		<label>日付指定</label>\r\n";

	$p_d_from = substr($pi->dfrom,0,4)."年".substr($pi->dfrom,5,2)."月".substr($pi->dfrom,-2)."日";
	$p_d_to = substr($pi->dto,0,4)."年".substr($pi->dto,5,2)."月".substr($pi->dto,-2)."日";
	print "			<label>".dp($p_d_from."　～　".$p_d_to)."</label>\r\n";
	print "	</dd>\r\n";
}

/*
 * 関数名：DBC_SBC
 * 関数説明：半角->全角の転換
 * パラメタ：$Str：転換前の文字列
 * 戻り値：転換後の文字列
 */
function DBC_SBC($Str) {
	$Queue = Array(
	               '0' => '０','1' => '１','2' => '２','3' => '３','4' => '４',
	               '5' => '５','6' => '６','7' => '７','8' => '８','9' => '９',
	);
	return preg_replace("/([0-9])/e", "\$Queue[\\1]", $Str);
}

/*
 * 関数名：disp_range
 * 関数説明：「掲載可能範囲」を出力する
 * パラメタ：
 * $r_id：	　掲載可能範囲ID
 * $r_name：  　掲載可能範囲
 * 戻り値：無し
 */
function disp_range($r_id,$r_name)
{
	global $pi;

	$ed = count($r_id);

	for ($i=0;$i < $ed;$i++)
	{
		// 掲載可能範囲の「外部出稿条件付き」を選択した場合
		if ((int)$r_id[$i] == 3)
		{
			// 登録した掲載可能範囲は掲載可能範囲ラジオボタングループに存在した場合
			if ((int)$pi->range_of_use_id == (int)$r_id[$i]) print"	<dd>".dp($r_name[$i]." | ".$pi->use_condition)."</dd>\r\n";
		} else {
			// 登録した掲載可能範囲は掲載可能範囲ラジオボタングループに存在した場合
			if ((int)$pi->range_of_use_id == (int)$r_id[$i]) print"	<dd>".dp($r_name[$i])."</dd>\r\n";
		}
	}
}

/*
 * 関数名：disp_additional_constraints
 * 関数説明：「付加条件」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_additional_constraints()
{
	global $pi;
	$flg = false;
	print "	<dl class=\"reg_addition reg_clear\">\r\n";
	print "		<dt>付加条件</dt>\r\n";
	if (!empty($pi->additional_constraints1) && $pi->additional_constraints1 != "")
	{
		//yupengbo modify 20110105 start
		print "		<dd>要クレジット | ".str_replace("=_=","<br/>",dp($pi->additional_constraints1))."</dd>\r\n";
		//yupengbo modify 20110105 end
	}

	if (!empty($pi->additional_constraints2) && $pi->additional_constraints2 != "")
	{
		print "		<dd>要使用許可 | ".dp($pi->additional_constraints2)."</dd>\r\n";
	}

	if ((empty($pi->additional_constraints1) || $pi->additional_constraints1 == "") && (empty($pi->additional_constraints2) || $pi->additional_constraints2 == ""))
	{
		print "		<dd>なし</dd>\r\n";
	}
	print "	</dl>\r\n";
}

/*
 * 関数名：disp_monopoly_use
 * 関数説明：「独占使用」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_monopoly_use()
{
	global $pi;

	print "	<dl class=\"reg_account reg_clear\">\r\n";
	print "		<dt>このアカウントのみ使用可</dt>\r\n";
	print "		<dd>\r\n";
	// このアカウントのみ使用可を選択した場合
	if ((int)$pi->monopoly_use == 1)
	{
		print "			<label>この申請アカウント </label>\r\n";
	} else {
		print "			<label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>\r\n";
	}
	print "		</dd>\r\n";
	print "	</dl>\r\n";
}

/*
 * 関数名：disp_borrowing_ahead
 * 関数説明：「写真入手元」を出力する
 * パラメタ：
 * $r_id：	　写真入手元ID
 * $r_name：  　写真入手元
 * 戻り値：無し
 */
function disp_borrowing_ahead($b_head_id,$b_head_name)
{
	global $pi;

	$ed = count($b_head_id);

	for ($i=0;$i < $ed;$i++)
	{
		// 写真入手元の「その他」を選択した場合
		if ((int)$b_head_id[$i] == 2)
		{
			// 登録した写真入手元は写真入手元ラジオボタングループに存在した場合
			//changed by wangtongchao 2011-11-28 begin
			//if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i]) print"	<dd>".dp($b_head_name[$i]." | ".$pi->content_borrowing_ahead)."</dd>\r\n";
			if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i]) print"	<dd>".dp($pi->content_borrowing_ahead)."</dd>\r\n";
			//changed by wangtongchao 2011-11-28 end
		} else {
			// 登録した写真入手元は写真入手元ラジオボタングループに存在した場合
			//changed by wangtongchao 2011-11-28 begin
			//if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i]) print"	<dd>".dp($b_head_name[$i])."</dd>\r\n";
			if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i]) print"	<dd></dd>\r\n";
			//changed by wangtongchao 2011-11-28 end
		}
	}
}

/*
 * 関数名：disp_copyright_owner
 * 関数説明：「版権所有者」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_copyright_owner()
{
	global $pi;

	print "	<dl class=\"reg_copyright reg_clear\">\r\n";
	print "		<dt>版権所有者</dt>\r\n";
	print "		<dd>".dp($pi->copyright_owner)."</dd>\r\n";
	print "	</dl>\r\n";
}

/*
 * 関数名：disp_source_image_no
 * 関数説明：「元画像管理番号」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_source_image_no()
{
	global $pi;

	print "	<dl class=\"reg_mate_mana reg_clear\">\r\n";
	print "		<dt>素材管理番号</dt>\r\n";
	print "		<dd>".dp($pi->source_image_no)."</dd>\r\n";
	print "	</dl>\r\n";
}

/*
 * 関数名：disp_bud_photo_no
 * 関数説明：「BUD_PHOTO番号」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_bud_photo_no()
{
	global $pi;

	print "	<dl class=\"reg_bud_number reg_clear\">\r\n";
	print "		<dt>BUD_PHOTO番号</dt>\r\n";
	if (!empty($pi->bud_photo_no))
	{
		print "		<dd>ある | ".dp($pi->bud_photo_no)."</dd>\r\n";
	} else {
		print "		<dd>なし</dd>\r\n";
	}
	print "	</dl>\r\n";
}

/*
 * 関数名：disp_customer
 * 関数説明：「お客様部署」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_customer()
{
	global $pi;

	print "		<dl class=\"reg_customer_info reg_clear reg_list_none_top\">\r\n";
	print "			<dt>お客様情報</dt>\r\n";
	print "			<dd> 部署名：".dp($pi->customer_section)."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;名前：".dp($pi->customer_name)."</dd>\r\n";
	print "		</dl>\r\n";
}

/*
 * 関数名：disp_registration
 * 関数説明：「登録申請者」、「登録許可者」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_registration()
{
	global $pi;

	print "		<dl class=\"reg_apply reg_clear\">\r\n";
	print "			<dt>登録申請者</dt>\r\n";
	print "			<dd>".dp($pi->registration_person)."</dd>\r\n";
	print "		</dl>\r\n";
	print "		<dl class=\"reg_permission reg_clear\">\r\n";
	print "			<dt>登録許可者</dt>\r\n";
	print "			<dd>".dp($pi->permission_person)."</dd>\r\n";
	print "		</dl>\r\n";
}
//xu add it on 2010-12-01 start
/*
 * 関数名：disp_note
 * 関数説明：「備考」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_photo_no_url()
{
	global $pi;
	if((int)$pi->registration_division_id==3)
	{
		print "	<dl class=\"reg_mate_mana reg_clear\">\r\n";
		print "		<dt>元画像番号</dt>\r\n";
		print "		<dd><pre>".dp($pi->photo_org_no)."</pre></dd>\r\n";
		print "	</dl>\r\n";
	}
	elseif((int)$pi->registration_division_id==4)
	{
		print "	<dl class=\"reg_mate_mana reg_clear\">\r\n";
		print "		<dt>ページURL</dt>\r\n";
		print "		<dd><pre>".dp($pi->photo_url)."</pre></dd>\r\n";
		print "	</dl>\r\n";
	}
	else
	{
		return '';
	}
}
//xu add it on 2010-12-01 end
/*
 * 関数名：disp_note
 * 関数説明：「備考」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_note()
{
	global $pi;

	print "<p class=\"reg_remarks\">".dp($pi->note)."</p>\r\n";
}

/*
 * 関数名：write_log_tofile
 * 関数説明：画像を削除すると、削除した画像はログファイルに出力する
 * パラメタ：logmsg:ログ情報
 * 戻り値：無し
 */
function write_log_tofile($logmsg)
{
	// CSVファイルを出力する
	$file = fopen($csvdir."./log/delete_image.log","a+");
	fwrite($file,$logmsg);
	fclose($file);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>新規登録確認画面｜BUD PHOTO WEB</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css"
	media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script type="text/javascript" src="./js/common.js" charset="utf-8"></script>
<script type="text/javascript">
/*
 * 関数名：form_reback
 * 関数説明：イメージ登録画面へ戻る
 * パラメタ：無し
 * 戻り値：無し
 */
function form_reback()
{
	parent.bottom.location.href  = "./register_image_input.php?initflg=2";
}

/*
 * 関数名：form_clear
 * 関数説明：画面のクリアー
 * パラメタ：無し
 * 戻り値：無し
 */
function delete_record()
{
	var ret = confirm("この画像を完全に削除しますか?");
	if (ret)
	{
		var url = "./register_image_confirm.php?p_action=delete";
		parent.bottom.location.href = url;
	}
}

/*
 * 関数名：init
 * 関数説明：画面の初期化の処理
 * パラメタ：無し
 * 戻り値：無し
 */
function init()
{
	//----------フレームの設定  開始---------------
	var obj_frame = top.document.getElementById('iframe_middle1');
	if (obj_frame) obj_frame.style.height = 0;
	var obj_frame = top.document.getElementById('iframe_middle2');
	if (obj_frame) obj_frame.style.height = 0;
	var obj_frame = top.document.getElementById('iframe_bottom');
	if (obj_frame) obj_frame.style.height = 1800;
	//added by wangtongchao 2011-12-05 begin
	var obj_catgr = document.getElementById('catgr_cnt');
	if(obj_catgr)
	{
		var catgr_value=parseInt(obj_catgr.value);
		if(catgr_value > 5)
		{
			obj_frame.style.height = 1800 + 50*(catgr_value-5);
		}
	}
	//added by wangtongchao 2011-12-05 end
	//modify by jinxin 2012-02-10 start
	<?php 
		if((int)$pi->registration_division_id==4||(int)$pi->registration_division_id==3){
			echo "obj_frame.style.height = '2000px';";
		}
	?>
	//modify by jinxin 2012-02-10 end
	var div_obj = document.getElementById("div_classification2");
	if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 50;

	var div_obj = document.getElementById("div_classification3");
	if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 100;

	var div_obj = document.getElementById("div_classification4");
	if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 150;

	var div_obj = document.getElementById("div_classification5");
	if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 200;
	//----------フレームの設定  終了---------------
}

window.onload = function()
{
	init();
}
</script>
<!-- javascript ここまで -->
</head>
<body>
<form name="register_image_edit" action="" method="post">
<div id="zentai">
<div id="contents">
<div id="registration">
<div>
<h2>基本情報</h2>
<p class="reg_photo_number"><img src="<?php  echo $pi->up_url[1]; ?>"
	width="250" height="180" /></p>
<div class="reg_file_subject"><?php  disp_image_file_url(); ?> <?php  disp_photo_name(); ?>
</div>
<dl class="reg_division reg_clear">
	<dt>登録区分</dt>
	<dd><?php  echo registration_division($registration_id, $registration_name); ?></dd>
</dl>
<?php echo disp_photo_no_url();?>
<!-- 分類1 --> <?php  disp_div_classification($classification_id1, $classification_name1, 1); ?>
<!-- 分類2 --> <?php  disp_div_classification($classification_id2, $classification_name2, 2); ?>
<!-- 分類3 --> <?php  disp_div_classification($classification_id3, $classification_name3, 3); ?>
<!-- 分類4 --> <?php  disp_div_classification($classification_id4, $classification_name4, 4); ?>
<!-- 分類5 --> <?php  disp_div_classification($classification_id5, $classification_name5, 5); ?>

<dl class="reg_category reg_clear">
	<dt>カテゴリー</dt>
	<dd><?php  disp_category($category_id,$category_name);?></dd>
</dl>
<dl class="take_picture reg_clear">
	<dt>撮影時期</dt>
	<dd><?php  take_picture_time2($take_picture_time2_id, $take_picture_time2_name); ?></dd>
	<dd class="mounth"><?php  take_picture_time($take_picture_time_id, $take_picture_time_name, 0); ?>
	</dd>
</dl>
<?php  disp_photo_explanation(); ?></div>
<div>
<h2>掲載条件</h2>
<dl class="reg_pub_period reg_clear reg_list_none_top">
	<dt>掲載期間</dt>
	<?php  disp_kikan(); ?>
	<?php  disp_kikan2(); ?>
</dl>
<dl class="reg_pub_possible reg_clear">
	<dt>掲載可能範囲</dt>
	<?php  disp_range($range_id,$range_name); ?>
</dl>
<?php  disp_additional_constraints(); ?> <?php  disp_monopoly_use(); ?></div>
<div>
<h2>版権情報</h2>
<dl class="reg_p_obtaining reg_clear reg_list_none_top">
	<dt>写真入手元</dt>
	<?php  disp_borrowing_ahead($borrow_id,$borrow_name) ?>
</dl>
<?php  disp_copyright_owner(); ?> <?php  disp_source_image_no(); ?> <?php  disp_bud_photo_no(); ?>
</div>
<div>
<h2>登録情報</h2>
<?php  disp_customer(); ?> <?php  disp_registration(); ?></div>
<div>
<h2>備考</h2>
<?php  disp_note(); ?></div>
<div class="reg_search_btn">
<ul>
	<li class="bt_reg_appli"><a href="#" onclick="delete_record();">削除</a></li>
	<li class="bt_reg_back"><a href="#" onclick="form_reback();">戻る</a></li>
</ul>
<p>※続けて画像を登録申請する場合は、前の入力内容に上書き、もしくはクリアして入力してください</p>
</div>
</div>
</div>
</div>
</form>
</body>

</html>
