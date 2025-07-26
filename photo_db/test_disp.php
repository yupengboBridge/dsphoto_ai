<?php
//
//$tmp = strtolower("ＰASYL-0253A.jpg");
//
//$memcache_obj = memcache_connect('http://x.hankyu-travel.com/');
//echo $memcache_obj;
//echo $tmp;
require_once('./config.php');
require_once('./lib.php');

if ($db_link)
{
	echo "ok";
} else {
	echo "error";
}
?>