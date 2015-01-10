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
use Framework\Services\HttpGet;
use Framework\Services\HttpRequest;
use Framework\Services\JsonResponse;
use Framework\Services\SubRouter;
use ReflectionClass;
use Tools\Singleton;
use Tools\SingletonInterface;

class Router implements SingletonInterface{

    use Singleton;

    private $route;
    private $legacyRoute;

    function __construct() {

        $httpGet = HttpGet::getInstance();

        $this->legacyRoute = preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->getParameter("route")
            ->getOrElse("index"));

        $routeParts = explode("/", $this->legacyRoute);

        $count = count($routeParts);
        $routeParts[$count - 1] = "Do" . ucfirst($routeParts[$count - 1]);
        $this->route = implode("/", $routeParts);

        // Custom sub routes registration
        $sub = SubRouter::getInstance();
        $sub->addRoute("test/:id", "DoTest");

    }

    public function route() {

        try {

            $sub = SubRouter::getInstance();
            if (!$sub->goMatching($this->legacyRoute)) {
                $this->findRoute();
            }

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

        }

        if (JsonResponse::hasInstance()) {

            $response = JsonResponse::getInstance();

            callPrivateMethod($response, "write");

        }


    }

    private function findRoute() {

        $this->callRoute($this->route);

    }

    public function callRoute($className) {

        $request = HttpRequest::getInstance();
        $method = "do" . ucfirst(strtolower($request->getMethod()));
        $class = str_replace("/", "\\", CONTROLLERS_ROOT . $className);

        // Reflect controller class
        loadClassOrThrow($class, new DocNotFoundException());
        $reflection = new \ReflectionClass($class);

        // Check for valid reflector
        if (!$reflection->implementsInterface("Framework\\Controller")) {
            throw new DocNotFoundException("Controller must implement Framework\\Controller interface");
        }

        try {

            // Try to find required method and get parameters
            $invoker = $reflection->getMethod($method);

        } catch (\ReflectionException $e) {

            throw new NotImplementedException();

        }

        // Create instance of desired controller
        $classInstance = $reflection->newInstance();

        // Execute controller
        $this->callDependencyInjection($classInstance, $invoker);
    }

    private function exceptionRouter(ControllerException $exception) {

        $response = JsonResponse::getInstance();

        $response->setMessage($exception->getMyMessage());
        $response->setData($exception->getMyData());
        $response->setCode(0);

    }


    private function callDependencyInjection($object, \ReflectionMethod $method) {
        $method->setAccessible(true);
        $args = [];
        foreach ($method->getParameters() as $param) {

            /** @var \ReflectionParameter $param */
            if (!$param->getClass()->implementsInterface("Framework\\Services\\Injectable")) {
                throw new \Exception("Object could not be injected");
            }

            if ($param->getClass()->implementsInterface("Tools\\SingletonInterface")) {
                $args[] = $param->getClass()->getMethod("getInstance")->invoke(null);
            } else {
                $args[] = $param->getClass()->newInstanceArgs();
            }

        }
        return $method->invokeArgs($object, $args);
    }

}