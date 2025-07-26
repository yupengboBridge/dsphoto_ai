<?php
require_once('./config.php');
require_once('./lib.php');

	// ＤＢへ接続します。
	$db_link = db_connect();

	//$sql = "select * from keyword order by photo_id";
	$sql = "select * from keyword_back order by photo_id";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();
			$p_photo_id = "";
			$p_keywordname = "";
			$bk_photo_id = "";

			while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$p_photo_id = $image_data['photo_id'];
				if ($p_photo_id != $bk_photo_id && !empty($bk_photo_id))
				{
					//$insert_sql = "INSERT INTO keyword2(photo_id,keyword_name) VALUES (";
					$insert_sql = "INSERT INTO keyword(photo_id,keyword_name) VALUES (";
					$insert_sql .= $bk_photo_id.",'".$p_keywordname."')";
					$stmt2 = $db_link->prepare($insert_sql);
					$result = $stmt2->execute();
					if ($result == true)
					{
						$p_keywordname = "";
					}
				} else {
					if (empty($p_keywordname))
					{
						$p_keywordname .= " ";
					}
					$p_keywordname .= $image_data['keyword_name']." ";
				}
				$bk_photo_id = $p_photo_id;
			}

			//$insert_sql = "INSERT INTO keyword2(photo_id,keyword_name) VALUES (";
			$insert_sql = "INSERT INTO keyword(photo_id,keyword_name) VALUES (";
			$insert_sql .= $image_data['photo_id'].",'".$p_keywordname."')";
			$stmt2 = $db_link->prepare($insert_sql);
			$result = $stmt2->execute();
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}






?>