<?php
header('Content-type: image/jpeg');
$material_id='';
if($_REQUEST['p_photo_mno']){
  $material_id =  str_replace(array('jpg','gif','png'),'', $_REQUEST['p_photo_mno']);
}
/*http://x.hankyu-travel.com/cms_photo_image/image_search_kikan3_test.php?p_photo_mno=00000-ALLUP-119457.jpg
*/
//$material_id = '749172';

echo file_get_contents('https://api.bud-group.com/img.php?material_id='.$material_id);




?>