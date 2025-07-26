<?php
require_once('./config.php');
require_once('./lib.php');

// ＤＢへ接続します。
$db_link = db_connect();

$sql = "select * from photoimg where photo_id >= 21260 order by photo_id";

$stmt = $db_link->prepare($sql);
$result = $stmt->execute();

if ($result == true)
{
	while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$photo_filename_th2 = $image_data['photo_filename_th2'];
		$tmp = substr($photo_filename_th2,strpos($photo_filename_th2,"./"));
		$tmp1 = str_replace("th2","th3",$tmp);
		$tmp2 = str_replace("thumb2","thumb3",$tmp1);

		$size = @getimagesize($tmp);
		list($width, $height, $type, $attr) = $size;

		// 縦・横の比率を合わせて、サムネイル用の縦、横を計算します。
		$thumb_width = 200;
		$thumb_height = ($thumb_width / $width) * $height;

		// アップロードしたファイルを読み込みます。
		$ufimage = @ImageCreateFromJPEG($tmp);
		// 空のサムネイル画像を作成します。
		$thumb = @ImageCreateTrueColor($thumb_width, $thumb_height);

		// 空のサムネイル画像にアップロードしたファイルをコピーします。
		@imagecopyresampled($thumb, $ufimage, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
		// クレジットを書き込みます。
		// フォントサイズを決定します。
		$font_size = 38;
		// 画像にクレジットを書き込みます。
		$thumb = write_credit2($thumb, "SAMPLE", $font_size, $thumb_width, $thumb_height);

		// ファイルを保存します。
		$thfilename = $tmp2;
		@imagejpeg($thumb, $thfilename);

		$str = "写真ID：".$image_data['photo_id'];
		print $str . str_repeat(' ', 256);
		print "<br/>";
		flush();
	}
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
	$tx = $alpha_x1 + 22;
	$ty = 92;

	// アルファチャンネル（グレー）
	$alpha = imagecolorallocatealpha($img, 255, 255, 255, 100);

	//テキスト描画
	ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $alpha, "./sazanami-gothic.ttf", $telop_text);
	ImageTTFText($img, $fsize, $font_angle, $tx, $ty, $alpha, "./sazanami-gothic.ttf", $telop_text);

	return $img;
}
?>