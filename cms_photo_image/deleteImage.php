<?php
$DS = DIRECTORY_SEPARATOR;
//$filePath = "./thumb5";
$filePath = "/home2/chroot/home/xhankyu/public_html/cms_photo_image/thumb5";
//$imageName = "new.jpg";
//$fileName = $filePath.$DS.$imageName;
if(@is_dir($filePath.$DS))
{
	if($dir = @opendir($filePath.$DS))
	{
		while (($file = @readdir($dir)) !== false)
		{
			if($file != ".." && $file != ".")
			{
				$fileName = $filePath.$DS.$file;
				$d1=time();
				$d2=@filectime($fileName);
				$Days=round(($d1-$d2)/3600/24,2);
//				echo $d1."<br>";
//				echo $d2."<br>";
//				echo $Days."<br>";
				if($Days > 7)
				{
//					echo $file;
					unlink($fileName);
				}
			}
		}
	}
}

