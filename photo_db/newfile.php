<?php
function test()
{
	setlocale(LC_ALL,'ja_JP.UTF-8');
	// CSVファイルを開く
	$file = fopen("./limited/ejdi1.csv","r");

	// CSVファイルからフィールド名を取得する
	if (!feof($file))
	{
		// CSVの内容
		$csv_fields = fgetcsv($file,1000000,"\t");
	} else {
		// CSVファイルを閉じる
		fclose($file);
	}

	$cnt = 0;
	// ファイルの内容より繰り返し一覧データを作成する
	while(!feof($file))
	{
		// 行の内容は配列にする
		$csv_content = fgetcsv($file,1000000,"\t");
		print_r($csv_content);
	}
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
	function select_image_keyword2($db_link,$sp_str,$sp_str_content)
	{
		$sql_where = "";
		$sql = "";
		$tmpsql2_1 = "";
		$kwd_a = array();

		if (!empty($sp_str))
		{
			// 検索内容の文字列（「or」で区切り）→配列に変換します。
			$flg1 = stripos($sp_str," or ");
			$flg2 = stripos($sp_str,"　or ");
			$flg3 = stripos($sp_str," or　");
			$flg4 = stripos($sp_str,"　or　");
			if ($flg1 || $flg2 || $flg3 || $flg4)
			{
				// 正常のSQL文は以下のようなSQL文です。
				/*
					SELECT DISTINCT photo_id FROM keyword WHERE
					keyword_name COLLATE utf8_bin LIKE '２月%' OR
					keyword_name COLLATE utf8_bin LIKE '８月%'
				*/
				if ($flg1) $kwd_a = spliti(" or ", $sp_str);
				if ($flg2) $kwd_a = spliti("　or ", $sp_str);
				if ($flg3) $kwd_a = spliti(" or　", $sp_str);
				if ($flg4) $kwd_a = spliti("　or　", $sp_str);
				$ed = count($kwd_a);
				for ($i = 0 ; $i < $ed ; $i++)
				{
					if (!empty($sql_where) && !empty($kwd_a[$i]))
					{
						 $sql_where.= " OR ";
					}
					if (!empty($kwd_a[$i]))
					{
						$sql_where.= "keyword_name LIKE '%".$kwd_a[$i]."%'";
					}
				}
				$sql = " SELECT DISTINCT photo_id FROM keyword WHERE ".$sql_where;
			} else {
				// 文字列に「-」をあるかどうかチェックする
				$tmpstr = "_".$sp_str;
				$flg = stripos($tmpstr,"\"");

				if ($flg)
				{
					// 検索内容の文字列（「 」で区切り）→配列に変換します。
					$tmp_1 = preg_replace('/(　)+?/'," ",$sp_str);
					$kwd_a = split(" ", $tmp_1);
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
						$kwd_a = split(" ", $tmp_1);
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
						$kwd_a = split(" ", $tmp_1);
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
			}
		}

		if (!empty($sp_str_content))
		{
			// 文字列に「-」をあるかどうかチェックする
			// 検索内容の文字列（「 」で区切り）→配列に変換します。
			$kwd_a = split(" ", $sp_str_content);
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
		echo $sql;
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
			$sp_p_id = "";

			while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$sp_p_id = $sp_p_id.$image_data['photo_id'].",";
			}
			$sp_p_id = substr($sp_p_id,0,strlen($sp_p_id) - 1);
			$this->sp_photo_id_str = $sp_p_id;
			$this->select_image($db_link);
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）";
			throw new Exception($this->message);
		}
		return true;
	}





	function insert_keyword_back($db_link, $pid, $kwd_str)
	{
		// エラーチェックを行います。
		if (empty($pid))
		{
			return ;
		}

		// 写真データを追加します。<写真管理番号>
		if (!empty($this->photo_mno) && strlen($this->photo_mno) > 0)
		{
			$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $this->photo_mno . "')";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（写真管理番号　処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（写真管理番号　条件設定エラー）";
				throw new Exception($this->message);
			}
		}

		// 写真データを追加します。<写真名>
		if (!empty($this->photo_name) && strlen($this->photo_name) > 0)
		{
			$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $this->photo_name . "')";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（写真名　処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（写真名　条件設定エラー）";
				throw new Exception($this->message);
			}
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
				$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
				$sql .= $pid . ",'" . $d_name . "')";
				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == true)
				{
					// 実行結果がOKの場合の処理です。
					$icount = $stmt->rowCount();
					if ($icount != 1)
					{
						$this->message = "キーワードをDBに登録できませんでした。（方面名　処理数!=1）";
						throw new Exception($this->message);
					}
				}
				else
				{
					$this->message = "キーワードをDBに登録できませんでした。（方面名　条件設定エラー）";
					throw new Exception($this->message);
				}
			}

			// 国・都道府県名をキーワードに新規する
			if (!empty($cp_name) && strlen($cp_name) > 0)
			{
				$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
				$sql .= $pid . ",'" . $cp_name . "')";
				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == true)
				{
					// 実行結果がOKの場合の処理です。
					$icount = $stmt->rowCount();
					if ($icount != 1)
					{
						$this->message = "キーワードをDBに登録できませんでした。（国・都道府県名　処理数!=1）";
						throw new Exception($this->message);
					}
				}
				else
				{
					$this->message = "キーワードをDBに登録できませんでした。（国・都道府県名　条件設定エラー）";
					throw new Exception($this->message);
				}
			}

			if (!empty($p_name) && strlen($p_name) > 0)
			{
				// 地名をキーワードに新規する
				$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
				$sql .= $pid . ",'" . $p_name . "')";
				$stmt = $db_link->prepare($sql);
				$result = $stmt->execute();
				if ($result == true)
				{
					// 実行結果がOKの場合の処理です。
					$icount = $stmt->rowCount();
					if ($icount != 1)
					{
						$this->message = "キーワードをDBに登録できませんでした。（地名　処理数!=1）";
						throw new Exception($this->message);
					}
				}
				else
				{
					$this->message = "キーワードをDBに登録できませんでした。（地名　条件設定エラー）";
					throw new Exception($this->message);
				}
			}
		}

		// 写真データを追加します。<内容（写真説明）>
		if (!empty($this->photo_explanation) && strlen($this->photo_explanation) > 0)
		{
			$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $this->photo_explanation . "')";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（内容（写真説明）　処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（内容（写真説明）　条件設定エラー）";
				throw new Exception($this->message);
			}
		}

		// 写真データを追加します。<撮影時期　2>
		$t_p_time2_name = "";
		$this->get_take_picture_time2_name($db_link,$this->take_picture_time2_id,$t_p_time2_name);
		if (!empty($t_p_time2_name) && strlen($t_p_time2_name) > 0)
		{
			$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $t_p_time2_name . "')";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（撮影時期　2　処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（撮影時期　2　条件設定エラー）";
				throw new Exception($this->message);
			}
		}

		// 写真データを追加します。<撮影時期　1>
		$t_p_time_name = "";
		$this->get_take_picture_time_name($db_link,$this->take_picture_time_id,$t_p_time_name);
		if (!empty($t_p_time_name) && strlen($t_p_time_name) > 0)
		{
			$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $t_p_time_name . "')";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（撮影時期　1　処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（撮影時期　1　条件設定エラー）";
				throw new Exception($this->message);
			}
		}

		// 写真データを追加します。<使用範囲>
		if ((int)$this->range_of_use_id == 1)
		{
			$r_name = "使用不可";
		} elseif ((int)$this->range_of_use_id == 2) {
			$r_name = "使用可";
		} elseif ((int)$this->range_of_use_id == 3) {
			$r_name = "条件有";
		} else {
			$r_name = "";
		}
		if (!empty($r_name))
		{
			$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $r_name . "')";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（使用範囲　処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（使用範囲　条件設定エラー）";
				throw new Exception($this->message);
			}
		}

		// 写真データを追加します。<付加条件：クレジット>
		if (!empty($this->additional_constraints1) && strlen($this->additional_constraints1) > 0)
		{
			$ad_c = "要クレ".$this->additional_constraints1;

			$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
			$sql .= $pid . ",'" . $ad_c . "')";
			$stmt = $db_link->prepare($sql);
			$result = $stmt->execute();
			if ($result == true)
			{
				// 実行結果がOKの場合の処理です。
				$icount = $stmt->rowCount();
				if ($icount != 1)
				{
					$this->message = "キーワードをDBに登録できませんでした。（付加条件　処理数!=1）";
					throw new Exception($this->message);
				}
			}
			else
			{
				$this->message = "キーワードをDBに登録できませんでした。（付加条件　条件設定エラー）";
				throw new Exception($this->message);
			}
		}

		if (empty($kwd_str)) return;

		// キーワード文字列（スペース区切り）→配列に変換します。
		$kwd_a = split(" ", $kwd_str);
		// 設定されているキーワードをすべてDBに登録します。
		$ed = count($kwd_a);
		for ($i = 0 ; $i < $ed ; $i++)
		{
			if (!empty($kwd_a[$i]) && strlen($kwd_a[$i]) > 0)
			{
				// 写真データを追加します。
				$sql = "INSERT INTO keyword_back (photo_id, keyword_name) VALUES ( ";
				$sql .= $pid . ",'" . $kwd_a[$i] . "')";

				$stmt = $db_link->prepare($sql);
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
	}
?>
<html>
<head>
<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8”>
<title>ＣＳＶのバッチインポート</title>
</head>
<body>
<?php
test();
?>
</body>
</html>