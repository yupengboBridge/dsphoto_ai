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
function cutStr($str, $length = '') { // $length&#20026;���������C�s����&#33410;
 if ($length != '') { // �@��$length�s&#20026;��
  $len = strlen($str); //��������&#38271;�x
  $strOk = '';
  $i = 0; //����&#38271;�x
  $n = 0; //��������
  while ($i < $len && $n < $length) {
   $ascii = ord($str{$i}); //�������O��&#33410;�IASCII&#30721;
   if ($ascii > 129) { // �嘰129�C��2����&#33410;����
    $strOk .= substr($str, $i, 2);
    $i += 2;
    $n++;
   } else { // ��������129�C��1����&#33410;����
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

$telop_text =  mb_convert_encoding("�N���W�b�g1�N���W�b�g�N���W�b�g�N���W�b�g�N���W�b�g2�N���W�b�g�N���W�b�g�N���W�b�g", "UTF-8", "auto");
echo mb_strlen($telop_text);
echo "<br/>";
echo mb_substr($telop_text,0,20,"utf-8");
echo "<br/>";
echo mb_substr($telop_text,20,20,"utf-8");
echo "<br/>";
?>