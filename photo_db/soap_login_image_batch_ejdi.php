<?php
date_default_timezone_set('Asia/Tokyo');
mb_internal_encoding('utf-8');
mb_http_output('utf-8');

$imgdir = "./ejdi/";

$classification_id1 = "";											// 分類ID
$classification_name1 = "";											// 分類
$direction_id1 = "";												// 方面ID
$direction_name1 = "";												// 方面
$country_prefecture_id1 = "";										// 国・都道府県ID
$country_prefecture_name1 = "";										// 国・都道府県
$place_id1 = "";													// 地名ID
$place_name1 = "";													// 地名

$p_photo_extentions = "";											// 画像内容
$p_photo_extentions_ok = "";										// 画像内容

$okflg = true;

// PhotoImageのインスタンスを生成します。
$pi;

$charset = 'UTF-8';

// データーベース情報です
//$db_host = '10.254.2.63';
//$db_host = '10.254.2.39';
$db_host = '127.0.0.1';
$db_user = 'ximage';
$db_password = 'kCK!7wu4';
$db_name = 'ximage';
$db_charset = 'utf8';
// サイト情報
$site_name = '写真管理システム';
$site_url = 'https://x.hankyu-travel.com/photo_db/';
$db_link;

//$db_host = 'localhost';
//$db_user = 'root';
//$db_password = '222222';
//$db_name = 'photo';
//$site_url = 'https://www.e-mon.vc/test/photodb1213/';
//$site_name = '写真管理システム';
//
//$db_charset = 'utf8';
//$db_link;

// ファイルアップロード用
$upload_conf['dir'] = "./uploads/";										// アップロードするディレクトリ
$upload_conf['temp_dir'] = "./temporary/";								// テンポラリーディレクトリ
$upload_conf['maxsize'] = 1000000;										// アップロードファイルの制限サイズ
$upload_conf['site_url'] = $site_url;									// サイトURL
// サムネイル保存用フォルダ
$thumb_dir = array("./thumb1/", "./thumb2/","./thumb3/","./thumb4/");
$thumb_width = array(400, 200, 200, 400);	// サムネイルの横幅　最初の800は固定です。（ここに設定されているだけ作成します。）
$write_credit = array(true, true, true, true);		// クレジットをサムネイルに書き込むかどうか　最初のtrueは固定です。

$font_name = "./sazanami-gothic.ttf";
$credit_fontsize = array(8, 10, 14, 18, 22, 26);						// -160, -320, -480, -640, -800, 801-（変更しないで下さい）

// WSDLのURL
//$wsdl = "https://".$_SERVER["SERVER_NAME"].dirname($_SERVER["PHP_SELF"])."/"."soap_login_image_batch_ejdi.wsdl";
$wsdl = "soap_login_image_batch_ejdi.wsdl";

/**
 *  配列から値を取り出します
 *
 *  配列から値を取り出します。もし連想キーが存在しない場合はデフォルト値（引数３）を返します
 *
 *  @param array $array 値を取得したい配列
 *  @param mixed $key 配列から値を取得したい連想キー
 *  @return mixed 配列から取り出した値、連想キーが存在しなければ$defaultを返します。
 * 例）
 *   $first_name = array_get_value($user_info, 'first_name', 'なかお');
 *   $last_name = array_get_value($user_info, 'last_name', 'ゆういち');
 */
function array_get_value($array, $key, $default)
{
	//return $array[$key];
    return isset($array[$key]) ? $array[$key]: $default;
}

//-------半角数字チェック
function Chk_num($num)
{
	if (ereg("[^0-9]+",$num))
	{
		return false;
	}else{
		return true;
	}
}

/*
 * 関数名：db_connect
 * 関数説明：DBの接続
 * パラメタ：無し
 * 戻り値：PDO
 */
function db_connect()
{
	global $db_host, $db_name, $db_user, $db_password, $db_charset;

	// パスワード以外が空の場合はエラーとします。
	if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_charset))
	{
		return;
	}

	// データベースに接続します。
	$hostdb = "mysql:host=". $db_host . "; dbname=" . $db_name;
	$pdo = new PDO($hostdb, $db_user, $db_password);

	// 使用するキャラクターセットを設定します。
	//$sql = "set character SET :DBCHAR";
	$sql = "set names :DBCHAR";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':DBCHAR', $db_charset);
	$result = $stmt->execute();

	// PDOのインスタンスを返します。
	return $pdo;
}

/*
 * 関数名：uploadfiles
 * 関数説明：ファイルのアップ
 * パラメタ：
 * csvcontent:ファイルの内容
 * s_logininfo：ログイン情報
 * 戻り値：アップロードは成功するかどうか、OK/ERR
 */
function uploadfiles($csvcontent,$s_logininfo)
{
	file_put_contents('/home/xhankyu/public_html/photo_db/soap_login_image_batch_limi.txt', 'ejdl'.PHP_EOL,FILE_APPEND);
	return "OK";
	global $db_link,$pi,$data_ary;
	global $classification_id1, $direction_id1, $country_prefecture_id1, $place_id1;
	global $classification_name1,$direction_name1,$country_prefecture_name1,$place_name1;
	global $p_photo_extentions,$p_photo_extentions_ok,$okflg;

	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();
		// PhotoImageのインスタンスを生成します。
		$pi = new PhotoImageDB ();
	}
	catch(Exception $ex)
	{
		return "ERR";
	}

	$classification_id1 = "";											// 分類ID
	$classification_name1 = "";											// 分類
	$direction_id1 = "";												// 方面ID
	$direction_name1 = "";												// 方面
	$country_prefecture_id1 = "";										// 国・都道府県ID
	$country_prefecture_name1 = "";										// 国・都道府県
	$place_id1 = "";													// 地名ID
	$place_name1 = "";													// 地名

	$p_photo_extentions = "";											// 画像内容
	$p_photo_extentions_ok = "";										// 画像内容

	$csv_content = array();

	$csv_content = split("\t",$csvcontent);
	//データ
	$data_ary = array();

	// 画像管理番号
	if (!empty($csv_content[0]))
	{
		// --------画像管理番号を作成する（開始）------------------------------
		$ipos= strpos($csv_content[0],"-");
		$tmp_photo_mno1_1 = $csv_content[32];//申請者管理番号
		if (!empty($tmp_photo_mno1_1))
		{
			$tmp_photo_mno1 = sprintf("%05d",$tmp_photo_mno1_1);
		} else {
			$tmp_photo_mno1 = "00000";//ディフォルト設定
		}

		$tmp_photo_mno2 = substr($csv_content[0],0,$ipos);
		$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
		$tmp_photo_mno3 = $p_maxno;
		$i_pos = strpos($csv_content[0],".");
		$tmp_photo_mno4 =substr($csv_content[0],$i_pos);
		$tmp_photo_mon_res = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
		$exitstedflg = check_Photomon($db_link,$tmp_photo_mon_res);
		if($exitsedflg == true)
		{
			$i = -1;
			$flg = "err";
			for($i=1;$i<=10;$i++)
			{
				$tmp_photo_mno3 = $p_maxno+$i;
				$tmp_photo_mon_res = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
				$exitstedflg1 = check_Photomon($db_link,$tmp_photo_mon_res);
				if($exitstedflg1==false)
				{
					$flg = "ok";
					//break;
					exit;
				}
			}
			if($flg == "err")
			{
				return "ERR";
			}
			$tmp_photo_mon_res = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
			$data_ary['photo_mno'] = $tmp_photo_mon_res;
			$data_ary['reg_photo_mno'] = $tmp_photo_mno2;
			$p_maxno = $tmp_photo_mno3 - 1;
			$pi->setmaxno($db_link,$tmp_photo_mno2,$p_maxno);
			//break;
			exit;
		} else {
			$data_ary['photo_mno'] = $tmp_photo_mon_res;
			$data_ary['reg_photo_mno'] = $tmp_photo_mno2;
		}
		// --------画像管理番号を作成する（終了）------------------------------
	}

	//BUD_PHOTO番号
	$bud_photo_no = $csv_content[5];
	if (!empty($bud_photo_no) && strlen($bud_photo_no) > 0)
	{
		//登録区分
		$data_ary['reg_division'] = 2;
	} else {
		//登録区分
		$data_ary['reg_division'] = 1;
	}

	//元画像管理番号
	$source_image_no = $csv_content[4];
	if (!empty($source_image_no) && strlen($source_image_no) > 0)
	{
		$data_ary['reg_mate_mana'] = $source_image_no;
	} else {
		$data_ary['reg_mate_mana'] = "";
	}

	//BUD_PHOTO番号
	if (!empty($bud_photo_no) && strlen($bud_photo_no) > 0)
	{
		$data_ary['reg_bud_number'] = 1;
		$data_ary['reg_bud_number_txt'] = $bud_photo_no;
	} else {
		$data_ary['reg_bud_number'] = 0;
	}

	//写真名
	$photo_name = $csv_content[6];
	if (!empty($photo_name) && strlen($photo_name) > 0)
	{
		$data_ary['reg_subject'] = $photo_name;
	} else {
		$data_ary['reg_subject'] = "";
	}

	//内容
	$photo_explanation = $csv_content[11];
	if (!empty($photo_explanation) && strlen($photo_explanation) > 0)
	{
		$data_ary['reg_material_txt'] = $photo_explanation;
	} else {
		$data_ary['reg_material_txt'] = "";
	}

	$take_picture_time_id = $csv_content[12];
	if (!empty($take_picture_time_id))
	{
		// 撮影時期２
		if ($take_picture_time_id == "春")
		{
			$data_ary['rad_kisetu'] = 1;
		} elseif ($take_picture_time_id == "夏") {
			$data_ary['rad_kisetu'] = 2;
		} elseif ($take_picture_time_id == "秋") {
			$data_ary['rad_kisetu'] = 3;
		} elseif ($take_picture_time_id == "冬") {
			$data_ary['rad_kisetu'] = 4;
		// 撮影時期１
		} else {
			$take_picture_time_id = mb_convert_kana($csv_content[12],"a","UTF-8");
			$ipos1 = strpos($take_picture_time_id,"月");
			$ipos2 = strpos($take_picture_time_id,"年");
			$istart = -1;
			if ($ipos2 > 0) $istart = $ipos2 + 3;

			if ((int)$ipos1 > 0)
			{
				if ($istart > 0)
				{
					$take_picture_time_id1 = substr($take_picture_time_id,$istart);
					$itmp_pos = strpos($take_picture_time_id1,"月");
					$tmp_t = substr($take_picture_time_id1,0,(int)$itmp_pos);
					$take_picture_time_id1 = $tmp_t;
				} else {
					$take_picture_time_id1 = substr($take_picture_time_id,0,(int)$ipos1);
				}

				if (!empty($take_picture_time_id1) && strlen($take_picture_time_id1) > 0)
				{
					if (Chk_num($take_picture_time_id1))
					{
						$take_picture_time_id2 = (int)$take_picture_time_id1;
						$data_ary['time2'] = $take_picture_time_id2;
						$take_picture_time_id = $take_picture_time_id2;
					}
				}
			} else {
				$tmp1 = (int)$take_picture_time_id;
				if ($tmp1 >= 1 && $tmp1 <= 12)
				{
					$data_ary['time2'] = $tmp1;
					$take_picture_time_id = $tmp1;
				}
			}
		}
	}

	//掲載期間：登録日
	$dfrom = date("Y-m-d");
	$data_ary['p_dfrom'] = $dfrom;

	// 掲載期間（To）
	$dto = $csv_content[14];
	if (!empty($dto))
	{
		$data_ary['p_dto'] = $dto;
	} else {
		$data_ary['p_dto'] = "";
	}

	if (!empty($dto))
	{
		// 期間
		$data_ary['reg_pub_period'] = "shitei";
	} else {
		$data_ary['reg_pub_period'] = "mukigen";
	}

	//写真入手元
	$content_borrowing_ahead = $csv_content[15];
	if (!empty($content_borrowing_ahead) && strlen($content_borrowing_ahead) > 0)
	{
		//写真入手元IDと写真入手元
		$data_ary['reg_p_obtaining'] = 2;
		$data_ary['reg_p_obtaining_txt'] = $content_borrowing_ahead;
	} else {
		//写真入手元IDと写真入手元
		$data_ary['reg_p_obtaining'] = 1;
	}

	$reg_pub_possible = $csv_content[16];
	if (!empty($reg_pub_possible))
	{
		// 使用範囲
		if ($reg_pub_possible == "トラベルコムのみ")
		{
			$data_ary['reg_pub_possible'] = 1;
		} elseif ($reg_pub_possible == "外部出稿可") {
			$data_ary['reg_pub_possible'] = 2;
		} else {
			$data_ary['reg_pub_possible'] = 3;
		}
		$data_ary['reg_pub_possible_txt'] = $reg_pub_possible;
	} else {
		$data_ary['reg_pub_possible_txt'] = "";
	}

	//付加条件：クレジット
	$additional_constraints1 = $csv_content[18];
	if (!empty($additional_constraints1) && strlen($additional_constraints1) > 0)
	{
		$data_ary['reg_addition0'] = $additional_constraints1;
	} else {
		$data_ary['reg_addition0'] = "";
	}

	//付加条件：要確認
	$additional_constraints2 = $csv_content[19];
	if (!empty($additional_constraints2) && strlen($additional_constraints2) > 0)
	{
		$data_ary['reg_addition1'] = $additional_constraints2;
	} else {
		$data_ary['reg_addition1'] = "";
	}

	//独占使用
	$monopoly_use = $csv_content[20];
	if (!empty($monopoly_use) && strlen($monopoly_use) > 0)
	{
		$data_ary['reg_account'] = "1";
	} else {
		$data_ary['reg_account'] = "";
	}

	//版権所有者
	$copyright_owner = $csv_content[22];
	if (!empty($copyright_owner) && strlen($copyright_owner) > 0)
	{
		$data_ary['reg_copyright'] = $copyright_owner;
	} else {
		$data_ary['reg_copyright'] = "";
	}

	// お客様部署
	$customer_section = $csv_content[24];
	if (!empty($customer_section))
	{
		$data_ary['post_name'] = $customer_section;
	} else {
		$data_ary['post_name'] = "";
	}

	// お客様名
	$customer_name = $csv_content[25];
	if (!empty($customer_name))
	{
		$data_ary['first_name'] = $customer_name;
	} else {
		$data_ary['first_name'] = "";
	}

	$tmp_photo_mno1_1 = $csv_content[32];//申請者管理番号
	if (!empty($tmp_photo_mno1_1))
	{
		$tmp_photo_mno1 = sprintf("%05d",$tmp_photo_mno1_1);
	} else {
		$tmp_photo_mno1 = "00000";//ディフォルト設定
	}

	$s_userid = "";
	$s_username = "";
	select_user($tmp_photo_mno1,$s_userid,$s_username);

//yupengbo comment start 2011/06/08
//	// 登録申請アカウント
//	$data_ary['reg_apply_id'] = $s_userid;
//
//	// 登録申請者
//	$data_ary['reg_apply'] =  $s_username;
//
//	$s_login_ary = array();
//	$s_login_ary = split(";",$s_logininfo);
//
//	// 登録許可アカウント
//	$permission_account = $s_login_ary[0];
//	$data_ary['reg_permission_id'] = $permission_account;
//
//	// 登録許可者
//	$data_ary['reg_permission'] = $s_login_ary[1];
//yupengbo comment end 2011/06/08

