<?php

use Buuum\S3;
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

// Load env file
if (file_exists(ENV_FILE)) {
    $loader = new \josegonzalez\Dotenv\Loader(ENV_FILE);
    $loader->parse();
    $loader->toEnv();
}

// Init session

// Facebook setup
FacebookSession::setDefaultApplication(
    env('FACEBOOK_APP_ID'),
    env('FACEBOOK_APP_SECRET')
);

// Template engine setup
Template::setTemplatePath("application/tmpl");

// Init s3 storage
S3::setAuth(config('services.s3.access_key'), config('services.s3.secret_key'));
S3::setBucket(config('services.s3.bucket'));
S3::setAcl(S3::ACL_PUBLIC_READ);
S3::setStorage(S3::STORAGE_CLASS_STANDARD);

// Routing setup and run
$router = Router::getInstance();
$router->route();
