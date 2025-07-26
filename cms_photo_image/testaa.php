<?php
function mysubstr($str, $start, $len) {
	$tmpstr = "";
	$strlen = $start + $len;
	for($i = 0; $i < $strlen; $i++) {
		if(ord(substr($str, $i, 1)) > 0xa0) {
   $tmpstr .= substr($str, $i, 2);
   $i++;
		} else
		$tmpstr .= substr($str, $i, 1);
	}
	return $tmpstr;
}
function cutStr($str, $length = '') { // $length&#20026;字符个数，不是字&#33410;
 if ($length != '') { // 如果$length不&#20026;空
  $len = strlen($str); //得到字符&#38271;度
  $strOk = '';
  $i = 0; //字符&#38271;度
  $n = 0; //字符个数
  while ($i < $len && $n < $length) {
   $ascii = ord($str{$i}); //得到当前字&#33410;的ASCII&#30721;
   if ($ascii > 129) { // 大于129，是2个字&#33410;字符
    $strOk .= substr($str, $i, 2);
    $i += 2;
    $n++;
   } else { // 小于等于129，是1个字&#33410;字符
    $strOk .= substr($str, $i, 1);
    $i++;
    $n++;
   }
  }
 } else {
  $strOk = $str;
 }
 return $strOk;
}

$telop_text =  mb_convert_encoding("クレジット1クレジットクレジットクレジットクレジット2クレジットクレジットクレジット", "UTF-8", "auto");
echo mb_strlen($telop_text);
echo "<br/>";
echo mb_substr($telop_text,0,20,"utf-8");
echo "<br/>";
echo mb_substr($telop_text,20,20,"utf-8");
echo "<br/>";
?>