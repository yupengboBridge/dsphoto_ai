<?php
// �f�[�^�[�x�[�X���ł�
$db_host = '10.254.2.63';
//$db_host = 'localhost';
$db_user = 'ximage';
//$db_user = 'root';
$db_password = 'kCK!7wu4';
//$db_password = '222222';
$db_name = 'ximage';
//$db_name = 'photo';
$db_charset = 'utf8';
$db_link;

date_default_timezone_set('Asia/Tokyo');

try
{
	// �c�a�֐ڑ����܂��B
	$db_link = db_connect();
	$p_photo_mno = '00021-ESP09-07717.jpg';
	$sql = "select * from photoimg where photo_mno='".$p_photo_mno."'";

	$stmt = $db_link->prepare($sql);
	// SQL�����s���܂��B
	$result = $stmt->execute();

	// ���s���ʂ��`�F�b�N���܂��B
	if ($result == true)
	{
		// ���s���ʂ�OK�̏ꍇ�̏����ł��B
		$icount = $stmt->rowCount();
		echo "aa>>".$icount;
		while($img = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			echo "aa".$img["bud_photo_no"]."<br/>";
			echo "bb".$img["publishing_situation_id"]."<br/>";
		}
		
	}
	else
	{
		echo "select error>>";
	}
}
catch(Exception $e)
{
	echo "Exception error>>";
}

function db_connect()
{
	global $db_host, $db_name, $db_user, $db_password, $db_charset, $is_connect, $db_link;

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
}
?>