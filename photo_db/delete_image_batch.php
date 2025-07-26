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

// ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

// CSVファイルのPATHを設定
$csvdir = "./photods_csv/";

$filename = array_get_value($_REQUEST,"p_filename","");

if (empty($filename) || $filename == "")
{
	$errmessage = "ファイル名はNULLになりました。<br/>";
	print "<p style='color: red'>".$errmessage."</p>";
	return;
}

if (is_file($csvdir.$filename) == false)
{
	$errmessage = "photods_csvディレクトリに".$filename."ファイルは見つかりませんでした！<br/>";
	print "<p style='color: red'>".$errmessage."</p>";
	return;
}

setlocale(LC_ALL,'ja_JP.UTF-8');

// PhotoImageのインスタンスを生成します。
$pi = new PhotoImageDB ();

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	// CSVファイルを開く
	$file = fopen($csvdir.$filename,"r");

	$cnt = 0;

	// ファイルの内容より繰り返し一覧データを作成する
	while(!feof($file))
	{
		// 行の内容は配列にする
		$csv_content = fgetcsv($file,1000000);

		if (count($csv_content) <= 0 || empty($csv_content)) continue;

		if (count($csv_content) != 1 )
		{
			$str =  "<p style='color: red'>画像管理BUD番号：".$csv_content[0]."　フィールド数は違います。</p>";
			$errmessage2 = "画像管理BUD番号：".$csv_content[0].",フィールド数は違います。,".date("Y-m-d H:i:s")."\r\n";

			write_log_tofile($errmessage2);
			print $str . str_repeat(' ', 256);
			print "<br/>";
			//ob_flush();
			flush();
			continue;
		}

		$p_photo_id = "";
		$p_name = "";
		//yupengbo add 2011/11/18 start
		$photo_mno = "";
		$photo_explanation = "";
		$bud_photo_no = "";
		$registration_person = "";
		$date_from = "";
		$date_to = "";
		//yupengbo add 2011/11/18 end
		
		//yupengbo modify 2011/11/18 start
		get_photo_id($db_link,$csv_content[0],$p_photo_id,$p_name,
                     $photo_mno,$photo_explanation,$bud_photo_no,$registration_person,
                     $date_from,$date_to);
        //yupengbo modify 2011/11/18 end

		if (empty($p_photo_id))
		{
			$str =  "<p style='color: red'>画像管理BUD番号：".$csv_content[0]."はDBに見つかりませんでした。</p>";
			$errmessage2 = "画像管理BUD番号：".$csv_content[0].",DBに見つかりませんでした。,".date("Y-m-d H:i:s")."\r\n";

			write_log_tofile($errmessage2);
			print $str . str_repeat(' ', 256);
			print "<br/>";
			//ob_flush();
			flush();
			continue;
		}

		$tmp_ary1 = split(",",$p_photo_id);
		$tmp_ary2 = split(",",$p_name);				// 画像名前
		//yupengbo add 2011/11/18 start
		$tmp_ary3 = split(",",$photo_mno);			// 画像管理番号
		$tmp_ary4 = split(",",$photo_explanation);	// 画像詳細内容
		$tmp_ary5 = split(",",$bud_photo_no);		// BUD Photo番号
		$tmp_ary6 = split(",",$registration_person);// 申請者
		$tmp_ary7 = split(",",$date_from);			// 期間From
		$tmp_ary8 = split(",",$date_to);			// 期間To
		//yupengbo add 2011/11/18 end
		for($i = 0; $i < count($tmp_ary1); $i++)
		{
			$p_photo_idtmp = $tmp_ary1[$i];
			//$p_nametmp = $tmp_ary2[$i];//yupengbo comment 2011/11/18

			$retval = $pi->delete_data($db_link, $p_photo_idtmp);
			if ($retval)
			{
				$cnt = $cnt + 1;

				$login_id_delimg = "";
				$login_name_delimg = "";

				$tmpcompcode = sprintf("%05d","0");
				select_user($tmpcompcode,$login_id_delimg,$login_name_delimg);

				if (!empty($login_id_delimg))
				{
					//yupengbo modify 2011/11/18 start
					//$logstr = date("Y-m-d H:i:s").",".$login_id_delimg.",".$login_name_delimg.",".$csv_content[0].",".$p_nametmp."\r\n";//yupengbo comment 2011/11/18
					$logstr = date("Y-m-d H:i:s").",".$login_id_delimg.",".$login_name_delimg.",".$tmp_ary3[$i].",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$tmp_ary2[$i]));
					$logstr .= ",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$tmp_ary4[$i])).",".preg_replace("/,/"," ",preg_replace("'([\r\n])[\s]+'", " ",$tmp_ary5[$i])).",";
					$logstr .= $tmp_ary7[$i].",".$tmp_ary8[$i].",1,".$tmp_ary6[$i]."\r\n";
					//yupengbo modify 2011/11/18 end
				} else {
					//yupengbo modify 2011/11/18 start
					//$logstr = date("Y-m-d H:i:s").",admin,BUD管理者,".$csv_content[0].",".$p_nametmp."\r\n";//yupengbo comment 2011/11/18
					$logstr = date("Y-m-d H:i:s").",admin,BUD管理者,".$tmp_ary3[$i].",".$tmp_ary2[$i];
					$logstr .= ",".preg_replace("'([\r\n])[\s]+'", " ",$tmp_ary4[$i]).",".$tmp_ary5[$i].",";
					$logstr .= $tmp_ary7[$i].",".$tmp_ary8[$i].",0,".$tmp_ary6[$i]."\r\n";
					//yupengbo modify 2011/11/18 end
				}

				if (!empty($logstr))
				{
					write_log_tofile($logstr);
				}

				$msg = "画像管理BUD番号：".$csv_content[0]."　削除しました。";
				print $msg . str_repeat(' ', 256);
				print "<br/>";
				//ob_flush();
				flush();
			} else {
				$str =  "<p style='color: red'>画像管理BUD番号：".$csv_content[0]."は削除エラーになりました。</p>";
				$errmessage2 = "画像管理BUD番号：".$csv_content[0].",削除エラーになりました。,".date("Y-m-d H:i:s")."\r\n";

				write_log_tofile($errmessage2);
				print $str . str_repeat(' ', 256);
				print "<br/>";
				//ob_flush();
				flush();
				continue;
			}
		}
	}

	print $cnt."件を処理しました。<br/>";
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