//yupengbo add start 2011/06/08
	$s_login_ary = array();
	$s_login_ary = split(";",$s_logininfo);
	
	$data_ary['reg_apply_id'] = $s_login_ary[0];// 登録申請アカウント
	
	$data_ary['reg_apply'] = $s_login_ary[1];// 登録申請者
	
	$data_ary['reg_permission_id'] = $s_userid;// 登録許可アカウント
	
	$data_ary['reg_permission'] =  $s_username;// 登録許可者
//yupengbo add end   2011/06/08

	//備考
	$note = $csv_content[31];
	if (!empty($note) && strlen($note) > 0)
	{
		$data_ary['reg_remarks'] = $note;
	} else {
		$data_ary['reg_remarks'] = "";
	}

	//カテゴリ
	$category = $csv_content[21];
	$category_tmp_ary = array();
	$category_tmp_ary = split(" ",$category);
	$keywordstr = "";
	for ($i = 0; $i < count($category_tmp_ary); $i++)
	{
		if ($category_tmp_ary[$i] == "風景")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"自然・植物") > 0)
				{
				} else {
					$keywordstr .= " 自然・植物";
				}
			} else {
				$keywordstr = "自然・植物";
			}
		}

		if ($category_tmp_ary[$i] == "海")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"自然・植物") > 0)
				{
					$keywordstr .= " 海・ビーチ";
				} else {
					$keywordstr .= " 自然・植物 海・ビーチ";
				}
			} else {
				$keywordstr = "自然・植物 海・ビーチ";
			}
		}

		if ($category_tmp_ary[$i] == "山" || $category_tmp_ary[$i] == "川" ||
		    $category_tmp_ary[$i] == "滝" || $category_tmp_ary[$i] == "木" ||
		    $category_tmp_ary[$i] == "桜" || $category_tmp_ary[$i] == "紅葉")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"自然・植物") > 0)
				{
					$keywordstr .= " ".$category_tmp_ary[$i];
				} else {
					$keywordstr .= " 自然・植物 ".$category_tmp_ary[$i];
				}
			} else {
				$keywordstr = "自然・植物 ".$category_tmp_ary[$i];
			}
		}

		if ($category_tmp_ary[$i] == "花")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"自然・植物") > 0)
				{
					$keywordstr .= " 花・草";
				} else {
					$keywordstr .= " 自然・植物 花・草";
				}
			} else {
				$keywordstr = "自然・植物 花・草";
			}
		}

		if ($category_tmp_ary[$i] == "湖・沼")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"自然・植物") > 0)
				{
					$keywordstr .= " 湖沼";
				} else {
					$keywordstr .= " 自然・植物 湖沼";
				}
			} else {
				$keywordstr = "自然・植物 湖沼";
			}
		}

		if ($category_tmp_ary[$i] == "建物")
		{
//			if (!empty($keywordstr))
//			{
//				$keywordstr .= " 建造物";
//			} else {
//				$keywordstr = "建造物";
//			}
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"建造物") > 0)
				{
				} else {
					$keywordstr .= " 建造物";
				}
			} else {
				$keywordstr = "建造物";
			}
		}

		if ($category_tmp_ary[$i] == "寺社" || $category_tmp_ary[$i] == "教会" ||
		    $category_tmp_ary[$i] == "城" || $category_tmp_ary[$i] == "橋" ||
		    $category_tmp_ary[$i] == "塔" || $category_tmp_ary[$i] == "遺跡" ||
		    $category_tmp_ary[$i] == "像")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"建造物") > 0)
				{
					$keywordstr .= " ".$category_tmp_ary[$i];
				} else {
					$keywordstr .= " 建造物 ".$category_tmp_ary[$i];
				}
			} else {
				$keywordstr = "建造物 ".$category_tmp_ary[$i];
			}
		}

		if ($category_tmp_ary[$i] == "モニュメント")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"建造物") > 0)
				{
					$keywordstr .= " モニュメント（記念碑）";
				} else {
					$keywordstr .= " 建造物 モニュメント（記念碑）";
				}
			} else {
				$keywordstr = "建造物 モニュメント（記念碑）";
			}
		}

		if ($category_tmp_ary[$i] == "美術館" || $category_tmp_ary[$i] == "博物館")
		{
//			if (!empty($keywordstr))
//			{
//				$keywordstr .= " 施設 美術館・博物館";
//			} else {
//				$keywordstr = "施設 美術館・博物館";
//			}
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"施設") > 0)
				{
					$keywordstr .= " 美術館・博物館 ";
				} else {
					$keywordstr .= " 施設 美術館・博物館";
				}
			} else {
				$keywordstr = "施設 美術館・博物館";
			}
		}

		if ($category_tmp_ary[$i] == "自然公園")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"施設") > 0)
				{
					$keywordstr .= " 公園・庭園";
				} else {
					$keywordstr .= " 施設 公園・庭園";
				}
			} else {
				$keywordstr = "施設 公園・庭園";
			}
		}

		if ($category_tmp_ary[$i] == "遊園地等")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"施設") > 0)
				{
					$keywordstr .= " 動物園・水族館・遊園地";
				} else {
					$keywordstr .= " 施設 動物園・水族館・遊園地";
				}
			} else {
				$keywordstr = "施設 動物園・水族館・遊園地";
			}
		}

		if ($category_tmp_ary[$i] == "ゴルフ")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"施設") > 0)
				{
					$keywordstr .= " ゴルフ場";
				} else {
					$keywordstr .= " 施設 ゴルフ場";
				}
			} else {
				$keywordstr = "施設 ゴルフ場";
			}
		}

		if ($category_tmp_ary[$i] == "レストラン")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"施設") > 0)
				{
					$keywordstr .= " レストラン";
				} else {
					$keywordstr .= " 施設 レストラン";
				}
			} else {
				$keywordstr = "施設 レストラン";
			}
		}

		if ($category_tmp_ary[$i] == "店")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"施設") > 0)
				{
					$keywordstr .= " 店舗";
				} else {
					$keywordstr .= " 施設 店舗";
				}
			} else {
				$keywordstr = "施設 店舗";
			}
		}

		if ($category_tmp_ary[$i] == "温泉")
		{
			if (!empty($keywordstr))
			{
				$keywordstr .= " 温泉";
			} else {
				$keywordstr = "温泉";
			}
		}

		if ($category_tmp_ary[$i] == "ホテル")
		{
			if (!empty($keywordstr))
			{
				$keywordstr .= " 宿泊施設";
			} else {
				$keywordstr = "宿泊施設";
			}
		}

		if ($category_tmp_ary[$i] == "乗り物")
		{
//			if (!empty($keywordstr))
//			{
//				$keywordstr .= " 乗り物";
//			} else {
//				$keywordstr = "乗り物";
//			}
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"乗り物") > 0)
				{
				} else {
					$keywordstr .= " 乗り物";
				}
			} else {
				$keywordstr = "乗り物";
			}
		}

		if ($category_tmp_ary[$i] == "航空")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"乗り物") > 0)
				{
					$keywordstr .= " 飛行機";
				} else {
					$keywordstr .= " 乗り物 飛行機";
				}
			} else {
				$keywordstr = "乗り物 飛行機";
			}
		}

		if ($category_tmp_ary[$i] == "鉄道" || $category_tmp_ary[$i] == "バス" ||
		    $category_tmp_ary[$i] == "船")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"乗り物") > 0)
				{
					$keywordstr .= " ".$category_tmp_ary[$i];
				} else {
					$keywordstr .= " 乗り物 ".$category_tmp_ary[$i];
				}
			} else {
				$keywordstr = "乗り物 ".$category_tmp_ary[$i];
			}
		}

		if ($category_tmp_ary[$i] == "食品")
		{
//			if (!empty($keywordstr))
//			{
//				$keywordstr .= " 飲食物";
//			} else {
//				$keywordstr = "飲食物";
//			}
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"飲食物") > 0)
				{
				} else {
					$keywordstr .= " 飲食物";
				}
			} else {
				$keywordstr = "飲食物";
			}
		}

		if ($category_tmp_ary[$i] == "料理")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"飲食物") > 0)
				{
					$keywordstr .= " 料理";
				} else {
					$keywordstr .= " 飲食物 料理";
				}
			} else {
				$keywordstr = "飲食物 料理";
			}
		}

		if ($category_tmp_ary[$i] == "製品")
		{
//			if (!empty($keywordstr))
//			{
//				$keywordstr .= " 品物";
//			} else {
//				$keywordstr = "品物";
//			}
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"品物") > 0)
				{
				} else {
					$keywordstr .= " 品物";
				}
			} else {
				$keywordstr = "品物";
			}
		}

		if ($category_tmp_ary[$i] == "美術")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"品物") > 0)
				{
					$keywordstr .= " 美術品";
				} else {
					$keywordstr .= " 品物 美術品";
				}
			} else {
				$keywordstr = "品物 美術品";
			}
		}

		if ($category_tmp_ary[$i] == "イベント")
		{
//			if (!empty($keywordstr))
//			{
//				$keywordstr .= " イベント";
//			} else {
//				$keywordstr = "イベント";
//			}
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"イベント") > 0)
				{
				} else {
					$keywordstr .= " イベント";
				}
			} else {
				$keywordstr = "イベント";
			}
		}

		if ($category_tmp_ary[$i] == "花火")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"イベント") > 0)
				{
					$keywordstr .= " 花火・祭り";
				} else {
					$keywordstr .= " イベント 花火・祭り";
				}
			} else {
				$keywordstr = "イベント 花火・祭り";
			}
		}

		if ($category_tmp_ary[$i] == "芸能")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"イベント") > 0)
				{
					$keywordstr .= " 芸能鑑賞";
				} else {
					$keywordstr .= " イベント 芸能鑑賞";
				}
			} else {
				$keywordstr = "イベント 芸能鑑賞";
			}
		}

		if ($category_tmp_ary[$i] == "夕景")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"時間") > 0)
				{
					$keywordstr .= " 夕方（夕景）";
				} else {
					$keywordstr .= " 時間 夕方（夕景）";
				}
			} else {
				$keywordstr = "時間 夕方（夕景）";
			}
		}

		if ($category_tmp_ary[$i] == "夜景")
		{
			if (!empty($keywordstr))
			{
				$tmp = "__".$keywordstr;
				if (strpos($tmp,"時間") > 0)
				{
					$keywordstr .= " 夜（夜景）";
				} else {
					$keywordstr .= " 時間 夜（夜景）";
				}
			} else {
				$keywordstr = "時間 夜（夜景）";
			}
		}

		if ($category_tmp_ary[$i] == "スポーツ")
		{
			if (!empty($keywordstr))
			{
				$keywordstr .= " スポーツ";
			} else {
				$keywordstr = "スポーツ";
			}
		}

		if ($category_tmp_ary[$i] == "街")
		{
			if (!empty($keywordstr))
			{
				$keywordstr .= " 街並み";
			} else {
				$keywordstr = "街並み";
			}
		}

		if ($category_tmp_ary[$i] == "人物" || $category_tmp_ary[$i] == "世界遺産")
		{
			if (!empty($keywordstr))
			{
				$keywordstr .= " ".$category_tmp_ary[$i];
			} else {
				$keywordstr = $category_tmp_ary[$i];
			}
		}

		if ($category_tmp_ary[$i] == "書類等")
		{
			if (!empty($keywordstr))
			{
				$keywordstr .= " 印刷物";
			} else {
				$keywordstr = "印刷物";
			}
		}
		
		if ($category_tmp_ary[$i] == "動物")
		{
			if (!empty($keywordstr))
			{
				$keywordstr .= " 生物";
			} else {
				$keywordstr = "生物";
			}
		}

