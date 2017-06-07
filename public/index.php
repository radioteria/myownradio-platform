<?php

use Facebook\FacebookSession;
use Framework\Router;
use Framework\Template;

define('BASE_DIR', __DIR__ . '/..');

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

$_GET['route'] = ltrim($uri, '/');

require_once BASE_DIR . '/vendor/autoload.php';

// AntiShame Mode: On
require_once BASE_DIR . '/application/init.php';
require_once BASE_DIR . '/application/startup.php';
// AntiShame Mode: Off

require_once BASE_DIR . "/application/libs/functions.php";
require_once BASE_DIR . "/application/libs/acResizeImage.php";

try {
    // Load env file
    if (file_exists(ENV_FILE)) {
        $loader = new \josegonzalez\Dotenv\Loader(ENV_FILE);
        $loader->parse();
        $loader->toEnv();
    }

    // Init sentry
    $ravenClient = new Raven_Client(env('SENTRY_DSN'));
    $ravenClient->install();

    // Facebook setup
    FacebookSession::setDefaultApplication(
        env('FACEBOOK_APP_ID'),
        env('FACEBOOK_APP_SECRET')
    );

    // Template engine setup
    Template::setTemplatePath(BASE_DIR . "/application/tmpl");

    // Set timezone
    date_default_timezone_set(config('app.timezone'));

    // Routing setup and run
    $router = Router::getInstance();

    $router->route();
} catch (\Exception $exception) {
    echo 'E500 ';

    echo $exception->getMessage();

    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

    $eventId = $ravenClient->captureException($exception);
}
