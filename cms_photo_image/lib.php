<?php
// lib.php
//ini_set( "display_errors", "Off");
ini_set("upload_tmp_dir","/var/www/cms_photo_image/temp");
ini_set("sys_temp_dir","/var/www/cms_photo_image/temp");
error_reporting (E_ERROR | E_WARNING);
error_reporting (E_ERROR);
$one_page_records_cnt = 20;
function pc_sp($width,$height,$path,$photo_id=0){
	#长宽判断条件，生成SP
	if($width>2400||$height>2400) {
		update_photo_filename_th($path, 1,$photo_id);
	}

	#长宽判断条件，生成PC
	if($width>2400||$height>2400) {
		update_photo_filename_th($path, 2,$photo_id);
	}

	#长宽判断条件，生成PC
	update_photo_filename_th($path, 3,$photo_id);
}

/**
 * @throws Exception
 */
function update_photo_filename_th($path, $percent, $photo_id=0){
	if($percent < 0 || $percent > 3) return false;

	$db_link = db_connect();
	#原图片
	$original_photo = strstr($path[0],'./uploads/');
	#原url
	$url = substr($path[0],0,strrpos($path[0] ,$original_photo));
	#新路径+图片名称
	$a = 'uploads/';
	$new_path_name = "";
	if($percent == 1){
		$new_path_name = './thumb11/'.substr($original_photo,strripos($original_photo,$a)+8);
	}elseif($percent == 2){
		$new_path_name = './thumb12/'.substr($original_photo,strripos($original_photo,$a)+8);
	}elseif($percent == 3){
		$new_path_name = './thumb13/'.substr($original_photo,strripos($original_photo,$a)+8);
	}
	#图片名称
	$file_name = basename($new_path_name);
	#新纯路径
	$new_path = substr($new_path_name,0,strrpos($new_path_name ,$file_name));
	if(!file_exists($new_path))
	{
		mkdir($new_path,0777,true);
	}

	resize_image($original_photo,$percent,$new_path_name);
	$set_pd_url = $url . $new_path . $file_name;

	$sql = "";
	if($percent == 1)
	{
		if($photo_id > 0){
			$sql = "UPDATE photoimg set photo_filename_th11 = '{$set_pd_url}', is_pc = '1' where photo_id = '{$photo_id}'";
		}else{
			$sql = "UPDATE photoimg set photo_filename_th11 = '{$set_pd_url}', is_pc = '1' where photo_filename = '{$path[0]}'";
		}
	}elseif($percent == 2){
		if($photo_id > 0){
			$sql = "UPDATE photoimg set photo_filename_th12 = '{$set_pd_url}', is_sp = '1' where photo_id = '{$photo_id}'";
		}else{
			$sql = "UPDATE photoimg set photo_filename_th12 = '{$set_pd_url}', is_sp = '1' where photo_filename = '{$path[0]}'";
		}
	}elseif($percent == 3){
		if($photo_id > 0){
			$sql = "UPDATE photoimg set photo_filename_th13 = '{$set_pd_url}' where photo_id = '{$photo_id}'";
		}else{
			$sql = "UPDATE photoimg set photo_filename_th13 = '{$set_pd_url}' where photo_filename = '{$path[0]}'";
		}
	}

	$result = false;
	if(!empty($sql)){
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
	}
	return $result;
}

function resize_image($filename,$percent,$new_path)
{
	// echo "裁剪图片并保存".'</br>';
	// echo '原图片='.$filename.'</br>';
	// echo '缩放比例='.$percent.'</br>';
	// echo '保存的图片路径='.$new_path.'</br>';
	$a = count(explode('.',basename($filename)))-1;
	$b = explode('.',basename($filename));
	$ext = $b[$a];
	list($width, $height) = getimagesize($filename);
	if($percent == 1){
		$new_width = 750;
		$new_height = 470; 
	}
	if($percent == 2){
		$new_width = 2600;
		$new_height = 1200; 
	}
	if($percent == 3){
		$new_width = 1252;
		$new_height = 578;
	}
	
	$image_p = imagecreatetruecolor($new_width, $new_height);
	switch ($ext) {
		case 'jpg':
			$image = ImageCreateFromJpeg($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $new_path);
			//create webp format image
			$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $new_path);
			@imagewebp($image_p, $tmp2_webp);
			break;
		case 'jpeg':
			$image = ImageCreateFromJpeg($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $new_path);
			//create webp format image
			$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $new_path);
			@imagewebp($image_p, $tmp2_webp);
			break;
		case 'png':
			$image = ImageCreateFromPng($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagepng($image_p, $new_path);
			//create webp format image
			$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $new_path);
			@imagewebp($image_p, $tmp2_webp);
			break;
		case 'gif':
			$image = ImageCreateFromGif($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagegif($image_p, $new_path);
			break;
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

/**
 * エラーメッセージを表示し、処理を中断します。
 **/
function error_exit($msg)
{
	// エラーが有った場合は、エラー画面を表示します�?
	global $charset;
	global $site_name;

	print "<html>\r\n";
	print "<head>\r\n";
	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">\r\n";
	//print "<link rel=\"stylesheet\" type=\"text/css\" href=\"./css/base.css\">\r\n";
	print "<title>$site_name</title>\r\n";
	print "</head>\r\n";
	print "<body>\r\n";

	print "<div align=\"center\">\r\n";
	print "<h1>$site_name</h1>\r\n";
	print "<font color=\"red\">";
	$ed = count($msg);
	for ($i = 0 ; $i < $ed ; $i++)
	{
		print $msg[$i] . "<br />";
	}
	print "</font>\r\n";
	print "</div>\r\n";
	print "</body>\r\n";
	print "</html>\r\n";

	exit (-1);
}

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
function array_get_value($array, $key, $default='')
{
	//return $array[$key];
    return isset($array[$key]) ? $array[$key]: $default;
}

/**
 * HTML表示用
 * →htmlエンティティー変換を行いスラッシュを取り除きます。
 */
function dp($dp_str)
{
	return stripslashes(htmlentities($dp_str, ENT_QUOTES, "utf-8"));
	//return htmlspecialchars($dp_str, ENT_QUOTES, "utf-8");
}

/**
 * 文字列がメールアドレスとして形式的に正しいかをチェックします。
 * （一般的なもののみに対応しています）
 */
function mail_checkk($mailaddress){
	if (preg_match('/^[a-zA-Z0-9_\.\-]+@[A-Za-z0-9]+\.[A-Za-z0-9]+$/',$mailaddress))
	{
		return true;
	}
	else{
		return false;
	}
}

/**
 * 指定の長さで三点リーダーを作成します。
 * ※最終的な長さは、指定の長さ＋１です。
 */
function santen_reader($strtmp, $len)
{
	if (mb_strlen($strtmp,"utf-8") > $len)
	//if (strlen($strtmp) > $len)
	{
		return mb_substr($strtmp, 0 ,$len, "utf-8") . "…";
		//return substr($strtmp, 0 ,$len) . "…";
	}
	else
	{
		return $strtmp;
	}
}


/**
 * 文字列が日付かどうかチェックします。
 *   チェック対象：yyyy/mm/dd
 *           ：yyyy/m/d
 *           ：yyyy-mm-dd
 *           ：yyyy-m-d
 *           ：yyyymmdd     以上
 * @param String $dt
 * @return Boolean
 */
function is_date($dt)
{
	// 何も入っていない場合はFALSEです。
	if (empty($dt))
	{
		return FALSE;
	}

	// 文字列の'/'の位置を取得します。
	$dlen = strlen($dt);				// 長さ
	$fpos = strpos($dt, "/");			// 前
	$bpos = strrpos($dt, "/");			// 後ろ

	// '/'の位置が取得できたかどうかをチェックします。
	if ($fpos == false || $bpos == false)
	{
		// できなかった場合は'-'の位置が取得します。
		$fpos = strpos($dt, "-");		// 前
		$bpos = strrpos($dt, "-");		// 後ろ
	}

	// '/' もしくは '-' の位置が取得できたかチェックします。
	if ($fpos != false)
	{
		// 取得できた場合の処理です。
		// 年月日に分割します。
		$year = substr($dt, 0, $fpos);
		$month = substr($dt, $fpos + 1, $bpos - $fpos - 1);
		$day = substr($dt, $bpos + 1, $dlen - $bpos - 1);

		// 取得した年月日がそれぞれ数値の場合のみチェックします。
		if (is_numeric($year) && is_numeric($month) && is_numeric($day))
		{
			return checkdate($month, $day, $year);
		}
		else
		{
			return FALSE;
		}
	}

	// 取得できなかった場合の処理です。
	// 文字列がyyyymmddの形式（長さが８）かどうかチェックします。。
	if ($dlen == 8)
	{
		// 年月日に分割します。
		$year = substr($dt, 0, 4);
		$month = substr($dt, 4, 2);
		$day = substr($dt, 6, 2);

		// 取得した年月日がそれぞれ数値の場合のみチェックします。
		if (is_numeric($year) && is_numeric($month) && is_numeric($day))
		{
			return checkdate($month, $day, $year);
		}
		else
		{
			return FALSE;
		}
	}

	// すべての条件に当てはまらない場合はFALSEとします。
	return FALSE;
}

function conv_htmlstr($src, $mode, $cs, $lno)
{
	$ds = array();

	for ($i = 0 ; $i < count($src) ; $i++)
	{
		if ($lno > 0)
		{
			$ds[] = sprintf("%4d:", $i + 1 + $lno);
		}
		$ds[] .= htmlentities($src[$i], $mode, $cs);
		$ds[] .= "\r\n";
	}

	return $ds;
}

/*
 * 関数名：header_out
 * 関数説明：ログイン画面へ遷移する
 * パラメタ：strurl：url
 * 戻り値：無し
 */
function header_out($strurl)
{
	print "<script type='text/javascript'>";
	print "document.location.href=\"".$strurl."\"";
	print "</script>";
}

/**
 * ファイルをアップロードします。
 *
 * 	【コンストラクター】
 * 		FileUpload($fl, $cf, $thdir, $flw, $fname, $cre, $wcredit)
 * 			@param array $fl 		元ファイル（配列）
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
class FileUpload
{
	var $message;									// メッセージ
	var $result;									// アップロード結果
	var $img_width;									// イメージサイズ（横）	0:元、1:サムネイル1、2:サムネイル2・・・
	var $img_height;								// イメージサイズ（縦）	0:元、1:サムネイル1、2:サムネイル2・・・
	var $up_url;									// アップロードURL（最終的にアップロードされたURL）
													//					0:元、1:サムネイル1、2:サムネイル2・・・
	var $ext;										// 拡張子（元ファイル名）

	private $file;									// ファイル情報
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
	private $write_font;								// フォントを書き込むかどうか

	/**
	 * コンストラクター
	 */
	function __construct($fl, $cf, $thdir, $flw, $fname, $cre, $wcredit)
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
		$this->write_font = true;							// クレジット

		$this->img_width = array();					// イメージサイズ（横）			0:元、1:サムネイル1、2:サムネイル2・・・
		$this->img_height = array();				// イメージサイズ（横）			0:元、1:サムネイル1、2:サムネイル2・・・
		$this->write_ok = array();					// クレジットを書き込むかどうか		0:元、1:サムネイル1、2:サムネイル2・・・
		$this->flwidth = array();					// サムネイルを作成するときの横幅	0:元、1:サムネイル1、2:サムネイル2・・・
		$this->thumbdir = array();					// サムネイルを保存するフォルダ		0:元、1:サムネイル1、2:サムネイル2・・・
		$this->up_url = array();					// アップロードURL				0:元、1:サムネイル1、2:サムネイル2・・・
		$this->svfullpath = array();				// 保存ファイル名（フルパス）		0:元、1:サムネイル1、2:サムネイル2・・・

		// ファイル情報を設定します。
		$this->file = $fl;

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
		$this->upfile = $fl['name'];								// アップロードファイル名

		// アップロードするファイルサイズを取得します。
		$this->uploadsize = $fl['size'];							// アップロードサイズ

		// アップロードするファイル名から拡張子を抜出します。
		preg_match("/\.[^.]*$/i", $this->upfile, $ext_tmp);			// extに拡張子

		// 拡張子を小文字に変換します。
		$this->ext = strtolower($ext_tmp[0]);

		// 拡張子のエラーチェックをします。
		if (empty($this->ext))
		{
			$this->result = false;
			$this->message = "ファイルの拡張子が付いていません。";
			throw new Exception($this->message);
		}

		// 拡張子が申請できる拡張子かどうかをチェックします。
		if ($this->ext == ".jpeg")
		{
			$this->result = false;
			$this->message = "拡張子が jpeg のため登録できません。";
			throw new Exception($this->message);
		}

		// 拡張子が申請できる拡張子かどうかをチェックします。
		if ($this->ext != ".jpg" && $this->ext != ".png" && $this->ext != ".gif")
		{
			$this->result = false;
			$this->message = "申請できない種類のファイルです。（拡張子.jpeg、.jpg、.png、.gifのみ申請可能です。）";
			throw new Exception($this->message);
		}

		// アップロードするファイル名が指定されているかチェックします。
		if (empty($this->upfile))
		{
			// アップロードするファイル名が空の場合は、エラーとします。
			$this->result = false;
			$this->message = "アップロードするファイルが指定されていません。";
			throw new Exception($this->message);
		}

		// アップロード時のエラーをチェックします。
		$err_no =$fl['error'];
		if ($err_no != UPLOAD_ERR_OK)
		{
			$this->result = false;
			// FILES ERRORをチェックします。
			if ($err_no == 1)
			{
				$this->message = $this->upfile . "はphp.ini の upload_max_filesize ディレクティブの値を超えています。";
			}
			elseif ($err_no == 2)
			{
				$this->message = $this->upfile . "は HTMLフォーム で指定された MAX_FILE_SIZE を超えています。";
			}
			elseif ($err_no == 3)
			{
				$this->message = $this->upfile . "のアップロードに失敗しました。(Errno=3)";
			}
			elseif ($err_no == 4)
			{
				$this->message = $this->upfile . "のアップロードに失敗しました。(Errno=4)";
			}
			else
			{
				$this->message = "ファイルの原因によりアップロードできませんでした。";
			}
			throw new Exception($this->message);
		}

		// ファイルサイズか０かどうかチェックします。
		if ($this->uploadsize == 0)
		{
			// ファイルサイズが０の場合は、エラーとします。
			$this->result = false;
			// $this->message = "ファイルサイズが０です。";
			$this->message = "ファイルは存在しません。";
			throw new Exception($this->message);
		}

		// ファイルサイズが設定した最大値を超えているかチェックします。
		if ($this->uploadsize > $this->uploadconf['maxsize'])
		{
			// 設定した最大値を超えた場合は、エラーとします。
			$this->result = false;
			$this->message = "ファイルサイズが設定した最大値を超えています。";
			throw new Exception($this->message);
		}
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
		$telop_texts = explode('=_=',$telop_text);
		//yupengbo add 20101208 start
		$str_len = count($telop_texts);
		if(strlen($telop_texts[0])>0 && count($telop_texts) >=2 && strlen($telop_texts[1])>0)
		{
			for($i=2;$i>0;$i--)
			{
				// 半透明のグレーバック表示位置
				$alpha_x1 = 0;
				$alpha_x2 = $width_i;

				$alpha_y1 = $height_i - ($fsize + 10) + 4;
				$alpha_y2 = $height_i;
				
				if($i==2) {
				    $alpha_y1 = $height_i - ($fsize * 2) - 10;
				    $alpha_y2 = $height_i;
				}

				// クレジット書き込み位置
				$tx = $alpha_x1 + 5;
				$ty = $alpha_y1 + $fsize + 2;

				// テキストカラー（黒）
				$font_color_b = ImageColorAllocate ($img, 0, 0, 0);
				// テキストカラー（白）
				$font_color_w = ImageColorAllocate ($img, 255, 255, 255);
				// アルファチャンネル（グレー）
				if($i==2) $alpha = imagecolorallocatealpha($img, 0, 0, 0, 70);
				if($i==1) $alpha = imagecolorallocatealpha($img, 0, 0, 0, 127);

				// 画像の一部を透かしイメージにします。
				imagefilledrectangle ($img , $alpha_x1 , $alpha_y1, $alpha_x2, $alpha_y2, $alpha);

				if($i==2) $tmp_telop_text = mb_substr($telop_texts[0],0,42,"utf-8");
				if($i==1) $tmp_telop_text = mb_substr($telop_texts[1],0,42,"utf-8");
				//テキスト描画
				ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $tmp_telop_text);
				ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $tmp_telop_text);
			}
			  //wangtongchao 2011/08/23 change start tianjiaif()puanduan
		} elseif($telop_text!='=_=') {
			  //wangtongchao 2011/08/23 change end
		//yupengbo add 20101208 end
			// 半透明のグレーバック表示位置
			$telop_text = str_replace("=_=","",$telop_text);
			$alpha_x1 = 0;
			$alpha_x2 = $width_i;

			$alpha_y1 = $height_i - ($fsize + 10) + 4;
			$alpha_y2 = $height_i;

			// クレジット書き込み位置
			$tx = $alpha_x1 + 5;
			$ty = $alpha_y1 + $fsize + 2;

			// テキストカラー（黒）
			$font_color_b = ImageColorAllocate ($img, 0, 0, 0);
			// テキストカラー（白）
			$font_color_w = ImageColorAllocate ($img, 255, 255, 255);
			// アルファチャンネル（グレー）
			$alpha = imagecolorallocatealpha($img, 0, 0, 0, 70);

			// 画像の一部を透かしイメージにします。
			imagefilledrectangle ($img , $alpha_x1 , $alpha_y1, $alpha_x2, $alpha_y2, $alpha);

			//テキスト描画
			//ImageTTFText($img, $font_size, $font_angle, $tx+2 , $ty+2, $font_color_b, $this->font_name, $telop_text);
			//ImageTTFText($img, $font_size, $font_angle, $tx+2 , $ty+2, $font_color_b, $this->font_name, $telop_text);
			ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $telop_text);
			ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $telop_text);
		}
		return $img;
	}

	/**
	 * ファイルをアップロードします。
	 */
	function upload()
	{
		// チェックでエラーが発生している場合は、例外をスローします。
		if ($this->result == false)
		{
			throw new Exception($this->message);
		}

		// 保存用ファイル名をYmdHis999.xxx（同じ拡張子）で生成します。
		$reg_time = time();												// 登録日時
		$rnd = rand(100, 999);											// 乱数100-999
		$this->dirno = rand(0, 9);											// ディレクトリ名（0-9）をランダムで決定します。
		$this->dirno .= "/";
		$this->svname = date("YmdHis", $reg_time) . $rnd;				// 保存ファイル名（元）

		// 画像データを取得します。（データベース保存用のイメージ）
		// $imgdat = file_get_contents($this->file['tmp_name']);

		// 一旦、テンポラリーにアップしたファイルを保存します。
		$tmppath = $this->uploadconf['temp_dir'].$this->svname. $this->ext;
		if (move_uploaded_file($this->file['tmp_name'], $tmppath) == true)
		{
			// 保存したファイルのタイプを取得します。
			$type = exif_imagetype($this->uploadconf['temp_dir'].$this->svname . $this->ext);
			if ($type == IMAGETYPE_GIF || $type == IMAGETYPE_JPEG || $type == IMAGETYPE_PNG)
			{
				$path = $this->uploadconf['temp_dir'].$this->svname . $this->ext;
				// ファイルタイプがGIF、JPEG、PNGだった場合はテンポラリー→アップロードディレクトリにファイルを移動します。
				$this->svfullpath = array();
				$this->svfullpath[] = $this->uploadconf['dir']. $this->dirno . $this->svname . $this->ext;
				rename($tmppath, $this->svfullpath[0]);

				// 画像のサイズを取得します。
				$this->img_width = array();
				$this->img_height = array();
				$size = @getimagesize($this->svfullpath[0]);
				list($width, $height, $type, $attr) = $size;
				$this->img_width[] = $width;
				$this->img_height[] = $height;
				//create webp image
				if($type == IMAGETYPE_JPEG){
					$image = imagecreatefromjpeg($this->svfullpath[0]);
					$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $this->svfullpath[0]);
					@imagewebp($image, $tmp2_webp);
				}else if($type == IMAGETYPE_PNG){
					$image = imagecreatefrompng($this->svfullpath[0]);
					$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $this->svfullpath[0]);
					@imagewebp($image, $tmp2_webp);
				}

				#pc_sp($width,$height,$path);
				// クレジットを書き込みます。
				/*
				if (!empty($this->credit))
				{
					// 画像を読み込みます。
					$image = imagecreatefromjpeg($this->svfullpath[0]);

					// クレジット書込用フォントサイズを決定します。
					$font_size = 6+(int)($width/80);
					if ($font_size >= 40)
					{
						$font_size=40;
					}

					// クレジットを書き込みます。
					$this->write_credit($image, $this->credit, $font_size, $width, $height);

					imageJPEG($image, $this->svfullpath[0], 100);
					imagedestroy($image);
				}
				*/

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
		else
		{
			//$this->file['tmp_name'], $tmppath
			$this->result = false;
			$this->message = "ファイルのアップロードに失敗しました。（move_upload_fileエラー)".$this->file['tmp_name'].'移动到'.$tmppath;
			throw new Exception($this->message);
		}
	}
	//xu add it on 2010-12-21 start
	function set_upload_info($photo_filename,$files,$file_f_name,$file_rd_path)
	{
		global $upload_conf;
		$pos = strrpos($file_f_name,".");
		$file_name = substr($file_f_name,0,$pos);
		$this->up_url = array($photo_filename);
		$this->dirno = $file_rd_path.'/';
		$this->svname = $file_name;
		$this->svfullpath = array($upload_conf['dir'].$file_rd_path.'/'.$file_f_name);
		$size = @getimagesize($photo_filename);
		list($width, $height, $type, $attr) = $size;
		$this->img_width = array($width);
		$this->img_height = array($height);
	}
	function set_write_ok($write_ok)
	{
		$this->write_ok = $write_ok;
	}
	//xu add it on 2010-12-21 end

	/**
	 * アップロードしたファイルを全て削除します。
	 */
	function delete_upfile()
	{
		$ed = count($this->svfullpath);
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($this->svfullpath[$i]))
			{
				unlink($this->svfullpath[$i]);
			}
		}
	}

	/**
	 * クレジット書き込み用のフォントサイズを決定します。
	 */
	private function decide_fontsize($thwidth)
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

	function IsAnimatedGif($filename)
	{
		$fp=fopen($filename, 'rb');
		$filecontent=fread($fp, filesize($filename));
		fclose($fp);
		return strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1;
	}

    /**
     * 获取字符串中的数字
     * 
     * @param string $str
     *            原字符串
     * @return string $result
     *            仅含数字的字符串
     */
    function findNum($str = '')
    {
        $str = trim($str);
        if (empty($str)) {
            return '';
        }
        $result = '';
        for ($i = 0; $i < strlen($str); $i ++) {
            if (is_numeric($str[$i])) {
                $result .= $str[$i];
            }
        }
        return $result;
    }

	/**
	 * サムネイルを作成します。
	 *  元ファイルと縦・横同じ比率で作成します。
	 *    ※ bmpはGD関数無いため作成できません。
	 *xu modified it on 2010-12-27
	 */
	function make_thumbfile($resize=true)
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
			if((int)$i == 3 || (int)$i == 2 || (int)$i == 5)
			{
				//thumb4
				if((int)$i == 3)
				{
					$photo_filename_th1 = $this->up_url[1];
					$tmp = substr($photo_filename_th1,strpos($photo_filename_th1,"./"));
					$tmp1 = str_replace("th1","th4",$tmp);
					$tmp2 = str_replace("thumb1","thumb4",$tmp1);
				} elseif((int)$i == 2) {//thumb3
					$photo_filename_th2 = $this->up_url[2];
					$tmp = substr($photo_filename_th2,strpos($photo_filename_th2,"./"));
					$tmp1 = str_replace("th2","th3",$tmp);
					$tmp2 = str_replace("thumb2","thumb3",$tmp1);
				}
				elseif((int)$i == 5) {//thumb6
				    $photo_filename_th5 = $this->up_url[5];
				    $tmp = substr($photo_filename_th5,strpos($photo_filename_th5,"./"));
				    $tmp1 = str_replace("th5","th6",$tmp);
				    $tmp2 = str_replace("thumb5","thumb6",$tmp1);
				}

				$size = @getimagesize($tmp);
				list($width, $height, $type, $attr) = $size;

				// 縦・横の比率を合わせて、サムネイル用の縦、横を計算します。
				$thumb_width = $this->flwidth[$i];
				if($thumb_width == 0 || (int)$width < $thumb_width||$resize===false)
				{
					$thumb_width = $width;
				}
				$thumb_height = ($thumb_width / $width) * $height;
				// 画像サイズをセットします。
				$this->img_width[] = $thumb_width;
				$this->img_height[] = $thumb_height;
				// フォントサイズを決定します。
				if((int)$i == 3)
				{
					if($width == 400)
					{
						$font_size = 88;
					} elseif($width == 800) {
						$font_size = 168;
					} elseif($width == 200) {
						$font_size = 38;
					}
				} elseif((int)$i == 2) {//thumb3
					$font_size = 38;
				}
				elseif((int)$i == 5) {//thumb6
				    $font_size = 168;
				}
				if($resize)
				{
					$cre_str ="SAMPLE";
				}
				else
				{
					$cre_str ="";
				}

				// 画像のタイプに合わせて、サムネイルを作成します。
				if ($type == IMAGETYPE_JPEG)
				{
					if($resize===false)
					{
						$cmd = "cp ".$tmp." ".$tmp2;
						exec($cmd);
					} else {
						// アップロードしたファイルを読み込みます。
						$ufimage = @ImageCreateFromJPEG($tmp);
						// 空のサムネイル画像を作成します。
						$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
						// 空のサムネイル画像にアップロードしたファイルをコピーします。
						@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						// 画像にクレジットを書き込みます。
						$thumb = $this->write_credit2($thumb, $cre_str, $font_size, $thumb_width, $thumb_height);
						@imagejpeg($thumb, $tmp2);
						//create webp format image
						$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $tmp2);
						imagewebp($thumb, $tmp2_webp);
					}
				} else if ($type == IMAGETYPE_GIF){
					if($resize===false)
					{
						$cmd = "cp ".$tmp." ".$tmp2;
						exec($cmd);
					} else {
						$retflg = $this->IsAnimatedGif($tmp);
						//アニメの場合
						if($retflg == 1)
						{
							$this->imagick_gif_thumb($tmp,$tmp2,$thumb_width,$thumb_height);
						} else {
							// アップロードしたファイルを読み込みます。
							$ufimage = @ImageCreateFromGIF($tmp);
							// 空のサムネイル画像を作成します。
							$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
							// 空のサムネイル画像にアップロードしたファイルをコピーします。
							@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
							// 画像にクレジットを書き込みます。
							$thumb = $this->write_credit2($thumb, $cre_str, $font_size, $thumb_width, $thumb_height);
							@imagegif($thumb, $tmp2);
						}
					}
				} else if ($type == IMAGETYPE_PNG){
					if($resize===false)
					{
						$cmd = "cp ".$tmp." ".$tmp2;
						exec($cmd);
					} else {
						// アップロードしたファイルを読み込みます。
						$ufimage = @ImageCreateFromPNG($tmp);
						// 空のサムネイル画像を作成します。
						$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
						// 空のサムネイル画像にアップロードしたファイルをコピーします。
						@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
						// 画像にクレジットを書き込みます。
						$thumb = $this->write_credit2($thumb, $cre_str, $font_size, $thumb_width, $thumb_height);
						@imagepng($thumb, $tmp2);
						
						//create webp format image
						$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $tmp2);
						@imagewebp($thumb, $tmp2_webp);
					}
				}

				// アップロードされたファイル名を設定します。
				$this->svfullpath[] = $tmp2;
				$this->up_url[] = $this->uploadconf['site_url'] . $tmp2;
				
				if($resize===true)
				{
					// 画像を破棄します。
					@imagedestroy($ufimage);
					@imagedestroy($thumb);
				}
			} else {
			    // 画像のサイズを取得します。
			    $size = @getimagesize($srcfilename);
			    list($width, $height, $type, $attr) = $size;
			    
				// 縦・横の比率を合わせて、サムネイル用の縦、横を計算します。
				$thumb_width = $this->flwidth[$i];
				if($thumb_width == 0 || (int)$width < $thumb_width||$resize===false)
				{
					$thumb_width = $width;
				}
				
				//th编号
				$thNum = $this->findNum($this->thumbdir[$i]);
				if (empty($thNum)) {
				    $thNum = strval($i + 1);
				}
				
				$thumb_height = ($thumb_width / $width) * $height;
				// 画像サイズをセットします。
				$this->img_width[] = $thumb_width;
				$this->img_height[] = $thumb_height;

				// 画像のタイプに合わせて、サムネイルを作成します。
				if ($type == IMAGETYPE_JPEG)
				{
					if($resize===false)
					{
						$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
						$cmd = "cp ".$srcfilename." ".$thfilename;
						exec($cmd);
					} else {
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
						$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
						@imagejpeg($thumb, $thfilename);
						//create webp format image
						$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $thfilename);
						imagewebp($thumb, $tmp2_webp);
					}
				} else if ($type == IMAGETYPE_GIF){
					if($resize===false)
					{
						$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
						$cmd = "cp ".$srcfilename." ".$thfilename;
						exec($cmd);
						$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
						$this->imagick_gif_thumb($srcfilename,$thfilename,$thumb_width,$thumb_height);
					} else {
						$retflg = $this->IsAnimatedGif($srcfilename);
						//静的な場合
						if($retflg == 0)
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
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
							@imagegif($thumb, $thfilename);
						} else {
							$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
							$this->imagick_gif_thumb($srcfilename,$thfilename,$thumb_width,$thumb_height);
						}
					}
				} else if ($type == IMAGETYPE_PNG){
					if($resize===false)
					{
						$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
						$cmd = "cp ".$srcfilename." ".$thfilename;
						exec($cmd);
					} else {
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
						$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . $thNum . $this->ext;
						@imagepng($thumb, $thfilename);
						//create webp format image
						$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $thfilename);
						imagewebp($thumb, $tmp2_webp);
					}
				}