//		if ($category_tmp_ary[$i] == "イメージ" || $category_tmp_ary[$i] == "その他")
//		{
//			//振り分け先なし
//		}
	}

	// 分類ID
	$p_classification_name1 = $csv_content[7];
	if($p_classification_name1 == "海外")
	{
		// 方面ID
		$p_direction_name1 = $csv_content[8];
		// 国・都道府県
		$p_country_prefecture_name1 = trimspace($csv_content[9]);
		$tmp1 = getCountryPrefectureName($p_country_prefecture_name1);
		$p_country_prefecture_name1 = $tmp1;
		// 地名ID
		$p_place_name1 = $csv_content[10];
	} else {
		// 方面ID
		$p_direction_name1 = $csv_content[8];
		// 国・都道府県
		$p_country_prefecture_name1 = trimspace($csv_content[9]);
		$tmpstr = "__".$p_country_prefecture_name1;
		$str_pos1 = strpos($p_country_prefecture_name1,"県");
		$str_pos2 = strpos($p_country_prefecture_name1,"府");
		$str_pos3 = strpos($tmpstr,"東京都");
		if($str_pos1 > 0)
		{
			$tmp = substr($p_country_prefecture_name1,0,$str_pos1);
			$p_country_prefecture_name1 = $tmp;
		} elseif($str_pos2 > 0) {
			$tmp = substr($p_country_prefecture_name1,0,$str_pos2);
			$p_country_prefecture_name1 = $tmp;
		} elseif($str_pos3 > 0) {
			$p_country_prefecture_name1 = "東京";
		}
		// 地名ID
		$p_place_name1 = $csv_content[10];
	}

	$p_photo_extentions = "";
	$p_photo_extentions_ok = "";

	//四つ（分類、方面、国、地名）登録の場合
	if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
	     (empty($p_direction_name1) || $p_direction_name1 == "") &&
	     (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
	     (!empty($p_place_name1) && strlen($p_place_name1) > 0)
	   )
	{
		$okflg4 = get_id("0001",null,null,null,$p_place_name1);
		//データが見つからない場合
		if ($okflg4 == "無し")
		{
			$p_photo_extentions = $p_place_name1;
		} elseif ($okflg4 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1." ".$place_name1 ;
		}
	}

	if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
	     (empty($p_direction_name1) || $p_direction_name1 == "") &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (!empty($p_place_name1) && strlen($p_place_name1) > 0)
	   )
	{
		$okflg4 = get_id("0011",null,null,$p_country_prefecture_name1,$p_place_name1);
		//データが見つからない場合
		if ($okflg4 == "無し")
		{
			$p_photo_extentions = $p_country_prefecture_name1." ".$p_place_name1;
		} elseif ($okflg4 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1." ".$place_name1 ;
		}
	}

	if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
	     (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
	     (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
	     (!empty($p_place_name1) && strlen($p_place_name1) > 0)
	   )
	{
		$okflg4 = get_id("0101",null,$p_direction_name1,null,$p_place_name1);
		//データが見つからない場合
		if ($okflg4 == "無し")
		{
			$p_photo_extentions = $p_direction_name1." ".$p_place_name1;
		} elseif ($okflg4 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1." ".$place_name1 ;
		}
	}

	if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
	     (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (!empty($p_place_name1) && strlen($p_place_name1) > 0)
	   )
	{
		$okflg4 = get_id("0111",null,$p_direction_name1,$p_country_prefecture_name1,$p_place_name1);
		//データが見つからない場合
		if ($okflg4 == "無し")
		{
			$p_photo_extentions = $p_direction_name1." ".$p_country_prefecture_name1." ".$p_place_name1;
		} elseif ($okflg4 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1." ".$place_name1 ;
		}
	}

	if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
	     (empty($p_direction_name1) || $p_direction_name1 == "") &&
	     (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
	     (!empty($p_place_name1) && strlen($p_place_name1) > 0)
	   )
	{
		$okflg4 = get_id("1001",$p_classification_name1,null,null,$p_place_name1);
		//データが見つからない場合
		if ($okflg4 == "無し")
		{
			$p_photo_extentions = $p_classification_name1." ".$p_place_name1;
		} elseif ($okflg4 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1." ".$place_name1 ;
		}
	}

	if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
	     (empty($p_direction_name1) || $p_direction_name1 == "") &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (!empty($p_place_name1) && strlen($p_place_name1) > 0)
	   )
	{
		$okflg4 = get_id("1011",$p_classification_name1,null,$p_country_prefecture_name1,$p_place_name1);
		//データが見つからない場合
		if ($okflg4 == "無し")
		{
			$p_photo_extentions = $p_classification_name1." ".$p_country_prefecture_name1." ".$p_place_name1;
		} elseif ($okflg4 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1." ".$place_name1 ;
		}
	}

	if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
	     (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (!empty($p_place_name1) && strlen($p_place_name1) > 0)
	   )
	{
		$okflg4 = get_id("1111",$p_classification_name1,$p_direction_name1,$p_country_prefecture_name1,$p_place_name1);
		//データが見つからない場合
		if ($okflg4 == "無し")
		{
			$p_photo_extentions = $p_classification_name1." ".$p_direction_name1." ".$p_country_prefecture_name1." ".$p_place_name1;
		} elseif ($okflg4 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1." ".$place_name1 ;
		}
	}

	//三つ（分類、方面、国、地名）登録の場合
	if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
	     (empty($p_direction_name1) || $p_direction_name1 == "") &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (empty($p_place_name1) || $p_place_name1 == "")
	   )
	{
		$okflg3 = get_id("0010",null,null,$p_country_prefecture_name1,null);
		//データが見つからない場合
		if ($okflg3 == "無し")
		{
			$p_photo_extentions = $p_country_prefecture_name1;
		} elseif ($okflg3 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1 ;
		}
	}

	if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
	     (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (empty($p_place_name1) || $p_place_name1 == "")
	   )
	{
		$okflg3 = get_id("0110",null,$p_direction_name1,$p_country_prefecture_name1,null);
		//データが見つからない場合
		if ($okflg3 == "無し")
		{
			$p_photo_extentions = $p_direction_name1." ".$p_country_prefecture_name1;
		} elseif ($okflg3 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1 ;
		}
	}

	if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
	     (empty($p_direction_name1) || $p_direction_name1 == "") &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (empty($p_place_name1) || $p_place_name1 == "")
	   )
	{
		$okflg3 = get_id("1010",$p_classification_name1,null,$p_country_prefecture_name1,null);
		//データが見つからない場合
		if ($okflg3 == "無し")
		{
			$p_photo_extentions = $p_classification_name1." ".$p_country_prefecture_name1;
		} elseif ($okflg3 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1 ;
		}
	}

	if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
	     (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
	     (!empty($p_country_prefecture_name1) && strlen($p_country_prefecture_name1) > 0) &&
	     (empty($p_place_name1) || $p_place_name1 == "")
	   )
	{
		$okflg3 = get_id("1110",$p_classification_name1,$p_direction_name1,$p_country_prefecture_name1,null);
		//データが見つからない場合
		if ($okflg3 == "無し")
		{
			$p_photo_extentions = $p_classification_name1." ".$p_direction_name1." ".$p_country_prefecture_name1;
		} elseif ($okflg3 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1 ;
		}
	}

	//二つ（分類、方面、国、地名）登録の場合
	if ( (empty($p_classification_name1) || $p_classification_name1 == "") &&
	     (!empty($p_direction_name1) && strlen($p_direction_name1) > 0) &&
	     (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
	     (empty($p_place_name1) || $p_place_name1 == "")
	   )
	{
		$okflg2 = get_id("0100",null,$p_direction_name1,null,null);
		//データが見つからない場合
		if ($okflg2 == "無し")
		{
			$p_photo_extentions = $p_direction_name1;
		} elseif ($okflg2 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1 ;
		}
	}

	//一つ（分類、方面、国、地名）登録の場合
	if ( (!empty($p_classification_name1) && strlen($p_classification_name1) > 0) &&
	     (empty($p_direction_name1) || $p_direction_name1 == "") &&
	     (empty($p_country_prefecture_name1) || $p_country_prefecture_name1 == "") &&
	     (empty($p_place_name1) || $p_place_name1 == "")
	   )
	{
		$okflg1 = get_id("1000",$p_classification_name1,null,null,null);
		//データが見つからない場合
		if ($okflg1 == "無し")
		{
			$p_photo_extentions = $p_classification_name1;
		} elseif ($okflg1 == "1") {
			$p_photo_extentions_ok = $classification_name1." ".$direction_name1 ." ".$country_prefecture_name1 ;
		}
	}

	if (!empty($p_photo_extentions) && strlen($p_photo_extentions) > 0 &&
	    !empty($csv_content[8]) && strlen($csv_content[8]) > 0)
	{
		if (!empty($keywordstr))
		{
			$keywordstr .= " ".$csv_content[8];
		} else {
			$keywordstr = $csv_content[8];
		}
	}

	if (!empty($p_photo_extentions) && strlen($p_photo_extentions) > 0 &&
	    !empty($csv_content[9]) && strlen($csv_content[9]) > 0)
	{
		if (!empty($keywordstr))
		{
			$keywordstr .= " ".$csv_content[9];
		} else {
			$keywordstr = $csv_content[9];
		}
	}

	if (!empty($p_photo_extentions) && strlen($p_photo_extentions) > 0 &&
	    !empty($csv_content[10]) && strlen($csv_content[10]) > 0)
	{
		if (!empty($keywordstr))
		{
			$keywordstr .= " ".$csv_content[10];
		} else {
			$keywordstr = $csv_content[10];
		}
	}

	//カテゴリー
	if (!empty($keywordstr))
	{
		$data_ary['p_keyword_str'] = $keywordstr;
	} else {
		$data_ary['p_keyword_str'] = "";
	}

	$filename = $csv_content[0];
	$tmp = $csv_content[18];
	if (!empty($filename) && !empty($tmp))
	{
		uploadfile($filename,$tmp,$data_ary);
		if ($okflg == false)
		{
			return "ERR";
		} elseif ($okflg == true) {
			return "OK";
		}
	} elseif (!empty($filename) && empty($tmp)) {
		uploadfile($filename,'',$data_ary);
		if ($okflg == false)
		{
			return "ERR";
		} elseif ($okflg == true) {
			return "OK";
		}
	}
}

/**
 * 国名を取得する
 *
 * @param unknown_type $p_cp_name
 */
function getCountryPrefectureName($p_cp_name)
{
	global $db_link;
	$ret_cp_name = $p_cp_name;
	if(!empty($p_cp_name))
	{
		$where = " WHERE ";
		$where .= "country_name_case0 = '".$p_cp_name."'";
		$where .= " OR country_name_case1 = '".$p_cp_name."'";
		$where .= " OR country_name_case2 = '".$p_cp_name."'";
		$where .= " OR country_name_case3 = '".$p_cp_name."'";
		$where .= " OR country_name_case4 = '".$p_cp_name."'";
		$where .= " OR country_name_case5 = '".$p_cp_name."'";
		$where .= " OR country_name_case6 = '".$p_cp_name."'";
		$where .= " OR country_name_case7 = '".$p_cp_name."'";
		$where .= " OR country_name_case8 = '".$p_cp_name."'";
		$where .= " OR country_name_case9 = '".$p_cp_name."'";
		$where .= " OR country_name_case10 = '".$p_cp_name."'";

		$sql = "SELECT country_name_case0 FROM country_case ".$where;
		$stmt = $db_link->prepare($sql);
		// SQLを実行します。
		$result = $stmt->execute();
		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				$registration_country_name = $stmt->fetch(PDO::FETCH_ASSOC);
				$ret_cp_name = $registration_country_name['country_name_case0'];
			}
		}
	}
	return $ret_cp_name;
}

function check_Photomon($db_link,$par_p_mno)
{
	$sql = "select count(*) cnt from photoimg where photo_mno = '".$par_p_mno."'";
	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();
	if ($result == true)
	{
		while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$p_cnt = $image_data['cnt'];
			if($p_cnt > 0)
			{
				return true;
			}
		}
	}
	return false;
}

/*
 * 関数名：uploadfile
 * 関数説明：ファイルのアップ
 * パラメタ：
 * filename:ファイル名
 * additional_constraints1:クレジット
 * p_d_ary:データ
 * 戻り値：アップロードは成功するかどうか、true/false
 */
function uploadfile($filename,$additional_constraints1,$p_d_ary)
{
	global $pi, $db_link,$imgdir,$okflg;

	// アップロード用のオブジェクト
	$fl = NULL;
	$okflg = true;

	try
	{
		// アップロード用のインスタンスを生成します。
		$fl = new FileUploadBatch($filename, "", "", "", "", $additional_constraints1, "");

		if ($fl->result == false)
		{
			$okflg = false;
		}

		// ファイルをアップロードします。
		$fl->upload($imgdir);
		if ($fl->result == false)
		{
			$okflg = false;
		}

		// サムネイルを作成します。
		$fl->make_thumbfile();
		if ($fl->result == false)
		{
			$okflg = false;
		}

		// DB保存用のデータを設定します。
		$pi->up_url = $fl->up_url;					// アップロードURL
		$pi->img_width = $fl->img_width;			// イメージサイズ（横）
		$pi->img_height = $fl->img_height;			// イメージサイズ（縦）
		$pi->ext = $fl->ext;						// 拡張子
		$pi->image_size_x = $fl->img_width[0];		// 画像サイズ（横）
		$pi->image_size_y = $fl->img_height[0];		// 画像サイズ（縦）

		// 画像情報新規用のデータを設定します。
		set_insertdata($p_d_ary);

		// DBにアップロードした画像情報を新規登録します。
		$pi->insert_data($db_link);
		$tmp_photo_mno2 = array_get_value($p_d_ary,"reg_photo_mno","");
		if (!empty($tmp_photo_mno2) && strlen($tmp_photo_mno2) > 0)
		{
			$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
			$pi->setmaxno($db_link,$tmp_photo_mno2,$p_maxno);
		}
	}
	catch(Exception $e)
	{
		$okflg = false;
		//$message = $e->getMessage();
		//throw new Exception($message);
	}
}

/*
 * 関数名：set_insertdata
 * 関数説明：画像情報新規用のデータを設定する
 * パラメタ：無し
 * 戻り値：無し
 */
function set_insertdata($d_ary)
{
	global $pi, $classification_id1, $direction_id1, $country_prefecture_id1, $place_id1, $p_photo_extentions_ok,$p_photo_extentions;

	//画像管理番号
	$pi->photo_mno = array_get_value($d_ary,"photo_mno" ,"");							// 画像管理番号

	$pi->publishing_situation_id = 1;													// 掲載状況(「申請中」を設定する)

	$pi->registration_division_id = array_get_value($d_ary,"reg_division" ,"");			// 登録区分

	$reg_mate_mana = array_get_value($d_ary,"reg_mate_mana" ,"");						// 元画像管理番号
	// BUD_PHOTO番号を入力しない場合
	if (empty($reg_mate_mana) || strlen($reg_mate_mana) <= 0)
	{
		$pi->source_image_no = "元画像なし";											// 元画像管理番号「元画像なし」を設定する
	} else {
		$pi->source_image_no = array_get_value($d_ary,"reg_mate_mana" ,"");				// 元画像管理番号
	}

	$reg_bud_number = array_get_value($d_ary,"reg_bud_number" ,"");						// BUD_PHOTO番号
	// BUD_PHOTO番号の「ある」を選択した場合
	if ((int)$reg_bud_number == 1)
	{
		$pi->bud_photo_no = array_get_value($d_ary,"reg_bud_number_txt" ,"");			// BUD_PHOTO番号
	}

	$pi->photo_name = array_get_value($d_ary,"reg_subject" ,"");						// 写真名（タイトル）

	// --------------------------写真説明-----------------------------------------------------------------
	$tmp_p_explanation = array_get_value($d_ary,"reg_material_txt" ,"");
	if (!empty($p_photo_extentions_ok) && strlen($p_photo_extentions_ok) > 0 && empty($p_photo_extentions))
	{
		if (!empty($tmp_p_explanation))
		{
			$tmp_p_explanation .= " ";
		}
		$tmp_p_explanation .= $p_photo_extentions_ok;
	} elseif (!empty($p_photo_extentions) && strlen($p_photo_extentions) > 0 && empty($p_photo_extentions_ok)) {
		if (!empty($tmp_p_explanation))
		{
			$tmp_p_explanation .= " ";
		}
		$tmp_p_explanation .= $p_photo_extentions;
	}
	$pi->photo_explanation = $tmp_p_explanation;
	// ----------------------------------------------------------------------------------------------------

	$pi->take_picture_time_id = array_get_value($d_ary,"time2" ,"");					// 撮影時期１

	$pi->take_picture_time2_id = array_get_value($d_ary,"rad_kisetu" ,"");				// 撮影時期２

	$pi->dfrom = array_get_value($d_ary,"p_dfrom" ,"");									// 掲載期間（From）

	$pi->dto = array_get_value($d_ary,"p_dto" ,"");										// 掲載期間（To）

	$pi->kikan = array_get_value($d_ary,"reg_pub_period" ,"");							// 期間

	$reg_p_obtaining = array_get_value($d_ary,"reg_p_obtaining" ,"");					// 写真入手元ID
	$pi->borrowing_ahead_id = $reg_p_obtaining;											// 写真入手元
	// 写真入手元の「その他」を選択した場合
	if ((int)$reg_p_obtaining == 2)
	{
		$pi->content_borrowing_ahead = array_get_value($d_ary,"reg_p_obtaining_txt" ,"");// 写真入手元内容
	}

	$reg_pub_possible = array_get_value($d_ary,"reg_pub_possible" ,"-1");				// 使用範囲
	$pi->range_of_use_id = $reg_pub_possible;											// 使用範囲
	// 使用範囲の「外部出稿条件付き」を選択した場合
	if ((int)$reg_pub_possible == 3)
	{
		$pi->use_condition = array_get_value($d_ary,"reg_pub_possible_txt" ,""); 		// 出稿条件
	}

	$reg_addition = array_get_value($d_ary,"reg_addition" ,"");							// 付加条件
	// 付加条件の「要クレジット」を選択した場合
	if ((int)$reg_addition == 0)
	{
		$pi->additional_constraints1 = array_get_value($d_ary,"reg_addition0" ,"");		// 付加条件（クレジット）
	}
	// 付加条件の「要使用許可」を選択した場合
	if ((int)$reg_addition == 1)
	{
		$pi->additional_constraints2 = array_get_value($d_ary,"reg_addition1" ,"");		// 付加条件（要確認）
	}

	$tmp = array_get_value($d_ary,"reg_account" ,"");									// 独占使用
	if (empty($tmp))
	{
		$pi->monopoly_use = 0;															// 独占使用
	} else {
		$pi->monopoly_use = 1;															// 独占使用
	}

	$pi->copyright_owner = array_get_value($d_ary,"reg_copyright" ,"");					// 版権所有者

	$pi->customer_section = array_get_value($d_ary,"post_name" ,"");					// お客様部署

	$pi->customer_name = array_get_value($d_ary,"first_name" ,"");						// お客様名

	$pi->registration_account = array_get_value($d_ary,"reg_apply_id" ,"");				// 登録申請アカウント

	$pi->registration_person = array_get_value($d_ary,"reg_apply" ,"");					// 登録申請者

	$pi->permission_account = array_get_value($d_ary,"reg_permission_id" ,"");				// 登録許可アカウント
	$pi->permission_person =  array_get_value($d_ary,"reg_permission" ,"");					// 登録許可者
	$pi->permission_date = date("Y-m-d");												// 登録許可日

	$pi->note = array_get_value($d_ary,"reg_remarks" ,"");								// 備考

	$pi->register_date = date("Y/m/d H:i:s");											// 登録日、システムの日付を設定する

	$pi->keyword_str = array_get_value($d_ary, 'p_keyword_str' ,"");					// キーワード文字列（スペース区切り）


	// 分類ID(1)は有効の場合
	$pi->registration_classifications->count = 0;

	unset($pi->registration_classifications->classification_id);						// 分類ID
	unset($pi->registration_classifications->classification_name);						// 分類

	unset($pi->registration_classifications->direction_id);								// 方面ID
	unset($pi->registration_classifications->direction_name);							// 方面

	unset($pi->registration_classifications->country_prefecture_id);					// 国・都道府県ID
	unset($pi->registration_classifications->country_prefecture_name);					// 国・都道府県

	unset($pi->registration_classifications->place_id);									// 地名ID
	unset($pi->registration_classifications->place_name);								// 地名

	// 登録分類１をDBに設定する
	$pi->registration_classifications->set_id($classification_id1, $direction_id1, $country_prefecture_id1, $place_id1);
}

