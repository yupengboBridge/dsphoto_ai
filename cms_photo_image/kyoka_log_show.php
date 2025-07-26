<?php
try
{
	$file = is_file("./log/bainari_image.log");
	// ファイルのオープンはエラーの場合
	if (!$file)
	{
		$err_msg = "ログファイルは見つかりません。ご確認ください。";
		print "<script type=\"text/javascript\">";
		print "alert(\"「bainari_image.log」".$err_msg."\");";
		print "</script>";
		return;
	}

	// CSVファイルを開く
	$file = fopen("./log/bainari_image.log","r");

	//ファイルの読み込みと出力
	while (! feof($file))
	{
		$load = fgets($file, 4096);

		print $load."<br/>";
	}

	//ファイルを閉じる
	fclose ($file);
}
catch(Exception $cla)
{
	// 異常を出力する
	$msg[] = $cla->getMessage();
	error_exit($msg);
}
?>