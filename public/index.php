<?php

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

$_GET['route'] = ltrim($uri, '/');

require_once __DIR__ . '/../index.php';
