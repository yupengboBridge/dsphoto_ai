<?php
require_once('./Pager.php');
require_once('./config.php');
require_once('./lib.php');
$db_link = db_connect();

$file=$_FILES['photo'];

$now_file = $_POST['now_photo_filename_th11'];
$photo_id = $_POST['hidden_photo_id'];
$back_url = $_POST['hidden_back_url'];

$new_file = strstr($now_file,'./');
if($new_file == FALSE){
    $reg_time = time();
    $rnd = rand(1, 10000);
    $dir_no = rand(0, 9);
    $dir_no .= "/";
    $save_name = date("YmdHis", $reg_time) . $rnd;
    $upload_file = $file['name'];
    preg_match("/\.[^.]*$/i", $upload_file, $ext_tmp);
    $ext = strtolower($ext_tmp[0]);
    $new_file = './thumb11/'.$dir_no.$save_name.$ext;

    $db_save_th11_file = $upload_conf['site_url'].$new_file;

    $sql = "UPDATE photoimg set is_sp = '2',photo_filename_th11='{$db_save_th11_file}' where photo_id = '{$photo_id}'";
}else{
    $sql = "UPDATE photoimg set is_sp = '2' where photo_id = '{$photo_id}'";
}

if(move_uploaded_file($file['tmp_name'], $new_file)){
	if(resize_image_new($new_file,$new_file)){
		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		echo "<script>alert('正常に変更されました');</script>";
		echo "<script>javascript:history.back();top.document.getElementById('iframe_bottom').src='".$back_url."';</script>";
	}else{
		echo '変更に失敗しました。';
	}
}else{
	echo 'アップロードに失敗しました。';
}

function resize_image_new($filename,$new_path)
{
	$ext = exif_imagetype($filename);
	list($width, $height) = getimagesize($filename);
	$new_width = 750;
	$new_height = 470; 
	$image_p = imagecreatetruecolor($new_width, $new_height);
	switch ($ext) {
		case IMAGETYPE_JPEG:
			$image = ImageCreateFromJpeg($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $new_path);
			return true;
		case IMAGETYPE_JPEG:
			$image = ImageCreateFromJpeg($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagejpeg($image_p, $new_path);
			return true;
		case IMAGETYPE_PNG:
			$image = ImageCreateFromJpeg($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagepng($image_p, $new_path);
			return true;
		case IMAGETYPE_GIF:
			$image = ImageCreateFromGif($filename);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
			imagegif($image_p, $new_path);
			return true;
	}
	return false;
}
?>