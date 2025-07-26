<?php
date_default_timezone_set("Asia/Tokyo");

set_time_limit(1800);

// �f�[�^�[�x�[�X���ł�
//$db_host = '10.254.2.63';
$db_host = '127.0.0.1';
$db_user = 'ximage';
$db_password = 'kCK!7wu4';
$db_name = 'ximage';
$db_charset = 'utf8';
$db_link;
$csv_dir = "./csv/";

function db_connect()
{
  global $db_host,$db_user,$db_password,$db_name,$db_charset,$db_link;

	try {
		$is_connect = false;

		// �p�X���[�h�ȊO����̏ꍇ�̓G���[�Ƃ��܂��B
		if (empty($db_host) || empty($db_name) || empty($db_user) || empty($db_charset))
		{
			$err_message = "�f�[�^�x�[�X���ɕs��������܂��B";
			throw new Exception($err_message);
		}
		// �f�[�^�x�[�X�L�����N�^�[�Z�b�g�̃`�F�b�N�����܂��B�i�ȗ��j

		// �f�[�^�x�[�X�ɐڑ����܂��B
		$hostdb = "mysql:host=". $db_host . "; dbname=" . $db_name;
		$pdo = new PDO($hostdb, $db_user, $db_password);

		// �g�p����L�����N�^�[�Z�b�g��ݒ肵�܂��B
		//$sql = "set character SET :DBCHAR";
		$sql = "set names :DBCHAR";
		$stmt = $pdo->prepare($sql);
		$stmt->bindValue(':DBCHAR', $db_charset);
		$result = $stmt->execute();

		$is_connect = $result;

		// PDO�̃C���X�^���X��Ԃ��܂��B
		return $pdo;
	} catch(Exception $cla) {
		$msg[] = $cla->getMessage();
		error_exit($msg);
	}
}

function error_exit($msg)
{
	// �G���[���L�����ꍇ�́A�G���[��ʂ�\�����܂�&#65533;?
	global $charset;
	global $site_name;

	print "<html>\r\n";
	print "<head>\r\n";
	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">\r\n";
	print "<link rel=\"stylesheet\" type=\"text/css\" href=\"default.css\">\r\n";
	print "<title>$site_name</title>\r\n";
	print "</head>\r\n";
	print "<body>\r\n";

	print "<div align=\"center\">\r\n";
	print "<h1>$site_name</h1>\r\n";
	print "<font color=\"red\">";
	$ed = count($msg);
	for ($i = 0 ; $i < $ed ; $i++)
	{
		print $msg[$i] . "<br />";
	}
	print "</font>\r\n";
	print "</div>\r\n";
	print "</body>\r\n";
	print "</html>\r\n";

	exit (-1);
}
	
try{
	$db_link = db_connect();

	if($db_link) {
		$sql="select photo_mno,bud_photo_no from photoimg";
		$stmt = $db_link->prepare($sql);
		// SQL�����s���܂��B
		$result = $stmt->execute();
		if($result == true) {
			$i = 1;
			$str = "";
			if(is_file($csv_dir."jpg_eps_list.csv")) {unlink($csv_dir."jpg_eps_list.csv");}

			while ($data = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$field1 = $data['photo_mno'];
				$field2 = $data['bud_photo_no'];
				
				$i = $i + 1;
				if($i == 1000) {
					$handle = fopen ($csv_dir."jpg_eps_list.csv","a");
					fwrite($handle,$str);
					fclose ($handle);
					$str = $field1."\t".$field2."\r\n";
					$i = 1;
				} else {
					$str .= $field1."\t".$field2."\r\n";
				}
			}
			if($i > 1) {
					$handle = fopen ($csv_dir."jpg_eps_list.csv","a");
					fwrite($handle,$str);
					fclose ($handle);
			}
		} else {
			$msg[] = $sql."SQL���̎��s�����s���܂����I";
			error_exit($msg);
		}
	} else {
		$msg[] = "�f�[�^�x�[�X�Ɛڑ��ł��܂���B";
		error_exit($msg);
	}
} catch(Exception $cla) {
	$msg[] = $cla->getMessage();
	error_exit($msg);
}

?>