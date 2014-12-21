<?php

gc_enable();

$memory = memory_get_usage(true);
$time = microtime(true);

require_once "application/startup.php";
require_once "application/libs/functions.php";
require_once "application/libs/acResizeImage.php";


$router = new \Framework\Router();

$router->route();


$used = memory_get_usage(true) - $memory;
$spent = microtime(true) - $time;

logger("Memory used: " . $used / 1000 . "KB, Time: " . number_format($spent, 2, ".", " ") . "s");