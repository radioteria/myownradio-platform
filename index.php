<?php

use Facebook\FacebookSession;
use Framework\Router;
use Framework\Template;
use Tools\System;

// Redirect from www.
if (substr($_SERVER['HTTP_HOST'], 0, 4) == "www.") {
    $redirect = "https://".substr($_SERVER['HTTP_HOST'], 4).$_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
    die();
}

// Allow only https access
if ((!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") && substr($_SERVER['HTTP_HOST'], 0, 5) != "test."){
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
    die();
}

require_once "application/startup.php";
require_once "application/libs/functions.php";
require_once "application/libs/acResizeImage.php";
require_once "dependencies/getid3/getid3.php";
require_once "dependencies/Twig/Autoloader.php";

gc_enable();

Twig_Autoloader::register(true);
FacebookSession::setDefaultApplication('731742683610572', 'f84af1cdddcc6ac06c6ebf606fb616c3');
Template::setTemplatePath("application/tmpl");

$router = Router::getInstance();

$router->route();

$start = System::realTime();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://homefs.biz:8080/notif1er/notify");
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    "keys" => "route",
    "data" => json_encode($_SERVER['REQUEST_URI'])
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);

error_log((System::realTime() - $start) / 1000);
