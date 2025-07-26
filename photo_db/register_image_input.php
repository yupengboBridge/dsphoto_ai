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

// アクション
$p_action = array_get_value($_REQUEST, 'p_action' ,"");
// PhotoImageのインスタンスを生成します。
$pi = new PhotoImageDB ();
// 初期表示フラグ
$initflg = array_get_value($_REQUEST, 'initflg' ,"");
//$initflg = 2;
try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	// アップロードの処理
	if ($p_action == "uploadfile")
	{
		// 正常にアップロードした場合、メッセージを表示し、登録確認画面に遷移する。
		if (uploadfile())
		{
			print "<script type=\"text/javascript\">";
			print "alert(\"画像をDBに登録しました。\");";
			print "parent.bottom.location.href  = \"./register_image_confirm.php\";";
			print "</script>";
		}
	// 画面表示用のデータをDBから抽出する
	} else {
		$pi->get_take_picture_time($db_link,$take_picture_time_id,$take_picture_time_name);						// 撮影時期１
		$pi->get_take_picture_time2($db_link,$take_picture_time2_id,$take_picture_time2_name);					// 撮影時期2

		//IE6.0
		if(strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0"))
		{
			//------分類1-------------------------------------------------------------------------
			$pi->get_classification($db_link, $classification_id1, $classification_name1);							// 分類
			$pi->get_direction($db_link,$direction_id1,$direction_name1,$classification_id1);						// 方面
			$pi->get_country_prefecture($db_link,$country_prefecture_id1,$country_prefecture_name1,$direction_id1);	// 国・都道府県
			$pi->get_place($db_link,$place_id1,$place_name1,$country_prefecture_id1);								// 地名
		} else {
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
		}

		$pi->get_category($db_link,$category_id,$category_name);												// カテゴリ

		$pi->get_registration_division($db_link,$registration_id,$registration_name);							// 登録区分

		$pi->get_range_of_use($db_link,$range_id,$range_name);													// 使用範囲
		$pi->get_borrowing_ahead($db_link,$borrow_id,$borrow_name);												// 写真入手元
	}
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

/*
 * 関数名：uploadfile
 * 関数説明：ファイルのアップ
 * パラメタ：無し
 * 戻り値：アップロードは成功するかどうか、true/false
 */
function uploadfile()
{
	global $pi, $db_link;

	// アップロード用のオブジェクト
	$fl = NULL;

	try
	{	
		$tmp1 = urldecode(array_get_value($_REQUEST,"reg_addition0" ,""));
		// アップロード用のインスタンスを生成します。
		$fl = new FileUpload($_FILES['p_photo_filename'], "", "", "", "", $tmp1, "");
		// ファイルをアップロードします。
		$fl->upload();
		// サムネイルを作成します。
		$fl->make_thumbfile();
		// DB保存用のデータを設定します。
		$pi->up_url = $fl->up_url;					// アップロードURL
		$pi->img_width = $fl->img_width;			// イメージサイズ（横）
		$pi->img_height = $fl->img_height;			// イメージサイズ（縦）
		$pi->ext = $fl->ext;						// 拡張子
		$pi->image_size_x = $fl->img_width[0];		// 画像サイズ（横）
		$pi->image_size_y = $fl->img_height[0];		// 画像サイズ（縦）
		// 画像情報新規用のデータを設定します。
		set_insertdata();
		// DBにアップロードした画像情報を新規登録します。
		$pi->insert_data($db_link);

		return true;
	}
	catch(Exception $e)
	{
		// アップロードしたファイルを削除します。
		if ($fl!=null)
		{
			$fl->delete_upfile();
		}
		$message = $e->getMessage();

        print "<script type=\"text/javascript\">";
        print "alert(\"".$message."\");";
        print "history.go(-1);";
        print "</script>";

		//throw new Exception($message);
		return false;
	}
}

/*
 * 関数名：set_insertdata
 * 関数説明：画像情報新規用のデータを設定する
 * パラメタ：無し
 * 戻り値：無し
 */
function set_insertdata()
{
	global $pi,$comp_code;

	$_SESSION['p_photo_filename'] = array_get_value($_POST,"p_photo_filename" ,"");		// 画像ファイル

	$pi->photo_mno = "00000";															// 初期値を設定する
	$pi->comp_code = $comp_code;														// ユーザー管理番号を設定する
	$pi->publishing_situation_id = 1;													// 掲載状況(「申請中」を設定する)

	$pi->registration_division_id = array_get_value($_POST,"reg_division" ,"");			// 登録区分
	$_SESSION['registration_division_id'] = $pi->registration_division_id;				// 登録区分

	$reg_mate_mana = array_get_value($_POST,"reg_mate_mana" ,"");						// 元画像管理番号
	$_SESSION['reg_mate_mana'] = $reg_mate_mana;										// 元画像管理番号
	// BUD_PHOTO番号を入力しない場合
	if (empty($reg_mate_mana) || strlen($reg_mate_mana) <= 0)
	{
		$pi->source_image_no = "元画像なし";											// 元画像管理番号「元画像なし」を設定する
	} else {
		$pi->source_image_no = array_get_value($_POST,"reg_mate_mana" ,"");				// 元画像管理番号
	}
	$_SESSION['source_image_no'] = $pi->source_image_no;								// 元画像管理番号

	$reg_bud_number = array_get_value($_POST,"reg_bud_number" ,"");						// BUD_PHOTO番号
	$_SESSION['reg_bud_number'] = $reg_bud_number;										// BUD_PHOTO番号
	// BUD_PHOTO番号の「ある」を選択した場合
	if ((int)$reg_bud_number == 1)
	{
		$pi->bud_photo_no = array_get_value($_POST,"reg_bud_number_txt" ,"");			// BUD_PHOTO番号
	}
	$_SESSION['bud_photo_no'] = $pi->bud_photo_no;										// BUD_PHOTO番号

	$pi->photo_name = array_get_value($_POST,"reg_subject" ,"");						// 写真名（タイトル）
	$_SESSION['photo_name'] = $pi->photo_name;											// 写真名（タイトル）

	$pi->photo_explanation = array_get_value($_POST,"reg_material_txt" ,"");			// 写真説明
	$_SESSION['photo_explanation'] = $pi->photo_explanation;							// 写真説明

	$pi->take_picture_time_id = urldecode(array_get_value($_REQUEST,"time2" ,""));		// 撮影時期１
	$_SESSION['take_picture_time_id'] = $pi->take_picture_time_id;						// 撮影時期１

	$pi->take_picture_time2_id = array_get_value($_POST,"rad_kisetu" ,"");				// 撮影時期２
	$_SESSION['take_picture_time2_id'] = $pi->take_picture_time2_id;					// 撮影時期２

	//$pi->dfrom  = date("Y-m-d");														// 掲載期間（From）
	$pi->dfrom = array_get_value($_POST,"p_dfrom" ,"");									// 掲載期間（From）
	$_SESSION['dfrom'] = $pi->dfrom;													// 掲載期間（From）

	$pi->dto = array_get_value($_POST,"p_dto" ,"");										// 掲載期間（To）
	$_SESSION['dto'] = $pi->dto;														// 掲載期間（To）

	$pi->kikan = array_get_value($_POST,"reg_pub_period" ,"");							// 期間
	$_SESSION['kikan'] = $pi->kikan;													// 期間

	$reg_p_obtaining = array_get_value($_POST,"reg_p_obtaining" ,"");					// 写真入手元ID
	$_SESSION['borrowing_ahead_id'] = $reg_p_obtaining;									// 写真入手元
	$pi->borrowing_ahead_id = $reg_p_obtaining;											// 写真入手元
	// 写真入手元の「その他」を選択した場合
	if ((int)$reg_p_obtaining == 2)
	{
		$pi->content_borrowing_ahead = array_get_value($_POST,"reg_p_obtaining_txt" ,""); // 写真入手元内容
	}
	$_SESSION['reg_p_obtaining_txt'] = array_get_value($_POST,"reg_p_obtaining_txt" ,"");// 写真入手元内容

	$reg_pub_possible = array_get_value($_POST,"reg_pub_possible" ,"");					// 使用範囲
	$pi->range_of_use_id = $reg_pub_possible;											// 使用範囲
	$_SESSION['range_of_use_id'] = $reg_pub_possible;									// 使用範囲
	// 使用範囲の「外部出稿条件付き」を選択した場合
	if ((int)$reg_pub_possible == 3)
	{
		$pi->use_condition = array_get_value($_POST,"reg_pub_possible_txt" ,""); 		// 出稿条件
	}
	$_SESSION['use_condition'] = array_get_value($_POST,"reg_pub_possible_txt" ,""); 	// 出稿条件

	$reg_addition = array_get_value($_POST,"reg_addition" ,"");							// 付加条件
	$_SESSION['reg_addition'] = $reg_addition;											// 付加条件
	// 付加条件の「要クレジット」を選択した場合
	if ((int)$reg_addition == 0)
	{
		$pi->additional_constraints1 = urldecode(array_get_value($_REQUEST,"reg_addition0" ,""));	// 付加条件（クレジット）
	}
	// 付加条件の「要使用許可」を選択した場合
	if ((int)$reg_addition == 1)
	{
		$pi->additional_constraints2 = urldecode(array_get_value($_REQUEST,"reg_addition1" ,""));	// 付加条件（要確認）
	}
	$_SESSION['reg_addition0'] = urldecode(array_get_value($_REQUEST,"reg_addition0" ,""));		// 付加条件（クレジット）
	$_SESSION['reg_addition1'] = urldecode(array_get_value($_REQUEST,"reg_addition1" ,""));		// 付加条件（要確認）

	$tmp = array_get_value($_POST,"reg_account" ,"");									// 独占使用
	if (empty($tmp))
	{
		$pi->monopoly_use = 0;															// 独占使用
	} else {
		$pi->monopoly_use = 1;															// 独占使用
	}
	$_SESSION['monopoly_use'] = $pi->monopoly_use;										// 独占使用

	$pi->copyright_owner = array_get_value($_POST,"reg_copyright" ,"");					// 版権所有者
	$_SESSION['copyright_owner'] = $pi->copyright_owner;								// 版権所有者

	$pi->customer_section = array_get_value($_POST,"post_name" ,"");					// お客様部署
	$_SESSION['customer_section'] = array_get_value($_POST,"post_name" ,"");			// お客様部署

	$pi->customer_name = array_get_value($_POST,"first_name" ,"");						// お客様名
	$_SESSION['customer_name'] = array_get_value($_POST,"first_name" ,"");				// お客様名

	$pi->registration_account = array_get_value($_POST,"reg_apply_id" ,"");				// 登録申請アカウント
	$_SESSION['registration_account'] = array_get_value($_POST,"reg_apply_id" ,"");		// 登録申請アカウント

	$pi->registration_person = array_get_value($_POST,"reg_apply" ,"");					// 登録申請者
	$_SESSION['registration_person'] = array_get_value($_POST,"reg_apply" ,"");			// 登録申請者

//	$pi->permission_account = array_get_value($_POST,"reg_permission_id");				// 登録許可アカウント
//	$pi->permission_person =  array_get_value($_POST,"reg_permission");					// 登録許可者
//	$pi->permission_date = date("Y-m-d");												// 登録許可日

	$pi->note = array_get_value($_POST,"reg_remarks" ,"");								// 備考
	$_SESSION['note'] = array_get_value($_POST,"reg_remarks" ,"");						// 備考

	$pi->register_date = date("Y/m/d H:i:s");											// 登録日、システムの日付を設定する

	$pi->keyword_str = array_get_value($_POST, 'p_keyword_str' ,"");					// キーワード文字列（スペース区切り）
	$_SESSION['p_keyword_str'] = array_get_value($_POST, 'p_keyword_str' ,"");			// キーワード文字列（スペース区切り）

	$p_classification_id1 = array_get_value($_POST, 'p_classification_id1' ,"");		// 分類ID(1)
	$_SESSION['p_classification_id1'] = $p_classification_id1;							// 分類ID(1)

	$p_direction_id1 = array_get_value($_POST, 'p_direction_id1' ,"");					// 方面ID(1)
	$_SESSION['p_direction_id1'] = $p_direction_id1;									// 方面ID(1)

	$p_country_prefecture_id1 = array_get_value($_POST, 'p_country_prefecture_id1' ,"");// 国・都道府県(1)
	$_SESSION['p_country_prefecture_id1'] = $p_country_prefecture_id1;					// 国・都道府県(1)

	$p_place_id1 = array_get_value($_POST, 'p_place_id1' ,"");							// 地名ID(1)
	$_SESSION['p_place_id1'] = $p_place_id1;											// 地名ID(1)
	// 分類ID(1)は有効の場合
	if(!empty($p_classification_id1))
	{
		// 登録分類１をDBに設定する
		$pi->registration_classifications->set_id($p_classification_id1, $p_direction_id1, $p_country_prefecture_id1, $p_place_id1);
	}

	$p_classification_id2 = array_get_value($_POST, 'p_classification_id2' ,"");		// 分類ID(2)
	$_SESSION['p_classification_id2'] = $p_classification_id2;							// 分類ID(2)

	$p_direction_id2 = array_get_value($_POST, 'p_direction_id2' ,"");					// 方面ID(2)
	$_SESSION['p_direction_id2'] = $p_direction_id2;									// 方面ID(2)

	$p_country_prefecture_id2 = array_get_value($_POST, 'p_country_prefecture_id2' ,"");// 国・都道府県ID(2)
	$_SESSION['p_country_prefecture_id2'] = $p_country_prefecture_id2;					// 国・都道府県ID(2)

	$p_place_id2 = array_get_value($_POST, 'p_place_id2' ,"");							// 地名ID(2)
	$_SESSION['p_place_id2'] = $p_place_id2;											// 地名ID(2)
	// 分類ID(2)は有効の場合
	if(!empty($p_classification_id2))
	{
		// 登録分類２をDBに設定する
		$pi->registration_classifications->set_id($p_classification_id2, $p_direction_id2, $p_country_prefecture_id2, $p_place_id2);
	}

	$p_classification_id3 = array_get_value($_POST, 'p_classification_id3' ,"");		// 分類ID(3)
	$_SESSION['p_classification_id3'] = $p_classification_id3;							// 分類ID(3)

	$p_direction_id3 = array_get_value($_POST, 'p_direction_id3' ,"");					// 方面ID(3)
	$_SESSION['p_direction_id3'] = $p_direction_id3;									// 方面ID(3)

	$p_country_prefecture_id3 = array_get_value($_POST, 'p_country_prefecture_id3' ,"");// 国・都道府県ID(3)
	$_SESSION['p_country_prefecture_id3'] = $p_country_prefecture_id3;					// 国・都道府県ID(3)

	$p_place_id3 = array_get_value($_POST, 'p_place_id3');								// 地名ID(3)
	$_SESSION['p_place_id3'] = $p_place_id3;											// 地名ID(3)
	// 分類ID(3)は有効の場合
	if(!empty($p_classification_id3))
	{
		// 登録分類３をDBに設定する
		$pi->registration_classifications->set_id($p_classification_id3, $p_direction_id3, $p_country_prefecture_id3, $p_place_id3);
	}

	$p_classification_id4 = array_get_value($_POST, 'p_classification_id4' ,"");		// 分類ID(4)
	$_SESSION['p_classification_id4'] = $p_classification_id4;							// 分類ID(4)

	$p_direction_id4 = array_get_value($_POST, 'p_direction_id4' ,"");					// 方面ID(4)
	$_SESSION['p_direction_id4'] = $p_direction_id4;									// 方面ID(4)

	$p_country_prefecture_id4 = array_get_value($_POST, 'p_country_prefecture_id4' ,"");// 国・都道府県ID(4)
	$_SESSION['p_country_prefecture_id4'] = $p_country_prefecture_id4;					// 国・都道府県ID(4)

	$p_place_id4 = array_get_value($_POST, 'p_place_id4' ,"");							// 地名ID(4)
	$_SESSION['p_place_id4'] = $p_place_id4;											// 地名ID(4)
	// 分類ID(4)は有効の場合
	if(!empty($p_classification_id4))
	{
		// 登録分類４をDBに設定する
		$pi->registration_classifications->set_id($p_classification_id4, $p_direction_id4, $p_country_prefecture_id4, $p_place_id4);
	}

	$p_classification_id5 = array_get_value($_POST, 'p_classification_id5' ,"");		// 分類ID(5)
	$_SESSION['p_classification_id5'] = $p_classification_id5;							// 分類ID(5)

	$p_direction_id5 = array_get_value($_POST, 'p_direction_id5' ,"");					// 方面ID(5)
	$_SESSION['p_direction_id5'] = $p_direction_id5;									// 方面ID(5)

	$p_country_prefecture_id5 = array_get_value($_POST, 'p_country_prefecture_id5' ,"");// 国・都道府県ID(5)
	$_SESSION['p_country_prefecture_id5'] = $p_country_prefecture_id5;					// 国・都道府県ID(5)

	$p_place_id5 = array_get_value($_POST, 'p_place_id5' ,"");							// 地名ID(5)
	$_SESSION['p_place_id5'] = $p_place_id5;											// 地名ID(5)
	
	// 分類ID(4)は有効の場合
	if(!empty($p_classification_id5))
	{
		// 登録分類５をDBに設定する
		$pi->registration_classifications->set_id($p_classification_id5, $p_direction_id5, $p_country_prefecture_id5, $p_place_id5);
	}
}

/*
 * 関数名：take_picture_year
 * 関数説明：「日付指定」の「年」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function take_picture_year()
{
	// 初期表示フラグ
	global $initflg;

	// 画面は初期表示ではない場合、それに、掲載期間は「日付指定」を選択した時
	$s_kikan = array_get_value($_SESSION,'kikan' ,"");
	if($initflg != 1 && $s_kikan == "shitei")
	{
		//print"<select name=\"select_year\" id=\"select_year\" onChange='change_year();'>\r\n";
		print"<select name=\"select_year\" id=\"select_year\">\r\n";
	} else {
		//print"<select name=\"select_year\" id=\"select_year\" onChange='change_year();' disabled=\"disabled\">\r\n";
		print"<select name=\"select_year\" id=\"select_year\" disabled=\"disabled\">\r\n";
	}

	// システム日付の「年」を取得する
	$now_year = (int)substr(date("Y-m-d"),0,4);
	print "		<option value='-1'>未定</option>\r\n";
	// システム日付から、11年後まで、「年」を出力する
	for ($i = 0; $i <= 10; $i++)
	{
		$ed_year = $now_year + $i;

		// セショーンの「掲載期間（To）」が設定した場合
		$s_dto = array_get_value($_SESSION,'dto' ,"");
		if (!empty($s_dto)) $i_year = (int)substr($s_dto,0,4);
		else $i_year = 0;

		// ①画面は初期表示ではない場合
		// ②日付指定の「年」レストから、アイテムを選択した時
		// ③掲載期間は「無期限」以外を選択した時
		if($initflg != 1 && (int)$i_year == $ed_year && $s_kikan != "mukigen")
		{
			print"	<option value=\"".$ed_year."\" selected=\"selected\">".dp($ed_year)."</option>\r\n";
		} else {
			print"	<option value=\"".$ed_year."\">".dp($ed_year)."</option>\r\n";
		}
	}
	print"</select>\r\n";
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
 * 関数名：take_picture_day
 * 関数説明：「日付指定」の「日」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function take_pictrue_day()
{
	// 初期表示フラグ
	global $initflg;

	// 画面は初期表示ではない場合、それに、掲載期間は「日付指定」を選択した時
	$s_kikan = array_get_value($_SESSION,'kikan' ,"");
	if($initflg != 1 && $s_kikan == "shitei")
	{
		print"	<select name=\"select_day\" id=\"select_day\">\r\n";
	} else {
		print"	<select name=\"select_day\" id=\"select_day\" disabled=\"disabled\">\r\n";
	}
	print "		<option value='-1'>未定</option>\r\n";
	// １～３１日を出力する
	for ($i = 1; $i <= 31; $i++)
	{
		//$s_day = DBC_SBC($i)."日";
		$s_day = $i."日";
		$day = (int)substr(array_get_value($_SESSION,'dto' ,""),-2);
		// ①画面は初期表示ではない場合
		// ②日付指定の「日」レストから、アイテムを選択した時
		// ③掲載期間は「無期限」以外を選択した時
		if ($initflg != 1 && $day == $i && $s_kikan != "mukigen")
		{
			print"		<option value=\"".$i."\" selected=\"selected\">".dp($s_day)."</option>\r\n";
		} else {
			print"		<option value=\"".$i."\">".dp($s_day)."</option>\r\n";
		}

	}
	print"	</select>\r\n";
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
	// 初期表示フラグ
	global $initflg;

	$ed = count($c_id);
	// 「登録区分」を出力する
	for ($i = 0 ; $i<$ed ; $i++)
	{
		$key_id = "reg_division".$i;
		// ①画面は初期表示ではない場合
		// ②セショーンには「登録区分」を設定した場合、
		$s_r_d_id = array_get_value($_SESSION,'registration_division_id' ,"");
		if ($initflg != 1 && (int)$s_r_d_id == (int)$c_id[$i])
		{
			print "	<label><input name=\"reg_division\" id=\"".$key_id."\" type='radio' checked=\"checked\" value=\"".$c_id[$i]."\" />".dp($c_name[$i])."</label>";
		} else {
			print "	<label><input name=\"reg_division\" id=\"".$key_id."\" type='radio' value=\"".$c_id[$i]."\" />".dp($c_name[$i])."</label>";
		}
	}
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
	// 初期表示フラグ
	global $initflg;

	$ed = count($c_id);
	// 「撮影時期」の季節を出力する
	for ($i = 0 ; $i<$ed ; $i++)
	{
		$key_id = "rad_kisetu".$i;
		// ①画面は初期表示ではない場合
		// ②セショーンには「撮影時期」の季節を設定した場合、
		$s_t_p_t_id = array_get_value($_SESSION,'take_picture_time2_id' ,"");
		if ($initflg != 1 && (int)$s_t_p_t_id == (int)$c_id[$i])
		{
			print "	<label><input name=\"rad_kisetu\" id=\"".$key_id."\" value=\"".$c_id[$i]."\" type='radio' checked />".dp($c_name[$i])."</label>";
		} else {
			print "	<label><input name=\"rad_kisetu\" id=\"".$key_id."\" type='radio' value=\"".$c_id[$i]."\" />".dp($c_name[$i])."</label>";
		}
	}
}

/*
 * 関数名：take_picture_time
 * 関数説明：「撮影時期」の月と「掲載期間」の月を出力する
 * パラメタ：
 * $c_id：	月ID
 * $c_name：　月
 * $flg:出力フラグ「1」：掲載期間の月；「0」：撮影時期の月
 * 戻り値：無し
 */
function take_picture_time($c_id, $c_name, $flg)
{
	// 初期表示フラグ
	global $initflg;

	// 撮影時期の月を出力する
	if ($flg == 0)
	{
		print"	<select id=\"take_picture_time_id0\" name=\"take_picture_time_name\">";
	}

	// 掲載期間の月を出力する
	if ($flg == 1)
	{
		// ①画面は初期表示ではない場合
		// ②セショーンに「掲載期間」は「日付指定」を設定した場合、
		$s_kikan = array_get_value($_SESSION,'kikan' ,"");
		if ($initflg != 1 && $s_kikan == "shitei")
		{
			print"	<select id=\"take_picture_time_id1\" name=\"take_picture_time_name\" onChange='calendar();'>";
		} else {
			print"	<select id=\"take_picture_time_id1\" name=\"take_picture_time_name\" disabled=\"disabled\" onChange='calendar();'>";
		}
	}

	$ed = count($c_id);
	// 掲載期間の月を出力する時
	if ($flg == 1)
	{
		print "		<option value='-1'>未定</option>\r\n";
	// 撮影時期の月を出力する時
	} else {
		print "		<option value='-1'>お選びください</option>\r\n";
	}

	// 月を出力する
	for ($i = 1 ; $i <= $ed ; $i++)
	{
		// 撮影時期の月を出力する時
		if ($flg == 0)
		{
			// ①画面は初期表示ではない場合
			// ②セショーンに「撮影時期」の月を設定した場合
			$s_t_p_t_id = array_get_value($_SESSION,'take_picture_time_id' ,"");
			if ($initflg != 1 && (int)$s_t_p_t_id == (int)$i)
			{
				print "	<option value=" .$i. " selected=\"selected\" >" . dp($c_name[$i - 1]) . "</option>\r\n";
			} else {
				print "	<option value=" .$i. ">" . dp($c_name[$i - 1]) . "</option>\r\n";
			}
		}

		// 掲載期間の月を出力する時
		if ($flg == 1)
		{
			$i_month = (int)substr(array_get_value($_SESSION,'dto' ,""),5,2);
			echo $i_month;
			// ①画面は初期表示ではない場合
			// ②セショーンに「掲載期間」は「無期限」以外を設定した場合、
			// ③セショーンに「掲載期間」の月を設定した場合
			$s_kikan = array_get_value($_SESSION,'kikan' ,"");
			if ($initflg != 1 && (int)$i_month == (int)$i && $s_kikan != "mukigen")
			{
				print "	<option value=" .$i. " selected=\"selected\" >" . dp($c_name[$i - 1]) . "</option>\r\n";
			} else {
				print "	<option value=" .$i. ">" . dp($c_name[$i - 1]) . "</option>\r\n";
			}
		}
	}
	print"	</select>";
}

/*
 * 関数名：disp_div_classification
 * 関数説明：「登録分類」を出力する
 * パラメータ：
 * c_id:分類ID；c_name:分類名;no:分類インデックス（分類１～分類５）
 * 戻り値：無し
 */
function disp_div_classification($c_id, $c_name, $no)
{
	// 初期表示フラグ
	global $initflg;

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
	$indx = (int)$no;

	// セショーンIDを作成する
	$session_id = 'p_classification_id' . $no;
	// セショーンにこの登録分類を設定した場合
	$s_p_c_id = array_get_value($_SESSION,$session_id ,"");
	if (!empty($s_p_c_id))
	{
		$tmp_class = $s_p_c_id;
	} else {
		$tmp_class = -1;
	}

	$ed = count($c_id);
	// 登録分類を表示するかどうかフラグ
	$showflg = false;

	for ($i = 0 ; $i < $ed ; $i++)
	{
		// セショーンにこの登録分類を設定した場合、登録分類を表示する
		if ((int)$tmp_class == (int)$c_id[$i])
		{
			$showflg = true;
			break;
		}
	}

	$div_key = "div_classification".$no;			// 今の登録分類
	$next = $no + 1;
	$div_key_next = "div_classification".$next;		// 次の登録分類

	// 画面は初期表示ではない場合
	if ($initflg != 1)
	{
		// この登録分類を表示する時
		if ($showflg)
		{
			print "<div id=\"".$div_key."\" style=\"display:block;\">\r\n";
		} else {
			print "<div id=\"".$div_key."\" style=\"display:none;\">\r\n";
		}
	} else {
		// 登録分類のインデックスは１の場合
		if ((int)$no == 1)
		{
			print "<div id=\"".$div_key."\" style=\"display:block;\">\r\n";
		} else {
			print "<div id=\"".$div_key."\" style=\"display:none;\">\r\n";
		}

	}

	print "<dl class=\"reg_classification reg_clear\">\r\n";
	print "<dt>登録分類".DBC_SBC($no)."</dt>\r\n";
	print "<dd>\r\n";

	// 登録分類のインデックスは１の場合
	if ((int)$no == 1)
	{
		disp_classification($classification_id1, $classification_name1, 1);
		disp_direction($direction_id1, $direction_name1, $classification_id1, 1);
		disp_country_prefecture($country_prefecture_id1, $country_prefecture_name1, $direction_id1, 1);
		disp_place($place_id1, $place_name1, $country_prefecture_id1, 1);
	// 登録分類のインデックスは２の場合
	} elseif ((int)$no == 2) {
		disp_classification($classification_id2, $classification_name2, 2);
		disp_direction($direction_id2, $direction_name2, $classification_id2, 2);
		disp_country_prefecture($country_prefecture_id2, $country_prefecture_name2, $direction_id2, 2);
		disp_place($place_id2, $place_name2, $country_prefecture_id2, 2);
	// 登録分類のインデックスは３の場合
	} elseif ((int)$no == 3) {
		disp_classification($classification_id3, $classification_name3, 3);
		disp_direction($direction_id3, $direction_name3, $classification_id3, 3);
		disp_country_prefecture($country_prefecture_id3, $country_prefecture_name3, $direction_id3, 3);
		disp_place($place_id3, $place_name3, $country_prefecture_id3, 3);
	// 登録分類のインデックスは４の場合
	} elseif ((int)$no == 4) {
		disp_classification($classification_id4, $classification_name4, 4);
		disp_direction($direction_id4, $direction_name4, $classification_id4, 4);
		disp_country_prefecture($country_prefecture_id4, $country_prefecture_name4, $direction_id4, 4);
		disp_place($place_id4, $place_name4, $country_prefecture_id4, 4);
	// 登録分類のインデックスは５の場合
	} elseif ((int)$no == 5) {
		disp_classification($classification_id5, $classification_name5, 5);
		disp_direction($direction_id5, $direction_name5, $classification_id5, 5);
		disp_country_prefecture($country_prefecture_id5, $country_prefecture_name5, $direction_id5, 5);
		disp_place($place_id5, $place_name5, $country_prefecture_id5, 5);
	}

	//IE6.0場合
	if(strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0"))
	{
		//今は何もしない
	} else {
		print "<ul class=\"bt_plus_minasu\">\r\n";
		// 登録分類のインデックスは５以外の場合、「＋」を出力する
		if ((int)$no < 5)
		{
			print "<li class=\"bt_plus\"><a href=\"#\" onclick=\"toggle_classification('".$div_key_next."',1);\">+</a></li>\r\n";
		}
		// 登録分類のインデックスは１以外の場合、「-」を出力する
		if ((int)$no > 1)
		{
			print "<li class=\"bt_minasu\"><a href=\"#\" onclick=\"toggle_classification('".$div_key."',0);\">-</a></li>\r\n";
		}
		print "</ul>\r\n";
	}

	print "</dd>\r\n";
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
	global $initflg;

	// 分類ID
	$selid = 'p_classification_id' . $no;
	// セショーンID
	$session_id = 'p_classification_id' . $no;
	print "	<select name=\"" . $selid . "\" id=\"" . $selid . "\">\r\n";
	//print "		<option value='-1'>お選びください</option>\r\n";
	print "		<option value='-1'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";
	$ed = count($c_id);
	for ($i = 0 ; $i < $ed ; $i++)
	{
		// ①画面は初期表示ではない
		// ②セショーンに分類を設定した場合
		$s_p_c_id = array_get_value($_SESSION,$session_id ,"");
		//print $s_p_c_id;
		if ($initflg != 1 && (int)$s_p_c_id == (int)$c_id[$i])
		{
			print "	<option value=\"" . $c_id[$i] . "\" selected=\"selected\" >" . dp($c_name[$i]) . "</option>\r\n";
		} else {
			print "	<option value=\"" . $c_id[$i] . "\">" . dp($c_name[$i]) . "</option>\r\n";
		}
	}
	print "	</select>\r\n";
}

/*
 * 関数名：init_disp_direction
 * 関数説明：「登録分類」を出力する、必ず、init_disp_classification()の後に実行してください。
 * パラメータ：
 * d_id:方面ID；d_name:方面名;c_id:分類ID;no:方面インデックス（方面１～方面５）
 * 戻り値：無し
 */
function init_disp_direction($d_id, $d_name, $c_id, $no)
{
	// セショーンIDを作成する
	$session_id = 'p_direction_id' . $no;
	// セッションから方面を取得する
	$s_p_d_id = array_get_value($_SESSION,$session_id ,"");
	// 方面を登録した場合、DBから取得する
	if (!empty($s_p_d_id))
	{
		$tmp_direct = $s_p_d_id;
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
			if ((int)$tmp_direct == (int)$d_id[$i][$j])
			{
				print "	<option value='" . $d_id[$i][$j] . "' selected=\"selected\">" . dp(santen_reader($d_name[$i][$j],18)) . "</option>\r\n";
			} else {
				//print "	<option value='" . $d_id[$i][$j] . "'>" . dp(santen_reader($d_name[$i][$j], 5)) . "</option>\r\n";
			}
		}
	}
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
	global $initflg;

	// 方面IDを作成する
	$selid = 'p_direction_id' . $no;
	print "	<select name='" . $selid . "' id='" . $selid . "' >\r\n";

	print "		<option value='-1'>方面&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";

	// 画面の初期化ではない時
	if ($initflg != 1)
	{
		// 方面を初期表示します。
		init_disp_direction($d_id, $d_name, $c_id, $no);
	}

	$ed = count($c_id);
	// 分類より繰り返し
	for ($i = 0 ; $i < $ed ; $i++)
	{
		$ed2 = count($d_id[$i]);
		print "	<optgroup label='" . $c_id[$i] . "'>\r\n";
		// 方面より繰り返し
		for ($j = 0 ; $j < $ed2 ; $j++)
		{
			print "	<option value='" . $d_id[$i][$j] . "'>" . dp(santen_reader($d_name[$i][$j], 18)) . "</option>\r\n";
		}
		print "	</optgroup>\r\n";
	}
	print "	</select>\r\n";
}

/*
 * 関数名：init_disp_country_prefecture
 * 関数説明：「登録分類」を出力する、必ず、init_disp_direction()の後に実行してください。
 * パラメータ：
 * cp_id:国・都道府県ID；cp_name:国・都道府県名;
 * d_id:方面ID;
 * no:国・都道府県インデックス（国・都道府県１～国・都道府県５）
 * 戻り値：無し
 */
function init_disp_country_prefecture($cp_id, $cp_name, $d_id, $no)
{
	// セショーンIDを作成する
	$session_id = "p_country_prefecture_id".$no;
	// セッションから国・都道府県を取得する
	$s_p_c_p_id = array_get_value($_SESSION,$session_id ,"");
	// 国・都道府県を登録した場合、DBから取得する
	if (!empty($s_p_c_p_id))
	{
		$tmp_country = $s_p_c_p_id;
	} else {
		$tmp_country = -1;
	}

	$ed = count($d_id);
	// 方面（海外）に分けて、国を取得します。
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
				if ((int)$tmp_country == (int)$cp_id[$i][$j][$k])
				{
					print "	<option value='" . $cp_id[$i][$j][$k] . "' selected=\"selected\">" . dp(santen_reader($cp_name[$i][$j][$k], 9)) . "</option>\r\n";
				} else {
					//print "	<option value='" . $cp_id[$i][$j][$k] . "'>" . dp(santen_reader($cp_name[$i][$j][$k], 5)) . "</option>\r\n";
				}
			}
		}
	}
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
	global $initflg;

	// 国・都道府県IDを作成する
	$selid = 'p_country_prefecture_id' . $no;

	print "	<select name='" . $selid . "' id='" . $selid . "' >\r\n";
	print "		<option value='-1'>国・都道府県&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";

	// 画面の初期化ではない時
	if ($initflg != 1)
	{
		// 国・都道府県を初期表示します。
		init_disp_country_prefecture($cp_id, $cp_name, $d_id, $no);
	}

	$ed = count($d_id);
	// 方面（海外）に分けて、国を取得します。
	// 方面より繰り返し
	for ($i = 0 ; $i < $ed ; $i++)
	{
		$ed2 = count($d_id[$i]);
		// 方面より繰り返し
		for ($j = 0 ; $j < $ed2 ; $j++)
		{
			$ed3 = count($cp_id[$i][$j]);
			print "	<optgroup label='" . $d_id[$i][$j] . "'>\r\n";
			// 国・都道府県より繰り返し
			for ($k = 0 ; $k < $ed3 ; $k++)
			{
				print "	<option value='" . $cp_id[$i][$j][$k] . "'>" . dp(santen_reader($cp_name[$i][$j][$k], 9)) . "</option>\r\n";
			}
			print "	</optgroup>\r\n";
		}
	}
	print "	</select>\r\n";
}

