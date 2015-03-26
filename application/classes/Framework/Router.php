<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace Framework;

use Framework\Exceptions\ControllerException;
use Framework\Exceptions\DocNotFoundException;
use Framework\Exceptions\NotImplementedException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injector;
use Framework\Services\HttpGet;
use Framework\Services\HttpRequest;
use Framework\Services\JsonResponse;
use Framework\Services\SubRouter;
use Framework\Services\TwigTemplate;
use Framework\View\Errors\View404Exception;
use Framework\View\Errors\ViewException;
use ReflectionClass;
use Tools\Singleton;
use Tools\SingletonInterface;

class Router implements SingletonInterface{

    use Singleton;

    private $route;
    private $legacyRoute;

    /**
     * @return mixed
     */
    public function getLegacyRoute() {
        return $this->legacyRoute;
    }



    function __construct() {

        $httpGet = HttpGet::getInstance();

        $this->legacyRoute = preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->getParameter("route")
            ->getOrElse("index"));

        $routeParts = explode("/", $this->legacyRoute);

        $count = count($routeParts);
        $routeParts[$count - 1] = "Do" . ucfirst($routeParts[$count - 1]);
        $this->route = implode("/", $routeParts);

        $this->registerSubRoutes();

    }

    private function registerSubRoutes() {

        $sub = SubRouter::getInstance();

        /* Public side routes register */
        $sub->addRoute("content/application.modules.js", "content\\DoGetJavascriptModules");

        /* Dashboard redirect */
        $sub->addRouteRegExp("~^profile(\\/.+)*$~", "content\\DoDashboard");

        $sub->addRoutes([
                "streams",
                "bookmarks",
                "login",
                "recover",
                "recover/:code",
                "signup",
                "signup/:code",
                "static/registrationLetterSent",
                "static/registrationCompleted",
                "static/resetLetterSent",
                "static/resetPasswordCompleted",
                "categories"
            ], "content\\DoDefaultTemplate");


        $sub->addRoute("user", function () {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: /");
            die();
        });

        $sub->addRoute("streams/:id",   "helpers\\DoStream");       // Helper for social networks
        $sub->addRoute("user/:id",      "helpers\\DoUser");
        $sub->addRoute("search/:query", "helpers\\DoSearch");

        $sub->addRoute("content/streamcovers/:fn",   "content\\DoGetStreamCover");
        $sub->addRoute("content/avatars/:fn",        "content\\DoGetUserAvatar");
        $sub->addRoute("content/audio/&id",          "content\\DoGetPreviewAudio");
        $sub->addRoute("content/m3u/:stream_id.m3u", "content\\DoM3u");
        $sub->addRoute("content/trackinfo/&id",      "content\\DoTrackExtraInfo");

        // Default route
        $sub->defaultRoute(function () {
            throw new View404Exception();
        });

    }

    public function route() {

        try {

            if (!$this->findRoute()) {
                $sub = SubRouter::getInstance();
                $sub->goMatching($this->legacyRoute);
            }


        } catch (UnauthorizedException $e) {

            $this->exceptionRouter($e);

        } catch (ControllerException $e) {

            $this->exceptionRouter($e);

        } catch (DocNotFoundException $e) {

            http_response_code(404);
            echo '<h1>E404: File not found</h1>';
            return;

        } catch (NotImplementedException $e) {

            http_response_code(501);
            echo '<h1>E501: Method not implemented</h1>';
            return;

        } catch (ViewException $exception) {

            $exception->render();
            return;

        }

        if (JsonResponse::hasInstance()) {

            $response = JsonResponse::getInstance();

            callPrivateMethod($response, "write");

        }


    }

    private function findRoute() {

        return $this->callRoute($this->route);

    }

    public function callRoute($className) {



        $request = HttpRequest::getInstance();
        $method = "do" . ucfirst(strtolower($request->getMethod()));
        $class = str_replace("/", "\\", CONTROLLERS_ROOT . $className);

        // Reflect controller class
        if (!class_exists($class, true)) {
            return false;
        }

        $reflection = new \ReflectionClass($class);

        // Check for valid reflector
        if (!$reflection->implementsInterface("Framework\\Controller")) {
            throw new DocNotFoundException("Controller must implement Framework\\Controller interface");
        }

        $classInstance = $reflection->newInstance();

        try {

            Injector::getInstance()->call([$classInstance, $method]);


        } catch (\ReflectionException $e) {

            throw new NotImplementedException();

        }

        return true;

    }

    private function exceptionRouter(ControllerException $exception) {

        $response = JsonResponse::getInstance();

        $response->setMessage($exception->getMyMessage());
        $response->setData($exception->getMyData());
        $response->setCode(0);

    }


}