<?php
	$dir = "log";
	$dh = opendir($dir);
	while (($file = readdir($dh)) !== false)
	{
		if(strpos("__".$file,"permit_image") && $file != "nopermit_image.log")
		{
			if(file_exists($dir."/".$file))
			{
				$datenum = getdatenum($file);
				$orderdate[] = $datenum;
				//regard date as key
				if(empty($array[$datenum])){
					$array[$datenum]= file($dir."/".$file);
				}else{
					$filecontent = file($dir."/".$file);
					$temp = $array[$datenum];
					$array[$datenum] = array_merge($filecontent,$temp);
				}
			}
		}
	}
	closedir($dh);
	rsort($orderdate);
	function getdatenum($str){
		preg_match("/[0-9]+/",$str,$num);
		return trim($num[0]);
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Logログ</title>
<style type="text/css">
body,html{margin:0px; padding:0px;font-size: 12px;}
* { margin:0px;padding:0px;}
.tiplnn{
	background:#FFFEE1;
    border:1px solid #EAA465;
    margin-left:auto;
    margin-right:auto;
    padding:10px;
}
.tiplnn dt{
	font-weight: bold;
}
.tiplnn dd{
	padding: 2px 15px;
	color: #005AA0;
}
</style>
</head>
<body>
	<?php
		if(isset($array)&&isset($orderdate))
  	  	{
      		foreach($orderdate as $key){
    ?>
    <div class="tiplnn">
	 <dl>
	  <dt>
	    <?php 
	    	$str = $key;
			$str_year = substr($key,0,4);
			$str_month = substr($key,4,2);
			$str_day = substr($key,6,2);
			echo $str_year."年".$str_month."月".$str_day."日";
			$arr = $array[$key];
	    ?>
	   </dt>
	  <?php
	    foreach($arr as $k => $value){
	  ?>
	  <dd><?php echo $value;?></dd>
	  <?php
	    }
	  ?>
	 </dl>
    </div>
    <?php
      	}
}
    ?>
</body>
</html>
<SCRIPT   LANGUAGE="JavaScript">  
    var   xMax   =   screen.width;
    var   yMax   =   screen.height;
    window.moveTo(xMax/2-415,yMax/2-250-80);
</SCRIPT>