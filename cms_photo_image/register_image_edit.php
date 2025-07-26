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

$div_classzeroflg = true;													// 分類登録するかどうかフラグ

$category_id = array();														// カテゴリーID
$category_name = array();													// カテゴリー名

$range_id = array();														// 使用範囲ID
$range_name = array();														// 使用範囲名

$borrow_id = array();														// 写真入手元ID
$borrow_name = array();														// 写真入手元

$p_situation_id = array();													// 掲載状況ID
$p_situation_name = array();												// 掲載状況

//jinxin 2012-02-09 modify start
$p_nopermis_id = array();												//no permission reason id
$p_nopermis_name = array();
//jinxin 2012-02-09 modify end

$s_login_id = array_get_value($_SESSION,'login_id' ,"");
$s_login_name = array_get_value($_SESSION,'user_name' ,"");
$s_security_level = array_get_value($_SESSION,'security_level' ,"");
$comp_code = array_get_value($_SESSION,'compcode' ,"");
$s_group_id = array_get_value($_SESSION,'group' ,"");
$s_user_id = array_get_value($_SESSION,'user_id' ,"");

//for debug 用
//$comp_code = '00000';

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

$initflg = 0;

$p_photo_id = array_get_value($_REQUEST, "p_photo_id","");						//イメージID

$p_action = array_get_value($_REQUEST, 'p_action',"");							// アクション
// PhotoImageのインスタンスを生成します。
$pi = new PhotoImageDB ();
try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

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
		if (!empty($p_photo_mno) && !empty($p_photo_name))
		{
			//yupengbo modify 2011/11/18 start
			$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",".$p_photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$p_photo_name));
			$logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$bud_photo_no)).",";
			$logstr .= $date_from.",".$date_to.",1,".$registration_person."\r\n";
			//yupengbo modify 2011/11/18 end
		} else {
//			//yupengbo modify 2011/11/18 start
//			$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",申請中,".$p_photo_name;
//			$logstr .= ",".$photo_explanation.",".$bud_photo_no.",";
//			$logstr .= $date_from.",".$date_to.",1,".$registration_person."\r\n";
//			//yupengbo modify 2011/11/18 end
		}

		$pi->delete_data($db_link, $p_photo_id);

//		$tmp_p_mon_old = array_get_value($_REQUEST,"del_photo_mno","");
//		if (!empty($tmp_p_mon_old))
//		{
//			$ipos1 = strpos($tmp_p_mon_old,"-");
//			if ((int)$ipos1 > 0)
//			{
//				$tmp_photo_mno2 = substr($tmp_p_mon_old,(int)$ipos1+1,5);
//				$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
//				$new_maxno = (int)$p_maxno - 2;
//				if (!empty($tmp_photo_mno2) && strlen($tmp_photo_mno2) > 0)
//				{
//					$pi->setmaxno($db_link,$tmp_photo_mno2,$new_maxno,1);
//				}
//			}
//		}

		if (!empty($logstr))
		{
			write_log_tofile($logstr);
		}

		print "<script type=\"text/javascript\">";
		print "alert(\"画像を削除しました。\");";
		print "parent.bottom.location.href  = \"./registration_list.php\";";
		print "</script>";
	} elseif ($p_action == "update") {
		// 更新する
		//updatefile();
		set_updatedata();

		if ((int)$pi->publishing_situation_id == 2)
		{
			// イメージをバイナリを変換して、DBに保存する
			$pi->write_imagetodb($db_link, $p_photo_id);
		}
		$pi->update_data($db_link);

		// maxnumberの更新
		$tmp_photo_mno2 = array_get_value($_POST,"reg_photo_mno","");
		$tmp_p_mon_old = array_get_value($_POST,"hidreg_photo_mno","");
		if (!empty($tmp_photo_mno2) && !empty($tmp_p_mon_old))
		{
			//if ($tmp_photo_mno2 != $tmp_p_mon_old)
			//{
				$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
				if (!empty($tmp_photo_mno2) && strlen($tmp_photo_mno2) > 0)
				{
					$pi->setmaxno($db_link,$tmp_photo_mno2,$p_maxno);
				}
			//}
		} elseif (!empty($tmp_photo_mno2) && empty($tmp_p_mon_old)) {
			//if ($tmp_photo_mno2 != $tmp_p_mon_old)
			//{
				$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
				if (!empty($tmp_photo_mno2) && strlen($tmp_photo_mno2) > 0)
				{
					$pi->setmaxno($db_link,$tmp_photo_mno2,$p_maxno);
				}
			//}
		}
		// add by jinxin 2012/02/09 start
		if ((int)$pi->publishing_situation_id == 3)
		{
			$p_photo_mno = "";
			$p_photo_name = "";
			$photo_explanation = "";
			$bud_photo_no = "";
			$registration_person = "";
			$date_from = "";
			$date_to = "";
			$nopermission = "";
			$pi->get_nopermit_log($db_link,$p_photo_id,$p_photo_mno,$p_photo_name,
		                    $photo_explanation,$bud_photo_no,
		                    $date_from,$date_to,$nopermission);
			$logmsg = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",".$p_photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$p_photo_name));
			$logmsg .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$bud_photo_no)).",";
			$logmsg .= $date_from.",".$date_to." ".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$nopermission))."\r\n";
			if(!empty($logmsg)){
				write_log_nopermit($logmsg);
			}
		}
		// add by jinxin 2012/02/09 end

		print "<script src='./js/common.js'  type='text/javascript'  charset='utf-8'></script>\r\n";
		print "<script type=\"text/javascript\">\r\n";
		print "alert(\"画像をＤＢに更新しました。\");";
		if ((int)$pi->publishing_situation_id == 3)
		{
			print "parent.bottom.location.href = './registration_list.php';";
		}else{
			print "parent.bottom.location.href = getCookie('reg_edit_url');";
		}
		print "</script>";

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

		$pi->get_nopermis_reasons($db_link, $p_nopermis_id, $p_nopermis_name);                                  // no permission
	}
}
catch(Exception $cla)
{
	// 異常を出力する
	$errMsg = $cla->getMessage();
	write_error_log($errMsg);
	
	$msg[] = $errMsg;
	error_exit($msg);
}

//xu add it on 2010-12-21 start
/*
 * 関数名：uploadfile
 * 関数説明：ファイルのアップ
 * パラメタ：無し
 * 戻り値：アップロードは成功するかどうか、true/false
 */
