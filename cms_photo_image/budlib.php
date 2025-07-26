<?php
class GetImages
{
	var $message;										// メッセージ
	var $error;											// エラー
	var $login_id;										// ログインID（独占使用チェック用）

	// 画像取得データ
	var $images;										// イメージのインスタンス保存用（配列）	[0]：１番目の画像,[1]：２番目の画像
	var $get_result;									// 取得結果（配列）					[0]：１番目の画像,[1]：２番目の画像
	var $get_message;									// 取得メッセージ（配列）				[0]：１番目の画像,[1]：２番目の画像
	var $count;											// 取得数

	// 検索条件
	var $sp_photo_mno;									// 画像番号
	var $check_term;									// 期間をチェックするかどうか

	// データーベース情報
	private $db_host;									// ホスト
	private $db_user;									// ユーザー
	private $db_password;								// パスワード
	private $db_name;									// データベース名
	private $db_charset;								// DBのキャラクターセット
	private $db_link;									// データーベースへのリンク

	function GetImages($lid)
	{
		$this->login_id = $lid;							// ログインID（独占使用チェック用）
		$this->message = "";							// メッセージ
		$this->error = false;							// エラー

		// 条件を初期化します。
		$this->init_condition();

		// 画像取得データを初期化します。
		$this->init_imgdata();

		// タイムゾーンを設定します。
		date_default_timezone_set('Asia/Tokyo');
	}

	private function init_condition()
	{
		// データーベース情報です
		$this->db_host = 'localhost';					// ホスト
		$this->db_user = 'photo';						// ユーザー
		$this->db_password = 'photo123';				// パスワード
		$this->db_name = 'photo';						// データベース名
		$this->db_charset = 'utf8';						// DBのキャラクターセット

		$this->db_connect();							// データベースへのリンク
	}

	private function init_imgdata()
	{
		// 画像取得データを初期化します。
		$this->images = array();						// イメージのインスタンス保存用（配列）	[0]：１番目の画像,[1]：２番目の画像
		$this->get_result = array();					// 取得結果（配列）					[0]：１番目の画像,[1]：２番目の画像
		$this->get_message = array();					// 取得メッセージ（配列）				[0]：１番目の画像,[1]：２番目の画像
		$this->count = 0;
	}

	private function set_sql()
	{
		$sql = "select * from photoimg ";
		$sql .= " left join publishing_situation on photoimg.publishing_situation_id = publishing_situation.publishing_situation_id ";
		$sql .= " left join registration_division on photoimg.registration_division_id = registration_division.registration_division_id ";
		$sql .= " left join take_picture_time on photoimg.take_picture_time_id = take_picture_time.take_picture_time_id ";
		$sql .= " left join take_picture_time2 on photoimg.take_picture_time2_id = take_picture_time2.take_picture_time2_id ";
		$sql .= " left join borrowing_ahead on photoimg.borrowing_ahead_id = borrowing_ahead.borrowing_ahead_id ";
		$sql .= " left join range_of_use on photoimg.range_of_use_id = range_of_use.range_of_use_id ";
		$sql .= " where photo_mno=?";

		return $sql;
	}

	private function db_connect()
	{
		try
		{
			// データベースに接続します。
			$hostdb = "mysql:host=". $this->db_host . "; dbname=" . $this->db_name;
			$this->db_link = new PDO($hostdb, $this->db_user, $this->db_password);

			// 使用するキャラクターセットを設定します。
			$sql = "set character set ?";
			$stmt = $this->db_link->prepare($sql);
			$stmt->bindValue(1, $this->db_charset);
			$result = $stmt->execute();

			if ($result == false)
			{
				$this->message = "キャラクターセットの設定が出来ませんでした。";
				throw new Exception($this->message);
			}
		}
		catch(Exception $e)
		{
			// エラーをセットし、
			$this->error = true;

			// データベースを切断します。
			$this->db_link = null;

			// 例外をスローします。
			$msg = $e->getMessage();
			throw new Exception($msg);
		}
	}

