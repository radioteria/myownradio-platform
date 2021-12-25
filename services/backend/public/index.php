<?php

use Facebook\FacebookSession;
use Framework\Models\AuthUserModel;
use Framework\Router;
use Framework\Template;
use josegonzalez\Dotenv\Loader;
use app\Config\Config;
use Sentry\State\Scope;
use function Sentry\captureException;
use function Sentry\configureScope;
use function Sentry\init;

define('BASE_DIR', realpath(__DIR__ . '/..'));

$uri = urldecode(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));

$_GET['route'] = ltrim($uri, '/');

require_once BASE_DIR . '/vendor/autoload.php';

// Init sentry
init(['dsn' => env('SENTRY_DSN')]);

// AntiShame Mode: On
require_once BASE_DIR . '/application/init.php';
require_once BASE_DIR . '/application/startup.php';
// AntiShame Mode: Off

require_once BASE_DIR . "/application/libs/functions.php";
require_once BASE_DIR . "/application/libs/acResizeImage.php";


try {
    // Load .env file
    if (file_exists(ENV_FILE)) {
        $loader = new Loader(ENV_FILE);
        $loader->parse();
        $loader->toEnv(true);
    }

    // Init Config 2.0
    $config = Config::fromEnv();
    $config->saveAsInstance();

    // Facebook setup
    FacebookSession::setDefaultApplication(
        $config->getFacebookAppId(),
        $config->getFacebookAppSecret(),
    );

    // Template engine setup
    Template::setTemplatePath(BASE_DIR . "/application/tmpl");

    // Set timezone
    date_default_timezone_set(config('app.timezone'));

    // Configure sentry scope
    configureScope(function (Scope $scope): void {
        try {
            $userModel = AuthUserModel::getInstance();
            $scope->setUser(['email' => $userModel->getEmail()]);
        } catch (\Exception $exception) {}
    });

    // Routing setup and run
    $router = Router::getInstance();

    $router->route();
} catch (\Throwable $exception) {
    echo 'E500 ';

    echo $exception->getMessage();

    echo $exception->getTraceAsString();

    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);

    captureException($exception);
}
