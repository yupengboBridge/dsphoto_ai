<?php
ini_set( "display_errors", "On");
ini_set( "memory_limit", "3072M" );
date_default_timezone_set("Asia/Tokyo");

set_time_limit(1800);

// �f�[�^�[�x�[�X���ł�
//$db_host = 'localhost';
//$db_user = 'root';
//$db_password = '222222';
//$db_name = 'HBOS';

//$db_host = '10.254.2.63';
$db_host = '127.0.0.1';
$db_user = 'ximage';
$db_password = 'kCK!7wu4';
$db_name = 'ximage';
$db_charset = 'utf8';
$db_link;
$csv_dir = "./csv/";
$log_dir = "./log/";

//$csv_dir = "/var/www/html/csv/";
//$log_dir = "/var/www/html/log/";

function db_connect()
{
	global $db_host,$db_user,$db_password,$db_name,$db_charset,$db_link;
	try
	{
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
		$error_exit($msg);
	}
}

function error_exit($msg)
{
	global $log_dir;
//	// �G���[���L�����ꍇ�́A�G���[��ʂ�\�����܂�
//	global $charset;
//	global $site_name;
//
//	print "<html>\r\n";
//	print "<head>\r\n";
//	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$charset\">\r\n";
//	print "<link rel=\"stylesheet\" type=\"text/css\" href=\"default.css\">\r\n";
//	print "<title>$site_name</title>\r\n";
//	print "</head>\r\n";
//	print "<body>\r\n";
//
//	print "<div align=\"center\">\r\n";
//	print "<h1>$site_name</h1>\r\n";
//	print "<font color=\"red\">";
//	$ed = count($msg);
//	for ($i = 0 ; $i < $ed ; $i++)
//	{
//		print $msg[$i] . "<br />";
//	}
//	print "</font>\r\n";
//	print "</div>\r\n";
//	print "</body>\r\n";
//	print "</html>\r\n";
//
//	exit (-1);
	$filename = "export_files_cms".date("Ymd").".log";
	$filelog = fopen($log_dir.$filename,"a");
	for ($i = 0 ; $i < $ed ; $i++)
	{
		fwrite($filelog,$msg[$i]."\r\n");
	}

	fclose($filelog);
}

function export() {
	global $db_host,$db_user,$db_password,$db_name,$db_charset,$db_link,$csv_dir;

	$retCSVArray = array();

	try{

		$db_link = db_connect();

		$file = is_file($csv_dir."photoimg_cms.csv");
		if($db_link) {
			if($file) {unlink($csv_dir."photoimg_cms.csv");}
			$sql = "select * from photoimg where photo_mno like '%webbn%'";
			$stmt = $db_link->prepare($sql);
			// SQL�����s���܂��B
			$result = $stmt->execute();
			if($result == true) {
				while($data = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$retCSVArray[] = $data;
				}
				if(count($retCSVArray) > 0)
				{
					csvout($retCSVArray,$csv_dir."photoimg_cms.csv");
				}
			} else {
				$msg[] = $sql."SQL���̎��s�����s���܂����I";
				$error_exit($msg);
			}
		} else {
			$msg[] = "�f�[�^�x�[�X�Ɛڑ��ł��܂���B";
			$error_exit($msg);
		}
	} catch(Exception $cla) {
		$msg[] = $cla->getMessage();
		$error_exit($msg);
	}
}

function csvout($csv_line_data,$csvfilename) {
	try
	{
		touch($csvfilename);
		$fp = fopen($csvfilename, "w");

		foreach($csv_line_data as $line){
			$tmpstr = implode(",",$line);
			$tmpstr1 = str_replace("\"","",$tmpstr);
			fwrite($fp,$tmpstr1."\r\n");
		}

		fclose($fp);

	}catch (Exception $e){
		// �ُ���o�͂���
		$msg[] = $e->getMessage();
		error_exit($msg);
	}

	return 0;
}

export();
?>