/*
 * 関数名：init_disp_place
 * 関数説明：「登録分類」を出力する、必ず、init_disp_country_prefecture()の後に実行してください。
 * パラメータ：
 * p_id:都市ID；p_name:都市名;
 * cp_id:国・都道府県ID;
 * no:都市インデックス（都市１～都市５）
 * 戻り値：無し
 */
function init_disp_place($p_id, $p_name, $cp_id, $no)
{
	// セショーンIDを作成する
	$session_id = "p_place_id".$no;
	// セッションから都市を取得する
	$s_p_p_id = array_get_value($_SESSION,$session_id ,"");
	// 地名を登録した場合、DBから取得する
	if (!empty($s_p_p_id))
	{
		$tmp_place = $s_p_p_id;
	} else {
		$tmp_place = -1;
	}

	// 地名を表示します。
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
					if ((int)$tmp_place == (int)$p_id[$i][$j][$k][$l])
					{
						print "		<option value='" . $p_id[$i][$j][$k][$l] . "' selected=\"selected\">" . dp(santen_reader($p_name[$i][$j][$k][$l], 8)) . "</option>\r\n";
					} else {
						//print "		<option value='" . $p_id[$i][$j][$k][$l] . "'>" . dp(santen_reader($p_name[$i][$j][$k][$l], 5)) . "</option>\r\n";
					}
				}
			}
		}
	}
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
	global $initflg;

	// 地名IDを作成する
	$selid = 'p_place_id' . $no;
	print "	<select name='". $selid . "' id='" . $selid . "' >\r\n";
	//print "		<option value='-1'>お選びください</option>\r\n";
	print "		<option value='-1'>都市&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";

	// 画面の初期化ではない時
	if ($initflg != 1)
	{
		// 地名を初期表示します。
		init_disp_place($p_id, $p_name, $cp_id, $no);
	}

	// 地名を表示します。
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
				print "	<optgroup label='" . $cp_id[$i][$j][$k] . "'>\r\n";
				// 地名より繰り返し
				for ($l = 0 ; $l < $ed4 ;$l++)
				{
					print "		<option value='" . $p_id[$i][$j][$k][$l] . "'>" . dp(santen_reader($p_name[$i][$j][$k][$l], 8)) . "</option>\r\n";
				}
				print "	</optgroup>\r\n";
			}
		}
	}
	print "	</select>\r\n";
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
		if ($ary[$i] == $fndstr)
		{
			return $i;
		}
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
	global $initflg;

	// キーワードーをセショーンに設定した場合
	$s_p_k_s = array_get_value($_SESSION,'p_keyword_str',"");
	if (!empty($s_p_k_s))
	{
		$kwd_a = array();
		// スペース区切りの文字列を配列にします。
		$kwd_a = explode(" ", $s_p_k_s);
	}

	$dc = count($cg_id);
	// カテゴリー(親)より繰り返し
	for ($i=0;$i < $dc;$i++)
	{
		print "<li class='reg_list reg_clear'> <em>";
		// カテゴリーIDを作成する
		$id = "ct_" . $i . "_0";
		// 画面は初期表示ではない時
		if ($initflg != 1)
		{
			// セショーンにキーワードを設定した場合
			if (check_array_index($kwd_a,dp($cg_name[$i][0])) != -1)
			{
				print "<input id=\"".$id."\" type=\"checkbox\" category='0' checked=\"checked\" value='".dp($cg_name[$i][0])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][0])."</em>";
			} else {
				print "<input id=\"".$id."\" type=\"checkbox\" category='0' value='".dp($cg_name[$i][0])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][0])."</em>";
			}
		} else {
			print "<input id=\"".$id."\" type=\"checkbox\" category='0' value='".dp($cg_name[$i][0])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][0])."</em>";
		}

		print "<ul class='reg_list_child'>";

		$dc2 = count($cg_id[$i]);
		// カテゴリー(子)より繰り返し
		for($j = 1;$j < $dc2; $j++)
		{
			$s_len = mb_strlen($cg_name[$i][$j]);

			$id = "ct_" . $i . "_" . $j;
			// カテゴリーの文字列のサイズは６以上の場合
			if ($s_len >= 6)
			{
				// 画面は初期表示ではない時
				if ($initflg != 1)
				{
					// セショーンにキーワードを設定した場合
					if (check_array_index($kwd_a,dp($cg_name[$i][$j])) != -1)
					{
						print "<li class='wide140'><input id=\"".$id."\" type=\"checkbox\" category='0' checked=\"checked\" value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
					} else {
						print "<li class='wide140'><input id=\"".$id."\" type=\"checkbox\" category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
					}
				} else {
					print "<li class='wide140'><input id=\"".$id."\" type=\"checkbox\" category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
				}
			}else
			{
				// 画面は初期表示ではない時
				if ($initflg != 1)
				{
					// セショーンにキーワードを設定した場合
					if (check_array_index($kwd_a,dp($cg_name[$i][$j])) != -1)
					{
						print "<li><input id=\"".$id."\" type=\"checkbox\" category='0' checked=\"checked\" value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
					} else {
						print "<li><input id=\"".$id."\" type=\"checkbox\" category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
					}
				} else {
					print "<li><input id=\"".$id."\" type=\"checkbox\" category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
				}
			}
		}

		print "</ul>";
		print "</li>";
	}
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
	global $initflg;

	$ed = count($r_id);

	// 掲載可能範囲の初期表示フラグ
	$flg = false;
	// 掲載可能範囲より繰り返し
	for ($i=0;$i < $ed;$i++)
	{
		// 掲載可能範囲IDを作成する
		$key_id = "reg_pub_possible".$i;
		// 掲載可能範囲の「外部出稿条件付き」を選択した場合
		if ((int)$r_id[$i] == 3)
		{
			print"	<dd class=\"outside\">\r\n";
			// ①画面は初期表示ではない時
			// ②セショーンに掲載可能範囲を設定した場合
			$s_r_id = array_get_value($_SESSION,'range_of_use_id' ,"");
			$s_u_c = array_get_value($_SESSION,'use_condition' ,"");
			if ($initflg != 1 && (int)$s_r_id == (int)$r_id[$i])
			{
				print"		<label><input name=\"reg_pub_possible\" id=\"".$key_id."\" type=\"radio\" checked=\"checked\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
				print"		<input name=\"reg_pub_possible_txt\" type=\"text\" id=\"reg_pub_possible_txt\" size=\"30\" value=".$s_u_c." />\r\n";
			} else {
				print"		<label><input name=\"reg_pub_possible\" id=\"".$key_id."\" type=\"radio\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
				print"		<input name=\"reg_pub_possible_txt\" type=\"text\" id=\"reg_pub_possible_txt\" size=\"30\" disabled/>\r\n";
			}
			print"	</dd>\r\n";
		} else {
			// 掲載可能範囲は初期表示する時
			if ($flg == false)
			{
				print"	<dd>\r\n";
				$flg = true;
			}
			// ①画面は初期表示ではない時
			// ②セショーンに掲載可能範囲を設定した場合
			$s_r_id = array_get_value($_SESSION,'range_of_use_id' ,"");
			if ($initflg != 1 && (int)$s_r_id == (int)$r_id[$i])
			{
				print"		<label><input name=\"reg_pub_possible\" id=\"".$key_id."\" type=\"radio\" checked=\"checked\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
			} else {
				print"		<label><input name=\"reg_pub_possible\" id=\"".$key_id."\" type=\"radio\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
			}
		}
	}

	if ($flg) print"	</dd>\r\n";
}

