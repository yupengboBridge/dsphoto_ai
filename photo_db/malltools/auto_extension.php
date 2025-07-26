<?php
if (PHP_SAPI === 'cli') {
    $root_path = str_replace("/malltools","",dirname(__FILE__));
} else {
    $root_path = "..";
}

require_once ($root_path.'/malltools/Extension.php');

$task = new Extension();

$task->run();
?>