/*
 * 関数名：get_photo_id
 * 関数説明：画像管理BUD番号より画像IDと画像名を取得する
 * パラメタ：
 * db_link：ＤＢリンク
 * pid：画像番号
 * p_bud_photo：画像BUD管理番号
 * p_photoname：画像名前
 * photo_mno：画像管理番号
 * photo_explanation　：画像詳細内容
 * bud_photo_no　：BUD Photo番号
 * registration_person　：申請者
 * date_from　：期間From
 * date_to　：期間To
 * 戻り値：最終画像番号
 */
function get_photo_id($db_link, $p_bud_photo,&$pid,&$p_photoname,&$photo_mno,&$photo_explanation,&$bud_photo_no,&$registration_person,&$date_from,&$date_to)
{
	$sql = "SELECT photo_id,photo_name,photo_mno,photo_explanation,bud_photo_no,registration_person,dfrom,dto FROM photoimg WHERE bud_photo_no = '".$p_bud_photo."'";
	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();
	if ($result == true)
	{
		// 実行結果がOKの場合の処理です。
		$icount = $stmt->rowCount();
		if ($icount >= 1)
		{
			// データを取得します。
			$tmp1 = "";
			$tmp2 = "";
			$tmp3 = "";
			$tmp4 = "";
			$tmp5 = "";
			$tmp6 = "";
			$tmp7 = "";
			$tmp8 = "";
			
			while($photoimg = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				if(empty($tmp1))
				{
					$tmp1 = $photoimg['photo_id'];
					$tmp2 = $photoimg['photo_name'];
					
					//yupengbo add 2011/11/18 start
					$tmp3 = $photoimg['photo_mno'];
					$tmp4 = $photoimg['photo_explanation'];
					$tmp5 = $photoimg['bud_photo_no'];
					$tmp6 = $photoimg['registration_person'];
					$tmp7 = $photoimg['date_from'];
					$tmp8 = $photoimg['date_to'];
					//yupengbo add 2011/11/18 end
				} else {
					$tmp1 .= ",".$photoimg['photo_id'];
					$tmp2 .= ",".$photoimg['photo_name'];
					
					//yupengbo add 2011/11/18 start
					$tmp3 .= ",".$photoimg['photo_mno'];
					$tmp4 .= ",".$photoimg['photo_explanation'];
					$tmp5 .= ",".$photoimg['bud_photo_no'];
					$tmp6 .= ",".$photoimg['registration_person'];
					$tmp7 .= ",".$photoimg['date_from'];
					$tmp8 .= ",".$photoimg['date_to'];
					//yupengbo add 2011/11/18 end
				}
			}
			$pid = $tmp1;
			$p_photoname = $tmp2;
			//yupengbo add 2011/11/18 start
			$photo_mno = $tmp3;
			$photo_explanation = $tmp4;
			$bud_photo_no = $tmp5;
			$registration_person = $tmp6;
			$date_from = $tmp7;
			$date_to = $tmp8;
			//yupengbo add 2011/11/18 end
		}
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
	$file = fopen("./log/delete_image.log","a+");
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
		$message= $e->getMessage();
		throw new Exception($message);
	}
}
?>