/*
 * 関数名：disp_borrowing_ahead
 * 関数説明：「写真入手元」を出力する
 * パラメタ：
 * $r_id：	　写真入手元ID
 * $r_name：  　写真入手元
 * 戻り値：無し
 */
 //added by wangtongchao 2011-11-28 begin
 function disp_borrowing_ahead($b_head_id,$b_head_name)
{
	global $initflg;

	$ed = count($b_head_id);

	// 写真入手元の初期表示フラグ
	$flg = false;
	for ($i=0;$i < $ed;$i++)
	{
		$key_id = "reg_p_obtaining".$i;
		// 写真入手元の「その他」を選択した場合
		if ((int)$b_head_id[$i] == 2)
		{
			print"	<dd class=\"other\">\r\n";
			// ①画面は初期表示ではない時
			// ②セショーンに写真入手元を設定した場合
			$s_b_a_id = array_get_value($_SESSION,'borrowing_ahead_id' ,"");
			$s_r_p_o_txt = array_get_value($_SESSION,'reg_p_obtaining_txt' ,"");
			if ($initflg != 1 && (int)$s_b_a_id == (int)$b_head_id[$i])
			{
				print"		<input name=\"reg_p_obtaining\" id=\"".$key_id."\" type=\"radio\" value=".$b_head_id[$i]." checked=\"checked\" onclick='change_obtaining_radio(this);' style=\"display:none\" />";
				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" value=".$s_r_p_o_txt." size=\"30\" />\r\n";
			} else {
				print"		<input name=\"reg_p_obtaining\" id=\"".$key_id."\" type=\"radio\" value=".$b_head_id[$i]." checked=\"checked\" onclick='change_obtaining_radio(this);' style=\"display:none\" />";
				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" size=\"30\"/>\r\n";
			}
			print"	</dd>\r\n";
		} else {
			// 写真入手元は初期表示する時
			if ($flg == false)
			{
				print"	<dd>\r\n";
				$flg = true;
			}
			// ①画面は初期表示ではない時
			// ②セショーンに写真入手元を設定した場合
			$s_b_a_id = array_get_value($_SESSION,'borrowing_ahead_id' ,"");
			if ($initflg != 1 && (int)$s_b_a_id == (int)$b_head_id[$i])
			{
				print"		<input name=\"reg_p_obtaining\" id=\"".$key_id."\"type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' style=\"display:none\" />";
			} else {
				print"		<input name=\"reg_p_obtaining\" id=\"".$key_id."\"type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' style=\"display:none\" />";
			}
		}
	}

	if ($flg) print"	</dd>\r\n";
}
//added by wangtongchao 2011-11-28 end

 //deleted by wangtongchao 2011-11-28 begin
