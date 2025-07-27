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

//ログインしているかをチェックします。
if (empty($s_login_id))
{
	// ログイン後のTOPページへリダイレクトします。
	header_out($logout_page);
}

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();

	$i = 0;

	$icount = 0;

	$s_p_photoid = array_get_value($_REQUEST,"s_p_photoid","");
	if(empty($s_p_photoid))
	{
		$sql = "SELECT * FROM photoimg WHERE publishing_situation_id = 2 ORDER BY photo_id";
	} else {
		$sql = "SELECT * FROM photoimg WHERE publishing_situation_id = 2 AND photo_id >= ".$s_p_photoid." ORDER BY photo_id";
	}

	$stmt = $db_link->prepare($sql);

	// SQLを実行します。
	$result = $stmt->execute();

	// 実行結果をチェックします。
	if ($result == true)
	{
		// 実行結果がOKの場合の処理です。
		$icount = $stmt->rowCount();
		if ($icount >= 0)
		{
			//ログファイルの削除
			$file = is_file("./log/bainari_image.log");
			// ファイルのオープンはエラーの場合
			if ($file)
			{
				unlink($file);
			}
			while ($img = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$i = $i + 1;

				$p_photoid = $img['photo_id'];
				$p_mno = $img['photo_mno'];
				$photo_filename = $img['photo_filename'];
				$photo_filename_th1 = $img['photo_filename_th1'];
				$photo_filename_th2 = $img['photo_filename_th2'];

				// 画像をチェックする
				$tmp = substr($photo_filename,strpos($photo_filename,"./"));
				if(!is_file($tmp))
				{
					$str =  "<p style='color: red'>".$tmp."ファイルは見つかりませんでした。</p>";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					flush();
					continue;
				}

				$tmp = substr($photo_filename_th1,strpos($photo_filename_th1,"./"));
				if(!is_file($tmp))
				{
					$str =  "<p style='color: red'>".$tmp."ファイルは見つかりませんでした。</p>";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					flush();
					continue;
				}

				$tmp = substr($photo_filename_th2,strpos($photo_filename_th2,"./"));
				if(!is_file($tmp))
				{
					$str =  "<p style='color: red'>".$tmp."ファイルは見つかりませんでした。</p>";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					flush();
					continue;
				}

				if($i > 1000)
				{
					//$str_log = date("Y-m-d H:i:s").",1000件を処理しました。\r\n";
					//write_log_tofile($str_log);

					print "<script type='text/javascript'>\r\n";
					print "document.location = './make_image_bainari.php?s_p_photoid='+".$p_photoid."\r\n";
					print "</script>\r\n";
					return;
				}
				write_imagetodb($db_link,$p_photoid,$photo_filename,$photo_filename_th1,$photo_filename_th2);

				$str =  "画像管理番号：".$p_mno."を処理しました。</p>";
				$str_log = date("Y-m-d H:i:s").",画像管理番号：".$p_mno."を処理しました。\r\n";
				write_log_tofile($str_log);
				print $str . str_repeat(' ', 256);
				print "<br/>";
				flush();
			}
		}
		else
		{
			// エラー情報をセットして、例外をスローします。
			$message = "画像を取得できませんでした。（取得数<=0）";
			throw new Exception($message);
		}
	}
	else
	{
		// 実行結果がNGの場合の処理です。
		// エラー情報をセットして、例外をスローします。
		$err = $stmt->errorInfo();
		$message = "画像を取得できませんでした。（条件設定エラー）";
		throw new Exception($message);
	}

	$str =  $icount."処理は正常に終わりました。</p>";
	$str_log = date("Y-m-d H:i:s").",処理は正常に終わりました。\r\n";
	write_log_tofile($str_log);
	print $str . str_repeat(' ', 256);
	print "<br/>";
	flush();
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

/*
 * 関数名：write_imagetodb
 * 関数説明：画像をバイナリになって、DBに保存する
 * パラメタ：
 * db_link：ＤＢリンク
 * p_photo_id:写真ID
 * photo_filename：アップロードのファイル名
 * photo_filename_th1：サムネル１のファイル名
 * photo_filename_th2：サムネル２のファイル名
 * 戻り値：無し
 */
