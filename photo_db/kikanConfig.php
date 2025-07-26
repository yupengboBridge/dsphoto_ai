<?php
// データーベース情報です
//$db_host = '10.254.2.63';
//$db_host = '10.254.2.39';
//$db_host = 'localhost';
$db_host = '127.0.0.1';
$db_user = 'photodbuser';
//$db_user = 'root';
$db_password = 'h9!rkG726';
//$db_password = '222222';
$db_name = 'photodb_image';
//$db_name = 'photo';
$db_charset = 'utf8';
$db_link;

$kikan_root_dir_photo_db = '/var/www/vhosts/photo-db.site/httpdocs/photo_db';
$kikan_root_dir_cms_photo_image = '/var/www/vhosts/photo-db.site/httpdocs/cms_photo_image';

date_default_timezone_set('Asia/Tokyo');
?>