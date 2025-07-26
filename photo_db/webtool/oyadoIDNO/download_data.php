<?php
/*=================================
	CSVをWebディレクトリ外からダウンロードさせるプログラムです
===================================*/

$FileDir = '/home/xhankyu/public_html/photo_db/webtool/oyadoIDNO/data/';
$FileNamePre = urldecode($_REQUEST['file']);

/*ファイル名の確定*/
$filename =$FileDir. $FileNamePre;
/*ダウンロード名は日付イラナイ*/
$csvFileName = 'ht_web_hk_extract_cid.csv';
//$csvFileName= '/home/xhankyu/public_html/photo_db/webtool/oyadoIDNO/data/ht_web_hk_extract_cid.csv';
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