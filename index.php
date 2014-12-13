<?php

include "libs/FluentPDO/FluentPDO.php";

require_once "application/startup.php";

$router = new \MVC\Router();

$router->route();
