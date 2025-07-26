<?php
require_once('./config.php');
require_once('./lib.php');
// セッション管理をスタートします。
ini_set("session.gc_maxlifetime", "1440"); 									// セッション有効時間
session_start();
//
//// タイムゾーンを設定します。
//date_default_timezone_set('Asia/Tokyo');
// 情報を取得します。
$p_login_id = array_get_value($_POST, 'username' ,"");							// ログインID
$p_login_pw = array_get_value($_POST, 'userpwd' ,"");							// ログインパスワード
$p_action =  array_get_value($_REQUEST, 'p_action' ,"");						// アクション

// パスワードが空だった場合は、適当な値（ここでは時間）を入れておきます。
//if (empty($p_login_pw))
//{
//	$p_login_pw = time();
//}

$err = false;																// エラーが発生しているかどうか
$msg = array();																// メッセージ

if ($p_action == "login")
{
	try
	{
		// ＤＢへ接続します。
		$db_link = db_connect();

		// ユーザー情報をDBより取得します。
		// 取得するためのSQLを作成します。
		$sql = "select * from user where login_id = ? ";
		$stmt = $db_link->prepare($sql);
		$stmt->bindParam(1,$p_login_id);

		// SQLを実行します。
		$result = $stmt->execute();

		// 実行結果をチェックします。
		if ($result == true)
		{
			// 実行結果がOKの場合の処理です。
			$icount = $stmt->rowCount();
			if ($icount == 1)
			{
				// 正常にデータの取得ができたときの処理です。
				$user = $stmt->fetch(PDO::FETCH_ASSOC);

				// 入力されたパスワードをmd5でエンコードします。
				//$pw_md5 = md5($p_login_pw);
				$pw_md5 = $p_login_pw;

				// DBに格納されているパスワードはmd5形式なのでそれと比較します。
				if ($pw_md5 == $user['password'])
				{
					//期間の追加yupengbo 20090810 start
					$kikan = $user['user_kikan'];
					if($kikan == "sitei")
					{
						$start_date = $user['start_date'];
						$end_date = $user['end_date'];
						$now_date = date("Y-m-d");
						if($now_date < $start_date)
						{
							$msg[] = "このユーザーIDはまだ使用できません。ユーザーID：".$p_login_id."　開始日：".$start_date;
							$err = true;
						} else {
							if($end_date < $now_date)
							{
								$msg[] = "このユーザーIDは期限切れです。ユーザーID：".$p_login_id."　終了日：".$end_date;
								$err = true;
							}
						}
					}
					//期間の追加yupengbo 20090810 end
					
					if($err != true)
					{
						// パスワードが同じ場合は、ログイン成功です。
						// ログイン時間を更新します。
						$sql = "update user set login_date = ? where user_id = ?";
						$stmt = $db_link->prepare($sql);
						$stmt->bindValue(1, date('Y-m-d H:i:s'));
						$stmt->bindParam(2, $user['user_id']);

						// SQLを実行します。
						$result = $stmt->execute();

						// 実行結果をチェックします。
						if ($result == true)
						{
							// ログイン日時の更新が成功した場合の処理です。
							$msg[] = "ログインに成功しました。";

							// セッションにログイン情報を保存します。
							$_SESSION['login_id'] = $user['login_id'];
							$_SESSION['user_name'] = $user['user_name'];
							$_SESSION['security_level'] = $user['security_level'];
							$_SESSION['compcode'] = $user['compcode'];
							$_SESSION['group'] = $user['group'];
							$_SESSION['user_id'] = $user['user_id'];
							$_SESSION['is_credit'] = $user['is_credit'];

							//ログイン後のTOPページへリダイレクトします。
							print "<script type='text/javascript'>\r\n";
							print "var ck_id_all = 'pickup_chk';";
							print "var keys_w = 'search_where_save';";
							print "var key_c_array_ck = 'c_array_ck';";
							print "var key_syousai_content_ck = 'syousai_content_ck';";
							print "var p_hatsu_index = 'p_hatsu_index';";
							print "var classname = 'classname';";
							print "var photo_id = 'photo_id';";

							print "document.cookie = ck_id_all + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
							print "document.cookie = keys_w + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
							print "document.cookie = key_c_array_ck + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
							print "document.cookie = key_syousai_content_ck + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
							print "document.cookie = p_hatsu_index + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
							print "document.cookie = classname + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
							print "document.cookie = photo_id + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";

							print "document.location.href=\"".$after_login_page."\"";
							print "</script>";
						}
						else
						{
							// ログイン日時の更新ができなかった場合の処理です。
							$msg[] = "ログイン日時の更新ができませんでした。";
							$err = true;
						}
					}
				}
				else
				{
					// エラー情報を取得します。
					$msg[] = "ログインできませんでした。\r\nユーザーIDもしくはパスワードが違います。";
					$err = true;
				}
			}
			else
			{
				// エラー情報を取得します。
				$msg[] = "ログインできませんでした。\r\nユーザーIDもしくはパスワードが違います。";
				$err = true;
			}
		}
		else
		{
			// 実行結果がNGの場合の処理です。
			// エラー情報を取得します。
			$msg[] = "ユーザー情報の取得に失敗しました。（条件設定エラー）";
			$dberr = $stmt->errorInfo();
			$msg[] = $dberr[2];
			$err = true;
		}
	}
	catch(Exception $e)
	{
		$msg[] = "ログインに失敗しました。（DB例外）";
		$msg[] = $e->getMessage();
		$err = true;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ログイン</title>
<meta name="Keywords" content="キーワードが入ります" />
<meta name="Description" content="" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<!--CSSリンク　ここから-->
<link rel="stylesheet" href="./css/master.css" type="text/css" media="all" />
<!--CSSリンク　ここまで-->
<!--javascript ここから -->
<script type="text/javascript">
/*
 * 関数名：input_check
 * 関数説明：入力内容のチェック
 * パラメタ：無し
 * adj:「-1」：「前の画像へ」ボタンを押す；「1」：「次の画像へ」ボタンを押す
 * flg:「1」ピックアップ画面から引き続き；「2」：検索結果画面から引き続き
 * 戻り値：true/false
 */
function input_check()
{
	var usr = document.getElementById('username');
	var pwd = document.getElementById('userpwd');

	if (usr)
	{
		var tmp1 = usr.value;
		if (tmp1 == null || tmp1 == "" || tmp1.length <= 0)
		{
			alert("ログイン名を入力してください。");
			usr.focus();
			return false;
		}
	}

	if (pwd)
	{
		var tmp1 = pwd.value;
		if (tmp1 == null || tmp1 == "" || tmp1.length <= 0)
		{
			alert("パスワードを入力してください。");
			pwd.focus();
			return false;
		}
	}

	return true;
}

/*
 * 関数名：login
 * 関数説明：ユーザーの登録
 * パラメタ：無し
 * 戻り値：true/false
 */
function login()
{
	if (input_check())
	{
		document.loginfrm.submit();
	}
}

</script>
<!-- javascript ここまで -->
</head>
<body>
<form name="loginfrm" action="login.php?p_action=login" method="post">
<div id="zentai">
	<!--ヘッダーの構造は、ここから-->
	<div id="header">
		<div>
			<h1 style="height: 40px;">BUD PHOTO WEB</h1>
			<span style="position: absolute;top: 43px;left: 15px;font-size: 15px;">Ver2.2.0</span>
		</div>
	</div>
	<!--ヘッダーの構造は、ここまで-->

	<!-- メインコンテンツ　ここから -->
	<div id="contents">
		<div class="photo_detail">

		<table border="0" cellspacing="0" cellpadding="0" id="login">
			<tr>
				<td class="ttl_login">login</td>
			</tr>
			<tr>
				<td>ログイン名</td>
			</tr>
			<tr>
				<td><label>
					<input type="text" name="username" id="username" />
					</label>
				</td>
			</tr>
			<tr>
				<td>パスワード</td>
			</tr>
			<tr>
				<td><input type="password" name="userpwd" id="userpwd" /></td>
			</tr>
			<tr>
				<td class="btn"><label>
					<input type="button" name="button" id="button" value="ログイン" onclick="login();"/>
					</label>
				</td>
			</tr>
		</table>
		<div align="center"><font color="red">
			【お知らせ】<br>
			阪急様用ID、パスワードが変更になっております。詳細は、イントラネットの掲示板を参照ください。
		</div></font>
		<?php if ($err == true) { ?>
			<br />
			<input type="text" name="errormsg" id="errormsg" value="<?php echo dp($msg[0]); ?>" style="width:600px;position:relative;left:510px;font-size:10;color:red;border:0px;" readonly="readonly"/>
		<?php } ?>
	</div>
	</div>
	<!-- メインコンテンツ　ここまで -->
	<!--フッダーの構造は、ここから-->
	<p id="copyright">Copyright &copy; 2008 BUD International All rights reserved.</p>

	<div id="footer"></div>
	<!--フッダーの構造は、ここまで-->
</div>
</form>
</body>
</html>
