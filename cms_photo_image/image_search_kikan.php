<?php

require_once('./kikanConfig.php');
require_once('./kikanCommon.php');

$font_name = "./sazanami-gothic.ttf";
$credit_fontsize = array(8, 10, 14, 18, 22, 26);
date_default_timezone_set('Asia/Tokyo');


//DS用です。CMSで利用するとエラーが発生する場合があります。
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

			//判断是否能webp
			$webp = strpos($_SERVER['HTTP_ACCEPT'], 'image/webp');
			define('IS_WEBP', $webp === false ? 0 : 1);
			if(IS_WEBP == '0'){
				header("Content-Type:image/jpg");
				$webp_ext = pathinfo($img['photo_filename_th2'], PATHINFO_EXTENSION);
					if($webp_ext == 'webp'){
						$file_only_name_array = explode('.webp',$img['photo_filename_th2']);
						$tmp1 = $file_only_name_array[0].'.jpg';
					}else{
						$tmp1 = $img['photo_filename_th2'];
					}
			}else{
				$tmp1 = $img['photo_filename_th2'];
			}
				if (!empty($tmp1))
				{
					$ipos = strpos($tmp1,"./");
					if ($ipos > 0)
					{
						$tmp2 = substr($tmp1,$ipos);
						if (!empty($tmp2))
						{
//							$binary = file_get_contents($tmp2);
//							//header("Content-type: text/html; charset=UTF-8");
//							echo $binary;
//liukeyu add strat 20110728
							//yupengbo modify start 20120203
							if(isset($_REQUEST['x']) && is_numeric($_REQUEST['x']) && (int)$_REQUEST['x'] > 0)
							{
							//if(isset($_REQUEST['x']) && isset($_REQUEST['y'])
							//	&& is_numeric($_REQUEST['x'])  && is_numeric($_REQUEST['y'])
							//	&& (int)$_REQUEST['x'] > 0 && (int)$_REQUEST['y'] > 0
							//	)
							//{
							//yupengbo modify end 20120203
								$imgWidth = $_REQUEST['x'];
								//yupengbo modify start 20120203
								if(isset($_REQUEST['y']) && is_numeric($_REQUEST['y']) && (int)$_REQUEST['y'] > 0)
								{
									$imgHeight = $_REQUEST['y'];
								} else {
									$imgHeight = 0;
								}
								//yupengbo modify end 20120203
								$newFilePath = "./change/";
								mkdirs($newFilePath);
								$fileName = getNewImageName($p_photo_mno,$imgWidth,$imgHeight);
								if(strlen($fileName)>0)
								{
									$newFile = $newFilePath.$fileName;
									if(fileExitOrNo($newFilePath,$fileName))
									{
										print_kikan_image("jpeg", $newFile);
										return;
									}
									//yupengbo modify start 20120203
									//横幅のみ固定された場合 400pxとＳａｍｐｌｅクレジット無し画像を取得
									if((int)$imgWidth == 400 && (int)$imgHeight == 0)
									{
										$tmp2_1 = $img['photo_filename_th1'];
										$ipos_1 = strpos($tmp2_1,"./");
										$tmp2_2=substr($tmp2_1,$ipos_1);
										print_kikan_image("jpeg", $tmp2_2);
										return;
									//横幅のみ固定された場合 200pxとＳａｍｐｌｅクレジット無し画像を取得
									} elseif((int)$imgWidth == 200 && (int)$imgHeight == 0) {
										print_kikan_image("jpeg", $tmp2);
										return;
									} else {
										$tmp2_1 = $img['photo_filename'];
										$ipos_1 = strpos($tmp2_1,"./");
										$tmp2_2=substr($tmp2_1,$ipos_1);
									}
									$size = @getimagesize($tmp2_2);
									list($width, $height, $type, $attr) = $size;
									//横幅と縦幅はすべて指定された場合
									if(isset($_REQUEST['y']) && is_numeric($_REQUEST['y']) && (int)$_REQUEST['y'] > 0)
									{
										$imgHeight = $_REQUEST['y'];
									//横幅のみ固定されて、縦幅は自動に計算する
									} else {
										$imgHeight = ($imgWidth / $width) * $height;
									}
									changeImageHeightWidth($tmp2_2,$newFile,$imgHeight,$imgWidth,
										                    $img['additional_constraints1']);
									//yupengbo modify end 20120203
									print_kikan_image("jpeg", $newFile);
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
//liukeyu add end 20110728
						} else {
							print_kikan_noimage();
						}
					} else {
						print_kikan_noimage();
					}
				} else {
					print_kikan_noimage();
				}
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
}finally {
    if (isset($db_link)) {
        $db_link = null;
    }
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
//liukeyu add strat 20110728
/**
 * 階層ディレクトリを作成するmkdir
 *
 * @param string $path 作成するディレクトリのパス
 * @param string $mode パーミッション
 */
function mkdirs($path, $mode=0777) 
{
    if(@mkdir($path, $mode) or file_exists($path)) return true;
    return (mkdir(dirname($path),$mode) and mkdir($path, $mode));
}
    
/**
 * image change size
 * liukeyu add 20110728
 * @param unknown_type $fileName
 * @param unknown_type $newFileName
 * @param unknown_type $height
 * @param unknown_type $width
 * @param cre_str      クレジット
 */
function changeImageHeightWidth($fileName,$newFileName,$height,$width,$cre_str)
{
	$font_size = decide_fontsize($width);
	list($imageWidth,$imageHeight,$type,$attr) = @getimagesize($fileName);
	$image_p = @imagecreatetruecolor($width,$height);
	switch(@strtolower($type))
	{
		case  2;
			$image = @imagecreatefromjpeg($fileName);
			@imagecopyresampled($image_p,$image,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
			if(!empty($cre_str) && $cre_str != null)
			{
				$image_p = write_credit($image_p, $cre_str, $font_size, $width, $height);
			}
			@imagejpeg($image_p,$newFileName);
			break;
		case 3:
			$image = @imagecreatefrompng($fileName);
			@imagecopyresampled($image_p,$image,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
			if(!empty($cre_str) && $cre_str != null)
			{
				$image_p = write_credit($image_p, $cre_str, $font_size, $width, $height);
			}
			@imagepng($image_p,$newFileName);
			break;
		case 1:
			$image = @imagecreatefromgif($fileName);
			@imagecopyresampled($image_p,$image,0,0,0,0,$width,$height,$imageWidth,$imageHeight);
			if(!empty($cre_str) && $cre_str != null)
			{
				$image_p = write_credit($image_p, $cre_str, $font_size, $width, $height);
			}
			@imagegif($image_p,$newFileName);
			break;
		default:
			break;
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

function write_credit($img, $cre_str, $fsize, $width_i, $height_i)
{
	global $font_name;
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
	$telop_texts = explode('=_=',$telop_text);
	$str_len = count($telop_texts);
	if(strlen($telop_texts[0])>0&&strlen($telop_texts[1])>0)
	{
		for($i=2;$i>0;$i--)
		{
			// 半透明のグレーバック表示位置
			$alpha_x1 = 5;
			$alpha_x2 = $width_i - 5;

			$alpha_y1 = $height_i - ($fsize + 10) - 5;
			if($i==2) $alpha_y1 = $height_i - ($fsize + 10) - 25;
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

			if($i==2) $tmp_telop_text = mb_substr($telop_texts[0],0,20,"utf-8");
			if($i==1) $tmp_telop_text = mb_substr($telop_texts[1],0,20,"utf-8");
			//テキスト描画
			ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $font_name, $tmp_telop_text);
			ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $font_name, $tmp_telop_text);
		}
	} elseif($telop_text!='=_=') {
		// 半透明のグレーバック表示位置
		$telop_text = str_replace("=_=","",$telop_text);
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
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $font_name, $telop_text);
		ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $font_name, $telop_text);
	}
	return $img;
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
	$ary_name = explode("-",basename($fileName,".".$type));
	$tmp_name = "";
	for($i=0;$i<count($ary_name);$i++)
	{
		$tmp_name = "-";
		if(strlen($ary_name[$i]) > 0) $tmp_name = $ary_name[$i];
		$imageName .= $tmp_name;
	}
	//if(count($ary_name)==3)
	//{
	//	$type = @pathinfo($fileName,PATHINFO_EXTENSION);
	//	$imageName = $ary_name[0].$ary_name[1].$ary_name[2]."-".getFiveStr($width)."-".getFiveStr($height).".".$type;
	//}
	$imageName .= "-".getFiveStr($width)."-".getFiveStr($height).".".$type;
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
//liukeyu add end 20110728

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
				$msg = "画像の表示回数をDBに登録できませんでした。（処理数!=1）";
				throw new Exception($msg);
			}
		}
		else
		{
			$msg = "画像の表示回数をDBに登録できませんでした。（条件設定エラー）";
			// 例外をスローします。
			//$msg = $e->getMessage();
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
	//			$msg = "画像の表示回数をDBに更新できませんでした。（条件設定エラー）";
	//			// 例外をスローします。
	//			//$msg = $e->getMessage();
	//			throw new Exception($msg);
	//		}
	//	}
	}
}
?>