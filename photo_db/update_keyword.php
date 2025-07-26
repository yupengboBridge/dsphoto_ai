<?php
header("Content-type: text/html; charset=UTF-8");
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

	$country = $csv_content[3];
	$country0 = $csv_content[4];
	$country1 = $csv_content[5];
	$country2 = $csv_content[6];
	$country3 = $csv_content[7];
	$country4 = $csv_content[8];
	$country5 = $csv_content[9];
	
	$rep_keyword = "";
	if(!empty($country0))
	{
		if(!empty($rep_keyword))
		{
			$rep_keyword .= " ";
		}
		$rep_keyword .= $country0;
	}
	if(!empty($country1))
	{
		if(!empty($rep_keyword))
		{
			$rep_keyword .= " ";
		}
		$rep_keyword .= $country1;
	}
	if(!empty($country2))
	{
		if(!empty($rep_keyword))
		{
			$rep_keyword .= " ";
		}
		$rep_keyword .= $country2;
	}
	if(!empty($country3))
	{
		if(!empty($rep_keyword))
		{
			$rep_keyword .= " ";
		}
		$rep_keyword .= $country3;
	}
	if(!empty($country4))
	{
		if(!empty($rep_keyword))
		{
			$rep_keyword .= " ";
		}
		$rep_keyword .= $country4;
	}
	if(!empty($country5))
	{
		if(!empty($rep_keyword))
		{
			$rep_keyword .= " ";
		}
		$rep_keyword .= $country5;
	}
	
	if(empty($rep_keyword)) continue;
	
	$rep_keyword = " ".$rep_keyword." ";
	
	$sql_keyword = "select * from keyword_new where keyword_name like '% ".$country." %' order by photo_id DESC";
	$stmt_keyword = $db_link->prepare($sql_keyword);
	$result_keyword = $stmt_keyword->execute();
	if ($result_keyword == true)
	{
		while($keyword_data = $stmt_keyword->fetch(PDO::FETCH_ASSOC))
		{
			$p_photo_id = $keyword_data['photo_id'];
			$tmp_keyword = $keyword_data['keyword_name'];
			
			$search_keyword = " ".$country." ";
			$tmp_new_keyword = str_replace_once($search_keyword,$rep_keyword,$tmp_keyword);
			$update_sql = "update keyword_new set keyword_name = '".$tmp_new_keyword."'"." where photo_id = ".$p_photo_id;
			try
			{
				$stmt2 = $db_link->prepare($update_sql);
				$result2 = $stmt2->execute();
				if($result2)
				{
					$str = "updatesql->".$update_sql.">>>OK";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					flush();
				} else {
					$str = "updatesql->".$update_sql.">>>ERR";
					print $str . str_repeat(' ', 256);
					print "<br/>";
					flush();
				}
			} catch(Exception $e) {
				$msg = $e->getMessage();
				throw new Exception($msg);
			}
		}
	}
}

function str_replace_once($needle, $replace, $haystack)
{
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        // Nothing found
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}
?>