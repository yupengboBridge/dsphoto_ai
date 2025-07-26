
<html>
	<body>
		<?php
		$p_test = isset($_GET['p_test'])?$_GET['p_test']:"";
		$p_preview = isset($_GET['p_preview'])?$_GET['p_preview']:"";
		if((1==$p_test)||(1==$p_preview))
		{
			//
		} else {
		?>
		<img id="img1" src="http://x.hankyu-travel.com/photo_db/access_log_get.php?p_course_no=1234567&access_time=2010-07-03_00:01:03" />
		<?php  
		} 
		?>
	</body>
</html>