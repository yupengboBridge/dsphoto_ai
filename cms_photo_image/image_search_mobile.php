<?php
// データーベース情報です
$db_host = '10.254.2.39';
//$db_host = 'localhost';
//$db_user = '_ximage';
$db_user = 'ximage';
//$db_user = 'root';
$db_password = 'kCK!7wu4';
//$db_password = '222222';
//$db_name = '_ximage';
//$db_password = '222222';
$db_name = 'ximage';
//$db_name = 'photo';
$db_charset = 'utf8';
$db_link;

$db_host = 'localhost';
$db_user = 'root';
$db_password = 'root@Hcst2022';
$db_name = 'photodb_image';

date_default_timezone_set('Asia/Tokyo');

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();
	if (isset($_REQUEST['p_photo_mno']))
	{
		$p_photo_mno = $_REQUEST['p_photo_mno'];
	} else {
		header("Content-type: image/jpeg; charset=UTF-8");
		echo file_get_contents("./parts/noimage.gif");
		return;
	}

	$sql = "select * from photoimg where photo_mno='".$p_photo_mno."'";

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
			$img = $stmt->fetch(PDO::FETCH_ASSOC);
			$now = date("Y-m-d");
			if ($now >= $img['dfrom'] && $now <= $img['dto'])
			{
				// 画像表示回数を更新する
				//$disp_counter = new DispCounter();
				//$disp_counter->photo_mno = $p_photo_mno;
				//$disp_counter->disp_date = $now;
				//$disp_counter->update_data($db_link);

				$tmp1 = $img['photo_filename_th2'];
				if (!empty($tmp1))
				{
					$ipos = strpos($tmp1,"./");
					if ($ipos > 0)
					{
						$tmp2 = substr($tmp1,$ipos);
						if(strpos($tmp1,"cms_photo_image")>0)
						{
							if (!empty($tmp2))
							{
								header("Content-type: image/jpeg; charset=UTF-8");
								$binary = file_get_contents($tmp2);
								echo $binary;
							} else {
								header("Content-type: image/jpeg; charset=UTF-8");
								echo file_get_contents("./parts/noimage.gif");
							}
						} else {
							if(strpos($tmp1,"photo_db")>0)
							{
								//echo "../photo_db/".substr($tmp1,$ipos+2);exit;
								if(!file_exists($tmp2))
								{
										copy("../photo_db/".substr($tmp1,$ipos+2),$tmp2);
								}
								header("Content-type: image/jpeg; charset=UTF-8");
								$binary = file_get_contents($tmp2);
								echo $binary;
							}
						}
					} else {
						header("Content-type: image/jpeg; charset=UTF-8");
						echo file_get_contents("./parts/noimage.gif");
					}
				} else {
					header("Content-type: image/jpeg; charset=UTF-8");
					echo file_get_contents("./parts/noimage.gif");
				}
			} else {
				header("Content-type: image/jpeg; charset=UTF-8");
				echo file_get_contents("./parts/noimage.gif");
			}
		}
		else
		{
			header("Content-type: image/jpeg; charset=UTF-8");
			echo file_get_contents("./parts/noimage.gif");
		}
	}
	else
	{
		header("Content-type: image/jpeg; charset=UTF-8");
		echo file_get_contents("./parts/noimage.gif");
	}
}
catch(Exception $e)
{
	header("Content-type: image/jpeg; charset=UTF-8");
	echo file_get_contents("./parts/noimage.gif");
}

function db_connect()
{
	global $db_host, $db_name, $db_user, $db_password, $db_charset, $is_connect, $db_link;

	$is_connect = false;

	// パスワード以外が空の場合はエラーとします。
	if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_charset))
	{
		$err_message = "データベース情報に不備があります。";
		throw new Exception($err_message);
	}
	// データベースキャラクターセットのチェックをします。（省略）

	// データベースに接続します。
	$hostdb = "mysql:host=". $db_host . "; dbname=" . $db_name;
	$pdo = new PDO($hostdb, $db_user, $db_password);

	// 使用するキャラクターセットを設定します。
	//$sql = "set character SET :DBCHAR";
	$sql = "set names :DBCHAR";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':DBCHAR', $db_charset);
	$result = $stmt->execute();

	$is_connect = $result;

	// PDOのインスタンスを返します。
	return $pdo;
}

