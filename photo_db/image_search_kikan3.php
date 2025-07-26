<?php
require_once('./kikanCommon.php');
// データーベース情報です
//$db_host = '10.254.2.63';
//$db_host = '10.254.2.39';
//$db_host = 'localhost';
$db_host = '127.0.0.1';
$db_user = 'photodbuser';
//$db_user = 'root';
$db_password = 'h9!rkG726';
//$db_password = '222222';
$db_name = 'photodb_image';
//$db_name = 'photo';
$db_charset = 'utf8';
$db_link;

date_default_timezone_set('Asia/Tokyo');

try
{
	// ＤＢへ接続します。
	$db_link = db_connect();
	if (isset($_REQUEST['p_photo_mno']))
	{
		$p_photo_mno = $_REQUEST['p_photo_mno'];
	} else {
		print_kikan_noimage();
		return;
	}

	$sql = "select * from photoimg where photo_mno=?";

	$stmt = $db_link->prepare($sql);
	$stmt->bindParam(1, $p_photo_mno);

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

				// wangdan add strat 20150402 400*300
				$tmp1 = $img['photo_filename_th1'];
//liukeyu add strat 20110905
				if(isset($_REQUEST['x']) && isset($_REQUEST['y']) 
					&& is_numeric($_REQUEST['x'])  && is_numeric($_REQUEST['y'])
					&& (int)$_REQUEST['x'] > 0 && (int)$_REQUEST['y'] > 0
					)
				{
					$imgWidth = $_REQUEST['x'];
					$imgHeight = $_REQUEST['y'];
					$newFilePath = "./change/";
					
					mkdirs($newFilePath);
					$fileName = getNewImageName($p_photo_mno,$imgWidth,$imgHeight);
					if(strlen($fileName)>0)
					{
						$newFile = $newFilePath.$fileName;
						if(fileExitOrNo($newFilePath,$fileName))
						{
							print_kikan_image('jpeg',$newFile);
							return;
						}
						$path_dir="./change/image/";
						mkdirs($path_dir);
						$file_dir = $path_dir.$fileName;
						if($fp = fopen($file_dir,'w')){
							if(fwrite($fp,$tmp1)){
								fclose($fp);
							}
						}
						changeImageHeightWidth($file_dir,$newFile,$imgHeight,$imgWidth);
						@unlink($file_dir);
						print_kikan_image('jpeg',$newFile);
						return;
					}
					print_kikan_noimage();
				} else {
					$webp = strpos($_SERVER['HTTP_ACCEPT'], 'image/webp');
					define('IS_WEBP', $webp === false ? 0 : 1);
					
					// 原始图片路径
					$original_url = $tmp1;
								
					if(empty($original_url)){
						print_kikan_noimage();
						return;
					}


					// 构造 .webp 的 URL（从原始 URL 转换）
					$path_info = pathinfo($original_url);
					$l_webp_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.webp';

					// 构造本地的文件路径
					$l_jpg_file_path = '../'.explode($_SERVER['SERVER_NAME'], $original_url)[1];
					$l_webp_file_path = '../'.explode($_SERVER['SERVER_NAME'], $l_webp_path)[1];

					if (IS_WEBP && file_exists($l_webp_file_path)) {
						print_kikan_image("webp", $l_webp_path);
					} else if (file_exists($l_jpg_file_path)) {
						print_kikan_image("jpeg", $original_url);
					} else {
						print_kikan_noimage();
					}
				}
//liukeyu add end 20110905
			} else {
				print_kikan_noimage();
			}
		}
		else
		{
			print_kikan_noimage();
		}
	}
	else
	{
		print_kikan_noimage();
	}
}
catch(Exception $e)
{
	print_kikan_noimage();
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
//liukeyu add strat 201100905
/**
 * 階層ディレクトリを作成するmkdir
 *
 * @param string $path 作成するディレクトリのパス
 * @param string $mode パーミッション
 */
function mkdirs($path, $mode=0777) 
{
    if(@mkdir($path, $mode) or file_exists($path)) return true;
    return ($this->mkdir(dirname($path),$mode) and mkdir($path, $mode));
}
    
/**
 * image change size
 * liukeyu add 20110728
 * @param unknown_type $fileName
 * @param unknown_type $newFileName
 * @param unknown_type $height
 * @param unknown_type $width
 */
function changeImageHeightWidth($fileName,$newFileName,$height,$width)
{
	list($imageWidth,$imageHeight,$type,$attr) = @getimagesize($fileName);
	$image_p = @imagecreatetruecolor($width,$height);
	switch(@strtolower($type))
	{
		case  2;
			$image = @imagecreatefromjpeg($fileName);
			@imagecopyresampled($image_p,$image,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
			@imagejpeg($image_p,$newFileName);
			break;
		case 3:
			$image = @imagecreatefrompng($fileName);
			@imagecopyresampled($image_p,$image,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
			@imagepng($image_p,$newFileName);
			break;
		case 1:
			$image = @imagecreatefromgif($fileName);
			@imagecopyresampled($image_p,$image,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
			@imagegif($image_p,$newFileName);
			break;
		default:
			break;
	}
}

/**
 * 
 * liukeyu add 20110728
 * @param $fileName
 * @param $width
 * @param $height
 */
function getNewImageName($fileName,$width,$height)
{
	$imageName = "";
	$type = @pathinfo($fileName,PATHINFO_EXTENSION);
	//$type = "gif";
	$ary_name = split("-",basename($fileName,".".$type));
	if(count($ary_name)==3)
	{
	//	$type = @pathinfo($fileName,PATHINFO_EXTENSION);
		$imageName = $ary_name[0].$ary_name[1].$ary_name[2]."-".getFiveStr($width)."-".getFiveStr($height).".".$type;
	}
	return $imageName;
}

/**
 * 
 * liukeyu add 20110728
 * @param $str
 */
function getFiveStr($str)
{
	$str_name = "";
	$len = strlen($str);
	
	if($len < 5)
	{
		for ($i=0;$i<(5-(int)$len);$i++)
		{
			$str_name .= "0";
		}
		$str_name = $str_name.$str;
	}
	else
	{
		$str_name = substr($str,0,5);
	}
	return $str_name;
}

/**
 * 
 * liukeyu add 20110728
 * @param $filePath
 * @param $fileName
 */
function fileExitOrNo($filePath,$fileName)
{
	if(@is_dir($filePath))
	{
		if($dir = @opendir($filePath))
		{
			while (($file = @readdir($dir)) !== false)
			{
				if($file == $fileName)
				{
					return true;
				}
			}
		}
	}
	return false;
}
//liukeyu add end 20110905

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