/*
 * 関数名：get_id
 * 関数説明：分類、方面、国、都市を取得する
 * パラメタ：
 * strflg:フラグ
 * p_c:分類
 * p_d:方面
 * p_cp:国
 * p_p:都市
 * 戻り値："1"（正常）/"-1"（エラー）/"無し（データ無し）"
 */
function get_id($strflg, $p_c = "", $p_d = "", $p_cp = "", $p_p = "")
{
	global $db_link;
	global $classification_id1, $direction_id1, $country_prefecture_id1, $place_id1;
	global $classification_name1,$direction_name1,$country_prefecture_name1,$place_name1;

	$classification_id1 = "";											// 分類ID
	$classification_name1 = "";											// 分類
	$direction_id1 = "";												// 方面ID
	$direction_name1 = "";												// 方面
	$country_prefecture_id1 = "";										// 国・都道府県ID
	$country_prefecture_name1 = "";										// 国・都道府県
	$place_id1 = "";													// 地名ID
	$place_name1 = "";													// 地名

	$sql = "SELECT classification.classification_id, classification_name,";
	$sql .= "direction.direction_id, direction_name, country_prefecture.country_prefecture_id,";
	$sql .= "country_prefecture_name,place_id,place_name";
	$sql .= " FROM classification, direction, country_prefecture, place";

	$sqlwhere = "";
	if ($p_c != null && !empty($p_c))
	{
		if (strlen($p_c) > 0)
		{
			$sqlwhere = " WHERE classification_name = '".$p_c."'";
		}
	}

	if ($p_d != null && !empty($p_d))
	{
		if (strlen($p_d) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND direction_name = '".$p_d."'";
			} else {
				$sqlwhere = " WHERE direction_name = '".$p_d."'";
			}
			$sqlwhere .= " AND direction.classification_id = classification.classification_id";
		}
	}

	if ($p_cp != null && !empty($p_cp))
	{
		if (strlen($p_cp) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND country_prefecture_name = '".$p_cp."'";
			} else {
				$sqlwhere = " WHERE country_prefecture_name = '".$p_cp."'";
			}
			$sqlwhere .= " AND country_prefecture.direction_id = direction.direction_id";
			if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
			{
				$sqlwhere .= " AND direction.classification_id = classification.classification_id";
			}
		}
	}

	if ($p_p != null && !empty($p_p))
	{
		if (strlen($p_p) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND place_name = '".$p_p."'";
			} else {
				$sqlwhere = " WHERE place_name = '".$p_p."'";
			}
			$sqlwhere .= " AND place.country_prefecture_id = country_prefecture.country_prefecture_id";
			if ($p_cp == null || empty($p_cp) || strlen($p_cp) <= 0)
			{
				$sqlwhere .= " AND country_prefecture.direction_id = direction.direction_id";
			}
			if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
			{
				$sqlwhere .= " AND direction.classification_id = classification.classification_id";
			}
		}
	}

	if (!empty($sqlwhere))
	{
		$sqlwhere .= " LIMIT 1";
		$sql .= $sqlwhere;
	} else {
		return false;
	}

	$stmt = $db_link->prepare($sql);
	// SQLを実行します。
	$result = $stmt->execute();

	// 実行結果をチェックします。
	if ($result == true)
	{
		// 実行結果がOKの場合の処理です。
		$icount = $stmt->rowCount();
		if ($icount > 0)
		{
			$reg_c = $stmt->fetch(PDO::FETCH_ASSOC);

			// 分類IDなどを保存します。
			if ($strflg == "0001" || $strflg == "0011" || $strflg == "0101" || $strflg == "0111" ||
			    $strflg == "1001" || $strflg == "1011" || $strflg == "1111")
			{
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];

				$country_prefecture_id1 = $reg_c['country_prefecture_id'];
				$country_prefecture_name1 = $reg_c['country_prefecture_name'];

				$place_id1 = $reg_c['place_id'];
				$place_name1 = $reg_c['place_name'];
			} elseif ($strflg == "0010" || $strflg == "0110" || $strflg == "1010" || $strflg == "1110") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];

				$country_prefecture_id1 = $reg_c['country_prefecture_id'];
				$country_prefecture_name1 = $reg_c['country_prefecture_name'];
			} elseif ($strflg == "0100") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];
			} elseif ($strflg == "1000") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];
			} else {
				return "無し";
			}
			return "1";
		} else {
			//都市を設定した場合、都市より検索しない場合

			if ($strflg == "0011")
		    {
		    	//国よりもう一度検索する、検索した場合、都市は登録しない
		    	$retval = get_id2($strflg, "", "", $p_cp, "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		    } elseif ($strflg == "0101") {
		    	//方面よりもう一度検索する、検索した場合、国と都市は登録しない
		    	$retval = get_id2($strflg, "", $p_d, "", "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$country_prefecture_id1 = "";
					$country_prefecture_name1 = "";
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		    } elseif ($strflg == "0111") {
		    	//国と方面よりもう一度検索する、検索した場合、都市は登録しない
		    	$retval = get_id2($strflg, "", $p_d, $p_cp, "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		    } elseif ($strflg == "1001") {
		    	//分類よりもう一度検索する、検索した場合、方面と国と都市は登録しない
		    	$retval = get_id2($strflg, $p_c , "", "", "");
		    	if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$direction_id1 = "";
					$direction_name1 = "";
					$country_prefecture_id1 = "";
					$country_prefecture_name1 = "";
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		   	} elseif ($strflg == "1011") {
				//国と分類よりもう一度検索する、検索した場合、都市は登録しない
		   		$retval = get_id2($strflg, $p_c , "", $p_cp, "");
		   		if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					//$place_name1 = "";
					$place_name1 = $p_p;

					return "1";
		    	} else {
		    		return "-1";
		    	}
		   	} elseif ($strflg == "1111") {
		   		$retval = get_id2($strflg, $p_c , $p_d, $p_cp, "");
		   		if ($retval == "無し")
		    	{
		    		return "無し";
		    	} elseif ((int)$retval > 0) {
					$place_id1 = "";
					$place_name1 = "";

					return "1";
		    	} else {
		    		return "-1";
		    	}
		   	} else {
		   		return "無し";
		   	}
		}
	} else {
		return "-1";
	}
}

/*
 * 関数名：get_id2
 * 関数説明：分類、方面、国、都市を取得する
 * パラメタ：
 * strflg:フラグ
 * p_c:分類
 * p_d:方面
 * p_cp:国
 * p_p:都市
 * 戻り値："1"（正常）/"-1"（エラー）/"無し（データ無し）"
 */
function get_id2($strflg, $p_c = "", $p_d = "", $p_cp = "", $p_p = "")
{
	global $db_link;
	global $classification_id1, $direction_id1, $country_prefecture_id1, $place_id1;
	global $classification_name1,$direction_name1,$country_prefecture_name1,$place_name1;

	$classification_id1 = "";											// 分類ID
	$classification_name1 = "";											// 分類
	$direction_id1 = "";												// 方面ID
	$direction_name1 = "";												// 方面
	$country_prefecture_id1 = "";										// 国・都道府県ID
	$country_prefecture_name1 = "";										// 国・都道府県
	$place_id1 = "";													// 地名ID
	$place_name1 = "";													// 地名

	$sql = "SELECT classification.classification_id, classification_name,";
	$sql .= "direction.direction_id, direction_name, country_prefecture.country_prefecture_id,";
	$sql .= "country_prefecture_name,place_id,place_name";
	$sql .= " FROM classification, direction, country_prefecture, place";

	$sqlwhere = "";
	if ($p_c != null && !empty($p_c))
	{
		if (strlen($p_c) > 0)
		{
			$sqlwhere = " WHERE classification_name = '".$p_c."'";
		}
	}

	if ($p_d != null && !empty($p_d))
	{
		if (strlen($p_d) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND direction_name = '".$p_d."'";
			} else {
				$sqlwhere = " WHERE direction_name = '".$p_d."'";
			}
			$sqlwhere .= " AND direction.classification_id = classification.classification_id";
		}
	}

	if ($p_cp != null && !empty($p_cp))
	{
		if (strlen($p_cp) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND country_prefecture_name = '".$p_cp."'";
			} else {
				$sqlwhere = " WHERE country_prefecture_name = '".$p_cp."'";
			}
			$sqlwhere .= " AND country_prefecture.direction_id = direction.direction_id";
			if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
			{
				$sqlwhere .= " AND direction.classification_id = classification.classification_id";
			}
		}
	}

	if ($p_p != null && !empty($p_p))
	{
		if (strlen($p_p) > 0)
		{
			if (!empty($sqlwhere))
			{
				$sqlwhere .= " AND place_name = '".$p_p."'";
			} else {
				$sqlwhere = " WHERE place_name = '".$p_p."'";
			}
			$sqlwhere .= " AND place.country_prefecture_id = country_prefecture.country_prefecture_id";
			if ($p_cp == null || empty($p_cp) || strlen($p_cp) <= 0)
			{
				$sqlwhere .= " AND country_prefecture.direction_id = direction.direction_id";
			}
			if ($p_d == null || empty($p_d) || strlen($p_d) <= 0)
			{
				$sqlwhere .= " AND direction.classification_id = classification.classification_id";
			}
		}
	}

	if (!empty($sqlwhere))
	{
		$sqlwhere .= " LIMIT 1";
		$sql .= $sqlwhere;
	} else {
		return false;
	}

	$stmt = $db_link->prepare($sql);
	// SQLを実行します。
	$result = $stmt->execute();

	// 実行結果をチェックします。
	if ($result == true)
	{
		// 実行結果がOKの場合の処理です。
		$icount = $stmt->rowCount();
		if ($icount > 0)
		{
			$reg_c = $stmt->fetch(PDO::FETCH_ASSOC);

			// 分類IDなどを保存します。
			if ($strflg == "0001" || $strflg == "0011" || $strflg == "0101" || $strflg == "0111" ||
			    $strflg == "1001" || $strflg == "1011" || $strflg == "1111")
			{
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];

				$country_prefecture_id1 = $reg_c['country_prefecture_id'];
				$country_prefecture_name1 = $reg_c['country_prefecture_name'];

				$place_id1 = $reg_c['place_id'];
				$place_name1 = $reg_c['place_name'];
			} elseif ($strflg == "0010" || $strflg == "0110" || $strflg == "1010" || $strflg == "1110") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];

				$country_prefecture_id1 = $reg_c['country_prefecture_id'];
				$country_prefecture_name1 = $reg_c['country_prefecture_name'];
			} elseif ($strflg == "0100") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];

				$direction_id1 = $reg_c['direction_id'];
				$direction_name1 = $reg_c['direction_name'];
			} elseif ($strflg == "1000") {
				$classification_id1 =  $reg_c['classification_id'];
				$classification_name1 = $reg_c['classification_name'];
			} else {
				return "無し";
			}
			return "1";
		} else {
		   	return "無し";
		}
	} else {
		return "-1";
	}
}

/*
 * 関数名：select_user
 * 関数説明：申請者管理番号よりユーザーを検索する
 * パラメタ：
 * p_mno:申請者管理番号
 * p_userid:ユーザーID
 * p_username:ユーザー名前
 * 戻り値：無し
 */
function select_user($p_mno,&$p_userid,&$p_username)
{
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();

		// ユーザー情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "select * from user where compcode = ?";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $p_mno);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				// 正常にデータの取得ができたときの処理です。
				$user = $stmt->fetch(PDO::FETCH_ASSOC);
				$p_userid = $user['login_id'];
				$p_username = $user['user_name'];
			}
		}
	}
	catch(Exception $e)
	{
		$p_userid = "";
		$p_username = "";
		//$message= $e->getMessage();
		//throw new Exception($message);
	}
}

/*
 * 関数名：trimspace
 * 関数説明：文字列の前後の全角と半角スペースを削除する
 * パラメタ：
 * str：文字列
 * 戻り値：スペースを削除した文字列
 */
function trimspace($str)
{
	$tmp2 = preg_replace('/^[ 　]*(.*?)[ 　]*$/u', '$1', $str);
	return $tmp2;
}

class PhotoImageData
{
	var $message;									// メッセージ
	var $error;										// エラー

