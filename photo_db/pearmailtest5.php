<?php
########################################################################
#
#  ・メールを送信(daily,weekly(月曜日),montly(1日))
#   
#  @copyright  2011 BUD International
#  @version    1.0.0
########################################################################

<?php
require_once("Mail.php");
require_once("Mail/mime.php");

$params = array(
  "host" => "203.133.239.34",
  "port" => 587,
  "auth" => true,
  "username" => "budinter",
  "password" => "#budinter"
);

$mailObject = Mail::factory("smtp", $params);

$recipients = "s.houda@bud-international.co.jp";

$body = <<<EOS
<html>
<head>
<meta http-equiv="Content-Type" Content="text/html;charset=UTF-8">
</head>
<body>
<h1>HTMLメールのテスト</h1>
<p>
HTMLメールのテストです。setHTMLBodyメソッドを使います。
</p>
</body>
</html>
EOS;

$mimeObject = new Mail_Mime("¥n");
$mimeObject -> setHTMLBody($body);

$bodyParam = array(
  "head_charset" => "ISO-2022-JP",
  "html_charset" => "Shift_Jis"
);

$body = $mimeObject -> get($bodyParam);

$addHeaders = array(
  "To" => "s.houda@bud-international.co.jp",
  "From" => "s.houda@bud-international.co.jp",
  "Subject" => mb_encode_mimeheader("テストメール")
);

$headers = $mimeObject -> headers($addHeaders);

$mailObject -> send($recipients, $headers, $body);

?>
