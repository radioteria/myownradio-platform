<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace Framework;

use Framework\Exceptions\ControllerException;
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

    }

    public function route() {

        try {

            if (!$this->findRoute()) {
                $sub = SubRouter::getInstance();
                $sub->goMatching($this->currentRoute->getLegacy());
            }

        } catch (ControllerException $e) {

            if (!JsonResponse::hasInstance()) {
                $exception = new ViewException($e->getMyMessage(), 400);
                $exception->render();
                return;
            }

            $this->exceptionRouter($e);

        } catch (ViewException $exception) {

            $exception->render();

            return;

        } catch (\Exception $e) {

            (new View500Exception($e->getMessage(), $e->getTraceAsString()))->render();

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

        // Reflect controller class
        if (!class_exists($className, true)) {
            return false;
        }

        $reflection = new \ReflectionClass($className);

        // Check for valid reflector
        if (!$reflection->implementsInterface(Controller::class)) {
            throw new View500Exception("Controller must implement Controller interface");
        }

        $classInstance = $reflection->newInstance();

        try {

            $result = Injector::getInstance()->call([$classInstance, $method]);

            if (!is_null($result)) {
                JsonResponse::getInstance()->setData($result);
            }

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