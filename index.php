<?php

gc_enable();

$memory = memory_get_usage();
$time = microtime(true);

require_once "application/startup.php";
require_once "application/libs/functions.php";
require_once "application/libs/acResizeImage.php";
require_once "dependencies/getid3/getid3.php";

$router = \Framework\Router::getInstance();

$router->route();

$used = memory_get_usage() - $memory - 79568;
$spent = microtime(true) - $time;

//logger(
//    "IP: " . Framework\Services\HttpRequest::getInstance()->getRemoteAddress() .
//    " Route: " . $router->getLegacyRoute() .
//    " | Memory used: " . $used / 1000 . "KB, " .
//    "Time: " . number_format($spent, 2, ".", " ") . "s");