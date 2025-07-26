<?php
require_once('./config.php');
require_once('./lib.php');

// CSVファイルのPATHを設定
$csvdir = "./csv/";
// XMLファイルのPATHを設定
$xmldir = "xml/";
//$filename = "";
$s_csv_content = "";
$s_csv_content_line = "";
$s_csv_content_line_bak1 = "";
$s_file_head = "\"course_no_header\",\"web_brand_name\"";

try
{
	$s_csv_content .= $s_file_head;
	$s_csv_content .= base64_decode("DQo=");
	$xml = simplexml_load_file($xmldir."brand_list.xml");
	foreach($xml->course as $node1)
	{
		$p_d_code = htmlentities($node1['course_no_header'], ENT_QUOTES, "utf-8");
		$p_d_name = htmlentities($node1['web_brand_name'], ENT_QUOTES, "utf-8");
		$s_csv_content_line = "\"" .$p_d_code."\"" ."," ."\"".$p_d_name."\"";
		$s_csv_content_line .= base64_decode("DQo=");
		$s_csv_content .= $s_csv_content_line;
	}

	// CSVファイルを出力する
	$file = fopen($csvdir."brand_list.csv","w");
	fwrite($file,$s_csv_content);
	fclose($file);
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
	return false;
}
?>