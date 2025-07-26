<?php

require_once dirname(__FILE__).'/../Router.php';
require_once dirname(__FILE__).'/../service/LoggerDiff.php';

LoggerDiff::rotate();

LoggerDiff::info('Entering difference csv file create mode.');
$router = new Router('difference/create');
$router->run();
LoggerDiff::info('End');
