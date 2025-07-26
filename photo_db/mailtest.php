#!/usr/bin/php -q

<?php
$MailTo = 's.houda@bud-international.co.jp';
//$MailTo = 'mado.nakahata@gmail.com';
$MailSubject = "メール送信テストです【xhankyu】";
$MailBody = "送信できましたか。日本語が化けないかどうかのテストもします<br /><strong style='color:red;'>HTMLメール</strong>で送られているかどうかも。";
$MasterSendMailName = "ほうだ";
$MasterSendMailAd = 's.houda@bud-international.co.jp';

mb_language("ja");
mb_internal_encoding("UTF-8");
/*文字化け対策一式*/
$MailSubject = mb_convert_encoding($MailSubject, "JIS", "UTF-8");
$MailSubject='=?iso-2022-jp?B?'.base64_encode($MailSubject).'?=';
$MailBody = mb_convert_encoding($MailBody, "JIS", "UTF-8");
$MasterSendMailName = mb_encode_mimeheader($MasterSendMailName);

$MailHead = "MIME-Version: 1.0\r\n";
$MailHead .= "Content-Type: text/html; multipart/alternative; charset=ISO-2022-JP\r\n";
$MailHead .= "From: " . $MasterSendMailName . " <" . $MasterSendMailAd . ">\r\n";

mail($MailTo, $MailSubject, $MailBody, $MailHead, '-fweb@bud-international.co.jp');

echo "終わったよ\n";

?>