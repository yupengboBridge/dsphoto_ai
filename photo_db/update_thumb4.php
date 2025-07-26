<?php
require_once('./config.php');
require_once('./lib.php');

// ＤＢへ接続します。
$db_link = db_connect();

$sql = "select * from photoimg where publishing_situation_id = 2 and photo_id > 30435 order by photo_id ASC";

$stmt = $db_link->prepare($sql);
$result = $stmt->execute();
if ($result == true)
{
	$p_photo_id = "";

	while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$p_photo_id = $image_data['photo_id'];

		$sql_exists = "SELECT COUNT(*) cnt FROM photo_imgdata WHERE photo_id = ".$p_photo_id;
		// SQL文法のチェック
		$stmt1 = $db_link->prepare($sql_exists);
		$result1 = $stmt1->execute();
		// 実行結果をチェックします。
		if ($result1 == true)
		{
			$pcnt = $stmt1->fetch(PDO::FETCH_ASSOC);
			$tmpcnt = $pcnt['cnt'];

			//既に存在の場合に更新する
			if ((int)$tmpcnt > 0)
			{
				$update_sql = "UPDATE photo_imgdata SET image5 = ? WHERE photo_id = ".$p_photo_id;

				$photo_filename_th4 = $image_data['photo_filename_th4'];
				$tmp = substr($photo_filename_th4,strpos($photo_filename_th4,"./"));
				$b_image5 = file_get_contents($tmp);

				try
				{
					$stmt2 = $db_link->prepare($update_sql);
					$stmt2->bindValue(1, $b_image5, PDO::PARAM_LOB);
					$result2 = $stmt2->execute();
					if($result2)
					{
						$str = "updatesql->".$update_sql.">>>OK";
						print $str . str_repeat(' ', 256);
						print "<br/>";
						flush();
					} else {
						$str = "updatesql->".$update_sql.">>>ERR";
						print $str . str_repeat(' ', 256);
						print "<br/>";
						flush();
					}
				} catch(Exception $e) {
					// 例外をスローします。
					$msg = $e->getMessage();
					throw new Exception($msg);
				}
			} else {
				// 画像をバイナリをなる
				$tmp = substr($image_data['photo_filename'],strpos($image_data['photo_filename'],"./"));
				//echo $tmp;
				$b_image1 = file_get_contents($tmp);

				$tmp = substr($image_data['photo_filename_th1'],strpos($image_data['photo_filename_th1'],"./"));
				//echo $tmp;
				$b_image2 = file_get_contents($tmp);

				$tmp = substr($image_data['photo_filename_th2'],strpos($image_data['photo_filename_th2'],"./"));
				//echo $tmp;
				$b_image3 = file_get_contents($tmp);

				$tmp = substr($image_data['photo_filename_th3'],strpos($image_data['photo_filename_th3'],"./"));
				//echo $tmp;
				$b_image4 = file_get_contents($tmp);

				$tmp = substr($image_data['photo_filename_th4'],strpos($image_data['photo_filename_th4'],"./"));
				//echo $tmp;
				$b_image5 = file_get_contents($tmp);

				// SQL文の作成
				$sql = "INSERT INTO photo_imgdata (  photo_id,
													 image1,
													 image2,
													 image3,
													 image4,
													 image5
									 	 		  ) VALUES ($p_photo_id,?,?,?,?,?)";
				// SQL文法のチェック
				$stmt3 = $db_link->prepare($sql);
				// パラメータの設定
				$stmt3->bindValue(1, $b_image1, PDO::PARAM_LOB);
				$stmt3->bindValue(2, $b_image2, PDO::PARAM_LOB);
				$stmt3->bindValue(3, $b_image3, PDO::PARAM_LOB);
				$stmt3->bindValue(4, $b_image4, PDO::PARAM_LOB);
				$stmt3->bindValue(5, $b_image5, PDO::PARAM_LOB);

				// トランザクションを開始します。（オートコミットがオフになります。）
				//$db_link->beginTransaction();
				try
				{
					//$result3 = $stmt3->execute();
					//if ($result3 == true)
					//{
						// コミットします。
					//	$db_link->commit();
						$str = "insertsql->".$sql.">>>OK";
						print $str . str_repeat(' ', 256);
						print "<br/>";
						flush();
					//} else {
						// ロールバックします。
					//	$db_link->rollBack();
						// 例外をスローします。
					//	$str = "insertsql->".$sql.">>>ERR";
					//	print $str . str_repeat(' ', 256);
					//	print "<br/>";
					//	flush();
					//}
				} catch(Exception $e) {
					// 例外をスローします。
					$msg = $e->getMessage();
					throw new Exception($msg);
				}
			}
		}
	}
}
else
{
	$err = $stmt->errorInfo();
	$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
	throw new Exception($this->message);
}
?>