	var $img_width;									// イメージサイズ（横）	0:元、1:サムネイル1、2:サムネイル2・・・
	var $img_height;								// イメージサイズ（縦）	0:元、1:サムネイル1、2:サムネイル2・・・
	var $up_url;									// アップロードURL（最終的にアップロードされたURL）
													//					0:元、1:サムネイル1、2:サムネイル2・・・
	var $photo_id;									// 写真ID
	var $photo_mno;									// 画像番号
	var $publishing_situation_id;					// 掲載状況
	var $registration_division_id;					// 登録区分
	var $source_image_no;							// 元画像管理番号
	var $bud_photo_no;								// BUD_PHOTO番号
	var $photo_name;								// 写真名（タイトル）
	var $photo_explanation;							// 写真説明
	var $take_picture_time_id;						// 撮影時期１
	var $take_picture_time2_id;						// 撮影時期２
	var $dfrom;										// 掲載期間（From）
	var $dto;										// 掲載期間（To）
	var $kikan;										// 期間
	var $borrowing_ahead_id;						// 写真入手元
	var $content_borrowing_ahead;					// 写真入手元内容
	var $range_of_use_id;							// 使用範囲
	var $use_condition;								// 出稿条件
	var $additional_constraints1;					// 付加条件（クレジット）
	var $additional_constraints2;					// 付加条件（要確認）
	var $monopoly_use;								// 独占使用
	var $copyright_owner;							// 版権所有者
	var $photo_filename;							// 写真ファイル名
	var $photo_filename_th1;						// 写真ファイル名（サムネイル1）
	var $photo_filename_th2;						// 写真ファイル名（サムネイル2）
	var $photo_filename_th3;						// 写真ファイル名（サムネイル3）
	var $photo_filename_th4;						// 写真ファイル名（サムネイル4）
	var $photo_filename_th5;						// 写真ファイル名（サムネイル5）
	var $photo_filename_th6;						// 写真ファイル名（サムネイル6）
	var $photo_filename_th7;						// 写真ファイル名（サムネイル7）
	var $photo_filename_th8;						// 写真ファイル名（サムネイル8）
	var $photo_filename_th9;						// 写真ファイル名（サムネイル9）
	var $photo_filename_th10;						// 写真ファイル名（サムネイル10）
	var $ext;										// 拡張子
	var $customer_section;							// お客様部署
	var $customer_name;								// お客様名
	var $registration_account;						// 登録申請アカウント
	var $registration_person;						// 登録申請者
	var $permission_account;						// 登録許可アカウント
	var $permission_person;							// 登録許可者
	var $permission_date;							// 登録許可日
	var $image_size_x;								// 画像サイズ（横）
	var $image_size_y;								// 画像サイズ（縦）
	var $note;										// 備考
	var $viewableue;								// 表示可否
	var $register_date;								// 登録日
	var $state;										// 状態
	var $keyword_str;								// キーワード
	var $registration_classifications;				// 登録分類
	var $image1;									// バイナリを変換したイメージ（アップロード）
	var $image2;									// バイナリを変換したイメージ（サムネイル1）
	var $image3;									// バイナリを変換したイメージ（サムネイル2）
	var $image4;									// バイナリを変換したイメージ（サムネイル3）
	var $image5;									// バイナリを変換したイメージ（サムネイル4）

	function __construct()
	{
		// タイムゾーンを設定します。
		date_default_timezone_set("Asia/Tokyo");

		// メンバーを初期化します。
		$this->init_data();

		// メッセージを初期化します。
		$this->message = "";
		$this->error = false;
	}

	/**
	 * データを初期化します。
	 */
	function init_data()
	{
		// 初期化します。
		$this->message = "";						// メッセージ
		$this->error = "";							// エラー

		$this->img_width = array();					// イメージサイズ（横）	0:元、1:サムネイル1、2:サムネイル2・・・
		$this->img_height = array();				// イメージサイズ（縦）	0:元、1:サムネイル1、2:サムネイル2・・・
		$this->up_url = array();					// アップロードURL（最終的にアップロードされたURL）
													//					0:元、1:サムネイル1、2:サムネイル2・・・
		$this->ext = "";							// 拡張子（元ファイル名）
		$this->photo_id = -1;						// 写真ID
		$this->photo_mno = "";						// 画像番号
		$this->publishnig_situation_id = -1;		// 掲載状況
		$this->registration_division_id = -1;		// 登録区分
		$this->source_image_no = "";				// 元画像管理番号
		$this->bud_photo_no = "";					// BUD_PHOTO番号
		$this->photo_name = "";						// 写真名（タイトル）
		$this->photo_explanation = "";				// 写真説明
		$this->take_picture_time_id = -1;			// 撮影時期１
		$this->take_picture_time2_id = -1;			// 撮影時期２
		$this->dfrom = "0000-00-00";				// 掲載期間（From）
		$this->dto = "0000-00-00";					// 掲載期間（To）
		$this->kikan = "";							// 期間
		$this->borrowing_ahead_id = -1;				// 写真入手元
		$this->content_borrowing_ahead = "";		// 写真入手元内容
		$this->range_of_use_id = -1;				// 使用範囲
		$this->use_condition = "";					// 出稿条件
		$this->additional_constraints1 = "";		// 付加条件（クレジット）
		$this->additional_constraints2 = "";		// 付加条件（要確認）
		$this->monopoly_use = "";					// 独占使用
		$this->copyright_owner = "";				// 版権所有者
		$this->photo_filename = "";					// 写真ファイル名
		$this->photo_filename_th1 = "";				// 写真ファイル名（サムネイル1）
		$this->photo_filename_th2 = "";				// 写真ファイル名（サムネイル2）
		$this->photo_filename_th3 = "";				// 写真ファイル名（サムネイル3）
		$this->photo_filename_th4 = "";				// 写真ファイル名（サムネイル4）
		$this->photo_filename_th5 = "";				// 写真ファイル名（サムネイル5）
		$this->photo_filename_th6 = "";				// 写真ファイル名（サムネイル6）
		$this->photo_filename_th7 = "";				// 写真ファイル名（サムネイル7）
		$this->photo_filename_th8 = "";				// 写真ファイル名（サムネイル8）
		$this->photo_filename_th9 = "";				// 写真ファイル名（サムネイル9）
		$this->photo_filename_th10 = "";			// 写真ファイル名（サムネイル10）
		$this->ext = "";							// 拡張子
		$this->customer_section = "";				// お客様部署
		$this->customer_name = "";					// お客様名
		$this->registration_account = "";			// 登録申請アカウント
		$this->registration_person = "";			// 登録申請者
		$this->permission_account = "";				// 登録許可アカウント
		$this->permission_person = "";				// 登録許可者
		$this->permission_date = "";				// 登録許可日
		$this->image_size_x = 0;					// 画像サイズ（横）
		$this->image_size_y = 0;					// 画像サイズ（縦）
		$this->note = "";							// 備考
		$this->viewableue = true;					// 表示可否
		$this->register_date = "0000-00-00";		// 登録日
		$this->state = 0;							// 状態
		$this->keyword_str = "";					// キーワード
		$this->registration_classifications = new RegistrationClassifications();
													// 登録分類
	}

	/**
	 * データをセットします。
	 */
	function set_data($imgdata)
	{
		$this->up_url = array();																					// アップロードURL（最終的にアップロードされたURL）
		$this->up_url[0] = $imgdata['photo_filename'];																//	0:元、1:サムネイル1、2:サムネイル2・・・
		$this->up_url[1] = $imgdata['photo_filename_th1'];
		$this->up_url[2] = $imgdata['photo_filename_th2'];
		$this->up_url[3] = $imgdata['photo_filename_th3'];
		$this->up_url[4] = $imgdata['photo_filename_th4'];
		$this->up_url[5] = $imgdata['photo_filename_th5'];
		$this->up_url[6] = $imgdata['photo_filename_th6'];
		$this->up_url[7] = $imgdata['photo_filename_th7'];
		$this->up_url[8] = $imgdata['photo_filename_th8'];
		$this->up_url[9] = $imgdata['photo_filename_th9'];
		$this->up_url[10] = $imgdata['photo_filename_th10'];
		$this->ext = $imgdata['ext'];																				// 拡張子（元ファイル名）
		$this->photo_id = $imgdata['photo_id'];																		// 写真ID
		$this->photo_mno = $imgdata['photo_mno'];																	// 写真管理番号
		$this->publishing_situation_id = $imgdata['publishing_situation_id'];										// 掲載状況
		$this->registration_division_id = $imgdata['registration_division_id'];										// 登録区分
		$this->source_image_no = $imgdata['source_image_no'];														// 元画像管理番号
		$this->bud_photo_no = $imgdata['bud_photo_no'];																// BUD_PHOTO番号
		$this->photo_name = $imgdata['photo_name'];																	// 写真名（タイトル）
		$this->photo_explanation = $imgdata['photo_explanation'];													// 写真説明
		$this->take_picture_time_id = $imgdata['take_picture_time_id'];												// 撮影時期１
		$this->take_picture_time2_id = $imgdata['take_picture_time2_id'];											// 撮影時期２
		$this->dfrom = $imgdata['dfrom'];																			// 掲載期間（From）
		$this->dto = $imgdata['dto'];																				// 掲載期間（To）
		$this->kikan = $imgdata['kikan'];																			// 期間
		$this->borrowing_ahead_id = $imgdata['borrowing_ahead_id'];													// 写真入手元
		$this->content_borrowing_ahead = $imgdata['content_borrowing_ahead'];										// 写真入手元内容
		$this->range_of_use_id = $imgdata['range_of_use_id'];														// 使用範囲
		$this->use_condition = $imgdata['use_condition'];															// 出稿条件
		$this->additional_constraints1 = $imgdata['additional_constraints1'];										// 付加条件（クレジット）
		$this->additional_constraints2 = $imgdata['additional_constraints2'];										// 付加条件（要確認）
		$this->monopoly_use = $imgdata['monopoly_use'];																// 独占使用
		$this->copyright_owner = $imgdata['copyright_owner'];														// 版権所有者
		$this->photo_filename = $imgdata['photo_filename'];															// 写真ファイル名
		$this->photo_filename_th1 = $imgdata['photo_filename_th1'];													// 写真ファイル名（サムネイル1）
		$this->photo_filename_th2 = $imgdata['photo_filename_th2'];													// 写真ファイル名（サムネイル2）
		$this->photo_filename_th3 = $imgdata['photo_filename_th3'];													// 写真ファイル名（サムネイル3）
		$this->photo_filename_th4 = $imgdata['photo_filename_th4'];													// 写真ファイル名（サムネイル4）
		$this->photo_filename_th5 = $imgdata['photo_filename_th5'];													// 写真ファイル名（サムネイル5）
		$this->photo_filename_th6 = $imgdata['photo_filename_th6'];													// 写真ファイル名（サムネイル6）
		$this->photo_filename_th7 = $imgdata['photo_filename_th7'];													// 写真ファイル名（サムネイル7）
		$this->photo_filename_th8 = $imgdata['photo_filename_th8'];													// 写真ファイル名（サムネイル8）
		$this->photo_filename_th9 = $imgdata['photo_filename_th9'];													// 写真ファイル名（サムネイル9）
		$this->photo_filename_th10 = $imgdata['photo_filename_th10'];												// 写真ファイル名（サムネイル10）
		$this->ext = $imgdata['ext'];																				// 拡張子
		$this->customer_section = $imgdata['customer_section'];														// お客様部署
		$this->customer_name = $imgdata['customer_name'];															// お客様名
		$this->registration_account = $imgdata['registration_account'];												// 登録申請アカウント
		$this->registration_person = $imgdata['registration_person'];												// 登録申請者
		$this->permission_account = $imgdata['permission_account'];													// 登録許可アカウント
		$this->permission_person = $imgdata['permission_person'];													// 登録許可者
		$this->permission_date = $imgdata['permission_date'];														// 登録許可日
		$this->image_size_x = $imgdata['image_size_x'];																// 画像サイズ（横）
		$this->image_size_y = $imgdata['image_size_y'];																// 画像サイズ（縦）
		$this->note = $imgdata['note'];																				// 備考
		$this->viewableue = true;																					// 表示可否
		$this->register_date = $imgdata['register_date'];															// 登録日
		$this->state = $imgdata['state'];																			// 状態
		$this->image1 = $imgdata['image1'];																			// バイナリを変換したイメージ（アップロード）
		$this->image2 = $imgdata['image2'];																			// バイナリを変換したイメージ（サムネイル1）
		$this->image3 = $imgdata['image3'];																			// バイナリを変換したイメージ（サムネイル2）
		$this->image4 = $imgdata['image4'];																			// バイナリを変換したイメージ（サムネイル3）
		$this->image5 = $imgdata['image5'];																			// バイナリを変換したイメージ（サムネイル4）
	}
}

class PhotoImageDB extends PhotoImageData
{
	function __construct()
	{
		PhotoImageData::__construct();
	}

	function check_adjust_param($act)
	{
		// パラメータの調整をします。
		if ($this->kikan == "mukigen")
		{
			$this->dfrom = "2000/01/01";
			$this->dto = "2100/01/01";
		}

		// 掲載期間（From）
		if (empty($this->dfrom))
		{
			$this->dfrom = "2000/01/01";
		}

		// 掲載期間（To）
		if (empty($this->dto))
		{
			$this->dto = "2100/01/01";
		}

		// 表示可否
		if (empty($this->viewable))
		{
			$this->viewable = true;
		}

		// 独占使用
		if (!is_numeric($this->monopoly_use) || empty($this->monopoly_use))
		{
			// 独占使用しないに設定します。
			$this->monopoly_use = 0;
		}

		// 撮影時期(1)
		if (!is_numeric($this->take_picture_time_id) || empty($this->take_picture_time_id))
		{
			$this->take_picture_time_id = -1;
		}

		// 撮影時期(2)
		if (!is_numeric($this->take_picture_time2_id) || empty($this->take_picture_time2_id))
		{
			$this->take_picture_time2_id = -1;
		}

		// 写真入手元
		if (empty($this->content_borrowing_ahead))
		{
			$this->content_borrowing_ahead = "";
		}

		// 出稿条件
		if (empty($this->use_condition))
		{
			$this->use_condition = "";
		}

		// エラーチェックをします。

		// 登録区分ID
		if (!is_numeric($this->registration_division_id))
		{
			$this->message = "登録区分ID(registration_division_id)が数値ではありません。";
			throw new Exception($this->message);
		}

		// 掲載期間
		if (empty($this->kikan))
		{
			$this->message = "掲載期間(kikan)が設定されていません。";
			throw new Exception($this->message);
		}

		// 写真入手元
		if (!is_numeric($this->borrowing_ahead_id))
		{
			$this->message = "写真入手元ID(borrowing_ahead_id)が数値ではありません。";
			throw new Exception($this->message);
		}

		// 使用範囲
		if (!is_numeric($this->range_of_use_id))
		{
			$this->message = "使用範囲ID(range_of_use_id)が数値ではありません。";
			throw new Exception($this->message);
		}

		// Insert用のチェックの場合です。
		if ($act == "I")
		{
			// ファイル名をチェックします。
			if (empty($this->up_url))
			{
				$this->message = "画像ファイル(up_url)が設定されていません。";
				throw new Exception($this->message);
			}

			// 登録日
			if (empty($this->register_date))
			{
				$this->register_date = date("Y/m/d H:i:s");
			}

			// 登録許可
			$this->permission_account = "";
			$this->permission_person = "";
			$this->permission_date = "0000-00-00 00:00:00";
		}
	}