// 				$j = $i;
// 				while($j < intval($thNum) - 1) {
// 				    $this->svfullpath[] = strval($j + 1);
// 				    $j++;
// 				}
				// アップロードされたファイル名を設定します。
				$this->svfullpath[] = $thfilename;
				$this->up_url[intval($thNum)] = $this->uploadconf['site_url'] . $thfilename;

				if($resize===true)
				{
					// 画像を破棄します。
					@imagedestroy($ufimage);
					@imagedestroy($thumb);
				}
			}
		}
	}

	function imagick_gif_thumb($srcfilename,$descfilename,$newW,$newH)
	{
		$src = new Imagick($srcfilename);
		$dest = new Imagick();
		$colorTransparent = new ImagickPixel("transparent");
		foreach($src as $img)
		{
		    $imageInfo = $img->getImagePage();
		    $tmp = new Imagick();

		    $tmp->newImage($imageInfo['width'], $imageInfo['height'], $colorTransparent, 'gif');
		    $tmp->compositeImage($img, Imagick::COMPOSITE_OVER, $imageInfo['x'], $imageInfo['y']);
		    $tmp->thumbnailImage($newW,$newH, true);

		    $dest->addImage($tmp);
		    $dest->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
		    $dest->setImageDelay($img->getImageDelay());
		    $dest->setImageDispose($img->getImageDispose());
		}
		$dest->coalesceImages();
		set_time_limit(100);
		$dest->writeImages($descfilename, true);
		$dest->clear();
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
		if($width_i == 800 || $fsize == 168)
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

		//yupengbo add 20101208 start
		$str_len = mb_strlen($telop_text);
		if($str_len > 16)
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

				if($i==2) $tmp_telop_text = mb_substr($telop_text,0,16,"utf-8");
				if($i==1) $tmp_telop_text = mb_substr($telop_text,16,16,"utf-8");

				//テキスト描画
				ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $tmp_telop_text);
				ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $font_color_w, $this->font_name, $tmp_telop_text);
			}
			  //wangtongchao 2011/08/23 change start tianjiaif()puanduan
		} elseif($str_len > 0) {
			  //wangtongchao 2011/08/23 change end
		//yupengbo add 20101209 end
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
		}
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
			if((int)$i == 3 || (int)$i == 2)
			{
				//thumb4
				if((int)$i == 3)
				{
					$photo_filename_th1 = $this->up_url[1];
					$tmp = substr($photo_filename_th1,strpos($photo_filename_th1,"./"));
					$tmp1 = str_replace("th1","th4",$tmp);
					$tmp2 = str_replace("thumb1","thumb4",$tmp1);
				} elseif((int)$i == 2) {//thumb3
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
				if((int)$i == 3)
				{
					if($width == 400)
					{
						$font_size = 88;
					} elseif($width == 800) {
						$font_size = 168;
					} elseif($width == 200) {
						$font_size = 38;
					}
				} elseif((int)$i == 2) {//thumb3
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
					//create webp format image
					$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $tmp2);
					imagewebp($thumb, $tmp2_webp);
				}
				else if ($type == IMAGETYPE_GIF)
				{
//					// アップロードしたファイルを読み込みます。
//					$ufimage = @ImageCreateFromGIF($tmp);
//					// 空のサムネイル画像を作成します。
//					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
//					// 空のサムネイル画像にアップロードしたファイルをコピーします。
//					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
//					// 画像にクレジットを書き込みます。
//					$thumb = $this->write_credit2($thumb, "SAMPLE", $font_size, $thumb_width, $thumb_height);
//					@imagegif($thumb, $tmp2);
					$this->imagick_gif_thumb($tmp,$tmp2,$thumb_width, $thumb_height);
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
					//create webp format image
					$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $tmp2);
					imagewebp($thumb, $tmp2_webp);
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
					//create webp format image
					$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $thfilename);
					imagewebp($thumb, $tmp2_webp);
				}
				else if ($type == IMAGETYPE_GIF)
				{
//					// アップロードしたファイルを読み込みます。
//					$ufimage = @ImageCreateFromGIF($srcfilename);
//
//					// 空のサムネイル画像を作成します。
//					$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);
//
//					// 空のサムネイル画像にアップロードしたファイルをコピーします。
//					@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
//
//					// クレジットを書き込みます。
//					//if ($this->write_ok[$i] == true)
//					if ($this->write_ok[$i] == true && !empty($this->credit) && strlen($this->credit) > 0)
//					{
//						// フォントサイズを決定します。
//						$font_size = $this->decide_fontsize($thumb_width);
//
//						// 画像にクレジットを書き込みます。
//						$thumb = $this->write_credit($thumb, $this->credit, $font_size, $thumb_width, $thumb_height);
//					}
//
//					// ファイルを保存します。
//					$thfilename = $this->thumbdir[$i] . $this->dirno . $this->svname . "th" . ($i + 1) . $this->ext;
//					@imagegif($thumb, $thfilename);
					$this->imagick_gif_thumb($srcfilename,$thfilename,$thumb_width, $thumb_height);
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
					//create webp format image
					$tmp2_webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $thfilename);
					imagewebp($thumb, $tmp2_webp);
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

	function imagick_gif_thumb($srcfilename,$descfilename,$newW,$newH)
	{
		$src = new Imagick($srcfilename);
		$dest = new Imagick();
		$colorTransparent = new ImagickPixel("transparent");
		foreach($src as $img)
		{
		    $imageInfo = $img->getImagePage();
		    $tmp = new Imagick();

		    $tmp->newImage($imageInfo['width'], $imageInfo['height'], $colorTransparent, 'gif');
		    $tmp->compositeImage($img, Imagick::COMPOSITE_OVER, $imageInfo['x'], $imageInfo['y']);
		    $tmp->thumbnailImage($newW,$newH, true);

		    $dest->addImage($tmp);
		    $dest->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
		    $dest->setImageDelay($img->getImageDelay());
		    $dest->setImageDispose($img->getImageDispose());
		}
		$dest->coalesceImages();
		$dest->writeImages($descfilename, true);
		$dest->clear();
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

	function __construct() {
		$this->init_data();
	}
	
/* 	function RegistrationClassifications()
	{
		$this->init_data();
	} */

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
	// エラーの場合は画像IDを含めすべて初期化します。
	function get_data(&$pid, &$c_id, &$c_name, &$d_id, &$d_name, &$cp_id, &$cp_name, &$p_id, &$p_name, $no)
	{
		if ($no <= 0 || $no > $this->count)
		{
			$pid = -1;

			$c_id = -1;
			$c_name = "";

			$d_id = -1;
			$d_name = "";

			$cp_id = -1;
			$cp_name = "";

			$p_id = -1;
			$p_name = "";

			return;
			//$this->message = "登録分類データを取得できませんでした。（no<=0）";
			//throw new Exception($this->message);
		}
		$no--;
		$c_id = $this->classification_id[$no];
		$c_name = $this->classification_name[$no];

		$d_id = $this->direction_id[$no];
		$d_name = $this->direction_name[$no];

		$cp_id = $this->country_prefecture_id[$no];
		$cp_name = $this->country_prefecture_name[$no];

		$p_id = $this->place_id[$no];
		$p_name = $this->place_name[$no];

		$pid = $this->photo_id;
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

	// $no:1～
	// エラーの場合はすべて初期化します。
	function get_name(&$c_name, &$d_name, &$cp_name, &$p_name, $no)
	{
		if ($no <= 0 || $no > $this->count)
		{
			$c_name = "";
			$d_name = "";
			$cp_name = "";
			$p_name = "";
			return;
			//$this->message = "登録分類データを取得できませんでした。（no<=0）";
			//throw new Exception($this->message);
		}
		$no--;
		$c_name = $this->classification_name[$no];
		$d_name = $this->direction_name[$no];
		$cp_name = $this->country_prefecture_name[$no];
		$p_name = $this->place_name[$no];
	}

	function set_photo_id($pid)
	{
		if (!is_numeric($pid))
		{
			$this->photo_id = -1;
		}
		else
		{
			$this->photo_id = $pid;
		}
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

	function select_data($db_link, $p_photo_id)
	{
		// 画像IDが数値でない場合は、エラー情報をセットして例外をスローします。
		if (!is_numeric($p_photo_id))
		{
			$this->message = "登録分類を取得できませんでした。（photo_idが数値ではありません。）";
			throw new Exception($this->message);
		}

		// データを初期化します。
		$this->init_data();

		// 登録分類をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT registration_classification.registration_classification_id, registration_classification.photo_id, registration_classification.state, ";
		$sql .= " registration_classification.classification_id, registration_classification.direction_id, registration_classification.country_prefecture_id, registration_classification.place_id, ";
		$sql .= " classification.classification_name, direction.direction_name, country_prefecture.country_prefecture_name, place.place_name FROM registration_classification ";
		$sql .= " LEFT JOIN classification on classification.classification_id = registration_classification.classification_id ";
		$sql .= " LEFT JOIN direction on direction.direction_id = registration_classification.direction_id ";
		$sql .= " LEFT JOIN country_prefecture on country_prefecture.country_prefecture_id = registration_classification.country_prefecture_id ";
		$sql .= " LEFT JOIN place on place.place_id = registration_classification.place_id ";
		$sql .= "WHERE photo_id=" . $p_photo_id . " order by registration_classification_id";
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
				while ($registration_classification = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 分類IDなどを保存します。
					$this->set_data($registration_classification);
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "登録分類を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "登録分類を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
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

	// データ削除時のphoto_idは$this->photo_idではなく、$pidです。
	function delete_data($db_link, $pid)
	{
		// 画像IDが数値の場合は、保存します。
		if (!is_numeric($pid))
		{
			$this->photo_id = $pid;
		}

		$sql = "delete FROM registration_classification WHERE photo_id=" . $pid;
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();

			// 処理数を返します。
			return $icount;
		}
		else
		{
			$this->message = "登録分類をDBから削除できませんでした。（条件設定エラー） ";
			throw new Exception($this->message);
		}
	}
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
	var $nopermis;		                        	//nopermit for no permission selection
	var $nopermis_date;								//nopermit date
	var $nopermis_personid;							//nopermit person id add by jinxin 2012/02/09
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
	var $comp_code;									// ユーザー管理番号
	//xu add it on 2010-12-01 start
	var $photo_url;									//page url
	var $photo_org_no;								//元画像番号
	//xu add it on 2010-12-01 end

	function __construct() {
		// タイムゾーンを設定します。
		date_default_timezone_set("Asia/Tokyo");

		// メンバーを初期化します。
		$this->init_data();

		// メッセージを初期化します。
		$this->message = "";
		$this->error = false;
	}
	
/* 	function PhotoImageData()
	{
		// タイムゾーンを設定します。
		date_default_timezone_set("Asia/Tokyo");

		// メンバーを初期化します。
		$this->init_data();

		// メッセージを初期化します。
		$this->message = "";
	 	$this->error = false;
	}*/

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
		$this->nopermis = "";						// permission
		$this->nopermis_date = "0000-00-00";		// no permit date
		$this->nopermis_personid = "";				// no permit person id add by jinxin 2012/02/09
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
		//xu add it on 2010-12-01 start
		$this->photo_org_no = $imgdata['photo_org_no'];
		$this->photo_url = $imgdata['photo_url'];
		//xu add it on 2010-12-01 end
	}
}

class PhotoImageDB extends PhotoImageData
{
	function __construct() {
		parent::__construct();
		//PhotoImageData::PhotoImageData();
	}
	
	//function PhotoImageDB()
	//{
	//	PhotoImageData::PhotoImageData();
	//}

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
		// 掲載状況ID
		if (!is_numeric($this->publishing_situation_id))
		{
			$this->message = "掲載状況ID(publishing_situation_id)が数値ではありません。";
			throw new Exception($this->message);
		}

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

		// Update用のチェックの場合です。
		if ($act == "U")
		{
			if (!is_numeric($this->photo_id))
			{
				$this->message = "写真ID(photo_id)が数値ではありません。";
				throw new Exception($this->message);
			}

			// 登録許可日
			if ($this->publishing_situation_id > 1)
			{
				// 掲載状況が申請中以外の場合は、登録許可日を設定します。
				$this->permission_date = date("Y/m/d");
			}
			else
			{
				// 掲載状況が申請中の場合は、登録許可日・登録許可アカウント、登録許可名をすべて未設定にします。
				$this->permission_date = "0000-00-00";
				$this->permission_account = "";
				$this->permission_person = "";
			}
		}
	}

	function get_keyword_str($db_link, $p_photo_id)
	{
		if (!is_numeric($p_photo_id))
		{
			$this->message = "画像ID(引数：p_photo_id)に数値以外が設定されています。";
			throw new Exception($this->message);
		}

		$sql = "SELECT * FROM keyword WHERE photo_id = ?";

		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $p_photo_id);
		$result = $stmt->execute();
		if ($result == true)
		{
			$this->keyword_str = "";

			while ($keyword = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				if (strlen($this->keyword_str)!=0)
				{
					$this->keyword_str .= " ";
				}
				$this->keyword_str .= $keyword['keyword_name'];
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "キーワードの読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	function delete_keyword($db_link, $p_photo_id)
	{
		if (!is_numeric($p_photo_id))
		{
			$this->message = "画像ID(引数：p_photo_id)に数値以外が設定されています。";
			throw new Exception($this->message);
		}

		$sql = "delete FROM keyword WHERE photo_id=" . $p_photo_id;

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();
			return $icount;
		}
		else
		{
			$this->message = "キーワードの削除に失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	//xu add it on 2010-12-07 start
	//select the expired datas.
	function select_expired_data($db_link)
	{
		$sql = "SELECT * FROM photoimg LEFT JOIN user  ON  photoimg.registration_account = user.login_id WHERE now()>=photoimg.dfrom and now()<=photoimg.dto";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				while($data=$stmt->fetch(PDO::FETCH_ASSOC))
				{
					$expired_data[] = $data;
				}
				return $expired_data;
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}
	//xu add it on 2010-12-07 end

	function select_data($db_link, $p_photo_id)
	{
		if (!is_numeric($p_photo_id))
		{
			$this->message = "画像ID(引数：p_photo_id)に数値以外が設定されています。";
			throw new Exception($this->message);
		}

		$sql = "SELECT * FROM photoimg WHERE photo_id = ?";

		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $p_photo_id);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();

			// 選択されたデータ数が１かどうかチェックします。
			if ($icount == 1)
			{
				// 画像データをセットします。
				$image_data = $stmt->fetch(PDO::FETCH_ASSOC);
				$this->set_data($image_data);

				// 分類などをセットします。
				$this->registration_classifications->select_data($db_link, $this->photo_id);
			}
			else
			{
				$this->message = "画像の読み込みに失敗しました。（処理数!=1）";
				throw new Exception($this->message);
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	function get_dfrom(&$p_year, &$p_month, &$p_day)
	{
		// 掲載期間（From）をチェックします。
		if (empty($this->dfrom) || $this->dfrom == "0000-00-00")
		{
			// 空もしくは初期値だった場合です。
			$p_year = "";
			$p_month = "";
			$p_day = "";

			return "";
		}
		else
		{
			// 値が入っている場合です。
			$p_year = substr($this->dfrom, 0 , 4);
			$p_month = substr($this->dfrom, 5 , 2);
			$p_day = substr($this->dfrom, 8 , 2);

			return $this->dfrom;
		}
	}

	function get_dto(&$p_year, &$p_month, &$p_day)
	{
		// 掲載期間（to）をチェックします。
		if (empty($this->dto) || $this->dto == "0000-00-00")
		{
			// 空もしくは初期値だった場合です。
			$p_year = "";
			$p_month = "";
			$p_day = "";

			return "";
		}
		else
		{
			// 値が入っている場合です。
			$p_year = substr($this->dto, 0 , 4);
			$p_month = substr($this->dto, 5 , 2);
			$p_day = substr($this->dto, 8 , 2);

			return $this->dto;
		}
	}

	/*
	 * 関数名：write_imagetodb
	 * 関数説明：画像をバイナリになって、DBに保存する
	 * パラメタ：db_link：ＤＢリンク
	 * 戻り値：無し
	 */
	function write_imagetodb($db_link,$p_photo_id)
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
			$tmp = substr($this->photo_filename,strpos($this->photo_filename,"./"));
			if (!file_exists($tmp)) return;
			$b_image1 = file_get_contents($tmp);

			$tmp = substr($this->photo_filename_th1,strpos($this->photo_filename_th1,"./"));
			if (!file_exists($tmp)) return;
			$b_image2 = file_get_contents($tmp);

			$tmp = substr($this->photo_filename_th2,strpos($this->photo_filename_th2,"./"));
			if (!file_exists($tmp)) return;
			$b_image3 = file_get_contents($tmp);

			$tmp = substr($this->photo_filename_th4,strpos($this->photo_filename_th4,"./"));
			if (!file_exists($tmp)) return;
			$b_image5 = file_get_contents($tmp);

			$pcnt = $stmt->fetch(PDO::FETCH_ASSOC);
			$tmpcnt = $pcnt['cnt'];

			//既に存在の場合に更新する
			if ((int)$tmpcnt > 0)
			{
				// SQL文の作成
				$sql = "UPDATE photo_imgdata SET ";
				$sql .= "image1= ?,";
				$sql .= "image2= ?,";
				$sql .= "image3= ?,";
				$sql .= "image5= ?";

				// 更新条件の設定
				$sql .= " WHERE photo_id=" . $p_photo_id;
				// SQL文法のチェック
				$stmt = $db_link->prepare($sql);
				// パラメータの設定
				$stmt->bindValue(1, $b_image1, PDO::PARAM_LOB);
				$stmt->bindValue(2, $b_image2, PDO::PARAM_LOB);
				$stmt->bindValue(3, $b_image3, PDO::PARAM_LOB);
				$stmt->bindValue(4, $b_image5, PDO::PARAM_LOB);

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
						// $err = $stmt->errorInfo();
						// $this->message = "画像データの更新に失敗しました。（条件設定エラー）";
						// //throw new Exception($this->message);
						// throw new Exception($err[2]);
					}
				} catch(Exception $e) {
					// ロールバックします。
					// //$db_link->rollBack();
					// // 例外をスローします。
					// $msg = $e->getMessage();
					// throw new Exception($msg);
				}
			} else {
			//存在しない場合に新規する
				// SQL文の作成
				$sql = "INSERT INTO photo_imgdata (  photo_id,
													 image1,
													 image2,
													 image3,
													 image5
									 	 		  ) VALUES ($p_photo_id,?,?,?,?)";
				// SQL文法のチェック
				$stmt = $db_link->prepare($sql);
				// パラメータの設定
				$stmt->bindValue(1, $b_image1, PDO::PARAM_LOB);
				$stmt->bindValue(2, $b_image2, PDO::PARAM_LOB);
				$stmt->bindValue(3, $b_image3, PDO::PARAM_LOB);
				$stmt->bindValue(4, $b_image5, PDO::PARAM_LOB);

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
						// // 例外をスローします。
						// $err = $stmt->errorInfo();
						// $this->message = "画像データの更新に失敗しました。（条件設定エラー）";
						// //throw new Exception($this->message);
						// throw new Exception($err[2]);
					}
				} catch(Exception $e) {
					// ロールバックします。
					// //$db_link->rollBack();
					// // 例外をスローします。
					// $msg = $e->getMessage();
					// throw new Exception($msg);
				}
			}
		} else {
			// $this->message = "画像データの更新に失敗しました。（条件設定エラー）";
			// throw new Exception($this->message);
		}
	}


	/*
	 * 関数名：update_data
	 * 関数説明：ＤＢの更新
	 * パラメタ：db_link：ＤＢリンク
	 * 戻り値：無し
	 */
	function update_data($db_link)
	{
		// パラメータのチェックと調整をします。
		$this->check_adjust_param("U");

		// 写真データを更新します。
		$sql = "UPDATE photoimg SET ";
		//yupengbo modify 2011/12/15 start
		$sql .= "publishing_situation_id=" . $this->publishing_situation_id . ",";
		$sql .= "registration_division_id=" . $this->registration_division_id . ",";
		$sql .= "take_picture_time_id=" . $this->take_picture_time_id . ",";
		$sql .= "take_picture_time2_id=" . $this->take_picture_time2_id . ",";
		$sql .= "borrowing_ahead_id=" . $this->borrowing_ahead_id . ",";
		$sql .= "range_of_use_id=" . $this->range_of_use_id . ",";
		$sql .= "monopoly_use=" . $this->monopoly_use . ",";
//		$sql .= "photo_mno=\"".$this->photo_mno. "\",";
//		$sql .= "source_image_no=\"".$this->source_image_no."\",";
//		$sql .= "bud_photo_no=\"".$this->bud_photo_no."\",";
//		$sql .= "photo_name=\"".$this->photo_name."\",";
//		$sql .= "photo_explanation=\"".$this->photo_explanation."\",";
//		$sql .= "dfrom=\"".$this->dfrom."\",";
//		$sql .= "dto=\"".$this->dto."\",";
//		$sql .= "kikan=\"".$this->kikan."\",";
//		$sql .= "content_borrowing_ahead=\"".$this->content_borrowing_ahead."\",";
//		$sql .= "use_condition=\"".$this->use_condition."\",";
//		$sql .= "additional_constraints1=\"".$this->additional_constraints1."\",";
//		$sql .= "additional_constraints2=\"".$this->additional_constraints2."\",";
//		$sql .= "copyright_owner=\"".$this->copyright_owner."\",";
//		$sql .= "customer_section=\"".$this->customer_section."\",";
//		$sql .= "customer_name=\"".$this->customer_name."\",";
//		$sql .= "permission_account=\"".$this->permission_account."\",";
//		$sql .= "permission_person=\"".$this->permission_person."\",";
//		$sql .= "permission_date=\"".$this->permission_date."\",";
//		$sql .= "note=\"".$this->note."\",";
//		//xu  add it on 2010-12-01 start
//		$sql .= "photo_org_no=\"".$this->photo_org_no."\",";
//		$sql .= "photo_url=\"".$this->photo_url."\"";
//		//xu  add it on 2010-12-01 end
//		$sql .= " WHERE photo_id=" . $this->photo_id;

		$sql .= "photo_mno=?,";
		$sql .= "source_image_no=?,";
		$sql .= "bud_photo_no=?,";
		$sql .= "photo_name=?,";
		$sql .= "photo_explanation=?,";
		$sql .= "dfrom=?,";
		$sql .= "dto=?,";
		$sql .= "kikan=?,";
		$sql .= "content_borrowing_ahead=?,";
		$sql .= "use_condition=?,";
		$sql .= "additional_constraints1=?,";
		$sql .= "additional_constraints2=?,";
		$sql .= "copyright_owner=?,";
		$sql .= "customer_section=?,";
		$sql .= "customer_name=?,";
		$sql .= "permission_account=?,";
		$sql .= "permission_person=?,";
		$sql .= "permission_date=?,";
		$sql .= "note=?,";
		$sql .= "photo_org_no=?,";
		$sql .= "photo_url=?,";
		//jinxin 2012/02/09 modify start
		$sql .= "nopermit_note=?,";
		$sql .= "nopermit_date=?,";
		$sql .= "nopermit_personid=?,";
		$sql .= "register_date=?";
		$sql .= " WHERE photo_id=?";
		//yupengbo modify 2011/12/15 end
		//jinxin modify 2012/02/07 end

		$stmt = $db_link->prepare($sql);
		//yupengbo add 2011/12/15 start
		$stmt->bindParam(1,$this->photo_mno);
		if(!empty($this->source_image_no))
		{
			$stmt->bindParam(2,$this->source_image_no);
		} else {
			$stmt->bindValue(2,null);
		}
		if(!empty($this->bud_photo_no))
		{
			$stmt->bindParam(3,$this->bud_photo_no);
		} else {
			$stmt->bindValue(3,null);
		}
		if(!empty($this->photo_name))
		{
			$stmt->bindParam(4,$this->photo_name);
		} else {
			$stmt->bindValue(4,null);
		}
		if(!empty($this->photo_explanation))
		{
			$stmt->bindParam(5,$this->photo_explanation);
		} else {
			$stmt->bindValue(5,null);
		}
		if(!empty($this->dfrom))
		{
			$stmt->bindParam(6,$this->dfrom);
		} else {
			$stmt->bindValue(6,null);
		}
		if(!empty($this->dto))
		{
			$stmt->bindParam(7,$this->dto);
		} else {
			$stmt->bindValue(7,null);
		}
		if(!empty($this->kikan))
		{
			$stmt->bindParam(8,$this->kikan);
		} else {
			$stmt->bindValue(8,null);
		}
		if(!empty($this->content_borrowing_ahead))
		{
			$stmt->bindParam(9,$this->content_borrowing_ahead);
		} else {
			$stmt->bindValue(9,null);
		}
		if(!empty($this->use_condition))
		{
			$stmt->bindParam(10,$this->use_condition);
		} else {
			$stmt->bindValue(10,null);
		}
		if(!empty($this->additional_constraints1))
		{
			$stmt->bindParam(11,$this->additional_constraints1);
		} else {
			$stmt->bindValue(11,null);
		}
		if(!empty($this->additional_constraints2))
		{
			$stmt->bindParam(12,$this->additional_constraints2);
		} else {
			$stmt->bindValue(12,null);
		}
		if(!empty($this->copyright_owner))
		{
			$stmt->bindParam(13,$this->copyright_owner);
		} else {
			$stmt->bindValue(13,null);
		}
		if(!empty($this->customer_section))
		{
			$stmt->bindParam(14,$this->customer_section);
		} else {
			$stmt->bindValue(14,null);
		}
		if(!empty($this->customer_name))
		{
			$stmt->bindParam(15,$this->customer_name);
		} else {
			$stmt->bindValue(15,null);
		}
		if(!empty($this->permission_account))
		{
			$stmt->bindParam(16,$this->permission_account);
		} else {
			$stmt->bindValue(16,null);
		}
		if(!empty($this->permission_person))
		{
			$stmt->bindParam(17,$this->permission_person);
		} else {
			$stmt->bindValue(17,null);
		}
		if(!empty($this->permission_date))
		{
			$stmt->bindParam(18,$this->permission_date);
		} else {
			$stmt->bindValue(18,null);
		}
		if(!empty($this->note))
		{
			$stmt->bindParam(19,$this->note);
		} else {
			$stmt->bindValue(19,null);
		}
		if(!empty($this->photo_org_no))
		{
			$stmt->bindParam(20,$this->photo_org_no);
		} else {
			$stmt->bindValue(20,null);
		}
		if(!empty($this->photo_url))
		{
			$stmt->bindParam(21,$this->photo_url);
		} else {
			$stmt->bindValue(21,null);
		}
		//jinxin 2012/02/09 modify start
		if(!empty($this->nopermis))
		{
			$stmt->bindParam(22,$this->nopermis);
		} else {
			$stmt->bindValue(22,null);
		}
		if(!empty($this->nopermis_date))
		{
			$stmt->bindParam(23,$this->nopermis_date);
		} else {
			$stmt->bindValue(23,null);
		}
		if(!empty($this->nopermis_personid))
		{
			$stmt->bindParam(24,$this->nopermis_personid);
		} else {
			$stmt->bindValue(24,null);
		}
		
		if(!empty($this->register_date))
		{
		    $stmt->bindParam(25,$this->register_date);
		} else {
		    $stmt->bindValue(25,null);
		}

		$stmt->bindParam(26,$this->photo_id);
		//jinxin 2012/02/09 modify end
		//yupengbo add 2011/12/15 end

		// トランザクションを開始します。（オートコミットがオフになります。）
		$db_link->beginTransaction();

		try
		{
			$result = $stmt->execute();
			if ($result == true)
			{
				// キーワードを別テーブルに登録します。
				$this->delete_keyword($db_link, $this->photo_id);
				$this->insert_keyword($db_link, $this->photo_id, $this->keyword_str);

				// 分類を別テーブルに登録します。
				// ※すでにphoto_id以外はすべてデータセット済みです
				$this->registration_classifications->delete_data($db_link, $this->photo_id);
				$this->registration_classifications->insert_data($db_link, $this->photo_id);
				$this->update_keyword($db_link);

				// コミットします。
				$db_link->commit();
			}
			else
			{
				$err = $stmt->errorInfo();
				$this->message = "画像データの更新に失敗しました。（条件設定エラー）";
				//throw new Exception($this->message);
				throw new Exception($err[2]);
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
	
	/**
	 * 関数名：batch_update_data
	 * 関数説明：一括更新
	 * パラメタ：db_link：ＤＢリンク
	 * 戻り値：無し
	 */
	function batch_update_data($db_link)
	{
	    // パラメータのチェックと調整をします。
	    $this->check_adjust_param("U");
	
	    // 写真データを更新します。
	    $sql = "UPDATE photoimg SET ";
	    //yupengbo modify 2011/12/15 start
	    $sql .= "publishing_situation_id=" . $this->publishing_situation_id . ",";
	    $sql .= "registration_division_id=" . $this->registration_division_id . ",";
	    $sql .= "take_picture_time_id=" . $this->take_picture_time_id . ",";
	    $sql .= "take_picture_time2_id=" . $this->take_picture_time2_id . ",";
	    $sql .= "borrowing_ahead_id=" . $this->borrowing_ahead_id . ",";
	    $sql .= "range_of_use_id=" . $this->range_of_use_id . ",";
	    $sql .= "monopoly_use=" . $this->monopoly_use . ",";
	
	    $sql .= "photo_mno=?,";
	    $sql .= "source_image_no=?,";
	    $sql .= "bud_photo_no=?,";
	    $sql .= "photo_name=?,";
	    $sql .= "photo_explanation=?,";
	    $sql .= "dfrom=?,";
	    $sql .= "dto=?,";
	    $sql .= "kikan=?,";
	    $sql .= "content_borrowing_ahead=?,";
	    $sql .= "use_condition=?,";
	    $sql .= "additional_constraints1=?,";
	    $sql .= "additional_constraints2=?,";
	    $sql .= "copyright_owner=?,";
	    $sql .= "customer_section=?,";
	    $sql .= "customer_name=?,";
	    $sql .= "permission_account=?,";
	    $sql .= "permission_person=?,";
	    $sql .= "permission_date=?,";
	    $sql .= "note=?,";
	    $sql .= "photo_org_no=?,";
	    $sql .= "photo_url=?,";
	    $sql .= "nopermit_note=?,";
	    $sql .= "nopermit_date=?,";
	    $sql .= "nopermit_personid=?,";
	    
	    $sql .= "photo_filename=?,";
	    $sql .= "photo_filename_th1=?,";
	    $sql .= "photo_filename_th2=?,";
	    $sql .= "photo_filename_th3=?,";
	    $sql .= "photo_filename_th4=?,";
	    $sql .= "photo_filename_th5=?,";
	    $sql .= "photo_filename_th6=?,";
	    $sql .= "photo_filename_th7=?,";
	    $sql .= "photo_filename_th8=?,";
	    $sql .= "photo_filename_th9=?,";
	    $sql .= "photo_filename_th10=?,";
	    
	    $sql .= "ext=?,";
	    $sql .= "image_size_x=" . $this->image_size_x . ",";
	    $sql .= "image_size_y=" . $this->image_size_y . ",";
	    $sql .= "register_date=?";
	    
	    $sql .= " WHERE photo_id=?";
	
	    $stmt = $db_link->prepare($sql);
	    
	    $stmt->bindParam(1,$this->photo_mno);
	    if(!empty($this->source_image_no))
	    {
	        $stmt->bindParam(2,$this->source_image_no);
	    } else {
	        $stmt->bindValue(2,null);
	    }
	    if(!empty($this->bud_photo_no))
	    {
	        $stmt->bindParam(3,$this->bud_photo_no);
	    } else {
	        $stmt->bindValue(3,null);
	    }
	    if(!empty($this->photo_name))
	    {
	        $stmt->bindParam(4,$this->photo_name);
	    } else {
	        $stmt->bindValue(4,null);
	    }
	    if(!empty($this->photo_explanation))
	    {
	        $stmt->bindParam(5,$this->photo_explanation);
	    } else {
	        $stmt->bindValue(5,null);
	    }
	    if(!empty($this->dfrom))
	    {
	        $stmt->bindParam(6,$this->dfrom);
	    } else {
	        $stmt->bindValue(6,null);
	    }
	    if(!empty($this->dto))
	    {
	        $stmt->bindParam(7,$this->dto);
	    } else {
	        $stmt->bindValue(7,null);
	    }
	    if(!empty($this->kikan))
	    {
	        $stmt->bindParam(8,$this->kikan);
	    } else {
	        $stmt->bindValue(8,null);
	    }
	    if(!empty($this->content_borrowing_ahead))
	    {
	        $stmt->bindParam(9,$this->content_borrowing_ahead);
	    } else {
	        $stmt->bindValue(9,null);
	    }
	    if(!empty($this->use_condition))
	    {
	        $stmt->bindParam(10,$this->use_condition);
	    } else {
	        $stmt->bindValue(10,null);
	    }
	    if(!empty($this->additional_constraints1))
	    {
	        $stmt->bindParam(11,$this->additional_constraints1);
	    } else {
	        $stmt->bindValue(11,null);
	    }
	    if(!empty($this->additional_constraints2))
	    {
	        $stmt->bindParam(12,$this->additional_constraints2);
	    } else {
	        $stmt->bindValue(12,null);
	    }
	    if(!empty($this->copyright_owner))
	    {
	        $stmt->bindParam(13,$this->copyright_owner);
	    } else {
	        $stmt->bindValue(13,null);
	    }
	    if(!empty($this->customer_section))
	    {
	        $stmt->bindParam(14,$this->customer_section);
	    } else {
	        $stmt->bindValue(14,null);
	    }
	    if(!empty($this->customer_name))
	    {
	        $stmt->bindParam(15,$this->customer_name);
	    } else {
	        $stmt->bindValue(15,null);
	    }
	    if(!empty($this->permission_account))
	    {
	        $stmt->bindParam(16,$this->permission_account);
	    } else {
	        $stmt->bindValue(16,null);
	    }
	    if(!empty($this->permission_person))
	    {
	        $stmt->bindParam(17,$this->permission_person);
	    } else {
	        $stmt->bindValue(17,null);
	    }
	    if(!empty($this->permission_date))
	    {
	        $stmt->bindParam(18,$this->permission_date);
	    } else {
	        $stmt->bindValue(18,null);
	    }
	    if(!empty($this->note))
	    {
	        $stmt->bindParam(19,$this->note);
	    } else {
	        $stmt->bindValue(19,null);
	    }
	    if(!empty($this->photo_org_no))
	    {
	        $stmt->bindParam(20,$this->photo_org_no);
	    } else {
	        $stmt->bindValue(20,null);
	    }
	    if(!empty($this->photo_url))
	    {
	        $stmt->bindParam(21,$this->photo_url);
	    } else {
	        $stmt->bindValue(21,null);
	    }
	    //jinxin 2012/02/09 modify start
	    if(!empty($this->nopermis))
	    {
	        $stmt->bindParam(22,$this->nopermis);
	    } else {
	        $stmt->bindValue(22,null);
	    }
	    if(!empty($this->nopermis_date))
	    {
	        $stmt->bindParam(23,$this->nopermis_date);
	    } else {
	        $stmt->bindValue(23,null);
	    }
	    if(!empty($this->nopermis_personid))
	    {
	        $stmt->bindParam(24,$this->nopermis_personid);
	    } else {
	        $stmt->bindValue(24,null);
	    }
        
        //图片链接
        if (! empty($this->up_url[0])) {
            $stmt->bindParam(25, $this->up_url[0]);
        } else {
            $stmt->bindValue(25, null);
        }
        
        if (! empty($this->up_url[1])) {
            $stmt->bindParam(26, $this->up_url[1]);
        } else {
            $stmt->bindValue(26, null);
        }
        
        if (! empty($this->up_url[2])) {
            $stmt->bindParam(27, $this->up_url[2]);
        } else {
            $stmt->bindValue(27, null);
        }
        
        if (! empty($this->up_url[3])) {
            $stmt->bindParam(28, $this->up_url[3]);
        } else {
            $stmt->bindValue(28, null);
        }
        
        if (! empty($this->up_url[4])) {
            $stmt->bindParam(29, $this->up_url[4]);
        } else {
            $stmt->bindValue(29, null);
        }
        
        if (! empty($this->up_url[5])) {
            $stmt->bindParam(30, $this->up_url[5]);
        } else {
            $stmt->bindValue(30, null);
        }
        
        if (! empty($this->up_url[6])) {
            $stmt->bindParam(31, $this->up_url[6]);
        } else {
            $stmt->bindValue(31, null);
        }
        
        if (! empty($this->up_url[7])) {
            $stmt->bindParam(32, $this->up_url[7]);
        } else {
            $stmt->bindValue(32, null);
        }
        
        if (! empty($this->up_url[8])) {
            $stmt->bindParam(33, $this->up_url[8]);
        } else {
            $stmt->bindValue(33, null);
        }
        
        if (! empty($this->up_url[9])) {
            $stmt->bindParam(34, $this->up_url[9]);
        } else {
            $stmt->bindValue(34, null);
        }
        
        if (! empty($this->up_url[10])) {
            $stmt->bindParam(35, $this->up_url[10]);
        } else {
            $stmt->bindValue(35, null);
        }
        
        $stmt->bindParam(36, $this->ext);
        if(!empty($this->register_date))
        {
            $stmt->bindParam(37,$this->register_date);
        } else {
            $stmt->bindValue(37,null);
        }
	
	    $stmt->bindParam(38,$this->photo_id);
	
	    // トランザクションを開始します。（オートコミットがオフになります。）
	    $db_link->beginTransaction();
	
	    try
	    {
	        $result = $stmt->execute();
	        if ($result == true)
	        {
	            // キーワードを別テーブルに登録します。
	            $this->delete_keyword($db_link, $this->photo_id);
	            $this->insert_keyword($db_link, $this->photo_id, $this->keyword_str);
	
	            // 分類を別テーブルに登録します。
	            // ※すでにphoto_id以外はすべてデータセット済みです
	            $this->registration_classifications->delete_data($db_link, $this->photo_id);
	            $this->registration_classifications->insert_data($db_link, $this->photo_id);
	            $this->update_keyword($db_link);
	
	            // コミットします。
	            $db_link->commit();
	        }
	        else
	        {
	            $err = $stmt->errorInfo();
	            $this->message = "画像データの更新に失敗しました。（条件設定エラー）";
	            //throw new Exception($this->message);
	            throw new Exception($err[2]);
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

	//yupengbo add 2011/12/12 start
	/*
	 * 関数名：update_data_batch
	 * 関数説明：ＤＢの更新
	 * パラメタ：db_link：ＤＢリンク
	 * 戻り値：無し
	 */
	function update_data_batch($db_link)
	{
		// 写真データを更新します。
		$sql = "UPDATE photoimg SET ";
		$sql .= "publishing_situation_id=?,";
		$sql .= "photo_mno=?,";
		$sql .= "permission_account=?,";
		$sql .= "permission_person=?,";
		$sql .= "permission_date=?,";
		$sql .= "photo_filename=?,";
		$sql .= "photo_filename_th1=?,";
		$sql .= "photo_filename_th2=?,";
		$sql .= "photo_filename_th3=?,";
		$sql .= "photo_filename_th4=?,";
		$sql .= "photo_filename_th7=?,";
		$sql .= "ext=?";
		$sql .= " WHERE photo_id=?";

		$stmt = $db_link->prepare($sql);
		
		$stmt->bindParam(1, $this->publishing_situation_id);
		$stmt->bindParam(2, $this->photo_mno);
		$stmt->bindParam(3, $this->permission_account);
		$stmt->bindParam(4, $this->permission_person);
		$stmt->bindParam(5, $this->permission_date);
		$stmt->bindParam(6, $this->photo_filename);
		$stmt->bindParam(7, $this->photo_filename_th1);
		$stmt->bindParam(8, $this->photo_filename_th2);
		$stmt->bindParam(9, $this->photo_filename_th3);
		$stmt->bindParam(10, $this->photo_filename_th4);		
		$stmt->bindParam(11, $this->photo_filename_th7);	
		$stmt->bindParam(12, $this->ext);
		$stmt->bindParam(13, $this->photo_id);


		// トランザクションを開始します。（オートコミットがオフになります。）
		$db_link->beginTransaction();

		try
		{
			$result = $stmt->execute();
			if ($result == true)
			{
				//
				$keyword_id_temp = array();
				$keyword_name_temp = array();
				$this->get_keyword($db_link,$keyword_id_temp,$keyword_name_temp,$this->photo_id);
				if(isset($keyword_name_temp[0]))
				{
					$keyword_name = array();
					$keyword_name = explode(" ",trim($keyword_name_temp[0]));
					$keyword_name[0] = $this->photo_mno;
					$keyword_name[1] = "";
					$this->update_keyword($db_link,implode(" ",$keyword_name));
				}
				//
				// コミットします。
				$db_link->commit();
			}
			else
			{
				$err = $stmt->errorInfo();
				$this->message = "画像データの更新に失敗しました。（条件設定エラー）";
				//throw new Exception($this->message);
				throw new Exception($err[2]);
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
	function update_photo($db_link){
		// 写真データを更新します。
		$sql = "UPDATE photoimg SET ";
		$sql .= "photo_filename=?,";
		$sql .= "photo_filename_th1=?,";
		$sql .= "photo_filename_th2=?,";
		$sql .= "photo_filename_th3=?,";
		$sql .= "photo_filename_th4=?,";
		$sql .= "photo_filename_th7=?,";
		$sql .= "ext=?";
		$sql .= " WHERE photo_id=?";

		$stmt = $db_link->prepare($sql);
		
		$stmt->bindParam(1, $this->photo_filename);
		$stmt->bindParam(2, $this->photo_filename_th1);
		$stmt->bindParam(3, $this->photo_filename_th2);
		$stmt->bindParam(4, $this->photo_filename_th3);
		$stmt->bindParam(5, $this->photo_filename_th4);		
		$stmt->bindParam(6, $this->photo_filename_th7);	
		$stmt->bindParam(7, $this->ext);
		$stmt->bindParam(8, $this->photo_id);


		// トランザクションを開始します。（オートコミットがオフになります。）
		$db_link->beginTransaction();

		try
		{
			$result = $stmt->execute();
			if ($result == true)
			{
				//
				$keyword_id_temp = array();
				$keyword_name_temp = array();
				$this->get_keyword($db_link,$keyword_id_temp,$keyword_name_temp,$this->photo_id);
				if(isset($keyword_name_temp[0]))
				{
					$keyword_name = array();
					$keyword_name = explode(" ",trim($keyword_name_temp[0]));
					$keyword_name[0] = $this->photo_mno;
					$keyword_name[1] = "";
					$this->update_keyword($db_link,implode(" ",$keyword_name));
				}
				//
				// コミットします。
				$db_link->commit();
			}
			else
			{
				$err = $stmt->errorInfo();
				$this->message = "画像データの更新に失敗しました。（条件設定エラー）";
				//throw new Exception($this->message);
				throw new Exception($err[2]);
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
	//yupengbo add 2011/12/12 end
	/*
	 * 関数名：update_keyword
	 * 関数説明：キーワードの更新
	 * パラメタ：db_link：ＤＢリンク
	 * パラメタ：keyword_name：キーワード //yupengbo add 2011/12/12
	 * 戻り値：無し
	 */
	function update_keyword($db_link,$keyword_name="")
	{
		// キーワードテーブルを更新します。
		$sql = "UPDATE keyword SET ";
		//yupengbo modify 2011/12/15 start
//		$sql .= "publishing_situation_id=". $this->publishing_situation_id;
//		//yupengbo add 2011/12/12 start
//		if(!empty($keyword_name))
//		{
//			$sql .= ",keyword_name='".$keyword_name."'";
//		}
//		//yupengbo add 2011/12/12 end
//		$sql .= " WHERE photo_id=" . $this->photo_id;
		$sql .= "publishing_situation_id=?";
		if(!empty($keyword_name)) $sql .= ",keyword_name=?";
		$sql .= " WHERE photo_id=?";
		//yupengbo modify 2011/11/15 end

		try
		{
			$stmt = $db_link->prepare($sql);
			//yupengbo add 2011/12/15 start
			$stmt->bindParam(1,$this->publishing_situation_id);
			if(!empty($keyword_name))
			{
				$stmt->bindParam(2,$keyword_name);
				$stmt->bindParam(3,$this->photo_id);
			} else {
				$stmt->bindParam(2,$this->photo_id);
			}
			//yupengbo add 2011/12/15 end
			$result = $stmt->execute();
			if ($result == false)
			{
				$err = $stmt->errorInfo();
				$this->message = "画像データの更新に失敗しました。（条件設定エラー）";
				throw new Exception($this->message);
			}
		}
		catch(Exception $e)
		{
			// 例外をスローします。
			$msg = $e->getMessage();
			throw new Exception($msg);
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

	// 国・都道府県名をDBより取得します。（海外用）
	function get_country_prefecture_name2($db_link, $db_id, &$db_name)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($db_id) || $db_id == "-1")
		{
			return;
		}

		// 国・都道府県情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT ";
		$sql .= "country_id, country_name_case0,country_name_case1,country_name_case2,";
		$sql .= "country_name_case3,country_name_case4,country_name_case5,country_name_case6,";
		$sql .= "country_name_case7,country_name_case8,country_name_case9,country_name_case10";
		$sql .= " FROM country_case WHERE country_id = ? ";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $db_id);
		// SQLを実行します。
		$result = $stmt->execute();
		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				$country_prefecture = $stmt->fetch(PDO::FETCH_ASSOC);
				//  国・都道府県名を保存します。
				$db_name = "";
				if(!empty($country_prefecture['country_name_case0'])) $db_name .= $country_prefecture['country_name_case0']." ";
				if(!empty($country_prefecture['country_name_case1'])) $db_name .= $country_prefecture['country_name_case1']." ";
				if(!empty($country_prefecture['country_name_case2'])) $db_name .= $country_prefecture['country_name_case2']." ";
				if(!empty($country_prefecture['country_name_case3'])) $db_name .= $country_prefecture['country_name_case3']." ";
				if(!empty($country_prefecture['country_name_case4'])) $db_name .= $country_prefecture['country_name_case4']." ";
				if(!empty($country_prefecture['country_name_case5'])) $db_name .= $country_prefecture['country_name_case5']." ";
				if(!empty($country_prefecture['country_name_case6'])) $db_name .= $country_prefecture['country_name_case6']." ";
				if(!empty($country_prefecture['country_name_case7'])) $db_name .= $country_prefecture['country_name_case7']." ";
				if(!empty($country_prefecture['country_name_case8'])) $db_name .= $country_prefecture['country_name_case8']." ";
				if(!empty($country_prefecture['country_name_case9'])) $db_name .= $country_prefecture['country_name_case9'];
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

	//added by wangtongchao 2011-12-20 begin
	function get_registration_person($db_link, $db_id, &$registration_person)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($db_id) || $db_id == "-1")
		{
			$registration_person = "";
			return "";
		}

		// 画像登録申請者をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT `photoimg`.`registration_person` FROM photoimg WHERE `photoimg`.`photo_id` = ? ";
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
				$registration_person_ary = $stmt->fetch(PDO::FETCH_ASSOC);
				//  画像登録申請者を保存します。
				$registration_person = $registration_person_ary['registration_person'];
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "画像登録申請者を取得できませんでした。（取得数<0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "画像登録申請者を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}
	//added by wangtongchao 2011-12-20 end
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
			//海外
			if((int)$c_id == 1)
			{
				// 方面名を取得する
				$d_name = "";
				if (!empty($d_id)) $this->get_direction_name($db_link,$d_id,$d_name);

				// 国・都道府県名を取得する
				$cp_name = "";
				if (!empty($d_id)) $this->get_country_prefecture_name2($db_link,$cp_id,$cp_name);

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
				}

				//地名
				if (!empty($p_name) && strlen($p_name) > 0)
				{
					$insert_keyword_str .= " ".$p_name;
				}
			} else {
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
	//					print "<script type=\"text/javascript\">";
	//					print "alert(\"".$cp_name."\");";
	//					print "</script>";
						if($cp_name == "京都" || $cp_name == "大阪")
						{
							$insert_keyword_str .= " ".$cp_name."府";
						} elseif($cp_name == "東京") {
							$insert_keyword_str .= " ".$cp_name."都";
						} else {
							$insert_keyword_str .= " ".$cp_name."県";
						}
	//					print "<script type=\"text/javascript\">";
	//					print "alert(\"".$insert_keyword_str."\");";
	//					print "</script>";
					}
				}

				if (!empty($p_name) && strlen($p_name) > 0)
				{
					$insert_keyword_str .= " ".$p_name;
				}
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
			//yupengbo modify 2011/12/15 start
			$sql = "INSERT INTO keyword (photo_id, keyword_name) VALUES ( ";
			//$sql .= $pid . ",'" . $keyword_str . "')";
			$sql .= "?,?)";
			//yupengbo modify 2011/12/15 end

			$stmt = $db_link->prepare($sql);
			//yupengbo add 2011/12/15 start
			$stmt->bindParam(1,$pid);
			$stmt->bindParam(2,$keyword_str);
			//yupengbo add 2011/12/15 end

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
	 * 関数名：get_photo_mno
	 * 関数説明：画像IDより画像管理番号を取得する
	 * パラメタ：
	 * db_link：ＤＢリンク
	 * pid	　：画像番号
	 * p_mno　：画像管理番号
	 * p_photoname　：画像名前
	 * photo_explanation　：画像詳細内容
	 * bud_photo_no　：BUD Photo番号
	 * registration_person　：申請者
	 * date_from　：期間From
	 * date_to　：期間To
	 * 戻り値：最終画像番号
	 */
	function get_photo_mno($db_link, $pid,&$p_mno,&$p_photoname,&$photo_explanation,&$bud_photo_no,&$registration_person,&$date_from,&$date_to)
	{
		//yupengbo modify 2011/11/18 start
		//$sql = "SELECT photo_mno,photo_name FROM photoimg WHERE photo_id = ?";//yupengbo comment 2011/11/18
		$sql = "SELECT photo_mno,photo_name,photo_explanation,bud_photo_no,registration_person,dfrom,dto FROM photoimg WHERE photo_id = ?";
		//yupengbo modify 2011/11/18 end
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $pid);

		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				// データを取得します。
				$photoimg = $stmt->fetch(PDO::FETCH_ASSOC);

				$p_mno = $photoimg['photo_mno'];
				$p_photoname = $photoimg['photo_name'];

				//yupengbo add 2011/11/18 start
				$photo_explanation = $photoimg['photo_explanation'];
				$bud_photo_no = $photoimg['bud_photo_no'];
				$registration_person = $photoimg['registration_person'];
				$date_from = $photoimg['dfrom'];
				$date_to = $photoimg['dto'];
				//yupengbo add 2011/11/18 end
			}
		}
	}

	//jinxin add 2012/02/09 start

	function get_nopermit_log($db_link,$pid,&$p_mno,&$p_photoname,&$photo_explanation,&$bud_photo_no,&$date_from,&$date_to,&$nopermission)
	{
		//$sql = "SELECT photo_mno,photo_name FROM photoimg WHERE photo_id = ?";//yupengbo comment 2011/11/18
		$sql = "SELECT photo_mno,photo_name,photo_explanation,bud_photo_no,dfrom,dto,nopermit_note FROM photoimg WHERE photo_id = ?";
		//yupengbo modify 2011/11/18 end
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $pid);

		$result = $stmt->execute();
		if ($result == true)
		{
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				$photoimg = $stmt->fetch(PDO::FETCH_ASSOC);

				$p_mno = $photoimg['photo_mno'];
				$p_photoname = $photoimg['photo_name'];

				//yupengbo add 2011/11/18 start
				$photo_explanation = $photoimg['photo_explanation'];
				$bud_photo_no = $photoimg['bud_photo_no'];
				$registration_person = $photoimg['registration_person'];
				$date_from = $photoimg['dfrom'];
				$date_to = $photoimg['dto'];
				$nopermission = $photoimg['nopermit_note'];
			}
		}
	}

	function get_dfrom_date_forhidden($db_link,$pid){
		$sql = "SELECT dfrom FROM photoimg WHERE photo_id = ?";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $pid);
		$result = $stmt->execute();
		if ($result == true){
			$icount = $stmt->rowCount();
			if($icount == 1){
				$photoimg = $stmt->fetch(PDO::FETCH_ASSOC);
				$date_from = $photoimg['dfrom'];
				return $date_from;
			}
		}
	}

	//jinxin add 2012/02/09 end

	//yupengbo add 2011/12/12 start
	/*
	 * 関数名：get_photo_ext
	 * 関数説明：画像IDより拡張子を取得する
	 * パラメタ：
	 * db_link：ＤＢリンク
	 * pid	　：画像番号
	 * ext　：拡張子
	 * 戻り値：拡張子
	 */
	function get_photo_ext($db_link, $pid,&$ext)
	{
		$sql = "SELECT ext FROM photoimg WHERE photo_id = ?";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $pid);

		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				// データを取得します。
				$photoimg = $stmt->fetch(PDO::FETCH_ASSOC);
				$ext = $photoimg['ext'];
			}
		}
	}

	/*
	 * 関数名：get_photo_server_flag
	 * 関数説明：画像IDよりサーバーフラグを取得する
	 * パラメタ：
	 * db_link：ＤＢリンク
	 * pid	　：画像番号
	 * ext　：サーバーフラグ
	 * 戻り値：サーバーフラグ
	 */
	function get_photo_server_flag($db_link, $pid,&$photo_server_flg)
	{
		$sql = "SELECT photo_server_flg FROM photoimg WHERE photo_id = ?";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $pid);

		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				// データを取得します。
				$photoimg = $stmt->fetch(PDO::FETCH_ASSOC);
				$photo_server_flg = $photoimg['photo_server_flg'];
			}
		}
	}

	/*
	 * 関数名：get_photo_filename
	 * 関数説明：画像IDよりファイル名を取得する
	 * パラメタ：
	 * db_link：ＤＢリンク
	 * pid	　：画像番号
	 * file_name　：ファイル名
	 * file_name_th1　：ファイル名
	 * file_name_th2　：ファイル名
	 * file_name_th3　：ファイル名
	 * file_name_th4　：ファイル名
	 * 戻り値：拡張子
	 */
	function get_photo_filename($db_link, $pid,&$file_name,&$file_name_th1,&$file_name_th2,&$file_name_th3,&$file_name_th4)
	{
		$sql = "SELECT photo_filename,photo_filename_th1,photo_filename_th2,photo_filename_th3,photo_filename_th4,photo_filename_th7,ext FROM photoimg WHERE photo_id = ?";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1, $pid);

		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				// データを取得します。
				$photoimg = $stmt->fetch(PDO::FETCH_ASSOC);
				$file_name = $photoimg['photo_filename'];
				$file_name_th1 = $photoimg['photo_filename_th1'];
				$file_name_th2 = $photoimg['photo_filename_th2'];
				$file_name_th3 = $photoimg['photo_filename_th3'];
				$file_name_th4 = $photoimg['photo_filename_th4'];
			}
		}
	}

	//yupengbo add 2011/12/12 end

	/*
	 * 関数名：get_photo_lastid
	 * 関数説明：最終画像番号を取得する
	 * パラメタ：db_link：ＤＢリンク
	 * 戻り値：最終画像番号
	 */
	function get_photo_lastid($db_link)
	{
		$sql = "SELECT max( photo_id ) max_photo_id FROM photoimg;";
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 最終番号を取得します。
			$max = $stmt->fetch(PDO::FETCH_ASSOC);
			return $max['max_photo_id'];
		}
		else
		{
			// エラーの場合は例外をスローします。
			$this->message = "最終画像番号を取得できませんでした。";
			throw new Exception($this->message);
			return "";
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
		$sql = "select * from lastnumber where lastnumber = \"".$p_photo_mno."\"";
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$icount = $stmt->rowCount();
			if ($icount == 0)
			{
				return 0;
			} else {
				$sql = "SELECT max(lastnumber)+1 as max FROM lastnumber WHERE lastnumber_name=\"".$p_photo_mno."\"";
				$stmt1 = $db_link->prepare($sql);
				$result = $stmt1->execute();
				if ($result == true)
				{
					// 最終番号を取得します。
					$max = $stmt1->fetch(PDO::FETCH_ASSOC);
					$maxno = $max['max'];
					return $maxno;
				}
				else
				{
					// エラーの場合は例外をスローします。
					$this->message = "最終番号のMAX値を取得できませんでした。";
					throw new Exception($this->message);
				}
			}
		}
		else
		{
			// エラーの場合は例外をスローします。
			$this->message = "最終番号のMAX値を取得できませんでした。";
			throw new Exception($this->message);
		}
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
											monopoly_use,
											photo_org_no,
											photo_url,
											photo_server_flg
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
											?,?,?,?,1
											)";
			$stmt = $db_link->prepare($sql);

			$p_max_photo_id = $this->get_photo_lastid($db_link);

			if (strlen($this->photo_mno) <= 5) $this->photo_mno = "申請中".$this->comp_code." ".$p_max_photo_id;

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
			$stmt->bindParam(33, $this->photo_org_no);
			$stmt->bindParam(34, $this->photo_url);

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

	// 画像IDよりデータを削除する
	function delete_data($db_link, $p_photo_id, $flg="1")
	{
		$retFlg = false;
		if (!empty($p_photo_id))
		{
			// トランザクションを開始します。（オートコミットがオフになります。）
			$db_link->beginTransaction();

			$sql = "DELETE FROM photoimg WHERE photo_id = ".$p_photo_id;
			$sql .= " AND photo_server_flg = ".$flg;
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == false)
			{
				// エラーの場合は例外をスローします。
				$this->message = "画像の削除ができませんでした。";
				// ロールバックします。
				$db_link->rollBack();
				// 例外をスローします。
				$msg = $e->getMessage();
				throw new Exception($msg);
			} else {
				// キーワードを削除する
				$del_ok = $this->delete_keyword($db_link, $p_photo_id);
				//if ($del_ok)//yupengbo comment 2011/11/18
				//{//yupengbo comment 2011/11/18
				// 分類を削除する
				$this->registration_classifications->delete_data($db_link, $p_photo_id);

				$sql = "DELETE FROM `photo_imgdata` WHERE photo_id = ".$p_photo_id;
				$stmt_imgdata = $db_link->prepare($sql);
				$stmt_imgdata->execute();

				$db_link->commit();
				$retFlg = true;
				//} else {//yupengbo comment 2011/11/18
				//	$db_link->rollBack();//yupengbo comment 2011/11/18
				//}//yupengbo comment 2011/11/18
			}
		} else {
			$retFlg = true;
		}

		return $retFlg;
	}

	// 掲載状況をDBより取得します。
	function get_publishing_situation($db_link, &$db_id, &$db_name)
	{
		// 掲載状況情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM publishing_situation order by publishing_situation_id";
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
				// 初期化します。
				$db_id = array();
				$db_name = array();

				while ($publishing_situation = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 掲載状況ID、掲載状況を保存します。
					$db_id[] = $publishing_situation['publishing_situation_id'];
					$db_name[] = $publishing_situation['publishing_situation_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "掲載状況を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "掲載状況を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

/*
 * no permission reasonをDBより取得します。
 * written by jinxin 2012/02/07
 */
	function get_nopermis_reasons($db_link, &$db_id, &$db_name)
	{
		$sql = "SELECT * FROM nopermit_master order by nopermit_id";
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
				// 初期化します。
				$db_id = array();
				$db_name = array();

				while ($nopermit_master = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// nopermis ID、nopermis を保存します。
					$db_id[] = $nopermit_master['nopermit_id'];
					$db_name[] = $nopermit_master['nopermit_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "nopermis reason を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "nopermis reason を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}
	//2012-02-09 modify by jinxin end

	// カテゴリーをDBより取得します。
	//   $db_id,$db_name:２次配列（カテゴリーテーブルは多次元構造）
	//   $db_id[x][0]：トップカテゴリー
	//   $db_id[x][1-]：子カテゴリー
	function get_category($db_link, &$db_id, &$db_name)
	{
		// トップカテゴリーをDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM category WHERE parent_id = 0 order by category_id";
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
				// 初期化します。
				$db_id = array();
				$db_name = array();

				$cnt = 0;
				while ($category = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// カテゴリーID、カテゴリーを保存します。
					$db_id[$cnt] = array();
					$db_name[$cnt] = array();
					$db_id[$cnt][] = $category['category_id'];
					$db_name[$cnt][] = $category['category_name'];
					$cnt++;
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "トップカテゴリーを取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "トップカテゴリーを取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}

		// １つ目の子カテゴリーを取得します。
		$ed = count($db_id);
		for ($i = 0 ; $i < $ed ; $i++)
		{
			// 取得するためのSQLを作成します。
			$sql = "SELECT * FROM category WHERE parent_id = " . $db_id[$i][0] . " order by category_id";
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
					while ($category = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						// カテゴリーID、カテゴリーを保存します。
						$db_id[$i][] = $category['category_id'];
						$db_name[$i][] = $category['category_name'];
					}
				}
				else
				{
					// エラー情報をセットして、例外をスローします。
					$this->message = "子カテゴリーを取得できませんでした。（取得数<0）";
					throw new Exception($this->message);
				}
			}
			else
			{
				// 実行結果がNGの場合の処理です。
				// エラー情報をセットして、例外をスローします。
				$err = $stmt->errorInfo();
				$this->message = "子カテゴリーを取得できませんでした。（条件設定エラー）";
				throw new Exception($this->message);
			}
		}
	}

	// 登録区分をDBより取得します。
	function get_registration_division($db_link, &$db_id, &$db_name)
	{
		// 登録区分をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM registration_division order by registration_division_id";
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
				// 初期化します。
				$db_id = array();
				$db_name = array();

				while ($registration_division = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 登録区分ID、登録区分を保存します。
					$db_id[] = $registration_division['registration_division_id'];
					$db_name[] = $registration_division['registration_division_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "登録区分を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "登録区分を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	// 登録区分をDBより取得します。
	function get_registration_division2($db_link, &$db_id, &$db_name)
	{
		// 登録区分をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM registration_division2 order by registration_division_id";
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
				// 初期化します。
				$db_id = array();
				$db_name = array();

				while ($registration_division = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 登録区分ID、登録区分を保存します。
					$db_id[] = $registration_division['registration_division_id'];
					$db_name[] = $registration_division['registration_division_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "登録区分を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "登録区分を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	// 分類をDBより取得します。
	function get_classification($db_link, &$db_id, &$db_name)
	{
		// 分類をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM classification order by classification_id";
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
				// 初期化します。
				$db_id = array();
				$db_name = array();

				while ($classification = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 分類ID、分類を保存します。
					$db_id[] = $classification['classification_id'];
					$db_name[] = $classification['classification_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "分類を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "分類を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	// 方面をDBより取得します。
	//   $select_id = １次配列
	//   $db_id,$db_name = ２次配列
	function get_direction($db_link, &$db_id, &$db_name, $select_id)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($select_id) || $select_id == "-1")
		{
			return;
		}

		// 条件が配列かどうかをチェックします。
		$where_id = array();
		if (is_array($select_id))
		{
			// 配列の場合は、そのまま条件とします。
			$where_id = $select_id;
		}
		else
		{
			// 配列でない場合は、配列に入れ替えて条件とします。
			$where_id[] = $select_id;
		}

		// 分類に分けて、方面を取得します。
		$ed = count($where_id);
		$db_id = array();
		$db_name = array();
		for ($i = 0 ; $i < $ed ; $i++)
		{
			$db_id[$i] = array();
			$db_name[$i] = array();

			// 方面情報をDBより取得します。
			// 取得するためのSQLを作成します。
			$sql = "SELECT direction_id, direction_name FROM direction WHERE classification_id = ? order by direction_id ";
			$stmt = $db_link->prepare($sql);
			$stmt->bindParam(1, $where_id[$i]);

			// SQLを実行します。
			$result = $stmt->execute();

			// 実行結果をチェックします。
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount >= 0)
				{
					while ($direction = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						//  方面ID、方面コード、方面名を保存します。
						$db_id[$i][] = $direction['direction_id'];
						$db_name[$i][] = $direction['direction_name'];
					}
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
	}

	// 国・都道府県をDBより取得します。
	//   $select_id = ２次配列
	//   $db_id,$db_name = ３次配列
	function get_country_prefecture($db_link, &$db_id, &$db_name, $select_id)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($select_id) || $select_id == "-1")
		{
			return;
		}

		// 条件が配列かどうかをチェックします。
		$where_id = array();
		if (is_array($select_id))
		{
			// 配列の場合は、そのまま条件とします。
			$where_id = $select_id;
		}
		else
		{
			// 配列でない場合は、配列に入れ替えて条件とします。
			$where_id[0] = array();
			$where_id[0][] = $select_id;
		}

		// 方面毎に、国・都道府県を取得します。
		$ed = count($where_id);
		$db_id = array();
		$db_name = array();
		for ($i = 0 ; $i < $ed ; $i++)
		{
			$db_id[$i] = array();
			$db_name[$i] = array();
			$ed2 = count($where_id[$i]);
			for ($j = 0 ; $j< $ed2 ; $j++)
			{
				$db_id[$i][$j] = array();
				$db_name[$i][$j] = array();

				// 国・都道府県情報をDBより取得します。
				// 取得するためのSQLを作成します。
				$sql = "SELECT country_prefecture_id, country_prefecture_name FROM country_prefecture WHERE direction_id = ? order by country_prefecture_id ";
				$stmt = $db_link->prepare($sql);
				$stmt->bindParam(1, $where_id[$i][$j]);

				// SQLを実行します。
				$result = $stmt->execute();

				// 実行結果をチェックします。
				if ($result == true)
				{
					// 実行結果がOKの場合の処理です。
					$icount = $stmt->rowCount();
					if ($icount >= 0)
					{
						while ($country_prefecture = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							//  国・都道府県ID、国・都道府県コード、国・都道府県名を保存します。
							$db_id[$i][$j][] = $country_prefecture['country_prefecture_id'];
							$db_name[$i][$j][] = $country_prefecture['country_prefecture_name'];
						}
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
		}
	}

	// 地名をDBより取得します。
	//   $select_id = ２次配列
	//   $db_id,$db_name = ３次配列
	function get_place($db_link, &$db_id, &$db_name, $select_id)
	{
		// 条件が入っていない場合は、そのまま戻ります。
		if (empty($select_id) || $select_id == "-1")
		{
			return;
		}

		// 条件が配列かどうかをチェックします。
		$where_id = array();
		if (is_array($select_id))
		{
			// 配列の場合は、そのまま条件とします。
			$where_id = $select_id;
		}
		else
		{
			// 配列でない場合は、配列に入れ替えて条件とします。
			$where_id[0] = array();
			$where_id[0][0] = array();
			$where_id[0][0][] = $select_id;
		}

		// 国・都道府県毎に、地名を取得します。
		$ed = count($where_id);
		$db_id = array();
		$db_name = array();

		for ($i = 0 ; $i < $ed ; $i++)
		{
			$db_id[$i] = array();
			$db_name[$i] = array();

			$ed2 = count($where_id[$i]);
			for ($j = 0 ; $j< $ed2 ; $j++)
			{
				$db_id[$i][$j] = array();
				$db_name[$i][$j] = array();

				$ed3 = count($where_id[$i][$j]);
				for ($k = 0 ; $k< $ed3 ; $k++)
				{
					$db_id[$i][$j][$k] = array();
					$db_name[$i][$j][$k] = array();

					// 地名情報をDBより取得します。
					// 取得するためのSQLを作成します。
					$sql = "SELECT place_id, place_name FROM place WHERE country_prefecture_id = ? order by place_id ";
					$stmt = $db_link->prepare($sql);
					$stmt->bindParam(1, $where_id[$i][$j][$k]);

					// SQLを実行します。
					$result = $stmt->execute();

					// 実行結果をチェックします。
					if ($result == true)
					{
						// 実行結果がOKの場合の処理です。
						$icount = $stmt->rowCount();
						if ($icount >= 0)
						{
							while ($place = $stmt->fetch(PDO::FETCH_ASSOC))
							{
								//  地名ID、地名コード、地名名を保存します。
								$db_id[$i][$j][$k][] = $place['place_id'];
								$db_name[$i][$j][$k][] = $place['place_name'];
							}
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
			}
		}
	}

	//yupengbo modify 2011/12/12 start
	// キーワードをDBより取得します。
	function get_keyword($db_link, &$db_id, &$db_name, $photo_id="")
	//yupengbo modify 2011/12/12 end
	{
		// キーワード情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM keyword";
		//yupengbo modify 2011/12/12 start
		if(!empty($photo_id))
		{
			$sql .= " WHERE photo_id = ".$photo_id;
		}
		$sql .= " order by keyword_id";
		//yupengbo modify 2011/12/12 end
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
				// キーワードID、キーワードを初期化します。
				$db_id = array();
				$db_name = array();
				while ($keyword = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// キーワードID、キーワードを保存します。
					$db_id[] = $keyword['keyword_id'];
					$db_name[] = $keyword['keyword_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "キーワードを取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "キーワードを取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}


	function get_take_picture_time($db_link, &$db_id, &$db_name)
	{
//		echo "<br />get_take_picture_time";

		// 撮影時期情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM take_picture_time order by take_picture_time_id";
		$stmt = $db_link->prepare($sql);

		// SQLを実行します。
		$result = $stmt->execute();
//		echo "<br />SQL Exc";

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				// 撮影時期ID、撮影時期を初期化します。
				$db_id = array();
				$db_name = array();
				while ($take_picture_time = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 撮影時期ID、撮影時期を保存します。
					$db_id[] = $take_picture_time['take_picture_time_id'];
					$db_name[] = $take_picture_time['take_picture_time_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "撮影時期を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "撮影時期を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
//		echo "<br />get_take_picture_time END";
	}

	function get_take_picture_time2($db_link, &$db_id, &$db_name)
	{
		// 撮影時期(2)をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM take_picture_time2 order by take_picture_time2_id";
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
				// 撮影時期ID、撮影時期を初期化します。
				$db_id = array();
				$db_name = array();
				while ($take_picture_time = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 撮影時期ID、撮影時期を保存します。
					$db_id[] = $take_picture_time['take_picture_time2_id'];
					$db_name[] = $take_picture_time['take_picture_time2_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "撮影時期(2)を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "撮影時期(2)を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	function get_borrowing_ahead($db_link, &$db_id, &$db_name)
	{
		// 写真入手元情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM borrowing_ahead order by borrowing_ahead_id";
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
				// 写真入手元ID、写真入手元を初期化します。
				$db_id = array();
				$db_name = array();
				while ($borrowing_ahead = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 写真入手元ID、写真入手元を保存します。
					$db_id[] = $borrowing_ahead['borrowing_ahead_id'];
					$db_name[] = $borrowing_ahead['borrowing_ahead_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->messag = "写真入手元を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->messag = "写真入手元を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	function get_range_of_use($db_link, &$db_id, &$db_name)
	{
		// 使用範囲情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT * FROM range_of_use order by range_of_use_id";
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
				// 使用範囲ID、使用範囲を初期化します。
				$db_id = array();
				$db_name = array();
				while ($range_of_use = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 使用範囲ID、使用範囲を保存します。
					$db_id[] = $range_of_use['range_of_use_id'];
					$db_name[] = $range_of_use['range_of_use_name'];
				}
			}
			else
			{
				// エラー情報をセットして、例外をスローします。
				$this->message = "使用範囲を取得できませんでした。（取得数<=0）";
				throw new Exception($this->message);
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "使用範囲を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}
}

class PhotoImageDataAll extends PhotoImageData
{
	var $publishing_situation_name;													// 掲載状況
	var $registration_division_name;												// 登録区分
	var $take_picture_time_name;													// 撮影時期(1)
	var $take_picture_time2_name;													// 撮影時期(2)
	var $borrowing_ahead_name;														// 写真入手元
	var $range_of_use_name;															// 使用範囲

	function __construct() {
		
	}
	
	function PhotoImageDataAll()
	{
		PhotoImageData::PhotoImageData();
	}

	/**
	 * 初期化します。
	 */
	function init_data()
	{
		$this->publishing_situation_name = "";										// 掲載状況
		$this->registration_division_name = "";										// 登録区分
		$this->take_picture_time_name = "";											// 撮影時期(1)
		$this->take_picture_time2_name = "";										// 撮影時期(2)
		$this->borrowing_ahead_name = "";											// 写真入手元
		$this->range_of_use_name = "";												// 使用範囲
	}

	/**
	 * データをセットします。
	 */
	function set_data($imgdata)
	{
		// 初期化します。
		$this->init_data();
		PhotoImageData::init_data();

		// データをセットします。
		$this->publishing_situation_name = $imgdata['publishing_situation_name'];	// 掲載状況
		$this->registration_division_name = $imgdata['registration_division_name'];	// 登録区分
		$this->take_picture_time_name = $imgdata['take_picture_time_name'];			// 撮影時期(1)
		$this->take_picture_time2_name = $imgdata['take_picture_time2_name'];		// 撮影時期(2)
		$this->borrowing_ahead_name = $imgdata['borrowing_ahead_name'];				// 写真入手元
		$this->range_of_use_name = $imgdata['range_of_use_name'];					// 使用範囲

		PhotoImageData::set_data($imgdata);
	}

}

//yupengbo add 2011/11/17 start
class PhotoImageLog
{
	var $photo_mno;									// 画像番号
	var $bud_photo_no;								// BUD_PHOTO番号
	var $photo_name;								// 写真名（タイトル）
	var $photo_explanation;							// 写真説明
	var $dfrom;										// 掲載期間（From）
	var $dto;										// 掲載期間（To）
	var $register_date;								// 登録日
	var $log_flag;									// ログフラッグ０：自動、１：手動
	var $login_account;								// ログインユーザー
	var $login_account_name;						// ログインユーザー名前
	var $registration_person;						// 申請者

	/**
	 * データをセットします。
	 */
	function set_data($imgdata)
	{
		$this->photo_mno = $imgdata['photo_mno'];						// 画像番号
		$this->bud_photo_no = $imgdata['bud_photo_no'];					// BUD_PHOTO番号
		$this->photo_name = $imgdata['photo_name'];						// 写真名（タイトル）
		$this->photo_explanation = $imgdata['photo_explanation'];		// 写真説明
		$this->dfrom = $imgdata['dfrom'];								// 掲載期間（From）
		$this->dto = $imgdata['dto'];									// 掲載期間（To）
		$this->register_date = $imgdata['register_date'];				// 登録日
		$this->log_flag = $imgdata['log_flag'];							// ログフラッグ０：自動、１：手動
		$this->login_account = $imgdata['login_account'];				// ログインユーザー
		$this->login_account_name = $imgdata['login_account_name'];		// ログインユーザー名前
		$this->registration_person = $imgdata['registration_person'];	// 申請者
	}
}
//yupengbo add 2011/11/17

//jinxin add 2012/02/07 start
class PhotoImageNopermit
{
	var $photo_mno;									// 画像番号
	var $bud_photo_no;								// BUD_PHOTO番号
	var $photo_name;								// 写真名（タイトル）
	var $photo_explanation;							// 写真説明
	var $dfrom;										// 掲載期間（From）
	var $dto;										// 掲載期間（To）
	var $nopermit_date;								// 登録日
	var $log_flag;									// ログフラッグ０：自動、１：手動
	var $login_account;								// ログインユーザー
	var $login_account_name;						// ログインユーザー名前
	var $nopermit_personid;						// 申請者
	var $nopermission;								// no permit reason

	/**
	 * データをセットします。
	 */
	function set_data($imgdata)
	{
		$this->photo_mno = $imgdata['photo_mno'];						// 画像番号
		$this->bud_photo_no = $imgdata['bud_photo_no'];					// BUD_PHOTO番号
		$this->photo_name = $imgdata['photo_name'];						// 写真名（タイトル）
		$this->photo_explanation = $imgdata['photo_explanation'];		// 写真説明
		$this->dfrom = $imgdata['dfrom'];								// 掲載期間（From）
		$this->dto = $imgdata['dto'];									// 掲載期間（To）
		$this->nopermit_date = $imgdata['nopermit_date'];				// 登録日
		$this->nopermit_personid = $imgdata['nopermit_personid'];	// 申請者
		$this->nopermission = $imgdata['nopermit_note'];  				// no permit reason
	}
}
//jinxin add 2012/02/07 end
#liucongxu1
class ImageSearch
{
	// 本来ならPrivateにした方が良いと思いますが利便性を考えvarで宣言しています。
	var $message;									// メッセージ
	var $error;										// エラー

	// 検索条件
	var $sp_ext;										// 拡張子（元ファイル名）
	var $sp_photo_id;									// 写真ID
	var $sp_photo_mno;									// 写真管理番号
	var $sp_photo_name;									// 写真名
	var $sp_photo_explanation;							// 写真説明
	var $sp_keyword_str;								// キーワード（カンマ区切り）
	var $sp_dfrom;										// 掲載期間（From）
	var $sp_dto;										// 掲載期間（To）
	var $sp_take_picture_time_id;						// 撮影時期(1)
	var $sp_take_picture_time2_id;						// 撮影時期(2)
	var $sp_borrowing_ahead_id;							// 写真入手元
	var $sp_content_borrowing_ahead;					// 写真入手元（内容）
	var $sp_range_of_use_id;							// 使用範囲
	var $sp_additional_constraints1;					// 付加条件(1)
	var $sp_additional_constraints2;					// 付加条件(2)
	var $sp_note;										// 分類
	var $sp_copyright_owner;							// 版権所有者
	var $sp_publishing_situation_id;					// 掲載状況
	var $sp_register_date;								// 登録日
	var $sp_state;										// 状態
	var $sp_kikan;										// 期間
	var $sp_photo_id_str;								// 写真ID（カンマ区切り）
	var $sp_source_image_no;							// 元画像ID
	var $sp_bud_photo_no;								// BUD 番号
	var $sp_registration_person;						// 登録申請者
	var $sp_permission_person;							// 登録許可者
	var $sp_customer_info;								// お客様情報
	var $sp_classification_id;							// 海外、国内、イメージ
	var $sp_direction_id;								// 方面
	var $sp_country_prefecture_id;						// 国・都道府県
	var $sp_place_id;									// 地名
	var $sp_monopoly_use;								// 独占使用
	var $sp_login_id;                                   // 当前登录用户
	var $sp_nopermission_reason;						// no permit reason add by jinxin 2012/02/07

	var $images;										// イメージのインスタンス保存用（配列）
	var $imagescount;									// 	イメージ総数

	var $istart;										// インデックス
	var $iend;											// インデックス
	//xu add it on 2010-12-09 start
	var $per_page;										// インデックス
	//xu add it on 2010-12-09 end

	var $registration_divisionflg;

	function __construct()
	{
		// 条件を初期化します。
		$this->init_condition();
	}

	function init_condition()
	{
		// 検索条件を初期化します。
		$this->sp_ext = "";									// 拡張子（元ファイル名）
		$this->sp_photo_id = -1;							// 写真ID
		$this->sp_photo_mno = "";							// 写真管理番号
		$this->sp_photo_name = "";							// 写真名
		$this->sp_photo_explanation = "";					// 写真説明
		$this->sp_keyword_str = "";							// キーワード（カンマ区切り）
		$this->sp_dfrom = "";								// 掲載期間（From）
		$this->sp_dto = "";									// 掲載期間（To）
		//$this->sp_take_picture_time_id = -1;				// 撮影時期(1)
		//$this->sp_take_picture_time2_id = -1;				// 撮影時期(2)
		$this->sp_take_picture_time_id = "";				// 撮影時期(1)
		$this->sp_take_picture_time2_id = "";				// 撮影時期(2)
		$this->sp_borrowing_ahead_id = -1;					// 写真入手元
		$this->sp_content_borrowing_ahead = "";				// 写真入手元（内容）
		//$this->sp_range_of_use_id = -1;					// 使用範囲
		$this->sp_range_of_use_id = "";						// 使用範囲
		$this->sp_additional_constraints1 = "";				// 付加条件(1)
		$this->sp_additional_constraints2 = "";				// 付加条件(2)
		$this->sp_note = "";								// 備考
		$this->sp_copyright_owner = "";						// 版権所有者
		$this->sp_publishing_situation_id = -1;				// 掲載状況
		$this->sp_nopermission_reason = "";					// no permission add by jinxin
		$this->sp_register_date = "0000-00-00 00:00:00";	// 登録日
		$this->sp_state = 0;								// 状態
		$this->sp_kikan = "";								// 期間
		$this->sp_photo_id_str = "";						// 写真ID（カンマ区切り）
		$sp_source_image_no = "";							// 元画像ID
		$sp_bud_photo_no = "";								// BUD 番号
		$sp_registration_person = "";						// 登録申請者
		$sp_permission_person = "";							// 登録許可者
		$sp_customer_info = "";								// お客様情報
		$sp_classification_id = "";							// 海外、国内、イメージ
		$sp_direction_id = "";								// 方面
		$sp_country_prefecture_id = "";						// 国・都道府県
		$sp_place_id = "";									// 地名
		$sp_monopoly_use = "";								// 独占使用
		$this->sp_login_id = "";                            // 当前登录用户

		$this->images = array();							// イメージのインスタンス保存用
		$this->imagescount = 0;								// イメージ総数
		$this->istart = -1;									// インデックス
		$this->iend = -1;									// インデックス
		$registration_divisionflg = -1;				//登録区分
	}

	function set_ext($ext)
	{
		if (!empty($ext))
		{
			$this->sp_ext = $ext;
		}
	}

	function set_photo_mno($photo_mno)
	{
		if (!empty($photo_mno))
		{
			$this->sp_photo_mno = $photo_mno;
		}
	}

	function set_photo_name($photo_name)
	{
		if (!empty($photo_name))
		{
			$this->sp_photo_name = $photo_name;
		}
	}

	function set_take_picture_time_id($take_picture_time_id)
	{
		//if (is_numeric($take_picture_time_id))
		//{
			$this->sp_take_picture_time_id = $take_picture_time_id;
		//}
	}

	function set_take_picture_time2_id($take_picture_time2_id)
	{
		//if (is_numeric($take_picture_time2_id))
		//{
			$this->sp_take_picture_time2_id = $take_picture_time2_id;
		//}
	}

	function set_borrowing_ahead_id($borrowing_ahead_id)
	{
		if (is_numeric($borrowing_ahead_id))
		{
			$this->sp_borrowing_ahead_id = $borrowing_ahead_id;
		}
	}

	function set_content_borrowing_ahead($content_borrowing_ahead)
	{
		if (!empty($content_borrowing_ahead))
		{
			$this->sp_content_borrowing_ahead = $content_borrowing_ahead;
		}
	}

	function set_range_of_use_id($range_of_use_id)
	{
		//if (is_numeric($range_of_use_id))
		//{
			$this->sp_range_of_use_id = $range_of_use_id;
		//}
	}

	function set_publishing_situation_id($publishing_situation_id)
	{
		//if (is_numeric($publishing_situation_id))
		//{
			$this->sp_publishing_situation_id = $publishing_situation_id;
		//}
	}

	function set_additional_constraints1($additional_constraints1)
	{
		if (!empty($additional_constraints1))
		{
			$this->sp_additional_constraints1 = $additional_constraints1;
		}
	}

	function set_additional_constraints2($additional_constraints2)
	{
		if (!empty($additional_constraints2))
		{
			$this->sp_additional_constraints2 = $additional_constraints2;
		}
	}

	function set_note($note)
	{
		if (!empty($note))
		{
			$this->sp_note = $note;
		}
	}

	function set_copyright_owner($copyright_owner)
	{
		if (!empty($copyright_owner))
		{
			$this->sp_copyright_owner = $copyright_owner;
		}
	}

	function set_register_date($register_date)
	{
		if (!empty($register_date))
		{
			$this->sp_register_date = $register_date;
		}
	}

	function set_viewable($viewable)
	{
		if ($viewable == true || $viewable == false)
		{
			$this->sp_viewable = $viewable;
		}
	}

	function set_dfrom($dfrom)
	{
		if (!empty($dfrom))
		{
			$this->sp_dfrom = $dfrom;
		}
	}

	function set_dto($dto)
	{
		if (!empty($dto))
		{
			$this->sp_dto = $dto;
		}
	}

	function set_keyword_str($keyword_str)
	{
		if (!empty($keyword_str))
		{
			$this->sp_keyword_str = $keyword_str;
		}
	}

	function set_photo_id_str($photo_id_str)
	{
		if (!empty($photo_id_str))
		{
			$this->sp_photo_id_str = $photo_id_str;
		}
	}

	function set_source_image_no($source_image_no)
	{
		if (!empty($source_image_no))
		{
			$this->sp_source_image_no = $source_image_no;
		}
	}

	function set_bud_photo_no($bud_photo_no)
	{
		if (!empty($bud_photo_no))
		{
			$this->sp_bud_photo_no = $bud_photo_no;
		}
	}

	function set_registration_person($registration_person)
	{
		if (!empty($registration_person))
		{
			$this->sp_registration_person = $registration_person;
		}
	}

	function set_permission_person($permission_person)
	{
		if (!empty($permission_person))
		{
			$this->sp_permission_person = $permission_person;
		}
	}

	function set_customer_info($customer_info)
	{
		if (!empty($customer_info))
		{
			$this->sp_customer_info = $customer_info;
		}
	}

	function set_classification_id($classification_id)
	{
		if (!empty($classification_id))
		{
			$this->sp_classification_id = $classification_id;
		}
	}

	function set_direction_id($direction_id)
	{
		if (!empty($direction_id))
		{
			$this->sp_direction_id = $direction_id;
		}
	}

	function set_country_prefecture_id($country_prefecture_id)
	{
		if (!empty($country_prefecture_id))
		{
			$this->sp_country_prefecture_id = $country_prefecture_id;
		}
	}

	function set_place_id($place_id)
	{
		if (!empty($place_id))
		{
			$this->sp_place_id = $place_id;
		}
	}

	function set_monopoly_use($monopoly_use)
	{
		if (!empty($monopoly_use))
		{
			$this->sp_monopoly_use = $monopoly_use;
		}
	}

	function set_registration_divisionflg($para_reg_div)
	{
		if (!empty($para_reg_div))
		{
			$this->registration_divisionflg = $para_reg_div;
		}
	}

	private function set_sql()
	{
		$sql = "SELECT * FROM photoimg";
		$sql .= " LEFT JOIN publishing_situation on photoimg.publishing_situation_id = publishing_situation.publishing_situation_id ";
		if ($this->registration_divisionflg == "1")
		{
			$sql .= " LEFT JOIN registration_division2 on photoimg.registration_division_id = registration_division2.registration_division_id ";
		} else {
			$sql .= " LEFT JOIN registration_division on photoimg.registration_division_id = registration_division.registration_division_id ";
		}
		$sql .= " LEFT JOIN take_picture_time on photoimg.take_picture_time_id = take_picture_time.take_picture_time_id ";
		$sql .= " LEFT JOIN take_picture_time2 on photoimg.take_picture_time2_id = take_picture_time2.take_picture_time2_id ";
		$sql .= " LEFT JOIN borrowing_ahead on photoimg.borrowing_ahead_id = borrowing_ahead.borrowing_ahead_id ";
		$sql .= " LEFT JOIN range_of_use on photoimg.range_of_use_id = range_of_use.range_of_use_id ";

		// Group Byを設定します。
		//$sql .= "group by photoimg.photo_id, photoimg.photo_mno ";
		return $sql;
	}

	/*
	 * 関数名：select_image_keyword
	 * 関数説明：キーワードテーブルから検索する
	 * パラメタ：
	 * db_link:データベースのリンク
	 * sp_str:入力の検索条件
	 * sp_str_content:選択の検索条件（詳細検索）
	 * 戻り値：無し
	 */
	#liucongxu2
	function select_image_keyword($db_link,$sp_str,$sp_str_content,$p_tmp_kikan)
	{

		// echo '第1个值';
		// var_dump($db_link);
		// echo '第2个值';
		// echo $sp_str;
		// echo '第3个值';
		// echo $sp_str_content;
		// echo '第4个值';
		// echo $p_tmp_kikan;
		// echo 'end';
		$sql_where = "";
		$sql = "";
		$tmpsql2_1 = "";
		$kwd_a = array();

		if (!empty($sp_str))
		{
			// 検索内容の文字列（「or」で区切り）→配列に変換します。
//			$flg1 = stripos($sp_str," or ");
//			$flg2 = stripos($sp_str,"　or ");
//			$flg3 = stripos($sp_str," or　");
//			$flg4 = stripos($sp_str,"　or　");
//			if ($flg1 || $flg2 || $flg3 || $flg4)
//			{
//				// 正常のSQL文は以下のようなSQL文です。
//				/*
//					SELECT DISTINCT photo_id FROM keyword WHERE
//					keyword_name COLLATE utf8_bin LIKE '２月%' OR
//					keyword_name COLLATE utf8_bin LIKE '８月%'
//				*/
//				if ($flg1) $kwd_a = spliti(" or ", $sp_str);
//				if ($flg2) $kwd_a = spliti("　or ", $sp_str);
//				if ($flg3) $kwd_a = spliti(" or　", $sp_str);
//				if ($flg4) $kwd_a = spliti("　or　", $sp_str);
//				$ed = count($kwd_a);
//				for ($i = 0 ; $i < $ed ; $i++)
//				{
//					if (!empty($sql_where) && !empty($kwd_a[$i]))
//					{
//						 $sql_where.= " OR ";
//					}
//					if (!empty($kwd_a[$i]))
//					{
//						$sql_where.= "keyword_name LIKE '%".$kwd_a[$i]."%'";
//					}
//				}
//				$sql = " SELECT DISTINCT photo_id FROM keyword WHERE ".$sql_where;
//			} else {
				// 文字列に「-」をあるかどうかチェックする
				$tmpstr = "_".$sp_str;
				$flg = stripos($tmpstr,"\"");
				if ($flg)
				{
					// 検索内容の文字列（「 」で区切り）→配列に変換します。
					$tmp_1 = preg_replace('/(　)+?/'," ",$sp_str);
					$kwd_a = explode(" ", $tmp_1);
					$ed = count($kwd_a);
					$tmp_str = "";
					$sql_where1 = "";
					// --------SQL文を構築する（開始）--------------------------------------
					for ($i = 0 ; $i < $ed ; $i++)
					{
						$tmp_str = str_replace("\\","",$kwd_a[$i]);
						$tmp_str = str_replace("\"","",$tmp_str);

						if (!empty($sql_where))
						{
							$sql_where1 .= " AND ";
						}

						$sql_where1 .= "keyword_name LIKE '% ".$tmp_str." %'";
					}

					$sql = " SELECT * FROM keyword WHERE ".$sql_where1;
					// --------SQL文を構築する（終了）--------------------------------------
				} else {
					// 検索内容の文字列（「 」で区切り）→配列に変換します。
					$tmpstr = "_".$sp_str;
					$flg = stripos($tmpstr,"-");
					if ($flg)
					{
						// 検索内容の文字列（「 」で区切り）→配列に変換します。
						$tmp_1 = preg_replace('/(　)+?/'," ",$sp_str);
						$kwd_a = explode(" ", $tmp_1);
						$ed = count($kwd_a);
						// --------SQL文を構築する（開始）--------------------------------------

						$tmpsql2_ary = array();
						$tmpsql2_not_ary = array();

						$sql_where1 = "";
						for ($i = 0 ; $i < $ed ; $i++)
						{
							if (substr($kwd_a[$i],0,1) == "-")
							{
								$tmp1 = substr($kwd_a[$i],1);

								if (!empty($sql_where1))
								{
									$sql_where1 .= " AND ";
								}
								$sql_where1 .= "keyword.keyword_name NOT LIKE '%".$tmp1."%'";
							} else {
								if (!empty($sql_where1))
								{
									$sql_where1 .= " AND ";
								}
								$sql_where1 .= "keyword.keyword_name LIKE '%".$kwd_a[$i]."%'";
							}
						}

						if (!empty($sql_where1))
						{
							$sql = "SELECT * FROM keyword WHERE ".$sql_where1;
						}
					} else {
						// 検索内容の文字列（「 」で区切り）→配列に変換します。
						$tmp_1 = preg_replace("/　/"," ",$sp_str);
						$kwd_a = explode(" ", $tmp_1);
						$ed = count($kwd_a);
						// --------SQL文を構築する（開始）--------------------------------------
						$sql_where1 = "";
						for ($i = 0 ; $i < $ed ; $i++)
						{
							if (!empty($sql_where1))
							{
								$sql_where1 .= " AND ";
							}
							$sql_where1 .= "keyword.keyword_name LIKE '%".$kwd_a[$i]."%'";
						}

						$sql = " SELECT * FROM keyword WHERE ".$sql_where1;
					}
				}
			//}
		}

		if (!empty($sp_str_content))
		{
			// 文字列に「-」をあるかどうかチェックする
			// 検索内容の文字列（「 」で区切り）→配列に変換します。
			$kwd_a = explode(" ", $sp_str_content);
			$ed = count($kwd_a);

			$sql_where1 = "";

			for ($i = 0 ; $i < $ed ; $i++)
			{
				if (substr($kwd_a[$i],0,1) == "-")
				{
					if (!empty($kwd_a[$i]))
					{
						if (!empty($sql_where1))
						{
							$sql_where1 .= " AND ";
						}
						$sql_where1 .= " keyword.keyword_name NOT LIKE '% ".substr($kwd_a[$i],1)." %'";
					}
				} else {
					if (!empty($kwd_a[$i]))
					{
						if (!empty($sql_where1))
						{
							$sql_where1 .= " AND ";
						}
						$sql_where1 .= " keyword.keyword_name LIKE '% ".$kwd_a[$i]." %'";
					}
				}
			}
			if (!empty($sql))
			{
				$sql .= " AND ".$sql_where1;
			} else {
				$sql = " SELECT * FROM keyword WHERE ".$sql_where1;
			}
		}

		$sql .= " AND keyword.publishing_situation_id = 2 ";

		if(empty($p_tmp_kikan))
		{
			$sql .= " AND keyword.photo_id IN(SELECT photo_id FROM photoimg WHERE photoimg.publishing_situation_id = 2)";
		} else {
			if((int)$p_tmp_kikan == 3)
			{
				$sql .= " AND keyword.photo_id IN(SELECT photo_id FROM photoimg WHERE photoimg.publishing_situation_id = 2";
				$sql .= " AND DATEDIFF( photoimg.dto, NOW() ) >90 )";
			}

			if((int)$p_tmp_kikan == 6)
			{
				$sql .= " AND keyword.photo_id IN(SELECT photo_id FROM photoimg WHERE photoimg.publishing_situation_id = 2";
				$sql .= " AND DATEDIFF( photoimg.dto, NOW() ) >180 )";
			}

			if((int)$p_tmp_kikan == 9)
			{
				$sql .= " AND keyword.photo_id IN(SELECT photo_id FROM photoimg WHERE photoimg.publishing_situation_id = 2";
				$sql .= " AND photoimg.kikan = 'mukigen')";
			}
		}

		$sqlcount = $sql;

		$sql .= " ORDER BY keyword.photo_id DESC";
		$tmpistart = (int)$this->istart;
		$tmpend = (int)$this->iend;
		if ($tmpistart >= 0 && $tmpend > 0)
		{
			$sql .= " LIMIT ".$this->istart.",".$this->iend;
		}
		#echo $sql;
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();
			// 選択されたデータ数が１かどうかチェックします。
			if ($icount <= 0)
			{
				$this->images = array();
				return true;
			}

			$stmtcount = $db_link->prepare($sqlcount);
			$resultcount = $stmtcount->execute();
			if ($resultcount == true)
			{
				$this->imagescount = $stmtcount->rowCount();
			} else {
				$this->imagescount = 0;
			}

			$sp_p_id = "";
			if ((int)$this->istart >= 0 && (int)$this->iend > 0)
			{
				$tmpend = $this->iend - $this->istart;
			} else {
				$tmpend = 0;
			}
			$tmpi = 0;
			while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$tmpi = $tmpi + 1;
				if ($tmpend > 0)
				{
					if ($tmpi > $tmpend)
					{
						break;
					}
				}

				$sp_p_id = $sp_p_id.$image_data['photo_id'].",";
			}
			$sp_p_id = substr($sp_p_id,0,strlen($sp_p_id) - 1);
			#echo $sp_p_id;
			#'liucongxu';
			$this->sp_photo_id_str = $sp_p_id;
			$this->select_image($db_link,"");
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
		return true;
	}
//added by wangtongchao 2011-12-13 begin
	function select_image_all($db_link, $where="")
	{
		// SQLをセットします。
		$sql = $this->set_sql();
		if(!empty($where))
		{
			$sql .= $where;
		}
		$sql .= " ORDER BY photo_id DESC";

		$sqlcount = $sql;

		$tmpistart = (int)$this->istart;
		$tmpend = (int)$this->iend;

		if ($tmpistart >= 0 && $tmpend > 0)
		{
			$sql .= " LIMIT ".$this->istart.",".$this->iend;
		}
//echo $sql;
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();
			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				// イメージを統計する

				$stmtcount = $db_link->prepare($sqlcount);
				$resultcount = $stmtcount->execute();
				if ($resultcount == true)
				{
					if ($this->imagescount <= 0)
					{
						$this->imagescount = $stmtcount->rowCount();
					}
				} else {
					$this->imagescount = 0;
				}

				$this->images = array();

				if ((int)$this->istart >= 0 && (int)$this->iend > 0)
				{
					$tmpend = $this->iend - $this->istart;
				} else {
					$tmpend = 0;
				}

				$tmpi = 0;
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$tmpi = $tmpi + 1;
					if ($tmpend > 0)
					{
						if ($tmpi > $tmpend)
						{
							break;
						}
					}
					// 画像データをセットします。
					$photo_idata = new PhotoImageDataAll();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			} else {
				$this->imagescount = 0;
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
			//echo $err[2];
		}

		return $this->images;
	}
//added by wangtongchao 2011-12-13 end
 	function select_image($db_link, $p_tmp_kikan)
	{
		// SQLをセットします。
		$sql = $this->set_sql();

		$optset = false;

		// 写真番号
		#echo $this->sp_photo_id_str;
		#$this->sp_photo_id_str = '';
		if (!empty($this->sp_photo_id_str))
		{
			$sql .= " WHERE photo_id IN (".$this->sp_photo_id_str.") ";
			$optset = true;
		}

		// 写真管理番号
		if (!empty($this->sp_photo_mno))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			//$sql .= "photo_mno LIKE ? ";
			//$opt[] = $this->sp_photo_mno;
			$sql .= "photo_mno COLLATE utf8_bin LIKE ".$this->sp_photo_mno;
			$optset = true;
		}

		// 写真名
		if (!empty($this->sp_photo_name))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "photo_name COLLATE utf8_bin LIKE ".$this->sp_photo_name;
			$optset = true;
		}

		// 写真説明
		if (!empty($this->sp_photo_explanation))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "photo_explanation COLLATE utf8_bin LIKE ".$this->sp_photo_explanation;
			$optset = true;
		}

		// 撮影時期ID
		if (is_numeric($this->sp_take_picture_time_id) && $this->sp_take_picture_time_id != -1)
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "photoimg.take_picture_time_id=" . $this->sp_take_picture_time_id;
			$optset = true;
		}

		// 撮影時期2ID
		if (is_numeric($this->sp_take_picture_time2_id) && $this->sp_take_picture_time2_id != -1)
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "photoimg.take_picture_time2_id=".$this->sp_take_picture_time2_id;
			$optset = true;
		}

		// 写真入手元（内容）
		if (!empty($this->sp_content_borrowing_ahead))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}

			$tmp = array();
			$tmp = explode(";",$this->sp_content_borrowing_ahead);
			// 写真入手元ID
			$sql .= "photoimg.borrowing_ahead_id=" . $tmp[0];
			if (count($tmp) > 1)
			{
				//if (substr($tmp[1],0,1) == "*")
				//{
				//	$sql .= " AND photoimg.content_borrowing_ahead COLLATE utf8_bin LIKE '%".substr($tmp[1],1)."%'";
				//} else {
				//	$sql .= " AND photoimg.content_borrowing_ahead COLLATE utf8_bin LIKE '".$tmp[1]."%'";
				//}
				$sql .= " AND photoimg.content_borrowing_ahead COLLATE utf8_bin LIKE '%".$tmp[1]."%'";
			}
			$optset = true;
		}

		// 使用範囲ID
		if (!empty($this->sp_range_of_use_id))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$tmp = array();
			$tmp = explode(";",$this->sp_range_of_use_id);
			$sql .= "photoimg.range_of_use_id=".$tmp[0];
			if (count($tmp) > 1)
			{
				if (!empty($tmp[1]))
				{
					//if (substr($tmp[1],0,1) == "*")
					//{
					//	$sql .= " AND use_condition COLLATE utf8_bin LIKE '%".substr($tmp[1],1)."%'";
					//} else {
					//	$sql .= " AND use_condition COLLATE utf8_bin LIKE '".$tmp[1]."%'";
					//}
					$sql .= " AND use_condition COLLATE utf8_bin LIKE '%".$tmp[1]."%'";
				}
			}
			$optset = true;
		}

		// 付加条件(1)
		if (!empty($this->sp_additional_constraints1))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "additional_constraints1 COLLATE utf8_bin LIKE ".$this->sp_additional_constraints1;
			$optset = true;
		}

		// 付加条件(2)
		if (!empty($this->sp_additional_constraints2))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "additional_constraints2 COLLATE utf8_bin LIKE ".$this->sp_additional_constraints2;
			$optset = true;
		}

		// 備考
		if (!empty($this->sp_note))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "note COLLATE utf8_bin LIKE ".$this->sp_note;
			$optset = true;
		}

		// 版権所有者
		if (!empty($this->sp_copyright_owner))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "copyright_owner COLLATE utf8_bin LIKE ".$this->sp_copyright_owner;
			$optset = true;
		}

		// 掲載期間（期間）
		if (!empty($this->sp_kikan))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "kikan ='".$this->sp_kikan."'";
			$optset = true;
		}

		// 掲載期間（To）
		if (!empty($this->sp_dto) && $this->sp_dto != "0000-00-00 00:00:00")
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$dt = $this->sp_dto;
			$dtto = $dt . " 23:59:59";
			$sql .= "dto <= '" .$dtto."'";
			$optset = true;
		}

		// 元画像ID
		if (!empty($this->sp_source_image_no))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "source_image_no COLLATE utf8_bin LIKE ".$this->sp_source_image_no;
			$optset = true;
		}

		// BUD 番号
		if (!empty($this->sp_bud_photo_no))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			if ($this->sp_bud_photo_no == "_")
			{
				$sql .= "bud_photo_no = NULL OR bud_photo_no = '' OR bud_photo_no = null OR bud_photo_no = \"\"";
			} else {
				//if (substr($this->sp_bud_photo_no,0,1) == "*")
				//{
				//	$sql .= "bud_photo_no COLLATE utf8_bin LIKE '%".substr($this->sp_bud_photo_no,1)."%'";
				//} else {
				//	$sql .= "bud_photo_no COLLATE utf8_bin LIKE '".$this->sp_bud_photo_no."%'";
				//}
				$sql .= "bud_photo_no COLLATE utf8_bin LIKE '%".$this->sp_bud_photo_no."%'";
			}
			$optset = true;
		}

		// 登録申請者
		if (!empty($this->sp_registration_person))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "registration_person COLLATE utf8_bin LIKE ".$this->sp_registration_person;
			$optset = true;
		}

		// 登録許可者
		if (!empty($this->sp_permission_person))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$sql .= "permission_person COLLATE utf8_bin LIKE ".$this->sp_permission_person;
			$optset = true;
		}

		// お客様情報
		if (!empty($this->sp_customer_info))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
//			$tmp_customer = "==".$this->sp_customer_info;
//			$sql .= " ISNULL(customer_section) = 0 AND ISNULL(customer_name) = 0";
//			$sql .= " AND customer_section != '' AND customer_name != ''";
//			$sql .= " AND (POSITION( customer_section COLLATE utf8_bin IN '".$tmp_customer."' ) >0 ";
//			$sql .= " OR POSITION( customer_name COLLATE utf8_bin IN '".$tmp_customer."' ) >0) ";
//			$optset = true;
			$sql .= "( customer_section COLLATE utf8_bin LIKE ".$this->sp_customer_info;
			$sql .= " OR customer_name COLLATE utf8_bin LIKE ".$this->sp_customer_info.")";
			$optset = true;
		}

		// 海外、国内、イメージ
		if (!empty($this->sp_classification_id))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			// --------WHERE SQL文を構築する（開始）--------------------------------------
			// 正常のSQL文は以下のようなSQL文です。
			/*
				photo_id IN (
					SELECT photoid FROM registration_classification WHERE classification_id IN
					(
						SELECT classification_id FROM classification WHERE classification_name like '%国内%'
					)
				)
			*/
			$sql1 = "SELECT photo_id FROM registration_classification WHERE classification_id IN(";
			$sql2 = "SELECT classification_id FROM classification WHERE classification_name COLLATE utf8_bin like".$this->sp_classification_id;
			$sql3 = $sql1.$sql2.")";
			$sql .= "photo_id IN (".$sql3.") ";
			// --------WHERE SQL文を構築する（終了）--------------------------------------
			$optset = true;
		}

		// 方面
		if (!empty($this->sp_direction_id))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			// --------WHERE SQL文を構築する（開始）--------------------------------------
			// 正常のSQL文は以下のようなSQL文です。
			/*
				photo_id IN (
					SELECT photoid FROM registration_classification WHERE direction_id IN
					(
						SELECT direction_id FROM direction WHERE direction_name like '%中国%'
					)
				)
			*/
			$sql1 = "SELECT photo_id FROM registration_classification WHERE direction_id IN(";
			$sql2 = "SELECT direction_id FROM direction WHERE direction_name COLLATE utf8_bin like".$this->sp_direction_id;
			$sql3 = $sql1.$sql2.")";
			$sql .= "photo_id IN (".$sql3.") ";
			// --------WHERE SQL文を構築する（終了）--------------------------------------
			$optset = true;
		}

		// 国・都道府県
		if (!empty($this->sp_country_prefecture_id))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			// --------WHERE SQL文を構築する（開始）--------------------------------------
			// 正常のSQL文は以下のようなSQL文です。
			/*
				photo_id IN (
					SELECT photoid FROM registration_classification WHERE country_prefecture_id IN
					(
						SELECT country_prefecture_id FROM country_prefecture WHERE country_prefecture_name like '%中国%'
					)
				)
			*/
			$sql1 = "SELECT photo_id FROM registration_classification WHERE country_prefecture_id IN(";
			$sql2 = "SELECT country_prefecture_id FROM country_prefecture WHERE country_prefecture_name COLLATE utf8_bin like".$this->sp_country_prefecture_id;
			$sql3 = $sql1.$sql2.")";
			$sql .= "photo_id IN (".$sql3.") ";
			// --------WHERE SQL文を構築する（終了）--------------------------------------
			$optset = true;
		}

		// 地名
		if (!empty($this->sp_place_id))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			// --------WHERE SQL文を構築する（開始）--------------------------------------
			// 正常のSQL文は以下のようなSQL文です。
			/*
				photo_id IN (
					SELECT photoid FROM registration_classification WHERE place_id IN
					(
						SELECT place_id FROM place WHERE place_name like '%中国%'
					)
				)
			*/
			$sql1 = "SELECT photo_id FROM registration_classification WHERE place_id IN(";
			$sql2 = "SELECT place_id FROM place WHERE place_name COLLATE utf8_bin like".$this->sp_place_id;
			$sql3 = $sql1.$sql2.")";
			$sql .= "photo_id IN (".$sql3.") ";
			// --------WHERE SQL文を構築する（終了）--------------------------------------
			$optset = true;
		}

		// カテゴリー
		if (!empty($this->sp_keyword_str))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			// --------WHERE SQL文を構築する（開始）--------------------------------------
			// 正常のSQL文は以下のようなSQL文です。
			/*
				photo_id IN (
					SELECT photo_id FROM keyword WHERE keyword_name like '%中国%'
					AND keyword_name
					IN (
					SELECT category_name
					FROM category
					WHERE category_name
					COLLATE utf8_bin LIKE '%物%'
					)
				)
			*/
			#echo $this->sp_keyword_str;
			$sql1 = "SELECT photo_id FROM keyword WHERE keyword_name COLLATE utf8_bin LIKE".$this->sp_keyword_str;
			$sql1 .= " AND keyword_name IN (";
			$sql1 .= "SELECT category_name FROM category WHERE category_name COLLATE utf8_bin LIKE".$this->sp_keyword_str.")";
			$sql .= "photo_id IN (".$sql1.") ";
			// --------WHERE SQL文を構築する（終了）--------------------------------------
			$optset = true;
		}

		$s_group_id = array_get_value($_SESSION,'group' ,"");

		// 制限付き
		if($s_group_id !== 'BUD'){
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}

			$sql .= " is_publish = 1 ";
			$optset = true;
		}

		// このアカウントのみ使用可(独占使用)
		if (!empty($this->sp_monopoly_use))
		{
			if ($optset == false)
			{
				$sql .= " WHERE ";
			}
			else
			{
				$sql .= " AND ";
			}
			$only_use = substr($this->sp_monopoly_use,1);
			$sql .= "monopoly_use = ".$only_use;
			//仅当前画像登録申請者和BUD管理者可查询
			if($only_use == "1" && $this->sp_login_id != "admin") {
			    $sql .= " AND registration_account = '".$this->sp_login_id."'";
			}
			$optset = true;
		}
		else {
		    if($this->sp_login_id != "admin") {

		        if ($optset == false)
		        {
		            $sql .= " WHERE ";
		        }
		        else
		        {
		            $sql .= " AND ";
		        }
		        
		        $sql .= " (monopoly_use = 1 AND registration_account = '".$this->sp_login_id."'"
		            ." OR monopoly_use = 0) ";
		        
		        $optset = true;
		    }
		}

		// 条件を設定します。
		if ($optset == false)
		{
			$sql .= " WHERE ";
		}
		else
		{
			$sql .= " AND ";
		}
		//$sql .= "photo_mno NOT LIKE '%申請中%'";
		//2008-12-22 debug
		$sql .= " photoimg.publishing_situation_id = 2";

		//$sql .= " photoimg.publishing_situation_id = 2 OR photoimg.publishing_situation_id = 1";
		if(!empty($p_tmp_kikan))
		{
			if((int)$p_tmp_kikan == 3)
			{
				$sql .= " AND DATEDIFF( photoimg.dto, NOW() ) >90";
			}

			if((int)$p_tmp_kikan == 6)
			{
				$sql .= " AND DATEDIFF( photoimg.dto, NOW() ) >180";
			}

			if((int)$p_tmp_kikan == 9)
			{
				$sql .= " AND photoimg.kikan = 'mukigen'";
			}
		}
		$sql .= " ORDER BY photo_id DESC";

		$sqlcount = $sql;

		if (!empty($this->sp_photo_id_str))
		{
			//処理しない
		} else {
			$tmpistart = (int)$this->istart;
			$tmpend = (int)$this->iend;

			if ($tmpistart >= 0 && $tmpend > 0)
			{
				$sql .= " LIMIT ".$this->istart.",".$this->iend;
			}
		}
		#echo $sql;
		$stmt = $db_link->prepare($sql);
		#var_dump($stmt);
		$result = $stmt->execute();
		if ($result == true)
		{
			#liucongxu1011
			#echo 'result=true';
			// 処理数を取得します。
			#echo $sql;
			$icount = $stmt->rowCount();
			#echo 'icount='.$icount;
			#liucongxu20211
			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				// イメージを統計する

				$stmtcount = $db_link->prepare($sqlcount);
				$resultcount = $stmtcount->execute();
				if ($resultcount == true)
				{
					if ($this->imagescount <= 0)
					{
						$this->imagescount = $stmtcount->rowCount();
					}
				} else {
					$this->imagescount = 0;
				}

				$this->images = array();
				//if (!empty($this->istart) && !empty($this->iend))
				//{
					if ((int)$this->istart >= 0 && (int)$this->iend > 0)
					{
						$tmpend = $this->iend - $this->istart;
					} else {
						$tmpend = 0;
					}
				//} else {
				//	$tmpend = 0;
				//}

				$tmpi = 0;
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$tmpi = $tmpi + 1;
					if ($tmpend > 0)
					{
						if ($tmpi > $tmpend)
						{
							break;
						}
					}
					// 画像データをセットします。
					$photo_idata = new PhotoImageDataAll();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			} else {
				$this->imagescount = 0;
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
			//echo $err[2];
		}

		return $this->images;
	}

	function select_image_fmid_2($db_link)
	{
		// 検索条件が設定されていない場合は、戻ります。
		if (empty($this->sp_photo_id_str))
		{
			return ;
		}

		// SQLをセットします。
		$sql = $this->set_sql();

		// 条件を文字列から配列にします。
		$sp_photo_id_a = explode(",", $this->sp_photo_id_str);
		$ed = count($sp_photo_id_a);
		$opt = array();
		$optset = false;
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($sp_photo_id_a))
			{
				if ($optset == false)
				{
					$sql .= " WHERE ";
				}
				else
				{
					$sql .= " or ";
				}
				$sql .= " photo_id = ? ";
				$opt[] = $sp_photo_id_a[$i];
				$optset = true;
			}
		}

		// 条件を設定します。
		$stmt = $db_link->prepare($sql);
		$ed = count($opt);
		for($i = 0 ; $i < $ed ; $i++)
		{
			$stmt->bindParam($i + 1, $opt[$i]);
		}
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();

			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				$this->images = array();
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// 画像データをセットします。
					$photo_idata = new PhotoImageDataAll();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}

		return $this->images;
	}

	function select_image_fmid($db_link)
	{
		// 検索条件が設定されていない場合は、戻ります。
		if (empty($this->sp_photo_id_str))
		{
			return ;
		}

		// SQLをセットします。
		$sql = $this->set_sql();
		$sql .= " WHERE photo_id=?";

		// 条件を文字列から配列にします。
		$sp_photo_id_a = explode(",", $this->sp_photo_id_str);
		$ed = count($sp_photo_id_a);
		$this->images = array();
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($sp_photo_id_a))
			{
				// 条件を設定します。
				$stmt = $db_link->prepare($sql);
				$stmt->bindParam(1, $sp_photo_id_a[$i]);
				$result = $stmt->execute();
				if ($result == true)
				{
					// 処理数を取得します。
					$icount = $stmt->rowCount();

					// 選択されたデータ数が１かどうかチェックします。
					if ($icount == 1)
					{
						$image_data = $stmt->fetch(PDO::FETCH_ASSOC);
						// 画像データをセットします。
						$photo_idata = new PhotoImageDataAll();
						$photo_idata->set_data($image_data);
						$this->images[] = $photo_idata;
					}
				}
				else
				{
					$err = $stmt->errorInfo();
					$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
					throw new Exception($this->message);
				}
			}
		}

		return $this->images;
	}

	//xu add it on 2010-12-09 start
	//select the csv data
	function select_image_csv($db_link,$sel_data,$where="")
	{
		// SQLをセットします。
		$sql = $this->set_sql();
		//$sql .= " WHERE photo_mno = \"申請中\"";
		if(strlen($sel_data)>0)
		{
			$sql .= " WHERE photoimg.publishing_situation_id = 2 and photo_id in "."(".$sel_data.")";
			$sel_data = "(".$sel_data.")";
		}
		else
		{
			$sql .= " WHERE photoimg.publishing_situation_id = 2";
			//yupengbo add 2011/11/15 start
			if(!empty($where))
			{
				$sql .= " AND ".$where;
			}
			//yupengbo add 2011/11/15 end
		}
		$sql .= " ORDER BY photo_id ";
//echo($sql);
//echo($sel_data);
//exit;
		//if (!empty($this->istart) && !empty($this->iend))
		//{
//			$tmpistart = (int)$this->istart;
//			$tmpend = (int)$this->iend;
//
//			if ($tmpistart >= 0 && $tmpend > 0)
//			{
//				$sql .= " LIMIT ".$this->istart.",".$this->per_page;
//			}
		//}
//		echo $sql;
		$this->images = array();
		// 条件を設定します。
		$stmt = $db_link->prepare($sql);
//		if(strlen($sel_data)>0)
//		{
//			$stmt->bindParam(1, $sel_data);
//		}
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();

			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				$this->images = array();
//				if (!empty($this->istart) && !empty($this->iend))
//				{
//					if ((int)$this->istart >= 0 && (int)$this->iend > 0)
//					{
//						$tmpend = $this->iend - $this->istart + 1;
//					} else {
//						$tmpend = 0;
//					}
//				} else {
//					$tmpend = 0;
//				}
//
//				$tmpi = 0;
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
//					$tmpi = $tmpi + 1;
//
//					if ($tmpend > 0)
//					{
//						if ($tmpi > $tmpend)
//						{
//							break;
//						}
//					}
					// 画像データをセットします。
					$photo_idata = new PhotoImageDataAll();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
		return $this->images;
	}
	//select the image data
	function select_image_registed($db_link,$where="",$orderby="")
	{
		// SQLをセットします。
		$sql = $this->set_sql();
		//$sql .= " WHERE photo_mno = \"申請中\"";
		$sql .= " WHERE photoimg.publishing_situation_id = 2";
		//yupengbo add 2011/11/14 start
		if(!empty($where)) {
			$sql .= " AND ".$where;
		}
		//yupengbo add 2011/11/14 end
		//yupengbo add 2011/11/15 start
		if(!empty($orderby))
		{
			$sql .= " ORDER BY ".$orderby;
		} else {
			$sql .= " ORDER BY photo_id ";
		}
		//yupengbo add 2011/11/15 end
		//$sql .= " ORDER BY photo_id ";//yupengbo comment 2011/11/15
//echo($sql);
//echo($sel_data);
//exit;
		//if (!empty($this->istart) && !empty($this->iend))
		//{
			$tmpistart = (int)$this->istart;
			$tmpend = (int)$this->iend;

			if ($tmpistart >= 0 && $tmpend > 0)
			{
				$sql .= " LIMIT ".$this->istart.",".$this->per_page;
			}
		//}
//		echo $sql;
		$this->images = array();
		// 条件を設定します。
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();

			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				error_log(date('Y-m-d H:i:s') .
                'sql语句：' . $sql . PHP_EOL . 'num:' . $icount . PHP_EOL,
                3, 'aaaa.log');
				$this->images = array();
				if (!empty($this->istart) && !empty($this->iend))
				{
					if ((int)$this->istart >= 0 && (int)$this->iend > 0)
					{
						$tmpend = $this->iend - $this->istart + 1;
					} else {
						$tmpend = 0;
					}
				} else {
					$tmpend = 0;
				}

				$tmpi = 0;
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$tmpi = $tmpi + 1;

					if ($tmpend > 0)
					{
						if ($tmpi > $tmpend)
						{
							break;
						}
					}
					// 画像データをセットします。
					$photo_idata = new PhotoImageDataAll();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
		return $this->images;
	}
	//xu add it on 2010-12-09 end

	//yupengbo add 2011/11/17 start
	function select_image_deleted($db_link,$where="",$orderby="register_date")
	{
		global $table_log_name;
		// SQLをセットします。
		$sql = "SELECT * FROM ".$table_log_name." WHERE 1=1";
		if(!empty($where)) $sql .= $where;
		$sql .= " ORDER BY ".$orderby;
		//print $sql;exit;
		$tmpistart = (int)$this->istart;
		$tmpend = (int)$this->iend;
		if ($tmpistart >= 0 && $tmpend > 0) $sql .= " LIMIT ".$this->istart.",".$this->per_page;
		$this->images = array();
		// 条件を設定します。
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		// echo $sql;
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();
			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				$this->images = array();
				$tmpend = 0;
				if ((!empty($this->istart) && !empty($this->iend)) &&
				    ((int)$this->istart >= 0 && (int)$this->iend > 0)
				    ) $tmpend = $this->iend - $this->istart + 1;

				$tmpi = 0;
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$tmpi = $tmpi + 1;
					if (($tmpend > 0) && ($tmpi > $tmpend)) break;
					// 画像データをセットします。
					$photo_idata = new PhotoImageLog();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			}
		} else {
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
		return $this->images;
	}
	//yupengbo add 2011/11/17 end

	//jinxin add 2012/02/07 start
	function select_image_nopermit($db_link,$where="",$orderby="permit_date")
	{
		global $table_log_name;
		// SQLをセットします。
		$sql = "SELECT * FROM photoimg WHERE photoimg.publishing_situation_id = 3 AND photo_server_flg = 1";
		if(!empty($where)) $sql .= $where;
		$sql .= " ORDER BY ".$orderby;
		//print $sql;exit;
		$tmpistart = (int)$this->istart;
		$tmpend = (int)$this->iend;
		if ($tmpistart >= 0 && $tmpend > 0) $sql .= " LIMIT ".$this->istart.",".$this->per_page;
		$this->images = array();
		// 条件を設定します。
//		echo $sql;
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();
			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				$this->images = array();
				$tmpend = 0;
				if ((!empty($this->istart) && !empty($this->iend)) &&
				    ((int)$this->istart >= 0 && (int)$this->iend > 0)
				    ) $tmpend = $this->iend - $this->istart + 1;

				$tmpi = 0;
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
//					print_r($image_data);			//add by jinxin 2012/02/07
					$tmpi = $tmpi + 1;
					if (($tmpend > 0) && ($tmpi > $tmpend)) break;
					// 画像データをセットします。
					$photo_idata = new PhotoImageNopermit();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			}
		} else {
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
		return $this->images;
	}
	//jinxin add 2012/02/07 end

	function select_image_registration($db_link)
	{
		global $one_page_records_cnt;
		// SQLをセットします。
		$sql = $this->set_sql();
		//$sql .= " WHERE photo_mno = \"申請中\"";
		//added by wangtongchao 2011-12-14 begin
		$sql .= " WHERE photoimg.publishing_situation_id = 1 AND photo_server_flg = 1";
		//added by wangtongchao 2011-12-14 end
		$sql .= " ORDER BY photo_id ";

		//if (!empty($this->istart) && !empty($this->iend))
		//{
			$tmpistart = (int)$this->istart;
			$tmpend = (int)$this->iend;

			if ($tmpistart >= 0 && $tmpend > 0)
			{
				//$sql .= " LIMIT ".$this->istart.",".$this->iend;
				$sql .= " LIMIT ".$this->istart.",".$one_page_records_cnt;
			}
		//}
		//echo $sql;
		$this->images = array();
		// 条件を設定します。
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();

			// 選択されたデータ数が１かどうかチェックします。
			if ($icount > 0)
			{
				$this->images = array();
				if (!empty($this->istart) && !empty($this->iend))
				{
					if ((int)$this->istart >= 0 && (int)$this->iend > 0)
					{
						$tmpend = $this->iend - $this->istart + 1;
					} else {
						$tmpend = 0;
					}
				} else {
					$tmpend = 0;
				}

				$tmpi = 0;
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$tmpi = $tmpi + 1;

					if ($tmpend > 0)
					{
						if ($tmpi > $tmpend)
						{
							break;
						}
					}
					// 画像データをセットします。
					$photo_idata = new PhotoImageDataAll();
					$photo_idata->set_data($image_data);
					$this->images[] = $photo_idata;
				}
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
		return $this->images;
	}
}

