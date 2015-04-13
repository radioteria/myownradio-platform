<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace Framework;

use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Injector\Injector;
use Framework\Services\CurrentRoute;
use Framework\Services\HttpRequest;
use Framework\Services\JsonResponse;
use Framework\Services\SubRouter;
use Framework\View\Errors\View500Exception;
use Framework\View\Errors\View501Exception;
use Framework\View\Errors\ViewException;
use ReflectionClass;
use Tools\Singleton;
use Tools\SingletonInterface;

class Router implements SingletonInterface, Injectable {

    use Singleton;

    /** @var CurrentRoute $currentRoute */
    private $currentRoute;


    function __construct() {

        $route = CurrentRoute::getInstance();
        $this->currentRoute = $route;

        $this->registerSubRoutes();

    }

    private function registerSubRoutes() {

        $sub = SubRouter::getInstance();

        /* Public side routes register */
        $sub->addRoute("content/application.modules.js", "content\\DoGetJavascriptModules");

        /* Dashboard redirect */
        $sub->addRouteRegExp("~^profile(\\/.+)*$~", "content\\DoDashboard");

        $sub->addRoutes([
            "index",
            "streams",
            "bookmarks",
            "login",
            "recover",
            "recover/:code",
            "tag/:tag",
            "signup",
            "signup/:code",
            "static/registrationLetterSent",
            "static/registrationCompleted",
            "static/resetLetterSent",
            "static/resetPasswordCompleted",
            "categories"
        ], "content\\DoDefaultTemplate");

        $sub->addRoute("category/:category", "helpers\\DoCategory");
        $sub->addRoute("streams/:id", "helpers\\DoStream");
        $sub->addRoute("user/:id", "helpers\\DoUser");
        $sub->addRoute("search/:query", "helpers\\DoSearch");

        $sub->addRoute("content/streamcovers/:fn", "content\\DoGetStreamCover");
        $sub->addRoute("content/avatars/:fn", "content\\DoGetUserAvatar");
        $sub->addRoute("content/audio/&id", "content\\DoGetPreviewAudio");
        $sub->addRoute("content/m3u/:stream_id.m3u", "content\\DoM3u");
        $sub->addRoute("content/trackinfo/&id", "content\\DoTrackExtraInfo");

        // Default route
        $sub->defaultRoute(function (Router $router) {
            http_response_code(404);
            $router->callRoute("content\\DoDefaultTemplate");
        });

    }

    public function route() {

        try {

            if (!$this->findRoute()) {
                $sub = SubRouter::getInstance();
                $sub->goMatching($this->currentRoute->getLegacy());
            }


        } catch (UnauthorizedException $e) {

            $this->exceptionRouter($e);

        } catch (ControllerException $e) {

            $this->exceptionRouter($e);

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

        return $this->callRoute($this->currentRoute->getRoute());

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
            throw new View500Exception("Controller must implement Framework\\Controller interface");
        }

        $classInstance = $reflection->newInstance();

        try {

            Injector::getInstance()->call([$classInstance, $method]);


        } catch (\ReflectionException $e) {

            throw new View501Exception();

        }

        return true;

    }

    private function exceptionRouter(ControllerException $exception) {

        $response = JsonResponse::getInstance();

        $response->setMessage($exception->getMyMessage());
        $response->setData($exception->getMyData());
        $response->setCode($exception->getMyStatus());

    }


}