	// 方面名をDBより取得します。
	function get_direction_name($db_link, $db_id, &$db_name)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($db_id) || $db_id == "-1")
		{
			return;
		}

		// 方面情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT direction_id, direction_name FROM direction WHERE direction_id = ? ";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $db_id);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount >= 0)
			{
				$direction = $stmt->fetch(PDO::FETCH_ASSOC);
				//  方面名を保存します。
				$db_name = $direction['direction_name'];
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "方面を取得できませんでした。（取得数<0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "方面を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	// 国・都道府県名をDBより取得します。
	function get_country_prefecture_name($db_link, $db_id, &$db_name)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($db_id) || $db_id == "-1")
		{
			return;
		}

		// 国・都道府県情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT country_prefecture_id, country_prefecture_name FROM country_prefecture WHERE country_prefecture_id = ? ";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $db_id);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount >= 0)
			{
				$country_prefecture = $stmt->fetch(PDO::FETCH_ASSOC);
				//  国・都道府県名を保存します。
				$db_name = $country_prefecture['country_prefecture_name'];
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "国・都道府県を取得できませんでした。（取得数<0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "国・都道府県を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	// 地名をDBより取得します。
	function get_place_name($db_link, $db_id, &$db_name)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($db_id) || $db_id == "-1")
		{
			return;
		}

		// 地名をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT place_id, place_name FROM place WHERE place_id = ? ";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $db_id);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount >= 0)
			{
				$place = $stmt->fetch(PDO::FETCH_ASSOC);
				//  地名を保存します。
				$db_name = $place['place_name'];
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "地名を取得できませんでした。（取得数<0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "地名を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	// 撮影時期の漢字「春、夏、秋、冬」をDBより取得します。
	function get_take_picture_time2_name($db_link, $db_id, &$db_name)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($db_id) || $db_id == "-1")
		{
			return "";
		}

		// 撮影時期2をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM take_picture_time2 WHERE take_picture_time2_id = ? ";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $db_id);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount >= 0)
			{
				$take_picture_time = $stmt->fetch(PDO::FETCH_ASSOC);
				//  撮影時期2を保存します。
				$db_name = $take_picture_time['take_picture_time2_name'];
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "撮影時期2を取得できませんでした。（取得数<0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "撮影時期2を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	// 撮影時期1「1月～12月」をDBより取得します。
	function get_take_picture_time_name($db_link, $db_id, &$db_name)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($db_id) || $db_id == "-1")
		{
			return "";
		}

		// 撮影時期1をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM take_picture_time WHERE take_picture_time_id = ? ";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $db_id);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount >= 0)
			{
				$take_picture_time = $stmt->fetch(PDO::FETCH_ASSOC);
				//  撮影時期1を保存します。
				$db_name = $take_picture_time['take_picture_time_name'];
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "撮影時期1を取得できませんでした。（取得数<0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "撮影時期1を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	/*
	 * 関数名：insert_keyword
	 * 関数説明：キーワードをテーブルに登録する
	 * パラメタ：
	 * db_link:データベースのリンク
	 * pid:画像ID
	 * kwd_str:設定のキーワード
	 * 戻り値：無し
	 */
	function insert_keyword($db_link, $pid, $kwd_str)
	{
		// エラーチェックを行います。
		if (empty($pid))
		{
			return ;
		}

		$insert_keyword_str = "";

		// 写真データを追加します。<写真管理番号>
		if (!empty($this->photo_mno) && strlen($this->photo_mno) > 0)
		{
			$insert_keyword_str = " ".$this->photo_mno;
		}

		// 写真データを追加します。<写真名>
		if (!empty($this->photo_name) && strlen($this->photo_name) > 0)
		{
			$insert_keyword_str .= " ".$this->photo_name;
		}

		// 写真データを追加します。<方面、国・都道府県、地名>
		$ed = $this->registration_classifications->count;
		for ($i = 1; $i <= $ed; $i++)
		{
			// 方面名、国・都道府県名、地名IDを取得する
			$c_id = "";
			$d_id = "";
			$cp_id = "";
			$p_id = "";
			$this->registration_classifications->get_id($c_id, $d_id, $cp_id, $p_id, $i);

			// 方面名を取得する
			$d_name = "";
			if (!empty($d_id)) $this->get_direction_name($db_link,$d_id,$d_name);

			// 国・都道府県名を取得する
			$cp_name = "";
			if (!empty($d_id)) $this->get_country_prefecture_name($db_link,$cp_id,$cp_name);

			// 地名を取得する
			$p_name = "";
			if (!empty($d_id)) $this->get_place_name($db_link,$p_id,$p_name);

			// 方面名をキーワードに新規する
			if (!empty($d_name) && strlen($d_name) > 0)
			{
				$insert_keyword_str .= " ".$d_name;
			}

			// 国・都道府県名をキーワードに新規する
			if (!empty($cp_name) && strlen($cp_name) > 0)
			{
				$insert_keyword_str .= " ".$cp_name;
				if($cp_name != "北海道")
				{
					if($cp_name == "京都" || $cp_name == "大阪")
					{
						$insert_keyword_str .= " ".$cp_name."府";
					} elseif($cp_name == "東京") {
						$insert_keyword_str .= " ".$cp_name."都";
					} else {
						$insert_keyword_str .= " ".$cp_name."県";
					}
				}
			}

			if (!empty($p_name) && strlen($p_name) > 0)
			{
				$insert_keyword_str .= " ".$p_name;
			}
		}

		// 写真データを追加します。<内容（写真説明）>
		if (!empty($this->photo_explanation) && strlen($this->photo_explanation) > 0)
		{
			$insert_keyword_str .= " ".$this->photo_explanation;
		}

		// 写真データを追加します。<撮影時期　2>
		$t_p_time2_name = "";
		$this->get_take_picture_time2_name($db_link,$this->take_picture_time2_id,$t_p_time2_name);
		if (!empty($t_p_time2_name) && strlen($t_p_time2_name) > 0)
		{
			$insert_keyword_str .= " ".$t_p_time2_name;
		}

		// 写真データを追加します。<撮影時期　1>
		$t_p_time_name = "";
		$this->get_take_picture_time_name($db_link,$this->take_picture_time_id,$t_p_time_name);
		if (!empty($t_p_time_name) && strlen($t_p_time_name) > 0)
		{
			$insert_keyword_str .= " ".$t_p_time_name;
		}

		// 写真データを追加します。<使用範囲>
		if ((int)$this->range_of_use_id == 1)
		{
			$insert_keyword_str .= " 使用不可";
		} elseif ((int)$this->range_of_use_id == 2) {
			$insert_keyword_str .= " 使用可";
		} elseif ((int)$this->range_of_use_id == 3) {
			$insert_keyword_str .= " 条件有";
		}

		// 写真データを追加します。<付加条件：クレジット>
		if (!empty($this->additional_constraints1) && strlen($this->additional_constraints1) > 0)
		{
			$insert_keyword_str .= " 要クレ".$this->additional_constraints1;
		}

		if (!empty($kwd_str))
		{
			$insert_keyword_str .= " ".$kwd_str;
		}

		// 設定されているキーワードをすべてDBに登録します。
		if (!empty($insert_keyword_str) && strlen($insert_keyword_str) > 0)
		{
			$keyword_str = $insert_keyword_str." ";

			// 写真データを追加します。
			$sql = "INSERT INTO keyword (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $keyword_str . "')";

			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();

			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（条件設定エラー）";
				throw new Exception($this->message);
			}
		}
	}

	/*
	 * 関数名：getmaxno
	 * 関数説明：画像番号の最終番号
	 * パラメタ：db_link：ＤＢリンク;p_photo_mno:画像管理番号
	 * 戻り値：最終画像番号
	 */
	function getmaxno($db_link,$p_photo_mno)
	{
		// 画像番号の最終番号を取得します。
		$sql = "SELECT max(lastnumber)+1 as max FROM lastnumber WHERE lastnumber_name=\"".$p_photo_mno."\"";
		$stmt = $db_link->prepare($sql);

		$result = $stmt->execute();
		if ($result == true)
		{
			// 最終番号を取得します。
			$max = $stmt->fetch(PDO::FETCH_ASSOC);
			$maxno = $max['max'];
		}
		else
		{
			// エラーの場合は例外をスローします。
			$this->message = "最終番号のMAX値を取得できませんでした。";
			throw new Exception($this->message);
		}

		// 最終番号を補正します。
		if (empty($maxno))
		{
			$maxno = 0;
		}
		return $maxno;
	}

	/*
	 * 関数名：setmaxno
	 * 関数説明：画像番号の最終番号
	 * パラメタ：db_link：ＤＢリンク;p_photo_mno:画像管理番号;p_maxno:画像番号の最終番号
	 * 戻り値：最終画像番号
	 */
	function setmaxno($db_link,$p_photo_mno,$p_maxno,$delflg=0)
	{
		// 最終番号を更新します。

		if ($delflg == 1)
		{
			if ($p_maxno >= 0)
			{
				$sql = "UPDATE lastnumber SET lastnumber=? WHERE lastnumber_name=?";
				$stmt = $db_link->prepare($sql);
				$stmt->bindParam(1, $p_maxno);
				$stmt->bindParam(2, $p_photo_mno);
			} else {
				$sql = "DELETE FROM lastnumber WHERE lastnumber_name=?";
				$stmt = $db_link->prepare($sql);
				$stmt->bindParam(1, $p_photo_mno);
			}
		} else {
			if ($p_maxno > 0)
			{
				$sql = "UPDATE lastnumber SET lastnumber=? WHERE lastnumber_name=?";
				$stmt = $db_link->prepare($sql);
				$stmt->bindParam(1, $p_maxno);
				$stmt->bindParam(2, $p_photo_mno);
			} else {
				$sql = "INSERT INTO lastnumber (lastnumber_name, lastnumber) value (?, 0)";
				$stmt = $db_link->prepare($sql);
				$stmt->bindParam(1, $p_photo_mno);
			}
		}

		$result = $stmt->execute();
		if ($result == false)
		{
			$this->message = "最終番号のMAX値を更新できませんでした。";
			throw new Exception($this->message);
		}
	}

	function insert_data($db_link)
	{
		//global $comp_code;

		// パラメータのチェックと調整をします。
		$this->check_adjust_param("I");

		// トランザクションを開始します。（オートコミットがオフになります。）
		$db_link->beginTransaction();

		try
		{
			// 写真データを追加します。
			$sql = "INSERT INTO photoimg (  publishing_situation_id,
											registration_division_id,
											take_picture_time_id,
											take_picture_time2_id,
											borrowing_ahead_id,
											range_of_use_id,
											image_size_x,
											image_size_y,
											photo_mno,
											source_image_no,
											bud_photo_no,
											photo_name,
											photo_explanation,
											dfrom,
											dto,
											kikan,
											photo_filename,
											photo_filename_th1,
											photo_filename_th2,
											photo_filename_th3,
											photo_filename_th4,
											photo_filename_th5,
											photo_filename_th6,
											photo_filename_th7,
											photo_filename_th8,
											photo_filename_th9,
											photo_filename_th10,
											ext,
											note,
											copyright_owner,
											content_borrowing_ahead,
											use_condition,
											additional_constraints1,
											additional_constraints2,
											customer_section,
											customer_name,
											registration_account,
											registration_person,
											register_date,
											monopoly_use
								) VALUES (
											$this->publishing_situation_id,
											$this->registration_division_id,
											$this->take_picture_time_id,
											$this->take_picture_time2_id,
											$this->borrowing_ahead_id,
											$this->range_of_use_id,
											$this->image_size_x,
											$this->image_size_y,
											?,?,?,?,?,
											?,?,?,?,?,
											?,?,?,?,?,
											?,?,?,?,?,
											?,?,?,?,?,
											?,?,?,?,?,
											?,?
											)";
			$stmt = $db_link->prepare($sql);
			if (strlen($this->photo_mno) <= 5) $this->photo_mno = "申請中";

			$stmt->bindParam(1, $this->photo_mno);
			$stmt->bindParam(2, $this->source_image_no);
			$stmt->bindParam(3, $this->bud_photo_no);
			$stmt->bindParam(4, $this->photo_name);
			$stmt->bindParam(5, $this->photo_explanation);
			$stmt->bindParam(6, $this->dfrom);
			$stmt->bindParam(7, $this->dto);
			$stmt->bindParam(8, $this->kikan);

			if (!empty($this->up_url[0]))
			{
				$stmt->bindParam(9, $this->up_url[0]);
			}
			else
			{
				$stmt->bindValue(9, null);
			}

			if (!empty($this->up_url[1]))
			{
				$stmt->bindParam(10, $this->up_url[1]);
			}
			else
			{
				$stmt->bindValue(10, null);
			}

			if (!empty($this->up_url[2]))
			{
				$stmt->bindParam(11, $this->up_url[2]);
			}
			else
			{
				$stmt->bindValue(11, null);
			}

			if (!empty($this->up_url[3]))
			{
				$stmt->bindParam(12, $this->up_url[3]);
			}
			else
			{
				$stmt->bindValue(12, null);
			}

			if (!empty($this->up_url[4]))
			{
				$stmt->bindParam(13, $this->up_url[4]);
			}
			else
			{
				$stmt->bindValue(13, null);
			}

			if (!empty($this->up_url[5]))
			{
				$stmt->bindParam(14, $this->up_url[5]);
			}
			else
			{
				$stmt->bindValue(14, null);
			}

			if (!empty($this->up_url[6]))
			{
				$stmt->bindParam(15, $this->up_url[6]);
			}
			else
			{
				$stmt->bindValue(15, null);
			}

			if (!empty($this->up_url[7]))
			{
				$stmt->bindParam(16, $this->up_url[7]);
			}
			else
			{
				$stmt->bindValue(16, null);
			}

			if (!empty($this->up_url[8]))
			{
				$stmt->bindParam(17, $this->up_url[8]);
			}
			else
			{
				$stmt->bindValue(17, null);
			}

			if (!empty($this->up_url[9]))
			{
				$stmt->bindParam(18, $this->up_url[9]);
			}
			else
			{
				$stmt->bindValue(18, null);
			}

			if (!empty($this->up_url[10]))
			{
				$stmt->bindParam(19, $this->up_url[10]);
			}
			else
			{
				$stmt->bindValue(19, null);
			}

			$stmt->bindParam(20, $this->ext);

			$stmt->bindParam(21, $this->note);
			$stmt->bindParam(22, $this->copyright_owner);
			$stmt->bindParam(23, $this->content_borrowing_ahead);
			$stmt->bindParam(24, $this->use_condition);
			$stmt->bindParam(25, $this->additional_constraints1);
			$stmt->bindParam(26, $this->additional_constraints2);
			$stmt->bindParam(27, $this->customer_section);
			$stmt->bindParam(28, $this->customer_name);
			$stmt->bindParam(29, $this->registration_account);
			$stmt->bindParam(30, $this->registration_person);
			$stmt->bindParam(31, $this->register_date);
			$stmt->bindParam(32, $this->monopoly_use);

			$result = $stmt->execute();
			if ($result == true)
			{
				// 処理数を取得します。
				$icount = $stmt->rowCount();

				// 追加されたデータ数が１かどうかチェックします。
				if ($icount == 1)
				{
					// 挿入した画像データのphoto_idを取得します。(今登録した、photo_idを取得します。)
					$pid = $db_link->lastInsertId();

					// キーワードを別テーブルに登録します。
					$this->insert_keyword($db_link, $pid, $this->keyword_str);

					// 分類を別テーブルに登録します。
					// ※すでにphoto_id以外はすべてデータセット済みです
					$this->registration_classifications->insert_data($db_link, $pid);

					// コミットします。
					$db_link->commit();

					return true;
				}
				else
				{
					$err = $stmt->errorInfo();
					$this->message = "画像をDBに登録できませんでした。（処理数!=1）";
					throw new Exception($this->message);
					//throw new Exception($err[2]);
				}
			}
			else
			{
				$err = $stmt->errorInfo();
				$this->message = "画像をDBに登録できませんでした。";
				throw new Exception($this->message);
				//throw new Exception($err[2]);
			}
		}
		catch(Exception $e)
		{
			// ロールバックします。
			$db_link->rollBack();

			// 例外をスローします。
			$msg = $e->getMessage();
			throw new Exception($msg);
		}
	}
}

