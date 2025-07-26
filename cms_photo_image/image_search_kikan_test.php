<?php
require_once('./lib.php');

$p_mno = array_get_value($_REQUEST, 'p_photo_mno' ,"");

$p_mno = '00142-webbn-01360.jpg';
if(isKeitai())
{
	$newurl = "http://x.hankyu-travel.com/cms_photo_image/image_search_kikan_new.php?p_photo_mno=".$p_mno;

	//echo ("<script>window.open('$newurl');</script>"); 
	//echo ("<script>window.location.href='$newurl';</script>"); 
	
	 header("Location: $newurl");
	 exit;

}
else
{
	$stragent = $_SERVER['HTTP_USER_AGENT'];
	echo "$stragent <br/>";
	echo 'this is come from PC <br/>';
	$str =  "<img id='img1' src='http://x.hankyu-travel.com/cms_photo_image/image_search_kikan.php?p_photo_mno=";
	$str .= $p_mno."' /><br/>";
	print $str;
}

?>
<html>
<head>
<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8”>
<title>画像表示</title>
<script type="text/javascript">

</script>
</head>
<body>
<?php
$str = "ファイル名を使っています<br/>";
print $str;


$str = "<br/><br/><br/><br/>DBから取得する<br/>";
print $str;

$str =  "<img id='img1' src='./image_search_kikan2.php?p_photo_mno=";
$str .= $p_mno."' />";

print $str;

//ユーザーエージェントの判別
	function isKeitai() {
	    //NTT DoCoMo
	    if (preg_match("/DoCoMo/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //旧J-PHONE〜vodafoneの2G
	    if (preg_match("/J-PHONE/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //vodafoneの3G
	    if (preg_match("/Vodafone/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //vodafoneの702MOシリーズ
	    if (preg_match("/MOT/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //SoftBankの3G
	    if (preg_match("/SoftBank/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //au (KDDI)
	    if (preg_match("/PDXGW/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    if (preg_match("/UP\.Browser/i", $_SERVER['HTTP_USER_AGENT'])) return true;
			if (preg_match("/KDDI-/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //ASTEL
	    if (preg_match("/ASTEL/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //DDI Pocket
	    if (preg_match("/DDIPOCKET/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    
	    if (preg_match("/Android/i", $_SERVER['HTTP_USER_AGENT'])) return true;
	    //IPHONE
	    if (preg_match("/iPhone/i", $_SERVER['HTTP_USER_AGENT'])) return true;

	    return false;
	}
?>
</body>
</html>