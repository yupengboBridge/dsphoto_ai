<?php
@ini_set('memory_limit', -1);
date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('utf-8');
mb_http_output('utf-8');

if (PHP_SAPI === 'cli') {
	$photo_db_root_dir = str_replace("/malltools","",dirname(__FILE__));
} else {
	$photo_db_root_dir = "..";
}

$image_root_dir = $photo_db_root_dir."/malltools/webLimited/";

require_once ($photo_db_root_dir.'/malltools/common_util.php');
require_once ($photo_db_root_dir.'/malltools/CommonPhotoImage.php');

$classification_id1 = "";											// 分類ID
$direction_id1 = "";												// 方面ID
$country_prefecture_id1 = "";										// 国・都道府県ID
$place_id1 = "";													// 地名ID

$p_photo_extentions = "";											// 画像内容
$p_photo_extentions_ok = "";										// 画像内容

$okflg = true;

// PhotoImageのインスタンスを生成します。
$pi;

/**
 * @param $par_csv_content
 * @return bool
 */
function insertPhotoImage($par_csv_content){
	global $db_link, $pi, $photo_db_root_dir, $image_root_dir;
	global $write_credit, $thumb_dir, $upload_conf, $thumb_width, $font_name_batch;

	// $ext = pathinfo($par_csv_content[1], PATHINFO_EXTENSION);
	// $end_pos = strlen(substr($par_csv_content[1],2)) - strlen($ext) - 2;
	// $mall_no = substr($par_csv_content[1],2, $end_pos);
	
	$mall_no = funcGetMallNo($par_csv_content[1]);
	try {
		$insert_data_ary = array(
			"photo_mno" => "",//写真管理番号(CSVのデータから変更後)
			"bud_photo_no" => $par_csv_content[1],//MALL_PHOTO番号
			"mall_no" => $mall_no,//MALL_PHOTO番号
			"reg_mate_mana" => $par_csv_content[10],//素材管理番号/元画像管理番号(CSVのデータのまま)
			"reg_subject" => $par_csv_content[8],//写真名（タイトル）(CSVのデータのまま)
			"time2" => "",//撮影時期１→id(CSVのデータから変更後)
			"rad_kisetu" => "",//撮影時期２→id(CSVのデータから変更後)
			"reg_p_obtaining_txt" => $par_csv_content[9],//写真入手元内容(CSVのデータのまま)
			"reg_addition" => $par_csv_content[11],//付加条件(CSVのデータのまま)
			"reg_pub_possible_txt" => $par_csv_content[14],//掲載可能範囲(CSVのデータのまま)
			"post_name" => "",//お客様部署(CSVのデータから変更後)
			"customer_name" => "",//お客様名(CSVのデータから変更後)
			"reg_remarks" => $par_csv_content[15],//備考(CSVのデータのまま)
			"registration_account" => "",//写真新規アカウント(CSVのデータから変更後)
			"registration_person" => "",//写真新規アカウント（名前）(CSVのデータから変更後)
			"register_date" => date('Y-m-d H:i:s'),//写真新規日付
			"permission_account" => "admin",//写真許可アカウント
			"permission_person" => "BUD管理者",//写真許可アカウント（名前）
			"permission_date" => date('Y-m-d H:i:s'),//写真許可日付
			"is_mall" => 1,
			"is_extension" => 0,//自動3年延期
			"publishing_situation_id" => 2,//掲載状態(掲載許可)
			"p_dto" => "",//掲載期間TO
			"p_from" => date('Y-m-d H:i:s'),//掲載期間From
			"reg_pub_period" => "shitei",//掲載期間
			"p_keyword_str" => "",//keywords(CSVのデータから変更後),
			"is_publish" => $par_csv_content[16]
		);

		// ＤＢへ接続します。
		$db_link = db_connect();
		// PhotoImageのインスタンスを生成します。
		$pi = new PhotoImageDB ();

		$registration_account_info = explode(";",$par_csv_content[17]);
		if(isset($registration_account_info[0])){
			$insert_data_ary["registration_account"] = $registration_account_info[0];
		}
		if(isset($registration_account_info[1])){
			$insert_data_ary["registration_person"] = $registration_account_info[1];
		}

		// --------画像管理番号を作成する（開始）------------------------------
		$i_pos_1= strpos($par_csv_content[1],"-");
		$compcode = "00000";
		$tmp_photo_mno1 = sprintf("%05d",$compcode);//申請者管理番号
		$tmp_photo_mno2 = substr($par_csv_content[1],0,$i_pos_1);
		$p_max_no = $pi->getmaxno($db_link, $tmp_photo_mno2);
		$tmp_photo_mno3 = $p_max_no;
		$i_pos_2 = strpos($par_csv_content[1],".");
		$tmp_photo_mno4 =substr($par_csv_content[1],$i_pos_2);
		$tmp_photo_mon_res = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
		$check_flag = CommonPhotoImage::checkPhotoMno($db_link,$tmp_photo_mon_res);
		if($check_flag == true)
		{
			$flg = "err";
			for($i=1;$i<=10;$i++)
			{
				$tmp_photo_mno3 = $p_max_no+$i;
				$tmp_photo_mon_res = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
				$check_flag_sub = CommonPhotoImage::checkPhotoMno($db_link,$tmp_photo_mon_res);
				if($check_flag_sub==false)
				{
					$flg = "ok";
					break;
				}
			}
			if($flg == "err")
			{
				return "ERR_FLG";
			}
			$tmp_photo_mon_res = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
			$insert_data_ary['photo_mno'] = $tmp_photo_mon_res;
			$p_max_no = $tmp_photo_mno3 - 1;
			$pi->setmaxno($db_link,$tmp_photo_mno2,$p_max_no);
		} else {
			$insert_data_ary['photo_mno'] = $tmp_photo_mon_res;
		}
		// --------画像管理番号を作成する（終了）------------------------------

		// 掲載期間（To）
		$dto = $par_csv_content[3];
		$insert_data_ary['p_dto'] = $dto;
		//CSVファイルに使用期限が「0000/0/00 0:00」の場合、使用期限を３年で取り込む
		if($dto == "0000/0/00 0:00"){
			$now = date('Y-m-d H:i:s',time());
			$insert_data_ary['p_dto'] = date("Y-m-d H:i:s",strtotime("+3years",strtotime($now)));
			$insert_data_ary['is_extension'] = 1;
		}

		//撮影時期
		if (!empty($par_csv_content[6])) {
			$ret_take_picture_time = CommonPhotoImage::getTakePictureTime($par_csv_content[6]);
			$insert_data_ary['rad_kisetu'] = $ret_take_picture_time["rad_kisetu"];
			$insert_data_ary['time2'] = $ret_take_picture_time["time2"];
		}else{
			$insert_data_ary['rad_kisetu'] = 0;
		}

		//お客様部署とお客様名(担当部署)
		$customer_info_ary = explode("|", $par_csv_content[12]);
		$insert_data_ary["post_name"] = $customer_info_ary[0];
		if (isset($customer_info_ary[1])) $insert_data_ary["customer_name"] = $customer_info_ary[1];

		if($insert_data_ary["is_publish"] == "あり" ||
			$insert_data_ary["is_publish"] == "有り" ||
			$insert_data_ary["is_publish"] == "有" ||
			$insert_data_ary["is_publish"] == "1"
		) {
			$insert_data_ary["is_publish"] = 1;
		}
		if($insert_data_ary["is_publish"] == "なし" ||
			$insert_data_ary["is_publish"] == "無し" ||
			$insert_data_ary["is_publish"] == "無" ||
			$insert_data_ary["is_publish"] == "0"
		){
			$insert_data_ary["is_publish"] = 0;
		}

		//カテゴリ
		$keywords_str = CommonPhotoImage::getCategories($par_csv_content[2]);

		// 都道府県(国名県名)
		$p_country_prefecture_name1 = CommonUtil::trimSpace($par_csv_content[4]);
		$tmp_str = "__" . $p_country_prefecture_name1;
		$str_pos1 = strpos($p_country_prefecture_name1, "県");
		$str_pos2 = strpos($p_country_prefecture_name1, "府");
		$str_pos3 = strpos($tmp_str, "東京都");
		if ($str_pos1 > 0) {
			$tmp = substr($p_country_prefecture_name1, 0, $str_pos1);
			$p_country_prefecture_name1 = $tmp;
		} elseif ($str_pos2 > 0) {
			$tmp = substr($p_country_prefecture_name1, 0, $str_pos2);
			$p_country_prefecture_name1 = $tmp;
		} elseif ($str_pos3 > 0) {
			$p_country_prefecture_name1 = "東京";
		}
		// 地名ID
		$p_place_name1 = $par_csv_content[5];

		$ret_classification_data = CommonPhotoImage::getClassificationNames(
			$db_link,
			"",
			"",
			$p_country_prefecture_name1,
			$p_place_name1
		);
		$classification_id1 = $ret_classification_data["classification_id1"];
		$direction_id1 = $ret_classification_data["direction_id1"];
		$country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
		$place_id1 = $ret_classification_data["place_id1"];
		$p_photo_extensions = $ret_classification_data["ret_param_classification_names"];
		$p_photo_extensions_ok = $ret_classification_data["ret_classification_names"];

		if (!empty($p_photo_extensions) && strlen($p_photo_extensions) > 0 &&
			!empty($par_csv_content[4]) && strlen($par_csv_content[4]) > 0) {
			if (!empty($keywords_str)) {
				$keywords_str .= " " . $par_csv_content[4];
			} else {
				$keywords_str = $par_csv_content[4];
			}
		}

		if (!empty($p_photo_extensions) && strlen($p_photo_extensions) > 0 &&
			!empty($par_csv_content[5]) && strlen($par_csv_content[5]) > 0) {
			if (!empty($keywords_str)) {
				$keywords_str .= " " . $par_csv_content[5];
			} else {
				$keywords_str = $par_csv_content[5];
			}
		}

		if (!empty($keywords_str)) {
			$keywords_str .= " " . $par_csv_content[1];
		} else {
			$keywords_str = $par_csv_content[1];
		}

		$insert_data_ary["p_keyword_str"] = $keywords_str;

		// --------------------------写真説明-----------------------------------------------------------------
		$tmp_p_explanation = $par_csv_content[7];
		if (!empty($p_photo_extensions_ok) && strlen($p_photo_extensions_ok) > 0 && empty($p_photo_extensions)) {
			if (!empty($tmp_p_explanation)) {
				$tmp_p_explanation .= " ";
			}
			$tmp_p_explanation .= $p_photo_extensions_ok;
		} elseif (!empty($p_photo_extensions) && strlen($p_photo_extensions) > 0 && empty($p_photo_extensions_ok)) {
			if (!empty($tmp_p_explanation)) {
				$tmp_p_explanation .= " ";
			}
			$tmp_p_explanation .= $p_photo_extensions;
		}
		$pi->photo_explanation = $tmp_p_explanation;
		// ----------------------------------------------------------------------------------------------------

		// 分類ID(1)は有効の場合
		$pi->registration_classifications->count = 0;
		unset($pi->registration_classifications->classification_id);                        // 分類ID
		unset($pi->registration_classifications->classification_name);                      // 分類
		unset($pi->registration_classifications->direction_id);                             // 方面ID
		unset($pi->registration_classifications->direction_name);                           // 方面
		unset($pi->registration_classifications->country_prefecture_id);                    // 国・都道府県ID
		unset($pi->registration_classifications->country_prefecture_name);                  // 国・都道府県
		unset($pi->registration_classifications->place_id);                                 // 地名ID
		unset($pi->registration_classifications->place_name);                               // 地名
		// 登録分類１をDBに設定する
		$pi->registration_classifications->set_id(
			$classification_id1,
			$direction_id1,
			$country_prefecture_id1,
			$place_id1
		);

		set_insert_data($pi, $insert_data_ary);

		$new_thumb_dir = array_slice($thumb_dir, 0, -1);
		$new_thumb_width = array_slice($thumb_width, 0, -1);
		$new_write_credit = array_slice($write_credit, 0, -1);
		// アップロード用のインスタンスを生成します。
		$fl = new FileUploadBatchMall(
			$par_csv_content[1],
			$upload_conf,
			$new_thumb_dir,
			$new_thumb_width,
			$font_name_batch,
			$par_csv_content[11],
			$new_write_credit,
			$image_root_dir,
			$photo_db_root_dir
		);

		if ($fl->result == false) {
			$error_msg = "BUD_PHOTO_NO::" . $par_csv_content[1] . ":::FileUploadBatch初期化がエラーになりました。";
			CommonUtil::writeUploadPhotoImageLog($error_msg, $photo_db_root_dir);
			throw new Exception($error_msg);
		}
		$fl->upload();
		if ($fl->result == false) {
			$error_msg = "BUD_PHOTO_NO::" . $par_csv_content[1] . ":::ファイルアップロードがエラーになりました。";
			CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
			throw new Exception($error_msg);
		}
		// サムネイルを作成します。
		$fl->make_thumbfile();
		if ($fl->result == false) {
			$error_msg = "BUD_PHOTO_NO::" . $par_csv_content[1] . ":::サムネイル作成がエラーになりました。";
			CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
			throw new Exception($error_msg);
		}
		// DB保存用のデータを設定します。
		$pi->up_url = $fl->up_url;                    // アップロードURL
		$pi->img_width = $fl->img_width;              // イメージサイズ（横）
		$pi->img_height = $fl->img_height;            // イメージサイズ（縦）
		$pi->ext = $fl->ext;                          // 拡張子
		$pi->image_size_x = $fl->img_width[0];        // 画像サイズ（横）
		$pi->image_size_y = $fl->img_height[0];       // 画像サイズ（縦）

		$pi->photo_filename = $fl->up_url[0];			// アップロードURL
		$pi->photo_filename_th1 = $fl->up_url[1];		// サムネイル1
		$pi->photo_filename_th2 = $fl->up_url[2];		// サムネイル2
		$pi->photo_filename_th3 = $fl->up_url[3];		// サムネイル3
		$pi->photo_filename_th4 = $fl->up_url[4];		// サムネイル4
		$pi->photo_filename_th5 = isset($fl->up_url[5])?$fl->up_url[5]:"";		// サムネイル5
		$pi->photo_filename_th6 = isset($fl->up_url[6])?$fl->up_url[6]:"";		// サムネイル6
		$pi->photo_filename_th7 = isset($fl->up_url[7])?$fl->up_url[7]:"";		// サムネイル7
		$pi->photo_filename_th8 = isset($fl->up_url[8])?$fl->up_url[8]:"";		// サムネイル8
		$pi->photo_filename_th9 = isset($fl->up_url[9])?$fl->up_url[9]:"";		// サムネイル9
		$pi->photo_filename_th10 = isset($fl->up_url[10])?$fl->up_url[10]:"";	// サムネイル10

		$pi->photo_db_root_dir = $photo_db_root_dir;

		$insert_flag = $pi->batch_insert_data_for_mall($db_link);

		if ($insert_flag == false) {
			$error_msg = "MALL_NO::" . $par_csv_content[1] . ":::" . $pi->message;
			CommonUtil::writeUploadPhotoImageLog(
				"MALL_NO::" . $par_csv_content[1] . ":::" . $pi->message,
				$photo_db_root_dir
			);
			throw new Exception($error_msg);
		}
	}
	catch(Exception $ex){
		$message = $ex->getMessage();
		CommonUtil::writeUploadPhotoImageLog(
			"MALL_NO::".$par_csv_content[1].":::".$message,
			$photo_db_root_dir
		);
		throw $ex;
	}
}

