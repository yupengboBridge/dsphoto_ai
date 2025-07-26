<html>
<head>
<style type="text/css">
table.imagetable {
    font-family: verdana,arial,sans-serif;
    font-size:11px;
    color:#333333;
    border-width: 1px;
    border-color: #999999;
    border-collapse: collapse;
}
table.imagetable th {
    background:#b5cfd2 url('cell-blue.jpg');
    border-width: 1px;
    padding: 8px;
    border-style: solid;
    border-color: #999999;
}
table.imagetable td {
    background:#dcddc0 url('cell-grey.jpg');
    border-width: 1px;
    padding: 8px;
    border-style: solid;
    border-color: #999999;
}
</style>
</head>
<body>
<form action="" method="post"
      enctype="multipart/form-data">
    <label for="file">ファイル:</label>
    <input type="file" name="up_load_excel"/>
    <input type="submit" name="submit" value="実行"/>
</form>
<div style="display: block; color: red; font-size: 14px">
	<a href="template.xlsx" style="font-size: 18px">ダウンロード</a>
</div>
</body>
</html>
<?php
require_once('./config.php');
require_once('./lib.php');
include "./PHPExcel-1.8/Classes/PHPExcel/IOFactory.php";
global $array;
$array = array();

$file_name = $_FILES["up_load_excel"]["name"];
if ($file_name == '') {
    exit;
}
$file_temp_name = $_FILES['up_load_excel']['tmp_name'];
$file_ext = substr(strrchr($file_name, '.'), 1);

if ($file_ext != 'xlsx') {
    echo "拡張子がxlsxのExcelファイルをアップロードしてください";
    exit;
}

if (!empty($file_name)) {

    if (!is_dir("./upload_excel/")) {
        mkdir("./upload_excel/");
    }

    $path = './upload_excel/' . $file_name;

    if (is_uploaded_file($file_temp_name)) {

        if (move_uploaded_file($file_temp_name, $path)) {

            // echo "上传文件成功：" . $file_name;

            read_excel($path);

        } else {

            echo "アップロードに失敗しました。ネットワークを確認してください";
        }

    } else {

        echo "ファイル名" . $file_name . "違法";

    }
}
// 上传成功后读取excel数据
function read_excel($file)
{
	$error_log = '';
	$db_link = db_connect();
    date_default_timezone_set('PRC');
    // 读取excel文件
    $success_num = 0;
    try {
        $inputFileType = PHPExcel_IOFactory::identify($file);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objReader->load($file);
    } catch (Exception $e) {

    }
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    for ($row = 2; $row <= $highestRow; $row++) {
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
        for ($i = 0; $i < count($rowData); $i++) {
        	$error_log = '';
        	$photo_mno = $rowData[$i][0];
        	$photo_name = $rowData[$i][1];
        	$bud_photo_no = $rowData[$i][2];
        	$dto = gmdate('Y-m-d',intval(($rowData[$i][3] - 25569) * 3600 * 24));
        	# $dto = PHPExcel_Shared_Date::ExcelToPHP($rowData[$i][3]);
        	$kikan = $rowData[$i][4];
        	$content_borrowing_ahead = $rowData[$i][5];
        	$customer_section = $rowData[$i][6];
        	$customer_name = $rowData[$i][7];
        	if($photo_mno == ''){
        		$error_log = 'テンプレートをインポート写真管理番号空です';
        	}else{
        		$mno_sql = "SELECT photo_mno FROM photoimg WHERE photo_mno = '$photo_mno'";
        		$stmt = $db_link->prepare($mno_sql);
				$result = $stmt->execute();
        		$icount = $stmt->rowCount();
        		if ($result == true) {
        			if ($icount == 1) {
        				# 开始修改
        				$error_log = '改訂';
        				if ($photo_name != null){
        					$sql = "photo_name = '$photo_name'";
        					if(update_line($photo_mno,$sql) == '0'){
        						$error_log = $error_log . ' 被写体の名称';
        					}
        				}
        				if ($bud_photo_no != null){
        					$sql = "bud_photo_no = '$bud_photo_no'";
        					if(update_line($photo_mno,$sql) == '0'){
        						$error_log = $error_log . ' BUD番号';
        					}
        				}
        				if ($dto != null){
        					$sql = "dto = '$dto'";
        					if(update_line($photo_mno,$sql) == '0'){
        						$error_log = $error_log . ' 掲載終了日';
        					}
        				}
        				if ($kikan != null){
        					$sql = "kikan = '$kikan'";
        					if(update_line($photo_mno,$sql) == '0'){
        						$error_log = $error_log . ' 期間';
        					}
        				}
        				if ($content_borrowing_ahead != null){
        					$sql = "content_borrowing_ahead = '$content_borrowing_ahead'";
        					if(update_line($photo_mno,$sql) == '0'){
        						$error_log = $error_log . ' 写真入手元';
        					}
        				}
        				$error_log = $error_log . '不合格';
        				if ($error_log == '改訂不合格'){
							$error_log = '修改成功';
        				}
        				if ($customer_section != null){
        					$sql = "customer_section = '$customer_section'";
        					if(update_line($photo_mno,$sql) == '0'){
        						$error_log = 'お客様情報1（部署名）';
        					}
        				}
        				if ($customer_name != null){
        					$sql = "customer_name = '$customer_name'";
        					if(update_line($photo_mno,$sql) == '0'){
        						$error_log = 'お客様情報2（名前）';
        					}
        				}

        				# echo $all_sql;
        				# 结束修改
        				$error_log = '修改成功';
        				$success_num += 1;
        			}else{
        				if ($icount == 0){
        					$error_log = '写真管理番号存在しません';
        				}else if ($icount > 1){
        					$error_log = '写真管理番号複数あります';
        				}
        			}
        		}else{
        					$error_log = 'データベースクエリが失敗しました';
        		}
        	}
        	if ($error_log != '修改成功'){
					$one_list = array(
        			"serialnumber"=> $row,
				    "mno" => $photo_mno,
				    "error" => $error_log);
					$array[] = $one_list;
        		}
        }
    }
    $all_number = $row - 2;
    $fail = $all_number - $success_num;
    echo '更新が完了しました，更新の総数：'.$all_number.'量；'.'成功数'.$success_num.'量；'.'失敗の数'.$fail.'量；';
    if ($fail != 0){
    	echo "<br/>";
    	echo '失败详情:';
	    echo "<table class='imagetable'>";
		echo "<tr>";
		echo "<td width='270px'>".'テンプレートエラー行をインポートする'."</td>";
		echo "<td width='270px'>".'写真管理番号'."</td>";
		echo "<td width='270px'>".'エラーメッセージ'."</td>";
		echo "</tr>";
		echo "</table>";
	    echo "<div style='height:500px; overflow:scroll;'>";
		echo "<table class='imagetable'>";
		foreach($array as $line)
		{
		echo "<tr >";
			foreach($line as $l)
			{
				echo "<td width='270px'>".$l."</td>";
			}
		echo "</tr>";
		}
		echo "</table>";
	    echo "</div>";
	}
}
function update_line($photo_mno,$sql){
	$db_link = db_connect();
	$sql_start = "UPDATE photoimg SET ";
	$sql_finish = " WHERE photo_mno = '$photo_mno'";
	$sql = $sql_start.$sql.$sql_finish;
	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();
	if ($result == true) {
		return '1';
	}else{
		return '0';
	}
}
?>
</body>
</html>