/**
 * ファイルをアップロードします。
 *
 * 	【コンストラクター】
 * 		FileUpload($fl, $cf, $thdir, $flw, $fname, $cre, $wcredit)
 * 			@param array $cf 		定義情報				（指定なし：config.phpの$upload_conf）
 * 										$cf['dir'] 			：アップロードフォルダ
 * 										$cf['temp_dir']		：テンポラリーフォルダ
 * 										$cf['maxsize']		：アップロードファイルの上限サイズ
 * 										$cf['site_url']		：サイトURL
 * 			@param array $thdir		サムネイルフォルダ情報	（指定なし：config.phpの$thumb_dir）
 * 			@param array $flw		サムネイル横幅			（指定なし：config.phpの$thumb_width）
 * 			@param string $fname	フォント名				（指定なし：config.phpの$font_name）
 * 			@param string $cre		クレジット
 * 			@param array $wcredit	クレジットを書き込みかどうか	（指定なし：config.phpの$write_credit）
 *
 * 	【メンバー】
 * 		var $message;				メッセージ
 * 		var $result;				アップロード結果
 * 		var $img_width;				イメージサイズ（横）	0:元、1:サムネイル1、2:サムネイル2・・・
 * 		var $img_height;			イメージサイズ（縦）	0:元、1:サムネイル1、2:サムネイル2・・・
 * 		var $up_url;				アップロードURL（最終的にアップロードされたURL）
 * 													0:元、1:サムネイル1、2:サムネイル2・・・
 *		var $ext;					拡張子（元ファイル名）
 *
 * 	$file（アップロードファイル全情報）	→	$upfile（元のファイル名）+$ext（拡張子）
 * 										↓ユニークなファイル名へ
 * 									$svname（ユニークなファイル名：YmdHis999）+$ext（拡張子）
 * 										↓テンポラリーへ
 * 									$uploadconf['temp_dir'] + $svname + $ext
 * 										↓チェックOK
 * 									$uploadconf['dir'] + dirno（0～9までのフォルダ） + $svname + $ext　（$svfullpath[0]）
 * 										↓URLに変換
 * 									$uploadconf['site_url'] + $svfullpath[0]　（$up_url）
 */
class FileUploadBatch
{
	var $message;									// メッセージ
	var $result;									// アップロード結果
	var $img_width;									// イメージサイズ（横）	0:元、1:サムネイル1、2:サムネイル2・・・
	var $img_height;								// イメージサイズ（縦）	0:元、1:サムネイル1、2:サムネイル2・・・
	var $up_url;									// アップロードURL（最終的にアップロードされたURL）
													//					0:元、1:サムネイル1、2:サムネイル2・・・
	var $ext;										// 拡張子（元ファイル名）

	private $filename;								// ファイル情報
	private $dirno;									// uploadおよびthumbフォルダ以下0-9のどのフォルダに入れるか
	private $svname;								// 保存ファイル名作成元（YmdHis999）
	private $svfullpath;							// 保存ファイル名（フルパス:./$uploadconf['dir']/YmdHis999/dirno/svname.ext）
	private $upfile;								// アップロードファイル名
	private $uploadsize;							// アップロードサイズ
	private $uploadconf;							// アップロード用定義
													//	$uploadconf['dir'] = "./uploads/";			アップロードフォルダ
													//	$uploadconf['temp_dir'] = "./temporary/";	テンポラリーフォルダ
													//	$uploadconf['maxsize'] = 1000000;			 アップロードファイルの制限サイズ
													//	$uploadconf['site_url'] = 'http:			サイトURL
	private $flwidth;								// サムネイルを作成するときの横幅
	private $thumbdir;								// サムネイルを保存するフォルダ
	private $font_name;								// フォント名
	private $credit;								// クレジット
	private $write_ok;								// フォントを書き込むかどうか

	/**
	 * コンストラクター
	 */
	function __construct($fln,$cf, $thdir, $flw, $fname, $cre, $wcredit)
	{
		// config.phpからデフォルト値を読み込むためのglobalです。
		global $upload_conf, $thumb_dir, $thumb_width, $font_name, $write_credit;
		global $credit_fontsize;

		// メンバーを初期化します。
		$this->message = "";						// メッセージ
		$this->result = true;						// 結果＝成功（true）
		$this->file = "";							// ファイル情報
		$this->svname = "";							// 保存ファイル名作成元（YmdHis999）
		$this->uploadsize = 0;						// アップロードサイズ
		$this->ext = "";							// 拡張子
		$this->font_name = "";						// フォント名
		$this->dirno = 0;							// uploadおよびthumbフォルダ以下のディレクトリ名
		$this->credit = "";							// クレジット

		$this->img_width = array();					// イメージサイズ（横）			0:元、1:サムネイル1、2:サムネイル2・・・
		$this->img_height = array();				// イメージサイズ（横）			0:元、1:サムネイル1、2:サムネイル2・・・
		$this->write_ok = array();					// クレジットを書き込むかどうか		0:元、1:サムネイル1、2:サムネイル2・・・
		$this->flwidth = array();					// サムネイルを作成するときの横幅	0:元、1:サムネイル1、2:サムネイル2・・・
		$this->thumbdir = array();					// サムネイルを保存するフォルダ		0:元、1:サムネイル1、2:サムネイル2・・・
		$this->up_url = array();					// アップロードURL				0:元、1:サムネイル1、2:サムネイル2・・・
		$this->svfullpath = array();				// 保存ファイル名（フルパス）		0:元、1:サムネイル1、2:サムネイル2・・・

		// ファイル情報を設定します。
		$this->filename = $fln;

		// 定義情報を設定します。
		if (empty($cf))
		{
			// インスタンス生成時に定義情報が設定されていない場合で、
			if (!empty($upload_conf))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->uploadconf = $upload_conf;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "アップロード用定義が設定されていません。";
				throw new Exception($this->message);
			}
		}
		else
		{
			// インスタンス生成時に定義情報が設定されている場合は、その値を使用します。
			$this->uploadconf = $cf;
		}

		// 定義ファイルの内容をチェックします。
		if (empty($this->uploadconf['dir']))
		{
			$this->result = false;
			$this->message = "保存用ディレクトリが設定されていません。";
			throw new Exception($this->message);
		}

		if (empty($this->uploadconf['temp_dir']))
		{
			$this->result = false;
			$this->message = "テンポラリーディレクトリが設定されていません。";
			throw new Exception($this->message);
		}

		if (empty($this->uploadconf['maxsize']))
		{
			$this->result = false;
			$this->message = "アップロード最大サイズが設定されていません。";
			throw new Exception($this->message);
		}

		if (empty($this->uploadconf['site_url']))
		{
			$this->result = false;
			$this->message = "URLが設定されていません。";
			throw new Exception($this->message);
		}