//function disp_borrowing_ahead($b_head_id,$b_head_name)
//{
//	global $initflg;
//
//	$ed = count($b_head_id);
//
//	// 写真入手元の初期表示フラグ
//	$flg = false;
//	for ($i=0;$i < $ed;$i++)
//	{
//		$key_id = "reg_p_obtaining".$i;
//		// 写真入手元の「その他」を選択した場合
//		if ((int)$b_head_id[$i] == 2)
//		{
//			print"	<dd class=\"other\">\r\n";
//			// ①画面は初期表示ではない時
//			// ②セショーンに写真入手元を設定した場合
//			$s_b_a_id = array_get_value($_SESSION,'borrowing_ahead_id' ,"");
//			$s_r_p_o_txt = array_get_value($_SESSION,'reg_p_obtaining_txt' ,"");
//			if ($initflg != 1 && (int)$s_b_a_id == (int)$b_head_id[$i])
//			{
//				print"		<label><input name=\"reg_p_obtaining\" id=\"".$key_id."\" type=\"radio\" value=".$b_head_id[$i]." checked=\"checked\" onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" value=".$s_r_p_o_txt." size=\"30\" />\r\n";
//			} else {
//				print"		<label><input name=\"reg_p_obtaining\" id=\"".$key_id."\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" size=\"30\" disabled/>\r\n";
//			}
//			print"	</dd>\r\n";
//		} else {
//			// 写真入手元は初期表示する時
//			if ($flg == false)
//			{
//				print"	<dd>\r\n";
//				$flg = true;
//			}
//			// ①画面は初期表示ではない時
//			// ②セショーンに写真入手元を設定した場合
//			$s_b_a_id = array_get_value($_SESSION,'borrowing_ahead_id' ,"");
//			if ($initflg != 1 && (int)$s_b_a_id == (int)$b_head_id[$i])
//			{
//				print"		<label><input name=\"reg_p_obtaining\" id=\"".$key_id."\"type=\"radio\" checked=\"checked\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//			} else {
//				print"		<label><input name=\"reg_p_obtaining\" id=\"".$key_id."\"type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//			}
//		}
//	}
//
//	if ($flg) print"	</dd>\r\n";
//}
 //deleted by wangtongchao 2011-11-28 end

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>新規登録画面｜BUD PHOTO WEB</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="css/master.css" type="text/css" media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script type="text/javascript" src="./js/common.js"  charset="utf-8"></script>
<script type='text/javascript' src='./js/dateformat/dateformat.js' charset="utf-8"></script>
<script type="text/javascript" src="./js/ConnectedSelect/ConnectedSelect2.js" charset="utf-8"></script>
<script type="text/javascript">
<!--
// 日付のフォーマット
var dateFormat = new DateFormat("yyyy-MM-dd");
var TimeID=null;

function setFocus(fn)
{
	clearTimeout(TimeID);
	document.forms[0][fn].focus();
}

/**
 * 全角であるかをチェックします。
 *
 * @param チェックする値
 * @return ture : 全角 / flase : 全角以外
 */
function checkIsZenkaku(value)
{
	for (var i = 0; i < value.length; ++i)
	{
		var c = value.charCodeAt(i);
		//  半角カタカナは不許可
		if (c < 256 || (c >= 0xff61 && c <= 0xff9f))
		{
			return false;
		}
	}
	return true;
}

/*
 * 関数名：change_kikan
 * 関数説明：掲載期間の設定
 * パラメタ：th:コントロール
 * 戻り値：無し
 */
function change_kikan(th)
{
	// 掲載期間の「月」を取得する
	var objs_month = document.getElementsByName("take_picture_time_name");
	if (objs_month) var obj_month = objs_month[1];
	// 掲載期間の「日付指定」を選択した時
	if (th.value == 'shitei')
	{
		// 日付の「年」のインデックスを設定する
		document.register_image_input.select_year.selectedIndex = 0;
		// 日付の「日」のインデックスを設定する
		document.register_image_input.select_day.selectedIndex = 0;
		// 日付の「月」のインデックスを設定する
		if (obj_month) obj_month.selectedIndex = 0;

		// 日付の「年」は有効になる
		document.register_image_input.select_year.disabled = false;
		// 日付の「日」は有効になる
		document.register_image_input.select_day.disabled = false;
		// 日付の「月」は有効になる
		if (obj_month) obj_month.disabled = false;
		return ;
	}

	// システムの日付を取得する
	var fdt = new Date();
	// システム日付から、年を取得する
	var year = fdt.getYear();
	// システム日付から、月を取得する
	var mon = fdt.getMonth();
	// システム日付から、日を取得する
	var day = fdt.getDate();
	// 掲載期間の「無期限」を選択した場合
	if (th.value == 'mukigen')
	{
		year += 100;
	}
	// 掲載期間の「三か月」を選択した場合
	else if (th.value == 'sankagetu')
	{
		mon += 3;
	}
	// 掲載期間の「六か月」を選択した場合
	else if (th.value == 'hantoshi')
	{
		mon += 6;
	}
	// 掲載期間の「一年間」を選択した場合
	else if (th.value == 'ichinen')
	{
		year += 1;
	}
	//added by wangtongchao 2011-12-01 begin
	// 掲載期間の「二年間」を選択した場合
	else if (th.value == 'ninen')
	{
		year += 2;
	}
	// 掲載期間の「三年間」を選択した場合
	else if (th.value == 'sannen')
	{
		year += 3;
	}
	//added by wangtongchao 2011-12-01 end
	if (year < 1900)
	{
		year += 1900;
	}

	// 設定後の日付をフォーマット
	var tdt = new Date(year, mon, day);

	// 掲載期間の「無期限」以外を選択した場合
	if (th.value != 'mukigen')
	{
		// 年を取得する
		yr = parseInt(tdt.getYear());
		if (yr < 1900)
		{
			yr += 1900;
		}
		// 年を設定する
		document.register_image_input.select_year.value = yr;
		// 月を設定する
		if (obj_month) obj_month.value = tdt.getMonth() + 1;
		// 日を設定する
		document.register_image_input.select_day.value = tdt.getDate();
	}
	else
	{
		// 年を設定する
		document.register_image_input.select_year.value = -1;
		// 月を設定する
		if (obj_month) obj_month.value = -1;
		// 日を設定する
		document.register_image_input.select_day.value = -1;
	}

	// 掲載期間（To）を設定する。
	disp_to =  dateFormat.format(tdt);
	document.register_image_input.p_dto.value = disp_to;

	// 年を無効になる
	document.register_image_input.select_year.disabled = true;
	// 日を無効になる
	document.register_image_input.select_day.disabled = true;
	// 月を無効になる
	if (obj_month) obj_month.disabled = true;
}