class Album
{
	var $message;					// メッセージ
	var $error;						// エラー

	var $sp_album_id;				// アルバムID
	var $sp_album_name;				// アルバム名
	var $sp_album_explanation;		// 説明
	var $sp_registration_date;		// 登録日

	var $sp_state;					// ステータス（現在は常に0）

	var $sp_photo_id;				//	イメージID
	var $sp_photo_mno;              //　イメージ管理番号
	var $sp_photo_name;			    //　写真名（タイトル）
	var $sp_up_url;					//　アップロードURL

	function __construct() {
		
	}
	function set_album_id($album_id)
	{
		if (!empty($album_id))
		{
			$this->sp_album_id = $album_id;
		}
	}

	function set_album_name($album_name)
	{
		if (!empty($album_name))
		{
			$this->sp_album_name = $album_name;
		}
	}

	function set_album_explanation($album_explanation)
	{
		if (!empty($album_explanation))
		{
			$this->sp_album_explanation = $album_explanation;
		}
	}

	function set_album_registration_date($album_registration_date)
	{
		if (!empty($album_registration_date))
		{
			$this->sp_registration_date = $album_registration_date;
		}
	}

	function set_state($state)
	{
		if (!empty($state))
		{
			$this->sp_state = $state;
		}
	}

	function set_photo_id($photo_id)
	{
		if (!empty($photo_id))
		{
			$this->sp_photo_id = $photo_id;
		}
	}

