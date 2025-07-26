<?php

require_once dirname(__FILE__).'/../Router.php';
require_once dirname(__FILE__).'/../service/Logger.php';

Logger::rotate();

Logger::info('Entering recent registration mode');
$router = new Router('register/recent');
$router->run();
Logger::info('End');
