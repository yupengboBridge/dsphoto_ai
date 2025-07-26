<?php
	date_default_timezone_set('Asia/Tokyo');
	$dir = dirname(__FILE__)."/log";
	$dh = opendir($dir);
	while (($file = readdir($dh)) !== false)
	{
		if(strpos("__".$file,"nopermit_image"))
		{
			if(file_exists($dir."/".$file))
			{
				if(getdatenum($file) != "")
				{
					$datenum = strtotime(getdatenum($file));
					$now = strtotime(date("Ymd"));
					$time_difference = ($now-$datenum)/3600/24;
					if($time_difference>30)
					{
						$order = "rm -rf ".$dir."/".$file;
						exec($order);
					}
				}
			}
		}
	}
	closedir($dh);
	function getdatenum($str){
		preg_match("/[0-9]+/",$str,$num);
		if(isset($num[0]))
		{
			return trim($num[0]);
		}else{
			return "";
		}
	}
?>