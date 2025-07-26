<?php

require_once dirname(__FILE__).'/../Router.php';
require_once dirname(__FILE__).'/../service/Logger.php';

Logger::rotate();

/**
 * release-tb-delete
 * ALTER TABLEの前に該当テーブルのデータを削除するバッチ（リリース専用）
 *
 * TBのデータを削除しますので、リリース以外には使用しないでください！！
 */
Logger::info('Entering release mode.');
$router = new Router('release/all');
$router->run();
Logger::info('End');