		// サムネイル保存用フォルダを設定します。
		if (empty($thdir))
		{
			// インスタンス生成時にサムネイル保存用フォルダが設定されていない場合で、
			if (!empty($thumb_dir))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->thumbdir = $thumb_dir;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "サムネイル保存用フォルダが設定されていません。";
				throw new Exception($this->message);
			}
		}
		else
		{
			// インスタンス生成時にサムネイル保存用フォルダが設定されている場合は、その値を使用します。
			$this->thumbdir = $thdir;
		}

		// サムネイル作成時の横幅を設定します。
		if (empty($flw))
		{
			// インスタンス生成時にサムネイル作成時の横幅が設定されていない場合で、
			if (!empty($thumb_width))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->flwidth = $thumb_width;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "サムネイル作成時の横幅が設定されていません。";
				throw new Exception($this->message);
			}
		}
		else
		{
			// インスタンス生成時にサムネイル作成時の横幅が設定されている場合は、その値を使用します。
			$this->flwidth = $flw;
		}

		// クレジット書込用のフォントを設定します。
		if (empty($fname))
		{
			// インスタンス生成時にクレジット書込用のフォントが設定されていない場合で、
			if (!empty($font_name))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->font_name = $font_name;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "フォントが設定されていません。";
				throw new Exception($this->message);
			}
		}
		else
		{
			// インスタンス生成時にフォントが設定されている場合は、その値を使用します。
			$this->font_name = $fname;
		}

		// クレジットを書き込むかどうかの設定します。
		if (empty($wcredit))
		{
			// インスタンス生成時にクレジットを書き込むかどうかが設定されていない場合で、
			if (!empty($write_credit))
			{
				// config.phpにデフォルト値が設定されていれば、
				// その値を使用します。
				$this->write_ok = $write_credit;
			}
			else
			{
				// config.phpにデフォルト値が設定されていなければ、
				// エラーとします。
				$this->result = false;
				$this->message = "クレジットを書き込むかどうかが設定されていません。";
				throw new Exception($this->message);
			}
		}
		else
		{
			// インスタンス生成時にフォントが設定されている場合は、その値を使用します。
			$this->write_ok  = $wcredit;
		}

		// クレジットを設定します。
		$this->credit = $cre;										// クレジット

		// 指定されたアップロードファイル名を取得します。
		$this->upfile = $fln;										// アップロードファイル名

		// アップロードするファイルサイズを取得します。
		//$this->uploadsize = filesize($this->uploadconf['temp_dir'].$this->upfile);							// アップロードサイズ
		//echo "upfilesize->".$this->uploadsize;
		// アップロードするファイル名から拡張子を抜出します。
		preg_match("/\.[^.]*$/i", $this->upfile, $ext_tmp);			// extに拡張子

		// 拡張子を小文字に変換します。
		$this->ext = strtolower($ext_tmp[0]);
	}

	function write_credit($img, $cre_str, $fsize, $width_i, $height_i)
	{
		// クレジット書き込み用の設定を行います。
		// 書き込み角度を設定します。
		$font_angle = 0;

		//GD環境情報を取得します。
		$arrInfo = gd_info();

		// 書き込むクレジットを設定します。
		$telop_text = "";
		if ($arrInfo['JIS-mapped Japanese Font Support']) {
			// GDが対応している場合はUTF-8への変換は不要です。
			$telop_text = $cre_str;
		}
		else
		{
			// 組込みテキスト
			// GDが対応していない場合はUTF-8へ変換します。（UTF-8に変換しない場合、文字化けします。）
			$telop_text =  mb_convert_encoding($cre_str, "UTF-8", "auto");
		}

		// 半透明のグレーバック表示位置
		$alpha_x1 = 5;
		$alpha_x2 = $width_i - 5;

		$alpha_y1 = $height_i - ($fsize + 10) - 5;
		$alpha_y2 = $height_i - 5;

		// クレジット書き込み位置
		$tx = $alpha_x1 + 5;
		$ty = $alpha_y1 + $fsize + 5;

		// テキストカラー（黒）
		$font_color_b = ImageColorAllocate ($img, 0, 0, 0);
		// テキストカラー（白）
		$font_color_w = ImageColorAllocate ($img, 255, 255, 255);
		// アルファチャンネル（グレー）
		$alpha = imagecolorallocatealpha($img, 0, 0, 0, 90);

		// 画像の一部を透かしイメージにします。
		imagefilledrectangle ($img , $alpha_x1 , $alpha_y1, $alpha_x2, $alpha_y2, $alpha);

		//テキスト描画
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $telop_text);
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $telop_text);

		return $img;
	}

	/**
	 * ファイルをアップロードします。
	 */
	function upload($imagedir)
	{
		// チェックでエラーが発生している場合は、例外をスローします。
		if ($this->result == false)
		{
			throw new Exception($this->message);
		}

		// 保存用ファイル名をYmdHis999.xxx（同じ拡張子）で生成します。
		$reg_time = time();												// 登録日時
		$rnd = rand(1, 10000);											// 乱数1-10000
		$this->dirno = rand(0, 9);										// ディレクトリ名（0-9）をランダムで決定します。
		$this->dirno .= "/";
		$this->svname = date("YmdHis", $reg_time) . $rnd;				// 保存ファイル名（元）

		// 一旦、テンポラリーにアップしたファイルを保存します。
		$tmppath = $this->uploadconf['temp_dir'].$this->svname.$this->ext;
		//$cmdstr = "cp ./temporary/".$this->filename." ".$tmppath;
		$cmdstr = "cp ".$imagedir.$this->filename." ".$tmppath;

		echo exec( $cmdstr );

		// 保存したファイルのタイプを取得します。
		$type = exif_imagetype($this->uploadconf['temp_dir'].$this->svname . $this->ext);

		if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG)
		{
			// ファイルタイプがGIF、JPEG、PNGだった場合はテンポラリー→アップロードディレクトリにファイルを移動します。
			$this->svfullpath = array();
			$this->svfullpath[] = $this->uploadconf['dir']. $this->dirno . $this->svname . $this->ext;

			$cmdstr = "mv ".$tmppath." ".$this->svfullpath[0];
			echo exec( $cmdstr );

			// 画像のサイズを取得します。
			$this->img_width = array();
			$this->img_height = array();
			$size = @getimagesize($this->svfullpath[0]);
			list($width, $height, $type, $attr) = $size;
			$this->img_width[] = $width;
			$this->img_height[] = $height;

			// アップロードされたファイル名を設定します。
			$this->up_url = array();
			$this->up_url[] = $this->uploadconf['site_url'] . $this->svfullpath[0];
		}
		else
		{
			// ファイルタイプがそれ以外の場合はそのファイルを削除します。
			unlink($tmppath);
			$this->result = false;
			$this->message = "アップロードしたファイルタイプがjpg,gif,png以外です。";
			throw new Exception($this->message);
		}
	}

	/**
	 * クレジット書き込み用のフォントサイズを決定します。
	 */
	function decide_fontsize($thwidth)
	{
		global $credit_fontsize;

		// クレジット書込用フォントサイズが設定されているかチェックします。
		if (count($credit_fontsize)<6)
		{
			$this->result = false;
			$this->message = "クレジット書込用フォントサイズが指定されていません。";
			throw new Exception($this->message);
		}
		// クレジット書込用フォントサイズを決定します。
		if ($thwidth <= 160)
		{
			$font_size = $credit_fontsize[0];
		}
		else if ($thwidth <= 320)
		{
			$font_size = $credit_fontsize[1];
		}
		else if ($thwidth <= 480)
		{
			$font_size = $credit_fontsize[2];
		}
		else if ($thwidth <= 640)
		{
			$font_size = $credit_fontsize[3];
		}
		else if ($thwidth <= 800)
		{
			$font_size = $credit_fontsize[4];
		}
		else
		{
			$font_size = $credit_fontsize[5];
		}

		return $font_size;
	}

	/**
	 * サムネイルを作成します。
	 *  元ファイルと縦・横同じ比率で作成します。
	 *    ※ bmpはGD関数無いため作成できません。
	 */
	function make_thumbfile()
	{
		// チェックでエラーが発生している場合は、例外をスローします。
		if ($this->result == false)
		{
			throw new Exception($this->message);
		}

		// サムネイルを作成するときの元ファイルを決定します。
		$srcfilename = "";
		if (!empty($this->svfullpath[0]))
		{
			$srcfilename = $this->svfullpath[0];
		}
		else
		{
			$this->result = false;
			$this->message = "サムネイルを作成する元ファイルが指定されていません。";
			throw new Exception($this->message);
		}

		// サムネイルを作成するときの横幅が設定されているかチェックします。
		if (empty($this->flwidth))
		{
			$this->result = false;
			$this->message = "サムネイルを作成するときの横幅が指定されていません。";
			throw new Exception($this->message);
		}

		// サムネイルを作成するフォルダが設定されているかチェックします。
		if (empty($this->thumbdir))
		{
			$this->result = false;
			$this->message = "サムネイルを作成するフォルダが指定されていません。";
			throw new Exception($this->message);
		}

		// クレジット書込用フォント名が設定されているかチェックします。
		if (empty($this->font_name))
		{
			$this->result = false;
			$this->message = "クレジット書込用フォント名が指定されていません。";
			throw new Exception($this->message);
		}

		// クレジットを書き込むかどうかが設定されているかチェックします。
		if (empty($this->write_ok))
		{
			$this->result = false;
			$this->message = "クレジットを書き込むかどうかが指定されていません。";
			throw new Exception($this->message);
		}

		// 画像のサイズを取得します。
		$size = @getimagesize($srcfilename);
		list($width, $height, $type, $attr) = $size;

		// 設定されているサムネイルのサイズとフォルダの数を比較します。
		$szmax = count($this->flwidth);
		$dirmax = count($this->thumbdir);
		if ($dirmax < $szmax)
		{
			$this->result = false;
			$this->message = "サムネイルを保存するフォルダの数が足りません。";
			throw new Exception($this->message);
		}

		// サムネイルを作成するときの横幅が設定されている分だけ、サムネイルを作成します。
		for ($i = 0 ; $i < $szmax ; $i++)
		{
			if((int)$i == (int)($szmax - 1) || (int)$i == (int)($szmax - 2))
			{
				//thumb4
				if((int)$i == (int)($szmax - 1))
				{
					$photo_filename_th1 = $this->up_url[1];
					$tmp = substr($photo_filename_th1,strpos($photo_filename_th1,"./"));
					$tmp1 = str_replace("th1","th4",$tmp);
					$tmp2 = str_replace("thumb1","thumb4",$tmp1);
				} elseif((int)$i == (int)($szmax - 2)) {//thumb3
					$photo_filename_th2 = $this->up_url[2];
					$tmp = substr($photo_filename_th2,strpos($photo_filename_th2,"./"));
					$tmp1 = str_replace("th2","th3",$tmp);
					$tmp2 = str_replace("thumb2","thumb3",$tmp1);
				}

				$size = @getimagesize($tmp);
				list($width, $height, $type, $attr) = $size;

				// 縦・横の比率を合わせて、サムネイル用の縦、横を計算します。
				$thumb_width = $this->flwidth[$i];
				if((int)$width < $thumb_width)
				{
					$thumb_width = $width;
				}
				$thumb_height = ($thumb_width / $width) * $height;
				// 画像サイズをセットします。
				$this->img_width[] = $thumb_width;
				$this->img_height[] = $thumb_height;

				// フォントサイズを決定します。
				if((int)$i == (int)($szmax - 1))
				{
					if($width == 400)
					{
						$font_size = 88;
					} elseif($width == 800) {
						$font_size = 168;
					} elseif($width == 200) {
						$font_size = 38;
					}
				} elseif((int)$i == (int)($szmax - 2)) {//thumb3
					$font_size = 38;
				}
				// 画像のタイプに合わせて、サムネイルを作成します。
				if ($type == IMAGETYPE_JPEG)
				{
					// アップロードしたファイルを読み込みます。
					$ufimage = @ImageCreateFromJPEG($tmp);
					// 空のサムネイル画像を作成します。
					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
					// 空のサムネイル画像にアップロードしたファイルをコピーします。
					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
					// 画像にクレジットを書き込みます。
					$thumb = $this->write_credit2($thumb, "SAMPLE", $font_size, $thumb_width, $thumb_height);
					@imagejpeg($thumb, $tmp2);
				}
				else if ($type == IMAGETYPE_GIF)
				{
					// アップロードしたファイルを読み込みます。
					$ufimage = @ImageCreateFromGIF($tmp);
					// 空のサムネイル画像を作成します。
					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
					// 空のサムネイル画像にアップロードしたファイルをコピーします。
					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
					// 画像にクレジットを書き込みます。
					$thumb = $this->write_credit2($thumb, "SAMPLE", $font_size, $thumb_width, $thumb_height);
					@imagegif($thumb, $tmp2);
				}
				else if ($type == IMAGETYPE_PNG)
				{
					// アップロードしたファイルを読み込みます。
					$ufimage = @ImageCreateFromPNG($tmp);
					// 空のサムネイル画像を作成します。
					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
					// 空のサムネイル画像にアップロードしたファイルをコピーします。
					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
					// 画像にクレジットを書き込みます。
					$thumb = $this->write_credit2($thumb, "SAMPLE", $font_size, $thumb_width, $thumb_height);
					@imagepng($thumb, $tmp2);
				}

				// アップロードされたファイル名を設定します。
				$this->svfullpath[] = $tmp2;
				$this->up_url[] = $this->uploadconf['site_url'] . $tmp2;

				// 画像を破棄します。
				@imagedestroy($ufimage);
				@imagedestroy($thumb);
			} else {
				// 縦・横の比率を合わせて、サムネイル用の縦、横を計算します。
				$thumb_width = $this->flwidth[$i];
				if((int)$width < $thumb_width)
				{
					$thumb_width = $width;
				}
				$thumb_height = ($thumb_width / $width) * $height;
				// 画像サイズをセットします。
				$this->img_width[] = $thumb_width;
				$this->img_height[] = $thumb_height;

				// 画像のタイプに合わせて、サムネイルを作成します。
				if ($type == IMAGETYPE_JPEG)
				{
					// アップロードしたファイルを読み込みます。
					$ufimage = @ImageCreateFromJPEG($srcfilename);

					// 空のサムネイル画像を作成します。
					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);

					// 空のサムネイル画像にアップロードしたファイルをコピーします。
					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

					// クレジットを書き込みます。
					//if ($this->write_ok[$i] == true)
					if ($this->write_ok[$i] == true && !empty($this->credit) && strlen($this->credit) > 0)
					{
						// フォントサイズを決定します。
						$font_size = $this->decide_fontsize($thumb_width);

						// 画像にクレジットを書き込みます。
						$thumb = $this->write_credit($thumb, $this->credit, $font_size, $thumb_width, $thumb_height);
					}

					// ファイルを保存します。
					$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . ($i + 1) . $this->ext;
					@imagejpeg($thumb, $thfilename);
				}
				else if ($type == IMAGETYPE_GIF)
				{
					// アップロードしたファイルを読み込みます。
					$ufimage = @ImageCreateFromGIF($srcfilename);

					// 空のサムネイル画像を作成します。
					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);

					// 空のサムネイル画像にアップロードしたファイルをコピーします。
					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

					// クレジットを書き込みます。
					//if ($this->write_ok[$i] == true)
					if ($this->write_ok[$i] == true && !empty($this->credit) && strlen($this->credit) > 0)
					{
						// フォントサイズを決定します。
						$font_size = $this->decide_fontsize($thumb_width);

						// 画像にクレジットを書き込みます。
						$thumb = $this->write_credit($thumb, $this->credit, $font_size, $thumb_width, $thumb_height);
					}

					// ファイルを保存します。
					$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . ($i + 1) . $this->ext;
					@imagegif($thumb, $thfilename);
				}
				else if ($type == IMAGETYPE_PNG)
				{
					// アップロードしたファイルを読み込みます。
					$ufimage = @ImageCreateFromPNG($srcfilename);

					// 空のサムネイル画像を作成します。
					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);

					// 空のサムネイル画像にアップロードしたファイルをコピーします。
					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);

					// クレジットを書き込みます。
					//if ($this->write_ok[$i] == true)
					if ($this->write_ok[$i] == true && !empty($this->credit) && strlen($this->credit) > 0)
					{
						// フォントサイズを決定します。
						$font_size = $this->decide_fontsize($thumb_width);

						// 画像にクレジットを書き込みます。
						$thumb = $this->write_credit($thumb, $this->credit, $font_size, $thumb_width, $thumb_height);
					}

					// ファイルを保存します。
					$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . ($i + 1) . $this->ext;
					@imagepng($thumb, $thfilename);
				}

				// アップロードされたファイル名を設定します。
				$this->svfullpath[] = $thfilename;
				$this->up_url[] = $this->uploadconf['site_url'] . $thfilename;

				// 画像を破棄します。
				@imagedestroy($ufimage);
				@imagedestroy($thumb);
			}
		}
		return true;
	}

	function write_credit2($img, $cre_str, $fsize, $width_i, $height_i)
	{
		// クレジット書き込み用の設定を行います。
		// 書き込み角度を設定します。
		$font_angle = 0;

		//GD環境情報を取得します。
		$arrInfo = gd_info();

		// 書き込むクレジットを設定します。
		$telop_text = "";
		if ($arrInfo['JIS-mapped Japanese Font Support']) {
			// GDが対応している場合はUTF-8への変換は不要です。
			$telop_text = $cre_str;
		}
		else
		{
			// 組込みテキスト
			// GDが対応していない場合はUTF-8へ変換します。（UTF-8に変換しない場合、文字化けします。）
			$telop_text =  mb_convert_encoding($cre_str, "UTF-8", "auto");
		}

		// 半透明のグレーバック表示位置
		$alpha_x1 = 5;
		$alpha_x2 = $width_i - 5;

		$alpha_y1 = $height_i - ($fsize + 10) - 5;
		$alpha_y2 = $height_i - 5;

		// クレジット書き込み位置
		if($width_i == 200)
		{
			$tx = $alpha_x1 + 22;
			$ty = 92;
		}
		if($width_i == 400)
		{
			$tx = $width_i/2-170;
			$ty = $height_i/2+35;
		}
		if($width_i == 800)
		{
			$tx = $width_i/2-334;
			$ty = $height_i/2+65;
		}

		// アルファチャンネル（グレー）
		$alpha = imagecolorallocatealpha($img, 255, 255, 255, 100);

		//テキスト描画
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $alpha, "./sazanami-gothic.ttf", $telop_text);
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $alpha, "./sazanami-gothic.ttf", $telop_text);

		return $img;
	}
}

class RegistrationClassifications
{
	var $message;											// メッセージ
	var $error;												// エラー
	var $registration_classification_id;					// 登録分類ID
	var $photo_id;											// 写真ID

	var $classification_id;									// 分類ID
	var $classification_name;								// 分類

	var $direction_id;										// 方面ID
	var $direction_name;									// 方面

	var $country_prefecture_id;								// 国・都道府県ID
	var $country_prefecture_name;							// 国・都道府県

	var $place_id;											// 地名ID
	var $place_name;										// 地名

	var $state;												// 状態
	var $count;												// 数

	function __construct()
	{
		$this->init_data();
	}

	function init_data()
	{
		// 初期化します。
		$this->message = "";								// メッセージ
		$this->error = "";									// エラー

		$this->registration_classification_id = array();	// 登録分類ID
		$this->photo_id = -1;								// 写真ID

		$this->classification_id = array();					// 分類ID
		$this->classification_name = array();				// 分類

		$this->direction_id = array();						// 方面ID
		$this->direction_name = array();					// 方面

		$this->country_prefecture_id = array();				// 国・都道府県ID
		$this->country_prefecture_name = array();			// 国・都道府県

		$this->place_id = array();							// 地名ID
		$this->place_name = array();						// 地名

		$this->state = array();								// 状態
		$this->count = 0;									// 数
	}

	function set_data($rcdata)
	{
		$this->registration_classification_id[] = $rcdata['registration_classification_id'];

		$this->classification_id[] = $rcdata['classification_id'];
		$this->classification_name[] = $rcdata['classification_name'];

		$this->direction_id[] = $rcdata['direction_id'];
		$this->direction_name[] = $rcdata['direction_name'];

		$this->country_prefecture_id[] = $rcdata['country_prefecture_id'];
		$this->country_prefecture_name[] = $rcdata['country_prefecture_name'];

		$this->place_id[] = $rcdata['place_id'];
		$this->place_name[] = $rcdata['place_name'];

		$this->state[] = $rcdata['state'];
		$this->count++;
	}

	// $no:1～
	// エラーの場合はすべて初期化します。
	function get_id(&$c_id, &$d_id, &$cp_id, &$p_id, $no)
	{
		if ($no <= 0 || $no > $this->count)
		{
			$c_id = -1;
			$d_id = -1;
			$cp_id = -1;
			$p_id = -1;
			return;
			//$this->message = "登録分類データを取得できませんでした。（no<=0）";
			//throw new Exception($this->message);
		}
		$no--;
		$c_id = $this->classification_id[$no];
		$d_id = $this->direction_id[$no];
		$cp_id = $this->country_prefecture_id[$no];
		$p_id = $this->place_id[$no];
	}

	function set_id($c_id, $d_id, $cp_id, $p_id)
	{

		if (is_numeric($c_id))
		{
			$this->classification_id[] = $c_id;
		}
		else
		{
			$this->classification_id[] = -1;
		}

		if (is_numeric($d_id))
		{
			$this->direction_id[] = $d_id;
		}
		else
		{
			$this->direction_id[] = -1;
		}

		if (is_numeric($cp_id))
		{
			$this->country_prefecture_id[] = $cp_id;
		}
		else
		{
			$this->country_prefecture_id[] = -1;
		}

		if (is_numeric($p_id))
		{
			$this->place_id[] = $p_id;
		}
		else
		{
			$this->place_id[] = -1;
		}

		$this->count++;
	}

	// データ追加時のphoto_idは$this->photo_idではなく、$pidです。
	function insert_data($db_link, $pid)
	{
		// 画像IDが数値の場合は、保存します。
		if (!is_numeric($pid))
		{
			$this->photo_id = $pid;
		}

		// データを全て追加登録します。
		for($i = 0 ; $i < $this->count ; $i++)
		{
			// 各IDをチェックします。
			if (!is_numeric($this->classification_id[$i]))
			{
				$this->classification_id[$i] = -1;
			}

			if (!is_numeric($this->direction_id[$i]))
			{
				$this->direction_id[$i] = -1;
			}

			if (!is_numeric($this->country_prefecture_id[$i]))
			{
				$this->country_prefecture_id[$i] = -1;
			}

			if (!is_numeric($this->place_id[$i]))
			{
				$this->place_id[$i] = -1;
			}

			// 全てが指定されていなければ、追加しません。
			if ($this->classification_id[$i] == -1 && $this->direction_id[$i] == -1 && $this->country_prefecture_id[$i] == -1 && $this->place_id[$i] == -1)
			{
				continue;
			}
			$sql = "INSERT INTO registration_classification (photo_id, classification_id, direction_id, country_prefecture_id, place_id) VALUES ( ";
			$sql .= $pid . ",";										// 画像ID
			$sql .= $this->classification_id[$i] . ",";				// 分類名
			$sql .= $this->direction_id[$i] . ",";					// 方面
			$sql .= $this->country_prefecture_id[$i] . ",";			// 国・都道府県
			$sql .= $this->place_id[$i];							// 地名
			$sql .= ");";

			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "登録分類をDBに登録できませんでした。（処理数!=1） No=" . $i;
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "登録分類をDBに登録できませんでした。（条件設定エラー） No=" . $i;
				throw new Exception($this->message);
			}
		}
	}
}

// (1) SOAPサーバオブジェクトの作成
$server = new SoapServer($wsdl);

// (2) メソッドの追加
$server->addFunction("uploadfiles");

// (3) リクエストの処理
$server->handle();
?>
