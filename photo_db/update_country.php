<?php
require_once('./config.php');
require_once('./lib.php');

$csvdir = "./photods_csv/";
$file_name_para = "coutry.csv";

setlocale(LC_ALL,'ja_JP.UTF-8');
// CSVファイルを開く
$file = fopen($csvdir.$file_name_para,"r");

// ＤＢへ接続します。
$db_link = db_connect();

// ファイルの内容より繰り返し一覧データを作成する
while(!feof($file))
{
	// 行の内容は配列にする
	$csv_content = fgetcsv($file,1000000,"\t");

	$country_id = $csv_content[2];
	$district_id = $csv_content[0];
	$country_name = $csv_content[4];

	$sql = "insert into country_prefecture_new(country_prefecture_id,direction_id,country_prefecture_name)values(".$country_id.",".$district_id.",'".$country_name."');";
	$stmt = $db_link->prepare($sql);
	$result = $stmt->execute();
	if ($result == true)
	{
		print $sql . str_repeat(' ', 256).">>>OK";
		print "<br/>";
		ob_flush();
		flush();
	} else {
		print $sql . str_repeat(' ', 256).">>>ERR";
		print "<br/>";
		ob_flush();
		flush();
	}
}

$ret_cp_max_id = 0;
$sql_max = "select (max(country_prefecture_id)+1) country_prefecture_id from country_prefecture_new";
$stmt = $db_link->prepare($sql_max);
$result = $stmt->execute();
if ($result == true)
{
	$registration_country = $stmt->fetch(PDO::FETCH_ASSOC);
	$ret_cp_max_id = $registration_country['country_prefecture_id'];
}

$sql = "select direction_id,country_prefecture_name from country_prefecture where direction_id in(10,11,12,13,14,15,16,17);";
$stmt = $db_link->prepare($sql);
$result = $stmt->execute();
if ($result == true)
{
	while($registration_country = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$sql_insert = "insert into country_prefecture_new(country_prefecture_id,direction_id,country_prefecture_name)values(";
		$sql_insert .= $ret_cp_max_id.",".$registration_country['direction_id'].",'".$registration_country['country_prefecture_name']."');";
		$stmt_insert = $db_link->prepare($sql_insert);
		$result_insert = $stmt_insert->execute();
		if ($result_insert == true)
		{
			$ret_cp_max_id = $ret_cp_max_id + 1;
			print $sql_insert . str_repeat(' ', 256).">>>OK";
			print "<br/>";
			ob_flush();
			flush();
		} else {
			print $sql_insert . str_repeat(' ', 256).">>>ERR";
			print "<br/>";
			ob_flush();
			flush();
		}
	}
} else {
	print $sql . str_repeat(' ', 256).">>>ERR";
	print "<br/>";
	ob_flush();
	flush();
}
?>