	function set_photo_mno($photo_mno)
	{
		if (!empty($photo_mno))
		{
			$this->sp_photo_mno = $photo_mno;
		}
	}

	function set_photo_name($photo_name)
	{
		if (!empty($photo_name))
		{
			$this->sp_photo_name = $photo_name;
		}
	}

	function Album()
	{
		// タイムゾーンを設定します。
		date_default_timezone_set("Asia/Tokyo");
		// メンバーを初期化します。
		$this->init_data();
	}

	/**
	 * データを初期化します。
	 */
	function init_data()
	{
		$this->sp_album_id = -1;						// アルバムID
		$this->sp_album_name = "";						// アルバム名
		$this->sp_album_explanation = "";				// 説明
		$this->sp_registration_date = date("Y-m-d");	// 登録日
		$this->state = 0;								// ステータス（現在は常に0）

		$this->sp_photo_id = -1;						//	イメージID
		$this->sp_photo_mno = "";						//　イメージ管理番号
		$this->sp_photo_name = "";						//　写真名（タイトル）
		$this->sp_up_url = array();						// アップロードURL（最終的にアップロードされたURL）
														//	0:元、1:サムネイル1、2:サムネイル2・・・
	}

	/*
	 * 関数名：insert_data
	 * 関数説明：アルバムに登録する
	 * パラメタ：
	 * $db_link:データベースのリンク
	 * 戻り値：無し
	 */
	function insert_data($db_link)
	{
		$sql = "SELECT max( album_id ) + 1 max_id FROM album;";
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 最終番号を取得します。
			$max = $stmt->fetch(PDO::FETCH_ASSOC);
			$this->sp_album_id = $max['max_id'];
		}
		else
		{
			// エラーの場合は例外をスローします。
			$this->message = "最終番号のMAX値を取得できませんでした。";
			throw new Exception($this->message);
		}
		// 最終番号を補正します。
		if (empty($this->sp_album_id))
		{
			$this->sp_album_id = 1;
		}