	function increment_disp_counter($pid)
	{
		// 取得したイメージが無ければ、そのまま戻ります。
		if (empty($pid) || !is_numeric($pid))
		{
			return;
		}

		$sql = "select disp_counter_id from disp_counter where photo_id=?";

		// 条件を設定します。
		$stmt = $this->db_link->prepare($sql);
		$stmt->bindParam(1, $pid);

		// 今日の日付をセットします。
		$today = date('Y-m-d');

		// DBからデータを取得します。
		$result = $stmt->execute();
		if ($result == true)
		{
			$disp_counter = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($disp_counter != false && !empty($disp_counter['disp_counter_id']))
			{
				// カウンター更新用のSQLを作成します。
				$sql = "update disp_counter set counter=counter+1 where photo_id=" . $pid . " and disp_date='" . $today . "'";
				$stmt = $this->db_link->prepare($sql);

				// カウンターを更新します。
				$result = $stmt->execute();
				if ($result == false)
				{
					$err = $stmt->errorInfo();
					$this->message = "表示カウンターの更新に失敗しました。（条件設定エラー）[" . $err . "]";
					throw new Exception($this->message);
				}
							}
			else
			{
				// 新規追加用のSQLを作成します。
				$sql = "insert into disp_counter (photo_id, disp_date, counter) values (" . $pid . ", " . "'" . $today . "', 1)";
				$stmt = $this->db_link->prepare($sql);

				// DBに新規追加します。
				$result = $stmt->execute();
				if ($result == false)
				{
					$err = $stmt->errorInfo();
					$this->message = "表示カウンターの新規追加に失敗しました。（条件設定エラー）[" . $err . "]";
					throw new Exception($this->message);
				}
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "表示カウンターの取得に失敗しました。（条件設定エラー）[" . $err . "]";
			throw new Exception($this->message);
		}
	}

	function get_image($sp_pmno, $chk_term)
	{
		// 画像番号のチェックをします。
		if (!empty($sp_pmno))
		{
			// 空でなければ条件を設定します。
			$this->sp_photo_mno = $sp_pmno;
		}
		else
		{
			// 空の場合はエラーをセットし、例外をスローします。
			$this->error = true;
			$this->message = "取得する画像番号が空です。";
			throw new Exception($this->message);
		}

		// 期間チェックをするかどうかのチェックをします。
		if ($chk_term == false)
		{
			$this->check_term = false;
		}
		else
		{
			$this->check_term = true;
		}

		// データベースへの接続をチェックします。
		if ($this->db_link == null)
		{
			// 未接続の場合は、DBへ接続します。
			$this->db_connect();
		}

		// SQLをセットします。
		$sql = $this->set_sql();

		// 条件を設定します。
		$stmt = $this->db_link->prepare($sql);
		$stmt->bindParam(1, $this->sp_photo_mno);

		// 画像取得データを初期化します。
		$this->init_imgdata();

		// DBからデータを取得します。
		$result = $stmt->execute();
		if ($result == true)
		{
			// 処理数を取得します。
			$icount = $stmt->rowCount();

			// 選択されたデータ数が１かどうかチェックします。
			if ($icount >= 0)
			{
				while($image_data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					// メッセージを初期化します。
					$msg = array();

					// 掲載期間をチェックする必要があるかどうかをチェックします。
					if ($this->check_term == true)
					{
						// 掲載期間をチェックする必要がある場合です。
						// 掲載期間を取得します。
						$img_from = $image_data['dfrom'];

						// 掲載期間（From,To）を補正します。
						//   空の場合もしくは初期値の場合は今日の日付をセットします。
						$today = date("Y-m-d");
						if (empty($img_from) || $img_from == "0000-00-00")
						{
							$img_from = $today;
						}

						$img_to = $image_data['dto'];
						if (empty($img_to) || $img_to == "0000-00-00")
						{
							$img_to = $today;
						}

						// 本日の日付が掲載期間のFrom－Toの間に入っているかチェックします。
						if ($today < $img_from)
						{
							// 掲載日前
							$msg[] = "この画像はまだ掲載できません。（掲載日前）";
						}
						else if ($today > $img_to)
						{
							// 掲載日後
							$msg[] = "掲載期間が過ぎています。";
						}
					}

					// 掲載状況をチェックします。
					// 掲載状況を取得します。
					$psid = $image_data['publishing_situation_id'];
					if ($psid == 0)
					{
						$msg[] = "申請中です。";
					}
					else if ($psid == 1)
					{
					}
					else if ($psid == 2)
					{
						$msg[] = "掲載不許可です。";
					}
					else if ($psid == 3)
					{
						$msg[] = "掲載期間外です。";
					}
					else
					{
						$msg[] = "掲載状況に異常な値がセットされています。";
					}

					// 独占使用をチェックします。
					// 独占使用を取得します。
					$monouse = $image_data['monopoly_use'];
					if ($monouse == true && !empty($this->login_id))
					{
						// 使用できるアカウントを取得します。
						$raccount = $image_data['registration_account'];
						if ($this->login_id != $raccount)
						{
							$msg[] = "アカウント：" . $raccount . "様の独占画像のため使用できません。";
						}
					}

					// 取得結果とメッセージをセットします。
					if (count($msg) == 0)
					{
						// 正常に取得できた場合です。
						$this->get_result[] = true;
						$this->get_message[] = "正常に取得できました。";
						// 画像データをセットします。
						$photo_idata = new PhotoImageDataAll();
						$photo_idata->set_data($image_data);
						$this->images[] = $photo_idata;
						// 表示カウンターを＋１します。
						$this->increment_disp_counter($photo_idata->photo_id);
					}
					else
					{
						// チェックでエラーがあった場合です。
						$this->get_result[] = false;
						$this->get_message[] = join(",", $msg);
					}

					// 取得数を＋１します。
					$this->count++;
				}
			}
		}
		else
		{
			$err = $stmt->errorInfo();
			$this->message = "画像の読み込みに失敗しました。（条件設定エラー）[" . $err . "]";
			throw new Exception($this->message);
		}

		return $this->images;
	}
}
?>