<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
@ini_set('memory_limit', -1);

if (PHP_SAPI === 'cli') {
    $root_path = str_replace("/malltools","",dirname(__FILE__));
} else {
    $root_path = "..";
}
require_once ($root_path.'/malltools/vendor/autoload.php');
require_once ($root_path.'/malltools/Task.php');

if (function_exists('posix_getppid')) {
    $ppid = posix_getppid();
    $parent_process = trim(shell_exec("ps -p $ppid -o comm="));
    if($parent_process == "systemd" || $parent_process == "bash"){
        $task = new Task();
        $task->run($argv);
    }
    $log_file = $root_path."/log/testcron.log";
    file_put_contents($log_file,date("Y-m-d H:i:s").">>>>".$parent_process.PHP_EOL,FILE_APPEND);
}
