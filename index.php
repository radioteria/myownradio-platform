<?php

use Facebook\FacebookSession;
use Framework\Router;

require_once "application/startup.php";
require_once "application/libs/functions.php";
require_once "application/libs/acResizeImage.php";
require_once "dependencies/getid3/getid3.php";
require_once "dependencies/Twig/Autoloader.php";

gc_enable();

\Twig_Autoloader::register(true);

FacebookSession::setDefaultApplication('731742683610572', 'f84af1cdddcc6ac06c6ebf606fb616c3');

$router = Router::getInstance();

$router->route();