/*
 * 関数名：change_reg_addition
 * 関数説明：付加条件の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
 /*deleted by wangtongchao 2011-11-26 begin
function change_reg_addition(obj)
{
	var key = "reg_addition_txt";
	// 付加条件のテキストボックスを取得する
	var objs_txt = document.getElementsByName(key);

	// 選択した付加条件の値を取得する
	var indx1 = parseInt(obj.value);
	var indx2 = null;

	// 付加条件の「要使用許可」を選択した場合
	if (indx1 == 2)
	{
		objs_txt[0].disabled = true;
		objs_txt[1].disabled = true;
		return;
	}

	// 付加条件の「要使用許可」を選択した場合
	if (indx1 == 1) indx2 = 0;
	// 付加条件の「要クレジット」を選択した場合
	else indx2 = 1;

	// 選択した付加条件の対応テキストボックスを有効になる
	if (objs_txt && objs_txt[indx1])
	{
		objs_txt[indx1].disabled = false;
		objs_txt[indx1].value="";
	}

	// 選択しない付加条件の対応テキストボックスを無効になる
	if (objs_txt && objs_txt[indx2])
	{
		objs_txt[indx2].disabled = true;
		objs_txt[indx2].value="";
	}
}
deleted by wangtongchao end*/
//added by wangtongchao 2011-11-26 begin
function change_reg_addition(obj)
{
	var key = "reg_addition_txt";
	// 付加条件のテキストボックスを取得する
	var objs_txt = document.getElementsByName(key);

	// 選択した付加条件の値を取得する
	var indx1 = parseInt(obj.value);
	var indx2 = null;

	// 付加条件の「要使用許可」を選択した場合
	if (indx1 == 2)
	{
		objs_txt[0].disabled = true;
		objs_txt[1].disabled = true;
		objs_txt[2].disabled = true;
		objs_txt[0].value = "";
		objs_txt[1].value = "";
		objs_txt[2].value = "";
		return;
	}

//	// 付加条件の「要使用許可」を選択した場合
//	if (indx1 == 1) indx2 = 0;
//	// 付加条件の「要クレジット」を選択した場合
//	else indx2 = 1;
//
//	// 選択した付加条件の対応テキストボックスを有効になる
//	if (objs_txt && objs_txt[indx1])
//	{
//		objs_txt[indx1].disabled = false;
//		objs_txt[indx1].value="";
//	}
//
//	// 選択しない付加条件の対応テキストボックスを無効になる
//	if (objs_txt && objs_txt[indx2])
//	{
//		objs_txt[indx2].disabled = true;
//		objs_txt[indx2].value="";
//	}
	if (indx1 == 1)
	{
		objs_txt[0].disabled = true;
		objs_txt[1].disabled = true;
		objs_txt[2].disabled = false;
		objs_txt[0].value = "";
		objs_txt[1].value = "";
		objs_txt[2].value = "";
		return;
	}

	if (indx1 == 0)
	{
		objs_txt[0].disabled = false;
		objs_txt[1].disabled = false;
		objs_txt[2].disabled = true;
		objs_txt[0].value = "";
		objs_txt[1].value = "";
		objs_txt[2].value = "";
		return;
	}
	
}
//added by wangtongchao 2011-11-26 end
/*
 * 関数名：change_category
 * 関数説明：チェックボックスの選択の処理
 * パラメタ：id:チェックボックスID
 * 戻り値：無し
 */
function change_category(id)
{
	// 子IDの一部を抜き出します。（ct_xxの部分）
	var pos = id.lastIndexOf("_");
	if (pos == -1)
	{
		alert("IDの形式が違います。");
		return false;
	}
	var cid = id.substr(0, pos);			// 子ID : ct_xx_yy

	// 親IDを作成します。
	var pid = cid + "_0";					// 親ID : ct_xx_0;

	// 親IDのチェックを入れます。
	if (document.getElementById(id).checked == true)
	{
		document.getElementById(pid).checked = true;
	}
	// 親IDのチェックを入らない時
	if (document.getElementById(pid).checked == false)
	{
		for (var i = 1; i < 20; i++)
		{
			var chk_key = cid + "_" + i;
			var obj_chk = document.getElementById(chk_key);
			if (obj_chk) obj_chk.checked = false;
		}
	}
}

/*
 * 関数名：change_range_radio
 * 関数説明：掲載可能範囲の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_range_radio(obj)
{
	var key = "reg_pub_possible_txt";
	var obj_txt = document.getElementById(key);

	// 掲載可能範囲の「外部出稿条件付き」を選択した場合
	if (parseInt(obj.value) == 3)
	{
		// 掲載可能範囲の「外部出稿条件付き」テキストボックスは有効になる
		if (obj_txt)
		{
			obj_txt.value="";
			obj_txt.disabled = false;
		}
	// 掲載可能範囲の「外部出稿条件付き」以外を選択した場合
	} else {
		// 掲載可能範囲の「外部出稿条件付き」テキストボックスは無効になる
		if (obj_txt)
		{
			obj_txt.disabled  = true;
			obj_txt.value="";
		}
	}
}

/*
 * 関数名：change_obtaining_radio
 * 関数説明：写真入手元の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_obtaining_radio(obj)
{
	var key = "reg_p_obtaining_txt";
	var obj_txt = document.getElementById(key);

	// 写真入手元の「その他」を選択した場合
	if (parseInt(obj.value) == 2)
	{
		// 写真入手元の「その他」テキストボックスは有効になる
		if (obj_txt)
		{
			obj_txt.value="";
			obj_txt.disabled = false;
		}
	// 写真入手元の「アマナ」を選択した場合
	} else {
		// 写真入手元の「その他」テキストボックスは無効になる
		if (obj_txt)
		{
			obj_txt.disabled  = true;
			obj_txt.value="";
		}
	}
}

/*
 * 関数名：change_bud_number_radio
 * 関数説明：BUD_PHOTO番号の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_bud_number_radio(obj)
{
	var key = "reg_bud_number_txt";
	var obj_txt = document.getElementById(key);

	// BUD_PHOTO番号の「ある」を選択した場合
	if (parseInt(obj.value) == 1)
	{
		// BUD_PHOTO番号の「ある」テキストボックスは有効になる
		if (obj_txt)
		{
			obj_txt.disabled = false;
			obj_txt.value="";
		}
	// BUD_PHOTO番号の「なし」を選択した場合
	} else {
		// BUD_PHOTO番号の「ある」テキストボックスは無効になる
		if (obj_txt)
		{
			obj_txt.disabled  = true;
			obj_txt.value="";
		}
	}
}

///*
// * 関数名：change_year
// * 関数説明：カレンダーの処理
// * パラメタ：無し
// * 戻り値：無し
// */
//function change_year()
//{
//	var key_month = "take_picture_time_name";
//	var key_day = "select_day";
//
//	// 掲載期間の「月」を取得する
//	var objs_month = document.getElementsByName(key_month);
//	if (objs_month) var obj_month = objs_month[1];
//	// 掲載期間の「日」を取得する
//	var obj_day = document.getElementById(key_day);
//
//	// 掲載期間の「月」のインデックスを設定する
//	objs_month[1].selectedIndex = 0;
//	// 掲載期間の「日」のインデックスを設定する
//	obj_day.selectedIndex = 0;
//}

/*
 * 関数名：calendar
 * 関数説明：カレンダーの処理
 * パラメタ：無し
 * 戻り値：無し
 */
function calendar()
{
	var monthDays = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

	var key_year = "select_year";
	var key_month = "take_picture_time_name";
	var key_day = "select_day";

	// 掲載期間の「年」を取得する
	var obj_year = document.getElementById(key_year);
	if (obj_year) var year = parseInt(obj_year.value);

	if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0)) monthDays[1] = 29;

	// 掲載期間の「月」を取得する
	var objs_month = document.getElementsByName(key_month);
	if (objs_month)
	{
		var obj_month = objs_month[1];
		var month = parseInt(obj_month.value);
	}

	// 掲載期間の「日」を取得する
	var obj_day = document.getElementById(key_day);
	var ed = monthDays[month - 1];
	if (obj_day)
	{
		// アイテムのクリアー
		clearItem(obj_day);
		// アイテムの追加
		addItem(obj_day,ed);
	}
	obj_day.selectedIndex = 0;
}

/*
 * 関数名：DBC_SBC
 * 関数説明：半角->全角の転換
 * パラメタ：index：インデックス
 * 戻り値：転換後の文字列
 */
function DBC_SBC(index)
{
	var sdArray = new Array();

	var i = 1;
	sdArray[i++] = "１";
	sdArray[i++] = "２";
	sdArray[i++] = "３";
	sdArray[i++] = "４";
	sdArray[i++] = "５";
	sdArray[i++] = "６";
	sdArray[i++] = "７";
	sdArray[i++] = "８";
	sdArray[i++] = "９";
	sdArray[i++] = "１０";
	sdArray[i++] = "１１";
	sdArray[i++] = "１２";
	sdArray[i++] = "１３";
	sdArray[i++] = "１４";
	sdArray[i++] = "１５";
	sdArray[i++] = "１６";
	sdArray[i++] = "１７";
	sdArray[i++] = "１８";
	sdArray[i++] = "１９";
	sdArray[i++] = "２０";
	sdArray[i++] = "２１";
	sdArray[i++] = "２２";
	sdArray[i++] = "２３";
	sdArray[i++] = "２４";
	sdArray[i++] = "２５";
	sdArray[i++] = "２６";
	sdArray[i++] = "２７";
	sdArray[i++] = "２８";
	sdArray[i++] = "２９";
	sdArray[i++] = "３０";
	sdArray[i++] = "３１";

	return sdArray[index];
}

/*
 * 関数名：delOption
 * 関数説明：アイテムを削除する
 * パラメタ：SourceSelectオブジェクト
 * 戻り値：無し
 */
function delOption(obj) {
	// インデックスを取得する
	var selIdx = obj.selectedIndex;
	// レストのオプションのサイズを取得する
	var selNum = obj.options.length;
	var opt = selIdx >= 0 ? obj.options[selIdx] : null;
	if (selIdx >= 0 && selIdx < selNum) {
	   obj.options[selIdx] = null;
	}
}

/*
 * 関数名：addItem
 * 関数説明：アイテムを追加する
 * パラメタ：TargetSelect:Selectオブジェクト;ed_day:終了の日付
 * 戻り値：無し
 */
function addItem(TargetSelect,ed_day)
{
	var thevalue = "";
	var tmp_ed_day = ed_day;
	if (ed_day == null) tmp_ed_day = 31;
	TargetSelect.options.add(new  Option("未定",0));
	// 掲載期間の「日」にアイテムを追加する
	for (var i = 1; i <= tmp_ed_day; i++)
	{
		//thevalue = DBC_SBC(i) + "日";
		thevalue = i + "日";
		TargetSelect.options.add(new  Option(thevalue,i));
	}
	if (ed_day != null) TargetSelect.selectedIndex = 1;
}

/*
 * 関数名：clearItem
 * 関数説明：アイテムを削除する
 * パラメタ：SourceSelectオブジェクト
 * 戻り値：無し
 */
function clearItem(SourceSelect)
{
	var break_flg = true;
	// オプションのサイズを取得する
	var ed = SourceSelect.options.length;
	while (break_flg == true)
	{
		delOption(SourceSelect);
		ed = SourceSelect.options.length;
		if (ed <= 0) break_flg = false;
	}
}

/*
 * 関数名：form_submit
 * 関数説明：イメージ登録確認の画面へ遷移する
 * パラメタ：無し
 * 戻り値：無し
 */
function form_submit()
{
	// 入力チェックを行う
	var ok_flg = check_input_value();
	// 正常の場合
	if (ok_flg != false)
	{
		// 掲載期間の「月」を取得する
		var key_month = "take_picture_time_name";
		var objs_month = document.getElementsByName(key_month);
		if (objs_month) var obj_month = objs_month[0];

		// 付加条件を取得する
		var objs_txt = document.getElementsByName("reg_addition_txt");
		var url_str = "";
		if (objs_txt)
		{
			url_str = "&reg_addition0=" + encodeURIComponent(objs_txt[0].value)+"=_="+encodeURIComponent(objs_txt[1].value);
			url_str = url_str + "&reg_addition1=" + encodeURIComponent(objs_txt[2].value);
		}
		document.register_image_input.action = "./register_image_input.php?p_action=uploadfile&time2=" + encodeURIComponent(obj_month.value) + url_str;
		document.register_image_input.submit();
	}
}

/*
 * 関数名：form_clear
 * 関数説明：画面のクリアー
 * パラメタ：無し
 * 戻り値：無し
 */
function form_clear()
{
	var url = "./register_image_input.php?initflg=1";
	parent.bottom.location.href = url;
}

/*
 * 関数名：toggle_classification
 * 関数説明：「+」と「-」ボタンの処理
 * パラメタ：elm:divID,flg:表示と隠れるのフラグ
 * 戻り値：無し
 */
