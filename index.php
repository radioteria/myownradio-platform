<?php

$memory = memory_get_usage(true);

require_once "application/startup.php";
require_once "application/libs/functions.php";


$router = new \MVC\Router();

$router->route();


$used = memory_get_usage() - $memory;

logger("Memory used: " . number_format($used));