function write_imagetodb($db_link,$p_photo_id,$photo_filename,$photo_filename_th1,$photo_filename_th2)
{
	if (empty($p_photo_id) || $p_photo_id == "-1" || $p_photo_id <= 0) return;

	$sql_exists = "SELECT COUNT(*) cnt FROM photo_imgdata WHERE photo_id = ".$p_photo_id;
	// SQL文法のチェック
	$stmt = $db_link->prepare($sql_exists);
	$result = $stmt->execute();
	// 実行結果をチェックします。
	if ($result == true)
	{
		// 画像をバイナリをなる
		$tmp = substr($photo_filename,strpos($photo_filename,"./"));
		$b_image1 = file_get_contents($tmp);

		$tmp = substr($photo_filename_th1,strpos($photo_filename_th1,"./"));
		$b_image2 = file_get_contents($tmp);

		$tmp = substr($photo_filename_th2,strpos($photo_filename_th2,"./"));
		$b_image3 = file_get_contents($tmp);

		$pcnt = $stmt->fetch(PDO::FETCH_ASSOC);
		$tmpcnt = $pcnt['cnt'];

		//既に存在の場合に更新する
		if ((int)$tmpcnt > 0)
		{
			// SQL文の作成
			$sql = "UPDATE photo_imgdata SET ";
			$sql .= "image1= ?,";
			$sql .= "image2= ?,";
			$sql .= "image3= ?";

			// 更新条件の設定
			$sql .= " WHERE photo_id=" . $p_photo_id;
			// SQL文法のチェック
			$stmt = $db_link->prepare($sql);
			// パラメータの設定
			$stmt->bindValue(1, $b_image1, PDO::PARAM_LOB);
			$stmt->bindValue(2, $b_image2, PDO::PARAM_LOB);
			$stmt->bindValue(3, $b_image3, PDO::PARAM_LOB);

						// トランザクションを開始します。（オートコミットがオフになります。）
			$db_link->beginTransaction();
			try
			{
				$result = $stmt->execute();
				if ($result == true)
				{
					// コミットします。
					$db_link->commit();
				} else {
					// ロールバックします。
					$db_link->rollBack();
					// 例外をスローします。
					$err = $stmt->errorInfo();
					throw new Exception($err[2]);
				}
			} catch(Exception $e) {
				// ロールバックします。
				//$db_link->rollBack();
				// 例外をスローします。
				$msg = $e->getMessage();
				throw new Exception($msg);
			}
		} else {
		//存在しない場合に新規する
			// SQL文の作成
			$sql = "INSERT INTO photo_imgdata (  photo_id,
												 image1,
												 image2,
												 image3
								 	 		  ) VALUES ($p_photo_id,?,?,?)";
			// SQL文法のチェック
			$stmt = $db_link->prepare($sql);
			// パラメータの設定
			$stmt->bindValue(1, $b_image1, PDO::PARAM_LOB);
			$stmt->bindValue(2, $b_image2, PDO::PARAM_LOB);
			$stmt->bindValue(3, $b_image3, PDO::PARAM_LOB);

						// トランザクションを開始します。（オートコミットがオフになります。）
			$db_link->beginTransaction();
			try
			{
				$result = $stmt->execute();
				if ($result == true)
				{
					// コミットします。
					$db_link->commit();
				} else {
					// ロールバックします。
					$db_link->rollBack();
					// 例外をスローします。
					$err = $stmt->errorInfo();
					$message = "画像データの更新に失敗しました。（条件設定エラー）";
					//throw new Exception($message);
					throw new Exception($err[2]);
				}
			} catch(Exception $e) {
				// ロールバックします。
				//$db_link->rollBack();
				// 例外をスローします。
				$msg = $e->getMessage();
				throw new Exception($msg);
			}
		}
	} else {
		$message = "画像データの更新に失敗しました。（条件設定エラー）";
		throw new Exception($message);
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
	$file = fopen("./log/bainari_image.log","a+");
	fwrite($file,$errmsg);
	fclose($file);
}
?>