		$sql = "INSERT INTO album (album_id, album_name, album_explanation, registration_date, state) VALUES ( ";
		$sql .= $this->sp_album_id . ",";					// アルバムID
		$sql .= "\"".$this->sp_album_name . "\",";			// アルバム名
		$sql .= "\"".$this->sp_album_explanation . "\",";	// 説明
		$sql .= "\"".$this->sp_registration_date . "\",";	// 登録日
		$sql .= $this->state;								// ステータス
		$sql .= ");";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount != 1)
			{
				$this->message = "アルバムをDBに登録できませんでした。";
				throw new Exception($this->message);
			}
		}
		else
		{
			$this->message = "アルバムをDBに登録できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	/*
	 * 関数名：set_data
	 * 関数説明：元のデータクラスより、このクラスに設定する
	 * パラメタ：
	 * $rcdata:	元のデータクラス
	 * 戻り値：無し
	 */
	function set_data($rcdata)
	{
		$this->sp_album_id = $rcdata['album_id'];						// アルバムID
		$this->sp_album_name = $rcdata['album_name'];					// アルバム名
		$this->sp_album_explanation = $rcdata['album_explanation'];		// 説明
		$this->sp_registration_date = $rcdata['registration_date'];		// 登録日
	}

	/*
	 * 関数名：set_data2
	 * 関数説明：元のデータクラスより、このクラスに設定する
	 * パラメタ：
	 * $rcdata:	元のデータクラス
	 * 戻り値：無し
	 */
	function set_data2($rcdata)
	{
		$this->set_data($rcdata);

		$this->sp_up_url = array();

		$this->sp_photo_id   =  $rcdata['photo_id'];					// イメージID
		$this->sp_photo_mno  =  $rcdata['photo_mno'];					// イメージ管理番号
		$this->sp_photo_name =  $rcdata['photo_name'];					// 写真名（タイトル）
		$this->sp_up_url[0]  =  $rcdata['photo_filename'];				//	0:元、1:サムネイル1、2:サムネイル2・・・
		$this->sp_up_url[1]  =  $rcdata['photo_filename_th1'];
		$this->sp_up_url[2]  =  $rcdata['photo_filename_th2'];
		$this->sp_up_url[3]  =  $rcdata['photo_filename_th3'];
		$this->sp_up_url[4]  =  $rcdata['photo_filename_th4'];
		$this->sp_up_url[5]  =  $rcdata['photo_filename_th5'];
		$this->sp_up_url[6]  =  $rcdata['photo_filename_th6'];
		$this->sp_up_url[7]  =  $rcdata['photo_filename_th7'];
		$this->sp_up_url[8]  =  $rcdata['photo_filename_th8'];
		$this->sp_up_url[9]  =  $rcdata['photo_filename_th9'];
		$this->sp_up_url[10] =  $rcdata['photo_filename_th10'];
	}

	/*
	 * 関数名：update_registration_date
	 * 関数説明：指定のアルバムＩＤの登録日を更新する
	 * パラメタ：
	 * $db_link:	データベースのリンク
	 * $album_id:	アルバムＩＤ
 	 * $new_date:	システム日付
	 * 戻り値：無し
	 */
	function update_registration_date($db_link,$album_id,$new_date)
	{
		$sql = "UPDATE album SET registration_date = \"".$new_date."\" WHERE album_id = ".$album_id.";";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == false)
		{
			$this->message = "アルバムをDBに更新できませんでした。";
			throw new Exception($this->message);
		}
	}

	/*
	 * 関数名：delete_data
	 * 関数説明：指定のアルバムＩＤの文字列より、アルバムテーブルから削除する
	 * パラメタ：
	 * $db_link:	データベースのリンク
	 * $albm_id_str:アルバムＩＤの文字列
	 * 戻り値：無し
	 */
	function delete_data($db_link,$albm_id_str)
	{
		// 条件を文字列から配列にします。
		$albm_id_a = array();
		$albm_id_a = explode(",", $albm_id_str);
		$ed = count($albm_id_a);
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($albm_id_a[$i]))
			{
				$sql = "DELETE FROM album WHERE album_id = ".$albm_id_a[$i].";";
				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == false)
				{
					// エラーの場合は例外をスローします。
					$this->message = "アルバムの削除ができませんでした。";
					// 例外をスローします。
					$msg = $e->getMessage();
					throw new Exception($msg);
				}
			}
		}
	}

	/*
	 * 関数名：delete_data2
	 * 関数説明：アルバムテーブルから、指定のアルバムＩＤを削除する
	 * パラメタ：
	 * $db_link:データベースのリンク
	 * $albm_id:アルバムＩＤ
	 * 戻り値：無し
	 */
	function delete_data2($db_link,$albm_id)
	{
		if (!empty($albm_id))
		{
			$sql = "DELETE FROM album WHERE album_id = ".$albm_id.";";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == false)
			{
				// エラーの場合は例外をスローします。
				$this->message = "アルバムの削除ができませんでした。";
				// 例外をスローします。
				$msg = $e->getMessage();
				throw new Exception($msg);
			}
		}
	}

	/*
	 * 関数名：update_data
	 * 関数説明：アルバムテーブルを更新する
	 * パラメタ：
	 * $db_link:データベースのリンク
	 * $albm_id:アルバムＩＤ
	 * 戻り値：無し
	 */
	function update_data($db_link,$albm_id)
	{
		if (!empty($albm_id))
		{
			$sql = "UPDATE album SET album_name = \"".$this->sp_album_name."\", album_explanation = \"".$this->sp_album_explanation."\" WHERE album_id = ".$albm_id.";";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == false)
			{
				// エラーの場合は例外をスローします。
				$this->message = "アルバムが更新できませんでした。";
				// 例外をスローします。
				$msg = $e->getMessage();
				throw new Exception($msg);
			} else {
				//$new_date = date("Y-m-d");
				//$this->update_registration_date($db_link,$albm_id,$new_date);
			}
		}
	}
}

