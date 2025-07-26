<?php
/*=================================
	CSVをWebディレクトリ外からダウンロードさせるプログラムです
===================================*/
ini_set("memory_limit","2048M");

$BaseDir = '/home/xhankyu/public_html/photo_db/webtool/PriceCheck/outdata/';

$FileNamePre = urldecode($_REQUEST['file']);

/*ファイル名の確定*/
$filename = $BaseDir . $FileNamePre;
/*ダウンロード名は日付イラナイ*/
if($_REQUEST['type']=='i'){
	$csvFileName = 'price_check_download_i.csv';
}
else{
	$csvFileName = 'price_check_download_d.csv';	
}
/*ファイルが存在しなかったらゴメン*/
if(!is_file($filename)){
echo <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
該当ファイルは生成されていません
$filename
</body>
</html>

EOD;
	exit;
}

header ("Content-Disposition: attachment; filename=$csvFileName");
header ("Content-type: application/x-csv; charset=UTF-8");
readfile ($filename);
?>