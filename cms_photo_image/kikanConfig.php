<?php
// データーベース情報です
$db_host = 'localhost';
// $db_host = 'localhost';
$db_user = 'root';
// $db_user = 'root';
$db_password = 'root@Hcst2022';
//$db_password = '';
$db_name = 'photodb_image';
// $db_name = 'photo';
$db_charset = 'utf8';
$db_link = null;

$kikan_root_dir_photo_db = '/var/www/vhosts/photo-db.site/httpdocs/photo_db';
$kikan_root_dir_cms_photo_image = '/var/www/vhosts/photo-db.site/httpdocs/cms_photo_image';

$credit_fontsize = array(8, 10, 14, 16, 16, 16);						// -160, -320, -480, -640, -800, 

date_default_timezone_set('Asia/Tokyo');
?>