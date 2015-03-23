<?php

require_once "application/startup.php";
require_once "application/libs/functions.php";
require_once "application/libs/acResizeImage.php";
require_once "dependencies/getid3/getid3.php";
require_once "dependencies/Twig/Autoloader.php";

gc_enable();

\Twig_Autoloader::register(true);

$router = \Framework\Router::getInstance();

$router->route();