function updatefile()
{
	global $pi, $db_link,$upload_conf;

	// アップロード用のオブジェクト
	$fl = NULL;

	try
	{
		$tmp1 = urldecode(array_get_value($_REQUEST,"reg_addition0" ,""));
		$reg_div = (array_get_value($_REQUEST,"reg_division" ,""));
		// アップロード用のインスタンスを生成します。
		$photo_filename =  array_get_value($_POST,"img_url0","");
		$file_name = basename($photo_filename);
		$pos = strrpos($photo_filename,"/");
		$file_rd_path = substr($photo_filename,$pos-1,1);
		$img_size = @filesize($upload_conf['dir'].$file_rd_path.'/'.$file_name);
		// echo $upload_conf['dir'].$file_rd_path.'/'.$file_name;
		$files['name'] = $file_name;
		$files['size'] = $img_size;
		$files['error'] = 0;

		$fl = new FileUpload($files, "", "", "", "", $tmp1, "");
		$fl->set_upload_info($photo_filename,$files,$file_name,$file_rd_path);
		//「バナー」を選択した場合に、①画像はResizeをしなくて、元のサイズのまま登録。②クレジットもしないようにする
		$resize_flg=true;
		if($reg_div==3)
		{
			$write_credit = array(false, false, false, false, false);
			$fl->set_write_ok($write_credit);
			$resize_flg=false;
		}
//		print_r($fl);
//		exit;
		// サムネイルを作成します。
		$fl->make_thumbfile($resize_flg);
		// DB保存用のデータを設定します。
		$pi->up_url = $fl->up_url;					// アップロードURL
		$pi->img_width = $fl->img_width;			// イメージサイズ（横）
		$pi->img_height = $fl->img_height;			// イメージサイズ（縦）
		$pi->ext = $fl->ext;						// 拡張子
		$pi->image_size_x = $fl->img_width[0];		// 画像サイズ（横）
		$pi->image_size_y = $fl->img_height[0];		// 画像サイズ（縦）

		// 更新する
		set_updatedata();

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
		throw new Exception($message);
		return false;
	}
}
//xu add it on 2010-12-21 end
function check_Photomon($db_link,$par_p_mno)
{
	$sql = "select count(*) cnt from photoimg where photo_mno = '".$par_p_mno."'";
	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();
	if ($result == true)
	{
		while(!!($image_data = $stmt->fetch(PDO::FETCH_ASSOC)))
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

function check_photo_mno($db_link, $photo_id, $par_p_mno)
{
    $sql = "select count(*) cnt from photoimg where photo_id <> ? and photo_mno = ?;";
    $stmt = $db_link->prepare($sql);
    $stmt->bindParam(1, $photo_id);
    $stmt->bindParam(2, $par_p_mno);
    $result = $stmt->execute();
    if ($result == true)
    {
        while(!!($image_data = $stmt->fetch(PDO::FETCH_ASSOC)))
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
 * 関数名：set_updatedata
 * 関数説明：イメージの更新
 * パラメタ：無し
 * 戻り値：無し
 */
function set_updatedata()
{
	global $pi, $p_photo_id, $comp_code, $db_link;

	$pi->photo_id = $p_photo_id;														// 画像ID
	//画像管理番号の設定
	$tmp_regs = (int)array_get_value($_POST,"reg_situation","");							// 掲載状況
	$s_photo_mno = array_get_value($_POST,"photo_mno","");
	if (empty($s_photo_mno)) $s_photo_mno = "";

	// 掲載状況の「掲載許可」を選択した場合
	if ((int)$tmp_regs == 2)
	{
		$tmp = "__".$s_photo_mno;
		$i_pos = strpos($tmp,"申請中");
		if($i_pos > 0)
		{
			// --------画像管理番号を作成する（開始）------------------------------
			//$tmp_photo_mno1 = $comp_code;
			$tmp_photo_mno1 = substr($tmp,11,5);
			$tmp_photo_mno2 = array_get_value($_POST,"reg_photo_mno","");
			if (empty($tmp_photo_mno2) || strlen($tmp_photo_mno2) <= 0)
			{
				//$pi->photo_mno = "申請中".$tmp_photo_mno1;
				$pi->photo_mno = "申請中".substr($tmp,11);
			} else {
				$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
				$tmp_photo_mno3 = $p_maxno;
				$i_pos = strpos($s_photo_mno,".");
				$tmp_photo_mno4 =substr($s_photo_mno,$i_pos);
				$pi->photo_mno = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
				$flg = "err";
				for($i=0;$i<=9;$i++)
				{
					$tmp_photo_mno3 = $p_maxno+$i;
					$pi->photo_mno = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
					//チェック
					$exitstedflg1 = check_Photomon($db_link,$pi->photo_mno);
					if($exitstedflg1==false)
					{
						$flg = "ok";
						break;
					}
				}
				if($flg == "err")
				{
					// エラー情報をセットして、例外をスローします。
					$message = "管理番号の作成はエラーになりました。申請中";
					throw new Exception($message);
				}
//		print "<script src='./js/common.js'  type='text/javascript'  charset='utf-8'></script>\r\n";
//		print "<script type=\"text/javascript\">\r\n";
//		print "alert(\"".$pi->photo_mno."\");";
//		print "parent.bottom.location.href = getCookie('reg_edit_url');";
//		print "</script>";
				// --------画像管理番号を作成する（終了）------------------------------
			}
		} else {
		    //原掲載状況
			$tmp1 = array_get_value($_POST,"publishing_situation_id","");
			if (!empty($tmp1))
			{
				$tmp2 = (int)$tmp1;
				// 掲載状況（掲載許可）
				if ($tmp2 == 2)
				{
					$pi->photo_mno = $s_photo_mno;										// 画像管理番号
				// 掲載状況（申請中）
				} elseif ($tmp2 == 1) {
					if(!empty($s_photo_mno))
					{
					    $split_mno = explode("-", $s_photo_mno);
					    if(count($split_mno) >= 3)
					    {
					        $tmp_photo_mno1 = $split_mno[0];
					        $reg_photo_mno = array_get_value($_POST,"reg_photo_mno","");
					        $tmp_photo_mno2 = $split_mno[1];
					        $split_mno3 = explode(".", $split_mno[2]);
					        $tmp_photo_mno3 = $split_mno3[0];
					        $tmp_photo_mno4 = ".".$split_mno3[1];
					        
					        $p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
					        $flg = "err";
					        
					        if($reg_photo_mno == $tmp_photo_mno2)
					        {
					            for($i=0;$i<=99999 - $p_maxno;$i++)
					            {
    					            $tmp_photo_mno3 = intval($tmp_photo_mno3) + (int)$i;
    					            $pi->photo_mno = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
    					            
    					            //チェック
    					            $exitstedflg1 = check_photo_mno($db_link, $pi->photo_id, $pi->photo_mno);
    					            if($exitstedflg1==false)
    					            {
        					            $flg = "ok";
        					            break;
    					            }
					            }
					        }
					        else
					        {
					            $tmp_photo_mno2 = $reg_photo_mno;
					            for($i=0;$i<=99999 - $p_maxno;$i++)
					            {
					                $tmp_photo_mno3 = (int)$p_maxno + (int)$i;
					                $pi->photo_mno = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
					                 
					                //チェック
					                $exitstedflg1 = check_Photomon($db_link, $pi->photo_mno);
					                if($exitstedflg1==false)
					                {
    					                $flg = "ok";
    					                break;
    					            }
					            }
					        }
					        if($flg == "err")
					        {
					            // エラー情報をセットして、例外をスローします。
					            $message = "管理番号の作成はエラーになりました。CSVアップロードツール";
					            throw new Exception($message);
					        }
					    }
					} else {
						// --------画像管理番号を作成する（開始）------------------------------
						$tmp_photo_mno1 = $comp_code;
						$tmp_photo_mno2 = array_get_value($_POST,"reg_photo_mno","");
						//$tmp_photo_mno3 = substr($s_photo_mno,-8,5);
						$p_maxno = (int)$pi->getmaxno($db_link, $tmp_photo_mno2);
						$tmp_photo_mno3 = $p_maxno + 1;
						//$i_pos = strpos($s_photo_mno,".");
						//$tmp_photo_mno4 =substr($s_photo_mno,$i_pos);
						$tmp_photo_mno4 = $pi->ext;
						$pi->photo_mno = sprintf("%s-%s-%05d%s", $tmp_photo_mno1, $tmp_photo_mno2, $tmp_photo_mno3, $tmp_photo_mno4);
						// --------画像管理番号を作成する（終了）------------------------------
					}
				}
			}

		}
	} else {
		$pi->photo_mno = $s_photo_mno;													// 画像管理番号
	}

	$pi->publishing_situation_id = (int)array_get_value($_POST,"reg_situation","");		// 掲載状況
	$pi->registration_division_id = (int)array_get_value($_POST,"reg_division","");		// 登録区分
	$reg_mate_mana = array_get_value($_POST,"reg_mate_mana","");							// 元画像管理番号
	if (empty($reg_mate_mana) || strlen($reg_mate_mana) <= 0)
	{
		$pi->source_image_no = "元画像なし";											// 元画像管理番号
	} else {
		$pi->source_image_no = array_get_value($_POST,"reg_mate_mana","");					// 元画像管理番号
	}
	$reg_bud_number = array_get_value($_POST,"reg_bud_number","");
	if ((int)$reg_bud_number == 1)														// BUD_PHOTO番号
	{
		$pi->bud_photo_no = array_get_value($_POST,"reg_bud_number_txt","");				// BUD_PHOTO番号
	} else {
		$pi->bud_photo_no = "";
	}
	$pi->photo_name = array_get_value($_POST,"reg_subject","");							// 写真名（タイトル）
	$pi->photo_explanation = array_get_value($_POST,"reg_material_txt","");				// 写真説明
	$pi->take_picture_time_id = urldecode(array_get_value($_REQUEST,"time2",""));			// 撮影時期１
	$pi->take_picture_time2_id = array_get_value($_POST,"rad_kisetu","");					// 撮影時期２
	$pi->dfrom  = array_get_value($_POST,"hidden_dfrom","");								// 掲載期間（From）
	$pi->dto = array_get_value($_POST,"p_dto","");											// 掲載期間（To）
	$pi->kikan = array_get_value($_POST,"reg_pub_period","");								// 期間
	$reg_p_obtaining = array_get_value($_POST,"reg_p_obtaining","");						// 写真入手元ID

	$pi->borrowing_ahead_id = $reg_p_obtaining;											// 写真入手元
	if ((int)$reg_p_obtaining == 2)														// 写真入手元
	{
		$pi->content_borrowing_ahead = array_get_value($_POST,"reg_p_obtaining_txt",""); 	// 写真入手元内容
	} else {
		$pi->content_borrowing_ahead = "";												// 写真入手元内容
	}
	$reg_pub_possible = array_get_value($_POST,"reg_pub_possible","");						// 使用範囲
	$pi->range_of_use_id = $reg_pub_possible;											// 使用範囲
	if ((int)$reg_pub_possible == 3)													// 使用範囲
	{
		$pi->use_condition = array_get_value($_POST,"reg_pub_possible_txt",""); 			// 出稿条件
	} else {
		$pi->use_condition = "";														// 出稿条件
	}
	$reg_addition = array_get_value($_POST,"reg_addition");								// 付加条件
	// 付加条件の「要クレジット」を選択した場合
	if ((int)$reg_addition == 0)
	{
		$pi->additional_constraints1 = urldecode(array_get_value($_REQUEST,"reg_addition0",""));			// 付加条件（クレジット）
	}
	// 付加条件の「要使用許可」を選択した場合
	if ((int)$reg_addition == 1)
	{
		$pi->additional_constraints2 = urldecode(array_get_value($_REQUEST,"reg_addition1" ,""));	// 付加条件（要確認）
	}
	$pi->monopoly_use = array_get_value($_POST,"reg_account","");							// 独占使用
	$pi->copyright_owner = array_get_value($_POST,"reg_copyright","");						// 版権所有者
	$pi->customer_section = array_get_value($_POST,"post_name","");						// お客様部署
	$pi->customer_name = array_get_value($_POST,"first_name","");							// お客様名
	$pi->permission_account = array_get_value($_POST,"reg_permission_id","");				// 登録許可アカウント
	$pi->permission_person =  array_get_value($_POST,"reg_permission_hidden","");			// 登録許可者
	$pi->permission_date = date("Y-m-d");												// 登録許可日
	$pi->register_date = array_get_value($_POST,"reg_apply_date", date("Y-m-d"));        // 登録日
	$pi->note = array_get_value($_POST,"reg_remarks","");									// 備考
	$pi->keyword_str = array_get_value($_POST, 'p_keyword_str',"");						// キーワード文字列（スペース区切り）

	$p_classification_id1 = array_get_value($_POST, 'p_classification_id1',"");			// 分類ID(1)
	$p_direction_id1 = array_get_value($_POST, 'p_direction_id1',"");						// 方面ID(1)
	$p_country_prefecture_id1 = array_get_value($_POST, 'p_country_prefecture_id1',"");	// 国・都道府県(1)
	$p_place_id1 = array_get_value($_POST, 'p_place_id1',"");								// 地名ID(1)
	if(!empty($p_classification_id1))
	{
		$pi->registration_classifications->set_id($p_classification_id1, $p_direction_id1, $p_country_prefecture_id1, $p_place_id1);
	}

	$p_classification_id2 = array_get_value($_POST, 'p_classification_id2',"");			// 分類ID(2)
	$p_direction_id2 = array_get_value($_POST, 'p_direction_id2',"");						// 方面ID(2)
	$p_country_prefecture_id2 = array_get_value($_POST, 'p_country_prefecture_id2',"");	// 国・都道府県ID(2)
	$p_place_id2 = array_get_value($_POST, 'p_place_id2',"");								// 地名ID(2)
	if(!empty($p_classification_id2))
	{
		$pi->registration_classifications->set_id($p_classification_id2, $p_direction_id2, $p_country_prefecture_id2, $p_place_id2);
	}

	$p_classification_id3 = array_get_value($_POST, 'p_classification_id3',"");			// 分類ID(3)
	$p_direction_id3 = array_get_value($_POST, 'p_direction_id3',"");						// 方面ID(3)
	$p_country_prefecture_id3 = array_get_value($_POST, 'p_country_prefecture_id3',"");	// 国・都道府県ID(3)
	$p_place_id3 = array_get_value($_POST, 'p_place_id3',"");								// 地名ID(3)
	if(!empty($p_classification_id3))
	{
		$pi->registration_classifications->set_id($p_classification_id3, $p_direction_id3, $p_country_prefecture_id3, $p_place_id3);
	}

	$p_classification_id4 = array_get_value($_POST, 'p_classification_id4',"");			// 分類ID(4)
	$p_direction_id4 = array_get_value($_POST, 'p_direction_id4',"");						// 方面ID(4)
	$p_country_prefecture_id4 = array_get_value($_POST, 'p_country_prefecture_id4',"");	// 国・都道府県ID(4)
	$p_place_id4 = array_get_value($_POST, 'p_place_id4',"");								// 地名ID(4)
	if(!empty($p_classification_id4))
	{
		$pi->registration_classifications->set_id($p_classification_id4, $p_direction_id4, $p_country_prefecture_id4, $p_place_id4);
	}

	$p_classification_id5 = array_get_value($_POST, 'p_classification_id5',"");			// 分類ID(5)
	$p_direction_id5 = array_get_value($_POST, 'p_direction_id5',"");						// 方面ID(5)
	$p_country_prefecture_id5 = array_get_value($_POST, 'p_country_prefecture_id5',"");	// 国・都道府県ID(5)
	$p_place_id5 = array_get_value($_POST, 'p_place_id5',"");								// 地名ID(5)
	if(!empty($p_classification_id5))
	{
		$pi->registration_classifications->set_id($p_classification_id5, $p_direction_id5, $p_country_prefecture_id5, $p_place_id5);
	}

	$pi->photo_filename = array_get_value($_POST,"img_url0","");							// アップロードURL
	$pi->photo_filename_th1 = array_get_value($_POST,"img_url1","");						// サムネイル1
	$pi->photo_filename_th2 = array_get_value($_POST,"img_url2","");						// サムネイル2
	$pi->photo_filename_th3 = array_get_value($_POST,"img_url3","");						// サムネイル3
	$pi->photo_filename_th4 = array_get_value($_POST,"img_url4","");						// サムネイル4

	//xu add it on 2010-12-01 start
	$pi->photo_org_no = array_get_value($_POST,"photo_org_no","");						// 元画像管理
	$pi->photo_url = array_get_value($_POST,"photo_url","");						// page url
	//xu add it on 2010-12-01 end

	//add by jinxin on 2012-02-07 start
	$pi->nopermis = array_get_value($_POST,"nopermis","");
	$pi->nopermis_date = date("Y-m-d");
	$pi->nopermis_personid = array_get_value($_SESSION,'user_name' ,"");
	//add by jinxin on 2012-02-07 end
}

/*
 * 関数名：disp_image
 * 関数説明：画像を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_image()
{
	global $pi,$p_photo_id;

	$tmp_url = "./disp_register_image.php?p_photo_id=".$p_photo_id;

	print "<span class=\"guard\"></span>\r\n";
	// バイナリを作成しない時
	if (empty($pi->image2))
	{
		//print "<p class=\"reg_photo_number\"><img src=\"".$pi->up_url[1]."\" width=\"250\" height=\"180\" /></p>\r\n";
		//changed by wangtongchao 2011-12-06 begin up_url[3]->up_url[3]."?".time().
		print "<p class=\"reg_photo_number\"><img src=\"".$pi->up_url[3]."?".time()."\" /></p>\r\n";
		//changed by wangtongchao 2011-12-06 end
	} else {
		//print "<p class=\"reg_photo_number\"><img src=\"".$tmp_url."\" width=\"250\" height=\"180\" /></p>\r\n";
		print "<p class=\"reg_photo_number\"><img src=\"".$tmp_url."\" /></p>\r\n";
	}
}

/*
 * 関数名：disp_photo_mno
 * 関数説明：「写真管理番号」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_photo_mno()
{
	// PhotoImageのインスタンス
	global $pi;

	print "	<dl class=\"reg_manage_number reg_clear reg_list_none_top\">\r\n";
	print "		<dt>写真管理番号</dt>\r\n";

	$p_mno = "";
	$tmp1 = $pi->publishing_situation_id;
	$tmp3 = $pi->photo_mno;
	if (!empty($tmp1))
	{
		$tmp2 = (int)$tmp1;
		// 掲載状況（申請中）
		if ($tmp2 == 1)
		{
			//00000-xxxxx-00000.jpgで入っている場合
			$match = "/^([a-z0-9A-Z_]{5})\-([a-z0-9A-Z_]{5})\-([a-z0-9A-Z_]{5})\.([a-zA-Z_]{3})$/";
			$isOK = preg_match($match,$tmp3);
			if($isOK)
			//if (strlen($tmp3) > 17)
			{
				$s_pos = (int)strpos($tmp3,"-",0);
				if ($s_pos > 0)
				{
					$tmp4 = substr($tmp3,$s_pos + 1);
					$s_pos1 = (int)strpos($tmp4,"-",0);
					//「xxxxx」を取得する
					if ($s_pos > 0)
					{
						$p_mno = substr($tmp4,0, $s_pos);
					}
				}
			} else {
				$tmp3_1 = "__".$tmp3;
				$s_pos = strpos($tmp3_1,"申請中",0);

				if ((int)$s_pos > 0)
				{
					$tmp_bud_photo_no = $pi->bud_photo_no;
					if (!empty($tmp_bud_photo_no))
					{
						//BUD-Photo番号を入っている場合、先頭５桁を取得する
						if (strlen($tmp_bud_photo_no) >= 5)
						{
							$p_mno = substr($tmp_bud_photo_no,0,5);
						} else {
							$p_mno = $tmp_bud_photo_no;
						}
					}
				}
			}
		// 掲載状況（掲載許可）
		} elseif ($tmp2 == 2) {
			$p_mno = $pi->photo_mno;
		}
	}

	if(empty($p_mno))
	{
		print "		<dd><input name=\"reg_photo_mno\" type=\"text\" id=\"reg_photo_mno\" onblur=\"check_reg_photo_mno();\" value=\"\" MaxLength=\"5\" size=\"10\" style=\"ime-mode:disabled\" disabled=\"disabled\"/></dd>\r\n";
	} else {
		if (!empty($tmp1))
		{
			$tmp2 = (int)$tmp1;
			// 掲載状況（申請中）
			if ($tmp2 == 1)
			{
				print "		<dd><input name=\"reg_photo_mno\" type=\"text\" id=\"reg_photo_mno\" onblur=\"check_reg_photo_mno();\" value=\"".$p_mno."\" MaxLength=\"5\" size=\"10\" style=\"ime-mode:disabled\" disabled=\"disabled\"/></dd>\r\n";
			// 掲載状況（掲載許可）
			} elseif ($tmp2 == 2) {
				print "		<dd>".dp($p_mno)."</dd>\r\n";
			} else {
				print "		<dd><input name=\"reg_photo_mno\" type=\"text\" id=\"reg_photo_mno\" onblur=\"check_reg_photo_mno();\" value=\"\" MaxLength=\"5\" size=\"10\" style=\"ime-mode:disabled\" disabled=\"disabled\"/></dd>\r\n";
			}
		}
	}
	print " <input type='hidden' id='hidreg_photo_mno' name='hidreg_photo_mno' value=\"".$p_mno."\" />\r\n";
	print "	</dl>\r\n";
}

/*
 * 関数名：disp_publishing_situation
 * 関数説明：「掲載状況」を出力する
 * パラメタ：
 * $ps_id：　　掲載状況ID
 * $ps_name：　掲載状況
 * 戻り値：無し
 */
function disp_publishing_situation($ps_id,$ps_name)
{
	global $pi;

	$ed = count($ps_id);

	print"<select id='reg_situation' name='reg_situation' onchange='change_reg_situation(this);'>\r\n";
	for ($i=0;$i < $ed;$i++)
	{
		// 登録した掲載状況は掲載状況レストに存在した場合
		if ((int)$pi->publishing_situation_id == (int)$ps_id[$i])
		{
			print"	<option value=".$ps_id[$i]." selected=\"selected\">".dp($ps_name[$i])."</option>\r\n";
		} else {
			print"	<option value=".$ps_id[$i].">".dp($ps_name[$i])."</option>\r\n";
		}
	}
	print"</select>\r\n";
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

	print "	<dl class=\"reg_subject reg_clear required\">\r\n";
	print "		<dt>被写体の名称・バナー、ロゴの名称</dt>\r\n";
	print "		<dd><input name=\"reg_subject\" type=\"text\" id=\"reg_subject\" size=\"65\" value='".$pi->photo_name."'/></dd>\r\n";
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
		//xu edit it on 2010-11-30 start
		$onclick_function = " onclick='show_none();' ";
		if ((int)$pi->registration_division_id == (int)$c_id[$i])
		{
			if($c_id[$i]=='3')
			{
				$onclick_function = " onclick='show_adv();' ";
			}
			elseif($c_id[$i]=='4')
			{
				$onclick_function = " onclick='show_url();' ";
			}
			print "	<label><input ".$onclick_function."  name=\"reg_division\" id=\"reg_division".$i."\" type='radio' checked=\"checked\" value=\"".$c_id[$i]."\" />".dp($c_name[$i])."</label>";
		} else {
			if($c_id[$i]=='3')
			{
				$onclick_function = " onclick='show_adv();' ";
			}
			elseif($c_id[$i]=='4')
			{
				$onclick_function = " onclick='show_url();' ";
			}
			print "	<label><input".$onclick_function." name=\"reg_division\" id=\"reg_division".$i."\" type='radio' value=\"".$c_id[$i]."\" />".dp($c_name[$i])."</label>";
		}
		//xu edit it on 2010-11-30 end
	}
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
	global $pi,$div_classzeroflg;

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
			$div_classzeroflg = false;
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
	global $pi;

	$indx = (int)$no - 1;
	// 分類を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->classification_id[$indx]))
	{
		$tmp_class = $pi->registration_classifications->classification_id[$indx];
	} else {
		$tmp_class = -1;
	}

	// 分類IDを作成する
	$selid = 'p_classification_id' . $no;

	print "	<select name='" . $selid . "' id='" . $selid . "'>\r\n";
	print "		<option value='-1'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";
	$ed = count($c_id);
	// 分類より繰り返し
	for ($i = 0 ; $i < $ed ; $i++)
	{
		// 登録した分類は分類レストに存在した場合
		if ((int)$tmp_class == (int)$c_id[$i])
		{
			print "	<option value=\"" . $c_id[$i] . "\" selected=\"selected\">" . dp($c_name[$i]) . "</option>\r\n";
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
	global $pi;

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
			if ((int)$tmp_direct == (int)$d_id[$i][$j])
			{
				print "	<option value='" . $d_id[$i][$j] . "' selected=\"selected\">" . dp(santen_reader($d_name[$i][$j], 18)) . "</option>\r\n";
			} else {
				//print "	<option value='" . $d_id[$i][$j] . "'>" . dp(santen_reader($d_name[$i][$j], 18)) . "</option>\r\n";
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
	// 方面IDを作成する
	$selid = 'p_direction_id' . $no;
	print "	<select name='" . $selid . "' id='" . $selid . "' >\r\n";

	print "		<option value='-1'>方面&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";

	// 方面を初期表示します。
	init_disp_direction($d_id, $d_name, $c_id, $no);

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
	global $pi;

	$indx = (int)$no - 1;
	// 国・都道府県を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->country_prefecture_id[$indx]))
	{
		$tmp_country = $pi->registration_classifications->country_prefecture_id[$indx];
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
					//print "	<option value='" . $cp_id[$i][$j][$k] . "'>" . dp(santen_reader($cp_name[$i][$j][$k], 16)) . "</option>\r\n";
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
	// 国・都道府県IDを作成する
	$selid = 'p_country_prefecture_id' . $no;

	print "	<select name='" . $selid . "' id='" . $selid . "' >\r\n";
	print "		<option value='-1'>国・都道府県&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";

	// 国・都道府県を初期表示します。
	init_disp_country_prefecture($cp_id, $cp_name, $d_id, $no);

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
	global $pi;

	$indx = (int)$no - 1;
	// 地名を登録した場合、DBから取得する
	if (!empty($pi->registration_classifications->place_id[$indx]))
	{
		$tmp_place = $pi->registration_classifications->place_id[$indx];
	} else {
		$tmp_place = -1;
	}

	if ($tmp_place == -1) return;

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
						//print "		<option value='" . $p_id[$i][$j][$k][$l] . "'>" . dp(santen_reader($p_name[$i][$j][$k][$l], 10)) . "</option>\r\n";
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
	// 地名IDを作成する
	$selid = 'p_place_id' . $no;
	print "	<select name='". $selid . "' id='" . $selid . "' >\r\n";
	//print "		<option value='-1'>お選びください</option>\r\n";
	print "		<option value='-1'>都市&nbsp;&nbsp;&nbsp;&nbsp;</option>\r\n";

	// 地名を初期表示します。
	init_disp_place($p_id, $p_name, $cp_id, $no);
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
	global $pi,$db_link,$p_photo_id;

	// PhotoIDよりキーワードーを取得する
	$pi->get_keyword_str($db_link, $p_photo_id);
	$kwd_a = array();
	// スペース区切りの文字列を配列にします。
	$kwd_a = explode(" ", $pi->keyword_str);

	$dc = count($cg_id);
	// カテゴリー（親）より繰り返し
	for ($i=0;$i < $dc;$i++)
	{
		print "<li class='reg_list reg_clear'> <em>";
		// カテゴリーIDを作成する
		$id = "ct_" . $i . "_0";
		// 登録したキーワードはキーワードの配列に存在した場合
		if (check_array_index($kwd_a,dp($cg_name[$i][0])) != -1)
		{
			print "<input id='".$id."' type='checkbox' category='0' value='".dp($cg_name[$i][0])."' onclick='change_category(\"$id\");' checked=\"checked\"/>&nbsp;".dp($cg_name[$i][0])."</em>";
		} else {
			print "<input id='".$id."' type='checkbox' category='0' value='".dp($cg_name[$i][0])."' onclick='change_category(\"$id\");' />&nbsp;".dp($cg_name[$i][0])."</em>";
		}
		print "<ul class='reg_list_child'>";

		$dc2 = count($cg_id[$i]);
		// カテゴリー（子）より繰り返し
		for($j = 1;$j < $dc2; $j++)
		{
			$s_len = mb_strlen($cg_name[$i][$j]);

			// カテゴリーIDを作成する
			$id = "ct_" . $i . "_" . $j;
			// カテゴリーの文字列のサイズは６以上の場合
			if ($s_len >= 6)
			{
				// 登録したキーワードはキーワードの配列に存在した場合
				if (check_array_index($kwd_a,dp($cg_name[$i][$j])) != -1)
				{
					print "<li class='wide140'><input id='".$id."' type='checkbox' category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");' checked=\"checked\" />&nbsp;".dp($cg_name[$i][$j])."</li>";
				} else {
					print "<li class='wide140'><input id='".$id."' type='checkbox' category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
				}
			}else
			{
				// 登録したキーワードはキーワードの配列に存在した場合
				if (check_array_index($kwd_a,dp($cg_name[$i][$j])) != -1)
				{
					print "<li><input id='".$id."' type='checkbox' category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");' checked=\"checked\" />&nbsp;".dp($cg_name[$i][$j])."</li>";
				} else {
					print "<li><input id='".$id."' type='checkbox' category='0' value='".dp($cg_name[$i][$j])."' onclick='change_category(\"$id\");'/>&nbsp;".dp($cg_name[$i][$j])."</li>";
				}
			}
		}
		print "</ul>";
		print "</li>";
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
	global $pi;

	$ed = count($c_id);
	for ($i = 0 ; $i<$ed ; $i++)
	{
		// 登録した撮影時期の「季節」はラジオボタングループに存在した場合
		if ((int)$pi->take_picture_time2_id == (int)$c_id[$i])
		{
			print "	<label><input name='rad_kisetu' type='radio' value=\"".$c_id[$i]."\" checked=\"checked\"/>".dp($c_name[$i])."</label>";
		} else {
			print "	<label><input name='rad_kisetu' type='radio' value=\"".$c_id[$i]."\" />".dp($c_name[$i])."</label>";
		}
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

	// 撮影時期の月を出力する
	if ($flg == 0)
	{
		print"	<select id='take_picture_time_id' name='take_picture_time_name'>";
	}

	// 掲載期間の月を出力する
	if ($flg == 1)
	{
		// 掲載期間の「日付指定」以外を選択した場合
		if ($pi->kikan != "shitei")
		{
			print"	<select id='take_picture_time_id' name='take_picture_time_name' onChange='calendar();' disabled=\"disabled\">";
		} else {
			print"	<select id='take_picture_time_id' name='take_picture_time_name' onChange='calendar();'>";
		}
	}

	// 掲載期間の月を出力する
	if ($flg == 1)
	{
		print "		<option value='-1'>未定</option>\r\n";
	} else {
		print "		<option value='-1'>お選びください</option>\r\n";
	}

	$ed = count($c_id);
	for ($i = 1 ; $i <= $ed ; $i++)
	{
		// 撮影時期の月を出力する
		if ($flg == 0)
		{
			// 登録した撮影時期の月はレストに存在した場合
			if ((int)$pi->take_picture_time_id == (int)$i)
			{
				print "	<option value=" .$i. " selected=\"selected\" >" . dp($c_name[$i - 1]) . "</option>\r\n";
			} else {
				print "	<option value=" .$i. ">" . dp($c_name[$i - 1]) . "</option>\r\n";
			}
		// 掲載期間の月を出力する
		} elseif ($flg == 1) {
			// 登録した掲載期間（To)から月を取得する
			$i_month = (int)substr($pi->dto,5,2);
			// ①掲載期間の「無期限」以外を選択した場合
			// ②登録した日付の「月」はレストに存在した場合
			if ($pi->kikan != "mukigen" && $i_month == (int)$i)
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
	print "		<dd>\r\n";
	print "			<textarea name=\"reg_material_txt\" id=\"reg_material\" cols=\"70\" rows=\"5\">".$pi->photo_explanation."</textarea>\r\n";
	print "		</dd>\r\n";
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
	// 掲載期間より振り替え
	switch ($pi->kikan)
	{
		// 無期限
		case 'mukigen':
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"mukigen\" checked=\"checked\" onclick='change_kikan(this);'/>無期限</label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sankagetu\" onclick='change_kikan(this);'/>3ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"hantoshi\" onclick='change_kikan(this);'/>6ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ichinen\" onclick='change_kikan(this);'/>1年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 begin
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ninen\" onclick='change_kikan(this);'/>2年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sannen\" onclick='change_kikan(this);'/>3年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 end
			break;
		// 三か月
		case 'sankagetu':
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"mukigen\" onclick='change_kikan(this);'/>無期限</label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sankagetu\" checked=\"checked\" onclick='change_kikan(this);'/>3ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"hantoshi\" onclick='change_kikan(this);'/>6ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ichinen\" onclick='change_kikan(this);'/>1年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 begin
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ninen\" onclick='change_kikan(this);'/>2年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sannen\" onclick='change_kikan(this);'/>3年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 end
			break;
		// 六か月
		case 'hantoshi':
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"mukigen\" onclick='change_kikan(this);'/>無期限</label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sankagetu\" onclick='change_kikan(this);'/>3ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"hantoshi\" checked=\"checked\" onclick='change_kikan(this);'/>6ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ichinen\" onclick='change_kikan(this);'/>1年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 begin
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ninen\" onclick='change_kikan(this);'/>2年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sannen\" onclick='change_kikan(this);'/>3年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 end
			break;
		// 一年間
		case 'ichinen':
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"mukigen\" onclick='change_kikan(this);'/>無期限</label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sankagetu\" onclick='change_kikan(this);'/>3ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"hantoshi\" onclick='change_kikan(this);'/>6ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ichinen\" checked=\"checked\" onclick='change_kikan(this);'/>1年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 begin
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ninen\" onclick='change_kikan(this);'/>2年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sannen\" onclick='change_kikan(this);'/>3年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 end
			break;
			//added by wangtongchao 2011-12-02 begin
			// 二年間
		case 'ninen':
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"mukigen\" onclick='change_kikan(this);'/>無期限</label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sankagetu\" onclick='change_kikan(this);'/>3ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"hantoshi\" onclick='change_kikan(this);'/>6ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ichinen\" onclick='change_kikan(this);'/>1年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ninen\" checked=\"checked\" onclick='change_kikan(this);'/>2年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sannen\" onclick='change_kikan(this);'/>3年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 end
			break;
			//added by wangtongchao 2011-12-02 begin
			// 三年間
		case 'sannen':
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"mukigen\" onclick='change_kikan(this);'/>無期限</label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sankagetu\" onclick='change_kikan(this);'/>3ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"hantoshi\" onclick='change_kikan(this);'/>6ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ichinen\" onclick='change_kikan(this);'/>1年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ninen\" onclick='change_kikan(this);'/>2年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sannen\" checked=\"checked\" onclick='change_kikan(this);'/>3年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 end
			break;
		// 日付指定
		default:
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"mukigen\" onclick='change_kikan(this);'/>無期限</label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sankagetu\" onclick='change_kikan(this);'/>3ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"hantoshi\" onclick='change_kikan(this);'/>6ヵ月 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ichinen\" onclick='change_kikan(this);'/>1年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 begin
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"ninen\" onclick='change_kikan(this);'/>2年間 </label>\r\n";
			print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"sannen\" onclick='change_kikan(this);'/>3年間 </label>\r\n";
			//added by wangtongchao 2011-12-02 end
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
	// 掲載期間の「日付指定」を選択した場合
	if ($pi->kikan == "shitei")
	{
		print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"shitei\" checked=\"checked\" onclick='change_kikan(this);'/>日付指定</label>\r\n";
	} else {
		print "		<label><input name=\"reg_pub_period\" type=\"radio\" value=\"shitei\" onclick='change_kikan(this);'/>日付指定</label>\r\n";
	}
	print "			<label>\r\n";
	take_picture_year();
	print "			</label>\r\n";
	print "			<label>\r\n";
	take_picture_time($take_picture_time_id, $take_picture_time_name, 1);
	print "			</label>\r\n";
	print "			<label>\r\n";
	take_pictrue_day();
	print "			</label>\r\n";
	print "	</dd>\r\n";
}

/*
 * 関数名：take_picture_year
 * 関数説明：「日付指定」の「年」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function take_picture_year()
{
	global $pi;

	// 掲載期間の「日付指定」以外を選択した場合
	if ($pi->kikan != "shitei")
	{
		//print"<select name=\"select_year\" id=\"select_year\" onChange='change_year();' disabled=\"disabled\">\r\n";
		print"<select name=\"select_year\" id=\"select_year\" disabled=\"disabled\">\r\n";
	} else {
		//print"<select name=\"select_year\" id=\"select_year\" onChange='change_year();'>\r\n";
		print"<select name=\"select_year\" id=\"select_year\" >\r\n";
	}

	// 登録した掲載期間（To)から年を取得する
	$p_d_to = (int)substr($pi->dto,0,4);
	// システム日付から年を取得する
	$now_year = (int)substr(date("Y-m-d"),0,4);
	print "		<option value='-1'>未定</option>\r\n";
	for ($i = 0; $i <= 10; $i++)
	{
		$ed_year = $now_year + $i;
		// ①掲載期間の「無期限」以外を選択した場合
		// ②登録した日付の「年」はレストに存在した場合
		if ($pi->kikan != "mukigen" && $p_d_to == (int)$ed_year)
		{
			print"	<option value=\"".$ed_year."\" selected=\"selected\" >".dp($ed_year)."</option>\r\n";
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
	global $pi;

	// 登録した掲載期間（To)から日を取得する
	$day = (int)substr($pi->dto,-2);
	// 掲載期間の「日付指定」以外を選択した場合
	if ($pi->kikan != "shitei")
	{
		print"	<select name=\"select_day\" id=\"select_day\" disabled=\"disabled\">\r\n";
	} else {
		print"	<select name=\"select_day\" id=\"select_day\">\r\n";
	}

	print "		<option value='-1'>未定</option>\r\n";
	for ($i = 1; $i <= 31; $i++)
	{
		//$s_day = DBC_SBC($i)."日";
		$s_day = $i."日";
		// ①掲載期間の「無期限」以外を選択した場合
		// ②登録した日付の「日」はレストに存在した場合
		if ($pi->kikan != "mukigen" && $day == $i)
		{
			print"		<option value=\"".$i."\" selected=\"selected\" >".dp($s_day)."</option>\r\n";
		} else {
			print"		<option value=\"".$i."\">".dp($s_day)."</option>\r\n";
		}
	}
	print"	</select>\r\n";
}

/*
 * 関数名：disp_range
 * 関数説明：「掲載可能範囲」を出力する
 * パラメタ：
 * $r_id：	　掲載可能範囲ID
 * $r_name：  掲載可能範囲
 * 戻り値：無し
 */
function disp_range($r_id,$r_name)
{
	global $pi;

	$ed = count($r_id);

	// 掲載可能範囲の初期表示フラグ
	$flg = false;
	// 掲載可能範囲より繰り返し
	for ($i=0;$i < $ed;$i++)
	{
		// 掲載可能範囲の「外部出稿条件付き」を選択した場合
		if ((int)$r_id[$i] == 3)
		{
			print"	<dd class=\"outside\">\r\n";
			// 画像を登録する時、掲載可能範囲の「外部出稿条件付き」を選択した場合
			if ((int)$pi->range_of_use_id == (int)$r_id[$i])
			{
				print"		<label><input name=\"reg_pub_possible\" type=\"radio\" value=".$r_id[$i]." checked=\"checked\" onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
				//changed by wangtongchao 2011-12-16 begin
				print"		<input name=\"reg_pub_possible_txt\" type=\"text\" id=\"reg_pub_possible_txt\" size=\"30\" onblur='return check_reg_pub_possible(this);' value='".htmlspecialchars($pi->use_condition,ENT_QUOTES,UTF-8)."'/>\r\n";
				//changed by wantongchao 2011-12-16 end
			} else {
				print"		<label><input name=\"reg_pub_possible\" type=\"radio\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
				print"		<input name=\"reg_pub_possible_txt\" type=\"text\" id=\"reg_pub_possible_txt\" size=\"30\" disabled/>\r\n";
			}
			print"	</dd>\r\n";
		} else {
			// 掲載可能範囲は初期表示ではない場合
			if ($flg == false)
			{
				print"	<dd>\r\n";
				$flg = true;
			}
			// 画像を登録する時、掲載可能範囲の「外部出稿条件付き」以外を選択した場合
			if ((int)$pi->range_of_use_id == (int)$r_id[$i])
			{
				print"		<label><input name=\"reg_pub_possible\" type=\"radio\" value=".$r_id[$i]." checked=\"checked\" onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
			} else {
				print"		<label><input name=\"reg_pub_possible\" type=\"radio\" value=".$r_id[$i]." onclick='change_range_radio(this);' />".dp($r_name[$i])."</label>\r\n";
			}
		}
	}
	// 掲載可能範囲は初期表示ではない場合
	if ($flg) print"	</dd>\r\n";
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
	// 付加条件の「要クレジット」を入力するかどうかフラグ
	$flg = false;
	print "	<dl class=\"reg_addition reg_clear required\">\r\n";
	print "		<dt>付加条件</dt>\r\n";
	if ((empty($pi->additional_constraints1) || $pi->additional_constraints1 == "") && (empty($pi->additional_constraints2) || $pi->additional_constraints2 == ""))
	{
		print "		<dd>\r\n";
		print "			<label><input name=\"reg_addition\" id=\"reg_addition2\" type=\"radio\" checked=\"checked\" value=\"2\" onclick='change_reg_addition(this);'/>なし </label>\r\n";
		print "		</dd>\r\n";
		//print "		<dd style=\"height:14px\"></dd>\r\n";
		print "		<dd class=\"outside\">\r\n";
		print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"0\" onclick='change_reg_addition(this);'/>要クレジット </label>\r\n";
		//yupengbo modify 20101208 start
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt0\" size=\"30\" />\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt1\" size=\"30\" />\r\n";
		//yupengbo modify 20101208 end
		print "		</dd>\r\n";
		print "		<dd class=\"outside\">\r\n";
		print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"1\" onclick='change_reg_addition(this);'/>要使用許可 </label>\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt2\" size=\"30\" />\r\n";
		print "		</dd>\r\n";
		print "	</dl>\r\n";
		return;
	}

	print "		<dd>\r\n";
	print "			<label><input name=\"reg_addition\" id=\"reg_addition2\" type=\"radio\" value=\"2\"  onclick='change_reg_addition(this);'/>なし </label>\r\n";
	print "		</dd>\r\n";
	//print "		<dd style=\"height:14px\"></dd>\r\n";

	$additional_constraints1 = explode('=_=',$pi->additional_constraints1);
	// 画像を登録する時、付加条件の「要クレジット」を入力した場合
	if (!empty($pi->additional_constraints1) && $pi->additional_constraints1 != "")
	{
		print "		<dd class=\"outside\">\r\n";
		//yupengbo modify 20101208 start
		//print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"0\" onclick='change_reg_addition(this);' checked=\"checked\" disabled=\"disabled\" />要クレジット </label>\r\n";
		print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"0\" onclick='change_reg_addition(this);' checked=\"checked\" />要クレジット </label>\r\n";
		//print "			<input type=\"hidden\" id=\"h_reg_addition\" name=\"h_reg_addition\" value='".$pi->additional_constraints1."' />\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt0\" size=\"30\" value='".$additional_constraints1[0]."' />\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt1\" size=\"30\" value='".$additional_constraints1[1]."' />\r\n";
		//yupengbo modify 20101208 end
		print "		</dd>\r\n";
		print "		<dd class=\"outside\">\r\n";
		print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"1\" onclick='change_reg_addition(this);' />要使用許可 </label>\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt2\" size=\"30\" />\r\n";
		print "		</dd>\r\n";
		print "	</dl>\r\n";
		return;
	} else {
		print "		<dd>\r\n";
		print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"0\" onclick='change_reg_addition(this);'/>要クレジット </label>\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt0\" size=\"30\" />\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt1\" size=\"30\" />\r\n";
		print "		</dd>\r\n";
	}

	print "		<dd class=\"outside\">\r\n";
	// 画像を登録する時、付加条件の「要使用許可」を入力した場合
	if (!empty($pi->additional_constraints2) && $pi->additional_constraints2 != "")
	{
		print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"1\" onclick='change_reg_addition(this);' checked=\"checked\" />要使用許可 </label>\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt2\" size=\"30\" value='".$pi->additional_constraints2."'/>\r\n";
	} else {
		print "			<label><input name=\"reg_addition\" type=\"radio\" value=\"1\" onclick='change_reg_addition(this);'/>要使用許可 </label>\r\n";
		print "			<input name=\"reg_addition_txt\" type=\"text\" id=\"reg_addition_txt2\" size=\"30\" />\r\n";
	}
	print "		</dd>\r\n";
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
	// 「この申請アカウント」をチェックする時
	if ((int)$pi->monopoly_use == 1)
	{
		print "			<label><input name=\"reg_account\" type=\"checkbox\" value=\"1\" checked=\"checked\" />この申請アカウント </label>\r\n";
	} else {
		print "			<label><input name=\"reg_account\" type=\"checkbox\" value=\"1\" />この申請アカウント </label>\r\n";
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
 //added by wangtongchao 2011-11-28 begin
 function disp_borrowing_ahead($b_head_id,$b_head_name)
{
	global $pi;

	$ed = count($b_head_id);

	// 写真入手元の初期表示フラグ
	$flg = false;
	for ($i=0;$i < $ed;$i++)
	{
		// 写真入手元の「その他」を選択した場合
		if ((int)$b_head_id[$i] == 2)
		{
			print"	<dd class=\"other\">\r\n";
			// 画像を登録する時に、「その他」を選択した場合
			if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i])
			{
				print"		<input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' checked=\"checked\" style=\"display:none\" />";
				//changed by wangtongchao 2011-12-16 begin
				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" size=\"30\" onblur='check_borrowing_ahead(this);' value='".htmlspecialchars($pi->content_borrowing_ahead,ENT_QUOTES,'UTF-8')."'/>\r\n";
				//changed by wangtongchao 2011-12-16 end
			} else {
				print"		<input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' checked=\"checked\" style=\"display:none\" />";
				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" size=\"30\"/>\r\n";
			}
			print"	</dd>\r\n";
		} else {
			// 写真入手元は初期表示ではない場合
			if ($flg == false)
			{
				print"	<dd>\r\n";
				$flg = true;
			}
			// 画像を登録する時に、「アマナ」を選択した場合
			if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i])
			{
				print"		<input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' style=\"display:none\" />";
			} else {
				print"		<input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' style=\"display:none\" />";
			}
		}
	}
	// 写真入手元は初期表示ではない場合
	if ($flg) print"	</dd>\r\n";
}
 //added by wangtongchao 2011-11-28 end
 //deleted by wangtongchao 2011-11-28 begin
//function disp_borrowing_ahead($b_head_id,$b_head_name)
//{
//	global $pi;
//
//	$ed = count($b_head_id);
//
//	// 写真入手元の初期表示フラグ
//	$flg = false;
//	for ($i=0;$i < $ed;$i++)
//	{
//		// 写真入手元の「その他」を選択した場合
//		if ((int)$b_head_id[$i] == 2)
//		{
//			print"	<dd class=\"other\">\r\n";
//			// 画像を登録する時に、「その他」を選択した場合
//			if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i])
//			{
//				print"		<label><input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' checked=\"checked\" />".dp($b_head_name[$i])."</label>\r\n";
//				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" size=\"30\" onblur='check_borrowing_ahead(this);' value='".$pi->content_borrowing_ahead."'/>\r\n";
//			} else {
//				print"		<label><input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//				print"		<input name=\"reg_p_obtaining_txt\" type=\"text\" id=\"reg_p_obtaining_txt\" size=\"30\" disabled/>\r\n";
//			}
//			print"	</dd>\r\n";
//		} else {
//			// 写真入手元は初期表示ではない場合
//			if ($flg == false)
//			{
//				print"	<dd>\r\n";
//				$flg = true;
//			}
//			// 画像を登録する時に、「アマナ」を選択した場合
//			if ((int)$pi->borrowing_ahead_id == (int)$b_head_id[$i])
//			{
//				print"		<label><input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' checked=\"checked\" />".dp($b_head_name[$i])."</label>\r\n";
//			} else {
//				print"		<label><input name=\"reg_p_obtaining\" type=\"radio\" value=".$b_head_id[$i]." onclick='change_obtaining_radio(this);' />".dp($b_head_name[$i])."</label>\r\n";
//			}
//		}
//	}
//	// 写真入手元は初期表示ではない場合
//	if ($flg) print"	</dd>\r\n";
//}
//deleted by wangtongchao 2011-11-28 end
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
	print "		<dd><input name=\"reg_copyright\" type=\"text\" id=\"reg_copyright\" size=\"30\" value='".$pi->copyright_owner."'/></dd>\r\n";
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
	print "		<dd><input name=\"reg_mate_mana\" type=\"text\" id=\"reg_mate_mana\" size=\"30\" value='".$pi->source_image_no."'/></dd>\r\n";
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

	print "	<dl class=\"reg_bud_number reg_clear required\">\r\n";
	print "		<dt>BUD_PHOTO番号</dt>\r\n";
	print "		<dd>\r\n";
	//changed by wangtongchao 2011-12-16 begin added  style=\"ime-mode:disabled\"
	if (!empty($pi->bud_photo_no))
	{
		print "			<label><input name=\"reg_bud_number\" type=\"radio\" value='1' checked=\"checked\" onclick=\"change_bud_number_radio(this);\"/>ある </label>\r\n";
		//changed by wangtongchao 2011-12-16 begin
		print "			<input name=\"reg_bud_number_txt\" type=\"text\" id=\"reg_bud_number_txt\" size=\"30\" onblur='check_reg_bud_number(this);' value='".$pi->bud_photo_no."' style=\"ime-mode:disabled\" />\r\n";
		//changed by wangtongchao 2011-12-16 end
	} else {
		print "			<label><input name=\"reg_bud_number\" type=\"radio\" value='1' onclick=\"change_bud_number_radio(this);\"/>ある </label>\r\n";
		print "			<input name=\"reg_bud_number_txt\" type=\"text\" id=\"reg_bud_number_txt\" size=\"30\" onblur='check_reg_bud_number(this);' disabled=\"disabled\" style=\"ime-mode:disabled\" />\r\n";
	}
	//changed by wangtongchao 2011-12-16 end
	print "		</dd>\r\n";
	print "		<dd class=\"other\">\r\n";
	if (empty($pi->bud_photo_no))
	{
		print "			<label><input name=\"reg_bud_number\" type=\"radio\" value='0' checked=\"checked\" onclick=\"change_bud_number_radio(this);\"/>なし </label>\r\n";
	} else {
		print "			<label><input name=\"reg_bud_number\" type=\"radio\" value='0' onclick=\"change_bud_number_radio(this);\"/>なし </label>\r\n";
	}
	print "		</dd>\r\n";
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
	print "			<dd> 部署名\r\n";
	print "				<input name=\"post_name\" type=\"text\" id=\"post_name\" size=\"15\" value='".$pi->customer_section."'/>\r\n";
	print "				名前\r\n";
	print "				<input name=\"first_name\" type=\"text\" id=\"first_name\" size=\"30\" value='".$pi->customer_name."'/>\r\n";
	print "			</dd>\r\n";
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
	global $pi,$s_login_id,$s_login_name;

	print "		<dl class=\"reg_apply reg_clear\">\r\n";
	print "			<dt>登録申請者</dt>\r\n";
	print "			<dd>\r\n";
	print "				<input name=\"reg_apply_id\" type=\"hidden\" id=\"reg_apply_id\" size=\"30\" value='".$pi->registration_account."' disabled=\"disabled\" />\r\n";
	print "				<input name=\"reg_apply\" type=\"text\" id=\"reg_apply\" size=\"30\" value='".$pi->registration_person."' disabled=\"disabled\" />\r\n";
	print "				<input name=\"reg_apply_date\" type=\"hidden\" id=\"reg_apply_date\" value='".$pi->register_date."' />\r\n";
	print "			</dd>\r\n";
	print "		</dl>\r\n";
	print "		<dl class=\"reg_permission reg_clear\">\r\n";
	print "			<dt>登録許可者</dt>\r\n";
	print "			<dd>\r\n";
	//print "				<input name=\"reg_permission_id\" type=\"hidden\" id=\"reg_permission_id\" size=\"30\" value='".$pi->permission_account."' disabled=\"disabled\"/>\r\n";
	//print "				<input name=\"reg_permission\" type=\"text\" id=\"reg_permission\" size=\"30\" value='".$pi->permission_person."' disabled=\"disabled\"/>\r\n";
	print "				<input name=\"reg_permission_id\" type=\"hidden\" id=\"reg_permission_id\" value=\"".$s_login_id."\" size=\"30\" />\r\n";
	print "				<input name=\"reg_permission_hidden\" type=\"hidden\" id=\"reg_permission_hidden\" value=\"".$s_login_name."\" size=\"30\" />\r\n";
	print "				<input name=\"reg_permission\" type=\"text\" id=\"reg_permission\" size=\"30\" value=".$s_login_name." disabled=\"disabled\"/>\r\n";
	print "			</dd>\r\n";
	print "		</dl>\r\n";
}

/*
 * 関数名：disp_note
 * 関数説明：「備考」を出力する
 * パラメタ：無し
 * 戻り値：無し
 */
function disp_note()
{
	global $pi;

	print "<p class=\"reg_remarks\"><textarea name=\"reg_remarks\" id=\"textarea2\" cols=\"70\" rows=\"5\">".dp($pi->note)."</textarea></p>\r\n";
}

/*
 * show not permis selection
 * modify by jinxin
 * $ps_id is not used
 */
function disp_nopermis($ps_id,$ps_name){
	global $pi;
	$ed = count($ps_id);
	for($i=0;$i < $ed;$i++){
		if(($i+1)%6 == 0){
			print "<br /><label><input name='nopermisnote' type='radio' value=\"".$ps_name[$i]."\" onclick=\"change_noper_reason(this);\"/>".$ps_name[$i]."</label>";
		}else{
			print"<label><input name='nopermisnote' type='radio' value=\"".$ps_name[$i]."\" onclick=\"change_noper_reason(this);\"/>".$ps_name[$i]."</label>";
		}
	}
	print "<label><input name='nopermisnote' type='radio' value=\"7\" onclick=\"change_noper_reason(this);\" />その他 </label><input id=\"nopermis_rea_txt\" name=\"enterreason\" type=\"text\" size=\"40\" disabled=true/><input name=\"nopermis\" id=\"nopermis\" type=\"hidden\" value=\"\" />";
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
	$file = fopen("./log/delete_image.log","a+");
	fwrite($file,$logmsg);
	fclose($file);
}
/*
 * 関数名：write_log_nopermit
 * add by jinxin 2012/02/09
 */
function write_log_nopermit($msg){
	//added by wangtongchao 2012-02-24 begin +".date("Ymd")."
	$file = fopen("./log/nopermit_image".date("Ymd").".log","a+");
	//added by wangtongchao 2012-02-24 end
	fwrite($file,$msg);
	fclose($file);
}

/**
 * Write register image error log
 */
function write_error_log($errmsg)
{
    $logfileName = date("Ymd").'_register_image.log';
    $file = fopen("./log/".$logfileName,"a+");
    fwrite($file,date("Y-m-d H:i:s  ").$errmsg."\r\n");
    fclose($file);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
span.guard{
    position:absolute;
    display:block;
    width:200px;
    height:150px;
    background-image:url(./noimage.gif);
}
#nopermisdiv{
	display:none;
}
#nopermisdivli{
	display:none;
}
</style>
<title>登録修正画面｜BUD PHOTO WEB</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script type="text/javascript" src="js/common.js"  charset="utf-8"></script>
<script type='text/javascript' src='./js/dateformat/dateformat.js' charset="utf-8"></script>
<script type="text/javascript" src="./js/ConnectedSelect/ConnectedSelect2.js" charset="utf-8"></script>
<script type="text/javascript">
<?php
if (!empty($GLOBALS["p_photo_id"]))
{
	print "var sp_id = ".$GLOBALS["p_photo_id"].";\r\n";
} else {
	print "var sp_id = '';";
}
?>
var dateFormat = new DateFormat("yyyy-MM-dd");
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
		document.register_image_edit.select_year.selectedIndex = 0;
		// 日付の「日」のインデックスを設定する
		document.register_image_edit.select_day.selectedIndex = 0;
		// 日付の「月」のインデックスを設定する
		if (obj_month) obj_month.selectedIndex = 0;

		// 日付の「年」は有効になる
		document.register_image_edit.select_year.disabled = false;
		// 日付の「日」は有効になる
		document.register_image_edit.select_day.disabled = false;
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
		document.register_image_edit.select_year.value = yr;
		// 月を設定する
		if (obj_month) obj_month.value = tdt.getMonth() + 1;
		// 日を設定する
		document.register_image_edit.select_day.value = tdt.getDate();
	}
	else
	{
		// 年を設定する
		document.register_image_edit.select_year.value = -1;
		// 月を設定する
		if (obj_month) obj_month.value = -1;
		// 日を設定する
		document.register_image_edit.select_day.value = -1;
	}

	// 掲載期間（To）を設定する。
	disp_to =  dateFormat.format(tdt);
	document.register_image_edit.p_dto.value = disp_to;

	// 年を無効になる
	document.register_image_edit.select_year.disabled = true;
	// 日を無効になる
	document.register_image_edit.select_day.disabled = true;
	// 月を無効になる
	if (obj_month) obj_month.disabled = true;
}

/*
 * 関数名：check_reg_pub_possible
 * 関数説明：外部出稿条件付きを入力するかどうかチェックする
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function check_reg_pub_possible(obj)
{
	var chks = document.getElementsByName("reg_pub_possible");
	// 掲載可能範囲の「外部出稿条件付き」を選択し、条件を入力しない場合、エラーメッセージを出力する
	if (obj.value == null || obj.value.length == 0)
	{
		alert("外部出稿条件付きを入力してください。");
		if (chks) chks[2].checked = true;
		obj.disabled = false;
		obj.focus();
		return false;
	}
	return true;
}

/*
 * 関数名：change_reg_addition
 * 関数説明：付加条件の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_reg_addition(obj)
{
	var key = "reg_addition_txt";
	var objs_txt = document.getElementsByName(key);
	var indx1 = parseInt(obj.value);
	var indx2 = null;
	// 付加条件の「要使用許可」を選択する時
	if (indx1 == 1)
	{
		// 要クレジットテキストボックス
		var tmpobj = document.getElementById("reg_addition_txt0");
		tmpobj.disabled = true;
		tmpobj.value = "";
		var tmpobj = document.getElementById("reg_addition_txt1");
		tmpobj.disabled = true;
		tmpobj.value = "";

		// 要使用許可テキストボックス
		var tmpobj = document.getElementById("reg_addition_txt2");
		tmpobj.disabled = false;
		tmpobj.value = "";
	} else if (indx1 == 0) {
		// 要クレジットテキストボックス
		var tmpobj = document.getElementById("reg_addition_txt0");
		tmpobj.disabled = false;
		tmpobj.value = "";
		var tmpobj = document.getElementById("reg_addition_txt1");
		tmpobj.disabled = false;
		tmpobj.value = "";

		// 要使用許可テキストボックス
		var tmpobj = document.getElementById("reg_addition_txt2");
		tmpobj.disabled = true;
		tmpobj.value = "";
	} else {
		// 要クレジットテキストボックス
		var tmpobj = document.getElementById("reg_addition_txt0");
		tmpobj.disabled = true;
		tmpobj.value = "";
		var tmpobj = document.getElementById("reg_addition_txt1");
		tmpobj.disabled = true;
		tmpobj.value = "";

		// 要使用許可テキストボックス
		var tmpobj = document.getElementById("reg_addition_txt2");
		tmpobj.disabled = true;
		tmpobj.value = "";
	}
}

/*
 * 関数名：change_reg_situation
 * 関数説明：掲載状況の選択の処理
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function change_reg_situation(obj)
{
	if (obj)
	{
		var indx = obj.selectedIndex;
		var tmp_obj = document.getElementById("reg_photo_mno");	// 管理番号

		//modify by jinxin 2012/02/06 start
		var nopermisdiv = document.getElementById("nopermisdiv");
		var nopermisdivli = document.getElementById("nopermisdivli");
		var changedivli = document.getElementById("changedivli");
		if(indx == 2){
			if(nopermisdiv){
				nopermisdiv.style.display= "block";
				nopermisdivli.style.display= "block";
				changedivli.style.display = "none";
			}else{
				alert("ページは全てロードされた後で操作してください。");
				var defaultindex = <?php echo (int)($pi->publishing_situation_id - 1);  ?>;
				document.getElementById("reg_situation").options[defaultindex].selected = true;
			}
		}else{
			if(nopermisdiv){
				nopermisdiv.style.display= "none";
				nopermisdivli.style.display= "none";
				changedivli.style.display = "block";
			}
		}

		//modify by jinxin 2012/02/06 end
		if (tmp_obj)
		{
			if (indx != 1)
			{
				tmp_obj.disabled = true;
			// 掲載状況の「掲載許可」を選択する時
			} else {
				tmp_obj.disabled = false;
				tmp_obj.focus();
			}
		}
	}
}

/*
 * 関数名：check_borrowing_ahead
 * 関数説明：写真入手元の「その他」を選択したテキストボックスのチェック
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function check_borrowing_ahead(obj)
{
	// 写真入手元のコントロールを取得する
	var reg_p_obtainings = document.getElementsByName("reg_p_obtaining");
	var msg = "";
	if (reg_p_obtainings)
	{
		var reg_p_obtaining = reg_p_obtainings[1];
		// 写真入手元を入力しない場合、エラーメッセージを出力する
		if ((obj.value.length == 0 || obj.value == null) && reg_p_obtaining.checked)
		{
			reg_p_obtaining.checked = true;
			obj.disabled = false;
			alert("写真入手元を入力してください。");
			obj.focus();
			return false;
		}
	}
	return true;
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
 * 関数名：check_reg_bud_number
 * 関数説明：BUD_PHOTO番号の「ある」を選択したテキストボックスのチェック
 * パラメタ：obj:コントロール
 * 戻り値：無し
 */
function check_reg_bud_number(obj)
{
	// BUD_PHOTO番号のコントロールを取得する
	var reg_bud_numbers = document.getElementsByName("reg_bud_number");
	var msg = "";
	if (reg_bud_numbers)
	{
		var reg_bud_number = reg_bud_numbers[0];
		// BUD_PHOTO番号の「ある」を選択した場合
		if (reg_bud_number.checked == true)
		{
			msg = "BUD_PHOTO番号を入力してください。";
		}
		// BUD_PHOTO番号を入力しない場合、エラーメッセージを出力する
		if (obj.value.length == 0 || obj.value == null)
		{
			reg_bud_number.checked = true;
			obj.disabled = false;
			alert(msg);
			obj.focus();
		//yupengbo add 2011/12/16 start
		} else {
			var patrn = /[a-zA-Z0-9_\.\-]$/ig;
			if(!patrn.exec(obj.value))
			{
				alert("BUD_PHOTO番号を確認してください。");
				TimeID=setTimeout("setFocus('"+ obj.name + "')",100);
				return false;
			}
		}
		//yupengbo add 2011/12/16 end
	}
}

/*
 * 関数名：issbccase
 * 関数説明：全角文字列のチェック
 * パラメタ：source:元の文字列
 * 戻り値：全角文字あるかどうか　「true」、「false」
 */
function issbccase(source)
{
	if (source=="") return true;
	var reg = /^([\u4E00-\u9FA5]|[\uFE30-\uFFA0])*$/gi;
	if (reg.test(source)) return false;
	else return true;
}

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
	obj_day.selectedIndex = 0;
	if (obj_day)
	{
		// アイテムのクリアー
		clearItem(obj_day);
		// アイテムの追加
		addItem(obj_day,ed);
	}
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

		var obj_regs = document.getElementsByName("reg_addition");
		var obj_txts = document.getElementsByName("reg_addition_txt");
		//var obj_hiddlen = document.getElementById("h_reg_addition"); //yupengbo comment 20111202

		var url_str = "";
		if (obj_regs && obj_txts)
		{
//yupengbo comment 20111202 start
//			// ①付加条件の「要クレジット」を選択する
//			// ②「要クレジット」のテキストボックスの状態は無効です。
//			if (obj_regs[1].checked == true && obj_txts[0].disabled == true)
//			{
//				url_str = "&reg_addition0=" +encodeURIComponent(obj_hiddlen.value)+"=_="+encodeURIComponent(obj_txts[1].value);;	// 要クレジット
//				url_str = url_str + "&reg_addition1=" + "";					// 要使用許可
//			} else if (obj_regs[2].checked == true && obj_txts[2].disabled == true) {
//				url_str = "&reg_addition0=" + encodeURIComponent(obj_txts[0].value)+"=_="+encodeURIComponent(obj_txts[1].value);			// 要クレジット
//				url_str = url_str + "&reg_addition1=" + encodeURIComponent(obj_txts[1].value);	// 要使用許可
//			}
//yupengbo comment 20111202 end
//yupengbo add 20111202 start
			if (obj_regs[1].checked == true)
			{
				url_str = "&reg_addition0=" +encodeURIComponent(obj_txts[0].value)+"=_="+encodeURIComponent(obj_txts[1].value);	// 要クレジット
				url_str = url_str + "&reg_addition1=" + "";					// 要使用許可
			} else if (obj_regs[2].checked == true) {
				url_str = "&reg_addition0=" + "";	// 要クレジット
				url_str = url_str + "&reg_addition1=" + encodeURIComponent(obj_txts[2].value);;					// 要使用許可
			}
//yupengbo add 20111202 end
		}
		url_str = url_str + "&p_photo_id=" + sp_id;
		document.register_image_edit.action = "./register_image_edit.php?p_action=update&time2=" + encodeURIComponent(obj_month.value) + url_str;
		document.register_image_edit.submit();
	}
}

/*
 * 関数名：delete_record
 * 関数説明：画像の削除
 * パラメタ：無し
 * 戻り値：無し
 */
function delete_record()
{
	var ret = confirm("この画像を完全に削除しますか?");
	if (ret)
	{
		var pid = document.getElementById("photo_id").value;
		var d_p_mno = document.getElementById("hidreg_photo_mno").value;
		var url = "./register_image_edit.php?p_action=delete&p_photo_id="+pid+"&del_photo_mno="+d_p_mno;
		parent.bottom.location.href = url;
	}
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

//yupengbo add 2011/11/16 start
function check_reg_photo_mno()
{
	//画像管理番号
	var reg_situation = document.getElementById("reg_situation");
	if (reg_situation)
	{
		if (reg_situation.selectedIndex == 1)
		{
			var p_mno = document.getElementById("reg_photo_mno");
			if (p_mno != null)
			{
				if (p_mno.value == null || p_mno.value == "")
				{
					alert('画像管理番号を入力してください。\r\n');
					p_mno.focus();
					return false;
				//yupengbo add 2011/12/16 start
				} else {
					var patrn = /[a-zA-Z0-9_]$/ig;
					if(!patrn.exec(p_mno.value))
					{
						alert("画像管理番号を確認してください。");
						TimeID=setTimeout("setFocus('reg_photo_mno')",100);
						return false;
					}
				}
				//yupengbo add 2011/12/16 end
			}
		}
	}
}
//yupengbo add 2011/11/16 end

/*
 * 関数名：check_input_value
 * 関数説明：更新する時の入力チェック
 * パラメタ：無し
 * 戻り値：無し
 */
function check_input_value()
{

	//画像の名前のチェック
	var p_name = document.register_image_edit.reg_subject;
	if (p_name.value.length == 0)
	{
		alert('画像の名前を入力してください。\r\n');
		p_name.focus();
		return false;
	}

	//画像管理番号
	var reg_situation = document.getElementById("reg_situation");
	if (reg_situation)
	{
		if (reg_situation.selectedIndex == 1)
		{
			var p_mno = document.getElementById("reg_photo_mno");
			if (p_mno != null)
			{
				if (p_mno.value == null || p_mno.value == "")
				{
					alert('画像管理番号を入力してください。\r\n');
					p_mno.focus();
					return false;
				//yupengbo add 2011/12/16 start
				} else {
					var patrn = /[a-zA-Z0-9_]$/ig;
					if(!patrn.exec(p_mno.value))
					{
						alert("画像管理番号を確認してください。");
						TimeID=setTimeout("setFocus('reg_photo_mno')",100);
						return false;
					}
				}
				//yupengbo add 2011/12/16 end
			}
		}
	}

	var p_mno = document.getElementById("reg_photo_mno");
	if (p_mno != null)
	{
		var flg = issbccase(p_mno.value);
		if (flg == false)
		{
			alert('画像管理番号は半角文字を入力してください。\r\n');
			p_mno.focus();
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
							if (obj_p_c.value == obj_p_c2.value     &&
							    obj_p_d.value == obj_p_d2.value     &&
							    obj_p_c_p.value == obj_p_c_p2.value &&
							    obj_p_p.value == obj_p_p2.value )
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

	// 掲載期間（To)のコンボボックス、年・月・日→YYYY-MM-DDにします。
	// 年
	idx = document.register_image_edit.select_year.selectedIndex;
	var p_year = document.register_image_edit.select_year.options[idx].value;

	// 月
	var objs_month = document.getElementsByName("take_picture_time_name");
	if (objs_month) var obj_month = objs_month[1];
	idx = obj_month.selectedIndex;
	var p_month = obj_month.options[idx].value;

	// 日
	idx = document.register_image_edit.select_day.selectedIndex;
	var p_day = document.register_image_edit.select_day.options[idx].value;

	// 年月日をチェックします。
	// 無期限の場合は、日付のチェックを行いません。
	if (document.register_image_edit.reg_pub_period[0].checked != true)
	{
		if(check_date(p_year, p_month, p_day) != 0)
		{
			alert("正しい日付ではありません。");
			document.register_image_edit.select_year.focus();
			return false;
		}

		if(check_date_range(p_year, p_month, p_day) != true)
		{
			alert("正しい日付範囲ではありません。");
			document.register_image_edit.select_year.focus();
			return false;
		}

		// 年月日を設定します。
		var fdt = new Date();
		var tdt = new Date(p_year, p_month - 1, p_day);

		// 掲載期間（To）を設定します。
		disp_to = dateFormat.format(tdt);
		document.register_image_edit.p_dto.value = disp_to;
	}
	else
	{
		// 無期限の場合の掲載期間（To）を設定します。
		document.register_image_edit.p_dto.value = "2100-01-01";
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

				if (reg_bud_numbers[0])
				{
					if (reg_bud_numbers[0].checked)
					{
						var obj_red = document.getElementById("reg_bud_number_txt");
						if (obj_red.value.length <= 0)
						{
							alert("BUD_PHOTO番号を入力してください。");
							obj_red.focus();
							return false;
						}
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

	var reg_p_obtaining_txt = document.getElementById("reg_p_obtaining_txt");
	var ok_flg = check_borrowing_ahead(reg_p_obtaining_txt);
	if (ok_flg == false)
	{
		return false;
	}

	var objs1 = document.getElementsByName("reg_addition");
	var objs2 = document.getElementsByName("reg_addition_txt");
	if (objs1)
	{
		if (objs1[1].checked && objs1[1].disabled == false)
		{
			// 要クレジット条件を入力しない場合、エラーメッセージを出力する
			if ((objs2[0].value.length == 0 || objs2[0].value == null)&&(objs2[1].value.length == 0 || objs2[1].value == null))
			{
				alert("要クレジット条件を入力してください。");
				objs2[1].focus();
				return false;
			//added by yupengbo 2011-12-02 begin
			} else {
				if(calcUTFByte(objs2[0].value) > 42)
				{
					alert("要クレジットの内容は全角２１または半角４２以内の文字を入力してください。");
					objs2[0].focus();
					return false;
				}
				if(calcUTFByte(objs2[1].value) > 42)
				{
					alert("要クレジットの内容は全角２１または半角４２以内の文字を入力してください。");
					objs2[1].focus();
					return false;
				}
			}
			//added by yupengbo 2011-12-02 end
		}

		if (objs1[2].checked)
		{
			// 許可条件を入力しない場合、エラーメッセージを出力する
			//changed by wangtongchao 2011-12-19 begin
			//if ((objs2[1].value.length == 0 || objs2[1].value == null)&&(objs2[1].value.length == 0 || objs2[1].value == null))
			if (objs2[2].value.length == 0 || objs2[2].value == null)
			//changed by wangtongchao 2011-12-19 end
			{
				alert("要使用許可を入力してください。");
				obj.focus();
				return false;
			}
		}
	}

	var objtxt = document.getElementById("reg_pub_possible_txt");
	var chks = document.getElementsByName("reg_pub_possible");
	if (chks[2].checked)
	{
		var ok_flg = check_reg_pub_possible(objtxt);
		if (ok_flg == false)
		{
			return false;
		}
	}


	//if not permission seasons has been chosen
	//jinxin 2012-02-09 modify start
	var reg_situation = document.getElementById("reg_situation");
	if (reg_situation)
	{
		if (reg_situation.selectedIndex == 2)
		{
			var obj = document.getElementsByName("nopermisnote");
			var submit_input = document.getElementById("nopermis");
			var frag = false;
			for(var i = 0;i<obj.length;i++){
				if(obj[i].checked == true){
					frag = true;
//					alert(obj[i].value);
					if(i == 6){
						var txt_value = document.getElementById("nopermis_rea_txt").value;
						if(trim(txt_value).length <= 0){
							alert("不許可理由を入力してください。");
							obj_txt.focus();
							return false;
						}
						submit_input.value = txt_value;
					}else{
						submit_input.value = obj[i].value;
					}
				}else{
					continue;
				}
			}
//			alert(submit_input.value);
			if(frag){
				return true;
			}else{
				alert("不許可理由を選択してください");
				return false;
			}
		}
	}

	//jinxin 2012-02-09 modify end
	//版権所有者のチェック
//	var s_reg_copyright = document.register_image_edit.reg_copyright;
//	if (s_reg_copyright.value.length == 0)
//	{
//		alert('版権所有者を入力してください。\r\n');
//		s_reg_copyright.focus();
//		return false;
//	}

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
	document.register_image_edit.p_keyword_str.value = keyword_str;
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
	if(obj_frame) <?php
						$s_r_d_id = (int)$pi->registration_division_id;
						if($s_r_d_id==4||$s_r_d_id==3)
						{echo "obj_frame.style.height = '2830px';";}
						else
						{echo "obj_frame.style.height = '2700px';";}
					?>

	//IE6.0の場合
	if (isIE6())
	{
		var obj_frame = top.document.getElementById('iframe_bottom');
		if(obj_frame) obj_frame.style.height = 2800;
	} else {
		var div_obj = document.getElementById("div_classification2");
		if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 250;

		var div_obj = document.getElementById("div_classification3");
		if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 300;

		var div_obj = document.getElementById("div_classification4");
		if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 350;

		var div_obj = document.getElementById("div_classification5");
		if (div_obj.style.display == "block") obj_frame.style.height = parseInt(obj_frame.style.height) + 400;
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
	document.register_image_edit.p_dfrom.value = disp_from;

}

//xu add it on 2010-11-30 start
function show_adv()
{
	var obj_frame = top.document.getElementById('iframe_bottom');
	var div_obj = document.getElementById("div_show_adv");
	var div_obj1 = document.getElementById("div_show_url");
	div_obj1.style.display = "none";
	div_obj.style.display = "block";
	obj_frame.style.height = "2830px";
}

function show_url()
{
	var obj_frame = top.document.getElementById('iframe_bottom');
	var div_obj = document.getElementById("div_show_adv");
	var div_obj1 = document.getElementById("div_show_url");
	div_obj1.style.display = "block";
	div_obj.style.display = "none";
	obj_frame.style.height = "2830px";
}

function show_none()
{
	var obj_frame = top.document.getElementById('iframe_bottom');
	var div_obj = document.getElementById("div_show_adv");
	var div_obj1 = document.getElementById("div_show_url");
	div_obj1.style.display = "none";
	div_obj.style.display = "none";
	obj_frame.style.height = "2700px";
}
//xu add it on 2010-11-30 end

//jinxin 2012-02-09 modify start
function change_noper_reason(obj){
	//alert(obj.value);nopermis_rea_txt
	var key = "nopermis_rea_txt";
	var obj_txt = document.getElementById(key);
	//if choose the other reason,the input is able else if disabled
	if(parseInt(obj.value) == 7){
		//disabled is false
		if(obj_txt){
			obj_txt.disabled = false;
			obj_txt.value = "";
		}
	}else{
		if(obj_txt){
			obj_txt.disabled = true;
			obj_txt.value="";

		}
	}
}

//jinxin 2012-02-09 modify end

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
			<?php  disp_image(); ?>
			<div class="reg_file_subject">
				<?php  disp_photo_mno(); ?>
				<dl class="reg_situation reg_clear">
					<dt>掲載状況</dt>
					<dd><?php  disp_publishing_situation($p_situation_id,$p_situation_name); ?></dd>
				</dl>
				<?php  disp_photo_name(); ?>
			</div>
			<dl class="reg_division reg_clear  required">
				<dt>登録区分</dt>
				<dd><?php  registration_division($registration_id, $registration_name); ?></dd>
			</dl>
			<dl id="div_show_url" class="reg_division reg_clear" style="<?php $s_r_d_id = (int)$pi->registration_division_id; if($s_r_d_id==4){echo "display:block;";}else{echo "display:none;";}?>">
				<dt>ページURL</dt>
				<?php  if($initflg != 1){ ?>
					<dd><textarea name="photo_url" id="photo_url" cols="70" rows="5"><?php  echo $pi->photo_url; ?></textarea></dd>
				<?php  }else{ ?>
					<dd><textarea name="photo_url" id="photo_url" cols="70" rows="5"></textarea></dd>
				<?php  } ?>
			</dl>
			<dl id="div_show_adv" class="reg_division reg_clear" style="<?php $s_r_d_id = (int)$pi->registration_division_id; if($s_r_d_id==3){echo "display:block;";}else{echo "display:none;";}?>">
				<dt>元画像番号</dt>
				<?php  if($initflg != 1){ ?>
					<dd><textarea name="photo_org_no" id="photo_org_no" cols="70" rows="5"><?php  echo $pi->photo_org_no; ?></textarea></dd>
				<?php  }else{ ?>
					<dd><textarea name="photo_org_no" id="photo_org_no" cols="70" rows="5"></textarea></dd>
				<?php  } ?>
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
				<dd class="mounth">
					<label><?php  take_picture_time($take_picture_time_id, $take_picture_time_name, 0); ?></label>
				</dd>
			</dl>
			<?php  disp_photo_explanation(); ?>
		</div>
		<div>
			<h2>掲載条件</h2>
			<dl class="reg_pub_period reg_clear reg_list_none_top required">
				<dt>掲載期間</dt>
				<?php  disp_kikan(); ?>
				<?php  disp_kikan2(); ?>
				<input type="hidden" id="hidden_dfrom" name="hidden_dfrom" value="<?php echo $pi->get_dfrom_date_forhidden($db_link,$p_photo_id);?>" />
			</dl>
			<dl class="reg_pub_possible reg_clear required">
				<dt>掲載可能範囲</dt>
				<?php  disp_range($range_id,$range_name); ?>
			</dl>
			<?php  disp_additional_constraints(); ?>
			<?php  disp_monopoly_use(); ?>
		</div>
		<div>
			<h2>版権情報</h2>
			<dl class="reg_p_obtaining reg_clear reg_list_none_top required">
				<dt>写真入手元</dt>
				<?php  disp_borrowing_ahead($borrow_id,$borrow_name) ?>
			</dl>
			<?php  disp_copyright_owner(); ?>
			<?php  disp_source_image_no(); ?>
			<?php  disp_bud_photo_no(); ?>
		</div>
		<div>
			<h2>登録情報</h2>
			<?php  disp_customer(); ?>
			<?php  disp_registration(); ?>
		</div>
		<div>
			<h2>備考</h2>
			<?php  disp_note(); ?>
		</div>
		<div id="nopermisdiv">
			<h2>不許可</h2>
			<dl class="reg_pub_period reg_clear reg_list_none_top required">
				<dt>不許可理由</dt>
				<dd><?php  disp_nopermis($p_nopermis_id,$p_nopermis_name); ?></dd>
			</dl>
		</div>
		<div class="reg_search_btn">
			<ul>
				<li id="changedivli" class="bt_reg_change"><a href="#" onclick="form_submit();return false;" title="登録変更">登録確認</a></li>
				<?php
				$flg="";
				$pi->get_photo_server_flag($db_link,$p_photo_id,$flg);
				if((int)$flg==1)
				{
				?>
					<li class="bt_reg_delete"><a href="#" onclick="delete_record();" title="削除">削除</a></li>
				<?php
				}
				?>
				<li id="nopermisdivli" class="bt_reg_nopermis"><a href="#" onclick="form_submit();return false;" title="不許可">不許可</a></li>
			</ul>
			<p>※続けて画像を登録申請する場合は、前の入力内容に上書き、もしくはクリアして入力してください</p>
		</div>
		<input type="hidden" id="img_url0" name="img_url0" value="<?php echo $pi->up_url[0]?>" />
		<input type="hidden" id="img_url1" name="img_url1" value="<?php echo $pi->up_url[1]?>" />
		<input type="hidden" id="img_url2" name="img_url2" value="<?php echo $pi->up_url[2]?>" />
		<input type="hidden" id="img_url3" name="img_url3" value="<?php echo $pi->up_url[3]?>" />
		<input type="hidden" id="img_url4" name="img_url4" value="<?php echo $pi->up_url[4]?>" />
		<input type="hidden" id="p_dfrom" name="p_dfrom" value="" />
		<input type="hidden" id="p_dto" name="p_dto" value="" />
		<input type="hidden" id="p_keyword_str" name="p_keyword_str" value="" />
		<?php  if(!empty($pi->photo_mno)){ ?>
			<?php
				$tmp = "__".$pi->photo_mno;
				$ipos = (int)strpos($tmp,"申請中");
				if($ipos > 0){
					if (strpos($pi->photo_mno,$pi->ext) != false)
					{
						print "<input type=\"hidden\" id=\"photo_mno\" value=\"".$pi->photo_mno."\" name=\"photo_mno\" />";
					} else {
						print "<input type=\"hidden\" id=\"photo_mno\" value=\"".$pi->photo_mno.$pi->ext."\" name=\"photo_mno\" />";
					}
				}else{
			?>
				<input type="hidden" id="photo_mno" value="<?php echo $pi->photo_mno?>" name="photo_mno" />
			<?php  } ?>
		<?php  }else{ ?>
			<input type="hidden" id="photo_mno" value="" name="photo_mno" />
		<?php  } ?>
		<input type="hidden" id="photo_id" value="<?php echo $p_photo_id; ?>" name="photo_id" />
		<input type="hidden" id="publishing_situation_id" name="publishing_situation_id" value="<?php echo $pi->publishing_situation_id?>" />
	</div>
</div>
</div>
</form>
</body>

</html>
