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
$s_csv_content_line_bak2 = "";
$s_csv_content_line_bak3 = "";
$s_file_head = "\"departure_place\",\"web_city_name\",\"destination_code\",\"destination_name\",\"country_code\",\"country_name\",\"city_code\",\"city_name\"";

try
{
	$s_csv_content .= $s_file_head;
	$s_csv_content .= base64_decode("DQo=");
	$xml = simplexml_load_file($xmldir."air_destination_list.xml");
	foreach($xml->departure as $node1)
	{
		$p_d_code = htmlentities($node1['place_departure_code'], ENT_QUOTES, "utf-8");
		$p_d_name = htmlentities($node1['place_departure_name'], ENT_QUOTES, "utf-8");
		$s_csv_content_line = "\"" .$p_d_code."\"" ."," ."\"".$p_d_name."\"";

		$node2 = $node1->district;
		if (empty($node2))
		{
			$s_csv_content_line .= base64_decode("DQo=");
			$s_csv_content .= $s_csv_content_line;
		} else {
			$s_csv_content_line_bak1 = $s_csv_content_line;
			foreach($node1->district as $node2)
			{
				$d_code = htmlentities($node2['district_code'], ENT_QUOTES, "utf-8");
				$d_name = htmlentities($node2['district_name'], ENT_QUOTES, "utf-8");
				$s_csv_content_line = $s_csv_content_line_bak1.",\"" .$d_code."\"" ."," ."\"".$d_name."\"";
				$node3 = $node2->country;
				if (empty($node3))
				{
					$s_csv_content_line .= base64_decode("DQo=");
					$s_csv_content .= $s_csv_content_line;
				} else {
					$s_csv_content_line_bak2 = $s_csv_content_line;
					foreach($node2->country as $node3)
					{
						$ct_code = htmlentities($node3['country_code'], ENT_QUOTES, "utf-8");
						$ct_name = htmlentities($node3['country_name'], ENT_QUOTES, "utf-8");
						$s_csv_content_line = $s_csv_content_line_bak2.",\"" .$ct_code."\"" ."," ."\"".$ct_name."\"";
						$node4 = $node3->city;
						if (empty($node4))
						{
							$s_csv_content_line .= base64_decode("DQo=");
							$s_csv_content .= $s_csv_content_line;
						} else {
							$s_csv_content_line_bak3 = $s_csv_content_line;
							foreach($node3->city as $node4)
							{
								$c_code = htmlentities($node4['city_code'], ENT_QUOTES, "utf-8");
								$c_name = htmlentities($node4['city_name'], ENT_QUOTES, "utf-8");
								$s_csv_content_line = $s_csv_content_line_bak3.",\"" .$c_code."\"" ."," ."\"".$c_name."\"";
								$s_csv_content_line .= base64_decode("DQo=");
								$s_csv_content .= $s_csv_content_line;
							}
						}
					}
				}
			}
		}
	}

	// CSVファイルを出力する
	$file = fopen($csvdir."air_destination_list.csv","w");
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