<?php
require_once('./config.php');
require_once('./lib.php');

date_default_timezone_set('Asia/Tokyo');

// CSVファイルのPATHを設定
$csvdir = "./photods_csv/";

$file_name_para = array_get_value($_REQUEST, 'p_filename' ,"");		// ファイル名
$startlineno = array_get_value($_REQUEST, 'start_line_no' ,"");		// 開始行数
$group_cnt = array_get_value($_REQUEST, 'group_cnt' ,"");			// グループの行数

//$file_name_para = "p_ejdi_sample.csv";
//$group_cnt = 10;
//$startlineno = 10;

if (is_file($csvdir.$file_name_para) == false)
{
	//$errmessage = $csvdir.$file_name_para."ファイルは見つかりませんでした！\r\n";
	//write_log_tofile($errmessage);
	print "<p style='color: red'>".$csvdir.$file_name_para."ファイルは見つかりませんでした！<br/></p>";
	return;
}

if (empty($group_cnt))
{
	return;
}

if ((int)$group_cnt == 0)
{
	return;
}

setlocale(LC_ALL,'ja_JP.UTF-8');
// CSVファイルを開く
$file = fopen($csvdir.$file_name_para,"r");

$cnt = 0;
$g_cnt = 0;
$tmp_cnt = 0;

$file_tmp = NULL;
$file_name = "";

$startflg = false;
// ファイルの内容より繰り返し一覧データを作成する
while(!feof($file))
{
	// 行の内容は配列にする
	$csv_content = fgetcsv($file,1000000,"\t");

	$cnt = $cnt + 1;

	$tmp_cnt = $tmp_cnt + 1;

	if (!empty($startlineno) && $startflg == false)
	{
		if((int)$startlineno > 0)
		{
			if ((int)$tmp_cnt != $startlineno)
			{
				continue;
			} else {
				$cnt = 1;
				$startflg = true;
			}
		}
	}


	if(!empty($cnt) && !empty($group_cnt))
	{
		if((int)$cnt > (int)$group_cnt)
		{
			$g_cnt = $g_cnt + 1;
			$cnt = 1;

			$ipos1 = strpos($file_name_para,".");
			if (!empty($ipos1))
			{
				if ((int)$ipos1 > 0)
				{
					$str =  $file_name."ファイルを処理しました。";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					//ob_flush();
					flush();

					$tmp2 = substr($file_name_para,0,$ipos1-1);
					$tmp3 = substr($file_name_para,$ipos1);
					$file_name = $tmp2.$g_cnt.$tmp3;

					fclose($file_tmp);
					$file_tmp = fopen($csvdir.$file_name,"w");
					if (!empty($csv_content))
					{
						fwrite($file_tmp,implode(",",$csv_content)."\r\n");
					} else {
						fwrite($file_tmp,"\r\n");
					}
				}
			}
		} else {
			if ((int)$cnt == 1)
			{
				$ipos1 = strpos($file_name_para,".");
				if (!empty($ipos1))
				{
					if ((int)$ipos1 > 0)
					{
						$tmp2 = substr($file_name_para,0,$ipos1-1);
						$tmp3 = substr($file_name_para,$ipos1);
						$file_name = $tmp2.$g_cnt.$tmp3;

						if($file_tmp == NULL)
						{
							$file_tmp = fopen($csvdir.$file_name,"w");
						}

						if (!empty($csv_content))
						{
							fwrite($file_tmp,implode(",",$csv_content)."\r\n");
						} else {
							fwrite($file_tmp,"\r\n");
						}
					}
				}
			} else {
				if (!empty($csv_content))
				{
					fwrite($file_tmp,implode(",",$csv_content)."\r\n");
				} else {
					fwrite($file_tmp,"\r\n");
				}
			}
		}
	}
}
$str =  $file_name."ファイルを処理しました。";
print $str . str_repeat(' ', 256);
print "<br/>";
//ob_flush();
flush();
fclose($file_tmp);
?>