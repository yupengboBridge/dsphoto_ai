<?php
require_once('./config.php');
require_once('./lib.php');

	// ＤＢへ接続します。
	$db_link = db_connect();

	$sql = "select * from photoimg order by photo_id";

		$stmt = $db_link->prepare($sql);
		$result = $stmt->execute();
		if ($result == true)
		{
			$p_photo_id = "";
			$p_photo_mno = "";

			while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$p_photo_id = $image_data['photo_id'];
				$p_photo_mno = $image_data['photo_mno'];
				$tmp = "_".$p_photo_mno;

				$ipos = strpos($tmp,"-");
				if ($ipos > 0)
				{
					$tmp2 = substr($tmp,$ipos);

					$new_p_mno = "00000".$tmp2;

					$update_tmp = " photo_mno = '".$new_p_mno."'";
					$update_tmp .= ",registration_account = 'admin'";
					$update_tmp .= ",registration_person = 'BUD管理者'";

					$update_sql = "UPDATE photoimg SET ".$update_tmp." WHERE photo_id = ".$p_photo_id;

					$stmt2 = $db_link->prepare($update_sql);
					$result = $stmt2->execute();
				}
				echo "updatesql->".$update_sql."<br/>";

			}

//			$insert_sql = "INSERT INTO keyword2(photo_id,keyword_name) VALUES (";
//			$insert_sql .= $image_data['photo_id'].",'".$p_keywordname."')";
//			$stmt2 = $db_link->prepare($insert_sql);
//			$result = $stmt2->execute();
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}






?>