//ユーザーエージェントの判別
	function isKeitai() {
	    //NTT DoCoMo
	    if (preg_match("/DoCoMo/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //旧J-PHONE〜vodafoneの2G
	    if (preg_match("/J-PHONE/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //vodafoneの3G
	    if (preg_match("/Vodafone/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //vodafoneの702MOシリーズ
	    if (preg_match("/MOT/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //SoftBankの3G
	    if (preg_match("/SoftBank/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //au (KDDI)
	    if (preg_match("/PDXGW/i", $_SERVER['HTTP_USER_AGENT'])) return TRUE;
	    if (preg_match("/UP\.Browser/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //ASTEL
	    if (preg_match("/ASTEL/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //DDI Pocket
	    if (preg_match("/DDIPOCKET/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    
	    if (preg_match("/Android/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //IPHONE
	    if (preg_match("/iPhone/i", $_SERVER['HTTP_USER_AGENT'])) return true;

	    return false;
	}

/*
 * クラス名：DispCounter
 * クラス説明：画像表示回数を管理する
 */
class DispCounter
{
	var $message;					// メッセージ
	var $error;						// エラー

	var $photo_mno;					// 画像管理番号
	var $disp_date;					// 画像表示日付
	var $counter;					// カウント
	var $disp_cnt_ary;				// 画像表示回数クラス

	function set_photo_mno($sp_photo_mno)
	{
		if (!empty($sp_photo_mno))
		{
			$this->photo_mno = $sp_photo_mno;
		}
	}

	function set_disp_date($sp_disp_date)
	{
		if (!empty($sp_disp_date))
		{
			$this->disp_date = $sp_disp_date;
		}
	}

	function set_counter($sp_counter)
	{
		if (!empty($sp_counter))
		{
			$this->counter = $sp_counter;
		}
	}

	/*
	 * 関数名：isExitsCheck
	 * 関数説明：画像管理番号があるかどうかチェックする
	 * パラメタ：
	 * db_link:	データベースのリンク
	 * 戻り値：true/false
	 */
	function isExitsCheck($db_link)
	{
		// 検索のSQL文
		$sql = "SELECT * FROM disp_counter ";
		$sql .= " WHERE photo_mno = \"" .$this->photo_mno. "\"";
		$sql .= " AND disp_date = \"" .$this->disp_date. "\"";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$dp_cn = $stmt->fetch(PDO::FETCH_ASSOC);
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				return true;
			} else {
				return false;
			}
		} else {
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			throw new Exception($err);
			return -1;
		}
	}

	/*
	 * 関数名：select_data1
	 * 関数説明：画像表示回数を検索する
	 * パラメタ：
	 * db_link:	データベースのリンク
	 * 戻り値：true/false
	 */
	function select_data1($db_link)
	{
		// 検索のSQL文
		$sql = "SELECT photo_mno,sum(counter) cnt FROM disp_counter ";
		$sql .= " GROUP BY photo_mno";
		$sql .= " ORDER BY cnt DESC";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$this->disp_cnt_ary = array();

			while ($dp_cn = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$tmp_disp = new DispCounter();
				$tmp_disp->photo_mno = $dp_cn['photo_mno'];
				$tmp_disp->counter = $dp_cn['cnt'];
				$this->disp_cnt_ary[] = $tmp_disp;
			}
			return true;
		} else {
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			throw new Exception($err[2]);
			return -1;
		}
	}

	/*
	 * 関数名：select_data2
	 * 関数説明：画像表示回数を検索する
	 * パラメタ：
	 * db_link:	データベースのリンク
	 * 戻り値：true/false
	 */
	function select_data2($db_link)
	{
		// 検索のSQL文
		$sql = "SELECT photo_mno,sum(counter) cnt,disp_date FROM disp_counter ";
		$sql .= " GROUP BY disp_date,photo_mno";
		$sql .= " ORDER BY disp_date,cnt DESC,photo_mno";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$this->disp_cnt_ary = array();

			while ($dp_cn = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$tmp_disp = new DispCounter();
				$tmp_disp->photo_mno = $dp_cn['photo_mno'];
				$tmp_disp->disp_date = $dp_cn['disp_date'];
				$tmp_disp->counter = $dp_cn['cnt'];
				$this->disp_cnt_ary[] = $tmp_disp;
			}
			return true;
		} else {
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			throw new Exception($err);
			return -1;
		}
	}

	/*
	 * 関数名：insert_data
	 * 関数説明：画像の表示回数をテーブルに登録します。
	 * パラメタ：
	 * $db_link: データベースのリンク
	 * 戻り値：無し
	 */
	function insert_data($db_link)
	{
		// 新規のSQL文
		$sql = "INSERT INTO disp_counter (photo_mno, disp_date, counter) VALUES ( ";
		$sql .= "\"".$this->photo_mno . "\",";		// 画像管理番号
		$sql .= "\"".$this->disp_date . "\",";		// 画像表示日付
		$sql .= "1";								// カウント
		$sql .= ");";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount != 1)
			{
				$this->message = "画像の表示回数をDBに登録できませんでした。（処理数!=1）";
				throw new Exception($this->message);
			}
		}
		else
		{
			$this->message = "画像の表示回数をDBに登録できませんでした。（条件設定エラー）";
			// 例外をスローします。
			$msg = $e->getMessage();
			throw new Exception($msg);
		}
	}

	/*
	 * 関数名  ：update_data
	 * 関数説明：画像の表示回数を更新します。
	 * パラメタ：
	 * db_link ：データベースのリンク
	 * 戻り値　：無し
	 */
	function update_data($db_link)
	{
	//	// 新規するかどうかチェックする
	//	$insert_flg = $this->isExitsCheck($db_link);
	//	// 存在しない場合
	//	if ((int)$insert_flg == 0)
	//	{
	//		$this->insert_data($db_link);
	//	// 存在した場合
	//	} else if ((int)$insert_flg > 0) {
	//		// 更新のSQL文
	//		$sql = "UPDATE disp_counter SET ";
	//		$sql .= "counter = counter + 1";
	//		$sql .= " WHERE photo_mno = \"" .$this->photo_mno. "\"";
	//		$sql .= " AND disp_date = \"" .$this->disp_date. "\"";

	//		$stmt = $db_link->prepare($sql);
	//		$result = $stmt->execute();
	//		if ($result == false)
	//		{
	//			$this->message = "画像の表示回数をDBに更新できませんでした。（条件設定エラー）";
	//			// 例外をスローします。
	//			$msg = $e->getMessage();
	//			throw new Exception($msg);
	//		}
	//	}
	}
}
?>