<?php
require_once('./config.php');
require_once('./lib.php');
// セッション管理をスタートします。
session_start();
//xu add it on 2010-11-30 start
if(empty($_SESSION['login_id'])&&isset($_COOKIE['login_id'])&&$_COOKIE['login_id']!="")
{
	$_SESSION['login_id'] = array_get_value($_COOKIE,'login_id' ,"");
	$_SESSION['user_name'] = array_get_value($_COOKIE,'user_name' ,"");
	$_SESSION['security_level'] = array_get_value($_COOKIE,'security_level' ,"");
	$_SESSION['compcode'] = array_get_value($_COOKIE,'compcode' ,"");
	$_SESSION['group'] = array_get_value($_COOKIE,'group' ,"");
	$_SESSION['user_id'] = array_get_value($_COOKIE,'user_id' ,"");
}
//xu add it on 2010-11-30 end
$s_login_id = array_get_value($_SESSION,'login_id' ,"");
$s_login_name = array_get_value($_SESSION,'user_name' ,"");
$s_security_level = array_get_value($_SESSION,'security_level' ,"");
$comp_code = array_get_value($_SESSION,'compcode' ,"");
$s_group_id = array_get_value($_SESSION,'group' ,"");
$s_user_id = array_get_value($_SESSION,'user_id' ,"");

if (!empty($s_security_level)) $s_security_level = (int)$s_security_level;

//// for Debug
//$s_user_id = 1;
//$s_login_name = "BUD管理者";
//$s_login_id = "admin";

if (!empty($s_login_id))
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD html 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript" />
<title>写真データベース</title>
<link href="./css/base.css" rel="stylesheet" type="text/css" />
<script src="./js/common.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
function init()
{
	setCookie("bt_cnt",0);
	setCookie("classname","");
}
</script>
</head>
<body style="background:url(parts/header_bg.gif) repeat-x; text-align:center;" onload="init();">
<div>
  <iframe scrolling="no" frameborder="0" id="iframe_top" height="75"  width="900" name="top"  src="./findtop<?=$s_security_level?>.php"></iframe>
</div>
<div>
  <iframe scrolling="no" frameborder="0" id="iframe_middle1"  height="90"  width="900" name="middle1" src="./search_menu.php"></iframe>
  </div>
  <div>
  <iframe scrolling="no" frameborder="0" id="iframe_middle2"  height="105"  width="900" name="middle2" src="./pickup_ichiran1.php?init=1"></iframe>
  </div>
  <div>
  <iframe scrolling="no" frameborder="0" id="iframe_bottom"  height="600"  width="900" name="bottom" src="./search_result.php?init=1"></iframe>
</div>
  <P id="copyright" width="1000" >Copyright &copy; 2008 BUD International All rights reserved.</P>
  <div id="footer" width="1000"></div>
</body>
  <noframes>
  <body>
  <P>このページを表示するには、フレームをサポートしているブラウザが必要です。</P>
  </body>
  </noframes>
</html>
<?php
}else{
	// ログイン画面へ遷移する
	header_out($logout_page);
}
$db_link = null;
exit(0);


?>
