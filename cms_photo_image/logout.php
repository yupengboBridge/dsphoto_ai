<?php
// セッション管理をスタートします。
session_start();

// セッション変数を全て解除します。
$_SESSION = array();

// セッションを切断するにはセッションクッキーも削除します。
if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
}

// 最終的に、セッションを破壊します。
session_destroy();

print "<script type='text/javascript'>\r\n";
print "var login_id = 'login_id';";
print "var user_name = 'user_name';";
print "var security_level = 'security_level';";
print "var compcode = 'compcode';";
print "var group = 'group';";
print "var user_id = 'user_id';";

print "document.cookie = login_id + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
print "document.cookie = user_name + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
print "document.cookie = security_level + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
print "document.cookie = compcode + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
print "document.cookie = group + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
print "document.cookie = user_id + \"=\" + \"xx; expires=Tue, 1-Jan-1980 00:00:00;\";";
print "</script>";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href="css/base.css" rel="stylesheet" type="text/css" />
<script language="JavaScript">
<!--
function NoFrame()
{
	if (self != top)
	{
		top.location.href = self.location.href;
	}
	location.href = "./login.php";
}
//-->
</script>
</head>
<body onload="NoFrame();">
</body>
</html>
<?php
exit(0);
?>
