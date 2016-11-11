<?php

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

if ($uri !== '/' && file_exists("public/" . $uri)) {
    return false;
}

$_GET['route'] = ltrim($uri, '/');

require_once __DIR__ . "/public/index.php";
