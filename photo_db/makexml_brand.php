<?php
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/config.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/lib.php');

// CSV XMLファイルのPATHを設定
//$csvdir = "./csv/";
//$xmldir = "./xml/";
$csvdir = "/home2/chroot/home/xhankyu/public_html/photo_db/csv/";
$xmldir = "/home2/chroot/home/xhankyu/public_html/photo_db/xml/";

try
{
	// CSVファイルを開く
	$file = fopen($csvdir."brand_list.csv","r");

	// XMLファイルを開く
	$filexml = fopen($xmldir."brand_list.xml","w");

	// CSVファイルからフィールド名を取得する
	if (!feof($file))
	{
		// CSVの内容
		$csv_fields = (fgetcsv($file));
	} else {
		// CSVファイルを閉じる
		fclose($file);
	}

	while(!feof($file))
	{
		// 行の内容は配列にする
		$csv_content = (fgetcsv($file));

		if (!empty($csv_content[0]))
		{
			$xml_content .= "    <course course_no_header=\"".$csv_content[0]."\" web_brand_name=\"".$csv_content[1]."\" />\r\n";
		}
	}
	// CSVファイルを閉じる
	fclose($file);

	$xml_content = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n"."<root>\r\n".$xml_content;
	$xml_content .= "</root>\r\n";

	fwrite($filexml,$xml_content);

	// XMLファイルを閉じる
	fclose($filexml);
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}


?>