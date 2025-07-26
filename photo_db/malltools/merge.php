<?php
require_once ('./malltools/vendor/autoload.php');
require_once ('./malltools/Task.php');

$task = new Task();
$result = $task->run();
echo json_encode($result);
