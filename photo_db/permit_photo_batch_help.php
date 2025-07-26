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
	
	$pi = new PhotoImageDB ();
	$db_link = db_connect();
	//added by wangtongchao 2012-02-15 begin
	$err_count = 0;
	//added by wangtongchao 2012-02-15 end
	if(defined('REGPHOTOMNO'))
	{
		if(isset($_COOKIE['photo_id']))
		{
			$sele_data = explode(",",$_COOKIE['photo_id']);
		}
		setcookie("photo_id","",time() - 3600);
		if(count($sele_data)>0)
		{
			foreach($sele_data as $val)
			{
				permit_batch($val);
			}
		}
		//added by wangtongchao 2012-02-15 begin
		if($err_count>0)
		{
			//modified by wangtongchao 2012-02-28 begin
			echo $err_count;
			//setcookie('res',$err_count);
			//modified by wangtongchao 2012-02-28 end
		}
		//added by wangtongchao 2012-02-15 end
		//deleted by wangtongchao 2012-02-28 begin
		//echo "<script language=\"javascript\">window.close();</script>";
		//deleted by wangtongchao 2012-02-28 end
	}
	
	function permit_batch($photo_id)
	{
		//changed by wangtongchao 2012-02-14 begin 2012-02-15 add $err_count
		global $pi, $db_link, $s_login_id, $s_login_name ,$err_count;
		//changed by wangtongchao 2012-02-14 end
		$pi->photo_id = $photo_id;														// 画像ID
		//画像管理番号の設定
		// --------画像管理番号を作成する（開始）------------------------------
		
		//changed by wangtongchao 2012-02-14 begin
		$s_photo_mno = "";
		$p_photo_name = "";
		$photo_explanation = "";
		$bud_photo_no = "";
		$registration_person = "";
		$date_from = "";
		$date_to = "";

		$pi->get_photo_mno($db_link,$photo_id,$s_photo_mno,$p_photo_name,
		                    $photo_explanation,$bud_photo_no,$registration_person,
		                    $date_from,$date_to);
		
		//$s_photo_mno = "";
		//$pi->get_photo_mno($db_link,$photo_id,$s_photo_mno);
		//changed by wangtongchao 2012-02-14 end
		$tmp = "__".$s_photo_mno;
		$i_pos = strpos($tmp,"申請中");
		if($i_pos > 0)
		{
			$tmp_photo_mno1 = substr($tmp,11,5);
		} else {
			$ipos2 = strpos($s_photo_mno,"-");
			if(!empty($ipos2))
			{
				$tmp_photo_mno1 = substr($s_photo_mno,0,$ipos2);
			} else {
				$tmp_photo_mno1 = "00000";
			}
		}
		//modified by wangtongchao 2012-02-28 begin
		$tmp_photo_mno2 = REGPHOTOMNO;
		//modified by wangtongchao 2012-02-28 end
		$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
		$tmp_photo_mno3 = $p_maxno;
		$tmp_photo_mno4 = "";
		$pi->get_photo_ext($db_link,$photo_id,$tmp_photo_mno4);
		
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
			//added by wangtongchao 2012-02-15 begin
			$err_count++;
			//added by wangtongchao 2012-02-15 end
			// エラー情報をセットして、例外をスローします。
			$message = "管理番号の作成はエラーになりました。申請中";
			//added log by wangtongchao 2012-02-14 begin
			if (!empty($s_photo_mno) && !empty($p_photo_name))
			{
				$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",".$s_photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$p_photo_name));
				$logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$bud_photo_no)).",";
				$logstr .= $date_from.",".$date_to.",".$registration_person.",失敗\r\n";
			}
			//added log by wangtongchao 2012-02-14 end
			throw new Exception($message);
		} else {
			$file_name_th1 = "";
			$file_name_th2 = "";
			$file_name_th3 = "";
			$file_name_th4 = "";
			$file_name = "";
			$pi->get_photo_filename($db_link, $photo_id,$file_name,$file_name_th1,$file_name_th2,$file_name_th3,$file_name_th4);
			$pi->photo_filename = $file_name;
			$pi->photo_filename_th1 = $file_name_th1;
			$pi->photo_filename_th2 = $file_name_th2;
			$pi->photo_filename_th3 = $file_name_th3;
			$pi->photo_filename_th4 = $file_name_th4;
			// イメージをバイナリを変換して、DBに保存する
			$pi->write_imagetodb($db_link, $photo_id);
			$pi->publishing_situation_id = 2;
			$pi->permission_account = $s_login_id;
			$pi->permission_person = $s_login_name;
			$pi->permission_date = date("Y/m/d H:i:s"); //许可日、システムの日付を設定する
			$pi->update_data_batch($db_link);
			// maxnumberの更新
				//modified by wangtongchao 2012-02-28 begin
				$tmp_photo_mno2 = REGPHOTOMNO;
				//modified by wangtongchao 2012-02-28 end
			if (!empty($tmp_photo_mno2))
			{
				$p_maxno = $pi->getmaxno($db_link, $tmp_photo_mno2);
				if (!empty($tmp_photo_mno2) && strlen($tmp_photo_mno2) > 0)
				{
					$pi->setmaxno($db_link,$tmp_photo_mno2,$p_maxno);
				}
			}
			//added log by wangtongchao 2012-02-14 begin
			if (!empty($s_photo_mno) && !empty($p_photo_name))
			{
				$logstr = date("Y-m-d H:i:s").",".$s_login_id.",".$s_login_name.",".$s_photo_mno.",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$p_photo_name));
				$logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$photo_explanation)).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$bud_photo_no)).",";
				$logstr .= $date_from.",".$date_to.",".$registration_person.",成功\r\n";
			}
			//added log by wangtongchao 2012-02-14 end
		}
		//added by wangtongchao 2012-02-14 begin
		write_log_tofile($logstr);
		//added by wangtongchao 2012-02-14 end
		// --------画像管理番号を作成する（終了）------------------------------
	}
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
	
/*
 * 関数名：write_log_tofile
 * 関数説明：画像を削除すると、削除した画像はログファイルに出力する
 * パラメタ：logmsg:ログ情報
 * 戻り値：無し
 */
function write_log_tofile($logmsg)
{
	// CSVファイルを出力する
	$tmp_date = date("Ymd");
	$csvdir = "";
	$file_name = $csvdir."./log/permit_image".$tmp_date.".log";
	$file = fopen($file_name,"a+");
	fwrite($file,$logmsg);
	fclose($file);
}
?>