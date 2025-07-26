<?php

require_once 'lib/Router.php';

$router = new Router(filter_input(INPUT_GET, 'path'));
$router->run();
