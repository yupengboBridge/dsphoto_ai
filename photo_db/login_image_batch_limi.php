<?php

require_once('./config.php');
require_once('./lib.php');

date_default_timezone_set('Asia/Tokyo');

// セッション管理をスタートします。
session_start();

$s_login_id = array_get_value($_SESSION,'login_id' ,"");
$s_login_name = array_get_value($_SESSION,'user_name' ,"");
$s_security_level = array_get_value($_SESSION,'security_level' ,"");
$comp_code = array_get_value($_SESSION,'compcode' ,"");
$s_group_id = array_get_value($_SESSION,'group' ,"");
$s_user_id = array_get_value($_SESSION,'user_id' ,"");

// for Debug
//$s_user_id = 1;
//$s_login_name = "BUD管理者";
//$s_login_id = "admin";

//ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

// CSVファイルのPATHを設定
$csvdir = "./photods_csv/";
$imgdir = "./limited/";

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
$pi = new PhotoImageDB ();

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
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
 * 関数名：uploadfiles
 * 関数説明：ファイルのアップ
 * パラメタ：無し
 * 戻り値：アップロードは成功するかどうか、true/false
 */
function uploadfiles()
{
	global $csvdir,$db_link,$pi,$comp_code,$data_ary,$s_login_id,$s_login_name;
	global $classification_id1, $direction_id1, $country_prefecture_id1, $place_id1;
	global $classification_name1,$direction_name1,$country_prefecture_name1,$place_name1;
	global $p_photo_extentions,$p_photo_extentions_ok,$okflg;

	$file_name_para = "";
	$file_name_para = array_get_value($_REQUEST, 'p_filename' ,"");		// ファイル名


	if (is_file($csvdir.$file_name_para) == false)
	{
		$errmessage = $csvdir.$file_name_para."ファイルは見つかりませんでした！\r\n";
		write_log_tofile($errmessage);
		print "<p style='color: red'>".$csvdir.$file_name_para."ファイルは見つかりませんでした！<br/></p>";
		return;
	}

	setlocale(LC_ALL,'ja_JP.UTF-8');
	// CSVファイルを開く
	$file = fopen($csvdir.$file_name_para,"r");

	// CSVファイルからフィールド名を取得する
	if (!feof($file))
	{
		// CSVの内容
		$csv_fields = fgetcsv($file,1000000,"\t");
	} else {
		// CSVファイルを閉じる
		fclose($file);
	}

	$cnt = 0;//成功の件数
	$total_cnt = 0;//総件数
	$err_cnt = 0;//エラー件数
	$exited_cnt = 0;//存在件数
	// ファイルの内容より繰り返し一覧データを作成する
	while(!feof($file))
	{
		// 行の内容は配列にする
		$csv_content = fgetcsv($file,1000000,"\t");

		if (count($csv_content) <= 0 || empty($csv_content)) continue;

		$total_cnt = $total_cnt + 1;
		
		if (count($csv_content) != 33 )
		{
			$str =  "<p style='color: red'>画像ファイル名：".$csv_content[0]."　フィールド数は違います。</p>";
			$errmessage2 = "画像ファイル名：".$csv_content[0].",フィールド数は違います。,".date("Y-m-d H:i:s")."\r\n";
			
			print $str . str_repeat(' ', 256);
			print "<br/>";
			ob_flush();
			flush();

			write_log_tofile($errmessage2);
			continue;
		}
			
		//BUD_PHOTO番号
		$bud_photo_no = $csv_content[5];
		if(!empty($bud_photo_no))
		{
			$exit_budid = query_budphoto($bud_photo_no);
			if(!empty($exit_budid))
			{
				if($exit_budid == "ERR1")
				{
					$str =  "<p style='color: red'>画像ファイル名：".$csv_content[0]."　DBに既に存在します。</p>";
					$errmessage2 = "画像ファイル名：".$csv_content[0].",DBに既に存在します。,".date("Y-m-d H:i:s")."\r\n";

					print $str . str_repeat(' ', 256);
					print "<br/>";
					ob_flush();
					flush();
					
					$exited_cnt = $exited_cnt + 1;
					
					write_log_tofile($errmessage2);
					continue;
				} elseif ($exit_budid == "ERR") {
					$str =  "<p style='color: red'>画像ファイル名：".$csv_content[0]."　DBに存在性チェックをする時、エラーが発生しまいました。</p>";
					$errmessage2 = "画像ファイル名：".$csv_content[0].",DBに存在性チェックをする時、エラーが発生しまいました。,".date("Y-m-d H:i:s")."\r\n";

					print $str . str_repeat(' ', 256);
					print "<br/>";
					ob_flush();
					flush();
					
					write_log_tofile($errmessage2);
					continue;
				}
			}
		}
		
		//「写真入手元」の項目がnullの場合エラーになる
		$reg_p_obtaining = $csv_content[15];
		if(empty($reg_p_obtaining))
		{
			$str =  "<p style='color: red'>画像ファイル名：".$csv_content[0]."「写真入手元」の項目がnullです。</p>";
			$errmessage2 = "画像ファイル名：".$csv_content[0].",「写真入手元」の項目がnullです。,".date("Y-m-d H:i:s")."\r\n";

			print $str . str_repeat(' ', 256);
			print "<br/>";
			ob_flush();
			flush();
			
			write_log_tofile($errmessage2);
			continue;
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
			$data_ary['photo_mno'] = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);

			$data_ary['reg_photo_mno'] = $tmp_photo_mno2;
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

		// 登録申請アカウント
		$data_ary['reg_apply_id'] = $s_userid;

		// 登録申請者
		$data_ary['reg_apply'] =  $s_username;

		// 登録許可アカウント
		$permission_account = $s_login_id;
		$data_ary['reg_permission_id'] = $permission_account;

		// 登録許可者
		$data_ary['reg_permission'] = $s_login_name;

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
				$keywordstr = "自然・植物";
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
				if (!empty($keywordstr))
				{
					$keywordstr .= " 建造物";
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
				if (!empty($keywordstr))
				{
					$keywordstr .= " 施設 美術館・博物館";
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
				if (!empty($keywordstr))
				{
					$keywordstr .= " 乗り物";
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
						$keywordstr .= " 航空";
					} else {
						$keywordstr .= " 乗り物 航空";
					}
				} else {
					$keywordstr = "乗り物 航空";
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
				if (!empty($keywordstr))
				{
					$keywordstr .= " 飲食物";
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
				if (!empty($keywordstr))
				{
					$keywordstr .= " 品物";
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
				if (!empty($keywordstr))
				{
					$keywordstr .= " イベント";
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

			if ($category_tmp_ary[$i] == "人物" || $category_tmp_ary[$i] == "動物" ||
			    $category_tmp_ary[$i] == "世界遺産")
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
		$tmpfilename = "./limited/".$csv_content[0];
		if (!is_file($tmpfilename))
		{
			$errmessage = "画像ファイル：".$csv_content[0].",limitedディレクトリに見つかりませんでした。,".date("Y-m-d H:i:s")."\r\n";
			$str =  "<p style='color: red'>"."画像ファイル：".$csv_content[0]."はlimitedディレクトリに見つかりませんでした。</p>";
			
			print $str . str_repeat(' ', 256);
			print "<br/>";
			ob_flush();
			flush();
			
			write_log_tofile($errmessage);
			continue;
		}

		//画像ファイルのサイズをチェックする
		$size = @getimagesize($tmpfilename);
		list($width, $height, $type, $attr) = $size;
		
		if(!empty($width) && !empty($height))
		{
			if((int)$width != 400 && (int)$width != 200)
			{
				$errmessage = "画像ファイル：".$csv_content[0].",イメージファイルのサイズは違います。,".date("Y-m-d H:i:s")."\r\n";
				$str =  "<p style='color: red'>"."画像ファイル：".$csv_content[0]."  イメージファイルのサイズは違います。</p>";
				
				print $str . str_repeat(' ', 256);
				print "<br/>";
				ob_flush();
				flush();
				
				write_log_tofile($errmessage);
				continue;
			}
			
			if((int)$height != 300 && (int)$height != 150)
			{
				$errmessage = "画像ファイル：".$csv_content[0].",イメージファイルのサイズは違います。,".date("Y-m-d H:i:s")."\r\n";
				$str =  "<p style='color: red'>"."画像ファイル：".$csv_content[0]."  イメージファイルのサイズは違います。</p>";
				
				print $str . str_repeat(' ', 256);
				print "<br/>";
				ob_flush();
				flush();
				
				write_log_tofile($errmessage);
				continue;
			}
		}
		
		$tmp = $csv_content[18];
		if (!empty($filename) && !empty($tmp))
		{
			file_put_contents('log.txt', '23333');
			uploadfile($filename,$tmp,$data_ary);

			if ($okflg == false)
			{
				$okflg = true;//リセット
				$str =  "<p style='color: red'>".$filename."イメージファイルの処理はエラーです。</p>";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				ob_flush();
				flush();
			} elseif ($okflg == true) {
				$cnt = $cnt + 1;
				$str =  $filename."イメージファイルの処理はＯＫです。";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				ob_flush();
				flush();
			}
		} elseif (!empty($filename) && empty($tmp)) {
			uploadfile($filename,'',$data_ary);

			if ($okflg == false)
			{
				$okflg = true;//リセット
				$str =  "<p style='color: red'>".$filename."イメージファイルの処理はエラーです。</p>";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				ob_flush();
				flush();
			} elseif ($okflg == true) {
				$cnt = $cnt + 1;
				$str =  $filename."イメージファイルの処理はＯＫです。";
				print $str . str_repeat(' ', 256);
				print "<br/>";
				ob_flush();
				flush();
			}
		}
	}
	// CSVファイルを閉じる
	fclose($file);
	
	$err_cnt = $total_cnt - $cnt - $exited_cnt;
	//print $cnt."成件を処理しました。\r\n";
	print $total_cnt."件を処理しました。"."成功：".$cnt."件，エラー：".$err_cnt."件，既に存在：".$exited_cnt."件";
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

/*
 * 関数名：uploadfile
 * 関数説明：ファイルのアップ
 * パラメタ：
 * $filename:ファイル名
 * $additional_constraints1:クレジット
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
		$message = $e->getMessage();
		throw new Exception($message);
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
					$place_name1 = "";

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
 * 関数名：write_log_tofile
 * 関数説明：CSVでバッチする時、エラーがある場合、エラーをログファイルに出力する
 * パラメタ：errmsg:エラーメッセージ
 * 戻り値：無し
 */
function write_log_tofile($errmsg)
{
	// CSVファイルを出力する
	$file = fopen("./log/csv_batch_errorlog.log","a+");
	fwrite($file,$errmsg);
	fclose($file);
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
		
		$message= $e->getMessage();
		throw new Exception($message);
	}
}

/*
 * 関数名：query
 * 関数説明：ユーザーのチェック
 * パラメタ：
 * bud_photo：BUD_PHOTO番号
 * 戻り値：OK/ERR
 */
function query_budphoto($bud_photo)
{
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();

		if(!$db_link)
		{
			return "ERR";
		}

		// ユーザー情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "select * from photoimg where bud_photo_no = '".$bud_photo."' and publishing_situation_id = 1";
		$stmt = $db_link->prepare($sql);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount >= 1)
			{
				return "ERR1";
			}
			else
			{
				return "OK";
			}
		}
		else
		{
			return "ERR";
		}
	}
	catch(Exception $e)
	{
		return "ERR";
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
?>
<html>
<head>
<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8”>
<title>ＣＳＶのバッチインポート</title>
</head>
<body>
<?php
uploadfiles();
?>
</body>
</html>