function toggle_classification(elm,flg)
{
	var div_obj = document.getElementById(elm);
	// フレームを取得する
	var obj_frame = top.document.getElementById('iframe_bottom');
	var bak_height = obj_frame.style.height;
	// フレームを隠れる時
	if (flg == 0)
	{
		var id = elm.substr(elm.length - 1,1);

		// 分類のインデックスの設定する
		var obj_key = "p_classification_id" + id;
		var obj_elm = document.getElementById(obj_key);
		if (obj_elm) obj_elm.selectedIndex = 0;

		// 方面のインデックスの設定する
		var obj_key = "p_direction_id" + id;
		var obj_elm = document.getElementById(obj_key);
		if (obj_elm) obj_elm.selectedIndex = 0;

		// 国．道府県のインデックスの設定する
		var obj_key = "p_country_prefecture_id" + id;
		var obj_elm = document.getElementById(obj_key);
		if (obj_elm) obj_elm.selectedIndex = 0;

		// 都市のインデックスの設定する
		var obj_key = "p_place_id" + id;
		var obj_elm = document.getElementById(obj_key);
		if (obj_elm) obj_elm.selectedIndex = 0;

		div_obj.style.display = "none";
		obj_frame.style.height = parseInt(bak_height) - 50;
	// フレームを表示する時
	} else {
		div_obj.style.display = "block";
		obj_frame.style.height = parseInt(bak_height) + 50;
	}
}

/*
 * 関数名：check_date_range
 * 関数説明：日付の範囲のチェック
 * パラメタ：para_year：年；para_month：月；para_day：日
 * 戻り値：無し
 */
function check_date_range(para_year,para_month,para_day)
{
	var now_date = new Date();
	var now_year = parseInt(now_date.getFullYear());
	var now_month = parseInt(now_date.getMonth()) + 1;
	var now_day = parseInt(now_date.getDate());

	// 選択の「年」はシステム日付の「年」の以前の場合
	if (now_year > parseInt(para_year))
	{
		return false;
	// 選択の「年」はシステム日付の「年」の以後の場合
	} else if (now_year < parseInt(para_year)) {
		return true;
	// 選択の「年」はシステム日付の「年」と同じ場合
	} else if (now_year == parseInt(para_year)) {
		// 選択の「月」はシステム日付の「月」の以前の場合
		if (now_month > parseInt(para_month))
		{
			return false;
		// 選択の「月」はシステム日付の「月」の以後の場合
		} else if (now_month < parseInt(para_month)) {
			return true;
		// 選択の「月」はシステム日付の「月」と同じ場合
		} else if (now_month == parseInt(para_month)) {
			// 選択の「日」はシステム日付の「日」の以前の場合
			if (now_day > parseInt(para_day))
			{
				return false;
			} else {
				return true;
			}
		}
	}
}

/*
 * 関数名：check_input_value
 * 関数説明：更新する時の入力チェック
 * パラメタ：無し
 * 戻り値：無し
 */
function check_input_value()
{
	var flname = document.register_image_input.p_photo_filename;

	// アップファイルを選択しない場合
	if (flname.value.length == 0)
	{
		alert('ファイルを選択してください。\r\n');
		flname.focus();
		return false;
	}

	var dotpos = flname.value.lastIndexOf(".");
	var ext = flname.value.substr(dotpos);
	ext = ext.toLowerCase();
	// 拡張子のチェック
	if (ext != ".jpg" && ext != ".jpeg" && ext != ".png" && ext != ".gif")
	{
		alert("申請できない種類のファイルです。\r\n（拡張子.jpeg、.jpg、.png、.gifのみ申請可能です。）");
		flname.focus();
		return false;
	}

	//画像の名前のチェック
	var p_name = document.register_image_input.reg_subject;
	if (p_name.value.length == 0)
	{
		alert('画像の名前を入力してください。\r\n');
		p_name.focus();
		return false;
	}

	//被写体の名称 のチェック
	if (p_name.value != null && p_name.value != "")
	{
		if (p_name.value.length > 0)
		{
			var tmp = "__" + p_name.value;
			// var ipos = tmp.indexOf("　");
			// if (ipos > 0)
			// {
			// 	alert("被写体の名称に全角スペースは入力できない。");
			// 	p_name.focus();
			// 	return false;
			// }
		}
	}

	//登録区分のチェック
	var reg_divisions = document.getElementsByName("reg_division");
	if (reg_divisions)
	{
		if (reg_divisions[0].checked == false && reg_divisions[1].checked == false)
		{
			alert('登録区分を選択してください。\r\n');
			reg_divisions[0].focus();
			return false;
		}
	}

	// 登録分類のチェック
	for (var i = 1; i <= 5; i++)
	{
		var obj_name = "div_classification" + i;
		var obj_classification = document.getElementById(obj_name);
		if (obj_classification)
		{
			if (obj_classification.style.display == "block")
			{
				var key = "p_classification_id" + i;
				var obj_p_c = document.getElementById(key);
				var key = "p_direction_id" + i;
				var obj_p_d = document.getElementById(key);
				var key = "p_country_prefecture_id" + i;
				var obj_p_c_p = document.getElementById(key);
				var key = "p_place_id" + i;
				var obj_p_p = document.getElementById(key);
				for (var j = 1; j <= 5; j++)
				{
					var obj_name2 = "div_classification" + j;
					var obj_classification2 = document.getElementById(obj_name2);
					if (obj_classification2)
					{
						if (obj_classification2.style.display == "block" && j != i)
						{
							var key = "p_classification_id" + j;
							var obj_p_c2 = document.getElementById(key);
							var key = "p_direction_id" + j;
							var obj_p_d2 = document.getElementById(key);
							var key = "p_country_prefecture_id" + j;
							var obj_p_c_p2 = document.getElementById(key);
							var key = "p_place_id" + j;
							var obj_p_p2 = document.getElementById(key);
							if (obj_p_c.selectedIndex == obj_p_c2.selectedIndex     &&
							    obj_p_d.selectedIndex == obj_p_d2.selectedIndex     &&
							    obj_p_c_p.selectedIndex == obj_p_c_p2.selectedIndex &&
							    obj_p_p.selectedIndex == obj_p_p2.selectedIndex )
							{
								var msg = "登録分類" + i + "と登録分類" + j + "は重複です。ご確認ください。\r\n";
								alert(msg);
								var key = "p_classification_id" + i;
								var obj_p_c = document.getElementById(key);
								obj_p_c.focus();
								return false;
							}
						}
					}
				}
			}
		}
	}

	//掲載期間のチェック
	var reg_pub_periods = document.getElementsByName("reg_pub_period");
	if (reg_pub_periods)
	{
		if (reg_pub_periods[0].checked == false &&
		    reg_pub_periods[1].checked == false &&
		    reg_pub_periods[2].checked == false &&
		    reg_pub_periods[3].checked == false &&
		    reg_pub_periods[4].checked == false &&
		    //added by wangtongchao 2011-12-02
		    reg_pub_periods[5].checked == false &&
		    reg_pub_periods[6].checked == false)
		    //added by wangtongchao 2011-12-02
		{
			alert('掲載期間を選択してください。\r\n');
			reg_pub_periods[0].focus();
			return false;
		}
	}

	//掲載可能範囲のチェック
	var reg_pub_possibles = document.getElementsByName("reg_pub_possible");
	if (reg_pub_possibles)
	{
		if (reg_pub_possibles[0].checked == false &&
		    reg_pub_possibles[1].checked == false &&
		    reg_pub_possibles[2].checked == false)
		{
			alert('掲載可能範囲を選択してください。\r\n');
			reg_pub_possibles[0].focus();
			return false;
		}
	}
	// 掲載可能範囲のチェック
	if (reg_pub_possibles)
	{
		// 外部出稿条件付きを選択した場合
		if (reg_pub_possibles[2].checked == true)
		{
			var obj_txt = document.getElementById("reg_pub_possible_txt");
			// 条件は未入力の場合
			if (trim(obj_txt.value).length <= 0)
			{
				alert("外部出稿条件付きを入力してください。");
				obj_txt.focus();
				return false;
			}
		}
	}

	// 付加条件のチェック
	var reg_adds = document.getElementsByName("reg_addition");
	var msg = "";
	if (reg_adds)
	{
		// 付加条件の「要クレジット」を選択した場合
		if (reg_adds[1].checked == true)
		{
			//deleted by wangtongchao 2011-11-26 begin
//			var obj_txt = document.getElementById("reg_addition_txt0");
//			// 付加条件を入力しない場合、エラーメッセージを出力する
//			if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
//			{
//				alert("要クレジットを入力してください。");
//				obj_txt.focus();
//				return false;
//			}
			//deleted by wangtongchao 2011-11-26 end
			
			//added by wangtongchao 2011-11-26 begin
			var obj_txt = document.getElementById("reg_addition_txt0");
			var obj_txt1 = document.getElementById("reg_addition_txt1");
			// 付加条件を入力しない場合、エラーメッセージを出力する
			if ((trim(obj_txt.value).length == 0 || obj_txt.value == null)&&(trim(obj_txt1.value).length == 0 || obj_txt1.value == null))
			{
				alert("要クレジットを入力してください。");
				obj_txt.focus();
				return false;
			} else {
				if(calcUTFByte(obj_txt.value) > 42)
				{
					alert("要クレジットの内容は全角２１または半角４２以内の文字を入力してください。");
					obj_txt.focus();
					return false;
				}
				if(calcUTFByte(obj_txt1.value) > 42)
				{
					alert("要クレジットの内容は全角２１または半角４２以内の文字を入力してください。");
					obj_txt1.focus();
					return false;
				}
			}
			//added by wangtongchao 2011-11-26 end
			
		// 付加条件の「要使用許可」を選択した場合
		} else if (reg_adds[2].checked == true) {
			//changed by wangtognchao 2011-11-26 reg_addition_txt1 to reg_addition_txt2 begin
			var obj_txt = document.getElementById("reg_addition_txt2");
			//changed by wangtognchao 2011-11-26 reg_addition_txt1 to reg_addition_txt2 end
			// 付加条件を入力しない場合、エラーメッセージを出力する
			if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
			{
				alert("要使用許可を入力してください。");
				obj_txt.focus();
				return false;
			}
		// 付加条件の「なし」を選択した場合
		} else if (reg_adds[0].checked == true) {
			//処理をしない
		} else {
			alert("付加条件を選択してください。");
			reg_adds[0].focus();
			return false;
		}
	}

	//写真入手元のチェック
	var reg_p_obtainings = document.getElementsByName("reg_p_obtaining");
	if (reg_p_obtainings)
	{
		if (reg_p_obtainings[0].checked == false && reg_p_obtainings[1].checked == false)
		{
			alert('写真入手元を選択してください。\r\n');
			reg_p_obtainings[0].focus();
			return false;
		}
		// 写真入手元の「その他」を選択した場合
		if (reg_p_obtainings[1].checked == true)
		{
			var obj_txt = document.getElementById("reg_p_obtaining_txt");
			// 写真入手元を入力しない場合、エラーメッセージを出力する
			if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
			{
				alert("写真入手元を入力してください。");
				obj_txt.focus();
				return false;
			}
		}
	}

//	//版権所有者のチェック
//	var s_reg_copyright = document.register_image_input.reg_copyright;
//	if (s_reg_copyright.value.length == 0)
//	{
//		alert('版権所有者を入力してください。\r\n');
//		s_reg_copyright.focus();
//		return false;
//	}

	// BUD_PHOTO番号のチェック
	var reg_bud_numbers = document.getElementsByName("reg_bud_number");
	//added by wangtongchao 2011-12-06 begin
	if(reg_bud_numbers[0].checked == false && reg_bud_numbers[1].checked == false)
	{
		alert("BUD_PHOTO番号を選択してください。");
		reg_bud_numbers[0].focus();
		return false;
	}
	//added by wangtongchao 2011-12-06 end
	if (reg_bud_numbers)
	{
		var reg_bud_number = reg_bud_numbers[0];
		// BUD_PHOTO番号の「ある」を選択した場合
		if (reg_bud_number.checked == true)
		{
			var obj_txt = document.getElementById("reg_bud_number_txt");
			// BUD_PHOTO番号を入力しない場合、エラーメッセージを出力する
			if (trim(obj_txt.value).length == 0 || obj_txt.value == null)
			{
				alert("BUD_PHOTO番号を入力してください。");
				obj_txt.focus();
				return false;
			}
		}
	}

	//「BUD PHOT DBあり」を選択した場合、BUD_PHOTO番号を入力しない場合、エラーメッセージを出力する
	var reg_divisions = document.getElementsByName("reg_division");
	if (reg_divisions)
	{
		var one_reg_division = reg_divisions[1];
		if (one_reg_division)
		{
			if (one_reg_division.checked == true)
			{
				var reg_bud_numbers = document.getElementsByName("reg_bud_number");
				var reg_bud_number = reg_bud_numbers[1];
				if (reg_bud_number)
				{
					if (reg_bud_number.checked == true)
					{
						alert("「BUD PHOT DBあり」を選択した場合、BUD_PHOTO番号「なし」を選択できない。");
						reg_bud_numbers[1].focus();
						return false;
					}
				}
			}
		}
	}

	var obj_red = document.getElementById("reg_bud_number_txt");
	if (obj_red.value.length > 0)
	{
		//全角漢字をチェック
		if (checkIsZenkaku(obj_red.value))
		{
			alert("BUD_PHOTO番号には半角を入力してください。");
			obj_red.focus();
			return false;
		}
	}

	// 掲載期間（To)のコンボボックス、年・月・日→YYYY-MM-DDにします。
	// 年
	idx = document.register_image_input.select_year.selectedIndex;
	var p_year = document.register_image_input.select_year.options[idx].value;

	// 月
	var objs_month = document.getElementsByName("take_picture_time_name");
	if (objs_month) var obj_month = objs_month[1];
	idx = obj_month.selectedIndex;
	var p_month = obj_month.options[idx].value;

	// 日
	idx = document.register_image_input.select_day.selectedIndex;
	var p_day = document.register_image_input.select_day.options[idx].value;

	// 年月日をチェックします。
	// 無期限の場合は、日付のチェックを行いません。
	if (document.register_image_input.reg_pub_period[0].checked != true)
	{
		if(check_date(p_year, p_month, p_day) != 0)
		{
			alert("正しい日付ではありません。");
			document.register_image_input.select_year.focus();
			return false;
		}

		if(check_date_range(p_year, p_month, p_day) != true)
		{
			alert("掲載期間に過去の日付は選択できません。");
			document.register_image_input.select_year.focus();
			return false;
		}

		// 年月日を設定します。
		var fdt = new Date();
		var tdt = new Date(p_year, p_month - 1, p_day);

		// 掲載期間（To）を設定します。
		disp_to = dateFormat.format(tdt);
		document.register_image_input.p_dto.value = disp_to;
	}
	else
	{
		// 無期限の場合の掲載期間（To）を設定します。
		document.register_image_input.p_dto.value = "2100-01-01";
	}

	// カテゴリーをスペース区切りの文字列（Keyword_str）へ変換します。
	var keyword_str = "";
	var tags = document.body.getElementsByTagName("*");
	for(var i = 0 ; i < tags.length ; i++)
	{
		var grp = tags[i].getAttribute("category");
		if(grp != undefined)
		{
			if (tags[i].checked == true)
			{
				if (keyword_str.length != 0)
				{
					keyword_str += " ";
				}

				keyword_str += tags[i].value;
			}
		}
	}

	// 結合したものをキーワードとします。
	document.register_image_input.p_keyword_str.value = keyword_str;
}