class AlbumDetail extends Album
{
	var $message;					// メッセージ
	var $error;						// エラー

	var $sp_album_detail_id;		// アルバム詳細ID
	var $sp_photo_id;				// 写真ID
	var $sp_state;					// ステータス（現在は常に0）

	function __construct() {
		
	}
	
	function set_album_detail_id($album_detail_id)
	{
		if (!empty($album_detail_id))
		{
			$this->sp_album_detail_id = $album_detail_id;
		}
	}

	function set_photo_id($photo_id)
	{
		if (!empty($photo_id))
		{
			$this->sp_photo_id = $photo_id;
		}
	}

	function set_state($state)
	{
		if (!empty($state))
		{
			$this->sp_state = $state;
		}
	}

	function AlbumDetail()
	{
		// タイムゾーンを設定します。
		date_default_timezone_set("Asia/Tokyo");
		Album::init_data();
		// メンバーを初期化します。
		$this->init_data();
	}

	/**
	 * データを初期化します。
	 */
	function init_data()
	{
		$this->sp_album_detail_id = -1;					// アルバム詳細ID
		$this->sp_photo_id = -1;						// 写真ID
		$this->state = 0;								// ステータス（現在は常に0）
	}

	/*
	 * 関数名：insert_data
	 * 関数説明：アルバム詳細テーブルに登録します。
	 * パラメタ：
	 * $db_link:		データベースのリンク
	 * $photo_id_str:	イメージＩＤの文字列（"23,24,25"）
	 * $flg:			「0」：アルバムテーブルに登録する
	 *					「1」：アルバムテーブルに登録しない
	 * 戻り値：無し
	 */
	function insert_data_detail($db_link,$photo_id_str,$flg)
	{
		if ($flg == 0) Album::insert_data($db_link);

		// 条件を文字列から配列にします。
		$sp_photo_id_a = explode(",", $photo_id_str);
		$ed = count($sp_photo_id_a);
		$this->images = array();
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($sp_photo_id_a[$i]))
			{
				$sql = "SELECT max( album_detail_id ) + 1 max_id FROM album_detail;";
				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == true)
				{
					// 最終番号を取得します。
					$max = $stmt->fetch(PDO::FETCH_ASSOC);
					$this->sp_album_detail_id = $max['max_id'];
				}
				else
				{
					// エラーの場合は例外をスローします。
					$this->message = "最終番号のMAX値を取得できませんでした。";
					throw new Exception($this->message);
				}
				// 最終番号を補正します。
				if (empty($this->sp_album_detail_id))
				{
					$this->sp_album_detail_id = 1;
				}

