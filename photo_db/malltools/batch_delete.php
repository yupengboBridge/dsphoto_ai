<?php
require_once ('../config.php');
require_once ('../lib.php');
require_once ('./mall_image_batch.php');

try{//提交保存
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$csvList = [];
		$db_link = db_connect();
		$csvFile = './upload/delete.csv';
		$pi = new PhotoImageDB();

		$upFiles = $_FILES['csv_file']['tmp_name'];
		// 检查文件类型是否为CSV
		$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($fileInfo, $upFiles);
		finfo_close($fileInfo);
		if ($mimeType !== 'text/csv' && $mimeType !== 'text/plain') {
			throw new Exception('アップロードされたファイルはCSV形式ではありません.');
		}

        if (!isset($_FILES['csv_file']['tmp_name']) || empty($_FILES['csv_file']['tmp_name'])) {
            throw new Exception('CSVファイルがアップロードされていません.');
        }

        if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $csvFile)) {
            throw new Exception('CSVファイルのアップロードに失敗しました.');
        }


		if ($mimeType !== 'text/csv' && $mimeType !== 'text/plain') {
			throw new Exception('上传的文件不是CSV格式.');
		}

		$handle = fopen($csvFile, 'rb');
        if (!$handle) {
            throw new Exception('CSVファイルを開くのに失敗しました.');
        }
		$start = 0;
		while (!feof($handle)) {
			$buffer = rtrim(fgets($handle));	//日本語ファイルはfgetcsv使うのやめておく
			$buffer = mb_convert_encoding($buffer, 'UTF-8', 'Shift_JIS'); //编码格式转换
			$line = explode(",", $buffer);
			if ($start == 0) {
				$start += 1;
			} else {
				if(count($line) === 1){
					$img = get_PhotoByBUD($db_link,$line[0]);
					$pi->delete_data($db_link,$img['photo_id']);
					array_push($csvList, $line[0]);
				}
			}
		}
		fclose($handle);
		echo json_encode( ['status'=>'success','msg'=> $csvList]);
	}
}catch (Exception $e){
	echo json_encode( ['status'=>'failed','msg'=>$e->getMessage()]);
}
