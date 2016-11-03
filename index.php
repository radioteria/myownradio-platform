<?php

use Facebook\FacebookSession;
use Framework\Router;
use Framework\Template;

require_once 'vendor/autoload.php';

// AntiShame Mode: On
require_once 'application/init.php';
require_once "application/startup.php";
// AntiShame Mode: Off

require_once "application/libs/functions.php";
require_once "application/libs/acResizeImage.php";

// Facebook setup
FacebookSession::setDefaultApplication(
    env('FACEBOOK_APP_ID'),
    env('FACEBOOK_APP_SECRET')
);

// Template engine setup
Template::setTemplatePath("application/tmpl");

// Env file
if (file_exists(ENV_FILE)) {
    $loader = new \josegonzalez\Dotenv\Loader(ENV_FILE);
    $loader->parse();
    $loader->toEnv();
}

// Routing setup and run
$router = Router::getInstance();

$router->route();