/*
 * 関数名：onlostfocus
 * 関数説明：被写体の名称のチェック
 * パラメタ：無し
 * 戻り値：無し
 */
function onlostfocus(obj)
{
	if (obj.value != null && obj.value != "")
	{
		if (obj.value.length > 0)
		{
			var tmp = "__" + obj.value;
			// var ipos = tmp.indexOf("　");
			// if (ipos > 0)
			// {
			// 	alert("被写体の名称に全角スペースは入力できない。");
			// 	return false;
			// }
		}
	}
}


/*
 * 関数名：isIE6
 * 関数説明：IE6のチェック
 * パラメタ：無し
 * 戻り値：True/False
 */
function isIE6()
{
	var ua, s, i;

	var isIE = false; // Internet Explorer
	var version = null;

	ua = navigator.userAgent;

	s = "MSIE";
	if ((i = ua.indexOf(s)) >= 0)
	{
		isIE = true;
		version = parseFloat(ua.substr(i + s.length));
		if (version == 6 || version == 6.0) return true;
		else return false;
	} else {
		return false;
	}
}
//added bywangtongchao 2011-12-16 begin
/*
 * 関数名：check_reg_bud_number
 * 関数説明：BUD_PHOTO番号の「ある」を選択したテキストボックスのチェック
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function check_reg_bud_number(obj)
{
	var patrn = /[a-zA-Z0-9_\.\-]$/ig;
	if(obj.value.length > 0)
	{
		if(!patrn.exec(obj.value))
		{
			alert("BUD_PHOTO番号を確認してください。");
			TimeID=setTimeout("setFocus('"+ obj.name + "')",100);
			return false;
		}
	}
}
//added by wangtongchao 2011-12-16 end
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
	if(obj_frame) obj_frame.style.height = 0;
	var obj_frame = top.document.getElementById('iframe_middle2');
	if(obj_frame) obj_frame.style.height = 0;
	var obj_frame = top.document.getElementById('iframe_bottom');
	if(obj_frame) obj_frame.style.height = 2500;

	//IE6.0の場合
	if (isIE6())
	{
		var obj_frame = top.document.getElementById('iframe_bottom');
		if(obj_frame) obj_frame.style.height = 2600;
	} else {
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

	if (isIE6())
	{
		ConnectedSelect(['p_classification_id1', 'p_direction_id1', 'p_country_prefecture_id1', 'p_place_id1']);
	} else {
		ConnectedSelect(['p_classification_id1', 'p_direction_id1', 'p_country_prefecture_id1', 'p_place_id1']);
		ConnectedSelect(['p_classification_id2', 'p_direction_id2', 'p_country_prefecture_id2', 'p_place_id2']);
		ConnectedSelect(['p_classification_id3', 'p_direction_id3', 'p_country_prefecture_id3', 'p_place_id3']);
		ConnectedSelect(['p_classification_id4', 'p_direction_id4', 'p_country_prefecture_id4', 'p_place_id4']);
		ConnectedSelect(['p_classification_id5', 'p_direction_id5', 'p_country_prefecture_id5', 'p_place_id5']);
	}

	// 掲載期間（From）の設定
	var today = new Date();
	disp_from = dateFormat.format(today);
	document.register_image_input.p_dfrom.value = disp_from;
}

window.onload = function()
{
	init();
}
-->
</script>
<!-- javascript ここまで -->
</head>
<body>
<form enctype="multipart/form-data" name="register_image_input" action="./register_image_input.php?p_action=uploadfile" method="post">
<div id="zentai">
<div id="contents">
	<div id="registration">
		<div>
		<h2>基本情報</h2>
		<p class="reg_photo_number"></p>
		<div class="reg_file_subject">
			<dl class="reg_filedata reg_clear reg_list_none_top">
				<dt>画像ファイル</dt>
				<dd class="bt_reg_reference">
					<input type="file" name="p_photo_filename" id="p_photo_filename" value=""  />
				</dd>
				<dd class="tag_txt"></dd>
			</dl>
			<dl class="reg_subject reg_clear">
				<dt>被写体の名称</dt>
				<?php  if ($initflg != 1){ ?>
					<dd><input name="reg_subject" type="text" id="reg_subject" value="<?php  echo array_get_value($_SESSION,'photo_name' ,""); ?>" size="65" /></dd>
				<?php  } else {  ?>
					<dd><input name="reg_subject" type="text" id="reg_subject" size="65" onblur="onlostfocus(this);"/></dd>
				<?php  } ?>
			</dl>
		</div>
		<dl class="reg_division reg_clear">
			<dt>登録区分</dt>
			<dd><?php  registration_division($registration_id, $registration_name); ?></dd>
		</dl>

		<?php
			//IE6.0場合
			if(strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0"))
			{
				//分類1
				disp_div_classification($classification_id1, $classification_name1, 1);
			} else {
				//分類1
				disp_div_classification($classification_id1, $classification_name1, 1);
				//分類2
				disp_div_classification($classification_id2, $classification_name2, 2);
				//分類3
				disp_div_classification($classification_id3, $classification_name3, 3);
				//分類4
				disp_div_classification($classification_id4, $classification_name4, 4);
				//分類5
				disp_div_classification($classification_id5, $classification_name5, 5);
			}
		?>

		<dl class="reg_category reg_clear">
			<dt>カテゴリー</dt>
			<dd><ul><?php  disp_category($category_id,$category_name);?></ul></dd>
		</dl>
		<dl class="take_picture reg_clear">
			<dt>撮影時期</dt>
			<dd><?php  take_picture_time2($take_picture_time2_id, $take_picture_time2_name); ?></dd>
			<dd class="mounth"><label><?php  take_picture_time($take_picture_time_id, $take_picture_time_name, 0); ?></label></dd>
		</dl>
		<dl class="reg_material reg_clear">
			<dt>素材（画像）の詳細内容</dt>
			<?php  if($initflg != 1){ ?>
				<dd><textarea name="reg_material_txt" id="reg_material" cols="70" rows="5"><?php  echo array_get_value($_SESSION,'photo_explanation' ,""); ?></textarea></dd>
			<?php  }else{ ?>
				<dd><textarea name="reg_material_txt" id="reg_material" cols="70" rows="5"></textarea></dd>
			<?php  } ?>
		</dl>
		</div>
		<div>
			<h2>掲載条件</h2>
			<dl class="reg_pub_period reg_clear reg_list_none_top">
				<dt>掲載期間</dt>
				<dd>
				<?php  if($initflg != 1){ ?>
					<?php  if(array_get_value($_SESSION,'kikan' ,"") == "mukigen"){ ?>
						<!--<label><input name="reg_pub_period" id="reg_pub_period0" type="radio" checked="checked" value="mukigen" onclick='change_kikan(this);'/>無期限</label>-->
					<?php  }else{ ?>
						<!--<label><input name="reg_pub_period" id="reg_pub_period0" type="radio" value="mukigen" onclick='change_kikan(this);'/>無期限</label>-->
					<?php  } ?>

					<?php  if(array_get_value($_SESSION,'kikan' ,"") == "sankagetu"){ ?>
						<label><input name="reg_pub_period" id="reg_pub_period1" type="radio" checked="checked" value="sankagetu" onclick='change_kikan(this);'/>3ヵ月 </label>
					<?php  }else{ ?>
						<label><input name="reg_pub_period" id="reg_pub_period1" type="radio" value="sankagetu" onclick='change_kikan(this);'/>3ヵ月 </label>
					<?php  } ?>

					<?php  if(array_get_value($_SESSION,'kikan' ,"") == "hantoshi"){ ?>
						<label><input name="reg_pub_period" id="reg_pub_period2" type="radio" checked="checked" value="hantoshi" onclick='change_kikan(this);'/>6ヵ月 </label>
					<?php  }else{ ?>
						<label><input name="reg_pub_period" id="reg_pub_period2" type="radio" value="hantoshi" onclick='change_kikan(this);'/>6ヵ月 </label>
					<?php  } ?>

					<?php  if(array_get_value($_SESSION,'kikan' ,"") == "ichinen"){ ?>
						<label><input name="reg_pub_period" id="reg_pub_period3" type="radio" checked="checked" value="ichinen" onclick='change_kikan(this);'/>1年間 </label>
					<?php  }else{ ?>
						<label><input name="reg_pub_period" id="reg_pub_period3" type="radio" value="ichinen" onclick='change_kikan(this);'/>1年間 </label>
					<?php  } ?>
					<!--added by wangtongchao 2011-12-01 begin-->
					<?php  if(array_get_value($_SESSION,'kikan' ,"") == "ninen"){ ?>
						<label><input name="reg_pub_period" id="reg_pub_period4" type="radio" checked="checked" value="ninen" onclick='change_kikan(this);'/>2年間 </label>
					<?php  }else{ ?>
						<label><input name="reg_pub_period" id="reg_pub_period4" type="radio" value="ninen" onclick='change_kikan(this);'/>2年間 </label>
					<?php  } ?>
							
					<?php  if(array_get_value($_SESSION,'kikan' ,"") == "sannen"){ ?>
						<label><input name="reg_pub_period" id="reg_pub_period5" type="radio" checked="checked" value="sannen" onclick='change_kikan(this);'/>3年間 </label>
					<?php  }else{ ?>
						<label><input name="reg_pub_period" id="reg_pub_period5" type="radio" value="sannen" onclick='change_kikan(this);'/>3年間 </label>
					<?php  } ?>
					<!--added by wangtongchao 2011-12-01 end-->
				<?php  }else{ ?>
					<!--<label><input name="reg_pub_period" id="reg_pub_period0" type="radio" value="mukigen" onclick='change_kikan(this);'/>無期限</label>-->
					<label><input name="reg_pub_period" id="reg_pub_period1" type="radio" value="sankagetu" onclick='change_kikan(this);'/>3ヵ月 </label>
					<label><input name="reg_pub_period" id="reg_pub_period2" type="radio" value="hantoshi" onclick='change_kikan(this);'/>6ヵ月 </label>
					<label><input name="reg_pub_period" id="reg_pub_period3" type="radio" value="ichinen" onclick='change_kikan(this);'/>1年間 </label>
					<!--added by wangtongchao 2011-12-01 begin-->
					<label><input name="reg_pub_period" id="reg_pub_period4" type="radio" value="ninen" onclick='change_kikan(this);'/>2年間 </label>
					<label><input name="reg_pub_period" id="reg_pub_period5" type="radio" value="sannen" onclick='change_kikan(this);'/>3年間 </label>
					<!--added by wangtongchao 2011-12-01 end-->
				<?php  } ?>
				</dd>
				<dd class="mounth">
					<?php  if($initflg != 1){ ?>
						<?php  if(array_get_value($_SESSION,'kikan' ,"") == "shitei"){ ?>
							<label><input name="reg_pub_period" id="reg_pub_period4" type="radio" checked="checked" value="shitei" onclick='change_kikan(this);'/>日付指定</label>
						<?php  }else{ ?>
							<label><input name="reg_pub_period" id="reg_pub_period4" type="radio" value="shitei" onclick='change_kikan(this);'/>日付指定</label>
						<?php  } ?>
					<?php  }else{ ?>
						<label><input name="reg_pub_period" id="reg_pub_period4" type="radio" value="shitei" onclick='change_kikan(this);'/>日付指定</label>
					<?php  } ?>
					<label><?php  take_picture_year(); ?></label>
					<label><?php  take_picture_time($take_picture_time_id, $take_picture_time_name, 1); ?></label>
					<label><?php  take_pictrue_day(); ?></label>
				</dd>
			</dl>
			<dl class="reg_pub_possible reg_clear">
				<dt>掲載可能範囲</dt>
				<?php  disp_range($range_id,$range_name); ?>
			</dl>
			<dl class="reg_addition reg_clear">
				<dt>付加条件</dt>
				<dd>
					<?php  if($initflg != 1){ ?>
						<?php  if((int)array_get_value($_SESSION,'reg_addition' ,"") == 2){ ?>
							<label><input name="reg_addition" id="reg_addition2" type="radio" checked="checked" value="2" onclick='change_reg_addition(this);'/>なし </label>
						<?php  }else{ ?>
							<label><input name="reg_addition" id="reg_addition2" type="radio" value="2" onclick='change_reg_addition(this);'/>なし </label>
						<?php  } ?>
					<?php  }else{ ?>
						<label><input name="reg_addition" id="reg_addition2" type="radio" value="2" onclick='change_reg_addition(this);'/>なし </label>
					<?php  } ?>
				</dd>
<!-- deleted by wangtongchao 2011-11-26 begin
				<dd class="outside">
					<?php  if($initflg != 1){ ?>
						<?php  if((int)array_get_value($_SESSION,'reg_addition' ,"") == 0){ ?>
							<label><input name="reg_addition" id="reg_addition0" type="radio" checked="checked" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
							<input name="reg_addition_txt" type="text" id="reg_addition_txt0" value="<?php  echo array_get_value($_SESSION,'reg_addition0' ,""); ?>" size="30"  />
						<?php  }else{ ?>
							<label><input name="reg_addition" id="reg_addition0" type="radio" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
							<input name="reg_addition_txt" type="text" id="reg_addition_txt0" size="30"  disabled="disabled"/>
						<?php  } ?>
					<?php  }else{ ?>
						<label><input name="reg_addition" id="reg_addition0" type="radio" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
						<input name="reg_addition_txt" type="text" id="reg_addition_txt0" size="30"  disabled="disabled"/>
					<?php  } ?>
				</dd>
deleted by wangtongchao 2011-11-26 end-->
<!--added by wangtongchao 2011-11-26 begin-->
				<dd class="outside">
					<?php  if($initflg != 1){ ?>
						<?php  if((int)array_get_value($_SESSION,'reg_addition' ,"") == 0){ 
							   $reg_addions = array_get_value($_SESSION,'reg_addition0' ,"");
							   $reg_addions = explode("=_=",$reg_addions);
						?>
							<label><input name="reg_addition" id="reg_addition0" type="radio" checked="checked" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
							<!-- changed by wanotongchao 2011-12-16 begin -->
							<input name="reg_addition_txt" type="text" id="reg_addition_txt0" value="<?php  echo  htmlspecialchars($reg_addions[0],ENT_QUOTES,'UTF-8');?>" size="30"  />
							<input name="reg_addition_txt" type="text" id="reg_addition_txt1" value="<?php  echo  htmlspecialchars($reg_addions[1],ENT_QUOTES,'UTF-8');?>" size="30"  />
							<!-- changed by wangtongchao 2011-12-16 end -->
						<?php  }else{ ?>
							<label><input name="reg_addition" id="reg_addition0" type="radio" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
							<input name="reg_addition_txt" type="text" id="reg_addition_txt0" size="30"  disabled="disabled"/>
							<input name="reg_addition_txt" type="text" id="reg_addition_txt1" size="30"  disabled="disabled"/>
						<?php  } ?>
					<?php  }else{ ?>
						<label><input name="reg_addition" id="reg_addition0" type="radio" value="0" onclick='change_reg_addition(this);'/>要クレジット </label>
						<input name="reg_addition_txt" type="text" id="reg_addition_txt0" size="30"  disabled="disabled"/>
						<input name="reg_addition_txt" type="text" id="reg_addition_txt1" size="30"  disabled="disabled"/>
					<?php  } ?>
				</dd>
<!--added by wangtongchao 2011-11-26 end-->
<!--change by wangtongchao 2011-11-26 begin reg_addition_txt1 to reg_addition_txt2-->
				<dd class="outside">
				<?php  if($initflg != 1){ ?>
					<?php  if((int)array_get_value($_SESSION,'reg_addition' ,"") == 1){ ?>
						<label><input name="reg_addition" id="reg_addition1" type="radio" checked="checked" value="1" onclick='change_reg_addition(this);'/>要使用許可 </label>
						<!-- changed by wangtongchao 2011-12-16 begin -->
						<input name="reg_addition_txt" type="text" id="reg_addition_txt2" value="<?php  echo array_get_value($_SESSION,'reg_addition1' ,""); ?>" size="30" />
						<!-- changed by wangtongchao 2011-12-16 end -->
					<?php  }else{ ?>
						<label><input name="reg_addition" id="reg_addition1" type="radio" value="1" onclick='change_reg_addition(this);'/>要使用許可 </label>
						<input name="reg_addition_txt" type="text" id="reg_addition_txt2" size="30"  disabled="disabled"/>
					<?php  } ?>
				<?php  }else{ ?>
					<label><input name="reg_addition" id="reg_addition1" type="radio" value="1" onclick='change_reg_addition(this);'/>要使用許可 </label>
					<input name="reg_addition_txt" type="text" id="reg_addition_txt2" size="30" disabled="disabled"/>
				<?php  } ?>
				</dd>
<!--change by wangtongchao 2011-11-26 end reg_addition_txt1 to reg_addition_txt2-->

			</dl>
			<dl class="reg_account reg_clear">
				<dt>このアカウントのみ使用可</dt>
				<?php  if($initflg != 1){ ?>
					<?php  if((int)array_get_value($_SESSION,'monopoly_use' ,"") == 1){ ?>
						<dd><label><input name="reg_account" id="reg_account" checked="checked" type="checkbox" value="1" />この申請アカウント </label></dd>
					<?php  }else{ ?>
						<dd><label><input name="reg_account" id="reg_account" type="checkbox" value="1" />この申請アカウント </label></dd>
					<?php  } ?>
				<?php  }else{ ?>
					<dd><label><input name="reg_account" id="reg_account" type="checkbox" value="1" />この申請アカウント </label></dd>
				<?php  } ?>
			</dl>
		</div>
		<div>
			<h2>版権情報</h2>
			<dl class="reg_p_obtaining reg_clear reg_list_none_top">
				<dt>写真入手元</dt>
				<?php  disp_borrowing_ahead($borrow_id,$borrow_name) ?>
			</dl>
			<dl class="reg_copyright reg_clear">
				<dt>版権所有者</dt>
				<?php  if($initflg != 1){ ?>
					<dd><input name="reg_copyright" type="text" id="reg_copyright" size="30" value="<?php  echo array_get_value($_SESSION,'copyright_owner' ,""); ?>" /></dd>
				<?php  }else{ ?>
					<dd><input name="reg_copyright" type="text" id="reg_copyright" size="30"/></dd>
				<?php  } ?>
			</dl>
			<dl class="reg_mate_mana reg_clear">
				<dt>素材管理番号</dt>
				<?php  if($initflg != 1){ ?>
					<dd><input name="reg_mate_mana" type="text" id="reg_mate_mana" size="30" value="<?php  echo array_get_value($_SESSION,'reg_mate_mana' ,""); ?>" /></dd>
				<?php  }else{ ?>
					<dd><input name="reg_mate_mana" type="text" id="reg_mate_mana" size="30"/></dd>
				<?php  } ?>
			</dl>
			<dl class="reg_bud_number reg_clear">
				<dt>BUD_PHOTO番号</dt>
				<dd>
				<!-- changed by wangtongchao 2011-12-16 begin added  style="ime-mode:disabled"  -->
					<?php  if($initflg != 1){ ?>
						<?php  if(array_get_value($_SESSION,'reg_bud_number' ,"") == 1){ ?>
						<label><input name="reg_bud_number" id="reg_bud_number0" type="radio" value="1" checked="checked" onclick="change_bud_number_radio(this);"/>ある </label>
						<!-- changed by wangtongchao 2011-12-16 begin -->
						<input name="reg_bud_number_txt" id="reg_bud_number_txt" type="text" size="30" value="<?php  echo array_get_value($_SESSION,'bud_photo_no' ,"");?>" style="ime-mode:disabled" />
						<!-- changed by wangtongchao 2011-12-16 end -->
						<?php  }else{ ?>
						<label><input name="reg_bud_number" id="reg_bud_number0" type="radio" value="1" onclick="change_bud_number_radio(this);"/>ある </label>
						<input name="reg_bud_number_txt" id="reg_bud_number_txt" type="text" size="30" disabled="disabled" style="ime-mode:disabled" onblur='check_reg_bud_number(this);' />
						<?php  } ?>
					<?php  }else{ ?>
						<label><input name="reg_bud_number" id="reg_bud_number0" type="radio" value="1" onclick="change_bud_number_radio(this);"/>ある </label>
						<input name="reg_bud_number_txt" id="reg_bud_number_txt" type="text" size="30" disabled="disabled" style="ime-mode:disabled" onblur='check_reg_bud_number(this);' />
					<?php  } ?>
				<!-- changed by wangtongchao 2011-12-16 end -->
				</dd>
				<?php  if($initflg != 1){ ?>
					<?php  if(array_get_value($_SESSION,'reg_bud_number' ,"") == 0){ ?>
					<dd class="other"><label><input name="reg_bud_number" id="reg_bud_number1" checked="checked" type="radio" value="0" onclick="change_bud_number_radio(this);" />なし </label></dd>
					<?php  }else{ ?>
					<dd class="other"><label><input name="reg_bud_number" id="reg_bud_number1" type="radio" value="0" onclick="change_bud_number_radio(this);" />なし </label></dd>
					<?php  } ?>
				<?php  }else{ ?>
				<dd class="other"><label><input name="reg_bud_number" id="reg_bud_number1" type="radio" value="0" onclick="change_bud_number_radio(this);" />なし </label></dd>
				<?php  } ?>
			</dl>
		</div>
		<div>
			<h2>登録情報</h2>
			<dl class="reg_customer_info reg_clear reg_list_none_top">
				<dt>お客様情報</dt>
				<dd> 部署名
				<?php  if($initflg != 1){ ?>
					<input name="post_name" type="text" id="post_name" value="<?php  echo array_get_value($_SESSION,'customer_section' ,""); ?>" size="15" />
				<?php  }else{ ?>
					<input name="post_name" type="text" id="post_name" size="15" />
				<?php  } ?>
					名前
				<?php  if($initflg != 1){ ?>
					<input name="first_name" type="text" id="first_name" value="<?php  echo array_get_value($_SESSION,'customer_name' ,""); ?>" size="30" />
				<?php  }else{ ?>
					<input name="first_name" type="text" id="first_name" size="30" />
				<?php  } ?>
				</dd>
			</dl>
			<dl class="reg_apply reg_clear">
				<dt>登録申請者</dt>
				<dd>
					<input name="reg_apply_id" type="hidden" id="reg_apply_id" size="30" value="<?php echo $s_login_id?>" readonly="readonly" />
					<input name="reg_apply" type="text" id="reg_apply" size="30" value="<?php echo $s_login_name?>" readonly="readonly" />
				</dd>
			</dl>
			<dl class="reg_permission reg_clear">
				<dt>登録許可者</dt>
				<dd>
					<input name="reg_permission_id" type="hidden" id="reg_permission_id" size="30" value="<?php echo $s_login_id?>" readonly="readonly"/>
					<input name="reg_permission" type="text" id="reg_permission" size="30" value="<?php echo $s_login_name?>" readonly="readonly"/>
				</dd>
			</dl>
		</div>

		<div>
			<h2>備考</h2>
			<?php  if($initflg != 1){ ?>
				<p class="reg_remarks"><textarea name="reg_remarks" id="textarea2" cols="70" rows="5"><?php  echo array_get_value($_SESSION,'note' ,""); ?></textarea></p>
			<?php  }else{ ?>
				<p class="reg_remarks"><textarea name="reg_remarks" id="textarea2" cols="70" rows="5"></textarea></p>
			<?php  } ?>
		</div>
		<div class="reg_search_btn">
			<ul>
				<li class="bt_reg_confirm">
				<p><a href="#" onclick="form_submit();return false;" title="登録確認">登録確認</a></p>
				</li>
				<li class="bt_reg_clear"><a href="#" onclick="form_clear();" title="内容をクリア">内容をクリア</a></li>
			</ul>
			<p>※続けて画像を登録申請する場合は、前の入力内容に上書き、もしくはクリアして入力してください</p>
		</div>
		<input type="hidden" id="p_dfrom" name="p_dfrom" value="" />
		<input type="hidden" id="p_dto" name="p_dto" value="" />
		<input type="hidden" id="p_keyword_str" name="p_keyword_str" value="" />
	</div>
</div>
</div>
</form>
</body>

</html>