				$sql = "INSERT INTO album_detail (album_detail_id, album_id, photo_id, state) VALUES ( ";
				$sql .= $this->sp_album_detail_id . ",";		// アルバム詳細ID
				$sql .= $this->sp_album_id . ",";				// アルバムID
				$sql .= $sp_photo_id_a[$i] . ",";				// 画像ID
				$sql .= $this->state;							// ステータス
				$sql .= ");";

				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == true)
				{
					// 実行結果がOKの場合の処理です。
					$icount = $stmt->rowCount();
					if ($icount != 1)
					{
						$this->message = "アルバム詳細をDBに登録できませんでした。（処理数!=1） No=" . $i;
						throw new Exception($this->message);
					}
				}
				else
				{
					$this->message = "アルバム詳細をDBに登録できませんでした。（条件設定エラー） No=" . $i;

					// 例外をスローします。
					$msg = $e->getMessage();
					throw new Exception($msg);
				}
			}
		}
	}

	/*
	 * 関数名：insert_image
	 * 関数説明：イメージをアルバム詳細に登録します。
	 * パラメタ：
	 * $db_link:		データベースのリンク
	 * $photo_id_str:	イメージＩＤの文字列（"23,24,25"）
	 * $albm_id:		アルバムＩＤ
	 * 戻り値：無し
	 */
	function insert_image($db_link,$albm_id,$photo_id_str)
	{
		// 条件を文字列から配列にします。
		$sp_photo_id_a = explode(",", $photo_id_str);
		$ed = count($sp_photo_id_a);
		$this->images = array();
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($sp_photo_id_a[$i]))
			{
				$sql = "SELECT count( * ) cnt FROM album_detail WHERE photo_id = \"".$sp_photo_id_a[$i]."\" AND album_id = ".$albm_id.";";

				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == true)
				{
					// イメージの存在性のチェック
					$cnt = $stmt->fetch(PDO::FETCH_ASSOC);
					if ($cnt['cnt'] == 0)
					{
						$this->sp_album_id = $albm_id;
						$this->insert_data($db_link,$sp_photo_id_a[$i],1);
						$new_date = date("Y-m-d");
						$this->update_registration_date($db_link,$albm_id,$new_date);
					}
				}
				else
				{
					// エラーの場合は例外をスローします。
					$this->message = "イメージの存在性のチェックができませんでした。";
					// 例外をスローします。
					$msg = $e->getMessage();
					throw new Exception($msg);
				}
			}
		}
	}

	/*
	 * 関数名：delete_data
	 * 関数説明：アルバム詳細テーブルからデータを削除する。
	 * パラメタ：
	 * $db_link:		データベースのリンク
	 * $albm_id_str:	アルバムＩＤの文字列（"23,24,25"）
	 * 戻り値：無し
	 */
	function delete_data($db_link,$albm_id_str)
	{
		// 条件を文字列から配列にします。
		$albm_id_a = array();
		$albm_id_a = explode(",", $albm_id_str);
		$ed = count($albm_id_a);
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($albm_id_a[$i]))
			{
				Album::delete_data2($db_link,$albm_id_a[$i]);
				$sql = "DELETE FROM album_detail WHERE album_id = ".$albm_id_a[$i].";";
				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == false)
				{
					// エラーの場合は例外をスローします。
					$this->message = "アルバム詳細の削除ができませんでした。";
					// 例外をスローします。
					$msg = $e->getMessage();
					throw new Exception($msg);
				}
			}
		}
	}

	/*
	 * 関数名：update_data
	 * 関数説明：アルバム詳細テーブルを更新する。
	 * パラメタ：
	 * $db_link:		データベースのリンク
	 * $photo_id_str:	イメージＩＤの文字列（"23,24,25"）
	 * $albm_id:		アルバムＩＤ
	 * 戻り値：無し
	 */
	function update_data_detail($db_link,$albm_id,$photo_id_str)
	{
		if (empty($albm_id)) return;

		//アルバムの情報を更新する
		Album::update_data($db_link,$albm_id);

		if (!empty($photo_id_str))
		{
			// 条件を文字列から配列にします。
			$photoid_a = array();
			$photoid_a = explode(",", $photo_id_str);
			$ed = count($photoid_a);
			$str1 = "";

			for ($i = 0 ; $i < $ed ; $i++)
			{
				if (!empty($photoid_a[$i]))
				{
					$str1 .= $photoid_a[$i];
					if ($i == $ed - 1)
					{
						$str1 .= "'";
					} else {
						$str1 .= "','";
					}
				}
			}
			if ($str1 != "")
			{
				$str1 = "'".$str1;
			}

			//削除イメージ
			$sql = "DELETE FROM album_detail WHERE album_id = ".$albm_id;
			$sql .= " AND photo_id NOT IN (".$str1.");";

			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == false)
			{
				// エラーの場合は例外をスローします。
				$this->message = "アルバム詳細の更新ができませんでした。";
				// ロールバックします。
				// 例外をスローします。
				$msg = $e->getMessage();
				throw new Exception($msg);
			}
		}
	}
}

