<?php
require_once('./config.php');
require_once('./lib.php');

// ＤＢへ接続します。
$db_link = db_connect();

$pi = new PhotoImageDB ();
$p_photo_id = "69564";
$pi->photo_filename = "http://x.hankyu-travel.com/photo_db/./uploads/4/201007281542589864.jpg";
$pi->photo_filename_th1 = "http://x.hankyu-travel.com/photo_db/./thumb1/4/201007281542589864th1.jpg";
$pi->photo_filename_th2 = "http://x.hankyu-travel.com/photo_db/./thumb2/4/201007281542589864th2.jpg";
$pi->photo_filename_th3 = "http://x.hankyu-travel.com/photo_db/./thumb3/4/201007281542589864th3.jpg";
$pi->photo_filename_th4 = "http://x.hankyu-travel.com/photo_db/./thumb4/4/201007281542589864th4.jpg";
$pi->write_imagetodb($db_link, $p_photo_id);

// ＤＢへ接続します。
$db_link2 = db_connect();
$p_photo_id = "48644";
$pi->photo_filename = "http://x.hankyu-travel.com/photo_db/./uploads/3/200911101121466009.jpg";
$pi->photo_filename_th1 = "http://x.hankyu-travel.com/photo_db/./thumb1/3/200911101121466009th1.jpg";
$pi->photo_filename_th2 = "http://x.hankyu-travel.com/photo_db/./thumb2/3/200911101121466009th2.jpg";
$pi->photo_filename_th3 = "http://x.hankyu-travel.com/photo_db/./thumb3/3/200911101121466009th3.jpg";
$pi->photo_filename_th4 = "http://x.hankyu-travel.com/photo_db/./thumb4/3/200911101121466009th4.jpg";
$pi->write_imagetodb($db_link2, $p_photo_id);

// ＤＢへ接続します。
$db_link3 = db_connect();
$p_photo_id = "48643";
$pi->photo_filename = "http://x.hankyu-travel.com/photo_db/./uploads/2/200911101121448621.jpg";
$pi->photo_filename_th1 = "http://x.hankyu-travel.com/photo_db/./thumb1/2/200911101121448621th1.jpg";
$pi->photo_filename_th2 = "http://x.hankyu-travel.com/photo_db/./thumb2/2/200911101121448621th2.jpg";
$pi->photo_filename_th3 = "http://x.hankyu-travel.com/photo_db/./thumb3/2/200911101121448621th3.jpg";
$pi->photo_filename_th4 = "http://x.hankyu-travel.com/photo_db/./thumb4/2/200911101121448621th4.jpg";
$pi->write_imagetodb($db_link3, $p_photo_id);
?>