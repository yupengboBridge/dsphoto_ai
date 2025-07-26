<?php
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/config.php');
require_once('/home2/chroot/home/xhankyu/public_html/photo_db/lib.php');

// CSVファイルのPATHを設定
$csvdir = "./csv/";
// XMLファイルのPATHを設定
$xmldir = "./xml/";
try
{
	// CSVファイルを開く
	$file = fopen($csvdir."ab_destination_list.csv","r");

	// XMLファイルを開く
	$filexml = fopen($xmldir."ab_destination_list.xml","w");

	// CSVファイルからフィールド名を取得する
	if (!feof($file))
	{
		// CSVの内容
		$csv_fields = (fgetcsv($file));
	} else {
		// CSVファイルを閉じる
		fclose($file);
	}

	$xml_place_code = "";
	$xml_departure_code = "";
	$xml_country_code = "";
	$xml_content = "";
	$j = 0;
	$k = 0;
	$flg_country = false;
	$sub_element = true;
	while(!feof($file))
	{
		// 行の内容は配列にする
		$csv_content = (fgetcsv($file));
		if (!empty($csv_content[0]) && $xml_place_code == $csv_content[0]
		    && !empty($csv_content[2]) && $xml_departure_code == $csv_content[2]
		    && !empty($csv_content[4]) && $xml_country_code != $csv_content[4])
	    {
	    	if (!empty($xml_content)) $xml_content .= "            </country>\r\n";
	    } elseif (!empty($csv_content[0]) && $xml_place_code == $csv_content[0]
	              && !empty($csv_content[2]) && $xml_departure_code != $csv_content[2] && $flg_country)
        {
			if (!empty($xml_content)) $xml_content .= "            </country>\r\n";
			if (!empty($xml_content)) $xml_content .= "        </district>\r\n";
        } elseif (!empty($csv_content[0]) && $xml_place_code == $csv_content[0]
                  && !empty($csv_content[2]) && $xml_departure_code != $csv_content[2]
                  && empty($csv_content[4]) && $flg_country == false)
        {
			if (!empty($xml_content)) $xml_content .= "        </district>\r\n";
        } elseif (!empty($csv_content[0]) && $xml_place_code != $csv_content[0] && $sub_element)
        {
			if (!empty($xml_content)) $xml_content .= "            </country>\r\n";
			if (!empty($xml_content)) $xml_content .= "        </district>\r\n";
			if (!empty($xml_content)) $xml_content .= "    </departure>\r\n";
		} elseif (!empty($csv_content[0]) && $xml_place_code != $csv_content[0] && $sub_element == false)
        {
			if (!empty($xml_content)) $xml_content .= "    </departure>\r\n";
        }

		$flg_place = false;
		if (!empty($csv_content[0]) && $xml_place_code != $csv_content[0])
		{
			$xml_content .= "    <departure place_departure_code=\"".$csv_content[0]."\" place_departure_name=\"".$csv_content[1]."\">\r\n";
			$xml_place_code = $csv_content[0];
			$flg_place = true;
		}

		$flg_district = false;
		if (!empty($csv_content[2]) && $xml_departure_code != $csv_content[2])
		{
			$j = $j + 1;
			$xml_content .= "        <district district_id=\"".$j."\" district_code=\"".$csv_content[2]."\" district_name=\"".$csv_content[3]."\">\r\n";
			$xml_departure_code = $csv_content[2];
			$flg_district = true;
		} elseif (!empty($csv_content[2]) && $xml_departure_code == $csv_content[2] && $flg_place) {
			$j = $j + 1;
			$xml_content .= "        <district district_id=\"".$j."\" district_code=\"".$csv_content[2]."\" district_name=\"".$csv_content[3]."\">\r\n";
			$xml_departure_code = $csv_content[2];
			$flg_district = true;
		}

		if (!empty($csv_content[4]) && $xml_country_code != $csv_content[4])
		{
			$k = $k + 1;
			$xml_content .= "            <country country_id=\"".$k."\" country_code=\"".$csv_content[4]."\" country_name=\"".$csv_content[5]."\">\r\n";
			$xml_country_code = $csv_content[4];
			$flg_country = true;
		} elseif (!empty($csv_content[4]) && $xml_country_code == $csv_content[4] && $flg_district)
        {
			$k = $k + 1;
			$xml_content .= "            <country country_id=\"".$k."\" country_code=\"".$csv_content[4]."\" country_name=\"".$csv_content[5]."\">\r\n";
			$xml_country_code = $csv_content[4];
			$flg_country = true;
        }
		if (!empty($csv_content[6]))
		{
			$xml_content .= "                <city city_code=\"".$csv_content[6]."\" city_name=\"".$csv_content[7]."\" />\r\n";
		}
		if (empty($csv_content[2]) && empty($csv_content[4]) && empty($csv_content[6]))
		{
			$sub_element = false;
		}
	}
	// CSVファイルを閉じる
	fclose($file);

	$xml_content = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n"."<root>\r\n".$xml_content;
	$xml_content .= "    </departure>\r\n";
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