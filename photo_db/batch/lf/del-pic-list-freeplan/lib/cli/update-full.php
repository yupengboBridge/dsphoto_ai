<?php

require_once dirname(__FILE__).'/../Router.php';
require_once dirname(__FILE__).'/../service/Logger.php';

Logger::rotate();

Logger::info('Entering full registration mode, this will take so long while updating database.');
$router = new Router('register/all');
$router->run();
Logger::info('End');
