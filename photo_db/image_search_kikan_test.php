<?php
require_once('./lib.php');
$p_mno = array_get_value($_REQUEST, 'p_photo_mno' ,"");
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

$str =  "<img id='img1' src='./image_search_kikan.php?p_photo_mno=";
$str .= $p_mno."' /><br/>";
print $str;

$str = "<br/><br/><br/><br/>DBから取得する<br/>";
print $str;

$str =  "<img id='img1' src='./image_search_kikan2.php?p_photo_mno=";
$str .= $p_mno."' />";
print $str;

?>
</body>
</html>