function funcGetMallNo($bud_photo_no){
	$ext = pathinfo($bud_photo_no, PATHINFO_EXTENSION);
	
	$char_ary = ['A','B','C','D','E','F','G','H','I','G','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
	$char1 = strtoupper(substr($bud_photo_no, 0, 1));
	$char2 = strtoupper(substr($bud_photo_no, 1, 1));
	$char3 = strtoupper(substr($bud_photo_no, 2, 1));
	$char1_flag = in_array($char1,$char_ary);
	$char2_flag = in_array($char2,$char_ary);
	$char3_flag = in_array($char3,$char_ary);
	$sub_len = 2;
	if($char1_flag && $char2_flag && $char3_flag) {
		$sub_len = 3;
	}
	
	$file_name = pathinfo($bud_photo_no, PATHINFO_FILENAME);
	$lastChar = strtoupper(substr($file_name, -1));
	$lastChar_flag = in_array($lastChar,$char_ary);
	
	$end_pos = strlen(substr($bud_photo_no,$sub_len)) - strlen($ext) - $sub_len;

	if($char1_flag && $char2_flag && $char3_flag) {
		if($lastChar_flag){
			$mall_no = substr($bud_photo_no,$sub_len, $end_pos+1);
		}else{
			$mall_no = substr($bud_photo_no,$sub_len, $end_pos+2);
		}
	}else{
		if($lastChar_flag){
			$mall_no = substr($bud_photo_no,$sub_len, $end_pos);
		}else{
			$mall_no = substr($bud_photo_no,$sub_len, $end_pos+1);
		}
	}
	
	return $mall_no;
}

/**
 * @param $par_csv_content
 * @return bool
 */
function updatePhotoImage($par_csv_content){
	global $db_link, $pi, $photo_db_root_dir, $image_root_dir;
	global $write_credit, $thumb_dir, $upload_conf, $thumb_width, $font_name_batch;

	try {
		$update_data_ary = array(
			"photo_id" => "",//photo_id
			"bud_photo_no" => $par_csv_content[1],//bud_photo_no
			"reg_mate_mana" => $par_csv_content[10],//素材管理番号/元画像管理番号(CSVのデータのまま)
			"reg_subject" => $par_csv_content[8],//写真名（タイトル）(CSVのデータのまま)
			"time2" => "",//撮影時期１→id(CSVのデータから変更後)
			"rad_kisetu" => "",//撮影時期２→id(CSVのデータから変更後)
			"reg_p_obtaining_txt" => $par_csv_content[9],//写真入手元内容(CSVのデータのまま)
			"reg_addition" => $par_csv_content[11],//付加条件(CSVのデータのまま)
			"reg_pub_possible_txt" => $par_csv_content[14],//掲載可能範囲(CSVのデータのまま)
			"post_name" => "",//お客様部署(CSVのデータから変更後)
			"customer_name" => "",//お客様名(CSVのデータから変更後)
			"reg_remarks" => $par_csv_content[15],//備考(CSVのデータのまま)
			"p_keyword_str" => ""//keywords(CSVのデータから変更後)
		);

		// ＤＢへ接続します。
		$db_link = db_connect();
		// PhotoImageのインスタンスを生成します。
		$pi = new PhotoImageDB ();

		// $ext = pathinfo($par_csv_content[1], PATHINFO_EXTENSION);
		// $end_pos = strlen(substr($par_csv_content[1],2)) - strlen($ext) - 2;
		// $mall_no = substr($par_csv_content[1],2, $end_pos);
		$mall_no = funcGetMallNo($par_csv_content[1]);
		$photo_image_info = CommonPhotoImage::getPhotoByMallNo($db_link, $mall_no);
		$update_data_ary["photo_id"] = $photo_image_info["photo_id"];
		$update_data_ary["mall_no"] = $photo_image_info["mall_no"];

		// 掲載期間（To）
		$dto = $par_csv_content[3];
		$update_data_ary['is_extension'] = $photo_image_info["is_extension"];
		$update_data_ary['reg_pub_period'] = $photo_image_info["kikan"];
		$update_data_ary['p_dto'] = $photo_image_info["dto"];

		if($photo_image_info["is_extension"]==1){
			if($dto != "0000/0/00 0:00"){
				$update_data_ary['is_extension'] = 0;
				$update_data_ary['p_dto'] = $dto;
				$update_data_ary['reg_pub_period'] = "shitei";
			}
		}else{
			//CSVファイルに使用期限が「0000/0/00 0:00」の場合、使用期限を３年で取り込む
			if($dto == "0000/0/00 0:00"){
				$now = date('Y-m-d H:i:s',time());
				$update_data_ary['p_dto'] = date("Y-m-d H:i:s",strtotime("+3years",strtotime($now)));
				$update_data_ary['is_extension'] = 1;
				$update_data_ary['reg_pub_period'] = "shitei";
			}else{
				$update_data_ary['p_dto'] = $dto;
			}
		}

		//撮影時期
		if (!empty($par_csv_content[6])) {
			$ret_take_picture_time = CommonPhotoImage::getTakePictureTime($par_csv_content[6]);
			$update_data_ary['rad_kisetu'] = $ret_take_picture_time["rad_kisetu"];
			$update_data_ary['time2'] = $ret_take_picture_time["time2"];
		}else{
			$update_data_ary['rad_kisetu'] = 0;
		}

		//お客様部署とお客様名(担当部署)
		$customer_info_ary = explode("|", $par_csv_content[12]);
		$update_data_ary["post_name"] = $customer_info_ary[0];
		if (isset($customer_info_ary[1])) $update_data_ary["customer_name"] = $customer_info_ary[1];

		//カテゴリ
		$keywords_str = CommonPhotoImage::getCategories($par_csv_content[2]);
		if (!empty($keywords_str)) {
			$keywords_str .= " " . $photo_image_info["photo_mno"];
		} else {
			$keywords_str = $photo_image_info["photo_mno"];
		}
		
		// 都道府県(国名県名)
		$p_country_prefecture_name1 = CommonUtil::trimSpace($par_csv_content[4]);
		$tmp_str = "__" . $p_country_prefecture_name1;
		$str_pos1 = strpos($p_country_prefecture_name1, "県");
		$str_pos2 = strpos($p_country_prefecture_name1, "府");
		$str_pos3 = strpos($tmp_str, "東京都");
		if ($str_pos1 > 0) {
			$tmp = substr($p_country_prefecture_name1, 0, $str_pos1);
			$p_country_prefecture_name1 = $tmp;
		} elseif ($str_pos2 > 0) {
			$tmp = substr($p_country_prefecture_name1, 0, $str_pos2);
			$p_country_prefecture_name1 = $tmp;
		} elseif ($str_pos3 > 0) {
			$p_country_prefecture_name1 = "東京";
		}
		// 地名ID
		$p_place_name1 = $par_csv_content[5];

		$ret_classification_data = CommonPhotoImage::getClassificationNames(
			$db_link,
			"",
			"",
			$p_country_prefecture_name1,
			$p_place_name1
		);
		$classification_id1 = $ret_classification_data["classification_id1"];
		$direction_id1 = $ret_classification_data["direction_id1"];
		$country_prefecture_id1 = $ret_classification_data["country_prefecture_id1"];
		$place_id1 = $ret_classification_data["place_id1"];
		$p_photo_extensions = $ret_classification_data["ret_param_classification_names"];
		$p_photo_extensions_ok = $ret_classification_data["ret_classification_names"];

		if (!empty($p_photo_extensions) && strlen($p_photo_extensions) > 0 &&
			!empty($par_csv_content[4]) && strlen($par_csv_content[4]) > 0) {
			if (!empty($keywords_str)) {
				$keywords_str .= " " . $par_csv_content[4];
			} else {
				$keywords_str = $par_csv_content[4];
			}
		}

		if (!empty($p_photo_extensions) && strlen($p_photo_extensions) > 0 &&
			!empty($par_csv_content[5]) && strlen($par_csv_content[5]) > 0) {
			if (!empty($keywords_str)) {
				$keywords_str .= " " . $par_csv_content[5];
			} else {
				$keywords_str = $par_csv_content[5];
			}
		}

		if (!empty($keywords_str)) {
			$keywords_str .= " " . $par_csv_content[1];
		} else {
			$keywords_str = $par_csv_content[1];
		}

		$update_data_ary["p_keyword_str"] = $keywords_str;

		// --------------------------写真説明-----------------------------------------------------------------
		$tmp_p_explanation = $par_csv_content[7];
		if (!empty($p_photo_extensions_ok) && strlen($p_photo_extensions_ok) > 0 && empty($p_photo_extensions)) {
			if (!empty($tmp_p_explanation)) {
				$tmp_p_explanation .= " ";
			}
			$tmp_p_explanation .= $p_photo_extensions_ok;
		} elseif (!empty($p_photo_extensions) && strlen($p_photo_extensions) > 0 && empty($p_photo_extensions_ok)) {
			if (!empty($tmp_p_explanation)) {
				$tmp_p_explanation .= " ";
			}
			$tmp_p_explanation .= $p_photo_extensions;
		}
		$pi->photo_explanation = $tmp_p_explanation;
		// ----------------------------------------------------------------------------------------------------

		// 分類ID(1)は有効の場合
		$pi->registration_classifications->count = 0;
		unset($pi->registration_classifications->classification_id);                        // 分類ID
		unset($pi->registration_classifications->classification_name);                      // 分類
		unset($pi->registration_classifications->direction_id);                             // 方面ID
		unset($pi->registration_classifications->direction_name);                           // 方面
		unset($pi->registration_classifications->country_prefecture_id);                    // 国・都道府県ID
		unset($pi->registration_classifications->country_prefecture_name);                  // 国・都道府県
		unset($pi->registration_classifications->place_id);                                 // 地名ID
		unset($pi->registration_classifications->place_name);                               // 地名
		// 登録分類１をDBに設定する
		$pi->registration_classifications->set_id(
			$classification_id1,
			$direction_id1,
			$country_prefecture_id1,
			$place_id1
		);

		set_update_data($pi, $update_data_ary);

		$is_upload = false;
		// $ext = pathinfo($par_csv_content[1], PATHINFO_EXTENSION);
		// $end_pos = strlen(substr($par_csv_content[1],2)) - strlen($ext) - 2;
		// $mall_no = substr($par_csv_content[1],2, $end_pos);
		$mall_no = funcGetMallNo($par_csv_content[1]);
		$photo = CommonPhotoImage::getPhotoByMallNo($db_link, $mall_no);
		$additional_constraints1 = $photo['additional_constraints1'];
		$additional_constraints1 = str_replace("=_=","",$additional_constraints1);
		//print($mall_no.":::".trim($par_csv_content[11]).":::".trim($additional_constraints1));
		if (trim($par_csv_content[11]) != trim($additional_constraints1)) {
			$is_upload = true;
		}
		$ext = pathinfo($par_csv_content[1], PATHINFO_EXTENSION);
		if("EPS" == strtoupper($ext)){
			$is_upload = false;
		}

		if ($is_upload) {
			$new_thumb_dir = array_slice($thumb_dir, 0, -1);
			$new_thumb_width = array_slice($thumb_width, 0, -1);
			$new_write_credit = array_slice($write_credit, 0, -1);

			// アップロード用のインスタンスを生成します。
			$fl = new FileUploadBatchMall(
				$par_csv_content[1],
				$upload_conf,
				$new_thumb_dir,
				$new_thumb_width,
				$font_name_batch,
				$par_csv_content[11],
				$new_write_credit,
				$image_root_dir,
				$photo_db_root_dir
			);

			if ($fl->result == false) {
				$error_msg = "MALL_NO::" . $par_csv_content[1] . ":::FileUploadBatch初期化がエラーになりました。";
				CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
				throw new Exception($error_msg);
			}
			$fl->upload();
			if ($fl->result == false) {
				$error_msg = "MALL_NO::" . $par_csv_content[1] . ":::ファイルアップロードがエラーになりました。";
				CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
				throw new Exception($error_msg);
			}
			// サムネイルを作成します。
			$fl->make_thumbfile();
			if ($fl->result == false) {
				$error_msg = "MALL_NO::" . $par_csv_content[1] . ":::サムネイル作成がエラーになりました。";
				CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
				throw new Exception($error_msg);
			}
			// DB保存用のデータを設定します。
			$pi->up_url = $fl->up_url;                    // アップロードURL
			$pi->img_width = $fl->img_width;              // イメージサイズ（横）
			$pi->img_height = $fl->img_height;            // イメージサイズ（縦）
			$pi->ext = $fl->ext;                          // 拡張子
			$pi->image_size_x = $fl->img_width[0];        // 画像サイズ（横）
			$pi->image_size_y = $fl->img_height[0];       // 画像サイズ（縦）

			$pi->photo_filename = $fl->up_url[0];			// アップロードURL
			$pi->photo_filename_th1 = isset($fl->up_url[1])?$fl->up_url[1]:"";		// サムネイル1
			$pi->photo_filename_th2 = isset($fl->up_url[2])?$fl->up_url[2]:"";		// サムネイル2
			$pi->photo_filename_th3 = isset($fl->up_url[3])?$fl->up_url[3]:"";		// サムネイル3
			$pi->photo_filename_th4 = isset($fl->up_url[4])?$fl->up_url[4]:"";		// サムネイル4
			$pi->photo_filename_th5 = isset($fl->up_url[5])?$fl->up_url[5]:"";		// サムネイル5
			$pi->photo_filename_th6 = isset($fl->up_url[6])?$fl->up_url[6]:"";		// サムネイル6
			$pi->photo_filename_th7 = isset($fl->up_url[7])?$fl->up_url[7]:"";		// サムネイル7
			$pi->photo_filename_th8 = isset($fl->up_url[8])?$fl->up_url[8]:"";		// サムネイル8
			$pi->photo_filename_th9 = isset($fl->up_url[9])?$fl->up_url[9]:"";		// サムネイル9
			$pi->photo_filename_th10 = isset($fl->up_url[10])?$fl->up_url[10]:"";		// サムネイル10
		}

		$pi->publishing_situation_id = $photo['publishing_situation_id'];
		$pi->photo_db_root_dir = $photo_db_root_dir;
		$update_flag = $pi->batch_update_data_for_mall($db_link,$is_upload);

		if ($update_flag == false) {
			$error_msg = "MALL_NO::" . $par_csv_content[1] . ":::" . $pi->message;
			CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
			throw new Exception($error_msg);
		}
	}
	catch(Exception $ex){
		$message = $ex->getMessage();
		CommonUtil::writeUploadPhotoImageLog(
			"MALL_NO::".$par_csv_content[1].":::".$message,
			$photo_db_root_dir
		);
		throw $ex;
	}
}

/**
 * @param $par_csv_content
 */
function updatePhotoImageThumbAll($data_col_a){
	global $db_link, $pi, $photo_db_root_dir, $image_root_dir;
	global $write_credit, $thumb_dir, $upload_conf, $thumb_width, $font_name_batch;

	try {
		// ＤＢへ接続します。
		$db_link = db_connect();
		// PhotoImageのインスタンスを生成します。
		$pi = new PhotoImageDB ();

		$mall_no = funcGetMallNo($data_col_a);
		$photo_image_info = CommonPhotoImage::getPhotoByMallNo($db_link, $mall_no);

		if(is_null($photo_image_info)){
			$error_message = "MALL番号:".$mall_no.":::見つかりませんでした.:::";
			CommonUtil::writeUploadPhotoImageLog($error_message,$photo_db_root_dir);
		}
		$pi->photo_id = $photo_image_info["photo_id"];

		$new_thumb_dir = array_slice($thumb_dir, 0, -1);
		$new_thumb_width = array_slice($thumb_width, 0, -1);
		$new_write_credit = array_slice($write_credit, 0, -1);

		// アップロード用のインスタンスを生成します。
		$fl = new FileUploadBatchMall(
			$data_col_a,
			$upload_conf,
			$new_thumb_dir,
			$new_thumb_width,
			$font_name_batch,
			$photo_image_info['additional_constraints1'],
			$new_write_credit,
			$image_root_dir,
			$photo_db_root_dir
		);

		if ($fl->result == false) {
			$error_msg = "MALL_NO::" . $data_col_a . ":::FileUploadBatch初期化がエラーになりました。";
			CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
			throw new Exception($error_msg);
		}
		$fl->upload();
		if ($fl->result == false) {
			$error_msg = "MALL_NO::" . $data_col_a . ":::ファイルアップロードがエラーになりました。";
			CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
			throw new Exception($error_msg);
		}
		// サムネイルを作成します。
		$fl->make_thumbfile();
		if ($fl->result == false) {
			$error_msg = "MALL_NO::" . $data_col_a . ":::サムネイル作成がエラーになりました。";
			CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
			throw new Exception($error_msg);
		}
		// DB保存用のデータを設定します。
		$pi->up_url       = $fl->up_url;                    // アップロードURL
		$pi->img_width    = $fl->img_width;                 // イメージサイズ（横）
		$pi->img_height   = $fl->img_height;                // イメージサイズ（縦）
		$pi->ext          = $fl->ext;                       // 拡張子
		$pi->image_size_x = $fl->img_width[0];              // 画像サイズ（横）
		$pi->image_size_y = $fl->img_height[0];             // 画像サイズ（縦）

		$pi->photo_filename     = $fl->up_url[0];			// アップロードURL
		$pi->photo_filename_th1 = isset($fl->up_url[1])?$fl->up_url[1]:"";		// サムネイル1
		$pi->photo_filename_th2 = isset($fl->up_url[2])?$fl->up_url[2]:"";		// サムネイル2
		$pi->photo_filename_th3 = isset($fl->up_url[3])?$fl->up_url[3]:"";		// サムネイル3
		$pi->photo_filename_th4 = isset($fl->up_url[4])?$fl->up_url[4]:"";		// サムネイル4
		$pi->photo_filename_th5 = isset($fl->up_url[5])?$fl->up_url[5]:"";		// サムネイル5
		$pi->photo_filename_th6 = isset($fl->up_url[6])?$fl->up_url[6]:"";		// サムネイル6
		$pi->photo_filename_th7 = isset($fl->up_url[7])?$fl->up_url[7]:"";		// サムネイル7
		$pi->photo_filename_th8 = isset($fl->up_url[8])?$fl->up_url[8]:"";		// サムネイル8
		$pi->photo_filename_th9 = isset($fl->up_url[9])?$fl->up_url[9]:"";		// サムネイル9
		$pi->photo_filename_th10 = isset($fl->up_url[10])?$fl->up_url[10]:"";   // サムネイル10

		$pi->photo_db_root_dir = $photo_db_root_dir;
		$update_flag = $pi->batch_update_data_for_mall_2($db_link);

		if ($update_flag == false) {
			$error_msg = "MALL_NO::" . $data_col_a . ":::" . $pi->message;
			CommonUtil::writeUploadPhotoImageLog($error_msg,$photo_db_root_dir);
			throw new Exception($error_msg);
		}
	}
	catch(Exception $ex){
		$message = $ex->getMessage();
		CommonUtil::writeUploadPhotoImageLog(
			"MALL_NO::".$data_col_a.":::".$message,
			$photo_db_root_dir
		);
		throw $ex;
	}
}

/*
function deleteImage($par_csv_content){
	global $db_link, $photo_db_root_dir;

	try{
		$db_link = db_connect();
		$ret_message="";
		$ret_flag = CommonPhotoImage::deletePhotoImage($db_link,$par_csv_content[1],$photo_db_root_dir, $ret_message);
		CommonUtil::writeDeletePhotoImageLog($ret_message,$photo_db_root_dir);

		if($ret_flag == false){
			throw new Exception($ret_message);
		}
	} catch(Exception $ex){
		throw $ex;
	}
}
*/
function deleteImage($par_csv_content){
	global $db_link, $photo_db_root_dir;

	try{
		$db_link = db_connect();
		$ret_message="";

		$mall_no = funcGetMallNo($par_csv_content[1]);
		$ret_flag = CommonPhotoImage::deletePhotoImage($db_link,$mall_no,$photo_db_root_dir, $ret_message);
		CommonUtil::writeDeletePhotoImageLog($ret_message,$photo_db_root_dir);

		if($ret_flag == false){
			throw new Exception($ret_message);
		}
	} catch(Exception $ex){
		throw $ex;
	}
}

/*
 * 関数名：set_update_data
 * 関数説明：画像情報新規用のデータを設定する
 * $d_ary
 * [
 * "photo_id",//photo_id
 * "bud_photo_no",//bud_photo_no
 * "reg_mate_mana",//素材管理番号/元画像管理番号(CSVのデータのまま)
 * "reg_subject",//写真名（タイトル）(CSVのデータのまま)
 * "time2",//撮影時期１→id(CSVのデータから変更後)
 * "rad_kisetu",//撮影時期２→id(CSVのデータから変更後)
 * "reg_p_obtaining_txt",//写真入手元内容(CSVのデータのまま)
 * "reg_addition",//付加条件(CSVのデータのまま)
 * "reg_pub_possible_txt",//掲載可能範囲(CSVのデータのまま)
 * "post_name",//お客様部署(CSVのデータから変更後)
 * "customer_name",//お客様名(CSVのデータから変更後)
 * "reg_remarks",//備考(CSVのデータのまま)
 * "p_keyword_str",//keywords(CSVのデータから変更後)
 * ]
 */
function set_update_data(&$pi, $d_ary)
{
	// photo_id
	$pi->photo_id = $d_ary["photo_id"];

	//mall_no
	$pi->mall_no = $d_ary["mall_no"];

	//bud_photo_no
	$pi->bud_photo_no = $d_ary["bud_photo_no"];

	// 元画像管理番号
	$reg_mate_mana = array_get_value($d_ary,"reg_mate_mana" ,"");
	$pi->source_image_no = $reg_mate_mana;
	if (empty($reg_mate_mana) || strlen($reg_mate_mana) <= 0) $pi->source_image_no = "元画像なし";

	// 写真名（タイトル）
	$pi->photo_name = array_get_value($d_ary,"reg_subject" ,"");

	// 撮影時期１
	$pi->take_picture_time_id = array_get_value($d_ary,"time2" ,"0");
	if(empty(trim($pi->take_picture_time_id))){
		$pi->take_picture_time_id = 0;
	}
	// 撮影時期２
	$pi->take_picture_time2_id = array_get_value($d_ary,"rad_kisetu" ,"0");

	// 写真入手元内容
	$reg_p_obtaining_txt = array_get_value($d_ary,"reg_p_obtaining_txt" ,"");
	$pi->borrowing_ahead_id = 0;
	if(!empty($reg_p_obtaining_txt)) $pi->borrowing_ahead_id = 2;
	$pi->content_borrowing_ahead = $reg_p_obtaining_txt;

	// 付加条件
	$pi->additional_constraints1 = array_get_value($d_ary,"reg_addition" ,"");

	// 掲載可能範囲
	$reg_pub_possible_txt = array_get_value($d_ary,"reg_pub_possible_txt" ,"");
	$pi->range_of_use_id = 0;
	$pi->use_condition = "";
	if($reg_pub_possible_txt=="できません" || $reg_pub_possible_txt=="できない" || $reg_pub_possible_txt=="トラベルコムのみ"){
		$pi->range_of_use_id = 1;
		$pi->use_condition = "";
	}else{
		if(!empty($reg_pub_possible_txt)){
			$pi->range_of_use_id = 3;
			$pi->use_condition = $reg_pub_possible_txt;
		}
	}

	// 期間
	$pi->dto = array_get_value($d_ary,"p_dto" ,"");
	$pi->kikan = array_get_value($d_ary,"reg_pub_period" ,"");
	$pi->is_extension = array_get_value($d_ary,"is_extension" ,"0");

	// お客様部署
	$pi->customer_section = array_get_value($d_ary,"post_name" ,"");
	// お客様名
	$pi->customer_name = array_get_value($d_ary,"customer_name" ,"");
	// 備考
	$pi->note = array_get_value($d_ary,"reg_remarks" ,"");
	// キーワード文字列（スペース区切り）
	$pi->keyword_str = array_get_value($d_ary, 'p_keyword_str' ,"");
}

function checkCsvData($line){
	global $db_link, $photo_db_root_dir;

	try {
		$db_link = db_connect();

		if(count($line) !==18){
			$ret_error_msg = "MALL_NO:".$line[1].":::CSVフォーマットエラー";
			throw new Exception($ret_error_msg);
		}

		if(empty(trim($line[1]))){
			$ret_error_msg = "MALL_NO:".$line[1].':::画像のMALL_NOがないためが処理できません。';
			throw new Exception($ret_error_msg);
		}

		if (in_array(strtoupper(substr($line[1], 0, 2)), ['SH', 'KH'])) {
			return false;
		}
		
		//$ext = pathinfo($line[1], PATHINFO_EXTENSION);
		//$end_pos = strlen(substr($line[1],2)) - strlen($ext) - 2;
		//$mall_no = substr($line[1],2, $end_pos);
		$mall_no = funcGetMallNo($line[1]);
		$photo = CommonPhotoImage::getPhotoByMallNo($db_link,$mall_no);

		if($line[0] === 'A' && !is_null($photo)){
			//$ret_error_msg = "MALL_NO:".$line[1].':::画像はDSのデータベースには既に存在しましたので、新規できませんでした。';
			//throw new Exception($ret_error_msg);
			return false;
		}

//		if($line[0] === 'U' && is_null($photo)){
//			$ret_error_msg = '画像がDSのデータベースには未登録なので更新できませんでした：BUD_PHOTO_NO::'.$line[1];
//			throw new Exception($ret_error_msg);
//		}
//
//		if($line[0] === 'D' && is_null($photo)){
//			$ret_error_msg = '画像がDSのデータベースには未登録なので削除できませんでした:BUD_PHOTO_NO::'.$line[1];
//			throw new Exception($ret_error_msg);
//		}

		if(empty(trim($line[8]))){
			$ret_error_msg = "MALL_NO:".$line[1].':::画像の写真名がないためが処理できません。';
			throw new Exception($ret_error_msg);
		}

		if(trim($line[16])===""){
			$ret_error_msg = "MALL_NO:".$line[1].':::画像の公開制限がないためが処理できません。';
			throw new Exception($ret_error_msg);
		}

		return true;
	} catch (Exception $e) {
		$ret_error_msg = $e->getMessage();
		throw $e;
	}
}

/*
 * 関数名：set_insert_data
 * 関数説明：画像情報新規用のデータを設定する
 * パラメタ：無し
 * 戻り値：無し
 */
function set_insert_data(&$pi, $d_ary)
{
	//写真管理番号
	$pi->photo_mno = $d_ary["photo_mno"];

	//BUD_PHOTO番号
	$pi->bud_photo_no = $d_ary["bud_photo_no"];
	$pi->mall_no = $d_ary["mall_no"];

	// 元画像管理番号
	$reg_mate_mana = array_get_value($d_ary,"reg_mate_mana" ,"");
	$pi->source_image_no = $reg_mate_mana;
	if (empty($reg_mate_mana) || strlen($reg_mate_mana) <= 0) $pi->source_image_no = "元画像なし";

	// 写真名（タイトル）
	$pi->photo_name = array_get_value($d_ary,"reg_subject" ,"");

	// 撮影時期１
	$pi->take_picture_time_id = array_get_value($d_ary,"time2" ,"");
	// 撮影時期２
	$pi->take_picture_time2_id = array_get_value($d_ary,"rad_kisetu" ,"");

	// 写真入手元内容
	$reg_p_obtaining_txt = array_get_value($d_ary,"reg_p_obtaining_txt" ,"");
	$pi->borrowing_ahead_id = 0;
	if(!empty($reg_p_obtaining_txt)) $pi->borrowing_ahead_id = 2;
	$pi->content_borrowing_ahead = $reg_p_obtaining_txt;

	// 付加条件
	$val = array_get_value($d_ary, "reg_addition", "");
	$val = mb_ereg_replace('^[　]+', '', $val); // 先頭の全角スペース削除
	$pi->additional_constraints1 = mb_ereg_replace('[　]+$', '', $val); // 末尾の全角スペース削除

	// 掲載可能範囲
	$reg_pub_possible_txt = array_get_value($d_ary,"reg_pub_possible_txt" ,"");
	$pi->range_of_use_id = 0;
	$pi->use_condition = "";
	if($reg_pub_possible_txt=="できません" || $reg_pub_possible_txt=="できない" || $reg_pub_possible_txt=="トラベルコムのみ"){
		$pi->range_of_use_id = 1;
		$pi->use_condition = "";
	}else{
		if(!empty($reg_pub_possible_txt)){
			$pi->range_of_use_id = 3;
			$pi->use_condition = $reg_pub_possible_txt;
		}
	}

	// 期間
	$pi->dto = array_get_value($d_ary,"p_dto" ,"");
	$pi->kikan = array_get_value($d_ary,"reg_pub_period" ,"");
	$pi->is_extension = array_get_value($d_ary,"reg_pub_period" ,"0");

	// お客様部署
	$pi->customer_section = array_get_value($d_ary,"post_name" ,"");
	// お客様名
	$pi->customer_name = array_get_value($d_ary,"customer_name" ,"");
	// 備考
	$pi->note = array_get_value($d_ary,"reg_remarks" ,"");
	// キーワード文字列（スペース区切り）
	$pi->keyword_str = array_get_value($d_ary, 'p_keyword_str' ,"");

	//写真新規アカウント
	$pi->registration_account = $d_ary["registration_account"];
	//写真新規アカウント（名前）
	$pi->registration_person = $d_ary["registration_person"];
	//写真新規日付
	$pi->register_date = $d_ary["register_date"];
	//写真許可アカウント
	$pi->permission_account = $d_ary["permission_account"];
	//写真許可アカウント（名前）
	$pi->permission_person = $d_ary["permission_person"];
	//写真許可日付
	$pi->permission_date = $d_ary["permission_date"];
	$pi->is_mall = $d_ary["is_mall"];
	//自動3年延期
	$pi->is_extension = $d_ary["is_extension"];
	//掲載状態(掲載許可)
	$pi->publishing_situation_id = $d_ary["publishing_situation_id"];
	//掲載期間TO
	$pi->dto = $d_ary["p_dto"];
	//掲載期間From
	$pi->dfrom = $d_ary["p_from"];
	$pi->kikan = $d_ary["reg_pub_period"];
	$pi->photo_server_flg = 0;
	$pi->is_publish = $d_ary["is_publish"];
}

?>