class AlbumSearch
{
	// 本来ならPrivateにした方が良いと思いますが利便性を考えvarで宣言しています。
	var $message;									// メッセージ
	var $error;										// エラー

	var $albums;									//　アルバムのインスタンス保存用（配列）

	function __construct()
	{
		// 条件を初期化します。
		$this->init_condition();
	}

	function init_condition()
	{
		$this->albums = array();					// アルバムのインスタンス保存用
	}

	/*
	 * 関数名：select_data
	 * 関数説明：アルバム詳細テーブルから全部データを検索する。
	 * パラメタ：
	 * $db_link:		データベースのリンク
	 * 戻り値：無し
	 */
	function select_data($db_link)
	{
		// アルバムをDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "SELECT album.album_id, album.album_name, album.album_explanation, ";
		$sql .= " album.registration_date FROM album ORDER BY album.album_id desc;";

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
				$this->albums = array();
				while ($album_class = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// アルバムをセットします。
					$ab_cls = new Album();
					$ab_cls->set_data($album_class);
					$this->albums[] = $ab_cls;
				}
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "アルバムを取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	/*
	 * 関数名：get_imgcnts
	 * 関数説明：「点数」を取得する。
	 * パラメタ：
	 * $db_link:データベースのリンク
	 * $albm_id:アルバムＩＤ
	 * 戻り値：取得した「点数」
	 */
	function get_imgcnts($db_link,$albm_id)
	{
		$img_cnts = 0;

		if (!empty($albm_id))
		{
		 	$sql = "SELECT COUNT( * ) cnt FROM album_detail WHERE album_id = ".$albm_id.";";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 点数を取得します。
				$cnt = $stmt->fetch(PDO::FETCH_ASSOC);
				$img_cnts = $cnt['cnt'];
			}
			else
			{
				// エラーの場合は例外をスローします。
				$this->message = "点数を取得できませんでした。";
				throw new Exception($this->message);
			}
		}

		return $img_cnts;
	}

	/*
	 * 関数名：set_sql
	 * 関数説明：ＳＱＬ文句の設定する。
	 * パラメタ：
	 * $albm_id:アルバムＩＤ
	 * 戻り値：ＳＱＬ文句
	 */
	private function set_sql($albm_id)
	{
		$sql = "SELECT album.album_id, album.album_name, album.album_explanation,";
		$sql .= " album.registration_date, album_detail.photo_id, photoimg.photo_mno, photoimg.photo_name,";
		$sql .= " photoimg.photo_filename, photoimg.photo_filename_th1, photoimg.photo_filename_th2,";
		$sql .= " photoimg.photo_filename_th3, photoimg.photo_filename_th4, photoimg.photo_filename_th5,";
		$sql .= " photoimg.photo_filename_th6,photoimg.photo_filename_th7,photoimg.photo_filename_th8,";
		$sql .= " photoimg.photo_filename_th9,photoimg.photo_filename_th10 FROM album ";
		$sql .= " LEFT JOIN album_detail on album_detail.album_id = album.album_id ";
		$sql .= " LEFT JOIN photoimg on photoimg.photo_id = album_detail.photo_id ";
		$sql .= " WHERE album.album_id = ".$albm_id;

		return $sql;
	}

	/*
	 * 関数名：select_data2
	 * 関数説明：アルバム詳細テーブルから、指定のアルバムの情報を検索する。
	 * パラメタ：
	 * $db_link:データベースのリンク
	 * $albm_id:アルバムＩＤ
	 * 戻り値：無し
	 */
	function select_data2($db_link,$albm_id)
	{
		// アルバムとイメージの情報をDBより取得します。
		// 取得するためのSQLを作成します。
		// SQLをセットします。
		$sql = $this->set_sql($albm_id);

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
				$this->albums = array();
				while ($album_class = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// アルバムをセットします。
					$ab_cls = new Album();
					$ab_cls->set_data2($album_class);
					$this->albums[] = $ab_cls;
				}
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "アルバムとイメージの情報を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}

	/*
	 * 関数名：select_data3
	 * 関数説明：指定のアルバムとイメージの情報とを検索する。
	 * パラメタ：
	 * $db_link:		データベースのリンク
	 * $albm_id:		アルバムＩＤ
	 * $p_photoid_str:	イメージＩＤの文字列（"23,24,25"）
	 * 戻り値：無し
	 */
	function select_data3($db_link,$albm_id,$p_photoid_str)
	{
		// 検索条件が設定されていない場合は、戻ります。
		if (empty($p_photoid_str))
		{
			return ;
		}

		// 条件を文字列から配列にします。
		$photoid_a = array();
		$photoid_a = explode(",", $p_photoid_str);
		$ed = count($photoid_a);
		$str1 = "";
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($photoid_a[$i]))
			{
				$str1 .= $photoid_a[$i];
				if ($i == $ed - 1)
				{
					$str1 .= "'";
				} else {
					$str1 .= "','";
				}
			}
		}
		if ($str1 != "")
		{
			$str1 = "'".$str1;
		}

		// SQLをセットします。
		$sql = $this->set_sql($albm_id);
		$sql .= " AND album_detail.photo_id IN (" .$str1. ")";

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
				$this->albums = array();
				while ($album_class = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// アルバムをセットします。
					$ab_cls = new Album();
					$ab_cls->set_data2($album_class);
					$this->albums[] = $ab_cls;
				}
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			$this->message = "アルバムとイメージの情報を取得できませんでした。（条件設定エラー）";
			throw new Exception($this->message);
		}
	}
}

/*
 * クラス名：CsvFile
 * クラス説明：アップロードとダウロードの状態を管理する
 */
class CsvFile
{
	var $message;					// メッセージ
	var $error;						// エラー

	var $file_name;					// CSVファイル名
	var $up_user;					// アップロードのユーザー
	var $up_time;					// アップロードのタイム
	var $down_user;					// ダウロードのユーザー
	var $down_time;					// ダウロードのタイム

	function set_file_name($sp_file_name)
	{
		if (!empty($sp_file_name))
		{
			$this->file_name = $sp_file_name;
		}
	}

	function set_up_user($sp_up_user)
	{
		if (!empty($sp_up_user))
		{
			$this->up_user = $sp_up_user;
		}
	}

	function set_up_time($sp_up_time)
	{
		if (!empty($sp_up_time))
		{
			$this->up_time = $sp_up_time;
		}
	}

	function set_down_user($sp_down_user)
	{
		if (!empty($sp_down_user))
		{
			$this->down_user = $sp_down_user;
		}
	}

	function set_down_time($sp_down_time)
	{
		if (!empty($sp_down_time))
		{
			$this->down_time = $sp_down_time;
		}
	}

	/*
	 * 関数名：isExits
	 * 関数説明：登録するかどうかチェックする
	 * パラメタ：
	 * db_link:	データベースのリンク
	 * 戻り値：無し
	 */
	function isExits($db_link)
	{
		// 検索のSQL文
		$sql = "SELECT count(*) cnt FROM csvfile ";
		$sql .= " WHERE file_name = \"" .$this->file_name. "\"";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$cnt = $stmt->fetch(PDO::FETCH_ASSOC);
			$img_cnts = $cnt['cnt'];
			return $img_cnts;
		} else {
			// 実行結果がNGの場合の処理です。
			// エラー情報をセットして、例外をスローします。
			$err = $stmt->errorInfo();
			throw new Exception($err);
			return -1;
		}
	}

	/*
	 * 関数名：select_data
	 * 関数説明：DBからCSVファイルの状態を検索する
	 * パラメタ：
	 * db_link:	データベースのリンク
	 * 戻り値：無し
	 */
	function select_data($db_link)
	{
		// 検索のSQL文
		$sql = "SELECT up_user,up_time,down_user,down_time FROM csvfile ";
		$sql .= " WHERE file_name = \"" .$this->file_name. "\"";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$csv = $stmt->fetch(PDO::FETCH_ASSOC);
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				$this->up_user = $csv['up_user'];
				$this->up_time = $csv['up_time'];
				$this->down_user = $csv['down_user'];
				$this->down_time = $csv['down_time'];
			} else {
				$this->up_user = "";
				$this->up_time = null;
				$this->down_user = "";
				$this->down_time = null;
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
	 * 関数名：insert_data
	 * 関数説明：CSVファイルテーブルに登録します。
	 * パラメタ：
	 * $db_link: データベースのリンク
	 * 戻り値：無し
	 */
	function insert_data($db_link)
	{
		// 新規のSQL文
		$sql = "INSERT INTO csvfile (file_name, up_user, up_time) VALUES ( ";
		$sql .= "\"".$this->file_name . "\",";		// CSVファイル名
		$sql .= "\"".$this->up_user . "\",";		// アップロードのユーザー
		$sql .= "\"".$this->up_time . "\"";			// アップロードのタイム
		$sql .= ");";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount != 1)
			{
				$this->message = "CSVファイルをDBに登録できませんでした。（処理数!=1）";
				throw new Exception($this->message);
			}
		}
		else
		{
			$this->message = "CSVファイルをDBに登録できませんでした。（条件設定エラー）";
			// 例外をスローします。
			$msg = $e->getMessage();
			throw new Exception($msg);
		}
	}

	/*
	 * 関数名  ：update_data
	 * 関数説明：CSVファイルテーブルに更新します。
	 * パラメタ：
	 * db_link ：データベースのリンク
	 * flg     ：「1」：アップロードを更新する；
	 *           「2」：ダウロードを更新する；
	 * 戻り値　：無し
	 */
	function update_data($db_link,$flg)
	{
		// 新規するかどうかチェックする
		$insert_flg = $this->isExits($db_link);
		// 存在しない場合
		if ((int)$insert_flg == 0)
		{
			$this->insert_data($db_link);
		// 存在した場合
		} else if ((int)$insert_flg > 0) {
			// 更新のSQL文
			$sql = "UPDATE csvfile SET ";
			if ((int)$flg == 1)
			{
				$sql .= "up_user = '".$this->up_user."',";
				$sql .= "up_time = now(),";
				$sql .= "down_user = '',";
				$sql .= "down_time = NULL";
			} elseif ((int)$flg == 2) {
				$sql .= "up_user = '',";
				$sql .= "up_time = NULL,";
				$sql .= "down_user = '".$this->down_user."',";
				$sql .= "down_time = now()";
			}

			$sql .= " WHERE file_name = \"".$this->file_name."\"";

			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == false)
			{
				$this->message = "CSVファイルをDBに更新できませんでした。（条件設定エラー）";
				// 例外をスローします。
				$msg = $e->getMessage();
				throw new Exception($msg);
			}
		}
	}
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

class UserManger
{
	var $message;					// メッセージ
	var $error;						// エラー

	var $ID;						// No
	var $user_name;					// ユーザー名
	var $user_group;				// 所属
	var $user_login_id;				// ID
	var $user_password;				// パスワード
	var $user_comp_code;			// 画像番号
	var $user_security_level;		// 権限
	var $user_kikan;				// 期間
	var $start_date;				// 開始日
	var $end_date;					// 終了日
	var $istart;					// 開始
	var $iend;						// 終了
	var $users;						// ユーザー
	var $user_email;				// email

	function set_ID($sp_ID)
	{
		if (!empty($sp_ID)) $this->ID = $sp_ID;
	}

	function set_user_name($sp_user_name)
	{
		if (!empty($sp_user_name)) $this->user_name = $sp_user_name;
	}

	function set_user_group($sp_user_group)
	{
		if (!empty($sp_user_group)) $this->user_group = $sp_user_group;
	}

	function set_user_login_id($sp_user_login_id)
	{
		if (!empty($sp_user_login_id)) $this->user_login_id = $sp_user_login_id;
	}

	function set_user_password($sp_user_password)
	{
		if (!empty($sp_user_password)) $this->user_password = $sp_user_password;
	}

	function set_user_comp_code($sp_user_comp_code)
	{
		if (!empty($sp_user_comp_code)) $this->user_comp_code = $sp_user_comp_code;
	}

	function set_user_security_level($sp_user_security_level)
	{
		if(!empty($sp_user_security_level)) $this->user_security_level = $sp_user_security_level;
	}
	//xu add it on 2010-12-03 start
	function  set_user_email($sp_user_email)
	{
		if(!empty($sp_user_email)) $this->user_email = $sp_user_email;
	}
	//xu add it on 2010-12-03 end

	function set_user_kikan($sp_user_kikan)
	{
		if (!empty($sp_user_kikan)) $this->user_kikan = $sp_user_kikan;
	}

	function set_istart($sp_istart)
	{
		if (!empty($sp_istart) || $sp_istart == 0) $this->istart = $sp_istart;
	}

	function set_iend($sp_iend)
	{
		if (!empty($sp_iend) || $sp_iend == 0) $this->iend = $sp_iend;
	}

	function set_start_date($sp_start_date)
	{
		if (!empty($sp_start_date)) $this->start_date = $sp_start_date;
	}

	function set_end_date($sp_end_date)
	{
		if (!empty($sp_end_date)) $this->end_date = $sp_end_date;
	}

	function select_user($db_link,$search_where="")
	{
		if(!empty($search_where)){
			$sql = "select * from `user` where user_name like '%".$search_where."%' ORDER BY user_id";
		}else {
			$sql = "select * from `user` order by user_id";
		}

		$tmpistart = (int)$this->istart;
		$tmpend = (int)$this->iend;

		if ($tmpistart >= 0 && $tmpend > 0)
		{
			$sql .= " LIMIT ".$this->istart.",".$this->iend;
		}

		$this->users = array();

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				if (!empty($this->istart) && !empty($this->iend))
				{
					if ((int)$this->istart >= 0 && (int)$this->iend > 0)
					{
						$tmpend = $this->iend - $this->istart + 1;
					} else {
						$tmpend = 0;
					}
				} else {
					$tmpend = 0;
				}

				$tmpi = 0;

				while($user_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$tmpi = $tmpi + 1;
					if ($tmpend > 0)
					{
						if ($tmpi > $tmpend) break;
					}
					$users_data = new UserManger();
					$users_data->set_ID($user_data['user_id']);
					$users_data->set_user_name($user_data['user_name']);
					$users_data->set_user_login_id($user_data['login_id']);
					$users_data->set_user_password($user_data['password']);
					$users_data->set_user_security_level($user_data['security_level']);
					$users_data->set_user_group($user_data['group']);
					$users_data->set_user_comp_code($user_data['compcode']);
					$users_data->set_user_kikan($user_data['user_kikan']);
					$users_data->set_start_date($user_data['start_date']);
					$users_data->set_end_date($user_data['end_date']);

					$this->users[] = $users_data;
				}
			}
		}
		else
		{
			$this->error = $stmt->errorInfo();
			$this->message = "ユーザーの読み込みに失敗しました。（条件設定エラー）".$this->error[2];
			throw new Exception($this->message);
		}
	}

	function select_data($db_link,$p_user_id)
	{
		$sql = "select * from `user` where user_id = ".$p_user_id;

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$icount = $stmt->rowCount();
			if ($icount > 0)
			{
				$user_data = $stmt->fetch(PDO::FETCH_ASSOC);
				$this->set_ID($user_data['user_id']);
				$this->set_user_name($user_data['user_name']);
				$this->set_user_login_id($user_data['login_id']);
				$this->set_user_password($user_data['password']);
				$this->set_user_security_level($user_data['security_level']);
				//xu add it on 2010-12-03 start
				$this->set_user_email($user_data['email']);
				//xu add it on 2010-12-03 end
				$this->set_user_group($user_data['group']);
				$this->set_user_comp_code($user_data['compcode']);
				$this->set_user_kikan($user_data['user_kikan']);
				if($user_data['user_kikan'] != "mukigenn")
				{
					$this->set_start_date($user_data['start_date']);
					$this->set_end_date($user_data['end_date']);
				}
			}
		}
		else
		{
			$this->error = $stmt->errorInfo();
			$this->message = "ユーザーの読み込みに失敗しました。（条件設定エラー）".$this->error[2];
			throw new Exception($this->message);
		}
	}

	function select_data2($db_link,$p_login_id)
	{
		$sql = "select * from `user` where login_id = '".$p_login_id."'";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$icount = $stmt->rowCount();
			return $icount;
		}
		else
		{
			$this->error = $stmt->errorInfo();
			$this->message = "ユーザーの読み込みに失敗しました。（条件設定エラー）".$this->error[2];
			throw new Exception($this->message);
		}
	}

	function update_data($db_link)
	{
		$sql = "update `user` ";
		$sql .= "set password = '".$this->user_password."'";
		$sql .= ",security_level = ".$this->user_security_level;
		//xu add it on 2010-12-03 start
		$sql .= ",email = '".$this->user_email."'";
		//xu add it on 2010-12-03 end
		$sql .= ",user_kikan = '".$this->user_kikan."'";
		$sql .= ",start_date = '".$this->start_date."'";
		$sql .= ",end_date = '".$this->end_date."'";
		$sql .= " where user_id = ".$this->ID;

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == false)
		{
			$this->error = $stmt->errorInfo();
			$this->message = "ユーザーデータの更新に失敗しました。（条件設定エラー）".$this->error[2];
			throw new Exception($this->message);
		}

		return $result;
	}

	function insert_data($db_link)
	{
		$okflg = $this->select_data2($db_link,$this->user_login_id);
		if($okflg)
		{
			$this->message = "このログインIDは既に使用されています。（".$this->user_login_id."）";
			throw new Exception($this->message);
		}

		$sql = "insert into `user`(`user_name`,`login_id`,`password`,`email`,`security_level`,`group`,`compcode`,`user_kikan`,`start_date`,`end_date`,`register_date`)values(";
		$sql .= "'".$this->user_name."'";
		$sql .= ","."'".$this->user_login_id."'";
		$sql .= ","."'".$this->user_password."'";
		$sql .= ","."'".$this->user_email."'";
		$sql .= ",".$this->user_security_level;
		$sql .= ",'".$this->user_group."'";
		$sql .= ",'".$this->user_comp_code."'";
		$sql .= ",'".$this->user_kikan."'";
		if(!empty($this->start_date)) $sql .= ",'".$this->start_date."'"; else $sql .= ",'0000-00-00'";
		if(!empty($this->end_date)) $sql .= ",'".$this->end_date."'"; else $sql .= ",'0000-00-00'";
		$sql .= ",now())";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == false)
		{
			$this->error = $stmt->errorInfo();
			$this->message = "ユーザーデータの新規に失敗しました。（条件設定エラー）".$this->error[2];
			throw new Exception($this->message);
		}

		return $result;
	}
}

class CmsPhotoDbCore{
	public static function findOne($dbLink,$sql){
		$stmt = $dbLink->prepare($sql);
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}

?>
