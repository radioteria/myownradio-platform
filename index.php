<?php

gc_enable();

$memory = memory_get_usage(true);

require_once "application/startup.php";
require_once "application/libs/functions.php";
require_once "application/libs/acResizeImage.php";


$router = new \Framework\Router();

$router->route();


$used = memory_get_usage() - $memory;

logger("Memory used: " . number_format($used));